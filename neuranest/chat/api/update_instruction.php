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

if (!isset($input['title']) || trim($input['title']) === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if (!isset($input['instructions']) || trim($input['instructions']) === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Instructions content is required']);
    exit;
}

$instruction_id = (int)$input['instruction_id'];
$title = trim($input['title']);
$instructions = trim($input['instructions']);
$user = get_logged_in_user();
global $db;

try {
    // Check if instruction exists and belongs to the user
    $stmt = $db->prepare("SELECT id FROM chat_instructions WHERE id = ? AND user_id = ?");
    $stmt->execute([$instruction_id, $user['id']]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Instruction not found or access denied']);
        exit;
    }
    
    // Update instruction
    $stmt = $db->prepare("UPDATE chat_instructions SET title = ?, instructions = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$title, $instructions, $instruction_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Instruction updated successfully',
        'instruction_id' => $instruction_id,
        'title' => $title
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>