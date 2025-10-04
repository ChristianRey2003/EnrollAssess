@extends('layouts.admin')

@section('title', 'Sets & Questions Management')

@php
    $pageTitle = 'Sets & Questions Management';
    $pageSubtitle = 'Manage exam sets and questions for the entrance examination';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/sets-questions.css') }}" rel="stylesheet">
@endpush

@section('content')
    <!-- Main Content -->
    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">Exam Sets & Questions</h2>
            <div class="section-actions">
                @if($currentExam)
                    <button type="button" onclick="showNewSemesterDrawer()" class="btn-outline">
                        New Semester
                    </button>
                    <button onclick="showCreateSetDrawer()" class="btn-primary">
                        Add Set
                    </button>
                @else
                    <button onclick="showCreateExamDrawer()" class="btn-primary">
                        Setup First Exam
                    </button>
                @endif
            </div>
        </div>

        @if($currentExam)
            <!-- Exam Info Bar -->
            <div class="exam-info-bar">
                <div class="exam-info">
                    <h3>{{ $currentExam->title }}</h3>
                    <p>{{ $currentExam->description }}</p>
                    <div class="exam-meta">
                        <span class="meta-item">Duration: {{ $currentExam->formatted_duration }}</span>
                        <span class="meta-item">Status: {{ $currentExam->is_active ? 'Active' : 'Draft' }}</span>
                        <span class="meta-item">Created: {{ $currentExam->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
                <div class="exam-actions">
                    @if(!$currentExam->is_active)
                        <button onclick="publishExam({{ $currentExam->exam_id }})" class="btn-success">
                            Publish Exam
                        </button>
                    @endif
                    <button onclick="editExamDetails({{ $currentExam->exam_id }})" class="btn-outline">
                        Edit Details
                    </button>
                </div>
            </div>

            <!-- Split View: Sets & Questions -->
            <div class="split-view">
                <!-- Left: Sets Panel -->
                <div class="sets-panel">
                    <div class="panel-header">
                        <h4>Exam Sets</h4>
                        <button onclick="showCreateSetDrawer()" class="btn-sm btn-primary">
                            Add Set
                        </button>
                    </div>
                    
                    <div class="sets-list" id="setsList">
                        @forelse($examSets as $set)
                            <div class="set-item {{ $selectedSet && $selectedSet->exam_set_id == $set->exam_set_id ? 'selected' : '' }}" 
                                 data-set-id="{{ $set->exam_set_id }}"
                                 onclick="selectSet({{ $set->exam_set_id }})">
                                <div class="set-info">
                                    <div class="set-name">{{ $set->set_name }}</div>
                                    <div class="set-meta">
                                        <span class="questions-count">{{ $set->questions->count() }} questions</span>
                                        <span class="status-badge status-{{ $set->is_active ? 'active' : 'draft' }}">
                                            {{ $set->is_active ? 'Active' : 'Draft' }}
                                        </span>
                                    </div>
                                    @if($set->description)
                                        <div class="set-description">{{ Str::limit($set->description, 60) }}</div>
                                    @endif
                                </div>
                                <div class="set-actions" onclick="event.stopPropagation()">
                                    <button onclick="editSet({{ $set->exam_set_id }})" class="btn-xs btn-outline" title="Edit Set">
                                        Edit
                                    </button>
                                    <button onclick="duplicateSet({{ $set->exam_set_id }})" class="btn-xs btn-secondary" title="Duplicate Set">
                                        Copy
                                    </button>
                                    <button onclick="deleteSet({{ $set->exam_set_id }})" class="btn-xs btn-danger" title="Delete Set">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <h4>No Sets Created</h4>
                                <p>Create your first exam set to get started.</p>
                                <button onclick="showCreateSetDrawer()" class="btn-primary">
                                    Create First Set
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Right: Questions Panel -->
                <div class="questions-panel">
                    <div class="panel-header">
                        <h4>
                            @if($selectedSet)
                                Questions for {{ $selectedSet->set_name }}
                            @else
                                Select a Set
                            @endif
                        </h4>
                        @if($selectedSet)
                            <div class="panel-actions">
                                <button onclick="showCreateQuestionDrawer({{ $selectedSet->exam_set_id }})" class="btn-sm btn-primary">
                                    Add Question
                                </button>
                                <button onclick="reorderQuestions({{ $selectedSet->exam_set_id }})" class="btn-sm btn-outline">
                                    Reorder
                                </button>
                                <button onclick="shuffleQuestions({{ $selectedSet->exam_set_id }})" class="btn-sm btn-secondary">
                                    Shuffle
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="questions-list" id="questionsList">
                        @if($selectedSet)
                            @forelse($selectedSet->questions->sortBy('order_number') as $question)
                                <div class="question-item" data-question-id="{{ $question->question_id }}">
                                    <div class="question-header">
                                        <div class="question-meta">
                                            <span class="question-number">#{{ $loop->iteration }}</span>
                                            <span class="question-type type-{{ str_replace('_', '-', $question->question_type) }}">
                                                {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                                            </span>
                                            <span class="question-points">{{ $question->points }} pts</span>
                                            <span class="status-badge status-{{ $question->is_active ? 'active' : 'draft' }}">
                                                {{ $question->is_active ? 'Active' : 'Draft' }}
                                            </span>
                                        </div>
                                        <div class="question-actions">
                                            <button onclick="editQuestion({{ $question->question_id }})" class="btn-xs btn-primary">
                                                Edit
                                            </button>
                                            <button onclick="duplicateQuestion({{ $question->question_id }})" class="btn-xs btn-secondary">
                                                Copy
                                            </button>
                                            <button onclick="toggleQuestionStatus({{ $question->question_id }})" class="btn-xs btn-outline">
                                                {{ $question->is_active ? 'Draft' : 'Activate' }}
                                            </button>
                                            <button onclick="deleteQuestion({{ $question->question_id }})" class="btn-xs btn-danger">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                    <div class="question-content">
                                        <div class="question-text">{{ $question->question_text }}</div>
                                        @if($question->question_type == 'multiple_choice' && $question->options->count() > 0)
                                            <div class="question-options">
                                                @foreach($question->options->sortBy('order_number') as $option)
                                                    <div class="option-item {{ $option->is_correct ? 'correct-option' : '' }}">
                                                        <span class="option-label">{{ chr(65 + $loop->index) }}.</span>
                                                        <span class="option-text">{{ $option->option_text }}</span>
                                                        @if($option->is_correct)
                                                            <span class="correct-indicator">✓</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <h4>No Questions Yet</h4>
                                    <p>Add questions to {{ $selectedSet->set_name }} to get started.</p>
                                    <button onclick="showCreateQuestionDrawer({{ $selectedSet->exam_set_id }})" class="btn-primary">
                                        Add First Question
                                    </button>
                                </div>
                            @endforelse
                        @else
                            <div class="empty-state">
                                <h4>Select a Set</h4>
                                <p>Choose an exam set from the left panel to view and manage its questions.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- No Exam State -->
            <div class="no-exam-state">
                <h3>No Exam Setup</h3>
                <p>Create your first entrance exam to start managing sets and questions.</p>
                <button onclick="showCreateExamDrawer()" class="btn-primary">
                    Setup Entrance Exam
                </button>
            </div>
        @endif
    </div>
@endsection

@push('modals')
    <!-- Create Exam Drawer -->
    <div id="createExamDrawer" class="drawer-overlay" style="display: none;">
        <div class="drawer-content">
            <div class="drawer-header">
                <h3>Setup Entrance Exam</h3>
                <button onclick="closeCreateExamDrawer()" class="drawer-close">×</button>
            </div>
            <div class="drawer-body">
                <form id="createExamForm">
                    <div class="form-group">
                        <label class="form-label">Exam Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., BSIT Entrance Exam 2024" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the examination"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="90" min="30" max="300" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-control">
                                <option value="0">Draft</option>
                                <option value="1">Active</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="drawer-footer">
                <button onclick="closeCreateExamDrawer()" class="btn-secondary">Cancel</button>
                <button onclick="submitCreateExam()" class="btn-primary">Create Exam</button>
            </div>
        </div>
    </div>

    <!-- Edit Exam Drawer -->
    <div id="editExamDrawer" class="drawer-overlay" style="display: none;">
        <div class="drawer-content">
            <div class="drawer-header">
                <h3>Edit Exam Details</h3>
                <button onclick="closeEditExamDrawer()" class="drawer-close">×</button>
            </div>
            <div class="drawer-body">
                <form id="editExamForm">
                    <div class="form-group">
                        <label class="form-label">Exam Title</label>
                        <input type="text" name="title" id="editExamTitle" class="form-control" placeholder="e.g., BSIT Entrance Exam 2024" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editExamDescription" class="form-control" rows="3" placeholder="Brief description of the examination"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" id="editExamDuration" class="form-control" min="30" max="300" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="editExamStatus" class="form-control">
                                <option value="0">Draft</option>
                                <option value="1">Active</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="drawer-footer">
                <button onclick="closeEditExamDrawer()" class="btn-secondary">Cancel</button>
                <button onclick="submitEditExam()" class="btn-primary">Update Exam</button>
            </div>
        </div>
    </div>

    <!-- New Semester Modal -->
    <div id="newSemesterModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Setup New Semester</h3>
                <button onclick="closeNewSemesterModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <div class="semester-options">
                    <div class="option-card" onclick="selectSemesterOption('duplicate')">
                        <h4>Duplicate from Previous</h4>
                        <p>Copy all sets and questions from the current exam as drafts</p>
                        <div class="option-details">
                            <span>✓ All questions copied</span>
                            <span>✓ Set to draft status</span>
                            <span>✓ Ready for review</span>
                        </div>
                    </div>
                    <div class="option-card" onclick="selectSemesterOption('fresh')">
                        <h4>Start Fresh</h4>
                        <p>Create a new exam with empty sets</p>
                        <div class="option-details">
                            <span>✓ Clean slate</span>
                            <span>✓ Custom setup</span>
                            <span>✓ Full control</span>
                        </div>
                    </div>
                </div>
                
                <form id="newSemesterForm" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">New Exam Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., BSIT Entrance Exam 2025" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <input type="hidden" name="semester_option" id="semesterOption">
                </form>
            </div>
            <div class="modal-footer" id="semesterModalFooter">
                <button onclick="closeNewSemesterModal()" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Consistency Check Modal -->
    <div id="consistencyModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Consistency Check Results</h3>
                <button onclick="closeConsistencyModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body" id="consistencyResults">
                <!-- Results will be populated here -->
            </div>
            <div class="modal-footer">
                <button onclick="closeConsistencyModal()" class="btn-secondary">Close</button>
                <button onclick="fixConsistencyIssues()" class="btn-primary">Fix Issues</button>
            </div>
        </div>
    </div>
@endpush

<!-- Right-side Drawers -->
<div id="createSetDrawer" class="drawer-overlay" style="display: none;">
    <div class="drawer-content">
        <div class="drawer-header">
            <h3>Create New Set</h3>
            <button onclick="closeCreateSetDrawer()" class="drawer-close">×</button>
        </div>
        <div class="drawer-body">
            <form id="createSetForm">
                <div class="form-group">
                    <label class="form-label">Set Name</label>
                    <input type="text" name="set_name" class="form-control" placeholder="e.g., Set A" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="0">Draft</option>
                        <option value="1">Active</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="drawer-footer">
            <button onclick="closeCreateSetDrawer()" class="btn-secondary">Cancel</button>
            <button onclick="submitCreateSet()" class="btn-primary">Create Set</button>
        </div>
    </div>
</div>

<div id="createQuestionDrawer" class="drawer-overlay" style="display: none;">
    <div class="drawer-content">
        <div class="drawer-header">
            <h3>Add Question</h3>
            <button onclick="closeCreateQuestionDrawer()" class="drawer-close">×</button>
        </div>
        <div class="drawer-body">
            <form id="createQuestionForm">
                <input type="hidden" name="exam_set_id" id="questionSetId">
                
                <div class="form-group">
                    <label class="form-label">Question Type</label>
                    <select name="question_type" class="form-control" onchange="toggleQuestionOptions()" required>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                        <option value="short_answer">Short Answer</option>
                        <option value="essay">Essay</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Question Text</label>
                    <textarea name="question_text" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Points</label>
                        <input type="number" name="points" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-control">
                            <option value="0">Draft</option>
                            <option value="1">Active</option>
                        </select>
                    </div>
                </div>
                
                <!-- Multiple Choice Options -->
                <div id="mcOptions" class="question-options-section">
                    <label class="form-label">Answer Options</label>
                    <div class="options-list">
                        <div class="option-input">
                            <input type="text" name="options[]" class="form-control" placeholder="Option A" required>
                            <label class="correct-checkbox">
                                <input type="radio" name="correct_option" value="0" required>
                                <span>Correct</span>
                            </label>
                        </div>
                        <div class="option-input">
                            <input type="text" name="options[]" class="form-control" placeholder="Option B" required>
                            <label class="correct-checkbox">
                                <input type="radio" name="correct_option" value="1" required>
                                <span>Correct</span>
                            </label>
                        </div>
                        <div class="option-input">
                            <input type="text" name="options[]" class="form-control" placeholder="Option C" required>
                            <label class="correct-checkbox">
                                <input type="radio" name="correct_option" value="2" required>
                                <span>Correct</span>
                            </label>
                        </div>
                        <div class="option-input">
                            <input type="text" name="options[]" class="form-control" placeholder="Option D" required>
                            <label class="correct-checkbox">
                                <input type="radio" name="correct_option" value="3" required>
                                <span>Correct</span>
                            </label>
                        </div>
                    </div>
                    <button type="button" onclick="addOption()" class="btn-sm btn-outline">Add Option</button>
                </div>
                
                <!-- True/False Options -->
                <div id="tfOptions" class="question-options-section" style="display: none;">
                    <label class="form-label">Correct Answer</label>
                    <div class="tf-options">
                        <label class="tf-option">
                            <input type="radio" name="tf_answer" value="true" required>
                            <span>True</span>
                        </label>
                        <label class="tf-option">
                            <input type="radio" name="tf_answer" value="false" required>
                            <span>False</span>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <div class="drawer-footer">
            <button onclick="closeCreateQuestionDrawer()" class="btn-secondary">Cancel</button>
            <button onclick="submitCreateQuestion()" class="btn-primary">Add Question</button>
        </div>
    </div>
</div>

<div id="newSemesterDrawer" class="drawer-overlay" style="display: none;">
    <div class="drawer-content">
        <div class="drawer-header">
            <h3>Setup New Semester</h3>
            <button onclick="closeNewSemesterDrawer()" class="drawer-close">×</button>
        </div>
        <div class="drawer-body">
            <!-- Step 1: Choose Option -->
            <div id="semesterOptionsStep">
                <div class="semester-options">
                    <div class="option-card" onclick="selectSemesterOption('duplicate')">
                        <h4>Duplicate from Previous</h4>
                        <p>Copy all sets and questions from the current exam as drafts</p>
                        <div class="option-details">
                            <span>✓ All questions copied</span>
                            <span>✓ Set to draft status</span>
                            <span>✓ Ready for review</span>
                        </div>
                    </div>
                    <div class="option-card" onclick="selectSemesterOption('fresh')">
                        <h4>Start Fresh</h4>
                        <p>Create a new exam with empty sets</p>
                        <div class="option-details">
                            <span>✓ Clean slate</span>
                            <span>✓ Custom setup</span>
                            <span>✓ Full control</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 2: Form Details -->
            <div id="semesterFormStep" style="display: none;">
                <form id="newSemesterForm">
                    <div class="form-group">
                        <label class="form-label">New Exam Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., BSIT Entrance Exam 2025" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the examination"></textarea>
                    </div>
                    <input type="hidden" name="semester_option" id="semesterOption">
                </form>
            </div>
        </div>
        <div class="drawer-footer" id="semesterDrawerFooter">
            <button onclick="closeNewSemesterDrawer()" class="btn-secondary">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        let selectedSetId = {{ $selectedSet->exam_set_id ?? 'null' }};
        let isReorderMode = false;
        
        // Set selection
        function selectSet(setId) {
            window.location.href = `{{ route('admin.sets-questions.index') }}?set=${setId}`;
        }
        
        // Modal functions
        function showCreateExamDrawer() {
            document.getElementById('createExamDrawer').style.display = 'flex';
        }
        
        function closeCreateExamDrawer() {
            document.getElementById('createExamDrawer').style.display = 'none';
        }
        
        function showEditExamDrawer() {
            // Pre-fill form with current exam data
            @if($currentExam)
                document.getElementById('editExamTitle').value = '{{ $currentExam->title }}';
                document.getElementById('editExamDescription').value = '{{ $currentExam->description ?? '' }}';
                document.getElementById('editExamDuration').value = '{{ $currentExam->duration_minutes }}';
                document.getElementById('editExamStatus').value = '{{ $currentExam->is_active ? 1 : 0 }}';
            @endif
            
            document.getElementById('editExamDrawer').style.display = 'flex';
        }
        
        function closeEditExamDrawer() {
            document.getElementById('editExamDrawer').style.display = 'none';
        }
        
        function showNewSemesterDrawer() {
            document.getElementById('newSemesterDrawer').style.display = 'flex';
            // Reset to step 1
            document.getElementById('semesterOptionsStep').style.display = 'block';
            document.getElementById('semesterFormStep').style.display = 'none';
            document.getElementById('semesterDrawerFooter').innerHTML = `
                <button onclick="closeNewSemesterDrawer()" class="btn-secondary">Cancel</button>
            `;
        }
        
        function closeNewSemesterDrawer() {
            document.getElementById('newSemesterDrawer').style.display = 'none';
            // Reset form
            document.getElementById('newSemesterForm').reset();
            document.getElementById('semesterOption').value = '';
        }
        
        // Drawer functions
        function showCreateSetDrawer() {
            document.getElementById('createSetDrawer').style.display = 'flex';
        }
        
        function closeCreateSetDrawer() {
            document.getElementById('createSetDrawer').style.display = 'none';
        }
        
        function showCreateQuestionDrawer(setId) {
            document.getElementById('questionSetId').value = setId;
            document.getElementById('createQuestionDrawer').style.display = 'flex';
        }
        
        function closeCreateQuestionDrawer() {
            document.getElementById('createQuestionDrawer').style.display = 'none';
        }
        
        // Question type toggle
        function toggleQuestionOptions() {
            const questionType = document.querySelector('select[name="question_type"]').value;
            const mcOptions = document.getElementById('mcOptions');
            const tfOptions = document.getElementById('tfOptions');
            
            mcOptions.style.display = questionType === 'multiple_choice' ? 'block' : 'none';
            tfOptions.style.display = questionType === 'true_false' ? 'block' : 'none';
        }
        
        // Add option for multiple choice
        function addOption() {
            const optionsList = document.querySelector('.options-list');
            const optionCount = optionsList.children.length;
            const newOption = document.createElement('div');
            newOption.className = 'option-input';
            newOption.innerHTML = `
                <input type="text" name="options[]" class="form-control" placeholder="Option ${String.fromCharCode(65 + optionCount)}" required>
                <label class="correct-checkbox">
                    <input type="radio" name="correct_option" value="${optionCount}" required>
                    <span>Correct</span>
                </label>
                <button type="button" onclick="removeOption(this)" class="btn-xs btn-danger">Remove</button>
            `;
            optionsList.appendChild(newOption);
        }
        
        function removeOption(button) {
            button.parentElement.remove();
        }
        
        // Semester setup
        function selectSemesterOption(option) {
            document.getElementById('semesterOption').value = option;
            
            // Show step 2 (form)
            document.getElementById('semesterOptionsStep').style.display = 'none';
            document.getElementById('semesterFormStep').style.display = 'block';
            
            // Update footer with Create button
            document.getElementById('semesterDrawerFooter').innerHTML = `
                <button onclick="goBackToSemesterOptions()" class="btn-secondary">Back</button>
                <button onclick="submitNewSemester()" class="btn-primary">Create New Semester</button>
            `;
            
            // Pre-fill title based on current exam
            const currentTitle = '{{ $currentExam->title ?? "" }}';
            if (currentTitle) {
                const newTitle = currentTitle.replace(/\d{4}/, new Date().getFullYear() + 1);
                document.querySelector('#newSemesterForm input[name="title"]').value = newTitle;
            }
        }
        
        function goBackToSemesterOptions() {
            document.getElementById('semesterFormStep').style.display = 'none';
            document.getElementById('semesterOptionsStep').style.display = 'block';
            document.getElementById('semesterDrawerFooter').innerHTML = `
                <button onclick="closeNewSemesterDrawer()" class="btn-secondary">Cancel</button>
            `;
        }
        
        // CRUD operations
        function submitCreateExam() {
            const form = document.getElementById('createExamForm');
            const formData = new FormData(form);
            
            fetch('/admin/exams', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCreateExamDrawer();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred while creating the exam');
                console.error('Error:', error);
            });
        }
        
        function submitEditExam() {
            @if($currentExam)
                const form = document.getElementById('editExamForm');
                const formData = new FormData(form);
                
                fetch('/admin/exams/{{ $currentExam->exam_id }}', {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title: form.title.value,
                        description: form.description.value,
                        duration_minutes: parseInt(form.duration_minutes.value),
                        is_active: parseInt(form.is_active.value)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeEditExamDrawer();
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred while updating the exam');
                    console.error('Error:', error);
                });
            @endif
        }
        
        function submitCreateSet() {
            const form = document.getElementById('createSetForm');
            const formData = new FormData(form);
            formData.append('exam_id', {{ $currentExam->exam_id ?? 'null' }});
            
            fetch('/admin/exam-sets', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCreateSetDrawer();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred while creating the set');
                console.error('Error:', error);
            });
        }
        
        function submitCreateQuestion() {
            const form = document.getElementById('createQuestionForm');
            const formData = new FormData(form);
            
            fetch('/admin/questions', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCreateQuestionDrawer();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred while creating the question');
                console.error('Error:', error);
            });
        }
        
        function submitNewSemester() {
            const form = document.getElementById('newSemesterForm');
            const formData = new FormData(form);
            
            fetch('/admin/sets-questions/new-semester', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeNewSemesterDrawer();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred while creating the new semester');
                console.error('Error:', error);
            });
        }
        
        // Drag and Drop / Reorder functionality
        function reorderQuestions(setId) {
            if (!isReorderMode) {
                enableReorderMode();
            } else {
                disableReorderMode();
            }
        }
        
        function enableReorderMode() {
            isReorderMode = true;
            const questionsList = document.getElementById('questionsList');
            const reorderButton = document.querySelector('[onclick*="reorderQuestions"]');
            
            if (reorderButton) {
                reorderButton.textContent = 'Save Order';
                reorderButton.classList.remove('btn-outline');
                reorderButton.classList.add('btn-success');
            }
            
            // Add visual indicators
            questionsList.classList.add('reorder-mode');
            
            // Initialize Sortable
            if (questionsList && !questionsList.sortable) {
                questionsList.sortable = Sortable.create(questionsList, {
                    animation: 150,
                    ghostClass: 'question-ghost',
                    chosenClass: 'question-chosen',
                    dragClass: 'question-drag',
                    handle: '.question-item',
                    onEnd: function(evt) {
                        // Update visual order numbers
                        updateQuestionNumbers();
                    }
                });
            }
        }
        
        function disableReorderMode() {
            if (!selectedSetId) return;
            
            const questionsList = document.getElementById('questionsList');
            const questions = questionsList.querySelectorAll('.question-item');
            const questionOrder = [];
            
            questions.forEach((item, index) => {
                const questionId = item.getAttribute('data-question-id');
                if (questionId) {
                    questionOrder.push({
                        id: parseInt(questionId),
                        order: index + 1
                    });
                }
            });
            
            // Save the new order
            fetch(`/admin/exam-sets/${selectedSetId}/reorder-questions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    questions: questionOrder
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    isReorderMode = false;
                    questionsList.classList.remove('reorder-mode');
                    
                    const reorderButton = document.querySelector('[onclick*="reorderQuestions"]');
                    if (reorderButton) {
                        reorderButton.textContent = 'Reorder';
                        reorderButton.classList.remove('btn-success');
                        reorderButton.classList.add('btn-outline');
                    }
                    
                    // Destroy sortable
                    if (questionsList.sortable) {
                        questionsList.sortable.destroy();
                        questionsList.sortable = null;
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred while saving the order');
                console.error('Error:', error);
            });
        }
        
        function updateQuestionNumbers() {
            const questions = document.querySelectorAll('.question-item');
            questions.forEach((item, index) => {
                const numberSpan = item.querySelector('.question-number');
                if (numberSpan) {
                    numberSpan.textContent = `#${index + 1}`;
                }
            });
        }

        // Action functions
        function publishExam(examId) {
            if (confirm('Are you sure you want to publish this exam? This will make it available for applicants.')) {
                fetch(`/admin/sets-questions/${examId}/publish`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        function toggleQuestionStatus(questionId) {
            fetch(`/admin/questions/${questionId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
        
        function deleteSet(setId) {
            if (confirm('Are you sure you want to delete this set and all its questions?')) {
                fetch(`/admin/exam-sets/${setId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        function deleteQuestion(questionId) {
            if (confirm('Are you sure you want to delete this question?')) {
                fetch(`/admin/questions/${questionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        function editSet(setId) {
            // TODO: Implement edit set drawer
            alert('Edit set functionality - to be implemented');
        }
        
        function editQuestion(questionId) {
            // TODO: Implement edit question drawer
            alert('Edit question functionality - to be implemented');
        }
        
        function editExamDetails(examId) {
            showEditExamDrawer();
        }
        
        function duplicateQuestion(questionId) {
            if (confirm('Are you sure you want to duplicate this question?')) {
                fetch(`/admin/questions/${questionId}/duplicate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred while duplicating the question');
                    console.error('Error:', error);
                });
            }
        }
        
        function duplicateSet(setId) {
            if (confirm('Are you sure you want to duplicate this set with all its questions?')) {
                fetch(`/admin/exam-sets/${setId}/duplicate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred while duplicating the set');
                    console.error('Error:', error);
                });
            }
        }
        
        function shuffleQuestions(setId) {
            if (confirm('Are you sure you want to shuffle the questions in this set? This will randomize their order.')) {
                fetch(`/admin/exam-sets/${setId}/shuffle-questions`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Store the previous order for potential undo
                        if (data.previous_order) {
                            sessionStorage.setItem('previousQuestionOrder', JSON.stringify(data.previous_order));
                            showUndoShuffleButton(setId);
                        }
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred while shuffling questions');
                    console.error('Error:', error);
                });
            }
        }
        
        function undoShuffle(setId) {
            const previousOrder = sessionStorage.getItem('previousQuestionOrder');
            if (!previousOrder) {
                alert('No previous order available to restore.');
                return;
            }
            
            if (confirm('Are you sure you want to restore the previous question order?')) {
                fetch(`/admin/exam-sets/${setId}/reorder-questions`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: previousOrder
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        sessionStorage.removeItem('previousQuestionOrder');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred while restoring question order');
                    console.error('Error:', error);
                });
            }
        }
        
        function showUndoShuffleButton(setId) {
            // Add undo button temporarily
            const panelActions = document.querySelector('.panel-actions');
            if (panelActions && !document.getElementById('undoShuffleBtn')) {
                const undoBtn = document.createElement('button');
                undoBtn.id = 'undoShuffleBtn';
                undoBtn.className = 'btn-sm btn-warning';
                undoBtn.textContent = 'Undo Shuffle';
                undoBtn.onclick = () => undoShuffle(setId);
                panelActions.appendChild(undoBtn);
                
                // Remove button after 30 seconds
                setTimeout(() => {
                    const btn = document.getElementById('undoShuffleBtn');
                    if (btn) btn.remove();
                }, 30000);
            }
        }
        
        // Close drawers/modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay') || e.target.classList.contains('drawer-overlay')) {
                closeCreateExamDrawer();
                closeEditExamDrawer();
                closeNewSemesterDrawer();
                closeCreateSetDrawer();
                closeCreateQuestionDrawer();
            }
        });
    </script>
@endpush
