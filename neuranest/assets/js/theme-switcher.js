/**
 * Theme Switcher for NeuraNest
 * Handles light/dark theme switching with smooth animations
 */

class ThemeSwitcher {
    constructor() {
        this.currentTheme = this.getStoredTheme() || this.getSystemTheme();
        this.init();
    }

    init() {
        this.applyTheme(this.currentTheme);
        this.bindEvents();
        this.addSwitcherStyles();
    }

    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    getStoredTheme() {
        return localStorage.getItem('neuranest-theme');
    }

    storeTheme(theme) {
        localStorage.setItem('neuranest-theme', theme);
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        this.currentTheme = theme;
        this.storeTheme(theme);
        this.updateSwitcherState();
    }

    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
        this.animateThemeChange();
    }

    animateThemeChange() {
        // Add a subtle animation effect with improved transitions
        document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
        
        // Prevent background jumping by maintaining fixed positioning
        const parallaxElements = document.querySelectorAll('.parallax');
        parallaxElements.forEach(el => {
            el.style.transition = 'none';
        });
        
        // Reset transition after animation
        setTimeout(() => {
            document.body.style.transition = '';
            parallaxElements.forEach(el => {
                el.style.transition = '';
            });
        }, 300);
    }

    createRippleEffect() {
        const ripple = document.createElement('div');
        ripple.className = 'theme-ripple';
        document.body.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    // Method removed - switcher is now only created via addToContainer()

    updateSwitcherState() {
        const switchers = document.querySelectorAll('.theme-switcher');
        switchers.forEach(switcher => {
            switcher.setAttribute('data-theme', this.currentTheme);
        });
    }

    bindEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.theme-toggle')) {
                this.toggleTheme();
            }
        });

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!this.getStoredTheme()) {
                this.applyTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    addSwitcherStyles() {
        if (document.getElementById('theme-switcher-styles')) return;

        const styles = document.createElement('style');
        styles.id = 'theme-switcher-styles';
        styles.textContent = `
            .theme-toggle {
                background: var(--bg-primary);
                border: 2px solid var(--border-color);
                border-radius: 25px;
                padding: 4px;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 2px 8px var(--shadow-color);
                width: 60px;
                height: 32px;
                position: relative;
                overflow: hidden;
            }

            .theme-toggle:hover {
                transform: scale(1.05);
                box-shadow: 0 4px 12px var(--shadow-color);
            }

            .theme-toggle-track {
                width: 100%;
                height: 100%;
                position: relative;
                border-radius: 20px;
                background: var(--bg-secondary);
            }

            .theme-toggle-thumb {
                width: 24px;
                height: 24px;
                background: var(--primary-color);
                border-radius: 50%;
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                left: 2px;
            }

            .theme-switcher[data-theme="dark"] .theme-toggle-thumb {
                left: calc(100% - 26px);
                background: var(--secondary-color);
            }

            .theme-icon {
                font-size: 12px;
                position: absolute;
                transition: all 0.3s ease;
            }

            .theme-icon-light {
                opacity: 1;
                transform: scale(1);
            }

            .theme-icon-dark {
                opacity: 0;
                transform: scale(0);
            }

            .theme-switcher[data-theme="dark"] .theme-icon-light {
                opacity: 0;
                transform: scale(0);
            }

            .theme-switcher[data-theme="dark"] .theme-icon-dark {
                opacity: 1;
                transform: scale(1);
            }

            .theme-ripple {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                pointer-events: none;
                background: radial-gradient(circle at center, var(--primary-color) 0%, transparent 70%);
                opacity: 0;
                animation: ripple 0.6s ease-out;
                z-index: 9999;
            }

            @keyframes ripple {
                0% {
                    opacity: 0;
                    transform: scale(0);
                }
                50% {
                    opacity: 0.1;
                }
                100% {
                    opacity: 0;
                    transform: scale(2);
                }
            }

            /* Mobile adjustments */
            @media (max-width: 768px) {
                .theme-toggle {
                    width: 50px;
                    height: 28px;
                }

                .theme-toggle-thumb {
                    width: 20px;
                    height: 20px;
                }

                .theme-switcher[data-theme="dark"] .theme-toggle-thumb {
                    left: calc(100% - 22px);
                }

                .theme-icon {
                    font-size: 10px;
                }
            }

            /* Integration with existing layouts */
            .profile-nav .theme-switcher,
            .navbar .theme-switcher {
                position: static;
                margin-left: auto;
            }

            .profile-nav .theme-toggle,
            .navbar .theme-toggle {
                width: 50px;
                height: 26px;
            }

            .profile-nav .theme-toggle-thumb,
            .navbar .theme-toggle-thumb {
                width: 18px;
                height: 18px;
            }

            .profile-nav .theme-switcher[data-theme="dark"] .theme-toggle-thumb,
            .navbar .theme-switcher[data-theme="dark"] .theme-toggle-thumb {
                left: calc(100% - 20px);
            }
        `;

        document.head.appendChild(styles);
    }

    // Public method to add switcher to specific container
    addToContainer(container, inline = false) {
        if (!container) {
            console.warn('Theme switcher: container not found');
            return;
        }
        
        const existingSwitcher = container.querySelector('.theme-switcher');
        if (existingSwitcher) {
            console.log('Theme switcher: already exists in container');
            return;
        }

        const switcher = document.createElement('div');
        switcher.className = `theme-switcher ${inline ? 'inline' : ''}`;
        switcher.innerHTML = `
            <button class="theme-toggle" aria-label="Toggle theme">
                <div class="theme-toggle-track">
                    <div class="theme-toggle-thumb">
                        <span class="theme-icon theme-icon-light">☀️</span>
                        <span class="theme-icon theme-icon-dark">🌙</span>
                    </div>
                </div>
            </button>
        `;

        container.appendChild(switcher);
        this.updateSwitcherState();
        console.log('Theme switcher: added to container');
    }
}

// Initialize theme switcher when DOM is loaded
function initializeThemeSwitcher() {
    if (!window.themeSwitcher) {
        console.log('Creating new ThemeSwitcher instance');
        window.themeSwitcher = new ThemeSwitcher();
    } else {
        console.log('ThemeSwitcher already exists');
    }
}

document.addEventListener('DOMContentLoaded', initializeThemeSwitcher);

// Fallback initialization
if (document.readyState === 'loading') {
    // Document still loading, wait for DOMContentLoaded
} else {
    // Document already loaded, initialize immediately
    initializeThemeSwitcher();
}

// Additional safety check
setTimeout(() => {
    if (!window.themeSwitcher) {
        console.warn('ThemeSwitcher not initialized, creating now...');
        initializeThemeSwitcher();
    }
}, 100);

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeSwitcher;
}