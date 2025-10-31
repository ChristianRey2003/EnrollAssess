@extends('layouts.instructor')

@section('title', 'Schedule')

@php
    $pageTitle = 'Interview Schedule';
    $pageSubtitle = 'Manage your interview appointments and schedule';
@endphp

@push('styles')
<style>
    .schedule-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .schedule-stats {
        display: flex;
        gap: 32px;
        margin-bottom: 32px;
        background: white;
        padding: 20px 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
    }

    .stat-item {
        flex: 1;
        text-align: center;
        padding: 0 16px;
        border-right: 1px solid #E5E7EB;
    }

    .stat-item:last-child {
        border-right: none;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--maroon-primary);
        margin-bottom: 4px;
    }

    .stat-label {
        color: #6B7280;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .schedule-sections {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
    }

    .schedule-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }

    .section-count {
        background: var(--maroon-primary);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .section-content {
        padding: 24px;
        max-height: 600px;
        overflow-y: auto;
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

    /* Bulk Scheduling Styles */
    .bulk-schedule-section {
        background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
        border: 2px solid #bae6fd;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 32px;
    }

    .bulk-schedule-header {
        margin-bottom: 20px;
    }

    .bulk-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--maroon-primary);
        margin: 0 0 4px 0;
    }

    .bulk-subtitle {
        color: #6B7280;
        font-size: 0.875rem;
        margin: 0;
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

    .bulk-label {
        font-weight: 500;
        color: #374151;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 8px;
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

    @media (max-width: 768px) {
        .schedule-sections {
            grid-template-columns: 1fr;
        }
        
        .schedule-stats {
            flex-direction: column;
            gap: 16px;
        }
        
        .stat-item {
            border-right: none;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 16px;
        }
        
        .stat-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .interview-meta {
            grid-template-columns: 1fr;
        }

        .bulk-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="schedule-container">
    <!-- Statistics -->
    <div class="schedule-stats">
        <div class="stat-item">
            <div class="stat-value">{{ $upcomingInterviews->count() }}</div>
            <div class="stat-label">Upcoming</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $pendingScheduling->count() }}</div>
            <div class="stat-label">Pending Schedule</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $upcomingInterviews->where('schedule_date', '<=', now()->addDay())->count() }}</div>
            <div class="stat-label">Due Soon</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $upcomingInterviews->count() + $pendingScheduling->count() }}</div>
            <div class="stat-label">Total Active</div>
        </div>
    </div>

    <!-- Bulk Scheduling Section -->
    @if($pendingScheduling->count() > 0)
    <div class="bulk-schedule-section">
        <div class="bulk-schedule-header">
            <h3 class="bulk-title"> Bulk Scheduling</h3>
            <p class="bulk-subtitle">Schedule multiple interviews with automatic time distribution</p>
        </div>
        <div class="bulk-schedule-form">
            <div class="bulk-form-grid">
                <div class="bulk-form-item">
                    <label class="bulk-label">
                        <input type="checkbox" id="selectAllPending" onchange="toggleAllPending()">
                        Select All (<span id="selectedCount">0</span> selected)
                    </label>
                </div>
                <div class="bulk-form-item">
                    <label class="bulk-label">Start Date & Time *</label>
                    <input type="datetime-local" id="bulkStartTime" class="bulk-input" required>
                </div>
                <div class="bulk-form-item">
                    <label class="bulk-label">Time Interval *</label>
                    <select id="bulkInterval" class="bulk-input">
                        <option value="15">15 minutes</option>
                        <option value="30" selected>30 minutes</option>
                        <option value="45">45 minutes</option>
                        <option value="60">60 minutes</option>
                    </select>
                </div>
                <div class="bulk-form-item">
                    <label class="bulk-label">
                        <input type="checkbox" id="bulkNotifyEmail" checked>
                        Send email notifications
                    </label>
                </div>
                <div class="bulk-form-item bulk-action-item">
                    <button type="button" onclick="submitBulkSchedule()" class="btn btn-primary" id="bulkScheduleBtn" disabled>
                         Bulk Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Schedule Sections -->
    <div class="schedule-sections">
        <!-- Upcoming Interviews -->
        <div class="schedule-section">
            <div class="section-header">
                <h2 class="section-title">Upcoming Interviews</h2>
                <span class="section-count">{{ $upcomingInterviews->count() }}</span>
            </div>
            <div class="section-content">
                @forelse($upcomingInterviews as $interview)
                    <div class="interview-card">
                        <div class="interview-header">
                            <div class="applicant-info">
                                <h4>{{ $interview->applicant->first_name }} {{ $interview->applicant->last_name }}</h4>
                                <p>{{ $interview->applicant->email_address }}</p>
                            </div>
                            <span class="status-badge status-scheduled">Scheduled</span>
                        </div>
                        
                        <div class="interview-meta">
                            <div class="meta-item">
                                <span class="meta-label">Date:</span>
                                <span>{{ $interview->schedule_date->format('M d, Y') }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Time:</span>
                                <span>{{ $interview->schedule_date->format('g:i A') }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">App No:</span>
                                <span>{{ $interview->applicant->application_no }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Score:</span>
                                @php
                                    $examScore = $interview->applicant->enrollassess_score ?? null;
                                @endphp
                                <span>{{ $examScore !== null ? number_format($examScore, 1) . '%' : 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="interview-actions">
                            @php $canConduct = $interview->applicant->hasCompletedExam(); @endphp
                            <a href="{{ route('instructor.interview.show', $interview->applicant->applicant_id) }}" 
                               class="btn btn-primary{{ !$canConduct ? ' disabled' : '' }}"
                               @if(!$canConduct) aria-disabled="true" tabindex="-1" title="Applicant must complete exam first" @endif>
                                Conduct Interview
                            </a>
                            <button onclick="rescheduleInterview({{ $interview->interview_id }})" 
                                    class="btn btn-outline">
                                Reschedule
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <h3>No Upcoming Interviews</h3>
                        <p>You don't have any interviews scheduled for the coming days.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pending Scheduling -->
        <div class="schedule-section">
            <div class="section-header">
                <h2 class="section-title">Pending Scheduling</h2>
                <span class="section-count">{{ $pendingScheduling->count() }}</span>
            </div>
            <div class="section-content">
                @forelse($pendingScheduling as $interview)
                    <div class="interview-card">
                        <div class="interview-header">
                            <div style="display: flex; align-items: start; gap: 12px; flex: 1;">
                                <input type="checkbox" class="interview-checkbox" 
                                       data-interview-id="{{ $interview->interview_id }}"
                                       onchange="updateBulkSelection()"
                                       style="width: 20px; height: 20px; cursor: pointer; margin-top: 2px;">
                                <div class="applicant-info">
                                    <h4>{{ $interview->applicant->first_name }} {{ $interview->applicant->last_name }}</h4>
                                    <p>{{ $interview->applicant->email_address }}</p>
                                </div>
                            </div>
                            <span class="status-badge status-pending">Pending</span>
                        </div>
                        
                        <div class="interview-meta">
                            <div class="meta-item">
                                <span class="meta-label">App No:</span>
                                <span>{{ $interview->applicant->application_no }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Score:</span>
                                @php
                                    $examScore = $interview->applicant->enrollassess_score ?? null;
                                @endphp
                                <span>{{ $examScore !== null ? number_format($examScore, 1) . '%' : 'N/A' }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Exam Date:</span>
                                <span>{{ $interview->applicant->exam_completed_at ? $interview->applicant->exam_completed_at->format('M d, Y') : 'N/A' }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Course:</span>
                                <span>{{ $interview->applicant->preferred_course ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="interview-actions">
                            @php $canSchedule = $interview->applicant->hasCompletedExam(); @endphp
                            @if($canSchedule)
                                <button onclick="scheduleInterview({{ $interview->interview_id }})" 
                                        class="btn btn-primary">
                                    Schedule Interview
                                </button>
                            @else
                                <span class="tooltip" data-tip="Cannot schedule: applicant must complete the exam">
                                    <button class="btn btn-primary disabled" disabled title="Applicant must complete the exam">
                                        Schedule Interview
                                    </button>
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <h3>No Pending Scheduling</h3>
                        <p>All assigned applicants have been scheduled for interviews.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div id="scheduleModal" class="schedule-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Schedule Interview</h3>
            <button class="close-btn" onclick="closeScheduleModal()">&times;</button>
        </div>
        <form id="scheduleForm">
            @csrf
            <input type="hidden" id="interviewId" name="interview_id">
            
            <div class="form-group">
                <label class="form-label">Interview Date & Time</label>
                <input type="datetime-local" id="scheduleDate" name="schedule_date" class="form-input" required>
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
                <button type="submit" class="btn btn-primary">Schedule Interview</button>
                <button type="button" class="btn btn-secondary" onclick="closeScheduleModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize minimum date for bulk scheduling
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        now.setHours(now.getHours() + 1);
        const bulkStartTime = document.getElementById('bulkStartTime');
        if (bulkStartTime) {
            bulkStartTime.min = now.toISOString().slice(0, 16);
        }
    });

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
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkScheduleBtn').disabled = count === 0;
        
        // Update "Select All" checkbox state
        const allCheckboxes = document.querySelectorAll('.interview-checkbox');
        const selectAllCheckbox = document.getElementById('selectAllPending');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = allCheckboxes.length > 0 && count === allCheckboxes.length;
        }
    }

    // Submit bulk schedule
    function submitBulkSchedule() {
        const checkedBoxes = document.querySelectorAll('.interview-checkbox:checked');
        const interviewIds = Array.from(checkedBoxes).map(cb => cb.dataset.interviewId);
        
        if (interviewIds.length === 0) {
            alert('Please select at least one interview to schedule.');
            return;
        }

        const startTime = document.getElementById('bulkStartTime').value;
        const interval = document.getElementById('bulkInterval').value;
        const notifyEmail = document.getElementById('bulkNotifyEmail').checked;

        if (!startTime) {
            alert('Please select a start date and time.');
            return;
        }

        // Confirm bulk scheduling
        const message = `Schedule ${interviewIds.length} interview(s) starting at ${new Date(startTime).toLocaleString()} with ${interval}-minute intervals?`;
        if (!confirm(message)) {
            return;
        }

        const bulkBtn = document.getElementById('bulkScheduleBtn');
        bulkBtn.disabled = true;
        bulkBtn.textContent = '⏳ Scheduling...';

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
                if (data.emails_sent) {
                    message += ` ${data.emails_sent} email(s) sent.`;
                }
                if (data.errors && data.errors.length > 0) {
                    message += '\n\nErrors:\n' + data.errors.join('\n');
                }
                alert(message);
                location.reload();
            } else {
                alert(data.message || 'Failed to schedule interviews');
                bulkBtn.disabled = false;
                bulkBtn.textContent = ' Bulk Schedule';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            bulkBtn.disabled = false;
            bulkBtn.textContent = ' Bulk Schedule';
        });
    }

    function scheduleInterview(interviewId) {
        document.getElementById('interviewId').value = interviewId;
        document.getElementById('scheduleModal').classList.add('show');
        
        // Set minimum date to current date + 1 hour
        const now = new Date();
        now.setHours(now.getHours() + 1);
        document.getElementById('scheduleDate').min = now.toISOString().slice(0, 16);
    }

    function rescheduleInterview(interviewId) {
        document.getElementById('interviewId').value = interviewId;
        document.getElementById('scheduleModal').classList.add('show');
        
        // Set minimum date to current date + 1 hour
        const now = new Date();
        now.setHours(now.getHours() + 1);
        document.getElementById('scheduleDate').min = now.toISOString().slice(0, 16);
        
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
        
        const data = {
            schedule_date: formData.get('schedule_date'),
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
</script>
@endpush
