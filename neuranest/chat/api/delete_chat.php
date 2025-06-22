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
    // Start transaction
    $db->beginTransaction();
    
    // Verify session belongs to user
    $stmt = $db->prepare("SELECT id FROM chat_sessions WHERE id = ? AND user_id = ?");
    $stmt->execute([$session_id, $user['id']]);
    
    if (!$stmt->fetch()) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Chat session not found']);
        exit;
    }
    
    // Delete all messages in this session (will be deleted automatically due to foreign key cascade)
    $stmt = $db->prepare("DELETE FROM messages WHERE session_id = ?");
    $stmt->execute([$session_id]);
    
    // Delete the chat session itself
    $stmt = $db->prepare("DELETE FROM chat_sessions WHERE id = ?");
    $stmt->execute([$session_id]);
    
    // Commit transaction
    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Chat deleted successfully']);
    
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>