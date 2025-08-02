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
    <link href="{{ asset('css/auth/university-auth.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- University Header -->
            <div class="auth-header">
                <div class="university-logo">
                    🎓
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
                        <input id="password" 
                               class="form-control @error('password') is-invalid @enderror"
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
                    <div class="form-check">
                        <input id="remember_me" 
                               type="checkbox" 
                               class="form-check-input" 
                               name="remember">
                        <label for="remember_me" class="form-check-label">
                            {{ __('Remember me') }}
                        </label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn-primary">
                        {{ __('Log In') }}
                    </button>

                    <!-- Links -->
                    <div class="auth-links">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                        
                        @if (Route::has('register'))
                            <br><br>
                            <span>Don't have an account? </span>
                            <a href="{{ route('register') }}">
                                {{ __('Register here') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>