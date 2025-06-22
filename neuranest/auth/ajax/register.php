<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/mail_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$terms_agreed = isset($_POST['terms_agreed']);
$csrf_token = $_POST['csrf_token'] ?? '';

// CSRF validation
if (!verify_csrf_token($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Недействительный токен безопасности']);
    exit;
}

// Validation
$errors = [];

if (empty($email)) {
    $errors[] = 'Email обязателен';
} elseif (!is_valid_email($email)) {
    $errors[] = 'Неверный форм��т email';
}

if (empty($password)) {
    $errors[] = 'Пароль обязателен';
} elseif (!is_valid_password($password)) {
    $errors[] = 'Пароль должен содержать минимум 8 символов';
}

if ($password !== $confirm_password) {
    $errors[] = 'Пароли не совпадают';
}

if (!$terms_agreed) {
    $errors[] = 'Необходимо согласиться с условиями использования';
}

// Check if email already exists
if (empty($errors)) {
    global $db;
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Пользователь с таким email уже существует';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

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
        echo json_encode(['success' => true, 'message' => 'Код подтверждения отправлен на ваш email']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка отправки email: ' . $email_result['message']]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка регистрации: ' . $e->getMessage()]);
}
?>