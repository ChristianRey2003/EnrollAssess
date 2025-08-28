/*===========================================
  FORM VALIDATOR UTILITY
  Client-side form validation
===========================================*/

window.FormValidator = {
    // Validation rules
    rules: {
        required: (value) => value && value.trim() !== '',
        email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        phone: (value) => /^[\+]?[0-9\s\-\(\)]{10,}$/.test(value),
        minLength: (value, length) => value && value.length >= length,
        maxLength: (value, length) => value && value.length <= length,
        numeric: (value) => /^\d+$/.test(value),
        alphanumeric: (value) => /^[a-zA-Z0-9]+$/.test(value),
        strongPassword: (value) => {
            // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/.test(value);
        }
    },

    // Error messages
    messages: {
        required: 'This field is required',
        email: 'Please enter a valid email address',
        phone: 'Please enter a valid phone number',
        minLength: 'Must be at least {length} characters',
        maxLength: 'Must be no more than {length} characters',
        numeric: 'Please enter numbers only',
        alphanumeric: 'Please enter letters and numbers only',
        strongPassword: 'Password must be at least 8 characters with uppercase, lowercase, and number'
    },

    // Validate single field
    validateField(field, rules) {
        const value = field.value;
        const errors = [];

        rules.forEach(rule => {
            if (typeof rule === 'string') {
                // Simple rule
                if (!this.rules[rule](value)) {
                    errors.push(this.messages[rule]);
                }
            } else if (typeof rule === 'object') {
                // Rule with parameters
                const ruleName = rule.rule;
                const params = rule.params || [];
                
                if (!this.rules[ruleName](value, ...params)) {
                    let message = this.messages[ruleName];
                    // Replace placeholders
                    if (rule.params) {
                        rule.params.forEach((param, index) => {
                            const key = Object.keys(rule)[index + 1] || 'param';
                            message = message.replace(`{${key}}`, param);
                        });
                    }
                    errors.push(message);
                }
            }
        });

        return errors;
    },

    // Show field error
    showError(field, errors) {
        this.clearError(field);
        
        if (errors.length > 0) {
            field.classList.add('error');
            field.setAttribute('aria-invalid', 'true');
            
            // Create error message element
            const errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            errorElement.textContent = errors[0]; // Show first error
            errorElement.setAttribute('role', 'alert');
            
            // Insert after field
            field.parentNode.insertBefore(errorElement, field.nextSibling);
        }
    },

    // Clear field error
    clearError(field) {
        field.classList.remove('error');
        field.removeAttribute('aria-invalid');
        
        // Remove error message
        const errorElement = field.parentNode.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
    },

    // Validate entire form
    validateForm(form) {
        const fields = form.querySelectorAll('[data-validate]');
        let isValid = true;
        let firstErrorField = null;

        fields.forEach(field => {
            const rulesAttr = field.getAttribute('data-validate');
            if (!rulesAttr) return;

            try {
                const rules = JSON.parse(rulesAttr);
                const errors = this.validateField(field, rules);
                
                if (errors.length > 0) {
                    this.showError(field, errors);
                    isValid = false;
                    if (!firstErrorField) {
                        firstErrorField = field;
                    }
                } else {
                    this.clearError(field);
                }
            } catch (e) {
                console.warn('Invalid validation rules for field:', field, e);
            }
        });

        // Focus first error field
        if (firstErrorField) {
            firstErrorField.focus();
        }

        return isValid;
    },

    // Real-time validation
    setupRealTimeValidation(form) {
        const fields = form.querySelectorAll('[data-validate]');
        
        fields.forEach(field => {
            // Validate on blur
            field.addEventListener('blur', () => {
                const rulesAttr = field.getAttribute('data-validate');
                if (!rulesAttr) return;

                try {
                    const rules = JSON.parse(rulesAttr);
                    const errors = this.validateField(field, rules);
                    
                    if (errors.length > 0) {
                        this.showError(field, errors);
                    } else {
                        this.clearError(field);
                    }
                } catch (e) {
                    console.warn('Invalid validation rules for field:', field, e);
                }
            });

            // Clear error on input
            field.addEventListener('input', () => {
                if (field.classList.contains('error')) {
                    this.clearError(field);
                }
            });
        });
    },

    // Initialize form validation
    init() {
        // Add form submit handlers
        document.querySelectorAll('form[data-validate-form]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    return false;
                }
            });

            // Setup real-time validation
            this.setupRealTimeValidation(form);
        });

        // Manual validation triggers
        document.querySelectorAll('[data-validate-trigger]').forEach(button => {
            button.addEventListener('click', (e) => {
                const formId = button.getAttribute('data-validate-trigger');
                const form = document.getElementById(formId);
                if (form) {
                    if (!this.validateForm(form)) {
                        e.preventDefault();
                    }
                }
            });
        });
    }
};

// Global functions for backwards compatibility
window.validateForm = (formId) => {
    const form = document.getElementById(formId);
    return form ? FormValidator.validateForm(form) : false;
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => FormValidator.init());
} else {
    FormValidator.init();
}

export default FormValidator;
