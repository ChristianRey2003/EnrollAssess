/*===========================================
  ADMIN JAVASCRIPT BUNDLE
  Optimized JS for admin interface
===========================================*/

// Import Alpine.js for reactive components
import Alpine from 'alpinejs'

// Import admin utilities
import './utils/modal-manager.js'
import './utils/form-validator.js'
import './utils/mobile-menu.js'
import './notifications.js'

// Global admin functionality
window.Alpine = Alpine

// Admin-specific global functions
window.AdminPanel = {
    // User dropdown functionality (global for all admin pages)
    toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdownMenu');
        if (dropdown) {
            const isVisible = dropdown.style.display === 'block';
            dropdown.style.display = isVisible ? 'none' : 'block';
            
            // Update ARIA attributes for accessibility
            const toggle = document.querySelector('.user-dropdown-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', !isVisible);
            }
        }
    },

    // Close dropdown when clicking outside (global handler)
    setupDropdownListeners() {
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdownMenu');
            const toggle = document.querySelector('.user-dropdown-toggle');
            
            if (dropdown && toggle && !toggle.contains(event.target)) {
                dropdown.style.display = 'none';
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Close dropdown on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const dropdown = document.getElementById('userDropdownMenu');
                const toggle = document.querySelector('.user-dropdown-toggle');
                if (dropdown && dropdown.style.display === 'block') {
                    dropdown.style.display = 'none';
                    if (toggle) {
                        toggle.setAttribute('aria-expanded', 'false');
                        toggle.focus(); // Return focus to trigger
                    }
                }
            }
        });
    },

    // Global CSRF token setup for AJAX requests
    setupCSRF() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            window.Laravel = {
                csrfToken: csrfToken.getAttribute('content')
            };

            // Set up Axios defaults if available
            if (window.axios) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
            }
        }
    },

    // Initialize all admin functionality
    init() {
        this.setupDropdownListeners();
        this.setupCSRF();
        
        // Initialize Alpine.js
        Alpine.start();

        // Add loading states to forms
        this.setupFormLoadingStates();

        // Setup accessibility enhancements
        this.setupAccessibilityEnhancements();
    },

    // Add loading states to form submissions
    setupFormLoadingStates() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        });
    },

    // Setup accessibility enhancements
    setupAccessibilityEnhancements() {
        // Add focus management for modals
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('keydown', function(event) {
                if (event.key === 'Tab') {
                    // Trap focus within modal
                    const focusableElements = modal.querySelectorAll(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];

                    if (event.shiftKey && document.activeElement === firstElement) {
                        event.preventDefault();
                        lastElement.focus();
                    } else if (!event.shiftKey && document.activeElement === lastElement) {
                        event.preventDefault();
                        firstElement.focus();
                    }
                }
            });
        });

        // Enhance button accessibility
        document.querySelectorAll('button').forEach(button => {
            if (!button.hasAttribute('aria-label') && !button.textContent.trim()) {
                const icon = button.querySelector('[aria-hidden="true"]');
                if (icon) {
                    button.setAttribute('aria-label', `Action button with ${icon.textContent} icon`);
                }
            }
        });
    }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => AdminPanel.init());
} else {
    AdminPanel.init();
}

// Make global functions available
window.toggleUserDropdown = () => AdminPanel.toggleUserDropdown();

// Export for module usage
export default AdminPanel;
