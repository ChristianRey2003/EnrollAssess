<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>BSIT Entrance Examination - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- University Theme CSS -->
    <link href="{{ asset('css/auth/university-auth.css') }}?v={{ time() }}" rel="stylesheet">
    <!-- Applicant Login Specific Styles -->
    <link href="{{ asset('css/auth/applicant-login.css') }}?v={{ time() }}" rel="stylesheet">
</head>
<body class="auth-page">
    <canvas id="network-bg"></canvas>

    <div class="auth-container">
        <div class="auth-card">
            <!-- University Header -->
            <div class="auth-header">
                <div class="university-logo" id="bsitLogo" role="button" aria-label="Click for easter egg" tabindex="0">
                    <img src="{{ asset('images/evsu-logo.png') }}" alt="EVSU Logo">
                </div>
                <h1 class="university-name">BSIT Entrance Examination</h1>
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
                        <div class="access-code-wrapper">
                            <span class="access-code-prefix" aria-hidden="true">BSIT-</span>
                            <input id="access_code" 
                                   class="form-control @error('access_code') is-invalid @enderror" 
                                   type="text" 
                                   name="access_code" 
                                   value="{{ old('access_code') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="off"
                                   maxlength="20"
                                   placeholder="Enter the code"
                                   aria-label="Enter your access code"
                                   aria-describedby="access-code-help">
                        </div>
                        @error('access_code')
                            <div class="invalid-feedback" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary" id="submitBtn" aria-label="Begin the examination">
                        <span id="buttonText">{{ __('Begin Examination') }}</span>
                    </button>

                    <!-- Information Note -->
                    <div class="auth-links">
                        <div class="info-note">
                            <div class="info-note-text">
                                <strong>Important:</strong>
                                You will receive your access code from the Information Technology Department.<br>
                                Please ensure you have a stable internet connection before beginning the examination.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="easterEggToast" class="easter-egg-toast" role="alert" aria-live="polite" aria-atomic="true">
        <div class="toast-content">
            <span class="toast-icon" aria-hidden="true">👀</span>
            <span class="toast-message">type 404</span>
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
                        ctx.strokeStyle = '#800020';
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
        document.getElementById('accessForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const buttonText = document.getElementById('buttonText');
            const input = document.getElementById('access_code');
            const accessCode = input.value.trim();
            
            // Ensure BSIT- prefix is added
            if (!/^BSIT-/i.test(accessCode)) {
                input.value = 'BSIT-' + accessCode.replace(/^BSIT-/i, '');
            }
            
            if (input.value.length < 8) { // BSIT- (5 chars) + at least 3 chars
                e.preventDefault();
                alert('Please enter a valid access code.');
                return;
            }
            
            submitBtn.disabled = true;
            buttonText.textContent = 'Verifying...';
        });

        // Auto-format access code input
        document.getElementById('access_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
        });

        // Remove error state on input
        document.getElementById('access_code').addEventListener('input', function(e) {
            e.target.classList.remove('is-invalid');
            const errorMsg = e.target.parentElement.parentElement.querySelector('.invalid-feedback');
            if (errorMsg) {
                errorMsg.remove();
            }
        });

        // Easter Egg: Single Click on BSIT Logo
        (function() {
            const logo = document.getElementById('bsitLogo');
            const toast = document.getElementById('easterEggToast');
            let hasInteracted = false;
            let wiggleInterval = null;

            // Track interactions to stop delayed wiggle
            function markAsInteracted() {
                hasInteracted = true;
                if (wiggleInterval) {
                    clearInterval(wiggleInterval);
                    wiggleInterval = null;
                }
            }

            // Click handler
            logo.addEventListener('click', function() {
                markAsInteracted();
                triggerLogoClick();
            });

            // Keyboard handler for accessibility
            logo.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    markAsInteracted();
                    triggerLogoClick();
                }
            });

            // Function to handle logo interaction
            function triggerLogoClick() {
                // Visual feedback
                logo.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    logo.style.transform = 'scale(1)';
                }, 150);
                
                // Show toast
                showToast();
            }

            // Hover handler - mark as interacted
            logo.addEventListener('mouseenter', function() {
                markAsInteracted();
            });

            // Delayed wiggle effect - starts after 2 seconds, then repeats every 2 seconds if not interacted
            setTimeout(function() {
                if (!hasInteracted) {
                    // Initial wiggle
                    triggerWiggle();
                    
                    // Set up interval to repeat every 2 seconds
                    wiggleInterval = setInterval(function() {
                        if (!hasInteracted) {
                            triggerWiggle();
                        } else {
                            clearInterval(wiggleInterval);
                            wiggleInterval = null;
                        }
                    }, 2000);
                }
            }, 2000);

            // Function to trigger wiggle animation
            function triggerWiggle() {
                logo.classList.add('logo-wiggle');
                // Remove the class after animation completes (0.8s)
                setTimeout(function() {
                    logo.classList.remove('logo-wiggle');
                }, 800);
            }

            // Show toast notification
            function showToast() {
                toast.classList.add('show');
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    hideToast();
                }, 5000);
            }

            // Hide toast
            function hideToast() {
                toast.classList.remove('show');
            }

            // Allow manual dismiss on click
            toast.addEventListener('click', hideToast);
        })();
    </script>
</body>
</html>