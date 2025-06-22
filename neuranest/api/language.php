<?php
// Suppress warnings to ensure clean JSON output
error_reporting(E_ALL & ~E_WARNING);
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Handle language switching
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $lang = $input['lang'] ?? '';
    
    if (!in_array($lang, ['ru', 'en'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid language']);
        exit;
    }
    
    // Set language in session
    set_language($lang);
    
    // Load language array
    $lang_array = load_language($lang);
    
    echo json_encode([
        'success' => true,
        'language' => $lang,
        'translations' => $lang_array
    ]);
    exit;
}

// Handle getting current language
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $current_lang = get_current_language();
    $lang_array = load_language($current_lang);
    
    echo json_encode([
        'success' => true,
        'language' => $current_lang,
        'translations' => $lang_array
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>
