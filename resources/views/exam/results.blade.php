<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Your Exam Results - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- University Theme CSS -->
    <link href="{{ asset('css/auth/university-auth.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card results-card">
            <!-- University Header -->
            <div class="auth-header">
                <div class="university-logo">
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo" style="width: 60px; height: 60px; object-fit: contain;">
                </div>
                <h1 class="university-name">Your Exam Results</h1>
                <p class="auth-subtitle">BSIT Entrance Examination</p>
            </div>

            <!-- Results Content -->
            <div class="auth-body">
                <!-- Score Summary Card -->
                <div class="results-summary">
                    <div class="score-display">
                        <div class="score-number">{{ $score ?? '18' }}</div>
                        <div class="score-total">/ {{ $totalQuestions ?? '20' }}</div>
                    </div>
                    <div class="score-percentage">
                        {{ $percentage ?? '90' }}% Score
                    </div>
                </div>

                <!-- Status Message -->
                <div class="status-message {{ ($percentage ?? 90) >= 75 ? 'status-passed' : 'status-failed' }}">
                    <div class="status-text">
                        <h3>{{ (($percentage ?? 90) >= 75) ? 'Congratulations' : 'Results Complete' }}</h3>
                        <p>
                            @if(($percentage ?? 90) >= 75)
                                You have <strong>passed</strong> the BSIT entrance examination.
                            @else
                                Thank you for taking the BSIT entrance examination.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Performance Breakdown -->
                <div class="performance-breakdown">
                    <h4 class="breakdown-title">Performance Summary</h4>
                    <div class="breakdown-stats">
                        <div class="stat-item">
                            <span class="stat-label">Correct Answers</span>
                            <span class="stat-value correct">{{ $correctAnswers ?? '18' }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Incorrect Answers</span>
                            <span class="stat-value incorrect">{{ $incorrectAnswers ?? '2' }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Time Taken</span>
                            <span class="stat-value">{{ $timeTaken ?? '24:30' }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Completion Date</span>
                            <span class="stat-value">{{ $completionDate ?? now()->format('M d, Y - g:i A') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Category Performance -->
                @if(isset($categoryPerformance) || true)
                <div class="category-performance">
                    <h4 class="breakdown-title">Performance by Category</h4>
                    <div class="category-grid">
                        @php
                            $categories = $categoryPerformance ?? [
                                ['name' => 'Programming', 'correct' => 5, 'total' => 6, 'percentage' => 83],
                                ['name' => 'Database', 'correct' => 4, 'total' => 5, 'percentage' => 80],
                                ['name' => 'Networking', 'correct' => 4, 'total' => 4, 'percentage' => 100],
                                ['name' => 'Data Structures', 'correct' => 3, 'total' => 3, 'percentage' => 100],
                                ['name' => 'Software Engineering', 'correct' => 2, 'total' => 2, 'percentage' => 100],
                            ];
                        @endphp
                        @foreach($categories as $category)
                        <div class="category-item">
                            <div class="category-name">{{ $category['name'] }}</div>
                            <div class="category-score">{{ $category['correct'] }}/{{ $category['total'] }}</div>
                            <div class="category-bar">
                                <div class="category-progress" style="width: {{ $category['percentage'] }}%"></div>
                            </div>
                            <div class="category-percentage">{{ $category['percentage'] }}%</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Next Steps -->
                <div class="next-steps">
                    <h4 class="next-steps-title">What's Next?</h4>
                    @if(($percentage ?? 90) >= 75)
                        <div class="next-steps-content">
                            <div class="step-item">
                                <div class="step-text">
                                    <strong>Email Notification:</strong> You will receive an email from the Computer Studies Department within 2-3 business days.
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-text">
                                    <strong>Interview Schedule:</strong> The email will contain your interview date, time, and location details.
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-text">
                                    <strong>Required Documents:</strong> Please prepare your academic transcripts, certificates, and valid ID for the interview.
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="next-steps-content">
                            <div class="step-item">
                                <div class="step-text">
                                    <strong>Result Notification:</strong> You will receive an official result letter via email within 2-3 business days.
                                </div>
                            </div>
                            <div class="step-item">
                                <div class="step-text">
                                    <strong>Future Opportunities:</strong> You may reapply for the next admission period. Check the university website for dates.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Contact Information -->
                <div class="contact-info">
                    <h4 class="contact-title">Need Help?</h4>
                    <div class="contact-details">
                        <div class="contact-item"><span class="contact-text">Phone: (123) 456-7890</span></div>
                        <div class="contact-item"><span class="contact-text">Email: admissions@university.edu</span></div>
                        <div class="contact-item"><span class="contact-text">Office: Computer Studies Department, Room 201</span></div>
                    </div>
                </div>

                <!-- Print/Download Actions -->
                <div class="result-actions">
                    <button onclick="window.print()" class="btn-secondary">Print Results</button>
                    <button onclick="downloadPDF()" class="btn-secondary">Download PDF</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function downloadPDF() {
            // In a real application, this would generate and download a PDF
            alert('PDF download feature would be implemented here. (Demo mode)');
        }

        // Disable back button to prevent returning to exam
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };

        // Add celebration animation for passed students
        document.addEventListener('DOMContentLoaded', function() {
            const percentage = {{ $percentage ?? 90 }};
            if (percentage >= 75) {
                // Add subtle celebration effect
                setTimeout(() => {
                    document.querySelector('.score-display').classList.add('celebrate');
                }, 500);
            }
        });
    </script>

    <style>
        /* Additional styles for results page */
        .results-card {
            max-width: 600px;
        }

        .results-summary {
            background: linear-gradient(135deg, var(--yellow-light) 0%, var(--white) 100%);
            border: 2px solid var(--yellow-primary);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .results-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--maroon-primary) 0%, var(--yellow-primary) 100%);
        }

        .score-display {
            display: flex;
            align-items: baseline;
            justify-content: center;
            margin-bottom: 12px;
        }

        .score-number {
            font-size: 48px;
            font-weight: 700;
            color: var(--maroon-primary);
            line-height: 1;
        }

        .score-total {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-gray);
            margin-left: 4px;
        }

        .score-percentage {
            font-size: 20px;
            font-weight: 600;
            color: var(--maroon-primary);
        }

        .score-display.celebrate {
            animation: celebrate 0.6s ease-out;
        }

        @keyframes celebrate {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .status-message {
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .status-passed {
            background: #dcfce7;
            border: 2px solid #22c55e;
        }

        .status-failed {
            background: #fef2f2;
            border: 2px solid #ef4444;
        }

        .status-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .status-text h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--maroon-primary);
        }

        .status-text p {
            margin: 0;
            color: var(--text-gray);
            line-height: 1.5;
        }

        .performance-breakdown, .category-performance, .next-steps, .contact-info {
            background: var(--light-gray);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .breakdown-title, .next-steps-title, .contact-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin: 0 0 16px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--yellow-primary);
        }

        .breakdown-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--border-gray);
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-gray);
            font-weight: 500;
        }

        .stat-value {
            font-weight: 600;
            color: var(--maroon-primary);
        }

        .stat-value.correct {
            color: #22c55e;
        }

        .stat-value.incorrect {
            color: #ef4444;
        }

        .category-grid {
            display: grid;
            gap: 16px;
        }

        .category-item {
            display: grid;
            grid-template-columns: 1fr auto 2fr auto;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--border-gray);
        }

        .category-name {
            font-weight: 500;
            color: var(--maroon-primary);
            font-size: 14px;
        }

        .category-score {
            font-weight: 600;
            color: var(--text-gray);
            font-size: 14px;
        }

        .category-bar {
            background: var(--border-gray);
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }

        .category-progress {
            height: 100%;
            background: linear-gradient(90deg, var(--maroon-primary) 0%, var(--yellow-primary) 100%);
            border-radius: 4px;
            transition: width 1s ease-out;
        }

        .category-percentage {
            font-weight: 600;
            color: var(--maroon-primary);
            font-size: 14px;
            text-align: right;
        }

        .next-steps-content {
            display: grid;
            gap: 16px;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px;
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--border-gray);
        }

        .step-icon {
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .step-text {
            flex: 1;
            font-size: 14px;
            line-height: 1.5;
            color: var(--text-gray);
        }

        .step-text strong {
            color: var(--maroon-primary);
            font-weight: 600;
        }

        .contact-details {
            display: grid;
            gap: 12px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--border-gray);
        }

        .contact-icon {
            font-size: 16px;
            flex-shrink: 0;
        }

        .contact-text {
            font-size: 14px;
            color: var(--text-gray);
        }

        .result-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-secondary {
            padding: 12px 24px;
            background: var(--white);
            color: var(--maroon-primary);
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-secondary:hover {
            background: var(--yellow-light);
            border-color: var(--yellow-primary);
        }

        /* Print styles */
        @media print {
            .auth-page {
                background: white !important;
            }
            
            .auth-container {
                box-shadow: none;
                padding: 0;
            }
            
            .auth-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .result-actions {
                display: none;
            }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .breakdown-stats {
                grid-template-columns: 1fr;
            }
            
            .category-item {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto auto auto;
                text-align: center;
                gap: 8px;
            }
            
            .category-bar {
                order: 3;
            }
            
            .result-actions {
                flex-direction: column;
            }
            
            .score-number {
                font-size: 36px;
            }
            
            .score-total {
                font-size: 20px;
            }
        }
    </style>
</body>
</html>