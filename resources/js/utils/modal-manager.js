/*===========================================
  MODAL MANAGER UTILITY
  Centralized modal functionality
===========================================*/

window.ModalManager = {
    activeModal: null,
    previousFocus: null,

    // Open modal with proper focus management
    open(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Store current focus
        this.previousFocus = document.activeElement;

        // Close any existing modal
        this.closeAll();

        // Show modal
        modal.style.display = 'flex';
        modal.classList.add('active');
        this.activeModal = modal;

        // Focus management
        const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) {
            firstFocusable.focus();
        }

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Add escape key listener
        this.addEscapeListener();
    },

    // Close specific modal
    close(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.style.display = 'none';
        modal.classList.remove('active');
        
        if (this.activeModal === modal) {
            this.activeModal = null;
        }

        // Restore body scroll
        document.body.style.overflow = '';

        // Restore focus
        if (this.previousFocus) {
            this.previousFocus.focus();
            this.previousFocus = null;
        }

        this.removeEscapeListener();
    },

    // Close all modals
    closeAll() {
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.style.display = 'none';
            modal.classList.remove('active');
        });
        
        this.activeModal = null;
        document.body.style.overflow = '';
        this.removeEscapeListener();
    },

    // Escape key handler
    handleEscape(event) {
        if (event.key === 'Escape' && this.activeModal) {
            const modalId = this.activeModal.id;
            this.close(modalId);
        }
    },

    addEscapeListener() {
        document.addEventListener('keydown', this.handleEscape.bind(this));
    },

    removeEscapeListener() {
        document.removeEventListener('keydown', this.handleEscape.bind(this));
    },

    // Initialize modal functionality
    init() {
        // Add click handlers for modal overlays
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.close(modal.id);
                }
            });
        });

        // Add click handlers for close buttons
        document.querySelectorAll('[data-modal-close]').forEach(button => {
            button.addEventListener('click', (e) => {
                const modalId = button.getAttribute('data-modal-close');
                this.close(modalId);
            });
        });

        // Add click handlers for modal triggers
        document.querySelectorAll('[data-modal-open]').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = button.getAttribute('data-modal-open');
                this.open(modalId);
            });
        });
    }
};

// Global functions for backwards compatibility
window.openModal = (modalId) => ModalManager.open(modalId);
window.closeModal = (modalId) => ModalManager.close(modalId);

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ModalManager.init());
} else {
    ModalManager.init();
}

export default ModalManager;
