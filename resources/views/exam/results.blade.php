<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Exam Results - {{ config('app.name', 'EnrollAssess') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .results-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .results-header {
            background: #800020;
            color: white;
            padding: 20px 24px;
            text-align: center;
        }

        .results-header h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .results-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .results-body {
            padding: 24px;
        }

        .score-display {
            text-align: center;
            margin-bottom: 20px;
        }

        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            position: relative;
        }

        .score-circle.passed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
        }

        .score-circle.failed {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
        }

        .score-percentage {
            font-size: 36px;
            font-weight: 700;
            color: white;
            line-height: 1;
        }

        .score-label {
            font-size: 11px;
            color: white;
            opacity: 0.9;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-badge.passed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .message-box {
            background: #fef3c7;
            border: 2px solid #fbbf24;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            margin-top: 20px;
        }

        .message-box h3 {
            font-size: 15px;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 8px;
        }

        .message-box p {
            font-size: 13px;
            color: #78350f;
            line-height: 1.5;
        }

        .info-section {
            background: #f0f9ff;
            border-left: 3px solid #3b82f6;
            border-radius: 6px;
            padding: 14px;
            margin-top: 16px;
        }

        .info-section p {
            font-size: 12px;
            color: #1e40af;
            line-height: 1.5;
        }

        .action-buttons {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .action-btn {
            display: inline-block;
            padding: 12px 32px;
            background: #800020;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.2s;
            text-align: center;
        }

        .action-btn:hover {
            background: #5c0017;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 32, 0.3);
        }

        @media (max-width: 640px) {
            body {
                padding: 12px;
            }

            .results-header {
                padding: 16px 20px;
            }

            .results-header h1 {
                font-size: 20px;
            }

            .results-body {
                padding: 20px 16px;
            }

            .score-circle {
                width: 100px;
                height: 100px;
            }

            .score-percentage {
                font-size: 30px;
            }

            .stat-value {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="results-container">
        <div class="results-header">
            <h1>Examination Complete</h1>
            <p>{{ $applicant->full_name ?? 'Applicant' }}</p>
        </div>

        <div class="results-body">
            <!-- Score Display -->
            <div class="score-display">
                <div class="score-circle {{ $stats['passed'] ? 'passed' : 'failed' }}">
                    <div class="score-percentage">{{ round($stats['percentage']) }}%</div>
                    <div class="score-label">Your Score</div>
                </div>
                <span class="status-badge {{ $stats['passed'] ? 'passed' : 'failed' }}">
                    {{ $stats['passed'] ? 'Passed' : 'Failed' }}
                </span>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['correct_answers'] }}/{{ $stats['total_questions'] }}</div>
                    <div class="stat-label">Correct Answers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['earned_points'] }}/{{ $stats['total_points'] }}</div>
                    <div class="stat-label">Points Earned</div>
                </div>
            </div>

            <!-- Next Steps Message -->
            <div class="message-box">
                <h3> Next Steps</h3>
                <p>Please wait for the email interview.</p>
            </div>

            <!-- Additional Information -->
            <div class="info-section">
                <p>
                    <strong>Important:</strong> You will receive an email notification regarding your interview schedule. 
                    Please check your email regularly and ensure your contact information is up to date.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('applicant.login') }}" class="action-btn">Go to Home</a>
            </div>
        </div>
    </div>

    <script>
        // Prevent going back to exam interface using browser back button
        (function() {
            // Replace the current history state to prevent back navigation
            if (window.history && window.history.pushState) {
                // Push a new state to prevent going back
                window.history.pushState(null, null, window.location.href);
                
                // Listen for back button press
                window.addEventListener('popstate', function(event) {
                    // Push state again to keep user on results page
                    window.history.pushState(null, null, window.location.href);
                    
                    // Optional: Show a message
                    alert('You cannot go back to the exam. The exam has been completed and submitted.');
                });
            }

            // Additional prevention: disable context menu and keyboard shortcuts
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });

            // Prevent refresh/close warnings but not the action itself
            window.addEventListener('beforeunload', function(e) {
                // Don't prevent unload, just let them know the exam is complete
                return undefined;
            });
        })();
    </script>
</body>
</html>
