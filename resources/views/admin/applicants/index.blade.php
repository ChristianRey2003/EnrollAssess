@extends('layouts.admin')

@section('title', 'Applicants Management')

@php
    $pageTitle = 'Applicants Management';
    $pageSubtitle = 'Track and manage all BSIT entrance examination applicants';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/applicants.css') }}" rel="stylesheet">
    <style>
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
            z-index: 10;
        }
        
        .floating-actions .action-btn {
            padding: 6px 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        
        .floating-actions .action-btn:hover {
            background: #f3f4f6;
        }
        
        .floating-actions .action-btn-delete:hover {
            background: #fee2e2;
        }
        
        tr:hover {
            background-color: rgba(255, 215, 0, 0.1) !important;
        }
        
        /* Ensure table stays within container */
        .applicants-table {
            max-width: 100%;
            overflow-x: auto;
        }
        
        .data-table {
            table-layout: fixed;
            width: 100%;
            max-width: 100%;
        }
        
        .status-badge {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-word;
        }
        
        /* Compact toolbar responsive styles */
        @media (max-width: 1200px) {
            .applicants-toolbar {
                flex-direction: column !important;
                gap: 10px !important;
                align-items: stretch !important;
            }
            
            .toolbar-left, .toolbar-right {
                justify-content: center !important;
                flex-wrap: wrap !important;
            }
            
            .toolbar-right {
                gap: 6px !important;
            }
        }
        
        @media (max-width: 768px) {
            .applicants-toolbar input[type="text"] {
                width: 150px !important;
            }
            
            .applicants-toolbar select {
                width: 100px !important;
            }
            
            .toolbar-right a {
                padding: 4px 8px !important;
                font-size: 12px !important;
            }
        }
    </style>
@endpush

@section('content')

                <!-- Statistics Section -->
                <section class="stats-section">
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['total_applicants'] ?? 0 }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['with_access_codes'] ?? 0 }}</div>
                        <div class="stat-label">With Access Codes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['exam_completed'] ?? 0 }}</div>
                        <div class="stat-label">Exam Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['pending_admission'] ?? 0 }}</div>
                        <div class="stat-label">Pending Admission</div>
                    </div>
                </section>

                <!-- Compact Toolbar -->
                <div class="applicants-toolbar" style="display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 20px; padding: 12px 0;">
                    <div class="toolbar-left" style="display: flex; align-items: center; gap: 8px;">
                        <input type="text" 
                               id="searchInput" 
                               class="form-control" 
                               placeholder="Search applicants..." 
                               value="{{ request('search') }}"
                               style="width: 200px; height: 32px; padding: 4px 8px; font-size: 13px; border: 1px solid #d1d5db; border-radius: 4px;"
                               aria-label="Search applicants">
                        <button onclick="performSearch()" 
                                class="btn btn-secondary" 
                                style="height: 32px; padding: 4px 12px; font-size: 13px; border-radius: 4px;">Search</button>
                    </div>
                    <div class="toolbar-right" style="display: flex; align-items: center; gap: 8px;">
                        <select id="statusFilter" 
                                class="form-control" 
                                onchange="applyFilter()" 
                                style="width: 120px; height: 32px; padding: 4px 8px; font-size: 13px; border: 1px solid #d1d5db; border-radius: 4px;"
                                aria-label="Filter by status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="exam_completed" {{ request('status') == 'exam_completed' ? 'selected' : '' }}>Exam Completed</option>
                            <option value="interview_scheduled" {{ request('status') == 'interview_scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                            <option value="interview_completed" {{ request('status') == 'interview_completed' ? 'selected' : '' }}>Interview Completed</option>
                            <option value="admitted" {{ request('status') == 'admitted' ? 'selected' : '' }}>Admitted</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        <a href="{{ route('admin.applicants.assign') }}" 
                           class="btn btn-primary" 
                           style="height: 32px; padding: 4px 12px; font-size: 13px; border-radius: 4px; background: #800020; border: none; color: white; text-decoration: none; display: inline-flex; align-items: center;">Assign</a>
                        <a href="{{ route('admin.applicants.exam-results') }}" 
                           class="btn btn-primary" 
                           style="height: 32px; padding: 4px 12px; font-size: 13px; border-radius: 4px; background: #059669; border: none; color: white; text-decoration: none; display: inline-flex; align-items: center;">Exam Results</a>
                        <a href="{{ route('admin.applicants.create') }}" 
                           class="btn btn-secondary" 
                           style="height: 32px; padding: 4px 10px; font-size: 13px; border-radius: 4px; background: #6b7280; border: none; color: white; text-decoration: none; display: inline-flex; align-items: center;">Add</a>
                        <a href="{{ route('admin.applicants.import') }}" 
                           class="btn btn-secondary" 
                           style="height: 32px; padding: 4px 10px; font-size: 13px; border-radius: 4px; background: #6b7280; border: none; color: white; text-decoration: none; display: inline-flex; align-items: center;">Import</a>
                    </div>
                </div>

                <!-- Compact Bulk Actions -->
                <div id="bulkActions" class="bulk-actions" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 8px 12px; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="selectedCount" style="font-size: 13px; font-weight: 500; color: #1e40af;">0 selected</span>
                        <div style="display: flex; gap: 6px;">
                            <button onclick="showGenerateAccessCodesModal()" 
                                    class="bulk-btn" 
                                    style="height: 28px; padding: 4px 8px; font-size: 12px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                Generate Codes
                            </button>
                            <button onclick="showAssignExamModal()" 
                                    class="bulk-btn" 
                                    style="height: 28px; padding: 4px 8px; font-size: 12px; background: #8b5cf6; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                Assign Exam
                            </button>
                            <button onclick="openEmailNotificationDrawer()" 
                                    class="bulk-btn" 
                                    style="height: 28px; padding: 4px 8px; font-size: 12px; background: #059669; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                Send Exam Notifications
                            </button>
                            <button onclick="bulkExport()" 
                                    class="bulk-btn" 
                                    style="height: 28px; padding: 4px 8px; font-size: 12px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                Export
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Applicants Table -->
                <div class="applicants-table" style="overflow-x: auto;">
                    <table class="data-table" style="width: 100%; table-layout: fixed;">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" 
                                           id="selectAll" 
                                           onchange="toggleSelectAll()"
                                           style="cursor: pointer;">
                                </th>
                                <th style="width: 40px;">NO.</th>
                                <th style="width: 120px;">APPLICANT NO.</th>
                                <th style="width: 180px;">FULL NAME</th>
                                <th style="width: 200px;">CONTACT INFORMATION</th>
                                <th style="width: 120px;">PREFERRED COURSE</th>
                                <th style="width: 100px;">WEIGHTED EXAM % (60%)</th>
                                <th style="width: 120px;">VERBAL DESCRIPTION</th>
                                <th style="width: 120px;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applicants ?? [] as $index => $applicant)
                                <tr style="page-break-inside: avoid; position: relative;" 
                                    onmouseover="showActions({{ $applicant->applicant_id }})" 
                                    onmouseout="hideActions({{ $applicant->applicant_id }})">
                                    <td class="text-center">
                                        <input type="checkbox" 
                                               class="applicant-checkbox" 
                                               value="{{ $applicant->applicant_id }}"
                                               onchange="updateBulkActions()"
                                               style="cursor: pointer;">
                                    </td>
                                    <td class="text-center font-medium">
                                        {{ ($applicants->currentPage() - 1) * $applicants->perPage() + $index + 1 }}
                                    </td>
                                    <td>
                                        <div class="applicant-number">
                                            <span class="font-mono text-sm">{{ $applicant->application_no ?: $applicant->formatted_applicant_no }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="applicant-name">
                                            <div class="font-medium text-gray-900" style="font-size: 14px;">{{ strtoupper($applicant->full_name) }}</div>
                                            @if($applicant->assignedInstructor)
                                                <div style="font-size: 11px; color: #1e40af; font-weight: 500; margin-top: 2px;">
                                                    Instructor: {{ $applicant->assignedInstructor->full_name }}
                                                </div>
                                            @else
                                                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">
                                                    No Instructor
                                                </div>
                                            @endif
                                            @if($applicant->accessCode)
                                                <div style="font-size: 11px; margin-top: 2px;">
                                                    <strong>{{ $applicant->accessCode->code }}</strong>
                                                    @if($applicant->accessCode->exam_id)
                                                        <div style="color: #059669; margin-top: 1px;">
                                                            <span style="display: inline-block; width: 4px; height: 4px; border-radius: 50%; background: #059669; margin-right: 4px;"></span>
                                                            {{ $applicant->accessCode->exam->title }}
                                                        </div>
                                                    @else
                                                        <div style="color: #f59e0b; margin-top: 1px;">
                                                            <span style="display: inline-block; width: 4px; height: 4px; border-radius: 50%; background: #f59e0b; margin-right: 4px;"></span>
                                                            No exam assigned
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <!-- Floating Actions -->
                                        <div id="actions-{{ $applicant->applicant_id }}" class="floating-actions" style="display: none;">
                                            <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" 
                                               class="action-btn action-btn-view" 
                                               title="View details">
                                                View
                                            </a>
                                            <a href="{{ route('admin.applicants.edit', $applicant->applicant_id) }}" 
                                               class="action-btn action-btn-edit" 
                                               title="Edit applicant">
                                                Edit
                                            </a>
                                            @if($applicant->accessCode)
                                                <button onclick="showSingleAssignExamModal({{ $applicant->applicant_id }})" 
                                                        class="action-btn action-btn-assign" 
                                                        title="Assign exam{{ $applicant->accessCode && $applicant->accessCode->exam_id ? ' (' . $applicant->accessCode->exam->title . ')' : '' }}"
                                                        style="background: #8b5cf6;">
                                                    Assign Exam
                                                </button>
                                            @endif
                                            <button onclick="sendIndividualNotification({{ $applicant->applicant_id }})" 
                                                    class="action-btn action-btn-notify" 
                                                    title="Send exam notification">
                                                Email
                                            </button>
                                            <button onclick="deleteApplicant({{ $applicant->applicant_id }})" 
                                                    class="action-btn action-btn-delete" 
                                                    title="Delete applicant">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div class="email-address text-sm" style="font-size: 13px;">{{ $applicant->email_address }}</div>
                                            @if($applicant->phone_number)
                                                <div class="phone-number text-sm text-gray-500">{{ $applicant->phone_number }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center" style="font-size: 13px;">
                                        {{ $applicant->preferred_course ?: '-' }}
                                    </td>
                                    <td class="text-center">
                                        @if($applicant->score !== null)
                                            <span class="score-value">{{ number_format((float) $applicant->score, 2) }}%</span>
                                        @else
                                            <span class="no-score">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="font-size: 13px;">
                                        <span class="verbal-description">{{ $applicant->computed_verbal_description ?: '-' }}</span>
                                    </td>
                                    <td class="text-center" style="padding: 6px 4px;">
                                        <span class="status-badge status-pending" style="font-size: 9px; padding: 2px 4px; border-radius: 3px; background: #fef3c7; color: #92400e; font-weight: 500; white-space: nowrap; display: inline-block;">
                                            @php
                                                $status = $applicant->status;
                                                $statusMap = [
                                                    'exam-completed' => 'EXAM DONE',
                                                    'interview-available' => 'INTERVIEW READY',
                                                    'interview-scheduled' => 'INTERVIEW SET',
                                                    'interview-completed' => 'INTERVIEW DONE',
                                                    'admitted' => 'ADMITTED',
                                                    'rejected' => 'REJECTED',
                                                    'pending' => 'PENDING'
                                                ];
                                                echo $statusMap[$status] ?? strtoupper(str_replace('-', ' ', $status));
                                            @endphp
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-8">
                                        <div class="empty-state">
                                            <div class="empty-title">No applicants found</div>
                                            <div class="empty-message">
                                                @if(request()->hasAny(['search', 'status']))
                                                    Try adjusting your search criteria or filters.
                                                @else
                                                    Start by importing applicants or adding them manually.
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(isset($applicants) && $applicants->hasPages())
                    <div class="pagination-wrapper">
                        {{ $applicants->links() }}
                    </div>
                @endif
    </div>

    <!-- Generate Access Codes Modal -->
    <div id="generateCodesModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Generate Access Codes</h3>
                <button type="button" class="modal-close" onclick="closeGenerateCodesModal()" aria-label="Close modal">×</button>
            </div>
            <div class="modal-body">
                <form id="generateCodesForm">
                    <div class="form-group">
                        <label for="expiry_hours" class="form-label required">Expiry Hours</label>
                        <input type="number" 
                               id="expiry_hours" 
                               name="expiry_hours" 
                               class="form-control" 
                               value="72" 
                               min="1" 
                               max="168"
                               required
                               aria-describedby="expiry-help">
                        <div id="expiry-help" class="form-help">How many hours should the access code be valid?</div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" 
                                   id="send_email" 
                                   name="send_email" 
                                   class="checkbox-input" 
                                   checked>
                            <label for="send_email" class="checkbox-label">
                                Send email notifications to applicants
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeGenerateCodesModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmGenerateAccessCodes()">Generate Codes</button>
            </div>
        </div>
    </div>

@endsection

<!-- Include Exam Notification Modal -->
@include('components.exam-notification-modal')

<!-- Include Assign Exam Modal -->
@include('admin.applicants.partials.assign-exam-modal')

@push('scripts')
    <script src="{{ asset('js/modules/applicant-manager.js') }}" defer></script>
    <script>
        function showActions(applicantId) {
            document.getElementById('actions-' + applicantId).style.display = 'flex';
        }
        
        function hideActions(applicantId) {
            document.getElementById('actions-' + applicantId).style.display = 'none';
        }

        // Send individual exam notification
        function sendIndividualNotification(applicantId) {
            console.log('Sending individual notification to applicant:', applicantId);
            
            // Set the global selectedApplicants to just this applicant
            window.selectedApplicants = [applicantId];
            
            // Also update the applicant manager if available
            if (window.applicantManager) {
                window.applicantManager.selectedApplicants.clear();
                window.applicantManager.selectedApplicants.add(applicantId);
            }
            
            // Open the email notification drawer
            openEmailNotificationDrawer();
        }

        // Show bulk assign exam drawer
        function showAssignExamModal() {
            const selected = document.querySelectorAll('.applicant-checkbox:checked');
            if (selected.length === 0) {
                alert('Please select at least one applicant');
                return;
            }

            const overlay = document.getElementById('assignExamDrawerOverlay');
            const drawer = document.getElementById('assignExamDrawer');
            
            if (!overlay || !drawer) {
                console.error('Bulk assign exam drawer elements not found');
                return;
            }
            
            overlay.classList.add('active');
            drawer.classList.add('active');
            
            document.getElementById('bulk_selected_count').textContent = selected.length;
            
            // Show exam details when exam is selected
            const examSelect = document.getElementById('bulk_exam_id');
            if (examSelect) {
                // Remove any existing event listeners by cloning
                const newExamSelect = examSelect.cloneNode(true);
                examSelect.parentNode.replaceChild(newExamSelect, examSelect);
                
                newExamSelect.addEventListener('change', function() {
                    const option = this.options[this.selectedIndex];
                    if (this.value) {
                        document.getElementById('examDuration').textContent = option.dataset.duration || 'N/A';
                        document.getElementById('examQuestions').textContent = option.dataset.total || 'N/A';
                        document.getElementById('examDetails').style.display = 'block';
                    } else {
                        document.getElementById('examDetails').style.display = 'none';
                    }
                });
            }
        }

        function closeAssignExamDrawer() {
            const overlay = document.getElementById('assignExamDrawerOverlay');
            const drawer = document.getElementById('assignExamDrawer');
            
            if (overlay && drawer) {
                overlay.classList.remove('active');
                drawer.classList.remove('active');
                
                // Reset form
                document.getElementById('bulk_exam_id').value = '';
                document.getElementById('examDetails').style.display = 'none';
            }
        }

        async function submitBulkExamAssignment() {
            const examId = document.getElementById('bulk_exam_id').value;
            if (!examId) {
                alert('Please select an exam');
                return;
            }

            const selectedCheckboxes = document.querySelectorAll('.applicant-checkbox:checked');
            const applicantIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));

            if (applicantIds.length === 0) {
                alert('No applicants selected');
                return;
            }

            const btn = document.getElementById('bulkAssignBtn');
            btn.disabled = true;
            btn.textContent = 'Assigning...';

            try {
                const response = await fetch('/admin/applicants/assign-exam', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        applicant_ids: applicantIds,
                        exam_id: examId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    closeAssignExamDrawer();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.textContent = 'Assign Exam';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while assigning exam');
                btn.disabled = false;
                btn.textContent = 'Assign Exam';
            }
        }

        // Show single assign exam drawer
        function showSingleAssignExamModal(applicantId) {
            const overlay = document.getElementById('singleAssignExamDrawerOverlay');
            const drawer = document.getElementById('singleAssignExamDrawer');
            
            if (!overlay || !drawer) {
                console.error('Single assign exam drawer elements not found');
                return;
            }
            
            overlay.classList.add('active');
            drawer.classList.add('active');
            
            document.getElementById('single_applicant_id').value = applicantId;
            
            // Find applicant name from the table
            try {
                const row = document.querySelector(`input.applicant-checkbox[value="${applicantId}"]`).closest('tr');
                const nameElement = row.querySelector('.applicant-name .font-medium');
                const name = nameElement ? nameElement.textContent.trim() : 'Applicant #' + applicantId;
                document.getElementById('single_applicant_name').textContent = name;
            } catch (error) {
                console.error('Error finding applicant name:', error);
                document.getElementById('single_applicant_name').textContent = 'Applicant #' + applicantId;
            }
            
            // Show exam details when exam is selected
            const examSelect = document.getElementById('single_exam_id');
            if (examSelect) {
                // Remove any existing event listeners by cloning
                const newExamSelect = examSelect.cloneNode(true);
                examSelect.parentNode.replaceChild(newExamSelect, examSelect);
                
                newExamSelect.addEventListener('change', function() {
                    const option = this.options[this.selectedIndex];
                    if (this.value) {
                        document.getElementById('singleExamDuration').textContent = option.dataset.duration || 'N/A';
                        document.getElementById('singleExamQuestions').textContent = option.dataset.total || 'N/A';
                        document.getElementById('singleExamDetails').style.display = 'block';
                    } else {
                        document.getElementById('singleExamDetails').style.display = 'none';
                    }
                });
            }
        }

        function closeSingleAssignExamDrawer() {
            const overlay = document.getElementById('singleAssignExamDrawerOverlay');
            const drawer = document.getElementById('singleAssignExamDrawer');
            
            if (overlay && drawer) {
                overlay.classList.remove('active');
                drawer.classList.remove('active');
                
                // Reset form
                document.getElementById('single_exam_id').value = '';
                document.getElementById('singleExamDetails').style.display = 'none';
            }
        }

        async function submitSingleExamAssignment() {
            const applicantId = document.getElementById('single_applicant_id').value;
            const examId = document.getElementById('single_exam_id').value;
            
            if (!examId) {
                alert('Please select an exam');
                return;
            }

            const btn = document.getElementById('singleAssignBtn');
            btn.disabled = true;
            btn.textContent = 'Assigning...';

            try {
                const response = await fetch(`/admin/applicants/${applicantId}/assign-exam`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        exam_id: examId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    closeSingleAssignExamDrawer();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.textContent = 'Assign Exam';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while assigning exam');
                btn.disabled = false;
                btn.textContent = 'Assign Exam';
            }
        }
    </script>
@endpush
