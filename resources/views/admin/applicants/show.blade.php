@extends('layouts.admin')

@section('title', 'Applicant Details - ' . config('app.name', 'EnrollAssess'))

@push('styles')
    <link href="{{ asset('css/admin/applicants.css') }}" rel="stylesheet">
    <style>
        /* Breadcrumb Styles */
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

        .main-content {
            padding: 20px !important;
        }

        .applicant-header {
            background: white;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .header-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 6px 0;
        }

        .header-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }

        .header-access-code {
            font-size: 13px;
            color: #6b7280;
            margin: 6px 0 0 0;
        }

        .header-status {
            display: flex;
            align-items: flex-start;
        }

        .header-personal-info {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .header-personal-info .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 12px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #800020;
        }

        .header-actions {
            display: flex;
            gap: 8px;
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

        .btn-primary {
            background: #800020;
            color: white;
        }

        .btn-primary:hover {
            background: #5C0016;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 0;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
        }

        .info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-exam-completed { background: #dbeafe; color: #1e40af; }
        .status-interview-available { background: #d1fae5; color: #065f46; }
        .status-interview-scheduled { background: #e0e7ff; color: #3730a3; }
        .status-interview-completed { background: #f3e8ff; color: #6b21a8; }
        .status-admitted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fecaca; color: #991b1b; }

        .content-section {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 20px;
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

        .data-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .data-row:last-child {
            border-bottom: none;
        }

        .info-grid-compact {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 8px;
        }

        .info-item-compact {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 6px;
        }

        .info-item-compact .data-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin: 0;
        }

        .info-item-compact .data-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
            margin: 0;
        }

        .personal-info-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px 20px;
            margin-top: 12px;
        }

        .personal-info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .personal-info-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
            margin: 0;
        }

        .personal-info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 400;
            margin: 0;
        }

        .data-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .data-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
        }

        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            background: conic-gradient(#10b981 0deg {{ ($applicant->enrollassess_score ?? 0) * 3.6 }}deg, #e5e7eb {{ ($applicant->enrollassess_score ?? 0) * 3.6 }}deg 360deg);
            padding: 4px;
        }

        .score-inner {
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .score-number {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1;
        }

        .score-label {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .timeline {
            position: relative;
            padding-left: 24px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 24px;
        }

        .timeline-marker {
            position: absolute;
            left: -20px;
            top: 4px;
            width: 16px;
            height: 16px;
            background: #800020;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 0 0 2px #e5e7eb;
        }

        .timeline-content {
            background: #f9fafb;
            border-radius: 6px;
            padding: 12px;
        }

        .timeline-event {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .timeline-time {
            font-size: 12px;
            color: #6b7280;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        @media (max-width: 1024px) {
            div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
        }

        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                gap: 16px;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .info-grid-compact {
                grid-template-columns: repeat(2, 1fr);
            }

            .content-section {
                padding: 16px;
            }

            .section-title {
                font-size: 16px;
            }
        }

        @media (max-width: 480px) {
            .info-grid-compact {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="{{ route('admin.applicants.index') }}" class="breadcrumb-link">Applicants</a>
    <span class="breadcrumb-separator">›</span>
    <span class="breadcrumb-current">View</span>
</div>

<div class="applicant-header">
    <div class="header-top">
        <div>
            <h1 class="header-title">{{ $applicant->full_name }}</h1>
            <p class="header-subtitle">{{ $applicant->application_no ?? $applicant->formatted_applicant_no }}</p>
            @if($applicant->accessCode)
            <p class="header-access-code">Access Code: {{ $applicant->accessCode->code }}</p>
            @endif
        </div>
        <div class="header-status">
            <span class="status-badge status-{{ str_replace('-', '-', $applicant->status) }}">
                {{ ucwords(str_replace('-', ' ', $applicant->status)) }}
            </span>
        </div>
    </div>
    
    <!-- Personal Information -->
    @if($applicant->basicInfo)
    <div class="header-personal-info">
        <h2 class="section-title">Personal Information</h2>
        <div class="personal-info-list">
            <div class="personal-info-item">
                <span class="personal-info-label">Sex:</span>
                <span class="personal-info-value">{{ $applicant->basicInfo->sex ?? '-' }}</span>
            </div>
            <div class="personal-info-item">
                <span class="personal-info-label">Civil Status:</span>
                <span class="personal-info-value">{{ $applicant->basicInfo->civil_status ?? '-' }}</span>
            </div>
            <div class="personal-info-item">
                <span class="personal-info-label">Date of Birth:</span>
                <span class="personal-info-value">
                    @if($applicant->basicInfo->date_of_birth)
                        {{ $applicant->basicInfo->date_of_birth->format('M d, Y') }}
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="personal-info-item">
                <span class="personal-info-label">Email:</span>
                <span class="personal-info-value">{{ $applicant->email_address ?? '-' }}</span>
            </div>
            <div class="personal-info-item">
                <span class="personal-info-label">Phone Number:</span>
                <span class="personal-info-value">{{ $applicant->phone_number ?? '-' }}</span>
            </div>
            <div class="personal-info-item">
                <span class="personal-info-label">Complete Address:</span>
                <span class="personal-info-value">{{ $applicant->basicInfo->complete_address ?? '-' }}</span>
            </div>
            <div class="personal-info-item">
                <span class="personal-info-label">Senior High School:</span>
                <span class="personal-info-value">{{ $applicant->basicInfo->senior_high_school_name ?? '-' }}</span>
            </div>
            <div class="personal-info-item">
                <span class="personal-info-label">SHS Strand:</span>
                <span class="personal-info-value">
                    @if($applicant->basicInfo->senior_high_school_strand)
                        {{ $applicant->basicInfo->senior_high_school_strand }}
                        @if($applicant->basicInfo->senior_high_school_strand === 'Others' && $applicant->basicInfo->senior_high_school_strand_other)
                            - {{ $applicant->basicInfo->senior_high_school_strand_other }}
                        @endif
                    @else
                        -
                    @endif
                </span>
            </div>
        </div>
    </div>
    @endif
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Main Content -->
    <div>

        <!-- Interview Information -->
        @if($applicant->latestInterview)
        <div class="content-section">
            <h2 class="section-title">Interview Information</h2>
            <div class="data-row">
                <span class="data-label">Status</span>
                <span class="status-badge status-{{ str_replace('-', '-', $applicant->latestInterview->status) }}">
                    {{ ucwords(str_replace('-', ' ', $applicant->latestInterview->status)) }}
                </span>
            </div>
            @if($applicant->latestInterview->schedule_date)
            <div class="data-row">
                <span class="data-label">Scheduled Date</span>
                <span class="data-value">{{ $applicant->latestInterview->schedule_date->format('M d, Y g:i A') }}</span>
            </div>
            @endif
            @if($applicant->latestInterview->interviewer)
            <div class="data-row">
                <span class="data-label">Interviewer</span>
                <span class="data-value">{{ $applicant->latestInterview->interviewer->full_name }}</span>
            </div>
            @endif
            @if($applicant->latestInterview->overall_score !== null)
            <div class="data-row">
                <span class="data-label">Interview Score</span>
                <span class="data-value">{{ number_format($applicant->latestInterview->overall_score, 2) }}/100</span>
            </div>
            @endif
            @if($applicant->latestInterview->recommendation)
            <div class="data-row">
                <span class="data-label">Recommendation</span>
                <span class="data-value">{{ ucwords(str_replace('_', ' ', $applicant->latestInterview->recommendation)) }}</span>
            </div>
            @endif
            <div style="margin-top: 16px;">
                <a href="{{ route('admin.interviews.show', $applicant->latestInterview->interview_id) }}" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    View Interview Details
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Scores Overview -->
        <div class="content-section">
            <h2 class="section-title">Scores Overview</h2>
            <div class="data-row">
                <span class="data-label">UEE Score</span>
                <span class="data-value">{{ $applicant->score ? number_format($applicant->score, 2) . '%' : '-' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">GWA</span>
                <span class="data-value">{{ $applicant->card_tor_gwa ? number_format($applicant->card_tor_gwa, 2) : '-' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">EnrollAssess Score</span>
                <span class="data-value" style="display: flex; align-items: center; gap: 8px;">
                    {{ $applicant->enrollassess_score ? number_format($applicant->enrollassess_score, 2) . '%' : '-' }}
                    @if($applicant->enrollassess_score && $totalQuestions > 0)
                        <a href="{{ route('admin.applicants.exam-details', $applicant->applicant_id) }}" 
                           class="btn btn-secondary" 
                           style="padding: 4px 12px; font-size: 12px; text-decoration: none;">
                            View Exam Details
                        </a>
                    @endif
                </span>
            </div>
            <div class="data-row">
                <span class="data-label">Interview Score</span>
                <span class="data-value">{{ $applicant->interview_score ? number_format($applicant->interview_score, 2) . '%' : '-' }}</span>
            </div>
            @if($overallRating)
            <div class="data-row">
                <span class="data-label">Overall Rating</span>
                <span class="data-value" style="font-weight: 600; color: #800020;">
                    {{ number_format($overallRating['overall_rating'], 2) }}% - {{ $overallRating['verbal_description'] }}
                </span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
