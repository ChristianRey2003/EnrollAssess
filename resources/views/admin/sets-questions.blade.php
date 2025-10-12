@extends('layouts.admin')

@section('title', 'Question Bank Management')

@php
    $pageTitle = 'Question Bank';
    $pageSubtitle = 'Manage your exam question bank';
@endphp

@push('styles')
<style>
    .content-section {
        padding: 20px;
        max-width: 1400px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .section-title {
        font-size: 25px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .section-actions {
        display: flex;
        gap: 8px;
    }

    .btn-primary, .btn-outline, .btn-success, .btn-secondary {
        padding: 7px 14px;
        border-radius: 4px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.15s;
    }

    .btn-primary {
        background: #991b1b;
        color: white;
    }

    .btn-primary:hover {
        background: #7f1d1d;
    }

    .btn-outline {
        background: white;
        color: #6b7280;
        border: 1px solid #d1d5db;
    }

    .btn-outline:hover {
        background: #f9fafb;
        border-color: #991b1b;
        color: #991b1b;
    }

    .btn-success {
        background: #059669;
        color: white;
    }

    .btn-success:hover {
        background: #047857;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    /* Exam Info - Compact */
    .exam-info-card {
        background: #fafafa;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }

    .exam-info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .exam-info-header h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .exam-info-header p {
        color: #6b7280;
        font-size: 14px;
        margin: 4px 0 0 0;
    }

    .exam-meta {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        font-size: 13px;
        color: #6b7280;
        margin-top: 8px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .meta-label {
        color: #9ca3af;
    }

    .status-badge {
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-draft {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Statistics - Inline Compact */
    .stats-grid {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .stat-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 10px 14px;
        display: flex;
        align-items: baseline;
        gap: 6px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
    }

    .stat-label {
        font-size: 13px;
        color: #6b7280;
    }

    /* Toolbar - Compact */
    .toolbar {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 10px 12px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .toolbar-left {
        display: flex;
        gap: 8px;
        flex: 1;
    }

    .search-box {
        position: relative;
        flex: 1;
        max-width: 300px;
    }

    .search-box input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 15px;
    }

    .search-box input:focus {
        outline: none;
        border-color: #991b1b;
    }

    .filter-select {
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 15px;
        background: white;
        color: #374151;
    }

    .filter-select:focus {
        outline: none;
        border-color: #991b1b;
    }

    /* Questions List - Compact */
    .questions-container {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .question-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: start;
        transition: background 0.15s;
    }

    .question-item:hover {
        background: #fafafa;
    }

    .question-item:last-child {
        border-bottom: none;
    }

    .question-content {
        flex: 1;
    }

    .question-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .question-number {
        background: #991b1b;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 600;
        min-width: 32px;
        text-align: center;
    }

    .question-type-badge {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .type-multiple_choice {
        background: #eff6ff;
        color: #1e40af;
    }

    .type-true_false {
        background: #f0fdf4;
        color: #166534;
    }

    /* .type-essay removed */

    .question-text {
        color: #1f2937;
        font-size: 15px;
        line-height: 1.4;
        margin-bottom: 6px;
    }

    .question-meta {
        display: flex;
        gap: 12px;
        font-size: 13px;
        color: #9ca3af;
    }

    .question-actions {
        display: flex;
        gap: 4px;
        margin-left: 12px;
    }

    .btn-icon {
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 15px;
        cursor: pointer;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .btn-icon:hover {
        background: #f9fafb;
        color: #1f2937;
        border-color: #d1d5db;
    }

    .btn-icon.danger:hover {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }

    /* Empty State */
    .empty-state {
        padding: 48px 20px;
        text-align: center;
    }

    .empty-state h4 {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 6px 0;
    }

    .empty-state p {
        color: #6b7280;
        font-size: 15px;
        margin: 0 0 20px 0;
    }

    /* Drawer */
    .drawer-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.4);
        z-index: 1000;
    }

    .drawer-overlay.active {
        display: block;
    }

    .drawer-content {
        position: fixed;
        top: 0;
        right: -600px;
        width: 600px;
        height: 100vh;
        background: white;
        box-shadow: -2px 0 8px rgba(0,0,0,0.1);
        transition: right 0.3s ease;
        display: flex;
        flex-direction: column;
        z-index: 1001;
    }

    .drawer-overlay.active .drawer-content {
        right: 0;
    }

    .drawer-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .drawer-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .drawer-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }

    .drawer-close:hover {
        background: #f3f4f6;
    }

    .drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    .drawer-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-shrink: 0;
    }

    .form-group {
        margin-bottom: 14px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 5px;
    }

    .form-control {
        width: 100%;
        padding: 7px 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 15px;
    }

    .form-control:focus {
        outline: none;
        border-color: #991b1b;
        box-shadow: 0 0 0 2px rgba(153, 27, 27, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .error-message {
        display: block;
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
    }

    .form-control.error {
        border-color: #ef4444;
    }

    /* Mobile responsiveness for drawer */
    @media (max-width: 768px) {
        .drawer-content {
            width: 100%;
            right: -100%;
        }
        
        .drawer-footer {
            position: sticky;
            bottom: 0;
            background: white;
        }
    }
</style>
@endpush

@section('content')
<div class="content-section">
    <!-- Header -->
    <div class="section-header">
        <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
            <h2 class="section-title">Question Bank</h2>
            
        </div>
        <div class="section-actions">
            @if($currentExam)
                <button type="button" onclick="showNewSemesterModal()" class="btn-outline">
                    New Exam
                </button>
                <button onclick="showAddQuestionModal()" class="btn-primary">
                    Add Question
                </button>
            @else
                <button onclick="showCreateExamModal()" class="btn-primary">
                    Setup First Exam
                </button>
            @endif
        </div>
    </div>

    @if($currentExam)
        <!-- Compact Exam Info Bar -->
        <div class="exam-info-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;">
                    <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                        <div style="font-size: 13px; color: #6b7280;">
                            {{ $currentExam->description }}
                        </div>
                        <div class="exam-meta" style="margin: 0;">
                            <span class="meta-item"><span class="meta-label">Duration:</span> {{ $currentExam->formatted_duration }}</span>
                            <span class="meta-item"><span class="meta-label">Created:</span> {{ $currentExam->created_at->format('M d, Y') }}</span>
                            <span class="meta-item"><span class="meta-label">Questions:</span> {{ $stats['total_questions'] }} ({{ $stats['active_questions'] }} active)</span>
                            @if($currentExam->total_items)
                                <span class="meta-item"><span class="meta-label">Exam Size:</span> {{ $currentExam->total_items }} items</span>
                            @endif
                            @if($currentExam->mcq_quota || $currentExam->tf_quota)
                                <span class="meta-item"><span class="meta-label">Quota:</span> MCQ:{{ $currentExam->mcq_quota ?? 0 }} / TF:{{ $currentExam->tf_quota ?? 0 }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    @if(!$currentExam->is_active)
                        <button onclick="publishExam({{ $currentExam->exam_id }})" class="btn-success">
                            Publish
                        </button>
                    @endif
                    <button onclick="openEditSettingsDrawer()" class="btn-outline">
                        Edit Settings
                    </button>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-left">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search questions..." onkeyup="filterQuestions()">
                </div>
                <select class="filter-select" id="typeFilter" onchange="filterQuestions()">
                    <option value="">All Types</option>
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="true_false">True/False</option>
                </select>
                <select class="filter-select" id="statusFilter" onchange="filterQuestions()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            <button onclick="runConsistencyCheck()" class="btn-outline">
                Consistency Check
            </button>
        </div>

        <!-- Questions List -->
        <div class="questions-container" id="questionsList">
            @forelse($questions as $index => $question)
                <div class="question-item" 
                     data-type="{{ $question->question_type }}" 
                     data-status="{{ $question->is_active ? 'active' : 'draft' }}"
                     data-text="{{ strtolower($question->question_text) }}">
                    <div class="question-content">
                        <div class="question-header">
                            <span class="question-number">Q{{ $index + 1 }}</span>
                            <span class="question-type-badge type-{{ $question->question_type }}">
                                {{ str_replace('_', ' ', $question->question_type) }}
                            </span>
                            @if(!$question->is_active)
                                <span class="status-badge status-draft">Draft</span>
                            @endif
                        </div>
                        <div class="question-text">{{ $question->question_text }}</div>
                        <div class="question-meta">
                            <span>Points: {{ $question->points }}</span>
                            @if($question->options->count() > 0)
                                <span>Options: {{ $question->options->count() }}</span>
                            @endif
                            @if($question->order_number)
                                <span>Order: {{ $question->order_number }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="question-actions">
                        <button onclick="editQuestion({{ $question->question_id }})" class="btn-icon" title="Edit">
                            Edit
                        </button>
                        <button onclick="duplicateQuestion({{ $question->question_id }})" class="btn-icon" title="Duplicate">
                            Duplicate
                        </button>
                        <button onclick="toggleQuestionStatus({{ $question->question_id }})" class="btn-icon" title="Toggle Status">
                            {{ $question->is_active ? 'Hide' : 'Show' }}
                        </button>
                        <button onclick="deleteQuestion({{ $question->question_id }})" class="btn-icon danger" title="Delete">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <h4>No Questions Yet</h4>
                    <p>Start building your question bank by adding your first question.</p>
                    <button onclick="showAddQuestionModal()" class="btn-primary">
                        Add First Question
                    </button>
                </div>
            @endforelse
        </div>
    @else
        <!-- No Exam Setup -->
        <div class="questions-container">
            <div class="empty-state">
                <h4>No Exam Configured</h4>
                <p>Create your first exam to start building your question bank.</p>
                <button onclick="showCreateExamModal()" class="btn-primary">
                    Setup First Exam
                </button>
            </div>
        </div>
    @endif
</div>

<!-- Add/Edit Question Drawer -->
<div id="questionDrawer" class="drawer-overlay">
    <div class="drawer-content">
        <div class="drawer-header">
            <h3 id="drawerTitle">Add Question</h3>
            <button class="drawer-close" onclick="closeQuestionDrawer()">×</button>
        </div>
        <div class="drawer-body">
            <form id="questionForm">
                <input type="hidden" id="questionId" name="question_id">
                <input type="hidden" name="exam_id" value="{{ $currentExam->exam_id ?? '' }}">
                
                <div class="form-group">
                    <label class="form-label">Question Type</label>
                    <select class="form-control" name="question_type" id="questionType" required onchange="handleTypeChange()">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Question Text</label>
                    <textarea class="form-control" name="question_text" id="questionText" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Points</label>
                    <input type="number" class="form-control" name="points" id="questionPoints" value="1" min="1" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Order Number (Optional)</label>
                    <input type="number" class="form-control" name="order_number" id="questionOrder" min="1">
                </div>

                <div class="form-group">
                    <label class="form-label">Explanation (Optional)</label>
                    <textarea class="form-control" name="explanation" id="questionExplanation" rows="3"></textarea>
                </div>

                <div id="optionsContainer" style="display: none;">
                    <label class="form-label">Answer Options</label>
                    <div id="optionsList"></div>
                    <button type="button" onclick="addOption()" class="btn-outline" style="margin-top: 8px;">Add Option</button>
                </div>
            </form>
        </div>
        <div class="drawer-footer">
            <button class="btn-secondary" onclick="closeQuestionDrawer()">Cancel</button>
            <button class="btn-primary" onclick="saveQuestion()" id="saveQuestionBtn">Save Question</button>
        </div>
    </div>
</div>

<!-- Edit Exam Settings Drawer -->
<div id="settingsDrawer" class="drawer-overlay">
    <div class="drawer-content">
        <div class="drawer-header">
            <h3>Exam Settings</h3>
            <button class="drawer-close" onclick="closeSettingsDrawer()">×</button>
        </div>
        <div class="drawer-body">
            <form id="settingsForm">
                <input type="hidden" id="exam_id" name="exam_id" value="{{ $currentExam->exam_id ?? '' }}">
                
                <div class="form-group">
                    <label class="form-label">Duration (minutes) <span style="color: #ef4444;">*</span></label>
                    <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                           value="{{ $currentExam->duration_minutes ?? '' }}" min="1" max="600" required>
                    <span class="error-message" id="error_duration_minutes"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Total Items <span style="color: #ef4444;">*</span></label>
                    <input type="number" class="form-control" id="total_items" name="total_items" 
                           value="{{ $currentExam->total_items ?? '' }}" min="1" required>
                    <span class="error-message" id="error_total_items"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Multiple Choice Quota</label>
                    <input type="number" class="form-control" id="mcq_quota" name="mcq_quota" 
                           value="{{ $currentExam->mcq_quota ?? 0 }}" min="0">
                    <span class="error-message" id="error_mcq_quota"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">True/False Quota</label>
                    <input type="number" class="form-control" id="tf_quota" name="tf_quota" 
                           value="{{ $currentExam->tf_quota ?? 0 }}" min="0">
                    <span class="error-message" id="error_tf_quota"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="is_active" name="is_active" 
                               {{ $currentExam && $currentExam->is_active ? 'checked' : '' }}>
                        Active
                    </label>
                    <small style="color: #6b7280; font-size: 13px;">Make this exam available for applicants</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Availability Start</label>
                    <input type="datetime-local" class="form-control" id="starts_at" name="starts_at" 
                           value="{{ $currentExam && $currentExam->starts_at ? $currentExam->starts_at->format('Y-m-d\TH:i') : '' }}">
                    <span class="error-message" id="error_starts_at"></span>
                    <small style="color: #6b7280; font-size: 13px;">When can applicants start taking the exam?</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Availability End</label>
                    <input type="datetime-local" class="form-control" id="ends_at" name="ends_at" 
                           value="{{ $currentExam && $currentExam->ends_at ? $currentExam->ends_at->format('Y-m-d\TH:i') : '' }}">
                    <span class="error-message" id="error_ends_at"></span>
                    <small style="color: #6b7280; font-size: 13px;">When should the exam become unavailable?</small>
                </div>

                <div style="padding: 12px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 4px; margin-top: 16px;">
                    <div style="font-size: 13px; color: #92400e;">
                        <strong>Note:</strong> MCQ quota + TF quota must equal or be less than total items.
                    </div>
                </div>
            </form>
        </div>
        <div class="drawer-footer">
            <button class="btn-secondary" onclick="closeSettingsDrawer()">Cancel</button>
            <button class="btn-primary" onclick="saveSettings()" id="saveSettingsBtn">Save Settings</button>
        </div>
    </div>
</div>

<!-- Create Exam Modal -->
<div id="examModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 8px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="examModalTitle" style="margin: 0; font-size: 20px; font-weight: 600; color: #1f2937;">Setup First Exam</h3>
            <button onclick="closeExamModal()" style="background: none; border: none; font-size: 28px; color: #6b7280; cursor: pointer; padding: 0; width: 32px; height: 32px;">&times;</button>
        </div>
        
        <div style="padding: 24px;">
            <form id="examForm">
                @csrf
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Exam Title <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="exam_title" 
                           name="title" 
                           placeholder="e.g., EnrollAssess - First Semester 2025" 
                           required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;">
                    <span class="error-message" id="exam_error_title" style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;"></span>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Description
                    </label>
                    <textarea class="form-control" 
                              id="exam_description" 
                              name="description" 
                              rows="3" 
                              placeholder="Brief description of this exam..."
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
                    <span class="error-message" id="exam_error_description" style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;"></span>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Duration (minutes) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="number" 
                           class="form-control" 
                           id="exam_duration_minutes" 
                           name="duration_minutes" 
                           min="5" 
                           max="480" 
                           value="60" 
                           required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;">
                    <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                        <button type="button" onclick="document.getElementById('exam_duration_minutes').value=30" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">30 min</button>
                        <button type="button" onclick="document.getElementById('exam_duration_minutes').value=60" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">1 hour</button>
                        <button type="button" onclick="document.getElementById('exam_duration_minutes').value=90" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">1.5 hours</button>
                        <button type="button" onclick="document.getElementById('exam_duration_minutes').value=120" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">2 hours</button>
                    </div>
                    <span class="error-message" id="exam_error_duration_minutes" style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;"></span>
                </div>

                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 12px; margin-bottom: 20px;">
                    <p style="margin: 0; font-size: 13px; color: #6b7280; line-height: 1.5;">
                        <strong style="color: #374151;">Next steps:</strong> After creating this exam, you'll be able to add questions to build your question bank.
                    </p>
                </div>
            </form>
        </div>

        <div style="padding: 16px 24px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" 
                    onclick="closeExamModal()" 
                    class="btn-outline"
                    style="padding: 8px 16px; background: white; color: #6b7280; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer;">
                Cancel
            </button>
            <button type="button" 
                    onclick="saveExam()" 
                    id="saveExamBtn"
                    class="btn-primary"
                    style="padding: 8px 16px; background: #991b1b; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer;">
                Create Exam
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Filter questions
    function filterQuestions() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const typeFilter = document.getElementById('typeFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        
        document.querySelectorAll('.question-item').forEach(item => {
            const text = item.dataset.text;
            const type = item.dataset.type;
            const status = item.dataset.status;
            
            const matchesSearch = !searchText || text.includes(searchText);
            const matchesType = !typeFilter || type === typeFilter;
            const matchesStatus = !statusFilter || status === statusFilter;
            
            item.style.display = (matchesSearch && matchesType && matchesStatus) ? 'flex' : 'none';
        });
    }

    // Show add question drawer
    function showAddQuestionModal() {
        document.getElementById('drawerTitle').textContent = 'Add Question';
        document.getElementById('questionForm').reset();
        document.getElementById('questionId').value = '';
        document.getElementById('questionDrawer').classList.add('active');
        handleTypeChange();
    }

    // Close question drawer
    function closeQuestionDrawer() {
        document.getElementById('questionDrawer').classList.remove('active');
    }

    // Handle question type change
    function handleTypeChange() {
        const type = document.getElementById('questionType').value;
        const optionsContainer = document.getElementById('optionsContainer');
        
        if (type === 'multiple_choice' || type === 'true_false') {
            optionsContainer.style.display = 'block';
            if (type === 'true_false') {
                // Auto-populate True/False options
                document.getElementById('optionsList').innerHTML = `
                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <input type="text" class="form-control" value="True" readonly>
                        <label style="display: flex; align-items: center; gap: 4px;">
                            <input type="radio" name="correct_option" value="0" required> Correct
                        </label>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" class="form-control" value="False" readonly>
                        <label style="display: flex; align-items: center; gap: 4px;">
                            <input type="radio" name="correct_option" value="1" required> Correct
                        </label>
                    </div>
                `;
            } else {
                // Clear for multiple choice
                document.getElementById('optionsList').innerHTML = '';
                addOption();
                addOption();
            }
        } else {
            optionsContainer.style.display = 'none';
        }
    }

    // Add option for MCQ
    let optionCount = 0;
    function addOption() {
        const optionsList = document.getElementById('optionsList');
        const optionHtml = `
            <div class="option-item" style="display: flex; gap: 8px; margin-bottom: 8px;">
                <input type="text" class="form-control" name="options[]" placeholder="Option text" required>
                <label style="display: flex; align-items: center; gap: 4px; white-space: nowrap;">
                    <input type="radio" name="correct_option" value="${optionCount}" required> Correct
                </label>
                <button type="button" onclick="this.parentElement.remove()" class="btn-icon danger">×</button>
            </div>
        `;
        optionsList.insertAdjacentHTML('beforeend', optionHtml);
        optionCount++;
    }

    // Save question
    function saveQuestion() {
        const form = document.getElementById('questionForm');
        const formData = new FormData(form);
        const questionId = document.getElementById('questionId').value;
        
        // Collect options
        const questionType = document.getElementById('questionType').value;
        if (questionType === 'multiple_choice' || questionType === 'true_false') {
            const options = [];
            const optionInputs = document.querySelectorAll('#optionsList input[name="options[]"]');
            const correctRadio = document.querySelector('input[name="correct_option"]:checked');
            
            optionInputs.forEach((option, index) => {
                options.push({
                    option_text: option.value,
                    is_correct: correctRadio && parseInt(correctRadio.value) === index
                });
            });
            
            formData.append('options', JSON.stringify(options));
        }
        
        const url = questionId ? `/admin/questions/${questionId}` : '/admin/questions';
        const method = questionId ? 'PUT' : 'POST';
        
        const saveBtn = document.getElementById('saveQuestionBtn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeQuestionDrawer();
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save question'));
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Question';
            }
        })
        .catch(error => {
            alert('Error saving question: ' + error.message);
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Question';
        });
    }

    // Edit question
    function editQuestion(id) {
        fetch(`/admin/questions/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const question = data.question;
                    
                    document.getElementById('drawerTitle').textContent = 'Edit Question';
                    document.getElementById('questionId').value = question.question_id;
                    document.getElementById('questionType').value = question.question_type;
                    document.getElementById('questionText').value = question.question_text;
                    document.getElementById('questionPoints').value = question.points;
                    document.getElementById('questionOrder').value = question.order_number || '';
                    document.getElementById('questionExplanation').value = question.explanation || '';
                    
                    handleTypeChange();
                    
                    // Load options if MCQ or T/F
                    if (question.options && question.options.length > 0) {
                        const optionsList = document.getElementById('optionsList');
                        optionsList.innerHTML = '';
                        
                        question.options.forEach((option, index) => {
                            const optionHtml = `
                                <div class="option-item" style="display: flex; gap: 8px; margin-bottom: 8px;">
                                    <input type="text" class="form-control" name="options[]" value="${option.option_text}" required>
                                    <label style="display: flex; align-items: center; gap: 4px; white-space: nowrap;">
                                        <input type="radio" name="correct_option" value="${index}" ${option.is_correct ? 'checked' : ''} required> Correct
                                    </label>
                                </div>
                            `;
                            optionsList.insertAdjacentHTML('beforeend', optionHtml);
                        });
                    }
                    
                    document.getElementById('questionDrawer').classList.add('active');
                } else {
                    alert('Error loading question');
                }
            })
            .catch(error => alert('Error: ' + error.message));
    }

    // Duplicate question
    function duplicateQuestion(id) {
        if (confirm('Duplicate this question?')) {
            fetch(`/admin/questions/${id}/duplicate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
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
            .catch(error => alert('Error: ' + error.message));
        }
    }

    // Toggle question status
    function toggleQuestionStatus(id) {
        fetch(`/admin/questions/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
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
        .catch(error => alert('Error: ' + error.message));
    }

    // Delete question
    function deleteQuestion(id) {
        if (confirm('Are you sure you want to delete this question?')) {
            fetch(`/admin/questions/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
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
            .catch(error => alert('Error: ' + error.message));
        }
    }

    // Publish exam
    function publishExam(id) {
        if (confirm('Publish this exam? It will become active for applicants.')) {
            fetch(`/admin/sets-questions/publish-exam/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }

    // Run consistency check
    function runConsistencyCheck() {
        const examId = {{ $currentExam->exam_id ?? 'null' }};
        if (!examId) return;
        
        fetch(`/admin/sets-questions/consistency-check/${examId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.total_issues === 0) {
                        alert('All checks passed! No issues found.');
                    } else {
                        let message = `Found ${data.total_issues} issue(s):\n\n`;
                        data.issues.forEach(issue => {
                            message += `- ${issue.message}\n`;
                        });
                        alert(message);
                    }
                }
            });
    }

    // Show new semester modal
    function showNewSemesterModal() {
        document.getElementById('examModalTitle').textContent = 'Create New Exam';
        document.getElementById('examForm').reset();
        document.getElementById('examModal').style.display = 'flex';
        clearExamErrors();
    }

    // Show create exam modal
    function showCreateExamModal() {
        document.getElementById('examModalTitle').textContent = 'Setup First Exam';
        document.getElementById('examForm').reset();
        document.getElementById('examModal').style.display = 'flex';
        clearExamErrors();
    }

    // Close exam modal
    function closeExamModal() {
        document.getElementById('examModal').style.display = 'none';
        clearExamErrors();
    }

    // Clear exam form errors
    function clearExamErrors() {
        document.querySelectorAll('#examForm .error-message').forEach(el => el.textContent = '');
        document.querySelectorAll('#examForm .form-control.error').forEach(el => el.classList.remove('error'));
    }

    // Show exam field error
    function showExamFieldError(fieldName, message) {
        const errorEl = document.getElementById('exam_error_' + fieldName);
        const inputEl = document.getElementById('exam_' + fieldName);
        
        if (errorEl) {
            errorEl.textContent = message;
        }
        if (inputEl) {
            inputEl.classList.add('error');
        }
    }

    // Save exam (create)
    function saveExam() {
        const form = document.getElementById('examForm');
        const formData = new FormData(form);
        const submitBtn = document.getElementById('saveExamBtn');
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';
        
        clearExamErrors();
        
        fetch('/admin/exams', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to show new exam
                window.location.reload();
            } else {
                // Show validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showExamFieldError(field, data.errors[field][0]);
                    });
                } else if (data.message) {
                    alert(data.message);
                }
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Exam';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to create exam. Please try again.');
            
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Exam';
        });
    }

    // Open edit settings drawer
    function openEditSettingsDrawer() {
        clearSettingsErrors();
        document.getElementById('settingsDrawer').classList.add('active');
    }

    // Close settings drawer
    function closeSettingsDrawer() {
        document.getElementById('settingsDrawer').classList.remove('active');
        clearSettingsErrors();
    }

    // Clear all error messages
    function clearSettingsErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
    }

    // Show error for specific field
    function showFieldError(fieldName, message) {
        const errorEl = document.getElementById('error_' + fieldName);
        const inputEl = document.getElementById(fieldName);
        
        if (errorEl) {
            errorEl.textContent = message;
        }
        if (inputEl) {
            inputEl.classList.add('error');
        }
    }

    // Save exam settings
    function saveSettings() {
        const examId = document.getElementById('exam_id').value;
        if (!examId) {
            alert('Exam ID not found');
            return;
        }

        clearSettingsErrors();

        const saveBtn = document.getElementById('saveSettingsBtn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        // Collect form data
        const payload = {
            duration_minutes: parseInt(document.getElementById('duration_minutes').value),
            total_items: parseInt(document.getElementById('total_items').value),
            mcq_quota: parseInt(document.getElementById('mcq_quota').value) || 0,
            tf_quota: parseInt(document.getElementById('tf_quota').value) || 0,
            is_active: document.getElementById('is_active').checked,
            starts_at: document.getElementById('starts_at').value || null,
            ends_at: document.getElementById('ends_at').value || null,
        };

        fetch(`/admin/exams/${examId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeSettingsDrawer();
                
                // Update the UI with new values without full reload
                updateExamInfoDisplay(data.exam);
                
                // Show success message
                alert(data.message || 'Exam settings updated successfully!');
            } else {
                // Handle validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorMessages = data.errors[field];
                        if (Array.isArray(errorMessages) && errorMessages.length > 0) {
                            showFieldError(field, errorMessages[0]);
                        }
                    });
                } else {
                    alert(data.message || 'Failed to update settings');
                }
                
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Settings';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving settings');
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Settings';
        });
    }

    // Update exam info display after save
    function updateExamInfoDisplay(exam) {
        // Update duration
        const durationEl = document.querySelector('.exam-meta .meta-item:nth-child(1)');
        if (durationEl && exam.formatted_duration) {
            durationEl.innerHTML = `<span class="meta-label">Duration:</span> ${exam.formatted_duration}`;
        }

        // Update exam size
        const sizeEl = document.querySelector('.exam-meta .meta-item:nth-child(4)');
        if (sizeEl && exam.total_items) {
            sizeEl.innerHTML = `<span class="meta-label">Exam Size:</span> ${exam.total_items} items`;
        }

        // Update quotas
        const quotaEl = document.querySelector('.exam-meta .meta-item:nth-child(5)');
        if (quotaEl) {
            const mcq = exam.mcq_quota || 0;
            const tf = exam.tf_quota || 0;
            quotaEl.innerHTML = `<span class="meta-label">Quota:</span> MCQ:${mcq} / TF:${tf}`;
        }

        // Update form values for next open
        document.getElementById('duration_minutes').value = exam.duration_minutes || '';
        document.getElementById('total_items').value = exam.total_items || '';
        document.getElementById('mcq_quota').value = exam.mcq_quota || 0;
        document.getElementById('tf_quota').value = exam.tf_quota || 0;
        document.getElementById('is_active').checked = exam.is_active;
        
        if (exam.starts_at) {
            // Convert server datetime to local datetime-local format
            const startsAt = new Date(exam.starts_at);
            document.getElementById('starts_at').value = formatDateTimeLocal(startsAt);
        }
        
        if (exam.ends_at) {
            const endsAt = new Date(exam.ends_at);
            document.getElementById('ends_at').value = formatDateTimeLocal(endsAt);
        }
    }

    // Format date for datetime-local input
    function formatDateTimeLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    // Close drawer on outside click or Esc
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('drawer-overlay')) {
            if (event.target.id === 'questionDrawer') {
                closeQuestionDrawer();
            } else if (event.target.id === 'settingsDrawer') {
                closeSettingsDrawer();
            }
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeQuestionDrawer();
            closeSettingsDrawer();
            closeExamModal();
        }
    });

    // Close modal when clicking outside
    document.getElementById('examModal')?.addEventListener('click', function(event) {
        if (event.target === this) {
            closeExamModal();
        }
    });
</script>
@endpush
