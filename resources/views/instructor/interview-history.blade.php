@extends('layouts.instructor')

@section('title', 'Interview History')

@php
    $pageTitle = 'Interview History';
    $pageSubtitle = 'Review your completed interview evaluations and performance statistics';
@endphp

@push('styles')
<link href="{{ asset('css/admin/interviews.css') }}" rel="stylesheet">
<style>
    /* Override main-content padding for this page */
    .main-content {
        padding: 20px !important;
    }

    /* Statistics Section */
    .stats-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        width: 100%;
    }

    .stats-section .stat-card {
        width: 100%;
        min-width: 0;
    }

    /* Content Card */
    .content-card {
        background: #FFFFFF;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid #E5E7EB;
    }

    /* Table Styles matching admin */
    .table-responsive {
        overflow-x: auto;
    }

    .table thead th {
        font-size: 0.85rem;
        font-weight: bold;
        background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
        padding: 12px;
    }

    .table tbody td {
        font-size: 13px;
        font-weight: normal;
        padding: 12px;
        border-bottom: 1px solid #F3F4F6;
        vertical-align: middle;
    }

    .table tbody tr {
        position: relative;
    }

    .table tbody tr:hover {
        background: rgba(255, 215, 0, 0.05);
    }

    /* Applicant Info */
    .applicant-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .applicant-name {
        font-weight: 500;
        color: #1F2937;
    }

    .applicant-email {
        font-size: 12px;
        color: #6B7280;
    }

    /* Schedule Info */
    .schedule-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .schedule-date {
        font-weight: 500;
        color: #1F2937;
    }

    .schedule-time {
        font-size: 12px;
        color: #6B7280;
    }

    /* Score Display */
    .score-display {
        text-align: center;
    }

    .score-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
    }

    .score-excellent {
        background: #dcfce7;
        color: #166534;
    }

    .score-good {
        background: #fef3c7;
        color: #92400e;
    }

    .score-fair {
        background: #fed7aa;
        color: #ea580c;
    }

    .score-poor {
        background: #fef2f2;
        color: #dc2626;
    }

    /* Floating Actions */
    .floating-actions {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 8px;
        display: none;
        flex-direction: column;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10;
    }

    .table tbody tr:hover .floating-actions {
        display: flex;
    }

    .floating-actions .action-btn {
        padding: 6px 12px;
        border: none;
        background: transparent;
        cursor: pointer;
        border-radius: 4px;
        font-size: 12px;
        transition: background-color 0.2s;
        text-align: left;
        white-space: nowrap;
        text-decoration: none;
        color: #1F2937;
    }

    .floating-actions .action-btn:hover {
        background: #F3F4F6;
    }

    .floating-actions .action-btn-view {
        background: white;
        color: #1F2937;
        border: 1px solid #E5E7EB;
    }

    .floating-actions .action-btn-view:hover {
        background: #F9FAFB;
        color: #1F2937;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 24px;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1F2937;
        margin: 0 0 10px 0;
    }

    .empty-state p {
        color: #6B7280;
        margin: 0 0 20px 0;
    }

    /* Badge Styles */
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .badge.bg-secondary {
        background: #6b7280;
        color: white;
    }

    .text-muted {
        color: #6B7280;
        font-style: italic;
    }

    .score-pending {
        color: #6B7280;
        font-style: italic;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-section {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .floating-actions {
            position: static;
            transform: none;
            right: auto;
            display: flex;
            flex-direction: row;
            margin-top: 10px;
        }

        .table tbody tr:hover .floating-actions {
            display: flex;
            flex-direction: row;
        }
    }
</style>
@endpush

@section('content')
    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $statistics['total_completed'] }}</div>
            <div class="stat-label">Total Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ number_format($statistics['average_score'] ?? 0, 1) }}%</div>
            <div class="stat-label">Average Score</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $statistics['recommended_count'] }}</div>
            <div class="stat-label">Recommended</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true"></div>
            <div class="stat-value">{{ $statistics['this_month'] }}</div>
            <div class="stat-label">This Month</div>
        </div>
    </section>

    <!-- Main Content Card -->
    <div class="content-card">
        <!-- Interviews Table -->
        <div class="table-responsive">
            @if($completedInterviews->count() > 0)
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px; font-size: 0.85rem; font-weight: bold;" class="text-center">No.</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-left">Applicant</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-center">Schedule date</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-center">Interview score</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-center">Recommendation</th>
                            <th style="font-size: 0.85rem; font-weight: bold;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completedInterviews as $index => $interview)
                            @php
                                $scoreValue = (int) ($interview->overall_score ?? 0);
                                $scoreClass = 'score-poor';
                                if ($scoreValue >= 75) {
                                    $scoreClass = 'score-excellent';
                                } elseif ($scoreValue >= 50) {
                                    $scoreClass = 'score-good';
                                } elseif ($scoreValue >= 25) {
                                    $scoreClass = 'score-fair';
                                }
                            @endphp
                            <tr>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    {{ ($completedInterviews->currentPage() - 1) * $completedInterviews->perPage() + $index + 1 }}
                                </td>
                                <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                    <div class="applicant-info">
                                        @if($interview->applicant)
                                            <div class="applicant-name">{{ $interview->applicant->full_name }}</div>
                                            <div class="applicant-email">{{ $interview->applicant->email_address ?? $interview->applicant->email ?? 'N/A' }}</div>
                                        @else
                                            <div class="applicant-name text-muted">Unknown Applicant</div>
                                            <div class="applicant-email text-muted">N/A</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    <div class="schedule-info">
                                        <div class="schedule-date">
                                            {{ $interview->schedule_date ? $interview->schedule_date->format('M d, Y') : ($interview->interview_date ? $interview->interview_date->format('M d, Y') : 'Not set') }}
                                        </div>
                                        <div class="schedule-time">
                                            {{ $interview->schedule_date ? $interview->schedule_date->format('g:i A') : ($interview->interview_date ? $interview->interview_date->format('g:i A') : '') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    <div class="score-display">
                                        @if($interview->overall_score !== null)
                                            <span class="score-badge {{ $scoreClass }}">
                                                {{ number_format($scoreValue, 0) }}/100
                                            </span>
                                        @else
                                            <span class="score-pending">N/A</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                    @if($interview->recommendation)
                                        <span class="badge bg-secondary">
                                            {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $interview->recommendation)) }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center" style="font-size: 13px; font-weight: normal; position: relative;">
                                    <span class="badge bg-secondary">
                                        {{ \Illuminate\Support\Str::title(str_replace('-', ' ', $interview->applicant->status ?? 'N/A')) }}
                                    </span>
                                    
                                    <!-- Floating Actions -->
                                    @if($interview->applicant)
                                    <div class="floating-actions">
                                        <a href="{{ route('instructor.interview.show', $interview->applicant->applicant_id) }}" 
                                           class="action-btn action-btn-view">
                                            View Details
                                        </a>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $completedInterviews->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-state">
                        <h3>No Completed Interviews</h3>
                        <p>You haven't completed any interviews yet. Start by scheduling interviews with your assigned applicants.</p>
                        <a href="{{ route('instructor.applicants') }}" class="btn btn-sm" style="background: #800020; color: white; border: none; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block;">
                            View Assigned Applicants
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
