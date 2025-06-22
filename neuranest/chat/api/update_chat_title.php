<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$session_id = $input['session_id'] ?? null;
$title = trim($input['title'] ?? '');

if (!$session_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Session ID required']);
    exit;
}

if (empty($title)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title cannot be empty']);
    exit;
}

if (strlen($title) > 50) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title too long']);
    exit;
}

$user = get_logged_in_user();
global $db;

try {
    // Verify session belongs to user
    $stmt = $db->prepare("SELECT id FROM chat_sessions WHERE id = ? AND user_id = ?");
    $stmt->execute([$session_id, $user['id']]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Chat session not found']);
        exit;
    }
    
    // Update chat title
    $stmt = $db->prepare("UPDATE chat_sessions SET title = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$title, $session_id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>