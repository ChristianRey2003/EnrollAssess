<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Interview Schedule - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Admin Dashboard CSS -->
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
</head>
<body class="admin-page instructor-portal">
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="admin-sidebar instructor-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo">
                    <div>
                        <h2 class="sidebar-title">EnrollAssess</h2>
                        <p class="sidebar-subtitle">Instructor Portal</p>
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('instructor.dashboard') }}" class="nav-link">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('instructor.applicants') }}" class="nav-link">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">My Applicants</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('instructor.schedule') }}" class="nav-link active">
                        <span class="nav-icon">📅</span>
                        <span class="nav-text">Schedule</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('instructor.interview-history') }}" class="nav-link">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Interview History</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('instructor.guidelines') }}" class="nav-link">
                        <span class="nav-icon">📋</span>
                        <span class="nav-text">Guidelines</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="instructor-info">
                    <div class="instructor-avatar">{{ substr(Auth::user()->full_name, 0, 2) }}</div>
                    <div>
                        <div class="instructor-name">{{ Auth::user()->full_name }}</div>
                        <div class="instructor-role">Instructor</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="logout-link">
                        <span class="nav-icon">🚪</span>
                        <span class="nav-text">Logout</span>
                    </button>
                </form>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <div class="main-header instructor-header">
                <div class="header-left">
                    <h1>Interview Schedule</h1>
                    <p class="header-subtitle">Manage your interview appointments and schedule</p>
                </div>
                <div class="header-right">
                    <div class="header-stats">
                        <div class="stat-item">
                            <span class="stat-value">{{ $upcomingInterviews->count() }}</span>
                            <span class="stat-label">Upcoming</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ $pendingScheduling->count() }}</span>
                            <span class="stat-label">Pending Schedule</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Upcoming Interviews -->
                @if($upcomingInterviews->count() > 0)
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">📅 Upcoming Interviews</h2>
                    </div>
                    <div class="section-content">
                        <div class="schedule-timeline">
                            @foreach($upcomingInterviews as $interview)
                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <div class="date-day">{{ $interview->schedule_date->format('d') }}</div>
                                    <div class="date-month">{{ $interview->schedule_date->format('M') }}</div>
                                    <div class="date-time">{{ $interview->schedule_date->format('g:i A') }}</div>
                                </div>
                                <div class="timeline-content">
                                    <div class="interview-card">
                                        <div class="interview-header">
                                            <div class="applicant-info">
                                                <div class="applicant-avatar">{{ substr($interview->applicant->full_name, 0, 2) }}</div>
                                                <div>
                                                    <h4>{{ $interview->applicant->full_name }}</h4>
                                                    <p>{{ $interview->applicant->application_no }}</p>
                                                </div>
                                            </div>
                                            <div class="interview-status">
                                                <span class="status-badge status-scheduled">Scheduled</span>
                                            </div>
                                        </div>
                                        <div class="interview-details">
                                            <div class="detail-item">
                                                <span class="detail-label">Email:</span>
                                                <span class="detail-value">{{ $interview->applicant->email_address }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Exam Score:</span>
                                                <span class="detail-value">
                                                    @if($interview->applicant->score)
                                                        {{ number_format($interview->applicant->score, 1) }}%
                                                    @else
                                                        Pending
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="interview-actions">
                                            <a href="{{ route('instructor.interview.show', $interview->applicant->applicant_id) }}" 
                                               class="btn-sm btn-primary">Start Interview</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Pending Scheduling -->
                @if($pendingScheduling->count() > 0)
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">⏳ Pending Scheduling</h2>
                        <p class="section-subtitle">These interviews need to be scheduled</p>
                    </div>
                    <div class="section-content">
                        <div class="pending-grid">
                            @foreach($pendingScheduling as $interview)
                            <div class="pending-card">
                                <div class="pending-header">
                                    <div class="applicant-info">
                                        <div class="applicant-avatar">{{ substr($interview->applicant->full_name, 0, 2) }}</div>
                                        <div>
                                            <h4>{{ $interview->applicant->full_name }}</h4>
                                            <p>{{ $interview->applicant->application_no }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="pending-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Email:</span>
                                        <span class="detail-value">{{ $interview->applicant->email_address }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Assigned:</span>
                                        <span class="detail-value">{{ $interview->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Exam Score:</span>
                                        <span class="detail-value">
                                            @if($interview->applicant->score)
                                                {{ number_format($interview->applicant->score, 1) }}%
                                            @else
                                                Pending
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="pending-actions">
                                    <button class="btn-sm btn-outline" onclick="scheduleInterview({{ $interview->interview_id }})">
                                        📅 Schedule Interview
                                    </button>
                                    <a href="{{ route('instructor.interview.show', $interview->applicant->applicant_id) }}" 
                                       class="btn-sm btn-primary">Start Now</a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Empty State -->
                @if($upcomingInterviews->count() == 0 && $pendingScheduling->count() == 0)
                <div class="content-section">
                    <div class="section-content">
                        <div class="empty-state">
                            <div class="empty-icon">📅</div>
                            <h3>No Interviews Scheduled</h3>
                            <p>You don't have any upcoming interviews. Check back later or contact the administrator.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Schedule Modal -->
    <div id="scheduleModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Schedule Interview</h3>
                <button class="modal-close" onclick="closeScheduleModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <div class="form-group">
                        <label for="scheduleDate">Date</label>
                        <input type="date" id="scheduleDate" name="schedule_date" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="scheduleTime">Time</label>
                        <input type="time" id="scheduleTime" name="schedule_time" required>
                    </div>
                    <div class="form-group">
                        <label for="scheduleNotes">Notes (Optional)</label>
                        <textarea id="scheduleNotes" name="notes" rows="3" placeholder="Any special instructions or preparation notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeScheduleModal()">Cancel</button>
                <button class="btn-primary" onclick="confirmSchedule()">Schedule Interview</button>
            </div>
        </div>
    </div>

    <style>
        /* Schedule-specific styles */
        .instructor-portal {
            --primary-color: #2563eb;
        }

        .instructor-sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #1e40af 100%);
        }

        .instructor-header {
            border-bottom: 3px solid var(--primary-color);
        }

        .header-stats {
            display: flex;
            gap: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-gray);
        }

        .schedule-timeline {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .timeline-item {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .timeline-date {
            min-width: 80px;
            text-align: center;
            background: var(--primary-color);
            color: white;
            padding: 15px 10px;
            border-radius: 12px;
        }

        .date-day {
            font-size: 24px;
            font-weight: bold;
        }

        .date-month {
            font-size: 12px;
            margin: 5px 0;
        }

        .date-time {
            font-size: 11px;
            opacity: 0.9;
        }

        .timeline-content {
            flex: 1;
        }

        .interview-card, .pending-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
        }

        .interview-header, .pending-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .applicant-info {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .applicant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .applicant-info h4 {
            margin: 0;
            font-size: 16px;
        }

        .applicant-info p {
            margin: 2px 0 0 0;
            font-size: 12px;
            color: var(--text-gray);
        }

        .interview-details, .pending-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .detail-label {
            font-size: 12px;
            color: var(--text-gray);
            font-weight: 500;
        }

        .detail-value {
            font-size: 14px;
            color: var(--text-dark);
        }

        .interview-actions, .pending-actions {
            display: flex;
            gap: 10px;
        }

        .pending-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .btn-sm {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-outline {
            background: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-scheduled {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Modal styles */
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
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .instructor-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .instructor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--white);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .instructor-name {
            color: var(--white);
            font-weight: 600;
            font-size: 14px;
        }

        .instructor-role {
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .timeline-item {
                flex-direction: column;
            }

            .pending-grid {
                grid-template-columns: 1fr;
            }

            .interview-details, .pending-details {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        let currentInterviewId = null;

        function scheduleInterview(interviewId) {
            currentInterviewId = interviewId;
            document.getElementById('scheduleModal').style.display = 'flex';
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').style.display = 'none';
            document.getElementById('scheduleForm').reset();
            currentInterviewId = null;
        }

        async function confirmSchedule() {
            const date = document.getElementById('scheduleDate').value;
            const time = document.getElementById('scheduleTime').value;
            const notes = document.getElementById('scheduleNotes').value;

            if (!date || !time) {
                alert('Please select both date and time.');
                return;
            }

            const scheduleDateTime = `${date} ${time}:00`;

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`{{ url('/instructor/schedule') }}/${currentInterviewId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        schedule_date: scheduleDateTime,
                        notes: notes
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Interview scheduled successfully.');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to schedule interview.');
                }
            } catch (e) {
                alert('An error occurred. Please try again.');
            } finally {
                closeScheduleModal();
            }
        }

        // Close modal when clicking outside
        document.getElementById('scheduleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeScheduleModal();
            }
        });
    </script>
</body>
</html>