<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>BSIT Entrance Examination - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Exam Interface CSS -->
    <link href="{{ asset('css/exam/exam-interface.css') }}" rel="stylesheet">

    <!-- Prevent right-click and other shortcuts -->
    <style>
        /* Disable text selection during exam */
        .exam-content {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Allow selection for radio buttons and labels for accessibility */
        .option-label, .option-input {
            -webkit-user-select: auto;
            -moz-user-select: auto;
            -ms-user-select: auto;
            user-select: auto;
        }

        /* Enhanced Exam Header Styles */
        .exam-meta {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .violation-counter {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .violation-counter.warning {
            background: rgba(245, 158, 11, 0.2);
            border-color: #F59E0B;
            animation: pulse-warning 2s infinite;
        }

        .violation-counter.danger {
            background: rgba(220, 38, 38, 0.2);
            border-color: #DC2626;
            animation: pulse-danger 1s infinite;
        }

        @keyframes pulse-warning {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes pulse-danger {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }

        .violation-icon {
            font-size: 16px;
        }

        .violation-text {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }

        .violation-counter.warning .violation-text {
            color: #F59E0B;
        }

        .violation-counter.danger .violation-text {
            color: #DC2626;
        }

        /* Violation Warning Modal */
        .violation-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
        }

        .violation-modal-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: slideInScale 0.3s ease-out;
        }

        @keyframes slideInScale {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .violation-modal-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .violation-modal h3 {
            color: #DC2626;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 16px 0;
        }

        .violation-modal p {
            color: #374151;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 16px 0;
        }

        .violation-count-display {
            background: #FEE2E2;
            color: #DC2626;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            margin: 16px 0;
            border: 2px solid #DC2626;
        }

        .violation-modal-actions {
            margin-top: 24px;
        }

        .violation-modal-btn {
            background: #DC2626;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .violation-modal-btn:hover {
            background: #B91C1C;
            transform: translateY(-1px);
        }

        /* Full screen overlay for violations */
        .violation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(220, 38, 38, 0.95);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 20000;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
        }

        .violation-overlay h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .violation-overlay p {
            font-size: 18px;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .exam-meta {
                flex-direction: column;
                gap: 10px;
            }

            .violation-counter {
                padding: 6px 12px;
            }

            .violation-text {
                font-size: 12px;
            }
        }
    </style>
</head>
<body class="exam-page" oncontextmenu="return false;" onselectstart="return false;" ondragstart="return false;">
    <div class="exam-container">
        <!-- Exam Header -->
        <header class="exam-header">
            <h1 class="exam-title">BSIT Entrance Examination</h1>
            <div class="exam-progress">
                <div class="progress-indicator">
                    Question <span id="currentQuestion">{{ $currentQuestionNumber ?? 5 }}</span> of <span id="totalQuestions">{{ $totalQuestions ?? 20 }}</span>
                </div>
                <div class="exam-meta">
                    <div class="violation-counter" id="violationCounter">
                        <span class="violation-icon">⚠️</span>
                        <span class="violation-text">Violations: <span id="violationCount">0</span>/5</span>
                    </div>
                    <div class="exam-timer" id="examTimer">
                        <span id="timeRemaining">25:30</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Progress Bar -->
        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar" style="width: {{ (($currentQuestionNumber ?? 5) / ($totalQuestions ?? 20)) * 100 }}%;"></div>
        </div>

        <!-- Exam Content -->
        <main class="exam-content">
            <!-- Question Section -->
            <section class="question-section">
                <div class="question-number">Question {{ $currentQuestionNumber ?? 5 }}</div>
                <h2 class="question-text">
                    {{ $question->question_text ?? 'Which of the following is NOT a principle of object-oriented programming?' }}
                </h2>
            </section>

            <!-- Answer Options -->
            <section class="answer-options">
                <form id="examForm" method="POST" action="{{ route('exam.submit-answer') }}">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->question_id ?? 5 }}">
                    <input type="hidden" name="exam_session_id" value="{{ $examSession->id ?? 'demo' }}">

                    @if(isset($question) && $question->options)
                        @foreach($question->options as $index => $option)
                        <div class="option-group">
                            <label class="option-label" for="option_{{ $index }}">
                                <input type="radio" 
                                       id="option_{{ $index }}" 
                                       name="selected_answer" 
                                       value="{{ $option->id }}" 
                                       class="option-input"
                                       {{ old('selected_answer') == $option->id ? 'checked' : '' }}>
                                <span class="option-letter">{{ chr(65 + $index) }})</span>
                                <span class="option-text">{{ $option->option_text }}</span>
                            </label>
                        </div>
                        @endforeach
                    @else
                        <!-- Demo options when no question data is available -->
                        <div class="option-group">
                            <label class="option-label" for="option_a">
                                <input type="radio" id="option_a" name="selected_answer" value="a" class="option-input">
                                <span class="option-letter">A)</span>
                                <span class="option-text">Encapsulation</span>
                            </label>
                        </div>
                        <div class="option-group">
                            <label class="option-label" for="option_b">
                                <input type="radio" id="option_b" name="selected_answer" value="b" class="option-input" checked>
                                <span class="option-letter">B)</span>
                                <span class="option-text">Inheritance</span>
                            </label>
                        </div>
                        <div class="option-group">
                            <label class="option-label" for="option_c">
                                <input type="radio" id="option_c" name="selected_answer" value="c" class="option-input">
                                <span class="option-letter">C)</span>
                                <span class="option-text">Polymorphism</span>
                            </label>
                        </div>
                        <div class="option-group">
                            <label class="option-label" for="option_d">
                                <input type="radio" id="option_d" name="selected_answer" value="d" class="option-input">
                                <span class="option-letter">D)</span>
                                <span class="option-text">Compilation</span>
                            </label>
                        </div>
                    @endif
                </form>
            </section>
        </main>

        <!-- Navigation -->
        <nav class="exam-navigation">
            @if(($currentQuestionNumber ?? 5) > 1)
                <button type="button" class="nav-button btn-secondary" onclick="goToPreviousQuestion()" disabled>
                    Previous
                </button>
            @else
                <div></div>
            @endif

            @if(($currentQuestionNumber ?? 5) < ($totalQuestions ?? 20))
                <button type="button" class="nav-button btn-primary" onclick="submitAndNext()" id="nextButton">
                    Next Question
                </button>
            @else
                <button type="button" class="nav-button btn-primary" onclick="submitExam()" id="submitButton">
                    Submit Exam
                </button>
            @endif
        </nav>

        <!-- Footer Instructions -->
        <footer class="exam-footer">
            <p class="exam-instructions">
                <strong>Instructions:</strong> Select one answer and click Next to continue. 
                <strong>Note:</strong> You cannot return to previous questions once you proceed.
            </p>
        </footer>
    </div>

    <!-- Submit Confirmation Modal -->
    <div id="submitModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Submit Examination</h3>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to submit your examination?</strong></p>
                <p>Once submitted, you will not be able to make any changes to your answers.</p>
                <p>Time remaining: <span id="modalTimeRemaining">25:30</span></p>
            </div>
            <div class="modal-footer">
                <button onclick="closeSubmitModal()" class="nav-button btn-secondary">Continue Exam</button>
                <button onclick="confirmSubmitExam()" class="nav-button btn-primary">Submit Final Answers</button>
            </div>
        </div>
    </div>

    <!-- Time Warning Modal -->
    <div id="timeWarningModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Time Warning</h3>
            </div>
            <div class="modal-body">
                <p><strong>Only 5 minutes remaining!</strong></p>
                <p>Please review your remaining answers carefully.</p>
            </div>
            <div class="modal-footer">
                <button onclick="closeTimeWarningModal()" class="nav-button btn-primary">Continue Exam</button>
            </div>
        </div>
    </div>

    <!-- Violation Warning Modal -->
    <div id="violationModal" class="violation-modal" style="display: none;">
        <div class="violation-modal-content">
            <div class="violation-modal-icon">🚨</div>
            <h3>Violation Detected!</h3>
            <p id="violationMessage">You have switched tabs or minimized the browser window.</p>
            <div class="violation-count-display" id="violationDisplay">
                Violations: <span id="modalViolationCount">1</span>/5
            </div>
            <p><strong>Warning:</strong> Your exam will be automatically submitted when you reach 5 violations.</p>
            <div class="violation-modal-actions">
                <button onclick="acknowledgeViolation()" class="violation-modal-btn">I Understand - Continue Exam</button>
            </div>
        </div>
    </div>

    <!-- Final Violation Overlay -->
    <div id="finalViolationOverlay" class="violation-overlay" style="display: none;">
        <h1>🚨 EXAM TERMINATED 🚨</h1>
        <p>Maximum violations reached (5/5)</p>
        <p>Your exam has been automatically submitted.</p>
        <p>Redirecting to results page...</p>
    </div>

    <script>
        // Enhanced Exam State Management with Violation Tracking
        let examStartTime = {{ $examStartTime ?? 'Date.now()' }};
        let examDuration = {{ $examDuration ?? 30 }}; // minutes
        let timeWarningShown = false;
        let examTimeEnded = false;
        let violationCount = 0;
        let maxViolations = 5;
        let isTabActive = true;
        let lastViolationType = '';
        let violationTimer = null;

        // Load previous violation count from localStorage
        function initializeViolationSystem() {
            const preRequirements = JSON.parse(localStorage.getItem('examPreRequirements') || '{}');
            if (preRequirements.violationCount !== undefined) {
                violationCount = preRequirements.violationCount;
                maxViolations = preRequirements.maxViolations || 5;
            }
            updateViolationCounter();
        }

        // Update violation counter display
        function updateViolationCounter() {
            const counter = document.getElementById('violationCounter');
            const countElement = document.getElementById('violationCount');
            
            countElement.textContent = violationCount;
            
            // Update visual state based on violation count
            counter.classList.remove('warning', 'danger');
            
            if (violationCount >= 3 && violationCount < 5) {
                counter.classList.add('warning');
            } else if (violationCount >= 4) {
                counter.classList.add('danger');
            }
        }

        // Record a violation
        function recordViolation(type, message) {
            if (examTimeEnded) return;
            
            violationCount++;
            lastViolationType = type;
            
            console.warn(`Violation ${violationCount}/${maxViolations}: ${type}`);
            
            // Update localStorage
            const preRequirements = JSON.parse(localStorage.getItem('examPreRequirements') || '{}');
            preRequirements.violationCount = violationCount;
            preRequirements.lastViolation = {
                type: type,
                message: message,
                timestamp: new Date().toISOString()
            };
            localStorage.setItem('examPreRequirements', JSON.stringify(preRequirements));
            
            updateViolationCounter();
            
            if (violationCount >= maxViolations) {
                handleMaxViolations();
            } else {
                showViolationWarning(message);
            }
        }

        // Show violation warning modal
        function showViolationWarning(message) {
            document.getElementById('violationMessage').textContent = message;
            document.getElementById('modalViolationCount').textContent = violationCount;
            document.getElementById('violationModal').style.display = 'flex';
            
            // Pause timer while modal is open
            clearInterval(violationTimer);
        }

        // Acknowledge violation and continue
        function acknowledgeViolation() {
            document.getElementById('violationModal').style.display = 'none';
            
            // Resume any timers
            if (!examTimeEnded) {
                setupViolationMonitoring();
            }
        }

        // Handle maximum violations reached
        function handleMaxViolations() {
            document.getElementById('finalViolationOverlay').style.display = 'flex';
            
            // Disable all exam interactions
            const buttons = document.querySelectorAll('button, input[type="radio"]');
            buttons.forEach(btn => btn.disabled = true);
            
            // Auto-submit exam after 5 seconds
            setTimeout(() => {
                autoSubmitExam('Maximum violations reached');
            }, 5000);
        }

        // Enhanced violation monitoring system
        function setupViolationMonitoring() {
            // Tab/Window visibility monitoring
            document.addEventListener('visibilitychange', function() {
                if (document.hidden && isTabActive && !examTimeEnded) {
                    isTabActive = false;
                    recordViolation('TAB_SWITCH', 'You switched to another tab or minimized the browser window.');
                } else if (!document.hidden) {
                    isTabActive = true;
                }
            });

            // Window focus/blur monitoring
            window.addEventListener('blur', function() {
                if (isTabActive && !examTimeEnded) {
                    recordViolation('WINDOW_BLUR', 'You clicked outside the exam window or switched applications.');
                }
            });

            // Detect Alt+Tab and other system shortcuts
            document.addEventListener('keydown', function(e) {
                // Alt+Tab detection
                if (e.altKey && e.key === 'Tab') {
                    e.preventDefault();
                    recordViolation('ALT_TAB', 'You attempted to switch applications using Alt+Tab.');
                    return false;
                }

                // Windows key detection
                if (e.key === 'Meta' || e.key === 'OS') {
                    e.preventDefault();
                    recordViolation('WINDOWS_KEY', 'You pressed the Windows key to access the desktop.');
                    return false;
                }

                // Ctrl+Alt+Del, Ctrl+Shift+Esc
                if ((e.ctrlKey && e.altKey && e.key === 'Delete') || 
                    (e.ctrlKey && e.shiftKey && e.key === 'Escape')) {
                    e.preventDefault();
                    recordViolation('SYSTEM_SHORTCUT', 'You attempted to access system functions.');
                    return false;
                }

                // Developer tools shortcuts
                if (e.key === 'F12' || 
                    (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) ||
                    (e.ctrlKey && (e.key === 'u' || e.key === 'U'))) {
                    e.preventDefault();
                    recordViolation('DEV_TOOLS', 'You attempted to access developer tools or view page source.');
                    return false;
                }

                // Copy/Paste prevention
                if (e.ctrlKey && (e.key === 'c' || e.key === 'C' || e.key === 'v' || e.key === 'V' || 
                                 e.key === 'x' || e.key === 'X' || e.key === 'a' || e.key === 'A')) {
                    e.preventDefault();
                    recordViolation('COPY_PASTE', 'You attempted to copy, paste, or select all content.');
                    return false;
                }

                // Print prevention
                if (e.ctrlKey && e.key === 'p') {
                    e.preventDefault();
                    recordViolation('PRINT_ATTEMPT', 'You attempted to print the exam content.');
                    return false;
                }

                // Refresh prevention
                if (e.key === 'F5' || (e.ctrlKey && (e.key === 'r' || e.key === 'R'))) {
                    e.preventDefault();
                    recordViolation('REFRESH_ATTEMPT', 'You attempted to refresh the page.');
                    return false;
                }
            });

            // Mouse right-click monitoring
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                recordViolation('RIGHT_CLICK', 'You attempted to access the context menu.');
                return false;
            });

            // Monitor for multiple windows/tabs
            let windowCount = 1;
            window.addEventListener('storage', function(e) {
                if (e.key === 'examWindowCount') {
                    windowCount = parseInt(e.newValue) || 1;
                    if (windowCount > 1) {
                        recordViolation('MULTIPLE_WINDOWS', 'Multiple exam windows detected.');
                    }
                }
            });

            // Set window count
            localStorage.setItem('examWindowCount', '1');
        }

        // Initialize exam timer
        function initializeTimer() {
            const timerElement = document.getElementById('timeRemaining');
            const examTimerElement = document.getElementById('examTimer');
            
            function updateTimer() {
                const now = Date.now();
                const elapsed = Math.floor((now - examStartTime) / 1000);
                const totalSeconds = examDuration * 60;
                const remaining = Math.max(0, totalSeconds - elapsed);
                
                const minutes = Math.floor(remaining / 60);
                const seconds = remaining % 60;
                
                const timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                timerElement.textContent = timeString;
                document.getElementById('modalTimeRemaining').textContent = timeString;
                
                // Warning at 5 minutes
                if (remaining <= 300 && !timeWarningShown) {
                    timeWarningShown = true;
                    showTimeWarning();
                    examTimerElement.classList.add('timer-warning');
                }
                
                // Auto-submit when time ends
                if (remaining <= 0 && !examTimeEnded) {
                    examTimeEnded = true;
                    autoSubmitExam('Time expired');
                }
            }
            
            updateTimer();
            setInterval(updateTimer, 1000);
        }

        // Form submission functions
        function submitAndNext() {
            const form = document.getElementById('examForm');
            const selectedAnswer = form.querySelector('input[name="selected_answer"]:checked');
            
            if (!selectedAnswer) {
                alert('Please select an answer before proceeding.');
                return;
            }
            
            // In a real application, this would submit via AJAX
            console.log('Submitting answer:', selectedAnswer.value);
            
            // Simulate moving to next question
            const nextButton = document.getElementById('nextButton');
            nextButton.disabled = true;
            nextButton.innerHTML = 'Loading...';
            
            setTimeout(() => {
                // In a real app, this would redirect to the next question
                alert('Answer saved! Moving to next question... (Demo mode)');
                nextButton.disabled = false;
                nextButton.innerHTML = 'Next Question →';
            }, 1000);
        }

        function submitExam() {
            const form = document.getElementById('examForm');
            const selectedAnswer = form.querySelector('input[name="selected_answer"]:checked');
            
            if (!selectedAnswer) {
                alert('Please select an answer before submitting the exam.');
                return;
            }
            
            showSubmitModal();
        }

        function confirmSubmitExam() {
            const form = document.getElementById('examForm');
            
            // Collect all answers from the current session
            const examAnswers = collectAllAnswers();
            const applicantId = getApplicantId(); // You'll need to implement this
            
            // Submit exam to server
            fetch('{{ route('exam.complete') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    applicant_id: applicantId,
                    answers: examAnswers,
                    exam_session_id: 'demo_session_' + Date.now()
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeSubmitModal();
                    showExamCompletionModal(data);
                } else {
                    alert('Failed to submit exam: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error submitting exam:', error);
                alert('Failed to submit exam. Please try again.');
            });
        }

        function autoSubmitExam(reason = 'Time expired') {
            examTimeEnded = true;
            
            // Collect all answers and submit
            const examAnswers = collectAllAnswers();
            const applicantId = getApplicantId();
            
            fetch('{{ route('exam.complete') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    applicant_id: applicantId,
                    answers: examAnswers,
                    exam_session_id: 'demo_session_' + Date.now(),
                    auto_submitted: true,
                    auto_submit_reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showExamCompletionModal(data, true, reason);
                } else {
                    alert('Failed to submit exam: ' + data.message);
                    // Fallback to results page
                    window.location.href = "{{ route('exam.results') }}";
                }
            })
            .catch(error => {
                console.error('Error auto-submitting exam:', error);
                alert(`${reason}! Your exam has been automatically submitted.`);
                // Fallback to results page
                setTimeout(() => {
                    window.location.href = "{{ route('exam.results') }}";
                }, 3000);
            });
        }

        // Modal functions
        function showSubmitModal() {
            document.getElementById('submitModal').style.display = 'flex';
        }

        function closeSubmitModal() {
            document.getElementById('submitModal').style.display = 'none';
        }

        function showTimeWarning() {
            document.getElementById('timeWarningModal').style.display = 'flex';
        }

        function closeTimeWarningModal() {
            document.getElementById('timeWarningModal').style.display = 'none';
        }

        // Option selection handling
        function setupOptionSelection() {
            const options = document.querySelectorAll('.option-label');
            
            options.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    options.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Check the radio button
                    const radio = this.querySelector('.option-input');
                    if (radio) {
                        radio.checked = true;
                    }
                });
            });
            
            // Set initial selected state
            const checkedOption = document.querySelector('.option-input:checked');
            if (checkedOption) {
                checkedOption.closest('.option-label').classList.add('selected');
            }
        }

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Check if pre-requirements were completed
            const preRequirements = JSON.parse(localStorage.getItem('examPreRequirements') || '{}');
            if (!preRequirements.instructionsAcknowledged) {
                alert('Please complete the pre-exam requirements first.');
                window.location.href = "{{ route('exam.pre-requirements') }}";
                return;
            }

            initializeViolationSystem();
            initializeTimer();
            setupOptionSelection();
            setupViolationMonitoring();
            
            // Enter fullscreen mode
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Fullscreen not supported or denied');
                });
            }
            
            console.log('Enhanced exam interface initialized with violation tracking');
        });

        // Warn on page unload
        window.addEventListener('beforeunload', function(e) {
            if (!examTimeEnded) {
                e.preventDefault();
                e.returnValue = 'Are you sure you want to leave? Your exam progress may be lost.';
                return e.returnValue;
            }
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                if (e.target.id === 'submitModal') {
                    closeSubmitModal();
                } else if (e.target.id === 'timeWarningModal') {
                    closeTimeWarningModal();
                }
            }
        });

    // Clean up on page unload
    window.addEventListener('unload', function() {
        localStorage.removeItem('examWindowCount');
    });

    // Helper functions for exam submission
    function collectAllAnswers() {
        // Demo implementation - collect answers from current session
        // In a real implementation, this would collect all answers from the session
        const form = document.getElementById('examForm');
        const selectedAnswer = form.querySelector('input[name="selected_answer"]:checked');
        
        // Demo answers - in real implementation, collect from session storage
        const demoAnswers = {};
        if (selectedAnswer) {
            const questionId = form.querySelector('input[name="question_id"]').value;
            demoAnswers[questionId] = selectedAnswer.value;
        }
        
        // Add some demo answers for testing
        for (let i = 1; i <= 20; i++) {
            if (!demoAnswers[i]) {
                demoAnswers[i] = 'a'; // Demo answer
            }
        }
        
        return demoAnswers;
    }

    function getApplicantId() {
        // Demo implementation - in real app, get from session or URL parameter
        return 1; // Demo applicant ID
    }

    function showExamCompletionModal(data, isAutoSubmitted = false, reason = null) {
        // Create completion modal
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.style.display = 'flex';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 600px; text-align: center;">
                <div class="modal-header">
                    <h3 style="color: ${isAutoSubmitted ? '#DC2626' : '#059669'}; margin-bottom: 16px;">
                        ${isAutoSubmitted ? '⚠️ Exam Auto-Submitted' : '🎉 Exam Completed Successfully!'}
                    </h3>
                </div>
                <div class="modal-body">
                    ${isAutoSubmitted ? `<p style="color: #DC2626; margin-bottom: 16px;"><strong>Reason:</strong> ${reason}</p>` : ''}
                    <div style="background: #F3F4F6; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <h4 style="margin: 0 0 16px 0; color: #1F2937;">Your Results</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; text-align: left;">
                            <div><strong>Score:</strong> ${data.score}%</div>
                            <div><strong>Grade:</strong> ${data.verbal_description}</div>
                            <div><strong>Correct Answers:</strong> ${data.correct_answers}/${data.total_questions}</div>
                            <div><strong>Points Earned:</strong> ${data.total_score}/${data.max_score}</div>
                        </div>
                    </div>
                    <div style="background: #EDE9FE; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                        <h4 style="margin: 0 0 8px 0; color: #7C3AED;">🎯 What's Next?</h4>
                        <p style="margin: 0; color: #6B46C1;">
                            Your exam has been completed and you've been automatically added to the interview pool. 
                            Instructors can now claim your interview for scheduling. You'll be contacted soon!
                        </p>
                    </div>
                    <p style="color: #6B7280;">
                        You will be redirected to the results page in <span id="countdown">5</span> seconds...
                    </p>
                </div>
                <div class="modal-footer">
                    <button onclick="goToResults()" style="background: #059669; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer;">
                        View Detailed Results
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Countdown timer
        let countdown = 5;
        const countdownElement = modal.querySelector('#countdown');
        const timer = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            if (countdown <= 0) {
                clearInterval(timer);
                goToResults();
            }
        }, 1000);
        
        // Store results data for results page
        localStorage.setItem('examResults', JSON.stringify(data));
    }

    function goToResults() {
        window.location.href = "{{ route('exam.results') }}";
    }
    </script>
</body>
</html>