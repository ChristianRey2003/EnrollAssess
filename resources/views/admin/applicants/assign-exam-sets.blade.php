@extends('layouts.admin')

@section('title', 'Exam Set Assignment')

@php
    $pageTitle = 'Exam Set Assignment';
    $pageSubtitle = 'Assign exam sets to applicants for the entrance examination';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/applicants.css') }}" rel="stylesheet">
    <style>
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .distribution-preview {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        
        .distribution-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .distribution-item:last-child {
            border-bottom: none;
        }
        
        .set-info {
            font-weight: 500;
        }
        
        .set-count {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .assignment-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
        }
        
        .btn-assignment {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-auto {
            background: #3b82f6;
            color: white;
        }
        
        .btn-auto:hover {
            background: #2563eb;
        }
        
        .btn-manual {
            background: #6b7280;
            color: white;
        }
        
        .btn-manual:hover {
            background: #4b5563;
        }
        
        .applicant-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .applicant-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            position: relative;
        }
        
        .applicant-card.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .applicant-checkbox {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
        }
        
        .applicant-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .applicant-email {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .current-assignment {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            background: #f3f4f6;
            color: #374151;
        }
        
        .current-assignment.assigned {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .filters-section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .filter-row {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        /* Drawer Styles */
        .drawer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .drawer-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 400px;
            height: 100%;
            background: white;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1001;
            overflow-y: auto;
        }
        
        .drawer.active {
            transform: translateX(0);
        }
        
        .drawer-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        
        .drawer-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        
        .drawer-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }
        
        .drawer-close:hover {
            background: #e5e7eb;
            color: #374151;
        }
        
        .drawer-body {
            padding: 20px;
        }
        
        .drawer-footer {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            color: #6b7280;
        }
        
        .modal-footer {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }
    </style>
@endpush

@section('content')
    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-label">Total Applicants</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['assigned'] ?? 0 }}</div>
            <div class="stat-label">Assigned</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['unassigned'] ?? 0 }}</div>
            <div class="stat-label">Unassigned</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ count($examSets ?? []) }}</div>
            <div class="stat-label">Available Sets</div>
        </div>
    </section>


    @if(count($examSets ?? []) == 0)
        <!-- No Exam Sets Warning -->
        <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #f59e0b; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">!</div>
                <div>
                    <div style="font-weight: 600; color: #92400e; margin-bottom: 4px;">No Exam Sets Available</div>
                    <div style="color: #92400e; font-size: 14px;">
                        Please create exam sets first before assigning them to applicants.
                        Go to <a href="{{ route('admin.sets-questions.index') }}" style="color: #1e40af; text-decoration: underline;">Sets & Questions</a> to create exam sets.
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Distribution Preview -->
    @if(count($distribution ?? []) > 0)
        <div class="distribution-preview">
            <h4 style="margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600;">Current Distribution</h4>
            @foreach($distribution as $dist)
                <div class="distribution-item">
                    <div>
                        <div class="set-info">{{ $dist['exam_set']->set_name ?? 'Unknown Set' }} - {{ $dist['exam_set']->exam->title ?? 'Unknown Exam' }}</div>
                    </div>
                    <div>
                        <span class="set-count">{{ $dist['count'] }} applicants ({{ $dist['percentage'] }}%)</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Filters Section -->
    <div class="filters-section">
        <form method="GET" action="{{ route('admin.applicants.assign-exam-sets') }}">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search applicants..."
                           value="{{ request('search') }}">
                </div>
                <div class="filter-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="exam-completed" {{ request('status') == 'exam-completed' ? 'selected' : '' }}>Exam Completed</option>
                        <option value="interview-scheduled" {{ request('status') == 'interview-scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="exam_set_filter" class="form-label">Assignment Status</label>
                    <select id="exam_set_filter" name="exam_set_filter" class="form-control">
                        <option value="">All Applicants</option>
                        <option value="unassigned" {{ request('exam_set_filter') == 'unassigned' ? 'selected' : '' }}>Unassigned Only</option>
                        <option value="assigned" {{ request('exam_set_filter') == 'assigned' ? 'selected' : '' }}>Assigned Only</option>
                        @foreach($examSets ?? [] as $examSet)
                            <option value="{{ $examSet->exam_set_id }}" {{ request('exam_set_filter') == $examSet->exam_set_id ? 'selected' : '' }}>
                                {{ $examSet->set_name }} - {{ $examSet->exam->title ?? 'Unknown Exam' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div id="bulkActions" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span id="selectedCount" style="font-weight: 600; color: #1e40af;">0 selected</span>
            </div>
            <div style="display: flex; gap: 12px;">
                @if(count($examSets ?? []) > 0)
                    <button onclick="openAssignmentDrawer()" class="btn btn-primary" style="background: #3b82f6;">
                        Assign Exam Sets
                    </button>
                @else
                    <div style="color: #6b7280; font-size: 14px; font-style: italic;">
                        No exam sets available for assignment
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Applicants Grid -->
    <div class="applicant-grid">
        @forelse($applicants as $applicant)
            <div class="applicant-card" id="card-{{ $applicant->applicant_id }}">
                <input type="checkbox" 
                       class="applicant-checkbox" 
                       value="{{ $applicant->applicant_id }}"
                       onchange="updateSelection(this)">
                
                <div class="applicant-name">{{ $applicant->full_name }}</div>
                <div class="applicant-email">{{ $applicant->email_address }}</div>
                <div class="applicant-email" style="font-size: 0.75rem;">{{ $applicant->application_no }}</div>
                
                <div class="current-assignment {{ $applicant->examSet ? 'assigned' : '' }}">
                    @if($applicant->examSet)
                        <div style="font-weight: 500;">{{ $applicant->examSet->set_name }}</div>
                        <div style="font-size: 11px; color: #6b7280;">{{ $applicant->examSet->exam->title ?? 'Unknown Exam' }}</div>
                    @else
                        <div style="color: #ef4444;">Not Assigned</div>
                    @endif
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #6b7280;">
                No applicants found matching your criteria.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($applicants->hasPages())
        <div style="margin-top: 25px; display: flex; justify-content: center;">
            {{ $applicants->appends(request()->query())->links() }}
        </div>
    @endif

    <!-- Back to Applicants Button -->
    <div style="margin-top: 25px; text-align: center;">
        <a href="{{ route('admin.applicants.index') }}" 
           class="btn btn-secondary" 
           style="padding: 8px 16px; background: #6b7280; color: white; text-decoration: none; border-radius: 6px;">
            ← Back to Applicants
        </a>
    </div>

<!-- Assignment Drawer -->
<div id="assignmentDrawerOverlay" class="drawer-overlay" onclick="closeAssignmentDrawer()"></div>
<div id="assignmentDrawer" class="drawer">
    <div class="drawer-header">
        <h3 class="drawer-title" id="drawerTitle">Assign Exam Sets</h3>
        <button type="button" class="drawer-close" onclick="closeAssignmentDrawer()">×</button>
    </div>
    
    <div class="drawer-body">
        <!-- Assignment Mode Selection -->
        <div style="margin-bottom: 20px;">
            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Assignment Mode</label>
            <div style="display: flex; gap: 8px;">
                <button type="button" id="autoModeBtn" onclick="setAssignmentMode('auto')" class="btn btn-primary" style="flex: 1; font-size: 12px;">
                    Auto Distribute
                </button>
                <button type="button" id="manualModeBtn" onclick="setAssignmentMode('manual')" class="btn btn-secondary" style="flex: 1; font-size: 12px;">
                    Manual Assign
                </button>
            </div>
        </div>

        <!-- Selected Applicants Info -->
        <div style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <div style="font-weight: 600; margin-bottom: 4px;">Selected Applicants</div>
            <div style="font-size: 14px; color: #6b7280;">
                <span id="selectedApplicantsCount">0</span> applicants selected
            </div>
        </div>

        <!-- Auto Distribution Info -->
        <div id="autoDistributionInfo" style="display: none; background: #eff6ff; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <div style="font-weight: 600; margin-bottom: 8px;">Auto Distribution</div>
            <div style="font-size: 14px; color: #1e40af;">
                <div>Available Sets: {{ count($examSets ?? []) }}</div>
                <div>Distribution: ~<span id="perSetCount">0</span> per set</div>
            </div>
        </div>

        <!-- Manual Assignment Selection -->
        <div id="manualAssignmentInfo" style="display: none; margin-bottom: 20px;">
            <label for="examSetSelect" style="font-weight: 600; margin-bottom: 8px; display: block;">Select Exam Set</label>
            <select id="examSetSelect" class="form-control" style="width: 100%;">
                <option value="">Choose an exam set</option>
                @foreach($examSets ?? [] as $examSet)
                    <option value="{{ $examSet->exam_set_id }}">
                        {{ $examSet->set_name }} - {{ $examSet->exam->title ?? 'Unknown Exam' }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Email Notifications -->
        <div style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 14px;">
                <input type="checkbox" id="sendNotifications" checked>
                Send email notifications to applicants
            </label>
        </div>
    </div>
    
    <div class="drawer-footer">
        <button type="button" class="btn btn-secondary" onclick="closeAssignmentDrawer()">Cancel</button>
        <button type="button" class="btn btn-primary" id="assignButton" onclick="confirmAssignment()">Assign</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedApplicants = [];
let currentAssignmentMode = 'manual'; // 'auto' or 'manual'


function updateSelection(checkbox) {
    const applicantId = checkbox.value;
    const card = document.getElementById('card-' + applicantId);
    
    if (checkbox.checked) {
        selectedApplicants.push(applicantId);
        card.classList.add('selected');
    } else {
        selectedApplicants = selectedApplicants.filter(id => id !== applicantId);
        card.classList.remove('selected');
    }
    
    console.log('Selected applicants updated:', selectedApplicants);
    updateBulkActions();
}

function updateBulkActions() {
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedApplicants.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = selectedApplicants.length + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
    
    // Update drawer if open
    const countSpan = document.getElementById('selectedApplicantsCount');
    if (countSpan) {
        countSpan.textContent = selectedApplicants.length;
    }
}

// Open assignment drawer
function openAssignmentDrawer() {
    console.log('Opening assignment drawer. Selected applicants:', selectedApplicants);
    
    if (selectedApplicants.length === 0) {
        alert('Please select at least one applicant first.');
        return;
    }
    
    const overlay = document.getElementById('assignmentDrawerOverlay');
    const drawer = document.getElementById('assignmentDrawer');
    
    if (overlay && drawer) {
        overlay.classList.add('active');
        drawer.classList.add('active');
        
        // Update selected count
        updateBulkActions();
        
        // Set default mode to manual
        setAssignmentMode('manual');
        
        console.log('Drawer opened successfully');
    } else {
        console.error('Drawer elements not found');
    }
}

// Close assignment drawer
function closeAssignmentDrawer() {
    const overlay = document.getElementById('assignmentDrawerOverlay');
    const drawer = document.getElementById('assignmentDrawer');
    
    if (overlay && drawer) {
        overlay.classList.remove('active');
        drawer.classList.remove('active');
        
        console.log('Drawer closed');
    }
}

// Set assignment mode (auto or manual)
function setAssignmentMode(mode) {
    console.log('Setting assignment mode to:', mode);
    currentAssignmentMode = mode;
    
    const autoBtn = document.getElementById('autoModeBtn');
    const manualBtn = document.getElementById('manualModeBtn');
    const autoInfo = document.getElementById('autoDistributionInfo');
    const manualInfo = document.getElementById('manualAssignmentInfo');
    const drawerTitle = document.getElementById('drawerTitle');
    const assignButton = document.getElementById('assignButton');
    
    if (mode === 'auto') {
        // Update button styles
        autoBtn.className = 'btn btn-primary';
        autoBtn.style.background = '#3b82f6';
        manualBtn.className = 'btn btn-secondary';
        manualBtn.style.background = '#6b7280';
        
        // Show/hide sections
        autoInfo.style.display = 'block';
        manualInfo.style.display = 'none';
        
        // Update title and button
        drawerTitle.textContent = 'Auto Distribute Exam Sets';
        assignButton.textContent = 'Auto Distribute';
        
        // Update distribution count
        const totalSets = {{ count($examSets ?? []) }};
        const perSet = Math.ceil(selectedApplicants.length / totalSets);
        document.getElementById('perSetCount').textContent = perSet;
        
    } else {
        // Update button styles
        manualBtn.className = 'btn btn-primary';
        manualBtn.style.background = '#3b82f6';
        autoBtn.className = 'btn btn-secondary';
        autoBtn.style.background = '#6b7280';
        
        // Show/hide sections
        autoInfo.style.display = 'none';
        manualInfo.style.display = 'block';
        
        // Update title and button
        drawerTitle.textContent = 'Manual Exam Set Assignment';
        assignButton.textContent = 'Assign to Set';
    }
}

// Confirm assignment based on current mode
function confirmAssignment() {
    console.log('Confirming assignment. Mode:', currentAssignmentMode);
    console.log('Selected applicants:', selectedApplicants);
    
    if (selectedApplicants.length === 0) {
        alert('Please select at least one applicant.');
        return;
    }
    
    const sendNotifications = document.getElementById('sendNotifications').checked;
    const assignButton = document.getElementById('assignButton');
    
    // Show loading state
    const originalText = assignButton.textContent;
    assignButton.textContent = 'Processing...';
    assignButton.disabled = true;
    
    let requestData = {
        applicant_ids: selectedApplicants,
        assignment_mode: currentAssignmentMode === 'auto' ? 'auto_distribute' : 'manual_assign',
        send_notifications: sendNotifications
    };
    
    // Add exam set ID for manual assignment
    if (currentAssignmentMode === 'manual') {
        const examSetId = document.getElementById('examSetSelect').value;
        if (!examSetId) {
            alert('Please select an exam set.');
            assignButton.textContent = originalText;
            assignButton.disabled = false;
            return;
        }
        requestData.exam_set_id = examSetId;
    }
    
    console.log('Sending request:', requestData);
    
    fetch('{{ route("admin.applicants.process-exam-sets") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert(data.message);
            closeAssignmentDrawer();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        alert('Network error: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        assignButton.textContent = originalText;
        assignButton.disabled = false;
    });
}

// Legacy functions removed - now using drawer-based assignment
</script>
@endpush
