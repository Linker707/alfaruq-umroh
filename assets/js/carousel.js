/**
 * CAROUSEL JS - Enhanced carousel functionality
 * @description Custom carousel enhancements
 * @version 1.0.0
 */

(function() {
    'use strict';

    class EnhancedCarousel {
        constructor(carouselId, options = {}) {
            this.carousel = document.getElementById(carouselId);
            if (!this.carousel) return;

            this.options = {
                autoplay: true,
                interval: 5000,
                pauseOnHover: true,
                showIndicators: true,
                showControls: true,
                touchEnabled: true,
                ...options
            };

            this.carouselInstance = null;
            this.autoplayInterval = null;
            this.isPaused = false;

            this.init();
        }

        /**
         * Initialize the carousel
         */
        init() {
            if (typeof bootstrap === 'undefined' || !bootstrap.Carousel) {
                console.warn('Bootstrap Carousel not found');
                return;
            }

            // Initialize Bootstrap carousel
            this.carouselInstance = new bootstrap.Carousel(this.carousel, {
                interval: this.options.interval,
                wrap: true,
                touch: this.options.touchEnabled
            });

            // Set up custom controls
            this.setupControls();

            // Set up indicators
            this.setupIndicators();

            // Set up autoplay
            if (this.options.autoplay) {
                this.startAutoplay();
            }

            // Set up pause on hover
            if (this.options.pauseOnHover) {
                this.setupPauseOnHover();
            }

            // Set up keyboard navigation
            this.setupKeyboardNavigation();

            // Set up swipe for touch devices
            if (this.options.touchEnabled) {
                this.setupTouchNavigation();
            }

            console.log(`EnhancedCarousel initialized for #${this.carousel.id}`);
        }

        /**
         * Set up custom controls
         */
        setupControls() {
            const prevBtn = this.carousel.querySelector('.carousel-control-prev');
            const nextBtn = this.carousel.querySelector('.carousel-control-next');

            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.prev();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.next();
                });
            }

            // Add ARIA labels
            if (prevBtn && !prevBtn.getAttribute('aria-label')) {
                prevBtn.setAttribute('aria-label', 'Previous slide');
            }

            if (nextBtn && !nextBtn.getAttribute('aria-label')) {
                nextBtn.setAttribute('aria-label', 'Next slide');
            }
        }

        /**
         * Set up custom indicators
         */
        setupIndicators() {
            const indicators = this.carousel.querySelector('.carousel-indicators');
            if (!indicators) return;

            // Add click handlers to indicators
            const indicatorButtons = indicators.querySelectorAll('button');
            indicatorButtons.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    this.goToSlide(index);
                });
            });
        }

        /**
         * Start autoplay
         */
        startAutoplay() {
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
            }

            this.autoplayInterval = setInterval(() => {
                if (!this.isPaused) {
                    this.next();
                }
            }, this.options.interval);
        }

        /**
         * Stop autoplay
         */
        stopAutoplay() {
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
                this.autoplayInterval = null;
            }
        }

        /**
         * Set up pause on hover
         */
        setupPauseOnHover() {
            this.carousel.addEventListener('mouseenter', () => {
                this.isPaused = true;
                this.carousel.classList.add('paused');
            });

            this.carousel.addEventListener('mouseleave', () => {
                this.isPaused = false;
                this.carousel.classList.remove('paused');
            });

            // For touch devices
            this.carousel.addEventListener('touchstart', () => {
                this.isPaused = true;
                this.carousel.classList.add('paused');
            });

            this.carousel.addEventListener('touchend', () => {
                setTimeout(() => {
                    this.isPaused = false;
                    this.carousel.classList.remove('paused');
                }, 1000);
            });
        }

        /**
         * Set up keyboard navigation
         */
        setupKeyboardNavigation() {
            document.addEventListener('keydown', (e) => {
                if (!this.carousel.contains(document.activeElement)) return;

                switch(e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        this.prev();
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        this.next();
                        break;
                    case 'Home':
                        e.preventDefault();
                        this.goToSlide(0);
                        break;
                    case 'End':
                        e.preventDefault();
                        const lastSlide = this.carousel.querySelectorAll('.carousel-item').length - 1;
                        this.goToSlide(lastSlide);
                        break;
                }
            });
        }

        /**
         * Set up touch navigation
         */
        setupTouchNavigation() {
            let touchStartX = 0;
            let touchEndX = 0;

            this.carousel.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            });

            this.carousel.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe(touchStartX, touchEndX);
            });
        }

        /**
         * Handle swipe gesture
         */
        handleSwipe(startX, endX) {
            const swipeThreshold = 50;
            const diff = startX - endX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
        }

        /**
         * Go to next slide
         */
        next() {
            if (this.carouselInstance) {
                this.carouselInstance.next();
            }
        }

        /**
         * Go to previous slide
         */
        prev() {
            if (this.carouselInstance) {
                this.carouselInstance.prev();
            }
        }

        /**
         * Go to specific slide
         */
        goToSlide(index) {
            if (this.carouselInstance) {
                this.carouselInstance.to(index);
            }
        }

        /**
         * Get current slide index
         */
        getCurrentSlideIndex() {
            const activeSlide = this.carousel.querySelector('.carousel-item.active');
            const slides = Array.from(this.carousel.querySelectorAll('.carousel-item'));
            return slides.indexOf(activeSlide);
        }

        /**
         * Get total slides count
         */
        getTotalSlides() {
            return this.carousel.querySelectorAll('.carousel-item').length;
        }

        /**
         * Update progress indicator
         */
        updateProgress() {
            const currentIndex = this.getCurrentSlideIndex();
            const totalSlides = this.getTotalSlides();
            
            // Update progress bar if exists
            const progressBar = this.carousel.querySelector('.carousel-progress');
            if (progressBar) {
                const progress = ((currentIndex + 1) / totalSlides) * 100;
                progressBar.style.width = `${progress}%`;
            }
        }

        /**
         * Destroy carousel instance
         */
        destroy() {
            this.stopAutoplay();
            if (this.carouselInstance) {
                this.carouselInstance.dispose();
            }
        }
    }

    // ============================================
    // AUTO-INITIALIZE CAROUSELS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize hero carousel
        if (document.getElementById('heroCarousel')) {
            new EnhancedCarousel('heroCarousel', {
                autoplay: true,
                interval: 6000,
                pauseOnHover: true,
                touchEnabled: true
            });
        }

        // Initialize testimonial carousel
        if (document.getElementById('testimonialCarousel')) {
            new EnhancedCarousel('testimonialCarousel', {
                autoplay: true,
                interval: 8000,
                pauseOnHover: true,
                touchEnabled: true
            });
        }

        // Initialize gallery carousel
        if (document.getElementById('galleryCarousel')) {
            new EnhancedCarousel('galleryCarousel', {
                autoplay: false,
                touchEnabled: true
            });
        }
    });

    // ============================================
    // EXPOSE TO GLOBAL SCOPE
    // ============================================
    window.EnhancedCarousel = EnhancedCarousel;

})();