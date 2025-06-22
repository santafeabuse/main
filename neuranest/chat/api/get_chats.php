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

$user = get_logged_in_user();
global $db;

try {
    // Get user's chat sessions with latest message preview
    $stmt = $db->prepare("
        SELECT 
            cs.id,
            cs.title,
            cs.created_at,
            cs.updated_at,
            (SELECT content FROM messages WHERE session_id = cs.id ORDER BY created_at DESC LIMIT 1) as preview
        FROM chat_sessions cs
        WHERE cs.user_id = ?
        ORDER BY cs.updated_at DESC
        LIMIT 50
    ");
    
    $stmt->execute([$user['id']]);
    $chats = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'chats' => $chats
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>