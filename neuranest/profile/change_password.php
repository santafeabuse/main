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

// Handle password change request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'request') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Недействительный токен безопасности';
    } elseif (empty($current_password)) {
        $errors[] = $lang['current_password_required'];
    } elseif (!verify_password($current_password, $user['password'])) {
        $errors[] = $lang['current_password_incorrect'];
    } else {
        try {
            // Generate verification code
            $verification_code = generate_verification_code();
            $expires_at = date('Y-m-d H:i:s', time() + VERIFICATION_CODE_LIFETIME);
            
            global $db;
            
            // Delete old codes
            $stmt = $db->prepare("DELETE FROM verification_codes WHERE email = ? AND type = 'password_reset'");
            $stmt->execute([$user['email']]);
            
            // Store verification code
            $stmt = $db->prepare("INSERT INTO verification_codes (email, code, type, expires_at) VALUES (?, ?, 'password_reset', ?)");
            $stmt->execute([$user['email'], $verification_code, $expires_at]);
            
            // Send verification email
            $email_result = send_verification_email($user['email'], $verification_code, 'password_reset');
            
            if ($email_result['success']) {
                redirect('change_password.php?step=verify');
            } else {
                $errors[] = 'Ошибка отправки email: ' . $email_result['message'];
            }
            
        } catch (Exception $e) {
            $errors[] = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Handle password change verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'verify') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $code = sanitize_input($_POST['code'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Недействительный токен безопасности';
    } elseif (empty($code)) {
        $errors[] = $lang['verification_code_required'];
    } elseif (!preg_match('/^\d{6}$/', $code)) {
        $errors[] = $lang['verification_code_invalid'];
    } elseif (empty($new_password)) {
        $errors[] = $lang['new_password_required'];
    } elseif (!is_valid_password($new_password)) {
        $errors[] = $lang['password_min_length'];
    } elseif ($new_password !== $confirm_password) {
        $errors[] = $lang['passwords_not_match'];
    } else {
        try {
            global $db;
            
            // Verify code
            $stmt = $db->prepare("SELECT id FROM verification_codes WHERE email = ? AND code = ? AND type = 'password_reset' AND expires_at > NOW() AND used = FALSE");
            $stmt->execute([$user['email'], $code]);
            $verification = $stmt->fetch();
            
            if ($verification) {
                // Mark code as used
                $stmt = $db->prepare("UPDATE verification_codes SET used = TRUE WHERE id = ?");
                $stmt->execute([$verification['id']]);
                
                // Update password
                $hashed_password = hash_password($new_password);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user['id']]);
                
                $success = $lang['password_changed_successfully'];
                
                // Redirect to profile after 3 seconds
                header("refresh:3;url=profile.php");
                
            } else {
                $errors[] = $lang['verification_code_invalid_or_expired'];
            }
            
        } catch (Exception $e) {
            $errors[] = 'Ошибка: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['change_password']; ?> - <?php echo $lang['site_name']; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Theme Switcher Script -->
    <script src="../assets/js/theme-switcher.js" defer></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- AJAX Functions -->
    <script src="../assets/js/ajax.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .change-password-container {
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 1rem;
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .change-password-container::before {
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
        
        .password-strength {
            margin-top: 0.5rem;
        }
        
        .strength-bar {
            height: 4px;
            background: var(--gray-200);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.25rem;
        }
        
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-text {
            font-size: 0.75rem;
            color: var(--gray-600);
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
        
        /* Dark Theme Styles */
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #3a3a7a 0%, #4a2c6b 100%);
        }
        
        [data-theme="dark"] .change-password-container {
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
        
        [data-theme="dark"] .strength-bar {
            background: var(--gray-700);
        }
        
        [data-theme="dark"] .strength-text {
            color: #dddddd;
        }
    </style>
</head>
<body>
    <div class="change-password-container">
        <a href="profile.php" class="back-btn">
            ← <?php echo $lang['back']; ?>
        </a>
        
        <div class="header">
            <div class="logo"><?php echo $lang['site_name']; ?></div>
            <h1 class="title"><?php echo $lang['change_password']; ?></h1>
            <p class="subtitle">
                <?php if ($step === 'request'): ?>
                    <?php echo $lang['enter_current_password_to_continue']; ?>
                <?php else: ?>
                    <?php echo $lang['enter_verification_code_and_new_password']; ?>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="step-indicator">
            <div class="step <?php echo $step === 'request' ? 'active' : 'inactive'; ?>">
                <span>1</span>
                <span><?php echo $lang['verify_identity']; ?></span>
            </div>
            <div class="step <?php echo $step === 'verify' ? 'active' : 'inactive'; ?>">
                <span>2</span>
                <span><?php echo $lang['new_password']; ?></span>
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
        
        <div id="step-request" style="<?php echo $step === 'verify' ? 'display: none;' : ''; ?>">
            <!-- Step 1: Current Password -->
            <form id="passwordRequestForm">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-group">
                    <label for="current_password" class="form-label"><?php echo $lang['current_password']; ?></label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            class="form-input" 
                            required
                            autocomplete="current-password"
                            autofocus
                        >
                        <span class="password-toggle-icon" onclick="togglePasswordVisibility('current_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁️</span>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <span><?php echo $lang['send_verification_code']; ?></span>
                </button>
            </form>
        </div>
        
        <div id="step-verify" style="<?php echo $step === 'request' ? 'display: none;' : ''; ?>">
            <!-- Step 2: Verification Code and New Password -->
            <form id="changePasswordForm">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="form-group">
                    <label for="code" class="form-label"><?php echo $lang['verification_code']; ?></label>
                    <input 
                        type="text" 
                        id="code" 
                        name="code" 
                        class="form-input code-input" 
                        maxlength="6"
                        pattern="\d{6}"
                        placeholder="000000"
                        required
                        autocomplete="one-time-code"
                    >
                </div>
                
                <div class="form-group">
                    <label for="new_password" class="form-label"><?php echo $lang['new_password']; ?></label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="form-input" 
                            required
                            autocomplete="new-password"
                            minlength="8"
                        >
                        <span class="password-toggle-icon" onclick="togglePasswordVisibility('new_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁️</span>
                    </div>
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label"><?php echo $lang['confirm_password']; ?></label>
                    <div style="position: relative;">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-input" 
                            required
                            autocomplete="new-password"
                        >
                        <span class="password-toggle-icon" onclick="togglePasswordVisibility('confirm_password')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁️</span>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <span><?php echo $lang['change_password']; ?></span>
                </button>
            </form>
        </div>
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
        
        $(document).ready(function() {
            // Handle password request form
            $('#passwordRequestForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                sendPasswordVerificationCode(formData);
            });
            
            // Handle password change form
            $('#changePasswordForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                changePassword(formData);
            });
            
            // Password strength checker
            $('#new_password').on('input', function() {
                const password = $(this).val();
                const strength = calculatePasswordStrength(password);
                
                if (password.length > 0) {
                    $('#passwordStrength').show();
                    updateStrengthIndicator(strength);
                } else {
                    $('#passwordStrength').hide();
                }
            });
            
            function calculatePasswordStrength(password) {
                let score = 0;
                
                if (password.length >= 8) score += 25;
                if (/[a-z]/.test(password)) score += 25;
                if (/[A-Z]/.test(password)) score += 25;
                if (/[\d\W]/.test(password)) score += 25;
                
                return { score };
            }
            
            function updateStrengthIndicator(strength) {
                const { score } = strength;
                const strengthFill = $('#strengthFill');
                const strengthText = $('#strengthText');
                
                strengthFill.css('width', score + '%');
                
                if (score < 50) {
                    strengthFill.css('background', '#ef4444');
                    strengthText.text('<?php echo $lang['weak_password']; ?>').css('color', '#ef4444');
                } else if (score < 75) {
                    strengthFill.css('background', '#f59e0b');
                    strengthText.text('<?php echo $lang['medium_password']; ?>').css('color', '#f59e0b');
                } else if (score < 100) {
                    strengthFill.css('background', '#10b981');
                    strengthText.text('<?php echo $lang['good_password']; ?>').css('color', '#10b981');
                } else {
                    strengthFill.css('background', '#059669');
                    strengthText.text('<?php echo $lang['excellent_password']; ?>').css('color', '#059669');
                }
            }
        });
    </script>
</body>
</html>
