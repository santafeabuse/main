<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('../chat/chat.php');
}

// Handle language switching
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en'])) {
    set_language($_GET['lang']);
    redirect($_SERVER['PHP_SELF'] . '?token=' . ($_GET['token'] ?? ''));
}

$lang = load_language();
$errors = [];
$success = '';
$token = $_GET['token'] ?? '';

// Validate token
$valid_token = false;
$email = '';

if ($token) {
    global $db;
    try {
        $stmt = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset_data = $stmt->fetch();
        
        if ($reset_data) {
            $valid_token = true;
            $email = $reset_data['email'];
        } else {
            $errors[] = $lang['invalid_or_expired_token'];
        }
    } catch (PDOException $e) {
        error_log("Token validation error: " . $e->getMessage());
        $errors[] = $lang['reset_error'];
    }
} else {
    $errors[] = $lang['invalid_token'];
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = $lang['invalid_csrf_token'];
    }
    
    // Validate input
    if (empty($new_password)) {
        $errors[] = $lang['password_required'];
    } elseif (strlen($new_password) < 8) {
        $errors[] = $lang['password_min_length'];
    }
    
    if (empty($confirm_password)) {
        $errors[] = $lang['confirm_password_required'];
    } elseif ($new_password !== $confirm_password) {
        $errors[] = $lang['passwords_not_match'];
    }
    
    // Update password if no validation errors
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update user password
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);
            
            // Delete used reset token
            $stmt = $db->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = $lang['password_reset_successful'];
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            $errors[] = $lang['reset_error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['reset_password']) ? $lang['reset_password'] : 'Reset Password'; ?> - <?php echo isset($lang['site_name']) ? $lang['site_name'] : 'NeuraNest'; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        body {
            background: var(--bg-secondary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
        }
        
        .auth-container {
            width: 100%;
            max-width: 400px;
        }
        
        .auth-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border-color);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }
        
        .auth-logo {
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-lg);
            font-size: var(--text-2xl);
            color: white;
            font-weight: 700;
        }
        
        .auth-title {
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--spacing-sm);
        }
        
        .auth-subtitle {
            color: var(--text-secondary);
            font-size: var(--text-sm);
            line-height: 1.5;
        }
        
        .auth-form {
            margin-bottom: var(--spacing-xl);
        }
        
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: var(--spacing-sm);
            font-size: var(--text-sm);
        }
        
        .form-input {
            width: 100%;
            padding: var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--text-base);
            transition: var(--transition-fast);
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-auth {
            width: 100%;
            padding: var(--spacing-md);
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--text-base);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
            margin-bottom: var(--spacing-lg);
        }
        
        .btn-auth:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-auth:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .auth-links {
            text-align: center;
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--border-color);
        }
        
        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: var(--text-sm);
            transition: var(--transition-fast);
        }
        
        .auth-link:hover {
            text-decoration: underline;
        }
        
        .language-switcher {
            position: absolute;
            top: var(--spacing-lg);
            right: var(--spacing-lg);
            display: flex;
            gap: var(--spacing-xs);
        }
        
        .lang-btn {
            padding: var(--spacing-xs) var(--spacing-sm);
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-size: var(--text-sm);
            transition: var(--transition-fast);
        }
        
        .lang-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .lang-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .alert {
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        [data-theme="dark"] .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border-color: rgba(239, 68, 68, 0.2);
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        [data-theme="dark"] .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #6ee7b7;
            border-color: rgba(16, 185, 129, 0.2);
        }
        
        .password-strength {
            margin-top: var(--spacing-sm);
            font-size: var(--text-sm);
        }
        
        .strength-weak { color: #ef4444; }
        .strength-medium { color: #f59e0b; }
        .strength-good { color: #10b981; }
        .strength-excellent { color: #059669; }
        
        @media (max-width: 480px) {
            .auth-card {
                padding: var(--spacing-xl);
            }
            
            .language-switcher {
                position: static;
                justify-content: center;
                margin-bottom: var(--spacing-lg);
            }
        }
    </style>
</head>
<body>
    <!-- Language Switcher -->
    <div class="language-switcher">
        <a href="?lang=ru&token=<?php echo htmlspecialchars($token); ?>" class="lang-btn <?php echo get_current_language() === 'ru' ? 'active' : ''; ?>">RU</a>
        <a href="?lang=en&token=<?php echo htmlspecialchars($token); ?>" class="lang-btn <?php echo get_current_language() === 'en' ? 'active' : ''; ?>">EN</a>
    </div>
    
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">🔐</div>
                <h1 class="auth-title"><?php echo isset($lang['reset_password']) ? $lang['reset_password'] : 'Reset Password'; ?></h1>
                <?php if ($valid_token && empty($success)): ?>
                    <p class="auth-subtitle"><?php echo $lang['reset_password_subtitle']; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Alerts -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($valid_token && empty($success)): ?>
            <form class="auth-form" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-group">
                    <label for="new_password" class="form-label"><?php echo $lang['new_password']; ?></label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="form-input" 
                        placeholder="<?php echo $lang['enter_new_password']; ?>"
                        required
                        minlength="8"
                    >
                    <div id="password-strength" class="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label"><?php echo $lang['confirm_password']; ?></label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-input" 
                        placeholder="<?php echo $lang['confirm_new_password']; ?>"
                        required
                        minlength="8"
                    >
                </div>
                
                    <button type="submit" class="btn-auth">
                    <?php echo isset($lang['reset_password']) ? $lang['reset_password'] : 'Reset Password'; ?>
                </button>
            </form>
            <?php endif; ?>
            
            <div class="auth-links">
                <a href="login.php" class="auth-link">← <?php echo $lang['back_to_login']; ?></a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/theme-switcher.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Add theme switcher to language switcher container
            window.themeSwitcher.addToContainer(document.querySelector('.language-switcher'));
            
            // Password strength checker
            const passwordInput = document.getElementById('new_password');
            const strengthDiv = document.getElementById('password-strength');
            
            if (passwordInput && strengthDiv) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    const strength = checkPasswordStrength(password);
                    
                    strengthDiv.className = 'password-strength strength-' + strength.level;
                    strengthDiv.textContent = strength.text;
                });
            }
            
            // Form validation
            document.querySelector('.auth-form')?.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (!newPassword || !confirmPassword) {
                    e.preventDefault();
                    alert('<?php echo $lang['fill_all_fields']; ?>');
                    return false;
                }
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('<?php echo $lang['passwords_not_match']; ?>');
                    return false;
                }
                
                // Disable submit button to prevent double submission
                const submitBtn = this.querySelector('.btn-auth');
                submitBtn.disabled = true;
                submitBtn.textContent = '<?php echo $lang['resetting_password']; ?>...';
            });
        });
        
        function checkPasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            if (score < 3) {
                return { level: 'weak', text: '<?php echo $lang['weak_password']; ?>' };
            } else if (score < 4) {
                return { level: 'medium', text: '<?php echo $lang['medium_password']; ?>' };
            } else if (score < 6) {
                return { level: 'good', text: '<?php echo $lang['good_password']; ?>' };
            } else {
                return { level: 'excellent', text: '<?php echo $lang['excellent_password']; ?>' };
            }
        }
    </script>
</body>
</html>
