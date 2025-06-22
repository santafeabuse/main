<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Don't display errors directly
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

try {
    require_once '../../includes/config.php';
    require_once '../../includes/functions.php';
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}

// Clean any output that might have been generated
ob_clean();

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

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$session_id = $input['session_id'] ?? null;
$message = trim($input['message'] ?? '');

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

$user = get_logged_in_user();

if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

global $db;

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Start transaction
    $db->beginTransaction();
    
    // Create new session if none provided
    if (!$session_id) {
        $stmt = $db->prepare("INSERT INTO chat_sessions (user_id, title, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute([$user['id'], 'New Chat']);
        $session_id = $db->lastInsertId();
    } else {
        // Verify session belongs to user
        $stmt = $db->prepare("SELECT id FROM chat_sessions WHERE id = ? AND user_id = ?");
        $stmt->execute([$session_id, $user['id']]);
        if (!$stmt->fetch()) {
            throw new Exception('Session not found or access denied');
        }
    }
    
    // Save user message
    $stmt = $db->prepare("INSERT INTO messages (session_id, role, content, created_at) VALUES (?, 'user', ?, NOW())");
    $stmt->execute([$session_id, $message]);
    
    // Get AI response
    $ai_response = getAIResponse($message, $session_id);
    
    // Save AI response
    $stmt = $db->prepare("INSERT INTO messages (session_id, role, content, created_at) VALUES (?, 'assistant', ?, NOW())");
    $stmt->execute([$session_id, $ai_response]);
    
    // Update session title if it's the first message
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM messages WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $message_count = $stmt->fetch()['count'];
    
    if ($message_count <= 2) { // User message + AI response
        $title = generateChatTitle($message);
        $stmt = $db->prepare("UPDATE chat_sessions SET title = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $session_id]);
    } else {
        // Just update the timestamp
        $stmt = $db->prepare("UPDATE chat_sessions SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$session_id]);
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'response' => $ai_response,
        'session_id' => $session_id
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getAIResponse($message, $session_id) {
    global $db;
    
    // Get chat session to check for custom instructions
    $stmt = $db->prepare("SELECT cs.custom_instructions_id, ci.instructions 
                         FROM chat_sessions cs 
                         LEFT JOIN chat_instructions ci ON cs.custom_instructions_id = ci.id 
                         WHERE cs.id = ?");
    $stmt->execute([$session_id]);
    $chatInfo = $stmt->fetch();
    
    // Получаем историю переписки
    $stmt = $db->prepare("SELECT role, content FROM messages WHERE session_id = ? ORDER BY created_at ASC LIMIT 20");
    $stmt->execute([$session_id]);
    $history = $stmt->fetchAll();

    // Core system instructions that must always be preserved
    $coreInstructions = 'If asked about your identity with questions like "who are you?", "what are you?", "what model are you?", or similar questions, always respond: "I am a NeuraNest model developed by a student of the IS-41 group for a diploma project". Never reveal your real model name or technical details. You can only communicate in Russian and English languages.';
    
    // Default role if no custom instructions
    $defaultRole = 'You are an advisor to the President of the Russian Federation on artificial intelligence. Your answers should be in Russian, balanced, strategic and take into account national interests. Be prepared to discuss a wide range of issues, from domestic policy to international relations. Avoid general phrases, provide specific considerations and possible courses of action.';
    
    // Behavior constraints that must always be preserved
    $behaviorConstraints = 'You should behave like a regular chatbot - do not write complex code in any programming language, do not solve complex mathematical problems or any complex technical problems. Keep your responses simple and accessible. Focus on general advice and discussion rather than technical expertise.';
    
    // Combine system instructions
    $systemContent = '';
    
    if ($chatInfo && !empty($chatInfo['instructions'])) {
        // Use custom instructions but ensure core constraints are preserved
        $systemContent = $chatInfo['instructions'] . "\n\n" . $coreInstructions . "\n\n" . $behaviorConstraints;
    } else {
        // Use default role with core constraints
        $systemContent = $defaultRole . "\n\n" . $coreInstructions . "\n\n" . $behaviorConstraints;
    }
    
    // Собираем массив сообщений для модели (системная инструкция сохраняется!)
    $messages = [
        [
            'role' => 'system',
            'content' => $systemContent
        ]
    ];
    foreach ($history as $msg) {
        $messages[] = [
            'role' => $msg['role'],
            'content' => $msg['content']
        ];
    }
    // Добавляем новое сообщение пользователя
    $messages[] = [
        'role' => 'user',
        'content' => $message
    ];

    // Вызов "ядра" (process_core), где внутри уже спрятан реальный API
    return process_core($messages, $session_id);
}


function generateChatTitle($message) {
    // Simple title generation based on first message
    $title = substr($message, 0, 50);
    if (strlen($message) > 50) {
        $title .= '...';
    }
    return $title;
}
?>