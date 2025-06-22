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
    <title><?php echo $lang['site_name']; ?> - <?php echo $lang['hero_subtitle']; ?></title>
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo $lang['hero_description']; ?>">
    <meta name="keywords" content="AI, чат, искусственный интеллект, NeuraNest">
    <meta name="author" content="NeuraNest">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $lang['site_name']; ?>">
    <meta property="og:description" content="<?php echo $lang['hero_description']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    
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
            transition: none !important;
        }
        
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
        
        /* Floating elements animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(1deg); }
            66% { transform: translateY(5px) rotate(-1deg); }
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        /* Glowing effect on scroll */
        .glow-on-scroll {
            transition: all 0.3s ease;
        }
        
        .glow-on-scroll.glowing {
            box-shadow: 0 0 30px rgba(102, 126, 234, 0.4), 0 0 60px rgba(240, 147, 251, 0.2);
            transform: scale(1.02);
        }
        
        /* Morphing background gradient */
        @keyframes morphGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .morphing-bg {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: morphGradient 15s ease infinite;
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
        
        /* Tilt effect on hover */
        .tilt-effect {
            transition: transform 0.3s ease;
        }
        
        .tilt-effect:hover {
            transform: perspective(1000px) rotateX(5deg) rotateY(5deg) scale(1.05);
        }
        
        /* Prevent layout shifts during theme changes */
        body {
            /* overflow-x: hidden; */
        }
        
        /* Smooth theme transitions without affecting background */
        [data-theme="light"] .parallax,
        [data-theme="dark"] .parallax {
            transition: none !important;
            transform: none !important;
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
        /* Chat preview card text in light theme */
        [data-theme="light"] .glass .text-xl.font-semibold.tracking-tight {
            color: rgb(103, 112, 123) !important;
        }
        [data-theme="light"] .glass .text-slate-200.font-semibold {
            color: rgb(103, 112, 123) !important;
        }
        [data-theme="light"] .glass .text-slate-300.text-base {
            color: #1e293b !important;
            font-weight: 500 !important;
        }
        [data-theme="light"] .glass .text-indigo-400.font-semibold {
            color: #4338ca !important;
        }
        [data-theme="light"] .glass .text-indigo-100.text-base {
            color: #1e293b !important;
        }
        [data-theme="light"] .glass .text-slate-400,
        [data-theme="light"] .glass .text-xs.text-slate-400 {
            color: #1e293b !important;
            font-weight: 500 !important;
        }
        [data-theme="light"] .ai-typing {
            color:rgb(103, 112, 123) !important;
        }
        /* Remove duplicate navigation link styles */
        /* Hero section text in light theme - subtle color change but still visible */
        [data-theme="light"] #hero .text-slate-400 {
            color: #e2e8f0 !important;
            text-shadow: 0 1px 8px rgba(0,0,0,0.3);
        }
        [data-theme="light"] #hero .text-slate-300 {
            color: #f1f5f9 !important;
            text-shadow: 0 1px 8px rgba(0,0,0,0.3);
        }
        [data-theme="light"] #hero #hero-tagline {
            color: #f8fafc !important;
            text-shadow: 0 1px 8px rgba(0,0,0,0.3);
        }
        [data-theme="light"] #hero .text-base {
            color:rgb(103, 112, 123) !important;
            text-shadow: 0 1px 8px rgba(0,0,0,0.3);
        }
        
        /* Preserve hero background in light theme */
        [data-theme="light"] .parallax {
            background: linear-gradient(120deg,rgba(70,55,255,.14),rgba(255,60,180,.09)), url('https://images.unsplash.com/photo-1621619856624-42fd193a0661?w=2160&q=80') center/cover no-repeat !important;
        }
    </style>
</head>
<body class="relative min-h-screen bg-[#181B24] text-slate-100 selection:bg-indigo-400/40 flex flex-col transition-colors duration-500">

    <!-- Particles background -->
    <div id="particles-js" class="fixed inset-0 -z-10"></div>
    <div class="parallax pointer-events-none"></div>

    <!-- Navigation Bar -->
    <nav class="flex items-center justify-between px-6 py-5 max-w-7xl mx-auto w-full z-10 relative">
        <div class="flex items-center gap-2">
            <img src="assets/images/logo.png" alt="" class="w-10 h-10 rounded-xl shadow-lg glass border border-white/10 animate-stagger animate-delay-1"/>
            <span class="text-2xl font-semibold tracking-tight bg-gradient-to-r from-indigo-400 via-fuchsia-400 to-pink-400 bg-clip-text text-transparent animate-stagger animate-delay-2" data-lang-key="site_name"><?php echo $lang['site_name']; ?></span>
        </div>
        <div class="hidden md:flex items-center gap-8">
            <a href="#features" class="text-base font-medium text-slate-200 hover:text-indigo-400 transition animate-stagger animate-delay-3" data-lang-key="features"><?php echo isset($lang['features']) ? $lang['features'] : 'Features'; ?></a>
            <a href="#privacy" class="text-base font-medium text-slate-200 hover:text-indigo-400 transition animate-stagger animate-delay-4" data-lang-key="privacy"><?php echo isset($lang['privacy']) ? $lang['privacy'] : 'Privacy'; ?></a>
            <a href="ai_history.php" class="text-base font-medium text-slate-200 hover:text-indigo-400 transition animate-stagger animate-delay-5" data-lang-key="ai_history"><?php echo isset($lang['ai_history']) ? $lang['ai_history'] : 'AI History'; ?></a>
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
            <a href="#features" class="text-lg font-medium text-slate-100 hover:text-indigo-400 mb-3" data-lang-key="features"><?php echo isset($lang['features']) ? $lang['features'] : 'Features'; ?></a>
            <a href="#privacy" class="text-lg font-medium text-slate-100 hover:text-indigo-400 mb-3" data-lang-key="privacy"><?php echo isset($lang['privacy']) ? $lang['privacy'] : 'Privacy'; ?></a>
            <a href="ai_history.php" class="text-lg font-medium text-slate-100 hover:text-indigo-400 mb-3" data-lang-key="ai_history"><?php echo isset($lang['ai_history']) ? $lang['ai_history'] : 'AI History'; ?></a>
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
    <section id="hero" class="relative flex flex-col md:flex-row items-center justify-center min-h-[75vh] px-4 md:px-0 max-w-7xl mx-auto mt-8 md:mt-16">
        <div class="w-full md:w-1/2 flex flex-col gap-8 z-10">
            <div>
                <h1 class="text-[2.8rem] md:text-[3.4rem] font-semibold tracking-tight leading-tight bg-gradient-to-br from-indigo-400 via-fuchsia-400 to-pink-400 bg-clip-text text-transparent animate-stagger animate-delay-1">
                    <span class="inline-flex items-center gap-3">
                        <img src="assets/images/logo.png" alt="" class="w-12 h-12 rounded-xl shadow-lg glass border border-white/10"/>
                        <span class="typing" id="hero-typing" data-lang-key="hero_title"><?php echo $lang['hero_title']; ?></span>
                    </span>
                </h1>
                <p id="hero-tagline" class="text-lg sm:text-xl md:text-2xl font-medium text-slate-300 mt-4 animate-stagger animate-delay-2" data-lang-key="hero_subtitle"><?php echo $lang['hero_subtitle']; ?></p>
                <p class="text-base text-slate-400 mt-2 animate-stagger animate-delay-2" data-lang-key="hero_description"><?php echo $lang['hero_description']; ?></p>
            </div>
            <div class="flex gap-4 mt-6 animate-stagger animate-delay-3">
                <?php if (is_logged_in()): ?>
                    <a href="chat/chat.php" class="px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-pink-400 font-semibold text-lg shadow-lg hover:scale-105 transition border-2 border-transparent focus-visible:ring-2 focus-visible:ring-indigo-400">
                        <i data-lucide="message-square" class="w-5 h-5 inline-block mr-2"></i>
                        <span data-lang-key="chat"><?php echo $lang['chat']; ?></span>
                    </a>
                    <a href="profile/profile.php" class="px-7 py-3 rounded-xl glass font-semibold text-lg border-2 border-indigo-500 text-indigo-200 hover:bg-indigo-500/20 hover:text-white transition focus-visible:ring-2 focus-visible:ring-indigo-400">
                        <i data-lucide="user" class="w-5 h-5 inline-block mr-2"></i>
                        <span data-lang-key="profile"><?php echo $lang['profile']; ?></span>
                    </a>
                <?php else: ?>
                    <a href="auth/register.php" class="px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-pink-400 font-semibold text-lg shadow-lg hover:scale-105 transition border-2 border-transparent focus-visible:ring-2 focus-visible:ring-indigo-400">
                        <i data-lucide="user-plus" class="w-5 h-5 inline-block mr-2"></i>
                        <span data-lang-key="get_started"><?php echo $lang['get_started']; ?></span>
                    </a>
                    <a href="auth/login.php" class="px-7 py-3 rounded-xl glass font-semibold text-lg border-2 border-indigo-500 text-indigo-200 hover:bg-indigo-500/20 hover:text-white transition focus-visible:ring-2 focus-visible:ring-indigo-400">
                        <i data-lucide="log-in" class="w-5 h-5 inline-block mr-2"></i>
                        <span data-lang-key="login"><?php echo $lang['login']; ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="w-full md:w-1/2 flex items-center justify-center relative pt-12 md:pt-0 animate-stagger animate-delay-4">
            <!-- Glassmorphism AI preview card -->
            <div class="glass p-7 rounded-3xl shadow-2xl border-[2.5px] border-indigo-400/30 backdrop-blur-xl w-full max-w-md mx-auto relative overflow-hidden transition hover:scale-105 duration-300">
                <div class="absolute -top-7 -right-10 w-32 h-32 bg-gradient-to-br from-indigo-500/50 via-fuchsia-400/30 to-transparent rounded-full blur-2xl"></div>
                <div class="flex items-center gap-3 mb-4">
                    <i data-lucide="message-square" class="w-7 h-7 text-indigo-400"></i>
                    <span class="text-xl font-semibold tracking-tight" data-lang-key="ai_chat_preview"><?php echo isset($lang['ai_chat_preview']) ? $lang['ai_chat_preview'] : 'AI Chat Preview'; ?></span>
                </div>
                <div class="flex flex-col gap-3">
                    <div class="flex gap-2 items-start">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-fuchsia-400 flex items-center justify-center">
                            <i data-lucide="user" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <div class="text-slate-200 font-semibold" data-lang-key="user"><?php echo isset($lang['user']) ? $lang['user'] : 'User'; ?></div>
                            <div class="text-slate-300 text-base" data-lang-key="demo_question"><?php echo isset($lang['demo_question']) ? $lang['demo_question'] : 'How can I use NeuraNest for studies?'; ?></div>
                        </div>
                    </div>
                    <div class="flex gap-2 items-start">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-pink-400 flex items-center justify-center">
                            <i data-lucide="bot" class="w-8 h-8 text-white"></i>
                        </div>
                        <div>
                            <div class="text-indigo-400 font-semibold" data-lang-key="site_name"><?php echo $lang['site_name']; ?></div>
                            <div class="text-indigo-100 text-base">
                                <span class="ai-typing" data-lang-key="demo_answer"><?php echo isset($lang['demo_answer']) ? $lang['demo_answer'] : 'Absolutely! Use NeuraNest to brainstorm, summarize, and organize your study materials.'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2 text-xs text-slate-400">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                    <span data-lang-key="privacy_badge"><?php echo isset($lang['privacy_badge']) ? $lang['privacy_badge'] : 'Private. Secure. Local-first.'; ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="relative z-10 mt-14 md:mt-24 max-w-7xl mx-auto px-4 md:px-0">
        <div class="flex flex-col items-center justify-center text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight bg-gradient-to-r from-indigo-300 via-fuchsia-400 to-pink-400 bg-clip-text text-transparent animate-stagger animate-delay-1" data-lang-key="features_title"><?php echo $lang['features_title']; ?></h2>
            <p class="mt-4 text-lg text-slate-400 animate-stagger animate-delay-2" data-lang-key="features_subtitle">
                <?php echo isset($lang['features_subtitle']) ? $lang['features_subtitle'] : 'Fast, private, and intuitive AI chat for everyone.'; ?>
            </p>
        </div>
        <div class="grid md:grid-cols-4 gap-7">
            <div class="glass p-7 flex flex-col items-center rounded-2xl border border-indigo-400/10 shadow-lg animate-stagger animate-delay-2 hover:border-indigo-400/60 transition group">
                <i data-lucide="bot" class="w-10 h-10 text-indigo-400 mb-3 group-hover:scale-110 transition"></i>
                <h3 class="text-xl font-semibold tracking-tight mb-2" data-lang-key="feature_ai_title"><?php echo $lang['feature_ai_title']; ?></h3>
                <p class="text-slate-300 text-base" data-lang-key="feature_ai_desc"><?php echo $lang['feature_ai_desc']; ?></p>
            </div>
            <div class="glass p-7 flex flex-col items-center rounded-2xl border border-indigo-400/10 shadow-lg animate-stagger animate-delay-3 hover:border-indigo-400/60 transition group">
                <i data-lucide="globe" class="w-10 h-10 text-pink-400 mb-3 group-hover:scale-110 transition"></i>
                <h3 class="text-xl font-semibold tracking-tight mb-2" data-lang-key="feature_multilang_title"><?php echo $lang['feature_multilang_title']; ?></h3>
                <p class="text-slate-300 text-base" data-lang-key="feature_multilang_desc"><?php echo $lang['feature_multilang_desc']; ?></p>
            </div>
            <div class="glass p-7 flex flex-col items-center rounded-2xl border border-indigo-400/10 shadow-lg animate-stagger animate-delay-4 hover:border-indigo-400/60 transition group">
                <i data-lucide="lock" class="w-10 h-10 text-fuchsia-400 mb-3 group-hover:scale-110 transition"></i>
                <h3 class="text-xl font-semibold tracking-tight mb-2" data-lang-key="feature_secure_title"><?php echo $lang['feature_secure_title']; ?></h3>
                <p class="text-slate-300 text-base" data-lang-key="feature_secure_desc"><?php echo $lang['feature_secure_desc']; ?></p>
            </div>
            <div class="glass p-7 flex flex-col items-center rounded-2xl border border-indigo-400/10 shadow-lg animate-stagger animate-delay-5 hover:border-indigo-400/60 transition group">
                <i data-lucide="layout-dashboard" class="w-10 h-10 text-indigo-300 mb-3 group-hover:scale-110 transition"></i>
                <h3 class="text-xl font-semibold tracking-tight mb-2" data-lang-key="feature_modern_ui_title"><?php echo isset($lang['feature_modern_ui_title']) ? $lang['feature_modern_ui_title'] : 'Modern UI'; ?></h3>
                <p class="text-slate-300 text-base" data-lang-key="feature_modern_ui_desc"><?php echo isset($lang['feature_modern_ui_desc']) ? $lang['feature_modern_ui_desc'] : 'Sleek, responsive, and beautiful on all devices.'; ?></p>
            </div>
        </div>
    </section>

    <!-- Divider -->
    <div class="my-16 w-full h-px bg-gradient-to-r from-transparent via-indigo-400/30 to-transparent"></div>

    <!-- Privacy Section -->
    <section id="privacy" class="relative z-10 max-w-4xl mx-auto px-4 md:px-0 mb-24">
        <div class="glass rounded-2xl p-10 md:p-16 text-center border border-indigo-400/10 shadow-lg animate-stagger animate-delay-5">
            <h2 class="text-2xl md:text-3xl font-semibold tracking-tight bg-gradient-to-r from-indigo-300 via-fuchsia-400 to-pink-400 bg-clip-text text-transparent mb-5" data-lang-key="privacy_title">
                <?php echo isset($lang['privacy_title']) ? $lang['privacy_title'] : 'Your Privacy, Guaranteed'; ?>
            </h2>
            <p class="text-lg text-slate-300 mb-3" data-lang-key="privacy_description">
                <?php echo isset($lang['privacy_description']) ? $lang['privacy_description'] : 'NeuraNest values your privacy. Your conversations are stored locally in your browser and never leave your device. No tracking, no ads, no compromise.'; ?>
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 py-12 flex flex-col md:flex-row items-center justify-between w-full max-w-none mx-0 px-8 md:px-16 border-t border-indigo-400/10 bg-[#181B24]/80 glass" style="min-height:90px;">
        <div class="flex items-center gap-2">
            <img src="assets/images/logo.png" alt="" class="w-8 h-8 rounded-xl shadow glass border border-white/10" onerror="this.style.display='none'"/>
            <span class="font-semibold text-slate-300 tracking-tight"><?php echo $lang['site_name']; ?></span>
        </div>
        <div class="flex items-center gap-4 mt-4 md:mt-0 text-slate-400 text-sm flex-wrap">
            <a href="#privacy" class="hover:text-indigo-400 transition" data-lang-key="privacy"><?php echo isset($lang['privacy']) ? $lang['privacy'] : 'Privacy'; ?></a>
            <a href="#features" class="hover:text-indigo-400 transition" data-lang-key="features"><?php echo isset($lang['features']) ? $lang['features'] : 'Features'; ?></a>
            <a href="ai_history.php" class="hover:text-indigo-400 transition" data-lang-key="ai_history"><?php echo isset($lang['ai_history']) ? $lang['ai_history'] : 'AI History'; ?></a>
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

        // Parallax effect
        let lastScrollY = 0;
        window.addEventListener('scroll', () => {
            const parallax = document.querySelector('.parallax');
            if (parallax) {
                parallax.style.backgroundPositionY = `${window.scrollY * 0.18}px`;
            }
        });

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

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', function(e) {
                const t = document.querySelector(this.getAttribute('href'));
                if(t) {
                    e.preventDefault();
                    t.scrollIntoView({behavior:'smooth'});
                    if(window.innerWidth<768 && mobileMenu) mobileMenu.style.display='none';
                }
            });
        });

        // Features card hover outline
        document.querySelectorAll('.glass').forEach(card=>{
            card.addEventListener('mouseenter',()=>card.classList.add('border-indigo-400/60'));
            card.addEventListener('mouseleave',()=>card.classList.remove('border-indigo-400/60'));});

        // AI typing effect initialization
        function startAITyping() {
            const aiText = document.querySelector('.ai-typing');
            if (aiText) {
                const text = aiText.textContent;
                aiText.textContent = '';
                let i = 0;
                const typeInterval = setInterval(() => {
                    if (i < text.length) {
                        aiText.textContent += text.charAt(i);
                        i++;
                    } else {
                        clearInterval(typeInterval);
                        aiText.style.borderRight = 'none';
                    }
                }, 50);
            }
        }

        // Initialize particles
        if (typeof particlesJS !== 'undefined') {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 80, density: { enable: true, value_area: 800 } },
                    color: { value: ["#667eea", "#f093fb", "#a855f7"] },
                    shape: {
                        type: "circle",
                        stroke: { width: 0, color: "#000000" }
                    },
                    opacity: {
                        value: 0.4,
                        random: true,
                        anim: { enable: true, speed: 1, opacity_min: 0.1, sync: false }
                    },
                    size: {
                        value: 3,
                        random: true,
                        anim: { enable: true, speed: 2, size_min: 0.5, sync: false }
                    },
                    line_linked: {
                        enable: true,
                        distance: 150,
                        color: "#667eea",
                        opacity: 0.2,
                        width: 1
                    },
                    move: {
                        enable: true,
                        speed: 1,
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
                        grab: { distance: 400, line_linked: { opacity: 1 } },
                        bubble: { distance: 400, size: 40, duration: 2, opacity: 8, speed: 3 },
                        repulse: { distance: 100, duration: 0.4 },
                        push: { particles_nb: 4 },
                        remove: { particles_nb: 2 }
                    }
                },
                retina_detect: true
            });
        }

        // Initialize AI typing effect after page load
        setTimeout(startAITyping, 2000);

        // Add floating animation to feature cards
        document.querySelectorAll('.glass').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.2}s`;
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
                this.style.boxShadow = '0 20px 40px rgba(102, 126, 234, 0.3)';
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
        document.querySelectorAll('#features .glass, #privacy .glass').forEach(el => {
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

        // Add click ripple effect to buttons
        document.querySelectorAll('a[class*="bg-gradient"], button').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                const rippleStyle = document.createElement('style');
                rippleStyle.textContent = `
                    .ripple {
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.3);
                        transform: scale(0);
                        animation: ripple-animation 0.6s linear;
                        pointer-events: none;
                    }
                    @keyframes ripple-animation {
                        to { transform: scale(4); opacity: 0; }
                    }
                `;
                document.head.appendChild(rippleStyle);
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
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
            .ai-typing {
                border-right: 2px solid #818cf8;
                animation: blink-caret 0.75s step-end infinite;
            }
            @keyframes blink-caret {
                from, to { border-color: transparent; }
                50% { border-color: #818cf8; }
            }
        `;
        document.head.appendChild(styleSheet);

        // Initialize all animations and effects
        setTimeout(() => {
            document.querySelectorAll('.animate-stagger').forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
            });
        }, 100);

    });
    </script>

    <!-- Theme Switcher Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeButton = document.getElementById('theme-switcher-button');
            if (themeButton) {
                // Set initial icon based on current theme
                const currentTheme = localStorage.getItem('neuranest-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
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
                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('neuranest-theme', newTheme);
                    themeButton.setAttribute('data-theme', newTheme);
                    if (newTheme === 'dark') {
                        themeButton.innerHTML = '<i data-lucide="sun" class="w-4 h-4 inline-block"></i>';
                    } else {
                        themeButton.innerHTML = '<i data-lucide="moon" class="w-4 h-4 inline-block"></i>';
                    }
                    lucide.createIcons();
                    // Force a visual change to confirm theme switch
                    document.body.style.backgroundColor = newTheme === 'dark' ? '#121212' : '#f5f5f5';
                    document.body.style.color = newTheme === 'dark' ? '#ffffff' : '#333333';
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
                    // If it's the ai-typing element, restart the animation if the function exists
                    if (element.classList.contains('ai-typing') && typeof startAITyping === 'function') {
                        startAITyping();
                    }
                }
            });

            // Update specific elements if needed
            document.querySelector('#hero-typing').textContent = translations['hero_title'] || 'Default Hero Title';
            document.querySelector('#hero-tagline').textContent = translations['hero_subtitle'] || 'Default Subtitle';
            document.querySelector('title').textContent = translations['site_name'] + ' - ' + translations['hero_subtitle'];
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
    
    <!-- Additional particles background script -->
    <script>
        // Create floating geometric shapes
        function createFloatingShapes() {
            const container = document.body;
            const shapes = ['circle', 'triangle', 'square'];
            
            for (let i = 0; i < 5; i++) {
                const shape = document.createElement('div');
                const shapeType = shapes[Math.floor(Math.random() * shapes.length)];
                const size = Math.random() * 60 + 20;
                
                shape.className = `floating-shape floating-${shapeType}`;
                shape.style.cssText = `
                    position: fixed;
                    width: ${size}px;
                    height: ${size}px;
                    opacity: 0.1;
                    pointer-events: none;
                    z-index: 1;
                    left: ${Math.random() * 100}vw;
                    top: ${Math.random() * 100}vh;
                    animation: floatAround ${15 + Math.random() * 10}s linear infinite;
                `;
                
                if (shapeType === 'circle') {
                    shape.style.borderRadius = '50%';
                    shape.style.background = 'linear-gradient(45deg, #667eea, #f093fb)';
                } else if (shapeType === 'triangle') {
                    shape.style.width = '0';
                    shape.style.height = '0';
                    shape.style.borderLeft = `${size/2}px solid transparent`;
                    shape.style.borderRight = `${size/2}px solid transparent`;
                    shape.style.borderBottom = `${size}px solid rgba(102, 126, 234, 0.3)`;
                } else {
                    shape.style.background = 'linear-gradient(45deg, #a855f7, #ec4899)';
                    shape.style.transform = 'rotate(45deg)';
                }
                
                container.appendChild(shape);
            }
        }

        // Add floating animation keyframes
        const floatingStyle = document.createElement('style');
        floatingStyle.textContent = `
            @keyframes floatAround {
                0% { transform: translate(0, 0) rotate(0deg); }
                25% { transform: translate(100px, -100px) rotate(90deg); }
                50% { transform: translate(-50px, -200px) rotate(180deg); }
                75% { transform: translate(-150px, -50px) rotate(270deg); }
                100% { transform: translate(0, 0) rotate(360deg); }
            }
        `;
        document.head.appendChild(floatingStyle);

        // Initialize floating shapes
        setTimeout(createFloatingShapes, 1000);
    </script>

    <!-- Cool Scroll Effects 2025 -->
    <script src="assets/js/scroll-effects.js"></script>

</body>
</html>