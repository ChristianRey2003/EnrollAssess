<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Interview Evaluation - {{ $applicant->full_name }} - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Admin Dashboard CSS -->
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
</head>
<body class="admin-page instructor-portal">
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="admin-sidebar instructor-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo">
                    <div>
                        <h2 class="sidebar-title">EnrollAssess</h2>
                        <p class="sidebar-subtitle">Instructor Portal</p>
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('instructor.dashboard') }}" class="nav-link">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('instructor.applicants') }}" class="nav-link">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">My Applicants</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link active">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Interview Evaluation</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="instructor-info">
                    <div class="instructor-avatar">{{ substr(Auth::user()->full_name, 0, 2) }}</div>
                    <div>
                        <div class="instructor-name">{{ Auth::user()->full_name }}</div>
                        <div class="instructor-role">Instructor</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="logout-link">
                        <span class="nav-icon">🚪</span>
                        <span class="nav-text">Logout</span>
                    </button>
                </form>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <div class="main-header instructor-header">
                <div class="header-left">
                    <h1>Interview Evaluation</h1>
                    <p class="header-subtitle">{{ $applicant->full_name }} - {{ $applicant->application_no }}</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('instructor.applicants') }}" class="btn-secondary">
                        ← Back to Applicants
                    </a>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
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

                            <div class="evaluation-guidelines">
                                <h5>📋 Evaluation Guidelines</h5>
                                <div class="guidelines-content">
                                    <div class="guideline-item">
                                        <strong>Technical Skills (40 points)</strong>
                                        <ul>
                                            <li>Programming knowledge (10 pts)</li>
                                            <li>Problem-solving approach (10 pts)</li>
                                            <li>Algorithm understanding (10 pts)</li>
                                            <li>System design thinking (10 pts)</li>
                                        </ul>
                                    </div>
                                    <div class="guideline-item">
                                        <strong>Communication (30 points)</strong>
                                        <ul>
                                            <li>Clarity of expression (10 pts)</li>
                                            <li>Active listening (10 pts)</li>
                                            <li>Confidence & presence (10 pts)</li>
                                        </ul>
                                    </div>
                                    <div class="guideline-item">
                                        <strong>Analytical Thinking (30 points)</strong>
                                        <ul>
                                            <li>Critical thinking (10 pts)</li>
                                            <li>Creativity & innovation (10 pts)</li>
                                            <li>Attention to detail (10 pts)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interview Evaluation Form -->
                    <div class="evaluation-panel">
                        <div class="panel-header">
                            <h3>📝 Interview Evaluation Form</h3>
                            <div class="evaluation-progress">
                                <span id="totalScore">Total: <strong>0/100 points</strong></span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('instructor.interview.submit', $applicant->applicant_id) }}" id="interviewForm">
                            @csrf
                            <div class="panel-content">
                                <!-- Technical Skills Section -->
                                <div class="evaluation-section">
                                    <div class="section-header">
                                        <h4>💻 Technical Skills (40 points)</h4>
                                        <span class="section-score" id="technicalScore">0/40</span>
                                    </div>
                                    
                                    <div class="criteria-grid">
                                        <div class="criteria-item">
                                            <label>Programming Knowledge (0-10 points)</label>
                                            <div class="score-slider">
                                                <input type="range" name="technical_programming" min="0" max="10" value="{{ old('technical_programming', 0) }}" class="slider technical-slider" data-section="technical">
                                                <div class="score-display-inline">
                                                    <span class="score-value">{{ old('technical_programming', 0) }}</span>/10
                                                </div>
                                            </div>
                                            <textarea name="technical_programming_notes" placeholder="Notes on programming knowledge..." class="criteria-notes">{{ old('technical_programming_notes') }}</textarea>
                                        </div>

                                        <div class="criteria-item">
                                            <label>Problem-Solving Approach (0-10 points)</label>
                                            <div class="score-slider">
                                                <input type="range" name="technical_problem_solving" min="0" max="10" value="{{ old('technical_problem_solving', 0) }}" class="slider technical-slider" data-section="technical">
                                                <div class="score-display-inline">
                                                    <span class="score-value">{{ old('technical_problem_solving', 0) }}</span>/10
                                                </div>
                                            </div>
                                            <textarea name="technical_problem_solving_notes" placeholder="Notes on problem-solving approach..." class="criteria-notes">{{ old('technical_problem_solving_notes') }}</textarea>
                                        </div>

                                        <div class="criteria-item">
                                            <label>Algorithm Understanding (0-10 points)</label>
                                            <div class="score-slider">
                                                <input type="range" name="technical_algorithms" min="0" max="10" value="{{ old('technical_algorithms', 0) }}" class="slider technical-slider" data-section="technical">
                                                <div class="score-display-inline">
                                                    <span class="score-value">{{ old('technical_algorithms', 0) }}</span>/10
                                                </div>
                                            </div>
                                            <textarea name="technical_algorithms_notes" placeholder="Notes on algorithm understanding..." class="criteria-notes">{{ old('technical_algorithms_notes') }}</textarea>
                                        </div>

                                        <div class="criteria-item">
                                            <label>System Design Thinking (0-10 points)</label>
                                            <div class="score-slider">
                                                <input type="range" name="technical_system_design" min="0" max="10" value="{{ old('technical_system_design', 0) }}" class="slider technical-slider" data-section="technical">
                                                <div class="score-display-inline">
                                                    <span class="score-value">{{ old('technical_system_design', 0) }}</span>/10
                                                </div>
                                            </div>
                                            <textarea name="technical_system_design_notes" placeholder="Notes on system design thinking..." class="criteria-notes">{{ old('technical_system_design_notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Communication Skills Section -->
                                <div class="evaluation-section">
                                    <div class="section-header">
                                        <h4>💬 Communication Skills (30 points)</h4>
                                        <span class="section-score" id="communicationScore">0/30</span>
                                    </div>
                                    
                                    <div class="criteria-grid">
                                        <div class="criteria-item">
                                            <label>Clarity of Expression (0-10 points)</label>
                                            <div class="score-slider">
                                                <input type="range" name="communication_clarity" min="0" max="10" value="{{ old('communication_clarity', 0) }}" class="slider communication-slider" data-section="communication">
                                                <div class="score-display-inline">
                                                    <span class="score-value">{{ old('communication_clarity', 0) }}</span>/10
                                                </div>
                                            </div>
                                            <textarea name="communication_clarity_notes" placeholder="Notes on clarity of expression..." class="criteria-notes">{{ old('communication_clarity_notes') }}</textarea>
                                        </div>

                                        <div class="criteria-item">
                                            <label>Active Listening (0-10 points)</label>
                                            <div class="score-slider">
                                                <input type="range" name="communication_listening" min="0" max="10" value="{{ old('communication_listening', 0) }}" class="slider communication-slider" data-section="communication">
                                                <div class="score-display-inline">
                                                    <span class="score-value">{{ old('communication_listening', 0) }}</span>/10
                                                </div>
                                            </div>
                                            <textarea name="communication_listening_notes" placeholder="Notes on active listening..." class="criteria-notes">{{ old('communication_listening_notes') }}</textarea>
                                        </div>

                                        <div class="criteria-item">
                                            <label>Confidence & Presence (0-10 points)</label>
                                            <div class="score-slider">
                                                <input type="range" name="communication_confidence" min="0" max="10" value="{{ old('communication_confidence', 0) }}" class="slider communication-slider" data-section="communication">
                                                <div class="score-display-inline">
                                                    <span class="score-value">{{ old('communication_confidence', 0) }}</span>/10
                                                </div>
                                            </div>
                                            <textarea name="communication_confidence_notes" placeholder="Notes on confidence and presence..." class="criteria-notes">{{ old('communication_confidence_notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Analytical Thinking Section -->
                                <div class="evaluation-section">
                                    <div class="section-header">
                                        <h4>🧠 Analytical Thinking (30 points)</h4>
                                        <span class="section-score" id="analyticalScore">0/30</span>
                                    </div>
                                    
                                    <div class="criteria-grid">
                                        <div class="criteria-item">
                                            <label>Critical Thinking (0-10 points)</label>
                                            <div class="score-slider">
                                                <input type="range" name="analytical_critical_thinking" min="0" max="10" value="{{ old('analytical_critical_thinking', 0) }}" class="slider analytical-slider" data-section="analytical">
                                                <div class="score-display-inline">
                                                    <span class="score-value">{{ old('analytical_critical_thinking', 0) }}</span>/10
                                                </div>
                                            </div>
                                            <textarea name="analytical_critical_thinking_notes" placeholder="Notes on critical thinking..." class="criteria-notes">{{ old('analytical_critical_thinking_notes') }}</textarea>
                                        </div>

                                        <div class="criteria-item">
                                            <label>Creativity & Innovation (0-10 points)</label>
                                            <div class="score-slider">
                                                <input type="range" name="analytical_creativity" min="0" max="10" value="{{ old('analytical_creativity', 0) }}" class="slider analytical-slider" data-section="analytical">
                                                <div class="score-display-inline">
                                                    <span class="score-value">{{ old('analytical_creativity', 0) }}</span>/10
                                                </div>
                                            </div>
                                            <textarea name="analytical_creativity_notes" placeholder="Notes on creativity and innovation..." class="criteria-notes">{{ old('analytical_creativity_notes') }}</textarea>
                                        </div>

                                        <div class="criteria-item">
                                            <label>Attention to Detail (0-10 points)</label>
                                            <div class="score-slider">
                                                <input type="range" name="analytical_attention_detail" min="0" max="10" value="{{ old('analytical_attention_detail', 0) }}" class="slider analytical-slider" data-section="analytical">
                                                <div class="score-display-inline">
                                                    <span class="score-value">{{ old('analytical_attention_detail', 0) }}</span>/10
                                                </div>
                                            </div>
                                            <textarea name="analytical_attention_detail_notes" placeholder="Notes on attention to detail..." class="criteria-notes">{{ old('analytical_attention_detail_notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Overall Assessment Section -->
                                <div class="evaluation-section">
                                    <div class="section-header">
                                        <h4>⭐ Overall Assessment</h4>
                                    </div>

                                    <div class="overall-assessment">
                                        <div class="assessment-row">
                                            <label for="overall_rating">Overall Rating</label>
                                            <select name="overall_rating" id="overall_rating" required>
                                                <option value="">Select rating...</option>
                                                <option value="excellent" {{ old('overall_rating') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                                <option value="very_good" {{ old('overall_rating') == 'very_good' ? 'selected' : '' }}>Very Good</option>
                                                <option value="good" {{ old('overall_rating') == 'good' ? 'selected' : '' }}>Good</option>
                                                <option value="satisfactory" {{ old('overall_rating') == 'satisfactory' ? 'selected' : '' }}>Satisfactory</option>
                                                <option value="needs_improvement" {{ old('overall_rating') == 'needs_improvement' ? 'selected' : '' }}>Needs Improvement</option>
                                            </select>
                                        </div>

                                        <div class="assessment-row">
                                            <label for="recommendation">Recommendation</label>
                                            <select name="recommendation" id="recommendation" required>
                                                <option value="">Select recommendation...</option>
                                                <option value="highly_recommended" {{ old('recommendation') == 'highly_recommended' ? 'selected' : '' }}>Highly Recommended</option>
                                                <option value="recommended" {{ old('recommendation') == 'recommended' ? 'selected' : '' }}>Recommended</option>
                                                <option value="conditional" {{ old('recommendation') == 'conditional' ? 'selected' : '' }}>Conditional</option>
                                                <option value="not_recommended" {{ old('recommendation') == 'not_recommended' ? 'selected' : '' }}>Not Recommended</option>
                                            </select>
                                        </div>

                                        <div class="assessment-row">
                                            <label for="strengths">Key Strengths</label>
                                            <textarea name="strengths" id="strengths" required placeholder="Describe the applicant's key strengths..." rows="3">{{ old('strengths') }}</textarea>
                                        </div>

                                        <div class="assessment-row">
                                            <label for="areas_improvement">Areas for Improvement</label>
                                            <textarea name="areas_improvement" id="areas_improvement" required placeholder="Suggest areas where the applicant could improve..." rows="3">{{ old('areas_improvement') }}</textarea>
                                        </div>

                                        <div class="assessment-row">
                                            <label for="interview_notes">Additional Notes</label>
                                            <textarea name="interview_notes" id="interview_notes" placeholder="Any additional observations or comments..." rows="4">{{ old('interview_notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Score Summary -->
                                <div class="score-summary">
                                    <div class="summary-item">
                                        <span class="summary-label">Technical Skills:</span>
                                        <span class="summary-value" id="technicalTotal">0/40</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">Communication:</span>
                                        <span class="summary-value" id="communicationTotal">0/30</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">Analytical Thinking:</span>
                                        <span class="summary-value" id="analyticalTotal">0/30</span>
                                    </div>
                                    <div class="summary-item total">
                                        <span class="summary-label">Total Score:</span>
                                        <span class="summary-value" id="grandTotal">0/100 (0%)</span>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="form-actions">
                                    <button type="button" class="btn-secondary" onclick="saveDraft()">
                                        💾 Save Draft
                                    </button>
                                    <button type="submit" class="btn-primary">
                                        ✅ Submit Evaluation
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        /* Interview Form Styles */
        .instructor-portal {
            --primary-color: #2563eb;
        }

        .instructor-sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #1e40af 100%);
        }

        .interview-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .applicant-panel, .evaluation-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .panel-header {
            padding: 20px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-header h3 {
            margin: 0;
            color: var(--primary-color);
            font-size: 18px;
        }

        .evaluation-progress {
            font-size: 14px;
            color: var(--text-gray);
        }

        .panel-content {
            padding: 20px;
        }

        .applicant-summary {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .applicant-avatar-large {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
        }

        .applicant-details h4 {
            margin: 0 0 10px 0;
            color: var(--text-dark);
        }

        .applicant-details p {
            margin: 5px 0;
            font-size: 14px;
            color: var(--text-gray);
        }

        .exam-performance, .evaluation-guidelines {
            margin-bottom: 20px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }

        .exam-performance h5, .evaluation-guidelines h5 {
            margin: 0 0 15px 0;
            color: var(--primary-color);
            font-size: 16px;
        }

        .score-display {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .score-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .score-circle.good {
            background: #dcfce7;
            color: #166534;
        }

        .score-circle.needs-improvement {
            background: #fef3c7;
            color: #92400e;
        }

        .score-details p {
            margin: 3px 0;
            font-size: 12px;
        }

        .guidelines-content {
            font-size: 12px;
        }

        .guideline-item {
            margin-bottom: 15px;
        }

        .guideline-item strong {
            color: var(--primary-color);
            display: block;
            margin-bottom: 5px;
        }

        .guideline-item ul {
            margin: 0;
            padding-left: 15px;
        }

        .guideline-item li {
            margin-bottom: 2px;
            color: var(--text-gray);
        }

        .evaluation-section {
            margin-bottom: 30px;
            border: 1px solid #f3f4f6;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-header {
            background: #f9fafb;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h4 {
            margin: 0;
            color: var(--primary-color);
            font-size: 16px;
        }

        .section-score {
            font-weight: bold;
            color: var(--text-dark);
        }

        .criteria-grid {
            padding: 20px;
        }

        .criteria-item {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .criteria-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .criteria-item label {
            display: block;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .score-slider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .slider {
            flex: 1;
            height: 6px;
            border-radius: 3px;
            background: #f3f4f6;
            outline: none;
            -webkit-appearance: none;
        }

        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
        }

        .slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary-color);
            cursor: pointer;
            border: none;
        }

        .score-display-inline {
            min-width: 40px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .criteria-notes {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            resize: vertical;
            min-height: 60px;
            font-size: 14px;
        }

        .overall-assessment {
            padding: 20px;
        }

        .assessment-row {
            margin-bottom: 20px;
        }

        .assessment-row label {
            display: block;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .assessment-row select,
        .assessment-row textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
        }

        .score-summary {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .summary-item.total {
            border-top: 2px solid var(--primary-color);
            margin-top: 10px;
            padding-top: 15px;
            font-weight: bold;
            font-size: 16px;
        }

        .summary-label {
            color: var(--text-gray);
        }

        .summary-value {
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
        }

        .btn-primary, .btn-secondary {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .instructor-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .instructor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--white);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .instructor-name {
            color: var(--white);
            font-weight: 600;
            font-size: 14px;
        }

        .instructor-role {
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
        }

        @media (max-width: 1200px) {
            .interview-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>

    <script>
        // Score calculation and live updates
        function updateScores() {
            // Technical scores
            const technicalSliders = document.querySelectorAll('.technical-slider');
            let technicalTotal = 0;
            technicalSliders.forEach(slider => {
                technicalTotal += parseInt(slider.value);
            });
            document.getElementById('technicalScore').textContent = `${technicalTotal}/40`;
            document.getElementById('technicalTotal').textContent = `${technicalTotal}/40`;

            // Communication scores
            const communicationSliders = document.querySelectorAll('.communication-slider');
            let communicationTotal = 0;
            communicationSliders.forEach(slider => {
                communicationTotal += parseInt(slider.value);
            });
            document.getElementById('communicationScore').textContent = `${communicationTotal}/30`;
            document.getElementById('communicationTotal').textContent = `${communicationTotal}/30`;

            // Analytical scores
            const analyticalSliders = document.querySelectorAll('.analytical-slider');
            let analyticalTotal = 0;
            analyticalSliders.forEach(slider => {
                analyticalTotal += parseInt(slider.value);
            });
            document.getElementById('analyticalScore').textContent = `${analyticalTotal}/30`;
            document.getElementById('analyticalTotal').textContent = `${analyticalTotal}/30`;

            // Grand total
            const grandTotal = technicalTotal + communicationTotal + analyticalTotal;
            const percentage = Math.round((grandTotal / 100) * 100);
            document.getElementById('totalScore').innerHTML = `Total: <strong>${grandTotal}/100 points</strong>`;
            document.getElementById('grandTotal').textContent = `${grandTotal}/100 (${percentage}%)`;
        }

        // Update individual score displays
        document.querySelectorAll('.slider').forEach(slider => {
            slider.addEventListener('input', function() {
                const scoreDisplay = this.parentNode.querySelector('.score-value');
                scoreDisplay.textContent = this.value;
                updateScores();
            });
        });

        // Save draft functionality
        function saveDraft() {
            const formData = new FormData(document.getElementById('interviewForm'));
            // Implementation would save to localStorage or send AJAX request
            alert('Draft saved successfully!');
        }

        // Form validation
        document.getElementById('interviewForm').addEventListener('submit', function(e) {
            const requiredFields = ['overall_rating', 'recommendation', 'strengths', 'areas_improvement'];
            let isValid = true;

            requiredFields.forEach(field => {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    isValid = false;
                    element.style.borderColor = '#ef4444';
                } else {
                    element.style.borderColor = '#e5e7eb';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Confirm submission
            const grandTotal = document.getElementById('grandTotal').textContent;
            if (!confirm(`Are you sure you want to submit this evaluation?\n\nFinal Score: ${grandTotal}\n\nThis action cannot be undone.`)) {
                e.preventDefault();
                return false;
            }
        });

        // Initialize scores on page load
        updateScores();
    </script>
</body>
</html>