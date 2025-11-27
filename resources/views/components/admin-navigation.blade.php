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
     id="adminSidebar"
     role="navigation" 
     aria-label="{{ $userRole === 'instructor' ? 'Instructor' : 'Admin' }} navigation menu">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('images/evsu-logo.png') }}" alt="EVSU Logo" class="logo-image">
            <div class="logo-content">
                <h2 class="sidebar-title">EnrollAssess</h2>
                <p class="sidebar-subtitle">{{ $userRole === 'instructor' ? 'Instructor Portal' : 'Admin Portal' }}</p>
            </div>
        </div>
    </div>

    <div class="nav-menu" role="list">
        {{-- Common Navigation Items --}}
        @if($userRole === 'department-head')
            {{-- School Year Dropdown - Above Dashboard (Visible on all pages for consistency) --}}
            @if(isset($schoolYears) && $schoolYears->count() > 0)
            <div class="nav-item school-year-nav-item" role="listitem">
                <form method="POST" action="{{ route('admin.school-year.switch') }}" id="schoolYearNavForm" class="school-year-nav-form">
                    @csrf
                    <select name="school_year_id" 
                            id="schoolYearNavSelect" 
                            class="school-year-nav-select"
                            onchange="document.getElementById('schoolYearNavForm').submit();"
                            aria-label="Select school year">
                        @foreach($schoolYears ?? [] as $schoolYear)
                            <option value="{{ $schoolYear->school_year_id }}" 
                                    {{ ($currentSchoolYearId ?? null) == $schoolYear->school_year_id ? 'selected' : '' }}>
                                {{ $schoolYear->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            @endif
            
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.dashboard') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.dashboard')) aria-current="page" @endif
                   aria-label="Dashboard - Main admin overview">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-tachometer-alt"></i></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.applicants.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.applicants') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.applicants')) aria-current="page" @endif
                   aria-label="Applicants - View and manage applicants">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-user-graduate"></i></span>
                    <span class="nav-text">Applicants</span>
                </a>
            </div>
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.sets-questions.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.sets-questions') || str_starts_with($currentRoute, 'admin.exams') || str_starts_with($currentRoute, 'admin.questions') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.sets-questions') || str_starts_with($currentRoute, 'admin.exams') || str_starts_with($currentRoute, 'admin.questions')) aria-current="page" @endif
                   aria-label="Question Bank - Manage exam questions and sets">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-book"></i></span>
                    <span class="nav-text">Question Bank</span>
                </a>
            </div>
            
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.interviews.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.interviews') && !str_contains($currentRoute, 'pool') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.interviews') && !str_contains($currentRoute, 'pool')) aria-current="page" @endif
                   aria-label="Interviews - Manage interview schedules and records">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-calendar-alt"></i></span>
                    <span class="nav-text">Interviews</span>
                </a>
            </div>
            
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.users.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.users')) aria-current="page" @endif
                   aria-label="Users - Manage system users and permissions">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-users-cog"></i></span>
                    <span class="nav-text">Users</span>
                </a>
            </div>
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.reports.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.reports') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.reports')) aria-current="page" @endif
                   aria-label="Reports - View system reports and analytics">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-chart-bar"></i></span>
                    <span class="nav-text">Reports</span>
                </a>
            </div>
            
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.settings.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.settings') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.settings')) aria-current="page" @endif
                   aria-label="Settings - System configuration and preferences">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-cog"></i></span>
                    <span class="nav-text">Settings</span>
                </a>
            </div>

            
        @elseif($userRole === 'instructor')
            <div class="nav-item" role="listitem">
                <a href="{{ route('instructor.dashboard') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.dashboard') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'instructor.dashboard')) aria-current="page" @endif
                   aria-label="Dashboard - Instructor overview">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-tachometer-alt"></i></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
            
            @if(auth()->user()->hasPermission('assign_applicants'))
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.applicants.assign') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.applicants.assign') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.applicants.assign')) aria-current="page" @endif
                   aria-label="Assign applicants to instructors">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-user-plus"></i></span>
                    <span class="nav-text">Assign Applicants</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasPermission('view_reports'))
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.reports.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.reports') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.reports')) aria-current="page" @endif
                   aria-label="Reports - View system reports and analytics">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-chart-bar"></i></span>
                    <span class="nav-text">Reports</span>
                </a>
            </div>
            @endif

            @if(auth()->user()->hasPermission('manage_questions'))
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.sets-questions.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.sets-questions') || str_starts_with($currentRoute, 'admin.exams') || str_starts_with($currentRoute, 'admin.questions') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.sets-questions') || str_starts_with($currentRoute, 'admin.exams') || str_starts_with($currentRoute, 'admin.questions')) aria-current="page" @endif
                   aria-label="Question Bank - Manage exam questions and sets">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-book"></i></span>
                    <span class="nav-text">Question Bank</span>
                </a>
            </div>
            @endif
            
            <div class="nav-item" role="listitem">
                <a href="{{ route('instructor.applicants') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.applicants') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'instructor.applicants')) aria-current="page" @endif
                   aria-label="My assigned applicants">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-user-graduate"></i></span>
                    <span class="nav-text">My Applicants</span>
                </a>
            </div>
            <div class="nav-item" role="listitem">
                <a href="{{ route('instructor.schedule') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.schedule') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'instructor.schedule')) aria-current="page" @endif
                   aria-label="Interview schedule">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-calendar-alt"></i></span>
                    <span class="nav-text">Schedule</span>
                </a>
            </div>
            <div class="nav-item" role="listitem">
                <a href="{{ route('instructor.interview-history') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.interview-history') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'instructor.interview-history')) aria-current="page" @endif
                   aria-label="Interview history and records">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-history"></i></span>
                    <span class="nav-text">Interview History</span>
                </a>
            </div>
            <div class="nav-item" role="listitem">
                <a href="{{ route('instructor.guidelines') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'instructor.guidelines') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'instructor.guidelines')) aria-current="page" @endif
                   aria-label="Interview guidelines and standards">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-file-alt"></i></span>
                    <span class="nav-text">Guidelines</span>
                </a>
            </div>
        @endif
    </div>
</nav>
