<?php
require_once __DIR__ . '/engine_core.php';


// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'neuranest');
define('DB_USER', 'root');
define('DB_PASS', '');

// Email configuration
define('SMTP_HOST', 'smtp.yandex.ru');
define('SMTP_USER', 'neuranest@yandex.com');
define('SMTP_PASS', 'dpciontyiyrjhrhk');
define('SMTP_PORT', 587);

// Site configuration
define('SITE_URL', 'http://localhost/neuranest');
define('SITE_NAME', 'NeuraNest');

// Security
define('SESSION_LIFETIME', 3600 * 24 * 30); // 30 days
define('VERIFICATION_CODE_LIFETIME', 900); // 15 minutes

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
ini_set('session.cookie_samesite', 'Lax');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
