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
    <link href="{{ asset('css/auth/university-auth.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- University Header -->
            <div class="auth-header">
                <div class="university-logo">
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo" style="width: 60px; height: 60px; object-fit: contain;">
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

                <form method="POST" action="{{ route('login') }}" id="adminLoginForm">
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
                               autocomplete="username"
                               placeholder="admin@university.edu">
                        @error('email')
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
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                return; // Let Laravel validation handle this
            }
            
            submitBtn.disabled = true;
            buttonText.textContent = 'Logging in...';
            
            // Form will submit normally, this just provides user feedback
        });

        // Remove error state on input
        ['email', 'password'].forEach(fieldId => {
            document.getElementById(fieldId).addEventListener('input', function(e) {
                e.target.classList.remove('is-invalid');
                const errorMsg = e.target.parentElement.querySelector('.invalid-feedback');
                if (errorMsg) {
                    errorMsg.remove();
                }
            });
        });
    </script>

    <style>
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
            color: var(--yellow-primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
        }

        .forgot-link:hover {
            color: var(--yellow-dark);
            text-decoration: underline;
        }

        .auth-links {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</body>
</html>