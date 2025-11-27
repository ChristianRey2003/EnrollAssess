<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Faculty Portal - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- University Theme CSS -->
    <link href="{{ asset('css/auth/university-auth.css') }}?v={{ time() }}" rel="stylesheet">
    <!-- Admin Login Specific Styles -->
    <link href="{{ asset('css/auth/admin-login.css') }}?v={{ time() }}" rel="stylesheet">
</head>
    <style>
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
</head>
<body class="auth-page">
    <canvas id="network-bg"></canvas>

    <div class="auth-container">
        <div class="auth-card">
            <!-- University Header -->
            <div class="auth-header">
                <div class="university-logo">
                    <img src="{{ asset('images/evsu-logo.png') }}" alt="EVSU Logo">
                </div>
                <h1 class="university-name">Faculty Portal</h1>
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

                    <!-- Username or Email -->
                    <div class="form-group">
                        <label for="username" class="form-label">{{ __('Username or Email') }}</label>
                        <input id="username" 
                               class="form-control admin-input @error('username') is-invalid @enderror" 
                               type="text" 
                               name="username" 
                               value="{{ old('username') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               placeholder="Enter your username or email"
                               aria-label="Enter your username or email"
                               aria-describedby="username-help">
                        @error('username')
                            <div class="invalid-feedback" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <div class="password-input-wrapper">
                            <input id="password" 
                                   class="form-control admin-input @error('password') is-invalid @enderror"
                                   type="password"
                                   name="password"
                                   required 
                                   autocomplete="current-password"
                                   placeholder="Enter your password"
                                   aria-label="Enter your password"
                                   aria-describedby="password-help">
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
                            <div class="invalid-feedback" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="form-group remember-group">
                        <label class="remember-label" for="remember">
                            <input type="checkbox" name="remember" id="remember" class="remember-checkbox" aria-label="Remember me on this device">
                            <span class="remember-text">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary" id="submitBtn" aria-label="Log in to faculty portal">
                        <span id="buttonText">{{ __('Log In') }}</span>
                    </button>

                    <!-- Forgot Password Link -->
                    <div class="auth-links">
                        <a href="{{ route('admin.password.request') }}" class="forgot-link" aria-label="Reset your password">
                            {{ __('Forgot your password?') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Network Animation
        const canvas = document.getElementById('network-bg');
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];
        
        // University Palette (Maroon & Gold)
        const colors = ['#800020', '#FFD700', '#A00028', '#E6C200'];

        // Configuration
        const particleCount = 100; // Increased density
        const connectionDistance = 160; // Longer connections

        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.5;
                this.vy = (Math.random() - 0.5) * 0.5;
                this.size = Math.random() * 3 + 1.5; // Slightly larger particles
                this.color = colors[Math.floor(Math.random() * colors.length)];
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                if (this.x < 0 || this.x > width) this.vx *= -1;
                if (this.y < 0 || this.y > height) this.vy *= -1;
            }

            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = this.color;
                ctx.fill();
            }
        }

        function init() {
            resize();
            particles = [];
            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }
        }

        function animate() {
            ctx.clearRect(0, 0, width, height);
            
            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();

                for (let j = i; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < connectionDistance) {
                        ctx.beginPath();
                        ctx.strokeStyle = '#800020'; // Maroon connections
                        ctx.globalAlpha = (1 - distance/connectionDistance) * 0.35; // More visible lines
                        ctx.lineWidth = 1.5; // Slightly thicker lines
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                        ctx.globalAlpha = 1;
                    }
                }
            }
            requestAnimationFrame(animate);
        }

        window.addEventListener('resize', resize);
        init();
        animate();

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

        // Password toggle function
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