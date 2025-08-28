<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found - {{ config('app.name', 'EnrollAssess') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
</head>
<body class="error-page">
    <div class="error-container">
        <div class="error-content">
            <!-- University Logo -->
            <div class="error-logo">
                <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo" class="logo-image">
            </div>

            <!-- Error Information -->
            <div class="error-info">
                <h1 class="error-code">404</h1>
                <h2 class="error-title">Page Not Found</h2>
                <p class="error-message">
                    Sorry, the page you are looking for could not be found. It may have been moved, 
                    deleted, or you may have entered an incorrect URL.
                </p>
            </div>

            <!-- Navigation Options -->
            <div class="error-actions">
                @auth
                    @if(Auth::user()->role === 'department-head')
                        <a href="{{ route('admin.dashboard') }}" class="btn-primary">
                            🏠 Admin Dashboard
                        </a>
                    @elseif(Auth::user()->role === 'instructor')
                        <a href="{{ route('instructor.dashboard') }}" class="btn-primary">
                            🏠 Instructor Dashboard
                        </a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="btn-primary">
                            🏠 Dashboard
                        </a>
                    @endif
                @else
                    <a href="{{ route('admin.login') }}" class="btn-primary">
                        🔐 Login
                    </a>
                @endauth
                
                <button onclick="history.back()" class="btn-secondary">
                    ← Go Back
                </button>
            </div>

            <!-- Help Information -->
            <div class="error-help">
                <h3>Need Help?</h3>
                <ul>
                    <li>Check the URL for typos</li>
                    <li>Use the navigation menu to find what you're looking for</li>
                    <li>Contact the system administrator if you believe this is an error</li>
                </ul>
            </div>
        </div>
    </div>

    <style>
        .error-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Figtree', sans-serif;
        }

        .error-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .error-content {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .error-logo {
            margin-bottom: 2rem;
        }

        .logo-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #667eea;
            margin: 0;
            line-height: 1;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 600;
            color: #374151;
            margin: 1rem 0;
        }

        .error-message {
            font-size: 1.1rem;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-secondary {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
            transform: translateY(-2px);
        }

        .error-help {
            text-align: left;
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }

        .error-help h3 {
            margin: 0 0 1rem 0;
            color: #374151;
            font-size: 1.2rem;
        }

        .error-help ul {
            margin: 0;
            padding-left: 1.5rem;
            color: #6b7280;
        }

        .error-help li {
            margin-bottom: 0.5rem;
        }

        @media (max-width: 640px) {
            .error-container {
                padding: 1rem;
            }

            .error-content {
                padding: 2rem 1.5rem;
            }

            .error-code {
                font-size: 4rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .error-actions {
                flex-direction: column;
                align-items: center;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
</body>
</html>
