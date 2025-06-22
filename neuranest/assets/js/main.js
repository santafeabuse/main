// NeuraNest - Main JavaScript File

// Global app object
const NeuraNest = {
    // Configuration
    config: {
        apiUrl: '/neuranest',
        language: 'ru',
        theme: 'light'
    },
    
    // Initialize the application
    init() {
        this.setupEventListeners();
        this.loadTheme();
        this.loadLanguage();
        this.initializeComponents();
    },
    
    // Setup global event listeners
    setupEventListeners() {
        // Language switcher
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-language]')) {
                this.switchLanguage(e.target.dataset.language);
            }
        });
        
        // Theme switcher
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-theme-toggle]')) {
                this.toggleTheme();
            }
        });
        
        // Sidebar toggle
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-sidebar-toggle]')) {
                this.toggleSidebar();
            }
        });
        
        // Modal handlers
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-modal-open]')) {
                this.openModal(e.target.dataset.modalOpen);
            }
            if (e.target.matches('[data-modal-close]') || e.target.matches('.modal-overlay')) {
                this.closeModal();
            }
        });
        
        // Form validation
        document.addEventListener('submit', (e) => {
            if (e.target.matches('form[data-validate]')) {
                if (!this.validateForm(e.target)) {
                    e.preventDefault();
                }
            }
        });
        
        // Auto-resize textareas
        document.addEventListener('input', (e) => {
            if (e.target.matches('textarea[data-auto-resize]')) {
                this.autoResizeTextarea(e.target);
            }
        });
    },
    
    // Theme management
    loadTheme() {
        const savedTheme = localStorage.getItem('neuranest-theme') || 'light';
        this.setTheme(savedTheme);
    },
    
    setTheme(theme) {
        this.config.theme = theme;
        document.body.classList.toggle('dark-mode', theme === 'dark');
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('neuranest-theme', theme);
        
        // Update theme toggle button
        const toggleBtn = document.querySelector('[data-theme-toggle]');
        if (toggleBtn) {
            toggleBtn.innerHTML = theme === 'dark' ? '☀️' : '🌙';
        }
    },
    
    toggleTheme() {
        const newTheme = this.config.theme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
    },
    
    // Language management
    loadLanguage() {
        const savedLang = localStorage.getItem('neuranest-language') || 'ru';
        this.config.language = savedLang;
    },
    
    switchLanguage(lang) {
        if (lang !== this.config.language) {
            localStorage.setItem('neuranest-language', lang);
            window.location.href = `${window.location.pathname}?lang=${lang}`;
        }
    },
    
    // Sidebar management
    toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        
        if (sidebar) {
            sidebar.classList.toggle('open');
        }
        
        if (mainContent) {
            mainContent.classList.toggle('sidebar-open');
        }
    },
    
    // Modal management
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    },
    
    closeModal() {
        const activeModal = document.querySelector('.modal-overlay.active');
        if (activeModal) {
            activeModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    },
    
    // Form validation
    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            if (!this.validateInput(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    validateInput(input) {
        const value = input.value.trim();
        const type = input.type;
        let isValid = true;
        let errorMessage = '';
        
        // Clear previous errors
        this.clearInputError(input);
        
        // Required validation
        if (input.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Это поле обязательно для заполнения';
        }
        
        // Email validation
        else if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Введите корректный email адрес';
            }
        }
        
        // Password validation
        else if (type === 'password' && value) {
            if (value.length < 8) {
                isValid = false;
                errorMessage = 'Пароль должен содержать минимум 8 символов';
            }
        }
        
        // Confirm password validation
        else if (input.name === 'confirm_password' && value) {
            const passwordInput = input.form.querySelector('input[name="password"]');
            if (passwordInput && value !== passwordInput.value) {
                isValid = false;
                errorMessage = 'Пароли не совпадают';
            }
        }
        
        // Show error if validation failed
        if (!isValid) {
            this.showInputError(input, errorMessage);
        }
        
        return isValid;
    },
    
    showInputError(input, message) {
        input.classList.add('error');
        
        let errorElement = input.parentNode.querySelector('.form-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-error';
            input.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    },
    
    clearInputError(input) {
        input.classList.remove('error');
        const errorElement = input.parentNode.querySelector('.form-error');
        if (errorElement) {
            errorElement.remove();
        }
    },
    
    // Auto-resize textarea
    autoResizeTextarea(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    },
    
    // Initialize components
    initializeComponents() {
        // Initialize tooltips
        this.initTooltips();
        
        // Initialize dropdowns
        this.initDropdowns();
        
        // Initialize file uploads
        this.initFileUploads();
        
        // Initialize animations
        this.initAnimations();
    },
    
    // Tooltip initialization
    initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    },
    
    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        
        setTimeout(() => tooltip.classList.add('show'), 10);
    },
    
    hideTooltip() {
        const tooltip = document.querySelector('.tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    },
    
    // Dropdown initialization
    initDropdowns() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-dropdown-toggle]')) {
                const dropdownId = e.target.dataset.dropdownToggle;
                const dropdown = document.getElementById(dropdownId);
                if (dropdown) {
                    dropdown.classList.toggle('show');
                }
            } else {
                // Close all dropdowns when clicking outside
                document.querySelectorAll('.dropdown.show').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });
    },
    
    // File upload initialization
    initFileUploads() {
        const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFilePreview(e.target);
            });
        });
    },
    
    handleFilePreview(input) {
        const file = input.files[0];
        const previewId = input.dataset.preview;
        const preview = document.getElementById(previewId);
        
        if (file && preview) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
    },
    
    // Animation initialization
    initAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);
        
        // Observe elements with animation classes
        document.querySelectorAll('[data-animate]').forEach(el => {
            observer.observe(el);
        });
    },
    
    // Utility functions
    utils: {
        // Debounce function
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // Throttle function
        throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },
        
        // Format date
        formatDate(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            
            return new Intl.DateTimeFormat('ru-RU', { ...defaultOptions, ...options })
                .format(new Date(date));
        },
        
        // Copy to clipboard
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.showNotification('Скопировано в буфер обмена', 'success');
            } catch (err) {
                console.error('Failed to copy text: ', err);
                this.showNotification('Ошибка копирования', 'error');
            }
        },
        
        // Show notification
        showNotification(message, type = 'info', duration = 3000) {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => notification.classList.add('show'), 10);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, duration);
        },
        
        // Generate random ID
        generateId(prefix = 'id') {
            return `${prefix}-${Math.random().toString(36).substr(2, 9)}`;
        },
        
        // Sanitize HTML
        sanitizeHtml(str) {
            const temp = document.createElement('div');
            temp.textContent = str;
            return temp.innerHTML;
        },
        
        // Format file size
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    },
    
    // API helper functions
    api: {
        // Make API request
        async request(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            const config = { ...defaultOptions, ...options };
            
            try {
                const response = await fetch(url, config);
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Request failed');
                }
                
                return data;
            } catch (error) {
                console.error('API request failed:', error);
                throw error;
            }
        },
        
        // GET request
        async get(url) {
            return this.request(url, { method: 'GET' });
        },
        
        // POST request
        async post(url, data) {
            return this.request(url, {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },
        
        // PUT request
        async put(url, data) {
            return this.request(url, {
                method: 'PUT',
                body: JSON.stringify(data)
            });
        },
        
        // DELETE request
        async delete(url) {
            return this.request(url, { method: 'DELETE' });
        }
    }
};

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    NeuraNest.init();
});

// Export for use in other scripts
window.NeuraNest = NeuraNest;