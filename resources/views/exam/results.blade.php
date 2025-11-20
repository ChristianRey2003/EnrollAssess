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
            background: #f5f5f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
        }

        .results-container {
            background: white;
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .results-header {
            background: #800020;
            color: white;
            padding: 20px;
            text-align: center;
            flex-shrink: 0;
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
            padding: 32px;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }

        /* Custom scrollbar styling */
        .results-body::-webkit-scrollbar {
            width: 8px;
        }

        .results-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .results-body::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        .results-body::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
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

        .confirmation-message {
            font-size: 14px;
            font-weight: 600;
            color: #059669;
            margin-bottom: 16px;
            text-align: center;
        }

        .applicant-name {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 24px;
        }

        .next-steps-section {
            background: #f0f9ff;
            border-left: 3px solid #3b82f6;
            border-radius: 6px;
            padding: 14px;
            margin-top: 0;
        }

        .next-steps-section h3 {
            font-size: 15px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 10px;
        }

        .next-steps-section p {
            font-size: 12px;
            color: #1e40af;
            line-height: 1.5;
            margin: 0;
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
                padding: 10px;
            }

            .results-container {
                max-height: 95vh;
            }

            .results-header {
                padding: 16px 20px;
            }

            .results-header h1 {
                font-size: 20px;
            }

            .results-body {
                padding: 24px;
            }

            .confirmation-message {
                font-size: 13px;
            }

            .applicant-name {
                font-size: 20px;
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
        </div>

        <div class="results-body">
            <!-- Confirmation Message -->
            <div class="confirmation-message">Good job! You've completed the examination.</div>

            <!-- Applicant Name -->
            <div class="applicant-name">
                {{ $applicant->full_name ?? 'Applicant' }}
            </div>

            <!-- Next Steps Section -->
            <div class="next-steps-section">
                <h3>Next Steps</h3>
                <p>You will receive an email notification regarding your interview schedule. Please check your email regularly and ensure your contact information is up to date.</p>
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
