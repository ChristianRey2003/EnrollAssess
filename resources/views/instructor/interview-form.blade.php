@extends('layouts.instructor')

@section('title', 'Interview Evaluation - ' . $applicant->full_name)

@php
    $pageTitle = 'Interview Evaluation';
    $pageSubtitle = $applicant->full_name . ' - ' . $applicant->application_no;
@endphp

@push('styles')
<link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
<style>
    .interview-layout {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 24px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .applicant-panel {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }

    .panel-header {
        background: #F9FAFB;
        padding: 16px 20px;
        border-bottom: 1px solid #E5E7EB;
    }

    .panel-header h3 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
    }

    .panel-content {
        padding: 20px;
    }

    .applicant-summary {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .applicant-avatar-large {
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

    .applicant-details h4 {
        margin: 0 0 8px 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
    }

    .applicant-details p {
        margin: 4px 0;
        font-size: 0.875rem;
        color: #6B7280;
    }

    .exam-performance, .evaluation-guidelines {
        margin-bottom: 24px;
    }

    .exam-performance h5, .evaluation-guidelines h5 {
        margin: 0 0 12px 0;
        font-size: 1rem;
        font-weight: 600;
        color: #1F2937;
    }

    .score-display {
        display: flex;
        align-items: center;
        gap: 16px;
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

    .guidelines-content {
        font-size: 0.875rem;
        color: #6B7280;
        line-height: 1.5;
    }

    .guideline-item {
        margin-bottom: 8px;
    }

    .evaluation-form {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }

    .form-header {
        background: #F9FAFB;
        padding: 20px 24px;
        border-bottom: 1px solid #E5E7EB;
    }

    .form-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
    }

    .form-content {
        padding: 24px;
    }

    .form-section {
        margin-bottom: 32px;
    }

    .form-section h4 {
        margin: 0 0 16px 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--maroon-primary);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 0.875rem;
    }

    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 0.875rem;
        transition: border-color 0.3s ease;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--maroon-primary);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
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

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #E5E7EB;
    }

    @media (max-width: 768px) {
        .interview-layout {
            grid-template-columns: 1fr;
        }
        
        .applicant-summary {
            flex-direction: column;
            text-align: center;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Help icon styling */
    .help-icon {
        display: inline-block;
        margin-left: 0.5rem;
        cursor: help;
        font-size: 0.9rem;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .help-icon:hover {
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="interview-layout">
    <!-- Applicant Information Panel -->
    <div class="applicant-panel">
        <div class="panel-header">
            <h3>👤 Applicant Information</h3>
        </div>
        <div class="panel-content">
            <div class="applicant-summary">
                <div class="applicant-avatar-large">
                    {{ substr($applicant->full_name, 0, 2) }}
                </div>
                <div class="applicant-details">
                    <h4>{{ $applicant->full_name }}</h4>
                    <p><strong>Application No:</strong> {{ $applicant->application_no }}</p>
                    <p><strong>Email:</strong> {{ $applicant->email_address }}</p>
                    <p><strong>Phone:</strong> {{ $applicant->phone_number }}</p>
                    <p><strong>Education:</strong> {{ $applicant->education_background }}</p>
                </div>
            </div>

            <div class="exam-performance">
                <h5>📊 Exam Performance</h5>
                @if($applicant->score)
                    <div class="score-display">
                        <div class="score-circle {{ $applicant->score >= 70 ? 'good' : 'needs-improvement' }}">
                            {{ number_format($applicant->score, 1) }}%
                        </div>
                        <div class="score-details">
                            <p><strong>Exam Set:</strong> {{ $applicant->examSet->name ?? 'N/A' }}</p>
                            <p><strong>Total Questions:</strong> {{ $applicant->examSet->total_questions ?? 'N/A' }}</p>
                            <p><strong>Completed:</strong> {{ $applicant->exam_completed_at ? $applicant->exam_completed_at->format('M d, Y g:i A') : 'N/A' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-muted">Exam not completed yet</p>
                @endif
            </div>

            <!-- Include Grading Guide -->
            @include('instructor.partials.grading-guide')
        </div>
    </div>

    <!-- Evaluation Form -->
    <div class="evaluation-form">
        <div class="form-header">
            <h3>📝 Interview Evaluation Form</h3>
        </div>
        <div class="form-content">
            <form method="POST" action="{{ route('instructor.interview.submit', $applicant->applicant_id) }}">
                @csrf
                
                <!-- Technical Skills Section -->
                <div class="form-section">
                    <h4>Technical Skills (40 points)</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                Programming Knowledge (0-10)
                                <span class="help-icon" title="Rate the candidate's understanding of programming fundamentals, syntax, and coding practices">ℹ️</span>
                            </label>
                            <select name="technical_programming" class="form-select" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    @php 
                                        $scoreKey = min($i, 5);
                                        $scoringScale = config('interview_rubric.scoring_scale', []);
                                        $scoreLabel = isset($scoringScale[$scoreKey]) ? $scoringScale[$scoreKey]['label'] : '';
                                    @endphp
                                    <option value="{{ $i }}">{{ $i }} - {{ $scoreLabel }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Problem Solving (0-10)
                                <span class="help-icon" title="Assess the candidate's approach to breaking down problems and finding solutions">ℹ️</span>
                            </label>
                            <select name="technical_problem_solving" class="form-select" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    @php 
                                        $scoreKey = min($i, 5);
                                        $scoringScale = config('interview_rubric.scoring_scale', []);
                                        $scoreLabel = isset($scoringScale[$scoreKey]) ? $scoringScale[$scoreKey]['label'] : '';
                                    @endphp
                                    <option value="{{ $i }}">{{ $i }} - {{ $scoreLabel }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Algorithm Understanding (0-10)
                                <span class="help-icon" title="Evaluate knowledge of algorithms, data structures, and computational thinking">ℹ️</span>
                            </label>
                            <select name="technical_algorithms" class="form-select" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    @php 
                                        $scoreKey = min($i, 5);
                                        $scoringScale = config('interview_rubric.scoring_scale', []);
                                        $scoreLabel = isset($scoringScale[$scoreKey]) ? $scoringScale[$scoreKey]['label'] : '';
                                    @endphp
                                    <option value="{{ $i }}">{{ $i }} - {{ $scoreLabel }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                System Design (0-10)
                                <span class="help-icon" title="Assess ability to design systems, architecture, and scalable solutions">ℹ️</span>
                            </label>
                            <select name="technical_system_design" class="form-select" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    @php 
                                        $scoreKey = min($i, 5);
                                        $scoringScale = config('interview_rubric.scoring_scale', []);
                                        $scoreLabel = isset($scoringScale[$scoreKey]) ? $scoringScale[$scoreKey]['label'] : '';
                                    @endphp
                                    <option value="{{ $i }}">{{ $i }} - {{ $scoreLabel }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Communication Skills Section -->
                <div class="form-section">
                    <h4>Communication Skills (30 points)</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                Clarity of Expression (0-10)
                                <span class="help-icon" title="Rate how clearly the candidate communicates ideas and explanations">ℹ️</span>
                            </label>
                            <select name="communication_clarity" class="form-select" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    @php 
                                        $scoreKey = min($i, 5);
                                        $scoringScale = config('interview_rubric.scoring_scale', []);
                                        $scoreLabel = isset($scoringScale[$scoreKey]) ? $scoringScale[$scoreKey]['label'] : '';
                                    @endphp
                                    <option value="{{ $i }}">{{ $i }} - {{ $scoreLabel }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Active Listening (0-10)
                                <span class="help-icon" title="Assess how well the candidate listens and responds to questions">ℹ️</span>
                            </label>
                            <select name="communication_listening" class="form-select" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    @php 
                                        $scoreKey = min($i, 5);
                                        $scoringScale = config('interview_rubric.scoring_scale', []);
                                        $scoreLabel = isset($scoringScale[$scoreKey]) ? $scoringScale[$scoreKey]['label'] : '';
                                    @endphp
                                    <option value="{{ $i }}">{{ $i }} - {{ $scoreLabel }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Confidence (0-10)
                                <span class="help-icon" title="Evaluate the candidate's confidence and composure during the interview">ℹ️</span>
                            </label>
                            <select name="communication_confidence" class="form-select" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    @php 
                                        $scoreKey = min($i, 5);
                                        $scoringScale = config('interview_rubric.scoring_scale', []);
                                        $scoreLabel = isset($scoringScale[$scoreKey]) ? $scoringScale[$scoreKey]['label'] : '';
                                    @endphp
                                    <option value="{{ $i }}">{{ $i }} - {{ $scoreLabel }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Analytical Thinking Section -->
                <div class="form-section">
                    <h4>Analytical Thinking (30 points)</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                Critical Thinking (0-10)
                                <span class="help-icon" title="Assess the candidate's ability to analyze and evaluate information objectively">ℹ️</span>
                            </label>
                            <select name="analytical_critical_thinking" class="form-select" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    @php 
                                        $scoreKey = min($i, 5);
                                        $scoringScale = config('interview_rubric.scoring_scale', []);
                                        $scoreLabel = isset($scoringScale[$scoreKey]) ? $scoringScale[$scoreKey]['label'] : '';
                                    @endphp
                                    <option value="{{ $i }}">{{ $i }} - {{ $scoreLabel }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Creativity (0-10)
                                <span class="help-icon" title="Rate the candidate's ability to think outside the box and propose innovative solutions">ℹ️</span>
                            </label>
                            <select name="analytical_creativity" class="form-select" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    @php 
                                        $scoreKey = min($i, 5);
                                        $scoringScale = config('interview_rubric.scoring_scale', []);
                                        $scoreLabel = isset($scoringScale[$scoreKey]) ? $scoringScale[$scoreKey]['label'] : '';
                                    @endphp
                                    <option value="{{ $i }}">{{ $i }} - {{ $scoreLabel }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Attention to Detail (0-10)
                                <span class="help-icon" title="Evaluate how carefully the candidate considers edge cases and small details">ℹ️</span>
                            </label>
                            <select name="analytical_attention_detail" class="form-select" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    @php 
                                        $scoreKey = min($i, 5);
                                        $scoringScale = config('interview_rubric.scoring_scale', []);
                                        $scoreLabel = isset($scoringScale[$scoreKey]) ? $scoringScale[$scoreKey]['label'] : '';
                                    @endphp
                                    <option value="{{ $i }}">{{ $i }} - {{ $scoreLabel }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Overall Assessment Section -->
                <div class="form-section">
                    <h4>Overall Assessment</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Overall Rating</label>
                            <select name="overall_rating" class="form-select" required>
                                <option value="">Select Rating</option>
                                <option value="excellent">Excellent</option>
                                <option value="very_good">Very Good</option>
                                <option value="good">Good</option>
                                <option value="satisfactory">Satisfactory</option>
                                <option value="needs_improvement">Needs Improvement</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Recommendation</label>
                            <select name="recommendation" class="form-select" required>
                                <option value="">Select Recommendation</option>
                                <option value="highly_recommended">Highly Recommended</option>
                                <option value="recommended">Recommended</option>
                                <option value="conditional">Conditional</option>
                                <option value="not_recommended">Not Recommended</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Written Feedback Section -->
                <div class="form-section">
                    <h4>Written Feedback</h4>
                    <div class="form-group">
                        <label class="form-label">Strengths</label>
                        <textarea name="strengths" class="form-textarea" required 
                                  placeholder="Describe the applicant's key strengths and positive attributes..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Areas for Improvement</label>
                        <textarea name="areas_improvement" class="form-textarea" required 
                                  placeholder="Identify specific areas where the applicant can improve..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Interview Notes (Optional)</label>
                        <textarea name="interview_notes" class="form-textarea" 
                                  placeholder="Additional observations, comments, or notes from the interview..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('instructor.applicants') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Submit Evaluation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection