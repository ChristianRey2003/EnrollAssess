@extends('layouts.admin')

@section('title', 'Exam Details - ' . $applicant->full_name)

@push('styles')
<style>
    .exam-details-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        font-size: 14px;
        margin-bottom: 20px;
        padding: 0;
    }

    .breadcrumb-link {
        color: #800020;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .breadcrumb-link:hover {
        color: #5C0016;
        text-decoration: underline;
    }

    .breadcrumb-separator {
        margin: 0 8px;
        color: #9CA3AF;
    }

    .breadcrumb-current {
        color: #1F2937;
        font-weight: 600;
    }

    .summary-card {
        background: white;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f4f6;
    }

    .summary-title {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-item {
        background: #f9fafb;
        border-radius: 6px;
        padding: 16px;
        text-align: center;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 500;
    }

    .stat-value.correct {
        color: #059669;
    }

    .stat-value.incorrect {
        color: #dc2626;
    }

    .stat-value.total {
        color: #800020;
    }

    .questions-section {
        background: white;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 20px 0;
        padding-bottom: 12px;
        border-bottom: 2px solid #f3f4f6;
    }

    .question-card {
        background: #f9fafb;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
        border-left: 4px solid #e5e7eb;
    }

    .question-card.correct {
        border-left-color: #10b981;
        background: #f0fdf4;
    }

    .question-card.incorrect {
        border-left-color: #ef4444;
        background: #fef2f2;
    }

    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .question-number {
        font-size: 14px;
        font-weight: 600;
        color: #6b7280;
    }

    .question-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .question-status.correct {
        background: #d1fae5;
        color: #065f46;
    }

    .question-status.incorrect {
        background: #fee2e2;
        color: #991b1b;
    }

    .question-text {
        font-size: 15px;
        font-weight: 500;
        color: #1f2937;
        margin-bottom: 16px;
        line-height: 1.6;
    }

    .answer-section {
        margin-top: 12px;
    }

    .answer-label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }

    .answer-value {
        font-size: 14px;
        color: #1f2937;
        padding: 10px 12px;
        background: white;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }

    .answer-value.correct-answer {
        background: #d1fae5;
        border-color: #10b981;
        color: #065f46;
        font-weight: 500;
    }

    .answer-value.incorrect-answer {
        background: #fee2e2;
        border-color: #ef4444;
        color: #991b1b;
        font-weight: 500;
    }

    .correct-answer-label {
        font-size: 12px;
        font-weight: 600;
        color: #059669;
        margin-top: 8px;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    .empty-state-text {
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .empty-state-subtext {
        font-size: 14px;
        color: #9ca3af;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .question-header {
            flex-direction: column;
            gap: 8px;
        }
    }
</style>
@endpush

@section('content')
<div class="exam-details-container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.applicants.index') }}" class="breadcrumb-link">Applicants</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" class="breadcrumb-link">{{ $applicant->full_name }}</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Exam Details</span>
    </nav>

    <!-- Summary Card -->
    <div class="summary-card">
        <div class="summary-header">
            <h1 class="summary-title">Exam Details - {{ $applicant->full_name }}</h1>
            <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" class="btn btn-secondary">
                ← Back to Applicant
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value total">{{ $totalQuestions }}</div>
                <div class="stat-label">Total Questions</div>
            </div>
            <div class="stat-item">
                <div class="stat-value correct">{{ $correctAnswers }}</div>
                <div class="stat-label">Correct Answers</div>
            </div>
            <div class="stat-item">
                <div class="stat-value incorrect">{{ $incorrectAnswers }}</div>
                <div class="stat-label">Incorrect Answers</div>
            </div>
            <div class="stat-item">
                <div class="stat-value total">{{ $applicant->enrollassess_score ? number_format($applicant->enrollassess_score, 2) . '%' : 'N/A' }}</div>
                <div class="stat-label">Final Score</div>
            </div>
        </div>

        @if($examAttempt)
        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; font-size: 13px; color: #6b7280;">
                @if($examAttempt->started_at)
                <div>
                    <strong>Started:</strong> {{ $examAttempt->started_at->format('M d, Y g:i A') }}
                </div>
                @endif
                @if($examAttempt->completed_at)
                <div>
                    <strong>Completed:</strong> {{ $examAttempt->completed_at->format('M d, Y g:i A') }}
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Questions Section -->
    <div class="questions-section">
        <h2 class="section-title">Question-by-Question Breakdown</h2>

        @if($results->count() > 0)
            @foreach($results as $index => $result)
                @php
                    $question = $result->question;
                    $isCorrect = $result->is_correct;
                    $selectedOption = $result->selectedOption;
                    $correctOption = $question && $question->options ? $question->options->where('is_correct', true)->first() : null;
                @endphp
                <div class="question-card {{ $isCorrect ? 'correct' : 'incorrect' }}">
                    <div class="question-header">
                        <span class="question-number">Question {{ $index + 1 }}</span>
                        <span class="question-status {{ $isCorrect ? 'correct' : 'incorrect' }}">
                            {{ $isCorrect ? '✓ Correct' : '✗ Incorrect' }}
                        </span>
                    </div>

                    @if($question)
                        <div class="question-text">
                            {{ $question->question_text }}
                        </div>

                        <div class="answer-section">
                            @if($question->question_type === 'multiple_choice' && $question->options)
                                <div style="margin-bottom: 12px;">
                                    <div class="answer-label">Applicant's Answer:</div>
                                    <div class="answer-value {{ $isCorrect ? 'correct-answer' : 'incorrect-answer' }}">
                                        @if($selectedOption)
                                            {{ $selectedOption->option_text }}
                                        @else
                                            <span style="color: #9ca3af;">No answer selected</span>
                                        @endif
                                    </div>
                                </div>

                                @if(!$isCorrect && $correctOption)
                                <div>
                                    <div class="answer-label">Correct Answer:</div>
                                    <div class="answer-value correct-answer">
                                        {{ $correctOption->option_text }}
                                    </div>
                                </div>
                                @endif

                                <!-- Show all options for reference -->
                                <div style="margin-top: 12px;">
                                    <div class="answer-label">All Options:</div>
                                    <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 8px;">
                                        @foreach($question->options as $option)
                                            <div style="padding: 8px 12px; background: white; border-radius: 4px; border: 1px solid #e5e7eb; font-size: 13px; 
                                                @if($option->is_correct) border-color: #10b981; background: #f0fdf4; @endif
                                                @if($selectedOption && $selectedOption->option_id === $option->option_id && !$option->is_correct) border-color: #ef4444; background: #fef2f2; @endif">
                                                {{ $option->option_text }}
                                                @if($option->is_correct)
                                                    <span style="color: #059669; font-weight: 600; margin-left: 8px;">(Correct)</span>
                                                @endif
                                                @if($selectedOption && $selectedOption->option_id === $option->option_id && !$option->is_correct)
                                                    <span style="color: #dc2626; font-weight: 600; margin-left: 8px;">(Selected)</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($question->question_type === 'essay' || $question->question_type === 'short_answer')
                                <div style="margin-bottom: 12px;">
                                    <div class="answer-label">Applicant's Answer:</div>
                                    <div class="answer-value {{ $isCorrect ? 'correct-answer' : 'incorrect-answer' }}">
                                        {{ $result->answer_text ?: 'No answer provided' }}
                                    </div>
                                </div>
                            @endif

                            @if($result->points_earned !== null)
                            <div style="margin-top: 12px; font-size: 12px; color: #6b7280;">
                                Points Earned: <strong>{{ number_format($result->points_earned, 2) }}</strong>
                                @if($question->points)
                                    / {{ number_format($question->points, 2) }}
                                @endif
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="answer-value" style="color: #9ca3af;">
                            Question data not available
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <div class="empty-state-text">No exam results found</div>
                <div class="empty-state-subtext">This applicant has not completed the exam yet.</div>
            </div>
        @endif
    </div>
</div>
@endsection

