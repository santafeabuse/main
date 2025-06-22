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

// Handle export request
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    $format = $_GET['format'] ?? 'json';
    
    try {
        global $db;
        
        // Get user data
        $userData = [
            'user_info' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'display_name' => $user['display_name'],
                'is_premium' => (bool)$user['is_premium'],
                'is_verified' => (bool)$user['is_verified'],
                'created_at' => $user['created_at'],
                'last_login' => $user['last_login']
            ]
        ];
        
        // Get chat sessions
        $stmt = $db->prepare("SELECT id, title, created_at, updated_at FROM chat_sessions WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $chats = $stmt->fetchAll();
        
        $userData['chat_sessions'] = [];
        
        foreach ($chats as $chat) {
            // Get messages for each chat
            $stmt = $db->prepare("SELECT role, content, created_at FROM messages WHERE session_id = ? ORDER BY created_at ASC");
            $stmt->execute([$chat['id']]);
            $messages = $stmt->fetchAll();
            
            $userData['chat_sessions'][] = [
                'id' => $chat['id'],
                'title' => $chat['title'],
                'created_at' => $chat['created_at'],
                'updated_at' => $chat['updated_at'],
                'messages' => $messages
            ];
        }
        
        // Get payment transactions if premium
        if ($user['is_premium']) {
            $stmt = $db->prepare("SELECT transaction_id, amount, currency, status, payment_method, subscription_type, created_at FROM payment_transactions WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user['id']]);
            $transactions = $stmt->fetchAll();
            $userData['payment_transactions'] = $transactions;
        }
        
        // Export statistics
        $userData['statistics'] = [
            'total_chats' => count($chats),
            'total_messages' => array_sum(array_map(function($chat) { return count($chat['messages']); }, $userData['chat_sessions'])),
            'export_date' => date('Y-m-d H:i:s'),
            'export_format' => $format
        ];
        
        // Generate filename
        $filename = 'neuranest_data_export_' . $user['id'] . '_' . date('Y-m-d_H-i-s');
        
        if ($format === 'json') {
            // JSON Export
            $content = json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $filename .= '.json';
            $contentType = 'application/json';
            
        } else {
            // Markdown Export
            $content = generateMarkdownExport($userData, $lang);
            $filename .= '.md';
            $contentType = 'text/markdown';
        }
        
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        
        echo $content;
        exit;
        
    } catch (Exception $e) {
        $error = 'Оши��ка экспорта: ' . $e->getMessage();
    }
}

function generateMarkdownExport($userData, $lang) {
    $content = "# NeuraNest Data Export\n\n";
    
    // User info
    $content .= "## User Information\n\n";
    $content .= "- **Email:** " . $userData['user_info']['email'] . "\n";
    $content .= "- **Display Name:** " . ($userData['user_info']['display_name'] ?: 'Not set') . "\n";
    $content .= "- **Account Type:** " . ($userData['user_info']['is_premium'] ? 'Premium' : 'Free') . "\n";
    $content .= "- **Verified:** " . ($userData['user_info']['is_verified'] ? 'Yes' : 'No') . "\n";
    $content .= "- **Member Since:** " . date('Y-m-d H:i:s', strtotime($userData['user_info']['created_at'])) . "\n";
    $content .= "- **Last Login:** " . ($userData['user_info']['last_login'] ? date('Y-m-d H:i:s', strtotime($userData['user_info']['last_login'])) : 'Never') . "\n\n";
    
    // Statistics
    $content .= "## Statistics\n\n";
    $content .= "- **Total Chats:** " . $userData['statistics']['total_chats'] . "\n";
    $content .= "- **Total Messages:** " . $userData['statistics']['total_messages'] . "\n";
    $content .= "- **Export Date:** " . $userData['statistics']['export_date'] . "\n\n";
    
    // Chat sessions
    $content .= "## Chat Sessions\n\n";
    
    if (empty($userData['chat_sessions'])) {
        $content .= "*No chat sessions found.*\n\n";
    } else {
        foreach ($userData['chat_sessions'] as $chat) {
            $content .= "### " . $chat['title'] . "\n\n";
            $content .= "- **Created:** " . date('Y-m-d H:i:s', strtotime($chat['created_at'])) . "\n";
            $content .= "- **Last Updated:** " . date('Y-m-d H:i:s', strtotime($chat['updated_at'])) . "\n";
            $content .= "- **Messages:** " . count($chat['messages']) . "\n\n";
            
            if (!empty($chat['messages'])) {
                $content .= "#### Messages\n\n";
                foreach ($chat['messages'] as $message) {
                    $role = $message['role'] === 'user' ? 'You' : 'AI Assistant';
                    $timestamp = date('H:i:s', strtotime($message['created_at']));
                    $content .= "**{$role}** ({$timestamp}):\n";
                    $content .= $message['content'] . "\n\n";
                }
            }
            
            $content .= "---\n\n";
        }
    }
    
    // Payment transactions
    if (isset($userData['payment_transactions']) && !empty($userData['payment_transactions'])) {
        $content .= "## Payment History\n\n";
        foreach ($userData['payment_transactions'] as $transaction) {
            $content .= "- **Transaction ID:** " . $transaction['transaction_id'] . "\n";
            $content .= "- **Amount:** " . $transaction['amount'] . " " . $transaction['currency'] . "\n";
            $content .= "- **Status:** " . $transaction['status'] . "\n";
            $content .= "- **Payment Method:** " . $transaction['payment_method'] . "\n";
            $content .= "- **Subscription:** " . $transaction['subscription_type'] . "\n";
            $content .= "- **Date:** " . date('Y-m-d H:i:s', strtotime($transaction['created_at'])) . "\n\n";
        }
    }
    
    $content .= "---\n";
    $content .= "*Exported from NeuraNest - " . date('Y-m-d H:i:s') . "*\n";
    
    return $content;
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['export_data']; ?> - <?php echo $lang['site_name']; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Theme Switcher Script -->
    <script src="../assets/js/theme-switcher.js" defer></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .export-container {
            width: 100%;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 1rem;
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .export-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .back-btn {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: none;
            border: none;
            color: var(--gray-600);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: var(--transition-fast);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-btn:hover {
            background: var(--gray-100);
            color: var(--primary-color);
        }
        
        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        .export-options {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .export-option {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 2px solid var(--gray-200);
            transition: var(--transition-fast);
        }
        
        .export-option:hover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.05);
        }
        
        .option-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .option-icon {
            font-size: 2rem;
        }
        
        .option-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .option-description {
            color: var(--gray-600);
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .option-features {
            list-style: none;
            margin-bottom: 1.5rem;
        }
        
        .option-features li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .option-features .check {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .export-btn {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .export-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .info-section {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
        }
        
        .info-title {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .info-item {
            text-align: center;
        }
        
        .info-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            display: block;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        /* Dark Theme Styles */
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #3a3a7a 0%, #4a2c6b 100%);
        }
        
        [data-theme="dark"] .export-container {
            background: rgba(30, 30, 30, 0.95);
            color: #ffffff;
        }
        
        [data-theme="dark"] .back-btn {
            color: #ffffff;
        }
        
        [data-theme="dark"] .back-btn:hover {
            background: var(--gray-800);
            color: var(--primary-color);
        }
        
        [data-theme="dark"] .title {
            color: #ffffff;
        }
        
        [data-theme="dark"] .subtitle {
            color: #dddddd;
        }
        
        [data-theme="dark"] .export-option {
            background: rgba(40, 40, 40, 0.95);
            border-color: var(--gray-700);
        }
        
        [data-theme="dark"] .export-option:hover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.2);
        }
        
        [data-theme="dark"] .option-title {
            color: #ffffff;
        }
        
        [data-theme="dark"] .option-description {
            color: #eeeeee;
        }
        
        [data-theme="dark"] .option-features li {
            color: #eeeeee;
        }
        
        [data-theme="dark"] .info-section {
            background: rgba(40, 40, 40, 0.95);
        }
        
        [data-theme="dark"] .info-title {
            color: #ffffff;
        }
        
        [data-theme="dark"] .info-label {
            color: #dddddd;
        }
        
        [data-theme="dark"] .alert-error {
            background: #3a1a1a;
            color: #ffcccc;
            border-color: #5a2a2a;
        }
        
        [data-theme="dark"] .alert-info {
            background: #1a2a3a;
            color: #bbccff;
            border-color: #2a3a5a;
        }
    </style>
</head>
<body>
    <div class="export-container">
        <a href="profile.php" class="back-btn">
            ← <?php echo $lang['back']; ?>
        </a>
        
        <div class="header">
            <div class="logo">📥</div>
            <h1 class="title"><?php echo $lang['export_data']; ?></h1>
            <p class="subtitle"><?php echo $lang['export_data_description']; ?></p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-info">
            <strong><?php echo $lang['privacy_notice']; ?>:</strong> <?php echo $lang['export_privacy_text']; ?>
        </div>
        
        <!-- User Statistics -->
        <div class="info-section">
            <div class="info-title"><?php echo $lang['your_data_summary']; ?></div>
            <div class="info-grid">
                <?php
                global $db;
                $stmt = $db->prepare("SELECT COUNT(*) as chat_count FROM chat_sessions WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $chat_stats = $stmt->fetch();
                
                $stmt = $db->prepare("SELECT COUNT(*) as message_count FROM messages m JOIN chat_sessions cs ON m.session_id = cs.id WHERE cs.user_id = ?");
                $stmt->execute([$user['id']]);
                $message_stats = $stmt->fetch();
                ?>
                <div class="info-item">
                    <span class="info-number"><?php echo $chat_stats['chat_count']; ?></span>
                    <span class="info-label"><?php echo $lang['total_chats']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-number"><?php echo $message_stats['message_count']; ?></span>
                    <span class="info-label"><?php echo $lang['total_messages']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-number"><?php echo $user['is_premium'] ? 'Premium' : 'Free'; ?></span>
                    <span class="info-label"><?php echo $lang['account_type']; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Export Options -->
        <div class="export-options">
            <!-- JSON Export -->
            <div class="export-option">
                <div class="option-header">
                    <div class="option-icon">📄</div>
                    <div class="option-title">JSON Export</div>
                </div>
                <div class="option-description">
                    <?php echo $lang['json_export_description']; ?>
                </div>
                <ul class="option-features">
                    <li><span class="check">✓</span> <?php echo $lang['machine_readable']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['complete_data']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['developer_friendly']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['easy_import']; ?></li>
                </ul>
                <a href="?action=download&format=json" class="export-btn">
                    📄 <?php echo $lang['download_json']; ?>
                </a>
            </div>
            
            <!-- Markdown Export -->
            <div class="export-option">
                <div class="option-header">
                    <div class="option-icon">📝</div>
                    <div class="option-title">Markdown Export</div>
                </div>
                <div class="option-description">
                    <?php echo $lang['markdown_export_description']; ?>
                </div>
                <ul class="option-features">
                    <li><span class="check">✓</span> <?php echo $lang['human_readable']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['formatted_text']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['easy_viewing']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['printable']; ?></li>
                </ul>
                <a href="?action=download&format=markdown" class="export-btn">
                    📝 <?php echo $lang['download_markdown']; ?>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
