@extends('layouts.admin')

@section('title', 'Audit Logs')

@php
    $pageTitle = 'Audit Logs';
    $pageSubtitle = 'System activity and audit trail';
@endphp

@push('styles')
<style>
    :root {
        --primary-maroon: #800020;
        --primary-gold: #FFD700;
        --dark-maroon: #5C0016;
        --white: #FFFFFF;
        --light-gray: #F8F9FA;
        --border-gray: #E9ECEF;
        --text-gray: #6B7280;
        --text-dark: #1F2937;
        --success-green: #059669;
        --warning-orange: #D97706;
        --danger-red: #DC2626;
        --info-blue: #3B82F6;
        --transition: all 0.3s ease;
    }

    /* Override main-content padding for this page */
    .main-content {
        padding: 20px !important;
    }

    .audit-logs-container {
        padding: 8px 0 0 0;
        max-width: 100%;
        width: 100%;
    }
    
    .audit-logs-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0;
    }

    .audit-logs-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--white);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        font-size: 24px;
        margin-bottom: 10px;
    }

    .stat-title {
        color: var(--text-gray);
        font-size: 14px;
        margin: 0 0 8px 0;
    }

    .stat-value {
        color: var(--text-dark);
        font-size: 24px;
        font-weight: 700;
        margin: 0;
    }

    .audit-logs-table-container {
        background: var(--white);
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
        overflow: hidden;
    }

    .table-header {
        background: white;
        color: var(--text-dark);
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .search-filter-section {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        width: 250px;
        padding: 8px 32px 8px 12px;
        border: 1px solid var(--border-gray);
        border-radius: 6px;
        font-size: 13px;
        transition: var(--transition);
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary-maroon);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .filter-select {
        padding: 8px 28px 8px 10px;
        border: 1px solid var(--border-gray);
        border-radius: 6px;
        font-size: 13px;
        background: var(--white);
        color: var(--text-dark);
        cursor: pointer;
        transition: var(--transition);
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary-maroon);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .audit-logs-table {
        width: 100%;
        border-collapse: collapse;
    }

    .audit-logs-table th {
        background: var(--light-gray);
        color: var(--text-dark);
        font-weight: 600;
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid var(--border-gray);
        font-size: 0.85rem;
    }

    .audit-logs-table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-gray);
        font-size: 13px;
        vertical-align: top;
    }

    .audit-logs-table tbody tr {
        transition: var(--transition);
    }

    .audit-logs-table tbody tr:hover {
        background: rgba(128, 0, 32, 0.02);
    }

    .action-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .action-badge-create {
        background: rgba(5, 150, 105, 0.1);
        color: var(--success-green);
    }

    .action-badge-update {
        background: rgba(59, 130, 246, 0.1);
        color: var(--info-blue);
    }

    .action-badge-delete {
        background: rgba(220, 38, 38, 0.1);
        color: var(--danger-red);
    }

    .action-badge-login {
        background: rgba(5, 150, 105, 0.1);
        color: var(--success-green);
    }

    .action-badge-logout {
        background: rgba(107, 114, 128, 0.1);
        color: var(--text-gray);
    }

    .action-badge-default {
        background: rgba(217, 119, 6, 0.1);
        color: var(--warning-orange);
    }

    .user-info-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar-small {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-weight: 600;
        font-size: 12px;
        flex-shrink: 0;
    }

    .description-cell {
        max-width: 400px;
        word-wrap: break-word;
    }

    .properties-cell {
        font-size: 11px;
        color: var(--text-gray);
        font-family: monospace;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pagination-wrapper {
        padding: 20px;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .search-filter-section {
            width: 100%;
        }

        .search-box input {
            width: 100%;
        }

        .audit-logs-table {
            font-size: 12px;
        }

        .audit-logs-table th,
        .audit-logs-table td {
            padding: 8px;
        }
    }
</style>
@endpush

@section('content')
<div class="audit-logs-container">
    <div class="audit-logs-header"></div>

    <!-- Statistics Cards -->
    <div class="audit-logs-stats">
        <div class="stat-card">
            <p class="stat-title">Total Logs</p>
            <h3 class="stat-value">{{ number_format($stats['total_logs']) }}</h3>
        </div>
        <div class="stat-card">
            <p class="stat-title">Today's Logs</p>
            <h3 class="stat-value">{{ number_format($stats['today_logs']) }}</h3>
        </div>
        <div class="stat-card">
            <p class="stat-title">Active Users</p>
            <h3 class="stat-value">{{ number_format($stats['unique_users']) }}</h3>
        </div>
        <div class="stat-card">
            <p class="stat-title">Action Types</p>
            <h3 class="stat-value">{{ number_format($stats['unique_actions']) }}</h3>
        </div>
    </div>

    <!-- Table Container -->
    <div class="audit-logs-table-container">
        <!-- Table Header with Filters -->
        <div class="table-header">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="search-filter-section">
                <div class="search-box">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search descriptions..."
                           style="width: 250px; padding: 8px 32px 8px 12px; border: 1px solid var(--border-gray); border-radius: 6px; font-size: 13px;">
                    <svg style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; pointer-events: none; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <select name="action" class="filter-select" onchange="this.form.submit()" style="width: 180px; min-width: 180px;">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $action)) }}
                        </option>
                    @endforeach
                </select>
                <input type="date" 
                       name="date_from" 
                       value="{{ request('date_from') }}" 
                       class="filter-select"
                       style="width: 150px;"
                       placeholder="From Date">
                <input type="date" 
                       name="date_to" 
                       value="{{ request('date_to') }}" 
                       class="filter-select"
                       style="width: 150px;"
                       placeholder="To Date">
                @if(request()->anyFilled(['search', 'action', 'date_from', 'date_to']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary" style="white-space: nowrap;">
                        Clear Filters
                    </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <table class="audit-logs-table">
            <thead>
                <tr>
                    <th class="text-left">Timestamp</th>
                    <th class="text-left">User</th>
                    <th class="text-left">Action</th>
                    <th class="text-left">Description</th>
                    <th class="text-left">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="text-left" style="font-size: 13px; font-weight: normal; white-space: nowrap;">
                        {{ $log->created_at->format('M d, Y') }}<br>
                        <span style="color: var(--text-gray); font-size: 11px;">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td class="text-left" style="font-size: 13px; font-weight: normal;">
                        @if($log->user)
                            <div class="user-info-cell">
                                <div class="user-avatar-small">
                                    {{ $log->user->initials }}
                                </div>
                                <div>
                                    <div style="font-weight: 500;">{{ $log->user->full_name }}</div>
                                    <div style="font-size: 11px; color: var(--text-gray);">{{ $log->user->email }}</div>
                                </div>
                            </div>
                        @else
                            <span style="color: var(--text-gray); font-style: italic;">System</span>
                        @endif
                    </td>
                    <td class="text-left" style="font-size: 13px; font-weight: normal;">
                        @php
                            $badgeClass = 'action-badge-default';
                            if (str_contains($log->action, 'create') || str_contains($log->action, 'login')) {
                                $badgeClass = 'action-badge-create';
                            } elseif (str_contains($log->action, 'update') || str_contains($log->action, 'change')) {
                                $badgeClass = 'action-badge-update';
                            } elseif (str_contains($log->action, 'delete') || str_contains($log->action, 'remove')) {
                                $badgeClass = 'action-badge-delete';
                            } elseif (str_contains($log->action, 'logout')) {
                                $badgeClass = 'action-badge-logout';
                            }
                        @endphp
                        <span class="action-badge {{ $badgeClass }}">
                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                        </span>
                    </td>
                    <td class="text-left description-cell" style="font-size: 13px; font-weight: normal;">
                        {{ $log->description }}
                        @if($log->properties && count($log->properties) > 0)
                            <div class="properties-cell" title="{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}">
                                <strong>Details:</strong> {{ json_encode($log->properties) }}
                            </div>
                        @endif
                    </td>
                    <td class="text-left" style="font-size: 13px; font-weight: normal; color: var(--text-gray);">
                        {{ $log->ip_address ?? 'N/A' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="text-muted">
                            <h5>No audit logs found</h5>
                            <p class="mb-0">Activity logs will appear here as users interact with the system.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="pagination-wrapper">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

