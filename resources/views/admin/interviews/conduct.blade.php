@extends('layouts.admin')

@section('title', 'Conduct Interview - ' . $applicant->full_name)

@php
    $pageTitle = 'Conduct Interview';
    $pageSubtitle = $applicant->full_name . ' - ' . $applicant->application_no;
@endphp

@push('styles')
<style>
    .interview-layout {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .info-card {
        background: white;
        border: 1px solid #E5E5E5;
        border-radius: 6px;
        overflow: hidden;
    }

    .card-header {
        background: #FAFAFA;
        padding: 12px 16px;
        border-bottom: 1px solid #E5E5E5;
    }

    .card-header h3 {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: #333;
        letter-spacing: -0.01em;
    }

    .card-content {
        padding: 16px;
    }

    .applicant-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #F5F5F5;
    }

    .applicant-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--maroon-primary, #800020);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 600;
        flex-shrink: 0;
    }

    .applicant-name {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
    }

    .applicant-id {
        font-size: 0.8rem;
        color: #666;
    }

    .info-grid {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        font-size: 0.85rem;
    }

    .info-label {
        color: #666;
        font-weight: 500;
    }

    .info-value {
        color: #333;
        font-weight: 500;
        text-align: right;
    }

    .exam-score-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 50px;
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .exam-score-badge.good {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .exam-score-badge.warning {
        background: #FFF3E0;
        color: #E65100;
    }

    .exam-score-badge.poor {
        background: #FFEBEE;
        color: #C62828;
    }

    .evaluation-form {
        background: white;
        border: 1px solid #E5E5E5;
        border-radius: 6px;
        overflow: hidden;
    }

    .form-header {
        background: #FAFAFA;
        padding: 16px 20px;
        border-bottom: 1px solid #E5E5E5;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-header h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #333;
    }

    .score-display {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 4px 12px;
        background: white;
        border: 1px solid #E5E5E5;
        border-radius: 4px;
    }

    .score-label {
        font-size: 0.8rem;
        color: #666;
    }

    .score-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--maroon-primary, #800020);
    }

    .score-max {
        font-size: 0.9rem;
        color: #999;
    }

    .form-content {
        padding: 20px;
    }

    .form-section {
        margin-bottom: 28px;
    }

    .form-section:last-of-type {
        margin-bottom: 0;
    }

    .section-header {
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #E5E5E5;
    }

    .section-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #333;
    }

    .criteria-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }

    .criteria-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .criteria-label {
        display: block;
        font-weight: 500;
        color: #444;
        font-size: 0.85rem;
    }

    .criteria-description {
        font-size: 0.75rem;
        color: #666;
        margin-bottom: 4px;
    }

    .form-select,
    .form-textarea {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #D5D5D5;
        border-radius: 4px;
        font-size: 0.85rem;
        transition: all 0.2s;
        background: white;
    }

    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--maroon-primary, #800020);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.08);
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
        line-height: 1.5;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding-top: 20px;
        border-top: 1px solid #E5E5E5;
        margin-top: 24px;
    }

    .btn {
        padding: 11px 22px;
        border-radius: 4px;
        font-size: 0.9rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-primary {
        background: var(--maroon-primary, #800020);
        color: white;
    }

    .btn-primary:hover {
        background: #5C0016;
    }

    .btn-secondary {
        background: #666;
        color: white;
    }

    .btn-secondary:hover {
        background: #555;
    }

    .btn-outline {
        background: white;
        color: var(--maroon-primary, #800020);
        border: 1px solid var(--maroon-primary, #800020);
    }

    .btn-outline:hover {
        background: #F5F5F5;
    }

    .claimed-banner {
        background: #FFF3E0;
        border: 1px solid #FFB74D;
        color: #E65100;
        padding: 12px 16px;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 0.85rem;
    }

    @media (max-width: 1024px) {
        .interview-layout {
            grid-template-columns: 1fr;
        }
        
        .sidebar {
            order: 1;
        }
        
        .evaluation-form {
            order: 2;
        }
    }

    @media (max-width: 768px) {
        .criteria-grid {
            grid-template-columns: 1fr;
        }
        
        .applicant-header {
            flex-direction: column;
            text-align: center;
        }
        
        .form-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="header-content">
        <h1 class="page-title">{{ $pageTitle }}</h1>
        <p class="page-subtitle">{{ $pageSubtitle }}</p>
    </div>
</div>

@if($interview->claimed_by && $interview->claimed_by !== auth()->id() && $interview->claimed_at && $interview->claimed_at->gt(now()->subHour()))
    <div class="claimed-banner">
        This interview is currently being conducted by {{ \App\Models\User::find($interview->claimed_by)->full_name ?? 'another evaluator' }}.
        Started {{ $interview->claimed_at->diffForHumans() }}.
    </div>
@endif

<div class="interview-layout">
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Applicant Information -->
        <div class="info-card">
            <div class="card-header">
                <h3>Applicant Information</h3>
            </div>
            <div class="card-content">
                <div class="applicant-header">
                    <div class="applicant-avatar">
                        {{ substr($applicant->first_name, 0, 1) }}{{ substr($applicant->last_name, 0, 1) }}
                    </div>
                    <div>
                        <div class="applicant-name">{{ $applicant->first_name }} {{ $applicant->last_name }}</div>
                        <div class="applicant-id">{{ $applicant->application_no }}</div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $applicant->email_address }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value">{{ $applicant->phone_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Course</span>
                        <span class="info-value">{{ $applicant->preferred_course ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Exam Score</span>
                        <span class="info-value">
                            @php
                                $examScore = $applicant->enrollassess_score ?? $applicant->score ?? 0;
                                $scoreClass = $examScore >= 75 ? 'good' : ($examScore >= 60 ? 'warning' : 'poor');
                            @endphp
                            <span class="exam-score-badge {{ $scoreClass }}">{{ number_format($examScore, 1) }}%</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grading Guide -->
        @include('components.interview-grading-guide-bsit')
    </div>

    <!-- Evaluation Form -->
    <div class="evaluation-form">
        <div class="form-header">
            <h3>BSIT Admission Interview Evaluation</h3>
            <div class="score-display" id="liveScore">
                <div>
                    <div class="score-label">Total Score</div>
                </div>
                <div class="score-value" id="totalScore">{{ old('_total_score', $interview->overall_score ?? 0) }}</div>
                <span class="score-max">/100</span>
            </div>
        </div>
        <div class="form-content">
            <form method="POST" action="{{ route('admin.interviews.conduct.submit', $interview->interview_id) }}" id="evaluationForm">
                @csrf
                
                <!-- BSIT Rubric Criteria -->
                <div class="form-section">
                    <div class="section-header">
                        <h4 class="section-title">Interview Evaluation Criteria (10 points each)</h4>
                    </div>
                    <div class="criteria-grid">
                        <div class="criteria-item">
                            <label class="criteria-label">Communication Skills</label>
                            <p class="criteria-description">Clarity, confidence, and fluency with proper grammar</p>
                            <select name="communication_skills" class="form-select score-input" required>
                                <option value="">Select Score (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('communication_skills', $interview->communication_skills) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i >= 9 ? 'Excellent' : ($i >= 7 ? 'Good' : ($i >= 5 ? 'Fair' : ($i >= 3 ? 'Needs Improvement' : 'Insufficient'))) }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="criteria-item">
                            <label class="criteria-label">Motivation and Interest</label>
                            <p class="criteria-description">Clear and relevant goals in pursuing IT</p>
                            <select name="motivation_interest" class="form-select score-input" required>
                                <option value="">Select Score (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('motivation_interest', $interview->motivation_interest) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i >= 9 ? 'Excellent' : ($i >= 7 ? 'Good' : ($i >= 5 ? 'Fair' : ($i >= 3 ? 'Needs Improvement' : 'Insufficient'))) }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="criteria-item">
                            <label class="criteria-label">Problem-solving and Critical Thinking</label>
                            <p class="criteria-description">Demonstrates critical thinking and problem-solving attitude</p>
                            <select name="problem_solving_attitude" class="form-select score-input" required>
                                <option value="">Select Score (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('problem_solving_attitude', $interview->problem_solving_attitude) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i >= 9 ? 'Excellent' : ($i >= 7 ? 'Good' : ($i >= 5 ? 'Fair' : ($i >= 3 ? 'Needs Improvement' : 'Insufficient'))) }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="criteria-item">
                            <label class="criteria-label">Understanding of the Program</label>
                            <p class="criteria-description">Knowledge of BSIT curriculum and program content</p>
                            <select name="program_understanding" class="form-select score-input" required>
                                <option value="">Select Score (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('program_understanding', $interview->program_understanding) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i >= 9 ? 'Excellent' : ($i >= 7 ? 'Good' : ($i >= 5 ? 'Fair' : ($i >= 3 ? 'Needs Improvement' : 'Insufficient'))) }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="criteria-item">
                            <label class="criteria-label">Personality and Attitude</label>
                            <p class="criteria-description">Positive attitude, confidence, and potential</p>
                            <select name="personality_attitude" class="form-select score-input" required>
                                <option value="">Select Score (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('personality_attitude', $interview->personality_attitude) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i >= 9 ? 'Excellent' : ($i >= 7 ? 'Good' : ($i >= 5 ? 'Fair' : ($i >= 3 ? 'Needs Improvement' : 'Insufficient'))) }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="criteria-item">
                            <label class="criteria-label">IT Exposure / Background</label>
                            <p class="criteria-description">Experience or exposure to IT tools and concepts</p>
                            <select name="it_background" class="form-select score-input" required>
                                <option value="">Select Score (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('it_background', $interview->it_background) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i >= 9 ? 'Excellent' : ($i >= 7 ? 'Good' : ($i >= 5 ? 'Fair' : ($i >= 3 ? 'Needs Improvement' : 'Insufficient'))) }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="criteria-item">
                            <label class="criteria-label">Willingness to Learn</label>
                            <p class="criteria-description">Eagerness and commitment to learning new skills</p>
                            <select name="willingness_to_learn" class="form-select score-input" required>
                                <option value="">Select Score (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('willingness_to_learn', $interview->willingness_to_learn) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i >= 9 ? 'Excellent' : ($i >= 7 ? 'Good' : ($i >= 5 ? 'Fair' : ($i >= 3 ? 'Needs Improvement' : 'Insufficient'))) }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="criteria-item">
                            <label class="criteria-label">Overall Impression</label>
                            <p class="criteria-description">Overall candidate assessment and recommendation level</p>
                            <select name="overall_impression" class="form-select score-input" required>
                                <option value="">Select Score (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('overall_impression', $interview->overall_impression) == $i ? 'selected' : '' }}>{{ $i }} - {{ $i >= 9 ? 'Excellent' : ($i >= 7 ? 'Good' : ($i >= 5 ? 'Fair' : ($i >= 3 ? 'Needs Improvement' : 'Insufficient'))) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Recommendation -->
                <div class="form-section">
                    <div class="section-header">
                        <h4 class="section-title">Final Recommendation</h4>
                    </div>
                    <div class="criteria-item">
                        <label class="criteria-label">Recommendation</label>
                        <select name="recommendation" class="form-select" required>
                            <option value="">Select Recommendation</option>
                            <option value="highly_recommended" {{ old('recommendation', $interview->recommendation) == 'highly_recommended' ? 'selected' : '' }}>Highly Recommended (20 pts)</option>
                            <option value="recommended" {{ old('recommendation', $interview->recommendation) == 'recommended' ? 'selected' : '' }}>Recommended (10 pts)</option>
                            <option value="conditional" {{ old('recommendation', $interview->recommendation) == 'conditional' ? 'selected' : '' }}>Conditional (5 pts)</option>
                            <option value="not_recommended" {{ old('recommendation', $interview->recommendation) == 'not_recommended' ? 'selected' : '' }}>Not Recommended (0 pts)</option>
                        </select>
                    </div>
                </div>

                <!-- Written Feedback -->
                <div class="form-section">
                    <div class="section-header">
                        <h4 class="section-title">Final Comments</h4>
                    </div>
                    <div class="criteria-item">
                        <label class="criteria-label">Final Comments</label>
                        <textarea name="final_comments" class="form-textarea" required 
                                  placeholder="Provide your overall assessment, key observations, strengths, areas for improvement, and any additional notes about the applicant...">{{ old('final_comments', $interview->final_comments) }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.interviews.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="action" value="save_draft" class="btn btn-outline">Save Draft</button>
                    <button type="submit" name="action" value="submit_final" class="btn btn-primary">Submit Evaluation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scoreInputs = document.querySelectorAll('.score-input');
    const totalScoreEl = document.getElementById('totalScore');

    function calculateScore() {
        let total = 0;

        scoreInputs.forEach(input => {
            const value = parseInt(input.value) || 0;
            total += value;
        });

        totalScoreEl.textContent = total;
        
        // Update color based on total score (100 point scale)
        if (total >= 85) { // 85% of 100
            totalScoreEl.style.color = '#2E7D32'; // Green
        } else if (total >= 70) { // 70% of 100
            totalScoreEl.style.color = '#E65100'; // Orange
        } else if (total >= 50) { // 50% of 100
            totalScoreEl.style.color = '#F57C00'; // Yellow-orange
        } else {
            totalScoreEl.style.color = '#C62828'; // Red
        }
    }

    scoreInputs.forEach(input => {
        input.addEventListener('change', calculateScore);
    });

    // Initialize score on page load
    calculateScore();

    // Confirmation for final submission
    const form = document.getElementById('evaluationForm');
    form.addEventListener('submit', function(e) {
        const submitButton = e.submitter;
        
        if (submitButton && submitButton.value === 'submit_final') {
            const total = parseInt(totalScoreEl.textContent);
            const passing = total >= 70;
            const message = passing 
                ? `Submit final evaluation with a score of ${total}/100 points?\n\nThis score meets the passing requirement (70 points).\n\nThis action cannot be undone.`
                : `Submit final evaluation with a score of ${total}/100 points?\n\nWarning: This score is below the passing requirement (70 points).\n\nThis action cannot be undone.`;
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        }
    });
});
</script>
@endpush
@endsection
