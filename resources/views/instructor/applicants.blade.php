@extends('layouts.instructor')

@section('title', 'My Applicants')

@php
    $pageTitle = 'My Assigned Applicants';
    $pageSubtitle = 'Manage interviews and evaluations for your assigned applicants';
@endphp

@push('styles')
<style>
    .applicants-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: var(--maroon-primary);
        color: white;
    }

    .btn-primary:hover {
        background: #5C0016;
        color: white;
    }

    .btn-secondary {
        background: #6B7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4B5563;
        color: white;
    }

    .btn-primary.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Tooltip */
    .tooltip { position: relative; display: inline-block; }
    .tooltip[data-tip]:hover::after {
        content: attr(data-tip);
        position: absolute;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        background: #111827;
        color: #fff;
        padding: 6px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .tooltip[data-tip]:hover::before {
        content: '';
        position: absolute;
        bottom: calc(125% - 6px);
        left: 50%;
        transform: translateX(-50%);
        border-width: 6px;
        border-style: solid;
        border-color: #111827 transparent transparent transparent;
    }

    .applicants-table-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }

    .table-header {
        padding: 20px 24px;
        background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .table-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-label {
        font-weight: 500;
        color: #374151;
        font-size: 0.875rem;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 0.875rem;
        height: 40px;
        background: #FFFFFF;
        color: #1F2937;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--maroon-primary);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .applicants-table-section .table-responsive {
        margin-bottom: 0;
    }

    .table-header-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .table-header-controls .form-input,
    .table-header-controls .form-select {
        width: 220px;
        padding: 6px 12px;
        height: 36px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .table-header-controls .form-input:focus,
    .table-header-controls .form-select:focus {
        outline: none;
        border-color: var(--maroon-primary);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .table-header-controls .btn-small {
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 16px;
    }

    .instructor-data-table thead th {
        font-weight: 600;
        font-size: 0.8125rem;
        letter-spacing: 0.5px;
        color: #374151;
        background-color: #ffffff;
        border-bottom: 1px solid #E5E7EB;
    }

    .instructor-data-table tbody td {
        font-size: 0.875rem;
        color: #1F2937;
        vertical-align: middle;
        border-bottom: 1px solid #F3F4F6;
    }

    .instructor-data-table tbody tr:hover {
        background-color: rgba(255, 215, 0, 0.08);
    }

    .applicant-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .applicant-name {
        font-weight: 600;
        color: #1F2937;
    }

    .applicant-email {
        font-size: 0.875rem;
        color: #6B7280;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: #FEF3C7;
        color: #F59E0B;
    }

    .status-completed {
        background: #D1FAE5;
        color: #059669;
    }

    .status-examcompleted {
        background: #FEE2E2;
        color: #DC2626;
    }

    .status-interviewcompleted {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #6B7280;
    }

    .empty-state h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .pagination-wrapper {
        padding: 20px 24px;
        background: #F9FAFB;
        border-top: 1px solid #E5E7EB;
    }

    .bulk-actions-bar {
        background: var(--maroon-primary);
        color: white;
        padding: 16px 24px;
        display: none;
        align-items: center;
        justify-content: space-between;
        border-radius: 8px 8px 0 0;
    }

    .bulk-actions-bar.show {
        display: flex;
    }

    .bulk-actions-info {
        font-weight: 500;
    }

    .bulk-actions-buttons {
        display: flex;
        gap: 12px;
    }

    .btn-white {
        background: white;
        color: var(--maroon-primary);
        border: none;
    }

    .btn-white:hover {
        background: #f5f5f5;
        color: var(--maroon-primary);
    }

    .btn-outline-white {
        background: transparent;
        color: white;
        border: 2px solid white;
    }

    .btn-outline-white:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .schedule-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .schedule-modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 8px;
        padding: 24px;
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6B7280;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

    .form-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .form-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 0.875rem;
        resize: vertical;
        min-height: 80px;
    }

    .btn-small {
        padding: 6px 12px;
        font-size: 0.813rem;
    }

    @media (max-width: 768px) {
        .table-header-controls {
            flex-direction: column;
            align-items: stretch;
            width: 100%;
            gap: 10px;
        }

        .table-header-controls .form-input,
        .table-header-controls .form-select {
            width: 100%;
        }

        .applicants-table {
            font-size: 0.875rem;
        }
        
        .applicants-table th,
        .applicants-table td {
            padding: 12px 8px;
        }

        .bulk-actions-bar {
            flex-direction: column;
            gap: 12px;
            align-items: stretch;
        }

        .bulk-actions-buttons {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="applicants-container">
    @include('instructor.partials.applicants-table', ['assignedApplicants' => $assignedApplicants])
</div>

<!-- Individual Schedule Modal -->
<div id="scheduleModal" class="schedule-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Schedule Interview</h3>
            <button class="close-btn" onclick="closeScheduleModal()">&times;</button>
        </div>
        <form id="scheduleForm" onsubmit="submitSchedule(event)">
            @csrf
            <input type="hidden" id="interviewId" name="interview_id">
            
            <div class="form-group">
                <label class="form-label">Applicant</label>
                <input type="text" id="applicantName" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Interview Date & Time *</label>
                <input type="datetime-local" id="scheduleDate" name="schedule_date" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Notes (Optional)</label>
                <textarea id="scheduleNotes" name="notes" class="form-textarea" 
                          placeholder="Any special instructions or notes for this interview..."></textarea>
            </div>
            
            <div class="form-checkbox">
                <input type="checkbox" id="notifyEmail" name="notify_email" value="1" checked>
                <label for="notifyEmail">Send email notification to applicant</label>
            </div>
            
            <div class="bulk-actions-buttons">
                <button type="submit" class="btn btn-primary">Schedule Interview</button>
                <button type="button" class="btn btn-secondary" onclick="closeScheduleModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Schedule Modal -->
<div id="bulkScheduleModal" class="schedule-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Bulk Schedule Interviews</h3>
            <button class="close-btn" onclick="closeBulkScheduleModal()">&times;</button>
        </div>
        <form id="bulkScheduleForm" onsubmit="submitBulkSchedule(event)">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Selected Applicants</label>
                <div id="selectedApplicantsList" style="font-size: 0.875rem; color: #6B7280; margin-bottom: 12px;"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Start Date & Time *</label>
                <input type="datetime-local" id="bulkScheduleDate" name="schedule_date_start" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Time Interval Between Interviews (minutes) *</label>
                <select name="time_interval" class="form-select" required>
                    <option value="30">30 minutes</option>
                    <option value="45">45 minutes</option>
                    <option value="60" selected>60 minutes</option>
                    <option value="90">90 minutes</option>
                    <option value="120">120 minutes</option>
                </select>
            </div>
            
            <div class="form-checkbox">
                <input type="checkbox" id="bulkNotifyEmail" name="notify_email" value="1" checked>
                <label for="bulkNotifyEmail">Send email notifications to all applicants</label>
            </div>
            
            <div class="bulk-actions-buttons">
                <button type="submit" class="btn btn-primary">Schedule All</button>
                <button type="button" class="btn btn-secondary" onclick="closeBulkScheduleModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function initializeApplicantFilters() {
        const container = document.querySelector('.applicants-table-section');
        if (!container) {
            return;
        }

        const filterForm = document.getElementById('applicantFiltersForm');
        if (!filterForm) {
            return;
        }

        const searchInput = document.getElementById('searchApplicantsInput');
        const statusSelect = filterForm.querySelector('select[name="status"]');
        let searchDebounceTimer = null;

        const buildUrlFromForm = () => {
            const url = new URL(filterForm.action, window.location.origin);
            const formData = new FormData(filterForm);
            formData.forEach((value, key) => {
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            });
            return url.toString();
        };

        const loadApplicants = (targetUrl) => {
            const finalUrl = targetUrl || buildUrlFromForm();

            fetch(finalUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load applicants.');
                }
                return response.json();
            })
            .then(data => {
                if (data.html) {
                    const parser = new DOMParser();
                    const parsed = parser.parseFromString(data.html, 'text/html');
                    const newSection = parsed.body.firstElementChild;
                    if (newSection) {
                        const existingSection = document.querySelector('.applicants-table-section');
                        if (existingSection) {
                            existingSection.replaceWith(newSection);
                            history.replaceState({}, '', finalUrl);
                            initializeApplicantFilters();
                            updateBulkActions();
                        }
                    }
                }
            })
            .catch(error => {
                console.error(error);
            });
        };

        filterForm.onsubmit = function (event) {
            event.preventDefault();
            loadApplicants();
        };

        if (searchInput) {
            searchInput.oninput = function () {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => loadApplicants(), 350);
            };
        }

        if (statusSelect) {
            statusSelect.onchange = function () {
                loadApplicants();
            };
        }

        container.querySelectorAll('.pagination a').forEach(link => {
            link.onclick = function (event) {
                event.preventDefault();
                loadApplicants(this.href);
            };
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initializeApplicantFilters();
    });

    // Bulk selection management
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.applicant-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.applicant-checkbox:checked');
        const count = checkboxes.length;
        const bulkBar = document.getElementById('bulkActionsBar');
        const selectAll = document.getElementById('selectAll');
        
        document.getElementById('selectedCount').textContent = count;
        
        if (count > 0) {
            bulkBar.classList.add('show');
        } else {
            bulkBar.classList.remove('show');
        }
        
        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.applicant-checkbox');
        selectAll.checked = allCheckboxes.length > 0 && count === allCheckboxes.length;
    }

    function clearSelection() {
        const checkboxes = document.querySelectorAll('.applicant-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBulkActions();
    }

    function toggleAssignmentInfo(applicantId) {
        const infoDiv = document.getElementById('assignment-info-' + applicantId);
        if (infoDiv) {
            infoDiv.style.display = infoDiv.style.display === 'none' ? 'block' : 'none';
        }
    }

    // Individual schedule modal
    function openScheduleModal(interviewId, applicantName) {
        document.getElementById('interviewId').value = interviewId;
        document.getElementById('applicantName').value = applicantName;
        document.getElementById('scheduleModal').classList.add('show');
        
        // Set minimum date to current time + 1 hour
        const now = new Date();
        now.setHours(now.getHours() + 1);
        document.getElementById('scheduleDate').min = now.toISOString().slice(0, 16);
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.remove('show');
        document.getElementById('scheduleForm').reset();
    }

    function submitSchedule(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const interviewId = formData.get('interview_id');
        
        const data = {
            schedule_date: formData.get('schedule_date'),
            notes: formData.get('notes'),
            notify_email: formData.get('notify_email') ? 1 : 0
        };
        
        fetch(`/instructor/interviews/${interviewId}/schedule`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = data.message;
                if (data.email_sent) {
                    message += ' Email notification sent.';
                }
                alert(message);
                closeScheduleModal();
                location.reload();
            } else {
                alert(data.message || 'Failed to schedule interview');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Bulk schedule modal
    function openBulkScheduleModal() {
        const checkboxes = document.querySelectorAll('.applicant-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Please select at least one applicant to schedule.');
            return;
        }
        
        // Show selected applicants
        const names = Array.from(checkboxes).map(cb => cb.dataset.applicantName);
        document.getElementById('selectedApplicantsList').innerHTML = names.join(', ');
        
        document.getElementById('bulkScheduleModal').classList.add('show');
        
        // Set minimum date
        const now = new Date();
        now.setHours(now.getHours() + 1);
        document.getElementById('bulkScheduleDate').min = now.toISOString().slice(0, 16);
    }

    function closeBulkScheduleModal() {
        document.getElementById('bulkScheduleModal').classList.remove('show');
        document.getElementById('bulkScheduleForm').reset();
    }

    function submitBulkSchedule(event) {
        event.preventDefault();
        
        const checkboxes = document.querySelectorAll('.applicant-checkbox:checked');
        const interviewIds = Array.from(checkboxes).map(cb => cb.dataset.interviewId);
        
        if (interviewIds.length === 0) {
            alert('No applicants selected');
            return;
        }
        
        const form = event.target;
        const formData = new FormData(form);
        
        const data = {
            interview_ids: interviewIds,
            schedule_date_start: formData.get('schedule_date_start'),
            time_interval: parseInt(formData.get('time_interval')),
            notify_email: formData.get('notify_email') ? 1 : 0
        };
        
        fetch('/instructor/interviews/bulk-schedule', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = `Successfully scheduled ${data.scheduled} interview(s).`;
                if (data.emails_sent > 0) {
                    message += ` ${data.emails_sent} email notification(s) sent.`;
                }
                if (data.errors.length > 0) {
                    message += '\n\nErrors:\n' + data.errors.join('\n');
                }
                alert(message);
                closeBulkScheduleModal();
                clearSelection();
                location.reload();
            } else {
                alert(data.message || 'Failed to schedule interviews');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Close modals when clicking outside
    document.getElementById('scheduleModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeScheduleModal();
    });

    document.getElementById('bulkScheduleModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeBulkScheduleModal();
    });

    // AJAX Pagination
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('.pagination a, .pagination-wrapper a');
        
        if (paginationLink && paginationLink.href) {
            e.preventDefault();
            e.stopPropagation();
            const url = paginationLink.href;
            
            if (!url || url === '#' || url === 'javascript:void(0)') return;
            
            const tableBody = document.querySelector('table tbody');
            const paginationWrapper = document.querySelector('.pagination-wrapper');
            
            if (tableBody) {
                tableBody.style.opacity = '0.5';
                tableBody.style.pointerEvents = 'none';
            }
            if (paginationWrapper) {
                paginationWrapper.style.opacity = '0.5';
                paginationWrapper.style.pointerEvents = 'none';
            }
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.applicants && tableBody) {
                    // For instructor page, we'll need to reload the page since the table structure is complex
                    // with PHP logic for interviews, deadlines, etc.
                    // But we can still prevent the sidebar flash by using AJAX
                    window.location.href = url;
                }
            })
            .catch(error => {
                console.error('Pagination error:', error);
                window.location.href = url;
            });
        }
    });
</script>
@endpush
