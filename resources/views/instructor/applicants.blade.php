@extends('layouts.instructor')

@section('title', 'My Applicants')

@php
    $pageTitle = 'My Assigned Applicants';
    $pageSubtitle = 'Manage interviews and evaluations for your assigned applicants';
@endphp

@push('styles')
<style>
    .applicants-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .filters-section {
        background: white;
        padding: 20px 24px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
        margin-bottom: 24px;
    }

    .filters-form {
        display: flex;
        gap: 16px;
        align-items: end;
    }

    .form-group {
        flex: 1;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 0.875rem;
    }

    .form-input, .form-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--maroon-primary);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .btn {
        padding: 10px 20px;
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

    .applicants-table-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
        overflow: hidden;
    }

    .table-header {
        padding: 20px 24px;
        background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .table-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
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

    .applicants-table tr:hover {
        background: #F9FAFB;
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
        flex-shrink: 0;
    }

    .applicant-name {
        font-weight: 600;
        color: #1F2937;
    }

    .applicant-email {
        font-size: 0.875rem;
        color: #6B7280;
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

    .status-interviewcompleted {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: #6B7280;
    }

    .empty-state h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .pagination-wrapper {
        padding: 20px 24px;
        background: #F9FAFB;
        border-top: 1px solid #E5E7EB;
    }

    @media (max-width: 768px) {
        .filters-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .applicants-table {
            font-size: 0.875rem;
        }
        
        .applicants-table th,
        .applicants-table td {
            padding: 12px 8px;
        }
    }
</style>
@endpush

@section('content')
<div class="applicants-container">
    <!-- Filters and Search -->
    <div class="filters-section">
        <form method="GET" action="{{ route('instructor.applicants') }}" class="filters-form">
            <div class="form-group">
                <label class="form-label">Search Applicants</label>
                <input type="text" name="search" class="form-input" 
                       placeholder="Search by name or email..."
                       value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="exam-completed" {{ request('status') == 'exam-completed' ? 'selected' : '' }}>Exam Completed</option>
                    <option value="interview-completed" {{ request('status') == 'interview-completed' ? 'selected' : '' }}>Interview Completed</option>
                    <option value="admitted" {{ request('status') == 'admitted' ? 'selected' : '' }}>Admitted</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>

    <!-- Applicants Table -->
    <div class="applicants-table-section">
        <div class="table-header">
            <h2 class="table-title">Assigned Applicants ({{ $assignedApplicants->total() }})</h2>
        </div>
        
        @if($assignedApplicants->count() > 0)
            <table class="applicants-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Application No.</th>
                        <th>Exam Score</th>
                        <th>Status</th>
                        <th>Interview Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignedApplicants as $applicant)
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
                            @if($applicant->interviews->first() && $applicant->interviews->first()->schedule_date)
                                {{ $applicant->interviews->first()->schedule_date->format('M d, Y g:i A') }}
                            @else
                                <span style="color: #6B7280;">Not scheduled</span>
                            @endif
                        </td>
                        <td>
                            @if($applicant->status === 'exam-completed')
                                <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                   class="btn btn-primary">
                                    Start Interview
                                </a>
                            @elseif($applicant->status === 'interview-completed')
                                <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                   class="btn btn-secondary">
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

            <!-- Pagination -->
            @if($assignedApplicants->hasPages())
            <div class="pagination-wrapper">
                {{ $assignedApplicants->links() }}
            </div>
            @endif
        @else
            <div class="empty-state">
                <h3>No Applicants Found</h3>
                <p>No applicants match your current search criteria. Try adjusting your filters or check back later.</p>
            </div>
        @endif
    </div>
</div>
@endsection
