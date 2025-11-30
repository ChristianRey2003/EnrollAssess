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
                <p class="sidebar-subtitle">
                    @if($userRole === 'instructor')
                        Instructor Portal
                    @elseif($userRole === 'administrator')
                        Superadmin Portal
                    @else
                        Admin Portal
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="nav-menu" role="list">
        {{-- Common Navigation Items --}}
        @if(in_array($userRole, ['department-head', 'administrator']))
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

            @if($userRole === 'administrator')
            <div class="nav-item" role="listitem">
                <a href="{{ route('admin.audit-logs.index') }}" 
                   class="nav-link {{ str_starts_with($currentRoute, 'admin.audit-logs') ? 'active' : '' }}"
                   @if(str_starts_with($currentRoute, 'admin.audit-logs')) aria-current="page" @endif
                   aria-label="Audit Logs - System activity and audit trail">
                    <span class="nav-icon" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
                    <span class="nav-text">Audit Logs</span>
                </a>
            </div>
            @endif

            
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
            
            @if(auth()->user()->hasPermission('applicants.assign'))
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

            @if(auth()->user()->hasPermission('reports.view'))
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

            @if(auth()->user()->hasPermission('questions.view'))
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

    <div class="sidebar-footer" style="border-top: none; padding-top: 0; padding-bottom: 10px;">
        <button onclick="openCreditsModal()" class="logout-link w-full flex justify-center items-center" aria-label="View Credits">
            <span class="nav-icon" style="margin-right: 0;"><i class="fas fa-info-circle"></i></span>
        </button>
    </div>
</nav>

@push('modals')
<!-- Credits Modal -->
<div id="creditsModal" class="fixed inset-0 flex items-center justify-center hidden">
    <!-- Invisible Backdrop for closing -->
    <div class="absolute inset-0 bg-black/10 backdrop-blur-[1px]" onclick="closeCreditsModal()"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-[90vw] md:max-w-md mx-4 transform transition-all duration-300 scale-95 opacity-0 border border-gray-100" id="creditsModalContent">
        <div class="p-3 md:p-4 text-center">
            <div class="mb-2 md:mb-3">
                <h3 class="text-sm md:text-base font-bold text-gray-800">EnrollAssess</h3>
                <p class="text-[10px] md:text-xs text-gray-500">Development Team</p>
            </div>
            
            <div class="flex flex-col md:flex-row md:flex-nowrap justify-center gap-2 md:gap-4 mb-2 md:mb-3">
                <div class="flex flex-col items-center px-2 md:px-3 py-1.5 md:py-2 hover:bg-gray-50 rounded-lg transition-colors">
                    <p class="font-bold text-gray-700 text-xs md:text-sm whitespace-nowrap">Christian Rey Y.Alegre</p>
                    <p class="text-[10px] md:text-xs text-gray-400 uppercase mt-0.5 md:mt-1">Developer</p>
                </div>
                <div class="flex flex-col items-center px-2 md:px-3 py-1.5 md:py-2 hover:bg-gray-50 rounded-lg transition-colors">
                    <p class="font-bold text-gray-700 text-xs md:text-sm whitespace-nowrap">Marjorie G. Bebanco</p>
                    <p class="text-[10px] md:text-xs text-gray-400 uppercase mt-0.5 md:mt-1">UI/UX Designer</p>
                </div>
                <div class="flex flex-col items-center px-2 md:px-3 py-1.5 md:py-2 hover:bg-gray-50 rounded-lg transition-colors">
                    <p class="font-bold text-gray-700 text-xs md:text-sm whitespace-nowrap">Hazel A. Yray</p>
                    <p class="text-[10px] md:text-xs text-gray-400 uppercase mt-0.5 md:mt-1">QA</p>
                </div>
                <div class="flex flex-col items-center px-2 md:px-3 py-1.5 md:py-2 hover:bg-gray-50 rounded-lg transition-colors border-t md:border-t-0 md:border-l border-gray-100 pt-2 md:pt-1.5 md:pl-4">
                    <p class="font-bold text-gray-700 text-xs md:text-sm whitespace-nowrap">Joseph Jaymel S. Morpos</p>
                    <p class="text-[10px] md:text-xs text-gray-400 uppercase mt-0.5 md:mt-1">Adviser</p>
                </div>
            </div>
            
            <button onclick="closeCreditsModal()" class="text-[10px] md:text-xs text-gray-500 hover:text-gray-800 font-medium transition-colors px-3 md:px-4 py-1 md:py-1.5 bg-gray-50 rounded-lg hover:bg-gray-100">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    function openCreditsModal() {
        const modal = document.getElementById('creditsModal');
        const content = document.getElementById('creditsModalContent');
        
        // Force move to body if not already there to avoid sidebar constraints
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        
        if (modal && content) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex'; // Ensure flex is applied
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    }

    function closeCreditsModal() {
        const modal = document.getElementById('creditsModal');
        const content = document.getElementById('creditsModalContent');
        if (modal && content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }, 300);
        }
    }
    
    // Move to body on load as well
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('creditsModal');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
</script>
@endpush
