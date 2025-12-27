/**
 * RESPONSIVE JS - Responsive behaviors and utilities
 * @description Handles responsive design behaviors
 * @version 1.0.0
 */

(function() {
    'use strict';

    const Responsive = {
        /**
         * Breakpoint configuration
         */
        breakpoints: {
            xs: 0,
            sm: 576,
            md: 768,
            lg: 992,
            xl: 1200,
            xxl: 1400
        },

        /**
         * Current breakpoint state
         */
        currentBreakpoint: '',

        /**
         * Initialize responsive behaviors
         */
        init: function() {
            this.detectBreakpoint();
            this.initResponsiveNav();
            this.initViewportHeight();
            this.initTouchEvents();
            this.initOrientationChange();
            
            console.log('Responsive module initialized');
        },

        /**
         * Detect current breakpoint
         */
        detectBreakpoint: function() {
            const width = window.innerWidth;
            let breakpoint = 'xs';

            if (width >= this.breakpoints.xxl) breakpoint = 'xxl';
            else if (width >= this.breakpoints.xl) breakpoint = 'xl';
            else if (width >= this.breakpoints.lg) breakpoint = 'lg';
            else if (width >= this.breakpoints.md) breakpoint = 'md';
            else if (width >= this.breakpoints.sm) breakpoint = 'sm';

            this.currentBreakpoint = breakpoint;
            
            // Add class to body for CSS targeting
            document.body.classList.remove('breakpoint-xs', 'breakpoint-sm', 'breakpoint-md', 
                                         'breakpoint-lg', 'breakpoint-xl', 'breakpoint-xxl');
            document.body.classList.add(`breakpoint-${breakpoint}`);
            
            return breakpoint;
        },

        /**
         * Initialize responsive navigation
         */
        initResponsiveNav: function() {
            const nav = document.querySelector('.navbar-modern-green');
            if (!nav) return;

            // Handle dropdowns on touch devices
            const dropdowns = nav.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                if (this.isTouchDevice()) {
                    dropdown.addEventListener('click', function(e) {
                        if (window.innerWidth < 992) {
                            e.preventDefault();
                            this.classList.toggle('show');
                        }
                    });
                }
            });

            // Auto-close mobile menu on resize
            window.addEventListener('resize', this.debounce(() => {
                if (window.innerWidth >= 992) {
                    const mobileMenu = document.querySelector('.main-nav');
                    if (mobileMenu) mobileMenu.classList.remove('show');
                }
            }, 250));
        },

        /**
         * Set viewport height for mobile devices
         */
        initViewportHeight: function() {
            // Fix for mobile viewport height
            const setVh = () => {
                const vh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--vh', `${vh}px`);
            };

            setVh();
            window.addEventListener('resize', setVh);
            window.addEventListener('orientationchange', setVh);
        },

        /**
         * Initialize touch event optimizations
         */
        initTouchEvents: function() {
            // Remove hover effects on touch devices
            if (this.isTouchDevice()) {
                document.body.classList.add('touch-device');
                
                // Prevent double-tap zoom on buttons
                const buttons = document.querySelectorAll('.btn-modern-green');
                buttons.forEach(btn => {
                    btn.style.touchAction = 'manipulation';
                });
            } else {
                document.body.classList.add('no-touch');
            }
        },

        /**
         * Handle orientation changes
         */
        initOrientationChange: function() {
            let previousOrientation = window.orientation;
            
            window.addEventListener('orientationchange', () => {
                const currentOrientation = window.orientation;
                
                // Only trigger if orientation actually changed
                if (currentOrientation !== previousOrientation) {
                    previousOrientation = currentOrientation;
                    
                    // Force reflow to fix layout issues
                    document.body.style.display = 'none';
                    document.body.offsetHeight; // Trigger reflow
                    document.body.style.display = '';
                    
                    // Dispatch custom event
                    window.dispatchEvent(new Event('orientationchanged'));
                }
            });
        },

        /**
         * Check if device is touch capable
         */
        isTouchDevice: function() {
            return ('ontouchstart' in window) || 
                   (navigator.maxTouchPoints > 0) || 
                   (navigator.msMaxTouchPoints > 0);
        },

        /**
         * Debounce utility
         */
        debounce: function(func, wait) {
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

        /**
         * Get current device type
         */
        getDeviceType: function() {
            const width = window.innerWidth;
            if (width < this.breakpoints.md) return 'mobile';
            if (width < this.breakpoints.lg) return 'tablet';
            return 'desktop';
        },

        /**
         * Check if element is visible in viewport
         */
        isElementVisible: function(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },

        /**
         * Optimize images for current device
         */
        optimizeImagesForDevice: function() {
            const images = document.querySelectorAll('img[data-src-md], img[data-src-lg]');
            const deviceType = this.getDeviceType();
            
            images.forEach(img => {
                let src = img.src;
                
                if (deviceType === 'mobile' && img.dataset.srcSm) {
                    src = img.dataset.srcSm;
                } else if (deviceType === 'tablet' && img.dataset.srcMd) {
                    src = img.dataset.srcMd;
                } else if (deviceType === 'desktop' && img.dataset.srcLg) {
                    src = img.dataset.srcLg;
                }
                
                if (src !== img.src) {
                    img.src = src;
                }
            });
        }
    };

    // ============================================
    // WINDOW RESIZE HANDLER
    // ============================================
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            Responsive.detectBreakpoint();
            Responsive.optimizeImagesForDevice();
        }, 250);
    });

    // ============================================
    // EXPOSE TO GLOBAL SCOPE
    // ============================================
    window.Responsive = Responsive;

    // ============================================
    // AUTO-INITIALIZE
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        Responsive.init();
    });

})();