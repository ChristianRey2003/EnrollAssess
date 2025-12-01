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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
        }

        .container {
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

        .header {
            background: #800020;
            color: white;
            padding: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 12px;
            opacity: 0.9;
        }

        .progress-indicator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 10px;
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
            top: 12px;
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
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 11px;
            margin-bottom: 6px;
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
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }

        .progress-step.active .progress-label {
            color: #1f2937;
            font-weight: 600;
        }

        .content {
            padding: 32px;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }

        /* Custom scrollbar styling */
        .content::-webkit-scrollbar {
            width: 8px;
        }

        .content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .content::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        .content::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .section {
            margin-bottom: 15px;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6b7280;
            font-size: 13px;
        }

        .info-value {
            color: #1f2937;
            font-size: 13px;
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
            font-size: 12px;
            position: relative;
            padding-left: 20px;
        }

        .prohibited-list li:before {
            content: "×";
            position: absolute;
            left: 0;
            font-weight: bold;
            font-size: 14px;
        }

        .consequence {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 12px 16px;
            margin: 16px 0;
            font-size: 11px;
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
            font-size: 12px;
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
            font-size: 13px;
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
            body {
                padding: 10px;
            }

            .container {
                max-height: 95vh;
            }

            .header {
                padding: 24px;
            }

            .header h1 {
                font-size: 18px;
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

            .progress-indicator {
                padding: 0;
            }

            .progress-label {
                font-size: 9px;
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
            <!-- Progress Indicator -->
            <div class="progress-indicator">
                <div class="progress-step completed">
                    <div class="progress-number">1</div>
                    <div class="progress-label">Access Code</div>
                </div>
                <div class="progress-step active">
                    <div class="progress-number">2</div>
                    <div class="progress-label">Instructions</div>
                </div>
                <div class="progress-step">
                    <div class="progress-number">3</div>
                    <div class="progress-label">Basic Info</div>
                </div>
                <div class="progress-step">
                    <div class="progress-number">4</div>
                    <div class="progress-label">Exam</div>
                </div>
            </div>

            <!-- Exam Information -->
            <div class="section">
                <div class="section-title">Exam Information</div>
                <div class="info-row">
                    <span class="info-label">Exam Name</span>
                    <span class="info-value">{{ $exam->title ?? 'BSIT Entrance Exam' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Items</span>
                    <span class="info-value">{{ $totalItems ?? 20 }} items</span>
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

            <!-- Agreement -->
            <div class="section">
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="agree-instructions" class="agreement-checkbox" required>
                        <label for="agree-instructions">
                            I have read and understood all exam instructions and prohibited actions.
                        </label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="agree-terms" class="agreement-checkbox" required>
                        <label for="agree-terms">
                            I agree to the terms and conditions. I understand that violations will result in automatic exam submission.
                        </label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="agree-privacy" class="agreement-checkbox" required>
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
                <button type="button" class="btn btn-primary" id="continueBtn" disabled onclick="continueToBasicInfo()">
                    Continue
                </button>
            </div>
        </div>
    </div>

    <script>
        // Enable continue button only when all checkboxes are checked
        const agreementCheckboxes = document.querySelectorAll('.agreement-checkbox');
        const continueButton = document.getElementById('continueBtn');

        // Update continue button state when individual checkboxes change
        agreementCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateContinueButton();
            });
        });

        // Function to update continue button state
        function updateContinueButton() {
            const allAgreementChecked = Array.from(agreementCheckboxes).every(cb => cb.checked);
            continueButton.disabled = !allAgreementChecked;
        }

        function continueToBasicInfo() {
            // Store consent acknowledgment in localStorage
            localStorage.setItem('examPreRequirements', JSON.stringify({
                instructionsAcknowledged: true,
                timestamp: new Date().toISOString(),
                violationCount: 0,
                maxViolations: 5
            }));

            // Redirect to basic information form
            window.location.href = "{{ route('exam.basic-info') }}";
        }
    </script>
</body>
</html>
