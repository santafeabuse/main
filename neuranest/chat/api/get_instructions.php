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
    // Get user's custom instructions
    $stmt = $db->prepare("SELECT id, title, instructions, created_at, updated_at FROM chat_instructions WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $instructions = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'instructions' => $instructions
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>