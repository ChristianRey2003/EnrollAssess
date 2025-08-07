<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($applicant) ? 'Edit Applicant' : 'Add New Applicant' }} - {{ config('app.name', 'EnrollAssess') }}</title>

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
                    <a href="{{ route('admin.exams.index') }}" class="nav-link">
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
                    <a href="{{ route('admin.applicants') }}" class="nav-link active">
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
                    <h1>{{ isset($applicant) ? 'Edit Applicant' : 'Add New Applicant' }}</h1>
                    <p class="header-subtitle">{{ isset($applicant) ? 'Modify applicant information and settings' : 'Add an individual applicant to the system' }}</p>
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
                    <a href="{{ route('admin.applicants') }}" class="breadcrumb-link">Applicants</a>
                    <span class="breadcrumb-separator">›</span>
                    <span class="breadcrumb-current">{{ isset($applicant) ? 'Edit' : 'Add New' }}</span>
                </div>

                <!-- Applicant Form -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">{{ isset($applicant) ? 'Edit' : 'Applicant' }} Information</h2>
                        <div class="form-actions-header">
                            <a href="{{ route('admin.applicants') }}" class="btn-secondary">
                                ← Back to Applicants
                            </a>
                        </div>
                    </div>
                    <div class="section-content">
                        <form method="POST" action="{{ isset($applicant) ? route('admin.applicants.update', $applicant->applicant_id) : route('admin.applicants.store') }}" id="applicantForm" class="applicant-form">
                            @csrf
                            @if(isset($applicant))
                                @method('PUT')
                            @endif

                            <div class="form-grid">
                                <!-- Personal Information Section -->
                                <div class="form-section">
                                    <h3 class="section-subtitle">Personal Information</h3>
                                    
                                    <!-- Full Name -->
                                    <div class="form-group">
                                        <label for="full_name" class="form-label required">Full Name</label>
                                        <input type="text" 
                                               id="full_name" 
                                               name="full_name" 
                                               class="form-control @error('full_name') is-invalid @enderror"
                                               placeholder="e.g., Juan A. Dela Cruz"
                                               value="{{ old('full_name', $applicant->full_name ?? '') }}"
                                               required>
                                        @error('full_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-help">Enter the applicant's complete name as it appears on official documents.</div>
                                    </div>

                                    <!-- Email Address -->
                                    <div class="form-group">
                                        <label for="email_address" class="form-label required">Email Address</label>
                                        <input type="email" 
                                               id="email_address" 
                                               name="email_address" 
                                               class="form-control @error('email_address') is-invalid @enderror"
                                               placeholder="e.g., juan.delacruz@email.com"
                                               value="{{ old('email_address', $applicant->email_address ?? '') }}"
                                               required>
                                        @error('email_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-help">Email must be unique and will be used for access code delivery.</div>
                                    </div>

                                    <!-- Phone Number -->
                                    <div class="form-group">
                                        <label for="phone_number" class="form-label">Phone Number</label>
                                        <input type="tel" 
                                               id="phone_number" 
                                               name="phone_number" 
                                               class="form-control @error('phone_number') is-invalid @enderror"
                                               placeholder="e.g., 09123456789"
                                               value="{{ old('phone_number', $applicant->phone_number ?? '') }}">
                                        @error('phone_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-help">Optional contact number for communication.</div>
                                    </div>

                                    <!-- Address -->
                                    <div class="form-group">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea id="address" 
                                                 name="address" 
                                                 class="form-textarea @error('address') is-invalid @enderror"
                                                 rows="3"
                                                 placeholder="Complete address including barangay, city, and province">{{ old('address', $applicant->address ?? '') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-help">Complete residential address of the applicant.</div>
                                    </div>

                                    <!-- Education Background -->
                                    <div class="form-group">
                                        <label for="education_background" class="form-label">Educational Background</label>
                                        <input type="text" 
                                               id="education_background" 
                                               name="education_background" 
                                               class="form-control @error('education_background') is-invalid @enderror"
                                               placeholder="e.g., Senior High School Graduate - STEM"
                                               value="{{ old('education_background', $applicant->education_background ?? '') }}">
                                        @error('education_background')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-help">Previous educational attainment and track/strand if applicable.</div>
                                    </div>
                                </div>

                                <!-- Exam Assignment Section -->
                                <div class="form-section">
                                    <h3 class="section-subtitle">Exam Assignment</h3>
                                    
                                    <!-- Exam Set -->
                                    <div class="form-group">
                                        <label for="exam_set_id" class="form-label">Assigned Exam Set</label>
                                        <select id="exam_set_id" name="exam_set_id" class="form-select @error('exam_set_id') is-invalid @enderror">
                                            <option value="">No exam set assigned</option>
                                            @foreach($examSets as $examSet)
                                                <option value="{{ $examSet->exam_set_id }}" 
                                                        {{ old('exam_set_id', $applicant->exam_set_id ?? '') == $examSet->exam_set_id ? 'selected' : '' }}>
                                                    {{ $examSet->exam->title }} - {{ $examSet->set_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('exam_set_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-help">Assign the applicant to a specific exam set. Leave blank to assign later.</div>
                                    </div>

                                    @if(isset($applicant))
                                    <!-- Status (Edit Only) -->
                                    <div class="form-group">
                                        <label for="status" class="form-label">Status</label>
                                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                            <option value="pending" {{ old('status', $applicant->status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="exam-completed" {{ old('status', $applicant->status ?? '') == 'exam-completed' ? 'selected' : '' }}>Exam Completed</option>
                                            <option value="interview-scheduled" {{ old('status', $applicant->status ?? '') == 'interview-scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                                            <option value="interview-completed" {{ old('status', $applicant->status ?? '') == 'interview-completed' ? 'selected' : '' }}>Interview Completed</option>
                                            <option value="admitted" {{ old('status', $applicant->status ?? '') == 'admitted' ? 'selected' : '' }}>Admitted</option>
                                            <option value="rejected" {{ old('status', $applicant->status ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-help">Current status of the applicant in the admission process.</div>
                                    </div>

                                    <!-- Score (Edit Only) -->
                                    <div class="form-group">
                                        <label for="score" class="form-label">Exam Score</label>
                                        <input type="number" 
                                               id="score" 
                                               name="score" 
                                               class="form-control @error('score') is-invalid @enderror"
                                               step="0.01"
                                               min="0"
                                               max="9999.99"
                                               placeholder="e.g., 85.50"
                                               value="{{ old('score', $applicant->score ?? '') }}">
                                        @error('score')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-help">Final exam score. Usually auto-calculated after exam completion.</div>
                                    </div>
                                    @endif

                                    @if(!isset($applicant))
                                    <!-- Access Code Generation (Create Only) -->
                                    <div class="form-group">
                                        <div class="checkbox-group">
                                            <input type="checkbox" id="generate_access_code" name="generate_access_code" checked>
                                            <label for="generate_access_code" class="checkbox-label">
                                                Generate access code automatically
                                            </label>
                                        </div>
                                        <div class="form-help">Recommended: Generate a unique access code for this applicant immediately.</div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Current Access Code (Edit Only) -->
                            @if(isset($applicant) && $applicant->accessCode)
                            <div class="content-section" style="margin-top: 30px;">
                                <div class="section-header">
                                    <h3 class="section-subtitle">Access Code Information</h3>
                                </div>
                                <div class="access-code-info">
                                    <div class="access-code-display">
                                        <label>Current Access Code:</label>
                                        <code class="access-code">{{ $applicant->accessCode->code }}</code>
                                        <span class="code-status {{ $applicant->accessCode->is_used ? 'used' : 'active' }}">
                                            {{ $applicant->accessCode->is_used ? 'Used' : 'Active' }}
                                        </span>
                                    </div>
                                    @if($applicant->accessCode->expires_at)
                                    <div class="access-code-expiry">
                                        <label>Expires:</label>
                                        <span>{{ $applicant->accessCode->expires_at->format('M d, Y g:i A') }}</span>
                                    </div>
                                    @endif
                                    @if($applicant->accessCode->used_at)
                                    <div class="access-code-used">
                                        <label>Used:</label>
                                        <span>{{ $applicant->accessCode->used_at->format('M d, Y g:i A') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="button" onclick="previewApplicant()" class="btn-secondary">
                                    👁️ Preview Information
                                </button>
                                <button type="submit" class="btn-primary" id="saveButton">
                                    💾 {{ isset($applicant) ? 'Update Applicant' : 'Add Applicant' }}
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
                <h3>Applicant Information Preview</h3>
                <button onclick="closePreviewModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <div class="preview-applicant">
                    <div class="preview-section">
                        <h4>Personal Information</h4>
                        <div id="previewPersonal"></div>
                    </div>
                    <div class="preview-section">
                        <h4>Exam Assignment</h4>
                        <div id="previewExam"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closePreviewModal()" class="btn-secondary">Close Preview</button>
                <button onclick="submitForm()" class="btn-primary">Looks Good - {{ isset($applicant) ? 'Update' : 'Add' }} Applicant</button>
            </div>
        </div>
    </div>

    <script>
        // Form validation and preview
        function previewApplicant() {
            const fullName = document.getElementById('full_name').value;
            const email = document.getElementById('email_address').value;
            const phone = document.getElementById('phone_number').value;
            const address = document.getElementById('address').value;
            const education = document.getElementById('education_background').value;
            const examSetSelect = document.getElementById('exam_set_id');
            const examSetText = examSetSelect.options[examSetSelect.selectedIndex].text;
            const generateCode = document.getElementById('generate_access_code')?.checked || false;

            // Basic validation
            if (!fullName.trim()) {
                alert('Please enter the applicant\'s full name.');
                return;
            }
            
            if (!email.trim()) {
                alert('Please enter a valid email address.');
                return;
            }

            // Update preview content
            const personalInfo = `
                <div class="preview-item"><strong>Full Name:</strong> ${fullName}</div>
                <div class="preview-item"><strong>Email:</strong> ${email}</div>
                <div class="preview-item"><strong>Phone:</strong> ${phone || 'Not provided'}</div>
                <div class="preview-item"><strong>Address:</strong> ${address || 'Not provided'}</div>
                <div class="preview-item"><strong>Education:</strong> ${education || 'Not provided'}</div>
            `;

            const examInfo = `
                <div class="preview-item"><strong>Exam Set:</strong> ${examSetSelect.value ? examSetText : 'No assignment'}</div>
                ${!{{ isset($applicant) ? 'true' : 'false' }} ? `<div class="preview-item"><strong>Access Code:</strong> ${generateCode ? 'Will be generated' : 'Will not be generated'}</div>` : ''}
            `;

            document.getElementById('previewPersonal').innerHTML = personalInfo;
            document.getElementById('previewExam').innerHTML = examInfo;

            // Show modal
            document.getElementById('previewModal').style.display = 'flex';
        }

        function closePreviewModal() {
            document.getElementById('previewModal').style.display = 'none';
        }

        function submitForm() {
            closePreviewModal();
            document.getElementById('applicantForm').submit();
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
            
            // Set up form validation
            const form = document.getElementById('applicantForm');
            form.addEventListener('submit', function(e) {
                const saveButton = document.getElementById('saveButton');
                saveButton.disabled = true;
                saveButton.innerHTML = '💾 {{ isset($applicant) ? "Updating..." : "Adding..." }}';
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
        /* Additional styles for applicant form */
        .applicant-form {
            padding: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .form-section {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--border-gray);
        }

        .section-subtitle {
            margin: 0 0 20px 0;
            color: var(--maroon-primary);
            font-size: 16px;
            font-weight: 600;
            border-bottom: 2px solid var(--yellow-primary);
            padding-bottom: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-label.required::after {
            content: ' *';
            color: #dc2626;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
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
            min-height: 80px;
            transition: var(--transition);
        }

        .form-textarea:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-label {
            color: var(--text-dark);
            font-weight: 500;
            cursor: pointer;
        }

        .form-help {
            font-size: 12px;
            color: var(--text-gray);
            margin-top: 4px;
            font-style: italic;
        }

        .invalid-feedback {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
            font-weight: 500;
        }

        .access-code-info {
            padding: 20px;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .access-code-display, .access-code-expiry, .access-code-used {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .access-code-display label, .access-code-expiry label, .access-code-used label {
            font-weight: 600;
            color: var(--maroon-primary);
            min-width: 120px;
        }

        .access-code {
            background: var(--white);
            padding: 6px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 14px;
            border: 1px solid var(--border-gray);
        }

        .code-status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .code-status.used { background: #ffebee; color: #d32f2f; }
        .code-status.active { background: #e8f5e8; color: #2e7d32; }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid var(--border-gray);
            margin-top: 30px;
        }

        /* Preview Modal Styles */
        .preview-modal {
            max-width: 600px;
        }

        .preview-applicant {
            padding: 20px;
        }

        .preview-section {
            margin-bottom: 25px;
        }

        .preview-section h4 {
            margin: 0 0 12px 0;
            color: var(--maroon-primary);
            font-size: 16px;
            border-bottom: 1px solid var(--border-gray);
            padding-bottom: 6px;
        }

        .preview-item {
            padding: 8px 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .preview-item:last-child {
            border-bottom: none;
        }

        .preview-item strong {
            color: var(--maroon-primary);
            display: inline-block;
            min-width: 120px;
        }

        @media (max-width: 768px) {
            .applicant-form {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .form-section {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .access-code-display, .access-code-expiry, .access-code-used {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
        }
    </style>
</body>
</html>