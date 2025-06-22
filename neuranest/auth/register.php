<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/mail_config.php';

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
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms_agreed = isset($_POST['terms_agreed']);
    $csrf_token = $_POST['csrf_token'] ?? '';

    // CSRF validation
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Недействительный токен безопасности';
    }

    // Validation
    if (empty($email)) {
        $errors[] = $lang['email_required'];
    } elseif (!is_valid_email($email)) {
        $errors[] = $lang['email_invalid'];
    }

    if (empty($password)) {
        $errors[] = $lang['password_required'];
    } elseif (!is_valid_password($password)) {
        $errors[] = $lang['password_min_length'];
    }

    if ($password !== $confirm_password) {
        $errors[] = $lang['passwords_not_match'];
    }

    if (!$terms_agreed) {
        $errors[] = $lang['terms_required'];
    }

    // Check if email already exists
    if (empty($errors)) {
        global $db;
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = $lang['email_already_exists'];
        }
    }

    // If no errors, send verification email
    if (empty($errors)) {
        try {
            // Generate verification code
            $verification_code = generate_verification_code();
            $expires_at = date('Y-m-d H:i:s', time() + VERIFICATION_CODE_LIFETIME);

            // Store verification code
            $stmt = $db->prepare("INSERT INTO verification_codes (email, code, type, expires_at) VALUES (?, ?, 'registration', ?)");
            $stmt->execute([$email, $verification_code, $expires_at]);

            // Store user data in session for verification step
            $_SESSION['registration_data'] = [
                'email' => $email,
                'password' => hash_password($password),
                'verification_code' => $verification_code
            ];

            // Send verification email
            $email_result = send_verification_email($email, $verification_code, 'registration');

            if ($email_result['success']) {
                redirect('verify.php?type=registration');
            } else {
                $errors[] = 'Ошибка отправки email: ' . $email_result['message'];
            }

        } catch (Exception $e) {
            $errors[] = 'Ошибка регистрации: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['register']; ?> - <?php echo $lang['site_name']; ?></title>

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
        
        .form-check-label a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .form-check-label a:hover {
            text-decoration: underline;
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
                <div class="auth-logo">N</div>
                <h1 class="auth-title"><?php echo $lang['register']; ?></h1>
                <p class="auth-subtitle"><?php echo $lang['register_subtitle']; ?></p>
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
                        autocomplete="new-password"
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label"><?php echo $lang['confirm_password']; ?></label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-input"
                        placeholder="<?php echo $lang['confirm_password']; ?>"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <div class="form-check">
                    <input
                        type="checkbox"
                        id="terms_agreed"
                        name="terms_agreed"
                        class="form-check-input"
                        <?php echo isset($_POST['terms_agreed']) ? 'checked' : ''; ?>
                    >
                    <label for="terms_agreed" class="form-check-label">
                        <?php echo $lang['terms_agree']; ?>
                        <a href="#" onclick="openTermsModal()"><?php echo $lang['terms_of_service']; ?></a>
                    </label>
                </div>

                <button type="submit" class="btn-auth">
                    <?php echo $lang['register']; ?>
                </button>
            </form>

            <div class="auth-links">
                <?php echo $lang['already_have_account']; ?>
                <a href="login.php" class="auth-link"><?php echo $lang['login_now']; ?></a>
            </div>
        </div>
    </div>

    <!-- JavaScript --<script src="../assets/js/main.js"></script>
    <script>
        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirm_password = document.getElementById('confirm_password').value;
            const terms_agreed = document.getElementById('terms_agreed').checked;

            if (!email || !password || !confirm_password) {
                e.preventDefault();
                alert('<?php echo $lang['fill_all_fields']; ?>');
                return false;
            }
            
            if (password !== confirm_password) {
                e.preventDefault();
                alert('<?php echo $lang['passwords_not_match']; ?>');
                return false;
            }
            
            if (!terms_agreed) {
                e.preventDefault();
                alert('<?php echo $lang['terms_required']; ?>');
                return false;
            }

            // Disable submit button to prevent double submission
            const submitBtn = this.querySelector('.btn-auth');
            submitBtn.disabled = true;
            submitBtn.textContent = '<?php echo $lang['registering']; ?>...';
        });
        
        // Terms modal (placeholder)
        function openTermsModal() {
            alert('Здесь будет модальное окно с условиями использования');
        }
    </script>
</body>
</html>