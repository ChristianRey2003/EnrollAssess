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
                <div class="exam-timer" id="examTimer">
                    ⏰ <span id="timeRemaining">25:30</span>
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
                    ← Previous
                </button>
            @else
                <div></div>
            @endif

            @if(($currentQuestionNumber ?? 5) < ($totalQuestions ?? 20))
                <button type="button" class="nav-button btn-primary" onclick="submitAndNext()" id="nextButton">
                    Next Question →
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
                <strong>💡 Instructions:</strong> Select one answer and click Next to continue. 
                <strong>⚠️ Warning:</strong> You cannot return to previous questions once you proceed.
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
                <h3>⚠️ Time Warning</h3>
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

    <script>
        // Exam state management
        let examStartTime = {{ $examStartTime ?? 'Date.now()' }};
        let examDuration = {{ $examDuration ?? 30 }}; // minutes
        let timeWarningShown = false;
        let examTimeEnded = false;

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
                    autoSubmitExam();
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
            
            // In a real application, this would submit the final exam
            console.log('Submitting final exam');
            
            alert('Exam submitted successfully! Thank you for participating. (Demo mode)');
            closeSubmitModal();
            
            // In a real app, redirect to completion page
            // window.location.href = '/exam/complete';
        }

        function autoSubmitExam() {
            alert('Time is up! Your exam has been automatically submitted.');
            // In a real app, this would automatically submit the exam
            // confirmSubmitExam();
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

        // Prevent cheating measures
        function setupSecurityMeasures() {
            // Disable common keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U, Ctrl+S, F5
                if (e.key === 'F12' || 
                    (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J')) ||
                    (e.ctrlKey && (e.key === 'u' || e.key === 'U' || e.key === 's' || e.key === 'S')) ||
                    e.key === 'F5' ||
                    (e.ctrlKey && e.key === 'r')) {
                    e.preventDefault();
                    alert('This action is not allowed during the examination.');
                    return false;
                }
            });

            // Warn on page unload
            window.addEventListener('beforeunload', function(e) {
                if (!examTimeEnded) {
                    e.preventDefault();
                    e.returnValue = 'Are you sure you want to leave? Your exam progress may be lost.';
                    return e.returnValue;
                }
            });

            // Detect tab switching (basic detection)
            let isTabActive = true;
            let tabSwitchCount = 0;
            
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    isTabActive = false;
                    tabSwitchCount++;
                    console.warn('Tab switch detected:', tabSwitchCount);
                    
                    if (tabSwitchCount >= 3) {
                        alert('Warning: Multiple tab switches detected. Your exam may be flagged for review.');
                    }
                } else {
                    isTabActive = true;
                }
            });
        }

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeTimer();
            setupOptionSelection();
            setupSecurityMeasures();
            
            console.log('Exam interface initialized');
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
    </script>
</body>
</html>