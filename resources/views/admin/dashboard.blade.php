<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Dashboard - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Admin Dashboard CSS -->
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
</head>
<body class="admin-page">
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo">
                    <div>
                        <h2 class="sidebar-title">EnrollAssess</h2>
                        <p class="sidebar-subtitle">Admin Portal</p>
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link active">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.exams.index') }}" class="nav-link">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Exams</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.questions') }}" class="nav-link">
                        <span class="nav-icon">❓</span>
                        <span class="nav-text">Questions</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.applicants') }}" class="nav-link">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">Applicants</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.reports') }}" class="nav-link">
                        <span class="nav-icon">📈</span>
                        <span class="nav-text">Reports</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-footer">
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
            <div class="main-header">
                <div class="header-left">
                    <h1>Dashboard</h1>
                    <p class="header-subtitle">Computer Studies Department</p>
                </div>
                <div class="header-right">
                    <div class="header-time">
                        🕐 {{ now()->format('M d, Y g:i A') }}
                    </div>
                    <div class="header-user">
                        Welcome, {{ auth()->user()->name ?? 'Dr. Admin' }}
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value">{{ $totalApplicants ?? 145 }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-value">{{ $examsCompleted ?? 89 }}</div>
                        <div class="stat-label">Exams Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value">{{ $pendingInterviews ?? 23 }}</div>
                        <div class="stat-label">Pending Interviews</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-value">{{ $passRate ?? 78 }}%</div>
                        <div class="stat-label">Pass Rate</div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Applicant Activity</h2>
                        <a href="{{ route('admin.applicants') }}" class="section-action">
                            <span class="section-action-icon">👁️</span>
                            View All
                        </a>
                    </div>
                    <div class="section-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Applicant Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentApplicants ?? [] as $applicant)
                                <tr>
                                    <td>{{ $applicant->name }}</td>
                                    <td>{{ $applicant->email }}</td>
                                    <td>
                                        <span class="status-badge status-{{ strtolower($applicant->status) }}">
                                            {{ $applicant->status }}
                                        </span>
                                    </td>
                                    <td>{{ $applicant->score ? $applicant->score . '%' : '--' }}</td>
                                    <td>{{ $applicant->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('admin.applicants.show', $applicant->id) }}" class="action-btn action-btn-edit">
                                                👁️ View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <!-- Demo data when no applicants exist -->
                                <tr>
                                    <td>John Doe</td>
                                    <td>john.doe@email.com</td>
                                    <td><span class="status-badge status-completed">Completed</span></td>
                                    <td>85%</td>
                                    <td>{{ now()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-edit">👁️ View</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jane Smith</td>
                                    <td>jane.smith@email.com</td>
                                    <td><span class="status-badge status-in-progress">In Progress</span></td>
                                    <td>--</td>
                                    <td>{{ now()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-edit">👁️ View</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Mike Johnson</td>
                                    <td>mike.j@email.com</td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                    <td>--</td>
                                    <td>{{ now()->subDay()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-edit">👁️ View</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Sarah Williams</td>
                                    <td>sarah.w@email.com</td>
                                    <td><span class="status-badge status-completed">Completed</span></td>
                                    <td>92%</td>
                                    <td>{{ now()->subDay()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-edit">👁️ View</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>David Brown</td>
                                    <td>david.brown@email.com</td>
                                    <td><span class="status-badge status-in-progress">In Progress</span></td>
                                    <td>--</td>
                                    <td>{{ now()->subDays(2)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-edit">👁️ View</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Quick Actions</h2>
                    </div>
                    <div class="section-content" style="padding: 24px 30px;">
                        <div class="quick-actions-grid">
                            <a href="{{ route('admin.questions.create') }}" class="quick-action-card">
                                <div class="quick-action-icon">➕</div>
                                <div class="quick-action-title">Add New Question</div>
                                <div class="quick-action-desc">Create a new exam question</div>
                            </a>
                            <a href="{{ route('admin.applicants') }}" class="quick-action-card">
                                <div class="quick-action-icon">📊</div>
                                <div class="quick-action-title">Generate Report</div>
                                <div class="quick-action-desc">Export applicant data</div>
                            </a>
                            <a href="{{ route('admin.settings') }}" class="quick-action-card">
                                <div class="quick-action-icon">⚙️</div>
                                <div class="quick-action-title">System Settings</div>
                                <div class="quick-action-desc">Configure exam parameters</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        /* Additional styles for dashboard-specific elements */
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .status-completed {
            background: #dcfce7;
            color: #166534;
        }

        .status-in-progress {
            background: #fef3c7;
            color: #92400e;
        }

        .status-pending {
            background: #f3f4f6;
            color: #374151;
        }

        .logout-link {
            background: none;
            border: none;
            width: 100%;
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
            font-size: 14px;
            cursor: pointer;
        }

        .logout-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--yellow-primary);
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .quick-action-card {
            background: var(--light-gray);
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 20px;
            text-decoration: none;
            transition: var(--transition);
            text-align: center;
        }

        .quick-action-card:hover {
            border-color: var(--yellow-primary);
            background: var(--yellow-light);
        }

        .quick-action-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .quick-action-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 8px;
        }

        .quick-action-desc {
            font-size: 14px;
            color: var(--text-gray);
        }
    </style>
</body>
</html>