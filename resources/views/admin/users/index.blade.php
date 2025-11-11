@extends('layouts.admin')

@section('title', 'User Management')

@php
    $pageTitle = 'User Management';
    $pageSubtitle = 'Manage system users and their roles';
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
        --transition: all 0.3s ease;
    }

    .users-container {
        padding: 30px;
    }

    .users-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .users-stats {
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

    .users-table-container {
        background: var(--white);
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
        overflow: hidden;
    }

    .table-header {
        background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
        color: var(--white);
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
    }

    .search-box input,
    .filter-select {
        padding: 8px 15px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
        color: var(--white);
        font-size: 14px;
        transition: var(--transition);
    }

    .search-box input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .search-box input:focus,
    .filter-select:focus {
        outline: none;
        border-color: var(--primary-gold);
        background: rgba(255, 255, 255, 0.2);
    }

    .filter-select option {
        color: var(--text-dark);
        background: var(--white);
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table th {
        background: var(--light-gray);
        color: var(--text-dark);
        font-weight: 600;
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid var(--border-gray);
        font-size: 14px;
    }

    .users-table td {
        padding: 15px;
        border-bottom: 1px solid var(--border-gray);
        font-size: 14px;
    }

    .users-table tbody tr {
        transition: var(--transition);
    }

    .users-table tbody tr:hover {
        background: rgba(128, 0, 32, 0.02);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-maroon), var(--primary-gold));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-weight: 600;
        font-size: 16px;
    }

    .user-details h4 {
        margin: 0 0 4px 0;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-dark);
    }

    .user-details h4 a {
        color: var(--text-dark);
        text-decoration: none;
        transition: var(--transition);
    }

    .user-details h4 a:hover {
        color: var(--primary-maroon);
    }

    .user-details p {
        margin: 0;
        font-size: 13px;
        color: var(--text-gray);
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .role-badge.department-head {
        background: rgba(255, 215, 0, 0.2);
        color: #B8860B;
    }

    .role-badge.administrator {
        background: rgba(128, 0, 32, 0.1);
        color: var(--primary-maroon);
    }

    .role-badge.instructor {
        background: rgba(217, 119, 6, 0.1);
        color: var(--warning-orange);
    }

    .user-actions {
        display: flex;
        gap: 8px;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-primary {
        background: var(--primary-maroon);
        color: var(--white);
    }

    .btn-primary:hover {
        background: var(--dark-maroon);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(128, 0, 32, 0.3);
    }

    .btn-edit {
        background: rgba(59, 130, 246, 0.1);
        color: #2563EB;
    }

    .btn-edit:hover {
        background: #2563EB;
        color: var(--white);
    }

    .pagination {
        padding: 20px;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .users-container {
            padding: 15px;
        }

        .users-table-container {
            overflow-x: auto;
        }

        .users-table {
            min-width: 600px;
        }
    }
</style>
@endpush

@section('content')
<div class="users-container">
    <!-- Users Table -->
    <div class="users-table-container">
        <div class="table-header">
            <h3>Users</h3>
            <div class="search-filter-section" style="display: flex; gap: 12px; align-items: center;">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="padding: 8px 16px; white-space: nowrap;">
                    Add New User
                </a>
                <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 10px; align-items: center;">
                    <label style="font-size: 13px; color: rgba(255,255,255,0.9);">Search:</label>
                    <div class="search-box">
                        <input type="text" 
                               name="search" 
                               placeholder="Search..." 
                               value="{{ request('search') }}"
                               style="width: 180px;">
                    </div>
                    <label style="font-size: 13px; color: rgba(255,255,255,0.9);">Role:</label>
                    <select name="role" class="filter-select" onchange="this.form.submit()" style="width: 140px;">
                        <option value="">All Roles ▼</option>
                        <option value="department-head" {{ request('role') === 'department-head' ? 'selected' : '' }}>Department Head</option>
                        <option value="administrator" {{ request('role') === 'administrator' ? 'selected' : '' }}>Administrator</option>
                        <option value="instructor" {{ request('role') === 'instructor' ? 'selected' : '' }}>Instructor</option>
                    </select>
                </form>
            </div>
        </div>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">
                                {{ strtoupper(substr($user->full_name, 0, 2)) }}
                            </div>
                            <div class="user-details">
                                <h4>
                                    <a href="{{ route('admin.users.show', $user->user_id) }}">
                                        {{ $user->full_name }}
                                    </a>
                                </h4>
                                <p>{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-badge {{ $user->role }}">
                            @if($user->role === 'department-head')
                                 Department Head
                            @elseif($user->role === 'administrator')
                                 Administrator
                            @else
                                🧑‍ Instructor
                            @endif
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td>{{ $user->updated_at->diffForHumans() }}</td>
                    <td>
                        <div class="user-actions">
                            <a href="{{ route('admin.users.show', $user->user_id) }}" 
                               class="btn btn-edit">
                                ️ View
                            </a>
                            <a href="{{ route('admin.users.edit', $user->user_id) }}" 
                               class="btn btn-edit">
                                ️ Edit
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-gray);">
                        No users found. 
                        <a href="{{ route('admin.users.create') }}" style="color: var(--primary-maroon);">
                            Add your first user
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="pagination">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
