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
if (!isset($input['instruction_id']) || !is_numeric($input['instruction_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid instruction ID is required']);
    exit;
}

$instruction_id = (int)$input['instruction_id'];
$user = get_logged_in_user();
global $db;

try {
    // Start transaction
    $db->beginTransaction();
    
    // Check if instruction exists and belongs to the user
    $stmt = $db->prepare("SELECT id FROM chat_instructions WHERE id = ? AND user_id = ?");
    $stmt->execute([$instruction_id, $user['id']]);
    if (!$stmt->fetch()) {
        $db->rollBack();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Instruction not found or access denied']);
        exit;
    }
    
    // Remove instruction reference from any chat sessions
    $stmt = $db->prepare("UPDATE chat_sessions SET custom_instructions_id = NULL WHERE custom_instructions_id = ?");
    $stmt->execute([$instruction_id]);
    
    // Delete the instruction
    $stmt = $db->prepare("DELETE FROM chat_instructions WHERE id = ?");
    $stmt->execute([$instruction_id]);
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Instruction deleted successfully'
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>