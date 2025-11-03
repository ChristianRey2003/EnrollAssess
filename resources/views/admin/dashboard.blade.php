@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Computer Studies Department';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }

        .stat-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .stat-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
            white-space: nowrap;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1;
            white-space: nowrap;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            font-weight: 600;
        }

        .trend-up {
            color: #10b981;
        }

        .trend-down {
            color: #ef4444;
        }



        .recent-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }

        .table-action {
            color: #800020;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }

        .table-action:hover {
            color: #5c0017;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: #f9fafb;
        }

        .data-table th {
            padding: 12px 24px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #1f2937;
        }

        .data-table tbody tr {
            transition: background 0.2s;
        }

        .data-table tbody tr:hover {
            background: #f9fafb;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-exam-completed, .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-in-progress, .status-interview-scheduled {
            background: #dbeafe;
            color: #1e40af;
        }

        .quick-access {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
        }

        .access-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }

        .access-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .access-item {
            margin-bottom: 0;
        }

        .access-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #374151;
            transition: all 0.2s;
            font-size: 14px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background: white;
        }

        .access-link:hover {
            background: #f9fafb;
            color: #800020;
        }

        .access-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 24px;
        }

        .access-content {
            flex: 0;
        }

        .access-name {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .access-desc {
            font-size: 12px;
            color: #9ca3af;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 16px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .empty-message {
            font-size: 14px;
            color: #9ca3af;
        }
    </style>
@endpush

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Total Applicants</div>
                <div class="stat-value" data-stat="total">{{ $stats['total'] ?? $stats['total_applicants'] ?? 0 }}</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Exams Completed</div>
                <div class="stat-value" data-stat="exam_completed">{{ $stats['exam_completed'] ?? 0 }}</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Interviews Scheduled</div>
                <div class="stat-value" data-stat="interview_scheduled">{{ $stats['interview_scheduled'] ?? $stats['interviews_scheduled'] ?? 0 }}</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Pending Reviews</div>
                <div class="stat-value" data-stat="pending">{{ $stats['pending'] ?? $stats['pending_reviews'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <!-- Recent Applicants -->
    <div class="recent-table-container" style="margin-bottom: 30px;">
            <div class="table-header">
                <div class="table-title">Recent Applicants</div>
                <a href="{{ route('admin.applicants.index') }}" class="table-action">View All</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_applicants ?? [] as $applicant)
                    <tr>
                        <td>
                            <div>
                                <div style="font-weight: 500; color: #1f2937;">{{ $applicant->full_name }}</div>
                                <div style="font-size: 12px; color: #9ca3af;">{{ $applicant->email_address }}</div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $applicant->status)) }}">
                                {{ $applicant->status }}
                            </span>
                        </td>
                        <td>{{ $applicant->score ? $applicant->score . '%' : '--' }}</td>
                        <td style="color: #6b7280;">{{ $applicant->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" 
                               style="color: #800020; text-decoration: none; font-weight: 500; font-size: 13px;">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-title">No applicants yet</div>
                                <div class="empty-message">Start by importing or adding applicants</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
    </div>

    <!-- Interview Assignment Progress -->
    @if(isset($interviewProgress) && $interviewProgress->count() > 0)
    <div class="recent-table-container" style="margin-bottom: 30px;">
        <div class="table-header">
            <div class="table-title">Interview Assignment Progress</div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Instructor</th>
                    <th style="text-align: center;">Assigned</th>
                    <th style="text-align: center;">Completed</th>
                    <th style="text-align: center;">Pending</th>
                    <th style="text-align: center;">Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($interviewProgress as $progress)
                <tr>
                    <td>
                        <div style="font-weight: 500; color: #1f2937;">{{ $progress->instructor_name }}</div>
                    </td>
                    <td style="text-align: center; font-weight: 500; color: #1f2937;">{{ $progress->total }}</td>
                    <td style="text-align: center; font-weight: 500; color: #10b981;">{{ $progress->completed }}</td>
                    <td style="text-align: center; font-weight: 500; color: #f59e0b;">{{ $progress->pending }}</td>
                    <td style="text-align: center;">
                        <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                            <div style="flex: 1; max-width: 120px; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; background: {{ $progress->completion_rate == 100 ? '#10b981' : '#3b82f6' }}; width: {{ $progress->completion_rate }}%;"></div>
                            </div>
                            <span style="font-size: 13px; font-weight: 600; color: #6b7280; min-width: 40px;">{{ $progress->completion_rate }}%</span>
                        </div>
                    </td>
                    <td>
                        @if($progress->completion_rate == 100)
                            <span class="status-badge" style="background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">Complete</span>
                        @elseif($progress->has_overdue)
                            <span class="status-badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">Overdue</span>
                        @elseif($progress->has_upcoming_deadline)
                            <span class="status-badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">Due Soon</span>
                        @else
                            <span class="status-badge" style="background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;">In Progress</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Quick Access -->
    <div class="quick-access">
            <div class="access-title">Quick Access</div>
            <ul class="access-list">
                <li class="access-item">
                    <a href="{{ route('admin.applicants.index') }}" class="access-link">
                        <div class="access-icon">👥</div>
                        <div class="access-content">
                            <div class="access-name">Manage Applicants</div>
                            <div class="access-desc">View and manage all applicants</div>
                        </div>
                    </a>
                </li>
                <li class="access-item">
                    <a href="{{ route('admin.questions.create') }}" class="access-link">
                        <div class="access-icon">📝</div>
                        <div class="access-content">
                            <div class="access-name">Add Questions</div>
                            <div class="access-desc">Create new exam questions</div>
                        </div>
                    </a>
                </li>
                <li class="access-item">
                    <a href="{{ route('admin.reports.index') }}" class="access-link">
                        <div class="access-icon">📊</div>
                        <div class="access-content">
                            <div class="access-name">Generate Reports</div>
                            <div class="access-desc">Export applicant data</div>
                        </div>
                    </a>
                </li>
                <li class="access-item">
                    <a href="{{ route('admin.interviews.index') }}" class="access-link">
                        <div class="access-icon">💼</div>
                        <div class="access-content">
                            <div class="access-name">Schedule Interviews</div>
                            <div class="access-desc">Manage interview sessions</div>
                        </div>
                    </a>
                </li>
                <li class="access-item">
                    <a href="{{ route('admin.settings') }}" class="access-link">
                        <div class="access-icon">⚙️</div>
                        <div class="access-content">
                            <div class="access-name">System Settings</div>
                            <div class="access-desc">Configure exam parameters</div>
                        </div>
                    </a>
                </li>
            </ul>
    </div>

@endsection

    @section('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Listen for real-time updates
        if (typeof window.Echo !== 'undefined') {
            window.Echo.channel('dashboard')
                .listen('.statistics.updated', (data) => {
                    // Update stat values with animation
                    if (data.stats) {
                        Object.keys(data.stats).forEach(stat => {
                            const element = document.querySelector(`[data-stat="${stat}"]`);
                            if (element) {
                                element.classList.add('stat-updated');
                                element.textContent = data.stats[stat];
                                setTimeout(() => element.classList.remove('stat-updated'), 600);
                            }
                        });
                    }
                });
        }
    });
    </script>
    @endsection