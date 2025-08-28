<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Instructor Dashboard - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Admin Dashboard CSS -->
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
    <!-- Component CSS -->
    <link href="{{ asset('css/components/status-badges.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components/buttons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components/modals.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components/forms.css') }}" rel="stylesheet">
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
                    <a href="{{ route('instructor.dashboard') }}" class="nav-link active">
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
                    <a href="{{ route('instructor.schedule') }}" class="nav-link">
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
                    <div class="instructor-avatar">{{ $instructor->initials ?? substr($instructor->full_name, 0, 2) }}</div>
                    <div>
                        <div class="instructor-name">{{ $instructor->full_name }}</div>
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
                    <h1>Welcome back, {{ explode(' ', $instructor->full_name)[0] }}!</h1>
                    <p class="header-subtitle">Manage your assigned applicant interviews and evaluations</p>
                </div>
                <div class="header-right">
                    <div class="header-time">
                        🕐 {{ now()->format('M d, Y g:i A') }}
                    </div>
                    <div class="header-user">
                        {{ $instructor->full_name }}
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Quick Stats -->
                <div class="stats-grid instructor-stats">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value">{{ $stats['total_assigned'] }}</div>
                        <div class="stat-label">Total Assigned</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value">{{ $stats['pending_interviews'] }}</div>
                        <div class="stat-label">Pending Interviews</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-value">{{ $stats['completed_interviews'] }}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🌟</div>
                        <div class="stat-value">{{ $stats['recommended'] }}</div>
                        <div class="stat-label">Recommended</div>
                    </div>
                </div>

                <!-- Main Dashboard Content -->
                <div class="dashboard-content">
                    <!-- Quick Actions -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">Quick Actions</h2>
                        </div>
                        <div class="section-content">
                            <div class="quick-actions-grid">
                                <a href="{{ route('instructor.applicants') }}" class="quick-action-card">
                                    <div class="action-icon">👥</div>
                                    <div class="action-content">
                                        <h3>View My Applicants</h3>
                                        <p>See all applicants assigned for interviews</p>
                                    </div>
                                    <div class="action-arrow">→</div>
                                </a>
                                
                                @if($stats['pending_interviews'] > 0)
                                <a href="{{ route('instructor.applicants') }}?filter=pending" class="quick-action-card urgent">
                                    <div class="action-icon">⏰</div>
                                    <div class="action-content">
                                        <h3>Pending Interviews</h3>
                                        <p>{{ $stats['pending_interviews'] }} interviews awaiting completion</p>
                                    </div>
                                    <div class="action-arrow">→</div>
                                </a>
                                @endif

                                <div class="quick-action-card info">
                                    <div class="action-icon">📊</div>
                                    <div class="action-content">
                                        <h3>Interview Guidelines</h3>
                                        <p>Review evaluation criteria and best practices</p>
                                    </div>
                                    <div class="action-arrow">→</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Applicants Overview -->
                    @if($assignedApplicants->count() > 0)
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">Recent Assigned Applicants</h2>
                            <a href="{{ route('instructor.applicants') }}" class="btn-primary">View All</a>
                        </div>
                        <div class="section-content">
                            <div class="applicants-table-wrapper">
                                <table class="applicants-table instructor-table">
                                    <thead>
                                        <tr>
                                            <th>Applicant</th>
                                            <th>Application No.</th>
                                            <th>Exam Score</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignedApplicants->take(5) as $applicant)
                                        <tr>
                                            <td>
                                                <div class="applicant-info">
                                                    <div class="applicant-avatar">{{ $applicant->initials }}</div>
                                                    <div>
                                                        <div class="applicant-name">{{ $applicant->full_name }}</div>
                                                        <div class="applicant-email">{{ $applicant->email_address }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="application-no">{{ $applicant->application_no }}</td>
                                            <td>
                                                @if($applicant->score)
                                                    <span class="score-badge {{ $applicant->score >= 70 ? 'good' : 'needs-improvement' }}">
                                                        {{ number_format($applicant->score, 1) }}%
                                                    </span>
                                                @else
                                                    <span class="score-badge pending">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="status-badge status-{{ str_replace(' ', '-', strtolower($applicant->status)) }}">
                                                    {{ ucfirst(str_replace('-', ' ', $applicant->status)) }}
                                                </span>
                                            </td>
                                            <td class="actions">
                                                @if($applicant->status === 'exam-completed')
                                                    <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                                       class="btn-sm btn-primary">
                                                        Start Interview
                                                    </a>
                                                @elseif($applicant->status === 'interview-completed')
                                                    <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                                       class="btn-sm btn-secondary">
                                                        View Interview
                                                    </a>
                                                @else
                                                    <span class="text-muted">Waiting for exam</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- No Applicants Assigned -->
                    <div class="content-section">
                        <div class="section-content">
                            <div class="empty-state">
                                <div class="empty-icon">👥</div>
                                <h3>No Applicants Assigned Yet</h3>
                                <p>You don't have any applicants assigned for interviews yet. Check back later or contact the administrator.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Recent Activity -->
                    @if($recentInterviews->count() > 0)
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">Recent Activity</h2>
                        </div>
                        <div class="section-content">
                            <div class="activity-list">
                                @foreach($recentInterviews as $interview)
                                <div class="activity-item">
                                    <div class="activity-icon">📝</div>
                                    <div class="activity-content">
                                        <div class="activity-text">
                                            <strong>Interview completed</strong> for {{ $interview->applicant->full_name }}
                                        </div>
                                        <div class="activity-time">{{ $interview->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <style>
        /* Instructor-specific styling */
        .instructor-portal {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --accent-color: #3b82f6;
        }

        .instructor-sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #1e40af 100%);
        }

        .instructor-header {
            border-bottom: 3px solid var(--primary-color);
        }

        .instructor-stats .stat-card {
            border-left: 4px solid var(--primary-color);
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

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .quick-action-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: var(--white);
            border: 2px solid var(--border-gray);
            border-radius: 12px;
            text-decoration: none;
            transition: var(--transition);
            position: relative;
        }

        .quick-action-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .quick-action-card.urgent {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        }

        .quick-action-card.info {
            border-color: #10b981;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        }

        .action-icon {
            font-size: 24px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gray);
            border-radius: 12px;
        }

        .action-content h3 {
            margin: 0 0 5px 0;
            color: var(--maroon-primary);
            font-size: 16px;
            font-weight: 600;
        }

        .action-content p {
            margin: 0;
            color: var(--text-gray);
            font-size: 14px;
        }

        .action-arrow {
            font-size: 20px;
            color: var(--primary-color);
            margin-left: auto;
        }

        .instructor-table {
            width: 100%;
            border-collapse: collapse;
        }

        .instructor-table th,
        .instructor-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-gray);
        }

        .instructor-table th {
            background: var(--light-gray);
            font-weight: 600;
            color: var(--maroon-primary);
        }

        .applicant-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .applicant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .applicant-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .applicant-email {
            font-size: 12px;
            color: var(--text-gray);
        }

        .score-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .score-badge.good { background: #dcfce7; color: #166534; }
        .score-badge.needs-improvement { background: #fef3c7; color: #92400e; }
        .score-badge.pending { background: #f3f4f6; color: #6b7280; }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.status-exam-completed { background: #dbeafe; color: #1e40af; }
        .status-badge.status-interview-completed { background: #dcfce7; color: #166534; }
        .status-badge.status-pending { background: #f3f4f6; color: #6b7280; }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
        }

        .btn-sm.btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-sm.btn-secondary {
            background: var(--text-gray);
            color: var(--white);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            margin: 0 0 10px 0;
            color: var(--maroon-primary);
            font-size: 24px;
        }

        .empty-state p {
            color: var(--text-gray);
            max-width: 400px;
            margin: 0 auto;
        }

        .activity-list {
            space-y: 15px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: var(--light-gray);
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .activity-icon {
            font-size: 20px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border-radius: 8px;
        }

        .activity-text {
            color: var(--text-dark);
            font-size: 14px;
        }

        .activity-time {
            color: var(--text-gray);
            font-size: 12px;
            margin-top: 2px;
        }

        .nav-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 10px;
            margin-left: auto;
        }

        .nav-link.disabled {
            opacity: 0.6;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .quick-actions-grid {
                grid-template-columns: 1fr;
            }
            
            .instructor-info {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>

    <!-- Enhanced JavaScript -->
    <script src="{{ asset('js/utils/modal-manager.js') }}" defer></script>
    <script src="{{ asset('js/utils/form-validator.js') }}" defer></script>
    <script src="{{ asset('js/utils/mobile-menu.js') }}" defer></script>
    <script src="{{ asset('js/notifications.js') }}" defer></script>
</body>
</html>