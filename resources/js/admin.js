/*===========================================
  ADMIN JAVASCRIPT BUNDLE
  Optimized JS for admin interface
===========================================*/

// Import Bootstrap 5 JS
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

// Import Alpine.js for reactive components
import Alpine from 'alpinejs'

// Import admin utilities
import './utils/modal-manager.js'
import './utils/form-validator.js'
import './notifications.js'

// Global admin functionality
window.Alpine = Alpine

// Admin-specific global functions
window.AdminPanel = {
    // Sidebar toggle functionality
    toggleSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const mainContent = document.querySelector('.admin-main');
        const toggleBtn = document.querySelector('.sidebar-toggle-btn');
        const overlay = document.getElementById('mobileMenuOverlay');
        const body = document.body;
        const isMobile = window.innerWidth <= 768;
        
        if (!sidebar) return;
        
        if (isMobile) {
            // Mobile behavior: slide out sidebar
            const isOpen = sidebar.classList.contains('mobile-open');
            
            if (isOpen) {
                sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('show');
                body.style.overflow = '';
            } else {
                sidebar.classList.add('mobile-open');
                if (overlay) overlay.classList.add('show');
                body.style.overflow = 'hidden';
            }
        } else {
            // Desktop behavior: collapse/expand sidebar
            const isCollapsed = sidebar.classList.contains('collapsed');
            
            if (isCollapsed) {
                // Expand sidebar
                sidebar.classList.remove('collapsed');
                if (mainContent) mainContent.classList.remove('sidebar-collapsed');
                if (body) body.classList.remove('sidebar-collapsed');
                if (toggleBtn) {
                    toggleBtn.classList.remove('collapsed');
                    toggleBtn.setAttribute('aria-expanded', 'true');
                }
                localStorage.setItem('sidebarCollapsed', 'false');
            } else {
                // Collapse sidebar
                sidebar.classList.add('collapsed');
                if (mainContent) mainContent.classList.add('sidebar-collapsed');
                if (body) body.classList.add('sidebar-collapsed');
                if (toggleBtn) {
                    toggleBtn.classList.add('collapsed');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                }
                localStorage.setItem('sidebarCollapsed', 'true');
            }
        }
    },

    // Initialize sidebar state from localStorage
    initSidebarState() {
        const isMobile = window.innerWidth <= 768;
        
        // Only apply saved state on desktop
        if (!isMobile) {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            
            if (isCollapsed) {
                const sidebar = document.getElementById('adminSidebar');
                const mainContent = document.querySelector('.admin-main');
                const toggleBtn = document.querySelector('.sidebar-toggle-btn');
                const body = document.body;
                
                if (sidebar) sidebar.classList.add('collapsed');
                if (mainContent) mainContent.classList.add('sidebar-collapsed');
                if (body) body.classList.add('sidebar-collapsed');
                if (toggleBtn) {
                    toggleBtn.classList.add('collapsed');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                }
            }
        }
        
        // Listen for window resize to handle mobile/desktop transitions
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const nowMobile = window.innerWidth <= 768;
                const sidebar = document.getElementById('adminSidebar');
                const overlay = document.getElementById('mobileMenuOverlay');
                
                if (nowMobile) {
                    // Switched to mobile: close mobile menu if open
                    if (sidebar) sidebar.classList.remove('mobile-open');
                    if (overlay) overlay.classList.remove('show');
                    document.body.style.overflow = '';
                } else {
                    // Switched to desktop: restore collapsed state
                    this.initSidebarState();
                }
            }, 250);
        });
    },

    // Mobile menu functionality
    toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileMenuOverlay');
        
        if (sidebar && overlay) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }
    },

    closeMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileMenuOverlay');
        
        if (sidebar && overlay) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    },

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
        this.initSidebarState(); // Initialize sidebar from localStorage
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
window.toggleSidebar = () => AdminPanel.toggleSidebar();
window.toggleMobileMenu = () => AdminPanel.toggleMobileMenu();
window.closeMobileMenu = () => AdminPanel.closeMobileMenu();

// Export for module usage
export default AdminPanel;
