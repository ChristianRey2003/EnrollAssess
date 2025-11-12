@extends('layouts.admin')

@section('title', 'Assign Applicants to Instructors')

@php
    $pageTitle = 'Assign Applicants to Instructors';
    $pageSubtitle = 'Bulk assign applicants to instructors for interview scheduling';
@endphp

@push('styles')
<style>
    /* Remove horizontal padding from main-content to allow proper centering with header */
    /* This only affects this page to ensure assign-container aligns with header */
    .admin-main .main-content {
        padding-left: 0;
        padding-right: 0;
        padding-top: 5;
        padding-bottom: 30px;
    }

    /* ============================================
       LAYOUT CONTAINER - Consistent padding with header
       ============================================ */
    .assign-container {
        width: 100%;
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 30px 30px; /* No top padding to remove gap from header */
    }

    /* ============================================
       MAIN GRID LAYOUT - Single column (drawer replaces side panel)
       ============================================ */
    .assign-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        align-items: start;
    }

    /* Left panel - Applicants list */
    .assign-left {
        background: white;
        border-radius: 8px;
        padding: 24px; /* Consistent 24px padding */
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
        min-height: 600px; /* Ensure minimum height */
    }

    /* Drawer styles */
    .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        display: none;
        z-index: 1000;
    }

    .drawer {
        position: fixed;
        top: 0;
        right: -420px; /* hidden state */
        width: 420px;
        max-width: 100vw;
        height: 100vh;
        background: #FFFFFF;
        box-shadow: -2px 0 12px rgba(0,0,0,0.15);
        border-left: 1px solid #E5E7EB;
        z-index: 1001;
        display: flex;
        flex-direction: column;
        transition: right 0.25s ease;
    }

    .drawer.open { right: 0; }
    .drawer-overlay.open { display: block; }

    .drawer-header {
        padding: 16px 20px;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .drawer-title { font-weight: 700; font-size: 16px; }

    .drawer-body {
        padding: 20px;
        overflow: auto;
        flex: 1;
    }

    .drawer-footer {
        padding: 16px 20px;
        border-top: 1px solid #E5E7EB;
    }

    /* ============================================
       FILTERS SECTION - Improved Grid Layout
       ============================================ */
    .filters {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto; /* Fixed columns for consistency */
        gap: 16px;
        margin-bottom: 24px; /* Increased from 20px */
        padding: 20px; /* Match panel padding */
        background: #F9FAFB;
        border-radius: 8px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-group label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        letter-spacing: 0.01em;
    }

    .filter-group input,
    .filter-group select {
        height: 26px; /* Standardized height */
        padding: 8px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s;
        background: white;
    }

    /* Dropdown arrow for select elements */
    .filter-group select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #800020;
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .btn-filter,
    .btn-clear {
        height: 26px; /* Match input height */
        padding: 0 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
    }

    .btn-filter {
        background: #3B82F6;
        color: white;
    }

    .btn-filter:hover {
        background: #2563EB;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
    }

    .btn-clear {
        background: #6B7280;
        color: white;
    }

    .btn-clear:hover {
        background: #4B5563;
    }

    /* ============================================
       TABLE STYLES - Consistent Spacing
       ============================================ */
    .applicants-table {
        overflow-x: auto;
        margin: 0 -24px; /* Extend to panel edges */
        padding: 0 24px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead {
        background: #F9FAFB;
        border-top: 1px solid #E5E7EB;
        border-bottom: 2px solid #E5E7EB;
    }

    .data-table th {
        padding: 12px 16px; /* Consistent padding */
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .data-table td {
        padding: 14px 16px; /* Match header padding with slightly more vertical */
        border-bottom: 1px solid #F3F4F6;
        font-size: 14px;
        color: #111827;
    }

    .data-table tbody tr {
        transition: background-color 0.15s;
    }

    .data-table tbody tr:hover {
        background: #F9FAFB;
    }

    .checkbox-cell {
        width: 48px; /* Slightly wider for better click area */
        text-align: center;
    }

    .checkbox-cell input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #800020;
    }

    /* ============================================
       STATUS BADGES - Better Styling
       ============================================ */
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px; /* More rounded */
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.025em;
        text-transform: none !important; /* Override global uppercase transform */
    }

    .status-pending { 
        background: #FEF3C7; 
        color: #92400E;
        border: 1px solid #FDE68A;
    }
    
    .status-assigned { 
        background: #DBEAFE; 
        color: #1E40AF;
        border: 1px solid #BFDBFE;
    }
    
    .status-examcompleted { 
        background: #D1FAE5; 
        color: #065F46;
        border: 1px solid #A7F3D0;
    }

    .status-interviewscheduled {
        background: #E0E7FF;
        color: #3730A3;
        border: 1px solid #C7D2FE;
    }

    /* ============================================
       ASSIGNMENT PANEL - Right Side
       ============================================ */
    .assignment-panel h3 {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 20px 0;
    }

    .summary {
        padding: 16px;
        background: #EFF6FF;
        border: 2px solid #BFDBFE;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
    }

    .summary span {
        font-size: 24px;
        font-weight: 700;
        color: #1E40AF;
    }

    .summary-text {
        font-size: 13px;
        color: #60A5FA;
        font-weight: 500;
        margin-top: 2px;
    }

    .form-group {
        margin-bottom: 20px; /* Increased from 16px */
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px; /* Increased from 6px */
    }

    .form-control {
        width: 100%;
        height: 42px; /* Slightly taller */
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #800020;
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #374151;
        cursor: pointer;
        padding: 8px 0;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #800020;
    }

    .btn-primary {
        width: 100%;
        height: 46px; /* Slightly taller */
        background: #800020;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 4px;
    }

    .btn-primary:hover:not(:disabled) {
        background: #5C0016;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(128, 0, 32, 0.3);
    }

    .btn-primary:disabled {
        background: #D1D5DB;
        cursor: not-allowed;
        color: #9CA3AF;
    }

    /* ============================================
       PAGINATION
       ============================================ */
    .pagination-wrapper {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #E5E7EB;
        display: flex;
        justify-content: center;
    }

    /* ============================================
       EMPTY STATE
       ============================================ */
    .empty-state {
        padding: 80px 20px;
        text-align: center;
        color: #6B7280;
    }

    .empty-state h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #374151;
    }

    .empty-state p {
        font-size: 14px;
        color: #9CA3AF;
    }

    /* ============================================
       RESPONSIVE DESIGN - Multiple Breakpoints
       ============================================ */
    
    /* Extra large screens - wider spacing */
    @media (min-width: 1920px) {
        .assign-container {
            max-width: 1800px;
        }
        
        .assign-grid {
            grid-template-columns: 1fr 400px;
            gap: 32px;
        }
    }

    /* Large tablets and smaller desktops */
    @media (max-width: 1280px) {
        .assign-grid {
            grid-template-columns: 1fr 340px;
            gap: 20px;
        }
        
        .filters {
            grid-template-columns: 1fr 1fr auto;
            gap: 12px;
        }
        
        .filter-group:nth-child(3) {
            grid-column: 1 / 2;
        }
        
        .filter-actions {
            grid-column: 2 / 3;
        }
    }

    /* Tablets */
    @media (max-width: 1024px) {
        .assign-container {
            padding: 20px;
        }
        
        .assign-grid { gap: 20px; }
        
        .filters {
            grid-template-columns: 1fr;
            padding: 16px;
        }
        
        .filter-actions {
            flex-direction: row;
            justify-content: stretch;
        }
        
        .btn-filter,
        .btn-clear {
            flex: 1;
            height: 26px;
        }
    }

    /* Mobile phones */
    @media (max-width: 640px) {
        .assign-container {
            padding: 16px;
        }
        
        .assign-left,
        .assign-right {
            padding: 16px;
            border-radius: 6px;
        }
        
        .applicants-table {
            margin: 0 -16px;
            padding: 0 16px;
        }
        
        .data-table th,
        .data-table td {
            padding: 10px 8px;
            font-size: 13px;
        }
        
        .data-table th {
            font-size: 10px;
        }
        
        /* Hide less critical columns on mobile */
        .data-table th:nth-child(2),
        .data-table td:nth-child(2) {
            display: none;
        }
    }

    /* Very small screens */
    @media (max-width: 480px) {
        .summary span {
            font-size: 20px;
        }
        
        .filter-actions {
            flex-direction: column;
        }
        
        .btn-filter,
        .btn-clear {
            width: 100%;
            height: 26px;
        }
    }
</style>
@endpush

@section('content')
<div class="assign-container">
    <div class="assign-grid">
        <!-- Left Panel: Applicants List -->
        <div class="assign-left">
            <div class="filters">
                <div class="filter-group" style="position: relative;">
                    <input type="text" 
                           id="search" 
                           name="q" 
                           placeholder="Search..." 
                           value="{{ request('q') }}"
                           class="form-control form-control-sm"
                           style="height: 26px; padding: 4px 32px 4px 8px; width: 220px;">
                    <svg style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; pointer-events: none; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <div class="filter-group">
                    <select id="status" name="status" class="form-select form-select-sm" style="height: 26px; padding: 4px 28px 4px 8px;">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="exam-completed" {{ request('status') === 'exam-completed' ? 'selected' : '' }}>Exam Completed</option>
                        <option value="interview-scheduled" {{ request('status') === 'interview-scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                    </select>
                </div>

                <div class="filter-group">
                    <select id="assigned" name="assigned" class="form-select form-select-sm" style="height: 26px; padding: 4px 28px 4px 8px;">
                        <option value="">All</option>
                        <option value="unassigned" {{ request('assigned') === 'unassigned' ? 'selected' : '' }}>Unassigned Only</option>
                        <option value="assigned" {{ request('assigned') === 'assigned' ? 'selected' : '' }}>Assigned Only</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#6B7280;margin-right:8px;">
                        <span id="bulkCount">0</span> selected
                    </div>
                    <button type="button" id="openAssignDrawer" class="btn-filter" disabled>Assign Selected</button>
                    <button type="button" class="btn-clear" onclick="clearFilters()">Clear</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px; font-size: 0.85rem; font-weight: bold;">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-left">Applicant no</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-left">Name</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-left">Email</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-center">Status</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-left">Assigned instructor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applicants as $applicant)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" 
                                           class="form-check-input rowChk" 
                                           value="{{ $applicant->applicant_id }}"
                                           data-name="{{ $applicant->full_name }}">
                                </td>
                                <td class="text-left" style="font-size: 13px; font-weight: normal;">{{ $applicant->application_no ?: $applicant->formatted_applicant_no }}</td>
                                <td class="text-left" style="font-size: 13px; font-weight: normal;">{{ $applicant->full_name }}</td>
                                <td class="text-left" style="font-size: 13px; font-weight: normal;">{{ $applicant->email_address }}</td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    <span class="badge bg-secondary">
                                        {{ ucwords(str_replace('-', ' ', $applicant->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($applicant->assignedInstructor)
                                        {{ $applicant->assignedInstructor->full_name }}
                                    @else
                                        <span style="color: #9ca3af;">Not Assigned</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <h5>No applicants found</h5>
                                        <p class="mb-0">Try adjusting your filters or search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($applicants->hasPages())
                <div class="pagination-wrapper">
                    {{ $applicants->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        <!-- Drawer Markup -->
        <div id="drawerOverlay" class="drawer-overlay" aria-hidden="true"></div>
        <aside id="assignDrawer" class="drawer" role="dialog" aria-modal="true" aria-labelledby="drawerTitle">
            <div class="drawer-header">
                <div class="drawer-title" id="drawerTitle">Assign Instructor</div>
                <button type="button" id="closeAssignDrawer" aria-label="Close" class="btn-clear" style="height:auto;padding:6px 10px;">Close</button>
            </div>
            <div class="drawer-body">
                <div class="summary" style="margin-top:0;">
                    <div><span id="selCount">0</span></div>
                    <div class="summary-text">selected</div>
                </div>
                <div class="form-group">
                    <label for="instructor_id">Instructor *</label>
                    <select id="instructor_id" class="form-control" required>
                        <option value="">Select Instructor</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->user_id }}">
                                {{ $instructor->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="interview_start_date">Interview Start Date *</label>
                    <input type="date" id="interview_start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="interview_end_date">Interview End Date *</label>
                    <input type="date" id="interview_end_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="notify_email" checked>
                        Notify applicants by email
                    </label>
                </div>
                <div class="form-group">
                    <label for="assignment_message">Assignment Message (Optional)</label>
                    <textarea id="assignment_message" class="form-control" rows="3" placeholder="Add instructions or context for the instructor"></textarea>
                </div>
            </div>
            <div class="drawer-footer">
                <button class="btn-primary" id="assignBtn" disabled>Assign to Instructor</button>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const selected = new Set();
    const selCount = document.getElementById('selCount');
    const bulkCount = document.getElementById('bulkCount');
    const assignBtn = document.getElementById('assignBtn');
    const openDrawerBtn = document.getElementById('openAssignDrawer');
    const closeDrawerBtn = document.getElementById('closeAssignDrawer');
    const drawer = document.getElementById('assignDrawer');
    const overlay = document.getElementById('drawerOverlay');

    // Select All functionality
    document.getElementById('selectAll')?.addEventListener('change', function(e) {
        const isChecked = e.target.checked;
        document.querySelectorAll('.rowChk').forEach(checkbox => {
            checkbox.checked = isChecked;
            toggleSelection(checkbox.value, isChecked);
        });
        refreshUI();
    });

    // Individual checkbox functionality
    document.querySelectorAll('.rowChk').forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            toggleSelection(e.target.value, e.target.checked);
            refreshUI();
            
            // Update select all checkbox
            const allCheckboxes = document.querySelectorAll('.rowChk');
            const checkedCheckboxes = document.querySelectorAll('.rowChk:checked');
            document.getElementById('selectAll').checked = allCheckboxes.length === checkedCheckboxes.length;
        });
    });

    function toggleSelection(id, isSelected) {
        if (isSelected) {
            selected.add(id);
        } else {
            selected.delete(id);
        }
    }

    function refreshUI() {
        const count = selected.size;
        if (selCount) selCount.textContent = count;
        if (bulkCount) bulkCount.textContent = count;
        if (assignBtn) assignBtn.disabled = count === 0;
        if (openDrawerBtn) openDrawerBtn.disabled = count === 0;
    }

    // Drawer controls
    function openDrawer() {
        drawer?.classList.add('open');
        overlay?.classList.add('open');
        // focus first field when opening
        setTimeout(() => document.getElementById('instructor_id')?.focus(), 50);
    }

    function closeDrawer() {
        drawer?.classList.remove('open');
        overlay?.classList.remove('open');
    }

    openDrawerBtn?.addEventListener('click', openDrawer);
    closeDrawerBtn?.addEventListener('click', closeDrawer);
    overlay?.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDrawer();
    });

    // Assign button functionality
    assignBtn?.addEventListener('click', async function() {
        const instructorId = parseInt(document.getElementById('instructor_id').value, 10);
        const startDate = document.getElementById('interview_start_date').value;
        const endDate = document.getElementById('interview_end_date').value;
        
        if (!instructorId) {
            alert('Please select an instructor.');
            return;
        }

        if (!startDate || !endDate) {
            alert('Please select both interview start and end dates.');
            return;
        }

        if (new Date(endDate) < new Date(startDate)) {
            alert('Interview end date must be after or equal to start date.');
            return;
        }

        if (selected.size === 0) {
            alert('Please select at least one applicant.');
            return;
        }

        if (!confirm(`Assign ${selected.size} applicant(s) to the selected instructor?`)) {
            return;
        }

        assignBtn.disabled = true;
        assignBtn.textContent = 'Assigning...';

        const payload = {
            applicant_ids: Array.from(selected),
            instructor_id: instructorId,
            interview_start_date: startDate,
            interview_end_date: endDate,
            notify_email: document.getElementById('notify_email').checked,
            assignment_message: document.getElementById('assignment_message').value || null
        };

        try {
            const response = await fetch('{{ route("admin.applicants.bulk.assign-instructors") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Applicants assigned successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to assign applicants.');
                assignBtn.disabled = false;
                assignBtn.textContent = 'Assign to Instructor';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while assigning applicants.');
            assignBtn.disabled = false;
            assignBtn.textContent = 'Assign to Instructor';
        }
    });

    // Filter functionality
    let searchTimeout;
    
    function applyFilters() {
        const params = new URLSearchParams();
        
        const search = document.getElementById('search').value.trim();
        const status = document.getElementById('status').value;
        const assigned = document.getElementById('assigned').value;

        if (search) params.append('q', search);
        if (status) params.append('status', status);
        if (assigned) params.append('assigned', assigned);

        const url = params.toString() ? `{{ route('admin.applicants.assign') }}?${params.toString()}` : '{{ route('admin.applicants.assign') }}';
        window.location.href = url;
    }

    function clearFilters() {
        document.getElementById('search').value = '';
        document.getElementById('status').value = '';
        document.getElementById('assigned').value = '';
        window.location.href = '{{ route('admin.applicants.assign') }}';
    }

    // Auto-apply filters when dropdowns change
    document.getElementById('status')?.addEventListener('change', function() {
        applyFilters();
    });

    document.getElementById('assigned')?.addEventListener('change', function() {
        applyFilters();
    });

    // Debounced auto-apply for search input (500ms delay)
    document.getElementById('search')?.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 500);
    });

    // Enter key support for search (immediate apply)
    document.getElementById('search')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            clearTimeout(searchTimeout);
            applyFilters();
        }
    });

    // AJAX Pagination
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('.pagination a, .pagination-wrapper a');
        
        if (paginationLink && paginationLink.href) {
            e.preventDefault();
            e.stopPropagation();
            const url = paginationLink.href;
            
            if (!url || url === '#' || url === 'javascript:void(0)') return;
            
            const tableBody = document.querySelector('table tbody');
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
                if (data.applicants && tableBody) {
                    let html = '';
                    const from = data.pagination.from || 0;
                    
                    if (data.applicants.length === 0) {
                        html = '<tr><td colspan="6"><div class="empty-state"><h3>No applicants found</h3><p>Try adjusting your filters or search criteria.</p></div></td></tr>';
                    } else {
                        data.applicants.forEach((applicant, index) => {
                            const statusClass = (applicant.status || '').replace(/-/g, '');
                            const statusText = (applicant.status || '').split('-').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(' ');
                            const instructorName = applicant.assigned_instructor ? applicant.assigned_instructor.full_name : null;
                            
                            html += `<tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox" class="rowChk" value="${applicant.applicant_id}" data-name="${applicant.full_name || ''}">
                                </td>
                                <td>${applicant.application_no || applicant.formatted_applicant_no || 'N/A'}</td>
                                <td>${applicant.full_name || ''}</td>
                                <td>${applicant.email_address || ''}</td>
                                <td>
                                    <span class="status-badge status-${statusClass}">${statusText}</span>
                                </td>
                                <td>${instructorName ? instructorName : '<span style="color: #9ca3af;">Not Assigned</span>'}</td>
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

