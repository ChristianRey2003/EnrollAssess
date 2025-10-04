@extends('layouts.admin')

@section('title', 'Import Applicants')
@section('description', 'Bulk import applicants from a CSV file with automatic access code generation')

@php
    $pageTitle = 'Import Applicants';
    $pageSubtitle = 'Bulk import applicants from a CSV file with automatic access code generation';
@endphp

@section('content')
                <!-- Breadcrumb -->
                <div class="breadcrumb">
                    <a href="{{ route('admin.applicants.index') }}" class="breadcrumb-link">Applicants</a>
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
                                        Download CSV Template
                                    </a>
                                </div>
                            </div>
                            
                            <div class="instruction-step">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h3>Prepare Your Data</h3>
                                    <p>Your CSV file should contain these exact column headers (only <strong>First Name</strong>, <strong>Last Name</strong>, and <strong>E-mail</strong> are required):</p>
                                    <ul class="required-columns">
                                        <li><strong>No.</strong> - Row number (auto-generated, can be left empty)</li>
                                        <li><strong>Applicant No.</strong> - Application number (auto-generated if empty)</li>
                                        <li><strong>Preferred Course</strong> - Course preference (optional)</li>
                                        <li><strong>Last Name</strong> - Last name of the applicant (required)</li>
                                        <li><strong>First Name</strong> - First name of the applicant (required)</li>
                                        <li><strong>Middle Name</strong> - Middle name of the applicant (optional)</li>
                                        <li><strong>E-mail</strong> - Valid email address (required, must be unique)</li>
                                        <li><strong>Contact #</strong> - Phone/contact number (optional)</li>
                                        <li><strong>Weighted Exam Percentage (60%)</strong> - Exam score (auto-calculated, can be left empty)</li>
                                        <li><strong>Verbal Description</strong> - Performance description (auto-generated, can be left empty)</li>
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
                                        <div class="upload-icon">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <polyline points="14,2 14,8 20,8"/>
                                                <line x1="16" y1="13" x2="8" y2="13"/>
                                                <line x1="16" y1="17" x2="8" y2="17"/>
                                                <polyline points="10,9 9,9 8,9"/>
                                            </svg>
                                        </div>
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
                                <div class="form-help">All imported applicants will be assigned to this exam set. You can change assignments later.<br><small class="text-muted">Note: If the exam set has a configured exam window, access will only be allowed during that time period.</small></div>
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
                                <label for="access_code_expiry_days" class="form-label">Expires In (Days)</label>
                                <div class="duration-input-group">
                                    <input type="number" id="access_code_expiry_days" name="access_code_expiry_days" class="form-control duration-input" value="30" min="1" max="365">
                                    <span class="duration-label">days</span>
                                    <div class="duration-presets">
                                        <button type="button" onclick="setExpiry(7)" class="preset-btn">7 days</button>
                                        <button type="button" onclick="setExpiry(30)" class="preset-btn">30 days</button>
                                        <button type="button" onclick="setExpiry(60)" class="preset-btn">60 days</button>
                                        <button type="button" onclick="setExpiry(90)" class="preset-btn">90 days</button>
                                    </div>
                                </div>
                                <div class="form-help">Access codes are single-use and will expire after this duration. Default: 30 days. Applicants can resume the same attempt if interrupted.</div>
                            </div>

                            <!-- Import Actions -->
                            <div class="form-actions">
                                <a href="{{ route('admin.applicants.index') }}" class="btn-secondary">
                                    ← Back to Applicants
                                </a>
                                <button type="button" onclick="previewImport()" class="btn-secondary" id="previewBtn" disabled>
                                    Preview Import
                                </button>
                                <button type="submit" class="btn-primary" id="importBtn" disabled>
                                    Start Import
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
@endsection

@push('scripts')
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
                        <span class="file-name">${file.name}</span>
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
                    <div class="applicants-table">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Applicant No.</th>
                                    <th>Full Name</th>
                                    <th>Contact Information</th>
                                    <th>Preferred Course</th>
                                    <th>Weighted Exam % (60%)</th>
                                    <th>Verbal Description</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${csvData.rows.map((row, index) => {
                                    // Create header mapping for official template
                                    const headerMapping = {
                                        'First Name': 'first_name',
                                        'Middle Name': 'middle_name', 
                                        'Last Name': 'last_name',
                                        'Preferred Course': 'preferred_course',
                                        'E-mail': 'email_address',
                                        'Contact #': 'phone_number',
                                        'Applicant No.': 'applicant_no',
                                        'Weighted Exam Percentage (60%)': 'weighted_exam',
                                        'Weighted Exam % (60%)': 'weighted_exam',
                                        'Verbal Description': 'verbal_description'
                                    };
                                    
                                    // Map CSV columns to internal format
                                    const rowData = {};
                                    csvData.headers.forEach((header, i) => {
                                        const mappedField = headerMapping[header] || header.toLowerCase().replace(/[^a-z0-9]/g, '_');
                                        rowData[mappedField] = row[i] || '';
                                    });
                                    
                                    const fullName = [rowData.first_name, rowData.middle_name, rowData.last_name]
                                        .filter(name => name && name.trim())
                                        .join(' ');
                                    
                                    const formalName = [rowData.last_name, rowData.first_name, rowData.middle_name]
                                        .filter(name => name && name.trim())
                                        .join(', ');
                                    
                                    return `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>
                                                <div class="applicant-number">
                                                    <span class="font-mono text-sm">${rowData.applicant_no || 'Auto-generated'}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="applicant-name">
                                                    <div class="font-medium text-gray-900">${fullName || '-'}</div>
                                                    <div class="text-sm text-gray-500">${formalName || '-'}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div class="contact-email">${rowData.email_address || ''}</div>
                                                    <div class="contact-phone">${rowData.phone_number || ''}</div>
                                                </div>
                                            </td>
                                            <td class="text-center">${rowData.preferred_course || '-'}</td>
                                            <td class="text-center">
                                                ${rowData.weighted_exam ? 
                                                    `<span class="score-value">${rowData.weighted_exam}%</span>` : 
                                                    `<span class="no-score text-gray-400">-</span>`
                                                }
                                            </td>
                                            <td class="text-center">
                                                <span class="verbal-description">${rowData.verbal_description || '-'}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="status-badge status-pending">Pending</span>
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            previewContent.innerHTML = html;
            previewSection.style.display = 'block';
            previewSection.scrollIntoView({ behavior: 'smooth' });
        }

        function setExpiry(days) {
            document.getElementById('access_code_expiry_days').value = days;
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
            // Send as 1/0 to satisfy strict boolean validation on backend
            formData.append('generate_access_codes', document.getElementById('generate_access_codes').checked ? '1' : '0');
            
            // Convert days to hours for backend compatibility
            const expiryDays = document.getElementById('access_code_expiry_days').value;
            const expiryHours = expiryDays * 24;
            formData.append('access_code_expiry_hours', expiryHours);
            
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
            fetch('/admin/applicants/bulk/import', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
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
                
                console.error('Import error:', error);
                
                // Check if it's an authentication error
                if (error.message.includes('Authentication required')) {
                    setTimeout(() => {
                        alert('Your session has expired. Please log in again.');
                        window.location.href = '/admin/login';
                    }, 500);
                    return;
                }
                
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
                importBtn.textContent = 'Start Import';
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
                        <div class="success-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22,4 12,14.01 9,11.01"/>
                            </svg>
                        </div>
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
                        <a href="{{ route('admin.applicants.index') }}" class="btn-primary">
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
                        <div class="error-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="15" y1="9" x2="9" y2="15"/>
                                <line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                        </div>
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
@endpush

@push('styles')
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
            margin-bottom: 16px;
            opacity: 0.7;
            color: var(--text-gray);
        }
        
        .upload-icon svg {
            width: 48px;
            height: 48px;
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

        /* Applicant table styles for preview */
        .applicants-table {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .applicant-number .font-mono {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }

        .applicant-name .font-medium {
            font-weight: 500;
            color: #1f2937;
        }

        .applicant-name .text-sm {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .contact-info .contact-email {
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .contact-info .contact-phone {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .no-score {
            color: #9ca3af !important;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
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
            flex-shrink: 0;
        }
        
        .success-icon svg {
            color: #2e7d32;
        }
        
        .error-icon svg {
            color: #d32f2f;
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
@endpush