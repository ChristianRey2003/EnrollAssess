<!-- Email Notification Drawer -->
<div id="emailNotificationDrawerOverlay" class="drawer-overlay" onclick="closeEmailNotificationDrawer()"></div>
<div id="emailNotificationDrawer" class="drawer">
    <div class="drawer-header">
        <h3 class="drawer-title">Send Exam Notifications</h3>
        <button type="button" class="drawer-close" onclick="closeEmailNotificationDrawer()">×</button>
    </div>
    
    <div class="drawer-body">
        <!-- Selected Applicants Info -->
        <div style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <div style="font-weight: 600; font-size: 12px; margin-bottom: 4px;">Selected Applicants</div>
            <div style="font-size: 12px; color: #6b7280;">
                <span id="emailSelectedCount">0</span> applicant(s) will receive this email
            </div>
        </div>

        <!-- Info Box -->
        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; margin-bottom: 20px;">
            <div style="font-weight: 600; font-size: 12px; color: #1e40af; margin-bottom: 4px;">ℹ️ Note</div>
            <div style="font-size: 12px; color: #1e3a8a;">
                Only applicants who are already scheduled for an exam will receive notifications. 
                The email will include their scheduled date, time, and venue from their schedule.
            </div>
        </div>

        <!-- Warning for Missing Requirements -->
        <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px;">
            <div style="font-weight: 600; font-size: 12px; color: #92400e; margin-bottom: 4px;"> Requirements Check</div>
            <div style="font-size: 12px; color: #78350f;">
                Applicants must have:
                <ul style="margin: 8px 0; padding-left: 20px; font-size: 12px;">
                    <li><strong>Already scheduled for exam</strong></li>
                    <li><strong>Access code generated</strong></li>
                    <li><strong>Valid email address</strong></li>
                </ul>
                Applicants missing any of these will be skipped (you'll see a summary after sending).
            </div>
        </div>
    </div>
    
    <div class="drawer-footer">
        <button type="button" class="btn btn-secondary" onclick="closeEmailNotificationDrawer()">Cancel</button>
        <button type="button" class="btn btn-primary" id="sendEmailButton" onclick="confirmSendEmails()">
            Send Email Notifications
        </button>
    </div>
</div>

<style>
    /* Drawer Overlay */
    .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        display: none;
        z-index: 1000;
        transition: opacity 0.3s ease;
    }

    .drawer-overlay.active {
        display: block;
        opacity: 1;
    }

    /* Drawer Panel */
    .drawer {
        position: fixed;
        top: 0;
        right: -600px;
        width: 600px;
        max-width: 90vw;
        height: 100vh;
        background: white;
        box-shadow: -2px 0 8px rgba(0,0,0,0.1);
        z-index: 1001;
        overflow-y: auto;
        transition: right 0.3s ease;
    }

    .drawer.active {
        right: 0;
    }

    .drawer-header {
        position: sticky;
        top: 0;
        background: white;
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 10;
    }

    .drawer-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .drawer-close {
        background: none;
        border: none;
        font-size: 28px;
        color: #6b7280;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: background 0.2s;
    }

    .drawer-close:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .drawer-body {
        padding: 24px;
    }

    .drawer-footer {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    /* Form styles inside drawer */
    #emailNotificationDrawer input[type="date"],
    #emailNotificationDrawer input[type="time"] {
        padding: 8px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
    }
    
    #emailNotificationDrawer input[type="date"]:focus,
    #emailNotificationDrawer input[type="time"]:focus,
    #emailNotificationDrawer input[type="text"]:focus,
    #emailNotificationDrawer textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    #emailNotificationDrawer input[type="text"],
    #emailNotificationDrawer textarea {
        padding: 8px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
    }

    /* Button styles */
    .drawer-footer .btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        font-size: 14px;
        transition: all 0.2s;
    }

    .drawer-footer .btn-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .drawer-footer .btn-secondary:hover {
        background: #e5e7eb;
    }

    .drawer-footer .btn-primary {
        background: #800020;
        color: white;
    }

    .drawer-footer .btn-primary:hover {
        background: #660019;
    }

    .drawer-footer .btn-primary:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }
</style>

<script>
// Open email notification drawer
function openEmailNotificationDrawer() {
    // Get selected applicants from the applicant manager or global window
    const selectedApplicants = window.selectedApplicants || 
                               (window.applicantManager ? Array.from(window.applicantManager.selectedApplicants) : []);
    
    console.log('Opening email notification drawer. Selected applicants:', selectedApplicants);
    
    if (!selectedApplicants || selectedApplicants.length === 0) {
        if (window.NotificationSystem) {
            window.NotificationSystem.error('Please select at least one applicant first.');
        } else if (window.showError) {
            window.showError('Please select at least one applicant first.');
        } else {
            console.error('Please select at least one applicant first.');
        }
        return;
    }
    
    const overlay = document.getElementById('emailNotificationDrawerOverlay');
    const drawer = document.getElementById('emailNotificationDrawer');
    
    if (overlay && drawer) {
        overlay.classList.add('active');
        drawer.classList.add('active');
        
        // Update selected count
        const countSpan = document.getElementById('emailSelectedCount');
        if (countSpan) {
            countSpan.textContent = selectedApplicants.length;
        }
        
        
        console.log('Email notification drawer opened successfully');
    } else {
        console.error('Email notification drawer elements not found');
    }
}

// Close email notification drawer
function closeEmailNotificationDrawer() {
    const overlay = document.getElementById('emailNotificationDrawerOverlay');
    const drawer = document.getElementById('emailNotificationDrawer');
    
    if (overlay && drawer) {
        overlay.classList.remove('active');
        drawer.classList.remove('active');
        
        console.log('Email notification drawer closed');
    }
}

// Confirm and send emails
function confirmSendEmails() {
    // Get selected applicants from the applicant manager or global window
    const selectedApplicants = window.selectedApplicants || 
                               (window.applicantManager ? Array.from(window.applicantManager.selectedApplicants) : []);
    
    console.log('Confirming email send. Selected applicants:', selectedApplicants);
    
    if (!selectedApplicants || selectedApplicants.length === 0) {
        if (window.NotificationSystem) {
            window.NotificationSystem.error('Please select at least one applicant.');
        } else if (window.showError) {
            window.showError('Please select at least one applicant.');
        } else {
            console.error('Please select at least one applicant.');
        }
        return;
    }
    
    // Get exam date and time
    const sendButton = document.getElementById('sendEmailButton');
    
    // Show loading state
    const originalText = sendButton.textContent;
    sendButton.textContent = 'Sending Emails...';
    sendButton.disabled = true;
    
    const requestData = {
        applicant_ids: selectedApplicants
    };
    
    console.log('Sending email request:', requestData);
    
    fetch('/admin/applicants/bulk/send-exam-notifications', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            let message = data.message;
            
            // Show detailed results if there were failures
            if (data.data && data.data.errors && data.data.errors.length > 0) {
                const errorDetails = data.data.errors.join('\n');
                message += '\n\nDetails:\n' + errorDetails;
                
                // Show success with warning if there were partial failures
                if (window.NotificationSystem) {
                    window.NotificationSystem.warning(message);
                } else if (window.showNotification) {
                    window.showNotification(message, 'warning');
                } else {
                    console.warn(message);
                }
            } else {
                // Show success notification
                if (window.NotificationSystem) {
                    window.NotificationSystem.success(message);
                } else if (window.showSuccess) {
                    window.showSuccess(message);
                } else {
                    console.log(message);
                }
            }
            
            closeEmailNotificationDrawer();
            
            // Optional: Reload page if all emails sent successfully
            if (data.data && data.data.failed_count === 0) {
                // You can choose to reload or just close the drawer
                // location.reload();
            }
        } else {
            const errorMessage = 'Error: ' + data.message;
            if (window.NotificationSystem) {
                window.NotificationSystem.error(errorMessage);
            } else if (window.showError) {
                window.showError(errorMessage);
            } else {
                console.error(errorMessage);
            }
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        const errorMessage = 'Network error: ' + error.message;
        if (window.NotificationSystem) {
            window.NotificationSystem.error(errorMessage);
        } else if (window.showError) {
            window.showError(errorMessage);
        } else {
            console.error(errorMessage);
        }
    })
    .finally(() => {
        // Reset button state
        sendButton.textContent = originalText;
        sendButton.disabled = false;
    });
}
</script>

