<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Assignment Notification - EnrollAssess</title>
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
        .assignment-info {
            background: #EFF6FF;
            border-left: 4px solid #800020;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .assignment-info h3 {
            margin-top: 0;
            color: #800020;
            font-size: 18px;
        }
        .info-row {
            margin: 10px 0;
            font-size: 14px;
        }
        .info-label {
            font-weight: 600;
            color: #374151;
            display: inline-block;
            min-width: 150px;
        }
        .info-value {
            color: #1F2937;
        }
        .applicants-list {
            margin: 20px 0;
        }
        .applicants-list h3 {
            color: #800020;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .applicant-item {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
        }
        .applicant-name {
            font-weight: 600;
            color: #1F2937;
            font-size: 15px;
            margin-bottom: 5px;
        }
        .applicant-details {
            font-size: 13px;
            color: #6B7280;
            margin-top: 5px;
        }
        .message-box {
            background: #FFF8DC;
            border: 1px solid #FDE68A;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            font-size: 14px;
            color: #92400E;
        }
        .footer {
            background: #F9FAFB;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #6B7280;
            border-top: 1px solid #E5E7EB;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #800020;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        .button:hover {
            background: #5C0016;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="university-name">Eastern Visayas State University</div>
            <div class="department-name">BSIT Department - EnrollAssess System</div>
        </div>
        
        <div class="content">
            <div class="greeting">
                Dear <strong>{{ $instructor->full_name }}</strong>,
            </div>
            
            <p>You have been assigned {{ $applicants->count() }} applicant(s) for interview scheduling.</p>
            
            <div class="assignment-info">
                <h3>Assignment Details</h3>
                <div class="info-row">
                    <span class="info-label">Interview Period:</span>
                    <span class="info-value">
                        {{ \Carbon\Carbon::parse($interviewStartDate)->format('F d, Y') }} - 
                        {{ \Carbon\Carbon::parse($interviewEndDate)->format('F d, Y') }}
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Number of Applicants:</span>
                    <span class="info-value">{{ $applicants->count() }}</span>
                </div>
            </div>

            @if($assignmentMessage)
            <div class="message-box">
                <strong>Additional Instructions:</strong><br>
                {{ $assignmentMessage }}
            </div>
            @endif

            <div class="applicants-list">
                <h3>Assigned Applicants</h3>
                @foreach($applicants as $applicant)
                <div class="applicant-item">
                    <div class="applicant-name">{{ $applicant->full_name }}</div>
                    <div class="applicant-details">
                        Application No: {{ $applicant->application_no ?: $applicant->formatted_applicant_no }}<br>
                        Email: {{ $applicant->email_address }}<br>
                        @if($applicant->phone_number)
                        Phone: {{ $applicant->phone_number }}<br>
                        @endif
                        Status: {{ ucwords(str_replace('-', ' ', $applicant->status ?? 'pending')) }}
                    </div>
                </div>
                @endforeach
            </div>

            <p style="margin-top: 30px;">
                Please log in to your instructor portal to schedule interviews with these applicants within the specified deadline period.
            </p>

            <div style="text-align: center;">
                <a href="{{ url('/instructor/applicants') }}" class="button">View Applicants Portal</a>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from the EnrollAssess System.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>

