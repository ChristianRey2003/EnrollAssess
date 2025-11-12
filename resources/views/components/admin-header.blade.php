@props(['title' => 'Admin Panel', 'subtitle' => '', 'level' => 1])

<header class="main-header" role="banner">
    <div class="header-left">
        <!-- Hamburger Menu Toggle -->
        <button class="sidebar-toggle-btn" 
                onclick="toggleSidebar()" 
                aria-label="Toggle sidebar navigation"
                aria-expanded="true"
                aria-controls="adminSidebar">
            <span class="hamburger-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>
        
        <div class="header-title-wrapper">
            @if($level === 1)
                <h1>{{ $title }}</h1>
            @elseif($level === 2)
                <h2>{{ $title }}</h2>
            @elseif($level === 3)
                <h3>{{ $title }}</h3>
            @else
                <h1>{{ $title }}</h1>
            @endif
            
            @if($subtitle)
                <p class="header-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    <div class="header-right">
        <div class="header-time">
            {{ now()->format('M d, Y g:i A') }}
        </div>
        <div class="user-dropdown">
            <button class="user-dropdown-toggle" 
                    onclick="toggleUserDropdown()"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-label="User menu for {{ auth()->user()->full_name ?? 'Dr. Admin' }}">
                <div class="user-avatar">
                    @if(auth()->user()->profile_picture_url)
                        <img src="{{ auth()->user()->profile_picture_url }}" alt="{{ auth()->user()->full_name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        <span class="avatar-icon" aria-hidden="true">{{ auth()->user()->initials ?? 'A' }}</span>
                    @endif
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->full_name ?? 'Dr. Admin' }}</div>
                    <div class="user-role">
                        @if(auth()->user()->role === 'department-head')
                            Department Head
                        @elseif(auth()->user()->role === 'administrator')
                            Administrator
                        @else
                            Instructor
                        @endif
                    </div>
                </div>
                <span class="dropdown-arrow" aria-hidden="true"></span>
            </button>
            
            <div class="user-dropdown-menu" 
                 id="userDropdownMenu"
                 role="menu"
                 aria-label="User account menu"
                 style="display: none;">
                <!-- Profile Link -->
                <a href="{{ route('admin.profile.edit') }}" 
                   class="dropdown-item"
                   role="menuitem"
                   aria-label="View profile">
                    <span class="dropdown-text">My Profile</span>
                </a>
                
                <div class="dropdown-divider" role="separator"></div>
                
                <!-- Department Head Features -->
                <div class="dropdown-section">
                    <div class="dropdown-section-title" role="presentation">Department Head</div>
                    <a href="{{ route('admin.interview-results') }}" 
                       class="dropdown-item"
                       role="menuitem"
                       aria-label="View interview results">
                        <span class="dropdown-text">Interview Results</span>
                    </a>
                    <a href="{{ route('admin.analytics.index') }}" 
                       class="dropdown-item"
                       role="menuitem"
                       aria-label="View analytics dashboard">
                        <span class="dropdown-text">Analytics</span>
                    </a>
                    <a href="{{ route('admin.settings') }}" 
                       class="dropdown-item"
                       role="menuitem"
                       aria-label="System settings">
                        <span class="dropdown-text">Settings</span>
                    </a>
                </div>
                
                <div class="dropdown-divider" role="separator"></div>
                
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" 
                            class="dropdown-item logout-item"
                            role="menuitem"
                            aria-label="Logout from admin panel">
                        <span class="dropdown-text">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<script>
    // User dropdown functionality
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdownMenu');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdownMenu');
        const toggle = document.querySelector('.user-dropdown-toggle');
        
        if (!toggle.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
</script>
