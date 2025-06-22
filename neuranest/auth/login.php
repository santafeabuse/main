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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
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
    
    if (empty($password)) {
        $errors[] = $lang['password_required'];
    }
    
    // Attempt login if no validation errors
    if (empty($errors)) {
        global $db;
        
        try {
            if (!$db) {
                throw new PDOException("Database connection is null");
            }
            $stmt = $db->prepare("SELECT id, email, password, display_name, is_verified, is_premium, avatar FROM users WHERE email = ?");
            if (!$stmt) {
                throw new PDOException("Failed to prepare statement");
            }
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $update_stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                if (!$update_stmt) {
                    throw new PDOException("Failed to prepare update statement");
                }
                $update_stmt->execute([$user['id']]);
                
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_display_name'] = $user['display_name'];
                $_SESSION['is_premium'] = $user['is_premium'];
                $_SESSION['user_avatar'] = $user['avatar'];
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $token_stmt = $db->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))");
                    if (!$token_stmt) {
                        throw new PDOException("Failed to prepare token statement");
                    }
                    $token_stmt->execute([$user['id'], hash('sha256', $token), $expires]);
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expires, '/', '', false, true);
                }
                
                // Redirect to intended page or chat
                $redirect_url = $_SESSION['intended_url'] ?? '../chat/chat.php';
                unset($_SESSION['intended_url']);
                redirect($redirect_url);
            } else {
                $errors[] = $lang['invalid_credentials'];
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
            $errors[] = $lang['login_error'] . " (Debug: " . $e->getMessage() . ")";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['login']; ?> - <?php echo $lang['site_name']; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Apply theme immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('neuranest-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    
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
        
        .form-check {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .form-check-input {
            width: 1rem;
            height: 1rem;
            accent-color: var(--primary-color);
        }
        
        .form-check-label {
            font-size: var(--text-sm);
            color: var(--text-primary);
            cursor: pointer;
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
        
        .forgot-password {
            text-align: center;
            margin-top: var(--spacing-md);
        }
        
        .forgot-password a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: var(--text-sm);
        }
        
        .forgot-password a:hover {
            color: var(--primary-color);
            text-decoration: underline;
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
                <div class="auth-logo">N</div>
                <h1 class="auth-title"><?php echo $lang['login']; ?></h1>
                <p class="auth-subtitle"><?php echo $lang['login_subtitle']; ?></p>
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
                
                <div class="form-group">
                    <label for="password" class="form-label"><?php echo $lang['password']; ?></label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="<?php echo $lang['enter_password']; ?>"
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <div class="form-check">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember" 
                        class="form-check-input"
                        <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>
                    >
                    <label for="remember" class="form-check-label">
                        <?php echo $lang['remember_me']; ?>
                    </label>
                </div>
                
                <button type="submit" class="btn-auth">
                    <?php echo $lang['login']; ?>
                </button>
                
                <div class="forgot-password">
                    <a href="forgot_password.php"><?php echo $lang['forgot_password']; ?></a>
                </div>
            </form>
            
            <div class="auth-links">
                <?php echo $lang['no_account']; ?> 
                <a href="register.php" class="auth-link"><?php echo $lang['register_now']; ?></a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript --<script src="../assets/js/main.js"></script>
    <script>
        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('<?php echo $lang['fill_all_fields']; ?>');
                return false;
            }
            
            // Disable submit button to prevent double submission
            const submitBtn = this.querySelector('.btn-auth');
            submitBtn.disabled = true;
            submitBtn.textContent = '<?php echo $lang['logging_in']; ?>...';
        });
    </script>
</body>
</html>
