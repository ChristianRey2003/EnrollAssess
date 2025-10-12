<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #1f2937;
        }
        .info-box {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #4b5563;
            min-width: 140px;
        }
        .info-value {
            color: #1f2937;
            font-weight: 500;
        }
        .access-code-box {
            background: #eff6ff;
            border: 2px dashed #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .access-code-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .access-code {
            font-size: 32px;
            font-weight: 700;
            color: #1e40af;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
        }
        .exam-link-box {
            background: #f0fdf4;
            border: 2px solid #86efac;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .exam-link {
            display: inline-block;
            background: #16a34a;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin-top: 10px;
        }
        .exam-link:hover {
            background: #15803d;
        }
        .instructions {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin: 0 0 10px 0;
            color: #92400e;
            font-size: 16px;
        }
        .instructions ul {
            margin: 0;
            padding-left: 20px;
        }
        .instructions li {
            color: #78350f;
            margin: 5px 0;
        }
        .footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                margin-bottom: 5px;
            }
            .access-code {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>BSIT Entrance Examination</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Computer Studies Department</p>
        </div>

        <div class="email-body">
            <div class="greeting">
                Dear {{ $applicant->full_name }},
            </div>

            <p>
                You have been assigned to take the <strong>BSIT Entrance Examination</strong>. 
                Below are the details you need to access your exam:
            </p>

            <div class="info-box">
                <div class="info-row">
                    <div class="info-label">Application No:</div>
                    <div class="info-value">{{ $applicant->application_no }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Exam Date:</div>
                    <div class="info-value">{{ $examDate }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Exam Time:</div>
                    <div class="info-value">{{ $examTime }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Exam Venue:</div>
                    <div class="info-value">{{ $examVenue }}</div>
                </div>
            </div>

            <div class="access-code-box">
                <div class="access-code-label">Your Access Code</div>
                <div class="access-code">{{ $accessCode }}</div>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: #6b7280;">
                    Keep this code secure. You will need it to access your exam.
                </p>
            </div>

            <div class="exam-link-box">
                <p style="margin: 0 0 10px 0; font-weight: 600; color: #166534;">
                    Access Your Exam
                </p>
                <a href="{{ url('/applicant/login') }}" class="exam-link">
                    Go to Exam Portal
                </a>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: #6b7280;">
                    {{ url('/applicant/login') }}
                </p>
            </div>

            @if($specialInstructions)
            <div style="background: #f0f9ff; border: 2px solid #3b82f6; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h3 style="margin: 0 0 10px 0; color: #1e40af; font-size: 16px;">📋 Special Instructions</h3>
                <p style="margin: 0; color: #1e40af; line-height: 1.6; white-space: pre-line;">{{ $specialInstructions }}</p>
            </div>
            @endif

            <div class="instructions">
                <h3>Important Instructions:</h3>
                <ul>
                    <li>Please arrive 15 minutes before the scheduled exam time</li>
                    <li>Bring a valid ID for verification</li>
                    <li>Make sure you have a stable internet connection</li>
                    <li>Use a desktop or laptop computer (mobile devices not recommended)</li>
                    <li>Ensure your browser allows fullscreen mode</li>
                    <li>Do not refresh the page during the exam</li>
                    <li>The exam must be completed in one sitting</li>
                </ul>
            </div>

            <p>
                If you have any questions or concerns, please contact the Computer Studies Department 
                before your scheduled exam date.
            </p>

            <p style="margin-top: 20px;">
                <strong>Good luck with your examination!</strong>
            </p>
        </div>

        <div class="footer">
            <p><strong>Computer Studies Department</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p style="margin-top: 10px; font-size: 12px;">
                &copy; {{ date('Y') }} EnrollAssess System. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>

