<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Entrance Examination - {{ config('app.name', 'EnrollAssess') }}</title>
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
            background: #f8f9fa;
            color: #1f2937;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        /* Fullscreen exam container */
        .exam-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }

        /* Top header bar */
        .exam-header {
            background: #800020;
            color: white;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .exam-title {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        .exam-meta {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .exam-timer {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            min-width: 100px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .exam-timer.warning {
            background: #dc2626;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .violation-badge {
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .violation-badge.warning {
            background: #f59e0b;
            color: #000;
        }

        .violation-badge.danger {
            background: #dc2626;
            animation: shake 0.5s infinite;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        /* Progress bar */
        .progress-bar-container {
            height: 4px;
            background: #e5e7eb;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #800020, #dc2626);
            transition: width 0.3s ease;
        }

        /* Main content area */
        .exam-content {
            flex: 1;
            overflow-y: auto;
            padding: 32px 24px;
        }

        .exam-sections {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        /* Section card */
        .exam-section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
        }

        .exam-section:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .exam-section.completed {
            border-color: #10b981;
            background: #f0fdf4;
        }

        /* Section header */
        .section-header {
            background: #f9fafb;
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .exam-section.completed .section-header {
            background: #ecfdf5;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-badge {
            background: #800020;
            color: white;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .exam-section.completed .section-badge {
            background: #10b981;
        }

        .section-status {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .exam-section.completed .section-status {
            color: #059669;
        }

        /* Section content */
        .section-content {
            padding: 32px 24px;
        }

        .question-grid {
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        /* Question card */
        .question-item {
            padding: 24px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 4px solid #800020;
        }

        .question-number {
            font-size: 13px;
            font-weight: 600;
            color: #800020;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        .question-text {
            font-size: 16px;
            font-weight: 500;
            color: #1f2937;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        /* Options */
        .question-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option-group {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .option-group:hover {
            border-color: #800020;
            background: #fef2f2;
        }

        .option-group.selected {
            border-color: #800020;
            background: #fef2f2;
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
        }

        .option-input {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            cursor: pointer;
            flex-shrink: 0;
            -webkit-user-select: auto;
            -moz-user-select: auto;
            user-select: auto;
        }

        .option-content {
            flex: 1;
        }

        .option-letter {
            font-weight: 600;
            color: #800020;
            margin-right: 8px;
        }

        .option-text {
            color: #374151;
            font-size: 15px;
            line-height: 1.5;
            -webkit-user-select: auto;
            -moz-user-select: auto;
            user-select: auto;
        }

        /* Essay textarea */
        .essay-textarea {
            width: 100%;
            min-height: 150px;
            padding: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-family: inherit;
            font-size: 15px;
            line-height: 1.6;
            resize: vertical;
            transition: all 0.2s;
            -webkit-user-select: auto;
            -moz-user-select: auto;
            user-select: auto;
        }

        .essay-textarea:focus {
            outline: none;
            border-color: #800020;
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
        }

        /* Section footer */
        .section-footer {
            padding: 20px 24px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-progress {
            font-size: 14px;
            color: #6b7280;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: #800020;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #5c0017;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(128, 0, 32, 0.3);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
        }

        .modal-body p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        /* Notification toast */
        .notification {
            position: fixed;
            top: 80px;
            right: 24px;
            background: white;
            border-radius: 8px;
            padding: 16px 20px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
            min-width: 320px;
            max-width: 420px;
            z-index: 10001;
            animation: slideIn 0.3s ease-out;
            border-left: 4px solid #3b82f6;
        }

        .notification.warning {
            border-left-color: #f59e0b;
        }

        .notification.error {
            border-left-color: #dc2626;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .notification-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .notification-content p {
            color: #374151;
            font-size: 14px;
            line-height: 1.5;
            white-space: pre-line;
            margin: 0;
        }

        .notification-close {
            background: #800020;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            flex-shrink: 0;
        }

        .notification-close:hover {
            background: #5c0017;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .exam-header {
                padding: 12px 16px;
            }

            .exam-title {
                font-size: 16px;
            }

            .exam-meta {
                gap: 12px;
            }

            .exam-timer {
                font-size: 14px;
                padding: 6px 12px;
                min-width: 80px;
            }

            .violation-badge {
                font-size: 12px;
                padding: 6px 12px;
            }

            .exam-content {
                padding: 20px 16px;
            }

            .exam-sections {
                gap: 24px;
            }

            .section-header {
                padding: 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .section-content {
                padding: 20px 16px;
            }

            .question-item {
                padding: 16px;
            }

            .notification {
                top: 60px;
                right: 16px;
                left: 16px;
                min-width: auto;
            }
        }
    </style>
</head>
<body oncontextmenu="return false;">
    <div class="exam-container">
        <!-- Header -->
        <header class="exam-header">
            <div class="exam-title">Entrance Examination</div>
            <div class="exam-meta">
                <div class="violation-badge" id="violationBadge">
                    Violations: <span id="violationCount">0</span>/5
                </div>
                <div class="exam-timer" id="examTimer">
                    <span id="timeRemaining">{{ gmdate('i:s', $timeRemaining ?? 1800) }}</span>
                </div>
            </div>
        </header>

        <!-- Progress bar -->
        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar" style="width: 0%;"></div>
        </div>

        <!-- Main content -->
        <main class="exam-content">
            <div class="exam-sections">
                @if(isset($sections) && $sections->count() > 0)
                    @foreach($sections as $index => $section)
                        <div class="exam-section" id="section-{{ $index }}" data-section-type="{{ $section['type'] }}">
                            <div class="section-header">
                                <div class="section-title">
                                    <span class="section-badge">{{ $section['icon'] }}</span>
                                    <span>{{ $section['label'] }}</span>
                                </div>
                                <div class="section-status" id="status-{{ $index }}">
                                    {{ $section['count'] }} Question{{ $section['count'] !== 1 ? 's' : '' }} | Pending
                                </div>
                            </div>

                            <div class="section-content">
                                <form class="section-form" data-section-index="{{ $index }}">
                                    <div class="question-grid">
                                        @foreach($section['questions'] as $questionIndex => $question)
                                            <div class="question-item">
                                                <div class="question-number">Question {{ $questionIndex + 1 }}</div>
                                                <div class="question-text">{{ $question->question_text }}</div>
                                                
                                                @if($question->question_type === 'multiple_choice' || $question->question_type === 'true_false')
                                                    <div class="question-options">
                                                        @foreach($question->options as $optionIndex => $option)
                                                            <div class="option-group" onclick="selectOption(this)">
                                                                <input type="radio" 
                                                                       name="question_{{ $question->question_id }}" 
                                                                       value="{{ $option->option_id }}" 
                                                                       class="option-input"
                                                                       id="q{{ $question->question_id }}_{{ $optionIndex }}">
                                                                <div class="option-content">
                                                                    <span class="option-letter">{{ chr(65 + $optionIndex) }})</span>
                                                                    <span class="option-text">{{ $option->option_text }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @elseif($question->question_type === 'essay')
                                                    <textarea name="question_{{ $question->question_id }}" 
                                                              class="essay-textarea"
                                                              placeholder="Type your answer here..."
                                                              data-question-id="{{ $question->question_id }}"></textarea>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </form>
                            </div>

                            <div class="section-footer">
                                <div class="section-progress" id="progress-{{ $index }}">0/{{ $section['count'] }} answered</div>
                                <div>
                                    @if($index === $sections->count() - 1)
                                        <button type="button" class="btn btn-success" onclick="submitSection({{ $index }}, true)">
                                            Complete Exam
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-primary" onclick="submitSection({{ $index }})">
                                            Submit Section
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="exam-section">
                        <div class="section-content">
                            <p style="text-align: center; color: #6b7280; padding: 40px 20px;">No questions available for this exam.</p>
                        </div>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Submit Section Modal -->
    <div id="submitModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Submit Section</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit this section?</p>
                <p>Once submitted, you cannot return to modify your answers.</p>
                <div id="sectionSummary"></div>
            </div>
            <div class="modal-footer">
                <button onclick="closeSubmitModal()" class="btn btn-secondary">Review Answers</button>
                <button onclick="confirmSubmitSection()" class="btn btn-primary">Submit Section</button>
            </div>
        </div>
    </div>

    <!-- Final Submit Modal -->
    <div id="finalSubmitModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Complete Examination</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to complete your examination?</p>
                <p>Once submitted, you will not be able to make any changes.</p>
                <p>Time remaining: <strong><span id="modalTimeRemaining">{{ gmdate('i:s', $timeRemaining ?? 1800) }}</span></strong></p>
            </div>
            <div class="modal-footer">
                <button onclick="closeFinalSubmitModal()" class="btn btn-secondary">Continue Exam</button>
                <button onclick="confirmFinalSubmit()" class="btn btn-success">Submit Final Answers</button>
            </div>
        </div>
    </div>

    <script>
        let timeRemaining = {{ $timeRemaining ?? 1800 }};
        let violationCount = 0;
        let currentSubmittingSection = null;
        let completedSections = @json($examSession['sections_completed'] ?? []);
        let sectionAnswers = @json($examSession['answers'] ?? []);
        let fullscreenActive = false;
        let examStarted = false;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            showFullscreenPrompt();
        });

        function showFullscreenPrompt() {
            // Create overlay prompt
            const prompt = document.createElement('div');
            prompt.id = 'fullscreenPrompt';
            prompt.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; 
                            background: rgba(0, 0, 0, 0.95); display: flex; align-items: center; 
                            justify-content: center; z-index: 99999;">
                    <div style="background: white; padding: 48px; border-radius: 16px; text-align: center; max-width: 500px;">
                        <h2 style="font-size: 24px; font-weight: 600; color: #1f2937; margin: 0 0 16px 0;">
                            Fullscreen Mode Required
                        </h2>
                        <p style="font-size: 16px; color: #6b7280; line-height: 1.6; margin: 0 0 24px 0;">
                            For exam security, you must complete the exam in fullscreen mode.
                            Click the button below to enter fullscreen and begin.
                        </p>
                        <button onclick="enterFullscreenAndStart()" 
                                style="padding: 14px 32px; background: #800020; color: white; border: none; 
                                       border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;
                                       transition: all 0.2s;">
                            Enter Fullscreen & Start Exam
                        </button>
                        <p style="font-size: 13px; color: #9ca3af; margin: 24px 0 0 0;">
                            Exiting fullscreen during the exam will be recorded as a violation.
                        </p>
                    </div>
                </div>
            `;
            document.body.appendChild(prompt);
        }

        function enterFullscreenAndStart() {
            const elem = document.documentElement;
            
            // Try different fullscreen methods
            const requestFullscreen = elem.requestFullscreen || 
                                      elem.webkitRequestFullscreen || 
                                      elem.mozRequestFullScreen || 
                                      elem.msRequestFullscreen;
            
            if (requestFullscreen) {
                requestFullscreen.call(elem).then(() => {
                    fullscreenActive = true;
                    removeFullscreenPrompt();
                    startExam();
                }).catch(err => {
                    console.error('Fullscreen failed:', err);
                    alert('Fullscreen is required to start the exam. Please allow fullscreen access.');
                });
            } else {
                // Browser doesn't support fullscreen
                alert('Your browser does not support fullscreen mode. Please use Chrome, Firefox, or Edge.');
            }
        }

        function removeFullscreenPrompt() {
            const prompt = document.getElementById('fullscreenPrompt');
            if (prompt) {
                prompt.remove();
            }
        }

        function startExam() {
            examStarted = true;
            initializeTimer();
            initializeViolationSystem();
            initializeSectionAnswers();
            updateSectionStates();
            
            // Setup violation monitoring after exam starts
            setTimeout(() => {
                setupViolationMonitoring();
            }, 500);
        }

        // Monitor fullscreen changes
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('mozfullscreenchange', handleFullscreenChange);
        document.addEventListener('MSFullscreenChange', handleFullscreenChange);

        function handleFullscreenChange() {
            const isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement || 
                                   document.mozFullScreenElement || document.msFullscreenElement);
            
            if (!isFullscreen && fullscreenActive && examStarted && timeRemaining > 0) {
                recordViolation('FULLSCREEN_EXIT', 'You exited fullscreen mode.');
                // Try to re-enter fullscreen
                setTimeout(reEnterFullscreen, 1000);
            }
        }

        function reEnterFullscreen() {
            const elem = document.documentElement;
            const requestFullscreen = elem.requestFullscreen || 
                                      elem.webkitRequestFullscreen || 
                                      elem.mozRequestFullScreen || 
                                      elem.msRequestFullscreen;
            
            if (requestFullscreen) {
                requestFullscreen.call(elem).catch(err => {
                    console.log('Re-entering fullscreen failed:', err);
                });
            }
        }

        // Timer
        function initializeTimer() {
            function updateTimer() {
                if (timeRemaining <= 0) {
                    autoSubmitExam('Time expired');
                    return;
                }
                
                timeRemaining--;
                
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                const timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                document.getElementById('timeRemaining').textContent = timeString;
                const modalTime = document.getElementById('modalTimeRemaining');
                if (modalTime) modalTime.textContent = timeString;
                
                // Warning at 5 minutes
                if (timeRemaining === 300) {
                    document.getElementById('examTimer').classList.add('warning');
                    showNotification('5 minutes remaining!', 'warning');
                }
            }
            
            setInterval(updateTimer, 1000);
        }

        // Violation system
        function initializeViolationSystem() {
            violationCount = 0;
            updateViolationBadge();
        }

        function updateViolationBadge() {
            const badge = document.getElementById('violationBadge');
            const count = document.getElementById('violationCount');
            
            count.textContent = violationCount;
            
            badge.classList.remove('warning', 'danger');
            if (violationCount >= 3 && violationCount < 5) {
                badge.classList.add('warning');
            } else if (violationCount >= 4) {
                badge.classList.add('danger');
            }
        }

        function setupViolationMonitoring() {
            // Tab visibility
            document.addEventListener('visibilitychange', function() {
                if (document.hidden && examStarted && timeRemaining > 0) {
                    recordViolation('TAB_SWITCH', 'You switched to another tab or minimized the browser.');
                }
            });

            // Window blur
            window.addEventListener('blur', function() {
                if (!document.hidden && examStarted && timeRemaining > 0) {
                    recordViolation('WINDOW_BLUR', 'You clicked outside the exam window.');
                }
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Dev tools
                if (e.key === 'F12' || 
                    (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) ||
                    (e.ctrlKey && (e.key === 'u' || e.key === 'U'))) {
                    e.preventDefault();
                    recordViolation('DEV_TOOLS', 'Developer tools access blocked.');
                    return false;
                }

                // Copy/Paste
                if (e.ctrlKey && (e.key === 'c' || e.key === 'C' || e.key === 'v' || e.key === 'V' || 
                                 e.key === 'x' || e.key === 'X' || e.key === 'a' || e.key === 'A')) {
                    e.preventDefault();
                    recordViolation('COPY_PASTE', 'Copy/paste is not allowed.');
                    return false;
                }

                // Print
                if (e.ctrlKey && (e.key === 'p' || e.key === 'P')) {
                    e.preventDefault();
                    recordViolation('PRINT_ATTEMPT', 'Printing is not allowed.');
                    return false;
                }

                // Refresh
                if (e.key === 'F5' || (e.ctrlKey && (e.key === 'r' || e.key === 'R'))) {
                    e.preventDefault();
                    recordViolation('REFRESH_ATTEMPT', 'Page refresh is not allowed.');
                    return false;
                }

                // Alt+Tab
                if (e.altKey && e.key === 'Tab') {
                    e.preventDefault();
                    recordViolation('ALT_TAB', 'Application switching is not allowed.');
                    return false;
                }

                // Windows key
                if (e.key === 'Meta' || e.key === 'OS') {
                    e.preventDefault();
                    recordViolation('WINDOWS_KEY', 'System key pressed.');
                    return false;
                }
            });

            // Right-click
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                recordViolation('RIGHT_CLICK', 'Right-click is not allowed.');
                return false;
            });
        }

        function recordViolation(type, message) {
            violationCount++;
            updateViolationBadge();
            
            console.warn(`Violation ${violationCount}/5: ${type}`);
            
            if (violationCount >= 5) {
                autoSubmitExam('Maximum violations reached');
            } else {
                showNotification(`${message}\n\nViolations: ${violationCount}/5`, 'warning');
            }
        }

        // Notification system
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existing = document.querySelectorAll('.notification');
            existing.forEach(n => n.remove());
            
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <p>${message}</p>
                    <button onclick="this.closest('.notification').remove()" class="notification-close">OK</button>
                </div>
            `;
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Section management
        function initializeSectionAnswers() {
            Object.keys(sectionAnswers).forEach(questionId => {
                const input = document.querySelector(`input[name="question_${questionId}"], textarea[name="question_${questionId}"]`);
                if (input) {
                    if (input.type === 'radio') {
                        const radioInput = document.querySelector(`input[name="question_${questionId}"][value="${sectionAnswers[questionId]}"]`);
                        if (radioInput) {
                            radioInput.checked = true;
                            selectOption(radioInput.closest('.option-group'));
                        }
                    } else {
                        input.value = sectionAnswers[questionId];
                    }
                }
            });

            document.querySelectorAll('.section-form').forEach((form, index) => {
                updateSectionProgress(index);
            });
        }

        function updateSectionStates() {
            completedSections.forEach(sectionType => {
                const section = document.querySelector(`[data-section-type="${sectionType}"]`);
                if (section) {
                    section.classList.add('completed');
                    const statusElement = section.querySelector('.section-status');
                    if (statusElement) {
                        const text = statusElement.textContent.split('|')[0].trim();
                        statusElement.textContent = text + ' | Completed';
                    }
                }
            });
        }

        function selectOption(optionGroup) {
            const siblings = optionGroup.parentElement.querySelectorAll('.option-group');
            siblings.forEach(sibling => sibling.classList.remove('selected'));
            
            optionGroup.classList.add('selected');
            
            const radio = optionGroup.querySelector('.option-input');
            if (radio) {
                radio.checked = true;
            }

            const form = optionGroup.closest('.section-form');
            const sectionIndex = parseInt(form.dataset.sectionIndex);
            updateSectionProgress(sectionIndex);
        }

        function updateSectionProgress(sectionIndex) {
            const form = document.querySelector(`.section-form[data-section-index="${sectionIndex}"]`);
            const questions = form.querySelectorAll('.question-item');
            let answered = 0;

            questions.forEach(question => {
                const radioInputs = question.querySelectorAll('input[type="radio"]');
                const textareas = question.querySelectorAll('textarea');
                
                if (radioInputs.length > 0) {
                    const checkedRadio = question.querySelector('input[type="radio"]:checked');
                    if (checkedRadio) answered++;
                } else if (textareas.length > 0) {
                    const textarea = textareas[0];
                    if (textarea.value.trim()) answered++;
                }
            });

            const progressElement = document.getElementById(`progress-${sectionIndex}`);
            if (progressElement) {
                progressElement.textContent = `${answered}/${questions.length} answered`;
            }

            updateOverallProgress();
        }

        function updateOverallProgress() {
            const totalQuestions = document.querySelectorAll('.question-item').length;
            let totalAnswered = 0;

            document.querySelectorAll('.question-item').forEach(question => {
                const radioInputs = question.querySelectorAll('input[type="radio"]');
                const textareas = question.querySelectorAll('textarea');
                
                if (radioInputs.length > 0) {
                    const checkedRadio = question.querySelector('input[type="radio"]:checked');
                    if (checkedRadio) totalAnswered++;
                } else if (textareas.length > 0) {
                    const textarea = textareas[0];
                    if (textarea.value.trim()) totalAnswered++;
                }
            });

            const progressBar = document.getElementById('progressBar');
            if (progressBar && totalQuestions > 0) {
                const percentage = (totalAnswered / totalQuestions) * 100;
                progressBar.style.width = `${percentage}%`;
            }
        }

        // Essay input tracking
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('essay-textarea')) {
                const form = e.target.closest('.section-form');
                const sectionIndex = parseInt(form.dataset.sectionIndex);
                updateSectionProgress(sectionIndex);
            }
        });

        // Section submission
        function submitSection(sectionIndex, isFinalSubmit = false) {
            currentSubmittingSection = sectionIndex;
            
            const form = document.querySelector(`.section-form[data-section-index="${sectionIndex}"]`);
            const questions = form.querySelectorAll('.question-item');
            const answers = {};
            let unanswered = 0;

            questions.forEach(question => {
                const radioInputs = question.querySelectorAll('input[type="radio"]');
                const textareas = question.querySelectorAll('textarea');
                
                if (radioInputs.length > 0) {
                    const checkedRadio = question.querySelector('input[type="radio"]:checked');
                    if (checkedRadio) {
                        const questionId = checkedRadio.name.replace('question_', '');
                        answers[questionId] = checkedRadio.value;
                    } else {
                        unanswered++;
                    }
                } else if (textareas.length > 0) {
                    const textarea = textareas[0];
                    const questionId = textarea.dataset.questionId;
                    if (textarea.value.trim()) {
                        answers[questionId] = textarea.value.trim();
                    } else {
                        unanswered++;
                    }
                }
            });

            if (unanswered > 0) {
                showNotification(`Please answer all questions in this section.\n${unanswered} question(s) remaining.`, 'error');
                return;
            }

            window.tempSectionAnswers = answers;
            
            if (isFinalSubmit) {
                document.getElementById('finalSubmitModal').style.display = 'flex';
            } else {
                const sectionType = form.closest('.exam-section').dataset.sectionType;
                const summary = `${Object.keys(answers).length} question(s) answered in ${sectionType.replace('_', ' ')} section.`;
                document.getElementById('sectionSummary').innerHTML = `<p>${summary}</p>`;
                document.getElementById('submitModal').style.display = 'flex';
            }
        }

        function closeSubmitModal() {
            document.getElementById('submitModal').style.display = 'none';
            currentSubmittingSection = null;
        }

        function closeFinalSubmitModal() {
            document.getElementById('finalSubmitModal').style.display = 'none';
            currentSubmittingSection = null;
        }

        function confirmSubmitSection() {
            if (currentSubmittingSection === null) return;

            const sectionElement = document.querySelector(`#section-${currentSubmittingSection}`);
            const sectionType = sectionElement.dataset.sectionType;

            fetch('{{ route('exam.submit-section') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    section_type: sectionType,
                    answers: window.tempSectionAnswers
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    sectionElement.classList.add('completed');
                    const statusElement = sectionElement.querySelector('.section-status');
                    const text = statusElement.textContent.split('|')[0].trim();
                    statusElement.textContent = text + ' | Completed';

                    completedSections.push(sectionType);
                    Object.assign(sectionAnswers, window.tempSectionAnswers);

                    closeSubmitModal();
                    
                    const nextSection = document.querySelector(`#section-${currentSubmittingSection + 1}`);
                    if (nextSection) {
                        nextSection.scrollIntoView({ behavior: 'smooth' });
                    }
                } else {
                    showNotification('Failed to submit section: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error submitting section:', error);
                showNotification('Failed to submit section. Please try again.', 'error');
            });
        }

        let isSubmittingExam = false;

        function confirmFinalSubmit() {
            isSubmittingExam = true; // Disable beforeunload warning
            Object.assign(sectionAnswers, window.tempSectionAnswers);
            
            fetch('{{ route('exam.complete') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    applicant_id: {{ $examSession['applicant_id'] ?? ($applicant->applicant_id ?? 1) }},
                    answers: sectionAnswers,
                    exam_session_id: 'session_' + Date.now()
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeFinalSubmitModal();
                    window.location.href = "{{ route('exam.results') }}";
                } else {
                    showNotification('Failed to submit exam: ' + data.message, 'error');
                    isSubmittingExam = false; // Re-enable warning if submission failed
                }
            })
            .catch(error => {
                console.error('Error submitting exam:', error);
                showNotification('Failed to submit exam. Please try again.', 'error');
                isSubmittingExam = false; // Re-enable warning if submission failed
            });
        }

        function autoSubmitExam(reason) {
            isSubmittingExam = true; // Disable beforeunload warning
            showNotification(`${reason}! Automatically submitting your exam...`, 'error');
            
            fetch('{{ route('exam.complete') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    applicant_id: {{ $examSession['applicant_id'] ?? ($applicant->applicant_id ?? 1) }},
                    answers: sectionAnswers,
                    exam_session_id: 'session_' + Date.now(),
                    auto_submitted: true,
                    auto_submit_reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                setTimeout(() => {
                    window.location.href = "{{ route('exam.results') }}";
                }, 2000);
            })
            .catch(error => {
                console.error('Error auto-submitting exam:', error);
                setTimeout(() => {
                    window.location.href = "{{ route('exam.results') }}";
                }, 2000);
            });
        }

        // Prevent page unload (but allow when legitimately submitting)
        window.addEventListener('beforeunload', function(e) {
            if (isSubmittingExam) {
                return; // Allow navigation when submitting exam
            }

            if (timeRemaining > 0) {
                e.preventDefault();
                e.returnValue = 'Are you sure you want to leave? Your exam progress may be lost.';
                return e.returnValue;
            }
        });
    </script>
</body>
</html>
