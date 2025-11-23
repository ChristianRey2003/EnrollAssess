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
    <link href="{{ asset('css/auth/university-auth.css') }}?v={{ time() }}" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- University Header -->
            <div class="auth-header">
                <div class="university-logo" id="bsitLogo" style="cursor: pointer; transition: transform 0.2s ease;">
                    <img src="{{ asset('images/evsu-logo.png') }}" alt="EVSU Logo" style="width: 60px; height: 60px; object-fit: contain; pointer-events: none;">
                </div>
                <h1 class="university-name">BSIT Entrance Examination</h1>
                <p class="auth-subtitle hidden-password">Congrats, detective! Now type 404 in the access code.</p>
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
                        <div style="position: relative; display: flex; align-items: center;">
                            <span style="position: absolute; left: 12px; font-weight: 600; color: #800020; z-index: 1; pointer-events: none;">BSIT-</span>
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
                                   style="padding-left: 60px;">
                        </div>
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
                                <strong>Important:</strong>
                                You will receive your access code from the Computer Studies Department.<br>
                                Please ensure you have a stable internet connection before beginning the examination.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="easterEggToast" class="easter-egg-toast">
        <div class="toast-content">
            <span class="toast-icon">👀</span>
            <span class="toast-message">Press Ctrl + A</span>
        </div>
    </div>

    <script>
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
            
            // Show loading state during form submission
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

        // Focus animations are now handled by CSS

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
                
                // Visual feedback on click
                logo.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    logo.style.transform = 'scale(1)';
                }, 150);
                
                // Show toast every time logo is clicked
                showToast();
            });

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
            position: relative !important;
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

        /* Info Note Styling */
        .info-note {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%) !important;
            border: 1px solid rgba(128, 0, 32, 0.1) !important;
            border-radius: 10px !important;
            padding: 20px !important;
            margin-top: 25px !important;
            text-align: center !important;
            box-shadow: 0 2px 8px rgba(128, 0, 32, 0.08) !important;
        }

        .info-note-text {
            color: #800020 !important;
            font-size: 14px !important;
            line-height: 1.6 !important;
            font-weight: 500 !important;
            margin: 0 !important;
        }

        .info-note-text strong {
            font-weight: 700 !important;
            display: block !important;
            margin-bottom: 8px !important;
            color: #5c0017 !important;
        }

        /* Access Code Input Prefix Styling */
        .form-group div[style*="position: relative"] span {
            color: #800020 !important;
            font-weight: 600 !important;
        }

        /* Toast Notification Styles */
        .easter-egg-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            opacity: 0;
            transform: translateX(400px);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            pointer-events: none;
        }

        .easter-egg-toast.show {
            opacity: 1;
            transform: translateX(0);
            pointer-events: auto;
        }

        .toast-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(128, 0, 32, 0.2);
            border-left: 4px solid #800020;
            border-radius: 10px;
            padding: 16px 20px;
            box-shadow: 0 8px 24px rgba(128, 0, 32, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 350px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .toast-content:hover {
            box-shadow: 0 12px 32px rgba(128, 0, 32, 0.2);
            transform: translateY(-2px);
        }

        .toast-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .toast-message {
            color: #800020;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
        }

        /* Hidden Password - White text, only visible when selected */
        .auth-subtitle.hidden-password {
            color: #ffffff;
            user-select: text;
            font-size: 14px;
            font-weight: 500;
            pointer-events: none;
            line-height: 1.6;
            margin: 0;
        }

        /* Logo hover effect */
        #bsitLogo:hover {
            transform: scale(1.05);
        }

        #bsitLogo:active {
            transform: scale(0.95);
        }

        /* Delayed wiggle animation - more attention-grabbing, repeats every 2 seconds */
        @keyframes logoWiggle {
            0%, 100% { 
                transform: rotate(0deg) translateX(0) translateY(0) scale(1);
            }
            5% { 
                transform: rotate(-8deg) translateX(-4px) translateY(-2px) scale(1.05);
            }
            10% { 
                transform: rotate(8deg) translateX(4px) translateY(2px) scale(1.05);
            }
            15% { 
                transform: rotate(-6deg) translateX(-3px) translateY(-1px) scale(1.03);
            }
            20% { 
                transform: rotate(6deg) translateX(3px) translateY(1px) scale(1.03);
            }
            25% { 
                transform: rotate(-5deg) translateX(-2px) translateY(-1px) scale(1.02);
            }
            30% { 
                transform: rotate(5deg) translateX(2px) translateY(1px) scale(1.02);
            }
            35% { 
                transform: rotate(-4deg) translateX(-2px) translateY(0) scale(1.01);
            }
            40% { 
                transform: rotate(4deg) translateX(2px) translateY(0) scale(1.01);
            }
            45% { 
                transform: rotate(-3deg) translateX(-1px) translateY(0) scale(1);
            }
            50% { 
                transform: rotate(3deg) translateX(1px) translateY(0) scale(1);
            }
            55% { 
                transform: rotate(-2deg) translateX(-1px) translateY(0) scale(1);
            }
            60% { 
                transform: rotate(2deg) translateX(1px) translateY(0) scale(1);
            }
            65% { 
                transform: rotate(-1deg) translateX(0) translateY(0) scale(1);
            }
            70% { 
                transform: rotate(1deg) translateX(0) translateY(0) scale(1);
            }
            75% { 
                transform: rotate(-1deg) translateX(0) translateY(0) scale(1);
            }
            80% { 
                transform: rotate(1deg) translateX(0) translateY(0) scale(1);
            }
            85% { 
                transform: rotate(0deg) translateX(0) translateY(0) scale(1);
            }
            90%, 100% { 
                transform: rotate(0deg) translateX(0) translateY(0) scale(1);
            }
        }

        #bsitLogo.logo-wiggle {
            animation: logoWiggle 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        /* Responsive toast */
        @media (max-width: 480px) {
            .easter-egg-toast {
                top: 15px;
                right: 15px;
                left: 15px;
                max-width: none;
            }

            .toast-content {
                max-width: 100%;
            }

            .auth-subtitle.hidden-password {
                font-size: 12px;
            }
        }
    </style>
</body>
</html> 