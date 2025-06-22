<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_login();
$lang = load_language();
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_language(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['custom_instructions']; ?> - <?php echo $lang['site_name']; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Main CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Apply theme immediately -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('neuranest-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    
    <style>
        :root[data-theme="light"] {
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing-md: 16px;
            --spacing-lg: 24px;
            --spacing-xl: 32px;
            --spacing-2xl: 48px;
            --text-xs: 12px;
            --text-sm: 14px;
            --text-base: 16px;
            --text-lg: 18px;
            --text-xl: 24px;
            --text-2xl: 32px;
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --white: #ffffff;
            --primary-color: #667eea;
            --primary-color-light: #e0e7ff;
            --primary-color-dark: #4c63d2;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --error-color: #ef4444;
            --success-color: #22c55e;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --transition-fast: all 0.15s ease;
        }
        
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: var(--gray-50);
            font-family: 'Inter', sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-xl);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
        }
        
        .header h1 {
            margin: 0;
            font-size: var(--text-2xl);
            color: var(--gray-800);
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-sm) var(--spacing-md);
            background: none;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            color: var(--gray-700);
            cursor: pointer;
            transition: var(--transition-fast);
            font-size: var(--text-sm);
            text-decoration: none;
        }
        
        .back-btn:hover {
            background: var(--gray-100);
            color: var(--gray-900);
        }
        
        .instructions-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-lg);
        }
        
        .instructions-list {
            border-radius: var(--radius-lg);
            background: var(--white);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        
        .instructions-list-header {
            padding: var(--spacing-md) var(--spacing-lg);
            background: var(--gray-100);
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .instructions-list-title {
            font-size: var(--text-lg);
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
        }
        
        .new-instruction-btn {
            padding: var(--spacing-sm) var(--spacing-md);
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .new-instruction-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .instructions-list-body {
            padding: var(--spacing-lg);
            max-height: 600px;
            overflow-y: auto;
        }
        
        .no-instructions {
            text-align: center;
            color: var(--gray-500);
            font-size: var(--text-base);
            padding: var(--spacing-2xl) var(--spacing-lg);
        }
        
        .instruction-card {
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-md);
            background: var(--white);
            transition: var(--transition-fast);
        }
        
        .instruction-card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color-light);
        }
        
        .instruction-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-sm);
        }
        
        .instruction-title {
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
            font-size: var(--text-base);
        }
        
        .instruction-actions {
            display: flex;
            gap: var(--spacing-xs);
        }
        
        .action-btn {
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
            padding: var(--spacing-xs);
            border-radius: var(--radius-sm);
            transition: var(--transition-fast);
        }
        
        .action-btn:hover {
            color: var(--gray-700);
            background: var(--gray-100);
        }
        
        .edit-btn:hover {
            color: var(--primary-color);
        }
        
        .delete-btn:hover {
            color: var(--error-color);
        }
        
        .instruction-preview {
            font-size: var(--text-sm);
            color: var(--gray-600);
            margin-top: var(--spacing-sm);
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .instruction-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: var(--spacing-md);
            gap: var(--spacing-sm);
        }
        
        .apply-btn {
            padding: var(--spacing-xs) var(--spacing-md);
            background: var(--primary-color-light);
            color: var(--primary-color-dark);
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--text-xs);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .apply-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .new-chat-btn {
            padding: var(--spacing-xs) var(--spacing-md);
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--text-xs);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .new-chat-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }
        
        .instruction-form {
            border-radius: var(--radius-lg);
            background: var(--white);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        
        .instruction-form-header {
            padding: var(--spacing-md) var(--spacing-lg);
            background: var(--gray-100);
            border-bottom: 1px solid var(--gray-200);
        }
        
        .instruction-form-title {
            font-size: var(--text-lg);
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
        }
        
        .instruction-form-body {
            padding: var(--spacing-lg);
        }
        
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        .form-label {
            display: block;
            margin-bottom: var(--spacing-xs);
            font-weight: 500;
            color: var(--gray-700);
        }
        
        .form-input {
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--text-base);
            color: var(--text-primary);
            background: var(--bg-primary);
            transition: var(--transition-fast);
            box-sizing: border-box;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-textarea {
            width: 100%;
            min-height: 200px;
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--text-base);
            color: var(--text-primary);
            background: var(--bg-primary);
            transition: var(--transition-fast);
            resize: vertical;
            box-sizing: border-box;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-description {
            margin-top: var(--spacing-xs);
            font-size: var(--text-xs);
            color: var(--gray-500);
        }
        
        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-md);
            margin-top: var(--spacing-xl);
        }
        
        .cancel-btn {
            padding: var(--spacing-sm) var(--spacing-lg);
            background: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .cancel-btn:hover {
            background: var(--gray-200);
        }
        
        .save-btn {
            padding: var(--spacing-sm) var(--spacing-lg);
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .save-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .examples-section {
            margin-top: var(--spacing-xl);
        }
        
        .examples-title {
            font-size: var(--text-base);
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: var(--spacing-md);
        }
        
        .examples-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .example-item {
            padding: var(--spacing-sm);
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: var(--text-sm);
            color: var(--gray-700);
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .example-item:hover {
            background: var(--gray-100);
            border-color: var(--gray-300);
        }
        
        .tips-title {
            font-size: var(--text-base);
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: var(--spacing-md);
        }
        
        .tips-list {
            list-style-type: disc;
            padding-left: var(--spacing-xl);
            margin-bottom: 0;
        }
        
        .tips-list li {
            margin-bottom: var(--spacing-xs);
            font-size: var(--text-sm);
            color: var(--gray-700);
        }
        
        /* Modal */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
        }
        
        .modal {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: var(--spacing-md) var(--spacing-lg);
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: var(--text-lg);
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: var(--text-xl);
            color: var(--gray-500);
            cursor: pointer;
            transition: var(--transition-fast);
        }
        
        .modal-close:hover {
            color: var(--gray-800);
        }
        
        .modal-body {
            padding: var(--spacing-lg);
        }
        
        .modal-footer {
            padding: var(--spacing-md) var(--spacing-lg);
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: var(--spacing-md);
        }
        
        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            z-index: 1001;
            display: none;
        }
        
        .notification-success {
            background: #d1fae5;
            border-left: 4px solid #059669;
            color: #065f46;
        }
        
        .notification-error {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }
        
        .debug-info {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            z-index: 9999;
        }
        
        @media (max-width: 768px) {
            .instructions-container {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: var(--spacing-md);
            }
            
            .instruction-footer {
                flex-direction: column;
            }
            
            .apply-btn, .new-chat-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Debug info -->
    <div class="debug-info" id="debugInfo">
        Status: Loading...
    </div>
    
    <!-- Global notification element -->
    <div id="notification" class="notification" style="display:none;"></div>
    
    <div class="container">
        <div class="header">
            <h1><?php echo $lang['custom_instructions']; ?></h1>
            <a href="/chat/chat.php" class="back-btn">← <?php echo $lang['back_to_chat']; ?></a>
        </div>
        
        <div class="instructions-container">
            <div class="instructions-list">
                <div class="instructions-list-header">
                    <h2 class="instructions-list-title"><?php echo $lang['my_instructions']; ?></h2>
                    <button class="new-instruction-btn" id="newInstructionBtn"><?php echo $lang['create_instruction']; ?></button>
                </div>
                <div class="instructions-list-body" id="instructionsList">
                    <div class="no-instructions" id="noInstructions">
                        <?php echo $lang['no_instructions']; ?>
                    </div>
                </div>
            </div>
            
            <div class="instruction-form" id="instructionForm" style="display: none;">
                <div class="instruction-form-header">
                    <h2 class="instruction-form-title" id="formTitle"><?php echo $lang['create_instruction']; ?></h2>
                </div>
                <div class="instruction-form-body">
                    <form id="customInstructionForm">
                        <input type="hidden" id="instructionId" value="">
                        
                        <div class="form-group">
                            <label for="instructionTitle" class="form-label"><?php echo $lang['instruction_title']; ?></label>
                            <input type="text" id="instructionTitle" class="form-input" placeholder="<?php echo $lang['enter_instruction_title']; ?>" required maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label for="instructionContent" class="form-label"><?php echo $lang['instruction_content']; ?></label>
                            <textarea id="instructionContent" class="form-textarea" placeholder="<?php echo $lang['enter_instruction_content']; ?>" required></textarea>
                            <div class="form-description"><?php echo $lang['instruction_description']; ?></div>
                        </div>
                        
                        <div class="examples-section">
                            <h3 class="examples-title"><?php echo $lang['instruction_examples']; ?></h3>
                            <div class="examples-list">
                                <div class="example-item" data-example-id="1"><?php echo $lang['instruction_example_1']; ?></div>
                                <div class="example-item" data-example-id="2"><?php echo $lang['instruction_example_2']; ?></div>
                                <div class="example-item" data-example-id="3"><?php echo $lang['instruction_example_3']; ?></div>
                            </div>
                            
                            <h3 class="tips-title"><?php echo $lang['instruction_tips']; ?></h3>
                            <ul class="tips-list">
                                <li><?php echo $lang['instruction_tip_1']; ?></li>
                                <li><?php echo $lang['instruction_tip_2']; ?></li>
                                <li><?php echo $lang['instruction_tip_3']; ?></li>
                                <li><?php echo $lang['instruction_tip_4']; ?></li>
                            </ul>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="button" class="cancel-btn" id="cancelBtn"><?php echo $lang['cancel']; ?></button>
                            <button type="button" class="save-btn" id="saveBtn"><?php echo $lang['save']; ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Debug function
        function updateDebug(message) {
            const debugEl = document.getElementById('debugInfo');
            if (debugEl) {
                debugEl.textContent = message;
            }
            console.log('DEBUG:', message);
        }
        
        // API base path
        const API_BASE = '/chat/api';
        // Global state
        let instructions = [];
        let currentInstructionId = null;
        
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            updateDebug('DOM Loaded');
            
            try {
                // Get the button
                const newBtn = document.getElementById('newInstructionBtn');
                const instructionForm = document.getElementById('instructionForm');
                
                if (!newBtn) {
                    updateDebug('ERROR: Button not found!');
                    return;
                }
                
                if (!instructionForm) {
                    updateDebug('ERROR: Form not found!');
                    return;
                }
                
                updateDebug('Elements found, attaching listeners');
                
                // Add click listener with multiple methods
                newBtn.addEventListener('click', function(e) {
                    updateDebug('Button clicked via addEventListener');
                    e.preventDefault();
                    showInstructionForm();
                });
                
                // Also add onclick as backup
                newBtn.onclick = function(e) {
                    updateDebug('Button clicked via onclick');
                    e.preventDefault();
                    showInstructionForm();
                };
                
                // Add other listeners
                const cancelBtn = document.getElementById('cancelBtn');
                const saveBtn = document.getElementById('saveBtn');
                
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        resetForm(true);
                    });
                }
                
                if (saveBtn) {
                    saveBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        saveInstruction();
                    });
                }
                
                // Add example click listeners
                const examples = document.querySelectorAll('.example-item');
                examples.forEach(example => {
                    example.addEventListener('click', function() {
                        const exampleId = this.dataset.exampleId;
                        fillExample(parseInt(exampleId));
                    });
                });
                
                updateDebug('All listeners attached successfully');
                loadInstructions();
                
            } catch (error) {
                updateDebug('ERROR in initialization: ' + error.message);
                console.error('Initialization error:', error);
            }
        });
        
        function showInstructionForm() {
            updateDebug('showInstructionForm called');
            
            try {
                const form = document.getElementById('instructionForm');
                if (!form) {
                    updateDebug('ERROR: Form element not found');
                    return;
                }
                
                // Reset form
                resetForm(false);
                
                // Show form
                form.style.display = 'block';
                
                // Update title
                const formTitle = document.getElementById('formTitle');
                if (formTitle) {
                    formTitle.textContent = 'Create Instruction';
                }
                
                // Scroll to form
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                updateDebug('Form shown successfully');
                
            } catch (error) {
                updateDebug('ERROR in showInstructionForm: ' + error.message);
                console.error('showInstructionForm error:', error);
            }
        }
        
        function resetForm(hide = true) {
            updateDebug('resetForm called, hide=' + hide);
            
            try {
                const form = document.getElementById('customInstructionForm');
                const instructionId = document.getElementById('instructionId');
                const instructionForm = document.getElementById('instructionForm');
                
                if (form) form.reset();
                if (instructionId) instructionId.value = '';
                currentInstructionId = null;
                
                if (hide && instructionForm) {
                    instructionForm.style.display = 'none';
                }
                
                updateDebug('Form reset successfully');
                
            } catch (error) {
                updateDebug('ERROR in resetForm: ' + error.message);
                console.error('resetForm error:', error);
            }
        }
        
        function fillExample(exampleId) {
            updateDebug('fillExample called with ID: ' + exampleId);
            
            const titleField = document.getElementById('instructionTitle');
            const contentField = document.getElementById('instructionContent');
            
            if (!titleField || !contentField) {
                updateDebug('ERROR: Form fields not found');
                return;
            }
            
            let title = '', content = '';
            
            switch (exampleId) {
                case 1:
                    title = 'Travel Planning Assistant';
                    content = 'You are a travel planning assistant. Your goal is to help users plan budget-friendly trips, finding affordable transportation, accommodations, and activities. Suggest itineraries, cost-saving tips, and hidden gems that are not expensive tourist traps. Always prioritize value for money and authentic experiences.';
                    break;
                case 2:
                    title = 'Coding Mentor';
                    content = 'You are a coding mentor specializing in helping users solve programming problems. Explain concepts clearly, provide step-by-step guidance, and suggest debugging approaches. Don\'t just give answers, but help users understand the underlying principles. You can provide code examples in various languages but focus on helping users develop their problem-solving skills.';
                    break;
                case 3:
                    title = 'Fitness Coach';
                    content = 'You are a fitness coach who creates personalized workout plans. Ask about users\' fitness level, goals, available equipment, and any physical limitations. Provide detailed exercise routines with proper form instructions. Suggest modifications for different fitness levels and offer nutrition advice that complements the workout plan. Always emphasize safety and proper technique.';
                    break;
            }
            
            titleField.value = title;
            contentField.value = content;
            
            updateDebug('Example filled successfully');
        }
        
        async function loadInstructions() {
            updateDebug('Loading instructions from server');
            try {
                const resp = await fetch(`${API_BASE}/get_instructions.php?ts=${Date.now()}`, { cache: 'no-store' });
                const data = await resp.json();
                if (data.success) {
                    instructions = data.instructions ?? [];
                } else {
                    instructions = [];
                    showNotification(data.message || 'Failed to load instructions', 'error');
                }
            } catch (err) {
                instructions = [];
                console.error(err);
                showNotification('Network error while loading instructions', 'error');
            }
            renderInstructions();
            updateDebug(`Instructions loaded: ${instructions.length}`);
        }
        
        function renderInstructions() {
            const list = document.getElementById('instructionsList');
            const noInstructions = document.getElementById('noInstructions');
            
            if (!list || !noInstructions) {
                updateDebug('ERROR: List elements not found');
                return;
            }
            
            list.innerHTML = '';
            
            if (instructions.length === 0) {
                list.appendChild(noInstructions);
                noInstructions.style.display = 'block';
            } else {
                noInstructions.style.display = 'none';
                
                instructions.forEach(instruction => {
                    const card = document.createElement('div');
                    card.className = 'instruction-card';
                    card.innerHTML = `
                        <div class="instruction-card-header">
                            <h3 class="instruction-title">${escapeHtml(instruction.title)}</h3>
                            <div class="instruction-actions">
                                <button class="action-btn edit-btn" onclick="editInstruction(${instruction.id})" title="Edit">✏️</button>
                                <button class="action-btn delete-btn" onclick="deleteInstruction(${instruction.id})" title="Delete">🗑️</button>
                            </div>
                        </div>
                        <div class="instruction-preview">${escapeHtml(instruction.instructions)}</div>
                        <div class="instruction-footer">
                                                        <button class="new-chat-btn" onclick="createChatWithInstruction(${instruction.id})">New Chat with Instruction</button>
                        </div>
                    `;
                    list.appendChild(card);
                });
            }
            
            updateDebug('Instructions rendered: ' + instructions.length + ' items');
        }
        
        function editInstruction(id) {
            updateDebug('editInstruction called with ID: ' + id);
            const instr = instructions.find(i => i.id === id);
            if (!instr) return;
            currentInstructionId = id;
            document.getElementById('instructionId').value = id;
            document.getElementById('instructionTitle').value = instr.title;
            document.getElementById('instructionContent').value = instr.instructions;
            const formTitle = document.getElementById('formTitle');
            if (formTitle) formTitle.textContent = 'Edit Instruction';
            document.getElementById('instructionForm').style.display = 'block';
            document.getElementById('instructionForm').scrollIntoView({behavior:'smooth'});
        }
        
        async function deleteInstruction(id) {
            updateDebug('deleteInstruction called with ID: ' + id);
            if (!confirm('Are you sure you want to delete this instruction?')) return;
            try {
                const resp = await fetch(`${API_BASE}/delete_instruction.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ instruction_id: id })
                });
                const data = await resp.json();
                if (data.success) {
                    showNotification('Deleted successfully', 'success');
                    // remove locally
                    instructions = instructions.filter(i => i.id !== id);
                    renderInstructions();
                } else {
                    showNotification(data.message || 'Delete failed', 'error');
                }
            } catch (err) {
                console.error(err);
                showNotification('Network error while deleting', 'error');
            }
        }
        
        // applyInstruction deprecated — kept for backward compatibility but no UI button
        async function applyInstruction(id) {
            updateDebug('applyInstruction called with ID: ' + id);
            const sessionId = prompt('Enter chat session ID to apply to (leave blank to cancel):');
            if (!sessionId) return;
            try {
                const resp = await fetch(`${API_BASE}/update_chat_instruction.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: Number(sessionId), custom_instructions_id: id })
                });
                const data = await resp.json();
                if (data.success) {
                    showNotification('Instruction applied to chat', 'success');
                } else {
                    showNotification(data.message || 'Failed to apply', 'error');
                }
            } catch (err) {
                console.error(err);
                showNotification('Network error while applying', 'error');
            }
        }
        
        async function createChatWithInstruction(id) {
            updateDebug('Creating new chat with instruction ' + id);
            try {
                const resp = await fetch(`${API_BASE}/create_chat.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ title: instructions.find(i=>i.id===id)?.title || 'Chat', custom_instructions_id: id })
                });
                const data = await resp.json();
                if (data.success) {
                    window.location.href = `/chat/chat.php?session=${data.session_id}`;
                } else {
                    showNotification(data.message || 'Failed to create chat', 'error');
                }
            } catch (err) {
                console.error(err);
                showNotification('Network error while creating chat', 'error');
            }
        }
        
        async function saveInstruction() {
            updateDebug('saveInstruction called');
            const title = document.getElementById('instructionTitle')?.value?.trim();
            const content = document.getElementById('instructionContent')?.value?.trim();
            const instructionId = document.getElementById('instructionId')?.value;
            if (!title || !content) {
                showNotification('Please fill in all fields', 'error');
                return;
            }
            const payload = { title, instructions: content };
            let endpoint = `${API_BASE}/create_instruction.php`;
            if (instructionId) {
                payload.instruction_id = Number(instructionId);
                endpoint = `${API_BASE}/update_instruction.php`;
            }
            try {
                const resp = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await resp.json();
                if (data.success) {
                    showNotification(data.message || 'Saved!', 'success');
                    if (instructionId) {
                        // update existing in local array
                        const idx = instructions.findIndex(i => i.id === Number(instructionId));
                        if (idx !== -1) {
                            instructions[idx].title = title;
                            instructions[idx].instructions = content;
                        }
                    } else {
                        // add newly created
                        instructions.push({ id: data.instruction_id, title, instructions: content });
                    }
                    renderInstructions();
                    resetForm(true);
                } else {
                    showNotification(data.message || 'Error saving', 'error');
                }
            } catch (err) {
                console.error(err);
                showNotification('Network error while saving', 'error');
            }
        }
    
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        if (!notification) return;
        
        notification.textContent = message;
        notification.className = `notification notification-${type}`;
        notification.style.display = 'block';
        
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Make functions global for onclick handlers
    window.editInstruction = editInstruction;
    window.deleteInstruction = deleteInstruction;
    window.applyInstruction = applyInstruction;
    window.createChatWithInstruction = createChatWithInstruction;
    
    // Additional debugging
    setTimeout(() => {
        const btn = document.getElementById('newInstructionBtn');
        updateDebug('Final check - Button exists: ' + (btn ? 'YES' : 'NO'));
        if (btn) {
            updateDebug('Button onclick: ' + (btn.onclick ? 'SET' : 'NOT SET'));
            updateDebug('Button listeners: ' + (btn.addEventListener ? 'SUPPORTED' : 'NOT SUPPORTED'));
        }
    }, 1000);
    
    </script>
</body>
</html>