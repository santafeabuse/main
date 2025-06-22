<?php
require_once 'database.php';

// Security functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (empty($token) || !isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function generate_verification_code() {
    return sprintf('%06d', mt_rand(100000, 999999));
}

// User authentication functions
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit;
    }
}

function get_logged_in_user() {
    global $db;
    
    if (!is_logged_in()) {
        return null;
    }
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function login_user($user_id) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['login_time'] = time();
    
    // Update last login
    global $db;
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user_id]);
}

function logout_user() {
    session_destroy();
    session_start();
}

// Email validation
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Password validation
function is_valid_password($password) {
    return strlen($password) >= 8;
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Language functions
function get_current_language() {
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }
    return 'ru'; // Default language
}

function set_language($lang) {
    if (in_array($lang, ['ru', 'en'])) {
        $_SESSION['language'] = $lang;
    }
}

function load_language($lang = null) {
    if ($lang === null) {
        $lang = get_current_language();
    }
    
    $lang_file = __DIR__ . "/../languages/{$lang}.php";
    if (file_exists($lang_file)) {
        include $lang_file;
        return $lang_array;
    }
    
    // Fallback to Russian
    include __DIR__ . "/../languages/ru.php";
    return $lang_array;
}

// Utility functions
function redirect($url) {
    header("Location: $url");
    exit;
}

function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function format_date($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'только что';
    if ($time < 3600) return floor($time/60) . ' мин назад';
    if ($time < 86400) return floor($time/3600) . ' ч назад';
    if ($time < 2592000) return floor($time/86400) . ' дн назад';
    
    return date('d.m.Y', strtotime($datetime));
}

// File upload functions
function upload_avatar($file, $user_id) {
    $upload_dir = __DIR__ . '/../assets/images/avatars/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Недопустимый тип файла'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Файл слишком большой'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Ошибка загрузки файла'];
}
?>