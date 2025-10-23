@extends('layouts.admin')

@section('title', 'Interview Detail - ' . $applicant->full_name)

@php
    $pageTitle = 'Interview Detail';
    $pageSubtitle = $applicant->full_name . ' • ' . $applicant->application_no;
@endphp

@section('content')
<div class="interview-detail-container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.applicants.index') }}" class="breadcrumb-link">Applicants</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" class="breadcrumb-link">{{ $applicant->full_name }}</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Interview</span>
    </nav>

    <!-- 1. Header Section -->
    <div class="detail-card header-card">
        <div class="header-grid">
            <div class="header-primary">
                <div class="applicant-avatar">
                    {{ substr($applicant->full_name, 0, 2) }}
                </div>
                <div class="applicant-info">
                    <h2 class="applicant-name">{{ $applicant->full_name }}</h2>
                    <div class="applicant-meta">
                        <span class="meta-item">{{ $applicant->application_no }}</span>
                        <span class="meta-separator">•</span>
                        <span class="meta-item meta-email">{{ $applicant->email_address }}</span>
                    </div>
                </div>
            </div>
            <div class="header-status">
                <div class="status-group">
                    <span class="status-label">Interview Status</span>
                    <span class="status-badge status-{{ $interview->status }}">
                        {{ ucfirst(str_replace('-', ' ', $interview->status)) }}
                    </span>
                </div>
                <div class="dates-group">
                    @if($interview->schedule_date)
                        <div class="date-item">
                            <span class="date-label">Scheduled</span>
                            <span class="date-value">{{ $interview->schedule_date->format('M d, Y • g:i A') }}</span>
                        </div>
                    @endif
                    @if($interview->status === 'completed' && $interview->updated_at)
                        <div class="date-item">
                            <span class="date-label">Completed</span>
                            <span class="date-value">{{ $interview->updated_at->format('M d, Y • g:i A') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="detail-grid">
        <!-- 2. Interview Summary Section -->
        <div class="detail-card summary-card">
            <h3 class="card-title">Interview Summary</h3>
            <div class="summary-content">
                <div class="summary-row">
                    <span class="row-label">Interviewer</span>
                    <span class="row-value">
                        @if($interview->interviewer)
                            {{ $interview->interviewer->full_name }}
                            <span class="role-badge">{{ ucfirst($interview->interviewer->role) }}</span>
                        @else
                            <span class="text-muted">Unassigned</span>
                        @endif
                    </span>
                </div>
                
                @if($applicant->assignedInstructor && $applicant->assignedInstructor->user_id !== $interview->interviewer_id)
                    <div class="summary-row">
                        <span class="row-label">Assigned Instructor</span>
                        <span class="row-value">{{ $applicant->assignedInstructor->full_name }}</span>
                    </div>
                @endif

                <div class="summary-divider"></div>

                <div class="summary-row">
                    <span class="row-label">Exam Score</span>
                    <span class="row-value">
                        <span class="score-highlight">{{ number_format($applicant->enrollassess_score ?? 0, 1) }}%</span>
                        @if($totalQuestions > 0)
                            <span class="score-detail">({{ $correctAnswers }}/{{ $totalQuestions }})</span>
                        @endif
                        <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" class="detail-link">View Details</a>
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. Scoring and Rubric Section -->
        <div class="detail-card scoring-card">
            <h3 class="card-title">Scoring and Rubric</h3>
            
            @if($interview->status === 'completed' && $interview->overall_score !== null)
                <div class="scoring-content">
                    <div class="overall-score-display">
                        <div class="score-number">{{ number_format($interview->overall_score, 1) }}%</div>
                        <div class="score-label">Overall Interview Score</div>
                    </div>

                    <div class="rubric-breakdown">
                        <h4 class="rubric-heading">Rubric Breakdown</h4>
                        <div class="rubric-grid">
                            @if($interview->communication_skills !== null)
                                <div class="rubric-item">
                                    <span class="rubric-name">Communication Skills</span>
                                    <span class="rubric-score">{{ $interview->communication_skills }}/10</span>
                                </div>
                            @endif

                            @if($interview->motivation_interest !== null)
                                <div class="rubric-item">
                                    <span class="rubric-name">Motivation & Interest</span>
                                    <span class="rubric-score">{{ $interview->motivation_interest }}/10</span>
                                </div>
                            @endif

                            @if($interview->problem_solving_attitude !== null)
                                <div class="rubric-item">
                                    <span class="rubric-name">Problem-Solving Attitude</span>
                                    <span class="rubric-score">{{ $interview->problem_solving_attitude }}/10</span>
                                </div>
                            @endif

                            @if($interview->program_understanding !== null)
                                <div class="rubric-item">
                                    <span class="rubric-name">Program Understanding</span>
                                    <span class="rubric-score">{{ $interview->program_understanding }}/10</span>
                                </div>
                            @endif

                            @if($interview->personality_attitude !== null)
                                <div class="rubric-item">
                                    <span class="rubric-name">Personality & Attitude</span>
                                    <span class="rubric-score">{{ $interview->personality_attitude }}/10</span>
                                </div>
                            @endif

                            @if($interview->it_background !== null)
                                <div class="rubric-item">
                                    <span class="rubric-name">IT Background</span>
                                    <span class="rubric-score">{{ $interview->it_background }}/10</span>
                                </div>
                            @endif

                            @if($interview->willingness_to_learn !== null)
                                <div class="rubric-item">
                                    <span class="rubric-name">Willingness to Learn</span>
                                    <span class="rubric-score">{{ $interview->willingness_to_learn }}/10</span>
                                </div>
                            @endif

                            @if($interview->overall_impression !== null)
                                <div class="rubric-item">
                                    <span class="rubric-name">Overall Impression</span>
                                    <span class="rubric-score">{{ $interview->overall_impression }}/10</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($interview->recommendation)
                        <div class="recommendation-section">
                            <span class="recommendation-label">Final Recommendation</span>
                            <span class="recommendation-badge recommendation-{{ str_replace('_', '-', $interview->recommendation) }}">
                                {{ ucfirst(str_replace('_', ' ', $interview->recommendation)) }}
                            </span>
                        </div>
                    @endif
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <p class="empty-text">Not evaluated yet</p>
                    <p class="empty-subtext">Interview scores will appear here once the evaluation is complete.</p>
                </div>
            @endif
        </div>

        <!-- 4. Notes and Activity Section -->
        <div class="detail-card notes-card full-width">
            <h3 class="card-title">Notes and Activity</h3>
            
            @if($interview->final_comments || $interview->interview_notes || $interview->evaluator_notes)
                <div class="notes-content">
                    @if($interview->final_comments)
                        <div class="note-section">
                            <h4 class="note-heading">Final Comments</h4>
                            <div class="note-text">{{ $interview->final_comments }}</div>
                        </div>
                    @endif

                    @if($interview->interview_notes)
                        <div class="note-section">
                            <h4 class="note-heading">Interview Notes</h4>
                            <div class="note-text">{{ $interview->interview_notes }}</div>
                        </div>
                    @endif

                    @if($interview->evaluator_notes)
                        <div class="note-section">
                            <h4 class="note-heading">Evaluator Notes</h4>
                            <div class="note-text">{{ $interview->evaluator_notes }}</div>
                        </div>
                    @endif

                    <div class="activity-log">
                        <h4 class="activity-heading">Activity Log</h4>
                        <div class="activity-timeline">
                            <div class="activity-item">
                                <span class="activity-time">{{ $interview->created_at->format('M d, Y g:i A') }}</span>
                                <span class="activity-action">Interview created</span>
                            </div>
                            
                            @if($interview->schedule_date)
                                <div class="activity-item">
                                    <span class="activity-time">{{ $interview->schedule_date->format('M d, Y g:i A') }}</span>
                                    <span class="activity-action">Interview scheduled</span>
                                </div>
                            @endif

                            @if($interview->status === 'completed')
                                <div class="activity-item">
                                    <span class="activity-time">{{ $interview->updated_at->format('M d, Y g:i A') }}</span>
                                    <span class="activity-action">Interview completed and scored</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <p class="empty-text">No notes available</p>
                    <p class="empty-subtext">Notes and activity will appear here once the interview is conducted.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="actions-bar">
        <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" class="btn btn-secondary">
            Back to Applicant
        </a>
        
        @if($interview->status !== 'completed')
            <a href="{{ route('admin.interviews.conduct', $interview->interview_id) }}" class="btn btn-primary">
                Conduct Interview
            </a>
        @else
            <a href="{{ route('admin.interviews.conduct', $interview->interview_id) }}" class="btn btn-outline">
                Edit Evaluation
            </a>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.interview-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px;
}

/* Breadcrumb */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 24px;
    font-size: 14px;
    color: #6B7280;
}

.breadcrumb-link {
    color: #6B7280;
    text-decoration: none;
    transition: color 0.2s;
}

.breadcrumb-link:hover {
    color: #800020;
}

.breadcrumb-separator {
    color: #D1D5DB;
}

.breadcrumb-current {
    color: #111827;
    font-weight: 500;
}

/* Detail Cards */
.detail-card {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.full-width {
    grid-column: 1 / -1;
}

/* Header Card */
.header-card {
    margin-bottom: 24px;
}

.header-grid {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
}

.header-primary {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

.applicant-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #800020 0%, #a00028 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 20px;
    flex-shrink: 0;
}

.applicant-info {
    flex: 1;
}

.applicant-name {
    margin: 0 0 6px 0;
    font-size: 24px;
    font-weight: 600;
    color: #111827;
}

.applicant-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6B7280;
    font-size: 14px;
}

.meta-separator {
    color: #D1D5DB;
}

.meta-email {
    color: #4B5563;
}

.header-status {
    text-align: right;
}

.status-group {
    margin-bottom: 12px;
}

.status-label {
    display: block;
    font-size: 12px;
    color: #6B7280;
    margin-bottom: 4px;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
}

.status-badge.status-scheduled {
    background: #DBEAFE;
    color: #1E40AF;
}

.status-badge.status-completed {
    background: #D1FAE5;
    color: #065F46;
}

.status-badge.status-cancelled {
    background: #FEE2E2;
    color: #991B1B;
}

.dates-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.date-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.date-label {
    font-size: 12px;
    color: #6B7280;
}

.date-value {
    font-size: 14px;
    color: #111827;
    font-weight: 500;
}

/* Card Titles */
.card-title {
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    padding-bottom: 12px;
    border-bottom: 2px solid #F3F4F6;
}

/* Summary Card */
.summary-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
}

.row-label {
    font-size: 14px;
    color: #6B7280;
    font-weight: 500;
}

.row-value {
    font-size: 14px;
    color: #111827;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.role-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #F3F4F6;
    color: #6B7280;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
}

.text-muted {
    color: #9CA3AF;
    font-style: italic;
}

.summary-divider {
    height: 1px;
    background: #E5E7EB;
    margin: 8px 0;
}

.score-highlight {
    color: #800020;
    font-weight: 600;
    font-size: 16px;
}

.score-detail {
    color: #6B7280;
    font-size: 13px;
}

.detail-link {
    color: #800020;
    text-decoration: none;
    font-size: 13px;
    transition: opacity 0.2s;
}

.detail-link:hover {
    opacity: 0.8;
    text-decoration: underline;
}

/* Scoring Card */
.scoring-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.overall-score-display {
    text-align: center;
    padding: 20px;
    background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 100%);
    border-radius: 8px;
}

.score-number {
    font-size: 48px;
    font-weight: 700;
    color: #800020;
    line-height: 1;
    margin-bottom: 4px;
}

.score-label {
    font-size: 14px;
    color: #6B7280;
}

.rubric-breakdown {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rubric-heading {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #374151;
}

.rubric-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.rubric-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #F9FAFB;
    border-radius: 6px;
}

.rubric-name {
    font-size: 14px;
    color: #374151;
}

.rubric-score {
    font-size: 14px;
    font-weight: 600;
    color: #800020;
}

.recommendation-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #F9FAFB;
    border-radius: 8px;
}

.recommendation-label {
    font-size: 14px;
    color: #374151;
    font-weight: 500;
}

.recommendation-badge {
    padding: 6px 16px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
}

.recommendation-badge.recommendation-highly-recommended {
    background: #D1FAE5;
    color: #065F46;
}

.recommendation-badge.recommendation-recommended {
    background: #DBEAFE;
    color: #1E40AF;
}

.recommendation-badge.recommendation-conditional {
    background: #FEF3C7;
    color: #92400E;
}

.recommendation-badge.recommendation-not-recommended {
    background: #FEE2E2;
    color: #991B1B;
}

/* Notes Card */
.notes-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.note-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.note-heading {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #374151;
}

.note-text {
    padding: 16px;
    background: #F9FAFB;
    border-radius: 8px;
    color: #374151;
    line-height: 1.6;
    font-size: 14px;
    max-width: 800px;
}

.activity-log {
    margin-top: 8px;
}

.activity-heading {
    margin: 0 0 12px 0;
    font-size: 15px;
    font-weight: 600;
    color: #374151;
}

.activity-timeline {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.activity-item {
    display: flex;
    gap: 16px;
    padding: 8px 0;
    border-left: 2px solid #E5E7EB;
    padding-left: 16px;
}

.activity-time {
    font-size: 13px;
    color: #6B7280;
    min-width: 160px;
}

.activity-action {
    font-size: 14px;
    color: #374151;
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: 48px 24px;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.3;
}

.empty-text {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: #6B7280;
}

.empty-subtext {
    margin: 0;
    font-size: 14px;
    color: #9CA3AF;
}

/* Actions Bar */
.actions-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 20px 0;
    margin-top: 24px;
    border-top: 1px solid #E5E7EB;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    border: 1px solid transparent;
    cursor: pointer;
    display: inline-block;
}

.btn-primary {
    background: #800020;
    color: white;
    border-color: #800020;
}

.btn-primary:hover {
    background: #a00028;
    border-color: #a00028;
}

.btn-secondary {
    background: #F3F4F6;
    color: #374151;
    border-color: #E5E7EB;
}

.btn-secondary:hover {
    background: #E5E7EB;
}

.btn-outline {
    background: white;
    color: #800020;
    border-color: #800020;
}

.btn-outline:hover {
    background: #FFF7ED;
}

/* Responsive Design */
@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }

    .header-grid {
        flex-direction: column;
    }

    .header-status {
        text-align: left;
        width: 100%;
    }

    .actions-bar {
        flex-direction: column-reverse;
    }

    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>
@endpush

