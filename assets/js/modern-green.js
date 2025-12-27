/**
 * MODERN GREEN JS - Main JavaScript File
 * @description Core functionality for Alfaruq Travel website
 * @version 1.0.0
 */

(function() {
    'use strict';

    // ============================================
    // GLOBAL CONFIGURATION
    // ============================================
    const CONFIG = {
        scrollOffset: 70,
        animationDuration: 300,
        lazyLoadThreshold: 0.1,
        debounceDelay: 250
    };

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    const Utils = {
        /**
         * Debounce function untuk optimize event listeners
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
         * Throttle function untuk scroll events
         */
        throttle: function(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        /**
         * Check jika element ada di viewport
         */
        isInViewport: function(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },

        /**
         * Smooth scroll ke element
         */
        smoothScrollTo: function(target, duration = 500) {
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset;
            const startPosition = window.pageYOffset;
            const distance = targetPosition - startPosition;
            let startTime = null;

            function animation(currentTime) {
                if (startTime === null) startTime = currentTime;
                const timeElapsed = currentTime - startTime;
                const run = Utils.ease(timeElapsed, startPosition, distance, duration);
                window.scrollTo(0, run);
                if (timeElapsed < duration) requestAnimationFrame(animation);
            }

            function ease(t, b, c, d) {
                t /= d / 2;
                if (t < 1) return c / 2 * t * t + b;
                t--;
                return -c / 2 * (t * (t - 2) - 1) + b;
            }

            requestAnimationFrame(animation);
        },

        /**
         * Format nomor telepon
         */
        formatPhoneNumber: function(phone) {
            return phone.replace(/\D/g, '');
        },

        /**
         * Validasi email
         */
        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        /**
         * Set cookie
         */
        setCookie: function(name, value, days) {
            const expires = new Date(Date.now() + days * 864e5).toUTCString();
            document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/';
        },

        /**
         * Get cookie
         */
        getCookie: function(name) {
            return document.cookie.split('; ').reduce((r, v) => {
                const parts = v.split('=');
                return parts[0] === name ? decodeURIComponent(parts[1]) : r;
            }, '');
        }
    };

    // ============================================
    // MODULES
    // ============================================
    const Modules = {
        /**
         * Mobile Navigation Module
         */
        MobileNavigation: {
            init: function() {
                const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
                const mainNav = document.querySelector('.main-nav');
                const navLinks = document.querySelectorAll('.nav-link');

                if (!mobileMenuBtn || !mainNav) return;

                // Toggle mobile menu
                mobileMenuBtn.addEventListener('click', function() {
                    mainNav.classList.toggle('show');
                    this.setAttribute('aria-expanded', mainNav.classList.contains('show'));
                });

                // Close menu when clicking on links
                navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (mainNav.classList.contains('show')) {
                            mainNav.classList.remove('show');
                            mobileMenuBtn.setAttribute('aria-expanded', 'false');
                        }
                    });
                });

                // Close menu when clicking outside
                document.addEventListener('click', (e) => {
                    if (!mainNav.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                        mainNav.classList.remove('show');
                        mobileMenuBtn.setAttribute('aria-expanded', 'false');
                    }
                });

                console.log('Mobile Navigation initialized');
            }
        },

        /**
         * Smooth Scroll Module
         */
        SmoothScroll: {
            init: function() {
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        const href = this.getAttribute('href');
                        
                        if (href === '#' || href === '#!') return;
                        
                        const target = document.querySelector(href);
                        if (target) {
                            e.preventDefault();
                            Utils.smoothScrollTo(target, 800);
                        }
                    });
                });

                console.log('Smooth Scroll initialized');
            }
        },

        /**
         * Animations Module
         */
        Animations: {
            init: function() {
                // Initialize AOS jika ada
                if (typeof AOS !== 'undefined') {
                    AOS.init({
                        duration: 800,
                        once: true,
                        offset: 100,
                        delay: 50
                    });
                }

                // Animate elements on scroll
                this.initScrollAnimations();
                console.log('Animations initialized');
            },

            initScrollAnimations: function() {
                const animateElements = document.querySelectorAll('.animate-on-scroll');
                
                if (animateElements.length === 0) return;

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animated');
                        }
                    });
                }, {
                    threshold: CONFIG.lazyLoadThreshold
                });

                animateElements.forEach(el => observer.observe(el));
            }
        },

        /**
         * Navbar Scroll Module
         */
        NavbarScroll: {
            init: function() {
                const navbar = document.querySelector('.navbar-modern-green');
                if (!navbar) return;

                let lastScroll = 0;
                const navbarHeight = navbar.offsetHeight;

                const handleScroll = Utils.throttle(() => {
                    const currentScroll = window.pageYOffset;

                    // Add scrolled class
                    if (currentScroll > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }

                    // Auto-hide navbar on scroll down
                    if (currentScroll > lastScroll && currentScroll > navbarHeight) {
                        navbar.style.transform = `translateY(-${navbarHeight}px)`;
                    } else {
                        navbar.style.transform = 'translateY(0)';
                    }

                    lastScroll = currentScroll;
                }, 100);

                window.addEventListener('scroll', handleScroll);
                console.log('Navbar Scroll initialized');
            }
        },

        /**
         * Back to Top Module
         */
        BackToTop: {
            init: function() {
                const backToTopBtn = document.querySelector('.back-to-top-modern');
                if (!backToTopBtn) return;

                const toggleButton = () => {
                    if (window.pageYOffset > 300) {
                        backToTopBtn.style.display = 'flex';
                    } else {
                        backToTopBtn.style.display = 'none';
                    }
                };

                backToTopBtn.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });

                window.addEventListener('scroll', Utils.throttle(toggleButton, 100));
                toggleButton(); // Initial check
                console.log('Back to Top initialized');
            }
        },

        /**
         * Image Optimization Module
         */
        ImageOptimization: {
            init: function() {
                this.initLazyLoading();
                this.initPackageDetailImages();
                console.log('Image Optimization initialized');
            },

            initLazyLoading: function() {
                const images = document.querySelectorAll('img[data-src]');
                
                if ('IntersectionObserver' in window) {
                    const imageObserver = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                img.src = img.dataset.src;
                                img.removeAttribute('data-src');
                                imageObserver.unobserve(img);
                            }
                        });
                    }, {
                        rootMargin: '50px 0px'
                    });

                    images.forEach(img => imageObserver.observe(img));
                } else {
                    // Fallback for older browsers
                    images.forEach(img => {
                        img.src = img.dataset.src;
                    });
                }
            },

            initPackageDetailImages: function() {
                const packageImages = document.querySelectorAll('.package-detail-image');
                
                packageImages.forEach(img => {
                    // Lazy loading enhancement
                    if ('loading' in HTMLImageElement.prototype && !img.loading) {
                        img.loading = 'lazy';
                    }
                    
                    // Error handling
                    img.addEventListener('error', function() {
                        this.classList.add('error');
                        this.alt = 'Gambar tidak dapat dimuat';
                        this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2YwZjBmMCIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjEwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjOTk5Ij5JbWFnZSBFcnJvcjwvdGV4dD48L3N2Zz4=';
                    });
                    
                    // Loading state
                    if (!img.complete) {
                        img.classList.add('loading');
                        img.addEventListener('load', function() {
                            this.classList.remove('loading');
                            this.classList.add('loaded');
                        });
                    }
                    
                    // Optimize image dimensions
                    this.optimizeImageDimensions(img);
                });
            },

            optimizeImageDimensions: function(img) {
                if (!img.complete) {
                    img.addEventListener('load', () => this.applyImageConstraints(img));
                } else {
                    this.applyImageConstraints(img);
                }
            },

            applyImageConstraints: function(img) {
                const container = img.closest('.image-container-modern');
                if (!container) return;
                
                const maxWidth = container.clientWidth - 40; // Account for padding
                const maxHeight = container.clientHeight - 40;
                
                if (img.naturalWidth > maxWidth || img.naturalHeight > maxHeight) {
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '100%';
                    img.style.width = 'auto';
                    img.style.height = 'auto';
                    img.style.objectFit = 'contain';
                }
            }
            
        },

        /**
         * Testimonial Carousel Module
         */
        TestimonialCarousel: {
            init: function() {
                const carousel = document.querySelector('#testimonialCarousel');
                if (!carousel) return;

                // Initialize Bootstrap carousel jika belum
                if (typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
                    new bootstrap.Carousel(carousel, {
                        interval: 5000,
                        wrap: true,
                        touch: true
                    });
                }

                // Custom navigation
                this.initCustomNavigation();
                console.log('Testimonial Carousel initialized');
            },

            initCustomNavigation: function() {
                const prevBtn = document.querySelector('.testimonial-prev');
                const nextBtn = document.querySelector('.testimonial-next');
                
                if (prevBtn && nextBtn) {
                    prevBtn.addEventListener('click', () => {
                        const carousel = bootstrap.Carousel.getInstance('#testimonialCarousel');
                        if (carousel) carousel.prev();
                    });
                    
                    nextBtn.addEventListener('click', () => {
                        const carousel = bootstrap.Carousel.getInstance('#testimonialCarousel');
                        if (carousel) carousel.next();
                    });
                }
            }
        },

        /**
         * Gallery Module
         */
        Gallery: {
            init: function() {
                this.initGalleryFilter();
                this.initGalleryModal();
                console.log('Gallery initialized');
            },

            initGalleryFilter: function() {
                const filterBtns = document.querySelectorAll('.gallery-filter-btn');
                const galleryItems = document.querySelectorAll('.gallery-item-modern');
                
                if (filterBtns.length === 0) return;

                filterBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        // Remove active class from all buttons
                        filterBtns.forEach(b => b.classList.remove('active'));
                        
                        // Add active class to clicked button
                        this.classList.add('active');
                        
                        const filter = this.getAttribute('data-filter');
                        
                        // Filter gallery items
                        galleryItems.forEach(item => {
                            if (filter === 'all' || item.getAttribute('data-category') === filter) {
                                item.style.display = 'block';
                                setTimeout(() => {
                                    item.style.opacity = '1';
                                    item.style.transform = 'scale(1)';
                                }, 10);
                            } else {
                                item.style.opacity = '0';
                                item.style.transform = 'scale(0.8)';
                                setTimeout(() => {
                                    item.style.display = 'none';
                                }, 300);
                            }
                        });
                    });
                });
            },

            initGalleryModal: function() {
                const galleryItems = document.querySelectorAll('.gallery-item-modern[data-bs-toggle="modal"]');
                
                galleryItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const imgSrc = this.querySelector('img').src;
                        const imgAlt = this.querySelector('img').alt;
                        
                        // Update modal content
                        const modalImg = document.querySelector('#galleryModal .modal-body img');
                        if (modalImg) {
                            modalImg.src = imgSrc;
                            modalImg.alt = imgAlt;
                        }
                    });
                });
            }
        },

        /**
         * Form Validation Module (General)
         */
        FormValidation: {
            init: function() {
                // Contact form validation
                if (document.getElementById('contactForm')) {
                    this.initContactForm();
                }
                
                // Testimonial form validation
                if (document.getElementById('testimonialForm')) {
                    this.initTestimonialForm();
                }
                
                console.log('Form Validation initialized');
            },

            initContactForm: function() {
                const form = document.getElementById('contactForm');
                if (!form) return;

                // Phone number formatting
                const phoneInput = form.querySelector('#phone');
                if (phoneInput) {
                    phoneInput.addEventListener('input', function(e) {
                        this.value = this.value.replace(/\D/g, '');
                    });
                }

                // Real-time validation
                const fields = form.querySelectorAll('.form-control-modern');
                fields.forEach(field => {
                    field.addEventListener('blur', () => this.validateField(field));
                    field.addEventListener('input', () => this.clearValidation(field));
                });

                // Form submission
                form.addEventListener('submit', (e) => this.handleFormSubmit(e, form));
            },

            initTestimonialForm: function() {
                const form = document.getElementById('testimonialForm');
                if (!form) return;

                // Star rating
                const stars = form.querySelectorAll('.star-label');
                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        const rating = this.getAttribute('data-value');
                        form.querySelector('input[name="rating"]').value = rating;
                        
                        // Update star display
                        stars.forEach(s => {
                            if (s.getAttribute('data-value') <= rating) {
                                s.classList.add('active');
                            } else {
                                s.classList.remove('active');
                            }
                        });
                    });
                });

                form.addEventListener('submit', (e) => this.handleFormSubmit(e, form));
            },

            validateField: function(field) {
                let isValid = true;
                let errorMessage = '';

                // Required field validation
                if (field.required && !field.value.trim()) {
                    isValid = false;
                    errorMessage = 'Field ini wajib diisi';
                }

                // Email validation
                if (field.type === 'email' && field.value.trim()) {
                    if (!Utils.isValidEmail(field.value)) {
                        isValid = false;
                        errorMessage = 'Format email tidak valid';
                    }
                }

                // Phone validation
                if (field.type === 'tel' && field.value.trim()) {
                    const phonePattern = /^[0-9]{10,13}$/;
                    if (!phonePattern.test(field.value.replace(/\D/g, ''))) {
                        isValid = false;
                        errorMessage = 'Nomor telepon harus 10-13 digit';
                    }
                }

                // Update field state
                if (!isValid) {
                    this.showError(field, errorMessage);
                } else {
                    this.clearError(field);
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }

                return isValid;
            },

            showError: function(field, message) {
                this.clearError(field);
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = message;
                
                field.parentNode.appendChild(errorDiv);
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
            },

            clearError: function(field) {
                const errorMsg = field.parentNode.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
            },

            clearValidation: function(field) {
                if (field.value.trim()) {
                    field.classList.remove('is-invalid');
                    this.clearError(field);
                }
            },

            handleFormSubmit: function(event, form) {
                event.preventDefault();
                
                const fields = form.querySelectorAll('.form-control-modern[required]');
                let isValid = true;
                
                fields.forEach(field => {
                    if (!this.validateField(field)) {
                        isValid = false;
                    }
                });
                
                if (isValid) {
                    this.showLoading(form);
                    // Simulate form submission
                    setTimeout(() => {
                        this.showSuccess(form, 'Form berhasil dikirim!');
                        form.reset();
                        this.hideLoading(form);
                    }, 1500);
                } else {
                    // Scroll to first error
                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            },

            showLoading: function(form) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...';
                    submitBtn.disabled = true;
                }
            },

            hideLoading: function(form) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Kirim Pesan';
                    submitBtn.disabled = false;
                }
            },

            showSuccess: function(form, message) {
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success success-message mt-3';
                successDiv.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
                
                form.insertBefore(successDiv, form.querySelector('button[type="submit"]').parentNode);
                
                setTimeout(() => {
                    successDiv.remove();
                }, 5000);
            }
        },

        /**
         * WhatsApp Module
         */
        WhatsApp: {
            init: function() {
                this.initWhatsAppButtons();
                console.log('WhatsApp module initialized');
            },

            initWhatsAppButtons: function() {
                const whatsappButtons = document.querySelectorAll('[data-whatsapp]');
                
                whatsappButtons.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        const phone = this.getAttribute('data-whatsapp');
                        const message = this.getAttribute('data-message') || 'Halo, saya ingin berkonsultasi mengenai paket umroh';
                        
                        const encodedMessage = encodeURIComponent(message);
                        const whatsappUrl = `https://wa.me/${Utils.formatPhoneNumber(phone)}?text=${encodedMessage}`;
                        
                        // Open in new tab
                        window.open(whatsappUrl, '_blank');
                    });
                });
            }
        },

        /**
         * Counter Animation Module
         */
        CounterAnimation: {
            init: function() {
                const counters = document.querySelectorAll('.counter');
                if (counters.length === 0) return;

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.animateCounter(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });

                counters.forEach(counter => observer.observe(counter));
                console.log('Counter Animation initialized');
            },

            animateCounter: function(element) {
                const target = parseInt(element.getAttribute('data-target'));
                const duration = 2000; // 2 seconds
                const increment = target / (duration / 16); // 60fps
                let current = 0;

                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        element.textContent = target.toLocaleString() + (element.getAttribute('data-suffix') || '');
                        clearInterval(timer);
                    } else {
                        element.textContent = Math.floor(current).toLocaleString();
                    }
                }, 16);
            }
        }
    };

    // ============================================
    // MAIN APPLICATION
    // ============================================
    const ModernGreen = {
        /**
         * Initialize all modules
         */
        init: function() {
            // Check if DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initModules());
            } else {
                this.initModules();
            }
        },

        /**
         * Initialize individual modules
         */
        initModules: function() {
            console.log('Initializing Modern Green Application...');
            
            // Core modules (always loaded)
            Modules.MobileNavigation.init();
            Modules.SmoothScroll.init();
            Modules.Animations.init();
            Modules.NavbarScroll.init();
            Modules.BackToTop.init();
            Modules.ImageOptimization.init();
            Modules.WhatsApp.init();

            // Conditional modules
            if (document.querySelector('#testimonialCarousel')) {
                Modules.TestimonialCarousel.init();
            }

            if (document.querySelector('.gallery-filter-btn')) {
                Modules.Gallery.init();
            }

            if (document.querySelector('.counter')) {
                Modules.CounterAnimation.init();
            }

            if (document.querySelector('form')) {
                Modules.FormValidation.init();
            }

            // Initialize tooltips
            this.initTooltips();
            
            // Initialize modals
            this.initModals();

            console.log('Modern Green Application initialized successfully');
        },

        /**
         * Initialize Bootstrap tooltips
         */
        initTooltips: function() {
            const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            if (tooltipElements.length > 0 && typeof bootstrap !== 'undefined') {
                tooltipElements.forEach(el => {
                    new bootstrap.Tooltip(el, {
                        trigger: 'hover focus'
                    });
                });
            }
        },

        /**
         * Initialize Bootstrap modals
         */
        initModals: function() {
            const modalElements = document.querySelectorAll('.modal');
            if (modalElements.length > 0 && typeof bootstrap !== 'undefined') {
                modalElements.forEach(el => {
                    el.addEventListener('shown.bs.modal', function() {
                        document.body.classList.add('modal-open');
                    });
                    
                    el.addEventListener('hidden.bs.modal', function() {
                        document.body.classList.remove('modal-open');
                    });
                });
            }
        },

        /**
         * Public API for external use
         */
        API: {
            smoothScroll: Utils.smoothScrollTo,
            formatPhone: Utils.formatPhoneNumber,
            validateEmail: Utils.isValidEmail,
            debounce: Utils.debounce,
            throttle: Utils.throttle
        }
    };

    // ============================================
    // GLOBAL ERROR HANDLING
    // ============================================
    window.addEventListener('error', function(e) {
        console.error('Global error:', e.error);
    });

    window.addEventListener('unhandledrejection', function(e) {
        console.error('Unhandled promise rejection:', e.reason);
    });

    // ============================================
    // EXPOSE TO GLOBAL SCOPE
    // ============================================
    window.ModernGreen = ModernGreen;

    // ============================================
    // AUTO-INITIALIZE
    // ============================================
    ModernGreen.init();

})();