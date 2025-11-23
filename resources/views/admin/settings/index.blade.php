@extends('layouts.admin')

@section('title', 'Email Settings')

@php
    $pageTitle = 'Email Settings';
    $pageSubtitle = 'Configure email settings and preferences';
@endphp

@push('styles')
<style>
    :root {
        --primary-maroon: #800020;
        --primary-gold: #FFD700;
        --dark-maroon: #5C0016;
        --light-gold: #FFF8DC;
        --white: #FFFFFF;
        --light-gray: #F8F9FA;
        --border-gray: #E9ECEF;
        --text-gray: #6B7280;
        --text-dark: #1F2937;
        --success-green: #059669;
        --warning-orange: #D97706;
        --danger-red: #DC2626;
        --info-blue: #3B82F6;
        --transition: all 0.3s ease;
    }

    /* Override main-content padding for this page */
    .main-content {
        padding: 20px !important;
    }

    /* Hide default admin layout alerts on this page - we use floating notifications instead */
    .main-content > .alert {
        display: none;
    }

    .settings-container {
        width: 100%;
        max-width: 100%;
    }

    /* Tab Navigation */
    .settings-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid var(--border-gray);
        margin-bottom: 24px;
        padding: 0;
    }

    .settings-tab {
        padding: 12px 24px;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        color: var(--text-gray);
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
        position: relative;
        bottom: -2px;
    }

    .settings-tab:hover {
        color: var(--primary-maroon);
        background: rgba(128, 0, 32, 0.05);
    }

    .settings-tab.active {
        color: var(--primary-maroon);
        border-bottom-color: var(--primary-maroon);
        font-weight: 600;
    }

    .settings-tab-pane {
        display: none;
    }

    .settings-tab-pane.active {
        display: block;
    }

    /* Archived Reports Styles */
    .archived-reports-section {
        margin-top: 20px;
    }

    .archived-reports-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--border-gray);
    }

    .archived-reports-header h3 {
        color: var(--primary-maroon);
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    .archived-reports-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-archive {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: var(--white);
    }

    .btn-archive:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .btn-delete-permanent {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: var(--white);
    }

    .btn-delete-permanent:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--white);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .data-table thead {
        background: var(--light-gray);
    }

    .data-table th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .data-table td {
        padding: 12px 16px;
        border-top: 1px solid var(--border-gray);
        color: var(--text-dark);
        font-size: 14px;
    }

    .data-table tbody tr:hover {
        background: var(--light-gray);
    }

    .table-actions {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: var(--transition);
    }

    .action-btn-download {
        background: var(--info-blue);
        color: var(--white);
    }

    .action-btn-download:hover {
        background: #2563EB;
    }

    .report-type-name {
        font-weight: 500;
        color: var(--text-dark);
    }

    .question-text {
        max-width: 400px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .type-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        background: #E5E7EB;
        color: #374151;
        font-weight: 500;
    }


    .settings-card {
        background: var(--white);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border-gray);
        margin-bottom: 20px;
    }

    .settings-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--border-gray);
    }

    .settings-card h3 {
        color: var(--primary-maroon);
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    .settings-card-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px 20px;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 6px;
        font-size: 14px;
    }

    .form-group .help-text {
        display: block;
        font-size: 12px;
        color: var(--text-gray);
        margin-top: 4px;
        line-height: 1.4;
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="number"],
    .form-group input[type="password"],
    .form-group select {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid var(--border-gray);
        border-radius: 6px;
        font-size: 14px;
        transition: var(--transition);
        height: 36px;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary-maroon);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .form-group.checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group.checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .form-group.checkbox label {
        margin: 0;
        cursor: pointer;
    }

    .settings-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 2px solid var(--border-gray);
    }

    .btn {
        padding: 8px 14px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 12px;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-info {
        background: var(--info-blue);
        color: var(--white);
        font-size: 12px;
        padding: 6px 12px;
    }

    .btn-info:hover {
        background: #2563EB;
    }

    .btn-test-email {
        background: var(--info-blue);
        color: var(--white);
    }

    .btn-test-email:hover {
        background: #2563EB;
    }

    .btn-primary {
        background: var(--primary-maroon);
        color: var(--white);
    }

    .btn-primary:hover {
        background: var(--dark-maroon);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(128, 0, 32, 0.3);
    }

    .btn-secondary {
        background: var(--light-gray);
        color: var(--text-dark);
        border: 2px solid var(--border-gray);
    }

    .btn-secondary:hover {
        background: var(--border-gray);
    }

    .btn-test {
        background: var(--info-blue);
        color: var(--white);
    }

    .btn-test:hover {
        background: #2563EB;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    }

    .test-email-modal .modal-body {
        padding: 24px;
    }

    .test-email-form {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .test-email-form .form-group {
        margin: 0;
    }

    .test-email-form .form-group label {
        margin-bottom: 8px;
    }

    .test-email-form .form-group input {
        width: 100%;
    }

    .test-email-form .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 8px;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }

    .alert-success {
        background: rgba(5, 150, 105, 0.1);
        color: var(--success-green);
        border: 2px solid var(--success-green);
    }

    .alert-error {
        background: rgba(220, 38, 38, 0.1);
        color: var(--danger-red);
        border: 2px solid var(--danger-red);
    }

    .alert-info {
        background: rgba(59, 130, 246, 0.1);
        color: var(--info-blue);
        border: 2px solid var(--info-blue);
    }

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    /* Floating Notification */
    .floating-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        max-width: 500px;
        transform: translateX(400px);
        opacity: 0;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .floating-notification.show {
        transform: translateX(0);
        opacity: 1;
    }

    .floating-notification.success {
        background: rgba(5, 150, 105, 0.1);
        color: var(--success-green);
        border: 2px solid var(--success-green);
    }

    .floating-notification.error {
        background: rgba(220, 38, 38, 0.1);
        color: var(--danger-red);
        border: 2px solid var(--danger-red);
    }

    .floating-notification .notification-icon {
        font-size: 20px;
        flex-shrink: 0;
    }

    .floating-notification .notification-close {
        margin-left: auto;
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: inherit;
        opacity: 0.7;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: var(--transition);
    }

    .floating-notification .notification-close:hover {
        opacity: 1;
        background: rgba(0, 0, 0, 0.1);
    }

    /* Modal styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .modal-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background: var(--white);
        border-radius: 12px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        position: relative;
        animation: modalSlideIn 0.3s ease-out;
        transform: translateY(-20px);
        transition: transform 0.3s ease;
    }

    .modal-overlay.show .modal-content {
        transform: translateY(0);
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-gray);
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        color: var(--primary-maroon);
        font-size: 18px;
        font-weight: 600;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: var(--text-gray);
        cursor: pointer;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: var(--transition);
    }

    .modal-close:hover {
        background: var(--light-gray);
        color: var(--primary-maroon);
    }

    .modal-body {
        padding: 24px;
        color: var(--text-dark);
        font-size: 14px;
        line-height: 1.8;
    }

    .modal-body h4 {
        color: var(--primary-maroon);
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 12px 0;
    }

    .modal-body ul,
    .modal-body ol {
        margin: 0 0 20px 0;
        padding-left: 20px;
    }

    .modal-body li {
        margin-bottom: 8px;
    }

    .modal-body code {
        background: #FEF3C7;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 13px;
    }

    .modal-body a {
        color: #2563EB;
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .form-group.full-width {
            grid-column: 1;
        }

        .test-email-form {
            flex-direction: column;
        }

        .settings-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .settings-card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .settings-card-actions {
            width: 100%;
            flex-direction: column;
        }

        .settings-card-actions .btn {
            width: 100%;
        }

        .modal-content {
            width: 95%;
            margin: 20px;
        }
        
        .modal-header,
        .modal-body {
            padding-left: 20px;
            padding-right: 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="settings-container">
    <!-- Tab Navigation -->
    <div class="settings-tabs">
        <button type="button" class="settings-tab active" onclick="switchTab('email')">Email Settings</button>
        <button type="button" class="settings-tab" onclick="switchTab('archived-reports')">Archived Reports</button>
        <button type="button" class="settings-tab" onclick="switchTab('archived-questions')">Archived Question Bank</button>
    </div>

    <!-- Email Settings Tab Pane -->
    <div id="email-tab" class="settings-tab-pane active">
        <!-- Form -->
        <form method="POST" action="{{ route('admin.settings.update') }}" id="settingsForm">
            @csrf
            @method('PUT')

            <!-- Email Settings -->
            <div>
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3>Email Configuration</h3>
                    <div class="settings-card-actions">
                        <button type="button" class="btn btn-info" onclick="openSesGuide()" id="ses-guide-btn" style="display: none;">
                            <span>☁️</span> Amazon SES Setup Guide
                        </button>
                        <button type="button" class="btn btn-test-email" onclick="openTestEmailModal()">
                            <span>🧪</span> Test Email
                        </button>
                    </div>
                </div>
                
                <div class="form-grid">
                    @php
                        // Reorder fields for optimal layout: Mailer|Region, From Address|From Name, Access Key ID|Secret Access Key
                        $orderedKeys = ['mail_mailer', 'aws_region', 'mail_from_address', 'mail_from_name', 'aws_access_key_id', 'aws_secret_access_key'];
                        $orderedSettings = [];
                        $otherSettings = [];
                        
                        // Separate ordered fields from others
                        foreach ($emailSettings as $setting) {
                            if (in_array($setting->key, $orderedKeys)) {
                                $orderedSettings[$setting->key] = $setting;
                            } else {
                                $otherSettings[] = $setting;
                            }
                        }
                        
                        // Build final ordered array
                        $finalSettings = [];
                        foreach ($orderedKeys as $key) {
                            if (isset($orderedSettings[$key])) {
                                $finalSettings[] = $orderedSettings[$key];
                            }
                        }
                        // Add remaining fields
                        $finalSettings = array_merge($finalSettings, $otherSettings);
                    @endphp
                    @foreach($finalSettings as $setting)
                        @php
                            // Determine field grouping for show/hide logic
                            $isSmtpField = in_array($setting->key, ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption']);
                            $isSesField = in_array($setting->key, ['aws_access_key_id', 'aws_secret_access_key', 'aws_region']);
                            $fieldClass = '';
                            if ($isSmtpField) {
                                $fieldClass = 'smtp-field';
                            } elseif ($isSesField) {
                                $fieldClass = 'ses-field';
                            }
                            // Fields that should be side-by-side (pairs): Mailer|Region, From Address|From Name, Access Key ID|Secret Access Key
                            $sideBySideFields = ['mail_mailer', 'aws_region', 'mail_from_address', 'mail_from_name', 'aws_access_key_id', 'aws_secret_access_key'];
                            $fullWidth = in_array($setting->key, $sideBySideFields) ? '' : 'full-width';
                        @endphp
                        
                    <div class="form-group {{ $fieldClass }} {{ $fullWidth }}" @if($fieldClass) data-field-type="{{ $fieldClass }}" @endif>
                        <label for="{{ $setting->key }}">{{ ucwords(str_replace('_', ' ', str_replace(['mail_', 'aws_'], '', $setting->key))) }}</label>
                        
                        @if($setting->type === 'select')
                            @if($setting->key === 'mail_mailer')
                                <select name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" onchange="toggleMailerFields()">
                                    <option value="ses" {{ $setting->value === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                    <option value="log" {{ $setting->value === 'log' ? 'selected' : '' }}>Log (Testing)</option>
                                </select>
                            @elseif($setting->key === 'mail_encryption')
                                <select name="settings[{{ $setting->key }}]" id="{{ $setting->key }}">
                                    <option value="tls" {{ $setting->value === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ $setting->value === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="" {{ $setting->value === '' ? 'selected' : '' }}>None</option>
                                </select>
                            @elseif($setting->key === 'aws_region')
                                <select name="settings[{{ $setting->key }}]" id="{{ $setting->key }}">
                                    <option value="ap-southeast-1" {{ $setting->value === 'ap-southeast-1' ? 'selected' : '' }}>ap-southeast-1 (Singapore)</option>
                                    <option value="us-east-1" {{ $setting->value === 'us-east-1' ? 'selected' : '' }}>us-east-1 (N. Virginia)</option>
                                    <option value="us-west-2" {{ $setting->value === 'us-west-2' ? 'selected' : '' }}>us-west-2 (Oregon)</option>
                                    <option value="eu-west-1" {{ $setting->value === 'eu-west-1' ? 'selected' : '' }}>eu-west-1 (Ireland)</option>
                                    <option value="ap-northeast-1" {{ $setting->value === 'ap-northeast-1' ? 'selected' : '' }}>ap-northeast-1 (Tokyo)</option>
                                </select>
                            @endif
                        @elseif($setting->type === 'password')
                            <input type="password" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" value="{{ $setting->value }}" placeholder="Leave blank to keep current">
                        @elseif($setting->type === 'number')
                            <input type="number" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" value="{{ $setting->value }}">
                        @else
                            <input type="text" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" value="{{ $setting->value }}">
                        @endif
                        
                        @if($setting->description)
                            <span class="help-text">{{ $setting->description }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

            <!-- Form Actions -->
            <div class="settings-actions">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('admin.dashboard') }}'">
                    <span></span> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <span></span> Save Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Archived Reports Tab Pane -->
    <div id="archived-reports-tab" class="settings-tab-pane">
        <div class="archived-reports-section">
            <div class="archived-reports-header">
                <h3>Archived Reports</h3>
                <div class="archived-reports-actions">
                    <button type="button" class="btn btn-archive" onclick="loadArchivedReports()" id="loadArchivedBtn">Load Archived</button>
                    <button type="button" class="btn btn-archive" onclick="restoreAllArchived()">Restore All</button>
                    <button type="button" class="btn btn-delete-permanent" onclick="permanentlyDeleteAllArchived()">Permanently Delete All</button>
                </div>
            </div>
            <div class="settings-card">
                <table class="data-table archived-reports-table">
                    <thead>
                        <tr>
                            <th>Report Type</th>
                            <th>Generated By</th>
                            <th>Created</th>
                            <th>Archived</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #6B7280;">
                                <p>Click "Load Archived" to view archived reports.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Archived Question Bank Tab Pane -->
    <div id="archived-questions-tab" class="settings-tab-pane">
        <div class="archived-reports-section">
            <div class="archived-reports-header">
                <h3>Archived Question Bank</h3>
                <div class="archived-reports-actions">
                    <button type="button" class="btn btn-archive" onclick="loadArchivedQuestions()" id="loadArchivedQuestionsBtn">Load Archived</button>
                </div>
            </div>
            <div class="settings-card">
                <table class="data-table archived-questions-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Total Items</th>
                            <th>Questions</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: #6B7280;">
                                <p>Click "Load Archived" to view archived question banks.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Amazon SES Setup Guide Modal -->
<div id="sesGuideModal" class="modal-overlay" onclick="closeSesGuide(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>☁️ Amazon SES Setup Guide</h3>
            <button type="button" class="modal-close" onclick="closeSesGuide()">×</button>
        </div>
        <div class="modal-body">
            <h4>What is Amazon SES?</h4>
            <p>Amazon Simple Email Service (SES) is a cloud-based email service for sending transactional and marketing emails. It's more reliable and scalable than SMTP for production use.</p>
            
            <h4>Setup Steps:</h4>
            <ol>
                <li>Sign up for AWS at <a href="https://aws.amazon.com" target="_blank">aws.amazon.com</a></li>
                <li>Go to <strong>AWS Console → SES</strong></li>
                <li>Verify your sender email address or domain</li>
                <li>Create SMTP credentials or IAM user with SES permissions</li>
                <li>Copy <strong>Access Key ID</strong> and <strong>Secret Access Key</strong></li>
                <li>Choose your AWS region (Singapore recommended for Philippines)</li>
                <li>Paste credentials below</li>
            </ol>
            
            <h4>⚠️ Important Notes:</h4>
            <ul>
                <li><strong>Sandbox Mode:</strong> New SES accounts start in sandbox mode. You can only send to verified email addresses.</li>
                <li><strong>Production Access:</strong> Request production access in AWS SES Console to send to any email address.</li>
                <li><strong>Region:</strong> <code>ap-southeast-1</code> (Singapore) is recommended for best performance in Philippines.</li>
                <li><strong>Cost:</strong> First 62,000 emails/month are free, then $0.10 per 1,000 emails.</li>
            </ul>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div id="testEmailModal" class="modal-overlay" onclick="closeTestEmailModal(event)">
    <div class="modal-content test-email-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>🧪 Test Email Configuration</h3>
            <button type="button" class="modal-close" onclick="closeTestEmailModal()">×</button>
        </div>
        <div class="modal-body">
            <p style="color: var(--text-gray); font-size: 14px; margin-bottom: 20px;">
                Send a test email to verify your email configuration is working correctly.
            </p>
            <div class="test-email-form">
                <div class="form-group">
                    <label for="test_email">Test Email Address</label>
                    <input type="email" id="test_email" placeholder="your-email@example.com">
                </div>
                <div id="test-email-result"></div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeTestEmailModal()">
                        <span></span> Cancel
                    </button>
                    <button type="button" class="btn btn-test" onclick="sendTestEmail()">
                        <span></span> Send Test Email
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openSesGuide() {
        const modal = document.getElementById('sesGuideModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSesGuide(event) {
        if (event && event.target !== event.currentTarget) return;
        const modal = document.getElementById('sesGuideModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    function openTestEmailModal() {
        const modal = document.getElementById('testEmailModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        // Clear previous results
        document.getElementById('test-email-result').innerHTML = '';
        // Focus on email input
        setTimeout(() => {
            document.getElementById('test_email').focus();
        }, 100);
    }

    function closeTestEmailModal(event) {
        if (event && event.target !== event.currentTarget) return;
        const modal = document.getElementById('testEmailModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
        // Clear form
        document.getElementById('test_email').value = '';
        document.getElementById('test-email-result').innerHTML = '';
    }

    function toggleMailerFields() {
        const mailerType = document.getElementById('mail_mailer').value;
        
        // Get all SMTP and SES fields
        const smtpFields = document.querySelectorAll('.smtp-field');
        const sesFields = document.querySelectorAll('.ses-field');
        
        // Get guide button
        const sesGuideBtn = document.getElementById('ses-guide-btn');
        
        // Show/hide fields based on mailer type
        if (mailerType === 'ses') {
            // Show SES fields, hide SMTP fields
            smtpFields.forEach(field => field.style.display = 'none');
            sesFields.forEach(field => field.style.display = 'block');
            if (sesGuideBtn) sesGuideBtn.style.display = 'inline-flex';
        } else {
            // For log (testing), hide both
            smtpFields.forEach(field => field.style.display = 'none');
            sesFields.forEach(field => field.style.display = 'none');
            if (sesGuideBtn) sesGuideBtn.style.display = 'none';
        }
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSesGuide();
            closeTestEmailModal();
        }
    });

    // Call on page load to set initial state
    document.addEventListener('DOMContentLoaded', function() {
        toggleMailerFields();
        showFloatingNotification();
    });

    // Floating notification system
    function showFloatingNotification() {
        @if (session('success'))
            createFloatingNotification('{{ session('success') }}', 'success');
        @endif
        @if (session('error'))
            createFloatingNotification('{{ session('error') }}', 'error');
        @endif
    }

    function createFloatingNotification(message, type = 'success') {
        // Remove any existing notifications
        const existing = document.querySelector('.floating-notification');
        if (existing) {
            existing.remove();
        }

        const notification = document.createElement('div');
        notification.className = `floating-notification ${type}`;
        notification.innerHTML = `
            <span class="notification-icon">${type === 'success' ? '✓' : '✕'}</span>
            <span class="notification-message">${message}</span>
            <button type="button" class="notification-close" onclick="this.parentElement.remove()">×</button>
        `;

        document.body.appendChild(notification);

        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }, 5000);
    }


    function sendTestEmail() {
        const email = document.getElementById('test_email').value;
        const resultDiv = document.getElementById('test-email-result');
        const button = event.target.closest('.btn-test') || event.target;
        
        if (!email) {
            resultDiv.innerHTML = '<div class="alert alert-error"><span></span> Please enter an email address</div>';
            return;
        }

        // Show loading state
        button.classList.add('loading');
        button.innerHTML = '<span>⏳</span> Sending...';
        resultDiv.innerHTML = '<div class="alert alert-info"><span>⏳</span> Sending test email...</div>';

        // Send AJAX request
        fetch('{{ route('admin.settings.test-email') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ test_email: email })
        })
        .then(response => response.json())
        .then(data => {
            button.classList.remove('loading');
            button.innerHTML = '<span></span> Send Test Email';
            
            if (data.success) {
                resultDiv.innerHTML = `<div class="alert alert-success"><span></span> ${data.message}</div>`;
                // Clear email input on success
                document.getElementById('test_email').value = '';
            } else {
                resultDiv.innerHTML = `<div class="alert alert-error"><span></span> ${data.message}</div>`;
            }
        })
        .catch(error => {
            button.classList.remove('loading');
            button.innerHTML = '<span></span> Send Test Email';
            resultDiv.innerHTML = '<div class="alert alert-error"><span></span> Failed to send test email. Please check your configuration.</div>';
        });
    }

    // Auto-save warning on navigation
    let formChanged = false;
    document.getElementById('settingsForm').addEventListener('change', function() {
        formChanged = true;
    });

    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    document.getElementById('settingsForm').addEventListener('submit', function() {
        formChanged = false;
    });

    // Tab Switching Function
    function switchTab(tabName) {
        // Remove active class from all tabs and panes
        document.querySelectorAll('.settings-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.settings-tab-pane').forEach(pane => pane.classList.remove('active'));

        // Add active class to selected tab and pane
        const clickedTab = event.target;
        clickedTab.classList.add('active');
        document.getElementById(tabName + '-tab').classList.add('active');
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Notification function (reuse from reports page style)
    function showNotification(message, type = 'success') {
        createFloatingNotification(message, type);
    }

    // Archived Reports Functions
    async function loadArchivedReports() {
        const btn = document.getElementById('loadArchivedBtn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Loading...';

        try {
            const response = await fetch('/admin/reports/archived-history', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const text = await response.text();
            const data = JSON.parse(text);

            if (data.success) {
                if (data.reports.length > 0) {
                    updateArchivedReportsTable(data.reports);
                } else {
                    const tbody = document.querySelector('.archived-reports-table tbody');
                    if (tbody) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px; color: #6B7280;">
                                    <p>No archived reports found.</p>
                                </td>
                            </tr>
                        `;
                    }
                }
            } else {
                showNotification('Failed to load archived reports.', 'error');
            }
        } catch (error) {
            console.error('Error loading archived reports:', error);
            showNotification('Error loading archived reports. Please try again.', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }

    function updateArchivedReportsTable(reports) {
        const tbody = document.querySelector('.archived-reports-table tbody');
        if (!tbody) return;

        tbody.innerHTML = reports.map(report => `
            <tr>
                <td>
                    <div class="report-type">
                        <span class="report-type-name">${report.type}</span>
                    </div>
                </td>
                <td>${report.generated_by}</td>
                <td>${report.created_at}</td>
                <td style="color: #6b7280;">${report.deleted_at}</td>
                <td>
                    <div class="table-actions">
                        <button onclick="downloadReport(${report.id})" class="action-btn action-btn-download" title="Download Report">Download</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function downloadReport(id) {
        if (!id) {
            alert('Invalid report ID');
            return;
        }
        
        // Show loading message
        const btn = event.target;
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Downloading...';
        
        // Direct download
        window.location.href = `/admin/reports/${id}/download`;
        
        // Re-enable button after a short delay
        setTimeout(() => {
            btn.disabled = false;
            btn.textContent = originalText;
        }, 1000);
    }

    async function restoreAllArchived() {
        if (!confirm('Are you sure you want to restore all archived reports?')) {
            return;
        }

        try {
            const response = await fetch('/admin/reports/restore-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            const text = await response.text();
            const data = JSON.parse(text);

            if (data.success) {
                showNotification(data.message || `Successfully restored ${data.restored_count} report(s).`, 'success');
                loadArchivedReports(); // Refresh archived reports
            } else {
                showNotification(data.message || 'Failed to restore reports.', 'error');
            }
        } catch (error) {
            console.error('Error restoring reports:', error);
            showNotification('Error restoring reports. Please try again.', 'error');
        }
    }

    async function permanentlyDeleteAllArchived() {
        if (!confirm('WARNING: This will permanently delete all archived reports and their files. This action cannot be undone!\n\nAre you absolutely sure?')) {
            return;
        }

        const confirmText = prompt('Type "DELETE PERMANENTLY" to confirm permanent deletion:');
        if (confirmText !== 'DELETE PERMANENTLY') {
            showNotification('Permanent deletion cancelled.', 'info');
            return;
        }

        try {
            const response = await fetch('/admin/reports/permanently-delete-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            const text = await response.text();
            const data = JSON.parse(text);

            if (data.success) {
                showNotification(data.message || `Successfully permanently deleted ${data.deleted_count} report(s).`, 'success');
                loadArchivedReports(); // Refresh archived reports table
            } else {
                showNotification(data.message || 'Failed to permanently delete reports.', 'error');
            }
        } catch (error) {
            console.error('Error permanently deleting reports:', error);
            showNotification('Error permanently deleting reports. Please try again.', 'error');
        }
    }

    // Archived Question Bank (Exams) Functions
    async function loadArchivedQuestions() {
        const btn = document.getElementById('loadArchivedQuestionsBtn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Loading...';

        try {
            const response = await fetch('{{ route('admin.settings.archived-questions') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const text = await response.text();
            const data = JSON.parse(text);

            if (data.success) {
                if (data.exams.length > 0) {
                    updateArchivedQuestionsTable(data.exams);
                } else {
                    const tbody = document.querySelector('.archived-questions-table tbody');
                    if (tbody) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px; color: #6B7280;">
                                    <p>No archived question banks found.</p>
                                </td>
                            </tr>
                        `;
                    }
                }
            } else {
                showNotification('Failed to load archived question banks.', 'error');
            }
        } catch (error) {
            console.error('Error loading archived question banks:', error);
            showNotification('Error loading archived question banks. Please try again.', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }

    function updateArchivedQuestionsTable(exams) {
        const tbody = document.querySelector('.archived-questions-table tbody');
        if (!tbody) return;

        tbody.innerHTML = exams.map(exam => {
            const duration = exam.duration_minutes >= 60 
                ? `${Math.floor(exam.duration_minutes / 60)}h ${exam.duration_minutes % 60}m`
                : `${exam.duration_minutes}m`;
            
            return `
            <tr>
                <td><strong>${exam.title}</strong></td>
                <td class="question-text" title="${(exam.description || '').replace(/"/g, '&quot;')}">${exam.description || 'No description'}</td>
                <td>${duration}</td>
                <td>${exam.total_items} items</td>
                <td>${exam.active_questions}/${exam.total_questions} active</td>
                <td>${exam.created_at}</td>
                <td>
                    <div class="table-actions">
                        <button onclick="restoreExam(${exam.id})" class="action-btn action-btn-download" title="Restore & Publish Exam">Restore & Publish</button>
                    </div>
                </td>
            </tr>
        `;
        }).join('');
    }

    async function restoreExam(examId) {
        if (!confirm('Are you sure you want to restore and publish this question bank? This will deactivate the current active exam.')) {
            return;
        }

        try {
            const response = await fetch('{{ route('admin.settings.restore-archived-questions') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ exam_id: examId }),
            });

            const text = await response.text();
            const data = JSON.parse(text);

            if (data.success) {
                showNotification(data.message || 'Question bank restored and published successfully.', 'success');
                loadArchivedQuestions(); // Refresh archived exams table
            } else {
                showNotification(data.message || 'Failed to restore question bank.', 'error');
            }
        } catch (error) {
            console.error('Error restoring exam:', error);
            showNotification('Error restoring question bank. Please try again.', 'error');
        }
    }
</script>
@endpush


