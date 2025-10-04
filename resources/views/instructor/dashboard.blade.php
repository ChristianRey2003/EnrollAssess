@extends('layouts.instructor')

@section('title', 'Dashboard')

@php
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Overview of your interview assignments and activities';
@endphp

@push('styles')
<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-stats {
        display: flex;
        gap: 32px;
        margin-bottom: 32px;
        background: white;
        padding: 20px 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
    }

    .stat-item {
        flex: 1;
        text-align: center;
        padding: 0 16px;
        border-right: 1px solid #E5E7EB;
    }

    .stat-item:last-child {
        border-right: none;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--maroon-primary);
        margin-bottom: 4px;
    }

    .stat-label {
        color: #6B7280;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .dashboard-sections {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 32px;
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

    .applicants-table {
        width: 100%;
        border-collapse: collapse;
    }

    .applicants-table th,
    .applicants-table td {
        padding: 16px;
        text-align: left;
        border-bottom: 1px solid #F3F4F6;
    }

    .applicants-table th {
        background: #F9FAFB;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .applicant-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .applicant-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--maroon-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
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
        
        .dashboard-stats {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Statistics -->
    <div class="dashboard-stats">
        <div class="stat-item">
            <div class="stat-value">{{ $stats['total_assigned'] }}</div>
            <div class="stat-label">Total Assigned</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['pending_interviews'] }}</div>
            <div class="stat-label">Pending Interviews</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['completed_interviews'] }}</div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $stats['recommended'] }}</div>
            <div class="stat-label">Recommended</div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="dashboard-sections">
        <!-- Left Column - Recent Applicants -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Recent Assigned Applicants</h2>
                <a href="{{ route('instructor.applicants') }}" class="btn-small btn-primary">View All</a>
            </div>
            <div class="section-content">
                @if($assignedApplicants->count() > 0)
                    <table class="applicants-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Application No.</th>
                                <th>Exam Score</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignedApplicants->take(5) as $applicant)
                            <tr>
                                <td>
                                    <div class="applicant-cell">
                                        <div class="applicant-avatar">
                                            {{ substr($applicant->first_name ?? 'A', 0, 1) }}{{ substr($applicant->last_name ?? 'A', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="applicant-name">{{ $applicant->first_name }} {{ $applicant->last_name }}</div>
                                            <div class="applicant-email">{{ $applicant->email_address }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $applicant->application_no }}</td>
                                <td>
                                    @if($applicant->score)
                                        <span class="status-badge {{ $applicant->score >= 70 ? 'status-completed' : 'status-pending' }}">
                                            {{ number_format($applicant->score, 1) }}%
                                        </span>
                                    @else
                                        <span class="status-badge status-pending">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ str_replace([' ', '-'], ['', ''], strtolower($applicant->status)) }}">
                                        {{ ucfirst(str_replace('-', ' ', $applicant->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($applicant->status === 'exam-completed')
                                        <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                           class="btn-small btn-primary">
                                            Start Interview
                                        </a>
                                    @elseif($applicant->status === 'interview-completed')
                                        <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                           class="btn-small btn-secondary">
                                            View Interview
                                        </a>
                                    @else
                                        <span style="color: #6B7280; font-size: 0.875rem;">Waiting for exam</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <h3>No Applicants Assigned Yet</h3>
                        <p>You don't have any applicants assigned for interviews yet. Check the Interview Pool for available interviews or contact the administrator.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column - Quick Actions & Recent Activity -->
        <div>
            <!-- Quick Actions -->
            <div class="dashboard-section" style="margin-bottom: 32px;">
                <div class="section-header">
                    <h2 class="section-title">Quick Actions</h2>
                </div>
                <div class="section-content">
                    <div class="quick-actions">
                        <a href="{{ route('instructor.interview-pool.index') }}" class="action-card featured">
                            <div class="action-icon">IP</div>
                            <div class="action-content">
                                <h3>Interview Pool</h3>
                                <p>Claim available interviews from the pool</p>
                            </div>
                        </a>
                        
                        <a href="{{ route('instructor.applicants') }}" class="action-card">
                            <div class="action-icon">MA</div>
                            <div class="action-content">
                                <h3>My Applicants</h3>
                                <p>View all assigned applicants</p>
                            </div>
                        </a>

                        @if($stats['pending_interviews'] > 0)
                        <a href="{{ route('instructor.applicants') }}?filter=pending" class="action-card">
                            <div class="action-icon">PI</div>
                            <div class="action-content">
                                <h3>Pending Interviews</h3>
                                <p>{{ $stats['pending_interviews'] }} interviews awaiting completion</p>
                            </div>
                        </a>
                        @endif

                        <a href="{{ route('instructor.guidelines') }}" class="action-card">
                            <div class="action-icon">GL</div>
                            <div class="action-content">
                                <h3>Guidelines</h3>
                                <p>Review evaluation criteria</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            @if($recentInterviews->count() > 0)
            <div class="dashboard-section">
                <div class="section-header">
                    <h2 class="section-title">Recent Activity</h2>
                </div>
                <div class="section-content">
                    @foreach($recentInterviews as $interview)
                    <div class="activity-item">
                        <div class="activity-icon">✓</div>
                        <div class="activity-content">
                            <h4>Interview Completed</h4>
                            <p>{{ $interview->applicant->first_name }} {{ $interview->applicant->last_name }} • {{ $interview->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
