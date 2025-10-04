<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Instructor Portal') - {{ config('app.name', 'EnrollAssess') }}</title>
    
    <!-- SEO and Meta Tags -->
    <meta name="description" content="@yield('description', 'EnrollAssess Instructor Portal - Manage interviews and applicant assessments')">
    <meta name="robots" content="noindex, nofollow"> {{-- Instructor portal should not be indexed --}}
    <meta name="author" content="{{ config('app.name', 'EnrollAssess') }}">
    
    <!-- Theme and Viewport -->
    <meta name="theme-color" content="#800020"> {{-- Maroon primary color --}}
    <meta name="color-scheme" content="light">
    
    <!-- PWA and Mobile -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'EnrollAssess') }}">

    <!-- Fonts with preload for performance -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preload" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"></noscript>

    <!-- Critical CSS inlined for immediate rendering -->
    <style>
        /* Critical above-the-fold styles */
        :root {
            --maroon-primary: #800020; /* Primary */
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-400: #9CA3AF;
            --sidebar-width: 260px;
        }
        
        body.instructor-page {
            margin: 0;
            padding: 0;
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-gray);
            color: #1F2937;
            line-height: 1.6;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--maroon-primary) 0%, #5C0016 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #FFD700;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .top-header {
            background: white;
            padding: 16px 32px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1F2937;
            margin: 0;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--maroon-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-name {
            font-weight: 500;
            color: #374151;
        }

        .logout-btn {
            background: none;
            border: 1px solid var(--gray-200);
            padding: 8px 16px;
            border-radius: 6px;
            color: #6B7280;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #F3F4F6;
            color: #374151;
        }

        .content-area {
            padding: 32px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
                background: none;
                border: none;
                font-size: 1.5rem;
                color: #374151;
                cursor: pointer;
            }

            .content-area {
                padding: 16px;
            }
        }

        @media (min-width: 769px) {
            .mobile-menu-btn {
                display: none;
            }
        }

        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Notification Styles */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border-left: 4px solid;
        }

        .alert-success {
            background-color: #D1FAE5;
            border-left-color: #059669;
            color: #065F46;
        }

        .alert-error {
            background-color: #FEE2E2;
            border-left-color: #DC2626;
            color: #991B1B;
        }

        .alert-warning {
            background-color: #FEF3C7;
            border-left-color: #F59E0B;
            color: #92400E;
        }

        .alert-info {
            background-color: #DBEAFE;
            border-left-color: #3B82F6;
            color: #1E40AF;
        }
    </style>

    <!-- Additional Styles -->
    @stack('styles')

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'maroon': '#800020',
                        'gold': '#FFD700'
                    }
                }
            }
        }
    </script>
</head>

<body class="instructor-page">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('instructor.dashboard') }}" class="sidebar-brand">
                <span>Instructor Portal</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('instructor.dashboard') }}" class="nav-link {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
                    <span>Dashboard</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('instructor.interview-pool.index') }}" class="nav-link {{ request()->routeIs('instructor.interview-pool.*') ? 'active' : '' }}">
                    <span>Interview Pool</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('instructor.applicants') }}" class="nav-link {{ request()->routeIs('instructor.applicants') ? 'active' : '' }}">
                    <span>My Applicants</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('instructor.schedule') }}" class="nav-link {{ request()->routeIs('instructor.schedule.*') ? 'active' : '' }}">
                    <span>Schedule</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('instructor.interview-history') }}" class="nav-link {{ request()->routeIs('instructor.interview-history') ? 'active' : '' }}">
                    <span>Interview History</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('instructor.guidelines') }}" class="nav-link {{ request()->routeIs('instructor.guidelines') ? 'active' : '' }}">
                    <span>Guidelines</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="flex items-center gap-4">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    ☰
                </button>
                <h1 class="page-title">
                    @if(isset($pageTitle))
                        {{ $pageTitle }}
                    @else
                        @yield('title', 'Instructor Portal')
                    @endif
                </h1>
                @if(isset($pageSubtitle))
                    <span class="text-gray-500 text-sm">{{ $pageSubtitle }}</span>
                @endif
            </div>
            
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        {{ substr(auth()->user()->name ?? 'I', 0, 1) }}
                    </div>
                    <div>
                        <div class="user-name">{{ auth()->user()->name ?? 'Instructor' }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->role ?? 'instructor' }}</div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="logout-btn">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info">
                    {{ session('info') }}
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !mobileMenuBtn.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });

        // Global error handling for AJAX requests
        window.addEventListener('unhandledrejection', function(event) {
            console.error('Unhandled promise rejection:', event.reason);
        });
    </script>

    @stack('scripts')
</body>
</html>
