<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Exam Instructions - EnrollAssess</title>
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
            max-width: 700px;
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
            padding: 32px;
        }

        .section {
            margin-bottom: 32px;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6b7280;
            font-size: 14px;
        }

        .info-value {
            color: #1f2937;
            font-size: 14px;
            font-weight: 600;
        }

        .prohibited-list {
            background: #fef2f2;
            border-left: 3px solid #dc2626;
            padding: 16px;
            margin: 16px 0;
        }

        .prohibited-list ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .prohibited-list li {
            padding: 4px 0;
            color: #991b1b;
            font-size: 14px;
            position: relative;
            padding-left: 20px;
        }

        .prohibited-list li:before {
            content: "×";
            position: absolute;
            left: 0;
            font-weight: bold;
            font-size: 16px;
        }

        .consequence {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 12px 16px;
            margin: 16px 0;
            font-size: 13px;
            color: #92400e;
        }

        .checkbox-group {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            margin: 24px 0;
        }

        .checkbox-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .checkbox-item:last-child {
            margin-bottom: 0;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 12px;
            margin-top: 2px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .checkbox-item label {
            font-size: 14px;
            color: #374151;
            cursor: pointer;
            line-height: 1.5;
        }

        .button-container {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #800020;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #5c0017;
        }

        .btn-primary:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        @media (max-width: 640px) {
            .header {
                padding: 24px;
            }

            .header h1 {
                font-size: 20px;
            }

            .content {
                padding: 24px;
            }

            .button-container {
                flex-direction: column;
            }

            .info-row {
                flex-direction: column;
                gap: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Entrance Examination</h1>
            <p>Please read the instructions carefully before proceeding</p>
        </div>

        <div class="content">
            <!-- Exam Information -->
            <div class="section">
                <div class="section-title">Exam Information</div>
                <div class="info-row">
                    <span class="info-label">Exam Name</span>
                    <span class="info-value">{{ $exam->title ?? 'BSIT Entrance Exam' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Questions</span>
                    <span class="info-value">{{ $totalQuestions ?? 20 }} questions</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Time Limit</span>
                    <span class="info-value">{{ $duration ?? 30 }} minutes</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Passing Score</span>
                    <span class="info-value">60%</span>
                </div>
            </div>

            <!-- Prohibited Actions -->
            <div class="section">
                <div class="section-title">Prohibited Actions</div>
                <div class="prohibited-list">
                    <ul>
                        <li>Switching tabs or minimizing browser window</li>
                        <li>Exiting fullscreen mode</li>
                        <li>Copying, pasting, or printing content</li>
                        <li>Opening developer tools or external applications</li>
                        <li>Using keyboard shortcuts (Alt+Tab, Windows key, etc.)</li>
                    </ul>
                </div>
                <div class="consequence">
                    <strong>Warning:</strong> Each violation will be recorded. After 5 violations, your exam will be automatically submitted.
                </div>
            </div>

            <!-- Technical Requirements -->
            <div class="section">
                <div class="section-title">Technical Requirements</div>
                <div class="info-row">
                    <span class="info-label">Stable internet connection</span>
                    <span class="info-value">Required</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Recommended browsers</span>
                    <span class="info-value">Chrome, Firefox, Edge</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Device</span>
                    <span class="info-value">Desktop or laptop preferred</span>
                </div>
            </div>

            <!-- Agreement -->
            <div class="section">
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="agree-instructions" required>
                        <label for="agree-instructions">
                            I have read and understood all exam instructions and prohibited actions.
                        </label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="agree-terms" required>
                        <label for="agree-terms">
                            I agree to the terms and conditions. I understand that violations will result in automatic exam submission.
                        </label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="agree-privacy" required>
                        <label for="agree-privacy">
                            I consent to the collection of exam data including answers, timing, and violation records for assessment purposes.
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="button-container">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="startExamBtn" disabled onclick="startExam()">
                    Start Exam
                </button>
            </div>
        </div>
    </div>

    <script>
        // Enable start button only when all checkboxes are checked
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        const startButton = document.getElementById('startExamBtn');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                startButton.disabled = !allChecked;
            });
        });

        let isStartingExam = false;

        async function startExam() {
            // Prevent double-clicks
            if (isStartingExam) return;
            isStartingExam = true;
            
            // Disable button and show loading state
            startButton.disabled = true;
            startButton.textContent = 'Starting Exam...';

            // Store consent in localStorage
            localStorage.setItem('examPreRequirements', JSON.stringify({
                instructionsAcknowledged: true,
                timestamp: new Date().toISOString(),
                violationCount: 0,
                maxViolations: 5
            }));

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
                }
            } catch (error) {
                console.error('Error starting exam:', error);
                alert('An error occurred while starting the exam. Please try again.');
                startButton.disabled = false;
                startButton.textContent = 'Start Exam';
                isStartingExam = false;
            }
        }

        // Prevent accidental navigation (but allow when starting exam)
        window.addEventListener('beforeunload', function(e) {
            if (isStartingExam) {
                return; // Allow navigation when starting exam
            }

            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            if (allChecked && !startButton.disabled) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>
