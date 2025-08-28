/**
 * Modern Form Validator
 * Provides real-time form validation with accessibility support
 */

class FormValidator {
    constructor(formSelector, options = {}) {
        this.form = typeof formSelector === 'string' 
            ? document.querySelector(formSelector) 
            : formSelector;
            
        if (!this.form) {
            console.error('Form not found:', formSelector);
            return;
        }

        this.options = {
            validateOnInput: true,
            validateOnBlur: true,
            showSuccessMessages: false,
            ...options
        };

        this.rules = {};
        this.errors = {};
        this.isValid = false;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.extractValidationRules();
    }

    setupEventListeners() {
        // Form submission
        this.form.addEventListener('submit', (e) => {
            if (!this.validateAll()) {
                e.preventDefault();
                this.focusFirstError();
            }
        });

        // Real-time validation
        if (this.options.validateOnInput) {
            this.form.addEventListener('input', (e) => {
                if (e.target.matches('input, textarea, select')) {
                    this.validateField(e.target);
                }
            });
        }

        if (this.options.validateOnBlur) {
            this.form.addEventListener('blur', (e) => {
                if (e.target.matches('input, textarea, select')) {
                    this.validateField(e.target);
                }
            }, true);
        }
    }

    /**
     * Extract validation rules from HTML attributes
     */
    extractValidationRules() {
        const fields = this.form.querySelectorAll('input, textarea, select');
        
        fields.forEach(field => {
            const rules = [];
            
            if (field.hasAttribute('required')) {
                rules.push({ type: 'required', message: 'This field is required' });
            }
            
            if (field.type === 'email') {
                rules.push({ type: 'email', message: 'Please enter a valid email address' });
            }
            
            if (field.type === 'url') {
                rules.push({ type: 'url', message: 'Please enter a valid URL' });
            }
            
            if (field.hasAttribute('minlength')) {
                const min = parseInt(field.getAttribute('minlength'));
                rules.push({ 
                    type: 'minlength', 
                    value: min, 
                    message: `Must be at least ${min} characters` 
                });
            }
            
            if (field.hasAttribute('maxlength')) {
                const max = parseInt(field.getAttribute('maxlength'));
                rules.push({ 
                    type: 'maxlength', 
                    value: max, 
                    message: `Must be no more than ${max} characters` 
                });
            }
            
            if (field.hasAttribute('min')) {
                const min = parseFloat(field.getAttribute('min'));
                rules.push({ 
                    type: 'min', 
                    value: min, 
                    message: `Must be at least ${min}` 
                });
            }
            
            if (field.hasAttribute('max')) {
                const max = parseFloat(field.getAttribute('max'));
                rules.push({ 
                    type: 'max', 
                    value: max, 
                    message: `Must be no more than ${max}` 
                });
            }
            
            if (field.hasAttribute('pattern')) {
                const pattern = field.getAttribute('pattern');
                rules.push({ 
                    type: 'pattern', 
                    value: new RegExp(pattern), 
                    message: 'Please match the required format' 
                });
            }
            
            if (rules.length > 0) {
                this.rules[field.name || field.id] = rules;
            }
        });
    }

    /**
     * Add custom validation rule
     * @param {string} fieldName - Field name
     * @param {Object} rule - Validation rule
     */
    addRule(fieldName, rule) {
        if (!this.rules[fieldName]) {
            this.rules[fieldName] = [];
        }
        this.rules[fieldName].push(rule);
    }

    /**
     * Validate a single field
     * @param {Element} field - Form field element
     */
    validateField(field) {
        const fieldName = field.name || field.id;
        const rules = this.rules[fieldName];
        
        if (!rules) return true;

        const value = field.value.trim();
        const errors = [];

        for (const rule of rules) {
            const error = this.checkRule(value, rule, field);
            if (error) {
                errors.push(error);
                break; // Stop at first error
            }
        }

        this.updateFieldValidation(field, errors);
        this.errors[fieldName] = errors;

        return errors.length === 0;
    }

    /**
     * Check individual validation rule
     * @param {string} value - Field value
     * @param {Object} rule - Validation rule
     * @param {Element} field - Form field element
     */
    checkRule(value, rule, field) {
        switch (rule.type) {
            case 'required':
                return value === '' ? rule.message : null;
                
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return value && !emailRegex.test(value) ? rule.message : null;
                
            case 'url':
                try {
                    new URL(value);
                    return null;
                } catch {
                    return value ? rule.message : null;
                }
                
            case 'minlength':
                return value && value.length < rule.value ? rule.message : null;
                
            case 'maxlength':
                return value && value.length > rule.value ? rule.message : null;
                
            case 'min':
                const numValue = parseFloat(value);
                return value && numValue < rule.value ? rule.message : null;
                
            case 'max':
                const maxValue = parseFloat(value);
                return value && maxValue > rule.value ? rule.message : null;
                
            case 'pattern':
                return value && !rule.value.test(value) ? rule.message : null;
                
            case 'custom':
                return rule.validator(value, field) ? null : rule.message;
                
            default:
                return null;
        }
    }

    /**
     * Update field validation state and UI
     * @param {Element} field - Form field element
     * @param {Array} errors - Array of error messages
     */
    updateFieldValidation(field, errors) {
        const hasErrors = errors.length > 0;
        const hasValue = field.value.trim() !== '';
        
        // Update field classes
        field.classList.toggle('is-invalid', hasErrors);
        field.classList.toggle('is-valid', !hasErrors && hasValue && this.options.showSuccessMessages);
        
        // Update ARIA attributes
        field.setAttribute('aria-invalid', hasErrors);
        
        // Find or create feedback element
        let feedback = field.parentNode.querySelector('.invalid-feedback, .valid-feedback');
        
        if (hasErrors) {
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                // Remove existing valid feedback
                if (feedback && feedback.classList.contains('valid-feedback')) {
                    feedback.remove();
                }
                
                // Create invalid feedback
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.setAttribute('role', 'alert');
                feedback.setAttribute('aria-live', 'polite');
                field.parentNode.appendChild(feedback);
            }
            
            feedback.textContent = errors[0];
            field.setAttribute('aria-describedby', feedback.id || `${field.id}-error`);
            
        } else if (!hasErrors && hasValue && this.options.showSuccessMessages) {
            if (!feedback || !feedback.classList.contains('valid-feedback')) {
                // Remove existing invalid feedback
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
                
                // Create valid feedback
                feedback = document.createElement('div');
                feedback.className = 'valid-feedback';
                field.parentNode.appendChild(feedback);
            }
            
            feedback.textContent = 'Looks good!';
            
        } else if (feedback) {
            feedback.remove();
            field.removeAttribute('aria-describedby');
        }
    }

    /**
     * Validate all form fields
     */
    validateAll() {
        const fields = this.form.querySelectorAll('input, textarea, select');
        let isValid = true;

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        this.isValid = isValid;
        return isValid;
    }

    /**
     * Focus the first field with an error
     */
    focusFirstError() {
        const firstError = this.form.querySelector('.is-invalid');
        if (firstError) {
            firstError.focus();
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    /**
     * Clear all validation states
     */
    reset() {
        const fields = this.form.querySelectorAll('input, textarea, select');
        
        fields.forEach(field => {
            field.classList.remove('is-invalid', 'is-valid');
            field.removeAttribute('aria-invalid');
            field.removeAttribute('aria-describedby');
            
            const feedback = field.parentNode.querySelector('.invalid-feedback, .valid-feedback');
            if (feedback) {
                feedback.remove();
            }
        });

        this.errors = {};
        this.isValid = false;
    }

    /**
     * Get all current errors
     */
    getErrors() {
        return { ...this.errors };
    }

    /**
     * Check if form is valid
     */
    isFormValid() {
        return this.isValid;
    }
}

// Export for use in other modules
window.FormValidator = FormValidator;
