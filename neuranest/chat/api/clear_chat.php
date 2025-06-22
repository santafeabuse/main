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

if (!$session_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Session ID required']);
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
    
    // This endpoint is deprecated. Use delete_chat.php instead.
    // For backward compatibility, we'll redirect to delete functionality
    
    // Delete all messages in this session
    $stmt = $db->prepare("DELETE FROM messages WHERE session_id = ?");
    $stmt->execute([$session_id]);
    
    // Delete the chat session itself to prevent empty duplicates
    $stmt = $db->prepare("DELETE FROM chat_sessions WHERE id = ?");
    $stmt->execute([$session_id]);
    
    echo json_encode(['success' => true, 'message' => 'Chat deleted completely']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>