<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Interview Analytics - {{ config('app.name', 'EnrollAssess') }}</title>
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
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('department-head.interview-results') }}" class="nav-link">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Interview Results</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('department-head.analytics') }}" class="nav-link active">
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
                    <h1>Interview Analytics</h1>
                    <p class="header-subtitle">Comprehensive insights into the admission process performance</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('department-head.export-interview-results') }}" class="btn-secondary">📊 Export Data</a>
                </div>
            </div>

            <div class="main-content">
                <!-- Overview Stats -->
                <div class="stats-grid analytics-stats">
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-value">{{ $analytics['completion_stats']['total_interviews'] }}</div>
                        <div class="stat-label">Total Interviews</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-value">{{ $analytics['completion_stats']['completed'] }}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value">{{ $analytics['completion_stats']['pending'] }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📈</div>
                        <div class="stat-value">{{ number_format($analytics['score_averages']['overall'] ?? 0, 1) }}%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                </div>

                <!-- Score Averages -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Score Averages by Category</h2>
                    </div>
                    <div class="section-content">
                        <div class="score-categories">
                            <div class="category-chart">
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-name">💻 Technical Skills</span>
                                        <span class="category-score">{{ number_format($analytics['score_averages']['technical'] ?? 0, 1) }}/40</span>
                                    </div>
                                    <div class="category-bar">
                                        <div class="category-fill" style="width: {{ (($analytics['score_averages']['technical'] ?? 0) / 40) * 100 }}%"></div>
                                    </div>
                                </div>
                                
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-name">💬 Communication</span>
                                        <span class="category-score">{{ number_format($analytics['score_averages']['communication'] ?? 0, 1) }}/30</span>
                                    </div>
                                    <div class="category-bar">
                                        <div class="category-fill" style="width: {{ (($analytics['score_averages']['communication'] ?? 0) / 30) * 100 }}%"></div>
                                    </div>
                                </div>
                                
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-name">🧠 Analytical</span>
                                        <span class="category-score">{{ number_format($analytics['score_averages']['analytical'] ?? 0, 1) }}/30</span>
                                    </div>
                                    <div class="category-bar">
                                        <div class="category-fill" style="width: {{ (($analytics['score_averages']['analytical'] ?? 0) / 30) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations & Outcomes -->
                <div class="analytics-grid">
                    <!-- Recommendations Distribution -->
                    <div class="analytics-card">
                        <div class="card-header">
                            <h3>📝 Interview Recommendations</h3>
                        </div>
                        <div class="card-body">
                            <div class="recommendation-chart">
                                @foreach($analytics['recommendations'] as $recommendation => $count)
                                <div class="recommendation-item">
                                    <div class="recommendation-info">
                                        <span class="recommendation-badge {{ str_replace('_', '-', $recommendation) }}">
                                            {{ ucfirst(str_replace('_', ' ', $recommendation)) }}
                                        </span>
                                        <span class="recommendation-count">{{ $count }}</span>
                                    </div>
                                    <div class="recommendation-bar">
                                        <div class="recommendation-fill" style="width: {{ $analytics['recommendations']->sum() > 0 ? ($count / $analytics['recommendations']->sum()) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Admission Outcomes -->
                    <div class="analytics-card">
                        <div class="card-header">
                            <h3>🎓 Admission Outcomes</h3>
                        </div>
                        <div class="card-body">
                            <div class="outcome-chart">
                                <div class="outcome-item">
                                    <div class="outcome-info">
                                        <span class="outcome-label">✅ Admitted</span>
                                        <span class="outcome-count">{{ $analytics['admission_outcomes']['admitted'] }}</span>
                                    </div>
                                    <div class="outcome-percentage">
                                        @php
                                            $total = array_sum($analytics['admission_outcomes']);
                                            $percentage = $total > 0 ? round(($analytics['admission_outcomes']['admitted'] / $total) * 100, 1) : 0;
                                        @endphp
                                        {{ $percentage }}%
                                    </div>
                                </div>
                                
                                <div class="outcome-item">
                                    <div class="outcome-info">
                                        <span class="outcome-label">❌ Rejected</span>
                                        <span class="outcome-count">{{ $analytics['admission_outcomes']['rejected'] }}</span>
                                    </div>
                                    <div class="outcome-percentage">
                                        @php
                                            $percentage = $total > 0 ? round(($analytics['admission_outcomes']['rejected'] / $total) * 100, 1) : 0;
                                        @endphp
                                        {{ $percentage }}%
                                    </div>
                                </div>
                                
                                <div class="outcome-item">
                                    <div class="outcome-info">
                                        <span class="outcome-label">⏳ Pending</span>
                                        <span class="outcome-count">{{ $analytics['admission_outcomes']['pending'] }}</span>
                                    </div>
                                    <div class="outcome-percentage">
                                        @php
                                            $percentage = $total > 0 ? round(($analytics['admission_outcomes']['pending'] / $total) * 100, 1) : 0;
                                        @endphp
                                        {{ $percentage }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trends -->
                @if($analytics['monthly_trends']->count() > 0)
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">📈 Monthly Interview Trends</h2>
                    </div>
                    <div class="section-content">
                        <div class="trends-table">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Interviews Completed</th>
                                        <th>Average Score</th>
                                        <th>Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['monthly_trends'] as $trend)
                                    <tr>
                                        <td>{{ date('M Y', strtotime($trend->month . '-01')) }}</td>
                                        <td>{{ $trend->count }}</td>
                                        <td>
                                            <span class="score-badge {{ $trend->avg_score >= 75 ? 'good' : 'needs-improvement' }}">
                                                {{ number_format($trend->avg_score, 1) }}%
                                            </span>
                                        </td>
                                        <td>
                                            @if($loop->index > 0)
                                                @php
                                                    $prevTrend = $analytics['monthly_trends'][$loop->index - 1];
                                                    $change = $trend->avg_score - $prevTrend->avg_score;
                                                @endphp
                                                @if($change > 0)
                                                    <span class="trend-up">↗ +{{ number_format($change, 1) }}</span>
                                                @elseif($change < 0)
                                                    <span class="trend-down">↘ {{ number_format($change, 1) }}</span>
                                                @else
                                                    <span class="trend-stable">→ 0.0</span>
                                                @endif
                                            @else
                                                <span class="trend-stable">→ New</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Instructor Performance -->
                @if($analytics['instructor_comparison']->count() > 0)
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">👨‍🏫 Instructor Performance Comparison</h2>
                    </div>
                    <div class="section-content">
                        <div class="instructor-performance">
                            @foreach($analytics['instructor_comparison'] as $instructor)
                            <div class="instructor-card">
                                <div class="instructor-header">
                                    <div class="instructor-avatar">{{ substr($instructor->full_name, 0, 2) }}</div>
                                    <div class="instructor-info">
                                        <h4>{{ $instructor->full_name }}</h4>
                                        <p>{{ $instructor->email }}</p>
                                    </div>
                                </div>
                                <div class="instructor-stats">
                                    <div class="stat-item">
                                        <span class="stat-value">{{ $instructor->interviews_count ?? 0 }}</span>
                                        <span class="stat-label">Interviews</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">{{ number_format($instructor->completedInterviews->avg('overall_score') ?? 0, 1) }}%</span>
                                        <span class="stat-label">Avg Score</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">{{ $instructor->completedInterviews->whereIn('recommendation', ['highly_recommended', 'recommended'])->count() }}</span>
                                        <span class="stat-label">Positive Rec.</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </main>
    </div>

    <style>
        .department-head-portal { --primary-color: #7c3aed; }
        .department-head-sidebar { background: linear-gradient(180deg, var(--primary-color) 0%, #6d28d9 100%); }
        .analytics-stats .stat-card { border-left: 4px solid var(--primary-color); }
        
        .analytics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .analytics-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; }
        .analytics-card .card-header { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; }
        .analytics-card .card-body { padding: 20px; }
        
        .score-categories { background: #f9fafb; padding: 20px; border-radius: 8px; }
        .category-item { margin-bottom: 20px; }
        .category-info { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .category-name { font-weight: 500; color: #374151; }
        .category-score { font-weight: bold; color: var(--primary-color); }
        .category-bar { height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
        .category-fill { height: 100%; background: var(--primary-color); transition: width 0.3s ease; }
        
        .recommendation-chart, .outcome-chart { display: flex; flex-direction: column; gap: 15px; }
        .recommendation-item, .outcome-item { display: flex; justify-content: space-between; align-items: center; }
        .recommendation-info, .outcome-info { display: flex; align-items: center; gap: 10px; }
        .recommendation-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .recommendation-badge.highly-recommended { background: #dcfce7; color: #166534; }
        .recommendation-badge.recommended { background: #dbeafe; color: #1e40af; }
        .recommendation-badge.conditional { background: #fef3c7; color: #92400e; }
        .recommendation-badge.not-recommended { background: #fee2e2; color: #991b1b; }
        .recommendation-count, .outcome-count { font-weight: bold; color: #374151; }
        .outcome-percentage { font-weight: bold; color: var(--primary-color); }
        
        .instructor-performance { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; }
        .instructor-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; }
        .instructor-header { display: flex; gap: 12px; align-items: center; margin-bottom: 15px; }
        .instructor-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .instructor-info h4 { margin: 0; color: #374151; }
        .instructor-info p { margin: 2px 0 0 0; color: #6b7280; font-size: 12px; }
        .instructor-stats { display: flex; justify-content: space-between; }
        .stat-item { text-align: center; }
        .stat-value { display: block; font-weight: bold; color: var(--primary-color); }
        .stat-label { font-size: 12px; color: #6b7280; }
        
        .trend-up { color: #10b981; font-weight: bold; }
        .trend-down { color: #ef4444; font-weight: bold; }
        .trend-stable { color: #6b7280; font-weight: bold; }
        
        .score-badge.good { background: #dcfce7; color: #166534; }
        .score-badge.needs-improvement { background: #fef3c7; color: #92400e; }
        
        @media (max-width: 768px) {
            .analytics-grid { grid-template-columns: 1fr; }
            .instructor-performance { grid-template-columns: 1fr; }
        }
    </style>
</body>
</html>
