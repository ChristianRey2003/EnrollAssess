<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Add New Question - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Admin Dashboard CSS -->
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
</head>
<body class="admin-page">
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo">
                    <div>
                        <h2 class="sidebar-title">EnrollAssess</h2>
                        <p class="sidebar-subtitle">Admin Portal</p>
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.exams.index') }}" class="nav-link">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Exams</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.questions') }}" class="nav-link active">
                        <span class="nav-icon">❓</span>
                        <span class="nav-text">Questions</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.applicants') }}" class="nav-link">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">Applicants</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.reports') }}" class="nav-link">
                        <span class="nav-icon">📈</span>
                        <span class="nav-text">Reports</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="logout-link">
                        <span class="nav-icon">🚪</span>
                        <span class="nav-text">Logout</span>
                    </button>
                </form>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <div class="main-header">
                <div class="header-left">
                    <h1>{{ isset($question) ? 'Edit Question' : 'Add New Question' }}</h1>
                    <p class="header-subtitle">{{ isset($question) ? 'Modify existing exam question' : 'Create a new exam question for BSIT entrance examination' }}</p>
                </div>
                <div class="header-right">
                    <div class="header-time">
                        🕐 {{ now()->format('M d, Y g:i A') }}
                    </div>
                    <div class="header-user">
                        {{ auth()->user()->name ?? 'Dr. Admin' }}
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Breadcrumb -->
                <div class="breadcrumb">
                    <a href="{{ route('admin.questions') }}" class="breadcrumb-link">Questions</a>
                    <span class="breadcrumb-separator">›</span>
                    <span class="breadcrumb-current">{{ isset($question) ? 'Edit Question' : 'Add New Question' }}</span>
                </div>

                <!-- Question Form -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Question Details</h2>
                        <div class="form-actions-header">
                            <a href="{{ route('admin.questions') }}" class="btn-secondary">
                                ← Back to Questions
                            </a>
                        </div>
                    </div>
                    <div class="section-content">
                        <form method="POST" action="{{ isset($question) ? route('admin.questions.update', $question->question_id) : route('admin.questions.store') }}" id="questionForm" class="question-form">
                            @csrf
                            @if(isset($question))
                                @method('PUT')
                            @endif

                            <!-- Exam Set Selection -->
                            <div class="form-group">
                                <label for="exam_set_id" class="form-label required">Exam Set</label>
                                <select id="exam_set_id" name="exam_set_id" class="form-select @error('exam_set_id') is-invalid @enderror" required>
                                    <option value="">Select Exam Set</option>
                                    @foreach($examSets ?? [] as $examSet)
                                        <option value="{{ $examSet->exam_set_id }}" 
                                            {{ old('exam_set_id', $question->exam_set_id ?? '') == $examSet->exam_set_id ? 'selected' : '' }}>
                                            {{ $examSet->exam->title ?? 'Exam' }} - {{ $examSet->set_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('exam_set_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-help">Select which exam set this question belongs to.</div>
                            </div>

                            <!-- Question Type -->
                            <div class="form-group">
                                <label for="question_type" class="form-label required">Question Type</label>
                                <select id="question_type" name="question_type" class="form-select @error('question_type') is-invalid @enderror" required onchange="toggleQuestionTypeFields()">
                                    <option value="">Select Question Type</option>
                                    <option value="multiple_choice" {{ old('question_type', $question->question_type ?? '') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="true_false" {{ old('question_type', $question->question_type ?? '') == 'true_false' ? 'selected' : '' }}>True/False</option>
                                    <option value="short_answer" {{ old('question_type', $question->question_type ?? '') == 'short_answer' ? 'selected' : '' }}>Short Answer</option>
                                    <option value="essay" {{ old('question_type', $question->question_type ?? '') == 'essay' ? 'selected' : '' }}>Essay</option>
                                </select>
                                @error('question_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-help">Choose the format for this question.</div>
                            </div>

                            <!-- Question Text -->
                            <div class="form-group">
                                <label for="question_text" class="form-label required">Question Text</label>
                                <textarea id="question_text" 
                                         name="question_text" 
                                         class="form-textarea @error('question_text') is-invalid @enderror"
                                         rows="4"
                                         placeholder="Enter the complete question text here..."
                                         required>{{ old('question_text', $question->question_text ?? '') }}</textarea>
                                @error('question_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-help">Write a clear, concise question. Be specific and avoid ambiguous language.</div>
                            </div>

                            <!-- Multiple Choice Options -->
                            <div id="multiple_choice_section" class="question-type-section" style="display: none;">
                                <div class="form-group">
                                    <label class="form-label required">Multiple Choice Options</label>
                                    <div class="options-grid">
                                        @for($i = 0; $i < 6; $i++)
                                            @php
                                                $letter = chr(65 + $i); // A, B, C, D, E, F
                                                $existing_option = null;
                                                if (isset($question) && $question->options && $question->options->count() > $i) {
                                                    $existing_option = $question->options->skip($i)->first();
                                                }
                                                $option_value = old("options.{$i}", $existing_option->option_text ?? '');
                                            @endphp
                                            <div class="option-input-group">
                                                <label for="option_{{ $i }}" class="option-label-text">Option {{ $letter }}</label>
                                                <input type="text" 
                                                       id="option_{{ $i }}" 
                                                       name="options[{{ $i }}]" 
                                                       class="form-control option-input @error("options.{$i}") is-invalid @enderror"
                                                       placeholder="Enter option {{ $letter }}"
                                                       value="{{ $option_value }}">
                                                @error("options.{$i}")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endfor
                                    </div>
                                    <div class="form-help">Enter at least 2 options. Leave unused options blank.</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label required">Correct Answer</label>
                                    <div class="correct-answer-options">
                                        @for($i = 0; $i < 6; $i++)
                                            @php
                                                $letter = chr(65 + $i); // A, B, C, D, E, F
                                                $is_correct = false;
                                                if (isset($question) && $question->options && $question->options->count() > $i) {
                                                    $option = $question->options->skip($i)->first();
                                                    $is_correct = $option && $option->is_correct;
                                                }
                                                $is_checked = old('correct_answer', $is_correct ? $i : null) == $i;
                                            @endphp
                                            <label class="correct-answer-label" id="correct_label_{{ $i }}" style="display: none;">
                                                <input type="radio" 
                                                       name="correct_answer" 
                                                       value="{{ $i }}"
                                                       class="correct-answer-input"
                                                       {{ $is_checked ? 'checked' : '' }}>
                                                <span class="correct-answer-text">Option {{ $letter }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                    @error('correct_answer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">Select which option is the correct answer to this question.</div>
                                </div>
                            </div>

                            <!-- True/False Section -->
                            <div id="true_false_section" class="question-type-section" style="display: none;">
                                <div class="form-group">
                                    <label class="form-label required">Correct Answer</label>
                                    <div class="correct-answer-options">
                                        @php
                                            $tf_correct = null;
                                            if (isset($question) && $question->options && $question->options->count() > 0) {
                                                $correct_option = $question->options->where('is_correct', true)->first();
                                                $tf_correct = $correct_option ? strtolower($correct_option->option_text) : null;
                                            }
                                            $selected_answer = old('correct_answer', $tf_correct);
                                        @endphp
                                        <label class="correct-answer-label">
                                            <input type="radio" 
                                                   name="correct_answer" 
                                                   value="true"
                                                   class="correct-answer-input"
                                                   {{ $selected_answer === 'true' ? 'checked' : '' }}>
                                            <span class="correct-answer-text">True</span>
                                        </label>
                                        <label class="correct-answer-label">
                                            <input type="radio" 
                                                   name="correct_answer" 
                                                   value="false"
                                                   class="correct-answer-input"
                                                   {{ $selected_answer === 'false' ? 'checked' : '' }}>
                                            <span class="correct-answer-text">False</span>
                                        </label>
                                    </div>
                                    @error('correct_answer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">Select whether the statement is true or false.</div>
                                </div>
                            </div>

                            <!-- Short Answer Section -->
                            <div id="short_answer_section" class="question-type-section" style="display: none;">
                                <div class="form-group">
                                    <label for="sample_answer" class="form-label">Sample Answer</label>
                                    <textarea id="sample_answer" 
                                             name="sample_answer" 
                                             class="form-textarea @error('sample_answer') is-invalid @enderror"
                                             rows="3"
                                             placeholder="Provide a sample correct answer for reference...">{{ old('sample_answer', isset($question) && $question->options && $question->options->count() > 0 ? $question->options->first()->option_text : '') }}</textarea>
                                    @error('sample_answer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">This will help graders evaluate student responses consistently.</div>
                                </div>
                            </div>

                            <!-- Essay Section -->
                            <div id="essay_section" class="question-type-section" style="display: none;">
                                <div class="form-group">
                                    <label for="sample_answer" class="form-label">Sample Answer</label>
                                    <textarea id="essay_sample_answer" 
                                             name="sample_answer" 
                                             class="form-textarea @error('sample_answer') is-invalid @enderror"
                                             rows="5"
                                             placeholder="Provide a comprehensive sample answer...">{{ old('sample_answer', isset($question) && $question->options && $question->options->count() > 0 ? $question->options->first()->option_text : '') }}</textarea>
                                    @error('sample_answer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">Provide a detailed sample answer to guide the grading process.</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="grading_criteria" class="form-label">Grading Criteria</label>
                                    <textarea id="grading_criteria" 
                                             name="grading_criteria" 
                                             class="form-textarea @error('grading_criteria') is-invalid @enderror"
                                             rows="3"
                                             placeholder="Specify the criteria for grading this essay question...">{{ old('grading_criteria') }}</textarea>
                                    @error('grading_criteria')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">Define what elements should be present for full marks.</div>
                                </div>
                            </div>

                            <!-- Additional Settings -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="points" class="form-label">Points</label>
                                    <input type="number" 
                                           id="points" 
                                           name="points" 
                                           class="form-control @error('points') is-invalid @enderror"
                                           min="1" 
                                           max="100" 
                                           value="{{ old('points', $question->points ?? 1) }}"
                                           placeholder="1">
                                    @error('points')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">How many points this question is worth (1-100).</div>
                                </div>

                                <div class="form-group">
                                    <label for="explanation" class="form-label">Explanation (Optional)</label>
                                    <textarea id="explanation" 
                                             name="explanation" 
                                             class="form-textarea @error('explanation') is-invalid @enderror"
                                             rows="2"
                                             placeholder="Provide an explanation for the correct answer...">{{ old('explanation', $question->explanation ?? '') }}</textarea>
                                    @error('explanation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help">This explanation will be shown to applicants after the exam.</div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="button" onclick="previewQuestion()" class="btn-secondary">
                                    👁️ Preview Question
                                </button>
                                <button type="submit" class="btn-primary" id="saveButton">
                                    💾 {{ isset($question) ? 'Update Question' : 'Save Question' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="modal-overlay" style="display: none;">
        <div class="modal-content preview-modal">
            <div class="modal-header">
                <h3>Question Preview</h3>
                <button onclick="closePreviewModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <div class="preview-question">
                    <div class="preview-question-text" id="previewQuestionText"></div>
                    <div class="preview-options" id="previewOptions"></div>
                    <div class="preview-correct" id="previewCorrect"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closePreviewModal()" class="btn-secondary">Close Preview</button>
                <button onclick="submitForm()" class="btn-primary">Looks Good - Save Question</button>
            </div>
        </div>
    </div>

    <script>
        // Toggle question type sections
        function toggleQuestionTypeFields() {
            const questionType = document.getElementById('question_type').value;
            const sections = document.querySelectorAll('.question-type-section');
            
            // Hide all sections
            sections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Show relevant section
            if (questionType) {
                const targetSection = document.getElementById(`${questionType}_section`);
                if (targetSection) {
                    targetSection.style.display = 'block';
                }
            }
            
            // For multiple choice, update correct answer options visibility
            if (questionType === 'multiple_choice') {
                updateMultipleChoiceOptions();
            }
        }
        
        // Update multiple choice correct answer options based on filled options
        function updateMultipleChoiceOptions() {
            const optionInputs = document.querySelectorAll('input[name^="options["]');
            const correctLabels = document.querySelectorAll('[id^="correct_label_"]');
            
            optionInputs.forEach((input, index) => {
                const label = document.getElementById(`correct_label_${index}`);
                if (label) {
                    if (input.value.trim()) {
                        label.style.display = 'flex';
                    } else {
                        label.style.display = 'none';
                        // Uncheck if hidden
                        const radio = label.querySelector('input[type="radio"]');
                        if (radio) radio.checked = false;
                    }
                }
            });
        }
        
        // Form validation and preview
        function previewQuestion() {
            const questionText = document.getElementById('question_text').value;
            const questionType = document.getElementById('question_type').value;
            
            // Validate
            if (!questionText.trim()) {
                alert('Please enter the question text.');
                return;
            }
            
            if (!questionType) {
                alert('Please select a question type.');
                return;
            }
            
            let previewHtml = '';
            let correctAnswer = null;
            
            // Build preview based on question type
            switch(questionType) {
                case 'multiple_choice':
                    const options = [];
                    for (let i = 0; i < 6; i++) {
                        const input = document.querySelector(`input[name="options[${i}]"]`);
                        if (input && input.value.trim()) {
                            options.push(input.value.trim());
                        }
                    }
                    
                    if (options.length < 2) {
                        alert('Please provide at least 2 options for multiple choice questions.');
                        return;
                    }
                    
                    const correctAnswerInput = document.querySelector('input[name="correct_answer"]:checked');
                    if (!correctAnswerInput) {
                        alert('Please select the correct answer.');
                        return;
                    }
                    
                    correctAnswer = parseInt(correctAnswerInput.value);
                    
                    options.forEach((option, index) => {
                        const letter = String.fromCharCode(65 + index);
                        const isCorrect = index === correctAnswer;
                        previewHtml += `
                            <div class="preview-option ${isCorrect ? 'correct-option' : ''}">
                                <span class="option-letter">${letter})</span>
                                <span class="option-text">${option}</span>
                                ${isCorrect ? '<span class="correct-indicator">✓ Correct</span>' : ''}
                            </div>
                        `;
                    });
                    break;
                    
                case 'true_false':
                    const tfCorrect = document.querySelector('input[name="correct_answer"]:checked');
                    if (!tfCorrect) {
                        alert('Please select the correct answer.');
                        return;
                    }
                    
                    ['True', 'False'].forEach((option, index) => {
                        const isCorrect = (index === 0 && tfCorrect.value === 'true') || (index === 1 && tfCorrect.value === 'false');
                        previewHtml += `
                            <div class="preview-option ${isCorrect ? 'correct-option' : ''}">
                                <span class="option-letter">${option}</span>
                                ${isCorrect ? '<span class="correct-indicator">✓ Correct</span>' : ''}
                            </div>
                        `;
                    });
                    break;
                    
                case 'short_answer':
                    const shortSample = document.getElementById('sample_answer').value;
                    previewHtml = `
                        <div class="preview-text-answer">
                            <strong>Answer Type:</strong> Short Answer<br>
                            ${shortSample ? `<strong>Sample Answer:</strong> ${shortSample}` : '<em>No sample answer provided</em>'}
                        </div>
                    `;
                    break;
                    
                case 'essay':
                    const essaySample = document.getElementById('essay_sample_answer').value;
                    const criteria = document.getElementById('grading_criteria').value;
                    previewHtml = `
                        <div class="preview-text-answer">
                            <strong>Answer Type:</strong> Essay<br>
                            ${essaySample ? `<strong>Sample Answer:</strong> ${essaySample}<br>` : ''}
                            ${criteria ? `<strong>Grading Criteria:</strong> ${criteria}` : ''}
                        </div>
                    `;
                    break;
            }
            
            // Update preview
            document.getElementById('previewQuestionText').textContent = questionText;
            document.getElementById('previewOptions').innerHTML = previewHtml;
            document.getElementById('previewCorrect').innerHTML = questionType === 'multiple_choice' && correctAnswer !== null ? 
                `<strong>Correct Answer: Option ${String.fromCharCode(65 + correctAnswer)}</strong>` : '';
            
            // Show modal
            document.getElementById('previewModal').style.display = 'flex';
        }
        
        function closePreviewModal() {
            document.getElementById('previewModal').style.display = 'none';
        }
        
        function submitForm() {
            closePreviewModal();
            document.getElementById('questionForm').submit();
        }
        
        // Auto-save draft functionality
        let saveTimeout;
        function autoSave() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                // In a real application, this would save a draft
                console.log('Auto-saving draft...');
            }, 3000);
        }
        
        // Add auto-save listeners and initialize form
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', autoSave);
            });
            
            // Initialize question type display
            toggleQuestionTypeFields();
            
            // Add listeners for multiple choice option changes
            const optionInputs = document.querySelectorAll('input[name^="options["]');
            optionInputs.forEach(input => {
                input.addEventListener('input', updateMultipleChoiceOptions);
            });
            
            // Set up form validation
            const form = document.getElementById('questionForm');
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
        });
        
        // Form validation
        function validateForm() {
            const questionType = document.getElementById('question_type').value;
            const questionText = document.getElementById('question_text').value;
            const examSetId = document.getElementById('exam_set_id').value;
            
            if (!examSetId) {
                alert('Please select an exam set.');
                return false;
            }
            
            if (!questionType) {
                alert('Please select a question type.');
                return false;
            }
            
            if (!questionText.trim()) {
                alert('Please enter the question text.');
                return false;
            }
            
            // Type-specific validation
            switch(questionType) {
                case 'multiple_choice':
                    const options = [];
                    for (let i = 0; i < 6; i++) {
                        const input = document.querySelector(`input[name="options[${i}]"]`);
                        if (input && input.value.trim()) {
                            options.push(input.value.trim());
                        }
                    }
                    
                    if (options.length < 2) {
                        alert('Please provide at least 2 options for multiple choice questions.');
                        return false;
                    }
                    
                    const correctAnswerInput = document.querySelector('input[name="correct_answer"]:checked');
                    if (!correctAnswerInput) {
                        alert('Please select the correct answer.');
                        return false;
                    }
                    break;
                    
                case 'true_false':
                    const tfCorrect = document.querySelector('input[name="correct_answer"]:checked');
                    if (!tfCorrect) {
                        alert('Please select the correct answer for the True/False question.');
                        return false;
                    }
                    break;
            }
            
            return true;
        }
        
        // Form submission handling
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            const saveButton = document.getElementById('saveButton');
            saveButton.disabled = true;
            saveButton.innerHTML = '💾 Saving...';
            
            // In a real application, form would submit normally
            // This is just for demo feedback
            setTimeout(() => {
                saveButton.disabled = false;
                saveButton.innerHTML = '💾 {{ isset($question) ? "Update Question" : "Save Question" }}';
            }, 2000);
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closePreviewModal();
            }
        });
    </script>

    <style>
        /* Additional styles for question form */
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--text-gray);
        }

        .breadcrumb-link {
            color: var(--maroon-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-link:hover {
            text-decoration: underline;
        }

        .breadcrumb-separator {
            margin: 0 8px;
            color: var(--text-gray);
        }

        .breadcrumb-current {
            color: var(--text-gray);
        }

        .question-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-label.required::after {
            content: ' *';
            color: #dc2626;
        }

        .form-textarea {
            width: 100%;
            padding: 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-family: inherit;
            font-size: 16px;
            line-height: 1.5;
            resize: vertical;
            min-height: 120px;
            transition: var(--transition);
        }

        .form-textarea:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            background: var(--white);
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--yellow-primary);
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .option-input-group {
            display: flex;
            flex-direction: column;
        }

        .option-label-text {
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .option-input {
            padding: 12px 16px;
        }

        .correct-answer-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
        }

        .correct-answer-label {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            background: var(--white);
        }

        .correct-answer-label:hover {
            border-color: var(--yellow-primary);
            background: var(--yellow-light);
        }

        .correct-answer-label:has(input:checked) {
            border-color: var(--maroon-primary);
            background: var(--yellow-light);
            font-weight: 600;
        }

        .correct-answer-input {
            margin-right: 8px;
            accent-color: var(--maroon-primary);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .form-help {
            font-size: 12px;
            color: var(--text-gray);
            margin-top: 6px;
            font-style: italic;
        }

        .invalid-feedback {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
            font-weight: 500;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid var(--border-gray);
            margin-top: 30px;
        }

        .form-actions-header {
            display: flex;
            gap: 10px;
        }

        .btn-secondary {
            padding: 12px 24px;
            background: var(--light-gray);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: var(--border-gray);
        }

        .btn-primary {
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            color: var(--white);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--yellow-primary) 0%, var(--yellow-dark) 100%);
            color: var(--maroon-primary);
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Preview Modal Styles */
        .preview-modal {
            max-width: 600px;
        }

        .preview-question {
            padding: 20px;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .preview-question-text {
            font-size: 18px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .preview-option {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            background: var(--white);
            border-radius: 6px;
            border: 1px solid var(--border-gray);
        }

        .preview-option.correct-option {
            background: var(--yellow-light);
            border-color: var(--yellow-primary);
        }

        .option-letter {
            font-weight: 600;
            margin-right: 12px;
            color: var(--maroon-primary);
        }

        .option-text {
            flex: 1;
        }

        .correct-indicator {
            color: #166534;
            font-weight: 600;
            font-size: 12px;
        }

        .preview-text-answer {
            padding: 15px;
            background: var(--light-gray);
            border-radius: 8px;
            line-height: 1.6;
        }

        .question-type-section {
            margin-bottom: 20px;
        }

        .logout-link {
            background: none;
            border: none;
            width: 100%;
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
            font-size: 14px;
            cursor: pointer;
        }

        .logout-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--yellow-primary);
        }

        @media (max-width: 768px) {
            .question-form {
                padding: 20px;
            }

            .options-grid {
                grid-template-columns: 1fr;
            }

            .correct-answer-options {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>