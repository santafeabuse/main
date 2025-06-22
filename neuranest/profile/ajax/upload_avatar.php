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

// CSRF validation
if (!verify_csrf_token($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Недействительный токен безопасности']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Файл не загружен']);
    exit;
}

try {
    $upload_result = upload_avatar($_FILES['avatar'], $user['id']);
    
    if ($upload_result['success']) {
        global $db;
        $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$upload_result['filename'], $user['id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Аватар обновлен',
            'filename' => $upload_result['filename']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $upload_result['message']]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка загрузки: ' . $e->getMessage()]);
}
?>