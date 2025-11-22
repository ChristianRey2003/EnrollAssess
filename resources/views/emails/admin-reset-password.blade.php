<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Faculty Portal</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #800020 0%, #5c0017 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #800020 0%, #FFD700 100%);
        }
        .university-logo {
            margin-bottom: 15px;
        }
        .university-logo img {
            max-width: 80px;
            height: auto;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.95;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #1f2937;
        }
        .message {
            color: #4b5563;
            margin-bottom: 25px;
            line-height: 1.8;
        }
        .reset-button-container {
            text-align: center;
            margin: 30px 0;
        }
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #800020 0%, #5c0017 100%);
            color: white !important;
            padding: 14px 40px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(128, 0, 32, 0.3);
            transition: all 0.3s ease;
        }
        .reset-button:hover {
            background: linear-gradient(135deg, #5c0017 0%, #800020 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(128, 0, 32, 0.4);
        }
        .info-box {
            background: linear-gradient(135deg, #fff8dc 0%, #ffeaa7 100%);
            border: 2px solid #FFD700;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .info-box-title {
            font-size: 16px;
            font-weight: 600;
            color: #800020;
            margin: 0 0 12px 0;
            display: flex;
            align-items: center;
        }
        .info-box-title::before {
            content: '⚠️';
            margin-right: 8px;
            font-size: 18px;
        }
        .info-box-content {
            color: #5c0017;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }
        .expiry-info {
            background: #f0f9ff;
            border-left: 4px solid #800020;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 0 6px 6px 0;
        }
        .expiry-info p {
            margin: 0;
            color: #1e40af;
            font-size: 14px;
        }
        .expiry-info strong {
            color: #800020;
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
        .footer {
            background: #f9fafb;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #6b7280;
        }
        .footer-logo {
            color: #800020;
            font-weight: 600;
            font-size: 16px;
        }
        .url-fallback {
            background: #f3f4f6;
            border: 1px dashed #d1d5db;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            word-break: break-all;
        }
        .url-fallback p {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
        }
        .url-fallback a {
            color: #800020;
            font-size: 12px;
            word-break: break-all;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .email-body {
                padding: 25px 20px;
            }
            .reset-button {
                padding: 12px 30px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="university-logo">
                <img src="{{ asset('images/evsu-logo.png') }}" alt="EVSU Logo" onerror="this.style.display='none'">
            </div>
            <h1>Faculty Portal</h1>
            <p>Password Reset Request</p>
        </div>

        <div class="email-body">
            <div class="greeting">
                Hello {{ $userName ?? 'User' }}!
            </div>

            <div class="message">
                <p>You are receiving this email because we received a password reset request for your Faculty Portal account.</p>
                <p>Click the button below to reset your password. If you did not request a password reset, you can safely ignore this email.</p>
            </div>

            <div class="reset-button-container">
                <a href="{{ $resetUrl }}" class="reset-button">
                    Reset Password
                </a>
            </div>

            <div class="url-fallback">
                <p>If the button doesn't work, copy and paste this link into your browser:</p>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </div>

            <div class="expiry-info">
                <p><strong>⏰ Important:</strong> This password reset link will expire in <strong>{{ $expireMinutes ?? 60 }} minutes</strong>.</p>
            </div>

            <div class="info-box">
                <div class="info-box-title">Security Reminder</div>
                <div class="info-box-content">
                    For your security, please:
                    <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                        <li>Never share your password reset link with anyone</li>
                        <li>Choose a strong, unique password</li>
                        <li>If you didn't request this, please contact the administrator immediately</li>
                    </ul>
                </div>
            </div>

            <div class="security-note">
                <p><strong>Didn't request this?</strong> If you didn't request a password reset, no further action is required. Your account remains secure. If you're concerned about your account security, please contact the system administrator.</p>
            </div>
        </div>

        <div class="footer">
            <p class="footer-logo">{{ config('app.name', 'EnrollAssess') }}</p>
            <p>Computer Studies Department</p>
            <p>Eastern Visayas State University</p>
            <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>

