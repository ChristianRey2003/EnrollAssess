@extends('layouts.admin')

@section('title', 'Exam & Questions Management')

@php
    $pageTitle = 'Exam & Questions Management';
    $pageSubtitle = 'Manage examinations and their question banks in one unified interface';
@endphp

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_exams'] ?? 0 }}</div>
            <div class="stat-label">Total Exams</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['active_exams'] ?? 0 }}</div>
            <div class="stat-label">Active Exams</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_questions'] ?? 0 }}</div>
            <div class="stat-label">Total Questions</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_exam_sets'] ?? 0 }}</div>
            <div class="stat-label">Exam Sets</div>
        </div>
    </div>

    <!-- Search and Filter Controls -->
    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">Exam & Questions Overview</h2>
            <div class="section-actions">
                <a href="{{ route('admin.exams.create') }}" class="btn-primary">
                    Create New Exam
                </a>
                <a href="{{ route('admin.questions.create') }}" class="btn-outline">
                    Add Question
                </a>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="search-controls">
            <form method="GET" action="{{ route('admin.sets-questions.index') }}" class="search-form">
                <div class="search-input-group">
                    <input type="text" name="search" placeholder="Search exams and questions..."
                           value="{{ request('search') }}" class="search-input">
                    <button type="submit" class="search-btn">Search</button>
                </div>

                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <select name="view_mode" class="filter-select">
                        <option value="hierarchical" {{ request('view_mode', 'hierarchical') == 'hierarchical' ? 'selected' : '' }}>Hierarchical View</option>
                        <option value="exams_only" {{ request('view_mode') == 'exams_only' ? 'selected' : '' }}>Exams Only</option>
                        <option value="questions_only" {{ request('view_mode') == 'questions_only' ? 'selected' : '' }}>Questions Only</option>
                    </select>

                    <button type="submit" class="btn-outline">Apply Filters</button>
                    @if(request()->hasAny(['search', 'status', 'view_mode']))
                        <a href="{{ route('admin.sets-questions.index') }}" class="btn-clear">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Content Display -->
        <div class="exam-questions-content">
            @if(request('view_mode') == 'questions_only')
                <!-- Questions Only View -->
                <div class="questions-only-section">
                    <div class="section-subheader">
                        <h3>Questions Library</h3>
                        <a href="{{ route('admin.questions.create') }}" class="btn-secondary">
                            Add New Question
                        </a>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Question ID</th>
                                    <th>Question Text</th>
                                    <th>Type</th>
                                    <th>Points</th>
                                    <th>Exam Set</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questions ?? [] as $question)
                                <tr>
                                    <td>#{{ $question->question_id }}</td>
                                    <td class="question-text">{{ Str::limit($question->question_text, 80) }}</td>
                                    <td>
                                        <span class="type-badge type-{{ str_replace('_', '-', $question->question_type) }}">
                                            {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="points-badge">{{ $question->points }} pts</span>
                                    </td>
                                    <td>
                                        <span class="exam-set-badge">
                                            {{ $question->examSet->exam->title ?? 'Unknown' }} - {{ $question->examSet->set_name ?? 'Set' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $question->is_active ? 'active' : 'inactive' }}">
                                            {{ $question->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.questions.edit', $question->question_id) }}" class="btn-sm btn-primary">Edit</a>
                                            <button onclick="toggleQuestionStatus({{ $question->question_id }})" class="btn-sm btn-secondary">
                                                {{ $question->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                            <button onclick="deleteQuestion({{ $question->question_id }})" class="btn-sm btn-danger">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <h3>No Questions Found</h3>
                                        <p>No questions match your current search criteria.</p>
                                        <a href="{{ route('admin.questions.create') }}" class="btn-primary">
                                            Create First Question
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @elseif(request('view_mode') == 'exams_only')
                <!-- Exams Only View -->
                <div class="exams-only-section">
                    <div class="section-subheader">
                        <h3>Examinations</h3>
                        <a href="{{ route('admin.exams.create') }}" class="btn-secondary">
                            Create New Exam
                        </a>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Exam ID</th>
                                    <th>Exam Title</th>
                                    <th>Duration</th>
                                    <th>Sets</th>
                                    <th>Questions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exams ?? [] as $exam)
                                <tr>
                                    <td>#{{ $exam->exam_id }}</td>
                                    <td>
                                        <div class="exam-title-info">
                                            <div class="exam-name">{{ $exam->title }}</div>
                                            @if($exam->description)
                                                <div class="exam-description">{{ Str::limit($exam->description, 60) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="duration-badge">{{ $exam->formatted_duration }}</span>
                                    </td>
                                    <td>
                                        <span class="sets-count">{{ $exam->examSets->count() }} sets</span>
                                    </td>
                                    <td>
                                        @php
                                            $totalQuestions = $exam->examSets->sum(function($set) {
                                                return $set->questions->count();
                                            });
                                        @endphp
                                        <span class="questions-count">{{ $totalQuestions }} questions</span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $exam->is_active ? 'active' : 'inactive' }}">
                                            {{ $exam->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.exams.show', $exam->exam_id) }}" class="btn-sm btn-info">View</a>
                                            <a href="{{ route('admin.exams.edit', $exam->exam_id) }}" class="btn-sm btn-primary">Edit</a>
                                            <button onclick="toggleExamStatus({{ $exam->exam_id }})" class="btn-sm btn-secondary">
                                                {{ $exam->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                            <button onclick="deleteExam({{ $exam->exam_id }})" class="btn-sm btn-danger">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <h3>No Exams Found</h3>
                                        <p>No exams match your current search criteria.</p>
                                        <a href="{{ route('admin.exams.create') }}" class="btn-primary">
                                            Create First Exam
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                <!-- Hierarchical View (Default) -->
                <div class="hierarchical-section">
                    <div class="section-subheader">
                        <h3>Hierarchical View: Exams → Sets → Questions</h3>
                        <div class="view-controls">
                            <button onclick="expandAll()" class="btn-sm btn-outline">Expand All</button>
                            <button onclick="collapseAll()" class="btn-sm btn-outline">Collapse All</button>
                        </div>
                    </div>
                    
                    <div class="hierarchical-container">
                        @forelse($exams ?? [] as $exam)
                            <div class="exam-item" data-exam-id="{{ $exam->exam_id }}">
                                <div class="exam-header" onclick="toggleExamExpansion({{ $exam->exam_id }})">
                                    <div class="expand-icon" id="expand-{{ $exam->exam_id }}">▼</div>
                                    <div class="exam-info">
                                        <div class="exam-title-row">
                                            <h4 class="exam-title">#{{ $exam->exam_id }} - {{ $exam->title }}</h4>
                                            <div class="exam-meta">
                                                <span class="duration-info">{{ $exam->formatted_duration }}</span>
                                                <span class="status-badge status-{{ $exam->is_active ? 'active' : 'inactive' }}">
                                                    {{ $exam->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                        @if($exam->description)
                                            <p class="exam-description">{{ $exam->description }}</p>
                                        @endif
                                        <div class="exam-stats">
                                            <span class="stat-item">{{ $exam->examSets->count() }} Sets</span>
                                            <span class="stat-item">
                                                @php
                                                    $totalQuestions = $exam->examSets->sum(function($set) {
                                                        return $set->questions->count();
                                                    });
                                                @endphp
                                                {{ $totalQuestions }} Questions
                                            </span>
                                        </div>
                                    </div>
                                    <div class="exam-actions">
                                        <a href="{{ route('admin.exams.edit', $exam->exam_id) }}" onclick="event.stopPropagation()" class="btn-sm btn-primary">Edit</a>
                                        <a href="{{ route('admin.exam-sets.create', $exam->exam_id) }}" onclick="event.stopPropagation()" class="btn-sm btn-secondary">Add Set</a>
                                        <button onclick="event.stopPropagation(); toggleExamStatus({{ $exam->exam_id }})" class="btn-sm btn-outline">
                                            {{ $exam->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </div>
                                </div>

                                <div class="exam-sets" id="sets-{{ $exam->exam_id }}" style="display: none;">
                                    @forelse($exam->examSets as $examSet)
                                        <div class="exam-set-item" data-set-id="{{ $examSet->exam_set_id }}">
                                            <div class="set-header" onclick="toggleSetExpansion({{ $examSet->exam_set_id }})">
                                                <div class="expand-icon" id="expand-set-{{ $examSet->exam_set_id }}">▼</div>
                                                <div class="set-info">
                                                    <div class="set-title-row">
                                                        <h5 class="set-title">{{ $examSet->set_name }}</h5>
                                                        <div class="set-meta">
                                                            <span class="questions-count">{{ $examSet->questions->count() }} Questions</span>
                                                            <span class="status-badge status-{{ $examSet->is_active ? 'active' : 'inactive' }}">
                                                                {{ $examSet->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @if($examSet->description)
                                                        <p class="set-description">{{ $examSet->description }}</p>
                                                    @endif
                                                </div>
                                                <div class="set-actions">
                                                    <a href="{{ route('admin.exam-sets.edit', [$exam->exam_id, $examSet->exam_set_id]) }}" onclick="event.stopPropagation()" class="btn-sm btn-primary">Edit</a>
                                                    <a href="{{ route('admin.questions.create') }}?exam_set_id={{ $examSet->exam_set_id }}" onclick="event.stopPropagation()" class="btn-sm btn-secondary">Add Question</a>
                                                </div>
                                            </div>

                                            <div class="set-questions" id="questions-{{ $examSet->exam_set_id }}" style="display: none;">
                                                @forelse($examSet->questions as $question)
                                                    <div class="question-item">
                                                        <div class="question-content">
                                                            <div class="question-header">
                                                                <span class="question-id">#{{ $question->question_id }}</span>
                                                                <span class="type-badge type-{{ str_replace('_', '-', $question->question_type) }}">
                                                                    {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                                                                </span>
                                                                <span class="points-badge">{{ $question->points }} pts</span>
                                                                <span class="status-badge status-{{ $question->is_active ? 'active' : 'inactive' }}">
                                                                    {{ $question->is_active ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </div>
                                                            <div class="question-text">{{ Str::limit($question->question_text, 120) }}</div>
                                                            @if($question->question_type == 'multiple_choice' && $question->options->count() > 0)
                                                                <div class="question-options">
                                                                    @foreach($question->options->take(4) as $option)
                                                                        <div class="option-item {{ $option->is_correct ? 'correct-option' : '' }}">
                                                                            {{ $option->option_text }}
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="question-actions">
                                                            <a href="{{ route('admin.questions.edit', $question->question_id) }}" class="btn-sm btn-primary">Edit</a>
                                                            <button onclick="toggleQuestionStatus({{ $question->question_id }})" class="btn-sm btn-secondary">
                                                                {{ $question->is_active ? 'Disable' : 'Enable' }}
                                                            </button>
                                                            <button onclick="deleteQuestion({{ $question->question_id }})" class="btn-sm btn-danger">Delete</button>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="no-questions">
                                                        <p>No questions in this set yet.</p>
                                                        <a href="{{ route('admin.questions.create') }}?exam_set_id={{ $examSet->exam_set_id }}" class="btn-sm btn-secondary">
                                                            Add First Question
                                                        </a>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    @empty
                                        <div class="no-sets">
                                            <p>No exam sets created yet.</p>
                                            <a href="{{ route('admin.exam-sets.create', $exam->exam_id) }}" class="btn-sm btn-secondary">
                                                Create First Set
                                            </a>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <h3>No Exams Found</h3>
                                <p>No exams have been created yet. Click the "Create New Exam" button to get started.</p>
                                <a href="{{ route('admin.exams.create') }}" class="btn-primary">
                                    Create Your First Exam
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        <!-- Pagination -->
        @if(isset($exams) && $exams->hasPages())
            <div class="pagination-container">
                {{ $exams->appends(request()->query())->links() }}
            </div>
        @elseif(isset($questions) && $questions->hasPages())
            <div class="pagination-container">
                {{ $questions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
/* Exam & Questions Combined Management - Minimalist Theme */
:root {
    --maroon-primary: #800020;
    --maroon-dark: #5e0018;
    --white: #FFFFFF;
    --black: #111827;
    --border-gray: #E5E7EB;
    --light-gray: #F9FAFB;
    --text-dark: #1F2937;
    --text-gray: #6B7280;
    --gray-100: #F3F4F6;
    --gray-200: #E5E7EB;
    --gray-300: #D1D5DB;
}

/* Search Controls */
.search-controls { margin-bottom: 25px; }
.search-form { display: flex; flex-direction: column; gap: 15px; }
.search-input-group { display: flex; flex: 1; max-width: 400px; }
.search-input { flex: 1; padding: 12px 15px; border: 2px solid var(--border-gray); border-radius: 8px 0 0 8px; font-size: 14px; }
.search-btn { padding: 12px 20px; background: var(--maroon-primary); color: var(--white); border: none; border-radius: 0 8px 8px 0; cursor: pointer; }
.filter-group { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
.filter-select { padding: 10px 15px; border: 2px solid var(--border-gray); border-radius: 8px; font-size: 14px; min-width: 150px; }

/* View Mode Specific Styles */
.section-subheader { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid var(--border-gray); }
.section-subheader h3 { margin: 0; color: var(--text-dark); font-size: 18px; font-weight: 600; }
.view-controls { display: flex; gap: 10px; }

/* Hierarchical View Styles */
.hierarchical-container { display: flex; flex-direction: column; gap: 20px; }
.exam-item { background: var(--white); border: 2px solid var(--border-gray); border-radius: 12px; overflow: hidden; }
.exam-header { display: flex; align-items: center; gap: 15px; padding: 20px; cursor: pointer; background: var(--light-gray); border-bottom: 1px solid var(--border-gray); transition: all 0.3s ease; }
.exam-header:hover { background: var(--gray-100); }
.expand-icon { font-size: 16px; font-weight: bold; color: var(--maroon-primary); min-width: 20px; text-align: center; }
.exam-info { flex: 1; }
.exam-title-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.exam-title { margin: 0; color: var(--text-dark); font-size: 16px; font-weight: 600; }
.exam-meta { display: flex; gap: 10px; align-items: center; }
.duration-info { background: var(--gray-100); color: var(--text-gray); padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500; }
.exam-description { color: var(--text-gray); font-size: 14px; margin-bottom: 8px; line-height: 1.4; }
.exam-stats { display: flex; gap: 15px; }
.stat-item { color: var(--text-gray); font-size: 12px; font-weight: 500; }
.exam-actions { display: flex; gap: 8px; }

/* Exam Sets Styles */
.exam-sets { background: var(--white); }
.exam-set-item { border-bottom: 1px solid var(--border-gray); }
.exam-set-item:last-child { border-bottom: none; }
.set-header { display: flex; align-items: center; gap: 15px; padding: 15px 20px 15px 40px; cursor: pointer; transition: all 0.3s ease; }
.set-header:hover { background: var(--light-gray); }
.set-info { flex: 1; }
.set-title-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.set-title { margin: 0; color: var(--text-dark); font-size: 14px; font-weight: 600; }
.set-meta { display: flex; gap: 8px; align-items: center; }
.questions-count { background: var(--gray-100); color: var(--text-gray); padding: 3px 6px; border-radius: 4px; font-size: 11px; font-weight: 500; }
.set-description { color: var(--text-gray); font-size: 12px; margin: 0; line-height: 1.3; }
.set-actions { display: flex; gap: 6px; }

/* Questions Styles */
.set-questions { background: var(--light-gray); }
.question-item { display: flex; gap: 15px; padding: 15px 20px 15px 60px; border-bottom: 1px solid var(--border-gray); background: var(--white); margin: 1px 0; }
.question-item:last-child { border-bottom: none; }
.question-content { flex: 1; }
.question-header { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
.question-id { color: var(--text-gray); font-size: 11px; font-weight: 500; }
.question-text { color: var(--text-dark); font-size: 14px; line-height: 1.4; margin-bottom: 8px; }
.question-options { display: flex; flex-direction: column; gap: 4px; }
.option-item { padding: 6px 10px; background: var(--gray-100); border-radius: 6px; font-size: 12px; color: var(--text-gray); }
.correct-option { background: var(--maroon-primary); color: var(--white); font-weight: 500; }
.question-actions { display: flex; flex-direction: column; gap: 4px; }

/* Type and Status Badges */
.type-badge { padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.025em; }
.type-multiple-choice, .type-true-false, .type-short-answer, .type-essay { background: var(--gray-200); color: var(--text-dark); }
.points-badge { background: var(--gray-100); color: var(--text-gray); padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; }
.status-badge { padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; }
.status-active { background: var(--gray-200); color: var(--text-dark); }
.status-inactive { background: var(--gray-300); color: var(--text-gray); }
.exam-set-badge { font-size: 12px; color: var(--text-gray); }

/* Empty States */
.no-sets, .no-questions { padding: 30px 20px; text-align: center; color: var(--text-gray); }
.no-sets p, .no-questions p { margin: 0 0 15px 0; font-size: 14px; }

/* Table Styles for List Views */
.table-container { background: var(--white); border-radius: 12px; overflow: hidden; border: 2px solid var(--border-gray); }
.exam-title-info { display: flex; flex-direction: column; gap: 4px; }
.exam-name { font-weight: 500; color: var(--text-dark); }
.duration-badge { background: var(--gray-100); color: var(--text-gray); padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500; }
.sets-count, .questions-count { color: var(--text-gray); font-size: 12px; }

/* Action Buttons */
.action-buttons { display: flex; gap: 6px; flex-wrap: wrap; }

/* Responsive Design */
@media (max-width: 768px) {
    .search-form { flex-direction: column; align-items: stretch; }
    .filter-group { flex-direction: column; align-items: stretch; }
    .exam-header { flex-direction: column; align-items: stretch; gap: 10px; }
    .exam-title-row { flex-direction: column; align-items: flex-start; gap: 8px; }
    .exam-actions { justify-content: flex-start; }
    .set-header { flex-direction: column; align-items: stretch; gap: 10px; }
    .set-title-row { flex-direction: column; align-items: flex-start; gap: 6px; }
    .question-item { flex-direction: column; gap: 10px; }
    .question-actions { flex-direction: row; justify-content: flex-start; }
}

@media (max-width: 480px) {
    .exam-header, .set-header { padding: 15px; }
    .question-item { padding: 15px; }
    .action-buttons { flex-direction: column; }
}
</style>
@endpush

@push('scripts')
<script>
// Hierarchical view expansion/collapse
function toggleExamExpansion(examId) {
    const setsContainer = document.getElementById(`sets-${examId}`);
    const expandIcon = document.getElementById(`expand-${examId}`);
    
    if (setsContainer.style.display === 'none') {
        setsContainer.style.display = 'block';
        expandIcon.textContent = '▲';
    } else {
        setsContainer.style.display = 'none';
        expandIcon.textContent = '▼';
    }
}

function toggleSetExpansion(setId) {
    const questionsContainer = document.getElementById(`questions-${setId}`);
    const expandIcon = document.getElementById(`expand-set-${setId}`);
    
    if (questionsContainer.style.display === 'none') {
        questionsContainer.style.display = 'block';
        expandIcon.textContent = '▲';
    } else {
        questionsContainer.style.display = 'none';
        expandIcon.textContent = '▼';
    }
}

function expandAll() {
    document.querySelectorAll('.exam-sets').forEach(el => el.style.display = 'block');
    document.querySelectorAll('.set-questions').forEach(el => el.style.display = 'block');
    document.querySelectorAll('.expand-icon').forEach(el => el.textContent = '▲');
}

function collapseAll() {
    document.querySelectorAll('.exam-sets').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.set-questions').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.expand-icon').forEach(el => el.textContent = '▼');
}

// Action functions
function toggleExamStatus(examId) {
    if (confirm('Are you sure you want to toggle the status of this exam?')) {
        fetch(`/admin/exams/${examId}/toggle-status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Error: ' + data.message);
        });
    }
}

function toggleQuestionStatus(questionId) {
    if (confirm('Are you sure you want to toggle the status of this question?')) {
        fetch(`/admin/questions/${questionId}/toggle-status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Error: ' + data.message);
        });
    }
}

function deleteExam(examId) {
    if (confirm('Are you sure you want to delete this exam? This will also delete all associated sets and questions.')) {
        fetch(`/admin/exams/${examId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Error: ' + data.message);
        });
    }
}

function deleteQuestion(questionId) {
    if (confirm('Are you sure you want to delete this question?')) {
        fetch(`/admin/questions/${questionId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Error: ' + data.message);
        });
    }
}
</script>
@endpush
