<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

echo "Testing database connection...\n";

try {
    // Test basic connection
    if ($db) {
        echo "✓ Database connection established\n";
        
        // Test query
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $result = $stmt->fetch();
        
        echo "✓ Database query successful\n";
        echo "✓ Users table has {$result['count']} records\n";
        
        // Test session
        echo "✓ Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "\n";
        echo "✓ Session ID: " . session_id() . "\n";
        
        // Test CSRF token generation
        require_once 'includes/functions.php';
        $token = generate_csrf_token();
        echo "✓ CSRF token generated: " . substr($token, 0, 10) . "...\n";
        
        // Test token verification
        $verify_result = verify_csrf_token($token);
        echo "✓ CSRF token verification: " . ($verify_result ? 'Success' : 'Failed') . "\n";
        
    } else {
        echo "✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "✗ Trace: " . $e->getTraceAsString() . "\n";
}
?>