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
            <div class="stat-value">{{ $stats['scheduled'] }}</div>
            <div class="stat-label">Scheduled</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $stats['completed'] }}</div>
            <div class="stat-label">Completed</div>
        </div>
    </section>

    <!-- Main Content Card -->
    <div class="content-card">
        <div class="content-header">
            <h2 class="section-title">Interview Schedule</h2>
        </div>

        <!-- Search and Filter Bar -->
        <div class="search-controls" style="padding: 20px 24px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB;">
            <form method="GET" action="{{ route('admin.interviews.index') }}" id="interviewSearchForm" class="search-form">
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <input type="text" name="search" placeholder="Search..." 
                           value="{{ request('search') }}" class="search-input" style="width: 180px; height: 30px; padding: 4px 40px 4px 8px; font-size: 12px; border: 1px solid #d1d5db; border-radius: 4px; flex: none; background-position: right 12px center;">
                    
                    <select name="status" class="filter-select" style="width: 140px; height: 30px; padding: 2px 6px; font-size: 12px; border: 1px solid #d1d5db; border-radius: 4px;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.interviews.index') }}" class="btn-clear" style="height: 30px; padding: 4px 12px; font-size: 12px; border-radius: 4px; background: #800020; border: none; color: white; text-decoration: none; display: inline-flex; align-items: center;">Clear</a>
                    @endif
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


@push('scripts')
<script>
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

    // AJAX Pagination
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('.pagination a, .pagination-wrapper a');
        
        if (paginationLink && paginationLink.href) {
            e.preventDefault();
            e.stopPropagation();
            const url = paginationLink.href;
            
            if (!url || url === '#' || url === 'javascript:void(0)') return;
            
            const tableBody = document.querySelector('.data-table tbody');
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
                            
                            // Build action buttons HTML
                            let actionsHtml = '';
                            if (['scheduled', 'available', 'claimed'].includes(interview.status) && interview.status !== 'completed') {
                                actionsHtml += `<a href="/admin/interviews/${interview.interview_id}/conduct" class="action-btn action-btn-conduct">I'll Conduct This</a>`;
                            }
                            actionsHtml += `<button onclick="editInterview(${interview.interview_id})" class="action-btn action-btn-edit">Edit</button>`;
                            if (interview.status === 'scheduled') {
                                actionsHtml += `<button onclick="cancelInterview(${interview.interview_id})" class="action-btn action-btn-delete">Cancel</button>`;
                            }
                            actionsHtml += `<a href="/admin/interviews/${interview.interview_id}" class="action-btn action-btn-view">View Details</a>`;
                            
                            html += `<tr>
                                <td class="text-center">${rowNum}</td>
                                <td style="position: relative;">
                                    <div class="applicant-info">
                                        <div class="applicant-name">${(applicant.full_name || applicant.first_name + ' ' + applicant.last_name || 'Unknown Applicant').trim()}</div>
                                        <div class="applicant-email">${applicant.email_address || applicant.email || 'N/A'}</div>
                                    </div>
                                    <div class="floating-actions">${actionsHtml}</div>
                                </td>
                                <td>
                                    <div class="interviewer-info">
                                        <div class="interviewer-name">${interviewer.full_name || interviewer.first_name + ' ' + interviewer.last_name || 'Not Assigned'}</div>
                                        <div class="interviewer-role">${interviewer.role ? (interviewer.role.charAt(0).toUpperCase() + interviewer.role.slice(1)) : 'Available in Pool'}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="schedule-info">
                                        <div class="schedule-date">${dateStr}</div>
                                        ${timeStr ? `<div class="schedule-time">${timeStr}</div>` : ''}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge status-${statusClass}">${statusText}</span>
                                </td>
                                <td>
                                    <div class="score-display">${scoreHtml}</div>
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
