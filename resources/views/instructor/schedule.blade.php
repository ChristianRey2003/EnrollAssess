@extends('layouts.instructor')

@section('title', 'Schedule')

@php
    $pageTitle = 'Interview Schedule';
    $pageSubtitle = 'Manage your interview appointments and schedule';
@endphp

@push('styles')
<style>
    .schedule-container {
        width: 100%;
        max-width: none;
        margin: 0;
    }


    .schedule-section {
        width: 100%;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }

    .section-header {
        padding: 20px 24px;
        background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
        display: inline-block;
        margin-right: 12px;
    }

    .section-count {
        background: var(--maroon-primary);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-block;
    }

    .section-content {
        padding: 24px;
    }

    .table-responsive {
        margin-bottom: 0;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background-color: white !important;
    }

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

    .applicant-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .applicant-name {
        font-weight: 500;
        color: #1F2937;
    }

    .applicant-email {
        font-size: 12px;
        color: #6B7280;
    }

    .interview-card {
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
        transition: all 0.3s ease;
    }

    .interview-card:hover {
        border-color: var(--maroon-primary);
        box-shadow: 0 2px 8px rgba(128, 0, 32, 0.1);
    }

    .interview-card:last-child {
        margin-bottom: 0;
    }

    .interview-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .applicant-info h4 {
        font-weight: 600;
        color: #1F2937;
        margin: 0 0 4px 0;
    }

    .applicant-info p {
        color: #6B7280;
        font-size: 0.875rem;
        margin: 0;
    }

    .interview-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
        font-size: 0.875rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6B7280;
    }

    .meta-label {
        font-weight: 500;
        color: #374151;
    }

    .interview-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-start;
    }

    .btn {
        padding: 8px 16px;
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

    .btn-primary.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    .btn-secondary {
        background: #6B7280;
        color: white;
    }

    .btn-secondary-light {
        background: #ffffff;
        color: #374151;
        border: 1px solid #D1D5DB;
    }

    .btn-secondary-light:hover {
        background: #F9FAFB;
        color: #111827;
    }

    .btn-secondary:hover {
        background: #4B5563;
        color: white;
    }

    .btn-outline {
        background: transparent;
        color: var(--maroon-primary);
        border: 2px solid var(--maroon-primary);
    }

    .btn-outline:hover {
        background: var(--maroon-primary);
        color: white;
    }

    /* Tooltip for disabled actions */
    .tooltip {
        position: relative;
        display: inline-block;
    }
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

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-scheduled {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .status-pending {
        background: #FEF3C7;
        color: #F59E0B;
    }

    .status-completed {
        background: #D1FAE5;
        color: #059669;
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

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 0.875rem;
    }

    .form-input, .form-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .form-input:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--maroon-primary);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 80px;
    }


    .bulk-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        align-items: end;
    }

    .bulk-form-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .bulk-form-item.bulk-action-item {
        justify-content: flex-end;
    }

    .pending-search-input {
        width: 220px;
        height: 40px;
        padding: 8px 32px 8px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 0.875rem;
        background: white;
    }

    .pending-search-input:focus {
        outline: none;
        border-color: var(--maroon-primary);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .bulk-input {
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 0.875rem;
        background: white;
    }

    .bulk-input:focus {
        outline: none;
        border-color: var(--maroon-primary);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .bulk-drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(17, 24, 39, 0.45);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        z-index: 1200;
    }

    .bulk-drawer-overlay.active {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .bulk-drawer {
        position: fixed;
        top: 0;
        right: -520px;
        width: min(520px, 92vw);
        height: 100%;
        background: white;
        box-shadow: -4px 0 12px rgba(15, 23, 42, 0.15);
        transition: right 0.3s ease;
        z-index: 1201;
        display: flex;
        flex-direction: column;
        pointer-events: none;
    }

    .bulk-drawer.active {
        right: 0;
        pointer-events: auto;
    }

    .bulk-drawer-header {
        padding: 15px 22px;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .bulk-drawer-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }

    .bulk-drawer-body {
        padding: 24px;
        flex: 1;
        overflow-y: auto;
    }

    .bulk-drawer-footer {
        padding: 20px 24px;
        border-top: 1px solid #E5E7EB;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .bulk-drawer-footer .btn-primary {
        background: var(--maroon-primary) !important;
        border-color: var(--maroon-primary) !important;
        color: #fff;
    }

    .bulk-drawer-footer .btn-primary:hover {
        background: #5C0016 !important;
        border-color: #5C0016 !important;
        color: #fff;
    }

        @media (max-width: 768px) {
        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .bulk-trigger-actions {
            width: 100%;
            justify-content: space-between;
        }

        .bulk-form-grid {
            grid-template-columns: 1fr;
        }

        .bulk-drawer {
            width: 100%;
        }

        .table-responsive {
            overflow-x: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="schedule-container">
    <!-- Pending Scheduling Table -->
    <div class="schedule-section">
        <div class="section-header">
            <div>
                <h2 class="section-title">Pending Scheduling</h2>
                <span class="section-count">{{ $pendingScheduling->count() }}</span>
            </div>
            @if($pendingScheduling->count() > 0)
            <div class="bulk-trigger-actions" style="display: flex; align-items: center; gap: 16px;">
                <div class="bulk-selected-counter" style="font-size: 0.875rem; color: #1F2937; font-weight: 500;">
                    <span data-bulk-selected-count>0</span> selected
                </div>
                <div style="position: relative; width: 220px;">
                    <input
                        type="text"
                        id="pendingSearchInput"
                        class="pending-search-input"
                        placeholder="Search..."
                        aria-label="Search pending applicants">
                    <svg style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; pointer-events: none; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-bulk-drawer-trigger
                    aria-haspopup="dialog"
                    aria-controls="bulkScheduleDrawer"
                    aria-expanded="false"
                    onclick="openBulkScheduleDrawer()">
                    Bulk Schedule
                </button>
            </div>
            @endif
        </div>
        <div class="section-content">
            @if($pendingScheduling->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle" id="pendingScheduleTable">
                        <thead style="background-color: white !important; color: #1F2937 !important;">
                            <tr>
                                <th style="width: 40px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">
                                    <input type="checkbox" 
                                           id="selectAllPending" 
                                           onchange="toggleAllPending()"
                                           class="form-check-input"
                                           style="cursor: pointer;">
                                </th>
                                <th style="width: 180px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-left">Applicant</th>
                                <th style="width: 120px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Application No.</th>
                                <th style="width: 100px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Exam Score</th>
                                <th style="width: 120px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Exam Date</th>
                                <th style="width: 150px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Course</th>
                                <th style="width: 200px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingScheduling as $interview)
                            @php
                                $searchText = strtolower(
                                    trim(
                                        implode(' ', [
                                            $interview->applicant->first_name ?? '',
                                            $interview->applicant->last_name ?? '',
                                            $interview->applicant->email_address ?? '',
                                            $interview->applicant->application_no ?? '',
                                            $interview->applicant->preferred_course ?? ''
                                        ])
                                    )
                                );
                            @endphp
                            <tr data-pending-row data-search="{{ $searchText }}">
                                <td class="text-center">
                                    <input type="checkbox" 
                                           class="interview-checkbox form-check-input" 
                                           data-interview-id="{{ $interview->interview_id }}"
                                           data-deadline-start="{{ $interview->interview_deadline_start ? $interview->interview_deadline_start->toIso8601String() : '' }}"
                                           data-deadline-end="{{ $interview->interview_deadline_end ? $interview->interview_deadline_end->toIso8601String() : '' }}"
                                           onchange="updateBulkSelection()"
                                           style="cursor: pointer;">
                                </td>
                                <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                    <div class="applicant-info">
                                        <div class="applicant-name" style="font-weight: 500; color: #1F2937; margin-bottom: 4px;">{{ $interview->applicant->first_name }} {{ $interview->applicant->last_name }}</div>
                                        <div class="applicant-email" style="font-size: 12px; color: #6B7280;">{{ $interview->applicant->email_address }}</div>
                                    </div>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    <div class="applicant-name" style="font-weight: 500; color: #1F2937;">{{ $interview->applicant->application_no }}</div>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    @php
                                        $examScore = $interview->applicant->enrollassess_score ?? null;
                                    @endphp
                                    @if($examScore !== null)
                                        <div class="applicant-name" style="font-weight: 500; color: #1F2937;">{{ number_format($examScore, 2) }}%</div>
                                    @else
                                        <span class="applicant-email" style="font-size: 12px; color: #6B7280;">-</span>
                                    @endif
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    {{ $interview->applicant->exam_completed_at ? $interview->applicant->exam_completed_at->format('M d, Y') : '-' }}
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    <div class="applicant-name" style="font-weight: 500; color: #1F2937;">{{ $interview->applicant->preferred_course ?? '-' }}</div>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    @php $canSchedule = $interview->applicant->hasCompletedExam(); @endphp
                                    @if($canSchedule)
                                        <button onclick="scheduleInterview(
                                                    {{ $interview->interview_id }},
                                                    '{{ $interview->interview_deadline_start ? $interview->interview_deadline_start->toIso8601String() : '' }}',
                                                    '{{ $interview->interview_deadline_end ? $interview->interview_deadline_end->toIso8601String() : '' }}'
                                                )" 
                                                class="btn btn-primary"
                                                style="font-size: 12px; padding: 6px 12px;">
                                            Schedule Interview
                                        </button>
                                    @else
                                        <span class="tooltip" data-tip="Cannot schedule: applicant must complete the exam">
                                            <button class="btn btn-primary disabled" disabled title="Applicant must complete the exam"
                                                    style="font-size: 12px; padding: 6px 12px;">
                                                Schedule Interview
                                            </button>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <h3>No Pending Scheduling</h3>
                    <p>All assigned applicants have been scheduled for interviews.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@if($pendingScheduling->count() > 0)
<div id="bulkScheduleDrawerOverlay" class="bulk-drawer-overlay" onclick="closeBulkScheduleDrawer()" aria-hidden="true"></div>
<aside id="bulkScheduleDrawer" class="bulk-drawer" role="dialog" aria-modal="true" aria-labelledby="bulkScheduleTitle" aria-hidden="true">
    <div class="bulk-drawer-header">
        <div>
            <h3 class="bulk-drawer-title" id="bulkScheduleTitle">Bulk Scheduling</h3>
        </div>
    </div>
    <div class="bulk-drawer-body">
                <div style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <div style="font-weight: 600; color: #1F2937; margin-bottom: 8px;">How bulk scheduling works</div>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.875rem; color: #4B5563; list-style: disc;">
                        <li>The selected applicants will be scheduled sequentially starting from your chosen date and time.</li>
                        <li>Time intervals are applied between each interview in the order they appear in the pending list.</li>
                        <li>Enable email notifications to automatically inform applicants of their scheduled interview.</li>
                    </ul>
                </div>
        <div class="bulk-schedule-form">
            <div class="bulk-form-grid">
                <div class="bulk-form-item">
                    <label>Start Date *</label>
                    <input type="date" id="bulkStartDate" class="bulk-input" required>
                </div>
                <div class="bulk-form-item">
                    <label>Start Time *</label>
                    <input type="time" id="bulkStartTime" class="bulk-input" required>
                </div>
                <div class="bulk-form-item" style="grid-column: 1 / -1;">
                    <label>Time Interval *</label>
                    <select id="bulkInterval" class="bulk-input">
                        <option value="15">15 minutes</option>
                        <option value="30" selected>30 minutes</option>
                        <option value="45">45 minutes</option>
                        <option value="60">60 minutes</option>
                    </select>
                </div>
                <div class="bulk-form-item">
                    <label>
                        <input type="checkbox" id="bulkNotifyEmail" checked>
                        Send email notifications
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="bulk-drawer-footer">
        <button type="button" class="btn btn-secondary-light" onclick="closeBulkScheduleDrawer()">Cancel</button>
        <button type="button" onclick="submitBulkSchedule()" class="btn btn-primary" id="bulkScheduleBtn" disabled>
            Bulk Schedule
        </button>
    </div>
</aside>
@endif

<!-- Schedule Modal -->
<div id="scheduleModal" class="schedule-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Schedule Interview</h3>
        </div>
        <form id="scheduleForm">
            @csrf
            <input type="hidden" id="interviewId" name="interview_id">
            <input type="hidden" id="scheduleDeadlineStart" name="schedule_deadline_start">
            <input type="hidden" id="scheduleDeadlineEnd" name="schedule_deadline_end">
            
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
            
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                <input type="checkbox" id="notifyEmail" name="notify_email" value="1" checked
                       style="width: 18px; height: 18px; cursor: pointer;">
                <label for="notifyEmail" style="margin: 0; cursor: pointer;">Send email notification to applicant</label>
            </div>
            
            <div class="interview-actions">
                <button type="button" class="btn btn-secondary-light" onclick="closeScheduleModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Schedule Interview</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let bulkDrawerRestoreFocusTo = null;
    let bulkDrawerKeydownCleanup = null;

    // Initialize minimum date for bulk scheduling (same behavior as other schedule UIs)
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        const bulkStartDate = document.getElementById('bulkStartDate');
        if (bulkStartDate) {
            bulkStartDate.min = formatDateForInput(now);
        }
        updateBulkSelection();
        initializePendingSearch();
    });

    function openBulkScheduleDrawer() {
        const overlay = document.getElementById('bulkScheduleDrawerOverlay');
        const drawer = document.getElementById('bulkScheduleDrawer');
        const triggerButton = document.querySelector('[data-bulk-drawer-trigger]');
        if (!overlay || !drawer) {
            console.error('Bulk scheduling drawer elements not found');
            return;
        }

        // Ensure at least one interview is selected
        const checkedBoxes = document.querySelectorAll('.interview-checkbox:checked');
        if (!checkedBoxes || checkedBoxes.length === 0) {
            alert('Please select at least one interview to schedule.');
            return;
        }

        // Configure bulk start date limits based on selected interviews' windows
        const bulkStartDateInput = document.getElementById('bulkStartDate');
        if (bulkStartDateInput) {
            const now = new Date();
            now.setHours(now.getHours() + 1);
            let minDate = now;
            let maxDate = null;

            checkedBoxes.forEach(cb => {
                const deadlineStart = cb.dataset.deadlineStart;
                const deadlineEnd = cb.dataset.deadlineEnd;

                if (deadlineStart) {
                    const startObj = new Date(deadlineStart);
                    if (startObj > minDate) {
                        minDate = startObj;
                    }
                }
                if (deadlineEnd) {
                    const endObj = new Date(deadlineEnd);
                    if (!maxDate || endObj < maxDate) {
                        maxDate = endObj;
                    }
                }
            });

            // Apply min/max derived from windows
            bulkStartDateInput.min = formatDateForInput(minDate);
            if (maxDate) {
                bulkStartDateInput.max = formatDateForInput(maxDate);
            } else {
                bulkStartDateInput.removeAttribute('max');
            }

            // Default value to min (earliest allowed)
            bulkStartDateInput.value = bulkStartDateInput.min;
        }

        bulkDrawerRestoreFocusTo = document.activeElement;

        if (!document.body.dataset.bulkDrawerOverflow) {
            document.body.dataset.bulkDrawerOverflow = document.body.style.overflow || '';
        }

        overlay.classList.add('active');
        drawer.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        drawer.setAttribute('aria-hidden', 'false');
        if (triggerButton) {
            triggerButton.setAttribute('aria-expanded', 'true');
        }
        document.body.style.overflow = 'hidden';

        const focusableSelectors = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
        const focusableElements = Array.from(drawer.querySelectorAll(focusableSelectors));
        const focusTarget = drawer.querySelector('#bulkStartTime') || focusableElements[0];
        if (focusTarget) {
            setTimeout(() => focusTarget.focus(), 150);
        }

        const handleKeydown = (event) => {
            if (event.key !== 'Tab') {
                return;
            }

            if (focusableElements.length === 0) {
                event.preventDefault();
                return;
            }

            const first = focusableElements[0];
            const last = focusableElements[focusableElements.length - 1];
            if (event.shiftKey) {
                if (document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                }
            } else {
                if (document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        };

        drawer.addEventListener('keydown', handleKeydown);
        bulkDrawerKeydownCleanup = () => {
            drawer.removeEventListener('keydown', handleKeydown);
            bulkDrawerKeydownCleanup = null;
        };
    }

    function closeBulkScheduleDrawer() {
        const overlay = document.getElementById('bulkScheduleDrawerOverlay');
        const drawer = document.getElementById('bulkScheduleDrawer');
        const triggerButton = document.querySelector('[data-bulk-drawer-trigger]');
        if (overlay) {
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
        }
        if (drawer) {
            drawer.classList.remove('active');
            drawer.setAttribute('aria-hidden', 'true');
        }
        if (triggerButton) {
            triggerButton.setAttribute('aria-expanded', 'false');
        }

        if (bulkDrawerKeydownCleanup) {
            bulkDrawerKeydownCleanup();
        }
        if (bulkDrawerRestoreFocusTo && typeof bulkDrawerRestoreFocusTo.focus === 'function') {
            bulkDrawerRestoreFocusTo.focus();
        }
        bulkDrawerRestoreFocusTo = null;

        if (document.body.dataset.bulkDrawerOverflow !== undefined) {
            document.body.style.overflow = document.body.dataset.bulkDrawerOverflow;
            delete document.body.dataset.bulkDrawerOverflow;
        }

        const bulkBtn = document.getElementById('bulkScheduleBtn');
        if (bulkBtn && bulkBtn.dataset.loading !== 'true') {
            bulkBtn.textContent = 'Bulk Schedule';
        }

        updateBulkSelection();
    }

    // Toggle all pending interview checkboxes
    function toggleAllPending() {
        const selectAll = document.getElementById('selectAllPending');
        const checkboxes = document.querySelectorAll('.interview-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkSelection();
    }

    // Update bulk selection count and button state
    function updateBulkSelection() {
        const checkedBoxes = document.querySelectorAll('.interview-checkbox:checked');
        const count = checkedBoxes.length;
        document.querySelectorAll('[data-bulk-selected-count]').forEach(el => {
            el.textContent = count;
        });

        const bulkBtn = document.getElementById('bulkScheduleBtn');
        if (bulkBtn && bulkBtn.dataset.loading !== 'true') {
            bulkBtn.disabled = count === 0;
        }
        
        // Update "Select All" checkbox state
        const allCheckboxes = document.querySelectorAll('.interview-checkbox');
        const selectAllCheckbox = document.getElementById('selectAllPending');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = allCheckboxes.length > 0 && count === allCheckboxes.length;
        }
    }

    function initializePendingSearch() {
        const searchInput = document.getElementById('pendingSearchInput');
        if (!searchInput) {
            return;
        }

        const applySearch = () => {
            const query = searchInput.value.trim().toLowerCase();
            document.querySelectorAll('[data-pending-row]').forEach(row => {
                const text = row.dataset.search || '';
                row.style.display = text.includes(query) ? '' : 'none';
            });
        };

        searchInput.addEventListener('input', applySearch);
        applySearch();
    }

    // Helper to format date for datetime-local input (YYYY-MM-DDTHH:MM)
    function formatDateTimeLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    // Helper to format date for date input (YYYY-MM-DD)
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Submit bulk schedule
    function submitBulkSchedule() {
        const checkedBoxes = document.querySelectorAll('.interview-checkbox:checked');
        const interviewIds = Array.from(checkedBoxes).map(cb => cb.dataset.interviewId);
        
        if (interviewIds.length === 0) {
            alert('Please select at least one interview to schedule.');
            return;
        }

        const startDate = document.getElementById('bulkStartDate').value;
        const startTime = document.getElementById('bulkStartTime').value;
        const interval = document.getElementById('bulkInterval').value;
        const notifyEmail = document.getElementById('bulkNotifyEmail').checked;

        if (!startDate || !startTime) {
            alert('Please select both start date and time.');
            return;
        }

        const startDateTimeStr = `${startDate}T${startTime}`;

        // Validate start time against interview windows (basic check)
        const startTimeObj = new Date(startDateTimeStr);

        // Also ensure start time is not in the past (with 1 hour buffer)
        const now = new Date();
        now.setHours(now.getHours() + 1);
        if (startTimeObj < now) {
            alert('Start time must be at least 1 hour from now.');
            return;
        }
        for (const cb of checkedBoxes) {
            const deadlineStart = cb.dataset.deadlineStart;
            const deadlineEnd = cb.dataset.deadlineEnd;

            if (deadlineStart) {
                const startObj = new Date(deadlineStart);
                if (startTimeObj < startObj) {
                    alert('Selected start time is before the allowed interview window for one or more applicants.');
                    return;
                }
            }
            if (deadlineEnd) {
                const endObj = new Date(deadlineEnd);
                endObj.setHours(23, 59, 59, 999);
                if (startTimeObj > endObj) {
                    alert('Selected start time is beyond the allowed interview window for one or more applicants.');
                    return;
                }
            }
        }

        // Confirm bulk scheduling
        const message = `Schedule ${interviewIds.length} interview(s) starting at ${new Date(startTime).toLocaleString()} with ${interval}-minute intervals?`;
        if (!confirm(message)) {
            return;
        }

        const bulkBtn = document.getElementById('bulkScheduleBtn');
        if (bulkBtn) {
            bulkBtn.disabled = true;
            bulkBtn.dataset.loading = 'true';
            bulkBtn.textContent = '⏳ Scheduling...';
        }

        fetch('/instructor/interviews/bulk-schedule', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                interview_ids: interviewIds,
                schedule_date_start: startTime,
                time_interval: parseInt(interval),
                notify_email: notifyEmail ? 1 : 0
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = data.message;
                let notificationType = 'success';
                
                // Build email notification message
                if (data.emails_sent !== undefined || data.emails_failed_count !== undefined) {
                    if (data.emails_failed_count > 0) {
                        // Some emails failed - show warning with details
                        const failedNames = data.emails_failed ? data.emails_failed.join(', ') : '';
                        message += ` ${data.emails_sent || 0} email(s) sent successfully, ${data.emails_failed_count} failed: ${failedNames}`;
                        notificationType = 'warning';
                    } else if (data.emails_sent > 0) {
                        // All emails sent successfully
                        message += ` ${data.emails_sent} email(s) sent successfully.`;
                    }
                }
                
                // Add scheduling errors if any
                if (data.errors && data.errors.length > 0) {
                    message += '\n\nErrors:\n' + data.errors.join('\n');
                    notificationType = 'warning';
                }
                
                // Show notification using NotificationSystem
                if (window.NotificationSystem) {
                    if (notificationType === 'warning') {
                        window.NotificationSystem.warning(message);
                    } else {
                        window.NotificationSystem.success(message);
                    }
                } else if (window.showNotification) {
                    window.showNotification(message, notificationType);
                } else {
                    alert(message);
                }
                
                location.reload();
            } else {
                const errorMessage = data.message || 'Failed to schedule interviews';
                if (window.NotificationSystem) {
                    window.NotificationSystem.error(errorMessage);
                } else if (window.showError) {
                    window.showError(errorMessage);
                } else {
                    alert(errorMessage);
                }
                
                if (bulkBtn) {
                    bulkBtn.disabled = interviewIds.length === 0;
                    delete bulkBtn.dataset.loading;
                    bulkBtn.textContent = 'Bulk Schedule';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMessage = 'An error occurred. Please try again.';
            if (window.NotificationSystem) {
                window.NotificationSystem.error(errorMessage);
            } else if (window.showError) {
                window.showError(errorMessage);
            } else {
                alert(errorMessage);
            }
            
            if (bulkBtn) {
                bulkBtn.disabled = interviewIds.length === 0;
                delete bulkBtn.dataset.loading;
                bulkBtn.textContent = 'Bulk Schedule';
            }
        });
    }

    function scheduleInterview(interviewId, deadlineStart = '', deadlineEnd = '') {
        document.getElementById('interviewId').value = interviewId;
        document.getElementById('scheduleModal').classList.add('show');
        
        const dateInput = document.getElementById('scheduleDate');
        const timeInput = document.getElementById('scheduleTime');
        const deadlineStartInput = document.getElementById('scheduleDeadlineStart');
        const deadlineEndInput = document.getElementById('scheduleDeadlineEnd');

        // Store deadlines for validation on submit
        if (deadlineStartInput) deadlineStartInput.value = deadlineStart || '';
        if (deadlineEndInput) deadlineEndInput.value = deadlineEnd || '';

        // Base min date: today (local) + 1 hour
        const now = new Date();
        now.setHours(now.getHours() + 1);
        const todayStr = formatDateForInput(now);
        dateInput.min = todayStr;

        // Apply interview window start if provided
        if (deadlineStart) {
            const startDate = new Date(deadlineStart);
            const startStr = formatDateForInput(startDate);
            // max of (today, start date)
            dateInput.min = startStr > todayStr ? startStr : todayStr;
        }

        // Apply interview window end if provided
        if (deadlineEnd) {
            const endDate = new Date(deadlineEnd);
            const endStr = formatDateForInput(endDate);
            dateInput.max = endStr;
        } else {
            dateInput.removeAttribute('max');
        }

        // Reset time field
        if (timeInput) {
            timeInput.value = '';
        }
    }

    function rescheduleInterview(interviewId, deadlineStart = '', deadlineEnd = '') {
        document.getElementById('interviewId').value = interviewId;
        document.getElementById('scheduleModal').classList.add('show');
        
        const dateInput = document.getElementById('scheduleDate');
        const timeInput = document.getElementById('scheduleTime');
        const deadlineStartInput = document.getElementById('scheduleDeadlineStart');
        const deadlineEndInput = document.getElementById('scheduleDeadlineEnd');

        // Store deadlines for validation on submit
        if (deadlineStartInput) deadlineStartInput.value = deadlineStart || '';
        if (deadlineEndInput) deadlineEndInput.value = deadlineEnd || '';

        // Base min date: today (local) + 1 hour
        const now = new Date();
        now.setHours(now.getHours() + 1);
        const todayStr = formatDateForInput(now);
        dateInput.min = todayStr;

        // Apply interview window start if provided
        if (deadlineStart) {
            const startDate = new Date(deadlineStart);
            const startStr = formatDateForInput(startDate);
            dateInput.min = startStr > todayStr ? startStr : todayStr;
        }

        // Apply interview window end if provided
        if (deadlineEnd) {
            const endDate = new Date(deadlineEnd);
            const endStr = formatDateForInput(endDate);
            dateInput.max = endStr;
        } else {
            dateInput.removeAttribute('max');
        }

        // Reset time field
        if (timeInput) {
            timeInput.value = '';
        }
        
        // Update modal title for rescheduling
        document.querySelector('.modal-title').textContent = 'Reschedule Interview';
        
        // Mark as reschedule mode
        document.getElementById('scheduleForm').dataset.mode = 'reschedule';
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.remove('show');
        document.getElementById('scheduleForm').reset();
        document.getElementById('scheduleForm').dataset.mode = '';
        document.querySelector('.modal-title').textContent = 'Schedule Interview';
    }

    // Handle form submission
    document.getElementById('scheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const interviewId = formData.get('interview_id');
        const mode = this.dataset.mode || 'schedule';
        
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
        
        // Determine endpoint based on mode
        const endpoint = mode === 'reschedule' 
            ? `/instructor/interviews/${interviewId}/reschedule`
            : `/instructor/interviews/${interviewId}/schedule`;
        
        fetch(endpoint, {
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
                location.reload();
            } else {
                alert(data.message || 'Failed to ' + mode + ' interview');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });

    // Close modal when clicking outside
    document.getElementById('scheduleModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeScheduleModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const drawer = document.getElementById('bulkScheduleDrawer');
            if (drawer && drawer.classList.contains('active')) {
                closeBulkScheduleDrawer();
            }
        }
    });
</script>
@endpush
