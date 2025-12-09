/**
 * RESPONSIVE ENHANCEMENTS - ALFARUQ TEAM
 * File: assets/js/responsive.js
 * Author: IT Development Team
 * Created: <?php echo date('Y-m-d'); ?>
 * 
 * Tujuan:
 * 1. Handle responsive behavior yang tidak bisa di CSS
 * 2. Touch device optimizations
 * 3. Mobile-specific interactions
 * 4. Performance optimizations untuk mobile
 * 5. Fallback untuk browser lama
 */

// IIFE (Immediately Invoked Function Expression) untuk prevent global scope pollution
(function() {
    'use strict'; // Strict mode untuk better error handling
    
    console.log('Responsive enhancements script loaded');
    
    // ============================================
    // 1. DOM READY CHECK & INITIALIZATION
    // ============================================
    
    /**
     * Fungsi untuk check jika DOM sudah siap
     * Support untuk semua browser termasuk lama
     */
    function domReady(callback) {
        if (document.readyState === 'loading') {
            // Document masih loading, tambah event listener
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            // Document sudah siap, langsung execute callback
            callback();
        }

        
    }

    function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img.lazy-load');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy-load');
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.1
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback untuk browser lama
        document.querySelectorAll('img.lazy-load').forEach(img => {
            img.src = img.dataset.src;
        });
    }
}
    
    // ============================================
    // 2. MOBILE NAVIGATION ENHANCEMENTS
    // ============================================
    
    /**
     * Enhance mobile navigation menu
     * - Auto-close ketika klik di luar
     * - Touch optimizations
     * - Keyboard navigation support
     */
    function enhanceMobileNavigation() {
        const navbar = document.getElementById('navbarNav');
        const toggler = document.querySelector('.navbar-toggler');
        
        if (!navbar || !toggler) {
            console.warn('Navbar elements not found');
            return;
        }
        
        // 2.1 Close mobile menu ketika klik di luar
        document.addEventListener('click', function(event) {
            // Check jika menu terbuka
            if (navbar.classList.contains('show')) {
                // Check jika klik di luar navbar dan bukan di toggler
                const isClickInsideNavbar = navbar.contains(event.target);
                const isClickOnToggler = toggler.contains(event.target);
                
                if (!isClickInsideNavbar && !isClickOnToggler) {
                    // Close menu menggunakan Bootstrap's collapse API
                    const bsCollapse = bootstrap.Collapse.getInstance(navbar);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            }
        });
        
        // 2.2 Close menu setelah clicking a link (untuk single page)
        const navLinks = navbar.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Hanya close jika ini link anchor (href="#") atau link internal
                if (this.getAttribute('href') && this.getAttribute('href').startsWith('#')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbar);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });
        
        // 2.3 Keyboard navigation support
        navbar.addEventListener('keydown', function(e) {
            // Close dengan ESC key
            if (e.key === 'Escape' && navbar.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getInstance(navbar);
                if (bsCollapse) {
                    bsCollapse.hide();
                    toggler.focus(); // Return focus ke toggler
                }
            }
        });
        
        console.log('Mobile navigation enhanced');
    }
    
    // ============================================
    // 3. TOUCH DEVICE DETECTION & OPTIMIZATIONS
    // ============================================
    
    /**
     * Detect jika device support touch
     * @returns {boolean} true jika touch device
     */
    function isTouchDevice() {
        return ('ontouchstart' in window) || 
               (navigator.maxTouchPoints > 0) || 
               (navigator.msMaxTouchPoints > 0);
    }
    
    /**
     * Apply touch-specific optimizations
     */
    function applyTouchOptimizations() {
        if (!isTouchDevice()) {
            return; // Skip jika bukan touch device
        }
        
        // 3.1 Add touch-device class ke body
        document.body.classList.add('touch-device');
        
        // 3.2 Carousel swipe support
        const carousels = document.querySelectorAll('.carousel');
        
        carousels.forEach(carousel => {
            let touchStartX = 0;
            let touchStartY = 0;
            let touchEndX = 0;
            let touchEndY = 0;
            
            // Touch start event
            carousel.addEventListener('touchstart', function(e) {
                const touch = e.touches[0];
                touchStartX = touch.clientX;
                touchStartY = touch.clientY;
            }, { passive: true }); // passive untuk performance
            
            // Touch end event
            carousel.addEventListener('touchend', function(e) {
                if (!touchStartX || !touchStartY) return;
                
                const touch = e.changedTouches[0];
                touchEndX = touch.clientX;
                touchEndY = touch.clientY;
                
                // Calculate swipe distance
                const diffX = touchStartX - touchEndX;
                const diffY = touchStartY - touchEndY;
                
                // Only process horizontal swipes (ignore vertical scrolls)
                if (Math.abs(diffX) > Math.abs(diffY)) {
                    // Minimum swipe distance threshold
                    if (Math.abs(diffX) > 50) {
                        if (diffX > 0) {
                            // Swipe left - next slide
                            const carouselInstance = bootstrap.Carousel.getInstance(carousel);
                            if (carouselInstance) {
                                carouselInstance.next();
                            }
                        } else {
                            // Swipe right - previous slide
                            const carouselInstance = bootstrap.Carousel.getInstance(carousel);
                            if (carouselInstance) {
                                carouselInstance.prev();
                            }
                        }
                    }
                }
                
                // Reset touch coordinates
                touchStartX = 0;
                touchStartY = 0;
            }, { passive: true });
        });
        
        // 3.3 Disable hover effects on touch devices
        const style = document.createElement('style');
        style.textContent = `
            @media (hover: none) and (pointer: coarse) {
                .card:hover { transform: none !important; }
                .btn:hover { transform: none !important; }
                .nav-link:hover { color: inherit !important; }
            }
        `;
        document.head.appendChild(style);
        
        console.log('Touch optimizations applied');
    }
    
    // ============================================
    // 4. RESPONSIVE TABLE HANDLING
    // ============================================
    
    /**
     * Convert tables to responsive cards di mobile
     * Menambahkan data-labels untuk mobile view
     */
    function makeTablesResponsive() {
        const tables = document.querySelectorAll('table.table:not(.no-responsive)');
        
        if (tables.length === 0) {
            console.log('No tables found for responsive conversion');
            return;
        }
        
        tables.forEach(table => {
            // Skip jika sudah ada wrapper
            if (table.parentElement.classList.contains('table-responsive')) {
                return;
            }
            
            // 4.1 Create wrapper div
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            wrapper.setAttribute('role', 'region');
            wrapper.setAttribute('aria-label', 'Scrollable table');
            wrapper.setAttribute('tabindex', '0');
            
            // 4.2 Wrap the table
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
            
            // 4.3 Add data-labels untuk mobile view
            function addDataLabels() {
                if (window.innerWidth <= 768) {
                    const headers = Array.from(table.querySelectorAll('th'));
                    const rows = table.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        const cells = row.querySelectorAll('td');
                        cells.forEach((cell, index) => {
                            if (headers[index]) {
                                cell.setAttribute('data-label', headers[index].textContent.trim());
                            }
                        });
                    });
                } else {
                    // Remove data-labels di desktop
                    const cells = table.querySelectorAll('td[data-label]');
                    cells.forEach(cell => {
                        cell.removeAttribute('data-label');
                    });
                }
            }
            
            // Initial setup
            addDataLabels();
            
            // Update on resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(addDataLabels, 250); // Debounce
            });
        });
        
        console.log(`Made ${tables.length} tables responsive`);
    }
    
    // ============================================
    // 5. MOBILE FORM VALIDATION ENHANCEMENT
    // ============================================
    
    /**
     * Enhance form validation untuk mobile
     * - Custom validation messages
     * - Better error display
     * - Touch-friendly validation
     */
    function enhanceMobileForms() {
        const forms = document.querySelectorAll('form');
        
        if (forms.length === 0) {
            console.log('No forms found for enhancement');
            return;
        }
        
        forms.forEach(form => {
            // 5.1 Add novalidate attribute untuk mobile
            if (window.innerWidth <= 768) {
                form.setAttribute('novalidate', 'novalidate');
            }
            
            // 5.2 Custom validation handler
            const inputs = form.querySelectorAll('input, select, textarea[required]');
            
            inputs.forEach(input => {
                // Remove browser validation UI
                input.addEventListener('invalid', function(e) {
                    e.preventDefault(); // Prevent default browser UI
                    
                    // 5.3 Custom validation message
                    let message = 'Field ini wajib diisi';
                    
                    if (this.type === 'email') {
                        message = 'Masukkan alamat email yang valid';
                    } else if (this.type === 'tel') {
                        message = 'Masukkan nomor telepon yang valid';
                    } else if (this.type === 'number') {
                        message = 'Masukkan angka yang valid';
                    } else if (this.hasAttribute('minlength')) {
                        const min = this.getAttribute('minlength');
                        message = `Minimum ${min} karakter`;
                    }
                    
                    // 5.4 Show custom error message
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback d-block';
                    feedback.textContent = message;
                    feedback.style.cssText = `
                        font-size: 0.875rem;
                        color: #dc3545;
                        margin-top: 0.25rem;
                        animation: fadeIn 0.3s ease;
                    `;
                    
                    // Remove existing feedback
                    const existing = this.nextElementSibling;
                    if (existing && existing.classList.contains('invalid-feedback')) {
                        existing.remove();
                    }
                    
                    // Add invalid class
                    this.classList.add('is-invalid');
                    
                    // Insert feedback setelah input
                    this.insertAdjacentElement('afterend', feedback);
                    
                    // 5.5 Scroll to invalid field dengan offset untuk navbar
                    const navbarHeight = document.querySelector('#mainNavbar')?.offsetHeight || 70;
                    const inputPosition = this.getBoundingClientRect().top + window.pageYOffset;
                    
                    window.scrollTo({
                        top: inputPosition - navbarHeight - 20,
                        behavior: 'smooth'
                    });
                    
                    // Focus ke field
                    this.focus();
                });
                
                // 5.6 Clear validation on input
                input.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    const feedback = this.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.remove();
                    }
                });
                
                // 5.7 Clear validation on change (untuk select)
                input.addEventListener('change', function() {
                    this.classList.remove('is-invalid');
                    const feedback = this.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.remove();
                    }
                });
            });
        });
        
        console.log(`Enhanced ${forms.length} forms for mobile`);
    }
    
    // ============================================
    // 6. LAZY LOADING FOR MOBILE PERFORMANCE
    // ============================================
    
    /**
     * Implement lazy loading untuk images
     * Meningkatkan performance terutama di mobile
     */
    function lazyLoadImages() {
        // 6.1 Check IntersectionObserver support
        if (!('IntersectionObserver' in window)) {
            console.log('IntersectionObserver not supported, using fallback');
            loadAllImages(); // Fallback
            return;
        }
        
        // 6.2 Create IntersectionObserver
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    
                    // 6.3 Handle regular <img> elements
                    if (element.tagName === 'IMG' && element.dataset.src) {
                        element.src = element.dataset.src;
                        delete element.dataset.src;
                        element.classList.add('lazy-loaded');
                    }
                    
                    // 6.4 Handle background images
                    else if (element.dataset.bg) {
                        element.style.backgroundImage = `url('${element.dataset.bg}')`;
                        delete element.dataset.bg;
                        element.classList.add('lazy-loaded');
                    }
                    
                    // 6.5 Unobserve setelah load
                    observer.unobserve(element);
                }
            });
        }, {
            rootMargin: '100px 0px', // Start loading 100px sebelum masuk viewport
            threshold: 0.01          // Minimal 1% visibility
        });
        
        // 6.6 Observe semua lazy images
        const lazyImages = document.querySelectorAll('img[data-src], [data-bg]');
        
        if (lazyImages.length === 0) {
            console.log('No lazy images found');
            return;
        }
        
        lazyImages.forEach(image => {
            // Add loading state class
            image.classList.add('lazy-loading');
            
            // Observe image
            imageObserver.observe(image);
        });
        
        console.log(`Lazy loading ${lazyImages.length} images`);
        
        // 6.7 Fallback function untuk browser lama
        function loadAllImages() {
            const images = document.querySelectorAll('img[data-src]');
            images.forEach(img => {
                img.src = img.dataset.src;
            });
            
            const bgElements = document.querySelectorAll('[data-bg]');
            bgElements.forEach(el => {
                el.style.backgroundImage = `url('${el.dataset.bg}')`;
            });
        }
    }
    
    // ============================================
    // 7. VIEWPORT HEIGHT FIX FOR MOBILE BROWSERS
    // ============================================
    
    /**
     * Fix 100vh issue di mobile browsers
     * Address masalah address bar yang hide/show
     */
    function fixViewportHeight() {
        // 7.1 Set custom CSS variable untuk viewport height
        function setViewportHeight() {
            // Dapatkan actual viewport height
            const vh = window.innerHeight * 0.01;
            
            // Set CSS variable
            document.documentElement.style.setProperty('--vh', `${vh}px`);
            
            // Apply ke elements yang perlu fixed height
            const fullHeightElements = document.querySelectorAll('[data-full-height]');
            fullHeightElements.forEach(el => {
                el.style.height = `calc(var(--vh, 1vh) * 100)`;
            });
        }
        
        // 7.2 Initial set
        setViewportHeight();
        
        // 7.3 Update on resize dan orientation change
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(setViewportHeight, 150);
        });
        
        window.addEventListener('orientationchange', () => {
            setTimeout(setViewportHeight, 100);
        });
        
        console.log('Viewport height fix applied');
    }
    
    // ============================================
    // 8. MOBILE-ONLY FEATURES
    // ============================================
    
    /**
     * Features yang hanya aktif di mobile
     */
    function mobileOnlyFeatures() {
        // Hanya jalankan di mobile
        if (window.innerWidth > 768) return;
        
        // 8.1 Add pull-to-refresh prevention (optional)
        let touchStartY = 0;
        
        document.addEventListener('touchstart', function(e) {
            touchStartY = e.touches[0].clientY;
        }, { passive: true });
        
        document.addEventListener('touchmove', function(e) {
            // Prevent pull-to-refresh ketika scroll di atas
            if (window.scrollY === 0 && e.touches[0].clientY > touchStartY) {
                e.preventDefault();
            }
        }, { passive: false });
        
        // 8.2 Add mobile-specific event listeners
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            // Add touch feedback
            card.addEventListener('touchstart', function() {
                this.style.transition = 'none';
                this.style.transform = 'scale(0.98)';
            }, { passive: true });
            
            card.addEventListener('touchend', function() {
                this.style.transition = 'transform 0.2s ease';
                this.style.transform = 'scale(1)';
            }, { passive: true });
        });
        
        console.log('Mobile-only features activated');
    }
    
    // ============================================
    // 9. PERFORMANCE MONITORING (DEV ONLY)
    // ============================================
    
    /**
     * Monitoring performance metrics
     * Hanya di development environment
     */
    function monitorPerformance() {
        // Hanya jalankan jika bukan production
        if (window.location.hostname !== 'localhost' && 
            window.location.hostname !== '127.0.0.1') {
            return;
        }
        
        // 9.1 Log performance metrics
        if ('performance' in window) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    const perfData = window.performance.timing;
                    const loadTime = perfData.loadEventEnd - perfData.navigationStart;
                    
                    console.log(`Page loaded in ${loadTime}ms`);
                    
                    // Warning jika load time terlalu lama
                    if (loadTime > 3000) {
                        console.warn('Page load time is slow (>3s)');
                    }
                }, 0);
            });
        }
        
        // 9.2 Log memory usage (Chrome only)
        if ('memory' in window.performance) {
            console.log(`JS heap size: ${Math.round(performance.memory.usedJSHeapSize / 1048576)}MB`);
        }
    }
    
    // ============================================
    // 10. INITIALIZATION & ERROR HANDLING
    // ============================================
    
    /**
     * Initialize semua responsive enhancements
     * Dengan error handling yang baik
     */
    function initializeResponsiveEnhancements() {
        try {
            console.group('Initializing Responsive Enhancements');
            
            // Run semua enhancement functions
            enhanceMobileNavigation();
            applyTouchOptimizations();
            makeTablesResponsive();
            enhanceMobileForms();
            lazyLoadImages();
            fixViewportHeight();
            mobileOnlyFeatures();
            monitorPerformance();
            
            console.log('✅ All responsive enhancements initialized successfully');
            console.groupEnd();
            
        } catch (error) {
            console.error('❌ Error initializing responsive enhancements:', error);
            console.groupEnd();
            
            // Fallback: load semua images langsung
            const images = document.querySelectorAll('img[data-src]');
            images.forEach(img => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
            });
        }
    }
    
    // ============================================
    // 11. EXPORT FUNCTIONS (untuk debugging)
    // ============================================
    
    // Expose functions ke window object untuk debugging
    window.alfaruqResponsive = {
        reinit: initializeResponsiveEnhancements,
        isTouchDevice: isTouchDevice,
        fixViewportHeight: fixViewportHeight,
        lazyLoadImages: lazyLoadImages
    };
    
    // ============================================
    // 12. START EVERYTHING
    // ============================================
    
    // Tunggu DOM ready, lalu initialize
    domReady(function() {
        // Delay sedikit untuk pastikan semua resource loaded
        setTimeout(initializeResponsiveEnhancements, 100);
    });
    
    // Re-initialize on window resize (debounced)
    let reinitTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(reinitTimeout);
        reinitTimeout = setTimeout(function() {
            console.log('Window resized, re-initializing responsive features');
            if (window.alfaruqResponsive) {
                window.alfaruqResponsive.reinit();
            }
        }, 500);
    });
    
})(); // End of IIFE

/* =================================================================
   END OF RESPONSIVE ENHANCEMENTS SCRIPT
   ================================================================= */