<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Faculty Portal - {{ config('app.name', 'EnrollAssess') }}</title>

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
                <h1 class="university-name">Faculty Portal</h1>
                <p class="auth-subtitle">Computer Studies Department</p>
            </div>

            <!-- Login Form -->
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

                <form method="POST" action="{{ route('admin.login.submit') }}" id="adminLoginForm">
                    @csrf

                    <!-- Username -->
                    <div class="form-group">
                        <label for="username" class="form-label">{{ __('Username') }}</label>
                        <input id="username" 
                               class="form-control admin-input @error('username') is-invalid @enderror" 
                               type="text" 
                               name="username" 
                               value="{{ old('username') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               placeholder="dept_head or admin1">
                        @error('username')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input id="password" 
                               class="form-control admin-input @error('password') is-invalid @enderror"
                               type="password"
                               name="password"
                               required 
                               autocomplete="current-password"
                               placeholder="Enter your password">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="form-group remember-group">
                        <label class="remember-label">
                            <input type="checkbox" name="remember" class="remember-checkbox">
                            <span class="remember-text">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <span id="buttonText">{{ __('Log In') }}</span>
                    </button>

                    <!-- Forgot Password Link -->
                    <div class="auth-links">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Enhanced form interaction
        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const buttonText = document.getElementById('buttonText');
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                return; // Let Laravel validation handle this
            }
            
            submitBtn.disabled = true;
            buttonText.textContent = 'Logging in...';
            
            // Form will submit normally, this just provides user feedback
        });

        // Remove error state on input
        ['username', 'password'].forEach(fieldId => {
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

        .remember-group {
            margin-bottom: 20px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 14px;
            color: var(--maroon-primary);
        }

        .remember-checkbox {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            accent-color: var(--maroon-primary);
        }

        .remember-text {
            font-weight: 500;
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
    </style>
</body>
</html>