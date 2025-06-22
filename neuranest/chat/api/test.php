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
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}

// Clean any output
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Test database connection
global $db;

try {
    if (!$db) {
        throw new Exception('Database connection is null');
    }
    
    // Test query
    $stmt = $db->prepare("SELECT 1 as test");
    $stmt->execute();
    $result = $stmt->fetch();
    
    // Test user authentication
    $user = null;
    if (is_logged_in()) {
        $user = get_logged_in_user();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'API is working',
        'database' => 'Connected',
        'test_query' => $result,
        'user_logged_in' => is_logged_in(),
        'user_id' => $user ? $user['id'] : null,
        'mistral_api_key' => defined('MISTRAL_API_KEY') ? 'Configured' : 'Not configured',
        'php_version' => PHP_VERSION,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>