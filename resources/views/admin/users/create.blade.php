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

    .user-form-container {
        padding: 30px;
        max-width: 800px;
        margin: 0 auto;
    }

    .form-card {
        background: var(--white);
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
    }

    .form-card h3 {
        color: var(--primary-maroon);
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 25px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border-gray);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
        font-size: 14px;
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
        padding: 10px 15px;
        border: 2px solid var(--border-gray);
        border-radius: 8px;
        font-size: 14px;
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
        font-size: 13px;
        color: var(--text-gray);
        margin-top: 5px;
    }

    .error-text {
        color: var(--danger-red);
        font-size: 13px;
        margin-top: 5px;
        display: block;
    }

    .form-group input.error,
    .form-group select.error {
        border-color: var(--danger-red);
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 2px solid var(--border-gray);
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
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
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
        font-size: 13px;
    }

    .role-descriptions h4 {
        margin: 0 0 10px 0;
        color: var(--text-dark);
        font-size: 14px;
    }

    .role-descriptions ul {
        margin: 0;
        padding-left: 20px;
        color: var(--text-gray);
    }

    .role-descriptions li {
        margin: 5px 0;
    }

    @media (max-width: 768px) {
        .user-form-container {
            padding: 15px;
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
<div class="user-form-container">
    <div class="form-card">
        <h3> User Account Information</h3>

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

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
                <span class="help-text">Unique username for login (letters, numbers, and underscores only)</span>
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
                <span class="help-text">Complete name as it should appear in the system</span>
            </div>

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
                <span class="help-text">Valid email address for notifications and password recovery</span>
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

                <!-- Role Descriptions -->
                <div class="role-descriptions" id="role-description" style="display: none;">
                    <h4>Role Permissions:</h4>
                    <ul id="role-permissions"></ul>
                </div>
            </div>

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
                <span class="help-text">Minimum 8 characters with letters and numbers</span>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation">Confirm Password <span class="required">*</span></label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       required>
                <span class="help-text">Re-enter the password to confirm</span>
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

