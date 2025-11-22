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

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Admin CSS Bundle -->
    @vite(['resources/css/admin.css'])
    
    <!-- Global Modal Fix -->
    <link rel="stylesheet" href="{{ asset('css/components/modal-fix.css') }}">
    
    <!-- Page-specific CSS -->
    @stack('styles')
    
    <!-- Sidebar State Initialization - Must be in head to prevent flash -->
    <script>
        // Initialize sidebar state immediately to prevent flash
        // This runs synchronously before body renders
        try {
            const isMobile = window.innerWidth <= 768;
            if (!isMobile) {
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState === 'true') {
                    // Add class to html element immediately
                    document.documentElement.classList.add('sidebar-collapsed-init');
                }
            }
        } catch(e) {
            // localStorage might not be available in some contexts
        }
    </script>
    <style>
        /* Hide sidebar immediately if collapsed state was saved - but NOT the main content */
        html.sidebar-collapsed-init #adminSidebar {
            visibility: hidden;
        }
    </style>
</head>
<body class="admin-page @stack('body-class')">
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Mobile menu overlay -->
    <div class="mobile-menu-overlay" 
         id="mobileMenuOverlay" 
         onclick="toggleSidebar()"
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
                <!-- Error/Info Messages (Success messages shown as toast notifications) -->
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

    <!-- Page-specific Modals -->
    @stack('modals')

    <!-- Optimized JavaScript Bundles -->
    @vite(['resources/js/admin.js', 'resources/js/app.js'])
    
    <!-- Sidebar Persistence Script -->
    <script>
        // Persist sidebar state - runs after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Remove the init class to restore visibility
            document.documentElement.classList.remove('sidebar-collapsed-init');
            
            const isMobile = window.innerWidth <= 768;
            if (!isMobile) {
                const sidebar = document.getElementById('adminSidebar');
                const mainContent = document.querySelector('.admin-main');
                const body = document.body;
                const toggleBtn = document.querySelector('.sidebar-toggle-btn');
                const savedState = localStorage.getItem('sidebarCollapsed');
                
                if (savedState === 'true' && sidebar) {
                    sidebar.classList.add('collapsed');
                    if (mainContent) mainContent.classList.add('sidebar-collapsed');
                    if (body) body.classList.add('sidebar-collapsed');
                    if (toggleBtn) {
                        toggleBtn.classList.add('collapsed');
                        toggleBtn.setAttribute('aria-expanded', 'false');
                    }
                }
            }
        });
        
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.querySelector('.admin-main');
            const body = document.body;
            const toggleBtn = document.querySelector('.sidebar-toggle-btn');
            
            if (sidebar && main) {
                const isCollapsed = sidebar.classList.toggle('collapsed');
                main.classList.toggle('sidebar-collapsed');
                if (body) body.classList.toggle('sidebar-collapsed');
                if (toggleBtn) {
                    toggleBtn.classList.toggle('collapsed');
                    toggleBtn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                }
                localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
            }
        }
    </script>
    
    <!-- Global Success Message Toast Notifications -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                if (window.NotificationSystem) {
                    window.NotificationSystem.success('{{ session('success') }}');
                } else if (window.showSuccess) {
                    window.showSuccess('{{ session('success') }}');
                }
            @endif
        });
    </script>
    
    <!-- Page-specific JavaScript -->
    @stack('scripts')
</body>
</html>
