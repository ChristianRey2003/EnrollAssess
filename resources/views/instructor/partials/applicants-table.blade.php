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
            <div style="position: relative; width: 220px;">
                <input
                    type="text"
                    name="search"
                    id="searchApplicantsInput"
                    class="form-input"
                    placeholder="Search..."
                    value="{{ request('search') }}"
                    autocomplete="off"
                    style="width: 100%; height: 40px; padding: 4px 32px 4px 8px;"
                    aria-label="Search applicants"
                >
                <svg style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; pointer-events: none; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <select
                name="status"
                class="form-select"
                aria-label="Filter by status"
                style="width: 180px; min-width: 180px; height: 40px; padding: 4px 36px 4px 8px;"
            >
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="needs-scheduling" {{ request('status') == 'needs-scheduling' ? 'selected' : '' }}>Need to be Scheduled</option>
                <option value="interview-scheduled" {{ request('status') == 'interview-scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                <option value="interview-completed" {{ request('status') == 'interview-completed' ? 'selected' : '' }}>Interview Completed</option>
            </select>
        </form>
    </div>
    
    @if($assignedApplicants->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead style="background-color: white !important; color: #1F2937 !important;">
                    <tr>
                        <th style="width: 40px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">
                            <input type="checkbox" 
                                   id="selectAll" 
                                   onchange="toggleSelectAll(this)"
                                   class="form-check-input"
                                   style="cursor: pointer;">
                        </th>
                        <th style="width: 180px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-left">Applicant</th>
                        <th style="width: 120px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Application No.</th>
                        <th style="width: 100px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Exam Score</th>
                        <th style="width: 120px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Status</th>
                        <th style="width: 150px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Interview Date</th>
                        <th style="width: 200px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-left">Interview Window</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignedApplicants as $index => $applicant)
                    @php
                        $interview = $applicant->latestInterview;
                        $canSchedule = $interview && (!$interview->schedule_date || $interview->status === 'assigned');
                        $hasCompletedExam = method_exists($applicant, 'hasCompletedExam') ? $applicant->hasCompletedExam() : ($applicant->status === 'exam-completed');
                        
                        // Check if applicant has been interviewed by department head
                        $hasDepartmentHeadInterview = \App\Models\Interview::where('applicant_id', $applicant->applicant_id)
                            ->where('status', 'completed')
                            ->whereHas('interviewer', function($query) {
                                $query->where('role', 'department-head');
                            })
                            ->exists();
                    @endphp
                    <tr style="position: relative;" 
                        onmouseover="showActions({{ $applicant->applicant_id }})" 
                        onmouseout="hideActions({{ $applicant->applicant_id }})">
                        <td class="text-center">
                            <input type="checkbox" 
                                   class="form-check-input applicant-checkbox" 
                                   @if($canSchedule && $interview && !$hasDepartmentHeadInterview)
                                       data-interview-id="{{ $interview->interview_id }}"
                                       data-applicant-name="{{ $applicant->first_name }} {{ $applicant->last_name }}"
                                       data-deadline-start="{{ $interview->interview_deadline_start ? $interview->interview_deadline_start->toIso8601String() : '' }}"
                                       data-deadline-end="{{ $interview->interview_deadline_end ? $interview->interview_deadline_end->toIso8601String() : '' }}"
                                       onchange="updateBulkActions()"
                                   @else
                                       disabled
                                       title="{{ !$interview ? 'No interview assigned' : ($hasDepartmentHeadInterview ? 'Already interviewed by department head' : 'Interview already scheduled or cannot be scheduled') }}"
                                   @endif
                                   style="cursor: {{ ($canSchedule && $interview && !$hasDepartmentHeadInterview) ? 'pointer' : 'not-allowed' }};">
                        </td>
                        <td class="text-left" style="font-size: 13px; font-weight: normal;">
                            <div class="applicant-info">
                                <div class="applicant-name" style="font-weight: 500; color: #1F2937; margin-bottom: 4px;">{{ $applicant->first_name }} {{ $applicant->last_name }}</div>
                                <div class="applicant-email" style="font-size: 12px; color: #6B7280;">{{ $applicant->email_address }}</div>
                                @if($interview && $interview->assignment_notes)
                                    <button type="button" 
                                            onclick="toggleAssignmentInfo({{ $applicant->applicant_id }})" 
                                            style="background: none; border: none; cursor: pointer; padding: 4px; font-size: 1.2rem; color: #3B82F6; margin-top: 4px;"
                                            title="View assignment message">
                                        📋
                                    </button>
                                    <div id="assignment-info-{{ $applicant->applicant_id }}" 
                                         style="display: none; margin-top: 8px; padding: 10px; background: #F3F4F6; border-radius: 6px; font-size: 0.875rem; color: #374151;">
                                        <strong style="color: #1F2937;">Assignment Message:</strong><br>
                                        <div style="margin-top: 4px;">{{ $interview->assignment_notes }}</div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="text-center" style="font-size: 13px; font-weight: normal;">
                            <div class="applicant-name" style="font-weight: 500; color: #1F2937;">{{ $applicant->application_no }}</div>
                        </td>
                        <td class="text-center" style="font-size: 13px; font-weight: normal;">
                            @php
                                $examScore = $applicant->enrollassess_score ?? null;
                            @endphp
                            @if($examScore !== null)
                                <div class="applicant-name" style="font-weight: 500; color: #1F2937;">{{ number_format($examScore, 2) }}%</div>
                            @else
                                <span class="applicant-email" style="font-size: 12px; color: #6B7280;">-</span>
                            @endif
                        </td>
                        <td class="text-center" style="padding: 6px 4px;">
                            @php
                                $status = $applicant->status;
                                $statusConfig = [
                                    'exam-completed' => ['label' => 'FOR INTERVIEW', 'class' => 'status-completed'],
                                    'interview-available' => ['label' => 'INTERVIEW READY', 'class' => 'status-pending'],
                                    'interview-scheduled' => ['label' => 'INTERVIEW SET', 'class' => 'status-pending'],
                                    'interview-completed' => ['label' => 'INTERVIEW DONE', 'class' => 'status-interviewcompleted'],
                                    'admitted' => ['label' => 'ADMITTED', 'class' => 'status-completed'],
                                    'rejected' => ['label' => 'REJECTED', 'class' => 'status-examcompleted'],
                                    'pending' => ['label' => 'PENDING', 'class' => 'status-pending'],
                                ];
                                $statusText = $statusConfig[$status]['label'] ?? strtoupper(str_replace('-', ' ', $status));
                                $statusClass = $statusConfig[$status]['class'] ?? 'status-pending';
                            @endphp
                            <span class="status-badge {{ $statusClass }}" style="font-size: 9px; padding: 2px 8px; border-radius: 3px; font-weight: 600; white-space: nowrap; display: inline-block;">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="text-center" style="font-size: 13px; font-weight: normal;">
                            @if($interview && $interview->schedule_date)
                                <div class="applicant-name" style="font-weight: 500; color: #1F2937;">{{ $interview->schedule_date->format('M d, Y g:i A') }}</div>
                            @else
                                <span class="applicant-email" style="font-size: 12px; color: #6B7280;">Not scheduled</span>
                            @endif
                        </td>
                        <td class="text-left" style="font-size: 13px; font-weight: normal; position: relative;">
                            @if($interview && $interview->interview_deadline_start && $interview->interview_deadline_end)
                                <div style="font-size: 12px;">
                                    <div style="color: #374151; font-weight: 500;">
                                        {{ $interview->interview_deadline_start->format('M d, Y') }} - 
                                        {{ $interview->interview_deadline_end->format('M d, Y') }}
                                    </div>
                                    @php
                                        $now = now();
                                        $daysUntilEnd = $now->diffInDays($interview->interview_deadline_end, false);
                                    @endphp
                                    @if($interview->interview_deadline_end->isPast())
                                        <span style="color: #DC2626; font-size: 11px; font-weight: 600;">⚠️ Deadline passed</span>
                                    @elseif($daysUntilEnd <= 3 && $daysUntilEnd >= 0)
                                        <span style="color: #F59E0B; font-size: 11px; font-weight: 600;">⚠️ Due soon</span>
                                    @endif
                                </div>
                            @else
                                <span style="color: #9CA3AF; font-size: 12px;">No deadline set</span>
                            @endif
                            <!-- Floating Actions -->
                            <div id="actions-{{ $applicant->applicant_id }}" class="floating-actions" style="display: none;">
                                @php
                                    // Determine Schedule button state
                                    $scheduleEnabled = $interview && $canSchedule && $hasCompletedExam && !$hasDepartmentHeadInterview;
                                    $scheduleTooltip = "Schedule Interview";
                                    if (!$scheduleEnabled) {
                                        if (!$interview) {
                                            $scheduleTooltip = "No interview assigned";
                                        } elseif ($hasDepartmentHeadInterview) {
                                            $scheduleTooltip = "Already interviewed by department head";
                                        } elseif (!$hasCompletedExam) {
                                            $scheduleTooltip = "Applicant must complete the exam first";
                                        } elseif ($interview->schedule_date) {
                                            $scheduleTooltip = "Interview already scheduled";
                                        } else {
                                            $scheduleTooltip = "Interview not available";
                                        }
                                    }
                                    
                                    // Determine Start button state
                                    $startEnabled = in_array($applicant->status, ['exam-completed', 'interview-scheduled']) && !$hasDepartmentHeadInterview;
                                    $startTooltip = "Start Interview";
                                    if (!$startEnabled) {
                                        if ($hasDepartmentHeadInterview) {
                                            $startTooltip = "Already interviewed by department head";
                                        } elseif ($applicant->status === 'interview-completed') {
                                            $startTooltip = "Interview already completed";
                                        } elseif ($applicant->status === 'pending') {
                                            $startTooltip = "Applicant must complete the exam first";
                                        } else {
                                            $startTooltip = "Interview not ready";
                                        }
                                    }
                                    
                                    // Determine View button state - check for any completed interview
                                    $hasCompletedInterview = \App\Models\Interview::where('applicant_id', $applicant->applicant_id)
                                        ->where('status', 'completed')
                                        ->exists();
                                    $viewEnabled = $hasCompletedInterview;
                                    $viewTooltip = "View Interview Summary";
                                    if (!$viewEnabled) {
                                        if (in_array($applicant->status, ['exam-completed', 'interview-scheduled'])) {
                                            $viewTooltip = "Interview not completed yet";
                                        } elseif ($applicant->status === 'pending') {
                                            $viewTooltip = "Interview not started";
                                        } else {
                                            $viewTooltip = "Interview not completed";
                                        }
                                    }
                                @endphp
                                
                                <!-- Schedule Button -->
                                @if($scheduleEnabled)
                                    <button type="button" 
                                            class="action-btn action-btn-primary" 
                                            onclick="openScheduleModal(
                                                {{ $interview->interview_id }}, 
                                                '{{ $applicant->first_name }} {{ $applicant->last_name }}',
                                                '{{ $interview->interview_deadline_start ? $interview->interview_deadline_start->toIso8601String() : '' }}',
                                                '{{ $interview->interview_deadline_end ? $interview->interview_deadline_end->toIso8601String() : '' }}'
                                            )"
                                            title="{{ $scheduleTooltip }}">
                                        Schedule
                                    </button>
                                @else
                                    <button type="button" 
                                            class="action-btn action-btn-disabled tooltip" 
                                            disabled
                                            data-tip="{{ $scheduleTooltip }}"
                                            title="{{ $scheduleTooltip }}">
                                        Schedule
                                    </button>
                                @endif
                                
                                <!-- Start Button -->
                                @if($startEnabled)
                                    <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                       class="action-btn action-btn-primary"
                                       title="{{ $startTooltip }}">
                                        Start
                                    </a>
                                @else
                                    <span class="action-btn action-btn-disabled tooltip" 
                                          data-tip="{{ $startTooltip }}"
                                          title="{{ $startTooltip }}"
                                          style="cursor: not-allowed;">
                                        Start
                                    </span>
                                @endif
                                
                                <!-- View Button -->
                                @if($viewEnabled)
                                    <a href="{{ route('instructor.interview.summary', $applicant->applicant_id) }}" 
                                       class="action-btn action-btn-secondary"
                                       title="{{ $viewTooltip }}">
                                        View
                                    </a>
                                @else
                                    <span class="action-btn action-btn-disabled tooltip" 
                                          data-tip="{{ $viewTooltip }}"
                                          title="{{ $viewTooltip }}"
                                          style="cursor: not-allowed;">
                                        View
                                    </span>
                                @endif
                            </div>
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
        <div class="text-center py-5">
            <div class="text-muted">
                <h5>No applicants found</h5>
                <p class="mb-0">
                    @if(request()->hasAny(['search', 'status']))
                        Try adjusting your search or filter criteria.
                    @else
                        No applicants have been assigned to you yet.
                    @endif
                </p>
            </div>
        </div>
    @endif
</div>

