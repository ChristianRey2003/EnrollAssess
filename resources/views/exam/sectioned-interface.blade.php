<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>BSIT Entrance Examination - {{ config('app.name', 'EnrollAssess') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/exam/exam-interface.css') }}" rel="stylesheet">

    <style>
        .exam-content {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        .option-label, .option-input, .essay-textarea {
            -webkit-user-select: auto;
            -moz-user-select: auto;
            -ms-user-select: auto;
            user-select: auto;
        }

        .exam-sections {
            display: flex;
            flex-direction: column;
            gap: 24px;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .exam-section {
            background: #fff;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .exam-section.completed {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .section-header {
            background: #f9fafb;
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .exam-section.completed .section-header {
            background: #dcfce7;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 18px;
            color: #1f2937;
        }

        .section-icon {
            background: #3b82f6;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .exam-section.completed .section-icon {
            background: #10b981;
        }

        .section-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 14px;
            color: #6b7280;
        }

        .section-content {
            padding: 24px;
        }

        .question-grid {
            display: grid;
            gap: 20px;
        }

        .question-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            background: #fafafa;
        }

        .question-number {
            font-weight: 600;
            color: #3b82f6;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .question-text {
            font-size: 16px;
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .question-options {
            display: grid;
            gap: 8px;
        }

        .option-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .option-group:hover {
            background: #f3f4f6;
        }

        .option-group.selected {
            background: #dbeafe;
            border: 1px solid #3b82f6;
        }

        .option-input {
            margin-top: 2px;
        }

        .option-letter {
            font-weight: 600;
            color: #374151;
            min-width: 24px;
        }

        .option-text {
            flex: 1;
            color: #374151;
        }

        .essay-textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }

        .essay-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .section-actions {
            padding: 16px 24px;
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

        .section-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #2563eb;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background: #059669;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .violation-counter {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            border: 2px solid transparent;
        }

        .violation-counter.warning {
            background: rgba(245, 158, 11, 0.2);
            border-color: #F59E0B;
        }

        .violation-counter.danger {
            background: rgba(220, 38, 38, 0.2);
            border-color: #DC2626;
        }

        .violation-text {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }

        .modal-overlay {
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
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .modal-header h3 {
            color: #1f2937;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 16px 0;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 24px;
        }

        @media (max-width: 768px) {
            .exam-sections {
                padding: 12px;
                gap: 16px;
            }

            .section-header {
                padding: 12px 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .section-content {
                padding: 16px;
            }

            .section-actions {
                padding: 12px 16px;
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body class="exam-page" oncontextmenu="return false;">
    <div class="exam-container">
        <header class="exam-header">
            <h1 class="exam-title">BSIT Entrance Examination</h1>
            <div class="exam-progress">
                <div class="progress-indicator">
                    @if(isset($sections))
                        {{ $sections->sum('count') }} Questions in {{ $sections->count() }} Sections
                    @else
                        {{ $totalQuestions ?? 20 }} Questions
                    @endif
                </div>
                <div class="exam-meta">
                    <div class="violation-counter" id="violationCounter">
                        <span class="violation-icon">⚠️</span>
                        <span class="violation-text">Violations: <span id="violationCount">0</span>/5</span>
                    </div>
                    <div class="exam-timer" id="examTimer">
                        <span id="timeRemaining">{{ gmdate('i:s', $timeRemaining ?? 1800) }}</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar" style="width: 0%;"></div>
        </div>

        <main class="exam-content">
            <div class="exam-sections">
                @if(isset($sections) && $sections->count() > 0)
                    @foreach($sections as $index => $section)
                        <div class="exam-section" id="section-{{ $index }}" data-section-type="{{ $section['type'] }}">
                            <div class="section-header">
                                <div class="section-title">
                                    <span class="section-icon">{{ $section['icon'] }}</span>
                                    <span>{{ $section['label'] }}</span>
                                </div>
                                <div class="section-meta">
                                    <span>{{ $section['count'] }} Question{{ $section['count'] !== 1 ? 's' : '' }}</span>
                                    <span class="section-status" id="status-{{ $index }}">Pending</span>
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
                                                                <span class="option-letter">{{ chr(65 + $optionIndex) }})</span>
                                                                <span class="option-text">{{ $option->option_text }}</span>
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

                            <div class="section-actions">
                                <div class="section-progress">
                                    <span id="progress-{{ $index }}">0/{{ $section['count'] }} answered</span>
                                </div>
                                <div class="section-buttons">
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
                            <p>No questions available for this exam set.</p>
                        </div>
                    </div>
                @endif
            </div>
        </main>

        <footer class="exam-footer">
            <p class="exam-instructions">
                <strong>Instructions:</strong> Answer all questions in each section before submitting. 
                You can navigate within the current section but cannot return to completed sections.
            </p>
        </footer>
    </div>

    <!-- Modals -->
    <div id="submitModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Submit Section</h3>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to submit this section?</strong></p>
                <p>Once submitted, you cannot return to modify your answers.</p>
                <div id="sectionSummary"></div>
            </div>
            <div class="modal-footer">
                <button onclick="closeSubmitModal()" class="btn btn-secondary">Review Answers</button>
                <button onclick="confirmSubmitSection()" class="btn btn-primary">Submit Section</button>
            </div>
        </div>
    </div>

    <div id="finalSubmitModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Complete Examination</h3>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to complete your examination?</strong></p>
                <p>Once submitted, you will not be able to make any changes to your answers.</p>
                <p>Time remaining: <span id="modalTimeRemaining">{{ gmdate('i:s', $timeRemaining ?? 1800) }}</span></p>
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

        document.addEventListener('DOMContentLoaded', function() {
            initializeTimer();
            initializeViolationSystem();
            setupViolationMonitoring();
            initializeSectionAnswers();
            updateSectionStates();
        });

        function initializeTimer() {
            function updateTimer() {
                timeRemaining = Math.max(0, timeRemaining - 1);
                
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                const timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                document.getElementById('timeRemaining').textContent = timeString;
                document.getElementById('modalTimeRemaining').textContent = timeString;
                
                if (timeRemaining <= 0) {
                    autoSubmitExam('Time expired');
                    return;
                }
                
                if (timeRemaining === 300) {
                    alert('5 minutes remaining!');
                    document.getElementById('examTimer').style.color = '#dc2626';
                }
            }
            
            setInterval(updateTimer, 1000);
        }

        function initializeViolationSystem() {
            violationCount = 0;
            updateViolationCounter();
        }

        function updateViolationCounter() {
            const counter = document.getElementById('violationCounter');
            const countElement = document.getElementById('violationCount');
            
            countElement.textContent = violationCount;
            
            counter.classList.remove('warning', 'danger');
            if (violationCount >= 3 && violationCount < 5) {
                counter.classList.add('warning');
            } else if (violationCount >= 4) {
                counter.classList.add('danger');
            }
        }

        function setupViolationMonitoring() {
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    recordViolation('TAB_SWITCH', 'You switched to another tab or minimized the browser window.');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'F12' || 
                    (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) ||
                    (e.ctrlKey && (e.key === 'u' || e.key === 'U'))) {
                    e.preventDefault();
                    recordViolation('DEV_TOOLS', 'You attempted to access developer tools.');
                    return false;
                }

                if (e.ctrlKey && (e.key === 'c' || e.key === 'v' || e.key === 'x' || e.key === 'a')) {
                    e.preventDefault();
                    recordViolation('COPY_PASTE', 'You attempted to copy, paste, or select content.');
                    return false;
                }
            });
        }

        function recordViolation(type, message) {
            violationCount++;
            updateViolationCounter();
            
            if (violationCount >= 5) {
                autoSubmitExam('Maximum violations reached');
            } else {
                alert(`Warning: ${message}\nViolations: ${violationCount}/5`);
            }
        }

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
                        statusElement.textContent = 'Completed';
                        statusElement.style.color = '#10b981';
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
            if (progressBar) {
                const percentage = totalQuestions > 0 ? (totalAnswered / totalQuestions) * 100 : 0;
                progressBar.style.width = `${percentage}%`;
            }
        }

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
                alert(`Please answer all questions in this section. ${unanswered} question(s) remaining.`);
                return;
            }

            const sectionType = form.closest('.exam-section').dataset.sectionType;
            const summary = `${Object.keys(answers).length} question(s) answered in ${sectionType.replace('_', ' ')} section.`;
            
            document.getElementById('sectionSummary').innerHTML = `<p>${summary}</p>`;
            
            if (isFinalSubmit) {
                document.getElementById('finalSubmitModal').style.display = 'flex';
            } else {
                document.getElementById('submitModal').style.display = 'flex';
            }

            window.tempSectionAnswers = answers;
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
                    statusElement.textContent = 'Completed';
                    statusElement.style.color = '#10b981';

                    completedSections.push(sectionType);
                    Object.assign(sectionAnswers, window.tempSectionAnswers);

                    closeSubmitModal();
                    
                    const nextSection = document.querySelector(`#section-${currentSubmittingSection + 1}`);
                    if (nextSection) {
                        nextSection.scrollIntoView({ behavior: 'smooth' });
                    }
                } else {
                    alert('Failed to submit section: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error submitting section:', error);
                alert('Failed to submit section. Please try again.');
            });
        }

        function confirmFinalSubmit() {
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

        function autoSubmitExam(reason) {
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
                if (data.success) {
                    showExamCompletionModal(data, true, reason);
                } else {
                    alert(`${reason}! Redirecting to results page...`);
                    setTimeout(() => {
                        window.location.href = "{{ route('exam.results') }}";
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error auto-submitting exam:', error);
                alert(`${reason}! Redirecting to results page...`);
                setTimeout(() => {
                    window.location.href = "{{ route('exam.results') }}";
                }, 3000);
            });
        }

        function showExamCompletionModal(data, isAutoSubmitted = false, reason = null) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.style.display = 'flex';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 600px; text-align: center;">
                    <div class="modal-header">
                        <h3 style="color: ${isAutoSubmitted ? '#DC2626' : '#059669'}; margin-bottom: 16px;">
                            ${isAutoSubmitted ? 'Exam Auto-Submitted' : 'Exam Completed Successfully!'}
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
                        <p style="color: #6B7280;">
                            You will be redirected to the results page in <span id="countdown">5</span> seconds...
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button onclick="goToResults()" class="btn btn-success">
                            View Detailed Results
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
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
        }

        function goToResults() {
            window.location.href = "{{ route('exam.results') }}";
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('essay-textarea')) {
                const form = e.target.closest('.section-form');
                const sectionIndex = parseInt(form.dataset.sectionIndex);
                updateSectionProgress(sectionIndex);
            }
        });

        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to leave? Your exam progress may be lost.';
            return e.returnValue;
        });
    </script>
</body>
</html>
