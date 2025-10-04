<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSIT Entrance Exam - Set Assignment</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .email-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1f2937;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #6b7280;
            margin: 5px 0 0 0;
            font-size: 16px;
        }
        .content {
            margin-bottom: 30px;
        }
        .exam-details {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .exam-details h3 {
            margin: 0 0 15px 0;
            color: #1f2937;
            font-size: 18px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #374151;
        }
        .detail-value {
            color: #1f2937;
        }
        .exam-set-highlight {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
        .seating-instructions {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .seating-instructions h3 {
            margin: 0 0 15px 0;
            color: #92400e;
            font-size: 18px;
        }
        .seating-instructions p {
            margin: 0;
            color: #92400e;
            font-weight: 500;
        }
        .reminders {
            background: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .reminders h3 {
            margin: 0 0 15px 0;
            color: #065f46;
            font-size: 18px;
        }
        .reminders ul {
            margin: 0;
            padding-left: 20px;
            color: #065f46;
        }
        .reminders li {
            margin-bottom: 8px;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .contact-info {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .contact-info p {
            margin: 5px 0;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>BSIT Entrance Examination</h1>
            <p>Exam Set Assignment Confirmation</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $applicant_name }}</strong>,</p>
            
            <p>We are pleased to inform you that your exam set assignment has been confirmed for the BSIT Entrance Examination.</p>

            <div class="exam-details">
                <h3>Examination Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Application Number:</span>
                    <span class="detail-value">{{ $application_no }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Assigned Exam Set:</span>
                    <span class="detail-value exam-set-highlight">Set {{ $exam_set_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Exam Title:</span>
                    <span class="detail-value">{{ $exam_title }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ $exam_date }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Time:</span>
                    <span class="detail-value">{{ $exam_time }}</span>
                </div>
                @if($access_code)
                <div class="detail-row">
                    <span class="detail-label">Access Code:</span>
                    <span class="detail-value" style="font-family: monospace; font-weight: bold;">{{ $access_code }}</span>
                </div>
                @endif
            </div>

            <div class="seating-instructions">
                <h3>Seating Instructions</h3>
                <p>{{ $instructions }}</p>
            </div>

            <div class="reminders">
                <h3>Important Reminders</h3>
                <ul>
                    <li>Arrive at the examination venue 30 minutes before the scheduled time</li>
                    <li>Bring a valid ID and your application form</li>
                    <li>Bring necessary writing materials (pen, pencil, eraser)</li>
                    <li>Mobile phones and electronic devices are not allowed during the exam</li>
                    <li>Follow all examination protocols and guidelines</li>
                    <li>Late arrivals may not be permitted to take the examination</li>
                </ul>
            </div>

            <div class="contact-info">
                <p><strong>Need Help?</strong></p>
                <p>If you have any questions or concerns, please contact the admissions office.</p>
                <p>Email: admissions@university.edu | Phone: (123) 456-7890</p>
            </div>

            <p>Good luck with your examination!</p>
        </div>

        <div class="footer">
            <p>Best regards,<br>
            <strong>BSIT Admissions Office</strong></p>
            <p style="margin-top: 15px; font-size: 12px;">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
