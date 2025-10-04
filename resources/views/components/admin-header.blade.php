@props(['title' => 'Admin Panel', 'subtitle' => '', 'level' => 1])

<header class="main-header" role="banner">
    <div class="header-left">
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
                    <span class="avatar-icon" aria-hidden="true"></span>
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->full_name ?? 'Dr. Admin' }}</div>
                    <div class="user-role">Department Head</div>
                </div>
                <span class="dropdown-arrow" aria-hidden="true"></span>
            </button>
            
            <div class="user-dropdown-menu" 
                 id="userDropdownMenu"
                 role="menu"
                 aria-label="User account menu"
                 style="display: none;">
                <!-- Department Head Features -->
                <div class="dropdown-section">
                    <div class="dropdown-section-title" role="presentation">Department Head</div>
                    <a href="{{ route('admin.interview-results') }}" 
                       class="dropdown-item"
                       role="menuitem"
                       aria-label="View interview results">
                        <span class="dropdown-text">Interview Results</span>
                    </a>
                    <a href="{{ route('admin.analytics') }}" 
                       class="dropdown-item"
                       role="menuitem"
                       aria-label="View analytics dashboard">
                        <span class="dropdown-text">Analytics</span>
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
