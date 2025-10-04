<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($examSet) ? 'Edit Exam Set' : 'Create New Exam Set' }} - {{ config('app.name', 'EnrollAssess') }}</title>

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
                    <h1>{{ isset($examSet) ? 'Edit Exam Set' : 'Create New Exam Set' }}</h1>
                    <p class="header-subtitle">{{ isset($examSet) ? 'Modify exam set details' : 'Create a new question set for ' . $exam->title }}</p>
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
                    <a href="{{ route('admin.exam-sets.index', $exam->exam_id) }}" class="breadcrumb-link">Question Sets</a>
                    <span class="breadcrumb-separator">›</span>
                    <span class="breadcrumb-current">{{ isset($examSet) ? 'Edit Set' : 'Create New Set' }}</span>
                </div>

                <!-- Exam Set Form -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Set Details</h2>
                        <div class="form-actions-header">
                            <a href="{{ route('admin.exam-sets.index', $exam->exam_id) }}" class="btn-secondary">
                                ← Back to Sets
                            </a>
                        </div>
                    </div>
                    <div class="section-content">
                        <form method="POST" action="{{ isset($examSet) ? route('admin.exam-sets.update', [$exam->exam_id, $examSet->exam_set_id]) : route('admin.exam-sets.store', $exam->exam_id) }}" id="examSetForm" class="exam-set-form">
                            @csrf
                            @if(isset($examSet))
                                @method('PUT')
                            @endif

                            <!-- Set Name -->
                            <div class="form-group">
                                <label for="set_name" class="form-label required">Set Name</label>
                                <input type="text" 
                                       id="set_name" 
                                       name="set_name" 
                                       class="form-control @error('set_name') is-invalid @enderror"
                                       placeholder="e.g., BSIT-2024-SET-A, Morning Session, Set B"
                                       value="{{ old('set_name', $examSet->set_name ?? '') }}"
                                       required>
                                @error('set_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-help">Enter a unique name to identify this question set.</div>
                            </div>

                            <!-- Set Description -->
                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" 
                                         name="description" 
                                         class="form-textarea @error('description') is-invalid @enderror"
                                         rows="3"
                                         placeholder="Describe the purpose or characteristics of this question set...">{{ old('description', $examSet->description ?? '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-help">Optional description to help identify this set.</div>
                            </div>

                            @if(!isset($examSet) && $existingSets && $existingSets->count() > 0)
                            <!-- Copy from existing set -->
                            <div class="form-group">
                                <label for="copy_from_set" class="form-label">Copy Questions From Existing Set (Optional)</label>
                                <select id="copy_from_set" name="copy_from_set" class="form-select @error('copy_from_set') is-invalid @enderror">
                                    <option value="">Start with empty set</option>
                                    @foreach($existingSets as $existingSet)
                                        <option value="{{ $existingSet->exam_set_id }}" {{ old('copy_from_set') == $existingSet->exam_set_id ? 'selected' : '' }}>
                                            {{ $existingSet->set_name }} ({{ $existingSet->questions->count() }} questions)
                                        </option>
                                    @endforeach
                                </select>
                                @error('copy_from_set')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-help">Select an existing set to copy all its questions to this new set.</div>
                            </div>
                            @endif

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="button" onclick="previewSet()" class="btn-secondary">
                                    👁️ Preview Set
                                </button>
                                <button type="submit" class="btn-primary" id="saveButton">
                                    💾 {{ isset($examSet) ? 'Update Set' : 'Create Set' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if(isset($examSet) && $examSet->questions->count() > 0)
                <!-- Existing Questions Preview -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Current Questions</h2>
                        <div class="section-actions">
                            <span class="stat-badge">{{ $examSet->questions->count() }} questions</span>
                        </div>
                    </div>
                    <div class="section-content">
                        <div class="questions-preview">
                            @foreach($examSet->questions->take(5) as $question)
                            <div class="question-preview-item">
                                <div class="question-text">{{ Str::limit($question->question_text, 80) }}</div>
                                <div class="question-meta">
                                    <span class="type-badge type-{{ str_replace('_', '-', $question->question_type) }}">
                                        {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                                    </span>
                                    <span class="points-badge">{{ $question->points }} pts</span>
                                </div>
                            </div>
                            @endforeach

                            @if($examSet->questions->count() > 5)
                            <div class="more-questions">
                                <span>... and {{ $examSet->questions->count() - 5 }} more questions</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="modal-overlay" style="display: none;">
        <div class="modal-content preview-modal">
            <div class="modal-header">
                <h3>Exam Set Preview</h3>
                <button onclick="closePreviewModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <div class="preview-set">
                    <div class="preview-name" id="previewName"></div>
                    <div class="preview-description" id="previewDescription"></div>
                    <div class="preview-exam">
                        <strong>Exam:</strong> {{ $exam->title }}
                    </div>
                    <div class="preview-info">
                        <p><strong>After creating this set, you'll be able to:</strong></p>
                        <ul>
                            <li>Add questions from the question bank</li>
                            <li>Create new questions specifically for this set</li>
                            <li>Reorder questions within the set</li>
                            <li>Assign this set to different applicants</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closePreviewModal()" class="btn-secondary">Close Preview</button>
                <button onclick="submitForm()" class="btn-primary">{{ isset($examSet) ? 'Update' : 'Create' }} Set</button>
            </div>
        </div>
    </div>

    <script>
        // Form validation and preview
        function previewSet() {
            const setName = document.getElementById('set_name').value;
            const description = document.getElementById('description').value;

            // Validate
            if (!setName.trim()) {
                alert('Please enter the set name.');
                return;
            }

            // Update preview
            document.getElementById('previewName').textContent = setName;
            document.getElementById('previewDescription').textContent = description || 'No description provided';

            // Show modal
            document.getElementById('previewModal').style.display = 'flex';
        }

        function closePreviewModal() {
            document.getElementById('previewModal').style.display = 'none';
        }

        function submitForm() {
            closePreviewModal();
            document.getElementById('examSetForm').submit();
        }

        // Auto-save draft functionality
        let saveTimeout;
        function autoSave() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                console.log('Auto-saving draft...');
            }, 3000);
        }

        // Add auto-save listeners
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', autoSave);
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closePreviewModal();
            }
        });
    </script>

    <style>
        /* Form styles */
        .exam-set-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-label.required::after {
            content: ' *';
            color: #dc2626;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        .form-textarea {
            width: 100%;
            padding: 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            line-height: 1.5;
            resize: vertical;
            min-height: 90px;
            transition: var(--transition);
        }

        .form-textarea:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            background: var(--white);
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--yellow-primary);
        }

        .form-help {
            font-size: 12px;
            color: var(--text-gray);
            margin-top: 6px;
            font-style: italic;
        }

        .invalid-feedback {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
            font-weight: 500;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid var(--border-gray);
            margin-top: 30px;
        }

        /* Questions preview */
        .questions-preview {
            padding: 20px;
        }

        .question-preview-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .question-text {
            flex: 1;
            color: var(--text-dark);
            font-size: 14px;
        }

        .question-meta {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .type-badge {
            padding: 3px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .type-multiple-choice { background: #dbeafe; color: #1e40af; }
        .type-true-false { background: #dcfce7; color: #166534; }
        .type-short-answer { background: #fef3c7; color: #92400e; }
        .type-essay { background: #f3e8ff; color: #7c3aed; }

        .points-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 3px 6px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }

        .more-questions {
            text-align: center;
            color: var(--text-gray);
            font-style: italic;
            font-size: 14px;
            padding: 12px;
        }

        /* Preview modal */
        .preview-modal {
            max-width: 500px;
        }

        .preview-set {
            padding: 20px;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .preview-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 8px;
        }

        .preview-description {
            color: var(--text-gray);
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .preview-exam {
            color: var(--text-dark);
            margin-bottom: 16px;
        }

        .preview-info {
            background: var(--white);
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid var(--yellow-primary);
        }

        .preview-info ul {
            margin: 10px 0 0 20px;
            color: var(--text-gray);
        }

        .preview-info li {
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .exam-set-form {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .question-preview-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</body>
</html>