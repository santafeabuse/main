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
$errors = [];
$success = '';

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Недействительный токен безопасности';
    } else {
        $upload_result = upload_avatar($_FILES['avatar'], $user['id']);
        
        if ($upload_result['success']) {
            global $db;
            $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$upload_result['filename'], $user['id']]);
            
            $success = $lang['avatar_updated'];
            $user['avatar'] = $upload_result['filename']; // Update local user data
        } else {
            $errors[] = $upload_result['message'];
        }
    }
}

// Handle display name update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_display_name'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $display_name = sanitize_input($_POST['display_name'] ?? '');
    
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Недействительный токен безопасности';
    } elseif (strlen($display_name) > 100) {
        $errors[] = $lang['display_name_too_long'];
    } else {
        global $db;
        $stmt = $db->prepare("UPDATE users SET display_name = ? WHERE id = ?");
        $stmt->execute([$display_name, $user['id']]);
        
        $success = $lang['profile_updated'];
        $user['display_name'] = $display_name; // Update local user data
    }
}

// Get user statistics
global $db;
$stmt = $db->prepare("SELECT COUNT(*) as chat_count FROM chat_sessions WHERE user_id = ?");
$stmt->execute([$user['id']]);
$chat_stats = $stmt->fetch();

$stmt = $db->prepare("SELECT COUNT(*) as message_count FROM messages m JOIN chat_sessions cs ON m.session_id = cs.id WHERE cs.user_id = ? AND m.role = 'user'");
$stmt->execute([$user['id']]);
$message_stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['my_profile']; ?> - <?php echo $lang['site_name']; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- AJAX Functions -->
    <script src="../assets/js/ajax.js"></script>
    <script src="../assets/js/theme-switcher.js"></script>
</head>
<body>
    <div class="profile-container">
        <!-- Navigation -->
        <div class="profile-nav">
            <a href="../chat/chat.php" class="back-btn">
                ← <?php echo $lang['back_to_chat']; ?>
            </a>
            
            <div class="language-switcher">
                <a href="?lang=ru" class="lang-btn <?php echo get_current_language() === 'ru' ? 'active' : ''; ?>">RU</a>
                <a href="?lang=en" class="lang-btn <?php echo get_current_language() === 'en' ? 'active' : ''; ?>">EN</a>
            </div>
        </div>
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-info">
                <div class="avatar-section">
                    <div class="avatar-container">
                        <?php if ($user['avatar']): ?>
                            <img src="../assets/images/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="avatar">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?php echo strtoupper(substr($user['email'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <button class="avatar-upload-btn" onclick="document.getElementById('avatarInput').click()">
                            📷
                        </button>
                    </div>
                    
                    <form id="avatarForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" class="avatar-upload-input">
                    </form>
                </div>
                
                <div class="user-details">
                    <h1 class="user-name">
                        <?php echo htmlspecialchars($user['display_name'] ?: $user['email']); ?>
                        <?php if ($user['is_premium']): ?>
                            <span class="premium-badge">👑 Premium</span>
                        <?php endif; ?>
                    </h1>
                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                    
                    <div class="user-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $chat_stats['chat_count']; ?></span>
                            <span class="stat-label"><?php echo $lang['total_chats']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $message_stats['message_count']; ?></span>
                            <span class="stat-label"><?php echo $lang['messages_sent']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                            <span class="stat-label"><?php echo $lang['member_since']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alerts -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Personal Information -->
            <div class="profile-section">
                <h2 class="section-title">
                    <span class="section-icon">👤</span>
                    <?php echo $lang['personal_info']; ?>
                </h2>
                
                <form id="profileForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="form-group">
                        <label for="display_name" class="form-label"><?php echo $lang['display_name']; ?></label>
                        <input 
                            type="text" 
                            id="display_name" 
                            name="display_name" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($user['display_name'] ?: ''); ?>"
                            placeholder="<?php echo $lang['enter_display_name']; ?>"
                            maxlength="100"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label"><?php echo $lang['email']; ?></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($user['email']); ?>"
                            disabled
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        💾 <?php echo $lang['save_changes']; ?>
                    </button>
                </form>
            </div>
            
            <!-- Account Information -->
            <div class="profile-section">
                <h2 class="section-title">
                    <span class="section-icon">ℹ️</span>
                    <?php echo $lang['account_info']; ?>
                </h2>
                
                <div class="info-item">
                    <span class="info-label"><?php echo $lang['account_created']; ?></span>
                    <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label"><?php echo $lang['last_login']; ?></span>
                    <span class="info-value"><?php echo $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : $lang['never']; ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label"><?php echo $lang['account_status']; ?></span>
                    <span class="info-value">
                        <?php if ($user['is_verified']): ?>
                            ✅ <?php echo $lang['verified']; ?>
                        <?php else: ?>
                            ❌ <?php echo $lang['not_verified']; ?>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label"><?php echo $lang['subscription']; ?></span>
                    <span class="info-value">
                        <?php if ($user['is_premium']): ?>
                            👑 <?php echo $lang['premium_user']; ?>
                        <?php else: ?>
                            🆓 <?php echo $lang['free_user']; ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <!-- Security Settings -->
            <div class="profile-section">
                <h2 class="section-title">
                    <span class="section-icon">🔒</span>
                    <?php echo $lang['security_settings']; ?>
                </h2>
                
                <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
                    <a href="change_password.php" class="btn btn-secondary">
                        🔑 <?php echo $lang['change_password']; ?>
                    </a>
                    
                    <a href="change_email.php" class="btn btn-secondary">
                        📧 <?php echo $lang['change_email']; ?>
                    </a>
                    
                    <a href="../auth/logout.php" class="btn btn-secondary">
                        🚪 <?php echo $lang['logout']; ?>
                    </a>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="profile-section">
                <h2 class="section-title">
                    <span class="section-icon">⚡</span>
                    <?php echo $lang['quick_actions']; ?>
                </h2>
                
                <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
                    <a href="../chat/chat.php" class="btn btn-primary">
                        💬 <?php echo $lang['back_to_chat']; ?>
                    </a>
                    
                    <a href="export_data.php" class="btn btn-secondary">
                        📥 <?php echo $lang['export_data']; ?>
                    </a>
                    
                    <a href="delete_account.php" class="btn btn-secondary" style="color: var(--error-color);">
                        🗑️ <?php echo $lang['delete_account']; ?>
                    </a>
                </div>
            </div>
            
            <!-- Premium Upgrade Section -->
            <?php if (!$user['is_premium']): ?>
            <div class="profile-section premium-section">
                <h2 class="section-title">
                    <span class="section-icon">👑</span>
                    <?php echo $lang['upgrade_to_pro']; ?>
                </h2>
                
                <p style="margin-bottom: var(--spacing-xl); opacity: 0.9;">
                    <?php echo $lang['premium_description']; ?>
                </p>
                
                <div class="premium-features">
                    <div class="premium-feature">
                        <div class="feature-icon">💬</div>
                        <div class="feature-title"><?php echo $lang['unlimited_chats']; ?></div>
                        <div class="feature-desc"><?php echo $lang['unlimited_chats_desc']; ?></div>
                    </div>
                    
                    <div class="premium-feature">
                        <div class="feature-icon">⚡</div>
                        <div class="feature-title"><?php echo $lang['priority_support']; ?></div>
                        <div class="feature-desc"><?php echo $lang['priority_support_desc']; ?></div>
                    </div>
                    
                    <div class="premium-feature">
                        <div class="feature-icon">🤖</div>
                        <div class="feature-title"><?php echo $lang['advanced_ai']; ?></div>
                        <div class="feature-desc"><?php echo $lang['advanced_ai_desc']; ?></div>
                    </div>
                    
                    <div class="premium-feature">
                        <div class="feature-icon">📥</div>
                        <div class="feature-title"><?php echo $lang['export_functionality']; ?></div>
                        <div class="feature-desc"><?php echo $lang['export_functionality_desc']; ?></div>
                    </div>
                </div>
                
                <a href="upgrade.php" class="btn btn-premium">
                    👑 <?php echo $lang['upgrade_now']; ?> - 299₽/<?php echo $lang['month']; ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            // Handle profile form submission
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                updateDisplayName(formData);
            });
            
            // Handle avatar upload
            $('#avatarInput').on('change', function() {
                if (this.files && this.files[0]) {
                    const formData = new FormData($('#avatarForm')[0]);
                    uploadAvatar(formData);
                }
            });
            // Add theme switcher to profile navigation
            window.themeSwitcher.addToContainer(document.querySelector('.profile-nav'));
        });
    </script>
</body>
</html>