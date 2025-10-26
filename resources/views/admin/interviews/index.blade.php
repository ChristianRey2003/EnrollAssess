@extends('layouts.admin')

@section('title', 'Interview Management')

@php
    $pageTitle = 'Interview Management';
    $pageSubtitle = 'Schedule and manage applicant interviews';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/interviews.css') }}" rel="stylesheet">
@endpush

@section('content')
    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Interviews</div>
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
            <div class="stat-value">{{ $stats['pending_assignment'] }}</div>
            <div class="stat-label">Pending Assignment</div>
        </div>
    </section>

    <!-- Main Content Card -->
    <div class="content-card">
        <div class="content-header">
            <h2 class="section-title">Interview Schedule</h2>
            <div class="section-actions">
                <a href="{{ route('admin.interviews.analytics') }}" class="btn-outline">
                    Analytics
                </a>
                <button onclick="showExportModal()" class="btn-outline">
                    Export
                </button>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="search-controls" style="padding: 24px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB;">
            <form method="GET" action="{{ route('admin.interviews.index') }}" class="search-form">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Search applicants or interviewers..." 
                               value="{{ request('search') }}" class="search-input">
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                    
                    <div class="filter-group">
                        <select name="status" class="filter-select">
                            <option value="">All Status</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        
                        <select name="interviewer_id" class="filter-select">
                            <option value="">All Interviewers</option>
                            @foreach($instructors as $instructor)
                                <option value="{{ $instructor->user_id }}" 
                                        {{ request('interviewer_id') == $instructor->user_id ? 'selected' : '' }}>
                                    {{ $instructor->full_name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <button type="submit" class="btn-outline">Apply Filters</button>
                        @if(request()->hasAny(['search', 'status', 'interviewer_id']))
                            <a href="{{ route('admin.interviews.index') }}" class="btn-clear">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Interviews Table -->
        <div class="interviews-table">
            @if($interviews->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">NO.</th>
                            <th>APPLICANT</th>
                            <th>INTERVIEWER</th>
                            <th>SCHEDULE DATE</th>
                            <th>STATUS</th>
                            <th>INTERVIEW SCORE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($interviews as $index => $interview)
                            <tr>
                                <td class="text-center">
                                    {{ ($interviews->currentPage() - 1) * $interviews->perPage() + $index + 1 }}
                                </td>
                                <td style="position: relative;">
                                    <div class="applicant-info">
                                        @if($interview->applicant)
                                            <div class="applicant-name">{{ $interview->applicant->full_name }}</div>
                                            <div class="applicant-email">{{ $interview->applicant->email_address ?? $interview->applicant->email ?? 'N/A' }}</div>
                                        @else
                                            <div class="applicant-name text-muted">Unknown Applicant</div>
                                            <div class="applicant-email text-muted">N/A</div>
                                        @endif
                                    </div>

                                    <!-- Floating Actions -->
                                    <div class="floating-actions">
                                        @if(in_array($interview->status, ['scheduled', 'available', 'claimed']) && $interview->status !== 'completed')
                                            @if($interview->claimed_by === auth()->id())
                                                <a href="{{ route('admin.interviews.conduct', $interview->interview_id) }}" 
                                                   class="action-btn action-btn-conduct">
                                                    Continue
                                                </a>
                                            @elseif(!$interview->claimed_by || $interview->isClaimedTooLong(1))
                                                <a href="{{ route('admin.interviews.conduct', $interview->interview_id) }}" 
                                                   class="action-btn action-btn-conduct">
                                                    I'll Conduct This
                                                </a>
                                            @else
                                                <span class="action-btn" style="cursor: not-allowed; opacity: 0.6;">
                                                    Claimed
                                                </span>
                                            @endif
                                        @endif
                                        
                                        <button onclick="editInterview({{ $interview->interview_id }})" 
                                                class="action-btn action-btn-edit">
                                            Edit
                                        </button>
                                        
                                        @if($interview->status === 'scheduled')
                                            <button onclick="cancelInterview({{ $interview->interview_id }})" 
                                                    class="action-btn action-btn-delete">
                                                Cancel
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('admin.interviews.show', $interview->interview_id) }}" 
                                           class="action-btn action-btn-view">
                                            View Details
                                        </a>
                                    </div>
                                </td>
                                <td>
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
                                <td>
                                    <div class="schedule-info">
                                        <div class="schedule-date">{{ $interview->schedule_date ? $interview->schedule_date->format('M d, Y') : 'Not set' }}</div>
                                        <div class="schedule-time">{{ $interview->schedule_date ? $interview->schedule_date->format('g:i A') : '' }}</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge status-{{ $interview->status }}">
                                        {{ ucfirst($interview->status) }}
                                    </span>
                                </td>
                                <td>
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
                <div class="empty-state">
                    <h3>No Interviews Found</h3>
                    <p>No interviews match your current search criteria.</p>
                    <a href="{{ route('admin.applicants.assign') }}" class="btn-primary">
                        Assign Applicants to Instructors
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('modals')
    <!-- Export Modal -->
    <div id="exportModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Export Interviews</h3>
                <button onclick="closeExportModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="form-group">
                        <label class="form-label">Status Filter</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="scheduled">Scheduled Only</option>
                            <option value="completed">Completed Only</option>
                            <option value="cancelled">Cancelled Only</option>
                        </select>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="closeExportModal()" class="btn-secondary">Cancel</button>
                <button onclick="confirmExport()" class="btn-primary">Export CSV</button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
<script>
    function showExportModal() {
        document.getElementById('exportModal').style.display = 'flex';
    }

    function closeExportModal() {
        document.getElementById('exportModal').style.display = 'none';
    }

    function confirmExport() {
        const form = document.getElementById('exportForm');
        const formData = new FormData(form);
        
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        
        const url = '/admin/interviews/export?' + params.toString();
        window.open(url, '_blank');
        closeExportModal();
    }

    function editInterview(interviewId) {
        // Implementation for editing interview
        alert('Edit interview functionality - to be implemented with inline editing');
    }

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

    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeExportModal();
        }
    });
</script>
@endpush
