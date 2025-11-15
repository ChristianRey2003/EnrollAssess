@extends('layouts.instructor')

@section('title', 'Dashboard')

@php
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Overview of your interview assignments and activities';
@endphp

@push('styles')
<link href="{{ asset('css/admin/interviews.css') }}" rel="stylesheet">
<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Statistics Section - using interview-history style */
    .stats-section {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
        max-width: 100%;
    }

    .dashboard-sections {
        display: block;
    }

    .dashboard-section {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .section-header {
        padding: 24px;
        background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }

    .section-content {
        padding: 24px;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: #FEF3C7;
        color: #F59E0B;
    }

    .status-completed {
        background: #D1FAE5;
        color: #059669;
    }

    .status-examcompleted {
        background: #FEE2E2;
        color: #DC2626;
    }

    .status-scheduled {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .quick-actions {
        display: grid;
        gap: 16px;
    }

    .action-card {
        padding: 20px;
        border: 2px solid #E5E7EB;
        border-radius: 12px;
        text-decoration: none;
        color: #374151;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .action-card:hover {
        border-color: var(--maroon-primary);
        background: #F9FAFB;
        transform: translateY(-2px);
    }

    .action-card.featured {
        background: linear-gradient(135deg, var(--maroon-primary) 0%, #5C0016 100%);
        color: white;
        border-color: var(--maroon-primary);
    }

    .action-card.featured:hover {
        background: linear-gradient(135deg, #5C0016 0%, var(--maroon-primary) 100%);
        color: white;
    }

    .action-icon {
        font-size: 1.25rem;
        opacity: 0.8;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F3F4F6;
        border-radius: 6px;
        flex-shrink: 0;
    }

    .action-content h3 {
        font-weight: 600;
        color: inherit;
        margin: 0 0 4px 0;
    }

    .action-content p {
        color: inherit;
        font-size: 0.875rem;
        margin: 0;
        opacity: 0.8;
    }

    .dashboard-table-wrapper .table-responsive {
        margin-bottom: 0;
    }

    .dashboard-table thead th {
        font-weight: 600;
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #374151;
        background-color: #ffffff;
        border-bottom: 1px solid #E5E7EB;
    }

    .dashboard-table tbody td {
        font-size: 0.875rem;
        color: #1F2937;
        vertical-align: middle;
        border-bottom: 1px solid #F3F4F6;
    }

    .dashboard-table tbody tr:hover {
        background-color: rgba(255, 215, 0, 0.08);
    }

    .applicant-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .applicant-name {
        font-weight: 600;
        color: #1F2937;
    }

    .applicant-email {
        font-size: 0.875rem;
        color: #6B7280;
    }

    .btn-small {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
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

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #6B7280;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid #F3F4F6;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        background: #F3F4F6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    .activity-content h4 {
        font-weight: 600;
        color: #1F2937;
        margin: 0 0 4px 0;
    }

    .activity-content p {
        color: #6B7280;
        font-size: 0.875rem;
        margin: 0;
    }

    @media (max-width: 768px) {
        .dashboard-sections {
            grid-template-columns: 1fr;
        }
        
        .stats-section {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Statistics -->
    <section class="stats-section">
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $stats['total_assigned'] }}</div>
            <div class="stat-label">Total Assigned</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $stats['pending_interviews'] }}</div>
            <div class="stat-label">Pending Interviews</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $stats['completed_interviews'] }}</div>
            <div class="stat-label">Completed</div>
        </div>
    </section>

    <!-- Main Dashboard Content -->
    <div class="dashboard-sections">
        <!-- Left Column - Upcoming Interviews -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Upcoming Interviews</h2>
                <a href="{{ route('instructor.schedule') }}" class="btn-small btn-primary">View Schedule</a>
            </div>
            <div class="section-content">
                @if($upcomingInterviews->count() > 0)
                    <div class="dashboard-table-wrapper">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle dashboard-table">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-left">Applicant</th>
                                        <th scope="col" class="text-center" style="min-width: 140px;">Application No.</th>
                                        <th scope="col" class="text-center">Date & Time</th>
                                        <th scope="col" class="text-center">Exam Score</th>
                                        <th scope="col" class="text-center" style="min-width: 160px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingInterviews->take(10) as $interview)
                                    <tr>
                                        <td>
                                            <div class="applicant-cell">
                                                <div class="applicant-name">{{ $interview->applicant->first_name }} {{ $interview->applicant->last_name }}</div>
                                                <div class="applicant-email">{{ $interview->applicant->email_address }}</div>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $interview->applicant->application_no }}</td>
                                        <td class="text-center">
                                            <div>{{ $interview->schedule_date->format('M d, Y') }}</div>
                                            <div style="font-size: 0.875rem; color: #6B7280;">{{ $interview->schedule_date->format('g:i A') }}</div>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $examScore = $interview->applicant->enrollassess_score ?? null;
                                            @endphp
                                            @if($examScore !== null)
                                                <span class="status-badge {{ $examScore >= 70 ? 'status-completed' : 'status-pending' }}">
                                                    {{ number_format($examScore, 1) }}%
                                                </span>
                                            @else
                                                <span class="status-badge status-pending">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php $canConduct = $interview->applicant->hasCompletedExam(); @endphp
                                            <a href="{{ route('instructor.interview.show', $interview->applicant->applicant_id) }}" 
                                               class="btn-small btn-primary{{ !$canConduct ? ' disabled' : '' }}"
                                               @if(!$canConduct) aria-disabled="true" tabindex="-1" title="Applicant must complete exam first" @endif>
                                                Conduct Interview
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <h3>No Upcoming Interviews</h3>
                        <p>You don't have any interviews scheduled for the coming days.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
