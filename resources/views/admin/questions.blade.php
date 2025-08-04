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
                    <a href="{{ route('admin.questions') }}" class="nav-link active">
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
                <form method="POST" action="{{ route('logout') }}">
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
                    <h1>Exam Question Bank</h1>
                    <p class="header-subtitle">Manage questions for BSIT entrance examination</p>
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
                <!-- Search and Filter Section -->
                <div class="content-section" style="margin-bottom: 20px;">
                    <div class="section-content" style="padding: 20px 30px;">
                        <div class="questions-toolbar">
                            <div class="search-filter-group">
                                <div class="search-box">
                                    <input type="text" placeholder="Search questions..." class="search-input" id="searchInput">
                                    <button class="search-btn">🔍</button>
                                </div>
                                <select class="filter-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    <option value="programming">Programming</option>
                                    <option value="database">Database</option>
                                    <option value="networking">Networking</option>
                                    <option value="software-engineering">Software Engineering</option>
                                    <option value="data-structures">Data Structures</option>
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
                            <span class="stat-badge">Total: {{ $totalQuestions ?? 87 }}</span>
                        </div>
                    </div>
                    <div class="section-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Question Text</th>
                                    <th style="width: 120px;">Category</th>
                                    <th style="width: 100px;">Difficulty</th>
                                    <th style="width: 120px;">Created</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questions ?? [] as $question)
                                <tr>
                                    <td>#{{ $question->id }}</td>
                                    <td class="question-text">{{ Str::limit($question->question_text, 80) }}</td>
                                    <td>
                                        <span class="category-badge category-{{ strtolower($question->category) }}">
                                            {{ ucfirst($question->category) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="difficulty-badge difficulty-{{ strtolower($question->difficulty) }}">
                                            {{ ucfirst($question->difficulty) }}
                                        </span>
                                    </td>
                                    <td>{{ $question->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('admin.questions.edit', $question->id) }}" class="action-btn action-btn-edit" title="Edit Question">
                                                ✏️ Edit
                                            </a>
                                            <button onclick="deleteQuestion({{ $question->id }})" class="action-btn action-btn-delete" title="Delete Question">
                                                🗑️ Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <!-- Demo data when no questions exist -->
                                <tr>
                                    <td>#001</td>
                                    <td class="question-text">What is object-oriented programming and what are its main principles?</td>
                                    <td><span class="category-badge category-programming">Programming</span></td>
                                    <td><span class="difficulty-badge difficulty-medium">Medium</span></td>
                                    <td>{{ now()->subDays(5)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-edit">✏️ Edit</a>
                                            <button onclick="deleteQuestion(1)" class="action-btn action-btn-delete">🗑️ Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#002</td>
                                    <td class="question-text">Define data structures and algorithms. Explain the difference between them.</td>
                                    <td><span class="category-badge category-data-structures">Data Structures</span></td>
                                    <td><span class="difficulty-badge difficulty-hard">Hard</span></td>
                                    <td>{{ now()->subDays(3)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-edit">✏️ Edit</a>
                                            <button onclick="deleteQuestion(2)" class="action-btn action-btn-delete">🗑️ Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#003</td>
                                    <td class="question-text">Explain database normalization and its importance in database design.</td>
                                    <td><span class="category-badge category-database">Database</span></td>
                                    <td><span class="difficulty-badge difficulty-medium">Medium</span></td>
                                    <td>{{ now()->subDays(2)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-edit">✏️ Edit</a>
                                            <button onclick="deleteQuestion(3)" class="action-btn action-btn-delete">🗑️ Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#004</td>
                                    <td class="question-text">What is network topology? Describe different types of network topologies.</td>
                                    <td><span class="category-badge category-networking">Networking</span></td>
                                    <td><span class="difficulty-badge difficulty-easy">Easy</span></td>
                                    <td>{{ now()->subDay()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-edit">✏️ Edit</a>
                                            <button onclick="deleteQuestion(4)" class="action-btn action-btn-delete">🗑️ Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#005</td>
                                    <td class="question-text">Define software engineering and explain the software development life cycle.</td>
                                    <td><span class="category-badge category-software-engineering">Software Eng</span></td>
                                    <td><span class="difficulty-badge difficulty-medium">Medium</span></td>
                                    <td>{{ now()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-edit">✏️ Edit</a>
                                            <button onclick="deleteQuestion(5)" class="action-btn action-btn-delete">🗑️ Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="pagination">
                            <div class="pagination-info">
                                Showing 1-5 of {{ $totalQuestions ?? 87 }} questions
                            </div>
                            <div class="pagination-controls">
                                <a href="#" class="page-btn">← Previous</a>
                                <a href="#" class="page-btn active">1</a>
                                <a href="#" class="page-btn">2</a>
                                <a href="#" class="page-btn">3</a>
                                <span class="page-btn" style="border: none; cursor: default;">...</span>
                                <a href="#" class="page-btn">18</a>
                                <a href="#" class="page-btn">Next →</a>
                            </div>
                        </div>
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

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.data-table tbody tr');
            
            rows.forEach(row => {
                const questionText = row.querySelector('.question-text').textContent.toLowerCase();
                if (questionText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Category filter
        document.getElementById('categoryFilter').addEventListener('change', function(e) {
            const selectedCategory = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.data-table tbody tr');
            
            rows.forEach(row => {
                const categoryElement = row.querySelector('.category-badge');
                if (!categoryElement) return;
                
                const rowCategory = categoryElement.textContent.toLowerCase().replace(' ', '-');
                if (!selectedCategory || rowCategory.includes(selectedCategory)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

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
                // In a real application, this would make an AJAX request to delete the question
                console.log('Deleting question with ID:', questionToDelete);
                alert('Question deleted successfully! (Demo mode)');
                closeDeleteModal();
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

        .category-programming { background: #dbeafe; color: #1e40af; }
        .category-database { background: #dcfce7; color: #166534; }
        .category-networking { background: #fef3c7; color: #92400e; }
        .category-software-engineering { background: #f3e8ff; color: #7c3aed; }
        .category-data-structures { background: #fce7f3; color: #be185d; }

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
    </style>
</body>
</html>