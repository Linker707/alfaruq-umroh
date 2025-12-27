/**
 * FORM VALIDATION JS - Advanced form validation
 * @description Enhanced form validation for contact forms
 * @version 1.0.0
 */

(function() {
    'use strict';

    class FormValidator {
        constructor(formId, options = {}) {
            this.form = document.getElementById(formId);
            if (!this.form) return;

            this.options = {
                showErrors: true,
                showSuccess: true,
                scrollToError: true,
                disableOnSubmit: true,
                ...options
            };

            this.init();
        }

        /**
         * Initialize the form validator
         */
        init() {
            if (!this.form) return;

            // Set up form submission
            this.form.setAttribute('novalidate', 'novalidate');
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));

            // Set up real-time validation
            this.setupRealTimeValidation();

            // Set up phone formatting
            this.setupPhoneFormatting();

            // Set up character counters
            this.setupCharacterCounters();

            console.log(`FormValidator initialized for #${this.form.id}`);
        }

        /**
         * Set up real-time validation
         */
        setupRealTimeValidation() {
            const fields = this.form.querySelectorAll('.form-control-modern, select, textarea');
            
            fields.forEach(field => {
                // Validate on blur
                field.addEventListener('blur', () => {
                    this.validateField(field);
                });

                // Clear validation on input
                field.addEventListener('input', () => {
                    this.clearFieldError(field);
                });

                // Handle Enter key for navigation
                field.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey && field.type !== 'textarea') {
                        e.preventDefault();
                        this.focusNextField(field);
                    }
                });
            });
        }

        /**
         * Set up phone number formatting
         */
        setupPhoneFormatting() {
            const phoneInputs = this.form.querySelectorAll('input[type="tel"]');
            
            phoneInputs.forEach(input => {
                input.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/\D/g, '');
                    
                    // Format as Indonesian phone number
                    if (value.length > 0) {
                        if (value.startsWith('0')) {
                            value = '62' + value.substring(1);
                        } else if (value.startsWith('8')) {
                            value = '62' + value;
                        }
                    }
                    
                    e.target.value = value;
                });
            });
        }

        /**
         * Set up character counters
         */
        setupCharacterCounters() {
            const textareas = this.form.querySelectorAll('textarea[maxlength]');
            
            textareas.forEach(textarea => {
                const maxLength = parseInt(textarea.getAttribute('maxlength'));
                const counter = document.createElement('div');
                counter.className = 'character-counter text-end text-muted small mt-1';
                counter.textContent = `0/${maxLength}`;
                
                textarea.parentNode.appendChild(counter);
                
                textarea.addEventListener('input', () => {
                    const length = textarea.value.length;
                    counter.textContent = `${length}/${maxLength}`;
                    
                    if (length > maxLength * 0.9) {
                        counter.classList.add('text-warning');
                    } else {
                        counter.classList.remove('text-warning');
                    }
                    
                    if (length > maxLength) {
                        counter.classList.add('text-danger');
                        textarea.value = textarea.value.substring(0, maxLength);
                    } else {
                        counter.classList.remove('text-danger');
                    }
                });
            });
        }

        /**
         * Validate a single field
         */
        validateField(field) {
            let isValid = true;
            let errorMessage = '';

            // Skip disabled or readonly fields
            if (field.disabled || field.readOnly) {
                return true;
            }

            // Required field validation
            if (field.required && !field.value.trim()) {
                isValid = false;
                errorMessage = field.getAttribute('data-error-required') || 'Field ini wajib diisi';
            }

            // Email validation
            if (field.type === 'email' && field.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    isValid = false;
                    errorMessage = field.getAttribute('data-error-email') || 'Format email tidak valid';
                }
            }

            // Phone validation
            if (field.type === 'tel' && field.value.trim()) {
                const phoneRegex = /^[0-9]{10,13}$/;
                const cleanedValue = field.value.replace(/\D/g, '');
                if (!phoneRegex.test(cleanedValue)) {
                    isValid = false;
                    errorMessage = field.getAttribute('data-error-phone') || 'Nomor telepon harus 10-13 digit angka';
                }
            }

            // Min length validation
            if (field.hasAttribute('minlength')) {
                const minLength = parseInt(field.getAttribute('minlength'));
                if (field.value.length < minLength && field.value.length > 0) {
                    isValid = false;
                    errorMessage = field.getAttribute('data-error-minlength') || `Minimal ${minLength} karakter`;
                }
            }

            // Max length validation
            if (field.hasAttribute('maxlength')) {
                const maxLength = parseInt(field.getAttribute('maxlength'));
                if (field.value.length > maxLength) {
                    isValid = false;
                    errorMessage = field.getAttribute('data-error-maxlength') || `Maksimal ${maxLength} karakter`;
                }
            }

            // Pattern validation
            if (field.hasAttribute('pattern') && field.value.trim()) {
                const pattern = new RegExp(field.getAttribute('pattern'));
                if (!pattern.test(field.value)) {
                    isValid = false;
                    errorMessage = field.getAttribute('data-error-pattern') || 'Format tidak sesuai';
                }
            }

            // Update field state
            if (!isValid) {
                this.showFieldError(field, errorMessage);
            } else {
                this.clearFieldError(field);
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }

            return isValid;
        }

        /**
         * Show error for a specific field
         */
        showFieldError(field, message) {
            // Remove existing error
            this.clearFieldError(field);

            // Create error element
            const errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            errorElement.textContent = message;
            errorElement.setAttribute('role', 'alert');

            // Insert after field
            field.parentNode.appendChild(errorElement);

            // Update field classes
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');

            // Add aria attributes
            field.setAttribute('aria-invalid', 'true');
            field.setAttribute('aria-describedby', errorElement.id || this.generateId());
        }

        /**
         * Clear error for a specific field
         */
        clearFieldError(field) {
            // Remove error message
            const errorElement = field.parentNode.querySelector('.error-message');
            if (errorElement) {
                errorElement.remove();
            }

            // Remove aria attributes
            field.removeAttribute('aria-invalid');
            field.removeAttribute('aria-describedby');

            // Remove validation classes
            field.classList.remove('is-invalid');
        }

        /**
         * Validate entire form
         */
        validateForm() {
            const fields = this.form.querySelectorAll('.form-control-modern, select, textarea');
            let isValid = true;
            let firstInvalidField = null;

            fields.forEach(field => {
                if (!this.validateField(field) && !firstInvalidField) {
                    isValid = false;
                    firstInvalidField = field;
                }
            });

            return { isValid, firstInvalidField };
        }

        /**
         * Handle form submission
         */
        handleSubmit(event) {
            event.preventDefault();

            const { isValid, firstInvalidField } = this.validateForm();

            if (isValid) {
                this.submitForm();
            } else if (firstInvalidField && this.options.scrollToError) {
                this.scrollToField(firstInvalidField);
            }
        }

        /**
         * Submit the form
         */
        async submitForm() {
            // Disable submit button
            if (this.options.disableOnSubmit) {
                this.disableSubmitButton(true);
            }

            // Collect form data
            const formData = new FormData(this.form);
            const data = Object.fromEntries(formData);

            try {
                // Show loading state
                this.showLoading();

                // Here you would typically send data to server
                // For demo, we'll simulate an API call
                const response = await this.simulateApiCall(data);

                if (response.success) {
                    this.showSuccess(response.message);
                    this.form.reset();
                    
                    // Clear all validation states
                    this.clearAllValidation();
                } else {
                    this.showError(response.message);
                }
            } catch (error) {
                this.showError('Terjadi kesalahan. Silakan coba lagi.');
                console.error('Form submission error:', error);
            } finally {
                // Re-enable submit button
                if (this.options.disableOnSubmit) {
                    this.disableSubmitButton(false);
                }
                
                // Hide loading state
                this.hideLoading();
            }
        }

        /**
         * Simulate API call (replace with actual fetch)
         */
        simulateApiCall(data) {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({
                        success: true,
                        message: 'Pesan berhasil dikirim! Kami akan menghubungi Anda segera.'
                    });
                }, 1500);
            });
        }

        /**
         * Show loading state
         */
        showLoading() {
            const submitBtn = this.form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...';
            }
        }

        /**
         * Hide loading state
         */
        hideLoading() {
            const submitBtn = this.form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Kirim Pesan';
            }
        }

        /**
         * Disable/enable submit button
         */
        disableSubmitButton(disabled) {
            const submitBtn = this.form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = disabled;
            }
        }

        /**
         * Show success message
         */
        showSuccess(message) {
            if (!this.options.showSuccess) return;

            // Remove existing messages
            this.clearMessages();

            // Create success message
            const successDiv = document.createElement('div');
            successDiv.className = 'alert alert-success success-message mt-3';
            successDiv.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
            successDiv.setAttribute('role', 'alert');

            // Insert before form
            this.form.parentNode.insertBefore(successDiv, this.form);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (successDiv.parentNode) {
                    successDiv.remove();
                }
            }, 5000);
        }

        /**
         * Show error message
         */
        showError(message) {
            if (!this.options.showErrors) return;

            // Remove existing messages
            this.clearMessages();

            // Create error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger form-error-message mt-3';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${message}`;
            errorDiv.setAttribute('role', 'alert');

            // Insert before form
            this.form.parentNode.insertBefore(errorDiv, this.form);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 5000);
        }

        /**
         * Clear all messages
         */
        clearMessages() {
            const messages = this.form.parentNode.querySelectorAll('.alert');
            messages.forEach(msg => msg.remove());
        }

        /**
         * Clear all validation states
         */
        clearAllValidation() {
            const fields = this.form.querySelectorAll('.form-control-modern, select, textarea');
            fields.forEach(field => {
                this.clearFieldError(field);
                field.classList.remove('is-valid');
            });
        }

        /**
         * Scroll to field
         */
        scrollToField(field) {
            field.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            field.focus();
        }

        /**
         * Focus next field
         */
        focusNextField(currentField) {
            const fields = Array.from(this.form.querySelectorAll('input, select, textarea'));
            const currentIndex = fields.indexOf(currentField);
            
            if (currentIndex < fields.length - 1) {
                fields[currentIndex + 1].focus();
            }
        }

        /**
         * Generate unique ID
         */
        generateId() {
            return 'error-' + Math.random().toString(36).substr(2, 9);
        }
    }

    // ============================================
    // AUTO-INITIALIZE FOR SPECIFIC FORMS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize contact form
        if (document.getElementById('contactForm')) {
            new FormValidator('contactForm', {
                scrollToError: true,
                showSuccess: true,
                showErrors: true
            });
        }

        // Initialize testimonial form
        if (document.getElementById('testimonialForm')) {
            new FormValidator('testimonialForm', {
                scrollToError: true,
                showSuccess: true,
                showErrors: true
            });
        }

        // Initialize newsletter form
        if (document.getElementById('newsletterForm')) {
            new FormValidator('newsletterForm', {
                scrollToError: false,
                showSuccess: true,
                showErrors: false
            });
        }
    });

    // ============================================
    // EXPOSE TO GLOBAL SCOPE
    // ============================================
    window.FormValidator = FormValidator;

})();