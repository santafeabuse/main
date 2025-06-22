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
    redirect($_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
}

$lang = load_language();
$errors = [];
$success = '';

// Get verification type
$type = $_GET['type'] ?? 'registration';
if (!in_array($type, ['registration', 'password_reset', 'email_change'])) {
    $type = 'registration';
}

// Check if we have the necessary session data
if ($type === 'registration' && !isset($_SESSION['registration_data'])) {
    redirect('register.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = sanitize_input($_POST['code'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // CSRF validation
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Недействительный токен безопасности';
    }
    
    // Validation
    if (empty($code)) {
        $errors[] = $lang['verification_code_required'];
    } elseif (!preg_match('/^\d{6}$/', $code)) {
        $errors[] = $lang['verification_code_invalid'];
    }
    
    // Verify code
    if (empty($errors)) {
        global $db;
        
        try {
            if ($type === 'registration') {
                $registration_data = $_SESSION['registration_data'];
                $email = $registration_data['email'];
                
                // Check verification code
                $stmt = $db->prepare("SELECT id FROM verification_codes WHERE email = ? AND code = ? AND type = 'registration' AND expires_at > NOW() AND used = FALSE");
                $stmt->execute([$email, $code]);
                $verification = $stmt->fetch();
                
                if ($verification) {
                    // Mark code as used
                    $stmt = $db->prepare("UPDATE verification_codes SET used = TRUE WHERE id = ?");
                    $stmt->execute([$verification['id']]);
                    
                    // Create user account
                    $stmt = $db->prepare("INSERT INTO users (email, password, created_at, is_verified) VALUES (?, ?, NOW(), TRUE)");
                    $stmt->execute([$email, $registration_data['password']]);
                    $user_id = $db->lastInsertId();
                    
                    // Log in the user
                    login_user($user_id);
                    
                    // Clean up session data
                    unset($_SESSION['registration_data']);
                    
                    // Redirect to chat
                    redirect('../chat/chat.php');
                } else {
                    $errors[] = $lang['verification_code_invalid_or_expired'];
                }
            }
            // Add other verification types here if needed
            
        } catch (Exception $e) {
            $errors[] = 'Ошибка верификации: ' . $e->getMessage();
        }
    }
}

// Handle resend code
if (isset($_POST['resend_code'])) {
    if ($type === 'registration' && isset($_SESSION['registration_data'])) {
        try {
            $registration_data = $_SESSION['registration_data'];
            $email = $registration_data['email'];
            
            // Generate new verification code
            $verification_code = generate_verification_code();
            $expires_at = date('Y-m-d H:i:s', time() + VERIFICATION_CODE_LIFETIME);
            
            // Update verification code in database
            $stmt = $db->prepare("UPDATE verification_codes SET code = ?, expires_at = ?, used = FALSE WHERE email = ? AND type = 'registration'");
            $stmt->execute([$verification_code, $expires_at, $email]);
            
            // Update session data
            $_SESSION['registration_data']['verification_code'] = $verification_code;
            
            // Send new verification email
            require_once '../includes/mail_config.php';
            $email_result = send_verification_email($email, $verification_code, 'registration');
            
            if ($email_result['success']) {
                $success = $lang['verification_code_resent'];
            } else {
                $errors[] = 'Ошибка отправки email: ' . $email_result['message'];
            }
            
        } catch (Exception $e) {
            $errors[] = 'Ошибка отправки кода: ' . $e->getMessage();
        }
    }
}

// Get email for display
$display_email = '';
if ($type === 'registration' && isset($_SESSION['registration_data'])) {
    $display_email = $_SESSION['registration_data']['email'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['email_verification']; ?> - <?php echo $lang['site_name']; ?></title>
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo $lang['email_verification']; ?> - <?php echo $lang['site_name']; ?>">
    <meta name="robots" content="noindex, nofollow">
    
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
            line-height: 1.5;
        }
        
        .email-display {
            background: var(--bg-secondary);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin: var(--spacing-md) 0;
            text-align: center;
            font-weight: 500;
            color: var(--text-primary);
            word-break: break-all;
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
        
        .btn-secondary {
            width: 100%;
            padding: var(--spacing-md);
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            border-radius: var(--radius-md);
            font-size: var(--text-base);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .btn-secondary:hover:not(:disabled) {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-secondary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
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
        
        .resend-info {
            text-align: center;
            margin: var(--spacing-md) 0;
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }
        
        .countdown {
            font-weight: 600;
            color: var(--primary-color);
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
        <a href="?lang=ru&type=<?php echo $type; ?>" class="lang-btn <?php echo get_current_language() === 'ru' ? 'active' : ''; ?>">RU</a>
        <a href="?lang=en&type=<?php echo $type; ?>" class="lang-btn <?php echo get_current_language() === 'en' ? 'active' : ''; ?>">EN</a>
    </div>
    
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">N</div>
                <h1 class="auth-title"><?php echo $lang['email_verification']; ?></h1>
                <p class="auth-subtitle">
                    <?php echo $lang['verification_code_sent']; ?>
                    <?php if ($display_email): ?>
                        <div class="email-display"><?php echo htmlspecialchars($display_email); ?></div>
                    <?php endif; ?>
                </p>
            </div>
        
        <!-- Errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Success -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Verification Form -->
        <form method="POST" id="verifyForm">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="form-group">
                <label for="code" class="form-label"><?php echo $lang['verification_code']; ?></label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    class="form-input" 
                    maxlength="6"
                    pattern="\d{6}"
                    placeholder="000000"
                    required
                    autocomplete="one-time-code"
                    autofocus
                >
            </div>
            
            <button type="submit" class="btn-auth" id="submitBtn">
                <span id="submitText"><?php echo $lang['verify']; ?></span>
                <div class="spinner" id="submitSpinner" style="display: none;"></div>
            </button>
        </form>
        
        <!-- Resend Code -->
        <div class="resend-info">
            <p><?php echo $lang['didnt_receive_code']; ?></p>
            <div id="resendContainer">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <button type="submit" name="resend_code" class="btn-secondary" id="resendBtn">
                        <span id="resendText"><?php echo $lang['resend_code']; ?></span>
                        <div class="spinner" id="resendSpinner" style="display: none;"></div>
                    </button>
                </form>
            </div>
            <div id="countdownContainer" style="display: none;">
                <p><?php echo $lang['resend_available_in']; ?> <span class="countdown" id="countdown">60</span> <?php echo $lang['seconds']; ?></p>
            </div>
        </div>
        
            <div class="auth-links">
                <a href="register.php" class="auth-link"><?php echo $lang['back_to_registration']; ?></a>
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
        
        // Auto-format verification code input
        const codeInput = document.getElementById('code');
        
        codeInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 6) {
                value = value.slice(0, 6);
            }
            e.target.value = value;
            
            // Auto-submit when 6 digits are entered
            if (value.length === 6) {
                document.getElementById('verifyForm').submit();
            }
        });
        
        // Prevent non-numeric input
        codeInput.addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
        
        // Form submission handling
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = '<?php echo $lang['verifying']; ?>...';
            
            // Re-enable button after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = '<?php echo $lang['verify']; ?>';
            }, 5000);
        });
        
        // Resend code handling
        let resendCooldown = 60;
        let resendTimer = null;
        
        function startResendCooldown() {
            const resendContainer = document.getElementById('resendContainer');
            const countdownContainer = document.getElementById('countdownContainer');
            const countdownElement = document.getElementById('countdown');
            
            resendContainer.style.display = 'none';
            countdownContainer.style.display = 'block';
            
            resendTimer = setInterval(() => {
                resendCooldown--;
                countdownElement.textContent = resendCooldown;
                
                if (resendCooldown <= 0) {
                    clearInterval(resendTimer);
                    resendContainer.style.display = 'block';
                    countdownContainer.style.display = 'none';
                    resendCooldown = 60;
                }
            }, 1000);
        }
        
        // Handle resend form submission
        document.querySelector('form[method="POST"]').addEventListener('submit', function(e) {
            if (e.target.querySelector('button[name="resend_code"]')) {
                const resendBtn = document.getElementById('resendBtn');
                
                // Show loading state
                resendBtn.disabled = true;
                resendBtn.textContent = '<?php echo $lang['sending']; ?>...';
                
                // Start cooldown after form submission
                setTimeout(() => {
                    resendBtn.disabled = false;
                    resendBtn.textContent = '<?php echo $lang['resend_code']; ?>';
                    startResendCooldown();
                }, 2000);
            }
        });
        
        // Auto-focus on code input
        window.addEventListener('load', function() {
            codeInput.focus();
        });
    </script>
</body>
</html>
