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

// Validate input
if (!isset($input['session_id']) || !is_numeric($input['session_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid session ID is required']);
    exit;
}

// Custom instruction ID can be null to remove the instruction
$session_id = (int)$input['session_id'];
$custom_instructions_id = isset($input['custom_instructions_id']) ? (int)$input['custom_instructions_id'] : null;

$user = get_logged_in_user();
global $db;

try {
    // Check if chat session exists and belongs to the user
    $stmt = $db->prepare("SELECT id FROM chat_sessions WHERE id = ? AND user_id = ?");
    $stmt->execute([$session_id, $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Chat session not found or access denied']);
        exit;
    }
    
    // If custom_instructions_id is provided, verify it exists and belongs to the user
    if ($custom_instructions_id) {
        $stmt = $db->prepare("SELECT id FROM chat_instructions WHERE id = ? AND user_id = ?");
        $stmt->execute([$custom_instructions_id, $user['id']]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Instruction not found or access denied']);
            exit;
        }
    }
    
    // Update chat session with new custom instruction
    $stmt = $db->prepare("UPDATE chat_sessions SET custom_instructions_id = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$custom_instructions_id, $session_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Chat instruction updated successfully',
        'session_id' => $session_id,
        'custom_instructions_id' => $custom_instructions_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>