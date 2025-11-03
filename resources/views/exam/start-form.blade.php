<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Start Exam - EnrollAssess</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            max-width: 600px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: #800020;
            color: white;
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .progress-indicator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 0 20px;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .progress-step::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: -1;
        }

        .progress-step:last-child::after {
            display: none;
        }

        .progress-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .progress-step.completed .progress-number {
            background: #10b981;
            color: white;
        }

        .progress-step.active .progress-number {
            background: #800020;
            color: white;
        }

        .progress-label {
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }

        .progress-step.active .progress-label {
            color: #1f2937;
            font-weight: 600;
        }

        .welcome-message {
            text-align: center;
            margin-bottom: 32px;
        }

        .welcome-message h2 {
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .welcome-message p {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 14px;
            color: #6b7280;
        }

        .info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .ready-box {
            background: #f0fdf4;
            border: 2px solid #86efac;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }

        .ready-box h3 {
            font-size: 16px;
            color: #15803d;
            margin-bottom: 8px;
        }

        .ready-box p {
            font-size: 14px;
            color: #166534;
        }

        .button-container {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-primary {
            background: #800020;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #660019;
        }

        .btn-primary:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .content {
                padding: 24px;
            }

            .progress-indicator {
                padding: 0;
            }

            .progress-label {
                font-size: 10px;
            }

            .button-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ready to Start</h1>
            <p>All preparations complete</p>
        </div>

        <div class="content">
            <!-- Progress Indicator -->
            <div class="progress-indicator">
                <div class="progress-step completed">
                    <div class="progress-number">1</div>
                    <div class="progress-label">Access Code</div>
                </div>
                <div class="progress-step completed">
                    <div class="progress-number">2</div>
                    <div class="progress-label">Instructions</div>
                </div>
                <div class="progress-step completed">
                    <div class="progress-number">3</div>
                    <div class="progress-label">Basic Info</div>
                </div>
                <div class="progress-step active">
                    <div class="progress-number">4</div>
                    <div class="progress-label">Exam</div>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info">
                    {{ session('info') }}
                </div>
            @endif

            <div class="welcome-message">
                <h2>Welcome, {{ $applicant->first_name }}!</h2>
                <p>You have successfully completed all pre-exam requirements. You are now ready to begin your entrance examination.</p>
            </div>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Exam Name</span>
                    <span class="info-value">{{ $exam->title ?? 'BSIT Entrance Exam' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Duration</span>
                    <span class="info-value">{{ $exam->duration_minutes ?? 30 }} minutes</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Questions</span>
                    <span class="info-value">{{ $exam->activeQuestions()->count() }} questions</span>
                </div>
            </div>

            <div class="ready-box">
                <h3>All Set!</h3>
                <p>Click the button below to start your exam. Good luck!</p>
            </div>

            <div class="button-container">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                    Back
                </button>
                <button type="button" class="btn btn-primary" id="startExamBtn" onclick="startExam()">
                    Start Exam
                </button>
            </div>
        </div>
    </div>

    <script>
        let isStartingExam = false;

        async function startExam() {
            // Prevent double-clicks
            if (isStartingExam) return;
            isStartingExam = true;
            
            const startButton = document.getElementById('startExamBtn');
            
            // Disable button and show loading state
            startButton.disabled = true;
            startButton.textContent = 'Starting Exam...';

            try {
                // Call the exam start endpoint to initialize session
                const response = await fetch("{{ route('exam.start') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Successfully initialized exam session, redirect to interface
                    window.location.href = data.redirect_url || "{{ route('exam.interface') }}";
                } else {
                    // Show error message
                    alert(data.message || 'Failed to start exam. Please try again.');
                    startButton.disabled = false;
                    startButton.textContent = 'Start Exam';
                    isStartingExam = false;
                    
                    // If there's a redirect URL in the error, redirect to it
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    }
                }
            } catch (error) {
                console.error('Error starting exam:', error);
                alert('An error occurred while starting the exam. Please try again.');
                startButton.disabled = false;
                startButton.textContent = 'Start Exam';
                isStartingExam = false;
            }
        }

        // Prevent accidental navigation
        window.addEventListener('beforeunload', function(e) {
            if (!isStartingExam) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>

