<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Team Credits - {{ config('app.name', 'EnrollAssess') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/auth/university-auth.css') }}?v={{ time() }}" rel="stylesheet">
</head>
<body class="credits-page">
    <div class="credits-wrapper">
        <div class="credits-container">
            <!-- Team Photo -->
            <div class="team-photo-container">
                <img src="{{ asset('images/team-photo.jpg') }}" alt="Development Team" class="team-photo">
            </div>

            <!-- Credits Content -->
            <div class="credits-content">
                <h2 class="developed-by">DEVELOPED BY</h2>
                
                <div class="team-members">
                    <span>Christian Rey Alegre</span>
                    <span class="bullet">•</span>
                    <span>Marjorie G. Bebanco</span>
                    <span class="bullet">•</span>
                    <span>Hazel A. Yray</span>
                </div>

                <div class="university-info">
                    <p class="university-name">Eastern Visayas State University - Ormoc Campus</p>
                    <p>Bachelor of Science in Information Technology</p>
                    <p>Academic Year 2025-2026</p>
                </div>
            </div>

            <div class="back-link">
                <a href="{{ route('applicant.login') }}">← Back to Login</a>
            </div>
        </div>
    </div>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body.credits-page {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .credits-wrapper {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .credits-container {
            width: 100%;
            max-width: 1000px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(128, 0, 32, 0.08);
            border: 1px solid rgba(128, 0, 32, 0.1);
            display: flex;
            flex-direction: column;
            padding: 30px;
            gap: 24px;
            max-height: calc(100vh - 40px);
        }

        /* Team Photo */
        .team-photo-container {
            width: 100%;
            border-radius: 8px;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
        }

        .team-photo {
            max-width: 55%;
            height: auto;
            display: block;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(128, 0, 32, 0.1);
        }

        /* Credits Content */
        .credits-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            gap: 16px;
            min-height: 0;
        }

        .developed-by {
            color: #800020;
            font-size: 22px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0;
        }

        .team-members {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .bullet {
            color: #800020;
            font-size: 18px;
            font-weight: 600;
        }

        .university-info {
            margin-top: 8px;
        }

        .university-info p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
            margin: 4px 0;
        }

        .university-name {
            font-weight: 600;
            color: #800020 !important;
            font-size: 15px !important;
        }

        .back-link {
            text-align: center;
            padding-top: 8px;
            flex-shrink: 0;
            border-top: 1px solid rgba(128, 0, 32, 0.1);
        }

        .back-link a {
            color: #800020;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-block;
        }

        .back-link a:hover {
            color: #5c0017;
            transform: translateX(-2px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .credits-wrapper {
                padding: 15px;
            }

            .credits-container {
                padding: 20px;
                gap: 20px;
                max-height: calc(100vh - 30px);
            }

            .team-photo {
                max-width: 85%;
            }

            .developed-by {
                font-size: 20px;
            }

            .team-members {
                font-size: 15px;
                flex-direction: column;
                gap: 6px;
            }

            .bullet {
                display: none;
            }

            .university-info p {
                font-size: 13px;
            }

            .university-name {
                font-size: 14px !important;
            }
        }

        @media (max-height: 700px) {
            .credits-container {
                padding: 20px;
                gap: 16px;
            }


            .credits-content {
                gap: 12px;
            }

            .developed-by {
                font-size: 20px;
            }

            .team-members {
                font-size: 15px;
            }

            .university-info {
                margin-top: 4px;
            }

            .university-info p {
                font-size: 13px;
                line-height: 1.4;
            }
        }
    </style>
</body>
</html>
