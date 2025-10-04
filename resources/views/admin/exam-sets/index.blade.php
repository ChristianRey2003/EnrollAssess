<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $exam->title }} - Exam Sets - {{ config('app.name', 'EnrollAssess') }}</title>

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
                    <a href="{{ route('admin.applicants.index') }}" class="nav-link">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">Applicants</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.sets-questions.index') }}" class="nav-link active">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Exams</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.questions.index') }}" class="nav-link">
                        <span class="nav-icon">❓</span>
                        <span class="nav-text">Questions</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.interviews.index') }}" class="nav-link">
                        <span class="nav-icon">📅</span>
                        <span class="nav-text">Interviews</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link">
                        <span class="nav-icon">👤</span>
                        <span class="nav-text">Users</span>
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
                    <h1>{{ $exam->title }} - Question Sets</h1>
                    <p class="header-subtitle">Manage different question sets for this examination</p>
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
                    <a href="{{ route('admin.sets-questions.index') }}" class="breadcrumb-link">Exams</a>
                    <span class="breadcrumb-separator">›</span>
                    <a href="{{ route('admin.exams.show', $exam->exam_id) }}" class="breadcrumb-link">{{ $exam->title }}</a>
                    <span class="breadcrumb-separator">›</span>
                    <span class="breadcrumb-current">Question Sets</span>
                </div>

                <!-- Exam Info Bar -->
                <div class="exam-info-bar">
                    <div class="exam-info">
                        <span class="exam-duration">⏱️ {{ $exam->formatted_duration }}</span>
                        <span class="exam-status status-{{ $exam->is_active ? 'active' : 'inactive' }}">
                            {{ $exam->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="exam-actions">
                        <a href="{{ route('admin.exams.show', $exam->exam_id) }}" class="btn-secondary">
                            ← Back to Exam
                        </a>
                        <a href="{{ route('admin.exam-sets.create', $exam->exam_id) }}" class="btn-primary">
                            ➕ Create New Set
                        </a>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="content-section" style="margin-bottom: 20px;">
                    <div class="section-content" style="padding: 20px 30px;">
                        <div class="sets-toolbar">
                            <div class="search-filter-group">
                                <div class="search-box">
                                    <input type="text" placeholder="Search sets..." class="search-input" id="searchInput" value="{{ request('search') }}">
                                    <button class="search-btn" onclick="performSearch()">🔍</button>
                                </div>
                                <select class="filter-select" id="statusFilter" name="status" onchange="applyFilter()">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exam Sets Grid -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Question Sets</h2>
                        <div class="header-stats">
                            <span class="stat-badge">Total: {{ $examSets->total() ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="section-content">
                        @if($examSets->count() > 0)
                            <div class="exam-sets-grid">
                                @foreach($examSets as $examSet)
                                <div class="exam-set-card">
                                    <div class="set-header">
                                        <h3 class="set-name">{{ $examSet->set_name }}</h3>
                                        <span class="status-badge status-{{ $examSet->is_active ? 'active' : 'inactive' }}">
                                            {{ $examSet->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    
                                    @if($examSet->description)
                                    <p class="set-description">{{ Str::limit($examSet->description, 120) }}</p>
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
                                        <div class="set-stat">
                                            <span class="stat-number">{{ $examSet->questions->where('is_active', true)->count() }}</span>
                                            <span class="stat-text">Active</span>
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
                                        <a href="{{ route('admin.exam-sets.show', [$exam->exam_id, $examSet->exam_set_id]) }}" class="action-btn action-btn-view" title="View Details">
                                            👁️
                                        </a>
                                        <a href="{{ route('admin.exam-sets.edit', [$exam->exam_id, $examSet->exam_set_id]) }}" class="action-btn action-btn-edit" title="Edit Set">
                                            ✏️
                                        </a>
                                        <button onclick="toggleStatus({{ $examSet->exam_set_id }})" class="action-btn action-btn-toggle" title="Toggle Status">
                                            {{ $examSet->is_active ? '🔒' : '🔓' }}
                                        </button>
                                        <button onclick="duplicateSet({{ $examSet->exam_set_id }})" class="action-btn action-btn-duplicate" title="Duplicate Set">
                                            📄
                                        </button>
                                        @if($examSet->questions->count() === 0)
                                        <button onclick="deleteSet({{ $examSet->exam_set_id }})" class="action-btn action-btn-delete" title="Delete Set">
                                            🗑️
                                        </button>
                                        @endif
                                    </div>
                                    
                                    <div class="set-footer">
                                        <small class="created-date">Created {{ $examSet->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if($examSets->hasPages())
                            <div class="pagination">
                                <div class="pagination-info">
                                    Showing {{ $examSets->firstItem() ?? 0 }}-{{ $examSets->lastItem() ?? 0 }} of {{ $examSets->total() }} sets
                                </div>
                                <div class="pagination-controls">
                                    {{ $examSets->appends(request()->query())->links() }}
                                </div>
                            </div>
                            @endif
                        @else
                            <div class="empty-state">
                                <div class="empty-content">
                                    <span class="empty-icon">📋</span>
                                    <h3>No Question Sets Created</h3>
                                    <p>This exam doesn't have any question sets yet. Create your first set to start adding different question variations.</p>
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
        // Search functionality
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value;
            const url = new URL(window.location);
            if (searchTerm.trim()) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }
            window.location = url;
        }

        // Enter key search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Status filter
        function applyFilter() {
            const status = document.getElementById('statusFilter').value;
            const url = new URL(window.location);
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            window.location = url;
        }

        // Toggle set status
        function toggleStatus(setId) {
            if (confirm('Are you sure you want to toggle the status of this exam set?')) {
                fetch(`/admin/exams/{{ $exam->exam_id }}/sets/${setId}/toggle-status`, {
                    method: 'POST',
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
                        alert(data.message || 'Failed to toggle status');
                    }
                })
                .catch(error => {
                    alert('An error occurred while toggling the status');
                });
            }
        }

        // Duplicate set
        function duplicateSet(setId) {
            if (confirm('Are you sure you want to duplicate this exam set? This will create a copy with all questions.')) {
                fetch(`/admin/exams/{{ $exam->exam_id }}/sets/${setId}/duplicate`, {
                    method: 'POST',
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
                        alert(data.message || 'Failed to duplicate set');
                    }
                })
                .catch(error => {
                    alert('An error occurred while duplicating the set');
                });
            }
        }

        // Delete set
        function deleteSet(setId) {
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
                        alert(data.message || 'Failed to delete set');
                    }
                })
                .catch(error => {
                    alert('An error occurred while deleting the set');
                });
            }
        }
    </script>

    <style>
        /* Exam info bar */
        .exam-info-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--light-gray);
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid var(--border-gray);
        }

        .exam-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .exam-duration {
            color: var(--text-gray);
            font-size: 14px;
        }

        .exam-actions {
            display: flex;
            gap: 10px;
        }

        /* Sets toolbar */
        .sets-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .search-filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex: 1;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 10px 40px 10px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        .search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
        }

        .filter-select {
            padding: 10px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            background: var(--white);
            cursor: pointer;
            min-width: 150px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--yellow-primary);
        }

        /* Exam sets grid */
        .exam-sets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
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
            font-size: 14px;
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
            font-size: 18px;
            font-weight: 700;
            color: var(--maroon-primary);
        }

        .stat-text {
            font-size: 11px;
            color: var(--text-gray);
            text-transform: uppercase;
        }

        .set-questions-preview {
            margin-bottom: 16px;
            min-height: 30px;
        }

        .questions-types {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .type-badge {
            padding: 3px 6px;
            border-radius: 10px;
            font-size: 9px;
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
            font-size: 13px;
        }

        .empty-icon {
            opacity: 0.5;
            font-size: 16px;
        }

        .set-actions {
            display: flex;
            gap: 6px;
            justify-content: flex-end;
            margin-bottom: 12px;
        }

        .action-btn {
            padding: 6px 10px;
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
        .action-btn-toggle { background: #f3e5f5; color: #7b1fa2; border-color: #e1bee7; }
        .action-btn-duplicate { background: #e8f5e8; color: #2e7d32; border-color: #c8e6c9; }
        .action-btn-delete { background: #ffebee; color: #d32f2f; border-color: #ffcdd2; }

        .set-footer {
            border-top: 1px solid var(--border-gray);
            padding-top: 12px;
        }

        .created-date {
            color: var(--text-gray);
            font-size: 12px;
        }

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
            .exam-info-bar {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }

            .exam-actions {
                justify-content: center;
            }

            .sets-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .search-filter-group {
                flex-direction: column;
                gap: 10px;
            }

            .search-box {
                max-width: none;
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