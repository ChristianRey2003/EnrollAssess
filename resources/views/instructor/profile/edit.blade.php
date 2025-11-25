@extends('layouts.instructor')

@section('title', 'My Profile')

@php
    $pageTitle = 'My Profile';
    $pageSubtitle = 'Manage your account information and profile picture';
@endphp

@push('styles')
<style>
    .profile-container {
        padding: 8px 20px 20px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .card-body {
        padding: 14px !important;
    }

    .profile-picture-preview {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #E9ECEF;
        background: #F8F9FA;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6B7280;
        font-weight: 600;
        font-size: 32px;
        margin: 0 auto;
    }

    .profile-picture-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .file-upload-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .file-upload-label {
        cursor: pointer;
    }

    .card-title {
        font-size: 14px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 0;
        padding-bottom: 8px;
        border-bottom: 1px solid #E9ECEF;
    }

    .form-label {
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 3px;
    }

    .form-control {
        font-size: 13px;
        padding: 5px 10px;
        height: calc(1.5em + 0.5rem + 2px);
    }

    .form-control:focus {
        border-color: #800020;
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 32, 0.25);
    }

    .form-text {
        font-size: 11px;
    }

    .invalid-feedback {
        font-size: 11px;
    }

    .btn {
        font-size: 12px;
        padding: 8px 14px;
        font-weight: 500;
    }

    .btn-primary {
        background-color: #800020;
        border-color: #800020;
    }

    .btn-primary:hover {
        background-color: #5C0016;
        border-color: #5C0016;
    }

    .btn-secondary {
        background-color: #F8F9FA;
        border-color: #E9ECEF;
        color: #1F2937;
    }

    .btn-secondary:hover {
        background-color: #E9ECEF;
        border-color: #D1D5DB;
        color: #1F2937;
    }

    .password-section {
        background: #F8F9FA;
        border-radius: 5px;
        padding: 10px;
    }

    .password-section h5 {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    @media (max-width: 968px) {
        .profile-container {
            padding: 16px;
        }
    }

    @media (max-width: 768px) {
        .profile-container {
            padding: 12px;
        }
    }
</style>
@endpush

@section('content')
<div class="profile-container">
    <div class="row g-2">
        <!-- Profile Picture Card -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-2">Profile Picture</h5>
                    
                    <div class="d-flex flex-column align-items-center">
                        <div class="profile-picture-preview mb-2" id="profilePreview">
                            @if($user->profile_picture_url)
                                <img src="{{ $user->profile_picture_url }}" alt="Profile Picture" id="profileImage">
                            @else
                                <span id="profileInitials">{{ $user->initials }}</span>
                            @endif
                        </div>
                        
                        <div class="text-center w-100">
                            <h6 class="mb-1" style="font-size: 12px; font-weight: 600;">Upload Profile Picture</h6>
                            <p class="text-muted mb-2" style="font-size: 11px; line-height: 1.3;">
                                Upload a profile picture. Accepted formats: JPG, PNG, GIF. Maximum size: 2MB.
                            </p>
                            <p id="profilePictureWarning" class="text-danger mb-2" style="font-size: 11px; display: none;">
                                The selected file exceeds the 2MB limit. Please choose a smaller image.
                            </p>
                            
                            <div class="file-upload-wrapper d-inline-block">
                                <input type="file" 
                                       id="profile_picture_preview" 
                                       accept="image/jpeg,image/png,image/jpg,image/gif"
                                       class="file-upload-input"
                                       onchange="previewProfilePicture(this)">
                                <label for="profile_picture_preview" class="btn btn-primary btn-sm file-upload-label">
                                    Choose File
                                </label>
                            </div>
                            
                            @if($user->profile_picture)
                                <form method="POST" action="{{ route('instructor.profile.delete-picture') }}" class="d-inline-block mt-1">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete your profile picture?')">
                                        Delete Picture
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information Card -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-2">Account Information</h5>

                    <form method="POST" action="{{ route('instructor.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Full Name -->
                        <div class="mb-1">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="{{ old('full_name', $user->full_name) }}" 
                                   class="form-control @error('full_name') is-invalid @enderror"
                                   required>
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Username -->
                        <div class="mb-1">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   value="{{ old('username', $user->username) }}" 
                                   class="form-control @error('username') is-invalid @enderror"
                                   required>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This username is used to sign in and must be unique.</div>
                        </div>

                        <!-- Email -->
                        <div class="mb-1">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   class="form-control @error('email') is-invalid @enderror"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Profile Picture Upload (hidden, synced from preview) -->
                        <input type="file" 
                               id="profile_picture" 
                               name="profile_picture" 
                               accept="image/jpeg,image/png,image/jpg,image/gif"
                               style="display: none;">

                        <!-- Password Section -->
                        <div class="password-section mt-1">
                            <h5>Change Password (Optional)</h5>

                            <!-- Current Password -->
                            <div class="mb-1">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       class="form-control @error('current_password') is-invalid @enderror">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div class="mb-1">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimum 8 characters</div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-1">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation"
                                       class="form-control">
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                            <a href="{{ route('instructor.dashboard') }}" class="btn btn-secondary btn-sm">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewProfilePicture(input) {
        const warningEl = document.getElementById('profilePictureWarning');
        const formInput = document.getElementById('profile_picture');
        const maxBytes = 2 * 1024 * 1024; // 2MB limit

        if (input.files && input.files[0]) {
            const file = input.files[0];

            if (file.size > maxBytes) {
                warningEl.style.display = 'block';
                input.value = '';
                formInput.value = '';
                return;
            }

            warningEl.style.display = 'none';

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profilePreview');
                const initials = document.getElementById('profileInitials');
                let image = document.getElementById('profileImage');
                
                if (image) {
                    image.src = e.target.result;
                } else {
                    if (initials) {
                        initials.style.display = 'none';
                    }
                    image = document.createElement('img');
                    image.id = 'profileImage';
                    image.src = e.target.result;
                    image.alt = 'Profile Picture';
                    preview.appendChild(image);
                }
            };
            reader.readAsDataURL(file);
            
            // Sync with form input - create a new FileList
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            formInput.files = dataTransfer.files;
        }
    }
</script>
@endpush

