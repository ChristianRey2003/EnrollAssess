<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnrollAssess Exam Access Code</title>
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
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .university-logo {
            margin-bottom: 15px;
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
        .access-code-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #FFD700;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .access-code-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .access-code {
            font-size: 32px;
            font-weight: bold;
            color: #8B0000;
            letter-spacing: 3px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        .exam-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        .exam-info h3 {
            margin: 0 0 15px 0;
            color: #1976d2;
            font-size: 16px;
        }
        .info-item {
            margin: 8px 0;
            display: flex;
            align-items: center;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            min-width: 120px;
        }
        .exam-button {
            display: inline-block;
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(139, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        .exam-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(139, 0, 0, 0.4);
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .instructions h3 {
            margin: 0 0 15px 0;
            color: #856404;
            font-size: 16px;
        }
        .instructions ul {
            margin: 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
            color: #856404;
        }
        .warning {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
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
        .contact-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
        .social-links {
            margin-top: 15px;
        }
        .social-links a {
            color: #8B0000;
            text-decoration: none;
            margin: 0 10px;
        }
        .expires-info {
            color: #dc3545;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
        }
        @media (max-width: 600px) {
            .content {
                padding: 20px 15px;
            }
            .header {
                padding: 20px 15px;
            }
            .access-code {
                font-size: 24px;
                letter-spacing: 2px;
            }
            .exam-button {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="university-logo">
                🏛️
            </div>
            <h1 class="university-name">Eastern Visayas State University</h1>
            <p class="department-name">Computer Studies Department</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Dear {{ $applicant->full_name }},
            </div>

            <p>Congratulations! You have been pre-qualified for the <strong>Computer Studies Department</strong> entrance evaluation process.</p>

            <p>Your application has been reviewed and you are now eligible to take the computerized entrance examination. Please use the access code below to begin your exam.</p>

            <!-- Access Code Section -->
            <div class="access-code-section">
                <div class="access-code-label">Your Exam Access Code</div>
                <div class="access-code">{{ $accessCode->code }}</div>
                @if($expiresAt)
                <div class="expires-info">
                    ⏰ Expires: {{ $expiresAt->format('F j, Y \a\t g:i A') }}
                </div>
                @endif
            </div>

            <!-- Exam Information -->
            <div class="exam-info">
                <h3>📋 Exam Information</h3>
                <div class="info-item">
                    <span class="info-label">Application No:</span>
                    <span>{{ $applicant->application_no }}</span>
                </div>
                @if($applicant->accessCode && $applicant->accessCode->exam)
                <div class="info-item">
                    <span class="info-label">Exam:</span>
                    <span>{{ $applicant->accessCode->exam->title }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Duration:</span>
                    <span>{{ $applicant->accessCode->exam->formatted_duration }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Format:</span>
                    <span>Online Computer-Based Test</span>
                </div>
            </div>

            <!-- Take Exam Button -->
            <div style="text-align: center;">
                <a href="{{ $examUrl }}" class="exam-button">
                    🖥️ Start Your Exam
                </a>
            </div>

            <!-- Instructions -->
            <div class="instructions">
                <h3>📝 Important Instructions</h3>
                <ul>
                    <li><strong>Use a reliable computer</strong> with stable internet connection</li>
                    <li><strong>Find a quiet environment</strong> free from distractions</li>
                    <li><strong>Have a valid ID ready</strong> for verification purposes</li>
                    <li><strong>Do not refresh or close</strong> the browser during the exam</li>
                    <li><strong>Complete the exam in one sitting</strong> - you cannot pause and resume</li>
                    <li><strong>Answer all questions</strong> before submitting your exam</li>
                </ul>
            </div>

            <!-- Warning -->
            <div class="warning">
                <strong>⚠️ Important:</strong> This access code is unique to you and can only be used once. Do not share it with anyone. If you encounter any technical issues during the exam, immediately contact the Computer Studies Department.
            </div>

            <p>We wish you the best of luck with your examination. This is an important step toward joining our Computer Studies program.</p>

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
            
            <div class="contact-info">
                <p>📧 Email: cs.department@evsu.edu.ph</p>
                <p>📞 Phone: (053) 123-4567</p>
                <p>🌐 Website: www.evsu.edu.ph</p>
            </div>

            <div class="social-links">
                <a href="#">Facebook</a> |
                <a href="#">Twitter</a> |
                <a href="#">LinkedIn</a>
            </div>

            <p style="margin-top: 20px; font-size: 12px; color: #888;">
                This is an automated message from EnrollAssess. Please do not reply to this email.
                If you have questions, contact the Computer Studies Department directly.
            </p>
        </div>
    </div>
</body>
</html>