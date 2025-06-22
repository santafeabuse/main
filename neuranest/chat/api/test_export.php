<?php
// Simple test file to check if export functionality works
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo "Not logged in";
    exit;
}

$user = get_logged_in_user();
echo "User ID: " . $user['id'] . "<br>";
echo "Email: " . $user['email'] . "<br>";

global $db;
if (!$db) {
    echo "Database connection failed<br>";
    exit;
}

// Get user's chats
$stmt = $db->prepare("SELECT id, title FROM chat_sessions WHERE user_id = ? ORDER BY updated_at DESC LIMIT 5");
$stmt->execute([$user['id']]);
$chats = $stmt->fetchAll();

echo "<h3>Your chats:</h3>";
foreach ($chats as $chat) {
    echo "Chat ID: " . $chat['id'] . " - Title: " . $chat['title'] . "<br>";
    echo "<a href='export_chat.php?session_id=" . $chat['id'] . "' target='_blank'>Export this chat</a><br><br>";
}

if (empty($chats)) {
    echo "No chats found. Create a chat first.";
}
?>