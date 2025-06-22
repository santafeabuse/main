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

// Redirect if already premium
if ($user['is_premium']) {
    redirect('profile.php');
}

$errors = [];
$success = '';

// Handle payment simulation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $plan = $_POST['plan'] ?? 'monthly';
    
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = 'Недействительный токен безопасности';
    } elseif (empty($payment_method)) {
        $errors[] = $lang['payment_method_required'];
    } else {
        // Simulate payment processing
        try {
            global $db;
            
            // Generate transaction ID
            $transaction_id = 'TXN_' . time() . '_' . $user['id'];
            $amount = $plan === 'yearly' ? 2990 : 299;
            $expires_at = $plan === 'yearly' ? 
                date('Y-m-d H:i:s', strtotime('+1 year')) : 
                date('Y-m-d H:i:s', strtotime('+1 month'));
            
            // Insert payment transaction (stub)
            $stmt = $db->prepare("INSERT INTO payment_transactions (user_id, transaction_id, amount, currency, status, payment_method, subscription_type, expires_at) VALUES (?, ?, ?, 'RUB', 'completed', ?, ?, ?)");
            $stmt->execute([$user['id'], $transaction_id, $amount, $payment_method, $plan, $expires_at]);
            
            // Update user to premium
            $stmt = $db->prepare("UPDATE users SET is_premium = TRUE WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            $success = $lang['payment_successful'];
            
            // Redirect to profile after 3 seconds
            header("refresh:3;url=profile.php");
            
        } catch (Exception $e) {
            $errors[] = 'Ошибка обработки платежа: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['upgrade_to_pro']; ?> - <?php echo $lang['site_name']; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/theme-switcher.js" defer></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        
        .upgrade-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }
        
        .back-btn {
            position: fixed;
            top: 2rem;
            left: 2rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(10px);
            transition: var(--transition-fast);
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .logo {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .pricing-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .pricing-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            padding: 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: var(--transition-normal);
            border: 2px solid transparent;
        }
        
        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }
        
        .pricing-card.popular {
            border-color: #ffd700;
            transform: scale(1.05);
        }
        
        .pricing-card.popular::before {
            content: '<?php echo $lang['most_popular']; ?>';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #92400e;
            padding: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .plan-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 1rem;
        }
        
        .plan-price {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .plan-period {
            color: var(--gray-600);
            margin-bottom: 2rem;
        }
        
        .plan-features {
            list-style: none;
            margin-bottom: 2rem;
        }
        
        .plan-features li {
            padding: 0.75rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--gray-700);
        }
        
        .plan-features .check {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .select-plan-btn {
            width: 100%;
            padding: 1rem 2rem;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .select-plan-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .payment-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        
        .payment-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .payment-method:hover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.05);
        }
        
        .payment-method input[type="radio"] {
            display: none;
        }
        
        .payment-method input[type="radio"]:checked + .payment-method {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
        }
        
        .payment-icon {
            font-size: 1.5rem;
        }
        
        .payment-info {
            flex: 1;
        }
        
        .payment-name {
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .payment-desc {
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .selected-plan {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
        }
        
        .plan-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .plan-summary:last-child {
            margin-bottom: 0;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-300);
            font-weight: 600;
            font-size: 1.125rem;
        }
        
        .pay-btn {
            width: 100%;
            padding: 1.25rem 2rem;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #92400e;
            border: none;
            border-radius: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .pay-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(255, 215, 0, 0.4);
        }
        
        .pay-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .features-comparison {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        
        .comparison-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .comparison-table th,
        .comparison-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .comparison-table th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .comparison-table .check {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .comparison-table .cross {
            color: var(--error-color);
            font-weight: 600;
        }
        
        .spinner {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(146, 64, 14, 0.3);
            border-top: 3px solid #92400e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .upgrade-container {
                padding: 1rem;
            }
            
            .back-btn {
                position: static;
                margin-bottom: 2rem;
                align-self: flex-start;
            }
            
            .pricing-card.popular {
                transform: none;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
        
        /* Dark Theme Styles */
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #3a3a7a 0%, #4a2c6b 100%);
        }
        
        [data-theme="dark"] .header {
            color: #ffffff;
        }
        
        [data-theme="dark"] .back-btn {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        
        [data-theme="dark"] .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        [data-theme="dark"] .pricing-card {
            background: rgba(30, 30, 30, 0.95);
            color: #ffffff;
        }
        
        [data-theme="dark"] .pricing-card.popular::before {
            background: linear-gradient(135deg, #ccac00, #d9c200);
            color: #ffffff;
        }
        
        [data-theme="dark"] .plan-name {
            color: #ffffff;
        }
        
        [data-theme="dark"] .plan-price {
            color: var(--primary-color);
        }
        
        [data-theme="dark"] .plan-period {
            color: #dddddd;
        }
        
        [data-theme="dark"] .plan-features li {
            color: #eeeeee;
        }
        
        [data-theme="dark"] .select-plan-btn {
            background: var(--gradient-primary);
            color: #ffffff;
        }
        
        [data-theme="dark"] .payment-section {
            background: rgba(30, 30, 30, 0.95);
            color: #ffffff;
        }
        
        [data-theme="dark"] .payment-title {
            color: #ffffff;
        }
        
        [data-theme="dark"] .payment-method {
            border-color: var(--gray-700);
        }
        
        [data-theme="dark"] .payment-method:hover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
        }
        
        [data-theme="dark"] .payment-method input[type="radio"]:checked + .payment-method {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.15);
        }
        
        [data-theme="dark"] .payment-name {
            color: #ffffff;
        }
        
        [data-theme="dark"] .payment-desc {
            color: #dddddd;
        }
        
        [data-theme="dark"] .selected-plan {
            background: rgba(40, 40, 40, 0.95);
        }
        
        [data-theme="dark"] .plan-summary {
            color: #eeeeee;
        }
        
        [data-theme="dark"] .plan-summary:last-child {
            border-top-color: var(--gray-700);
            color: #ffffff;
        }
        
        [data-theme="dark"] .alert-error {
            background: #3a1a1a;
            color: #ffcccc;
            border-color: #5a2a2a;
        }
        
        [data-theme="dark"] .alert-success {
            background: #1a3a2a;
            color: #ccffdd;
            border-color: #2a5a3a;
        }
        
        [data-theme="dark"] .features-comparison {
            background: rgba(30, 30, 30, 0.95);
            color: #ffffff;
        }
        
        [data-theme="dark"] .comparison-title {
            color: #ffffff;
        }
        
        [data-theme="dark"] .comparison-table th {
            background: var(--gray-800);
            color: var(--gray-100);
        }
        
        [data-theme="dark"] .comparison-table td {
            border-bottom-color: var(--gray-700);
        }
        
        [data-theme="dark"] .pay-btn {
            background: linear-gradient(135deg, #ccac00, #d9c200);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="upgrade-container">
        <a href="profile.php" class="back-btn">
            ← <?php echo $lang['back']; ?>
        </a>
        
        <div class="header">
            <div class="logo">👑</div>
            <h1 class="title"><?php echo $lang['upgrade_to_pro']; ?></h1>
            <p class="subtitle"><?php echo $lang['premium_description']; ?></p>
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
                <br><small><?php echo $lang['redirecting_to_profile']; ?></small>
            </div>
        <?php endif; ?>
        
        <?php if (empty($success)): ?>
        <!-- Pricing Cards -->
        <div class="pricing-cards">
            <div class="pricing-card">
                <div class="plan-name"><?php echo $lang['monthly_plan']; ?></div>
                <div class="plan-price">299₽</div>
                <div class="plan-period"><?php echo $lang['per_month']; ?></div>
                
                <ul class="plan-features">
                    <li><span class="check">✓</span> <?php echo $lang['unlimited_chats']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['priority_support']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['advanced_ai']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['export_functionality']; ?></li>
                </ul>
                
                <button class="select-plan-btn" onclick="selectPlan('monthly', 299)">
                    <?php echo $lang['select_plan']; ?>
                </button>
            </div>
            
            <div class="pricing-card popular">
                <div class="plan-name"><?php echo $lang['yearly_plan']; ?></div>
                <div class="plan-price">2990₽</div>
                <div class="plan-period"><?php echo $lang['per_year']; ?> <small>(<?php echo $lang['save_2_months']; ?>)</small></div>
                
                <ul class="plan-features">
                    <li><span class="check">✓</span> <?php echo $lang['unlimited_chats']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['priority_support']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['advanced_ai']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['export_functionality']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['custom_personality']; ?></li>
                    <li><span class="check">✓</span> <?php echo $lang['api_access']; ?></li>
                </ul>
                
                <button class="select-plan-btn" onclick="selectPlan('yearly', 2990)">
                    <?php echo $lang['select_plan']; ?>
                </button>
            </div>
        </div>
        
        <!-- Payment Section -->
        <div class="payment-section" id="paymentSection" style="display: none;">
            <h2 class="payment-title"><?php echo $lang['payment_method']; ?></h2>
            
            <form method="POST" id="paymentForm">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="plan" id="selectedPlan" value="">
                
                <div class="selected-plan" id="selectedPlanInfo">
                    <!-- Plan info will be inserted here -->
                </div>
                
                <div class="payment-methods">
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="yandex_money">
                        <div class="payment-method">
                            <div class="payment-icon">💳</div>
                            <div class="payment-info">
                                <div class="payment-name">Yandex</div>
                                <div class="payment-desc"><?php echo $lang['yandex_money_desc']; ?></div>
                            </div>
                        </div>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="sberbank">
                        <div class="payment-method">
                            <div class="payment-icon">🏦</div>
                            <div class="payment-info">
                                <div class="payment-name">Сбербанк</div>
                                <div class="payment-desc"><?php echo $lang['sberbank_desc']; ?></div>
                            </div>
                        </div>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="qiwi">
                        <div class="payment-method">
                            <div class="payment-icon">📱</div>
                            <div class="payment-info">
                                <div class="payment-name">QIWI</div>
                                <div class="payment-desc"><?php echo $lang['qiwi_desc']; ?></div>
                            </div>
                        </div>
                    </label>
                    
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="card">
                        <div class="payment-method">
                            <div class="payment-icon">💳</div>
                            <div class="payment-info">
                                <div class="payment-name"><?php echo $lang['bank_card']; ?></div>
                                <div class="payment-desc"><?php echo $lang['bank_card_desc']; ?></div>
                            </div>
                        </div>
                    </label>
                </div>
                
                <button type="submit" class="pay-btn" id="payBtn" disabled>
                    <span id="payText">🔒 <?php echo $lang['pay_securely']; ?></span>
                    <div class="spinner" id="paySpinner" style="display: none;"></div>
                </button>
            </form>
        </div>
        
        <!-- Features Comparison -->
        <div class="features-comparison">
            <h2 class="comparison-title"><?php echo $lang['feature_comparison']; ?></h2>
            
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th><?php echo $lang['feature']; ?></th>
                        <th><?php echo $lang['free_plan']; ?></th>
                        <th><?php echo $lang['premium_plan']; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $lang['basic_chat']; ?></td>
                        <td><span class="check">✓</span></td>
                        <td><span class="check">✓</span></td>
                    </tr>
                    <tr>
                        <td><?php echo $lang['chat_history']; ?></td>
                        <td>7 <?php echo $lang['days']; ?></td>
                        <td><span class="check">∞</span></td>
                    </tr>
                    <tr>
                        <td><?php echo $lang['daily_messages']; ?></td>
                        <td>50</td>
                        <td><span class="check">∞</span></td>
                    </tr>
                    <tr>
                        <td><?php echo $lang['export_chats']; ?></td>
                        <td><span class="cross">✗</span></td>
                        <td><span class="check">✓</span></td>
                    </tr>
                    <tr>
                        <td><?php echo $lang['priority_support']; ?></td>
                        <td><span class="cross">✗</span></td>
                        <td><span class="check">✓</span></td>
                    </tr>
                    <tr>
                        <td><?php echo $lang['advanced_ai_models']; ?></td>
                        <td><span class="cross">✗</span></td>
                        <td><span class="check">✓</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function selectPlan(plan, price) {
            const paymentSection = document.getElementById('paymentSection');
            const selectedPlanInput = document.getElementById('selectedPlan');
            const selectedPlanInfo = document.getElementById('selectedPlanInfo');
            
            selectedPlanInput.value = plan;
            
            const planNames = {
                'monthly': '<?php echo $lang['monthly_plan']; ?>',
                'yearly': '<?php echo $lang['yearly_plan']; ?>'
            };
            
            const savings = plan === 'yearly' ? '<div class="plan-summary"><span><?php echo $lang['savings']; ?></span><span style="color: var(--success-color); font-weight: 600;">590₽</span></div>' : '';
            
            selectedPlanInfo.innerHTML = `
                <div class="plan-summary">
                    <span><?php echo $lang['selected_plan']; ?>:</span>
                    <span>${planNames[plan]}</span>
                </div>
                ${savings}
                <div class="plan-summary">
                    <span><?php echo $lang['total']; ?>:</span>
                    <span>${price}₽</span>
                </div>
            `;
            
            paymentSection.style.display = 'block';
            paymentSection.scrollIntoView({ behavior: 'smooth' });
        }
        
        // Enable pay button when payment method is selected
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const payBtn = document.getElementById('payBtn');
        
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                payBtn.disabled = false;
            });
        });
        
        // Handle form submission
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const payBtn = document.getElementById('payBtn');
            const payText = document.getElementById('payText');
            const paySpinner = document.getElementById('paySpinner');
            
            payBtn.disabled = true;
            payText.style.display = 'none';
            paySpinner.style.display = 'block';
            
            // Re-enable after 10 seconds if no redirect
            setTimeout(() => {
                payBtn.disabled = false;
                payText.style.display = 'block';
                paySpinner.style.display = 'none';
            }, 10000);
        });
    </script>
</body>
</html>
