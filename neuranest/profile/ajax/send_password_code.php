<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/mail_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user = get_logged_in_user();
$csrf_token = $_POST['csrf_token'] ?? '';
$current_password = $_POST['current_password'] ?? '';

// CSRF validation
if (!verify_csrf_token($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Недействительный токен безопасности']);
    exit;
}

// Validation
if (empty($current_password)) {
    echo json_encode(['success' => false, 'message' => 'Текущий пароль обязателен']);
    exit;
}

if (!verify_password($current_password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Неверный текущий пароль']);
    exit;
}

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
        echo json_encode(['success' => true, 'message' => 'Код отправлен на ваш email']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка отправки email: ' . $email_result['message']]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>