/*===========================================
  MOBILE MENU UTILITIES
  Mobile navigation functionality
===========================================*/

// Mobile menu management
window.MobileMenu = {
    isOpen: false,
    sidebar: null,
    overlay: null,
    toggle: null,

    init() {
        this.sidebar = document.querySelector('.admin-sidebar');
        this.overlay = document.getElementById('mobileMenuOverlay');
        this.toggle = document.querySelector('.mobile-menu-toggle');

        // Set up event listeners
        this.setupEventListeners();
        this.setupResponsiveVisibility();
    },

    setupEventListeners() {
        // Escape key to close menu
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });

        // Resize handler
        window.addEventListener('resize', () => {
            this.handleResize();
        });

        // Touch gestures for mobile
        let startX = null;
        let startY = null;

        document.addEventListener('touchstart', (e) => {
            if (window.innerWidth <= 1024) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
            }
        });

        document.addEventListener('touchmove', (e) => {
            if (!startX || !startY || window.innerWidth > 1024) return;

            const deltaX = e.touches[0].clientX - startX;
            const deltaY = e.touches[0].clientY - startY;

            // Horizontal swipe detection
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
                if (deltaX > 0 && startX < 50 && !this.isOpen) {
                    // Swipe right from left edge - open menu
                    this.open();
                } else if (deltaX < -50 && this.isOpen) {
                    // Swipe left when menu is open - close menu
                    this.close();
                }
                startX = null;
                startY = null;
            }
        });
    },

    setupResponsiveVisibility() {
        const updateVisibility = () => {
            if (this.toggle) {
                if (window.innerWidth <= 1024) {
                    this.toggle.style.display = 'block';
                } else {
                    this.toggle.style.display = 'none';
                    this.close(); // Close menu when switching to desktop
                }
            }
        };

        // Initial check
        updateVisibility();

        // Update on resize
        window.addEventListener('resize', updateVisibility);
    },

    open() {
        if (!this.sidebar || !this.overlay) return;

        this.isOpen = true;
        this.sidebar.classList.add('mobile-open');
        this.overlay.classList.add('show');
        this.overlay.setAttribute('aria-hidden', 'false');

        // Update toggle button
        if (this.toggle) {
            this.toggle.setAttribute('aria-expanded', 'true');
            this.toggle.innerHTML = '<span aria-hidden="true">✕</span>';
        }

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Focus management
        const firstFocusable = this.sidebar.querySelector('a, button');
        if (firstFocusable) {
            firstFocusable.focus();
        }
    },

    close() {
        if (!this.sidebar || !this.overlay) return;

        this.isOpen = false;
        this.sidebar.classList.remove('mobile-open');
        this.overlay.classList.remove('show');
        this.overlay.setAttribute('aria-hidden', 'true');

        // Update toggle button
        if (this.toggle) {
            this.toggle.setAttribute('aria-expanded', 'false');
            this.toggle.innerHTML = '<span aria-hidden="true">☰</span>';
        }

        // Restore body scroll
        document.body.style.overflow = '';

        // Return focus to toggle button
        if (this.toggle) {
            this.toggle.focus();
        }
    },

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    },

    handleResize() {
        // Auto-close mobile menu when resizing to desktop
        if (window.innerWidth > 1024 && this.isOpen) {
            this.close();
        }
    }
};

// Global functions for onclick handlers
window.toggleMobileMenu = () => MobileMenu.toggle();
window.closeMobileMenu = () => MobileMenu.close();

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => MobileMenu.init());
} else {
    MobileMenu.init();
}

// Export for module usage
export default MobileMenu;
