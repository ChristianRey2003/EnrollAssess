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
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--maroon-primary, #800020);
    }

    .score-breakdown {
        font-size: 0.75rem;
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
        display: flex;
        justify-content: space-between;
        align-items: center;
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

    .section-weight {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--maroon-primary, #800020);
        background: #F5F5F5;
        padding: 3px 10px;
        border-radius: 3px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #444;
        font-size: 0.85rem;
    }

    .form-select,
    .form-textarea,
    .form-input {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #D5D5D5;
        border-radius: 4px;
        font-size: 0.85rem;
        transition: all 0.2s;
        background: white;
    }

    .form-select:focus,
    .form-textarea:focus,
    .form-input:focus {
        outline: none;
        border-color: var(--maroon-primary, #800020);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.08);
    }

    .form-textarea {
        resize: vertical;
        min-height: 90px;
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
        padding: 10px 20px;
        border-radius: 4px;
        font-size: 0.85rem;
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
        background: var(--maroon-primary, #800020);
        color: white;
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
        .form-grid {
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

@if($interview->claimed_by && $interview->claimed_by !== auth()->id())
    <div class="claimed-banner">
        This interview is currently being conducted by {{ \App\Models\User::find($interview->claimed_by)->full_name }}.
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
                            @if($applicant->score)
                                @php
                                    $scoreClass = $applicant->score >= 75 ? 'good' : ($applicant->score >= 60 ? 'warning' : 'poor');
                                @endphp
                                <span class="exam-score-badge {{ $scoreClass }}">{{ number_format($applicant->score, 1) }}%</span>
                            @else
                                <span class="exam-score-badge poor">N/A</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grading Guide -->
        @include('components.interview-grading-guide')
    </div>

    <!-- Evaluation Form -->
    <div class="evaluation-form">
        <div class="form-header">
            <h3>Interview Evaluation Form</h3>
            <div class="score-display" id="liveScore">
                <div>
                    <div class="score-label">Total Score</div>
                    <div class="score-breakdown" id="scoreBreakdown">T: 0 | C: 0 | A: 0</div>
                </div>
                <div class="score-value" id="totalScore">0</div>
            </div>
        </div>
        <div class="form-content">
            <form method="POST" action="{{ route('admin.interviews.conduct.submit', $interview->interview_id) }}" id="evaluationForm">
                @csrf
                
                <!-- Technical Skills Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h4 class="section-title">Technical Skills</h4>
                        <span class="section-weight">40 points</span>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Programming Knowledge</label>
                            <select name="technical_programming" class="form-select score-input" data-category="technical" required>
                                <option value="">Select (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('technical_programming') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Problem Solving</label>
                            <select name="technical_problem_solving" class="form-select score-input" data-category="technical" required>
                                <option value="">Select (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('technical_problem_solving') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Algorithm Understanding</label>
                            <select name="technical_algorithms" class="form-select score-input" data-category="technical" required>
                                <option value="">Select (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('technical_algorithms') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">System Design</label>
                            <select name="technical_system_design" class="form-select score-input" data-category="technical" required>
                                <option value="">Select (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('technical_system_design') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Communication Skills Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h4 class="section-title">Communication Skills</h4>
                        <span class="section-weight">30 points</span>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Clarity of Expression</label>
                            <select name="communication_clarity" class="form-select score-input" data-category="communication" required>
                                <option value="">Select (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('communication_clarity') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Active Listening</label>
                            <select name="communication_listening" class="form-select score-input" data-category="communication" required>
                                <option value="">Select (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('communication_listening') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confidence</label>
                            <select name="communication_confidence" class="form-select score-input" data-category="communication" required>
                                <option value="">Select (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('communication_confidence') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Analytical Thinking Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h4 class="section-title">Analytical Thinking</h4>
                        <span class="section-weight">30 points</span>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Critical Thinking</label>
                            <select name="analytical_critical_thinking" class="form-select score-input" data-category="analytical" required>
                                <option value="">Select (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('analytical_critical_thinking') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Creativity</label>
                            <select name="analytical_creativity" class="form-select score-input" data-category="analytical" required>
                                <option value="">Select (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('analytical_creativity') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Attention to Detail</label>
                            <select name="analytical_attention_detail" class="form-select score-input" data-category="analytical" required>
                                <option value="">Select (0-10)</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('analytical_attention_detail') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Overall Assessment Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h4 class="section-title">Overall Assessment</h4>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Overall Rating</label>
                            <select name="overall_rating" class="form-select" required>
                                <option value="">Select Rating</option>
                                <option value="excellent" {{ old('overall_rating') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                <option value="very_good" {{ old('overall_rating') == 'very_good' ? 'selected' : '' }}>Very Good</option>
                                <option value="good" {{ old('overall_rating') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="satisfactory" {{ old('overall_rating') == 'satisfactory' ? 'selected' : '' }}>Satisfactory</option>
                                <option value="needs_improvement" {{ old('overall_rating') == 'needs_improvement' ? 'selected' : '' }}>Needs Improvement</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Recommendation</label>
                            <select name="recommendation" class="form-select" required>
                                <option value="">Select Recommendation</option>
                                <option value="highly_recommended" {{ old('recommendation') == 'highly_recommended' ? 'selected' : '' }}>Highly Recommended</option>
                                <option value="recommended" {{ old('recommendation') == 'recommended' ? 'selected' : '' }}>Recommended</option>
                                <option value="conditional" {{ old('recommendation') == 'conditional' ? 'selected' : '' }}>Conditional</option>
                                <option value="not_recommended" {{ old('recommendation') == 'not_recommended' ? 'selected' : '' }}>Not Recommended</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Written Feedback Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h4 class="section-title">Written Feedback</h4>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Strengths</label>
                        <textarea name="strengths" class="form-textarea" required 
                                  placeholder="Describe the applicant's key strengths and positive attributes...">{{ old('strengths') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Areas for Improvement</label>
                        <textarea name="areas_improvement" class="form-textarea" required 
                                  placeholder="Identify specific areas where the applicant can improve...">{{ old('areas_improvement') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Interview Notes (Optional)</label>
                        <textarea name="interview_notes" class="form-textarea" 
                                  placeholder="Additional observations, comments, or notes from the interview...">{{ old('interview_notes') }}</textarea>
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
    const scoreBreakdownEl = document.getElementById('scoreBreakdown');

    function calculateScores() {
        let technical = 0, communication = 0, analytical = 0;

        scoreInputs.forEach(input => {
            const value = parseInt(input.value) || 0;
            const category = input.dataset.category;
            
            if (category === 'technical') technical += value;
            else if (category === 'communication') communication += value;
            else if (category === 'analytical') analytical += value;
        });

        const total = technical + communication + analytical;
        totalScoreEl.textContent = total;
        scoreBreakdownEl.textContent = `T: ${technical} | C: ${communication} | A: ${analytical}`;
        
        // Update color based on total
        if (total >= 85) totalScoreEl.style.color = '#2E7D32';
        else if (total >= 70) totalScoreEl.style.color = '#E65100';
        else totalScoreEl.style.color = '#C62828';
    }

    scoreInputs.forEach(input => {
        input.addEventListener('change', calculateScores);
    });

    // Initialize scores if old values exist
    calculateScores();

    // Confirmation for final submission
    const form = document.getElementById('evaluationForm');
    form.addEventListener('submit', function(e) {
        const submitButton = e.submitter;
        
        if (submitButton && submitButton.value === 'submit_final') {
            const total = parseInt(totalScoreEl.textContent);
            if (!confirm(`Submit final evaluation with score of ${total}/100?\n\nThis action cannot be undone.`)) {
                e.preventDefault();
            }
        }
    });
});
</script>
@endpush
@endsection
