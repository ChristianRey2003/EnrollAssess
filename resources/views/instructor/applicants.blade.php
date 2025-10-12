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

    .filters-section {
        background: white;
        padding: 20px 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
        margin-bottom: 24px;
    }

    .filters-form {
        display: flex;
        gap: 16px;
        align-items: end;
    }

    .form-group {
        flex: 1;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 0.875rem;
    }

    .form-input, .form-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--maroon-primary);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
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
        justify-content: between;
        align-items: center;
    }

    .table-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }

    .applicants-table {
        width: 100%;
        border-collapse: collapse;
    }

    .applicants-table th,
    .applicants-table td {
        padding: 16px;
        text-align: left;
        border-bottom: 1px solid #F3F4F6;
    }

    .applicants-table th {
        background: #F9FAFB;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .applicants-table tr:hover {
        background: #F9FAFB;
    }

    .applicant-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .applicant-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--maroon-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
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
        .filters-form {
            flex-direction: column;
            align-items: stretch;
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
    <!-- Filters and Search -->
    <div class="filters-section">
        <form method="GET" action="{{ route('instructor.applicants') }}" class="filters-form">
            <div class="form-group">
                <label class="form-label">Search Applicants</label>
                <input type="text" name="search" class="form-input" 
                       placeholder="Search by name or email..."
                       value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="exam-completed" {{ request('status') == 'exam-completed' ? 'selected' : '' }}>Exam Completed</option>
                    <option value="interview-completed" {{ request('status') == 'interview-completed' ? 'selected' : '' }}>Interview Completed</option>
                    <option value="admitted" {{ request('status') == 'admitted' ? 'selected' : '' }}>Admitted</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>

    <!-- Applicants Table -->
    <div class="applicants-table-section">
        <!-- Bulk Actions Bar -->
        <div class="bulk-actions-bar" id="bulkActionsBar">
            <div class="bulk-actions-info">
                <span id="selectedCount">0</span> applicant(s) selected
            </div>
            <div class="bulk-actions-buttons">
                <button type="button" class="btn btn-white" onclick="openBulkScheduleModal()">
                    Schedule Selected
                </button>
                <button type="button" class="btn btn-outline-white" onclick="clearSelection()">
                    Clear Selection
                </button>
            </div>
        </div>

        <div class="table-header">
            <h2 class="table-title">Assigned Applicants ({{ $assignedApplicants->total() }})</h2>
        </div>
        
        @if($assignedApplicants->count() > 0)
            <table class="applicants-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                        </th>
                        <th>Applicant</th>
                        <th>Application No.</th>
                        <th>Exam Score</th>
                        <th>Status</th>
                        <th>Interview Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignedApplicants as $applicant)
                    <tr>
                        <td>
                            @php
                                $interview = $applicant->latestInterview;
                                $canSchedule = $interview && (!$interview->schedule_date || $interview->status === 'assigned');
                            @endphp
                            @if($canSchedule)
                                <input type="checkbox" class="applicant-checkbox" 
                                       data-interview-id="{{ $interview->interview_id }}"
                                       data-applicant-name="{{ $applicant->first_name }} {{ $applicant->last_name }}"
                                       onchange="updateBulkActions()">
                            @endif
                        </td>
                        <td>
                            <div class="applicant-cell">
                                <div class="applicant-avatar">
                                    {{ substr($applicant->first_name ?? 'A', 0, 1) }}{{ substr($applicant->last_name ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <div class="applicant-name">{{ $applicant->first_name }} {{ $applicant->last_name }}</div>
                                    <div class="applicant-email">{{ $applicant->email_address }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $applicant->application_no }}</td>
                        <td>
                            @if($applicant->score)
                                <span class="status-badge {{ $applicant->score >= 70 ? 'status-completed' : 'status-pending' }}">
                                    {{ number_format($applicant->score, 1) }}%
                                </span>
                            @else
                                <span class="status-badge status-pending">Pending</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ str_replace([' ', '-'], ['', ''], strtolower($applicant->status)) }}">
                                {{ ucfirst(str_replace('-', ' ', $applicant->status)) }}
                            </span>
                        </td>
                        <td>
                            @if($interview && $interview->schedule_date)
                                {{ $interview->schedule_date->format('M d, Y g:i A') }}
                            @else
                                <span style="color: #6B7280;">Not scheduled</span>
                            @endif
                        </td>
                        <td>
                            @if($interview && $canSchedule)
                                <button type="button" class="btn btn-primary btn-small" 
                                        onclick="openScheduleModal({{ $interview->interview_id }}, '{{ $applicant->first_name }} {{ $applicant->last_name }}')">
                                    Schedule Interview
                                </button>
                            @elseif($applicant->status === 'exam-completed' || $applicant->status === 'interview-scheduled')
                                <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                   class="btn btn-primary btn-small">
                                    Start Interview
                                </a>
                            @elseif($applicant->status === 'interview-completed')
                                <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                   class="btn btn-secondary btn-small">
                                    View Interview
                                </a>
                            @else
                                <span style="color: #6B7280; font-size: 0.875rem;">Waiting for exam</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            @if($assignedApplicants->hasPages())
            <div class="pagination-wrapper">
                {{ $assignedApplicants->links() }}
            </div>
            @endif
        @else
            <div class="empty-state">
                <h3>No Applicants Found</h3>
                <p>No applicants match your current search criteria. Try adjusting your filters or check back later.</p>
            </div>
        @endif
    </div>
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
</script>
@endpush
