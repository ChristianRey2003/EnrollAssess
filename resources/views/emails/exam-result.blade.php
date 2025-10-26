<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result Notification</title>
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
        .email-header.passed {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        .email-header.failed {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
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
        .result-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 3px solid #3b82f6;
            border-radius: 12px;
            padding: 30px;
            margin: 20px 0;
            text-align: center;
        }
        .result-box.passed {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-color: #059669;
        }
        .result-box.failed {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #dc2626;
        }
        .result-status {
            font-size: 48px;
            margin: 0 0 15px 0;
        }
        .result-label {
            font-size: 16px;
            color: #6b7280;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .result-text {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }
        .result-text.passed {
            color: #047857;
        }
        .result-text.failed {
            color: #b91c1c;
        }
        .score-details {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid rgba(0, 0, 0, 0.1);
        }
        .score-item {
            text-align: center;
        }
        .score-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 5px 0;
        }
        .score-label {
            font-size: 12px;
            color: #6b7280;
            margin: 0;
            text-transform: uppercase;
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
        .next-steps {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            margin: 20px 0;
        }
        .next-steps.passed {
            background: #d1fae5;
            border-color: #059669;
        }
        .next-steps.failed {
            background: #fee2e2;
            border-color: #dc2626;
        }
        .next-steps h3 {
            margin: 0 0 10px 0;
            color: #1f2937;
            font-size: 16px;
        }
        .next-steps ul {
            margin: 0;
            padding-left: 20px;
        }
        .next-steps li {
            color: #4b5563;
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
            .score-details {
                flex-direction: column;
                gap: 15px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header {{ $passed ? 'passed' : 'failed' }}">
            <h1>BSIT Entrance Exam Results</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Computer Studies Department</p>
        </div>

        <div class="email-body">
            <div class="greeting">
                Dear {{ $applicant->full_name }},
            </div>

            <p>
                Your BSIT Entrance Examination has been evaluated. Below are your exam results:
            </p>

            <!-- Result Status Box -->
            <div class="result-box {{ $passed ? 'passed' : 'failed' }}">
                <div class="result-status">
                    @if($passed)
                        
                    @else
                        
                    @endif
                </div>
                <p class="result-label">Exam Result</p>
                <h2 class="result-text {{ $passed ? 'passed' : 'failed' }}">
                    @if($passed)
                        PASSED
                    @else
                        NEEDS REVIEW
                    @endif
                </h2>

                <!-- Score Details -->
                <div class="score-details">
                    <div class="score-item">
                        <p class="score-value">{{ $score }}</p>
                        <p class="score-label">Score Achieved</p>
                    </div>
                    <div class="score-item">
                        <p class="score-value">{{ $totalQuestions }}</p>
                        <p class="score-label">Total Questions</p>
                    </div>
                    <div class="score-item">
                        <p class="score-value">{{ $percentage }}%</p>
                        <p class="score-label">Percentage</p>
                    </div>
                    <div class="score-item">
                        <p class="score-value">{{ $passingScore }}%</p>
                        <p class="score-label">Passing Score</p>
                    </div>
                </div>
            </div>

            <!-- Application Information -->
            <div class="info-box">
                <div class="info-row">
                    <div class="info-label">Application No:</div>
                    <div class="info-value">{{ $applicant->application_no }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Exam Date:</div>
                    <div class="info-value">{{ $result->created_at->format('F d, Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Time Taken:</div>
                    <div class="info-value">{{ $result->time_taken ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Result Status:</div>
                    <div class="info-value">
                        <strong style="color: {{ $passed ? '#047857' : '#b91c1c' }}">
                            {{ $passed ? 'PASSED' : 'NEEDS REVIEW' }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="next-steps {{ $passed ? 'passed' : 'failed' }}">
                <h3>
                    @if($passed)
                         Next Steps
                    @else
                         What Happens Next
                    @endif
                </h3>
                @if($passed)
                <p style="margin: 0 0 10px 0; color: #047857; font-weight: 600;">
                    Congratulations on passing the entrance examination!
                </p>
                <ul>
                    <li>You will be contacted for the next stage of the enrollment process</li>
                    <li>Please prepare the required documents for enrollment</li>
                    <li>Check your email regularly for further instructions</li>
                    <li>You may be scheduled for an interview session</li>
                </ul>
                @else
                <p style="margin: 0 0 10px 0; color: #b91c1c; font-weight: 600;">
                    Your application requires further review by the admissions committee.
                </p>
                <ul>
                    <li>The Computer Studies Department will review your application</li>
                    <li>You will be notified of the final decision within 5-7 business days</li>
                    <li>Additional assessment may be required</li>
                    <li>Please check your email regularly for updates</li>
                </ul>
                @endif
            </div>

            <p>
                If you have any questions or concerns about your results, please contact the 
                Computer Studies Department during office hours.
            </p>

            @if($passed)
            <p style="margin-top: 20px; font-weight: 600; color: #047857;">
                We look forward to welcoming you to the BSIT program!
            </p>
            @else
            <p style="margin-top: 20px;">
                Thank you for your interest in the BSIT program. We appreciate the time and effort 
                you invested in your application.
            </p>
            @endif
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

