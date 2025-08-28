<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Interview Management - EnrollAssess Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-content">
                <div class="header-left">
                    <h1 class="page-title">📅 Interview Management</h1>
                    <p class="page-subtitle">Schedule and manage applicant interviews</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('admin.dashboard') }}" class="btn-secondary">← Back to Dashboard</a>
                    <button onclick="showBulkScheduleModal()" class="btn-primary">
                        📋 Bulk Schedule
                    </button>
                </div>
            </div>
        </header>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Interviews</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏰</div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['scheduled'] }}</div>
                    <div class="stat-label">Scheduled</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['completed'] }}</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['pending_assignment'] }}</div>
                    <div class="stat-label">Pending Assignment</div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">Interview Schedule</h2>
                <div class="section-actions">
                    <a href="{{ route('admin.interviews.analytics') }}" class="btn-outline">
                        📈 Analytics
                    </a>
                    <button onclick="showExportModal()" class="btn-outline">
                        📊 Export
                    </button>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="search-controls">
                <form method="GET" action="{{ route('admin.interviews.index') }}" class="search-form">
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Search applicants or interviewers..." 
                               value="{{ request('search') }}" class="search-input">
                        <button type="submit" class="search-btn">🔍</button>
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
                                            <div class="applicant-name">{{ $interview->applicant->full_name }}</div>
                                            <div class="applicant-email">{{ $interview->applicant->email_address }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="interviewer-info">
                                            <div class="interviewer-name">{{ $interview->interviewer->full_name }}</div>
                                            <div class="interviewer-role">{{ ucfirst($interview->interviewer->role) }}</div>
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
                                            <button onclick="editInterview({{ $interview->interview_id }})" 
                                                    class="btn-sm btn-primary" title="Edit Schedule">
                                                ✏️
                                            </button>
                                            @if($interview->status === 'scheduled')
                                                <button onclick="cancelInterview({{ $interview->interview_id }})" 
                                                        class="btn-sm btn-danger" title="Cancel Interview">
                                                    ❌
                                                </button>
                                            @endif
                                            <button onclick="viewDetails({{ $interview->interview_id }})" 
                                                    class="btn-sm btn-outline" title="View Details">
                                                👁️
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
                        <div class="empty-icon">📅</div>
                        <h3>No Interviews Found</h3>
                        <p>No interviews match your current search criteria.</p>
                        <button onclick="showBulkScheduleModal()" class="btn-primary">
                            Schedule First Interview
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bulk Schedule Modal -->
    <div id="bulkScheduleModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📋 Bulk Schedule Interviews</h3>
                <button onclick="closeBulkScheduleModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <form id="bulkScheduleForm">
                    <div class="form-group">
                        <label class="form-label">Select Applicants (Exam Completed)</label>
                        <div class="applicant-list" id="applicantList">
                            <!-- Will be populated via AJAX -->
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Assignment Strategy</label>
                            <select id="assignment_strategy" name="assignment_strategy" class="form-control" required>
                                <option value="balanced">Balanced Distribution</option>
                                <option value="specific">Specific Instructor</option>
                                <option value="random">Random Assignment</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="specific_interviewer_group" style="display: none;">
                            <label class="form-label">Select Instructor</label>
                            <select id="interviewer_id" name="interviewer_id" class="form-control">
                                <option value="">Choose Instructor</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->user_id }}">{{ $instructor->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input type="date" id="schedule_date_start" name="schedule_date_start" 
                                   class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Interview Duration (minutes)</label>
                            <select id="interview_duration" name="interview_duration" class="form-control">
                                <option value="30">30 minutes</option>
                                <option value="45" selected>45 minutes</option>
                                <option value="60">1 hour</option>
                                <option value="90">1.5 hours</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Available Time Slots</label>
                        <div class="time-slots-grid">
                            <label class="time-slot">
                                <input type="checkbox" name="time_slots[]" value="09:00" checked>
                                <span>9:00 AM</span>
                            </label>
                            <label class="time-slot">
                                <input type="checkbox" name="time_slots[]" value="10:00" checked>
                                <span>10:00 AM</span>
                            </label>
                            <label class="time-slot">
                                <input type="checkbox" name="time_slots[]" value="11:00" checked>
                                <span>11:00 AM</span>
                            </label>
                            <label class="time-slot">
                                <input type="checkbox" name="time_slots[]" value="14:00" checked>
                                <span>2:00 PM</span>
                            </label>
                            <label class="time-slot">
                                <input type="checkbox" name="time_slots[]" value="15:00" checked>
                                <span>3:00 PM</span>
                            </label>
                            <label class="time-slot">
                                <input type="checkbox" name="time_slots[]" value="16:00" checked>
                                <span>4:00 PM</span>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="closeBulkScheduleModal()" class="btn-secondary">Cancel</button>
                <button onclick="confirmBulkSchedule()" class="btn-primary">Schedule Interviews</button>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div id="exportModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📊 Export Interviews</h3>
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

    <script>
        // Load eligible applicants when modal opens
        function showBulkScheduleModal() {
            document.getElementById('bulkScheduleModal').style.display = 'flex';
            loadEligibleApplicants();
        }

        function closeBulkScheduleModal() {
            document.getElementById('bulkScheduleModal').style.display = 'none';
        }

        function showExportModal() {
            document.getElementById('exportModal').style.display = 'flex';
        }

        function closeExportModal() {
            document.getElementById('exportModal').style.display = 'none';
        }

        function loadEligibleApplicants() {
            fetch('/admin/applicants/api/eligible-for-interview')
                .then(response => response.json())
                .then(data => {
                    const listContainer = document.getElementById('applicantList');
                    if (data.applicants.length === 0) {
                        listContainer.innerHTML = '<p class="text-muted">No eligible applicants found.</p>';
                        return;
                    }
                    
                    listContainer.innerHTML = data.applicants.map(applicant => `
                        <label class="applicant-item">
                            <input type="checkbox" name="applicant_ids[]" value="${applicant.applicant_id}" checked>
                            <div class="applicant-details">
                                <div class="applicant-name">${applicant.full_name}</div>
                                <div class="applicant-email">${applicant.email_address}</div>
                                <div class="applicant-exam">${applicant.exam_set?.exam?.title || 'N/A'} - ${applicant.exam_set?.set_name || 'N/A'}</div>
                            </div>
                        </label>
                    `).join('');
                })
                .catch(error => {
                    console.error('Error loading applicants:', error);
                });
        }

        // Show/hide specific interviewer based on assignment strategy
        document.getElementById('assignment_strategy').addEventListener('change', function() {
            const specificGroup = document.getElementById('specific_interviewer_group');
            specificGroup.style.display = this.value === 'specific' ? 'block' : 'none';
        });

        function confirmBulkSchedule() {
            const form = document.getElementById('bulkScheduleForm');
            const formData = new FormData(form);
            
            // Get selected applicants
            const selectedApplicants = Array.from(document.querySelectorAll('input[name="applicant_ids[]"]:checked'))
                .map(input => input.value);
            
            if (selectedApplicants.length === 0) {
                alert('Please select at least one applicant.');
                return;
            }
            
            // Get selected time slots
            const timeSlots = Array.from(document.querySelectorAll('input[name="time_slots[]"]:checked'))
                .map(input => input.value);
            
            if (timeSlots.length === 0) {
                alert('Please select at least one time slot.');
                return;
            }
            
            const requestData = {
                applicant_ids: selectedApplicants,
                assignment_strategy: formData.get('assignment_strategy'),
                interviewer_id: formData.get('interviewer_id'),
                schedule_date_start: formData.get('schedule_date_start'),
                interview_duration: formData.get('interview_duration'),
                time_slots: timeSlots
            };
            
            fetch('/admin/interviews/bulk-schedule', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred while scheduling interviews');
                console.error('Error:', error);
            });
            
            closeBulkScheduleModal();
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
                closeBulkScheduleModal();
                closeExportModal();
            }
        });
    </script>

    <style>
        /* CSS Variables */
        :root {
            --white: #FFFFFF;
            --maroon-primary: #8B0000;
            --maroon-dark: #6B0000;
            --yellow-primary: #FFD700;
            --border-gray: #E5E7EB;
            --light-gray: #F9FAFB;
            --text-dark: #1F2937;
            --text-gray: #6B7280;
            --transition: all 0.3s ease;
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .admin-container {
            min-height: 100vh;
            padding: 20px;
        }

        /* Header */
        .admin-header {
            background: var(--white);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--maroon-primary);
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: var(--text-gray);
            font-size: 16px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 24px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--maroon-primary), var(--maroon-dark));
            color: var(--white);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--maroon-primary);
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-gray);
            font-weight: 500;
        }

        /* Content Section */
        .content-section {
            background: var(--white);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-gray);
        }

        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--maroon-primary);
        }

        .section-actions {
            display: flex;
            gap: 10px;
        }

        /* Search Controls */
        .search-controls {
            margin-bottom: 25px;
        }

        .search-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .search-input-group {
            display: flex;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid var(--border-gray);
            border-radius: 8px 0 0 8px;
            font-size: 14px;
        }

        .search-btn {
            padding: 12px 20px;
            background: var(--maroon-primary);
            color: var(--white);
            border: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
        }

        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 10px 15px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            min-width: 150px;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-gray);
        }

        .data-table th {
            background: var(--light-gray);
            font-weight: 600;
            color: var(--text-dark);
        }

        .applicant-info, .interviewer-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .applicant-name, .interviewer-name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .applicant-email, .interviewer-role {
            font-size: 12px;
            color: var(--text-gray);
        }

        .schedule-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .schedule-date {
            font-weight: 500;
            color: var(--text-dark);
        }

        .schedule-time {
            font-size: 12px;
            color: var(--text-gray);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-scheduled {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .status-completed {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-cancelled {
            background: #FEE2E2;
            color: #991B1B;
        }

        .ratings-summary {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .rating-item {
            font-size: 12px;
            color: var(--text-gray);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: var(--transition);
        }

        .btn-sm.btn-primary {
            background: var(--maroon-primary);
            color: var(--white);
        }

        .btn-sm.btn-danger {
            background: var(--danger-color);
            color: var(--white);
        }

        .btn-sm.btn-outline {
            background: transparent;
            border: 1px solid var(--border-gray);
            color: var(--text-gray);
        }

        /* Buttons */
        .btn-primary, .btn-secondary, .btn-outline, .btn-clear {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--maroon-primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--maroon-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--text-gray);
            color: var(--white);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--border-gray);
            color: var(--text-dark);
        }

        .btn-clear {
            background: var(--danger-color);
            color: var(--white);
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: var(--white);
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            padding: 25px 30px 20px;
            border-bottom: 1px solid var(--border-gray);
            position: relative;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--maroon-primary);
            font-size: 20px;
            font-weight: 600;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 25px;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-gray);
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }

        .modal-close:hover {
            background: var(--light-gray);
            color: var(--maroon-primary);
        }

        .modal-body {
            padding: 25px 30px;
        }

        .modal-footer {
            padding: 20px 30px 25px;
            border-top: 1px solid var(--border-gray);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        /* Applicant List */
        .applicant-list {
            max-height: 300px;
            overflow-y: auto;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            padding: 15px;
        }

        .applicant-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 10px;
        }

        .applicant-item:hover {
            background: var(--light-gray);
        }

        .applicant-details {
            flex: 1;
        }

        .applicant-details .applicant-name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .applicant-details .applicant-email {
            font-size: 12px;
            color: var(--text-gray);
        }

        .applicant-details .applicant-exam {
            font-size: 11px;
            color: var(--maroon-primary);
            font-weight: 500;
        }

        /* Time Slots */
        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
        }

        .time-slot {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
        }

        .time-slot:hover {
            border-color: var(--maroon-primary);
        }

        .time-slot input[type="checkbox"]:checked + span {
            color: var(--maroon-primary);
            font-weight: 500;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--maroon-primary);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--text-gray);
            margin-bottom: 25px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .header-actions {
                justify-content: center;
            }

            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .time-slots-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .text-muted {
            color: var(--text-gray);
            font-size: 14px;
        }

        .pagination-container {
            margin-top: 25px;
            display: flex;
            justify-content: center;
        }
    </style>
</body>
</html>