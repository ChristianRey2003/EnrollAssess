@extends('layouts.admin')

@section('title', 'Interview Pool Management')

@php
    $pageTitle = 'Interview Pool Management';
    $pageSubtitle = 'Department Head override and assignment control';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/interviews.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-maroon: #800020;
            --maroon-dark: #5C0016;
        }

        .pool-overview-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 24px;
        }

        .pool-header {
            background: linear-gradient(135deg, #800020 0%, #5C0016 100%);
            color: white;
            padding: 32px;
            border-radius: 16px;
            margin-bottom: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .pool-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .pool-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        .pool-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            min-width: 0; /* Allow cards to shrink */
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #800020;
            margin-bottom: 6px;
        }

        .stat-label {
            color: #6B7280;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .pool-controls {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 32px;
        }

        .controls-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 24px;
        }

        .controls-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1F2937;
            margin: 0;
        }

        .bulk-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .filters-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 16px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.875rem;
        }

        .form-input, .form-select {
            padding: 10px 12px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #800020;
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
        }

        .pool-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }

        .pool-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .section-header {
            padding: 24px;
            background: #F9FAFB;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1F2937;
            margin: 0;
        }

        .section-count {
            background: #800020;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .section-content {
            padding: 24px;
            max-height: 700px;
            overflow-y: auto;
        }

        .interview-card {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            position: relative;
        }

        .interview-card:hover {
            border-color: #800020;
            box-shadow: 0 4px 16px rgba(128, 0, 32, 0.1);
        }

        .interview-card.selected {
            border-color: #800020;
            background: rgba(128, 0, 32, 0.05);
        }

        .interview-card:last-child {
            margin-bottom: 0;
        }

        .card-checkbox {
            position: absolute;
            top: 16px;
            right: 16px;
        }

        .applicant-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            padding-right: 40px;
        }

        .applicant-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 4px;
        }

        .applicant-email {
            color: #6B7280;
            font-size: 0.875rem;
        }

        .priority-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .priority-high {
            background: #FEE2E2;
            color: #DC2626;
        }

        .priority-medium {
            background: #FEF3C7;
            color: #F59E0B;
        }

        .priority-low {
            background: #DCFCE7;
            color: #059669;
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

        .meta-icon {
            width: 16px;
            height: 16px;
            opacity: 0.7;
        }

        .claimed-by {
            background: #EDE9FE;
            color: #7C3AED;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 16px;
        }

        .interview-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.75rem;
        }

        .btn-primary {
            background: #800020 !important;
            color: white !important;
        }

        .btn-primary:hover {
            background: #5C0016 !important;
            color: white !important;
        }

        .btn-success {
            background: #059669;
            color: white;
        }

        .btn-success:hover {
            background: #047857;
        }

        .btn-warning {
            background: #F59E0B;
            color: white;
        }

        .btn-warning:hover {
            background: #D97706;
        }

        .btn-danger {
            background: #DC2626;
            color: white;
        }

        .btn-danger:hover {
            background: #B91C1C;
        }

        .btn-outline {
            background: transparent;
            color: #800020;
            border: 1px solid #800020;
        }

        .btn-outline:hover {
            background: #800020;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #6B7280;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            opacity: 0.5;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            margin-bottom: 24px;
        }

        .modal-title {
            font-size: 1.5rem;
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

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1100;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: #059669;
        }

        .notification.error {
            background: #DC2626;
        }

        @media (max-width: 1024px) {
            .pool-sections {
                grid-template-columns: 1fr;
            }
            
            .filters-form {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .pool-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
        }

        @media (max-width: 480px) {
            .pool-stats {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
@endpush

@section('content')
<div class="pool-overview-container">
    <!-- Header is provided by the admin layout via $pageTitle/$pageSubtitle -->

    <!-- Pool Statistics -->
    <div class="pool-stats">
        <div class="stat-card">
            <div class="stat-value">{{ $poolStats['total_available'] }}</div>
            <div class="stat-label">Available Interviews</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $poolStats['total_claimed'] }}</div>
            <div class="stat-label">Claimed Interviews</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $poolStats['claimed_by_dh'] ?? 0 }}</div>
            <div class="stat-label">Claimed by DH</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $poolStats['claimed_by_instructors'] ?? 0 }}</div>
            <div class="stat-label">Claimed by Instructors</div>
        </div>
    </div>

    <!-- Pool Controls -->
    <div class="pool-controls">
        <div class="controls-header">
            <h2 class="controls-title">Pool Controls</h2>
            <div class="bulk-actions">
                <button onclick="showBulkAssignModal()" class="btn btn-primary" id="bulk-assign-btn" disabled>
                    Bulk Assign (<span id="selected-count">0</span>)
                </button>
                <button onclick="refreshPoolData()" class="btn btn-outline">
                    Refresh Data
                </button>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('admin.interviews.pool.overview') }}" class="filters-form">
            <div class="form-group">
                <label class="form-label">Search Applicants</label>
                <input type="text" name="search" class="form-input" 
                       placeholder="Search by name or email..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Priority Level</label>
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="high" {{ ($filters['priority'] ?? '') == 'high' ? 'selected' : '' }}>High Priority</option>
                    <option value="medium" {{ ($filters['priority'] ?? '') == 'medium' ? 'selected' : '' }}>Medium Priority</option>
                    <option value="low" {{ ($filters['priority'] ?? '') == 'low' ? 'selected' : '' }}>Low Priority</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="available" {{ ($filters['status'] ?? '') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="claimed" {{ ($filters['status'] ?? '') == 'claimed' ? 'selected' : '' }}>Claimed</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- Pool Sections -->
    <div class="pool-sections">
        <!-- Available Interviews -->
        <div class="pool-section">
            <div class="section-header">
                <h2 class="section-title">Available Interviews</h2>
                <span class="section-count" id="available-count">{{ $availableInterviews->count() }}</span>
            </div>
            <div class="section-content" id="available-interviews">
                @forelse($availableInterviews as $interview)
                    <div class="interview-card" data-interview-id="{{ $interview->interview_id }}">
                        <input type="checkbox" class="card-checkbox interview-checkbox" 
                               value="{{ $interview->interview_id }}" 
                               onchange="updateBulkActions()">
                        
                        <div class="applicant-info">
                            <div>
                                <div class="applicant-name">{{ $interview->applicant->first_name }} {{ $interview->applicant->last_name }}</div>
                                <div class="applicant-email">{{ $interview->applicant->email }}</div>
                            </div>
                            <div class="priority-badge priority-{{ $interview->priority_level }}" 
                                 onclick="showPriorityModal({{ $interview->interview_id }}, '{{ $interview->priority_level }}')">
                                {{ ucfirst($interview->priority_level) }} Priority
                            </div>
                        </div>
                        
                        <div class="interview-meta">
                            <div class="meta-item">
                                <span class="meta-icon">📊</span>
                                <span>Exam Score: {{ $interview->applicant->exam_percentage ?? 'N/A' }}%</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon">📅</span>
                                <span>Added: {{ $interview->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="interview-actions">
                            <button onclick="dhClaimInterview({{ $interview->interview_id }})" 
                                    class="btn btn-success btn-sm">
                                I'll Conduct This
                            </button>
                            <button onclick="showAssignModal({{ $interview->interview_id }})" 
                                    class="btn btn-primary btn-sm">
                                Assign to Instructor
                            </button>
                            <button onclick="viewApplicant({{ $interview->applicant->applicant_id }})" 
                                    class="btn btn-outline btn-sm">
                                View Details
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <h3>No Available Interviews</h3>
                        <p>All interviews have been claimed or assigned.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Claimed Interviews -->
        <div class="pool-section">
            <div class="section-header">
                <h2 class="section-title">Claimed Interviews</h2>
                <span class="section-count" id="claimed-count">{{ $claimedInterviews->count() }}</span>
            </div>
            <div class="section-content" id="claimed-interviews">
                @forelse($claimedInterviews as $interview)
                    <div class="interview-card" data-interview-id="{{ $interview->interview_id }}">
                        <div class="applicant-info">
                            <div>
                                <div class="applicant-name">{{ $interview->applicant->first_name }} {{ $interview->applicant->last_name }}</div>
                                <div class="applicant-email">{{ $interview->applicant->email }}</div>
                            </div>
                            <span class="priority-badge priority-{{ $interview->priority_level }}">
                                {{ ucfirst($interview->priority_level) }} Priority
                            </span>
                        </div>
                        
                        @if($interview->claimedBy)
                            <div class="claimed-by">
                                Claimed by: {{ $interview->claimedBy->full_name }} 
                                ({{ $interview->claimedBy->role == 'department-head' ? 'Department Head' : 'Instructor' }})
                                - {{ $interview->time_since_claimed }}
                            </div>
                        @endif
                        
                        <div class="interview-meta">
                            <div class="meta-item">
                                <span class="meta-icon">📊</span>
                                <span>Exam Score: {{ $interview->applicant->exam_percentage ?? 'N/A' }}%</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon">⏰</span>
                                <span>Claimed: {{ $interview->time_since_claimed }}</span>
                            </div>
                        </div>

                        @if($interview->dh_override)
                            <div class="claimed-by">
                                <strong>DH Override:</strong> Specifically assigned to {{ $interview->interviewer->full_name }}
                            </div>
                        @endif

                        <div class="interview-actions">
                            @if($interview->claimed_by == auth()->id())
                                <a href="{{ route('admin.interviews.conduct', $interview->interview_id) }}" 
                                   class="btn btn-success btn-sm">
                                    Conduct Interview
                                </a>
                            @endif
                            
                            <button onclick="dhReleaseInterview({{ $interview->interview_id }})" 
                                    class="btn btn-warning btn-sm">
                                Release to Pool
                            </button>
                            
                            @if(!$interview->dh_override && $interview->claimed_by != auth()->id())
                                <button onclick="showReassignModal({{ $interview->interview_id }})" 
                                        class="btn btn-primary btn-sm">
                                    Reassign
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">🎯</div>
                        <h3>No Claimed Interviews</h3>
                        <p>No interviews are currently claimed by instructors.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Assign to Instructor Modal -->
<div id="assignModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Assign Interview to Instructor</h3>
        </div>
        <div class="modal-body">
            <form id="assignForm">
                <input type="hidden" id="assign-interview-id">
                <div class="form-group">
                    <label class="form-label">Select Instructor</label>
                    <select id="assign-instructor-id" class="form-select" required>
                        <option value="">Choose an instructor...</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->user_id }}">{{ $instructor->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Assignment Notes (Optional)</label>
                    <textarea id="assign-notes" class="form-input" rows="3" 
                              placeholder="Any specific instructions or notes for this assignment..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-actions">
            <button onclick="closeAssignModal()" class="btn btn-outline">Cancel</button>
            <button onclick="submitAssignment()" class="btn btn-primary">Assign Interview</button>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div id="bulkAssignModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Bulk Assignment</h3>
        </div>
        <div class="modal-body">
            <form id="bulkAssignForm">
                <div class="form-group">
                    <label class="form-label">Assignment Type</label>
                    <select id="bulk-assignment-type" class="form-select" onchange="toggleInstructorSelect()" required>
                        <option value="">Choose assignment type...</option>
                        <option value="specific_instructor">Assign to Specific Instructor</option>
                        <option value="department_head">Claim All for Department Head</option>
                        <option value="release_to_pool">Release All to Pool</option>
                    </select>
                </div>
                <div class="form-group" id="instructor-select-group" style="display: none;">
                    <label class="form-label">Select Instructor</label>
                    <select id="bulk-instructor-id" class="form-select">
                        <option value="">Choose an instructor...</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->user_id }}">{{ $instructor->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea id="bulk-notes" class="form-input" rows="3" 
                              placeholder="Bulk assignment notes..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-actions">
            <button onclick="closeBulkAssignModal()" class="btn btn-outline">Cancel</button>
            <button onclick="submitBulkAssignment()" class="btn btn-primary">Process Assignments</button>
        </div>
    </div>
</div>

<!-- Priority Change Modal -->
<div id="priorityModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Change Interview Priority</h3>
        </div>
        <div class="modal-body">
            <form id="priorityForm">
                <input type="hidden" id="priority-interview-id">
                <div class="form-group">
                    <label class="form-label">Priority Level</label>
                    <select id="priority-level" class="form-select" required>
                        <option value="high">High Priority</option>
                        <option value="medium">Medium Priority</option>
                        <option value="low">Low Priority</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-actions">
            <button onclick="closePriorityModal()" class="btn btn-outline">Cancel</button>
            <button onclick="submitPriorityChange()" class="btn btn-primary">Update Priority</button>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div id="notification-container"></div>
@endsection

@push('scripts')
<script>
    let selectedInterviews = new Set();

    // Auto-refresh every 30 seconds
    setInterval(refreshPoolData, 30000);

    // Department Head claim interview
    function dhClaimInterview(interviewId) {
        fetch(`/admin/interviews/pool/${interviewId}/dh-claim`, {
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
                showNotification(data.message, 'success');
                refreshPoolData();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to claim interview. Please try again.', 'error');
        });
    }

    // Department Head release interview
    function dhReleaseInterview(interviewId) {
        if (!confirm('Are you sure you want to release this interview back to the pool?')) {
            return;
        }

        fetch(`/admin/interviews/pool/${interviewId}/dh-release`, {
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
                showNotification(data.message, 'success');
                refreshPoolData();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to release interview. Please try again.', 'error');
        });
    }

    // Show assign modal
    function showAssignModal(interviewId) {
        document.getElementById('assign-interview-id').value = interviewId;
        document.getElementById('assignModal').style.display = 'flex';
    }

    function closeAssignModal() {
        document.getElementById('assignModal').style.display = 'none';
        document.getElementById('assignForm').reset();
    }

    // Submit assignment
    function submitAssignment() {
        const formData = {
            interview_id: document.getElementById('assign-interview-id').value,
            instructor_id: document.getElementById('assign-instructor-id').value,
            notes: document.getElementById('assign-notes').value
        };

        if (!formData.instructor_id) {
            showNotification('Please select an instructor', 'error');
            return;
        }

        fetch(`/admin/interviews/pool/assign-to-instructor`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closeAssignModal();
                refreshPoolData();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to assign interview. Please try again.', 'error');
        });
    }

    // Show priority modal
    function showPriorityModal(interviewId, currentPriority) {
        document.getElementById('priority-interview-id').value = interviewId;
        document.getElementById('priority-level').value = currentPriority;
        document.getElementById('priorityModal').style.display = 'flex';
    }

    function closePriorityModal() {
        document.getElementById('priorityModal').style.display = 'none';
    }

    // Submit priority change
    function submitPriorityChange() {
        const formData = {
            interview_id: document.getElementById('priority-interview-id').value,
            priority: document.getElementById('priority-level').value
        };

        fetch(`/admin/interviews/pool/set-priority`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closePriorityModal();
                refreshPoolData();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to update priority. Please try again.', 'error');
        });
    }

    // Bulk actions
    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.interview-checkbox:checked');
        selectedInterviews.clear();
        
        checkboxes.forEach(cb => {
            selectedInterviews.add(cb.value);
            cb.closest('.interview-card').classList.add('selected');
        });
        
        document.querySelectorAll('.interview-checkbox:not(:checked)').forEach(cb => {
            cb.closest('.interview-card').classList.remove('selected');
        });

        document.getElementById('selected-count').textContent = selectedInterviews.size;
        document.getElementById('bulk-assign-btn').disabled = selectedInterviews.size === 0;
    }

    function showBulkAssignModal() {
        if (selectedInterviews.size === 0) {
            showNotification('Please select interviews first', 'error');
            return;
        }
        document.getElementById('bulkAssignModal').style.display = 'flex';
    }

    function closeBulkAssignModal() {
        document.getElementById('bulkAssignModal').style.display = 'none';
        document.getElementById('bulkAssignForm').reset();
        document.getElementById('instructor-select-group').style.display = 'none';
    }

    function toggleInstructorSelect() {
        const assignmentType = document.getElementById('bulk-assignment-type').value;
        const instructorGroup = document.getElementById('instructor-select-group');
        
        if (assignmentType === 'specific_instructor') {
            instructorGroup.style.display = 'block';
        } else {
            instructorGroup.style.display = 'none';
        }
    }

    function submitBulkAssignment() {
        const formData = {
            interview_ids: Array.from(selectedInterviews),
            assignment_type: document.getElementById('bulk-assignment-type').value,
            instructor_id: document.getElementById('bulk-instructor-id').value,
            notes: document.getElementById('bulk-notes').value
        };

        if (!formData.assignment_type) {
            showNotification('Please select assignment type', 'error');
            return;
        }

        if (formData.assignment_type === 'specific_instructor' && !formData.instructor_id) {
            showNotification('Please select an instructor', 'error');
            return;
        }

        fetch(`/admin/interviews/pool/bulk-assign`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closeBulkAssignModal();
                selectedInterviews.clear();
                refreshPoolData();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to process bulk assignment. Please try again.', 'error');
        });
    }

    // Utility functions
    function viewApplicant(applicantId) {
        window.open(`/admin/applicants/${applicantId}`, '_blank');
    }

    function refreshPoolData() {
        const urlParams = new URLSearchParams(window.location.search);
        const filters = {
            priority: urlParams.get('priority') || '',
            search: urlParams.get('search') || '',
            status: urlParams.get('status') || ''
        };

        fetch(`/admin/interviews/pool/data?${new URLSearchParams(filters)}`)
            .then(response => response.json())
            .then(data => {
                // Update UI with fresh data
                updatePoolUI(data);
            })
            .catch(error => console.error('Error refreshing data:', error));
    }

    function updatePoolUI(data) {
        // Update statistics
        Object.keys(data.pool_stats).forEach(key => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                element.textContent = data.pool_stats[key];
            }
        });

        // Update counts
        document.getElementById('available-count').textContent = data.available_interviews.length;
        document.getElementById('claimed-count').textContent = data.claimed_interviews.length;
        
        console.log('Pool data refreshed');
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.getElementById('notification-container').appendChild(notification);
        
        setTimeout(() => notification.classList.add('show'), 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Department Head Pool Overview initialized');
    });
</script>
@endpush
