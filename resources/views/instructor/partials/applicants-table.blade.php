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
        <form class="table-header-controls" id="applicantFiltersForm" method="GET" action="{{ route('instructor.applicants') }}">
            <input
                type="text"
                name="search"
                id="searchApplicantsInput"
                class="form-input"
                placeholder="Search applicants..."
                value="{{ request('search') }}"
                autocomplete="off"
            >
            <select
                name="status"
                class="form-select"
                aria-label="Filter by status"
            >
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="exam-completed" {{ request('status') == 'exam-completed' ? 'selected' : '' }}>Exam Completed</option>
                <option value="interview-completed" {{ request('status') == 'interview-completed' ? 'selected' : '' }}>Interview Completed</option>
                <option value="admitted" {{ request('status') == 'admitted' ? 'selected' : '' }}>Admitted</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </form>
    </div>
    
    @if($assignedApplicants->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle instructor-data-table">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-center" style="width: 40px;">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                        </th>
                        <th scope="col" class="text-left">Applicant</th>
                        <th scope="col" class="text-center" style="min-width: 140px;">Application No.</th>
                        <th scope="col" class="text-center">Exam Score</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-center">Interview Date</th>
                        <th scope="col" class="text-left" style="min-width: 200px;">Interview Window</th>
                        <th scope="col" class="text-center" style="min-width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignedApplicants as $applicant)
                    @php
                        $interview = $applicant->latestInterview;
                        $canSchedule = $interview && (!$interview->schedule_date || $interview->status === 'assigned');
                        $hasCompletedExam = method_exists($applicant, 'hasCompletedExam') ? $applicant->hasCompletedExam() : ($applicant->status === 'exam-completed');
                    @endphp
                    <tr>
                        <td class="text-center">
                            @if($canSchedule)
                                <input type="checkbox" class="applicant-checkbox" 
                                       data-interview-id="{{ $interview->interview_id }}"
                                       data-applicant-name="{{ $applicant->first_name }} {{ $applicant->last_name }}"
                                       onchange="updateBulkActions()">
                            @endif
                        </td>
                        <td>
                            <div class="applicant-cell">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div>
                                        <div class="applicant-name">{{ $applicant->first_name }} {{ $applicant->last_name }}</div>
                                        <div class="applicant-email">{{ $applicant->email_address }}</div>
                                    </div>
                                    @if($interview && $interview->assignment_notes)
                                        <button type="button" 
                                                onclick="toggleAssignmentInfo({{ $applicant->applicant_id }})" 
                                                style="background: none; border: none; cursor: pointer; padding: 4px; font-size: 1.2rem; color: #3B82F6;"
                                                title="View assignment message">
                                            📋
                                        </button>
                                    @endif
                                </div>
                                @if($interview && $interview->assignment_notes)
                                    <div id="assignment-info-{{ $applicant->applicant_id }}" 
                                         style="display: none; margin-top: 8px; padding: 10px; background: #F3F4F6; border-radius: 6px; font-size: 0.875rem; color: #374151;">
                                        <strong style="color: #1F2937;">Assignment Message:</strong><br>
                                        <div style="margin-top: 4px;">{{ $interview->assignment_notes }}</div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">{{ $applicant->application_no }}</td>
                        <td class="text-center">
                            @php
                                $examScore = $applicant->enrollassess_score ?? null;
                            @endphp
                            @if($examScore !== null)
                                <span class="status-badge {{ $examScore >= 70 ? 'status-completed' : 'status-pending' }}">
                                    {{ number_format($examScore, 1) }}%
                                </span>
                            @else
                                <span class="status-badge status-pending">Pending</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="status-badge status-{{ str_replace([' ', '-'], ['', ''], strtolower($applicant->status)) }}">
                                {{ ucfirst(str_replace('-', ' ', $applicant->status)) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($interview && $interview->schedule_date)
                                {{ $interview->schedule_date->format('M d, Y g:i A') }}
                            @else
                                <span style="color: #6B7280;">Not scheduled</span>
                            @endif
                        </td>
                        <td>
                            @if($interview && $interview->interview_deadline_start && $interview->interview_deadline_end)
                                <div style="font-size: 0.875rem;">
                                    <div style="color: #374151; font-weight: 500;">
                                        {{ $interview->interview_deadline_start->format('M d, Y') }} - 
                                        {{ $interview->interview_deadline_end->format('M d, Y') }}
                                    </div>
                                    @php
                                        $now = now();
                                        $daysUntilEnd = $now->diffInDays($interview->interview_deadline_end, false);
                                    @endphp
                                    @if($interview->interview_deadline_end->isPast())
                                        <span style="color: #DC2626; font-size: 0.813rem; font-weight: 600;">⚠️ Deadline passed</span>
                                    @elseif($daysUntilEnd <= 3 && $daysUntilEnd >= 0)
                                        <span style="color: #F59E0B; font-size: 0.813rem; font-weight: 600;">⚠️ Due soon</span>
                                    @endif
                                </div>
                            @else
                                <span style="color: #9CA3AF; font-size: 0.875rem;">No deadline set</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($interview && $canSchedule)
                                @if($hasCompletedExam)
                                    <button type="button" class="btn btn-primary btn-small" 
                                            onclick="openScheduleModal({{ $interview->interview_id }}, '{{ $applicant->first_name }} {{ $applicant->last_name }}')">
                                        Schedule Interview
                                    </button>
                                @else
                                    <span class="tooltip" data-tip="Cannot schedule: applicant must complete the exam">
                                        <button type="button" class="btn btn-primary btn-small disabled" disabled title="Applicant must complete the exam">
                                            Schedule Interview
                                        </button>
                                    </span>
                                @endif
                            @elseif(in_array($applicant->status, ['exam-completed', 'interview-scheduled']))
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
        </div>

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

