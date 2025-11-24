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
            
            {{-- @if($subtitle)
                <p class="header-subtitle">{{ $subtitle }}</p>
            @endif --}}
        </div>
    </div>
    <div class="header-right">
        <div class="header-time" id="headerTime" data-server-time="{{ now()->setTimezone(\App\Models\Settings::getSetting('app_timezone', config('app.timezone')))->format('Y-m-d H:i:s') }}" data-timezone="{{ \App\Models\Settings::getSetting('app_timezone', config('app.timezone')) }}">
            {{ now()->setTimezone(\App\Models\Settings::getSetting('app_timezone', config('app.timezone')))->format('M d, Y g:i A') }}
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
                <a href="{{ auth()->user()->role === 'instructor' ? route('instructor.profile.edit') : route('admin.profile.edit') }}" 
                   class="dropdown-item"
                   role="menuitem"
                   aria-label="View profile">
                    <span class="dropdown-text">My Profile</span>
                </a>
                
                <div class="dropdown-divider" role="separator"></div>
                
                <!-- Department Head Features (not for instructors) -->
                @if(auth()->user()->role !== 'instructor')
                <div class="dropdown-section">
                    <a href="{{ route('admin.settings') }}" 
                       class="dropdown-item"
                       role="menuitem"
                       aria-label="System settings">
                        <span class="dropdown-text">Settings</span>
                    </a>
                </div>
                
                <div class="dropdown-divider" role="separator"></div>
                @endif
                
                <form method="POST" action="{{ auth()->user()->role === 'instructor' ? route('logout') : route('admin.logout') }}">
                    @csrf
                    <button type="submit" 
                            class="dropdown-item logout-item"
                            role="menuitem"
                            aria-label="Logout from {{ auth()->user()->role === 'instructor' ? 'instructor' : 'admin' }} panel">
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

    // Real-time clock update
    (function() {
        const timeElement = document.getElementById('headerTime');
        if (!timeElement) return;

        const serverTimeStr = timeElement.getAttribute('data-server-time');
        const timezone = timeElement.getAttribute('data-timezone');
        
        if (!serverTimeStr) return;

        // Parse server time (format: Y-m-d H:i:s)
        const [datePart, timePart] = serverTimeStr.split(' ');
        const [year, month, day] = datePart.split('-');
        const [hour, minute, second] = timePart.split(':');
        
        // Create Date object from server time (assume it's already in the correct timezone)
        let serverTime = new Date(year, month - 1, day, hour, minute, second);
        
        function updateTime() {
            // Calculate elapsed time since page load
            const now = new Date();
            const elapsed = now - (window.pageLoadTime || now);
            const currentTime = new Date(serverTime.getTime() + elapsed);
            
            // Format time
            const options = { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            };
            
            // If timezone is specified, try to use it (browser support may vary)
            if (timezone && Intl && Intl.DateTimeFormat) {
                try {
                    options.timeZone = timezone;
                } catch(e) {
                    // Timezone not supported, use local
                }
            }
            
            timeElement.textContent = currentTime.toLocaleString('en-US', options);
        }

        // Store page load time
        window.pageLoadTime = new Date();
        
        // Update immediately and then every second
        updateTime();
        setInterval(updateTime, 1000);
    })();
</script>
