@extends('layouts.admin')

@section('title', 'Interview Management')

@php
    $pageTitle = 'Interview Management';
    $pageSubtitle = 'Schedule and manage applicant interviews';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/interviews.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-maroon: #800020;
            --maroon-dark: #5C0016;
        }

        .interview-management-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }

        /* Header is handled by `x-admin-header` in layout. Remove local header styles. */

        .stats-section {
            margin-bottom: 32px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #E5E7EB;
            min-width: 0; /* Allow cards to shrink */
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        .stat-content {
            text-align: center;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #800020;
            margin-bottom: 6px;
            line-height: 1;
        }

        .stat-label {
            color: #6B7280;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }


        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #E5E7EB;
        }

        .content-header {
            padding: 24px;
            background: #F9FAFB;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1F2937;
            margin: 0;
        }

        .section-actions {
            display: flex;
            gap: 12px;
        }

        .btn-primary {
            background: #800020 !important;
            color: white !important;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: #5C0016 !important;
            color: white !important;
            transform: translateY(-1px);
        }

        .btn-primary:focus {
            background: #800020 !important;
            color: white !important;
            outline: 2px solid #FFD700;
            outline-offset: 2px;
        }

        .btn-outline {
            background: transparent;
            color: #800020;
            border: 1px solid #800020;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline:hover {
            background: #800020;
            color: white;
            transform: translateY(-1px);
        }

        .search-controls {
            padding: 24px;
            background: #F9FAFB;
            border-bottom: 1px solid #E5E7EB;
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 24px;
            align-items: end;
        }

        .search-input-group {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 48px 12px 16px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-maroon);
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
        }

        .search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-maroon);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-btn:hover {
            background: #5C0016;
        }

        .filter-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .filter-select {
            padding: 12px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 0.875rem;
            min-width: 150px;
            transition: border-color 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-maroon);
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
        }

        .btn-clear {
            background: #EF4444;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
        }

        .btn-clear:hover {
            background: #DC2626;
        }

        .table-container {
            padding: 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #F9FAFB;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #E5E7EB;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #E5E7EB;
            vertical-align: top;
        }

        .data-table tr:hover {
            background: #F9FAFB;
        }

        .applicant-info, .interviewer-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .applicant-name, .interviewer-name {
            font-weight: 600;
            color: #1F2937;
        }

        .applicant-email, .interviewer-role {
            font-size: 0.875rem;
            color: #6B7280;
        }

        .text-muted {
            color: #9CA3AF;
            font-style: italic;
        }

        .schedule-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .schedule-date {
            font-weight: 500;
            color: #1F2937;
        }

        .schedule-time {
            font-size: 0.875rem;
            color: #6B7280;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-available {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .status-claimed {
            background: #FEF3C7;
            color: #F59E0B;
        }

        .status-scheduled {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .status-completed {
            background: #DCFCE7;
            color: #059669;
        }

        .status-cancelled {
            background: #FEE2E2;
            color: #DC2626;
        }

        .ratings-summary {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .rating-item {
            font-size: 0.75rem;
            color: #6B7280;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-btn-edit {
            background: #F59E0B;
            color: white;
        }

        .action-btn-edit:hover {
            background: #D97706;
        }

        .action-btn-delete {
            background: #EF4444;
            color: white;
        }

        .action-btn-delete:hover {
            background: #DC2626;
        }

        .action-btn-view {
            background: #6B7280;
            color: white;
        }

        .action-btn-view:hover {
            background: #4B5563;
        }

        .action-btn-conduct {
            background: var(--primary-maroon);
            color: white;
        }

        .action-btn-conduct:hover {
            background: var(--maroon-dark);
        }

        .action-btn-claimed {
            background: #059669;
            color: white;
        }

        .action-btn-claimed:hover {
            background: #047857;
        }

        .empty-state {
            text-align: center;
            padding: 64px 24px;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
            margin: 0 0 8px 0;
        }

        .empty-state p {
            color: #6B7280;
            margin: 0 0 24px 0;
        }

        .pagination-container {
            padding: 24px;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
        }

        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .filter-group {
                flex-wrap: wrap;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }
    </style>
@endpush

@section('content')
<div class="interview-management-container">
    <!-- Header is provided by the admin layout via `$pageTitle`/`$pageSubtitle`. -->

    <!-- Statistics Section -->
    <div class="stats-section">
        <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-value">{{ $stats['total'] }}</div>
                            <div class="stat-label">Total Interviews</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-value">{{ $stats['scheduled'] }}</div>
                            <div class="stat-label">Scheduled</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-value">{{ $stats['completed'] }}</div>
                            <div class="stat-label">Completed</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-value">{{ $stats['pending_assignment'] }}</div>
                            <div class="stat-label">Pending Assignment</div>
                        </div>
                    </div>
        </div>
    </div>

    <!-- Main Content Section -->
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
        <div class="search-controls">
            <form method="GET" action="{{ route('admin.interviews.index') }}" class="search-form">
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
            </form>
        </div>

        <!-- Interviews Table -->
        <div class="table-container">
                        @if($interviews->count() > 0)
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Interviewer</th>
                                        <th>Schedule Date</th>
                                        <th>Status</th>
                                        <th>Ratings</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($interviews as $interview)
                                        <tr>
                                            <td>
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
                                            <td>
                                                <span class="status-badge status-{{ $interview->status }}">
                                                    {{ ucfirst($interview->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($interview->status === 'completed')
                                                    <div class="ratings-summary">
                                                        <div class="rating-item">Tech: {{ $interview->rating_technical ?? 'N/A' }}</div>
                                                        <div class="rating-item">Comm: {{ $interview->rating_communication ?? 'N/A' }}</div>
                                                        <div class="rating-item">PS: {{ $interview->rating_problem_solving ?? 'N/A' }}</div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    @if(in_array($interview->status, ['scheduled', 'available', 'claimed']) && $interview->status !== 'completed')
                                                        @if($interview->claimed_by === auth()->id())
                                                            <a href="{{ route('admin.interviews.conduct', $interview->interview_id) }}" 
                                                               class="action-btn action-btn-claimed" title="Continue Interview">
                                                                Continue
                                                            </a>
                                                        @elseif(!$interview->claimed_by || $interview->isClaimedTooLong(1))
                                                            <a href="{{ route('admin.interviews.conduct', $interview->interview_id) }}" 
                                                               class="action-btn action-btn-conduct" title="Conduct Interview">
                                                                I'll Conduct This
                                                            </a>
                                                        @else
                                                            <span class="action-btn action-btn-view" title="Interview claimed by {{ \App\Models\User::find($interview->claimed_by)->full_name ?? 'Another User' }}">
                                                                Claimed
                                                            </span>
                                                        @endif
                                                    @endif
                                                    
                                                    <button onclick="editInterview({{ $interview->interview_id }})" 
                                                            class="action-btn action-btn-edit" title="Edit Schedule">
                                                        Edit
                                                    </button>
                                                    @if($interview->status === 'scheduled')
                                                        <button onclick="cancelInterview({{ $interview->interview_id }})" 
                                                                class="action-btn action-btn-delete" title="Cancel Interview">
                                                            Cancel
                                                        </button>
                                                    @endif
                                                    <button onclick="viewDetails({{ $interview->interview_id }})" 
                                                            class="action-btn action-btn-view" title="View Details">
                                                        View
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

            <!-- Pagination -->
            <div class="pagination-container">
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
                    
                    <div class="form-row">
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

    function viewDetails(interviewId) {
        // Implementation for viewing interview details
        alert('View details functionality - to be implemented');
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeExportModal();
        }
    });
</script>
@endpush