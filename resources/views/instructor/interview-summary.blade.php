@extends('layouts.instructor')

@section('title', 'Interview Summary - ' . $applicant->full_name)

@php
    $pageTitle = 'Interview Summary';
    $pageSubtitle = $applicant->full_name . ' • ' . $applicant->application_no;
@endphp

@section('content')
<div class="interview-detail-container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('instructor.applicants') }}" class="breadcrumb-link">My Applicants</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Interview Summary</span>
    </nav>

    <!-- 1. Header Section -->
    <div class="detail-card header-card">
        <div class="header-grid">
            <div class="header-primary">
                <div class="applicant-avatar">
                    {{ $applicant->initials }}
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
            </div>
        </div>

        <!-- Interview Summary Section -->
        <div class="header-summary">
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

                @if($interview->schedule_date)
                    <div class="summary-row">
                        <span class="row-label">Scheduled</span>
                        <span class="row-value">{{ $interview->schedule_date->format('M d, Y • g:i A') }}</span>
                    </div>
                @endif

                @if($interview->status === 'completed' && $interview->updated_at)
                    <div class="summary-row">
                        <span class="row-label">Completed</span>
                        <span class="row-value">{{ $interview->updated_at->format('M d, Y • g:i A') }}</span>
                    </div>
                @endif

                @if($interview->status === 'completed' && $interview->overall_score !== null)
                    <div class="summary-divider"></div>
                    <div class="summary-row">
                        <span class="row-label">Interview Score</span>
                        <span class="row-value">
                            <span class="score-highlight">{{ number_format($interview->overall_score, 1) }}/80</span>
                            <span class="score-detail">({{ number_format(($interview->overall_score / 80) * 100, 1) }}%)</span>
                        </span>
                    </div>
                @endif

                @if($interview->recommendation)
                    <div class="summary-row">
                        <span class="row-label">Recommendation</span>
                        <span class="row-value">
                            <span class="recommendation-badge recommendation-{{ str_replace('_', '-', $interview->recommendation) }}">
                                {{ ucfirst(str_replace('_', ' ', $interview->recommendation)) }}
                            </span>
                        </span>
                    </div>
                @endif

                @if($applicant->card_tor_gwa)
                    <div class="summary-row">
                        <span class="row-label">CARD/TOR GWA</span>
                        <span class="row-value">{{ number_format($applicant->card_tor_gwa, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="detail-grid">
        <!-- Scoring and Rubric Section -->
        <div class="detail-card scoring-card full-width">
            <h3 class="card-title">Scoring and Rubric Breakdown</h3>
            
            @if($interview->status === 'completed' && $interview->overall_score !== null)
                <div class="scoring-content">
                    <div class="rubric-breakdown">
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

                    @if($interview->final_comments)
                        <div class="final-comments-section">
                            <h4 class="note-heading">Overall Notes</h4>
                            <div class="note-text">{{ $interview->final_comments }}</div>
                        </div>
                    @endif
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <p class="empty-text">Not evaluated yet</p>
                    <p class="empty-subtext">Interview scores will appear here once the evaluation is complete.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="actions-bar">
        <div class="actions-group">
            <a href="{{ route('instructor.applicants') }}" class="btn btn-secondary">
                Back to Applicants
            </a>
            @if($interview->status === 'completed' && $interview->interviewer_id === auth()->user()->user_id)
                <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" class="btn btn-outline">
                    Edit Evaluation
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.interview-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px 24px 0;
}

/* Breadcrumb */
.breadcrumb {
    display: flex;
    align-items: center;
    font-size: 14px;
    margin-bottom: 16px;
    margin-top: 0;
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

.header-summary {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #E5E7EB;
}

.header-summary .card-title {
    margin: 0 0 10px 0;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    padding-bottom: 6px;
    border-bottom: 2px solid #800020;
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
    gap: 6px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0px 0;
}

.row-label {
    font-size: 12px;
    color: #6B7280;
    font-weight: 500;
}

.row-value {
    font-size: 12px;
    color: #111827;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.role-badge {
    display: inline-block;
    padding: 2px 6px;
    background: #F3F4F6;
    color: #6B7280;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 500;
}

.text-muted {
    color: #9CA3AF;
    font-style: italic;
}

.summary-divider {
    height: 1px;
    background: #E5E7EB;
    margin: 4px 0;
}

.score-highlight {
    color: #800020;
    font-weight: 600;
    font-size: 13px;
}

.score-detail {
    color: #6B7280;
    font-size: 13px;
}

/* Scoring Card */
.scoring-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.rubric-breakdown {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rubric-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
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
    font-size: 12px;
    color: #374151;
}

.rubric-score {
    font-size: 12px;
    font-weight: 600;
    color: #800020;
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

.final-comments-section {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #E5E7EB;
}

.final-comments-section .note-heading {
    margin: 0 0 8px 0;
    font-size: 15px;
    font-weight: 600;
    color: #374151;
}

.final-comments-section .note-text {
    padding: 12px;
    background: #F9FAFB;
    border-radius: 8px;
    color: #374151;
    line-height: 1.6;
    font-size: 14px;
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
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    padding-top: 8px;
    padding-bottom: 0;
    margin-top: 8px;
}

.actions-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn {
    padding: 8px 14px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-primary {
    background: #800020;
    color: white;
    border: none;
}

.btn-primary:hover {
    background: #a00028;
    border: none;
}

.btn-secondary {
    background: #F3F4F6;
    color: #374151;
    border: none;
}

.btn-secondary:hover {
    background: #E5E7EB;
    border: none;
}

.btn-outline {
    background: white;
    color: #800020;
    border: none;
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

    .rubric-grid {
        grid-template-columns: 1fr;
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

