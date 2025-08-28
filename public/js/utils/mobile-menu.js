/**
 * Mobile Menu Manager
 * Handles responsive navigation for admin panel
 */

class MobileMenuManager {
    constructor() {
        this.sidebar = null;
        this.overlay = null;
        this.toggleButton = null;
        this.isOpen = false;
        
        this.init();
    }

    init() {
        this.createElements();
        this.setupEventListeners();
        this.handleResize();
    }

    createElements() {
        // Get sidebar
        this.sidebar = document.querySelector('.admin-sidebar');
        if (!this.sidebar) return;

        // Create mobile toggle button
        this.toggleButton = document.createElement('button');
        this.toggleButton.className = 'mobile-menu-toggle';
        this.toggleButton.innerHTML = '<span aria-hidden="true">☰</span>';
        this.toggleButton.setAttribute('aria-label', 'Toggle navigation menu');
        this.toggleButton.setAttribute('aria-expanded', 'false');
        
        // Create overlay
        this.overlay = document.createElement('div');
        this.overlay.className = 'mobile-overlay';
        this.overlay.setAttribute('aria-hidden', 'true');
        
        // Insert elements
        const header = document.querySelector('.admin-header .header-left');
        if (header) {
            header.prepend(this.toggleButton);
        }
        
        document.body.appendChild(this.overlay);
    }

    setupEventListeners() {
        if (!this.toggleButton) return;

        // Toggle button click
        this.toggleButton.addEventListener('click', () => {
            this.toggle();
        });

        // Overlay click to close
        this.overlay?.addEventListener('click', () => {
            this.close();
        });

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            this.handleResize();
        });
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        if (!this.sidebar || this.isOpen) return;

        this.isOpen = true;
        this.sidebar.classList.add('show');
        this.overlay?.classList.add('show');
        this.toggleButton?.setAttribute('aria-expanded', 'true');
        this.overlay?.setAttribute('aria-hidden', 'false');
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Focus management
        this.trapFocus();
    }

    close() {
        if (!this.sidebar || !this.isOpen) return;

        this.isOpen = false;
        this.sidebar.classList.remove('show');
        this.overlay?.classList.remove('show');
        this.toggleButton?.setAttribute('aria-expanded', 'false');
        this.overlay?.setAttribute('aria-hidden', 'true');
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        // Return focus to toggle button
        this.toggleButton?.focus();
    }

    handleResize() {
        // Close menu on desktop resize
        if (window.innerWidth > 768 && this.isOpen) {
            this.close();
        }
    }

    trapFocus() {
        if (!this.sidebar) return;

        const focusableElements = this.sidebar.querySelectorAll(
            'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        // Focus first element
        setTimeout(() => firstElement.focus(), 100);

        // Handle tab navigation
        const handleTabKey = (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        };

        // Add tab listener
        document.addEventListener('keydown', handleTabKey);

        // Remove listener when menu closes
        const removeListener = () => {
            document.removeEventListener('keydown', handleTabKey);
            this.sidebar?.removeEventListener('transitionend', removeListener);
        };

        this.sidebar.addEventListener('transitionend', removeListener, { once: true });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize on mobile/tablet screens
    if (window.innerWidth <= 768) {
        window.mobileMenu = new MobileMenuManager();
    }
    
    // Initialize on resize to mobile
    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768 && !window.mobileMenu) {
            window.mobileMenu = new MobileMenuManager();
        } else if (window.innerWidth > 768 && window.mobileMenu) {
            window.mobileMenu.close();
        }
    });
});
