<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $exam->title }} - {{ config('app.name', 'EnrollAssess') }}</title>

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
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.exams.index') }}" class="nav-link active">
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
                    <h1>{{ $exam->title }}</h1>
                    <p class="header-subtitle">Detailed view and management of exam sets and questions</p>
                </div>
                <div class="header-right">
                    <div class="header-time">
                        🕐 {{ now()->format('M d, Y g:i A') }}
                    </div>
                    <div class="header-user">
                        {{ auth()->user()->name ?? 'Dr. Admin' }}
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Breadcrumb -->
                <div class="breadcrumb">
                    <a href="{{ route('admin.exams.index') }}" class="breadcrumb-link">Exams</a>
                    <span class="breadcrumb-separator">›</span>
                    <span class="breadcrumb-current">{{ $exam->title }}</span>
                </div>

                <!-- Exam Info Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Exam Information</h2>
                        <div class="exam-actions">
                            <a href="{{ route('admin.exams.edit', $exam->exam_id) }}" class="btn-secondary">
                                ✏️ Edit Exam
                            </a>
                            <a href="{{ route('admin.exam-sets.create', $exam->exam_id) }}" class="btn-primary">
                                ➕ Add New Set
                            </a>
                        </div>
                    </div>
                    <div class="section-content">
                        <div class="exam-details">
                            <div class="exam-meta">
                                <div class="meta-item">
                                    <label>Duration:</label>
                                    <span>{{ $exam->formatted_duration }}</span>
                                </div>
                                <div class="meta-item">
                                    <label>Status:</label>
                                    <span class="status-badge status-{{ $exam->is_active ? 'active' : 'inactive' }}">
                                        {{ $exam->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="meta-item">
                                    <label>Created:</label>
                                    <span>{{ $exam->created_at->format('M d, Y g:i A') }}</span>
                                </div>
                                <div class="meta-item">
                                    <label>Last Updated:</label>
                                    <span>{{ $exam->updated_at->format('M d, Y g:i A') }}</span>
                                </div>
                            </div>
                            
                            @if($exam->description)
                            <div class="exam-description">
                                <label>Description:</label>
                                <p>{{ $exam->description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Statistics Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Statistics</h2>
                    </div>
                    <div class="section-content">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">📋</div>
                                <div class="stat-value">{{ $stats['total_sets'] }}</div>
                                <div class="stat-label">Total Sets</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">✅</div>
                                <div class="stat-value">{{ $stats['active_sets'] }}</div>
                                <div class="stat-label">Active Sets</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">❓</div>
                                <div class="stat-value">{{ $stats['total_questions'] }}</div>
                                <div class="stat-label">Total Questions</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">🎯</div>
                                <div class="stat-value">{{ $stats['total_points'] }}</div>
                                <div class="stat-label">Total Points</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exam Sets Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Exam Sets</h2>
                        <div class="section-actions">
                            <a href="{{ route('admin.exam-sets.index', $exam->exam_id) }}" class="btn-secondary">
                                View All Sets
                            </a>
                        </div>
                    </div>
                    <div class="section-content">
                        @if($exam->examSets->count() > 0)
                            <div class="exam-sets-grid">
                                @foreach($exam->examSets as $examSet)
                                <div class="exam-set-card">
                                    <div class="set-header">
                                        <h3 class="set-name">{{ $examSet->set_name }}</h3>
                                        <span class="status-badge status-{{ $examSet->is_active ? 'active' : 'inactive' }}">
                                            {{ $examSet->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    
                                    @if($examSet->description)
                                    <p class="set-description">{{ Str::limit($examSet->description, 100) }}</p>
                                    @endif
                                    
                                    <div class="set-stats">
                                        <div class="set-stat">
                                            <span class="stat-number">{{ $examSet->questions->count() }}</span>
                                            <span class="stat-text">Questions</span>
                                        </div>
                                        <div class="set-stat">
                                            <span class="stat-number">{{ $examSet->questions->sum('points') }}</span>
                                            <span class="stat-text">Points</span>
                                        </div>
                                    </div>
                                    
                                    <div class="set-questions-preview">
                                        @if($examSet->questions->count() > 0)
                                            <div class="questions-types">
                                                @php
                                                    $types = $examSet->questions->groupBy('question_type');
                                                @endphp
                                                @foreach($types as $type => $questions)
                                                    <span class="type-badge type-{{ str_replace('_', '-', $type) }}">
                                                        {{ ucwords(str_replace('_', ' ', $type)) }}: {{ $questions->count() }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="no-questions">
                                                <span class="empty-icon">❓</span>
                                                <span>No questions added yet</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="set-actions">
                                        <a href="{{ route('admin.exam-sets.show', [$exam->exam_id, $examSet->exam_set_id]) }}" class="action-btn action-btn-view">
                                            👁️ View
                                        </a>
                                        <a href="{{ route('admin.exam-sets.edit', [$exam->exam_id, $examSet->exam_set_id]) }}" class="action-btn action-btn-edit">
                                            ✏️ Edit
                                        </a>
                                        @if($examSet->questions->count() === 0)
                                        <button onclick="deleteExamSet({{ $examSet->exam_set_id }})" class="action-btn action-btn-delete">
                                            🗑️ Delete
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-content">
                                    <span class="empty-icon">📋</span>
                                    <h3>No Exam Sets Created</h3>
                                    <p>This exam doesn't have any question sets yet. Create your first set to start adding questions.</p>
                                    <a href="{{ route('admin.exam-sets.create', $exam->exam_id) }}" class="btn-primary">
                                        ➕ Create First Set
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Delete exam set
        function deleteExamSet(setId) {
            if (confirm('Are you sure you want to delete this exam set? This action cannot be undone.')) {
                fetch(`/admin/exams/{{ $exam->exam_id }}/sets/${setId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete exam set');
                    }
                })
                .catch(error => {
                    alert('An error occurred while deleting the exam set');
                });
            }
        }
    </script>

    <style>
        /* Exam details styles */
        .exam-actions {
            display: flex;
            gap: 10px;
        }

        .exam-details {
            padding: 20px;
        }

        .exam-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .meta-item label {
            font-weight: 600;
            color: var(--maroon-primary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-item span {
            color: var(--text-dark);
            font-size: 14px;
        }

        .exam-description {
            border-top: 1px solid var(--border-gray);
            padding-top: 20px;
        }

        .exam-description label {
            font-weight: 600;
            color: var(--maroon-primary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 8px;
        }

        .exam-description p {
            color: var(--text-dark);
            line-height: 1.6;
            margin: 0;
        }

        /* Statistics grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .stat-card {
            background: var(--white);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border-gray);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--maroon-primary);
            margin-bottom: 4px;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 14px;
            font-weight: 500;
        }

        /* Exam sets grid */
        .exam-sets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .exam-set-card {
            background: var(--white);
            border: 1px solid var(--border-gray);
            border-radius: 12px;
            padding: 20px;
            transition: var(--transition);
        }

        .exam-set-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .set-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .set-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin: 0;
        }

        .set-description {
            color: var(--text-gray);
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .set-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 16px;
        }

        .set-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-number {
            font-size: 20px;
            font-weight: 700;
            color: var(--maroon-primary);
        }

        .stat-text {
            font-size: 12px;
            color: var(--text-gray);
            text-transform: uppercase;
        }

        .set-questions-preview {
            margin-bottom: 16px;
        }

        .questions-types {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .type-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .type-multiple-choice { background: #dbeafe; color: #1e40af; }
        .type-true-false { background: #dcfce7; color: #166534; }
        .type-short-answer { background: #fef3c7; color: #92400e; }
        .type-essay { background: #f3e8ff; color: #7c3aed; }

        .no-questions {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-gray);
            font-style: italic;
            font-size: 14px;
        }

        .empty-icon {
            opacity: 0.5;
        }

        .set-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .action-btn {
            padding: 6px 12px;
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            background: var(--white);
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            transition: var(--transition);
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .action-btn-view { background: #e0f2fe; color: #0277bd; border-color: #b3e5fc; }
        .action-btn-edit { background: #fff3e0; color: #ef6c00; border-color: #ffcc02; }
        .action-btn-delete { background: #ffebee; color: #d32f2f; border-color: #ffcdd2; }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-content {
            max-width: 300px;
            margin: 0 auto;
        }

        .empty-content .empty-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-content h3 {
            margin: 0 0 8px 0;
            color: var(--text-dark);
            font-size: 18px;
        }

        .empty-content p {
            margin: 0 0 20px 0;
            color: var(--text-gray);
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .exam-actions {
                flex-direction: column;
            }

            .exam-meta {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .exam-sets-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .set-stats {
                justify-content: center;
            }
        }
    </style>
</body>
</html>