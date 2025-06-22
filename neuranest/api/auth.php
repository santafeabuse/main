<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/mail_config.php';

header('Content-Type: application/json');

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$lang = load_language();

switch ($action) {
    case 'login':
        handleLogin($input, $lang);
        break;
    
    case 'register':
        handleRegister($input, $lang);
        break;
    
    case 'forgot_password':
        handleForgotPassword($input, $lang);
        break;
    
    case 'reset_password':
        handleResetPassword($input, $lang);
        break;
    
    case 'verify_email':
        handleVerifyEmail($input, $lang);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function handleLogin($input, $lang) {
    $email = sanitize_input($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $remember = $input['remember'] ?? false;
    $csrf_token = $input['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!verify_csrf_token($csrf_token)) {
        echo json_encode(['success' => false, 'message' => $lang['invalid_csrf_token']]);
        return;
    }
    
    // Validate input
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => $lang['email_required']]);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => $lang['invalid_email']]);
        return;
    }
    
    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => $lang['password_required']]);
        return;
    }
    
    // Attempt login
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT id, email, password, display_name, is_verified, is_premium, avatar FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $update_stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
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
                $token_stmt = $db->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))");
                $token_stmt->execute([$user['id'], hash('sha256', $token), $expires]);
                
                // Set cookie
                setcookie('remember_token', $token, $expires, '/', '', false, true);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $lang['login_successful'] ?? 'Login successful',
                'redirect' => '../chat/chat.php'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => $lang['invalid_credentials']]);
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $lang['login_error']]);
    }
}

function handleRegister($input, $lang) {
    $email = sanitize_input($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $confirm_password = $input['confirm_password'] ?? '';
    $terms_agreed = $input['terms_agreed'] ?? false;
    $csrf_token = $input['csrf_token'] ?? '';
    
    // CSRF validation
    if (!verify_csrf_token($csrf_token)) {
        echo json_encode(['success' => false, 'message' => $lang['invalid_csrf_token']]);
        return;
    }
    
    // Validation
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => $lang['email_required']]);
        return;
    }
    
    if (!is_valid_email($email)) {
        echo json_encode(['success' => false, 'message' => $lang['email_invalid']]);
        return;
    }
    
    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => $lang['password_required']]);
        return;
    }
    
    if (!is_valid_password($password)) {
        echo json_encode(['success' => false, 'message' => $lang['password_min_length']]);
        return;
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => $lang['passwords_not_match']]);
        return;
    }
    
    if (!$terms_agreed) {
        echo json_encode(['success' => false, 'message' => $lang['terms_required']]);
        return;
    }
    
    // Check if email already exists
    global $db;
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => $lang['email_already_exists']]);
        return;
    }
    
    // Send verification email
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
            echo json_encode([
                'success' => true,
                'message' => $lang['verification_code_sent'],
                'redirect' => 'verify.php?type=registration'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка отправки email: ' . $email_result['message']]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка регистрации: ' . $e->getMessage()]);
    }
}

function handleForgotPassword($input, $lang) {
    // Implementation for forgot password
    echo json_encode(['success' => false, 'message' => 'Not implemented yet']);
}

function handleResetPassword($input, $lang) {
    // Implementation for reset password
    echo json_encode(['success' => false, 'message' => 'Not implemented yet']);
}

function handleVerifyEmail($input, $lang) {
    // Implementation for email verification
    echo json_encode(['success' => false, 'message' => 'Not implemented yet']);
}
?>