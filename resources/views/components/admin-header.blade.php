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
        
        <!-- Notifications Dropdown -->
        @if(auth()->user()->role !== 'instructor')
        <div class="notification-dropdown">
            <button class="notification-toggle" 
                    onclick="toggleNotificationDropdown()"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-label="Notifications"
                    id="notificationToggle">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span class="notification-badge" data-notification-count="0" style="display: none;">0</span>
            </button>
            
            <div class="notification-panel" id="notificationPanel" style="display: none;">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <button class="mark-all-read-btn" onclick="markAllNotificationsRead()" id="markAllReadBtn" style="display: none;">
                        Mark all as read
                    </button>
                </div>
                <div class="notification-list" id="notificationList">
                    <div class="notification-loading">Loading notifications...</div>
                </div>
                <div class="notification-footer">
                    <a href="#" onclick="event.preventDefault(); viewAllNotifications();" class="view-all-link">View all notifications</a>
                </div>
            </div>
        </div>
        @endif
        
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
                    <a href="{{ route('admin.settings.index') }}" 
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
        
        if (toggle && !toggle.contains(event.target)) {
            dropdown.style.display = 'none';
        }
        
        // Close notification dropdown when clicking outside
        const notificationPanel = document.getElementById('notificationPanel');
        const notificationToggle = document.getElementById('notificationToggle');
        
        if (notificationPanel && notificationToggle) {
            if (!notificationPanel.contains(event.target) && !notificationToggle.contains(event.target)) {
                notificationPanel.style.display = 'none';
                notificationToggle.setAttribute('aria-expanded', 'false');
            }
        }
    });
    
    // Notification dropdown functionality
    function toggleNotificationDropdown() {
        const panel = document.getElementById('notificationPanel');
        const toggle = document.getElementById('notificationToggle');
        
        if (panel && toggle) {
            const isOpen = panel.style.display === 'block';
            
            if (!isOpen) {
                // Calculate position relative to toggle button
                const toggleRect = toggle.getBoundingClientRect();
                const headerRect = toggle.closest('.main-header')?.getBoundingClientRect();
                
                // Position the panel below the toggle button
                panel.style.display = 'block';
                panel.style.position = 'fixed';
                panel.style.top = (toggleRect.bottom + 2) + 'px';
                panel.style.right = (window.innerWidth - toggleRect.right) + 'px';
                
                toggle.setAttribute('aria-expanded', 'true');
                loadNotifications();
            } else {
                panel.style.display = 'none';
                toggle.setAttribute('aria-expanded', 'false');
            }
        }
    }
    
    // Load notifications
    async function loadNotifications() {
        const list = document.getElementById('notificationList');
        if (!list) return;
        
        try {
            const response = await fetch('/admin/notifications', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            
            if (!response.ok) {
                list.innerHTML = '<div class="notification-empty">Failed to load notifications</div>';
                return;
            }
            
            const data = await response.json();
            
            if (data.success && data.notifications) {
                if (data.notifications.length === 0) {
                    list.innerHTML = '<div class="notification-empty">No notifications</div>';
                    return;
                }
                
                let html = '';
                data.notifications.forEach(notification => {
                    const isRead = notification.read_at !== null;
                    const notificationData = typeof notification.data === 'string' 
                        ? JSON.parse(notification.data) 
                        : notification.data;
                    
                    const timeAgo = getTimeAgo(notification.created_at);
                    const icon = notificationData.icon || 'bell';
                    
                    html += `
                        <div class="notification-item ${isRead ? 'read' : 'unread'}" data-id="${notification.id}" onclick="handleNotificationClick('${notification.id}', '${notificationData.url || ''}')">
                            <div class="notification-icon">
                                <i class="fas fa-${icon}"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">${notificationData.title || 'Notification'}</div>
                                <div class="notification-message">${notificationData.message || ''}</div>
                                <div class="notification-time">${timeAgo}</div>
                            </div>
                            ${!isRead ? '<div class="notification-unread-dot"></div>' : ''}
                        </div>
                    `;
                });
                
                list.innerHTML = html;
                
                // Update mark all read button visibility
                const markAllBtn = document.getElementById('markAllReadBtn');
                if (markAllBtn) {
                    markAllBtn.style.display = data.unread_count > 0 ? 'block' : 'none';
                }
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            list.innerHTML = '<div class="notification-empty">Error loading notifications</div>';
        }
    }
    
    // Handle notification click
    async function handleNotificationClick(notificationId, url) {
        // Mark as read
        await markNotificationRead(notificationId);
        
        // Navigate if URL provided
        if (url) {
            window.location.href = url;
        }
    }
    
    // Mark notification as read
    async function markNotificationRead(notificationId) {
        try {
            const response = await fetch(`/admin/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                credentials: 'same-origin',
            });
            
            if (response.ok) {
                const item = document.querySelector(`[data-id="${notificationId}"]`);
                if (item) {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    const dot = item.querySelector('.notification-unread-dot');
                    if (dot) dot.remove();
                }
                updateNotificationCount();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
    
    // Mark all notifications as read
    async function markAllNotificationsRead() {
        try {
            const response = await fetch('/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                credentials: 'same-origin',
            });
            
            if (response.ok) {
                loadNotifications();
                updateNotificationCount();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }
    
    // Update notification count badge
    async function updateNotificationCount() {
        try {
            const response = await fetch('/admin/notifications/unread-count', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            
            if (!response.ok) return;
            
            const data = await response.json();
            
            if (data.success) {
                const badge = document.querySelector('[data-notification-count]');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = data.count > 0 ? 'inline-block' : 'none';
                }
            }
        } catch (error) {
            console.error('Error updating notification count:', error);
        }
    }
    
    // Get time ago string
    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
        
        return date.toLocaleDateString();
    }
    
    // View all notifications (placeholder)
    function viewAllNotifications() {
        // Could navigate to a full notifications page
        window.location.href = '{{ route("admin.dashboard") }}';
    }
    
    // Initialize notification count on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateNotificationCount();
        // Update every 60 seconds
        setInterval(updateNotificationCount, 60000);
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
