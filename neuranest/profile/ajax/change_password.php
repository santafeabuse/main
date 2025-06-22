<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

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
$code = sanitize_input($_POST['code'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// CSRF validation
if (!verify_csrf_token($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Недействительный токен безопасности']);
    exit;
}

// Validation
if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Код подтверждения обязателен']);
    exit;
}

if (!preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['success' => false, 'message' => 'Неверный код подтверждения']);
    exit;
}

if (empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Новый пароль обязателен']);
    exit;
}

if (!is_valid_password($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Пароль должен содержать минимум 8 символов']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Пароли не совпадают']);
    exit;
}

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
        
        echo json_encode(['success' => true, 'message' => 'Пароль успешно изменен']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Неверный или истекший код подтверждения']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>