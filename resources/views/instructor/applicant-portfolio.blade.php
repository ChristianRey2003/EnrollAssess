<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Applicant Portfolio - {{ $applicant->full_name }} - {{ config('app.name', 'EnrollAssess') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
</head>
<body class="admin-page instructor-portal">
    <div class="admin-layout">
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

        <main class="admin-main">
            <div class="main-header instructor-header">
                <div class="header-left">
                    <h1>Applicant Portfolio</h1>
                    <p class="header-subtitle">Comprehensive overview for interview preparation</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('instructor.applicants') }}" class="btn-secondary">← Back to Applicants</a>
                </div>
            </div>

            <div class="main-content">
                <div class="portfolio-layout">
                    <!-- Left: Applicant summary -->
                    <section class="portfolio-card">
                        <div class="card-header"><h3>👤 Applicant Summary</h3></div>
                        <div class="card-body">
                            <div class="summary-header">
                                <div class="avatar-lg">{{ $applicant->initials }}</div>
                                <div>
                                    <h2 class="name">{{ $applicant->full_name }}</h2>
                                    <div class="muted">Application No: {{ $applicant->application_no }}</div>
                                    <div class="muted">Email: {{ $applicant->email_address }}</div>
                                    <div class="muted">Phone: {{ $applicant->phone_number }}</div>
                                </div>
                            </div>
                            <div class="grid two-cols">
                                <div>
                                    <div class="label">Education</div>
                                    <div class="value">{{ $applicant->education_background ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <div class="label">Status</div>
                                    <div class="value"><span class="badge">{{ ucfirst(str_replace('-', ' ', $applicant->status)) }}</span></div>
                                </div>
                                <div>
                                    <div class="label">Exam Set</div>
                                    <div class="value">{{ $applicant->examSet->name ?? 'N/A' }}</div>
                                </div>
                                <div>
                                    <div class="label">Access Code</div>
                                    <div class="value">{{ $applicant->accessCode->code ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Right: Actions -->
                    <section class="portfolio-card">
                        <div class="card-header"><h3>⚙️ Actions</h3></div>
                        <div class="card-body actions">
                            @if($latestInterview && $latestInterview->status === 'scheduled')
                                <a class="btn-primary" href="{{ route('instructor.interview.show', $applicant->applicant_id) }}">Start Interview</a>
                            @elseif($latestInterview && $latestInterview->status === 'completed')
                                <a class="btn-secondary" href="{{ route('instructor.interview.show', $applicant->applicant_id) }}">View Interview</a>
                            @else
                                <a class="btn-outline" href="{{ route('instructor.schedule') }}">Schedule Interview</a>
                            @endif
                            <a class="btn-outline" href="{{ route('instructor.interview-history') }}">View History</a>
                        </div>
                    </section>

                    <!-- Exam Overview -->
                    <section class="portfolio-card span-2">
                        <div class="card-header"><h3>📊 Exam Overview</h3></div>
                        <div class="card-body">
                            <div class="grid three-cols">
                                <div class="metric">
                                    <div class="metric-label">Score</div>
                                    <div class="metric-value">{{ number_format($applicant->score ?? 0, 1) }}%</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-label">Correct / Total</div>
                                    <div class="metric-value">{{ $examStats['correct'] }} / {{ $examStats['total_questions'] }}</div>
                                </div>
                                <div class="metric">
                                    <div class="metric-label">Percentage</div>
                                    <div class="metric-value">{{ number_format($examStats['percentage'], 1) }}%</div>
                                </div>
                            </div>

                            @if($applicant->results->count())
                                <div class="results-table-wrapper">
                                    <table class="results-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Question</th>
                                                <th>Your Answer</th>
                                                <th>Correct</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($applicant->results->take(10) as $idx => $result)
                                            <tr>
                                                <td>{{ $idx + 1 }}</td>
                                                <td>{{ Str::limit($result->question->question_text ?? 'N/A', 80) }}</td>
                                                <td>{{ $result->selected_answer ?? '-' }}</td>
                                                <td>
                                                    @if($result->is_correct)
                                                        <span class="chip good">✔ Correct</span>
                                                    @else
                                                        <span class="chip bad">✖ Incorrect</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="muted small">Showing first 10 results.</div>
                                </div>
                            @else
                                <div class="muted">No exam results available.</div>
                            @endif
                        </div>
                    </section>

                    <!-- Timeline -->
                    <section class="portfolio-card span-2">
                        <div class="card-header"><h3>🕒 Timeline</h3></div>
                        <div class="card-body">
                            <ul class="timeline">
                                <li><span class="time">{{ $applicant->created_at->format('M d, Y g:i A') }}</span> Application submitted</li>
                                @if($applicant->exam_completed_at)
                                    <li><span class="time">{{ $applicant->exam_completed_at->format('M d, Y g:i A') }}</span> Exam completed</li>
                                @endif
                                @if($latestInterview && $latestInterview->schedule_date)
                                    <li><span class="time">{{ $latestInterview->schedule_date->format('M d, Y g:i A') }}</span> Interview scheduled</li>
                                @endif
                                @if($latestInterview && $latestInterview->status === 'completed')
                                    <li><span class="time">{{ $latestInterview->interview_date?->format('M d, Y g:i A') ?? $latestInterview->updated_at->format('M d, Y g:i A') }}</span> Interview completed</li>
                                @endif
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <style>
        .instructor-portal { --primary-color: #2563eb; }
        .portfolio-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .portfolio-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; }
        .portfolio-card .card-header { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; }
        .portfolio-card .card-body { padding: 20px; }
        .span-2 { grid-column: span 2; }
        .summary-header { display: flex; gap: 15px; align-items: center; margin-bottom: 15px; }
        .avatar-lg { width: 56px; height: 56px; border-radius: 50%; background: var(--primary-color); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; }
        .name { margin: 0 0 5px 0; }
        .muted { color: #6b7280; font-size: 12px; }
        .small { font-size: 11px; }
        .label { font-size: 12px; color: #6b7280; }
        .value { font-weight: 600; color: #111827; }
        .badge { background: #eef2ff; color: #3730a3; padding: 4px 10px; border-radius: 9999px; font-size: 12px; }
        .grid.two-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px; }
        .grid.three-cols { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 12px 0; }
        .metric { background: #f9fafb; padding: 12px; border-radius: 8px; text-align: center; }
        .metric-label { font-size: 12px; color: #6b7280; }
        .metric-value { font-size: 18px; font-weight: 700; color: #111827; }
        .results-table { width: 100%; border-collapse: collapse; }
        .results-table th, .results-table td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .chip { padding: 2px 8px; border-radius: 12px; font-size: 12px; }
        .chip.good { background: #dcfce7; color: #166534; }
        .chip.bad { background: #fee2e2; color: #991b1b; }
        .timeline { list-style: none; padding: 0; margin: 0; }
        .timeline li { padding: 8px 0; border-left: 2px solid #e5e7eb; margin-left: 10px; padding-left: 10px; position: relative; }
        .timeline li::before { content: ''; position: absolute; left: -6px; top: 14px; width: 8px; height: 8px; border-radius: 50%; background: var(--primary-color); }
        @media (max-width: 900px) { .portfolio-layout { grid-template-columns: 1fr; } .span-2 { grid-column: span 1; } }
    </style>
</body>
</html>


