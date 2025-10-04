<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Interview Detail - {{ $interview->applicant->full_name }} - {{ config('app.name', 'EnrollAssess') }}</title>
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
                    <h1>Interview Detail</h1>
                    <p class="header-subtitle">{{ $interview->applicant->full_name }} - Detailed Interview Results</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('admin.interview-results') }}" class="btn-secondary">← Back to Results</a>
                </div>
            </div>

            <div class="main-content">
                <div class="interview-detail-layout">
                    <!-- Applicant Overview -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h3>👤 Applicant Information</h3>
                            <div class="status-indicator">
                                <span class="status-badge status-{{ str_replace(' ', '-', strtolower($interview->applicant->status)) }}">
                                    {{ ucfirst(str_replace('-', ' ', $interview->applicant->status)) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="applicant-summary">
                                <div class="applicant-avatar-lg">
                                    {{ substr($interview->applicant->full_name, 0, 2) }}
                                </div>
                                <div class="applicant-details">
                                    <h4>{{ $interview->applicant->full_name }}</h4>
                                    <div class="detail-row">
                                        <span class="label">Application No:</span>
                                        <span class="value">{{ $interview->applicant->application_no }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Email:</span>
                                        <span class="value">{{ $interview->applicant->email_address }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Phone:</span>
                                        <span class="value">{{ $interview->applicant->phone_number }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Education:</span>
                                        <span class="value">{{ $interview->applicant->education_background }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Exam Score:</span>
                                        <span class="value">
                                            <span class="score-badge {{ $interview->applicant->score >= 70 ? 'good' : 'needs-improvement' }}">
                                                {{ number_format($interview->applicant->score ?? 0, 1) }}%
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interview Summary -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h3>📋 Interview Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="summary-grid">
                                <div class="summary-item">
                                    <div class="summary-label">Interviewer</div>
                                    <div class="summary-value">{{ $interview->interviewer->full_name }}</div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Interview Date</div>
                                    <div class="summary-value">
                                        {{ $interview->interview_date ? $interview->interview_date->format('M d, Y g:i A') : 'N/A' }}
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Overall Rating</div>
                                    <div class="summary-value">
                                        <span class="rating-badge {{ str_replace('_', '-', $interview->overall_rating) }}">
                                            {{ ucfirst(str_replace('_', ' ', $interview->overall_rating ?? 'N/A')) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Recommendation</div>
                                    <div class="summary-value">
                                        <span class="recommendation-badge {{ str_replace('_', '-', $interview->recommendation) }}">
                                            {{ ucfirst(str_replace('_', ' ', $interview->recommendation ?? 'N/A')) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Score Breakdown -->
                    <div class="detail-card span-2">
                        <div class="card-header">
                            <h3>📊 Score Breakdown</h3>
                            <div class="overall-score">
                                <span class="score-value">{{ number_format($interview->overall_score ?? 0, 1) }}%</span>
                                <span class="score-label">Overall Score</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="scores-grid">
                                <div class="score-category">
                                    <div class="category-header">
                                        <h5>💻 Technical Skills</h5>
                                        <span class="category-score">{{ $interview->rating_technical ?? 0 }}/40</span>
                                    </div>
                                    @if(isset($rubricScores['technical']))
                                    <div class="rubric-breakdown">
                                        <div class="rubric-item">
                                            <span>Programming Knowledge:</span>
                                            <span>{{ $rubricScores['technical']['programming'] ?? 0 }}/10</span>
                                        </div>
                                        <div class="rubric-item">
                                            <span>Problem Solving:</span>
                                            <span>{{ $rubricScores['technical']['problem_solving'] ?? 0 }}/10</span>
                                        </div>
                                        <div class="rubric-item">
                                            <span>Algorithms:</span>
                                            <span>{{ $rubricScores['technical']['algorithms'] ?? 0 }}/10</span>
                                        </div>
                                        <div class="rubric-item">
                                            <span>System Design:</span>
                                            <span>{{ $rubricScores['technical']['system_design'] ?? 0 }}/10</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="score-category">
                                    <div class="category-header">
                                        <h5>💬 Communication</h5>
                                        <span class="category-score">{{ $interview->rating_communication ?? 0 }}/30</span>
                                    </div>
                                    @if(isset($rubricScores['communication']))
                                    <div class="rubric-breakdown">
                                        <div class="rubric-item">
                                            <span>Clarity:</span>
                                            <span>{{ $rubricScores['communication']['clarity'] ?? 0 }}/10</span>
                                        </div>
                                        <div class="rubric-item">
                                            <span>Listening:</span>
                                            <span>{{ $rubricScores['communication']['listening'] ?? 0 }}/10</span>
                                        </div>
                                        <div class="rubric-item">
                                            <span>Confidence:</span>
                                            <span>{{ $rubricScores['communication']['confidence'] ?? 0 }}/10</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="score-category">
                                    <div class="category-header">
                                        <h5>🧠 Analytical Thinking</h5>
                                        <span class="category-score">{{ $interview->rating_problem_solving ?? 0 }}/30</span>
                                    </div>
                                    @if(isset($rubricScores['analytical']))
                                    <div class="rubric-breakdown">
                                        <div class="rubric-item">
                                            <span>Critical Thinking:</span>
                                            <span>{{ $rubricScores['analytical']['critical_thinking'] ?? 0 }}/10</span>
                                        </div>
                                        <div class="rubric-item">
                                            <span>Creativity:</span>
                                            <span>{{ $rubricScores['analytical']['creativity'] ?? 0 }}/10</span>
                                        </div>
                                        <div class="rubric-item">
                                            <span>Attention to Detail:</span>
                                            <span>{{ $rubricScores['analytical']['attention_detail'] ?? 0 }}/10</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interview Notes -->
                    <div class="detail-card span-2">
                        <div class="card-header">
                            <h3>📝 Interview Assessment</h3>
                        </div>
                        <div class="card-body">
                            <div class="assessment-section">
                                <h5>💪 Key Strengths</h5>
                                <div class="assessment-content">
                                    {{ $interview->strengths ?? 'No strengths noted.' }}
                                </div>
                            </div>

                            <div class="assessment-section">
                                <h5>📈 Areas for Improvement</h5>
                                <div class="assessment-content">
                                    {{ $interview->areas_improvement ?? 'No areas for improvement noted.' }}
                                </div>
                            </div>

                            @if($interview->notes)
                            <div class="assessment-section">
                                <h5>📓 Additional Notes</h5>
                                <div class="assessment-content">
                                    {{ $interview->notes }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Decision Actions -->
                    @if($interview->applicant->status === 'interview-completed')
                    <div class="detail-card span-2">
                        <div class="card-header">
                            <h3>⚖️ Admission Decision</h3>
                        </div>
                        <div class="card-body">
                            <div class="decision-actions">
                                <button class="btn-success" onclick="makeDecision('admit')">
                                    ✅ Admit Applicant
                                </button>
                                <button class="btn-danger" onclick="makeDecision('reject')">
                                    ❌ Reject Applicant
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <style>
        .department-head-portal { --primary-color: #7c3aed; }
        .department-head-sidebar { background: linear-gradient(180deg, var(--primary-color) 0%, #6d28d9 100%); }
        
        .interview-detail-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .span-2 { grid-column: span 2; }
        
        .detail-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; }
        .detail-card .card-header { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .detail-card .card-body { padding: 20px; }
        
        .applicant-summary { display: flex; gap: 15px; align-items: flex-start; }
        .applicant-avatar-lg { width: 64px; height: 64px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; }
        .applicant-details h4 { margin: 0 0 15px 0; color: #111827; }
        .detail-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #6b7280; font-size: 14px; }
        .value { color: #111827; font-weight: 500; font-size: 14px; }
        
        .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .summary-item { text-align: center; }
        .summary-label { font-size: 12px; color: #6b7280; margin-bottom: 5px; }
        .summary-value { font-weight: 600; color: #111827; }
        
        .overall-score { text-align: center; }
        .score-value { font-size: 24px; font-weight: bold; color: var(--primary-color); display: block; }
        .score-label { font-size: 12px; color: #6b7280; }
        
        .scores-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .score-category { border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; }
        .category-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .category-header h5 { margin: 0; color: #374151; }
        .category-score { font-weight: bold; color: var(--primary-color); }
        
        .rubric-breakdown { font-size: 14px; }
        .rubric-item { display: flex; justify-content: space-between; padding: 4px 0; color: #6b7280; }
        
        .assessment-section { margin-bottom: 20px; }
        .assessment-section h5 { margin: 0 0 10px 0; color: #374151; }
        .assessment-content { background: #f9fafb; padding: 15px; border-radius: 6px; color: #374151; line-height: 1.6; }
        
        .decision-actions { display: flex; gap: 15px; justify-content: center; }
        .btn-success { background: #10b981; color: white; padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer; font-weight: 500; }
        .btn-danger { background: #ef4444; color: white; padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer; font-weight: 500; }
        
        .status-badge, .score-badge, .recommendation-badge, .rating-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .status-badge.status-interview-completed { background: #dbeafe; color: #1e40af; }
        .status-badge.status-admitted { background: #dcfce7; color: #166534; }
        .status-badge.status-rejected { background: #fee2e2; color: #991b1b; }
        
        .score-badge.good { background: #dcfce7; color: #166534; }
        .score-badge.needs-improvement { background: #fef3c7; color: #92400e; }
        
        .recommendation-badge.highly-recommended { background: #dcfce7; color: #166534; }
        .recommendation-badge.recommended { background: #dbeafe; color: #1e40af; }
        .recommendation-badge.conditional { background: #fef3c7; color: #92400e; }
        .recommendation-badge.not-recommended { background: #fee2e2; color: #991b1b; }
        
        .rating-badge.excellent { background: #dcfce7; color: #166534; }
        .rating-badge.very-good { background: #dbeafe; color: #1e40af; }
        .rating-badge.good { background: #e0f2fe; color: #0369a1; }
        .rating-badge.satisfactory { background: #fef3c7; color: #92400e; }
        .rating-badge.needs-improvement { background: #fee2e2; color: #991b1b; }
        
        @media (max-width: 1024px) {
            .interview-detail-layout { grid-template-columns: 1fr; }
            .span-2 { grid-column: span 1; }
            .scores-grid { grid-template-columns: 1fr; }
        }
    </style>

    <script>
        async function makeDecision(decision) {
            const actionText = decision === 'admit' ? 'admit' : 'reject';
            if (!confirm(`Are you sure you want to ${actionText} this applicant?`)) {
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('{{ route("admin.bulk-admission-decision") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        interview_ids: [{{ $interview->interview_id }}],
                        decision: decision
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred.');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            }
        }
    </script>
</body>
</html>
