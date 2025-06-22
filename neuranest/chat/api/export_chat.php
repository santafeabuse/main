<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering
ob_start();

try {
    require_once '../../includes/config.php';
    require_once '../../includes/functions.php';
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo 'Configuration error: ' . $e->getMessage();
    exit;
}

// Clean any output
ob_clean();

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo 'Unauthorized - Please log in';
    exit;
}

$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    http_response_code(400);
    echo 'Session ID required';
    exit;
}

$user = get_logged_in_user();

if (!$user) {
    http_response_code(401);
    echo 'User not found';
    exit;
}

global $db;

if (!$db) {
    http_response_code(500);
    echo 'Database connection failed';
    exit;
}

try {
    // Verify session belongs to user and get chat info
    $stmt = $db->prepare("SELECT title, created_at FROM chat_sessions WHERE id = ? AND user_id = ?");
    $stmt->execute([$session_id, $user['id']]);
    $chat = $stmt->fetch();
    
    if (!$chat) {
        http_response_code(404);
        echo 'Chat session not found';
        exit;
    }
    
    // Get messages for this session
    $stmt = $db->prepare("
        SELECT role, content, created_at 
        FROM messages 
        WHERE session_id = ? 
        ORDER BY created_at ASC
    ");
    
    $stmt->execute([$session_id]);
    $messages = $stmt->fetchAll();
    
    // Generate export content
    $export_content = "# " . $chat['title'] . "\n";
    $export_content .= "**Created:** " . date('Y-m-d H:i:s', strtotime($chat['created_at'])) . "\n";
    $export_content .= "**Exported:** " . date('Y-m-d H:i:s') . "\n";
    $export_content .= "**Messages:** " . count($messages) . "\n\n";
    $export_content .= "---\n\n";
    
    if (empty($messages)) {
        $export_content .= "*This chat has no messages yet.*\n";
    } else {
        foreach ($messages as $message) {
            $role = $message['role'] === 'user' ? 'You' : 'AI Assistant';
            $timestamp = date('H:i:s', strtotime($message['created_at']));
            
            $export_content .= "**{$role}** ({$timestamp}):\n";
            $export_content .= $message['content'] . "\n\n";
        }
    }
    
    $export_content .= "---\n";
    $export_content .= "*Exported from NeuraNest - " . date('Y-m-d H:i:s') . "*\n";
    
    // Set headers for file download
    $filename = 'neuranest_chat_' . $session_id . '_' . date('Y-m-d_H-i-s') . '.md';
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($export_content));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    echo $export_content;
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}
?>