<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'EnrollAssess') }}</title>
    
    <!-- SEO and Meta Tags -->
    <meta name="description" content="@yield('description', 'EnrollAssess Admin Panel - Manage university enrollment, exams, and applications')">
    <meta name="robots" content="noindex, nofollow"> {{-- Admin panel should not be indexed --}}
    <meta name="author" content="{{ config('app.name', 'EnrollAssess') }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Admin Panel') - {{ config('app.name', 'EnrollAssess') }}">
    <meta property="og:description" content="@yield('description', 'EnrollAssess Admin Panel - Manage university enrollment, exams, and applications')">
    <meta property="og:site_name" content="{{ config('app.name', 'EnrollAssess') }}">
    
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
            --maroon-primary: #800020;
            --yellow-primary: #FFD700;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --sidebar-width: 260px;
        }
        
        body.admin-page {
            margin: 0;
            font-family: 'Figtree', 'Segoe UI', sans-serif;
            background: var(--light-gray);
            min-height: 100vh;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--maroon-primary) 0%, #5c0017 100%);
            color: var(--white);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
        }
        
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: var(--light-gray);
            display: flex;
            flex-direction: column;
        }
        
        .main-header {
            background: var(--white);
            padding: 20px 30px;
            border-bottom: 1px solid #E9ECEF;
            order: 0; /* Ensure header appears first */
            flex-shrink: 0;
            position: relative;
            z-index: 10;
        }
        
        .main-content {
            flex: 1;
            order: 1; /* Ensure content appears after header */
        }
        
        /* Skip link for accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--maroon-primary);
            color: var(--white);
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 100000;
            font-weight: 600;
            transition: top 0.3s;
        }
        
        .skip-link:focus {
            top: 6px;
        }
    </style>

    <!-- Non-critical CSS loaded asynchronously -->
    @vite(['resources/css/admin.css'])
    
    <!-- Page-specific CSS -->
    @stack('styles')
</head>
<body class="admin-page">
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Mobile menu toggle -->
    <button class="mobile-menu-toggle" 
            onclick="toggleMobileMenu()" 
            aria-label="Toggle navigation menu"
            style="display: none;">
        <span aria-hidden="true">☰</span>
    </button>

    <!-- Mobile menu overlay -->
    <div class="mobile-menu-overlay" 
         id="mobileMenuOverlay" 
         onclick="closeMobileMenu()"
         aria-hidden="true"></div>

    <div class="admin-layout">
        <!-- Unified Navigation Component -->
        <x-admin-navigation 
            :userRole="auth()->user()->role ?? 'department-head'" 
            :currentRoute="request()->route()->getName() ?? ''" />

        <!-- Main Content Area -->
        <main class="admin-main" id="main-content" role="main">
            <!-- Unified Header Component -->
            <x-admin-header 
                :title="$pageTitle ?? 'Admin Panel'" 
                :subtitle="$pageSubtitle ?? ''" />

            <!-- Page Content -->
            <div class="main-content">
                <!-- Success/Error Messages -->
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif
                @if (session('info'))
                    <div class="alert alert-info">{{ session('info') }}</div>
                @endif

                <!-- Main Content Slot -->
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Optimized JavaScript Bundles -->
    @vite(['resources/js/admin.js'])
    
    <!-- Page-specific JavaScript -->
    @stack('scripts')
</body>
</html>
