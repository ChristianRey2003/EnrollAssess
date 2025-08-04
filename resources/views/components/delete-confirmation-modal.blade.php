<!-- Enhanced Delete Confirmation Modal Component -->
<div id="deleteConfirmationModal" class="modal-overlay delete-modal-overlay" style="display: none;">
    <div class="modal-content delete-modal-content">
        <!-- Modal Header -->
        <div class="modal-header delete-modal-header">
            <div class="warning-icon">
                <div class="warning-circle">
                    <span class="warning-symbol">⚠️</span>
                </div>
            </div>
            <h3 class="modal-title">Are you sure?</h3>
        </div>

        <!-- Modal Body -->
        <div class="modal-body delete-modal-body">
            <div class="delete-message">
                <p class="primary-message">
                    Do you really want to delete <strong id="deleteItemName">this item</strong>?
                </p>
                <p class="secondary-message">
                    This action cannot be undone. All associated data will be permanently removed from the system.
                </p>
            </div>

            <!-- Item Details (optional) -->
            <div class="item-details" id="deleteItemDetails" style="display: none;">
                <div class="details-card">
                    <div class="details-header">Item to be deleted:</div>
                    <div class="details-content" id="deleteItemContent">
                        <!-- Dynamic content will be inserted here -->
                    </div>
                </div>
            </div>

            <!-- Warning Messages -->
            <div class="warning-messages">
                <div class="warning-item">
                    <span class="warning-icon-small">🔒</span>
                    <span class="warning-text">This action is permanent and irreversible</span>
                </div>
                <div class="warning-item" id="additionalWarning" style="display: none;">
                    <span class="warning-icon-small">📊</span>
                    <span class="warning-text" id="additionalWarningText">Additional warning message</span>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer delete-modal-footer">
            <button type="button" onclick="closeDeleteModal()" class="btn-cancel">
                Cancel
            </button>
            <button type="button" onclick="confirmDelete()" class="btn-delete" id="confirmDeleteBtn">
                <span class="btn-icon">🗑️</span>
                <span class="btn-text">Delete</span>
            </button>
        </div>
    </div>
</div>

<style>
/* Enhanced Delete Modal Styles */
.delete-modal-overlay {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    animation: modalFadeIn 0.2s ease-out;
}

@keyframes modalFadeIn {
    from { 
        opacity: 0;
        backdrop-filter: blur(0px);
    }
    to { 
        opacity: 1;
        backdrop-filter: blur(4px);
    }
}

.delete-modal-content {
    background: var(--white);
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    max-width: 480px;
    width: 90%;
    overflow: hidden;
    border: 3px solid #ef4444;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        transform: scale(0.9) translateY(-20px);
        opacity: 0;
    }
    to {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
}

.delete-modal-header {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    padding: 24px 30px;
    text-align: center;
    border-bottom: 2px solid #fecaca;
    position: relative;
}

.warning-icon {
    margin-bottom: 16px;
}

.warning-circle {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    border: 4px solid var(--white);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
    animation: warningPulse 2s infinite;
}

@keyframes warningPulse {
    0%, 100% { 
        transform: scale(1);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
    }
    50% { 
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
    }
}

.warning-symbol {
    font-size: 28px;
    color: var(--white);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

.modal-title {
    font-size: 24px;
    font-weight: 700;
    color: #dc2626;
    margin: 0;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.delete-modal-body {
    padding: 30px;
}

.delete-message {
    text-align: center;
    margin-bottom: 24px;
}

.primary-message {
    font-size: 16px;
    font-weight: 500;
    color: var(--maroon-primary);
    margin: 0 0 12px 0;
    line-height: 1.5;
}

.primary-message strong {
    color: #dc2626;
    font-weight: 700;
}

.secondary-message {
    font-size: 14px;
    color: var(--text-gray);
    margin: 0;
    line-height: 1.6;
}

.item-details {
    margin-bottom: 24px;
}

.details-card {
    background: var(--light-gray);
    border: 2px solid var(--border-gray);
    border-radius: 12px;
    overflow: hidden;
}

.details-header {
    background: var(--yellow-light);
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 600;
    color: var(--maroon-primary);
    border-bottom: 1px solid var(--yellow-primary);
}

.details-content {
    padding: 16px;
    font-size: 14px;
    color: var(--text-gray);
    line-height: 1.5;
}

.warning-messages {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}

.warning-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
}

.warning-icon-small {
    font-size: 16px;
    flex-shrink: 0;
}

.warning-text {
    font-size: 13px;
    color: #dc2626;
    font-weight: 500;
}

.delete-modal-footer {
    padding: 24px 30px;
    background: var(--light-gray);
    border-top: 1px solid var(--border-gray);
    display: flex;
    gap: 16px;
    justify-content: center;
}

.btn-cancel {
    padding: 12px 24px;
    background: var(--white);
    color: var(--text-gray);
    border: 2px solid var(--border-gray);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: var(--transition);
    min-width: 100px;
}

.btn-cancel:hover {
    background: var(--border-gray);
    border-color: var(--text-gray);
}

.btn-delete {
    padding: 12px 24px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: var(--white);
    border: 2px solid #dc2626;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 700;
    font-size: 14px;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: center;
}

.btn-delete:hover:not(:disabled) {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-color: #b91c1c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.btn-delete:active {
    transform: translateY(0);
}

.btn-delete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-delete.loading .btn-text {
    opacity: 0.7;
}

.btn-delete.loading .btn-icon {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 480px) {
    .delete-modal-content {
        margin: 20px;
        width: calc(100% - 40px);
        border-radius: 12px;
    }
    
    .delete-modal-header {
        padding: 20px;
    }
    
    .warning-circle {
        width: 56px;
        height: 56px;
    }
    
    .warning-symbol {
        font-size: 24px;
    }
    
    .modal-title {
        font-size: 20px;
    }
    
    .delete-modal-body {
        padding: 24px 20px;
    }
    
    .delete-modal-footer {
        padding: 20px;
        flex-direction: column;
    }
    
    .btn-cancel,
    .btn-delete {
        width: 100%;
        justify-content: center;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .delete-modal-content {
        border-width: 4px;
    }
    
    .warning-circle {
        border-width: 6px;
    }
    
    .btn-cancel,
    .btn-delete {
        border-width: 3px;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .delete-modal-overlay,
    .delete-modal-content,
    .warning-circle {
        animation: none;
    }
    
    .btn-delete:hover {
        transform: none;
    }
}
</style>

<script>
// Enhanced Delete Modal JavaScript
let deleteModalData = {
    itemId: null,
    itemName: '',
    itemType: '',
    deleteCallback: null,
    additionalWarning: ''
};

/**
 * Show the delete confirmation modal
 * @param {Object} options - Configuration options
 * @param {string|number} options.itemId - ID of the item to delete
 * @param {string} options.itemName - Display name of the item
 * @param {string} options.itemType - Type of item (question, applicant, etc.)
 * @param {Function} options.onConfirm - Callback function when delete is confirmed
 * @param {string} options.additionalWarning - Optional additional warning message
 * @param {Object} options.itemDetails - Optional item details to display
 */
function showDeleteModal(options = {}) {
    // Store the data
    deleteModalData = {
        itemId: options.itemId,
        itemName: options.itemName || 'this item',
        itemType: options.itemType || 'item',
        deleteCallback: options.onConfirm,
        additionalWarning: options.additionalWarning || ''
    };
    
    // Update modal content
    document.getElementById('deleteItemName').textContent = deleteModalData.itemName;
    
    // Show additional warning if provided
    const additionalWarningElement = document.getElementById('additionalWarning');
    const additionalWarningText = document.getElementById('additionalWarningText');
    
    if (deleteModalData.additionalWarning) {
        additionalWarningText.textContent = deleteModalData.additionalWarning;
        additionalWarningElement.style.display = 'flex';
    } else {
        additionalWarningElement.style.display = 'none';
    }
    
    // Show item details if provided
    const itemDetailsElement = document.getElementById('deleteItemDetails');
    const itemContentElement = document.getElementById('deleteItemContent');
    
    if (options.itemDetails) {
        itemContentElement.innerHTML = options.itemDetails;
        itemDetailsElement.style.display = 'block';
    } else {
        itemDetailsElement.style.display = 'none';
    }
    
    // Update button text based on item type
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const btnText = deleteBtn.querySelector('.btn-text');
    btnText.textContent = `Delete ${deleteModalData.itemType}`;
    
    // Show the modal
    document.getElementById('deleteConfirmationModal').style.display = 'flex';
    
    // Focus the cancel button for accessibility
    setTimeout(() => {
        document.querySelector('.btn-cancel').focus();
    }, 100);
}

/**
 * Close the delete confirmation modal
 */
function closeDeleteModal() {
    document.getElementById('deleteConfirmationModal').style.display = 'none';
    
    // Reset the modal data
    deleteModalData = {
        itemId: null,
        itemName: '',
        itemType: '',
        deleteCallback: null,
        additionalWarning: ''
    };
}

/**
 * Confirm the deletion
 */
function confirmDelete() {
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const btnIcon = deleteBtn.querySelector('.btn-icon');
    const btnText = deleteBtn.querySelector('.btn-text');
    
    // Show loading state
    deleteBtn.disabled = true;
    deleteBtn.classList.add('loading');
    btnText.textContent = 'Deleting...';
    
    // Execute the callback if provided
    if (deleteModalData.deleteCallback && typeof deleteModalData.deleteCallback === 'function') {
        Promise.resolve(deleteModalData.deleteCallback(deleteModalData.itemId))
            .then(() => {
                // Success - close modal
                closeDeleteModal();
            })
            .catch((error) => {
                // Error - show error message
                console.error('Delete operation failed:', error);
                alert('Failed to delete item. Please try again.');
            })
            .finally(() => {
                // Reset button state
                deleteBtn.disabled = false;
                deleteBtn.classList.remove('loading');
                btnText.textContent = `Delete ${deleteModalData.itemType}`;
            });
    } else {
        // No callback provided - just close the modal (for demo purposes)
        setTimeout(() => {
            closeDeleteModal();
            alert(`${deleteModalData.itemName} has been deleted. (Demo mode)`);
        }, 1000);
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'deleteConfirmationModal') {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('deleteConfirmationModal').style.display === 'flex') {
        closeDeleteModal();
    }
});

// Prevent closing when clicking inside the modal
document.addEventListener('click', function(e) {
    if (e.target && e.target.closest('.delete-modal-content')) {
        e.stopPropagation();
    }
});
</script>