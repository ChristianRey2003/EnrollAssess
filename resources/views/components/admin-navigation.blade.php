{{--
    Admin Navigation Component
    
    Props:
    - userRole: Current user's role for conditional navigation
    - currentRoute: Current route name for active state management
--}}

@props([
    'userRole' => auth()->user()->role ?? 'department-head',
    'currentRoute' => request()->route()->getName() ?? ''
])

<nav class="admin-sidebar {{ $userRole === 'instructor' ? 'instructor-sidebar' : '' }}" 
     role="navigation" 
     aria-label="{{ $userRole === 'instructor' ? 'Instructor' : 'Admin' }} navigation menu">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('images/image-removebg-preview.png') }}" 
                 alt="University Logo" 
                 class="logo-image"
                 width="60" 
                 height="60" 
                 loading="eager"
                 decoding="async">
            <div class="logo-content">
                <h2 class="sidebar-title">EnrollAssess</h2>
                <p class="sidebar-subtitle">{{ $userRole === 'instructor' ? 'Instructor Portal' : 'Admin Portal' }}</p>
            </div>
        </div>
    </div>

    <div class="nav-menu" role="list">
        {{-- Common Navigation Items --}}
        @if($userRole === 'department-head')
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.dashboard') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.dashboard')) aria-current="page" @endif
                   aria-label="Dashboard - Main admin overview">
                    <span class="nav-icon" aria-hidden="true"></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.applicants.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.applicants') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Applicants</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.sets-questions.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.sets-questions') || str_starts_with($currentRoute, 'admin.exams') || str_starts_with($currentRoute, 'admin.questions') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Sets & Questions</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.interviews.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.interviews') && !str_contains($currentRoute, 'pool') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Interviews</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.interviews.pool.overview') }}" 
                   class="nav-link {{ str_contains($currentRoute, 'interviews.pool') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Interview Pool</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.users.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Users</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.reports') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.reports') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Reports</span>
                </a>
            </div>

            
        @elseif($userRole === 'instructor')
            <div class="nav-item">
                <a href="{{ route('instructor.dashboard') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('instructor.interview-pool.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.interview-pool') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Interview Pool</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('instructor.applicants') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.applicants') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">My Applicants</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('instructor.schedule') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.schedule') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Schedule</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('instructor.interview-history') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.interview-history') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Interview History</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('instructor.guidelines') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.guidelines') ? 'active' : '' }}">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Guidelines</span>
                </a>
            </div>
        @endif
    </div>

    <div class="nav-bottom">
        <form method="POST" action="{{ $userRole === 'instructor' ? route('admin.logout') : route('admin.logout') }}">
            @csrf
            <button type="submit" class="logout-link">
                <span class="nav-icon"></span>
                <span class="nav-text">Logout</span>
            </button>
        </form>
    </div>
</nav>
