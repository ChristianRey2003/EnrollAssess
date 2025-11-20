@extends('layouts.admin')

@section('title', 'Interview Management')

@php
    $pageTitle = 'Interview Management';
    $pageSubtitle = 'Schedule and manage applicant interviews';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/interviews.css') }}" rel="stylesheet">
    <style>
        /* Override main-content padding for this page */
        .main-content {
            padding: 20px !important;
        }

        /* Center stats section at top */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            max-width: 100%;
        }

        .stats-section .stat-card {
            min-width: 200px;
        }

        /* Pagination spacing */
        .pagination-wrapper {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .pagination-wrapper .relative.z-0.inline-flex {
            margin-left: 20px;
        }

        /* Floating Actions - matching applicants page style */
        .floating-actions {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: none;
            flex-direction: row;
            white-space: nowrap;
            align-items: center;
        }
        
        .floating-actions .action-btn {
            padding: 6px 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.2s;
            text-decoration: none;
            color: #374151;
            white-space: nowrap;
        }
        
        .floating-actions .action-btn:hover {
            background: #f3f4f6;
        }
        
        .floating-actions .action-btn-conduct {
            background: #800020;
            color: white;
        }
        
        .floating-actions .action-btn-conduct:hover {
            background: #5C0016;
            color: white;
        }
        
        .floating-actions .action-btn-edit {
            color: #2563eb;
        }
        
        .floating-actions .action-btn-edit:hover {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .floating-actions .action-btn-view {
            color: #6b7280;
        }
        
        .floating-actions .action-btn-view:hover {
            background: #f3f4f6;
            color: #374151;
        }
        
        .floating-actions .action-btn-delete {
            color: #dc2626;
        }
        
        .floating-actions .action-btn-delete:hover {
            background: #fee2e2;
            color: #991b1b;
        }
        
        tr:hover {
            background-color: rgba(255, 215, 0, 0.1) !important;
        }
        
        .table tbody tr {
            position: relative;
        }
        
        .table tbody td {
            overflow: visible !important;
            position: relative;
        }
        
        /* Ensure the last column (score column) allows overflow */
        .table tbody td:last-child {
            overflow: visible !important;
            position: relative !important;
        }
        
        .table-responsive {
            overflow-x: auto;
            overflow-y: visible !important;
            position: relative;
        }
        
        .table {
            overflow: visible !important;
            position: relative;
        }
        
        .table tbody {
            overflow: visible !important;
        }
        
        /* Ensure floating actions are not clipped */
        .table tbody tr td:last-child {
            overflow: visible !important;
        }
    </style>
@endpush

@section('content')
    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $stats['scheduled'] }}</div>
            <div class="stat-label">Scheduled</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $stats['completed'] }}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $stats['cancelled'] }}</div>
            <div class="stat-label">Cancelled</div>
        </div>
    </section>

    <!-- Main Content Card -->
    <div class="content-card">

        <!-- Search and Filter Bar -->
        <div class="search-controls" style="padding: 20px 24px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB;">
            <form method="GET" action="{{ route('admin.interviews.index') }}" id="interviewSearchForm" class="search-form">
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <div style="position: relative; width: 220px;">
                        <input type="text" 
                               id="searchInput" 
                               name="search" 
                               placeholder="Search..." 
                               value="{{ request('search') }}" 
                               class="form-control form-control-sm" 
                               style="width: 100%; height: 26px; padding: 4px 32px 4px 8px;">
                        <svg style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; pointer-events: none; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    
                    <select name="status" class="form-select form-select-sm" style="width: 180px; min-width: 180px; height: 40px; padding: 4px 28px 4px 8px;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Interviews Table -->
        <div class="table-responsive">
            @if($interviews->count() > 0)
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px; font-size: 0.85rem; font-weight: bold;" class="text-center">No.</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-left">Applicant</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-left">Interviewer</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-center">Schedule date</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-center">Status</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-center">Interview score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($interviews as $index => $interview)
                            <tr onmouseover="showActions({{ $interview->interview_id }})" 
                                onmouseout="hideActions({{ $interview->interview_id }})">
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    {{ ($interviews->currentPage() - 1) * $interviews->perPage() + $index + 1 }}
                                </td>
                                <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                    <div class="applicant-info">
                                        @if($interview->applicant)
                                            <div class="applicant-name">{{ $interview->applicant->full_name }}</div>
                                            <div class="applicant-email">{{ $interview->applicant->email_address ?? $interview->applicant->email ?? 'N/A' }}</div>
                                        @else
                                            <div class="applicant-name text-muted">Unknown Applicant</div>
                                            <div class="applicant-email text-muted">N/A</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                    <div class="interviewer-info">
                                        @if($interview->interviewer)
                                            <div class="interviewer-name">{{ $interview->interviewer->full_name }}</div>
                                            <div class="interviewer-role">{{ ucfirst($interview->interviewer->role) }}</div>
                                        @else
                                            <div class="interviewer-name text-muted">Not Assigned</div>
                                            <div class="interviewer-role text-muted">Available in Pool</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    <div class="schedule-info">
                                        <div class="schedule-date">{{ $interview->schedule_date ? $interview->schedule_date->format('M d, Y') : 'Not set' }}</div>
                                        <div class="schedule-time">{{ $interview->schedule_date ? $interview->schedule_date->format('g:i A') : '' }}</div>
                                    </div>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($interview->status) }}
                                    </span>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal; position: relative; overflow: visible;">
                                    <div class="score-display">
                                        @if($interview->status === 'completed' && $interview->overall_score !== null)
                                            @php
                                                $score = $interview->overall_score;
                                                $scoreClass = 'score-poor';
                                                if ($score >= 75) {
                                                    $scoreClass = 'score-excellent';
                                                } elseif ($score >= 50) {
                                                    $scoreClass = 'score-good';
                                                } elseif ($score >= 25) {
                                                    $scoreClass = 'score-fair';
                                                }
                                            @endphp
                                            <span class="score-badge {{ $scoreClass }}">
                                                {{ number_format($score, 0) }}/100
                                            </span>
                                        @else
                                            <span class="score-pending">Pending</span>
                                        @endif
                                    </div>

                                    <!-- Floating Actions -->
                                    <div id="actions-{{ $interview->interview_id }}" class="floating-actions" style="display: none;">
                                        @if(in_array($interview->status, ['scheduled', 'available', 'claimed']) && $interview->status !== 'completed')
                                            @php
                                                $hasCompletedExam = $interview->applicant && $interview->applicant->hasCompletedExam();
                                            @endphp
                                            @if($interview->claimed_by === auth()->id())
                                                @if($hasCompletedExam)
                                                    <a href="{{ route('admin.interviews.conduct', $interview->interview_id) }}" 
                                                       class="action-btn action-btn-conduct">
                                                        Continue
                                                    </a>
                                                @else
                                                    <span class="action-btn action-btn-conduct" 
                                                          style="cursor: not-allowed; opacity: 0.5; position: relative;"
                                                          title="Cannot conduct interview: Applicant has not completed the exam yet">
                                                        Continue
                                                    </span>
                                                @endif
                                            @elseif(!$interview->claimed_by || $interview->isClaimedTooLong(1))
                                                @if($hasCompletedExam)
                                                    <a href="{{ route('admin.interviews.conduct', $interview->interview_id) }}" 
                                                       class="action-btn action-btn-conduct">
                                                        Conduct
                                                    </a>
                                                @else
                                                    <span class="action-btn action-btn-conduct" 
                                                          style="cursor: not-allowed; opacity: 0.5; position: relative;"
                                                          title="Cannot conduct interview: Applicant has not completed the exam yet">
                                                        Conduct
                                                    </span>
                                                @endif
                                            @else
                                                <span class="action-btn" style="cursor: not-allowed; opacity: 0.6;">
                                                    Claimed
                                                </span>
                                            @endif
                                        @endif
                                        
                                        @if($interview->status !== 'completed')
                                            <button onclick="editInterview({{ $interview->interview_id }})" 
                                                    class="action-btn action-btn-edit">
                                                Edit
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('admin.interviews.show', $interview->interview_id) }}" 
                                           class="action-btn action-btn-view">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination-wrapper" style="padding: 20px;">
                    {{ $interviews->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <h5>No Interviews Found</h5>
                        <p>No interviews match your current search criteria.</p>
                        <a href="{{ route('admin.applicants.assign') }}" class="btn btn-sm" style="background: #800020; color: white; border: none;">
                            Assign Applicants to Instructors
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Interview Modal -->
    <div id="editInterviewModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #e5e7eb;">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Edit Interview</h3>
                <button onclick="closeEditModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
            </div>
            <form id="editInterviewForm" style="padding: 20px;">
                <input type="hidden" id="edit_interview_id" name="interview_id">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Schedule Date & Time</label>
                    <input type="datetime-local" 
                           id="edit_schedule_date" 
                           name="schedule_date" 
                           required
                           class="form-control"
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Interviewer</label>
                    <select id="edit_interviewer_id" 
                            name="interviewer_id" 
                            class="form-select"
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                        <option value="">Select Interviewer</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->user_id }}">{{ $instructor->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Status</label>
                    <select id="edit_status" 
                            name="status" 
                            required
                            class="form-select"
                            style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                        <option value="scheduled">Scheduled</option>
                        <option value="available">Available</option>
                        <option value="claimed">Claimed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Assignment Notes</label>
                    <textarea id="edit_assignment_notes" 
                              name="assignment_notes" 
                              rows="4"
                              class="form-control"
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                    <button type="button" 
                            onclick="closeEditModal()" 
                            style="padding: 8px 16px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer; color: #374151;">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="padding: 8px 16px; background: #2563eb; border: none; border-radius: 6px; cursor: pointer; color: white; font-weight: 500;">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.2s;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .floating-actions .action-btn[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            margin-bottom: 5px;
            pointer-events: none;
        }
        .floating-actions .action-btn[title]:hover::before {
            content: '';
            position: absolute;
            bottom: calc(100% - 5px);
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #1f2937;
            z-index: 1000;
            pointer-events: none;
        }
    </style>
@endsection


@push('scripts')
<script>
    // Define showActions and hideActions functions FIRST, before any other code
    // This ensures they're available when inline event handlers execute
    function showActions(interviewId) {
        const actionsElement = document.getElementById('actions-' + interviewId);
        if (actionsElement) {
            // Check if element has any children (should always have at least View button)
            if (actionsElement.children.length > 0) {
                actionsElement.style.setProperty('display', 'flex', 'important');
            } else {
                console.warn('Floating actions div is empty for interview:', interviewId);
            }
        } else {
            console.error('Actions element not found for interview:', interviewId);
        }
    }
    
    function hideActions(interviewId) {
        const actionsElement = document.getElementById('actions-' + interviewId);
        if (actionsElement) {
            actionsElement.style.display = 'none';
        }
    }
    
    // Make functions globally available immediately
    window.showActions = showActions;
    window.hideActions = hideActions;
    
    // Store current user ID for AJAX pagination
    const currentUserId = {{ auth()->id() }};
    
    // Auto-search functionality
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const searchValue = e.target.value.trim();
            
            searchTimeout = setTimeout(function() {
                const url = new URL(window.location);
                if (searchValue) {
                    url.searchParams.set('search', searchValue);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('page'); // Reset to first page
                window.location.href = url.toString();
            }, 500); // 500ms debounce
        });
    }


    function editInterview(interviewId) {
        // Fetch interview data via JSON endpoint
        fetch(`/admin/interviews/${interviewId}?json=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch interview data');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.interview) {
                const interview = data.interview;
                
                // Check if interview is completed (should not be editable)
                if (interview.status === 'completed') {
                    alert('Cannot edit completed interviews. Please use the Conduct form to make changes.');
                    return;
                }
                
                // Populate form
                document.getElementById('edit_interview_id').value = interview.interview_id;
                
                // Format date for datetime-local input (YYYY-MM-DDTHH:mm)
                if (interview.schedule_date) {
                    const scheduleDate = new Date(interview.schedule_date);
                    const year = scheduleDate.getFullYear();
                    const month = String(scheduleDate.getMonth() + 1).padStart(2, '0');
                    const day = String(scheduleDate.getDate()).padStart(2, '0');
                    const hours = String(scheduleDate.getHours()).padStart(2, '0');
                    const minutes = String(scheduleDate.getMinutes()).padStart(2, '0');
                    document.getElementById('edit_schedule_date').value = `${year}-${month}-${day}T${hours}:${minutes}`;
                }
                
                document.getElementById('edit_interviewer_id').value = interview.interviewer_id || '';
                document.getElementById('edit_status').value = interview.status || 'scheduled';
                document.getElementById('edit_assignment_notes').value = interview.assignment_notes || '';
                
                // Show modal
                document.getElementById('editInterviewModal').style.display = 'flex';
            } else {
                alert('Could not load interview data. Please refresh the page.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load interview data. Please try again.');
        });
    }

    function closeEditModal() {
        document.getElementById('editInterviewModal').style.display = 'none';
        document.getElementById('editInterviewForm').reset();
    }

    // Handle edit form submission
    document.getElementById('editInterviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const interviewId = document.getElementById('edit_interview_id').value;
        const formData = {
            schedule_date: document.getElementById('edit_schedule_date').value,
            interviewer_id: document.getElementById('edit_interviewer_id').value || null,
            status: document.getElementById('edit_status').value,
            assignment_notes: document.getElementById('edit_assignment_notes').value,
        };

        fetch(`/admin/interviews/${interviewId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeEditModal();
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to update interview'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the interview. Please try again.');
        });
    });

    // Close modal when clicking outside
    document.getElementById('editInterviewModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

    function cancelInterview(interviewId) {
        if (confirm('Are you sure you want to cancel this interview?')) {
            fetch(`/admin/interviews/${interviewId}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }

    // AJAX Pagination
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('.pagination a, .pagination-wrapper a');
        
        if (paginationLink && paginationLink.href) {
            e.preventDefault();
            e.stopPropagation();
            const url = paginationLink.href;
            
            if (!url || url === '#' || url === 'javascript:void(0)') return;
            
            const tableBody = document.querySelector('.table-responsive .table tbody');
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
                if (data.interviews && tableBody) {
                    let html = '';
                    const from = data.pagination.from || 0;
                    
                    if (data.interviews.length === 0) {
                        html = '<tr><td colspan="6" class="text-center py-8"><div class="empty-state"><h3>No Interviews Found</h3><p>No interviews match your current search criteria.</p></div></td></tr>';
                    } else {
                        data.interviews.forEach((interview, index) => {
                            const rowNum = (from - 1) + index + 1;
                            const applicant = interview.applicant || {};
                            const interviewer = interview.interviewer || {};
                            
                            // Handle date formatting - schedule_date might be a string or null
                            let dateStr = 'Not set';
                            let timeStr = '';
                            if (interview.schedule_date) {
                                try {
                                    const scheduleDate = new Date(interview.schedule_date);
                                    if (!isNaN(scheduleDate.getTime())) {
                                        dateStr = scheduleDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                                        timeStr = scheduleDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                                    }
                                } catch(e) {
                                    // If date parsing fails, use the raw value or default
                                    dateStr = interview.schedule_date || 'Not set';
                                }
                            }
                            
                            let scoreHtml = '<span class="score-pending">Pending</span>';
                            if (interview.status === 'completed' && interview.overall_score !== null) {
                                const score = Number(interview.overall_score);
                                let scoreClass = 'score-poor';
                                if (score >= 75) scoreClass = 'score-excellent';
                                else if (score >= 50) scoreClass = 'score-good';
                                else if (score >= 25) scoreClass = 'score-fair';
                                scoreHtml = `<span class="score-badge ${scoreClass}">${Math.round(score)}/100</span>`;
                            }
                            
                            const statusClass = interview.status || 'scheduled';
                            const statusText = (interview.status || 'scheduled').charAt(0).toUpperCase() + (interview.status || 'scheduled').slice(1);
                            
                            // Check if applicant has completed exam (applicant already declared above)
                            const hasCompletedExam = applicant.status && ['exam-completed', 'interview-scheduled', 'interview-completed', 'admitted', 'rejected'].includes(applicant.status);
                            
                            // Build action buttons HTML
                            let actionsHtml = '';
                            if (['scheduled', 'available', 'claimed'].includes(interview.status) && interview.status !== 'completed') {
                                const claimedBy = interview.claimed_by;
                                if (claimedBy === currentUserId) {
                                    if (hasCompletedExam) {
                                        actionsHtml += `<a href="/admin/interviews/${interview.interview_id}/conduct" class="action-btn action-btn-conduct">Continue</a>`;
                                    } else {
                                        actionsHtml += `<span class="action-btn action-btn-conduct" style="cursor: not-allowed; opacity: 0.5; position: relative;" title="Cannot conduct interview: Applicant has not completed the exam yet">Continue</span>`;
                                    }
                                } else if (!claimedBy || !interview.claimed_at || (new Date(interview.claimed_at) < new Date(Date.now() - 3600000))) {
                                    if (hasCompletedExam) {
                                        actionsHtml += `<a href="/admin/interviews/${interview.interview_id}/conduct" class="action-btn action-btn-conduct">Conduct</a>`;
                                    } else {
                                        actionsHtml += `<span class="action-btn action-btn-conduct" style="cursor: not-allowed; opacity: 0.5; position: relative;" title="Cannot conduct interview: Applicant has not completed the exam yet">Conduct</span>`;
                                    }
                                } else {
                                    actionsHtml += `<span class="action-btn" style="cursor: not-allowed; opacity: 0.6;">Claimed</span>`;
                                }
                            }
                            if (interview.status !== 'completed') {
                                actionsHtml += `<button onclick="editInterview(${interview.interview_id})" class="action-btn action-btn-edit">Edit</button>`;
                            }
                            actionsHtml += `<a href="/admin/interviews/${interview.interview_id}" class="action-btn action-btn-view">View</a>`;
                            
                            html += `<tr onmouseover="showActions(${interview.interview_id})" onmouseout="hideActions(${interview.interview_id})">
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">${rowNum}</td>
                                <td class="text-left" style="font-size: 13px; font-weight: normal; position: relative;">
                                    <div class="applicant-info">
                                        <div class="applicant-name">${(applicant.full_name || applicant.first_name + ' ' + applicant.last_name || 'Unknown Applicant').trim()}</div>
                                        <div class="applicant-email">${applicant.email_address || applicant.email || 'N/A'}</div>
                                    </div>
                                </td>
                                <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                    <div class="interviewer-info">
                                        <div class="interviewer-name">${interviewer.full_name || interviewer.first_name + ' ' + interviewer.last_name || 'Not Assigned'}</div>
                                        <div class="interviewer-role">${interviewer.role ? (interviewer.role.charAt(0).toUpperCase() + interviewer.role.slice(1)) : 'Available in Pool'}</div>
                                    </div>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    <div class="schedule-info">
                                        <div class="schedule-date">${dateStr}</div>
                                        ${timeStr ? `<div class="schedule-time">${timeStr}</div>` : ''}
                                    </div>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    <span class="badge bg-secondary">${statusText}</span>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal; position: relative;">
                                    <div class="score-display">${scoreHtml}</div>
                                    <div id="actions-${interview.interview_id}" class="floating-actions" style="display: none;">${actionsHtml}</div>
                                </td>
                            </tr>`;
                        });
                    }
                    
                    tableBody.innerHTML = html;
                    tableBody.style.opacity = '1';
                    tableBody.style.pointerEvents = '';
                    
                    if (data.pagination_html && paginationWrapper) {
                        paginationWrapper.innerHTML = data.pagination_html;
                    }
                    
                    if (paginationWrapper) {
                        paginationWrapper.style.opacity = '1';
                        paginationWrapper.style.pointerEvents = '';
                    }
                    
                    window.history.pushState({}, '', url);
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
