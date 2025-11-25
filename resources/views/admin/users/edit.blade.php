@extends('layouts.admin')

@section('title', 'Edit User')

@php
    $pageTitle = 'Edit User';
    $pageSubtitle = 'Update user account information';
@endphp

@push('styles')
<style>
    :root {
        --primary-maroon: #800020;
        --primary-gold: #FFD700;
        --dark-maroon: #5C0016;
        --light-gold: #FFF8DC;
        --white: #FFFFFF;
        --light-gray: #F8F9FA;
        --border-gray: #E9ECEF;
        --text-gray: #6B7280;
        --text-dark: #1F2937;
        --danger-red: #DC2626;
        --warning-orange: #D97706;
        --transition: all 0.3s ease;
    }

    /* Breadcrumb Styles */
    .breadcrumb {
        display: flex;
        align-items: center;
        font-size: 14px;
        margin-bottom: 20px;
        padding: 0;
    }

    .breadcrumb-link {
        color: #800020;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .breadcrumb-link:hover {
        color: #5C0016;
        text-decoration: underline;
    }

    .breadcrumb-separator {
        margin: 0 8px;
        color: #9CA3AF;
    }

    .breadcrumb-current {
        color: #1F2937;
        font-weight: 600;
    }

    /* Override main-content padding for this page */
    .main-content {
        padding: 20px !important;
    }

    .user-form-container {
        padding: 0;
        max-width: 100%;
        width: 100%;
        margin: 0;
    }

    .form-card {
        background: var(--white);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
        margin-bottom: 20px;
        max-width: 1000px;
        margin: 0 auto 20px auto;
    }

    .form-card h3 {
        color: var(--primary-maroon);
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--border-gray);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 12px;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
        font-size: 13px;
    }

    .form-group label .required {
        color: var(--danger-red);
        margin-left: 3px;
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="password"],
    .form-group select {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid var(--border-gray);
        border-radius: 6px;
        font-size: 13px;
        transition: var(--transition);
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary-maroon);
        box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
    }

    .form-group .help-text {
        display: block;
        font-size: 11px;
        color: var(--text-gray);
        margin-top: 3px;
    }

    .error-text {
        color: var(--danger-red);
        font-size: 11px;
        margin-top: 3px;
        display: block;
    }

    .form-group input.error,
    .form-group select.error {
        border-color: var(--danger-red);
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--border-gray);
    }

    .btn {
        padding: 8px 14px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 12px;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
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

    .btn-secondary {
        background: var(--light-gray);
        color: var(--text-dark);
        border: 2px solid var(--border-gray);
    }

    .btn-secondary:hover {
        background: var(--border-gray);
    }

    .btn-danger {
        background: var(--danger-red);
        color: var(--white);
    }

    .btn-danger:hover {
        background: #B91C1C;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
    }

    .warning-box {
        background: rgba(217, 119, 6, 0.1);
        border: 2px solid var(--warning-orange);
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--warning-orange);
    }

    .password-section {
        background: var(--light-gray);
        border-radius: 6px;
        padding: 15px;
        margin-top: 15px;
    }

    .password-section h4 {
        margin: 0 0 10px 0;
        color: var(--text-dark);
        font-size: 14px;
    }

    .password-section p {
        font-size: 12px !important;
        margin-bottom: 12px !important;
    }

    @media (max-width: 768px) {
        .user-form-container {
            padding: 0;
        }

        .form-card {
            padding: 15px;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .form-group.full-width {
            grid-column: 1;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="{{ route('admin.users.index') }}" class="breadcrumb-link">Users</a>
    <span class="breadcrumb-separator">›</span>
    <span class="breadcrumb-current">Edit</span>
</div>

<div class="user-form-container">
    @if(session('warning'))
        <div class="warning-box">
            <span style="font-size: 24px;">️</span>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    <div class="form-card">
        <h3>️ Edit User Account</h3>

        <form method="POST" action="{{ route('admin.users.update', $user->user_id) }}">
            @csrf
            @method('PUT')

            <!-- Row 1: Username | Full Name -->
            <div class="form-row">
                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="{{ old('username', $user->username) }}" 
                           class="@error('username') error @enderror"
                           required>
                    @error('username')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                    <span class="help-text">Unique username for login</span>
                </div>

                <!-- Full Name -->
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           value="{{ old('full_name', $user->full_name) }}" 
                           class="@error('full_name') error @enderror"
                           required>
                    @error('full_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                    <span class="help-text">Complete name as it should appear</span>
                </div>
            </div>

            <!-- Row 2: Email Address | Role -->
            <div class="form-row">
                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}" 
                           class="@error('email') error @enderror"
                           required>
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                    <span class="help-text">Valid email address</span>
                </div>

                <!-- Role -->
                <div class="form-group">
                    <label for="role">Role <span class="required">*</span></label>
                    <select id="role" 
                            name="role" 
                            class="@error('role') error @enderror" 
                            required>
                        <option value="department-head" {{ old('role', $user->role) === 'department-head' ? 'selected' : '' }}> Department Head</option>
                        <option value="instructor" {{ old('role', $user->role) === 'instructor' ? 'selected' : '' }}>🧑‍ Instructor</option>
                    </select>
                    @error('role')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                    <span class="help-text">Select user role</span>
                </div>
            </div>

            <!-- Password Section -->
            <div class="password-section">
                <h4> Change Password (Optional)</h4>
                <p style="color: var(--text-gray); font-size: 12px; margin-bottom: 12px;">
                    Leave blank to keep the current password unchanged.
                </p>

                <!-- Row 3: New Password | Confirm New Password -->
                <div class="form-row">
                    <!-- New Password -->
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="@error('password') error @enderror">
                        @error('password')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                        <span class="help-text">Min 8 characters</span>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation">
                        <span class="help-text">Re-enter password</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <span></span> Cancel
                </a>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                    <span>️</span> Delete User
                </button>
                <button type="submit" class="btn btn-primary">
                    <span></span> Update User
                </button>
            </div>
        </form>

        <!-- Hidden Delete Form -->
        <form id="delete-form" method="POST" action="{{ route('admin.users.destroy', $user->user_id) }}" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this user account? This action cannot be undone.')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endpush

