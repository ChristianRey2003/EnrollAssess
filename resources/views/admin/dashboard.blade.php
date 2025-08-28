@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Computer Studies Department';
@endphp

@section('content')
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value">{{ $stats['total_applicants'] ?? 0 }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-value">{{ $stats['exam_completed'] ?? 0 }}</div>
                        <div class="stat-label">Exams Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-value">{{ $stats['interviews_scheduled'] ?? 0 }}</div>
                        <div class="stat-label">Interviews Scheduled</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value">{{ $stats['pending_reviews'] ?? 0 }}</div>
                        <div class="stat-label">Pending Reviews</div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Applicant Activity</h2>
                        <a href="{{ route('admin.applicants.index') }}" class="section-action">
                            <span class="section-action-icon">👁️</span>
                            View All
                        </a>
                    </div>
                    <div class="section-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Applicant Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_applicants ?? [] as $applicant)
                                <tr>
                                    <td>{{ $applicant->full_name }}</td>
                                    <td>{{ $applicant->email_address }}</td>
                                    <td>
                                        <span class="status-badge status-{{ strtolower($applicant->status) }}">
                                            {{ $applicant->status }}
                                        </span>
                                    </td>
                                    <td>{{ $applicant->score ? $applicant->score . '%' : '--' }}</td>
                                    <td>{{ $applicant->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" 
                                               class="action-btn action-btn-view"
                                               aria-label="View details for {{ $applicant->full_name }}">
                                                <span aria-hidden="true">👁️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <!-- Demo data when no applicants exist -->
                                <tr>
                                    <td>John Doe</td>
                                    <td>john.doe@email.com</td>
                                    <td><span class="status-badge status-completed">Completed</span></td>
                                    <td>85%</td>
                                    <td>{{ now()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-view" 
                                               aria-label="View details">
                                                <span aria-hidden="true">👁️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jane Smith</td>
                                    <td>jane.smith@email.com</td>
                                    <td><span class="status-badge status-in-progress">In Progress</span></td>
                                    <td>--</td>
                                    <td>{{ now()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-view" 
                                               aria-label="View details">
                                                <span aria-hidden="true">👁️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Mike Johnson</td>
                                    <td>mike.j@email.com</td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                    <td>--</td>
                                    <td>{{ now()->subDay()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-view" 
                                               aria-label="View details">
                                                <span aria-hidden="true">👁️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Sarah Williams</td>
                                    <td>sarah.w@email.com</td>
                                    <td><span class="status-badge status-completed">Completed</span></td>
                                    <td>92%</td>
                                    <td>{{ now()->subDay()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-view" 
                                               aria-label="View details">
                                                <span aria-hidden="true">👁️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>David Brown</td>
                                    <td>david.brown@email.com</td>
                                    <td><span class="status-badge status-in-progress">In Progress</span></td>
                                    <td>--</td>
                                    <td>{{ now()->subDays(2)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-view" 
                                               aria-label="View details">
                                                <span aria-hidden="true">👁️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Quick Actions</h2>
                    </div>
                    <div class="section-content" style="padding: 24px 30px;">
                        <div class="quick-actions-grid">
                            <a href="{{ route('admin.questions.create') }}" class="quick-action-card">
                                <div class="quick-action-icon">➕</div>
                                <div class="quick-action-title">Add New Question</div>
                                <div class="quick-action-desc">Create a new exam question</div>
                            </a>
                            <a href="{{ route('admin.applicants.index') }}" class="quick-action-card">
                                <div class="quick-action-icon">📊</div>
                                <div class="quick-action-title">Generate Report</div>
                                <div class="quick-action-desc">Export applicant data</div>
                            </a>
                            <a href="{{ route('admin.settings') }}" class="quick-action-card">
                                <div class="quick-action-icon">⚙️</div>
                                <div class="quick-action-title">System Settings</div>
                                <div class="quick-action-desc">Configure exam parameters</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection