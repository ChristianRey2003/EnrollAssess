@extends('layouts.instructor')

@section('title', 'My Applicants')

@php
    $pageTitle = 'My Assigned Applicants';
    $pageSubtitle = 'Manage interviews and evaluations for your assigned applicants';
@endphp

@push('styles')
<style>
    .applicants-container {
        width: 100%;
        max-width: 100%;
    }

    /* Override main-content padding for this page */
    .main-content {
        padding: 20px !important;
    }

    .btn {
        font-size: 12px;
        padding: 8px 14px;
        font-weight: 500;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
    }

    .btn-primary {
        background: #800020;
        color: white;
    }

    .btn-primary:hover {
        background: #5C0016;
    }

    .btn-secondary {
        background: #F8F9FA;
        color: #1F2937;
        border: 1px solid #E9ECEF;
    }

    .btn-secondary:hover {
        background: #E9ECEF;
    }

    .btn-primary.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Tooltip - CSS tooltips hidden, using JavaScript tooltips instead */
    .tooltip { position: relative; display: inline-block; }
    .tooltip[data-tip]:hover::after {
        display: none; /* Hidden, using JavaScript tooltip instead */
    }
    .tooltip[data-tip]:hover::before {
        display: none; /* Hidden, using JavaScript tooltip instead */
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
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Allow tooltips to overflow table cells */
    .table tbody td {
        overflow: visible !important;
        position: relative;
    }
    
    .table tbody tr {
        overflow: visible !important;
    }

    .table-header-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .table-header-controls .form-input,
    .table-header-controls .form-select {
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    /* Dropdown arrow for select elements */
    .table-header-controls .form-select {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 12px center !important;
        cursor: pointer;
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

    /* Table styling matches admin design */
    .table thead th {
        background-color: white !important;
        color: #1F2937 !important;
    }

    .table tbody td {
        font-size: 13px;
        font-weight: normal;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: rgba(255, 215, 0, 0.05);
    }

    /* Disabled checkbox styling */
    .applicant-checkbox:disabled {
        opacity: 0.4;
        cursor: not-allowed !important;
    }

    .applicant-checkbox:disabled:hover {
        opacity: 0.5;
    }

    .floating-actions {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 6px 8px;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10;
        display: flex;
        align-items: center;
        flex-direction: row;
        overflow: visible;
    }
    
    .floating-actions .action-btn {
        padding: 6px 12px;
        border: none;
        background: transparent;
        cursor: pointer;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        transition: background-color 0.2s;
        color: #1F2937;
        text-decoration: none;
        white-space: nowrap;
        margin: 0 2px;
    }
    
    .floating-actions .action-btn:hover {
        background: #f3f4f6;
    }
    
    .floating-actions .action-btn-primary {
        color: #800020;
    }
    
    .floating-actions .action-btn-primary:hover {
        background: rgba(128, 0, 32, 0.1);
    }
    
    .floating-actions .action-btn-secondary {
        color: #6B7280;
    }
    
    .floating-actions .action-btn-secondary:hover {
        background: #f3f4f6;
    }
    
    .floating-actions .action-btn-disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .floating-actions .action-btn-disabled:hover {
        background: transparent;
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


    .pagination-wrapper {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px 24px;
        background: #F9FAFB;
        border-top: 1px solid #E5E7EB;
    }

    .pagination-wrapper .relative.z-0.inline-flex {
        margin-left: 20px;
    }

    /* Limit pagination to 5 page numbers - CSS hiding rules removed */

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
        margin-left: auto;
        justify-content: flex-end;
        align-items: center;
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
        margin-bottom: 20px;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }

    .modal-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 24px;
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

    /* Export Drawer Styles */
    .export-drawer {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        display: none;
    }

    .export-drawer.show {
        display: flex;
    }

    .drawer-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    .drawer-content {
        position: relative;
        background: white;
        width: 100%;
        max-width: 500px;
        height: 100%;
        margin-left: auto;
        box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
        }
        to {
            transform: translateX(0);
        }
    }

    .drawer-header {
        padding: 20px 24px;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .drawer-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }

    .drawer-close {
        background: none;
        border: none;
        font-size: 28px;
        color: #6B7280;
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
        background: #F3F4F6;
        color: #1F2937;
    }

    .drawer-body {
        flex: 1;
        padding: 24px;
        overflow-y: auto;
    }

    .drawer-footer {
        padding: 20px 24px;
        border-top: 1px solid #E5E7EB;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .radio-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .radio-item:hover {
        border-color: #800020;
        background: #FEF2F2;
    }

    .radio-item input[type="radio"] {
        margin-top: 4px;
        cursor: pointer;
    }

    .radio-item label {
        flex: 1;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .radio-item label strong {
        color: #1F2937;
        font-weight: 600;
    }

    .radio-description {
        font-size: 0.875rem;
        color: #6B7280;
    }

    .radio-item input[type="radio"]:checked + label strong {
        color: #800020;
    }

    .radio-item input[type="radio"]:checked ~ label {
        color: #800020;
    }

    @media (max-width: 768px) {
        .drawer-content {
            max-width: 100%;
        }

        .table-header-controls {
            flex-direction: column;
            align-items: stretch;
            width: 100%;
            gap: 10px;
        }

        .table-header-controls > div[style*="position: relative"] {
            width: 100% !important;
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
            margin-left: 0;
            align-items: stretch;
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
        </div>
        <form id="scheduleForm" onsubmit="submitSchedule(event)">
            @csrf
            <input type="hidden" id="interviewId" name="interview_id">
            <input type="hidden" id="scheduleDeadlineStart" name="schedule_deadline_start">
            <input type="hidden" id="scheduleDeadlineEnd" name="schedule_deadline_end">
            
            <div class="form-group">
                <label class="form-label">Applicant</label>
                <input type="text" id="applicantName" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Interview Date *</label>
                <input type="date" id="scheduleDate" name="schedule_date" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Interview Time *</label>
                <input type="time" id="scheduleTime" name="schedule_time" class="form-input" required>
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
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeScheduleModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Schedule Interview</button>
            </div>
        </form>
    </div>
</div>

<!-- Reschedule Modal -->
<div id="rescheduleModal" class="schedule-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Reschedule Interview</h3>
        </div>
        <form id="rescheduleForm" onsubmit="submitReschedule(event)">
            @csrf
            <input type="hidden" id="rescheduleInterviewId" name="interview_id">
            <input type="hidden" id="rescheduleDeadlineStart" name="schedule_deadline_start">
            <input type="hidden" id="rescheduleDeadlineEnd" name="schedule_deadline_end">
            
            <div class="form-group">
                <label class="form-label">Applicant</label>
                <input type="text" id="rescheduleApplicantName" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Current Schedule</label>
                <input type="text" id="currentSchedule" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">New Interview Date *</label>
                <input type="date" id="rescheduleDate" name="schedule_date" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">New Interview Time *</label>
                <input type="time" id="rescheduleTime" name="schedule_time" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Notes (Optional)</label>
                <textarea id="rescheduleNotes" name="notes" class="form-textarea" 
                          placeholder="Reason for rescheduling or additional notes..."></textarea>
            </div>
            
            <div class="form-checkbox">
                <input type="checkbox" id="rescheduleNotifyEmail" name="notify_email" value="1" checked>
                <label for="rescheduleNotifyEmail">Send email notification to applicant</label>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeRescheduleModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Reschedule Interview</button>
            </div>
        </form>
    </div>
</div>

<!-- Remarks Modal -->
<div id="remarksModal" class="schedule-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Remarks</h3>
        </div>
        <form id="remarksForm" onsubmit="submitRemarks(event)">
            @csrf
            <input type="hidden" id="remarksInterviewId" name="interview_id">
            
            <div class="form-group">
                <label class="form-label">Applicant</label>
                <input type="text" id="remarksApplicantName" class="form-input" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Remarks</label>
                <textarea id="remarksText" name="remarks" class="form-textarea" 
                          placeholder="Document contact attempts, applicant availability issues, or any other relevant notes..." 
                          rows="6"></textarea>
                <span class="help-text" style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    Use this field to document contact attempts, reasons for unavailability, or any other relevant information.
                </span>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeRemarksModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Remarks</button>
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
                            reinitializeTooltips();
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

        // Pagination is handled by the global event listener below
        // No need to attach handlers here as they'll be handled by event delegation
    }

    // Tooltip positioning to prevent clipping
    function initializeTooltips() {
        const tooltips = document.querySelectorAll('.tooltip[data-tip]');
        
        tooltips.forEach(tooltip => {
            tooltip.addEventListener('mouseenter', function(e) {
                const tooltipElement = this;
                const rect = tooltipElement.getBoundingClientRect();
                
                // Create a custom tooltip element
                let customTooltip = document.getElementById('custom-tooltip');
                if (!customTooltip) {
                    customTooltip = document.createElement('div');
                    customTooltip.id = 'custom-tooltip';
                    customTooltip.style.cssText = 'position: fixed; background: #111827; color: #fff; padding: 6px 8px; border-radius: 4px; font-size: 12px; white-space: nowrap; z-index: 99999; pointer-events: none; box-shadow: 0 2px 6px rgba(0,0,0,0.2); display: none;';
                    document.body.appendChild(customTooltip);
                }
                
                customTooltip.textContent = tooltipElement.getAttribute('data-tip');
                customTooltip.style.display = 'block';
                
                // Get tooltip dimensions
                const tooltipRect = customTooltip.getBoundingClientRect();
                const tooltipWidth = tooltipRect.width;
                const tooltipHeight = tooltipRect.height;
                const spacing = 12; // Space between button and tooltip
                const viewportPadding = 10; // Padding from viewport edges
                
                // Calculate initial position (centered above button)
                let left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                let top = rect.top - tooltipHeight - spacing;
                
                // Check if tooltip would go off the top of viewport
                if (top < viewportPadding) {
                    // Position below button instead
                    top = rect.bottom + spacing;
                }
                
                // Check if tooltip would go off the left edge
                if (left < viewportPadding) {
                    left = viewportPadding;
                }
                
                // Check if tooltip would go off the right edge
                const rightEdge = left + tooltipWidth;
                if (rightEdge > window.innerWidth - viewportPadding) {
                    left = window.innerWidth - tooltipWidth - viewportPadding;
                }
                
                // Ensure tooltip doesn't go below viewport
                const bottomEdge = top + tooltipHeight;
                if (bottomEdge > window.innerHeight - viewportPadding) {
                    top = window.innerHeight - tooltipHeight - viewportPadding;
                }
                
                customTooltip.style.left = left + 'px';
                customTooltip.style.top = top + 'px';
            });
            
            tooltip.addEventListener('mouseleave', function() {
                const customTooltip = document.getElementById('custom-tooltip');
                if (customTooltip) {
                    customTooltip.style.display = 'none';
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initializeApplicantFilters();
        initializeTooltips();
    });
    
    // Re-initialize tooltips after AJAX content loads
    function reinitializeTooltips() {
        initializeTooltips();
    }

    // Bulk selection management
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.applicant-checkbox:not(:disabled)');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateBulkActions();
    }

    function updateBulkActions() {
        // Bulk actions bar is hidden - no bulk scheduling on this page
        // Keep function for compatibility but don't show bulk actions bar
        const checkboxes = document.querySelectorAll('.applicant-checkbox:not(:disabled):checked');
        const count = checkboxes.length;
        const selectAll = document.getElementById('selectAll');
        
        // Update select all checkbox - only consider enabled checkboxes
        const allEnabledCheckboxes = document.querySelectorAll('.applicant-checkbox:not(:disabled)');
        const checkedEnabledCheckboxes = document.querySelectorAll('.applicant-checkbox:not(:disabled):checked');
        if (selectAll) {
            selectAll.checked = allEnabledCheckboxes.length > 0 && checkedEnabledCheckboxes.length === allEnabledCheckboxes.length;
            selectAll.indeterminate = checkedEnabledCheckboxes.length > 0 && checkedEnabledCheckboxes.length < allEnabledCheckboxes.length;
        }
    }

    function clearSelection() {
        const checkboxes = document.querySelectorAll('.applicant-checkbox:not(:disabled)');
        checkboxes.forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        document.getElementById('selectAll').indeterminate = false;
        updateBulkActions();
    }

    function toggleAssignmentInfo(applicantId) {
        const infoDiv = document.getElementById('assignment-info-' + applicantId);
        if (infoDiv) {
            infoDiv.style.display = infoDiv.style.display === 'none' ? 'block' : 'none';
        }
    }

    function showActions(applicantId) {
        const actionsDiv = document.getElementById('actions-' + applicantId);
        if (actionsDiv) {
            actionsDiv.style.display = 'flex';
        }
    }

    function hideActions(applicantId) {
        const actionsDiv = document.getElementById('actions-' + applicantId);
        if (actionsDiv) {
            actionsDiv.style.display = 'none';
        }
    }


    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Individual schedule modal
    function openScheduleModal(interviewId, applicantName, deadlineStart, deadlineEnd) {
        document.getElementById('interviewId').value = interviewId;
        document.getElementById('applicantName').value = applicantName;
        document.getElementById('scheduleDeadlineStart').value = deadlineStart || '';
        document.getElementById('scheduleDeadlineEnd').value = deadlineEnd || '';
        document.getElementById('scheduleModal').classList.add('show');
        
        const dateInput = document.getElementById('scheduleDate');
        const timeInput = document.getElementById('scheduleTime');

        const now = new Date();
        now.setHours(now.getHours() + 1);

        // Default min date is today (local)
        const todayStr = formatDateForInput(now);
        dateInput.min = todayStr;

        // Apply interview window limits if provided
        if (deadlineStart) {
            const startDate = new Date(deadlineStart);
            const startStr = formatDateForInput(startDate);
            // max of (today, start date)
            dateInput.min = startStr > todayStr ? startStr : todayStr;
        }
        if (deadlineEnd) {
            const endDate = new Date(deadlineEnd);
            const endStr = formatDateForInput(endDate);
            dateInput.max = endStr;
        }

        // Reset time field
        timeInput.value = '';
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

        const dateValue = formData.get('schedule_date');
        const timeValue = formData.get('schedule_time');
        if (!dateValue || !timeValue) {
            alert('Please select both interview date and time.');
            return;
        }

        // Combine date and time into ISO-like string (YYYY-MM-DDTHH:MM)
        const scheduleDateTime = `${dateValue}T${timeValue}`;

        // Validate against interview window if provided
        const deadlineStart = formData.get('schedule_deadline_start');
        const deadlineEnd = formData.get('schedule_deadline_end');
        const scheduleDateObj = new Date(scheduleDateTime);

        if (deadlineStart) {
            const startObj = new Date(deadlineStart);
            if (scheduleDateObj < startObj) {
                alert('Selected time is before the allowed interview window.');
                return;
            }
        }
        if (deadlineEnd) {
            const endObj = new Date(deadlineEnd);
            // Allow any time on the deadline day by pushing to end-of-day
            endObj.setHours(23, 59, 59, 999);
            if (scheduleDateObj > endObj) {
                alert('Selected time is beyond the allowed interview window.');
                return;
            }
        }

        const data = {
            schedule_date: scheduleDateTime,
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


    // Reschedule Modal Functions
    function openRescheduleModal(interviewId, applicantName, deadlineStart, deadlineEnd, currentSchedule) {
        document.getElementById('rescheduleInterviewId').value = interviewId;
        document.getElementById('rescheduleApplicantName').value = applicantName;
        document.getElementById('rescheduleDeadlineStart').value = deadlineStart || '';
        document.getElementById('rescheduleDeadlineEnd').value = deadlineEnd || '';
        
        // Format current schedule for display
        if (currentSchedule) {
            const currentDate = new Date(currentSchedule);
            const formattedDate = currentDate.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            document.getElementById('currentSchedule').value = formattedDate;
        } else {
            document.getElementById('currentSchedule').value = 'Not scheduled';
        }
        
        document.getElementById('rescheduleModal').classList.add('show');
        
        const dateInput = document.getElementById('rescheduleDate');
        const timeInput = document.getElementById('rescheduleTime');

        const now = new Date();
        now.setHours(now.getHours() + 1);

        // Default min date is today (local)
        const todayStr = formatDateForInput(now);
        dateInput.min = todayStr;

        // Apply interview window limits if provided
        if (deadlineStart) {
            const startDate = new Date(deadlineStart);
            const startStr = formatDateForInput(startDate);
            dateInput.min = startStr > todayStr ? startStr : todayStr;
        }
        if (deadlineEnd) {
            const endDate = new Date(deadlineEnd);
            const endStr = formatDateForInput(endDate);
            dateInput.max = endStr;
        }

        // Reset time field
        timeInput.value = '';
    }

    function closeRescheduleModal() {
        document.getElementById('rescheduleModal').classList.remove('show');
        document.getElementById('rescheduleForm').reset();
    }

    function submitReschedule(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const interviewId = formData.get('interview_id');

        const dateValue = formData.get('schedule_date');
        const timeValue = document.getElementById('rescheduleTime').value;
        if (!dateValue || !timeValue) {
            alert('Please select both interview date and time.');
            return;
        }

        // Combine date and time into ISO-like string (YYYY-MM-DDTHH:MM)
        const scheduleDateTime = `${dateValue}T${timeValue}`;

        // Validate against interview window if provided
        const deadlineStart = formData.get('schedule_deadline_start');
        const deadlineEnd = formData.get('schedule_deadline_end');
        const scheduleDateObj = new Date(scheduleDateTime);

        if (deadlineStart) {
            const startObj = new Date(deadlineStart);
            if (scheduleDateObj < startObj) {
                alert('Selected time is before the allowed interview window.');
                return;
            }
        }
        if (deadlineEnd) {
            const endObj = new Date(deadlineEnd);
            endObj.setHours(23, 59, 59, 999);
            if (scheduleDateObj > endObj) {
                alert('Selected time is beyond the allowed interview window.');
                return;
            }
        }

        const data = {
            schedule_date: scheduleDateTime,
            notes: formData.get('notes'),
            notify_email: formData.get('notify_email') === '1' || formData.get('notify_email') === 1
        };
        
        fetch(`/instructor/interviews/${interviewId}/reschedule`, {
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
                closeRescheduleModal();
                location.reload();
            } else {
                alert(data.message || 'Failed to reschedule interview');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Send Reminder Function
    function sendReminder(interviewId) {
        if (!confirm('Send a reminder email to the applicant about their scheduled interview?')) {
            return;
        }
        
        fetch(`/instructor/interviews/${interviewId}/send-notification`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Reminder email sent successfully!');
            } else {
                alert(data.message || 'Failed to send reminder email');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Remarks Modal Functions
    function openRemarksModal(interviewId, applicantName, currentRemarks) {
        if (!interviewId) {
            alert('No interview found for this applicant.');
            return;
        }
        
        document.getElementById('remarksInterviewId').value = interviewId;
        document.getElementById('remarksApplicantName').value = applicantName;
        document.getElementById('remarksText').value = currentRemarks || '';
        document.getElementById('remarksModal').classList.add('show');
    }

    function closeRemarksModal() {
        document.getElementById('remarksModal').classList.remove('show');
        document.getElementById('remarksForm').reset();
    }

    function submitRemarks(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const interviewId = formData.get('interview_id');

        const data = {
            remarks: formData.get('remarks')
        };
        
        fetch(`/instructor/interviews/${interviewId}/remarks`, {
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
                alert(data.message || 'Remarks saved successfully!');
                closeRemarksModal();
                location.reload();
            } else {
                alert(data.message || 'Failed to save remarks');
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
    
    document.getElementById('rescheduleModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeRescheduleModal();
    });
    
    document.getElementById('remarksModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeRemarksModal();
    });


    // Export Drawer Functions
    function openExportDrawer() {
        document.getElementById('exportDrawer').classList.add('show');
    }

    function closeExportDrawer() {
        document.getElementById('exportDrawer').classList.remove('show');
    }

    function exportReport(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const reportType = formData.get('report_type');
        
        if (!reportType) {
            alert('Please select a report type.');
            return;
        }

        const exportBtn = document.getElementById('exportBtn');
        const originalText = exportBtn.innerHTML;
        exportBtn.disabled = true;
        exportBtn.innerHTML = '<svg style="width: 16px; height: 16px; margin-right: 6px; animation: spin 1s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Generating...';

        // Create a form to submit
        const exportForm = document.createElement('form');
        exportForm.method = 'POST';
        exportForm.action = '{{ route("instructor.export.report") }}';
        exportForm.style.display = 'none';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        exportForm.appendChild(csrfInput);
        
        const reportTypeInput = document.createElement('input');
        reportTypeInput.type = 'hidden';
        reportTypeInput.name = 'report_type';
        reportTypeInput.value = reportType;
        exportForm.appendChild(reportTypeInput);
        
        document.body.appendChild(exportForm);
        exportForm.submit();
        
        // Reset button after a delay
        setTimeout(() => {
            exportBtn.disabled = false;
            exportBtn.innerHTML = originalText;
            closeExportDrawer();
        }, 2000);
    }

    // Close drawer on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeExportDrawer();
        }
    });

    // AJAX Pagination - Use event delegation for dynamically added pagination links
    document.addEventListener('click', function(e) {
        // Check if click is on a pagination link
        let paginationLink = e.target.closest('.pagination a, .pagination-wrapper a');
        
        // Also check if clicked element is itself a link within pagination
        if (!paginationLink && e.target.tagName === 'A' && e.target.closest('.pagination, .pagination-wrapper')) {
            paginationLink = e.target;
        }
        
        if (paginationLink && paginationLink.href && paginationLink.href !== window.location.href) {
            e.preventDefault();
            e.stopPropagation();
            const url = paginationLink.href;
            
            if (!url || url === '#' || url === 'javascript:void(0)') return;
            
            // Show loading state
            const tableSection = document.querySelector('.applicants-table-section');
            const paginationWrapper = document.querySelector('.pagination-wrapper');
            
            if (tableSection) {
                tableSection.style.opacity = '0.5';
                tableSection.style.pointerEvents = 'none';
            }
            if (paginationWrapper) {
                paginationWrapper.style.opacity = '0.5';
                paginationWrapper.style.pointerEvents = 'none';
            }
            
            // Load applicants with AJAX
            fetch(url, {
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
                            history.replaceState({}, '', url);
                            initializeApplicantFilters();
                            updateBulkActions();
                            reinitializeTooltips();
                            
                            // Restore opacity
                            if (tableSection) {
                                tableSection.style.opacity = '1';
                                tableSection.style.pointerEvents = 'auto';
                            }
                            if (paginationWrapper) {
                                paginationWrapper.style.opacity = '1';
                                paginationWrapper.style.pointerEvents = 'auto';
                            }
                        }
                    }
                } else {
                    // Fallback to page reload
                    window.location.href = url;
                }
            })
            .catch(error => {
                console.error('Pagination error:', error);
                // Restore opacity before reload
                if (tableSection) {
                    tableSection.style.opacity = '1';
                    tableSection.style.pointerEvents = 'auto';
                }
                if (paginationWrapper) {
                    paginationWrapper.style.opacity = '1';
                    paginationWrapper.style.pointerEvents = 'auto';
                }
                window.location.href = url;
            });
        }
    });

    // Export Drawer Functions
    function openExportDrawer() {
        document.getElementById('exportDrawer').classList.add('show');
    }

    function closeExportDrawer() {
        document.getElementById('exportDrawer').classList.remove('show');
    }

    function exportReport(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const reportType = formData.get('report_type');
        
        if (!reportType) {
            alert('Please select a report type.');
            return;
        }

        const exportBtn = document.getElementById('exportBtn');
        const originalText = exportBtn.innerHTML;
        exportBtn.disabled = true;
        exportBtn.innerHTML = '<svg style="width: 16px; height: 16px; margin-right: 6px; animation: spin 1s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Generating...';

        // Create a form to submit
        const exportForm = document.createElement('form');
        exportForm.method = 'POST';
        exportForm.action = '{{ route("instructor.export.report") }}';
        exportForm.style.display = 'none';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        exportForm.appendChild(csrfInput);
        
        const reportTypeInput = document.createElement('input');
        reportTypeInput.type = 'hidden';
        reportTypeInput.name = 'report_type';
        reportTypeInput.value = reportType;
        exportForm.appendChild(reportTypeInput);
        
        document.body.appendChild(exportForm);
        exportForm.submit();
        
        // Reset button after a delay
        setTimeout(() => {
            exportBtn.disabled = false;
            exportBtn.innerHTML = originalText;
            closeExportDrawer();
        }, 2000);
    }

    // Close drawer on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeExportDrawer();
        }
    });
</script>
@endpush
