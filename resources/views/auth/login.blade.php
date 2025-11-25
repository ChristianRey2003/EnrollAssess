<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'University Portal') }} - Login</title>

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
                <h1 class="university-name">EnrollAssess</h1>
                <p class="auth-subtitle">University Portal</p>
            </div>

            <!-- Login Form -->
            <div class="auth-body">
                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="form-group">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        <input id="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               placeholder="Enter your university email">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <div class="password-input-wrapper">
                            <input id="password" 
                                   class="form-control @error('password') is-invalid @enderror"
                                   type="password"
                                   name="password"
                                   required 
                                   autocomplete="current-password"
                                   placeholder="Enter your password">
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

                    <!-- Remember Me -->
                    <div class="form-group remember-group">
                        <label class="remember-label" for="remember_me">
                            <input type="checkbox" name="remember" class="remember-checkbox" id="remember_me">
                            <span class="remember-text">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn-primary">
                        {{ __('Log In') }}
                    </button>

                    <!-- Links -->
                    <div class="auth-links">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                        
                        @if (Route::has('register'))
                            <div style="margin-top: 15px;">
                                <span style="color: #6b7280; font-size: 14px;">Don't have an account? </span>
                                <a href="{{ route('register') }}" class="register-link">
                                    {{ __('Register here') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

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

        /* Remember Me Styling */
        .remember-group {
            margin-bottom: 20px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 14px;
            color: #800020;
        }

        .remember-checkbox {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            accent-color: #800020;
        }

        .remember-text {
            font-weight: 500;
        }

        /* Links Styling */
        .forgot-link {
            color: #800020;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
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

        .register-link {
            color: #800020;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
        }

        .register-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #800020 0%, #FFD700 100%);
            transition: width 0.3s ease;
        }

        .register-link:hover {
            color: #5c0017;
        }

        .register-link:hover::after {
            width: 100%;
        }

        .auth-links {
            margin-top: 20px;
            text-align: center;
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