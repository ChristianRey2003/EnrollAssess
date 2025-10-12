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
                                <span>{{ $interview->applicant->score ? number_format($interview->applicant->score, 1) . '%' : 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="interview-actions">
                            <a href="{{ route('instructor.interview.show', $interview->applicant->applicant_id) }}" 
                               class="btn btn-primary">
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
                            <div class="applicant-info">
                                <h4>{{ $interview->applicant->first_name }} {{ $interview->applicant->last_name }}</h4>
                                <p>{{ $interview->applicant->email_address }}</p>
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
                                <span>{{ $interview->applicant->score ? number_format($interview->applicant->score, 1) . '%' : 'N/A' }}</span>
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
                            <button onclick="scheduleInterview({{ $interview->interview_id }})" 
                                    class="btn btn-primary">
                                Schedule Interview
                            </button>
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
    function scheduleInterview(interviewId) {
        document.getElementById('interviewId').value = interviewId;
        document.getElementById('scheduleModal').classList.add('show');
        
        // Set minimum date to current date + 1 hour
        const now = new Date();
        now.setHours(now.getHours() + 1);
        document.getElementById('scheduleDate').min = now.toISOString().slice(0, 16);
    }

    function rescheduleInterview(interviewId) {
        // Find the interview data to pre-populate the form
        const interviewCard = document.querySelector(`button[onclick="rescheduleInterview(${interviewId})"]`).closest('.interview-card');
        const scheduleDate = interviewCard.querySelector('.meta-item span').textContent; // This would need to be improved
        
        document.getElementById('interviewId').value = interviewId;
        document.getElementById('scheduleModal').classList.add('show');
        
        // Set minimum date to current date + 1 hour
        const now = new Date();
        now.setHours(now.getHours() + 1);
        document.getElementById('scheduleDate').min = now.toISOString().slice(0, 16);
        
        // Update modal title for rescheduling
        document.querySelector('.modal-title').textContent = 'Reschedule Interview';
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.remove('show');
        document.getElementById('scheduleForm').reset();
        document.querySelector('.modal-title').textContent = 'Schedule Interview';
    }

    // Handle form submission
    document.getElementById('scheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
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
                location.reload();
            } else {
                alert(data.message || 'Failed to schedule interview');
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
