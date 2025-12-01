<!-- Schedule Exam Drawer -->
<div id="scheduleExamDrawerOverlay" class="drawer-overlay" onclick="closeScheduleExamDrawer()"></div>
<div id="scheduleExamDrawer" class="drawer">
    <div class="drawer-header">
        <h3 class="drawer-title">Schedule Exam</h3>
        <button type="button" class="drawer-close" onclick="closeScheduleExamDrawer()">×</button>
    </div>
    
    <div class="drawer-body">
        <!-- Selected Applicants Info -->
        <div style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <div style="font-weight: 600; font-size: 12px; margin-bottom: 4px;">Selected Applicants</div>
            <div style="font-size: 12px; color: #6b7280;">
                <span id="scheduleSelectedCount">0</span> applicant(s) will be scheduled for exam
            </div>
        </div>

        <!-- Exam Date -->
        <div style="margin-bottom: 20px;">
            <label for="scheduleExamDate" style="font-weight: 600; font-size: 12px; margin-bottom: 8px; display: block;">
                Exam Date <span style="color: #ef4444;">*</span>
            </label>
            <input type="date" 
                   id="scheduleExamDate" 
                   class="form-control" 
                   required
                   min="{{ date('Y-m-d') }}"
                   style="width: 100%; font-size: 12px;">
            <small style="color: #6b7280; font-size: 11px;">The scheduled date for the examination</small>
        </div>

        <!-- Exam Time -->
        <div style="margin-bottom: 20px;">
            <label for="scheduleExamTime" style="font-weight: 600; font-size: 12px; margin-bottom: 8px; display: block;">
                Exam Time <span style="color: #ef4444;">*</span>
            </label>
            <input type="time" 
                   id="scheduleExamTime" 
                   class="form-control" 
                   required
                   style="width: 100%; font-size: 12px;">
            <small style="color: #6b7280; font-size: 11px;">Start time of the examination</small>
        </div>

        <!-- Exam Venue -->
        <div style="margin-bottom: 20px;">
            <label for="scheduleExamVenue" style="font-weight: 600; font-size: 12px; margin-bottom: 8px; display: block;">
                Exam Venue/Room
            </label>
            <input type="text" 
                   id="scheduleExamVenue" 
                   class="form-control" 
                   placeholder="e.g., Computer Laboratory 1, Room 203"
                   style="width: 100%; font-size: 12px;">
            <small style="color: #6b7280; font-size: 11px;">Location where the exam will be held (optional)</small>
        </div>

        <!-- Special Instructions -->
        <div style="margin-bottom: 20px;">
            <label for="scheduleSpecialInstructions" style="font-weight: 600; font-size: 12px; margin-bottom: 8px; display: block;">
                Special Instructions
            </label>
            <textarea id="scheduleSpecialInstructions" 
                      class="form-control" 
                      rows="4"
                      placeholder="Additional notes or instructions for the applicants (optional)"
                      style="width: 100%; resize: vertical; font-size: 12px;"></textarea>
            <small style="color: #6b7280; font-size: 11px;">Optional custom instructions that will appear in notifications</small>
        </div>

        <!-- Send Notification Checkbox -->
        <div style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" 
                       id="sendNotificationCheckbox" 
                       style="width: 18px; height: 18px; cursor: pointer;">
                <span style="font-weight: 500; font-size: 12px; color: #1F2937;">
                    Send email notifications immediately
                </span>
            </label>
            <small style="color: #6b7280; font-size: 11px; display: block; margin-left: 26px; margin-top: 4px;">
                If checked, email notifications will be sent right after scheduling. Otherwise, you can send notifications later.
            </small>
        </div>

        <!-- Info Box -->
        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; margin-bottom: 20px;">
            <div style="font-weight: 600; font-size: 12px; color: #1e40af; margin-bottom: 4px;">ℹ️ Note</div>
            <div style="font-size: 12px; color: #1e3a8a;">
                Applicants will be scheduled for the exam. You can choose to send notifications now or later using the "Send Notifications" button.
            </div>
        </div>
    </div>
    
    <div class="drawer-footer">
        <button type="button" class="btn btn-secondary" onclick="closeScheduleExamDrawer()">Cancel</button>
        <button type="button" class="btn btn-primary" id="scheduleExamButton" onclick="confirmScheduleExam()">
            Schedule Exam
        </button>
    </div>
</div>

<script>
// Open schedule exam drawer
function openScheduleExamDrawer() {
    const selectedApplicants = window.selectedApplicants || 
                               (window.applicantManager ? Array.from(window.applicantManager.selectedApplicants) : []);
    
    if (!selectedApplicants || selectedApplicants.length === 0) {
        if (window.NotificationSystem) {
            window.NotificationSystem.error('Please select at least one applicant first.');
        } else if (window.showError) {
            window.showError('Please select at least one applicant first.');
        }
        return;
    }
    
    const overlay = document.getElementById('scheduleExamDrawerOverlay');
    const drawer = document.getElementById('scheduleExamDrawer');
    
    if (overlay && drawer) {
        overlay.classList.add('active');
        drawer.classList.add('active');
        
        const countSpan = document.getElementById('scheduleSelectedCount');
        if (countSpan) {
            countSpan.textContent = selectedApplicants.length;
        }
        
        // Set default date to today
        const dateInput = document.getElementById('scheduleExamDate');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.value = today;
        }
        
        // Set default time to 9:00 AM
        const timeInput = document.getElementById('scheduleExamTime');
        if (timeInput && !timeInput.value) {
            timeInput.value = '09:00';
        }
        
        // Reset checkbox to unchecked
        const checkbox = document.getElementById('sendNotificationCheckbox');
        if (checkbox) {
            checkbox.checked = false;
        }
    }
}

// Close schedule exam drawer
function closeScheduleExamDrawer() {
    const overlay = document.getElementById('scheduleExamDrawerOverlay');
    const drawer = document.getElementById('scheduleExamDrawer');
    
    if (overlay && drawer) {
        overlay.classList.remove('active');
        drawer.classList.remove('active');
    }
}

// Confirm and schedule exam
function confirmScheduleExam() {
    const selectedApplicants = window.selectedApplicants || 
                               (window.applicantManager ? Array.from(window.applicantManager.selectedApplicants) : []);
    
    if (!selectedApplicants || selectedApplicants.length === 0) {
        if (window.NotificationSystem) {
            window.NotificationSystem.error('Please select at least one applicant.');
        }
        return;
    }
    
    const scheduledDate = document.getElementById('scheduleExamDate').value;
    const scheduledTime = document.getElementById('scheduleExamTime').value;
    const venue = document.getElementById('scheduleExamVenue').value;
    const specialInstructions = document.getElementById('scheduleSpecialInstructions').value;
    
    if (!scheduledDate) {
        if (window.NotificationSystem) {
            window.NotificationSystem.error('Please enter the exam date.');
        }
        return;
    }
    
    if (!scheduledTime) {
        if (window.NotificationSystem) {
            window.NotificationSystem.error('Please enter the exam time.');
        }
        return;
    }
    
    const sendNotificationCheckbox = document.getElementById('sendNotificationCheckbox');
    const sendNotifications = sendNotificationCheckbox ? sendNotificationCheckbox.checked : false;
    
    const scheduleButton = document.getElementById('scheduleExamButton');
    const originalText = scheduleButton.textContent;
    scheduleButton.textContent = sendNotifications ? 'Scheduling & Sending...' : 'Scheduling...';
    scheduleButton.disabled = true;
    
    const requestData = {
        applicant_ids: selectedApplicants,
        scheduled_date: scheduledDate,
        scheduled_time: scheduledTime,
        venue: venue || null,
        special_instructions: specialInstructions || null
    };
    
    // First, schedule the exams
    fetch('/admin/applicants/bulk/schedule-exams', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = data.message;
            let hasErrors = data.data && data.data.errors && data.data.errors.length > 0;
            
            // If checkbox is checked, send notifications
            if (sendNotifications) {
                scheduleButton.textContent = 'Sending Notifications...';
                
                // Send notifications to successfully scheduled applicants
                return fetch('/admin/applicants/bulk/send-exam-notifications', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        applicant_ids: selectedApplicants
                    })
                })
                .then(response => response.json())
                .then(notificationData => {
                    if (notificationData.success) {
                        message += ' ' + notificationData.message;
                        
                        if (notificationData.data && notificationData.data.errors && notificationData.data.errors.length > 0) {
                            hasErrors = true;
                            const notificationErrors = notificationData.data.errors.join('\n');
                            message += '\n\nNotification Errors:\n' + notificationErrors;
                        }
                    } else {
                        hasErrors = true;
                        message += '\n\nWarning: Failed to send some notifications: ' + notificationData.message;
                    }
                    
                    return { message, hasErrors };
                })
                .catch(error => {
                    console.error('Notification error:', error);
                    hasErrors = true;
                    message += '\n\nWarning: Failed to send notifications: ' + error.message;
                    return { message, hasErrors };
                });
            } else {
                return { message, hasErrors };
            }
        } else {
            throw new Error(data.message || 'Failed to schedule exams');
        }
    })
    .then(result => {
        if (result) {
            const { message, hasErrors } = result;
            
            if (hasErrors) {
                if (window.NotificationSystem) {
                    window.NotificationSystem.warning(message);
                }
            } else {
                if (window.NotificationSystem) {
                    window.NotificationSystem.success(message);
                }
            }
            
            closeScheduleExamDrawer();
            
            // Reload page to refresh statuses
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMessage = 'Error: ' + (error.message || 'Failed to schedule exams');
        if (window.NotificationSystem) {
            window.NotificationSystem.error(errorMessage);
        }
    })
    .finally(() => {
        scheduleButton.textContent = originalText;
        scheduleButton.disabled = false;
    });
}
</script>

