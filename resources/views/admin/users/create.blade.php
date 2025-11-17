@extends('layouts.admin')

@section('title', 'Create New User')

@php
    $pageTitle = 'Create New User';
    $pageSubtitle = 'Add a new user account to the system';
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
        max-width: 1000px;
        margin: 0 auto;
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

    .role-descriptions {
        background: var(--light-gray);
        border-radius: 6px;
        padding: 10px;
        margin-top: 10px;
        font-size: 11px;
    }

    .role-descriptions h4 {
        margin: 0 0 6px 0;
        color: var(--text-dark);
        font-size: 12px;
    }

    .role-descriptions ul {
        margin: 0;
        padding-left: 18px;
        color: var(--text-gray);
    }

    .role-descriptions li {
        margin: 3px 0;
    }

    @media (max-width: 768px) {
        .user-form-container {
            padding: 15px;
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
    <span class="breadcrumb-current">Create</span>
</div>

<div class="user-form-container">
    <div class="form-card">
        <h3> User Account Information</h3>

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <!-- Row 1: Username | Full Name -->
            <div class="form-row">
                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="{{ old('username') }}" 
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
                           value="{{ old('full_name') }}" 
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
                           value="{{ old('email') }}" 
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
                            required
                            onchange="showRoleDescription()">
                        <option value="">Select a role...</option>
                        <option value="department-head" {{ old('role') === 'department-head' ? 'selected' : '' }}> Department Head</option>
                        <option value="administrator" {{ old('role') === 'administrator' ? 'selected' : '' }}> Administrator</option>
                        <option value="instructor" {{ old('role') === 'instructor' ? 'selected' : '' }}>🧑‍ Instructor</option>
                    </select>
                    @error('role')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                    <span class="help-text">Select user role</span>
                </div>
            </div>

            <!-- Role Descriptions (Full Width) -->
            <div class="form-group full-width">
                <div class="role-descriptions" id="role-description" style="display: none;">
                    <h4>Role Permissions:</h4>
                    <ul id="role-permissions"></ul>
                </div>
            </div>

            <!-- Row 3: Password | Confirm Password -->
            <div class="form-row">
                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="@error('password') error @enderror"
                           required>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                    <span class="help-text">Min 8 characters</span>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password <span class="required">*</span></label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required>
                    <span class="help-text">Re-enter password</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <span></span> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <span></span> Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rolePermissions = {
        'department-head': [
            'Full system administration',
            'Manage all users and roles',
            'Generate final reports',
            'Configure system settings',
            'Access all applicant data',
            'Override exam and interview scores'
        ],
        'administrator': [
            'Manage exam questions',
            'View and manage applicants',
            'Generate access codes',
            'Schedule interviews',
            'Update applicant status',
            'Export applicant data'
        ],
        'instructor': [
            'View assigned applicants',
            'Conduct interviews',
            'Submit interview scores',
            'Add interview notes',
            'View exam results',
            'Generate basic reports'
        ]
    };

    function showRoleDescription() {
        const role = document.getElementById('role').value;
        const descriptionDiv = document.getElementById('role-description');
        const permissionsList = document.getElementById('role-permissions');

        if (role && rolePermissions[role]) {
            permissionsList.innerHTML = rolePermissions[role]
                .map(perm => `<li>${perm}</li>`)
                .join('');
            descriptionDiv.style.display = 'block';
        } else {
            descriptionDiv.style.display = 'none';
        }
    }

    // Show role description if role is already selected (after validation error)
    document.addEventListener('DOMContentLoaded', function() {
        showRoleDescription();
    });
</script>
@endpush

