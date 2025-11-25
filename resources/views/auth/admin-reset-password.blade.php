<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Reset Password - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- University Theme CSS -->
    <link href="{{ asset('css/auth/university-auth.css') }}?v={{ time() }}" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- University Header -->
            <div class="auth-header">
                <div class="university-logo">
                    <img src="{{ asset('images/evsu-logo.png') }}" alt="EVSU Logo" style="width: 60px; height: 60px; object-fit: contain;">
                </div>
                <h1 class="university-name">Reset Password</h1>
                <p class="auth-subtitle">Faculty Portal - Computer Studies Department</p>
            </div>

            <!-- Reset Password Form -->
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

                <form method="POST" action="{{ route('admin.password.store') }}" id="resetPasswordForm">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div class="form-group">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        <input id="email" 
                               class="form-control admin-input @error('email') is-invalid @enderror" 
                               type="email" 
                               name="email" 
                               value="{{ old('email', $request->input('email')) }}" 
                               required 
                               autofocus 
                               autocomplete="email"
                               placeholder="Enter your email address">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">{{ __('New Password') }}</label>
                        <div class="password-input-wrapper">
                            <input id="password" 
                                   class="form-control admin-input @error('password') is-invalid @enderror"
                                   type="password"
                                   name="password"
                                   required 
                                   autocomplete="new-password"
                                   placeholder="Enter your new password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                                <svg id="password-eye-icon" class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="password-eye-off-icon" class="eye-off-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                        <div class="password-input-wrapper">
                            <input id="password_confirmation" 
                                   class="form-control admin-input @error('password_confirmation') is-invalid @enderror"
                                   type="password"
                                   name="password_confirmation"
                                   required 
                                   autocomplete="new-password"
                                   placeholder="Confirm your new password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')" aria-label="Toggle password visibility">
                                <svg id="password_confirmation-eye-icon" class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="password_confirmation-eye-off-icon" class="eye-off-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <span id="buttonText">{{ __('Reset Password') }}</span>
                    </button>

                    <!-- Back to Login Link -->
                    <div class="auth-links">
                        <a href="{{ route('admin.login') }}" class="forgot-link">
                            {{ __('Back to Login') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Enhanced form interaction
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const buttonText = document.getElementById('buttonText');
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            
            if (!password || !passwordConfirmation) {
                return; // Let Laravel validation handle this
            }
            
            submitBtn.disabled = true;
            buttonText.textContent = 'Resetting...';
            
            // Form will submit normally, this just provides user feedback
        });

        // Remove error state on input
        ['email', 'password', 'password_confirmation'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function(e) {
                    e.target.classList.remove('is-invalid');
                    const errorMsg = e.target.parentElement.querySelector('.invalid-feedback');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                });
            }
        });
    </script>

    <style>
        /* Force refresh styles - Override any cached CSS */
        body.auth-page {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 50%, #f8f9fa 100%) !important;
            background-size: 200% 200% !important;
            animation: gradientShift 15s ease infinite !important;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .auth-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            box-shadow: 0 8px 24px rgba(128, 0, 32, 0.12) !important;
            border: 1px solid rgba(128, 0, 32, 0.1) !important;
            max-width: 420px !important;
        }

        .auth-card::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 4px !important;
            background: linear-gradient(90deg, #800020 0%, #FFD700 100%) !important;
            z-index: 1 !important;
        }

        .auth-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(128, 0, 32, 0.1) !important;
        }

        .university-logo {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            box-shadow: 0 2px 8px rgba(128, 0, 32, 0.15), 0 1px 3px rgba(0, 0, 0, 0.1) !important;
        }

        .auth-body {
            background: transparent !important;
        }

        .form-control {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            border: 1px solid rgba(128, 0, 32, 0.2) !important;
        }

        .form-control:focus {
            border-color: #800020 !important;
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1) !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #800020 0%, #5c0017 100%) !important;
            box-shadow: 0 2px 8px rgba(128, 0, 32, 0.2), 0 1px 3px rgba(0, 0, 0, 0.1) !important;
        }

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #5c0017 0%, #800020 100%) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(128, 0, 32, 0.25), 0 2px 6px rgba(0, 0, 0, 0.15) !important;
        }

        /* Additional styles for admin login */
        .admin-input {
            text-align: left !important;
            text-transform: none !important;
            letter-spacing: normal !important;
        }

        .admin-input::placeholder {
            text-align: left !important;
        }

        .forgot-link {
            color: #800020;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            position: relative;
        }

        .forgot-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #800020 0%, #FFD700 100%);
            transition: width 0.3s ease;
        }

        .forgot-link:hover {
            color: #5c0017;
        }

        .forgot-link:hover::after {
            width: 100%;
        }

        .auth-links {
            margin-top: 20px;
            text-align: center;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        /* Password Toggle Styles */
        .password-input-wrapper {
            position: relative;
            width: 100%;
        }

        .password-input-wrapper .form-control {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #800020;
        }

        .password-toggle:focus {
            outline: none;
        }

        .eye-icon,
        .eye-off-icon {
            width: 20px;
            height: 20px;
        }
    </style>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId + '-eye-icon');
            const eyeOffIcon = document.getElementById(fieldId + '-eye-off-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                field.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }
    </script>
</body>
</html>

