<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Require login
require_login();

// Handle language switching
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en'])) {
    set_language($_GET['lang']);
    redirect($_SERVER['PHP_SELF']);
}

$lang = load_language();
$user = get_logged_in_user();

// Get current chat session
$current_session_id = $_GET['session'] ?? null;
$current_session = null;

if ($current_session_id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM chat_sessions WHERE id = ? AND user_id = ?");
    $stmt->execute([$current_session_id, $user['id']]);
    $current_session = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['chat']; ?> - <?php echo $lang['site_name']; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Apply theme immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('neuranest-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            background: var(--gray-50);
        }
        
        .chat-container {
            display: flex;
            height: 100vh;
        }
        
        /* Sidebar */
        .chat-sidebar {
            width: 300px;
            background: var(--white);
            border-right: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .sidebar-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }
        
        .sidebar-title {
            font-size: var(--text-lg);
            font-weight: 600;
            color: var(--gray-800);
            margin: 0 0 var(--spacing-md) 0;
        }
        
        .new-chat-btn {
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-md);
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .new-chat-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .chat-list {
            flex: 1;
            overflow-y: auto;
            padding: var(--spacing-md);
        }
        
        .chat-item {
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition-fast);
            margin-bottom: var(--spacing-sm);
            border: 1px solid transparent;
        }
        
        .chat-item:hover {
            background: var(--gray-50);
            border-color: var(--gray-200);
        }
        
        .chat-item.active {
            background: rgba(102, 126, 234, 0.1);
            border-color: var(--primary-color);
        }
        
                
        .chat-item-preview {
            color: var(--gray-600);
            font-size: var(--text-xs);
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }
        
        .chat-item-title {
            font-weight: 500;
            color: var(--gray-800);
            margin-bottom: var(--spacing-xs);
            font-size: var(--text-sm);
            cursor: pointer;
            position: relative;
        }
        
        .chat-item-title:hover .edit-title-btn {
            opacity: 1;
        }
        
        .edit-title-btn {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 2px;
            border-radius: 3px;
            opacity: 0;
            transition: var(--transition-fast);
            font-size: 12px;
            color: var(--gray-400);
            cursor: pointer;
            padding: 2px;
            border-radius: 3px;
            opacity: 0;
            transition: var(--transition-fast);
            font-size: 12px;
        }
        
        .edit-title-btn:hover {
            color: var(--primary-color);
            background: var(--gray-100);
        }
        
        .title-input {
            width: 100%;
            background: transparent;
            border: 1px solid var(--primary-color);
            border-radius: 4px;
            padding: 2px 4px;
            font-size: var(--text-sm);
            font-weight: 500;
            color: var(--gray-800);
        }
        
        .title-input:focus {
            outline: none;
        }
        
        .chat-item-date {
            color: var(--gray-400);
            font-size: var(--text-xs);
            margin-top: var(--spacing-xs);
        }
        
        .sidebar-footer {
            padding: var(--spacing-lg);
            border-top: 1px solid var(--gray-200);
            background: var(--gray-50);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            flex-wrap: nowrap;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: var(--text-sm);
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            display: block;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-weight: 500;
            color: var(--gray-800);
            font-size: var(--text-sm);
        }
        
        .user-email {
            color: var(--gray-600);
            font-size: var(--text-xs);
        }
        
        .logout-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 2px;
            color: var(--gray-400);
            transition: var(--transition-fast);
            color: var(--gray-400);
            cursor: pointer;
            padding: 2px; 
            border-radius: var(--radius-sm);
            transition: var(--transition-fast);
        }
        
        .logout-btn:hover {
            color: var(--error-color);
            background: var(--gray-100);
        }
        
        /* Adjust icon color based on theme */
        [data-theme="light"] .logout-btn {
            color: var(--gray-700); /* Darker for light theme */
        }
        
        [data-theme="light"] .logout-btn:hover {
            color: var(--gray-900);
            background: var(--gray-200);
        }
        
        [data-theme="dark"] .logout-btn {
            color: var(--gray-300); /* Lighter for dark theme */
        }
        
        [data-theme="dark"] .logout-btn:hover {
            color: var(--gray-100);
            background: var(--gray-700);
        }
        
        /* Main chat area */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .chat-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--gray-200);
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .chat-title {
            font-size: var(--text-lg);
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
        }
        
        .chat-actions {
            display: flex;
            gap: var(--spacing-sm);
        }
        
        .chat-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: var(--gray-400);
            transition: var(--transition-fast);
            color: var(--gray-400);
            cursor: pointer;
            padding: var(--spacing-xs);
            border-radius: var(--radius-sm);
            transition: var(--transition-fast);
            font-size: 1.2rem; /* Increase icon size */
        }
        
        .chat-action-btn:hover {
            color: var(--gray-600);
            background: var(--gray-100);
        }
        
        /* Adjust icon color based on theme */
        [data-theme="light"] .chat-action-btn {
            color: var(--gray-700); /* Darker for light theme */
        }
        
        [data-theme="light"] .chat-action-btn:hover {
            color: var(--gray-900);
            background: var(--gray-200);
        }
        
        [data-theme="dark"] .chat-action-btn {
            color: var(--gray-300); /* Lighter for dark theme */
        }
        
        [data-theme="dark"] .chat-action-btn:hover {
            color: var(--gray-100);
            background: var(--gray-700);
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: var(--spacing-lg);
            background: var(--gray-50);
        }
        
        .message {
            margin-bottom: var(--spacing-lg);
            display: flex;
            gap: var(--spacing-md);
        }
        
        .message.user {
            flex-direction: row-reverse;
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: var(--text-sm);
            flex-shrink: 0;
            overflow: hidden;
        }
        
        .message-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            display: block;
        }
        
        .message.user .message-avatar {
            background: var(--gradient-primary);
            color: white;
        }
        
        .message.assistant .message-avatar {
            background: var(--gray-200);
            color: var(--gray-700);
        }
        
        .message-content {
            max-width: 70%;
            background: var(--white);
            padding: var(--spacing-md);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            position: relative;
        }
        
        .message.user .message-content {
            background: var(--gradient-primary);
            color: white;
        }
        
        .message-text {
            line-height: 1.6;
            margin: 0;
        }
        
        .message-time {
            font-size: var(--text-xs);
            color: var(--gray-400);
            margin-top: var(--spacing-xs);
        }
        
        .message.user .message-time {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .typing-dots {
            display: flex;
            gap: 4px;
            padding: var(--spacing-md);
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gray-400);
            animation: typing 1.4s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes typing {
            0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }
        
        .chat-input-container {
            padding: var(--spacing-lg);
            background: var(--white);
            border-top: 1px solid var(--gray-200);
        }
        
        .chat-input-form {
            display: flex;
            gap: var(--spacing-md);
            align-items: flex-end;
        }
        
        .chat-input {
            flex: 1;
            min-height: 44px;
            max-height: 120px;
            padding: var(--spacing-md);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-lg);
            resize: none;
            font-family: inherit;
            font-size: var(--text-base);
            line-height: 1.5;
            transition: var(--transition-fast);
        }
        
        .chat-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .send-btn {
            width: 44px;
            height: 44px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-fast);
            flex-shrink: 0;
        }
        
        .send-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .send-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            padding: var(--spacing-2xl);
        }
        
        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: var(--spacing-lg);
            color: white;
            font-size: 2rem;
        }
        
        .empty-state-title {
            font-size: var(--text-xl);
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: var(--spacing-md);
        }
        
        .empty-state-text {
            color: var(--gray-600);
            max-width: 400px;
            line-height: 1.6;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .chat-sidebar {
                position: fixed;
                left: -300px;
                top: 0;
                height: 100vh;
                z-index: 1000;
                transition: var(--transition-normal);
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            }
            
            .chat-sidebar.open {
                left: 0;
            }
            
            .chat-main {
                width: 100%;
            }
            
            .mobile-menu-btn {
                display: block;
                width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: var(--spacing-lg);
                border: none;
                color: var(--gray-600);
                cursor: pointer;
                padding: var(--spacing-xs);
                border-radius: var(--radius-sm);
            }
            
            .message-content {
                max-width: 85%;
            }
            
            /* Mobile overlay */
            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: var(--transition-normal);
            }
            
            .mobile-overlay.active {
                opacity: 1;
                visibility: visible;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-menu-btn {
                display: none;
            }
        }
        
        /* Markdown styling for AI responses */
        .message-content pre {
            background: rgba(0, 0, 0, 0.1);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            overflow-x: auto;
            margin: var(--spacing-md) 0;
        }
        
        .message-content code {
            background: rgba(0, 0, 0, 0.1);
            padding: 2px 4px;
            border-radius: 3px;
            font-family: var(--font-family-mono);
            font-size: 0.9em;
        }
        
        .message-content pre code {
            width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: var(--spacing-lg);
            padding: 0;
        }
        
        .message.user .message-content pre {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .message.user .message-content code {
            background: rgba(255, 255, 255, 0.2);
        }
        
        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            min-width: 300px;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
        }
        
        .notification-content {
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .notification-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .notification-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .notification-warning {
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
        }
        
        .notification-info {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            color: #1e40af;
        }
        
        .notification-message {
            flex: 1;
            font-weight: 500;
        }
        
        .notification-close {
            width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: var(--spacing-lg);
            border: none;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.7;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .notification-close:hover {
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .notification {
                top: 10px;
                right: 10px;
                left: 10px;
                min-width: auto;
            }
        }
        .icon {
        width: 18px;
        height: 18px;
        object-fit: contain;
        vertical-align: middle;
    }
    /* Larger icons and column layout in user footer */
    .user-actions {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
    }
    .user-actions .icon {
        width: 22px;
        height: 22px;
    }
    /* Empty state AI image */
    .empty-state-icon {
        width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: var(--spacing-lg);
        padding: 0;
    }
    /* removed img rules */
/*
        width: 60%;
        height: 60%;
        object-fit: contain;
    }
        /* Empty state AI image */
    .empty-state-icon {
        width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: var(--spacing-lg);
        padding: 0;
    }
    /* removed img rules */
/*
        width: 60%;
        height: 60%;
        object-fit: contain;
    }
        /* Avatar container for messages */
        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .message-avatar img {
            width: 60%;
            height: 60%;
            object-fit: contain;
        }
        /* Empty-state AI icon image inside circle */
        .empty-state-icon img {
            width: 60%;
            height: 60%;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <!-- Mobile overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeSidebar()"></div>
    
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar" id="chatSidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title"><?php echo $lang['chat_history']; ?></h2>
                <button class="new-chat-btn" onclick="createNewChat()">
                    ➕ <?php echo $lang['new_chat']; ?>
                </button>
            </div>
            
            <div class="chat-list" id="chatList">
                <!-- Chat list will be loaded here -->
            </div>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php if ($user['avatar']): ?>
                            <img src="../assets/images/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                        <?php else: ?>
                            <?php echo strtoupper(substr($user['email'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($user['display_name'] ?: 'User'); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="user-actions" style="display: flex; flex-direction: row; gap: 6px; align-items: center;">
                        <button class="logout-btn" onclick="window.location.href='instructions.php'" title="<?php echo $lang['custom_instructions']; ?>">
                            <img src="../assets/images/icons/instructions.png" alt="<?php echo $lang['custom_instructions']; ?>" class="icon">
                        </button>
                        <button class="logout-btn" onclick="window.location.href='../profile/profile.php'" title="<?php echo $lang['my_profile']; ?>">
                            <img src="../assets/images/icons/user.png" alt="<?php echo $lang['my_profile']; ?>" class="icon">
                        </button>
                        <button class="logout-btn" onclick="logout()" title="<?php echo $lang['logout']; ?>">
                            <img src="../assets/images/icons/exit.png" alt="<?php echo $lang['logout']; ?>" class="icon">
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main chat area -->
        <div class="chat-main">
            <div class="chat-header">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
                <h1 class="chat-title" id="chatTitle"><?php echo $lang['chat_title']; ?></h1>
                <div class="chat-actions">
                    <button class="chat-action-btn" onclick="deleteChat()" title="<?php echo $lang['delete_chat']; ?>"><img src="../assets/images/icons/delete.png" alt="<?php echo $lang['delete_chat']; ?>" class="icon"></button>
                    <button class="chat-action-btn" onclick="exportChat()" title="<?php echo $lang['export_chat']; ?>"><img src="../assets/images/icons/export.png" alt="<?php echo $lang['export_chat']; ?>" class="icon"></button>
                </div>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <div class="empty-state" id="emptyState">
                    <div class="empty-state-icon"><img src="../assets/images/icons/ai.png" alt="AI"></div>
                    <h3 class="empty-state-title"><?php echo $lang['welcome_to_neuranest']; ?></h3>
                    <p class="empty-state-text"><?php echo $lang['hero_subtitle']; ?></p>
                </div>
            </div>
            
            <div class="chat-input-container">
                <form class="chat-input-form" id="chatForm" onsubmit="sendMessage(event)">
                    <textarea 
                        class="chat-input" 
                        id="messageInput" 
                        placeholder="<?php echo $lang['type_message']; ?>"
                        rows="1"
                        onkeydown="handleKeyDown(event)"
                    ></textarea>
                    <button type="submit" class="send-btn" id="sendBtn">
                        <span id="sendIcon">➤</span>
                        <div class="spinner" id="sendSpinner" style="display: none;"></div>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        let currentSessionId = <?php echo $current_session_id ? $current_session_id : 'null'; ?>;
// Localization strings from PHP
const L = {
    new_chat: '<?php echo addslashes($lang['new_chat']); ?>',
    no_messages_yet: '<?php echo addslashes($lang['no_messages_yet']); ?>',
    no_chats_yet: '<?php echo addslashes($lang['no_chats_yet']); ?>',
    today: '<?php echo addslashes($lang['today']); ?>',
    yesterday: '<?php echo addslashes($lang['yesterday']); ?>',
    days_ago: '<?php echo addslashes($lang['days_ago']); ?>'
};
        let isLoading = false;
        
        // Notification system
        function showNotification(message, type = 'info', duration = 5000) {
            // Remove existing notifications
            $('.notification').remove();
            
            const notification = $(`
                <div class="notification notification-${type}">
                    <div class="notification-content">
                        <span class="notification-icon">${getNotificationIcon(type)}</span>
                        <span class="notification-message">${message}</span>
                        <button class="notification-close" onclick="$(this).parent().parent().fadeOut()">&times;</button>
                    </div>
                </div>
            `);
            
            $('body').append(notification);
            notification.fadeIn();
            
            if (duration > 0) {
                setTimeout(() => {
                    notification.fadeOut(() => notification.remove());
                }, duration);
            }
        }
        
        function getNotificationIcon(type) {
            const icons = {
                'success': '✅',
                'error': '❌',
                'warning': '⚠️',
                'info': 'ℹ️'
            };
            return icons[type] || icons['info'];
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadChatList();
            if (currentSessionId) {
                loadChatMessages(currentSessionId);
            }
            autoResizeTextarea();
        });
        
        // Auto-resize textarea
        function autoResizeTextarea() {
            const textarea = document.getElementById('messageInput');
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
        }
        
        // Handle keyboard shortcuts
        function handleKeyDown(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage(event);
            }
        }
        
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('chatSidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        
        // Close sidebar when clicking outside
        function closeSidebar() {
            const sidebar = document.getElementById('chatSidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        }
        
        // Handle clicks outside sidebar to close it on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('chatSidebar');
                const menuBtn = document.querySelector('.mobile-menu-btn');
                
                // Check if sidebar is open and click is outside sidebar and menu button
                if (sidebar.classList.contains('open') && 
                    !sidebar.contains(event.target) && 
                    !menuBtn.contains(event.target)) {
                    closeSidebar();
                }
            }
        });
        
        // Handle Escape key to close sidebar on mobile
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && window.innerWidth <= 768) {
                const sidebar = document.getElementById('chatSidebar');
                if (sidebar.classList.contains('open')) {
                    closeSidebar();
                }
            }
        });
        
        // Load chat list
        async function loadChatList() {
            try {
                const response = await fetch('api/get_chats.php');
                const data = await response.json();
                
                const chatList = document.getElementById('chatList');
                
                if (data.success && data.chats.length > 0) {
                    chatList.innerHTML = data.chats.map(chat => {
                        const preview = chat.preview ? 
                            (chat.preview.length > 50 ? chat.preview.substring(0, 50) + '...' : chat.preview) : 
                            L.no_messages_yet;
                        
                        return `
                            <div class="chat-item ${chat.id == currentSessionId ? 'active' : ''}" onclick="loadChat(${chat.id})">
                                <div class="chat-item-title" id="title-${chat.id}">
                                    <span onclick="event.stopPropagation()">${escapeHtml(chat.title)}</span>
                                    <button class="edit-title-btn" onclick="event.stopPropagation(); editChatTitle(${chat.id}, '${escapeHtml(chat.title)}')"><img src="../assets/images/icons/edit.png" alt="Edit" class="icon"></button>
                                </div>
                                <div class="chat-item-preview">${escapeHtml(preview)}</div>
                                <div class="chat-item-date">${formatDate(chat.updated_at)}</div>
                            </div>
                        `;
                    }).join('');
                } else {
                    chatList.innerHTML = `<div style="text-align: center; color: var(--gray-500); padding: var(--spacing-lg);">${L.no_chats_yet}</div>`;
                }
            } catch (error) {
                console.error('Error loading chat list:', error);
            }
        }

        
        // Create new chat
        async function createNewChat() {
            try {
                const response = await fetch('api/create_chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        title: L.new_chat
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentSessionId = data.session_id;
                    window.history.pushState({}, '', `?session=${currentSessionId}`);
                    loadChatList();
                    clearMessages();
                    document.getElementById('chatTitle').textContent = L.new_chat;
                }
            } catch (error) {
                console.error('Error creating new chat:', error);
            }
        }
        
        // Expose function to global scope for inline onclick handler
        window.createNewChat = createNewChat;
        window.deleteChat = deleteChat;
        window.exportChat = exportChat;
        
        // Load specific chat
        function loadChat(sessionId) {
            currentSessionId = sessionId;
            window.history.pushState({}, '', `?session=${sessionId}`);
            loadChatMessages(sessionId);
            loadChatList(); // Refresh to update active state
            
            // Close sidebar on mobile
            if (window.innerWidth <= 768) {
                document.getElementById('chatSidebar').classList.remove('open');
            }
        }
        
        // Load chat messages
        async function loadChatMessages(sessionId) {
            try {
                const response = await fetch(`api/get_messages.php?session_id=${sessionId}`);
                const data = await response.json();
                
                const messagesContainer = document.getElementById('chatMessages');
                messagesContainer.innerHTML = '';
                if (data.success && data.messages.length > 0) {
                    messagesContainer.innerHTML = data.messages.map(message => createMessageHTML(message)).join('');
                    document.getElementById('chatTitle').textContent = data.chat_title || 'Chat';
                } else {
                    messagesContainer.innerHTML = `<div style="padding:20px; color: var(--gray-500); text-align:center;">No messages yet</div>`;
                }
                
                scrollToBottom();
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }
        
        // Send message
        async function sendMessage(event) {
            event.preventDefault();
            
            if (isLoading) return;
            
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message) return;
            
            // Clear input
            messageInput.value = '';
            messageInput.style.height = 'auto';
            
            // Hide placeholder if present
            const placeholder = document.getElementById('emptyState');
            if (placeholder) placeholder.style.display = 'none';
            
            // Add user message to UI
            addMessageToUI({
                role: 'user',
                content: message,
                created_at: new Date().toISOString()
            });
            
            // Show typing indicator
            showTypingIndicator();
            
            // Set loading state
            setLoadingState(true);
            
            try {
                const response = await fetch('api/send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        session_id: currentSessionId,
                        message: message
                    })
                });
                
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                // Get response text first to debug
                const responseText = await response.text();
                console.log('API Response:', responseText);
                
                // Try to parse JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response Text:', responseText);
                    throw new Error('Invalid JSON response from server');
                }
                
                if (data.success) {
                    // Update current session ID if it was created
                    if (data.session_id && !currentSessionId) {
                        currentSessionId = data.session_id;
                        window.history.pushState({}, '', `?session=${currentSessionId}`);
                    }
                    
                    // Remove typing indicator
                    hideTypingIndicator();
                    
                    // Add AI response to UI
                    addMessageToUI({
                        role: 'assistant',
                        content: data.response,
                        created_at: new Date().toISOString()
                    });
                    
                    // Update chat list
                    loadChatList();
                } else {
                    hideTypingIndicator();
                    alert('Error: ' + (data.message || 'Failed to send message'));
                }
            } catch (error) {
                hideTypingIndicator();
                console.error('Error sending message:', error);
                alert('Network error. Please try again.');
            } finally {
                setLoadingState(false);
            }
        }
        
        // Add message to UI
        function addMessageToUI(message) {
            const messagesContainer = document.getElementById('chatMessages');
            const messageHTML = createMessageHTML(message);
            messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
            scrollToBottom();
        }
        
        // Create message HTML
        function createMessageHTML(message) {
            const isUser = message.role === 'user';
            const time = formatTime(message.created_at);
            
            let avatar;
            if (isUser) {
                <?php if ($user['avatar']): ?>
                    avatar = '<img src="../assets/images/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">';
                <?php else: ?>
                    avatar = '<?php echo strtoupper(substr($user['email'], 0, 1)); ?>';
                <?php endif; ?>
            } else {
                avatar = '<img src="../assets/images/icons/ai.png" alt="AI">';
            }
            
            return `
                <div class="message ${message.role}">
                    <div class="message-avatar">${avatar}</div>
                    <div class="message-content">
                        <div class="message-text">${formatMessageContent(message.content)}</div>
                        <div class="message-time">${time}</div>
                    </div>
                </div>
            `;
        }
        
        // Format message content (basic markdown support)
        function formatMessageContent(content) {
            return escapeHtml(content)
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`(.*?)`/g, '<code>$1</code>')
                .replace(/\n/g, '<br>');
        }
        
        // Show typing indicator
        function showTypingIndicator() {
            const messagesContainer = document.getElementById('chatMessages');
            const typingHTML = `
                <div class="typing-indicator" id="typingIndicator">
                    <div class="message-avatar"><img src="../assets/images/icons/ai.png" alt="AI"></div>
                    <div class="typing-dots">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            `;
            messagesContainer.insertAdjacentHTML('beforeend', typingHTML);
            scrollToBottom();
        }
        
        // Hide typing indicator
        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }
        
        // Set loading state
        function setLoadingState(loading) {
            isLoading = loading;
            const sendBtn = document.getElementById('sendBtn');
            const sendIcon = document.getElementById('sendIcon');
            const sendSpinner = document.getElementById('sendSpinner');
            
            sendBtn.disabled = loading;
            sendIcon.style.display = loading ? 'none' : 'block';
            sendSpinner.style.display = loading ? 'block' : 'none';
        }
        
        // Clear messages
        function clearMessages() {
            document.getElementById('chatMessages').innerHTML = `
                <div class="empty-state" id="emptyState">
                    <div class="empty-state-icon"><img src="../assets/images/icons/ai.png" alt="AI"></div>
                    <h3 class="empty-state-title">Welcome to NeuraNest!</h3>
                    <p class="empty-state-text"><?php echo $lang['hero_subtitle']; ?></p>
                </div>
            `;
        }
        
        // Delete current chat completely
        async function deleteChat() {
            if (!currentSessionId) return;
            
            if (confirm('Вы уверены, что хотите полностью удалить этот диалог? Это действие нельзя отменить.')) {
                try {
                    const response = await fetch('api/delete_chat.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            session_id: currentSessionId
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Clear current session
                        currentSessionId = null;
                        window.history.pushState({}, '', window.location.pathname);
                        
                        // Show empty state
                        clearMessages();
                        document.getElementById('chatTitle').textContent = 'Chat';
                        
                        // Reload chat list
                        loadChatList();
                        
                        // Show notification
                        showNotification('Диалог удален', 'success');
                    } else {
                        showNotification('Ошибка удаления: ' + (data.message || 'Unknown error'), 'error');
                    }
                } catch (error) {
                    console.error('Error deleting chat:', error);
                    showNotification('Ошибка сети при удалении диалога', 'error');
                }
            }
        }
        
        // Export chat
        async function exportChat() {
            if (!currentSessionId) {
                alert('Пожалуйста, выберите чат для экспорта');
                return;
            }
            
            console.log('Exporting chat with session ID:', currentSessionId);
            
            try {
                // Show loading state
                const exportBtn = document.querySelector('[onclick="exportChat()"]');
                const originalText = exportBtn.innerHTML;
                exportBtn.innerHTML = '⏳';
                exportBtn.disabled = true;
                
                // Test if the export URL is accessible
                const testResponse = await fetch(`api/export_chat.php?session_id=${currentSessionId}`, {
                    method: 'HEAD'
                });
                
                if (!testResponse.ok) {
                    throw new Error(`HTTP ${testResponse.status}: ${testResponse.statusText}`);
                }
                
                // Create a temporary link to trigger download
                const link = document.createElement('a');
                link.href = `api/export_chat.php?session_id=${currentSessionId}`;
                link.download = `neuranest_chat_${currentSessionId}_${new Date().toISOString().slice(0,10)}.md`;
                link.style.display = 'none';
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Show success message
                setTimeout(() => {
                    exportBtn.innerHTML = '✅';
                    setTimeout(() => {
                        exportBtn.innerHTML = originalText;
                        exportBtn.disabled = false;
                    }, 1000);
                }, 500);
                
            } catch (error) {
                console.error('Export error:', error);
                alert('Ошибка экспорта: ' + error.message);
                
                // Restore button
                const exportBtn = document.querySelector('[onclick="exportChat()"]');
                exportBtn.innerHTML = '<img src="../assets/images/icons/export.png" alt="<?php echo $lang['export_chat']; ?>" class="icon">';
                exportBtn.disabled = false;
            }
        }
        
        // Edit chat title
        function editChatTitle(chatId, currentTitle) {
            const titleElement = document.getElementById(`title-${chatId}`);
            const originalHTML = titleElement.innerHTML;
            
            titleElement.innerHTML = `
                <input type="text" class="title-input" value="${currentTitle}" 
                       onblur="saveChatTitle(${chatId}, this.value, '${escapeHtml(currentTitle)}')"
                       onkeydown="handleTitleKeydown(event, ${chatId}, this.value, '${escapeHtml(currentTitle)}')"
                       onclick="event.stopPropagation()" maxlength="50">
            `;
            
            const input = titleElement.querySelector('.title-input');
            input.focus();
            input.select();
        }
        
        // Handle title input keydown
        function handleTitleKeydown(event, chatId, newTitle, originalTitle) {
            if (event.key === 'Enter') {
                event.target.blur();
            } else if (event.key === 'Escape') {
                saveChatTitle(chatId, originalTitle, originalTitle);
            }
        }
        
        // Save chat title
        async function saveChatTitle(chatId, newTitle, originalTitle) {
            newTitle = newTitle.trim();
            
            if (!newTitle || newTitle === originalTitle) {
                loadChatList(); // Restore original
                return;
            }
            
            try {
                const response = await fetch('api/update_chat_title.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        session_id: chatId,
                        title: newTitle
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadChatList();
                    if (chatId == currentSessionId) {
                        document.getElementById('chatTitle').textContent = newTitle;
                    }
                } else {
                    alert('Error updating title: ' + (data.message || 'Unknown error'));
                    loadChatList(); // Restore original
                }
            } catch (error) {
                console.error('Error updating chat title:', error);
                loadChatList(); // Restore original
            }
        }
        
        // Logout
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../auth/logout.php';
            }
        }
        
        // Scroll to bottom
        function scrollToBottom() {
            const messagesContainer = document.getElementById('chatMessages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays === 1) return L.today;
            if (diffDays === 2) return L.yesterday;
            if (diffDays <= 7) return `${diffDays} ${L.days_ago}`;
            
            return date.toLocaleDateString();
        }
        
        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    </script>
    <!-- Theme Switcher Scripts -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/theme-switcher.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add theme switcher to language switcher container
            // Note: You may need to add a language-switcher container in the chat UI if it's not already there
            const switcherContainer = document.createElement('div');
            switcherContainer.className = 'language-switcher';
            document.body.appendChild(switcherContainer);
            window.themeSwitcher.addToContainer(switcherContainer);
        });
    </script>
</body>
</html>
