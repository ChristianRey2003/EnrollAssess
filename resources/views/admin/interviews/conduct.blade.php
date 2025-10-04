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

    .btn-outline {
        background: transparent;
        color: var(--maroon-primary);
        border: 1px solid var(--maroon-primary);
    }

    .btn-outline:hover {
        background: var(--maroon-primary);
        color: white;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #E5E7EB;
    }

    .admin-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        background: #FEE2E2;
        color: #DC2626;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
        margin-left: 8px;
    }

    .claimed-banner {
        background: #FEF3C7;
        border: 1px solid #F59E0B;
        color: #92400E;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 24px;
        font-size: 0.875rem;
    }

    .live-total {
        position: sticky;
        top: 20px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 24px;
    }

    .live-total h5 {
        margin: 0 0 12px 0;
        font-size: 1rem;
        font-weight: 600;
        color: #1F2937;
    }

    .total-display {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .total-score {
        font-size: 2rem;
        font-weight: 700;
        color: var(--maroon-primary);
    }

    .total-breakdown {
        font-size: 0.875rem;
        color: #6B7280;
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

        .form-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="header-content">
        <h1 class="page-title">
            {{ $pageTitle }}
            <span class="admin-badge">
                <i class="fas fa-user-shield"></i>
                Admin Evaluation
            </span>
        </h1>
        <p class="page-subtitle">{{ $pageSubtitle }}</p>
    </div>
</div>

@if($interview->claimed_by && $interview->claimed_by !== auth()->id())
    <div class="claimed-banner">
        <i class="fas fa-info-circle"></i>
        This interview is currently being conducted by {{ \App\Models\User::find($interview->claimed_by)->full_name }}.
        Started {{ $interview->claimed_at->diffForHumans() }}.
    </div>
@endif

<div class="interview-layout">
    <!-- Applicant Information Panel -->
    <div class="applicant-panel">
        <div class="panel-header">
            <h3>Applicant Information</h3>
        </div>
        <div class="panel-content">
            <div class="applicant-summary">
                <div class="applicant-avatar-large">
                    {{ substr($applicant->first_name, 0, 1) }}{{ substr($applicant->last_name, 0, 1) }}
                </div>
                <div class="applicant-details">
                    <h4>{{ $applicant->first_name }} {{ $applicant->middle_name }} {{ $applicant->last_name }}</h4>
                    <p><strong>Application No:</strong> {{ $applicant->application_no }}</p>
                    <p><strong>Email:</strong> {{ $applicant->email_address }}</p>
                    <p><strong>Phone:</strong> {{ $applicant->phone_number }}</p>
                    <p><strong>Preferred Course:</strong> {{ $applicant->preferred_course }}</p>
                </div>
            </div>

            <div class="exam-performance">
                <h5>Exam Performance</h5>
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

            <div class="evaluation-guidelines">
                <h5>Evaluation Guidelines</h5>
                <div class="guidelines-content">
                    <div class="guideline-item">
                        <strong>Technical Skills (40 points)</strong>
                        <br>Programming, Problem Solving, Algorithms, System Design
                    </div>
                    <div class="guideline-item">
                        <strong>Communication Skills (30 points)</strong>
                        <br>Clarity, Listening, Confidence
                    </div>
                    <div class="guideline-item">
                        <strong>Analytical Thinking (30 points)</strong>
                        <br>Critical Thinking, Creativity, Attention to Detail
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Form -->
    <div class="evaluation-form">
        <div class="form-header">
            <h3>Interview Evaluation Form</h3>
        </div>
        <div class="form-content">
            <!-- Live Total Display -->
            <div class="live-total" id="liveTotalDisplay">
                <h5>Current Score</h5>
                <div class="total-display">
                    <div class="total-score" id="totalScore">0</div>
                    <div class="total-breakdown">
                        <div>Technical: <span id="technicalTotal">0</span>/40</div>
                        <div>Communication: <span id="communicationTotal">0</span>/30</div>
                        <div>Analytical: <span id="analyticalTotal">0</span>/30</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.interviews.conduct.submit', $interview->interview_id) }}" id="evaluationForm">
                @csrf
                
                <!-- Technical Skills Section -->
                <div class="form-section">
                    <h4>Technical Skills (40 points)</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Programming Knowledge (0-10)</label>
                            <select name="technical_programming" class="form-select score-input" data-section="technical" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('technical_programming') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('technical_programming')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Problem Solving (0-10)</label>
                            <select name="technical_problem_solving" class="form-select score-input" data-section="technical" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('technical_problem_solving') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('technical_problem_solving')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Algorithm Understanding (0-10)</label>
                            <select name="technical_algorithms" class="form-select score-input" data-section="technical" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('technical_algorithms') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('technical_algorithms')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">System Design (0-10)</label>
                            <select name="technical_system_design" class="form-select score-input" data-section="technical" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('technical_system_design') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('technical_system_design')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Communication Skills Section -->
                <div class="form-section">
                    <h4>Communication Skills (30 points)</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Clarity of Expression (0-10)</label>
                            <select name="communication_clarity" class="form-select score-input" data-section="communication" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('communication_clarity') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('communication_clarity')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Active Listening (0-10)</label>
                            <select name="communication_listening" class="form-select score-input" data-section="communication" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('communication_listening') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('communication_listening')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confidence (0-10)</label>
                            <select name="communication_confidence" class="form-select score-input" data-section="communication" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('communication_confidence') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('communication_confidence')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Analytical Thinking Section -->
                <div class="form-section">
                    <h4>Analytical Thinking (30 points)</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Critical Thinking (0-10)</label>
                            <select name="analytical_critical_thinking" class="form-select score-input" data-section="analytical" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('analytical_critical_thinking') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('analytical_critical_thinking')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Creativity (0-10)</label>
                            <select name="analytical_creativity" class="form-select score-input" data-section="analytical" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('analytical_creativity') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('analytical_creativity')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Attention to Detail (0-10)</label>
                            <select name="analytical_attention_detail" class="form-select score-input" data-section="analytical" required>
                                <option value="">Select Score</option>
                                @for($i = 0; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('analytical_attention_detail') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('analytical_attention_detail')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
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
                                <option value="excellent" {{ old('overall_rating') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                <option value="very_good" {{ old('overall_rating') == 'very_good' ? 'selected' : '' }}>Very Good</option>
                                <option value="good" {{ old('overall_rating') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="satisfactory" {{ old('overall_rating') == 'satisfactory' ? 'selected' : '' }}>Satisfactory</option>
                                <option value="needs_improvement" {{ old('overall_rating') == 'needs_improvement' ? 'selected' : '' }}>Needs Improvement</option>
                            </select>
                            @error('overall_rating')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
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
                            @error('recommendation')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Written Feedback Section -->
                <div class="form-section">
                    <h4>Written Feedback</h4>
                    <div class="form-group">
                        <label class="form-label">Strengths</label>
                        <textarea name="strengths" class="form-textarea" required 
                                  placeholder="Describe the applicant's key strengths and positive attributes...">{{ old('strengths') }}</textarea>
                        @error('strengths')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Areas for Improvement</label>
                        <textarea name="areas_improvement" class="form-textarea" required 
                                  placeholder="Identify specific areas where the applicant can improve...">{{ old('areas_improvement') }}</textarea>
                        @error('areas_improvement')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Interview Notes (Optional)</label>
                        <textarea name="interview_notes" class="form-textarea" 
                                  placeholder="Additional observations, comments, or notes from the interview...">{{ old('interview_notes') }}</textarea>
                        @error('interview_notes')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.interviews.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" name="action" value="save_draft" class="btn btn-outline">
                        <i class="fas fa-save"></i>
                        Save Draft
                    </button>
                    <button type="submit" name="action" value="submit_final" class="btn btn-primary">
                        <i class="fas fa-check"></i>
                        Submit Final Evaluation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scoreInputs = document.querySelectorAll('.score-input');
    const totalScoreElement = document.getElementById('totalScore');
    const technicalTotalElement = document.getElementById('technicalTotal');
    const communicationTotalElement = document.getElementById('communicationTotal');
    const analyticalTotalElement = document.getElementById('analyticalTotal');

    function calculateTotals() {
        let technicalTotal = 0;
        let communicationTotal = 0;
        let analyticalTotal = 0;

        scoreInputs.forEach(input => {
            const value = parseInt(input.value) || 0;
            const section = input.dataset.section;

            switch(section) {
                case 'technical':
                    technicalTotal += value;
                    break;
                case 'communication':
                    communicationTotal += value;
                    break;
                case 'analytical':
                    analyticalTotal += value;
                    break;
            }
        });

        const grandTotal = technicalTotal + communicationTotal + analyticalTotal;

        // Update display
        technicalTotalElement.textContent = technicalTotal;
        communicationTotalElement.textContent = communicationTotal;
        analyticalTotalElement.textContent = analyticalTotal;
        totalScoreElement.textContent = grandTotal;

        // Update color based on score
        totalScoreElement.style.color = grandTotal >= 75 ? '#059669' : grandTotal >= 50 ? '#D97706' : '#DC2626';
    }

    // Add event listeners to all score inputs
    scoreInputs.forEach(input => {
        input.addEventListener('change', calculateTotals);
    });

    // Form submission confirmation
    const form = document.getElementById('evaluationForm');
    form.addEventListener('submit', function(e) {
        const submitButton = e.submitter;
        
        if (submitButton.value === 'submit_final') {
            const totalScore = parseInt(totalScoreElement.textContent);
            const confirmation = confirm(
                `Are you sure you want to submit the final evaluation?\n\n` +
                `Total Score: ${totalScore}/100 (${totalScore}%)\n\n` +
                `This action cannot be undone.`
            );
            
            if (!confirmation) {
                e.preventDefault();
            }
        }
    });

    // Auto-save draft every 2 minutes (optional)
    setInterval(function() {
        const formData = new FormData(form);
        formData.append('action', 'save_draft');
        
        // Only auto-save if there's some content
        const hasContent = Array.from(formData.values()).some(value => value.trim() !== '');
        
        if (hasContent) {
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(error => {
                console.log('Auto-save failed:', error);
            });
        }
    }, 120000); // 2 minutes

    // Initial calculation
    calculateTotals();
});
</script>
@endpush
@endsection
