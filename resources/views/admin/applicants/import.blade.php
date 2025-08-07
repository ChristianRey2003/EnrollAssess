<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Import Applicants - {{ config('app.name', 'EnrollAssess') }}</title>

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
                    <h1>Import Applicants</h1>
                    <p class="header-subtitle">Bulk import applicants from a CSV file with automatic access code generation</p>
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
                    <span class="breadcrumb-current">Import</span>
                </div>

                <!-- Instructions Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Import Instructions</h2>
                    </div>
                    <div class="section-content">
                        <div class="import-instructions">
                            <div class="instruction-step">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h3>Download Template</h3>
                                    <p>Start by downloading our CSV template with the correct format and sample data.</p>
                                    <a href="{{ route('admin.applicants.download-template') }}" class="btn-secondary">
                                        📥 Download CSV Template
                                    </a>
                                </div>
                            </div>
                            
                            <div class="instruction-step">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h3>Prepare Your Data</h3>
                                    <p>Fill in the template with your applicant data. Required columns:</p>
                                    <ul class="required-columns">
                                        <li><strong>full_name</strong> - Complete name of the applicant</li>
                                        <li><strong>email_address</strong> - Valid email address (must be unique)</li>
                                        <li><strong>phone_number</strong> - Contact number (optional)</li>
                                        <li><strong>address</strong> - Full address (optional)</li>
                                        <li><strong>education_background</strong> - Educational background (optional)</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="instruction-step">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h3>Configure Import Settings</h3>
                                    <p>Choose your import settings below, then upload your CSV file.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Import Form Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Upload CSV File</h2>
                    </div>
                    <div class="section-content">
                        <form id="importForm" enctype="multipart/form-data" class="import-form">
                            @csrf
                            
                            <!-- File Upload -->
                            <div class="form-group">
                                <label for="csv_file" class="form-label required">CSV File</label>
                                <div class="file-upload-area" id="fileUploadArea">
                                    <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" required class="file-input">
                                    <div class="upload-content">
                                        <div class="upload-icon">📄</div>
                                        <div class="upload-text">
                                            <strong>Click to browse</strong> or drag and drop your CSV file here
                                        </div>
                                        <div class="upload-hint">Maximum file size: 2MB</div>
                                    </div>
                                </div>
                                <div id="fileInfo" class="file-info" style="display: none;"></div>
                            </div>

                            <!-- Exam Set Assignment -->
                            <div class="form-group">
                                <label for="exam_set_id" class="form-label">Default Exam Set (Optional)</label>
                                <select id="exam_set_id" name="exam_set_id" class="form-control">
                                    <option value="">Assign exam sets later</option>
                                    @foreach($examSets as $examSet)
                                        <option value="{{ $examSet->exam_set_id }}">
                                            {{ $examSet->exam->title }} - {{ $examSet->set_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-help">All imported applicants will be assigned to this exam set. You can change assignments later.</div>
                            </div>

                            <!-- Access Code Settings -->
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="generate_access_codes" name="generate_access_codes" checked>
                                    <label for="generate_access_codes" class="checkbox-label">
                                        Generate access codes automatically
                                    </label>
                                </div>
                                <div class="form-help">Recommended: Generate unique access codes for each applicant during import.</div>
                            </div>

                            <div class="form-group" id="accessCodeSettings">
                                <label for="access_code_expiry_hours" class="form-label">Access Code Expiry (Hours)</label>
                                <div class="duration-input-group">
                                    <input type="number" id="access_code_expiry_hours" name="access_code_expiry_hours" class="form-control duration-input" value="72" min="1" max="720">
                                    <span class="duration-label">hours</span>
                                    <div class="duration-presets">
                                        <button type="button" onclick="setExpiry(24)" class="preset-btn">1 day</button>
                                        <button type="button" onclick="setExpiry(72)" class="preset-btn">3 days</button>
                                        <button type="button" onclick="setExpiry(168)" class="preset-btn">1 week</button>
                                        <button type="button" onclick="setExpiry(720)" class="preset-btn">30 days</button>
                                    </div>
                                </div>
                                <div class="form-help">Access codes will expire after this duration. Default: 72 hours (3 days).</div>
                            </div>

                            <!-- Import Actions -->
                            <div class="form-actions">
                                <a href="{{ route('admin.applicants') }}" class="btn-secondary">
                                    ← Back to Applicants
                                </a>
                                <button type="button" onclick="previewImport()" class="btn-secondary" id="previewBtn" disabled>
                                    👁️ Preview Import
                                </button>
                                <button type="submit" class="btn-primary" id="importBtn" disabled>
                                    📤 Start Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="content-section" id="previewSection" style="display: none;">
                    <div class="section-header">
                        <h2 class="section-title">Import Preview</h2>
                    </div>
                    <div class="section-content">
                        <div id="previewContent"></div>
                    </div>
                </div>

                <!-- Import Progress Section -->
                <div class="content-section" id="progressSection" style="display: none;">
                    <div class="section-header">
                        <h2 class="section-title">Import Progress</h2>
                    </div>
                    <div class="section-content">
                        <div class="import-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" id="progressFill"></div>
                            </div>
                            <div class="progress-text" id="progressText">Starting import...</div>
                        </div>
                    </div>
                </div>

                <!-- Import Results Section -->
                <div class="content-section" id="resultsSection" style="display: none;">
                    <div class="section-header">
                        <h2 class="section-title">Import Results</h2>
                    </div>
                    <div class="section-content">
                        <div id="resultsContent"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let csvData = null;
        let isImporting = false;

        // File upload handling
        const fileInput = document.getElementById('csv_file');
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInfo = document.getElementById('fileInfo');
        const previewBtn = document.getElementById('previewBtn');
        const importBtn = document.getElementById('importBtn');

        fileInput.addEventListener('change', handleFileSelect);
        
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('drag-over');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('drag-over');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });

        function handleFileSelect() {
            const file = fileInput.files[0];
            
            if (file) {
                // Validate file type
                if (!file.name.toLowerCase().endsWith('.csv') && !file.name.toLowerCase().endsWith('.txt')) {
                    alert('Please select a CSV file (.csv or .txt)');
                    return;
                }

                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    return;
                }

                // Show file info
                fileInfo.innerHTML = `
                    <div class="file-details">
                        <span class="file-name">📄 ${file.name}</span>
                        <span class="file-size">${(file.size / 1024).toFixed(1)} KB</span>
                    </div>
                `;
                fileInfo.style.display = 'block';

                // Enable buttons
                previewBtn.disabled = false;
                importBtn.disabled = false;

                // Read file for preview
                readCSVFile(file);
            }
        }

        function readCSVFile(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const text = e.target.result;
                const lines = text.split('\n').filter(line => line.trim() !== '');
                
                if (lines.length < 2) {
                    alert('CSV file must contain at least a header row and one data row.');
                    return;
                }

                const headers = lines[0].split(',').map(h => h.trim().replace(/"/g, ''));
                const rows = lines.slice(1, 6).map(line => {
                    return line.split(',').map(cell => cell.trim().replace(/"/g, ''));
                });

                csvData = { headers, rows, totalRows: lines.length - 1 };
            };
            reader.readAsText(file);
        }

        function previewImport() {
            if (!csvData) {
                alert('Please select a CSV file first.');
                return;
            }

            const previewSection = document.getElementById('previewSection');
            const previewContent = document.getElementById('previewContent');

            let html = `
                <div class="preview-summary">
                    <div class="summary-item">
                        <strong>Total Records:</strong> ${csvData.totalRows}
                    </div>
                    <div class="summary-item">
                        <strong>Columns Found:</strong> ${csvData.headers.join(', ')}
                    </div>
                </div>
                
                <div class="preview-table">
                    <h4>Sample Data (First 5 rows):</h4>
                    <table class="data-table">
                        <thead>
                            <tr>
                                ${csvData.headers.map(header => `<th>${header}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${csvData.rows.map(row => `
                                <tr>
                                    ${row.map(cell => `<td>${cell}</td>`).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            previewContent.innerHTML = html;
            previewSection.style.display = 'block';
            previewSection.scrollIntoView({ behavior: 'smooth' });
        }

        function setExpiry(hours) {
            document.getElementById('access_code_expiry_hours').value = hours;
        }

        // Toggle access code settings
        document.getElementById('generate_access_codes').addEventListener('change', function() {
            const settings = document.getElementById('accessCodeSettings');
            settings.style.display = this.checked ? 'block' : 'none';
        });

        // Form submission
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (isImporting) {
                return;
            }

            if (!fileInput.files[0]) {
                alert('Please select a CSV file.');
                return;
            }

            startImport();
        });

        function startImport() {
            isImporting = true;
            
            // Show progress section
            const progressSection = document.getElementById('progressSection');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            
            progressSection.style.display = 'block';
            progressFill.style.width = '0%';
            progressText.textContent = 'Starting import...';
            
            // Disable form
            importBtn.disabled = true;
            importBtn.textContent = 'Importing...';

            // Prepare form data
            const formData = new FormData();
            formData.append('csv_file', fileInput.files[0]);
            formData.append('exam_set_id', document.getElementById('exam_set_id').value);
            formData.append('generate_access_codes', document.getElementById('generate_access_codes').checked);
            formData.append('access_code_expiry_hours', document.getElementById('access_code_expiry_hours').value);
            
            // Add CSRF token
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            // Simulate progress
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 30;
                if (progress >= 90) {
                    clearInterval(progressInterval);
                }
                progressFill.style.width = Math.min(progress, 90) + '%';
                progressText.textContent = `Processing... ${Math.min(progress, 90).toFixed(0)}%`;
            }, 200);

            // Submit form
            fetch('/admin/applicants-import', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                progressFill.style.width = '100%';
                progressText.textContent = 'Import completed!';
                
                setTimeout(() => {
                    showResults(data);
                }, 500);
            })
            .catch(error => {
                clearInterval(progressInterval);
                progressFill.style.width = '100%';
                progressText.textContent = 'Import failed!';
                
                setTimeout(() => {
                    showResults({
                        success: false,
                        message: 'An error occurred during import: ' + error.message
                    });
                }, 500);
            })
            .finally(() => {
                isImporting = false;
                importBtn.disabled = false;
                importBtn.textContent = '📤 Start Import';
            });
        }

        function showResults(data) {
            const resultsSection = document.getElementById('resultsSection');
            const resultsContent = document.getElementById('resultsContent');

            let html = '';

            if (data.success) {
                const results = data.results;
                html = `
                    <div class="import-success">
                        <div class="success-icon">✅</div>
                        <div class="success-message">
                            <h3>Import Completed Successfully!</h3>
                            <p>${data.message}</p>
                        </div>
                    </div>
                    
                    <div class="import-stats">
                        <div class="stat-item">
                            <span class="stat-label">Total Processed:</span>
                            <span class="stat-value">${results.total}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Successfully Imported:</span>
                            <span class="stat-value success">${results.successful}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Failed:</span>
                            <span class="stat-value error">${results.failed}</span>
                        </div>
                    </div>
                `;

                if (results.errors && results.errors.length > 0) {
                    html += `
                        <div class="import-errors">
                            <h4>Import Errors:</h4>
                            <ul class="error-list">
                                ${results.errors.map(error => `<li>${error}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }

                html += `
                    <div class="import-actions">
                        <a href="{{ route('admin.applicants') }}" class="btn-primary">
                            View Imported Applicants
                        </a>
                        <button onclick="location.reload()" class="btn-secondary">
                            Import More
                        </button>
                    </div>
                `;
            } else {
                html = `
                    <div class="import-error">
                        <div class="error-icon">❌</div>
                        <div class="error-message">
                            <h3>Import Failed</h3>
                            <p>${data.message}</p>
                        </div>
                    </div>
                    
                    <div class="import-actions">
                        <button onclick="location.reload()" class="btn-primary">
                            Try Again
                        </button>
                    </div>
                `;
            }

            resultsContent.innerHTML = html;
            resultsSection.style.display = 'block';
            resultsSection.scrollIntoView({ behavior: 'smooth' });
        }
    </script>

    <style>
        /* Import page styles */
        .import-instructions {
            padding: 20px;
        }

        .instruction-step {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            align-items: flex-start;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--maroon-primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }

        .step-content h3 {
            margin: 0 0 8px 0;
            color: var(--maroon-primary);
            font-size: 18px;
        }

        .step-content p {
            margin: 0 0 12px 0;
            color: var(--text-gray);
            line-height: 1.5;
        }

        .required-columns {
            margin: 12px 0;
            padding-left: 20px;
        }

        .required-columns li {
            margin-bottom: 6px;
            color: var(--text-dark);
        }

        .import-form {
            padding: 30px;
        }

        .file-upload-area {
            border: 2px dashed var(--border-gray);
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .file-upload-area:hover, .file-upload-area.drag-over {
            border-color: var(--yellow-primary);
            background: var(--yellow-light);
        }

        .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.7;
        }

        .upload-text strong {
            color: var(--maroon-primary);
        }

        .upload-hint {
            color: var(--text-gray);
            font-size: 12px;
            margin-top: 8px;
        }

        .file-info {
            margin-top: 12px;
            padding: 12px;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .file-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .file-name {
            color: var(--text-dark);
            font-weight: 500;
        }

        .file-size {
            color: var(--text-gray);
            font-size: 12px;
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

        .duration-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .duration-input {
            width: 120px;
        }

        .duration-presets {
            display: flex;
            gap: 5px;
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

        .preview-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .summary-item {
            color: var(--text-dark);
        }

        .preview-table {
            padding: 20px;
        }

        .preview-table h4 {
            margin: 0 0 16px 0;
            color: var(--maroon-primary);
        }

        .import-progress {
            padding: 20px;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: var(--light-gray);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--maroon-primary), var(--yellow-primary));
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            text-align: center;
            color: var(--text-dark);
            font-weight: 500;
        }

        .import-success, .import-error {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .import-success {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
        }

        .import-error {
            background: #ffebee;
            border: 1px solid #ffcdd2;
        }

        .success-icon, .error-icon {
            font-size: 32px;
        }

        .success-message h3, .error-message h3 {
            margin: 0 0 4px 0;
            color: var(--text-dark);
        }

        .success-message p, .error-message p {
            margin: 0;
            color: var(--text-gray);
        }

        .import-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 12px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .stat-value.success {
            color: #2e7d32;
        }

        .stat-value.error {
            color: #d32f2f;
        }

        .import-errors {
            margin-bottom: 20px;
            padding: 20px;
            background: #fff3e0;
            border: 1px solid #ffcc02;
            border-radius: 8px;
        }

        .import-errors h4 {
            margin: 0 0 12px 0;
            color: #ef6c00;
        }

        .error-list {
            margin: 0;
            padding-left: 20px;
        }

        .error-list li {
            color: #bf360c;
            margin-bottom: 4px;
        }

        .import-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .instruction-step {
                flex-direction: column;
                gap: 12px;
            }

            .import-form {
                padding: 20px;
            }

            .duration-input-group {
                flex-direction: column;
                align-items: flex-start;
            }

            .duration-presets {
                margin-top: 10px;
            }

            .preview-summary, .import-stats {
                flex-direction: column;
                gap: 12px;
            }

            .import-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>