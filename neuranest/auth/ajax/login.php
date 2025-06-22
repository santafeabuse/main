<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);
$csrf_token = $_POST['csrf_token'] ?? '';

// CSRF validation
if (!verify_csrf_token($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Недействительный токен безопасности']);
    exit;
}

// Validation
if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email обязателен']);
    exit;
}

if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Пароль обязателен']);
    exit;
}

try {
    global $db;
    
    // Find user by email
    $stmt = $db->prepare("SELECT id, email, password, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !verify_password($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Неверный email или пароль']);
        exit;
    }
    
    if (!$user['is_verified']) {
        echo json_encode(['success' => false, 'message' => 'Аккаунт не подтвержден. Проверьте email.']);
        exit;
    }
    
    // Login user
    login_user($user['id']);
    
    // Handle remember me
    if ($remember_me) {
        // Set remember me cookie (implement if needed)
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Успешный вход',
        'redirect' => '../chat/chat.php'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка входа: ' . $e->getMessage()]);
}
?>