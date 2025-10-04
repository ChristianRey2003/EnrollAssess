<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Department Head Dashboard - {{ config('app.name', 'EnrollAssess') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
</head>
<body class="admin-page department-head-portal">
    <div class="admin-layout">
        <nav class="admin-sidebar department-head-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo">
                    <div>
                        <h2 class="sidebar-title">EnrollAssess</h2>
                        <p class="sidebar-subtitle">Department Head</p>
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
                    <a href="{{ route('admin.interview-results') }}" class="nav-link">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Interview Results</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.analytics') }}" class="nav-link">
                        <span class="nav-icon">📈</span>
                        <span class="nav-text">Analytics</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <span class="nav-icon">⚙️</span>
                        <span class="nav-text">Admin Portal</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">{{ substr(Auth::user()->full_name, 0, 2) }}</div>
                    <div>
                        <div class="user-name">{{ Auth::user()->full_name }}</div>
                        <div class="user-role">Department Head</div>
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

        <main class="admin-main">
            <div class="main-header">
                <div class="header-left">
                    <h1>Department Head Dashboard</h1>
                    <p class="header-subtitle">Strategic overview of admission processes and interview results</p>
                </div>
                <div class="header-right">
                    <div class="header-time">🕐 {{ now()->format('M d, Y g:i A') }}</div>
                </div>
            </div>

            <div class="main-content">
                <!-- Key Statistics -->
                <div class="stats-grid department-head-stats">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value">{{ $stats['total_applicants'] }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-value">{{ $stats['interview_completed'] }}</div>
                        <div class="stat-label">Interviews Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎓</div>
                        <div class="stat-value">{{ $stats['admitted'] }}</div>
                        <div class="stat-label">Admitted</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value">{{ $stats['pending_decision'] }}</div>
                        <div class="stat-label">Pending Decision</div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Quick Actions</h2>
                    </div>
                    <div class="section-content">
                        <div class="quick-actions-grid">
                            <a href="{{ route('admin.interview-results') }}" class="quick-action-card">
                                <div class="action-icon">📝</div>
                                <div class="action-content">
                                    <h3>Review Interview Results</h3>
                                    <p>Make admission decisions based on interview evaluations</p>
                                </div>
                                <div class="action-arrow">→</div>
                            </a>
                            
                            <a href="{{ route('admin.analytics') }}" class="quick-action-card">
                                <div class="action-icon">📈</div>
                                <div class="action-content">
                                    <h3>View Analytics</h3>
                                    <p>Comprehensive insights into the admission process</p>
                                </div>
                                <div class="action-arrow">→</div>
                            </a>
                            
                            <a href="{{ route('admin.export-interview-results') }}" class="quick-action-card">
                                <div class="action-icon">📊</div>
                                <div class="action-content">
                                    <h3>Export Results</h3>
                                    <p>Download interview data for reporting</p>
                                </div>
                                <div class="action-arrow">→</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Interview Submissions -->
                @if($recentInterviews->count() > 0)
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Interview Submissions</h2>
                        <div class="section-actions">
                            <a href="{{ route('admin.interview-results') }}" class="btn-primary">View All</a>
                        </div>
                    </div>
                    <div class="section-content">
                        <div class="table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Interviewer</th>
                                        <th>Score</th>
                                        <th>Recommendation</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentInterviews as $interview)
                                    <tr>
                                        <td>
                                            <div class="applicant-info">
                                                <div class="applicant-name">{{ $interview->applicant->full_name }}</div>
                                                <div class="applicant-email">{{ $interview->applicant->email_address }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $interview->interviewer->full_name }}</td>
                                        <td>
                                            <span class="score-badge {{ $interview->overall_score >= 75 ? 'good' : 'needs-improvement' }}">
                                                {{ number_format($interview->overall_score, 1) }}%
                                            </span>
                                        </td>
                                        <td>
                                            <span class="recommendation-badge {{ str_replace('_', '-', $interview->recommendation) }}">
                                                {{ ucfirst(str_replace('_', ' ', $interview->recommendation)) }}
                                            </span>
                                        </td>
                                        <td>{{ $interview->updated_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.interview-detail', $interview->interview_id) }}" 
                                               class="btn-sm btn-primary">View Details</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Score Distribution Chart -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Score Distribution</h2>
                    </div>
                    <div class="section-content">
                        <div class="score-distribution">
                            @foreach($scoreDistribution as $range => $count)
                            <div class="distribution-item">
                                <div class="distribution-label">{{ $range }}</div>
                                <div class="distribution-bar">
                                    <div class="distribution-fill" style="width: {{ ($count / $scoreDistribution->sum()) * 100 }}%"></div>
                                </div>
                                <div class="distribution-count">{{ $count }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .department-head-portal { --primary-color: #7c3aed; }
        .department-head-sidebar { background: linear-gradient(180deg, var(--primary-color) 0%, #6d28d9 100%); }
        .department-head-stats .stat-card { border-left: 4px solid var(--primary-color); }
        
        .quick-actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .quick-action-card { display: flex; align-items: center; gap: 15px; padding: 20px; background: white; border: 2px solid #e5e7eb; border-radius: 12px; text-decoration: none; transition: all 0.3s ease; }
        .quick-action-card:hover { border-color: var(--primary-color); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(124, 58, 237, 0.15); }
        
        .action-icon { font-size: 24px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border-radius: 12px; }
        .action-content h3 { margin: 0 0 5px 0; color: var(--primary-color); font-size: 16px; font-weight: 600; }
        .action-content p { margin: 0; color: #6b7280; font-size: 14px; }
        .action-arrow { font-size: 20px; color: var(--primary-color); margin-left: auto; }
        
        .score-distribution { display: flex; flex-direction: column; gap: 15px; }
        .distribution-item { display: flex; align-items: center; gap: 15px; }
        .distribution-label { min-width: 180px; font-size: 14px; color: #374151; }
        .distribution-bar { flex: 1; height: 8px; background: #f3f4f6; border-radius: 4px; overflow: hidden; }
        .distribution-fill { height: 100%; background: var(--primary-color); transition: width 0.3s ease; }
        .distribution-count { min-width: 40px; text-align: right; font-weight: 600; color: #374151; }
        
        .score-badge.good { background: #dcfce7; color: #166534; }
        .score-badge.needs-improvement { background: #fef3c7; color: #92400e; }
        .recommendation-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .recommendation-badge.highly-recommended { background: #dcfce7; color: #166534; }
        .recommendation-badge.recommended { background: #dbeafe; color: #1e40af; }
        .recommendation-badge.conditional { background: #fef3c7; color: #92400e; }
        .recommendation-badge.not-recommended { background: #fee2e2; color: #991b1b; }
    </style>
</body>
</html>
