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

$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Session ID required']);
    exit;
}

$user = get_logged_in_user();
global $db;

try {
    // Verify session belongs to user and get chat info
    $stmt = $db->prepare("SELECT title FROM chat_sessions WHERE id = ? AND user_id = ?");
    $stmt->execute([$session_id, $user['id']]);
    $chat = $stmt->fetch();
    
    if (!$chat) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Chat session not found']);
        exit;
    }
    
    // Get messages for this session
    $stmt = $db->prepare("
        SELECT id, role, content, created_at 
        FROM messages 
        WHERE session_id = ? 
        ORDER BY created_at ASC
    ");
    
    $stmt->execute([$session_id]);
    $messages = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'chat_title' => $chat['title']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>