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
                        <div class="team-photo-placeholder" style="width: 100%; height: 300px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(128, 0, 32, 0.1), rgba(255, 215, 0, 0.1)); border-radius: 12px; color: var(--text-muted);">
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
                    <div class="member-card">
                        <a href="https://www.facebook.com/Garlicbreaddd" target="_blank" class="member-name developer-link">Christian Rey Y.Alegre</a>
                        <span class="member-role">Developer</span>
                    </div>
                    <div class="member-card">
                        <span class="member-name">Marjorie G. Bebanco</span>
                        <span class="member-role">UI/UX Designer</span>
                    </div>
                    <div class="member-card">
                        <span class="member-name">Hazel A. Yray</span>
                        <span class="member-role">QA</span>
                    </div>
                </div>

                <div class="adviser-section" style="margin-top: 5px;">
                    <div class="member-card" style="border-color: var(--secondary-color); background: rgba(255, 215, 0, 0.05);">
                        <span class="member-name" style="color: var(--primary-color);">Joseph Jaymel S. Morpos</span>
                        <span class="member-role">Capstone Adviser</span>
                    </div>
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

    <style>
        :root {
            --primary-color: #800020; /* Maroon */
            --secondary-color: #FFD700; /* Gold */
            --bg-light: #f8f9fa;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --text-main: #333333;
            --text-muted: #666666;
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
            background-color: var(--bg-light);
            position: relative;
            color: var(--text-main);
        }

        body.credits-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('/images/admin-login-bg.png?v=2');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(8px);
            -webkit-filter: blur(8px);
            z-index: 0;
            transform: scale(1.1);
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
            max-width: 800px; /* Reduced max-width */
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 24px;
            box-shadow: 
                0 10px 40px rgba(128, 0, 32, 0.1),
                0 1px 0 rgba(255, 255, 255, 0.5) inset;
            border: 1px solid rgba(255, 255, 255, 0.6);
            display: flex;
            flex-direction: column;
            padding: 30px; /* Reduced padding */
            gap: 20px; /* Reduced gap */
            max-height: 95vh; /* Ensure it fits */
            overflow-y: auto; /* Fallback for very small screens */
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
            margin-bottom: 5px; /* Reduced margin */
        }

        .photo-frame {
            position: relative;
            width: 45%; /* Reduced width */
            max-width: 350px; /* Reduced max-width */
            border-radius: 16px;
            padding: 4px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 10px 30px rgba(128, 0, 32, 0.2);
            transition: transform 0.3s ease;
        }

        .photo-frame:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 15px 40px rgba(128, 0, 32, 0.3);
        }

        .team-photo {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 12px;
        }

        /* Content Styling */
        .credits-content {
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 15px; /* Reduced gap */
            align-items: center;
        }

        .developed-by {
            color: var(--primary-color);
            font-size: 13px; /* Slightly smaller */
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }

        .team-members {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px; /* Reduced gap */
            width: 100%;
        }

        .member-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: rgba(255, 255, 255, 0.5);
            padding: 10px 20px; /* Reduced padding */
            border-radius: 14px;
            border: 1px solid rgba(128, 0, 32, 0.1);
            transition: all 0.3s ease;
            min-width: 180px; /* Slightly smaller min-width */
        }

        .member-card:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(128, 0, 32, 0.1);
            border-color: var(--primary-color);
        }

        .member-name {
            font-weight: 700;
            color: var(--text-main);
            font-size: 15px; /* Slightly smaller */
            margin-bottom: 2px;
        }

        .developer-link {
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .developer-link:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .member-role {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .divider {
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--secondary-color), transparent);
            margin: 5px 0; /* Reduced margin */
            opacity: 0.6;
        }

        .university-info {
            display: flex;
            flex-direction: column;
            gap: 2px; /* Reduced gap */
        }

        .university-name {
            font-size: 16px; /* Slightly smaller */
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: 0.5px;
        }

        .campus-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
        }

        .course-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            margin-top: 2px;
        }

        .academic-year {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
            margin-top: 5px;
            background: rgba(128, 0, 32, 0.05);
            padding: 3px 10px;
            border-radius: 10px;
            display: inline-block;
            align-self: center;
            border: 1px solid rgba(128, 0, 32, 0.1);
        }

        /* Back Button */
        .back-link-container {
            display: flex;
            justify-content: center;
            margin-top: 5px; /* Reduced margin */
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 24px; /* Reduced padding */
            background: white;
            color: var(--primary-color);
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid var(--primary-color);
            box-shadow: 0 4px 10px rgba(128, 0, 32, 0.1);
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(128, 0, 32, 0.2);
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
                padding: 20px;
                gap: 15px;
            }

            .photo-frame {
                width: 70%;
            }

            .team-members {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }

            .member-card {
                width: 100%;
                padding: 8px 16px;
            }

            .university-name {
                font-size: 15px;
            }
            
            .course-name {
                font-size: 13px;
            }
        }

        @media (max-height: 700px) {
            .credits-container {
                padding: 15px;
                gap: 10px;
            }
            
            .photo-frame {
                width: 35%;
                max-width: 250px;
            }

            .member-card {
                padding: 6px 12px;
            }
        }
    </style>
</body>
</html>

