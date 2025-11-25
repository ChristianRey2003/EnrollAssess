<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Account Credentials - EnrollAssess</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #800020 0%, #5c0017 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .university-name {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .department-name {
            font-size: 16px;
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .credentials-section {
            background: linear-gradient(135deg, #fff8dc 0%, #ffeaa7 100%);
            border: 2px solid #FFD700;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
        }
        .credentials-title {
            font-size: 16px;
            font-weight: 600;
            color: #800020;
            margin: 0 0 15px 0;
            text-align: center;
        }
        .credential-item {
            background: white;
            border-radius: 6px;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #800020;
        }
        .credential-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .credential-value {
            font-size: 18px;
            font-weight: bold;
            color: #800020;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .warning-box-title {
            font-size: 16px;
            font-weight: 600;
            color: #856404;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
        }
        .warning-box-title::before {
            content: '⚠️';
            margin-right: 8px;
            font-size: 18px;
        }
        .warning-box-content {
            color: #856404;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }
        .action-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        .action-box-title {
            font-size: 16px;
            font-weight: 600;
            color: #1976d2;
            margin: 0 0 10px 0;
        }
        .action-box-content {
            color: #1565c0;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }
        .login-button {
            display: inline-block;
            background: linear-gradient(135deg, #800020 0%, #5c0017 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(128, 0, 32, 0.3);
            transition: all 0.3s ease;
        }
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(128, 0, 32, 0.4);
        }
        .footer {
            background: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 5px 0;
            color: #6c757d;
            font-size: 14px;
        }
        .security-note {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin: 25px 0;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
        }
        .security-note strong {
            color: #800020;
        }
        @media (max-width: 600px) {
            .content {
                padding: 20px 15px;
            }
            .header {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1 class="university-name">Eastern Visayas State University</h1>
            <p class="department-name">Computer Studies Department</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Dear {{ $user->full_name }},
            </div>

            <p>Your EnrollAssess account has been created. Below are your login credentials:</p>

            <!-- Credentials Section -->
            <div class="credentials-section">
                <div class="credentials-title">Your Account Credentials</div>
                
                <div class="credential-item">
                    <div class="credential-label">Username</div>
                    <div class="credential-value">{{ $user->username }}</div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>
            </div>

            <!-- Warning Box -->
            <div class="warning-box">
                <div class="warning-box-title">Important: Password Change Required</div>
                <div class="warning-box-content">
                    <p><strong>For security reasons, you must change your password immediately after your first login.</strong></p>
                    <p>You will be automatically redirected to the password change page when you log in with these credentials.</p>
                </div>
            </div>

            <!-- Action Box -->
            <div class="action-box">
                <div class="action-box-title">Next Steps</div>
                <div class="action-box-content">
                    <ol style="margin: 0; padding-left: 20px;">
                        <li>Log in using the credentials provided above</li>
                        <li>You will be prompted to change your password</li>
                        <li>Choose a strong, unique password that you will remember</li>
                        <li>Once your password is changed, you can access all system features</li>
                    </ol>
                </div>
            </div>

            <!-- Login Button -->
            <div style="text-align: center;">
                <a href="https://enrollassess-evsu.com/admin/login" class="login-button">
                    Log In to EnrollAssess
                </a>
            </div>

            <!-- Security Note -->
            <div class="security-note">
                <p><strong>Security Reminder:</strong> Please keep your credentials secure and do not share them with anyone. If you suspect your account has been compromised, contact the system administrator immediately.</p>
            </div>

            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>Computer Studies Department</strong><br>
                Eastern Visayas State University
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Computer Studies Department</strong></p>
            <p>Eastern Visayas State University</p>
            <p>Tacloban City, Philippines</p>
            
            <div style="margin-top: 15px;">
                <p> Email: cs.department@evsu.edu.ph</p>
                <p> Phone: (053) 123-4567</p>
            </div>

            <p style="margin-top: 20px; font-size: 12px; color: #888;">
                This is an automated message from EnrollAssess. Please do not reply to this email.
                If you have questions, contact the Computer Studies Department directly.
            </p>
        </div>
    </div>
</body>
</html>

