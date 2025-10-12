<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Scheduled</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 20px -30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            min-width: 140px;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .button {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #777;
            text-align: center;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗓️ Interview Scheduled</h1>
        </div>

        <p>Dear <strong>{{ $applicant->full_name }}</strong>,</p>

        <p>Your interview has been scheduled! Please see the details below:</p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Application No:</span>
                <span class="info-value">{{ $applicant->application_no }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Interview Date:</span>
                <span class="info-value">{{ $scheduleDate }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Interview Time:</span>
                <span class="info-value">{{ $scheduleTime }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Interviewer:</span>
                <span class="info-value">{{ $instructor->name ?? 'TBA' }}</span>
            </div>
        </div>

        @if($interview->notes)
        <div class="highlight">
            <strong>Additional Notes:</strong><br>
            {{ $interview->notes }}
        </div>
        @endif

        <div style="margin-top: 25px;">
            <p><strong>Important Reminders:</strong></p>
            <ul>
                <li>Please arrive 10 minutes before your scheduled time</li>
                <li>Bring a valid ID for verification</li>
                <li>Prepare your portfolio or relevant documents</li>
                <li>Dress appropriately for the interview</li>
            </ul>
        </div>

        <p style="margin-top: 25px;">
            If you need to reschedule or have any questions, please contact us as soon as possible.
        </p>

        <div class="footer">
            <p>
                This is an automated email from {{ config('app.name') }}.<br>
                Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>

