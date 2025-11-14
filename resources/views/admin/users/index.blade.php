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

    /* Override main-content padding for this page */
    .main-content {
        padding: 20px !important;
    }

    .users-container {
        padding: 0;
        max-width: 100%;
        width: 100%;
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
        position: relative;
    }

    .users-table tbody tr:hover {
        background: rgba(128, 0, 32, 0.02);
    }

    .users-table tbody tr:hover .floating-actions {
        opacity: 1 !important;
        visibility: visible !important;
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
        overflow: hidden;
        flex-shrink: 0;
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
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

    .users-table tbody tr td:first-child {
        padding-right: 200px;
    }

    .floating-actions {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        gap: 6px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        z-index: 10;
        pointer-events: none;
    }

    .users-table tbody tr:hover .floating-actions {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .floating-actions .btn {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        pointer-events: auto;
    }

    .btn {
        padding: 8px 14px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-size: 12px;
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

    /* Pagination spacing */
    .pagination-wrapper {
        padding: 20px;
    }

    /* Style the pagination nav container */
    .pagination-wrapper nav {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    /* Style for the "Showing X to Y of Z results" text */
    .pagination-wrapper nav > div:first-child,
    .pagination-wrapper nav > p:first-child {
        color: var(--text-gray);
        font-size: 14px;
        margin: 0;
    }

    /* Spacing before pagination buttons */
    .pagination-wrapper .relative.z-0.inline-flex {
        margin-left: 20px;
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
            <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 10px; align-items: center;">
                <div style="position: relative; width: 220px;">
                    <input type="text" 
                           id="searchInput"
                           name="search" 
                           placeholder="Search..." 
                           value="{{ request('search') }}"
                           class="form-control form-control-sm"
                           style="width: 100%; height: 26px; padding: 4px 32px 4px 8px;">
                    <svg style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; pointer-events: none; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 180px; min-width: 180px; height: 40px; padding: 4px 28px 4px 8px;">
                    <option value="">All Roles</option>
                    <option value="department-head" {{ request('role') === 'department-head' ? 'selected' : '' }}>Department Head</option>
                    <option value="administrator" {{ request('role') === 'administrator' ? 'selected' : '' }}>Administrator</option>
                    <option value="instructor" {{ request('role') === 'instructor' ? 'selected' : '' }}>Instructor</option>
                </select>
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="white-space: nowrap;">
                Add New User
            </a>
        </div>
        
        <table class="table table-hover table-striped align-middle users-table">
            <thead style="background-color: white;">
                <tr>
                    <th style="font-size: 0.85rem; font-weight: bold;" class="text-left">User</th>
                    <th style="font-size: 0.85rem; font-weight: bold;" class="text-left">Role</th>
                    <th style="font-size: 0.85rem; font-weight: bold;" class="text-center">Created</th>
                    <th style="font-size: 0.85rem; font-weight: bold;" class="text-center">Last login</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-left" style="font-size: 13px; font-weight: normal;">
                        <div class="user-info">
                            <div class="user-avatar">
                                @if($user->profile_picture_url)
                                    <img src="{{ $user->profile_picture_url }}" alt="{{ $user->full_name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                @else
                                    {{ $user->initials }}
                                @endif
                            </div>
                            <div class="user-details">
                                <h4 style="font-weight: normal;">
                                    <a href="{{ route('admin.users.show', $user->user_id) }}">
                                        {{ $user->full_name }}
                                    </a>
                                </h4>
                                <p>{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="floating-actions">
                            @if($user->user_id === auth()->id())
                                <a href="{{ route('admin.profile.edit') }}" 
                                   class="btn btn-sm btn-secondary">
                                    My Profile
                                </a>
                            @else
                                <a href="{{ route('admin.users.show', $user->user_id) }}" 
                                   class="btn btn-sm btn-secondary">
                                    View
                                </a>
                                <a href="{{ route('admin.users.edit', $user->user_id) }}" 
                                   class="btn btn-sm btn-secondary">
                                    Edit
                                </a>
                            @endif
                        </div>
                    </td>
                    <td class="text-left" style="font-size: 13px; font-weight: normal;">
                        <span class="badge bg-secondary">
                            @if($user->role === 'department-head')
                                 Department Head
                            @elseif($user->role === 'administrator')
                                 Administrator
                            @else
                                Instructor
                            @endif
                        </span>
                    </td>
                    <td class="text-center" style="font-size: 13px; font-weight: normal;">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="text-center" style="font-size: 13px; font-weight: normal;">{{ $user->updated_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="text-muted">
                            <h5>No users found</h5>
                            <p class="mb-0">
                                <a href="{{ route('admin.users.create') }}" class="btn btn-sm" style="background: #800020; color: white; border: none;">
                                    Add your first user
                                </a>
                            </p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if(isset($users) && $users->hasPages())
            <div class="pagination-wrapper" style="padding: 20px;">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-search functionality
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const searchValue = e.target.value.trim();
            
            searchTimeout = setTimeout(function() {
                const url = new URL(window.location);
                if (searchValue) {
                    url.searchParams.set('search', searchValue);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('page'); // Reset to first page
                window.location.href = url.toString();
            }, 500); // 500ms debounce
        });
    }
</script>
@endpush
