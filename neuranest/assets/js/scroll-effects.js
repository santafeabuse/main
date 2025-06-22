/**
 * Cool Scroll Effects 2025 for NeuraNest
 * Advanced scroll animations and interactions
 */

class ScrollEffects {
    constructor() {
        this.init();
    }

    init() {
        this.createScrollProgress();
        this.setupScrollReveal();
        this.setupParticleTrail();
        this.setupTimelineAnimations();
        this.setupParallaxEffects();
        this.bindEvents();
    }

    createScrollProgress() {
        const progressBar = document.createElement('div');
        progressBar.className = 'scroll-progress';
        document.body.appendChild(progressBar);
        this.progressBar = progressBar;
    }

    setupScrollReveal() {
        // Add scroll-reveal class to elements that should animate
        const revealElements = document.querySelectorAll('.glass, .timeline-content, .feature-card');
        revealElements.forEach(el => {
            if (!el.classList.contains('scroll-reveal')) {
                el.classList.add('scroll-reveal');
            }
        });
    }

    setupParticleTrail() {
        let mouseX = 0, mouseY = 0;
        let particles = [];

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;

            // Create particle every few pixels of movement
            if (Math.random() < 0.1) {
                this.createParticle(mouseX, mouseY);
            }
        });
    }

    createParticle(x, y) {
        const particle = document.createElement('div');
        particle.className = 'particle-trail';
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';
        document.body.appendChild(particle);

        // Remove particle after animation
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, 2000);
    }

    setupTimelineAnimations() {
        const timelineItems = document.querySelectorAll('.timeline-item');
        timelineItems.forEach((item, index) => {
            // Add staggered delay
            item.style.transitionDelay = (index * 0.1) + 's';
        });
    }

    setupParallaxEffects() {
        const parallaxElements = document.querySelectorAll('.parallax-element');
        parallaxElements.forEach(el => {
            el.style.transform = 'translateZ(0)';
        });
    }

    updateScrollProgress() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = (scrollTop / scrollHeight) * 100;
        
        if (this.progressBar) {
            this.progressBar.style.width = progress + '%';
        }
    }

    handleScrollReveal() {
        const revealElements = document.querySelectorAll('.scroll-reveal:not(.revealed)');
        
        revealElements.forEach(el => {
            const rect = el.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight * 0.8 && rect.bottom > 0;
            
            if (isVisible) {
                el.classList.add('revealed');
                
                // Add glow effect to special elements
                if (el.classList.contains('glass')) {
                    el.classList.add('glow-on-scroll', 'glowing');
                    setTimeout(() => {
                        el.classList.remove('glowing');
                    }, 1000);
                }
            }
        });
    }

    handleTimelineAnimations() {
        const timelineItems = document.querySelectorAll('.timeline-item:not(.animate-in)');
        
        timelineItems.forEach(item => {
            const rect = item.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight * 0.7 && rect.bottom > 0;
            
            if (isVisible) {
                item.classList.add('animate-in');
            }
        });
    }

    handleParallaxScroll() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.parallax-element');
        
        parallaxElements.forEach((el, index) => {
            const speed = 0.5 + (index * 0.1);
            const yPos = -(scrolled * speed);
            el.style.transform = `translateY(${yPos}px) translateZ(0)`;
        });
    }

    addFloatingAnimation() {
        const floatingElements = document.querySelectorAll('.glass, .timeline-content');
        floatingElements.forEach((el, index) => {
            if (!el.classList.contains('floating')) {
                el.classList.add('floating');
                el.style.animationDelay = (index * 0.5) + 's';
            }
        });
    }

    addTiltEffects() {
        const tiltElements = document.querySelectorAll('.glass, .timeline-content');
        tiltElements.forEach(el => {
            if (!el.classList.contains('tilt-effect')) {
                el.classList.add('tilt-effect');
            }
        });
    }

    bindEvents() {
        let ticking = false;

        const handleScroll = () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    this.updateScrollProgress();
                    this.handleScrollReveal();
                    this.handleTimelineAnimations();
                    this.handleParallaxScroll();
                    ticking = false;
                });
                ticking = true;
            }
        };

        window.addEventListener('scroll', handleScroll, { passive: true });
        
        // Initial call
        handleScroll();
        
        // Add floating and tilt effects after page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                this.addFloatingAnimation();
                this.addTiltEffects();
            }, 1000);
        });

        // Intersection Observer for better performance
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        if (entry.target.classList.contains('timeline-item')) {
                            entry.target.classList.add('animate-in');
                        }
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -20% 0px'
            });

            // Observe all reveal elements
            document.querySelectorAll('.scroll-reveal, .timeline-item').forEach(el => {
                observer.observe(el);
            });
        }
    }
}

// Initialize scroll effects when DOM is ready
function initScrollEffects() {
    if (!window.scrollEffects) {
        window.scrollEffects = new ScrollEffects();
        console.log('Cool scroll effects 2025 initialized!');
    }
}

document.addEventListener('DOMContentLoaded', initScrollEffects);

// Fallback initialization
if (document.readyState === 'loading') {
    // Document still loading, wait for DOMContentLoaded
} else {
    // Document already loaded, initialize immediately
    initScrollEffects();
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ScrollEffects;
}

