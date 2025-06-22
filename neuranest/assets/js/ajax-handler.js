/**
 * AJAX Handler for NeuraNest
 * Handles all AJAX requests and form submissions
 */

class AjaxHandler {
    constructor() {
        // Use relative paths to avoid URL issues
        this.baseUrl = '../'; // Go up one level from auth/ to root
        this.currentLanguage = 'ru';
        this.translations = {};
        this.init();
    }

    init() {
        this.setupLanguageSwitcher();
        this.setupFormHandlers();
        this.loadCurrentLanguage();
    }

    // Language switching without page reload
    setupLanguageSwitcher() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.lang-btn')) {
                e.preventDefault();
                const lang = e.target.getAttribute('href').split('=')[1];
                this.switchLanguage(lang);
            }
        });
    }

    async switchLanguage(lang) {
        try {
            this.showLoading();
            
            const url = `${this.baseUrl}api/language.php`;
            console.log('Switching language to:', lang);
            console.log('Request URL:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ lang })
            });

            const data = await response.json();

            if (data.success) {
                this.currentLanguage = data.language;
                this.translations = data.translations;
                this.updatePageLanguage();
                this.updateLanguageButtons();
                
                // Show notification in the new language using translations
                const successMessage = this.translations['language_changed_successfully'] || 
                                     (lang === 'ru' ? 'Язык успешно изменен' : 'Language changed successfully');
                this.showNotification(successMessage, 'success');
            } else {
                const errorMessage = this.translations['language_change_error'] || 
                                   (lang === 'ru' ? 'Ошибка смены языка' : 'Error switching language');
                this.showNotification(data.message || errorMessage, 'error');
            }
        } catch (error) {
            console.error('Language switch error:', error);
            const errorMessage = this.translations['language_change_error'] || 
                               (lang === 'ru' ? 'Ошибка смены языка' : 'Error switching language');
            this.showNotification(errorMessage, 'error');
        } finally {
            this.hideLoading();
        }
    }

    async loadCurrentLanguage() {
        try {
            const url = `${this.baseUrl}api/language.php`;
            console.log('Loading current language from:', url);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.currentLanguage = data.language;
                this.translations = data.translations;
                this.updatePageLanguage();
                this.updateLanguageButtons();
            }
        } catch (error) {
            console.error('Error loading language:', error);
        }
    }

    updatePageLanguage() {
        // Update elements with translation keys (supports both legacy data-translate and current data-lang-key)
        const elementsToTranslate = document.querySelectorAll('[data-translate], [data-lang-key]');
        elementsToTranslate.forEach(element => {
            const key = element.getAttribute('data-translate') || element.getAttribute('data-lang-key');
            if (key && this.translations[key]) {
                if (element.tagName === 'INPUT' && element.type === 'submit') {
                    element.value = this.translations[key];
                } else {
                    element.textContent = this.translations[key];
                }
            }
        });

        // Update placeholders separately (supports legacy and new attributes)
        const placeholderElements = document.querySelectorAll('[data-translate-placeholder], [data-lang-placeholder]');
        placeholderElements.forEach(element => {
            const key = element.getAttribute('data-translate-placeholder') || element.getAttribute('data-lang-placeholder');
            if (key && this.translations[key]) {
                element.placeholder = this.translations[key];
            }
        });

        // Update document language
        document.documentElement.lang = this.currentLanguage;

        // Update page title if needed
        const titleElement = document.querySelector('title');
        if (titleElement && titleElement.getAttribute('data-translate')) {
            const key = titleElement.getAttribute('data-translate');
            if (this.translations[key]) {
                titleElement.textContent = this.translations[key];
            }
        }
    }

    updateLanguageButtons() {
        document.querySelectorAll('.lang-btn').forEach(btn => {
            const btnLang = btn.getAttribute('href').split('=')[1];
            btn.classList.toggle('active', btnLang === this.currentLanguage);
        });
    }

    // Form handling with AJAX
    setupFormHandlers() {
        // Login form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleLogin(loginForm);
            });
        }

        // Register form
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleRegister(registerForm);
            });
        }

        // Other forms can be added here
    }

    async handleLogin(form) {
        try {
            this.showFormLoading(form);
            
            const formData = new FormData(form);
            const data = {
                action: 'login',
                email: formData.get('email'),
                password: formData.get('password'),
                remember: formData.get('remember') ? true : false,
                csrf_token: formData.get('csrf_token')
            };

            const url = `${this.baseUrl}api/auth.php`;
            console.log('Login request URL:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification(result.message, 'success');
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1000);
            } else {
                this.showNotification(result.message, 'error');
                this.showFormError(form, result.message);
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showNotification('Login error occurred', 'error');
        } finally {
            this.hideFormLoading(form);
        }
    }

    async handleRegister(form) {
        try {
            this.showFormLoading(form);
            
            const formData = new FormData(form);
            const data = {
                action: 'register',
                email: formData.get('email'),
                password: formData.get('password'),
                confirm_password: formData.get('confirm_password'),
                terms_agreed: formData.get('terms_agreed') ? true : false,
                csrf_token: formData.get('csrf_token')
            };

            const url = `${this.baseUrl}api/auth.php`;
            console.log('Register request URL:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification(result.message, 'success');
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1000);
            } else {
                this.showNotification(result.message, 'error');
                this.showFormError(form, result.message);
            }
        } catch (error) {
            console.error('Register error:', error);
            this.showNotification('Registration error occurred', 'error');
        } finally {
            this.hideFormLoading(form);
        }
    }

    // UI Helper methods
    showLoading() {
        let loader = document.getElementById('global-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'global-loader';
            loader.className = 'global-loader';
            loader.innerHTML = '<div class="loader-spinner"></div>';
            document.body.appendChild(loader);
        }
        loader.style.display = 'flex';
    }

    hideLoading() {
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    showFormLoading(form) {
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            
            const originalText = submitBtn.textContent || submitBtn.value;
            submitBtn.setAttribute('data-original-text', originalText);
            
            if (submitBtn.tagName === 'BUTTON') {
                submitBtn.innerHTML = '<span class="spinner"></span> Loading...';
            } else {
                submitBtn.value = 'Loading...';
            }
        }
    }

    hideFormLoading(form) {
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
            
            const originalText = submitBtn.getAttribute('data-original-text');
            if (originalText) {
                if (submitBtn.tagName === 'BUTTON') {
                    submitBtn.textContent = originalText;
                } else {
                    submitBtn.value = originalText;
                }
            }
        }
    }

    showFormError(form, message) {
        // Remove existing error alerts
        const existingAlerts = form.querySelectorAll('.alert-error');
        existingAlerts.forEach(alert => alert.remove());

        // Create new error alert
        const alert = document.createElement('div');
        alert.className = 'alert alert-error';
        alert.textContent = message;

        // Insert at the beginning of the form
        form.insertBefore(alert, form.firstChild);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    showNotification(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Add to notification container or body
        let container = document.getElementById('notification-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
        
        container.appendChild(notification);
        
        // Show notification
        setTimeout(() => notification.classList.add('show'), 10);
        
        // Hide and remove notification
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    // Utility methods
    translate(key) {
        return this.translations[key] || key;
    }

    getCurrentLanguage() {
        return this.currentLanguage;
    }

    getTranslations() {
        return this.translations;
    }
}

// Initialize AJAX handler when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.ajaxHandler = new AjaxHandler();
});

// Export for use in other scripts
window.AjaxHandler = AjaxHandler;