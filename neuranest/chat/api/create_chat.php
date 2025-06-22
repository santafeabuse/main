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
$title = $input['title'] ?? 'New Chat';
$custom_instructions_id = isset($input['custom_instructions_id']) ? (int)$input['custom_instructions_id'] : null;

$user = get_logged_in_user();
global $db;

try {
    // If custom_instructions_id is provided, verify it exists and belongs to the user
    if ($custom_instructions_id) {
        $stmt = $db->prepare("SELECT id FROM chat_instructions WHERE id = ? AND user_id = ?");
        $stmt->execute([$custom_instructions_id, $user['id']]);
        if (!$stmt->fetch()) {
            $custom_instructions_id = null; // Reset if not found or not belonging to user
        }
    }
    
    // Create new chat session
    $stmt = $db->prepare("INSERT INTO chat_sessions (user_id, title, custom_instructions_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->execute([$user['id'], $title, $custom_instructions_id]);
    $session_id = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'session_id' => $session_id,
        'title' => $title
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>