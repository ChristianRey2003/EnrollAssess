<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($exam) ? 'Edit Exam' : 'Create New Exam' }} - {{ config('app.name', 'EnrollAssess') }}</title>

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
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <x-admin-header 
                :title="isset($exam) ? 'Edit Exam' : 'Create New Exam'" 
                :subtitle="isset($exam) ? 'Modify existing exam details' : 'Set up a new examination template for your institution'" />

            <!-- Content -->
            <div class="main-content">
                <!-- Breadcrumb -->
                <div class="breadcrumb">
                    <a href="{{ route('admin.sets-questions.index') }}" class="breadcrumb-link">Exams</a>
                    <span class="breadcrumb-separator">›</span>
                    <span class="breadcrumb-current">{{ isset($exam) ? 'Edit Exam' : 'Create New Exam' }}</span>
                </div>

                <!-- Exam Form -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Exam Details</h2>
                        <div class="form-actions-header">
                            <a href="{{ route('admin.sets-questions.index') }}" class="btn-secondary">
                                ← Back to Exams
                            </a>
                        </div>
                    </div>
                    <div class="section-content">
                        <form method="POST" action="{{ isset($exam) ? route('admin.exams.update', $exam->exam_id) : route('admin.exams.store') }}" id="examForm" class="exam-form">
                            @csrf
                            @if(isset($exam))
                                @method('PUT')
                            @endif

                            <!-- Exam Title -->
                            <div class="form-group">
                                <label for="title" class="form-label required">Exam Title</label>
                                <input type="text" 
                                       id="title" 
                                       name="title" 
                                       class="form-control @error('title') is-invalid @enderror"
                                       placeholder="e.g., BSIT Entrance Examination 2024"
                                       value="{{ old('title', $exam->title ?? '') }}"
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-help">Enter a clear, descriptive title for this examination.</div>
                            </div>

                            <!-- Exam Description -->
                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" 
                                         name="description" 
                                         class="form-textarea @error('description') is-invalid @enderror"
                                         rows="4"
                                         placeholder="Provide a detailed description of this examination, its purpose, and coverage...">{{ old('description', $exam->description ?? '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-help">Optional description to help identify and explain this exam.</div>
                            </div>

                            <!-- Duration -->
                            <div class="form-group">
                                <label for="duration_minutes" class="form-label required">Duration (Minutes)</label>
                                <div class="duration-input-group">
                                    <input type="number" 
                                           id="duration_minutes" 
                                           name="duration_minutes" 
                                           class="form-control duration-input @error('duration_minutes') is-invalid @enderror"
                                           min="5" 
                                           max="480" 
                                           value="{{ old('duration_minutes', $exam->duration_minutes ?? 90) }}"
                                           required>
                                    <span class="duration-label">minutes</span>
                                    <div class="duration-presets">
                                        <button type="button" onclick="setDuration(30)" class="preset-btn">30 min</button>
                                        <button type="button" onclick="setDuration(60)" class="preset-btn">1 hour</button>
                                        <button type="button" onclick="setDuration(90)" class="preset-btn">1.5 hours</button>
                                        <button type="button" onclick="setDuration(120)" class="preset-btn">2 hours</button>
                                    </div>
                                </div>
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-help">Set the time limit for this examination (5 minutes to 8 hours).</div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="button" onclick="previewExam()" class="btn-secondary">
                                    👁️ Preview Exam
                                </button>
                                <button type="submit" class="btn-primary" id="saveButton">
                                    💾 {{ isset($exam) ? 'Update Exam' : 'Create Exam' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="modal-overlay" style="display: none;">
        <div class="modal-content preview-modal">
            <div class="modal-header">
                <h3>Exam Preview</h3>
                <button onclick="closePreviewModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <div class="preview-exam">
                    <div class="preview-title" id="previewTitle"></div>
                    <div class="preview-description" id="previewDescription"></div>
                    <div class="preview-duration" id="previewDuration"></div>
                    <div class="preview-info">
                        <p><strong>Note:</strong> After creating this exam, you'll be able to:</p>
                        <ul>
                            <li>Create multiple question sets (Set A, Set B, etc.)</li>
                            <li>Add questions to each set</li>
                            <li>Assign different sets to different applicants</li>
                            <li>Monitor exam progress and results</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closePreviewModal()" class="btn-secondary">Close Preview</button>
                <button onclick="submitForm()" class="btn-primary">Looks Good - {{ isset($exam) ? 'Update' : 'Create' }} Exam</button>
            </div>
        </div>
    </div>

    <script>
        // Duration preset functions
        function setDuration(minutes) {
            document.getElementById('duration_minutes').value = minutes;
        }

        // Form validation and preview
        function previewExam() {
            const title = document.getElementById('title').value;
            const description = document.getElementById('description').value;
            const duration = document.getElementById('duration_minutes').value;

            // Validate
            if (!title.trim()) {
                alert('Please enter the exam title.');
                return;
            }
            
            if (!duration || duration < 5 || duration > 480) {
                alert('Please enter a valid duration between 5 and 480 minutes.');
                return;
            }

            // Update preview
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewDescription').textContent = description || 'No description provided';
            
            // Format duration
            const hours = Math.floor(duration / 60);
            const minutes = duration % 60;
            let durationText = '';
            if (hours > 0) {
                durationText = `${hours} hour${hours > 1 ? 's' : ''}`;
                if (minutes > 0) {
                    durationText += ` and ${minutes} minute${minutes > 1 ? 's' : ''}`;
                }
            } else {
                durationText = `${minutes} minute${minutes > 1 ? 's' : ''}`;
            }
            document.getElementById('previewDuration').innerHTML = `<strong>Duration:</strong> ${durationText}`;

            // Show modal
            document.getElementById('previewModal').style.display = 'flex';
        }

        function closePreviewModal() {
            document.getElementById('previewModal').style.display = 'none';
        }

        function submitForm() {
            closePreviewModal();
            document.getElementById('examForm').submit();
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
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', autoSave);
            });
            
            // Set up form validation
            const form = document.getElementById('examForm');
            form.addEventListener('submit', function(e) {
                const saveButton = document.getElementById('saveButton');
                saveButton.disabled = true;
                saveButton.innerHTML = '💾 {{ isset($exam) ? "Updating..." : "Creating..." }}';
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
        /* Additional styles for exam form */
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--text-gray);
        }

        .breadcrumb-link {
            color: var(--maroon-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-link:hover {
            text-decoration: underline;
        }

        .breadcrumb-separator {
            margin: 0 8px;
            color: var(--text-gray);
        }

        .breadcrumb-current {
            color: var(--text-gray);
        }

        .exam-form {
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
            min-height: 120px;
            transition: var(--transition);
        }

        .form-textarea:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        .duration-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .duration-input {
            width: 120px;
        }

        .duration-label {
            color: var(--text-gray);
            font-size: 14px;
        }

        .duration-presets {
            display: flex;
            gap: 5px;
            margin-left: 10px;
        }

        .preset-btn {
            padding: 6px 12px;
            background: var(--light-gray);
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: var(--transition);
        }

        .preset-btn:hover {
            background: var(--yellow-light);
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

        .form-actions-header {
            display: flex;
            gap: 10px;
        }

        .btn-secondary {
            padding: 12px 24px;
            background: var(--light-gray);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: var(--border-gray);
        }

        .btn-primary {
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            color: var(--white);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--yellow-primary) 0%, var(--yellow-dark) 100%);
            color: var(--maroon-primary);
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Preview Modal Styles */
        .preview-modal {
            max-width: 600px;
        }

        .preview-exam {
            padding: 20px;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .preview-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 10px;
        }

        .preview-description {
            color: var(--text-gray);
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .preview-duration {
            color: var(--text-dark);
            margin-bottom: 20px;
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
            .exam-form {
                padding: 20px;
            }

            .duration-input-group {
                flex-direction: column;
                align-items: flex-start;
            }

            .duration-presets {
                margin-left: 0;
                margin-top: 10px;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>