@extends('layouts.instructor')

@section('title', 'Applicant Portfolio - ' . $applicant->full_name)

@php
    $pageTitle = 'Applicant Portfolio';
    $pageSubtitle = 'Comprehensive overview for interview preparation';
@endphp

@push('styles')
<link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
<style>
    .portfolio-layout {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 24px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .portfolio-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }

    .card-header {
        background: #F9FAFB;
        padding: 16px 20px;
        border-bottom: 1px solid #E5E7EB;
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
    }

    .card-body {
        padding: 20px;
    }

    .summary-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .avatar-lg {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--maroon-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .summary-header .name {
        margin: 0 0 8px 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
    }

    .muted {
        font-size: 0.875rem;
        color: #6B7280;
        margin: 2px 0;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }

    .info-item {
        padding: 12px;
        background: #F9FAFB;
        border-radius: 6px;
    }

    .info-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1F2937;
    }

    .exam-performance {
        margin-bottom: 24px;
    }

    .exam-performance h4 {
        margin: 0 0 12px 0;
        font-size: 1rem;
        font-weight: 600;
        color: #1F2937;
    }

    .score-display {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
    }

    .score-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .score-circle.good {
        background: #D1FAE5;
        color: #059669;
    }

    .score-circle.needs-improvement {
        background: #FEE2E2;
        color: #DC2626;
    }

    .score-details p {
        margin: 2px 0;
        font-size: 0.75rem;
        color: #6B7280;
    }

    .interview-history {
        margin-bottom: 24px;
    }

    .interview-history h4 {
        margin: 0 0 12px 0;
        font-size: 1rem;
        font-weight: 600;
        color: #1F2937;
    }

    .interview-item {
        padding: 12px;
        background: #F9FAFB;
        border-radius: 6px;
        margin-bottom: 8px;
    }

    .interview-item:last-child {
        margin-bottom: 0;
    }

    .interview-date {
        font-size: 0.75rem;
        color: #6B7280;
        margin-bottom: 4px;
    }

    .interview-score {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1F2937;
    }

    .main-content-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }

    .section-header {
        background: #F9FAFB;
        padding: 20px 24px;
        border-bottom: 1px solid #E5E7EB;
    }

    .section-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
    }

    .section-content {
        padding: 24px;
    }

    .exam-details {
        margin-bottom: 32px;
    }

    .exam-details h4 {
        margin: 0 0 16px 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--maroon-primary);
    }

    .exam-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #F9FAFB;
        padding: 16px;
        border-radius: 6px;
        text-align: center;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--maroon-primary);
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .question-analysis {
        margin-bottom: 32px;
    }

    .question-analysis h4 {
        margin: 0 0 16px 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--maroon-primary);
    }

    .question-item {
        padding: 16px;
        border: 1px solid #E5E7EB;
        border-radius: 6px;
        margin-bottom: 12px;
    }

    .question-item:last-child {
        margin-bottom: 0;
    }

    .question-text {
        font-size: 0.875rem;
        color: #1F2937;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .question-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        color: #6B7280;
    }

    .correct-answer {
        color: #059669;
        font-weight: 500;
    }

    .incorrect-answer {
        color: #DC2626;
        font-weight: 500;
    }

    .interview-preparation {
        margin-bottom: 32px;
    }

    .interview-preparation h4 {
        margin: 0 0 16px 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--maroon-primary);
    }

    .preparation-tips {
        background: #F0F9FF;
        border: 1px solid #BAE6FD;
        border-radius: 6px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .preparation-tips h5 {
        margin: 0 0 8px 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1E40AF;
    }

    .preparation-tips ul {
        margin: 0;
        padding-left: 16px;
        font-size: 0.875rem;
        color: #1E40AF;
    }

    .preparation-tips li {
        margin-bottom: 4px;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #E5E7EB;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: var(--maroon-primary);
        color: white;
    }

    .btn-primary:hover {
        background: #5C0016;
        color: white;
    }

    .btn-secondary {
        background: #6B7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4B5563;
        color: white;
    }

    @media (max-width: 768px) {
        .portfolio-layout {
            grid-template-columns: 1fr;
        }
        
        .summary-header {
            flex-direction: column;
            text-align: center;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .exam-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="portfolio-layout">
    <!-- Left: Applicant summary -->
    <section class="portfolio-card">
        <div class="card-header"><h3>👤 Applicant Summary</h3></div>
        <div class="card-body">
            <div class="summary-header">
                <div class="avatar-lg">{{ $applicant->initials }}</div>
                <div>
                    <h2 class="name">{{ $applicant->full_name }}</h2>
                    <div class="muted">Application No: {{ $applicant->application_no }}</div>
                    <div class="muted">Email: {{ $applicant->email_address }}</div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ $applicant->phone_number ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Education</div>
                    <div class="info-value">{{ $applicant->education_background ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Preferred Course</div>
                    <div class="info-value">{{ $applicant->preferred_course ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Application Date</div>
                    <div class="info-value">{{ $applicant->created_at->format('M d, Y') }}</div>
                </div>
            </div>

            <div class="exam-performance">
                <h4>📊 Exam Performance</h4>
                @if($examStats['total_questions'] > 0)
                    <div class="score-display">
                        <div class="score-circle {{ $examStats['percentage'] >= 70 ? 'good' : 'needs-improvement' }}">
                            {{ number_format($examStats['percentage'], 1) }}%
                        </div>
                        <div class="score-details">
                            <p><strong>Correct:</strong> {{ $examStats['correct'] }}/{{ $examStats['total_questions'] }}</p>
                            <p><strong>Exam Set:</strong> {{ $applicant->examSet->name ?? 'N/A' }}</p>
                            <p><strong>Completed:</strong> {{ $applicant->exam_completed_at ? $applicant->exam_completed_at->format('M d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                @else
                    <p class="muted">Exam not completed yet</p>
                @endif
            </div>

            <div class="interview-history">
                <h4>📝 Interview History</h4>
                @if($latestInterview)
                    <div class="interview-item">
                        <div class="interview-date">
                            {{ $latestInterview->schedule_date ? $latestInterview->schedule_date->format('M d, Y g:i A') : 'N/A' }}
                        </div>
                        <div class="interview-score">
                            Score: {{ $latestInterview->overall_score ?? 'N/A' }}%
                        </div>
                    </div>
                @else
                    <p class="muted">No interviews conducted yet</p>
                @endif
            </div>
        </div>
    </section>

    <!-- Right: Detailed analysis -->
    <section class="main-content-section">
        <div class="section-header">
            <h3>📋 Detailed Analysis</h3>
        </div>
        <div class="section-content">
            <!-- Exam Details -->
            <div class="exam-details">
                <h4>Exam Performance Breakdown</h4>
                <div class="exam-stats">
                    <div class="stat-card">
                        <div class="stat-value">{{ $examStats['total_questions'] }}</div>
                        <div class="stat-label">Total Questions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $examStats['correct'] }}</div>
                        <div class="stat-label">Correct Answers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $examStats['total_questions'] - $examStats['correct'] }}</div>
                        <div class="stat-label">Incorrect Answers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($examStats['percentage'], 1) }}%</div>
                        <div class="stat-label">Overall Score</div>
                    </div>
                </div>
            </div>

            <!-- Question Analysis -->
            <div class="question-analysis">
                <h4>Question-by-Question Analysis</h4>
                @if($applicant->results->count() > 0)
                    @foreach($applicant->results->take(10) as $result)
                        <div class="question-item">
                            <div class="question-text">
                                {{ $result->question->question_text ?? 'Question not available' }}
                            </div>
                            <div class="question-meta">
                                <span class="{{ $result->is_correct ? 'correct-answer' : 'incorrect-answer' }}">
                                    {{ $result->is_correct ? '✓ Correct' : '✗ Incorrect' }}
                                </span>
                                <span>Question {{ $loop->iteration }}</span>
                            </div>
                        </div>
                    @endforeach
                    @if($applicant->results->count() > 10)
                        <p class="muted">... and {{ $applicant->results->count() - 10 }} more questions</p>
                    @endif
                @else
                    <p class="muted">No exam results available</p>
                @endif
            </div>

            <!-- Interview Preparation -->
            <div class="interview-preparation">
                <h4>Interview Preparation Notes</h4>
                <div class="preparation-tips">
                    <h5>Key Areas to Focus On:</h5>
                    <ul>
                        @if($examStats['percentage'] < 70)
                            <li>Review fundamental concepts where applicant struggled</li>
                            <li>Focus on problem-solving approach and methodology</li>
                        @else
                            <li>Explore advanced topics and real-world applications</li>
                            <li>Assess critical thinking and creativity</li>
                        @endif
                        <li>Evaluate communication skills and confidence</li>
                        <li>Discuss career goals and motivation</li>
                    </ul>
                </div>
            </div>

            <div class="action-buttons">
                <a href="{{ route('instructor.applicants') }}" class="btn btn-secondary">
                    ← Back to Applicants
                </a>
                @if(!$latestInterview || $latestInterview->status !== 'completed')
                    <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" class="btn btn-primary">
                        Conduct Interview
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection