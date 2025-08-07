<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>BSIT Entrance Examination - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- University Theme CSS -->
    <link href="{{ asset('css/auth/university-auth.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- University Header -->
            <div class="auth-header">
                <div class="university-logo">
                    <!-- Replace 'logo.png' with your actual logo filename -->
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo" style="width: 60px; height: 60px; object-fit: contain;">
                </div>
                <h1 class="university-name">BSIT Entrance Examination</h1>
                <p class="auth-subtitle">Computer Studies Department</p>
            </div>

            <!-- Access Code Form -->
            <div class="auth-body">
                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('applicant.verify') }}" id="accessForm">
                    @csrf

                    <!-- Access Code -->
                    <div class="form-group">
                        <label for="access_code" class="form-label">{{ __('Enter Your Access Code') }}</label>
                        <input id="access_code" 
                               class="form-control @error('access_code') is-invalid @enderror" 
                               type="text" 
                               name="access_code" 
                               value="{{ old('access_code') }}" 
                               required 
                               autofocus 
                               autocomplete="off"
                               maxlength="20"
                               placeholder="Enter the code">
                        @error('access_code')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <span id="buttonText">{{ __('Begin Examination') }}</span>
                    </button>

                    <!-- Information Note -->
                    <div class="auth-links">
                        <div class="info-note">
                            <div class="info-note-text">
                                <strong>📋 Important:</strong>
                                You will receive your access code from the Computer Studies Department.<br>
                                Please ensure you have a stable internet connection before beginning the examination.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Enhanced form interaction
        document.getElementById('accessForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const buttonText = document.getElementById('buttonText');
            const accessCode = document.getElementById('access_code').value;
            
            if (accessCode.length < 3) {
                e.preventDefault();
                alert('Please enter a valid access code.');
                return;
            }
            
            // Show loading state during form submission
            submitBtn.disabled = true;
            buttonText.textContent = 'Verifying...';
        });

        // Auto-format access code input
        document.getElementById('access_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });

        // Remove error state on input
        document.getElementById('access_code').addEventListener('input', function(e) {
            e.target.classList.remove('is-invalid');
            const errorMsg = e.target.parentElement.querySelector('.invalid-feedback');
            if (errorMsg) {
                errorMsg.remove();
            }
        });

        // Focus animations are now handled by CSS
    </script>
</body>
</html> 