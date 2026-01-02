/**
 * FORM VALIDATION JS - Advanced form validation
 * @description Enhanced form validation for all forms
 * @version 2.0.0
 */

(function() {
    'use strict';

    // ============================================
    // MAIN FORM VALIDATOR CLASS
    // ============================================
    class FormValidator {
        constructor(formId, options = {}) {
            this.form = document.getElementById(formId);
            if (!this.form) return;

            this.options = {
                showErrors: true,
                showSuccess: true,
                scrollToError: true,
                disableOnSubmit: true,
                autoValidate: true,
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
                submitBtn.classList.add('loading');
            }
        }

        /**
         * Hide loading state
         */
        hideLoading() {
            const submitBtn = this.form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Kirim Pesan';
                submitBtn.classList.remove('loading');
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
    // QNA FORM VALIDATOR (EXTENDS MAIN VALIDATOR)
    // ============================================
    class QnAFormValidator extends FormValidator {
        constructor(formId) {
            super(formId);
            this.setupQnAValidation();
        }

        setupQnAValidation() {
            // Date validation for departure
            const daySelect = this.form.querySelector('[name="departure_day"]');
            const monthSelect = this.form.querySelector('[name="departure_month"]');
            const yearSelect = this.form.querySelector('[name="departure_year"]');

            [daySelect, monthSelect, yearSelect].forEach(select => {
                select.addEventListener('change', () => this.validateDate(daySelect, monthSelect, yearSelect));
            });

            // Radio button validation
            const radioGroups = this.form.querySelectorAll('input[type="radio"][required]');
            radioGroups.forEach(radio => {
                radio.addEventListener('change', () => this.validateRadioGroup(radio.name));
            });

            // Character counters for textareas
            this.setupQnATextareaCounters();
        }

        validateDate(day, month, year) {
            if (day.value && month.value && year.value) {
                const dayNum = parseInt(day.value);
                const monthNum = parseInt(month.value);
                const yearNum = parseInt(year.value);
                
                // Validate day for the month
                const daysInMonth = new Date(yearNum, monthNum, 0).getDate();
                if (dayNum > daysInMonth) {
                    this.showFieldError(day, `Tanggal tidak valid untuk bulan ${month.value}`);
                    return false;
                }
                
                // Validate not in the future
                const selectedDate = new Date(yearNum, monthNum - 1, dayNum);
                const today = new Date();
                if (selectedDate > today) {
                    this.showFieldError(day, 'Tanggal tidak boleh di masa depan');
                    return false;
                }
                
                this.clearError(day);
                return true;
            }
            return false;
        }

        validateRadioGroup(groupName) {
            const radios = this.form.querySelectorAll(`input[name="${groupName}"]`);
            const groupChecked = Array.from(radios).some(radio => radio.checked);
            
            if (!groupChecked) {
                this.showRadioGroupError(groupName, 'Pilih salah satu opsi');
                return false;
            }
            
            this.clearRadioGroupError(groupName);
            return true;
        }

        showRadioGroupError(groupName, message) {
            const firstRadio = this.form.querySelector(`input[name="${groupName}"]`);
            if (firstRadio) {
                const groupContainer = firstRadio.closest('.form-check-modern')?.parentElement;
                if (groupContainer) {
                    this.clearRadioGroupError(groupName);
                    
                    const errorElement = document.createElement('div');
                    errorElement.className = 'qna-error-message mt-2';
                    errorElement.textContent = message;
                    
                    groupContainer.appendChild(errorElement);
                    groupContainer.classList.add('qna-error');
                }
            }
        }

        clearRadioGroupError(groupName) {
            const firstRadio = this.form.querySelector(`input[name="${groupName}"]`);
            if (firstRadio) {
                const groupContainer = firstRadio.closest('.form-check-modern')?.parentElement;
                if (groupContainer) {
                    const errorElement = groupContainer.querySelector('.qna-error-message');
                    if (errorElement) {
                        errorElement.remove();
                    }
                    groupContainer.classList.remove('qna-error');
                }
            }
        }

        setupQnATextareaCounters() {
            const textareas = this.form.querySelectorAll('textarea[maxlength]');
            
            textareas.forEach(textarea => {
                const maxLength = parseInt(textarea.getAttribute('maxlength')) || 500;
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

        validateForm() {
            let isValid = super.validateForm();
            
            // Validate date
            const daySelect = this.form.querySelector('[name="departure_day"]');
            const monthSelect = this.form.querySelector('[name="departure_month"]');
            const yearSelect = this.form.querySelector('[name="departure_year"]');
            
            if (!this.validateDate(daySelect, monthSelect, yearSelect)) {
                isValid = false;
            }
            
            // Validate all radio groups
            const radioGroups = new Set();
            this.form.querySelectorAll('input[type="radio"][required]').forEach(radio => {
                radioGroups.add(radio.name);
            });
            
            radioGroups.forEach(groupName => {
                if (!this.validateRadioGroup(groupName)) {
                    isValid = false;
                }
            });
            
            return isValid;
        }

        handleSubmit(event) {
            event.preventDefault();
            
            const isValid = this.validateForm();
            
            if (isValid) {
                this.submitQnAForm();
            } else {
                // Scroll to first error
                const firstError = this.form.querySelector('.is-invalid, .qna-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        }

        async submitQnAForm() {
            // Disable submit button
            const submitBtn = this.form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
            
            // Show loading state
            this.showLoading();
            
            try {
                // Simulate form submission (replace with actual fetch)
                const response = await this.simulateQnAApiCall();
                
                if (response.success) {
                    this.showSuccess('Terima kasih! Testimoni Anda telah berhasil dikirim.');
                    this.form.reset();
                    
                    // Redirect to home after 3 seconds
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 3000);
                } else {
                    this.showError('Gagal mengirim testimoni. Silakan coba lagi.');
                }
            } catch (error) {
                this.showError('Terjadi kesalahan. Silakan coba lagi.');
                console.error('QnA form error:', error);
            } finally {
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }
                
                // Hide loading state
                this.hideLoading();
            }
        }

        simulateQnAApiCall() {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({
                        success: true,
                        message: 'Testimoni berhasil dikirim!'
                    });
                }, 1500);
            });
        }

        showSuccess(message) {
            if (!this.options.showSuccess) return;

            // Remove existing messages
            this.clearMessages();

            // Create success message
            const successDiv = document.createElement('div');
            successDiv.className = 'qna-success-message mt-3';
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
    }

    // ============================================
    // AUTO-INITIALIZE FORMS
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

        // Initialize QnA form
        if (document.getElementById('qnaForm')) {
            new QnAFormValidator('qnaForm');
        }

        // Initialize newsletter form
        if (document.getElementById('newsletterForm')) {
            new FormValidator('newsletterForm', {
                scrollToError: false,
                showSuccess: true,
                showErrors: false
            });
        }

        // Initialize testimonial form (on index page)
        if (document.querySelector('form[action*="testimonial"]')) {
            const form = document.querySelector('form[action*="testimonial"]');
            form.addEventListener('submit', function(e) {
                // Validate rating
                const rating = this.querySelector('input[name="rating"]:checked');
                if (!rating) {
                    e.preventDefault();
                    alert('Harap berikan rating terlebih dahulu.');
                    return false;
                }
                
                // Validate name and email
                const name = this.querySelector('input[name="name"]');
                const email = this.querySelector('input[name="email"]');
                
                if (!name.value.trim()) {
                    e.preventDefault();
                    alert('Harap isi nama lengkap.');
                    name.focus();
                    return false;
                }
                
                if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                    e.preventDefault();
                    alert('Harap isi email yang valid.');
                    email.focus();
                    return false;
                }
                
                return true;
            });
        }
    });

    // ============================================
    // GLOBAL UTILITY FUNCTIONS
    // ============================================
    window.FormUtils = {
        /**
         * Validate email format
         */
        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        /**
         * Format phone number
         */
        formatPhoneNumber: function(phone) {
            return phone.replace(/\D/g, '');
        },

        /**
         * Show notification
         */
        showNotification: function(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} notification-fixed`;
            notification.innerHTML = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                animation: slideInRight 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        },

        /**
         * Debounce function
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
        }
    };

    // ============================================
    // ADD GLOBAL ANIMATION STYLES
    // ============================================
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .notification-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        }
    `;
    document.head.appendChild(style);

})();

// ============================================
// EXPOSE TO GLOBAL SCOPE FOR BACKWARDS COMPATIBILITY
// ============================================
if (typeof window.FormValidator === 'undefined') {
    window.FormValidator = FormValidator;
}
if (typeof window.QnAFormValidator === 'undefined') {
    window.QnAFormValidator = QnAFormValidator;
}