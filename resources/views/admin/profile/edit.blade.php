@extends('layouts.admin')

@section('title', 'My Profile')

@php
    $pageTitle = 'My Profile';
    $pageSubtitle = 'Manage your account information and profile picture';
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
        --success-green: #059669;
        --warning-orange: #D97706;
        --danger-red: #DC2626;
        --info-blue: #3B82F6;
        --transition: all 0.3s ease;
    }

    .profile-container {
        padding: 30px;
        max-width: 900px;
        margin: 0 auto;
    }

    .form-card {
        background: var(--white);
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
        margin-bottom: 20px;
    }

    .form-card h3 {
        color: var(--primary-maroon);
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 25px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border-gray);
    }

    .profile-picture-section {
        display: flex;
        align-items: center;
        gap: 30px;
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 2px solid var(--border-gray);
    }

    .profile-picture-preview {
        position: relative;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid var(--primary-maroon);
        background: linear-gradient(135deg, var(--primary-gold), #DAA520);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-maroon);
        font-weight: 700;
        font-size: 48px;
        flex-shrink: 0;
    }

    .profile-picture-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-picture-info {
        flex: 1;
    }

    .profile-picture-info h4 {
        margin: 0 0 10px 0;
        color: var(--text-dark);
        font-size: 16px;
    }

    .profile-picture-info p {
        margin: 0 0 15px 0;
        color: var(--text-gray);
        font-size: 14px;
    }

    .file-upload-wrapper {
        position: relative;
        display: inline-block;
    }

    .file-upload-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .file-upload-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: var(--primary-maroon);
        color: var(--white);
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: var(--transition);
    }

    .file-upload-label:hover {
        background: var(--dark-maroon);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(128, 0, 32, 0.3);
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
    .form-group input[type="password"] {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid var(--border-gray);
        border-radius: 8px;
        font-size: 14px;
        transition: var(--transition);
    }

    .form-group input:focus {
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

    .form-group input.error {
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

    .btn-danger {
        background: var(--danger-red);
        color: var(--white);
    }

    .btn-danger:hover {
        background: #B91C1C;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
    }

    .password-section {
        background: var(--light-gray);
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }

    .password-section h4 {
        margin: 0 0 15px 0;
        color: var(--text-dark);
        font-size: 16px;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background: rgba(5, 150, 105, 0.1);
        border: 2px solid var(--success-green);
        color: var(--success-green);
    }

    @media (max-width: 768px) {
        .profile-container {
            padding: 15px;
        }

        .profile-picture-section {
            flex-direction: column;
            text-align: center;
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
<div class="profile-container">
    @if(session('success'))
        <div class="alert alert-success">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="form-card">
        <h3>📸 Profile Picture</h3>

        <div class="profile-picture-section">
            <div class="profile-picture-preview" id="profilePreview">
                @if($user->profile_picture_url)
                    <img src="{{ $user->profile_picture_url }}" alt="Profile Picture" id="profileImage">
                @else
                    <span id="profileInitials">{{ $user->initials }}</span>
                @endif
            </div>
            <div class="profile-picture-info">
                <h4>Upload Profile Picture</h4>
                <p>Upload a profile picture to personalize your account. Accepted formats: JPG, PNG, GIF. Maximum size: 2MB.</p>
                <div class="file-upload-wrapper">
                    <input type="file" 
                           id="profile_picture_preview" 
                           accept="image/jpeg,image/png,image/jpg,image/gif"
                           class="file-upload-input"
                           onchange="previewProfilePicture(this)">
                    <label for="profile_picture_preview" class="file-upload-label">
                        <span>📁</span> Choose File
                    </label>
                </div>
                @if($user->profile_picture)
                    <form method="POST" action="{{ route('admin.profile.delete-picture') }}" style="display: inline-block; margin-top: 10px;">
                        @csrf
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete your profile picture?')">
                            <span>🗑️</span> Delete Picture
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="form-card">
        <h3>👤 Account Information</h3>

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

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
            </div>

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
            </div>

            <!-- Profile Picture Upload (hidden, synced from preview) -->
            <input type="file" 
                   id="profile_picture" 
                   name="profile_picture" 
                   accept="image/jpeg,image/png,image/jpg,image/gif"
                   style="display: none;">

            <!-- Password Section -->
            <div class="password-section">
                <h4>🔒 Change Password (Optional)</h4>
                <p style="color: var(--text-gray); font-size: 14px; margin-bottom: 15px;">
                    Leave blank to keep the current password unchanged.
                </p>

                <!-- Current Password -->
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           class="@error('current_password') error @enderror">
                    @error('current_password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

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
                    <span class="help-text">Minimum 8 characters</span>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Profile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewProfilePicture(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profilePreview');
                const initials = document.getElementById('profileInitials');
                const image = document.getElementById('profileImage');
                
                if (image) {
                    image.src = e.target.result;
                } else {
                    if (initials) {
                        initials.style.display = 'none';
                    }
                    const img = document.createElement('img');
                    img.id = 'profileImage';
                    img.src = e.target.result;
                    img.alt = 'Profile Picture';
                    preview.appendChild(img);
                }
            };
            reader.readAsDataURL(input.files[0]);
            
            // Sync with form input - create a new FileList
            const formInput = document.getElementById('profile_picture');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(input.files[0]);
            formInput.files = dataTransfer.files;
        }
    }
</script>
@endpush

