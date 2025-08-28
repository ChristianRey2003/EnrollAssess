<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Manage Questions - {{ config('app.name', 'EnrollAssess') }}</title>

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
                    <a href="{{ route('admin.exams.index') }}" class="nav-link">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Exams</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.questions.index') }}" class="nav-link active">
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
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <div class="main-header">
                <div class="header-left">
                    <h1>Exam Question Bank</h1>
                    <p class="header-subtitle">Manage questions for BSIT entrance examination</p>
                </div>
                <div class="header-right">
                    <div class="header-time">
                        🕐 {{ now()->format('M d, Y g:i A') }}
                    </div>
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle" onclick="toggleUserDropdown()">
                            <div class="user-avatar">
                                <span class="avatar-icon">👤</span>
                            </div>
                            <div class="user-info">
                                <div class="user-name">{{ auth()->user()->full_name ?? 'Dr. Admin' }}</div>
                                <div class="user-role">Department Head</div>
                            </div>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <!-- Department Head Features -->
                            <div class="dropdown-section">
                                <div class="dropdown-section-title">Department Head</div>
                                <a href="{{ route('admin.interview-results') }}" class="dropdown-item">
                                    <span class="dropdown-icon">🎯</span>
                                    <span class="dropdown-text">Interview Results</span>
                                </a>
                                <a href="{{ route('admin.analytics') }}" class="dropdown-item">
                                    <span class="dropdown-icon">📊</span>
                                    <span class="dropdown-text">Analytics</span>
                                </a>
                            </div>
                            
                            <div class="dropdown-divider"></div>
                            
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item logout-item">
                                    <span class="dropdown-icon">🚪</span>
                                    <span class="dropdown-text">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Search and Filter Section -->
                <div class="content-section" style="margin-bottom: 20px;">
                    <div class="section-content" style="padding: 20px 30px;">
                        <div class="questions-toolbar">
                            <div class="search-filter-group">
                                <div class="search-box">
                                    <input type="text" placeholder="Search questions..." class="search-input" id="searchInput">
                                    <button class="search-btn">🔍</button>
                                </div>
                                <select class="filter-select" id="typeFilter" name="type">
                                    <option value="">All Types</option>
                                    <option value="multiple_choice" {{ request('type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="true_false" {{ request('type') == 'true_false' ? 'selected' : '' }}>True/False</option>
                                    <option value="short_answer" {{ request('type') == 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                                    <option value="essay" {{ request('type') == 'essay' ? 'selected' : '' }}>Essay</option>
                                </select>
                                
                                <select class="filter-select" id="examSetFilter" name="exam_set_id">
                                    <option value="">All Exam Sets</option>
                                    @foreach($examSets ?? [] as $examSet)
                                        <option value="{{ $examSet->exam_set_id }}" {{ request('exam_set_id') == $examSet->exam_set_id ? 'selected' : '' }}>
                                            {{ $examSet->exam->title ?? 'Exam' }} - {{ $examSet->set_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <a href="{{ route('admin.questions.create') }}" class="section-action">
                                <span class="section-action-icon">➕</span>
                                Add New Question
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Questions Table -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Questions List</h2>
                        <div class="header-stats">
                            <span class="stat-badge">Total: {{ $questionStats['total'] ?? 0 }}</span>
                            <span class="stat-badge">MC: {{ $questionStats['multiple_choice'] ?? 0 }}</span>
                            <span class="stat-badge">T/F: {{ $questionStats['true_false'] ?? 0 }}</span>
                            <span class="stat-badge">SA: {{ $questionStats['short_answer'] ?? 0 }}</span>
                            <span class="stat-badge">Essay: {{ $questionStats['essay'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="section-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Question Text</th>
                                    <th style="width: 120px;">Type</th>
                                    <th style="width: 100px;">Points</th>
                                    <th style="width: 140px;">Exam Set</th>
                                    <th style="width: 80px;">Status</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questions as $question)
                                <tr>
                                    <td>#{{ $question->question_id }}</td>
                                    <td class="question-text">{{ Str::limit($question->question_text, 80) }}</td>
                                    <td>
                                        <span class="type-badge type-{{ str_replace('_', '-', $question->question_type) }}">
                                            {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="points-badge">{{ $question->points }} pts</span>
                                    </td>
                                    <td>
                                        <span class="exam-set-badge">
                                            {{ $question->examSet->exam->title ?? 'Unknown' }} - {{ $question->examSet->set_name ?? 'Set' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $question->is_active ? 'active' : 'inactive' }}">
                                            {{ $question->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('admin.questions.edit', $question->question_id) }}" class="action-btn action-btn-edit" title="Edit Question">
                                                ✏️ Edit
                                            </a>
                                            <button onclick="toggleStatus({{ $question->question_id }})" class="action-btn action-btn-toggle" title="Toggle Status">
                                                {{ $question->is_active ? '🔒' : '🔓' }}
                                            </button>
                                            <button onclick="deleteQuestion({{ $question->question_id }})" class="action-btn action-btn-delete" title="Delete Question">
                                                🗑️ Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <div class="empty-content">
                                            <span class="empty-icon">❓</span>
                                            <h3>No Questions Found</h3>
                                            <p>No questions have been created yet. Click the "Add New Question" button to get started.</p>
                                            <a href="{{ route('admin.questions.create') }}" class="btn-primary">
                                                ➕ Create Your First Question
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        @if($questions->hasPages())
                        <div class="pagination">
                            <div class="pagination-info">
                                Showing {{ $questions->firstItem() ?? 0 }}-{{ $questions->lastItem() ?? 0 }} of {{ $questions->total() }} questions
                            </div>
                            <div class="pagination-controls">
                                {{ $questions->appends(request()->query())->links() }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
                <button onclick="closeDeleteModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this question? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button onclick="closeDeleteModal()" class="btn-secondary">Cancel</button>
                <button onclick="confirmDelete()" class="btn-danger">Delete Question</button>
            </div>
        </div>
    </div>

    <script>
        let questionToDelete = null;

        // Search functionality with form submission
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const url = new URL(window.location);
                if (e.target.value) {
                    url.searchParams.set('search', e.target.value);
                } else {
                    url.searchParams.delete('search');
                }
                window.location = url;
            }, 500);
        });

        // Type filter
        document.getElementById('typeFilter').addEventListener('change', function(e) {
            const url = new URL(window.location);
            if (e.target.value) {
                url.searchParams.set('type', e.target.value);
            } else {
                url.searchParams.delete('type');
            }
            window.location = url;
        });

        // Exam Set filter
        document.getElementById('examSetFilter').addEventListener('change', function(e) {
            const url = new URL(window.location);
            if (e.target.value) {
                url.searchParams.set('exam_set_id', e.target.value);
            } else {
                url.searchParams.delete('exam_set_id');
            }
            window.location = url;
        });

        // Toggle question status
        function toggleStatus(questionId) {
            if (confirm('Are you sure you want to toggle the status of this question?')) {
                fetch(`/admin/questions/${questionId}/toggle-status`, {
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

        // Delete question
        function deleteQuestion(questionId) {
            questionToDelete = questionId;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            questionToDelete = null;
        }

        function confirmDelete() {
            if (questionToDelete) {
                fetch(`/admin/questions/${questionToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeDeleteModal();
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete question');
                    }
                })
                .catch(error => {
                    alert('An error occurred while deleting the question');
                });
            }
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>

    <style>
        /* Additional styles for questions page */
        .questions-toolbar {
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

        .header-stats {
            display: flex;
            gap: 10px;
        }

        .stat-badge {
            background: var(--yellow-light);
            color: var(--maroon-primary);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .question-text {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .category-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        /* Type badges */
        .type-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .type-multiple-choice { background: #dbeafe; color: #1e40af; }
        .type-true-false { background: #dcfce7; color: #166534; }
        .type-short-answer { background: #fef3c7; color: #92400e; }
        .type-essay { background: #f3e8ff; color: #7c3aed; }

        /* Points and status badges */
        .points-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .exam-set-badge {
            font-size: 12px;
            color: #64748b;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active { background: #dcfce7; color: #166534; }
        .status-inactive { background: #fecaca; color: #dc2626; }

        /* Empty state */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-content {
            max-width: 300px;
            margin: 0 auto;
        }

        .empty-icon {
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

        .empty-content .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .difficulty-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .difficulty-easy { background: #dcfce7; color: #166534; }
        .difficulty-medium { background: #fef3c7; color: #92400e; }
        .difficulty-hard { background: #fecaca; color: #dc2626; }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .modal-content {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            max-height: 90vh;
            overflow: hidden;
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--maroon-primary);
            font-size: 18px;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-gray);
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-body p {
            margin: 0;
            color: var(--text-gray);
            line-height: 1.6;
        }

        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid var(--border-gray);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-secondary {
            padding: 10px 20px;
            background: var(--light-gray);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-secondary:hover {
            background: var(--border-gray);
        }

        .btn-danger {
            padding: 10px 20px;
            background: #dc2626;
            color: var(--white);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-danger:hover {
            background: #b91c1c;
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

        @media (max-width: 768px) {
            .questions-toolbar {
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
        }

        // User dropdown functionality
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdownMenu');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdownMenu');
            const toggle = document.querySelector('.user-dropdown-toggle');
            
            if (!toggle.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });
    </style>
</body>
</html>