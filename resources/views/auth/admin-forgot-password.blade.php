<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Forgot Password - {{ config('app.name', 'EnrollAssess') }}</title>

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

            <!-- Forgot Password Form -->
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

                <div class="mb-4 text-sm text-gray-600" style="text-align: center; color: #666; margin-bottom: 20px;">
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                    <br><small style="color: #999; margin-top: 8px; display: block;">You can request a password reset once per minute for security purposes.</small>
                </div>

                <form method="POST" action="{{ route('admin.password.email') }}" id="forgotPasswordForm">
                    @csrf

                    <!-- Email Address -->
                    <div class="form-group">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        <input id="email" 
                               class="form-control admin-input @error('email') is-invalid @enderror" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
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

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <span id="buttonText">{{ __('Email Password Reset Link') }}</span>
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
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const buttonText = document.getElementById('buttonText');
            const email = document.getElementById('email').value;
            
            if (!email) {
                return; // Let Laravel validation handle this
            }
            
            submitBtn.disabled = true;
            buttonText.textContent = 'Sending...';
            
            // Form will submit normally, this just provides user feedback
        });

        // Remove error state on input
        const emailField = document.getElementById('email');
        if (emailField) {
            emailField.addEventListener('input', function(e) {
                e.target.classList.remove('is-invalid');
                const errorMsg = e.target.parentElement.querySelector('.invalid-feedback');
                if (errorMsg) {
                    errorMsg.remove();
                }
            });
        }
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
    </style>
</body>
</html>

