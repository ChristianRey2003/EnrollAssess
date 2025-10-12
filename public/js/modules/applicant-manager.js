/**
 * Modern Applicant Management Module
 * Handles bulk operations, search, filtering with proper error handling
 */

class ApplicantManager {
    constructor() {
        this.selectedApplicants = new Set();
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.elements = this.getElements();
        this.validator = null;
        
        this.init();
    }

    getElements() {
        return {
            selectAll: document.getElementById('selectAll'),
            tableSelectAll: document.getElementById('tableSelectAll'),
            selectedCount: document.getElementById('selectedCount'),
            bulkActions: document.getElementById('bulkActions'),
            searchInput: document.getElementById('searchInput'),
            statusFilter: document.getElementById('statusFilter'),
            instructorFilter: document.getElementById('instructorFilter'),
            checkboxes: () => document.querySelectorAll('.applicant-checkbox')
        };
    }

    init() {
        this.setupEventListeners();
        this.setupFormValidation();
        
        // Initialize global selectedApplicants for other components
        window.selectedApplicants = [];
        
        // Load notifications if available
        if (window.notifications) {
            this.notifications = window.notifications;
        } else {
            console.warn('Notification system not loaded, falling back to alerts');
            this.notifications = {
                success: (msg) => alert(msg),
                error: (msg) => alert('Error: ' + msg),
                info: (msg) => alert(msg)
            };
        }
    }

    setupEventListeners() {
        // Bulk selection
        this.elements.selectAll?.addEventListener('change', this.handleSelectAll.bind(this));
        this.elements.tableSelectAll?.addEventListener('change', this.handleTableSelectAll.bind(this));
        
        // Search functionality
        this.elements.searchInput?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.performSearch();
            }
        });

        // Filter functionality
        this.elements.statusFilter?.addEventListener('change', this.applyFilter.bind(this));
        this.elements.instructorFilter?.addEventListener('change', this.applyFilter.bind(this));

        // Individual checkboxes (event delegation)
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('applicant-checkbox')) {
                this.updateBulkActions();
            }
        });

        // Modal close events
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.closeAllModals();
            }
        });
    }

    setupFormValidation() {
        // Initialize form validators for modals
        const generateCodesForm = document.getElementById('generateCodesForm');

        if (generateCodesForm && typeof window.FormValidator === 'function') {
            try {
                this.validator = new window.FormValidator(generateCodesForm, {
                    validateOnInput: true,
                    showSuccessMessages: false
                });
            } catch (error) {
                console.warn('FormValidator not available, using basic validation:', error);
                this.validator = null;
            }
        }
    }

    handleSelectAll() {
        const isChecked = this.elements.selectAll.checked;
        this.elements.checkboxes().forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        this.updateBulkActions();
    }

    handleTableSelectAll() {
        const isChecked = this.elements.tableSelectAll.checked;
        this.elements.checkboxes().forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        this.updateBulkActions();
    }

    updateBulkActions() {
        const checkedBoxes = Array.from(this.elements.checkboxes()).filter(cb => cb.checked);
        const count = checkedBoxes.length;
        
        // Update selected applicants set
        this.selectedApplicants.clear();
        checkedBoxes.forEach(cb => this.selectedApplicants.add(cb.value));
        
        // Expose selected applicants to global scope for other components (like email notification modal)
        window.selectedApplicants = Array.from(this.selectedApplicants);
        
        // Update UI
        if (this.elements.selectedCount) {
            this.elements.selectedCount.textContent = `${count} selected`;
        }
        
        if (this.elements.bulkActions) {
            this.elements.bulkActions.style.display = count > 0 ? 'flex' : 'none';
        }

        // Update select all checkboxes
        const totalCheckboxes = this.elements.checkboxes().length;
        if (this.elements.selectAll) {
            this.elements.selectAll.indeterminate = count > 0 && count < totalCheckboxes;
            this.elements.selectAll.checked = count === totalCheckboxes;
        }
        if (this.elements.tableSelectAll) {
            this.elements.tableSelectAll.indeterminate = count > 0 && count < totalCheckboxes;
            this.elements.tableSelectAll.checked = count === totalCheckboxes;
        }
    }

    performSearch() {
        const searchTerm = this.elements.searchInput?.value?.trim();
        const url = new URL(window.location);
        
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        window.location = url;
    }

    applyFilter() {
        const status = this.elements.statusFilter?.value;
        const instructorId = this.elements.instructorFilter?.value;
        const url = new URL(window.location);
        
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        
        if (instructorId) {
            url.searchParams.set('instructor_id', instructorId);
        } else {
            url.searchParams.delete('instructor_id');
        }
        
        window.location = url;
    }

    // Modal management
    showGenerateAccessCodesModal() {
        if (this.selectedApplicants.size === 0) {
            this.notifications.error('Please select applicants first.');
            return;
        }
        
        if (window.modalManager) {
            window.modalManager.open('generateCodesModal');
        } else {
            document.getElementById('generateCodesModal').style.display = 'flex';
        }
    }

    closeAllModals() {
        const modals = document.querySelectorAll('.modal-overlay');
        modals.forEach(modal => {
            if (window.modalManager) {
                window.modalManager.close(modal.id);
            } else {
                modal.style.display = 'none';
            }
        });
    }

    // API operations with loading states and error handling
    async confirmGenerateAccessCodes() {
        if (this.validator && !this.validator.validateAll()) {
            this.notifications.error('Please fix the form errors before submitting.');
            return;
        }

        const expiryHours = document.getElementById('expiry_hours')?.value || 72;
        const sendEmail = document.getElementById('send_email')?.checked || false;
        
        const loadingId = this.notifications.info('Generating access codes...', 0);
        
        try {
            const response = await this.apiCall('/admin/applicants/generate-access-codes', {
                applicant_ids: Array.from(this.selectedApplicants),
                expiry_hours: expiryHours,
                send_email: sendEmail
            });

            this.notifications.dismiss?.(loadingId);
            
            if (response.success) {
                this.notifications.success(response.message);
                this.closeAllModals();
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(response.message || 'Operation failed');
            }
        } catch (error) {
            this.notifications.dismiss?.(loadingId);
            this.handleError(error, 'generating access codes');
        }
    }

    async generateSingleAccessCode(applicantId) {
        const confirmed = await this.confirm(
            'Generate Access Code',
            'Generate access code for this applicant?',
            'Generate',
            'Cancel'
        );
        
        if (!confirmed) return;
        
        const loadingId = this.notifications.info('Generating access code...', 0);
        
        try {
            const response = await this.apiCall('/admin/applicants/generate-access-codes', {
                applicant_ids: [applicantId],
                expiry_hours: 72
            });

            this.notifications.dismiss?.(loadingId);
            
            if (response.success) {
                this.notifications.success('Access code generated successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(response.message || 'Operation failed');
            }
        } catch (error) {
            this.notifications.dismiss?.(loadingId);
            this.handleError(error, 'generating access code');
        }
    }

    async deleteApplicant(applicantId) {
        const confirmed = await this.confirm(
            'Delete Applicant',
            'Are you sure you want to delete this applicant? This action cannot be undone.',
            'Delete',
            'Cancel'
        );
        
        if (!confirmed) return;
        
        const loadingId = this.notifications.info('Deleting applicant...', 0);
        
        try {
            const response = await fetch(`/admin/applicants/${applicantId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            this.notifications.dismiss?.(loadingId);
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.notifications.success('Applicant deleted successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.message || 'Operation failed');
                }
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            this.notifications.dismiss?.(loadingId);
            this.handleError(error, 'deleting applicant');
        }
    }

    bulkExport() {
        if (this.selectedApplicants.size === 0) {
            this.notifications.error('Please select applicants first.');
            return;
        }
        
        const url = new URL('/admin/applicants/export/with-access-codes', window.location.origin);
        const filters = new URLSearchParams(window.location.search);
        
        filters.forEach((value, key) => {
            url.searchParams.set(key, value);
        });
        
        this.notifications.info('Preparing export...');
        window.open(url.toString(), '_blank');
    }

    // Utility methods
    async apiCall(endpoint, data) {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }

    async confirm(title, message, confirmText = 'Confirm', cancelText = 'Cancel') {
        if (window.modalManager) {
            return await window.modalManager.confirm({
                title, message, confirmText, cancelText
            });
        } else {
            return confirm(`${title}\n\n${message}`);
        }
    }

    handleError(error, context) {
        console.error(`Error ${context}:`, error);
        
        let message = 'An unexpected error occurred.';
        
        if (error.message) {
            message = error.message;
        } else if (error.code === 'NETWORK_ERROR') {
            message = 'Please check your internet connection and try again.';
        }
        
        this.notifications.error(message);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.applicantManager = new ApplicantManager();
});

// Global functions for backward compatibility
window.showGenerateAccessCodesModal = () => window.applicantManager?.showGenerateAccessCodesModal();
window.confirmGenerateAccessCodes = () => window.applicantManager?.confirmGenerateAccessCodes();
window.generateSingleAccessCode = (id) => window.applicantManager?.generateSingleAccessCode(id);
window.deleteApplicant = (id) => window.applicantManager?.deleteApplicant(id);
window.bulkExport = () => window.applicantManager?.bulkExport();
window.toggleSelectAll = () => window.applicantManager?.handleSelectAll();
window.toggleTableSelectAll = () => window.applicantManager?.handleTableSelectAll();
window.updateBulkActions = () => window.applicantManager?.updateBulkActions();
window.performSearch = () => window.applicantManager?.performSearch();
window.applyFilter = () => window.applicantManager?.applyFilter();
window.closeGenerateCodesModal = () => window.applicantManager?.closeAllModals();
