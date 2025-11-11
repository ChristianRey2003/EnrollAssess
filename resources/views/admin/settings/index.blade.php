@extends('layouts.admin')

@section('title', 'System Settings')

@php
    $pageTitle = 'System Settings';
    $pageSubtitle = 'Configure system-wide settings and preferences';
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

    .settings-container {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .settings-tabs {
        display: flex;
        gap: 10px;
        border-bottom: 2px solid var(--border-gray);
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .tab-btn {
        padding: 12px 24px;
        background: transparent;
        border: none;
        color: var(--text-gray);
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: var(--transition);
        white-space: nowrap;
    }

    .tab-btn:hover {
        color: var(--primary-maroon);
        background: rgba(128, 0, 32, 0.05);
    }

    .tab-btn.active {
        color: var(--primary-maroon);
        border-bottom-color: var(--primary-maroon);
    }

    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .settings-card {
        background: var(--white);
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
        margin-bottom: 20px;
    }

    .settings-card h3 {
        color: var(--primary-maroon);
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 20px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border-gray);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group .help-text {
        display: block;
        font-size: 13px;
        color: var(--text-gray);
        margin-top: 5px;
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="number"],
    .form-group input[type="password"],
    .form-group select {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid var(--border-gray);
        border-radius: 8px;
        font-size: 14px;
        transition: var(--transition);
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
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
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

    .test-email-section {
        background: #EFF6FF;
        border: 2px solid var(--info-blue);
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }

    .test-email-section h4 {
        margin: 0 0 15px 0;
        color: var(--info-blue);
        font-size: 16px;
    }

    .test-email-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }

    .test-email-form .form-group {
        flex: 1;
        margin: 0;
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

    @media (max-width: 768px) {
        .settings-container {
            padding: 15px;
        }

        .settings-tabs {
            overflow-x: auto;
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
    }
</style>
@endpush

@section('content')
<div class="settings-container">
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="alert alert-success">
            <span></span> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">
            <span></span> {{ session('error') }}
        </div>
    @endif

    <!-- Tabs -->
    <div class="settings-tabs">
        <button class="tab-btn active" onclick="switchTab('email')"> Email Settings</button>
        <button class="tab-btn" onclick="switchTab('system')">️ System Defaults</button>
        <button class="tab-btn" onclick="switchTab('exam')"> Exam Configuration</button>
        <button class="tab-btn" onclick="switchTab('notifications')"> Notifications</button>
        <button class="tab-btn" onclick="switchTab('interview')"> Interview Settings</button>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('admin.settings.update') }}" id="settingsForm">
        @csrf
        @method('PUT')

        <!-- Email Settings Tab -->
        <div id="email-tab" class="tab-content active">
            <!-- SMTP/Gmail Setup Guide - Collapsible -->
            <div id="smtp-guide" style="margin-bottom: 20px;">
                <button type="button" onclick="toggleGmailGuide()" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #EFF6FF; border: 2px solid #3B82F6; border-radius: 8px; color: #1E40AF; font-weight: 600; font-size: 14px; cursor: pointer; width: 100%; transition: all 0.2s;">
                    <span id="gmail-guide-icon" style="font-size: 20px; transition: transform 0.3s;">ℹ️</span>
                    <span>Gmail SMTP Setup Guide (Click to expand)</span>
                </button>
                <div id="gmail-guide-content" style="display: none; margin-top: 15px; background: #F0F9FF; border: 1px solid #BAE6FD; border-radius: 8px; padding: 20px;">
                    <div style="color: #1F2937; font-size: 14px; line-height: 1.8;">
                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #1E40AF;">Default Gmail Settings:</p>
                        <ul style="margin: 0 0 20px 0; padding-left: 20px;">
                            <li>Mail Driver: <code style="background: #FEF3C7; padding: 2px 6px; border-radius: 3px;">smtp</code></li>
                            <li>SMTP Host: <code style="background: #FEF3C7; padding: 2px 6px; border-radius: 3px;">smtp.gmail.com</code></li>
                            <li>SMTP Port: <code style="background: #FEF3C7; padding: 2px 6px; border-radius: 3px;">587</code> (TLS) or <code style="background: #FEF3C7; padding: 2px 6px; border-radius: 3px;">465</code> (SSL)</li>
                            <li>Encryption: <code style="background: #FEF3C7; padding: 2px 6px; border-radius: 3px;">tls</code></li>
                        </ul>
                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #1E40AF;">How to get Gmail App Password:</p>
                        <ol style="margin: 0; padding-left: 20px;">
                            <li>Go to <strong>Google Account → Security</strong></li>
                            <li>Enable <strong>2-Step Verification</strong></li>
                            <li>Go to <strong>App passwords</strong> section</li>
                            <li>Select <strong>Mail</strong> and <strong>Other</strong></li>
                            <li>Name it "EnrollAssess"</li>
                            <li>Copy the 16-character password (e.g., <code style="background: #FEF3C7; padding: 2px 6px; border-radius: 3px;">abcd efgh ijkl mnop</code>)</li>
                            <li>Paste it in the <strong>Password</strong> field below</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Amazon SES Setup Guide - Collapsible -->
            <div id="ses-guide" style="margin-bottom: 20px; display: none;">
                <button type="button" onclick="toggleSesGuide()" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 8px; color: #92400E; font-weight: 600; font-size: 14px; cursor: pointer; width: 100%; transition: all 0.2s;">
                    <span id="ses-guide-icon" style="font-size: 20px; transition: transform 0.3s;">☁️</span>
                    <span>Amazon SES Setup Guide (Click to expand)</span>
                </button>
                <div id="ses-guide-content" style="display: none; margin-top: 15px; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 20px;">
                    <div style="color: #1F2937; font-size: 14px; line-height: 1.8;">
                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #92400E;">What is Amazon SES?</p>
                        <p style="margin: 0 0 15px 0;">Amazon Simple Email Service (SES) is a cloud-based email service for sending transactional and marketing emails. It's more reliable and scalable than SMTP for production use.</p>
                        
                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #92400E;">Setup Steps:</p>
                        <ol style="margin: 0 0 20px 0; padding-left: 20px;">
                            <li>Sign up for AWS at <a href="https://aws.amazon.com" target="_blank" style="color: #2563EB;">aws.amazon.com</a></li>
                            <li>Go to <strong>AWS Console → SES</strong></li>
                            <li>Verify your sender email address or domain</li>
                            <li>Create SMTP credentials or IAM user with SES permissions</li>
                            <li>Copy <strong>Access Key ID</strong> and <strong>Secret Access Key</strong></li>
                            <li>Choose your AWS region (Singapore recommended for Philippines)</li>
                            <li>Paste credentials below</li>
                        </ol>
                        
                        <p style="margin: 0 0 15px 0; font-weight: 600; color: #92400E;">⚠️ Important Notes:</p>
                        <ul style="margin: 0; padding-left: 20px;">
                            <li><strong>Sandbox Mode:</strong> New SES accounts start in sandbox mode. You can only send to verified email addresses.</li>
                            <li><strong>Production Access:</strong> Request production access in AWS SES Console to send to any email address.</li>
                            <li><strong>Region:</strong> <code style="background: #FEF3C7; padding: 2px 6px; border-radius: 3px;">ap-southeast-1</code> (Singapore) is recommended for best performance in Philippines.</li>
                            <li><strong>Cost:</strong> First 62,000 emails/month are free, then $0.10 per 1,000 emails.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <h3> Email Configuration</h3>
                
                @foreach($emailSettings as $setting)
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
                    @endphp
                    
                <div class="form-group {{ $fieldClass }}" @if($fieldClass) data-field-type="{{ $fieldClass }}" @endif>
                    <label for="{{ $setting->key }}">{{ ucwords(str_replace('_', ' ', str_replace(['mail_', 'aws_'], '', $setting->key))) }}</label>
                    
                    @if($setting->type === 'select')
                        @if($setting->key === 'mail_mailer')
                            <select name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" onchange="toggleMailerFields()">
                                <option value="smtp" {{ $setting->value === 'smtp' ? 'selected' : '' }}>SMTP (Gmail, etc.)</option>
                                <option value="ses" {{ $setting->value === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                <option value="sendmail" {{ $setting->value === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="mailgun" {{ $setting->value === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
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

            <!-- Test Email Section -->
            <div class="test-email-section">
                <h4>🧪 Test Email Configuration</h4>
                <p style="color: var(--text-gray); font-size: 14px; margin-bottom: 15px;">
                    Send a test email to verify your email configuration is working correctly.
                </p>
                <div class="test-email-form">
                    <div class="form-group">
                        <label for="test_email">Test Email Address</label>
                        <input type="email" id="test_email" placeholder="your-email@example.com">
                    </div>
                    <button type="button" class="btn btn-test" onclick="sendTestEmail()">
                        <span></span> Send Test Email
                    </button>
                </div>
                <div id="test-email-result" style="margin-top: 15px;"></div>
            </div>
        </div>

        <!-- System Settings Tab -->
        <div id="system-tab" class="tab-content">
            <div class="settings-card">
                <h3>️ System Defaults</h3>
                
                @foreach($systemSettings as $setting)
                <div class="form-group">
                    <label for="{{ $setting->key }}">{{ ucwords(str_replace('_', ' ', str_replace('app_', '', $setting->key))) }}</label>
                    
                    @if($setting->type === 'select' && $setting->key === 'app_timezone')
                        <select name="settings[{{ $setting->key }}]" id="{{ $setting->key }}">
                            <option value="Asia/Manila" {{ $setting->value === 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila (PHT)</option>
                            <option value="UTC" {{ $setting->value === 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ $setting->value === 'America/New_York' ? 'selected' : '' }}>America/New York (EST)</option>
                        </select>
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

        <!-- Exam Settings Tab -->
        <div id="exam-tab" class="tab-content">
            <div class="settings-card">
                <h3> Exam Configuration</h3>
                
                @foreach($examSettings as $setting)
                    @if($setting->type === 'boolean')
                        <div class="form-group checkbox">
                            <input type="checkbox" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" value="1" {{ $setting->value === 'true' ? 'checked' : '' }}>
                            <label for="{{ $setting->key }}">{{ ucwords(str_replace('_', ' ', str_replace('exam_', '', $setting->key))) }}</label>
                        </div>
                        @if($setting->description)
                            <span class="help-text" style="margin-left: 30px;">{{ $setting->description }}</span>
                        @endif
                    @else
                        <div class="form-group">
                            <label for="{{ $setting->key }}">{{ ucwords(str_replace('_', ' ', str_replace('exam_', '', $setting->key))) }}</label>
                            <input type="number" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" value="{{ $setting->value }}">
                            @if($setting->description)
                                <span class="help-text">{{ $setting->description }}</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Notifications Tab -->
        <div id="notifications-tab" class="tab-content">
            <div class="settings-card">
                <h3> Notification Preferences</h3>
                <p style="color: var(--text-gray); margin-bottom: 20px;">
                    Enable or disable automatic email notifications for various system events.
                </p>
                
                @foreach($notificationSettings as $setting)
                <div class="form-group checkbox">
                    <input type="checkbox" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" value="1" {{ $setting->value === 'true' ? 'checked' : '' }}>
                    <label for="{{ $setting->key }}">{{ ucwords(str_replace('_', ' ', str_replace('notify_', '', $setting->key))) }}</label>
                </div>
                @if($setting->description)
                    <span class="help-text" style="margin-left: 30px; display: block; margin-bottom: 15px;">{{ $setting->description }}</span>
                @endif
                @endforeach
            </div>
        </div>

        <!-- Interview Settings Tab -->
        <div id="interview-tab" class="tab-content">
            <div class="settings-card">
                <h3> Interview Configuration</h3>
                
                @foreach($interviewSettings as $setting)
                <div class="form-group">
                    <label for="{{ $setting->key }}">{{ ucwords(str_replace('_', ' ', str_replace('interview_', '', $setting->key))) }}</label>
                    <input type="number" name="settings[{{ $setting->key }}]" id="{{ $setting->key }}" value="{{ $setting->value }}">
                    @if($setting->description)
                        <span class="help-text">{{ $setting->description }}</span>
                    @endif
                </div>
                @endforeach
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
@endsection

@push('scripts')
<script>
    function toggleGmailGuide() {
        const content = document.getElementById('gmail-guide-content');
        const icon = document.getElementById('gmail-guide-icon');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.textContent = '📖';
        } else {
            content.style.display = 'none';
            icon.textContent = 'ℹ️';
        }
    }

    function toggleSesGuide() {
        const content = document.getElementById('ses-guide-content');
        const icon = document.getElementById('ses-guide-icon');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.textContent = '📚';
        } else {
            content.style.display = 'none';
            icon.textContent = '☁️';
        }
    }

    function toggleMailerFields() {
        const mailerType = document.getElementById('mail_mailer').value;
        
        // Get all SMTP and SES fields
        const smtpFields = document.querySelectorAll('.smtp-field');
        const sesFields = document.querySelectorAll('.ses-field');
        
        // Get guide sections
        const smtpGuide = document.getElementById('smtp-guide');
        const sesGuide = document.getElementById('ses-guide');
        
        // Show/hide fields based on mailer type
        if (mailerType === 'smtp') {
            // Show SMTP fields, hide SES fields
            smtpFields.forEach(field => field.style.display = 'block');
            sesFields.forEach(field => field.style.display = 'none');
            smtpGuide.style.display = 'block';
            sesGuide.style.display = 'none';
        } else if (mailerType === 'ses') {
            // Show SES fields, hide SMTP fields
            smtpFields.forEach(field => field.style.display = 'none');
            sesFields.forEach(field => field.style.display = 'block');
            smtpGuide.style.display = 'none';
            sesGuide.style.display = 'block';
        } else {
            // For other mailers (log, sendmail, etc.), hide both
            smtpFields.forEach(field => field.style.display = 'none');
            sesFields.forEach(field => field.style.display = 'none');
            smtpGuide.style.display = 'none';
            sesGuide.style.display = 'none';
        }
    }

    // Call on page load to set initial state
    document.addEventListener('DOMContentLoaded', function() {
        toggleMailerFields();
    });

    function switchTab(tabName) {
        // Remove active class from all tabs and content
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        // Add active class to selected tab and content
        event.target.classList.add('active');
        document.getElementById(tabName + '-tab').classList.add('active');
    }

    function sendTestEmail() {
        const email = document.getElementById('test_email').value;
        const resultDiv = document.getElementById('test-email-result');
        const button = event.target;
        
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
</script>
@endpush

