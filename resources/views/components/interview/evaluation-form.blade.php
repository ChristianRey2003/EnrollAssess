@php
    // Define criteria with clean descriptions
    $criteriaData = [
        'communication_skills' => [
            'label' => 'Communication Skills',
            'excellent' => 'Expresses ideas clearly, confidently, and with proper grammar.',
            'good' => 'Mostly clear and confident with minor grammatical lapses.',
            'fair' => 'Some difficulty expressing ideas; noticeable grammatical errors.',
            'poor' => 'Struggles to convey thoughts; grammar errors affect clarity.'
        ],
        'motivation_interest' => [
            'label' => 'Motivation and Interest',
            'excellent' => 'Highly motivated with clear and relevant goals related to IT.',
            'good' => 'Shows good interest and relevant goals.',
            'fair' => 'Shows moderate interest; goals are vague.',
            'poor' => 'Lacks or has unclear interest or unclear goals.'
        ],
        'problem_solving_attitude' => [
            'label' => 'Problem-solving Attitude',
            'excellent' => 'Demonstrates strong analytical skill; eager to solve problems.',
            'good' => 'Shows good problem-solving awareness.',
            'fair' => 'Shows some signs of critical thinking.',
            'poor' => 'Lacks understanding or interest in problem-solving.'
        ],
        'program_understanding' => [
            'label' => 'Understanding of the Program',
            'excellent' => 'Clear understanding of curriculum and expectations.',
            'good' => 'Adequate understanding of program.',
            'fair' => 'Limited understanding of program contents.',
            'poor' => 'No clear understanding of the BSIT program.'
        ],
        'personality_attitude' => [
            'label' => 'Personality and Attitude',
            'excellent' => 'Pleasant attitude, respectful, and shows potential.',
            'good' => 'Generally positive and respectful.',
            'fair' => 'Attitude needs improvement.',
            'poor' => 'Negative or disrespectful attitude observed.'
        ],
        'it_background' => [
            'label' => 'IT Exposure/Background',
            'excellent' => 'Has strong background or exposure to IT tools/concepts.',
            'good' => 'Some background or exposure to IT tools.',
            'fair' => 'Minimal IT background.',
            'poor' => 'No exposure or understanding of IT-related topics.'
        ],
        'willingness_to_learn' => [
            'label' => 'Willingness to Learn',
            'excellent' => 'Eager and committed to learning new things.',
            'good' => 'Willing to learn with hesitation.',
            'fair' => 'May need encouragement or motivation to learning.',
            'poor' => 'Resistant or indifferent toward learning new concepts.'
        ],
        'overall_impression' => [
            'label' => 'Overall Impression',
            'excellent' => 'Excellent candidate; highly recommended.',
            'good' => 'Good candidate; recommended.',
            'fair' => 'Average candidate; conditionally recommended.',
            'poor' => 'Below average; recommended for deferment.'
        ]
    ];

    // Group criteria for better organization
    $criteriaGroups = [
        'core_competencies' => [
            'title' => 'Core Competencies',
            'criteria' => ['communication_skills', 'problem_solving_attitude']
        ],
        'fit_motivation' => [
            'title' => 'Fit & Motivation',
            'criteria' => ['motivation_interest', 'personality_attitude', 'willingness_to_learn']
        ],
        'technical_preparation' => [
            'title' => 'Technical Preparation',
            'criteria' => ['it_background', 'program_understanding', 'overall_impression']
        ]
    ];
@endphp

@push('styles')
<style>
    :root {
        --primary: #800020;
        --text-primary: #111827;
        --text-secondary: #6B7280;
        --border: #E5E7EB;
        --bg-light: #F9FAFB;
    }

    .applicant-header {
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 14px 18px;
        margin-bottom: 14px;
        max-width: 1600px;
        margin-left: auto;
        margin-right: auto;
    }

    .applicant-header-content {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .applicant-header-left {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .applicant-header-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .applicant-header-details {
        flex: 1;
        min-width: 0;
    }

    .applicant-header-name {
        margin: 0 0 4px 0;
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.3;
    }

    .applicant-header-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        color: var(--text-secondary);
        flex-wrap: wrap;
    }

    .applicant-header-meta-separator {
        color: var(--text-secondary);
    }

    .applicant-header-right {
        display: flex;
        align-items: center;
        gap: 24px;
        flex-shrink: 0;
    }

    .applicant-header-score-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: flex-end;
    }

    .applicant-header-score-label {
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .applicant-header-score-value {
        font-size: 0.9375rem;
        font-weight: 700;
    }

    .interview-container {
        max-width: 1600px;
        margin: 0 auto;
        padding-bottom: 20px;
    }

    .card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 6px;
        overflow: hidden;
    }

    .card-header {
        background: var(--bg-light);
        padding: 8px 12px;
        border-bottom: 1px solid var(--border);
    }

    .card-header h3 {
        margin: 0;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .card-body {
        padding: 12px;
    }

    .applicant-info {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #F3F4F6;
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        flex-shrink: 0;
    }

    .applicant-details h4 {
        margin: 0 0 2px 0;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .applicant-details p {
        margin: 0;
        font-size: 0.6875rem;
        color: var(--text-secondary);
    }

    .info-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
    }

    .info-label {
        color: var(--text-secondary);
        font-weight: 500;
    }

    .info-value {
        color: var(--text-primary);
        font-weight: 500;
        text-align: right;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-success {
        background: #D1FAE5;
        color: #065F46;
    }

    .badge-warning {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-danger {
        background: #FEE2E2;
        color: #991B1B;
    }

    .form-container {
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
    }

    .form-header {
        background: var(--bg-light);
        padding: 10px 14px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0;
    }

    .form-header h2 {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .score-display {
        display: flex;
        align-items: baseline;
        gap: 3px;
        padding: 5px 10px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 4px;
    }

    .score-label {
        font-size: 0.6875rem;
        color: var(--text-secondary);
        font-weight: 500;
        margin-right: 2px;
    }

    .score-value {
        font-size: 1.375rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
    }

    .score-max {
        font-size: 0.8125rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .form-body {
        padding: 12px;
    }

    .section {
        margin-bottom: 12px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 6px;
        overflow: hidden;
    }

    .section:last-of-type {
        margin-bottom: 0;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: var(--bg-light);
        cursor: pointer;
        user-select: none;
    }

    .section-header:hover {
        background: #F3F4F6;
    }

    .section-header h3 {
        margin: 0;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .section-count {
        font-size: 0.6875rem;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .toggle-icon {
        width: 18px;
        height: 18px;
        color: var(--text-secondary);
        transition: transform 0.2s;
    }

    .section.collapsed .toggle-icon {
        transform: rotate(-90deg);
    }

    .section-body {
        padding: 10px 12px;
    }

    .section.collapsed .section-body {
        display: none;
    }

    .criterion {
        margin-bottom: 12px;
    }

    .criterion:last-child {
        margin-bottom: 0;
    }

    .criterion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .criterion-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .criterion-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
        display: none;
    }

    .criterion-badge.show {
        display: inline-block;
    }

    .criterion-badge.excellent {
        background: #D1FAE5;
        color: #065F46;
    }

    .criterion-badge.good {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .criterion-badge.fair {
        background: #FEF3C7;
        color: #92400E;
    }

    .criterion-badge.poor {
        background: #FEE2E2;
        color: #991B1B;
    }

    .rating-options {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 6px;
    }

    .rating-option {
        background: white;
        border: 1.5px solid var(--border);
        border-radius: 4px;
        padding: 8px;
        cursor: pointer;
        transition: all 0.15s;
        position: relative;
    }

    .rating-option:hover {
        border-color: #D1D5DB;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }

    .rating-option.selected {
        border-width: 2px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .rating-option.excellent {
        border-color: #10B981;
        background: #F0FDF4;
    }

    .rating-option.excellent.selected {
        border-color: #059669;
        background: #D1FAE5;
    }

    .rating-option.good {
        border-color: #3B82F6;
        background: #EFF6FF;
    }

    .rating-option.good.selected {
        border-color: #2563EB;
        background: #DBEAFE;
    }

    .rating-option.fair {
        border-color: #F59E0B;
        background: #FFFBEB;
    }

    .rating-option.fair.selected {
        border-color: #D97706;
        background: #FEF3C7;
    }

    .rating-option.poor {
        border-color: #EF4444;
        background: #FEF2F2;
    }

    .rating-option.poor.selected {
        border-color: #DC2626;
        background: #FEE2E2;
    }

    .rating-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .rating-title {
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 3px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .rating-option.excellent .rating-title {
        color: #065F46;
    }

    .rating-option.good .rating-title {
        color: #1E40AF;
    }

    .rating-option.fair .rating-title {
        color: #92400E;
    }

    .rating-option.poor .rating-title {
        color: #991B1B;
    }

    .rating-points {
        font-size: 0.75rem;
        font-weight: 700;
        opacity: 0.9;
    }

    .rating-desc {
        font-size: 0.6875rem;
        line-height: 1.3;
        color: #374151;
        margin: 0;
    }

    .rating-option.selected .rating-desc {
        color: var(--text-primary);
        font-weight: 500;
    }

    .gwa-section {
        background: white;
        border-bottom: 1px solid var(--border);
        padding: 14px 18px;
        margin-bottom: 0;
    }

    .gwa-section-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .gwa-section-header {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .gwa-input-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .gwa-input {
        width: 120px;
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 0.9375rem;
        font-weight: 600;
        text-align: center;
        transition: all 0.2s;
        background: white;
    }

    .gwa-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .gwa-help-text {
        font-size: 0.75rem;
        color: var(--text-secondary);
        line-height: 1.4;
        margin-top: 6px;
    }

    .gwa-status {
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.8125rem;
        margin-top: 8px;
    }

    .gwa-status.success {
        background: #D1FAE5;
        border: 1px solid #10B981;
        color: #065F46;
    }

    .gwa-status.warning {
        background: #FEF3C7;
        border: 1px solid #F59E0B;
        color: #92400E;
    }

    .recommendation-section,
    .comments-section {
        background: white;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .section-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 6px;
        display: block;
    }

    .form-select,
    .form-textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 0.8125rem;
        transition: all 0.2s;
        background: white;
    }

    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 80px;
        line-height: 1.5;
        font-family: inherit;
    }

    .char-count {
        text-align: right;
        font-size: 0.8125rem;
        color: var(--text-secondary);
        margin-top: 6px;
    }

    /* Sentence choices styles */
    .sentence-choices {
        margin-bottom: 10px;
    }

    .choices-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
    }

    .choices-label {
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .choices-toggle {
        background: var(--bg-light);
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 3px 8px;
        font-size: 0.6875rem;
        font-weight: 500;
        color: var(--text-primary);
        cursor: pointer;
        transition: all 0.2s;
    }

    .choices-toggle:hover {
        background: var(--border);
    }

    .choices-container {
        background: var(--bg-light);
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 8px;
        margin-bottom: 8px;
    }

    .choice-group {
        margin-bottom: 8px;
    }

    .choice-group:last-child {
        margin-bottom: 0;
    }

    .choice-group-label {
        display: block;
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 5px;
    }

    .choice-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .choice-btn {
        background: white;
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 0.6875rem;
        font-weight: 500;
        color: var(--text-primary);
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .choice-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }
    
    .actions .buttons-row {
        display: flex;
        flex-direction: row;
        gap: 10px;
        align-items: center;
        width: 100%;
        justify-content: flex-end;
    }

    .warning-message {
        background: #FEF3C7;
        border: 2px solid #F59E0B;
        border-radius: 6px;
        padding: 12px 16px;
        color: #92400E;
        font-size: 0.8125rem;
        font-weight: 500;
        display: none;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        flex-shrink: 0;
        max-width: 400px;
        min-width: 200px;
        order: -1;
        text-align: left;
        line-height: 1.4;
    }
    
    .actions .btn {
        flex-shrink: 0;
    }

    .warning-message.show {
        display: block;
    }

    .btn {
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 140px;
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-primary {
        background: #800020 !important;
        color: white !important;
        box-shadow: none !important;
    }

    .btn-primary:hover:not(:disabled) {
        background: #5C0016 !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .btn-secondary {
        background: #F8F9FA !important;
        color: #1F2937 !important;
        border: 1px solid #E9ECEF !important;
        box-shadow: none !important;
    }

    .btn-secondary:hover {
        background: #E9ECEF !important;
        transform: none !important;
        box-shadow: none !important;
    }

    @media (max-width: 1024px) {
        .applicant-header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .applicant-header-right {
            width: 100%;
            justify-content: flex-start;
            gap: 32px;
        }

        .applicant-header-score-item {
            align-items: flex-start;
        }

        .rating-options {
            grid-template-columns: repeat(2, 1fr);
        }

        .interview-container {
            padding-bottom: 20px;
        }
    }

    @media (max-width: 768px) {
        .rating-options {
            grid-template-columns: 1fr;
        }

        .actions {
            align-items: stretch;
        }
        
        .actions .warning-message {
            max-width: 100%;
            white-space: normal;
            order: -1;
        }
        
        .actions .buttons-row {
            display: flex;
            flex-direction: row;
            gap: 10px;
            width: 100%;
            justify-content: stretch;
        }

        .btn {
            flex: 1;
        }
    }
</style>
@endpush

<!-- Applicant Header -->
<div class="applicant-header">
    <div class="applicant-header-content">
        <div class="applicant-header-left">
            <div class="applicant-header-avatar">
                {{ substr($applicant->first_name, 0, 1) }}{{ substr($applicant->last_name, 0, 1) }}
            </div>
            <div class="applicant-header-details">
                <h3 class="applicant-header-name">{{ $applicant->full_name }}</h3>
                <div class="applicant-header-meta">
                    <span>{{ $applicant->application_no }}</span>
                    <span class="applicant-header-meta-separator">·</span>
                    <span>{{ $applicant->email_address }}</span>
                </div>
            </div>
        </div>
        <div class="applicant-header-right">
            <div class="applicant-header-score-item">
                <span class="applicant-header-score-label">UEE Score</span>
                <span class="applicant-header-score-value">
                    @php
                        $ueeScore = $applicant->score ?? 0;
                        $ueeClass = $ueeScore >= 75 ? 'success' : ($ueeScore >= 60 ? 'warning' : 'danger');
                    @endphp
                    <span class="badge badge-{{ $ueeClass }}">{{ number_format($ueeScore, 1) }}%</span>
                </span>
            </div>
            <div class="applicant-header-score-item">
                <span class="applicant-header-score-label">Exam Score</span>
                <span class="applicant-header-score-value">
                    @php
                        $examScore = $applicant->enrollassess_score ?? 0;
                        $scoreClass = $examScore >= 75 ? 'success' : ($examScore >= 60 ? 'warning' : 'danger');
                    @endphp
                    <span class="badge badge-{{ $scoreClass }}">{{ number_format($examScore, 1) }}%</span>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="interview-container">
    <!-- Evaluation Form -->
    <div class="form-container">
        <form method="POST" action="{{ $form_action }}" id="evaluationForm">
            @csrf
            <!-- Hidden field to submit GWA value -->
            <input type="hidden" name="card_tor_gwa" id="card_tor_gwa" value="{{ old('card_tor_gwa', $applicant->card_tor_gwa) }}">
            
            <!-- CARD/TOR GWA Input -->
            <div class="gwa-section">
                <div class="gwa-section-row">
                    <label for="card_tor_gwa_input" class="gwa-section-header">
                        CARD/TOR GWA (%) <span style="color: #DC2626;">*</span>
                    </label>
                    <div class="gwa-input-wrapper">
                        <input 
                            type="number" 
                            id="card_tor_gwa_input" 
                            class="gwa-input" 
                            min="0" 
                            max="100" 
                            step="0.01" 
                            value="{{ old('card_tor_gwa', $applicant->card_tor_gwa) }}"
                            placeholder="0.00"
                            required
                        >
                        <span style="font-size: 0.875rem; color: var(--text-secondary);">%</span>
                    </div>
                </div>
                <div class="gwa-help-text">
                    Enter the applicant's General Weighted Average from their CARD/TOR as a percentage (0-100). This is required before submission.
                </div>
                @if($applicant->card_tor_gwa)
                <div class="gwa-status success">
                    <strong>Current GWA:</strong> {{ number_format($applicant->card_tor_gwa, 2) }}%
                </div>
                @endif
            </div>

            <div class="form-header">
                <h2>BSIT Admission Interview Evaluation</h2>
                <div class="score-display">
                    <span class="score-label">Total</span>
                    <span class="score-value" id="totalScore">0</span>
                    <span class="score-max">/100</span>
                </div>
            </div>

            <div class="form-body">
                @foreach($criteriaGroups as $groupId => $group)
                <div class="section" data-section="{{ $groupId }}">
                    <div class="section-header" onclick="toggleSection('{{ $groupId }}')">
                        <h3>
                            {{ $group['title'] }}
                            <span class="section-count">{{ count($group['criteria']) }} criteria</span>
                        </h3>
                        <svg class="toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                    <div class="section-body">
                        @foreach($group['criteria'] as $criteriaKey)
                            @php
                                $criteria = $criteriaData[$criteriaKey];
                                $oldValue = old($criteriaKey, $interview->$criteriaKey ?? null);
                            @endphp
                            <div class="criterion">
                                <div class="criterion-header">
                                    <span class="criterion-label">{{ $criteria['label'] }}</span>
                                    <span class="criterion-badge" id="badge-{{ $criteriaKey }}"></span>
                                </div>
                                <div class="rating-options" data-crit="{{ $criteriaKey }}">
                                    <label class="rating-option excellent {{ $oldValue == 10 ? 'selected' : '' }}">
                                        <input type="radio" name="{{ $criteriaKey }}" value="10" {{ $oldValue == 10 ? 'checked' : '' }} required>
                                        <div class="rating-title">
                                            Excellent
                                            <span class="rating-points">10</span>
                                        </div>
                                        <p class="rating-desc">{{ $criteria['excellent'] }}</p>
                                    </label>
                                    <label class="rating-option good {{ $oldValue == 8 ? 'selected' : '' }}">
                                        <input type="radio" name="{{ $criteriaKey }}" value="8" {{ $oldValue == 8 ? 'checked' : '' }}>
                                        <div class="rating-title">
                                            Good
                                            <span class="rating-points">8</span>
                                        </div>
                                        <p class="rating-desc">{{ $criteria['good'] }}</p>
                                    </label>
                                    <label class="rating-option fair {{ $oldValue == 6 ? 'selected' : '' }}">
                                        <input type="radio" name="{{ $criteriaKey }}" value="6" {{ $oldValue == 6 ? 'checked' : '' }}>
                                        <div class="rating-title">
                                            Fair
                                            <span class="rating-points">6</span>
                                        </div>
                                        <p class="rating-desc">{{ $criteria['fair'] }}</p>
                                    </label>
                                    <label class="rating-option poor {{ $oldValue == 4 ? 'selected' : '' }}">
                                        <input type="radio" name="{{ $criteriaKey }}" value="4" {{ $oldValue == 4 ? 'checked' : '' }}>
                                        <div class="rating-title">
                                            Needs Improvement
                                            <span class="rating-points">4</span>
                                        </div>
                                        <p class="rating-desc">{{ $criteria['poor'] }}</p>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <!-- Recommendation -->
                <div class="recommendation-section">
                    <label class="section-label" for="recommendation">Final Recommendation</label>
                    <select name="recommendation" id="recommendation" class="form-select" required>
                        <option value="">Select Recommendation</option>
                        <option value="highly_recommended" {{ old('recommendation', $interview->recommendation) == 'highly_recommended' ? 'selected' : '' }}>Highly Recommended</option>
                        <option value="recommended" {{ old('recommendation', $interview->recommendation) == 'recommended' ? 'selected' : '' }}>Recommended</option>
                        <option value="conditional" {{ old('recommendation', $interview->recommendation) == 'conditional' ? 'selected' : '' }}>Conditional</option>
                        <option value="not_recommended" {{ old('recommendation', $interview->recommendation) == 'not_recommended' ? 'selected' : '' }}>Not Recommended</option>
                    </select>
                </div>

                <!-- Comments -->
                <div class="comments-section">
                    <label class="section-label" for="final_comments">Overall Notes</label>
                    
                    <!-- Auto-generated sentence choices -->
                    <div class="sentence-choices">
                        <div class="choices-header">
                            <span class="choices-label">Quick Add:</span>
                            <button type="button" class="choices-toggle" onclick="toggleChoices()">Show Options</button>
                        </div>
                        <div class="choices-container" id="choicesContainer" style="display: none;">
                            <div class="choice-group">
                                <span class="choice-group-label">Strengths:</span>
                                <div class="choice-buttons">
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant demonstrates strong communication skills and expresses ideas clearly.')">Strong communication skills</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant shows excellent motivation and clear interest in pursuing IT.')">Excellent motivation</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant displays good problem-solving abilities and analytical thinking.')">Good problem-solving</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant has a solid understanding of the BSIT program and its requirements.')">Solid program understanding</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant demonstrates a positive attitude and professional demeanor.')">Positive attitude</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant shows relevant IT background and technical knowledge.')">Relevant IT background</button>
                                </div>
                            </div>
                            <div class="choice-group">
                                <span class="choice-group-label">Areas for Improvement:</span>
                                <div class="choice-buttons">
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant may benefit from improving communication clarity and confidence.')">Needs communication improvement</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant would benefit from gaining more IT exposure and technical experience.')">Needs more IT exposure</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant should develop stronger problem-solving and critical thinking skills.')">Needs problem-solving development</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant needs to better understand the program expectations and curriculum.')">Needs program understanding</button>
                                </div>
                            </div>
                            <div class="choice-group">
                                <span class="choice-group-label">Overall Assessment:</span>
                                <div class="choice-buttons">
                                    <button type="button" class="choice-btn" onclick="addSentence('Overall, the applicant shows great potential and is well-suited for the BSIT program.')">Great potential</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant meets the requirements and demonstrates readiness for the program.')">Meets requirements</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant shows promise but may need additional support and guidance.')">Shows promise</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant demonstrates commitment and willingness to learn new concepts.')">Commitment to learn</button>
                                    <button type="button" class="choice-btn" onclick="addSentence('The applicant would be a valuable addition to the BSIT program.')">Valuable addition</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <textarea name="final_comments" id="final_comments" class="form-textarea" required 
                              placeholder="Provide your overall assessment, key observations, strengths, areas for improvement, and any additional notes about the applicant..." 
                              maxlength="5000">{{ old('final_comments', $interview->final_comments) }}</textarea>
                    <div class="char-count">
                        <span id="charCount">0</span>/5000 characters
                    </div>
                </div>

                @if($mode === 'admin')
                <input type="hidden" name="action" value="submit_final" id="actionField">
                @endif

                <!-- Actions -->
                <div class="actions">
                    <div class="warning-message" id="completionWarning">
                        Please complete all criteria and recommendation before submitting.
                    </div>
                    <div class="buttons-row">
                        <a href="{{ $back_url }}" class="btn btn-secondary">Cancel</a>
                        @if($mode === 'admin')
                        <button type="button" onclick="submitForm('save_draft')" class="btn btn-secondary">Save Draft</button>
                        <button type="button" onclick="submitForm('submit_final')" class="btn btn-primary" id="submitBtn" disabled>Submit Evaluation</button>
                        @else
                        <button type="submit" form="evaluationForm" class="btn btn-primary" id="submitBtn" disabled>Submit Evaluation</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('evaluationForm');
    const submitBtn = document.getElementById('submitBtn');
    const warningEl = document.getElementById('completionWarning');
    const totalScoreEl = document.getElementById('totalScore');
    const charCountEl = document.getElementById('charCount');
    const textarea = document.getElementById('final_comments');
    
    const requiredFields = [
        'communication_skills', 'motivation_interest', 'problem_solving_attitude', 'program_understanding',
        'personality_attitude', 'it_background', 'willingness_to_learn', 'overall_impression', 'recommendation', 'card_tor_gwa_input'
    ];

    // Character counter
    if (textarea) {
        charCountEl.textContent = textarea.value.length;
        textarea.addEventListener('input', () => charCountEl.textContent = textarea.value.length);
    }

    // Initialize selected states
    document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
        updateSelection(radio);
        updateBadge(radio);
    });
    

    // Rating selection
    document.querySelectorAll('.rating-option').forEach(option => {
        option.addEventListener('click', function(e) {
            if (e.target.tagName === 'INPUT') return;
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                updateSelection(radio);
                updateBadge(radio);
                checkCompletion();
                calculateScore();
            }
        });
    });

    // Recommendation change
    document.getElementById('recommendation')?.addEventListener('change', checkCompletion);
    
    // GWA change (sync input to hidden field inside form)
    const gwaInput = document.getElementById('card_tor_gwa_input');
    const gwaHidden = document.getElementById('card_tor_gwa');
    gwaInput?.addEventListener('input', function() {
        if (gwaHidden) gwaHidden.value = this.value;
        checkCompletion();
    });

    function updateSelection(radio) {
        const container = radio.closest('.rating-options');
        if (container) {
            container.querySelectorAll('.rating-option').forEach(opt => opt.classList.remove('selected'));
            if (radio.checked) radio.closest('.rating-option').classList.add('selected');
        }
    }

    function updateBadge(radio) {
        if (!radio.checked) return;
        const value = parseInt(radio.value);
        const badge = document.getElementById(`badge-${radio.name}`);
        if (badge) {
            let className = '', text = '';
            if (value === 10) { className = 'excellent'; text = '10'; }
            else if (value === 8) { className = 'good'; text = '8'; }
            else if (value === 6) { className = 'fair'; text = '6'; }
            else if (value === 4) { className = 'poor'; text = '4'; }
            badge.className = `criterion-badge ${className} show`;
            badge.textContent = text;
        }
    }

    function checkCompletion() {
        let allCompleted = requiredFields.every(field => {
            if (field === 'recommendation' || field === 'card_tor_gwa_input') {
                const element = document.getElementById(field);
                return element?.value && element.value.trim() !== '';
            }
            return form.querySelector(`input[name="${field}"]:checked`);
        });

        submitBtn.disabled = !allCompleted;
        warningEl.classList.toggle('show', !allCompleted);
        return allCompleted;
    }

    function calculateScore() {
        let totalRaw = 0; // out of 80
        document.querySelectorAll('[data-crit] input:checked').forEach(radio => {
            totalRaw += parseInt(radio.value) || 0;
        });
        const totalPercent = Math.round((totalRaw / 80) * 100);
        totalScoreEl.textContent = totalPercent;
        
        const color = totalPercent >= 70 ? '#059669' : totalPercent >= 50 ? '#2563EB' : totalPercent >= 35 ? '#F59E0B' : '#DC2626';
        totalScoreEl.style.color = color;
    }

    window.toggleSection = function(sectionId) {
        document.querySelector(`[data-section="${sectionId}"]`)?.classList.toggle('collapsed');
    };

    @if($mode === 'admin')
    window.submitForm = function(action) {
        if (!checkCompletion() && action === 'submit_final') {
            warningEl.classList.add('show');
            return;
        }
        document.getElementById('actionField').value = action;
        // Ensure hidden GWA is synced before submit
        if (gwaHidden && gwaInput) gwaHidden.value = gwaInput.value;
        
        if (action === 'submit_final') {
            const total = parseInt(totalScoreEl.textContent); // percent out of 100
            const passing = total >= 70;
            const message = passing 
                ? `Submit interview evaluation with a score of ${total}/100?\n\nThis meets the passing requirement (70%).`
                : `Submit interview evaluation with a score of ${total}/100?\n\nWarning: This is below the passing requirement (70%).`;
            
            if (!confirm(message)) return;
        }
        form.submit();
    };
    @else
    form.addEventListener('submit', function(e) {
        if (!checkCompletion()) {
            e.preventDefault();
            warningEl.classList.add('show');
            return;
        }
        if (gwaHidden && gwaInput) gwaHidden.value = gwaInput.value;

        const total = parseInt(totalScoreEl.textContent); // percent out of 100
        const passing = total >= 70;
        const message = passing 
            ? `Submit interview evaluation with a score of ${total}/100?\n\nThis meets the passing requirement (70%).`
            : `Submit interview evaluation with a score of ${total}/100?\n\nWarning: This is below the passing requirement (70%).`;
        
        if (!confirm(message)) e.preventDefault();
    });
    @endif

    calculateScore();
    checkCompletion();
    
    // Toggle choices container
    window.toggleChoices = function() {
        const container = document.getElementById('choicesContainer');
        const toggle = document.querySelector('.choices-toggle');
        if (container.style.display === 'none') {
            container.style.display = 'block';
            toggle.textContent = 'Hide Options';
        } else {
            container.style.display = 'none';
            toggle.textContent = 'Show Options';
        }
    };
    
    // Add sentence to textarea
    window.addSentence = function(sentence) {
        const textarea = document.getElementById('final_comments');
        const currentText = textarea.value.trim();
        const newText = currentText 
            ? currentText + ' ' + sentence
            : sentence;
        
        // Check if adding would exceed max length
        if (newText.length <= 5000) {
            textarea.value = newText;
            // Update character count
            if (charCountEl) {
                charCountEl.textContent = newText.length;
            }
            // Focus textarea
            textarea.focus();
            // Move cursor to end
            textarea.setSelectionRange(newText.length, newText.length);
        } else {
            alert('Adding this sentence would exceed the maximum character limit.');
        }
    };
});
</script>
@endpush

