/**
 * Modern Modal Manager
 * Replaces inline modal JavaScript with reusable, accessible modal system
 */

class ModalManager {
    constructor() {
        this.activeModal = null;
        this.init();
    }

    init() {
        // Close modals on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.close(this.activeModal);
            }
        });

        // Close modals when clicking overlay
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.close(e.target.id);
            }
        });
    }

    /**
     * Open a modal
     * @param {string} modalId - The ID of the modal to open
     * @param {Object} options - Optional configuration
     */
    open(modalId, options = {}) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error(`Modal with ID "${modalId}" not found`);
            return false;
        }

        // Close any existing modal first
        if (this.activeModal) {
            this.close(this.activeModal);
        }

        // Set up modal
        modal.style.display = 'flex';
        modal.classList.add('show');
        this.activeModal = modalId;

        // Focus management
        this.trapFocus(modal);

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Trigger custom event
        modal.dispatchEvent(new CustomEvent('modal:opened', { 
            detail: { modalId, options } 
        }));

        return true;
    }

    /**
     * Close a modal
     * @param {string} modalId - The ID of the modal to close
     */
    close(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return false;

        modal.classList.remove('show');
        
        // Wait for animation to complete
        setTimeout(() => {
            modal.style.display = 'none';
            this.activeModal = null;
            
            // Restore body scroll
            document.body.style.overflow = '';
            
            // Return focus to trigger element if available
            const trigger = document.querySelector(`[data-modal-trigger="${modalId}"]`);
            if (trigger) trigger.focus();
            
            // Trigger custom event
            modal.dispatchEvent(new CustomEvent('modal:closed', { 
                detail: { modalId } 
            }));
        }, 300);

        return true;
    }

    /**
     * Trap focus within modal for accessibility
     * @param {Element} modal - The modal element
     */
    trapFocus(modal) {
        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        // Focus first element
        firstElement.focus();

        // Handle tab navigation
        modal.addEventListener('keydown', (e) => {
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
        });
    }

    /**
     * Create a confirmation modal
     * @param {Object} config - Configuration object
     */
    async confirm(config) {
        const {
            title = 'Confirm Action',
            message = 'Are you sure?',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            type = 'warning'
        } = config;

        return new Promise((resolve) => {
            const modalId = `confirm-modal-${Date.now()}`;
            const modal = this.createConfirmModal(modalId, {
                title, message, confirmText, cancelText, type
            });

            document.body.appendChild(modal);

            // Event listeners
            const confirmBtn = modal.querySelector('.confirm-btn');
            const cancelBtn = modal.querySelector('.cancel-btn');

            const cleanup = () => {
                this.close(modalId);
                setTimeout(() => modal.remove(), 300);
            };

            confirmBtn.addEventListener('click', () => {
                cleanup();
                resolve(true);
            });

            cancelBtn.addEventListener('click', () => {
                cleanup();
                resolve(false);
            });

            // Open modal
            this.open(modalId);
        });
    }

    /**
     * Create confirmation modal HTML
     * @param {string} modalId - Modal ID
     * @param {Object} config - Configuration
     */
    createConfirmModal(modalId, config) {
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'modal-overlay';
        
        const typeIcons = {
            warning: '⚠️',
            danger: '🚨',
            info: 'ℹ️',
            success: '✅'
        };

        const typeColors = {
            warning: 'bg-yellow-100 text-yellow-800',
            danger: 'bg-red-100 text-red-800',
            info: 'bg-blue-100 text-blue-800',
            success: 'bg-green-100 text-green-800'
        };

        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>${config.title}</h3>
                    <button type="button" class="modal-close cancel-btn" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center ${typeColors[config.type]}">
                            ${typeIcons[config.type]}
                        </div>
                        <p class="text-gray-700">${config.message}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cancel-btn">
                        ${config.cancelText}
                    </button>
                    <button type="button" class="btn btn-danger confirm-btn">
                        ${config.confirmText}
                    </button>
                </div>
            </div>
        `;

        return modal;
    }
}

// Initialize global modal manager
window.modalManager = new ModalManager();

// Convenience functions for backward compatibility
window.openModal = (modalId, options) => window.modalManager.open(modalId, options);
window.closeModal = (modalId) => window.modalManager.close(modalId);
window.confirmAction = (config) => window.modalManager.confirm(config);
