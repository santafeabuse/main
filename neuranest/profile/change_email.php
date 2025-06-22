<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/mail_config.php';

// Require login
require_login();

// Handle language switching
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en'])) {
    set_language($_GET['lang']);
    redirect($_SERVER['PHP_SELF']);
}

$lang = load_language();
$user = get_logged_in_user();
$errors = [];
$success = '';
$step = $_GET['step'] ?? 'request';

// Handle email change request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'request') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $new_email = sanitize_input($_POST['new_email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Недействительный токен безопасности';
    } elseif (empty($new_email)) {
        $errors[] = $lang['email_required'];
    } elseif (!is_valid_email($new_email)) {
        $errors[] = $lang['email_invalid'];
    } elseif ($new_email === $user['email']) {
        $errors[] = $lang['email_same_as_current'];
    } elseif (empty($password)) {
        $errors[] = $lang['password_required'];
    } elseif (!verify_password($password, $user['password'])) {
        $errors[] = $lang['password_incorrect'];
    } else {
        try {
            global $db;
            
            // Check if new email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$new_email, $user['id']]);
            if ($stmt->fetch()) {
                $errors[] = $lang['email_already_exists'];
            } else {
                // Generate verification codes
                $old_email_code = generate_verification_code();
                $new_email_code = generate_verification_code();
                $expires_at = date('Y-m-d H:i:s', time() + VERIFICATION_CODE_LIFETIME);
                
                // Delete old codes
                $stmt = $db->prepare("DELETE FROM verification_codes WHERE email IN (?, ?) AND type = 'email_change'");
                $stmt->execute([$user['email'], $new_email]);
                
                // Store verification codes
                $stmt = $db->prepare("INSERT INTO verification_codes (email, code, type, expires_at) VALUES (?, ?, 'email_change', ?)");
                $stmt->execute([$user['email'], $old_email_code, $expires_at]);
                
                $stmt = $db->prepare("INSERT INTO verification_codes (email, code, type, expires_at) VALUES (?, ?, 'email_change', ?)");
                $stmt->execute([$new_email, $new_email_code, $expires_at]);
                
                // Store new email in session
                $_SESSION['new_email'] = $new_email;
                $_SESSION['old_email_code'] = $old_email_code;
                $_SESSION['new_email_code'] = $new_email_code;
                
                // Send verification emails
                $old_email_result = send_verification_email($user['email'], $old_email_code, 'email_change');
                $new_email_result = send_verification_email($new_email, $new_email_code, 'email_change');
                
                if ($old_email_result['success'] && $new_email_result['success']) {
                    redirect('change_email.php?step=verify');
                } else {
                    $errors[] = 'Ошибка отправки email';
                }
            }
            
        } catch (Exception $e) {
            $errors[] = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Handle email change verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'verify') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $old_code = sanitize_input($_POST['old_code'] ?? '');
    $new_code = sanitize_input($_POST['new_code'] ?? '');
    
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Недействительный токен безопасности';
    } elseif (empty($old_code) || empty($new_code)) {
        $errors[] = $lang['both_codes_required'];
    } elseif (!preg_match('/^\d{6}$/', $old_code) || !preg_match('/^\d{6}$/', $new_code)) {
        $errors[] = $lang['verification_code_invalid'];
    } elseif (!isset($_SESSION['new_email'])) {
        $errors[] = $lang['session_expired'];
        redirect('change_email.php');
    } else {
        try {
            global $db;
            
            $new_email = $_SESSION['new_email'];
            
            // Verify both codes
            $stmt = $db->prepare("SELECT id FROM verification_codes WHERE email = ? AND code = ? AND type = 'email_change' AND expires_at > NOW() AND used = FALSE");
            $stmt->execute([$user['email'], $old_code]);
            $old_verification = $stmt->fetch();
            
            $stmt = $db->prepare("SELECT id FROM verification_codes WHERE email = ? AND code = ? AND type = 'email_change' AND expires_at > NOW() AND used = FALSE");
            $stmt->execute([$new_email, $new_code]);
            $new_verification = $stmt->fetch();
            
            if ($old_verification && $new_verification) {
                // Mark codes as used
                $stmt = $db->prepare("UPDATE verification_codes SET used = TRUE WHERE id IN (?, ?)");
                $stmt->execute([$old_verification['id'], $new_verification['id']]);
                
                // Update email
                $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->execute([$new_email, $user['id']]);
                
                // Clean up session
                unset($_SESSION['new_email'], $_SESSION['old_email_code'], $_SESSION['new_email_code']);
                
                $success = $lang['email_changed_successfully'];
                
                // Redirect to profile after 3 seconds
                header("refresh:3;url=profile.php");
                
            } else {
                $errors[] = $lang['verification_codes_invalid_or_expired'];
            }
            
        } catch (Exception $e) {
            $errors[] = 'Ошибка: ' . $e->getMessage();
        }
    }
}

$new_email = $_SESSION['new_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['change_email']; ?> - <?php echo $lang['site_name']; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Theme Switcher Script -->
    <script src="../assets/js/theme-switcher.js" defer></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .change-email-container {
            width: 100%;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 1rem;
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .change-email-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .back-btn {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: none;
            border: none;
            color: var(--gray-600);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: var(--transition-fast);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-btn:hover {
            background: var(--gray-100);
            color: var(--primary-color);
        }
        
        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .step.active {
            background: var(--primary-color);
            color: white;
        }
        
        .step.inactive {
            background: var(--gray-200);
            color: var(--gray-600);
        }
        
        .email-display {
            background: var(--gray-100);
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1rem 0;
            text-align: center;
            font-weight: 500;
            color: var(--gray-700);
        }
        
        .verification-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .verification-card {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 0.75rem;
            text-align: center;
        }
        
        .verification-title {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }
        
        .verification-email {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
            word-break: break-all;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-input.error {
            border-color: var(--error-color);
        }
        
        .code-input {
            text-align: center;
            letter-spacing: 0.5rem;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .btn {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .verification-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Dark Theme Styles */
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #3a3a7a 0%, #4a2c6b 100%);
        }
        
        [data-theme="dark"] .change-email-container {
            background: rgba(30, 30, 30, 0.95);
            color: #ffffff;
        }
        
        [data-theme="dark"] .back-btn {
            color: #ffffff;
        }
        
        [data-theme="dark"] .back-btn:hover {
            background: var(--gray-800);
            color: var(--primary-color);
        }
        
        [data-theme="dark"] .title {
            color: #ffffff;
        }
        
        [data-theme="dark"] .subtitle {
            color: #dddddd;
        }
        
        [data-theme="dark"] .step.inactive {
            background: var(--gray-800);
            color: black;
        }
        
        [data-theme="dark"] .email-display {
            background: var(--gray-800);
            color: #eeeeee;
        }
        
        [data-theme="dark"] .verification-card {
            background: var(--gray-800);
        }
        
        [data-theme="dark"] .verification-title {
            color: #ffffff;
        }
        
        [data-theme="dark"] .verification-email {
            color: #dddddd;
        }
        
        [data-theme="dark"] .form-label {
            color: #eeeeee;
        }
        
        [data-theme="dark"] .form-input {
            background: var(--gray-800);
            color: #000000;
            border-color: var(--gray-700);
        }
        
        [data-theme="dark"] .form-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        
        [data-theme="dark"] .alert-error {
            background: #3a1a1a;
            color: #ffcccc;
            border-color: #5a2a2a;
        }
        
        [data-theme="dark"] .alert-success {
            background: #1a3a2a;
            color: #ccffdd;
            border-color: #2a5a3a;
        }
    </style>
</head>
<body>
    <div class="change-email-container">
        <a href="profile.php" class="back-btn">
            ← <?php echo $lang['back']; ?>
        </a>
        
        <div class="header">
            <div class="logo"><?php echo $lang['site_name']; ?></div>
            <h1 class="title"><?php echo $lang['change_email']; ?></h1>
            <p class="subtitle">
                <?php if ($step === 'request'): ?>
                    <?php echo $lang['enter_new_email_and_password']; ?>
                <?php else: ?>
                    <?php echo $lang['enter_both_verification_codes']; ?>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="step-indicator">
            <div class="step <?php echo $step === 'request' ? 'active' : 'inactive'; ?>">
                <span>1</span>
                <span><?php echo $lang['new_email']; ?></span>
            </div>
            <div class="step <?php echo $step === 'verify' ? 'active' : 'inactive'; ?>">
                <span>2</span>
                <span><?php echo $lang['verification']; ?></span>
            </div>
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
                <br><small><?php echo $lang['redirecting_to_profile']; ?></small>
            </div>
        <?php endif; ?>
        
        <?php if ($step === 'request' && empty($success)): ?>
        <!-- Step 1: New Email and Password -->
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="form-group">
                <label for="current_email" class="form-label"><?php echo $lang['current_email']; ?></label>
                <input 
                    type="email" 
                    id="current_email" 
                    class="form-input" 
                    value="<?php echo htmlspecialchars($user['email']); ?>"
                    disabled
                >
            </div>
            
            <div class="form-group">
                <label for="new_email" class="form-label"><?php echo $lang['new_email']; ?></label>
                <input 
                    type="email" 
                    id="new_email" 
                    name="new_email" 
                    class="form-input" 
                    required
                    autocomplete="email"
                    autofocus
                    value="<?php echo htmlspecialchars($_POST['new_email'] ?? ''); ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label"><?php echo $lang['current_password']; ?></label>
                <div style="position: relative;">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required
                        autocomplete="current-password"
                    >
                    <span class="password-toggle-icon" onclick="togglePasswordVisibility('password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁️</span>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <span><?php echo $lang['send_verification_codes']; ?></span>
            </button>
        </form>
        
        <?php elseif ($step === 'verify' && empty($success)): ?>
        <!-- Step 2: Verification Codes -->
        <div class="email-display">
            <?php echo $lang['verification_codes_sent_to_both_emails']; ?>
        </div>
        
        <form method="POST" id="verifyForm">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="verification-grid">
                <div class="verification-card">
                    <div class="verification-title"><?php echo $lang['current_email']; ?></div>
                    <div class="verification-email"><?php echo htmlspecialchars($user['email']); ?></div>
                    <input 
                        type="text" 
                        name="old_code" 
                        class="form-input code-input" 
                        maxlength="6"
                        pattern="\d{6}"
                        placeholder="000000"
                        required
                        autocomplete="one-time-code"
                    >
                </div>
                
                <div class="verification-card">
                    <div class="verification-title"><?php echo $lang['new_email']; ?></div>
                    <div class="verification-email"><?php echo htmlspecialchars($new_email); ?></div>
                    <input 
                        type="text" 
                        name="new_code" 
                        class="form-input code-input" 
                        maxlength="6"
                        pattern="\d{6}"
                        placeholder="000000"
                        required
                        autocomplete="one-time-code"
                    >
                </div>
            </div>
            
            <button type="submit" class="btn" id="submitBtn">
                <span id="submitText"><?php echo $lang['change_email']; ?></span>
                <div class="spinner" id="submitSpinner" style="display: none;"></div>
            </button>
        </form>
        <?php endif; ?>
    </div>
    
    <script>
        // Toggle password visibility
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = '🙈';
            } else {
                input.type = 'password';
                icon.textContent = '👁️';
            }
        }
        
        // Auto-format verification code inputs
        const codeInputs = document.querySelectorAll('.code-input');
        codeInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 6) {
                    value = value.slice(0, 6);
                }
                e.target.value = value;
            });
            
            input.addEventListener('keypress', function(e) {
                if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                    e.preventDefault();
                }
            });
        });
        
        // Form submission handling
        const form = document.getElementById('verifyForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitBtn');
                const submitText = document.getElementById('submitText');
                const submitSpinner = document.getElementById('submitSpinner');
                
                submitBtn.disabled = true;
                submitText.style.display = 'none';
                submitSpinner.style.display = 'block';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitText.style.display = 'block';
                    submitSpinner.style.display = 'none';
                }, 5000);
            });
        }
    </script>
</body>
</html>
