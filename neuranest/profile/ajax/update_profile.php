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
$display_name = sanitize_input($_POST['display_name'] ?? '');

// CSRF validation
if (!verify_csrf_token($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Недействительный токен безопасности']);
    exit;
}

// Validation
if (strlen($display_name) > 100) {
    echo json_encode(['success' => false, 'message' => 'Имя слишком длинное']);
    exit;
}

try {
    global $db;
    $stmt = $db->prepare("UPDATE users SET display_name = ? WHERE id = ?");
    $stmt->execute([$display_name, $user['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Профиль обновлен',
        'display_name' => $display_name
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка обновления: ' . $e->getMessage()]);
}
?>