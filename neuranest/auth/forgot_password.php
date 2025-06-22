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
    redirect($_SERVER['PHP_SELF']);
}

$lang = load_language();
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = $lang['invalid_csrf_token'];
    }
    
    // Validate input
    if (empty($email)) {
        $errors[] = $lang['email_required'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang['invalid_email'];
    }
    
    // Process password reset if no validation errors
    if (empty($errors)) {
        global $db;
        
        try {
            // Check if user exists
            $stmt = $db->prepare("SELECT id, email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
                
                // Store reset token
                $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = ?, expires_at = ?");
                $stmt->execute([$email, $token, $expires, $token, $expires]);
                
                // Send reset email using the function from mail_config.php
                // Use a custom base for the email link to start with neuranest but include protocol as per user request
                $reset_link = "http://neuranest/auth/reset_password.php?token=" . $token;
                require_once '../includes/mail_config.php';
                $email_result = send_verification_email($email, $reset_link, 'password_reset');
                
                if ($email_result['success']) {
                    $success = $lang['password_reset_sent'];
                } else {
                    error_log("Failed to send reset email: " . $email_result['message']);
                    // For debugging purposes, temporarily add detailed error to errors array
                    // Remove this in production to avoid exposing sensitive information
                    $errors[] = $lang['reset_error'] . " (Debug: " . $email_result['message'] . ")";
                }
            } else {
                // Don't reveal if email exists or not for security
                $success = $lang['password_reset_sent'];
            }
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            // For debugging purposes, temporarily add detailed error to errors array
            // Remove this in production to avoid exposing sensitive information
            $errors[] = $lang['reset_error'] . " (Debug: " . $e->getMessage() . ")";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['forgot_password']; ?> - <?php echo $lang['site_name']; ?></title>
    
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
        <a href="?lang=ru" class="lang-btn <?php echo get_current_language() === 'ru' ? 'active' : ''; ?>">RU</a>
        <a href="?lang=en" class="lang-btn <?php echo get_current_language() === 'en' ? 'active' : ''; ?>">EN</a>
    </div>
    
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">🔑</div>
                <h1 class="auth-title"><?php echo $lang['forgot_password']; ?></h1>
                <p class="auth-subtitle"><?php echo $lang['forgot_password_subtitle']; ?></p>
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
            
            <?php if (empty($success)): ?>
            <form class="auth-form" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label"><?php echo $lang['email']; ?></label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        placeholder="<?php echo $lang['enter_email']; ?>"
                        required
                        autocomplete="email"
                    >
                </div>
                
                <button type="submit" class="btn-auth">
                    <?php echo $lang['send_reset_link']; ?>
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
        });
        
        // Form validation
        document.querySelector('.auth-form')?.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            
            if (!email) {
                e.preventDefault();
                alert('<?php echo $lang['email_required']; ?>');
                return false;
            }
            
            // Disable submit button to prevent double submission
            const submitBtn = this.querySelector('.btn-auth');
            submitBtn.disabled = true;
            submitBtn.textContent = '<?php echo $lang['sending']; ?>...';
        });
    </script>
</body>
</html>
