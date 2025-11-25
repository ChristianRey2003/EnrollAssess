<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Change Password Required - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- University Theme CSS -->
    <link href="{{ asset('css/auth/university-auth.css') }}?v={{ time() }}" rel="stylesheet">
    <!-- Admin Login Specific Styles -->
    <link href="{{ asset('css/auth/admin-login.css') }}?v={{ time() }}" rel="stylesheet">
    
    <style>
        .password-requirements {
            background: #f8f9fa;
            padding: 15px 20px;
            margin-top: 15px;
            border-radius: 6px;
        }
        .password-requirements-title {
            font-size: 14px;
            font-weight: 600;
            color: #800020;
            margin: 0 0 10px 0;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
            color: #6b7280;
            font-size: 13px;
        }
        .password-requirements li {
            margin: 5px 0;
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
                <h1 class="university-name">Password Change Required</h1>
            </div>

            <!-- Password Change Form -->
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

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.password.force-change.update') }}" id="passwordChangeForm">
                    @csrf

                    <!-- New Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">{{ __('New Password') }}</label>
                        <input id="password" 
                               class="form-control admin-input @error('password') is-invalid @enderror"
                               type="password"
                               name="password"
                               required 
                               autofocus
                               autocomplete="new-password"
                               placeholder="Enter your new password"
                               aria-label="Enter your new password"
                               aria-describedby="password-help">
                        @error('password')
                            <div class="invalid-feedback" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                        <input id="password_confirmation" 
                               class="form-control admin-input @error('password_confirmation') is-invalid @enderror"
                               type="password"
                               name="password_confirmation"
                               required 
                               autocomplete="new-password"
                               placeholder="Confirm your new password"
                               aria-label="Confirm your new password"
                               aria-describedby="password-confirmation-help">
                        @error('password_confirmation')
                            <div class="invalid-feedback" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password Requirements -->
                    <div class="password-requirements">
                        <div class="password-requirements-title">Password Requirements:</div>
                        <ul>
                            <li>Minimum 8 characters</li>
                            <li>Use a combination of letters, numbers, and symbols</li>
                            <li>Choose a password you can remember but others cannot guess</li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary" id="submitBtn" aria-label="Change password">
                        <span id="buttonText">{{ __('Change Password') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Network Animation (same as login page)
        const canvas = document.getElementById('network-bg');
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];
        
        const colors = ['#800020', '#FFD700', '#A00028', '#E6C200'];
        const particleCount = 100;
        const connectionDistance = 160;

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
                this.size = Math.random() * 3 + 1.5;
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
                        ctx.strokeStyle = '#800020';
                        ctx.globalAlpha = (1 - distance/connectionDistance) * 0.35;
                        ctx.lineWidth = 1.5;
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

        // Form submission handling
        document.getElementById('passwordChangeForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const buttonText = document.getElementById('buttonText');
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            
            if (!password || !passwordConfirmation) {
                return;
            }
            
            if (password !== passwordConfirmation) {
                e.preventDefault();
                alert('Passwords do not match. Please try again.');
                return;
            }
            
            submitBtn.disabled = true;
            buttonText.textContent = 'Changing Password...';
        });

        // Remove error state on input
        ['password', 'password_confirmation'].forEach(fieldId => {
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
</body>
</html>

