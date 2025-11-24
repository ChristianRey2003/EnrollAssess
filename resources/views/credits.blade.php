<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Team Credits - {{ config('app.name', 'EnrollAssess') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/auth/university-auth.css') }}?v={{ time() }}" rel="stylesheet">
</head>
<body class="credits-page">
    <canvas id="network-bg"></canvas>

    <div class="credits-wrapper">
        <div class="credits-container">
            <!-- Team Photo -->
            <div class="team-photo-container">
                <div class="photo-frame">
                    @php
                        // Check if image file exists
                        $imagePath = public_path('images/team-photo.jpg');
                        $imageExists = file_exists($imagePath);
                        
                        if ($imageExists) {
                            // Generate absolute URL for the image
                            $imageUrl = asset('images/team-photo.jpg');
                            
                            // If HTTPS is detected, use secure URL
                            if (request()->secure() || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
                                $imageUrl = str_replace('http://', 'https://', $imageUrl);
                            }
                        } else {
                            // Use placeholder or hide image
                            $imageUrl = null;
                        }
                    @endphp
                    @if($imageExists)
                        <img src="{{ $imageUrl }}" 
                             alt="Development Team" 
                             class="team-photo"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="photo-glow"></div>
                    @else
                        <div class="team-photo-placeholder" style="width: 100%; height: 300px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(0, 243, 255, 0.1), rgba(255, 0, 255, 0.1)); border-radius: 12px; color: var(--text-muted);">
                            <div style="text-align: center;">
                                <div style="font-size: 48px; margin-bottom: 10px;">📷</div>
                                <div style="font-size: 14px;">Team Photo</div>
                            </div>
                        </div>
                        <div class="photo-glow"></div>
                    @endif
                </div>
            </div>

            <!-- Credits Content -->
            <div class="credits-content">
                <h2 class="developed-by">Developed By</h2>
                
                <div class="team-members">
                    <div class="member-pill">Christian Rey Alegre</div>
                    <div class="member-pill">Marjorie G. Bebanco</div>
                    <div class="member-pill">Hazel A. Yray</div>
                </div>

                <div class="divider"></div>

                <div class="university-info">
                    <p class="university-name">Eastern Visayas State University</p>
                    <p class="campus-name">Ormoc Campus</p>
                    <p class="course-name">Bachelor of Science in Information Technology</p>
                    <p class="academic-year">Academic Year 2025-2026</p>
                </div>
            </div>

            <div class="back-link-container">
                <a href="{{ route('applicant.login') }}" class="back-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    <span>Back to Login</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('network-bg');
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];
        
        // Neon Palette
        const colors = ['#00ff41', '#00f3ff', '#ff00ff', '#ffe600'];

        // Configuration
        const particleCount = 70; // Increased for more energy
        const connectionDistance = 150;

        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.8; // Faster movement
                this.vy = (Math.random() - 0.5) * 0.8;
                this.size = Math.random() * 2 + 1.5;
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
                ctx.shadowBlur = 10;
                ctx.shadowColor = this.color;
                ctx.fill();
                ctx.shadowBlur = 0; // Reset shadow
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
                        // Gradient line
                        const gradient = ctx.createLinearGradient(particles[i].x, particles[i].y, particles[j].x, particles[j].y);
                        gradient.addColorStop(0, particles[i].color);
                        gradient.addColorStop(1, particles[j].color);
                        
                        ctx.strokeStyle = gradient;
                        ctx.globalAlpha = 1 - distance/connectionDistance;
                        ctx.lineWidth = 1;
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
    </script>

    <style>
        :root {
            --primary-color: #00f3ff; /* Cyan */
            --secondary-color: #ff00ff; /* Magenta */
            --accent-color: #00ff41; /* Matrix Green */
            --bg-dark: #050510;
            --glass-bg: rgba(10, 10, 20, 0.65);
            --text-main: #ffffff;
            --text-muted: #a0a0b0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body.credits-page {
            font-family: 'Figtree', sans-serif;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-dark);
            position: relative;
            color: var(--text-main);
        }

        #network-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        /* Glassmorphism Card */
        .credits-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .credits-container {
            width: 100%;
            max-width: 900px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 
                0 0 20px rgba(0, 243, 255, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            padding: 40px;
            gap: 30px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .credits-container::-webkit-scrollbar {
            display: none;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Team Photo */
        .team-photo-container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }

        .photo-frame {
            position: relative;
            width: 60%;
            max-width: 500px;
            border-radius: 16px;
            padding: 5px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .photo-frame:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 0 30px rgba(255, 0, 255, 0.4);
        }

        .team-photo {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 12px;
            filter: contrast(1.1);
        }

        /* Content Styling */
        .credits-content {
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        .developed-by {
            color: var(--primary-color);
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin: 0;
            text-shadow: 0 0 10px rgba(0, 243, 255, 0.5);
        }

        .team-members {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        .member-pill {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            color: var(--text-main);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            cursor: default;
            letter-spacing: 0.5px;
        }

        .member-pill:hover {
            background: rgba(0, 243, 255, 0.1);
            transform: translateY(-2px);
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(0, 243, 255, 0.3);
            color: var(--primary-color);
        }

        .divider {
            width: 80px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--secondary-color), transparent);
            margin: 5px 0;
            opacity: 0.8;
        }

        .university-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .university-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: 0.5px;
        }

        .campus-name {
            font-size: 15px;
            font-weight: 500;
            color: var(--text-muted);
        }

        .course-name {
            font-size: 15px;
            font-weight: 500;
            color: var(--primary-color);
            margin-top: 4px;
        }

        .academic-year {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 8px;
            background: rgba(255,255,255,0.05);
            padding: 4px 12px;
            border-radius: 12px;
            display: inline-block;
            align-self: center;
            border: 1px solid rgba(255,255,255,0.05);
        }

        /* Back Button */
        .back-link-container {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: transparent;
            color: var(--text-main);
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            border: 1px solid var(--primary-color);
            box-shadow: 0 0 10px rgba(0, 243, 255, 0.1);
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--primary-color);
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.4);
        }

        .back-btn svg {
            transition: transform 0.3s ease;
        }

        .back-btn:hover svg {
            transform: translateX(-3px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .credits-container {
                padding: 24px;
                gap: 20px;
            }

            .photo-frame {
                width: 85%;
            }

            .team-members {
                flex-direction: column;
                gap: 8px;
                width: 100%;
            }

            .member-pill {
                width: 100%;
                text-align: center;
            }

            .university-name {
                font-size: 16px;
            }
            
            .course-name {
                font-size: 14px;
            }
        }

        @media (max-height: 800px) {
            .credits-container {
                padding: 20px;
                gap: 16px;
            }
            
            .photo-frame {
                max-width: 350px;
            }
        }
    </style>
</body>
</html>
