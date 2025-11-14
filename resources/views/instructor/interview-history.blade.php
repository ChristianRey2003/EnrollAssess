@extends('layouts.instructor')

@section('title', 'Interview History')

@php
    $pageTitle = 'Interview History';
    $pageSubtitle = 'Review your completed interview evaluations and performance statistics';
@endphp

@push('styles')
<style>
    .history-card {
        background: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #E5E7EB;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .history-card-header {
        padding: 24px;
        background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
    }

    .history-card-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1F2937;
    }

    .history-card-footer {
        padding: 16px 24px;
        border-top: 1px solid #E5E7EB;
        background: #F9FAFB;
    }

    .interview-history-table-wrapper .table-responsive {
        margin-bottom: 0;
    }

    .interview-history-table thead th {
        font-weight: 600;
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #374151;
        background-color: #ffffff;
        border-bottom: 1px solid #E5E7EB;
    }

    .interview-history-table tbody td {
        font-size: 0.875rem;
        color: #1F2937;
        vertical-align: middle;
        border-bottom: 1px solid #F3F4F6;
    }

    .interview-history-table tbody tr:hover {
        background-color: rgba(255, 215, 0, 0.08);
    }

    .interview-history-table .applicant-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .interview-history-table .applicant-details .name {
        font-weight: 600;
        color: #1F2937;
    }

    .interview-history-table .applicant-details .email {
        font-size: 0.8125rem;
        color: #6B7280;
    }

    .score-progress {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .score-progress-value {
        font-weight: 600;
        color: #1F2937;
    }

    .score-progress-bar {
        flex: 1;
        height: 6px;
        background: #E5E7EB;
        border-radius: 999px;
        overflow: hidden;
    }

    .score-progress-bar span {
        display: block;
        height: 100%;
        background: var(--maroon-primary);
        border-radius: inherit;
    }

    .badge-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-success {
        background: #D1FAE5;
        color: #047857;
    }

    .badge-info {
        background: #DBEAFE;
        color: #1D4ED8;
    }

    .badge-warning {
        background: #FEF3C7;
        color: #B45309;
    }

    .badge-danger {
        background: #FEE2E2;
        color: #B91C1C;
    }

    .badge-neutral {
        background: #E5E7EB;
        color: #374151;
    }

    .history-action-button {
        border: none;
        background: none;
        color: var(--maroon-primary);
        font-weight: 600;
        cursor: pointer;
        transition: color 0.2s ease, text-decoration 0.2s ease;
        padding: 0;
    }

    .history-action-button:hover {
        color: #5C0016;
        text-decoration: underline;
    }

    .history-empty-state {
        padding: 48px 24px;
        text-align: center;
    }

    .history-empty-state h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 8px;
    }

    .history-empty-state p {
        color: #6B7280;
        font-size: 0.9375rem;
        margin-bottom: 16px;
    }

    .history-empty-state-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #F3F4F6;
        color: var(--maroon-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full" style="background-color: #FFF8DC; color: #800020;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Completed</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_completed'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Average Score</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($statistics['average_score'], 1) }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Recommended</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['recommended_count'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">This Month</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['this_month'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interview History Table -->
    <div class="history-card">
        <div class="history-card-header">
            <h3>Completed Interviews</h3>
        </div>
        
        @if($completedInterviews->count() > 0)
            <div class="interview-history-table-wrapper">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle interview-history-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-left" style="min-width: 220px;">Applicant</th>
                                <th scope="col" class="text-center" style="min-width: 160px;">Interview Date</th>
                                <th scope="col" class="text-center" style="min-width: 200px;">Overall Score</th>
                                <th scope="col" class="text-center">Rating</th>
                                <th scope="col" class="text-center">Recommendation</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-center" style="min-width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedInterviews as $interview)
                                @php
                                    $ratingClass = match($interview->overall_rating ?? '') {
                                        'excellent' => 'badge-success',
                                        'very_good' => 'badge-info',
                                        'good' => 'badge-info',
                                        'satisfactory' => 'badge-warning',
                                        'needs_improvement' => 'badge-danger',
                                        default => 'badge-neutral'
                                    };
                                
                                    $recommendationClass = match($interview->recommendation ?? '') {
                                        'highly_recommended' => 'badge-success',
                                        'recommended' => 'badge-info',
                                        'conditional' => 'badge-warning',
                                        'not_recommended' => 'badge-danger',
                                        default => 'badge-neutral'
                                    };
                                
                                    $statusClass = match($interview->applicant->status ?? '') {
                                        'admitted' => 'badge-success',
                                        'rejected' => 'badge-danger',
                                        'interview-completed' => 'badge-warning',
                                        default => 'badge-neutral'
                                    };
                                
                                    $scoreValue = (int) ($interview->overall_score ?? 0);
                                    $scoreWidth = max(0, min(100, $scoreValue));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="applicant-info">
                                            <div class="applicant-details">
                                                <div class="name">{{ $interview->applicant->full_name ?? 'N/A' }}</div>
                                                <div class="email">{{ $interview->applicant->email_address ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        {{ $interview->interview_date ? $interview->interview_date->format('M d, Y g:i A') : 'N/A' }}
                                    </td>
                                    <td>
                                        <div class="score-progress">
                                            <span class="score-progress-value">{{ $scoreValue }}%</span>
                                            <div class="score-progress-bar">
                                                <span style="width: {{ $scoreWidth }}%;"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-pill {{ $ratingClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $interview->overall_rating ?? 'N/A')) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-pill {{ $recommendationClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $interview->recommendation ?? 'N/A')) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-pill {{ $statusClass }}">
                                            {{ ucfirst(str_replace('-', ' ', $interview->applicant->status ?? 'N/A')) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="history-action-button"
                                                onclick="viewInterviewDetails({{ $interview->interview_id }})">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="history-card-footer">
                {{ $completedInterviews->links() }}
            </div>
        @else
            <div class="history-empty-state">
                <div class="history-empty-state-icon">📄</div>
                <h3>No completed interviews yet</h3>
                <p>You haven't completed any interviews yet. Start by scheduling interviews with your assigned applicants.</p>
                <a href="{{ route('instructor.applicants') }}" class="btn btn-primary">
                    View Assigned Applicants
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function viewInterviewDetails(interviewId) {
        // This would typically open a modal or navigate to a detail page
        alert('Interview details view would open here for interview ID: ' + interviewId);
    }
</script>
@endpush