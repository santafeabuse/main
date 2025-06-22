<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Handle language switching
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ru', 'en'])) {
    set_language($_GET['lang']);
    redirect($_SERVER['PHP_SELF']);
}

$lang = load_language();
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['site_name']; ?> - <?php echo isset($lang['ai_history_title']) ? $lang['ai_history_title'] : 'Neural Network Training History'; ?></title>
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo isset($lang['ai_history_description']) ? $lang['ai_history_description'] : 'Explore the journey of how our neural network was trained and developed'; ?>">
    <meta name="keywords" content="AI, нейросеть, обучение, искусственный интеллект, NeuraNest">
    <meta name="author" content="NeuraNest">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $lang['site_name']; ?> - <?php echo isset($lang['ai_history_title']) ? $lang['ai_history_title'] : 'Neural Network Training History'; ?>">
    <meta property="og:description" content="<?php echo isset($lang['ai_history_description']) ? $lang['ai_history_description'] : 'Explore the journey of how our neural network was trained and developed'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>/ai_history.php">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Google Fonts: Inter, Manrope for modern look -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Particles.js for background -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    
    <style>
        body {
            font-family: 'Inter', 'Manrope', system-ui, sans-serif;
            transition: background 0.8s, color 0.5s;
        }
        .glass {
            background: rgba(40,48,72,0.7);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            backdrop-filter: blur(16px) saturate(180%);
            border-radius: 1.1rem;
            border: 1.5px solid rgba(255,255,255,0.09);
        }
        .gradient-border {
            border-width: 2px;
            border-image: linear-gradient(90deg, #667eea 10%, #f093fb 90%) 1;
        }
        /* Staggered fade/blur/slide in */
        @keyframes fadeUp {
            from { opacity: 0; filter: blur(8px); transform: translateY(32px);}
            to { opacity: 1; filter: blur(0); transform: none;}
        }
        .animate-stagger { animation: fadeUp 1s cubic-bezier(.7,0,.2,1) forwards; opacity: 0;}
        .animate-delay-1 { animation-delay: .15s; }
        .animate-delay-2 { animation-delay: .3s; }
        .animate-delay-3 { animation-delay: .45s; }
        .animate-delay-4 { animation-delay: .6s; }
        .animate-delay-5 { animation-delay: .75s; }
        /* Light theme contrast overrides */
        [data-theme="light"] body { color: var(--text-primary); }
        [data-theme="light"] .text-slate-200 { color: #374151; }
        [data-theme="light"] .text-slate-300 { color: #334155; }
        [data-theme="light"] .text-slate-400 { color: #475569; }
        [data-theme="light"] .text-slate-500 { color: #64748b; }
        [data-theme="light"] .text-slate-600 { color: #4b5563; }
        [data-theme="light"] a { color:rgb(0, 0, 0); }
        /* Theme switcher and language buttons in light theme - keep white text on dark backgrounds */
        [data-theme="light"] nav #theme-switcher-button,
        [data-theme="light"] nav button[onclick^="switchLanguage"] {
            color: white !important;
            background-color: transparent !important;
            border-color: rgba(255,255,255,0.2) !important;
        }
        /* Navigation links - features, privacy - white text in both themes */
        [data-theme="light"] nav .text-base.font-medium.text-slate-200 {
            color: white !important;
        }
        [data-theme="light"] nav .text-base.font-medium.text-slate-200:hover {
            color: #a5b4fc !important; /* light indigo on hover */
        }
        /* Login and register buttons in navigation - keep white text on dark backgrounds */
        [data-theme="light"] nav a[href="auth/login.php"],
        [data-theme="light"] nav a[href="auth/register.php"],
        [data-theme="light"] nav .gradient-border.bg-[#181B24]\/70,
        [data-theme="light"] a[href="auth/login.php"],
        [data-theme="light"] a[href="auth/register.php"] {
            background-color: rgba(67, 56, 202, 0.7) !important;
            color: white !important;
            border: 1px solid rgba(255,255,255,0.2) !important;
        }
        
        /* Hover effects for buttons in light theme - navigation area */
        [data-theme="light"] nav button[onclick^="switchLanguage"]:hover,
        [data-theme="light"] nav #theme-switcher-button:hover {
            background-color: rgba(255,255,255,0.1) !important;
        }
        
        [data-theme="light"] nav a[href="auth/login.php"]:hover,
        [data-theme="light"] nav a[href="auth/register.php"]:hover,
        [data-theme="light"] nav .gradient-border.bg-[#181B24]\/70:hover {
            background-color: rgba(99, 102, 241, 0.8) !important;
        }
        
        /* Theme switcher icon in light theme */
        [data-theme="light"] nav #theme-switcher-button i {
            color: white !important;
        }
        
        /* Typing animation for logo text */
        .typing::after {
            content: '';
            animation: typingdots 1.2s infinite steps(3, jump-none);
            font-weight: 400;
        }
        @keyframes typingdots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%,100% { content: '...'; }
        }
        /* Parallax background image */
        .parallax {
            background: linear-gradient(120deg,rgba(70,55,255,.14),rgba(255,60,180,.09)), url('https://images.unsplash.com/photo-1621619856624-42fd193a0661?w=2160&q=80') center/cover no-repeat;
            position: fixed;
            width: 100vw; height: 100vh;
            left: 0; top: 0; z-index: 0;
            will-change: transform;
            transition: transform 0.2s linear;
        }
        
        /* Hero text color tweaks for readability */
        #hero p, #hero .text-slate-300, #hero .text-slate-400 { color: #e5e7eb !important; }
        [data-theme="light"] #hero p, [data-theme="light"] #hero .text-slate-300 { color:rgb(182, 194, 221) !important; }
        [data-theme="light"] #hero .text-slate-400 { color: #c1cfee !important; }

        
        

        /* Cool scroll effects 2025 */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(50px) scale(0.95);
            transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        
        .parallax-element {
            transform: translateZ(0);
            will-change: transform;
        }
        
        /* Timeline scroll animations */
        .timeline-item {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .timeline-item:nth-child(even) {
            transform: translateX(50px);
        }
        
        .timeline-item.animate-in {
            opacity: 1;
            transform: translateX(0);
        }
        
        /* Floating timeline elements */
        @keyframes timelineFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }
        
        .timeline-content {
            animation: timelineFloat 4s ease-in-out infinite;
        }
        
        /* Glowing timeline dots */
        .timeline-item::after {
            transition: all 0.3s ease;
        }
        
        .timeline-item.animate-in::after {
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.6), 0 0 40px rgba(240, 147, 251, 0.4);
            transform: scale(1.2);
        }
        
        /* Scroll progress indicator */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #f093fb);
            z-index: 9999;
            transition: width 0.1s ease;
        }
        
        /* Particle trail effect */
        .particle-trail {
            position: absolute;
            width: 4px;
            height: 4px;
            background: radial-gradient(circle, #667eea, transparent);
            border-radius: 50%;
            pointer-events: none;
            animation: particleFade 2s ease-out forwards;
        }
        
        @keyframes particleFade {
            0% { opacity: 1; transform: scale(1); }
            100% { opacity: 0; transform: scale(0); }
        }
        
        /* Morphing background gradient for timeline */
        @keyframes morphGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .timeline-container::after {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: morphGradient 10s ease infinite;
        }
        
        /* Prevent layout shifts during theme changes */
        body {
            overflow-x: hidden;
        }
        
        /* Smooth theme transitions without affecting background */
        [data-theme="light"] .parallax,
        [data-theme="dark"] .parallax {
            transition: none !important;
            transform: none !important;
            will-change: background-position;
            position: fixed !important;
        }
        /* Hide scrollbar */
        ::-webkit-scrollbar { width: 0 !important }
        html {scroll-behavior: smooth;}
        /* Custom styling for theme switcher button */
        #theme-switcher-button[data-theme="dark"] i {
            color: #f093fb;
        }
        #theme-switcher-button[data-theme="light"] i {
            color: #667eea;
        }
        /* Light theme overrides */
        [data-theme="light"] body {
            background-color: #f5f5f5 !important;
            color: #222 !important;
        }
        [data-theme="light"] .glass {
            background: rgba(225, 224, 223, 0.8) !important;
            color: #222 !important;
            border-color: #c7d2fe !important;
        }
        [data-theme="light"] .text-slate-100 {
            color: #1a202c !important;
        }
        [data-theme="light"] .text-slate-200 {
            color:rgb(255, 255, 255) !important;
        }
        [data-theme="light"] .text-slate-300 {
            color: #4a5568 !important;
        }
        [data-theme="light"] .text-slate-400 {
            color: #718096 !important;
        }
        [data-theme="light"] .text-slate-500 {
            color: #a0aec0 !important;
        }
        [data-theme="light"] .gradient-border.bg-[#181B24]\/70,
        [data-theme="light"] a.gradient-border.bg-[#181B24]\/70 {
            background: #f8fafc !important;
            color: #4338ca !important;
            border: 1px solid #e2e8f0 !important;
        }
        [data-theme="light"] .gradient-border.bg-[#181B24]\/70:hover,
        [data-theme="light"] a.gradient-border.bg-[#181B24]\/70:hover {
            background: #edf2f7 !important;
            color: #4338ca !important;
        }
        [data-theme="light"] .bg-[#181B24],
        [data-theme="light"] .bg-[#181B24]\/80 {
            background-color: #fff !important;
        }
        [data-theme="light"] .text-base.font-medium.text-slate-200,
        [data-theme="light"] .text-base.font-medium.text-slate-200:hover {
            color: #4338ca !important;
        }
        [data-theme="light"] #hero-tagline,
        [data-theme="light"] .text-slate-400.mt-2 {
            color: #222 !important;
        }
        [data-theme="light"] .text-slate-300.text-base {
            color: #333 !important;
        }
        
        /* Timeline styles */
        .timeline-container {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
        }
        .timeline-container::after {
            content: '';
            position: absolute;
            width: 4px;
            background: linear-gradient(to bottom, #667eea, #f093fb);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -2px;
            border-radius: 4px;
        }
        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }
        .timeline-item:nth-child(odd) {
            left: 0;
        }
        .timeline-item:nth-child(even) {
            left: 50%;
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            right: -10px;
            top: 24px;
            background: linear-gradient(to right, #667eea, #f093fb);
            border-radius: 50%;
            z-index: 1;
        }
        .timeline-item:nth-child(even)::after {
            left: -10px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            width: 30px;
            height: 2px;
            background: linear-gradient(to right, #667eea, #f093fb);
            top: 33px;
            z-index: 1;
        }
        .timeline-item:nth-child(odd)::before {
            right: 0;
        }
        .timeline-item:nth-child(even)::before {
            left: 0;
        }
        .timeline-date {
            position: absolute;
            top: 18px;
            font-weight: 600;
        }
        .timeline-item:nth-child(odd) .timeline-date {
            right: -150px;
        }
        .timeline-item:nth-child(even) .timeline-date {
            left: -150px;
        }
        .timeline-content {
            padding: 20px;
            border-radius: 16px;
        }
        .timeline-content img {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        /* Mobile timeline adjustments */
        @media screen and (max-width: 768px) {
            .timeline-container::after {
                left: 31px;
            }
            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 20px;
            }
            .timeline-item:nth-child(odd),
            .timeline-item:nth-child(even) {
                left: 0;
            }
            .timeline-item::after {
                left: 21px;
                top: 24px;
            }
            .timeline-item:nth-child(odd)::before,
            .timeline-item:nth-child(even)::before {
                left: 40px;
                right: auto;
            }
            .timeline-item:nth-child(odd) .timeline-date,
            .timeline-item:nth-child(even) .timeline-date {
                right: auto;
                left: -40px;
                top: -15px;
                font-size: 0.8rem;
                width: 100px;
                text-align: right;
            }
        }

        /* Preserve hero background in light theme */
        [data-theme="light"] .parallax {
            background: linear-gradient(120deg,rgba(70,55,255,.14),rgba(255,60,180,.09)), url('https://images.unsplash.com/photo-1621619856624-42fd193a0661?w=2160&q=80') center/cover no-repeat;
        }
        
        /* Improved text contrast for hero section */
        #hero h1, #hero h2, #hero p {
            text-shadow: 0 2px 12px rgba(0,0,0,0.7);
            position: relative;
            z-index: 40;
        }
        
        /* Timeline content improvements */
        .timeline-content {
            background: rgba(40,48,72,0.95) !important;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.15);
            color: #e2e8f0 !important;
        }
        
        .timeline-content h3,
        .timeline-content h4,
        .timeline-content p {
            color: #e2e8f0 !important;
        }
        
        [data-theme="light"] .timeline-content {
            background: rgba(255,255,255,0.95) !important;
            color: #1a202c !important;
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        [data-theme="light"] .timeline-content h3,
        [data-theme="light"] .timeline-content h4,
        [data-theme="light"] .timeline-content p {
            color: #1a202c !important;
        }
        
        /* Timeline date improvements */
        .timeline-date {
            background: rgba(40,48,72,0.95);
            padding: 6px 12px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.15);
            color: #e2e8f0 !important;
        }
        
        [data-theme="light"] .timeline-date {
            background: rgba(255,255,255,0.95) !important;
            color: #1a202c !important;
            border: 1px solid rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="relative min-h-screen bg-[#181B24] text-slate-100 selection:bg-indigo-400/40 flex flex-col transition-colors duration-500">

    <!-- Particles background -->
    <div id="particles-js" class="fixed inset-0 -z-20"></div>
    <div class="parallax pointer-events-none"></div>

    <!-- Navigation Bar -->
    <nav class="flex items-center justify-between px-6 py-5 max-w-7xl mx-auto w-full z-10 relative">
        <div class="flex items-center gap-2">
            <a href="index.php">
                <img src="assets/images/logo.png" alt="" class="w-10 h-10 rounded-xl shadow-lg glass border border-white/10 animate-stagger animate-delay-1"/>
            </a>
            <a href="index.php">
                <span class="text-2xl font-semibold tracking-tight bg-gradient-to-r from-indigo-400 via-fuchsia-400 to-pink-400 bg-clip-text text-transparent animate-stagger animate-delay-2" data-lang-key="site_name"><?php echo $lang['site_name']; ?></span>
            </a>
        </div>
        <div class="hidden md:flex items-center gap-8">
            <a href="index.php#features" class="text-base font-medium text-slate-200 hover:text-indigo-400 transition animate-stagger animate-delay-3" data-lang-key="features"><?php echo isset($lang['features']) ? $lang['features'] : 'Features'; ?></a>
            <a href="index.php#privacy" class="text-base font-medium text-slate-200 hover:text-indigo-400 transition animate-stagger animate-delay-4" data-lang-key="privacy"><?php echo isset($lang['privacy']) ? $lang['privacy'] : 'Privacy'; ?></a>
            <a href="ai_history.php" class="text-base font-medium text-indigo-400 transition animate-stagger animate-delay-5" data-lang-key="ai_history"><?php echo isset($lang['ai_history']) ? $lang['ai_history'] : 'AI History'; ?></a>
            <?php if (is_logged_in()): ?>
                <a href="chat/chat.php" class="text-base font-medium text-slate-200 hover:text-indigo-400 transition animate-stagger animate-delay-5" data-lang-key="chat"><?php echo $lang['chat']; ?></a>
                <a href="profile/profile.php" class="text-base font-medium text-slate-200 hover:text-indigo-400 transition animate-stagger animate-delay-5" data-lang-key="profile"><?php echo $lang['profile']; ?></a>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-2 md:gap-4">
            <!-- Language Switcher -->
            <button onclick="switchLanguage('ru')" class="px-3 py-1.5 text-sm font-semibold rounded-lg hover:bg-indigo-600/30 border border-white/10 outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 transition animate-stagger animate-delay-3 <?php echo get_current_language() === 'ru' ? 'bg-indigo-600/50' : ''; ?>" aria-label="Switch to Russian">RU</button>
            <button onclick="switchLanguage('en')" class="px-3 py-1.5 text-sm font-semibold rounded-lg hover:bg-indigo-600/30 border border-white/10 outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 transition animate-stagger animate-delay-4 <?php echo get_current_language() === 'en' ? 'bg-indigo-600/50' : ''; ?>" aria-label="Switch to English">EN</button>
            
            <!-- Theme Switcher Button -->
            <button id="theme-switcher-button" class="ml-3 px-3 py-1.5 text-sm font-semibold rounded-lg hover:bg-indigo-600/30 border border-white/10 outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 transition animate-stagger animate-delay-5" aria-label="Switch Theme">
                <i data-lucide="moon" class="w-4 h-4 inline-block"></i>
            </button>
            
            <?php if (is_logged_in()): ?>
                <a href="chat/chat.php" class="ml-3 px-4 py-2 rounded-lg gradient-border bg-[#181B24]/70 hover:bg-indigo-700/70 text-slate-200 font-semibold transition shadow-xl animate-stagger animate-delay-5" data-lang-key="chat"><?php echo $lang['chat']; ?></a>
                <a href="auth/logout.php" class="ml-2 px-4 py-2 rounded-lg bg-gradient-to-r from-red-500 via-red-600 to-red-400 text-white font-semibold shadow-xl hover:scale-105 transition animate-stagger animate-delay-5" data-lang-key="logout"><?php echo $lang['logout']; ?></a>
            <?php else: ?>
                <a href="auth/login.php" class="ml-3 px-4 py-2 rounded-lg gradient-border bg-[#181B24]/70 hover:bg-indigo-700/70 text-slate-200 font-semibold transition shadow-xl animate-stagger animate-delay-5" data-lang-key="login"><?php echo $lang['login']; ?></a>
                <a href="auth/register.php" class="ml-2 px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-pink-400 text-white font-semibold shadow-xl hover:scale-105 transition animate-stagger animate-delay-5" data-lang-key="register"><?php echo $lang['register']; ?></a>
            <?php endif; ?>
            
            <!-- Hamburger Menu (Mobile) -->
            <button id="nav-mobile" class="ml-3 md:hidden flex items-center justify-center w-10 h-10 rounded-full hover:bg-indigo-500/20 transition animate-stagger animate-delay-5">
                <i data-lucide="menu" class="w-6 h-6 text-slate-200"></i>
            </button>
        </div>
    </nav>
    
    <!-- Mobile nav drawer -->
    <div id="mobile-menu" class="fixed z-30 top-0 left-0 w-full h-full bg-black/70 backdrop-blur-[2px] hidden">
        <div class="absolute top-0 right-0 w-60 bg-[#181B24] h-full flex flex-col gap-8 pt-20 px-7 shadow-2xl animate-stagger animate-delay-3 glass">
            <a href="index.php#features" class="text-lg font-medium text-slate-100 hover:text-indigo-400 mb-3" data-lang-key="features"><?php echo isset($lang['features']) ? $lang['features'] : 'Features'; ?></a>
            <a href="index.php#privacy" class="text-lg font-medium text-slate-100 hover:text-indigo-400 mb-3" data-lang-key="privacy"><?php echo isset($lang['privacy']) ? $lang['privacy'] : 'Privacy'; ?></a>
            <a href="ai_history.php" class="text-lg font-medium text-indigo-400 mb-3" data-lang-key="ai_history"><?php echo isset($lang['ai_history']) ? $lang['ai_history'] : 'AI History'; ?></a>
            <?php if (is_logged_in()): ?>
                <a href="chat/chat.php" class="text-lg font-medium text-slate-100 hover:text-indigo-400 mb-3" data-lang-key="chat"><?php echo $lang['chat']; ?></a>
                <a href="profile/profile.php" class="text-lg font-medium text-slate-100 hover:text-indigo-400 mb-3" data-lang-key="profile"><?php echo $lang['profile']; ?></a>
                <div class="flex flex-col gap-2 mt-6">
                    <a href="chat/chat.php" class="px-4 py-2 rounded-lg bg-indigo-700 text-white font-semibold shadow-lg" data-lang-key="chat"><?php echo $lang['chat']; ?></a>
                    <a href="auth/logout.php" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold shadow-lg" data-lang-key="logout"><?php echo $lang['logout']; ?></a>
                </div>
            <?php else: ?>
                <div class="flex flex-col gap-2 mt-6">
                    <a href="auth/login.php" class="px-4 py-2 rounded-lg bg-indigo-700 text-white font-semibold shadow-lg" data-lang-key="login"><?php echo $lang['login']; ?></a>
                    <a href="auth/register.php" class="px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-pink-400 text-white font-semibold shadow-lg" data-lang-key="register"><?php echo $lang['register']; ?></a>
                </div>
            <?php endif; ?>
            <button id="close-mobile" class="absolute top-4 right-4">
                <i data-lucide="x" class="w-7 h-7 text-slate-300"></i>
            </button>
        </div>
    </div>

    <!-- Hero Section -->
    <section id="hero" class="relative flex flex-col items-center justify-center min-h-[40vh] px-4 md:px-0 max-w-7xl mx-auto mt-8 md:mt-16 z-40">
        <div class="w-full flex flex-col gap-8 z-30 text-center">
            <div>
                <h1 class="text-[2.8rem] md:text-[3.4rem] font-semibold tracking-tight leading-tight bg-gradient-to-br from-indigo-400 via-fuchsia-400 to-pink-400 bg-clip-text text-transparent animate-stagger animate-delay-1 relative z-50" data-lang-key="ai_history_title">
                    <?php echo isset($lang['ai_history_title']) ? $lang['ai_history_title'] : 'Neural Network Training History'; ?>
                </h1>
                <p id="hero-tagline" class="text-lg sm:text-xl md:text-2xl font-medium text-slate-300 mt-4 animate-stagger animate-delay-2 relative z-30" data-lang-key="ai_history_subtitle">
                    <?php echo isset($lang['ai_history_subtitle']) ? $lang['ai_history_subtitle'] : 'Learn about our AI training process'; ?>
                </p>
                <p class="text-base text-slate-400 mt-2 animate-stagger animate-delay-2 max-w-2xl mx-auto relative z-30" data-lang-key="ai_history_description">
                    <?php echo isset($lang['ai_history_description']) ? $lang['ai_history_description'] : 'Explore the journey of how our neural network was trained and developed'; ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="relative z-10 max-w-6xl mx-auto px-4 md:px-8 py-16">
        <div class="timeline-container">
            <!-- Timeline Item 1 -->
            <div class="timeline-item animate-stagger animate-delay-2">
                <div class="timeline-date text-indigo-300"><?php echo isset($lang['stage1']) ? $lang['stage1'] : 'Stage 1'; ?></div>
                <div class="timeline-content glass p-6">
                    <h3 class="text-xl font-semibold tracking-tight mb-3 bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent"><?php echo $lang['ai_stage1_title']; ?></h3>
                    <img src="assets/images/history/stage-1.png" alt="<?php echo $lang['ai_stage1_title']; ?>" class="w-full h-48 object-cover rounded-lg mb-4 border border-indigo-500/30">
                    <p class="text-slate-300"><?php echo $lang['ai_stage1_desc']; ?></p>
                </div>
            </div>
            
            <!-- Timeline Item 2 -->
            <div class="timeline-item animate-stagger animate-delay-3">
                <div class="timeline-date text-indigo-300"><?php echo isset($lang['stage2']) ? $lang['stage2'] : 'Stage 2'; ?></div>
                <div class="timeline-content glass p-6">
                    <h3 class="text-xl font-semibold tracking-tight mb-3 bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent"><?php echo $lang['ai_stage2_title']; ?></h3>
                    <img src="assets/images/history/stage-2.png" alt="<?php echo $lang['ai_stage2_title']; ?>" class="w-full h-48 object-cover rounded-lg mb-4 border border-indigo-500/30">
                    <p class="text-slate-300"><?php echo $lang['ai_stage2_desc']; ?></p>
                </div>
            </div>
            
            <!-- Timeline Item 3 -->
            <div class="timeline-item animate-stagger animate-delay-4">
                <div class="timeline-date text-indigo-300"><?php echo isset($lang['stage3']) ? $lang['stage3'] : 'Stage 3'; ?></div>
                <div class="timeline-content glass p-6">
                    <h3 class="text-xl font-semibold tracking-tight mb-3 bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent"><?php echo $lang['ai_stage3_title']; ?></h3>
                    <img src="assets/images/history/stage-3.png" alt="<?php echo $lang['ai_stage3_title']; ?>" class="w-full h-48 object-cover rounded-lg mb-4 border border-indigo-500/30">
                    <p class="text-slate-300"><?php echo $lang['ai_stage3_desc']; ?></p>
                </div>
            </div>
            
            <!-- Timeline Item 4 -->
            <div class="timeline-item animate-stagger animate-delay-5">
                <div class="timeline-date text-indigo-300"><?php echo isset($lang['stage4']) ? $lang['stage4'] : 'Stage 4'; ?></div>
                <div class="timeline-content glass p-6">
                    <h3 class="text-xl font-semibold tracking-tight mb-3 bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent"><?php echo $lang['ai_stage4_title']; ?></h3>
                    <img src="assets/images/history/stage-4.png" alt="<?php echo $lang['ai_stage4_title']; ?>" class="w-full h-48 object-cover rounded-lg mb-4 border border-indigo-500/30">
                    <p class="text-slate-300"><?php echo $lang['ai_stage4_desc']; ?></p>
                </div>
            </div>
            
            <!-- Timeline Item 5 -->
            <div class="timeline-item animate-stagger animate-delay-5">
                <div class="timeline-date text-indigo-300"><?php echo isset($lang['stage5']) ? $lang['stage5'] : 'Stage 5'; ?></div>
                <div class="timeline-content glass p-6">
                    <h3 class="text-xl font-semibold tracking-tight mb-3 bg-gradient-to-r from-indigo-300 to-pink-300 bg-clip-text text-transparent"><?php echo $lang['ai_stage5_title']; ?></h3>
                    <img src="assets/images/history/stage-5.png" alt="<?php echo $lang['ai_stage5_title']; ?>" class="w-full h-48 object-cover rounded-lg mb-4 border border-indigo-500/30">
                    <p class="text-slate-300"><?php echo $lang['ai_stage5_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 py-12 flex flex-col md:flex-row items-center justify-between w-full max-w-none mx-0 px-8 md:px-16 border-t border-indigo-400/10 bg-[#181B24]/80 glass mt-24" style="min-height:90px;">
        <div class="flex items-center gap-2">
            <img src="assets/images/logo.png" alt="" class="w-8 h-8 rounded-xl shadow glass border border-white/10" onerror="this.style.display='none'"/>
            <span class="font-semibold text-slate-300 tracking-tight"><?php echo $lang['site_name']; ?></span>
        </div>
        <div class="flex items-center gap-4 mt-4 md:mt-0 text-slate-400 text-sm flex-wrap">
            <a href="index.php#privacy" class="hover:text-indigo-400 transition" data-lang-key="privacy"><?php echo isset($lang['privacy']) ? $lang['privacy'] : 'Privacy'; ?></a>
            <a href="index.php#features" class="hover:text-indigo-400 transition" data-lang-key="features"><?php echo isset($lang['features']) ? $lang['features'] : 'Features'; ?></a>
            <a href="ai_history.php" class="text-indigo-400 transition" data-lang-key="ai_history"><?php echo isset($lang['ai_history']) ? $lang['ai_history'] : 'AI History'; ?></a>
            <?php if (is_logged_in()): ?>
                <a href="chat/chat.php" class="hover:text-indigo-400 transition" data-lang-key="chat"><?php echo $lang['chat']; ?></a>
                <a href="profile/profile.php" class="hover:text-indigo-400 transition" data-lang-key="profile"><?php echo $lang['profile']; ?></a>
            <?php endif; ?>
        </div>
        <div class="text-xs text-slate-500 mt-4 md:mt-0 whitespace-nowrap">© 2024 <?php echo $lang['site_name']; ?>. All rights reserved.</div>
    </footer>

    <!-- Toast notification -->
    <div id="toast" class="fixed left-1/2 -translate-x-1/2 top-8 z-50 px-6 py-3 rounded-xl glass text-slate-100 font-semibold shadow-lg border border-indigo-400/30 backdrop-blur-xl pointer-events-none opacity-0 transition hidden"></div>

    <script>
        // Stagger animation on load
        window.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.animate-stagger').forEach(el => el.style.opacity = 1);
        });
        
        // Lucide icons render
        lucide.createIcons();

        // Parallax effect function
        function updateParallax() {
            const parallax = document.querySelector('.parallax');
            if (!parallax) return;
            const doc = document.documentElement;
            const maxScroll = doc.scrollHeight - doc.clientHeight;
            const progress = maxScroll ? window.scrollY / maxScroll : 0;
            parallax.style.transform = `translateY(${progress * 100}vh)`;
        }
        
        // Apply parallax on scroll
        window.addEventListener('scroll', () => {
            requestAnimationFrame(updateParallax);
        });
        
        // Initialize parallax on load
        window.addEventListener('load', updateParallax);

        // Toast notification
        function showToast(msg) {
            if (!msg) return;
            const el = document.getElementById('toast');
            el.textContent = msg;
            el.classList.remove('hidden');
            el.style.opacity = 1;
            el.style.pointerEvents = 'auto';
            setTimeout(() => {
                el.style.opacity = 0;
                el.style.pointerEvents = 'none';
                setTimeout(() => el.classList.add('hidden'), 400);
            }, 1600);
        }

        // Mobile nav
        const mobileMenu = document.getElementById('mobile-menu');
        const navBtn = document.getElementById('nav-mobile');
        const closeBtn = document.getElementById('close-mobile');
        if (navBtn && mobileMenu && closeBtn) {
            navBtn.onclick = () => { mobileMenu.style.display = 'block'; };
            closeBtn.onclick = () => { mobileMenu.style.display = 'none'; };
            mobileMenu.onclick = e => { if(e.target===mobileMenu) mobileMenu.style.display='none'; };
        }

        // Features card hover outline
        document.querySelectorAll('.glass').forEach(card=>{
            card.addEventListener('mouseenter',()=>card.classList.add('border-indigo-400/60'));
            card.addEventListener('mouseleave',()=>card.classList.remove('border-indigo-400/60'));});

        // Initialize particles with optimized configuration
        if (typeof particlesJS !== 'undefined') {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 40, density: { enable: true, value_area: 1200 } }, // Further reduced number
                    color: { value: ["#667eea", "#f093fb", "#a855f7"] },
                    shape: {
                        type: "circle",
                        stroke: { width: 0, color: "#000000" }
                    },
                    opacity: {
                        value: 0.2, // Lower opacity
                        random: true,
                        anim: { enable: true, speed: 1, opacity_min: 0.1, sync: false }
                    },
                    size: {
                        value: 2, // Even smaller particles
                        random: true,
                        anim: { enable: true, speed: 2, size_min: 0.3, sync: false }
                    },
                    line_linked: {
                        enable: true,
                        distance: 160, // Increased distance between links
                        color: "#667eea",
                        opacity: 0.15, // Lower opacity for links
                        width: 0.8 // Thinner links
                    },
                    move: {
                        enable: true,
                        speed: 0.6, // Even slower movement
                        direction: "none",
                        random: true,
                        straight: false,
                        out_mode: "out",
                        bounce: false
                    }
                },
                interactivity: {
                    detect_on: "canvas",
                    events: {
                        onhover: { enable: true, mode: "repulse" },
                        onclick: { enable: true, mode: "push" },
                        resize: true
                    },
                    modes: {
                        repulse: { distance: 100, duration: 0.4 }, // Increased repulse distance
                        push: { particles_nb: 2 }, // Even fewer particles pushed
                        remove: { particles_nb: 2 }
                    }
                },
                retina_detect: true
            });
        }

        // Add floating animation to timeline items
        document.querySelectorAll('.timeline-content').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.2}s`;
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
                this.style.boxShadow = '0 20px 40px rgba(102, 126, 234, 0.3)';
                this.style.transition = 'all 0.3s ease';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 8px 32px 0 rgba(31, 38, 135, 0.18)';
            });
        });

        // Add scroll reveal animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe elements for scroll animations
        document.querySelectorAll('.timeline-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Add dynamic background color change on scroll
        let ticking = false;
        function updateBackgroundOnScroll() {
            const scrollTop = window.pageYOffset;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = scrollTop / docHeight;
            
            const startColor = [24, 27, 36]; // #181B24
            const endColor = [30, 41, 59]; // #1e293b
            
            const currentColor = startColor.map((start, i) => {
                const end = endColor[i];
                return Math.round(start + (end - start) * scrollPercent);
            });
            
            document.body.style.backgroundColor = `rgb(${currentColor.join(', ')})`;
            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateBackgroundOnScroll);
                ticking = true;
            }
        });

        // Enhanced mobile menu functionality
        const mobileMenuOverlay = document.getElementById('mobile-menu');
        const mobileNavBtn = document.getElementById('nav-mobile');
        const closeMobileBtn = document.getElementById('close-mobile');
        
        if (mobileNavBtn && mobileMenuOverlay && closeMobileBtn) {
            mobileNavBtn.addEventListener('click', () => {
                mobileMenuOverlay.classList.remove('hidden');
                mobileMenuOverlay.style.animation = 'fadeIn 0.3s ease forwards';
                document.body.style.overflow = 'hidden';
            });
            
            closeMobileBtn.addEventListener('click', () => {
                mobileMenuOverlay.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => {
                    mobileMenuOverlay.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }, 300);
            });
            
            mobileMenuOverlay.addEventListener('click', (e) => {
                if (e.target === mobileMenuOverlay) {
                    closeMobileBtn.click();
                }
            });
        }

        // Add custom CSS animations
        const styleSheet = document.createElement('style');
        styleSheet.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(styleSheet);

        // Initialize all animations and effects
        setTimeout(() => {
            document.querySelectorAll('.animate-stagger').forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
            });
        }, 100);
    </script>

    <!-- Theme Switcher Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeButton = document.getElementById('theme-switcher-button');
            if (themeButton) {
                // Set initial icon based on current theme
                const currentTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', currentTheme);
                themeButton.setAttribute('data-theme', currentTheme);
                if (currentTheme === 'dark') {
                    themeButton.innerHTML = '<i data-lucide="sun" class="w-4 h-4 inline-block"></i>';
                } else {
                    themeButton.innerHTML = '<i data-lucide="moon" class="w-4 h-4 inline-block"></i>';
                }
                lucide.createIcons();

        // Toggle theme on button click
        themeButton.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            // Store current scroll position
            const currentScrollY = window.scrollY;
            
            // Update theme
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeButton.setAttribute('data-theme', newTheme);
            
            // Update button icon
            if (newTheme === 'dark') {
                themeButton.innerHTML = '<i data-lucide="sun" class="w-4 h-4 inline-block"></i>';
            } else {
                themeButton.innerHTML = '<i data-lucide="moon" class="w-4 h-4 inline-block"></i>';
            }
            lucide.createIcons();
            
            // Force a visual change to confirm theme switch
            document.body.style.backgroundColor = newTheme === 'dark' ? '#121212' : '#f5f5f5';
            document.body.style.color = newTheme === 'dark' ? '#ffffff' : '#333333';
            
            // Ensure parallax works after theme change
            setTimeout(() => {
                updateParallax();
            }, 50);
        });
            }
        });
    </script>
    
    <!-- Language Switcher AJAX Script -->
    <script>
        function switchLanguage(lang) {
            fetch('api/language.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ lang: lang })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text(); // Use text() instead of json() to debug content
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        updatePageContent(data.translations);
                        updateLanguageButtons(lang);
                        showToast('Language switched to ' + lang.toUpperCase());
                    } else {
                        showToast('Failed to switch language');
                    }
                } catch (error) {
                    console.error('JSON Parse Error:', error, 'Response Text:', text);
                    showToast('Error parsing language data');
                }
            })
            .catch(error => {
                console.error('Error switching language:', error);
                showToast('Error switching language');
            });
        }

        function updateLanguageButtons(lang) {
            document.querySelectorAll('button[onclick^="switchLanguage"]').forEach(btn => {
                btn.classList.remove('bg-indigo-600/50');
                if (btn.getAttribute('onclick').includes(lang)) {
                    btn.classList.add('bg-indigo-600/50');
                }
            });
        }

        function updatePageContent(translations) {
            // Update all elements with data-lang-key attribute, including nested ones
            document.querySelectorAll('[data-lang-key]').forEach(element => {
                const key = element.getAttribute('data-lang-key');
                if (translations[key]) {
                    element.textContent = translations[key];
                }
            });

            // Update specific elements if needed
            if (document.querySelector('title')) {
                document.querySelector('title').textContent = translations['site_name'] + ' - ' + (translations['ai_history_title'] || 'Neural Network Training History');
            }
        }

        // Define showToast function here to ensure it's accessible
        function showToast(msg) {
            const el = document.getElementById('toast');
            if (el) {
                el.textContent = msg;
                el.style.opacity = 1;
                el.style.pointerEvents = 'auto';
                setTimeout(() => {
                    el.style.opacity = 0;
                    el.style.pointerEvents = 'none';
                }, 1600);
            } else {
                console.log('Toast element not found, message:', msg);
            }
        }
    </script>

    <!-- Cool Scroll Effects 2025 -->
    <script src="assets/js/scroll-effects.js"></script>

</body>
</html>
