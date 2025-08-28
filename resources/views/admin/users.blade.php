<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Role Management - EnrollAssess Admin</title>
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-maroon: #800020;
            --primary-gold: #FFD700;
            --dark-maroon: #5C0016;
            --light-gold: #FFF8DC;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --border-gray: #E9ECEF;
            --text-gray: #6B7280;
            --text-dark: #1F2937;
            --success-green: #059669;
            --warning-orange: #D97706;
            --danger-red: #DC2626;
            --transition: all 0.3s ease;
        }

        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-gray);
        }

        .users-title {
            color: var(--primary-maroon);
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .users-title .title-icon {
            font-size: 32px;
        }

        .add-user-btn {
            background: var(--primary-maroon);
            color: var(--white);
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }

        .add-user-btn:hover {
            background: var(--dark-maroon);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(128, 0, 32, 0.3);
        }

        .users-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-gray);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card .stat-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .stat-card .stat-title {
            color: var(--text-gray);
            font-size: 14px;
            font-weight: 500;
            margin: 0 0 8px 0;
        }

        .stat-card .stat-value {
            color: var(--text-dark);
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .users-table-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-gray);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
            color: var(--white);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 8px 15px 8px 35px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            font-size: 14px;
            width: 250px;
            transition: var(--transition);
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-gold);
            background: rgba(255, 255, 255, 0.2);
        }

        .search-box .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .users-table th {
            background: var(--light-gray);
            color: var(--text-dark);
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-gray);
            font-size: 14px;
        }

        .users-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-gray);
            color: var(--text-dark);
            font-size: 14px;
        }

        .users-table tbody tr {
            transition: var(--transition);
        }

        .users-table tbody tr:hover {
            background: rgba(128, 0, 32, 0.02);
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
            background: linear-gradient(135deg, var(--primary-maroon), var(--primary-gold));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            font-size: 16px;
        }

        .user-details h4 {
            margin: 0 0 4px 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-details p {
            margin: 0;
            font-size: 13px;
            color: var(--text-gray);
        }

        .role-selector {
            position: relative;
        }

        .role-dropdown {
            padding: 8px 12px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            background: var(--white);
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            min-width: 140px;
        }

        .role-dropdown:focus {
            outline: none;
            border-color: var(--primary-maroon);
        }

        .role-dropdown option {
            padding: 8px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-badge.administrator {
            background: rgba(128, 0, 32, 0.1);
            color: var(--primary-maroon);
        }

        .role-badge.instructor {
            background: rgba(217, 119, 6, 0.1);
            color: var(--warning-orange);
        }

        .role-badge.department-head {
            background: rgba(255, 215, 0, 0.2);
            color: #B8860B;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-indicator.active .status-dot {
            background: var(--success-green);
        }

        .status-indicator.inactive .status-dot {
            background: var(--text-gray);
        }

        .user-actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .action-btn.edit {
            background: rgba(59, 130, 246, 0.1);
            color: #2563EB;
        }

        .action-btn.edit:hover {
            background: #2563EB;
            color: var(--white);
        }

        .action-btn.deactivate {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger-red);
        }

        .action-btn.deactivate:hover {
            background: var(--danger-red);
            color: var(--white);
        }

        .action-btn.activate {
            background: rgba(5, 150, 105, 0.1);
            color: var(--success-green);
        }

        .action-btn.activate:hover {
            background: var(--success-green);
            color: var(--white);
        }

        .permissions-section {
            margin-top: 30px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-gray);
            overflow: hidden;
        }

        .permissions-header {
            background: linear-gradient(135deg, var(--primary-gold), #DAA520);
            color: var(--dark-maroon);
            padding: 20px;
        }

        .permissions-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .permissions-grid {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .permission-card {
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            padding: 15px;
            background: var(--light-gray);
        }

        .permission-card h4 {
            margin: 0 0 10px 0;
            color: var(--primary-maroon);
            font-size: 16px;
            font-weight: 600;
        }

        .permission-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .permission-list li {
            padding: 4px 0;
            color: var(--text-dark);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .permission-list li::before {
            content: '✓';
            color: var(--success-green);
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .users-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }

            .search-box input {
                width: 100%;
            }

            .users-table-container {
                overflow-x: auto;
            }

            .users-table {
                min-width: 600px;
            }

            .permissions-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Loading and Success States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .success-flash {
            background: rgba(5, 150, 105, 0.1);
            animation: flashSuccess 1s ease-out;
        }

        @keyframes flashSuccess {
            0% { background: rgba(5, 150, 105, 0.3); }
            100% { background: transparent; }
        }
    </style>
</head>
<body>
    <!-- Include Admin Sidebar -->
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">
                    <span class="logo-icon">🎓</span>
                    EnrollAssess
                </h2>
            </div>
            
            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="nav-item">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="{{ route('admin.questions.index') }}" class="nav-item">
                    <span class="nav-icon">❓</span>
                    <span class="nav-text">Questions</span>
                </a>
                <a href="{{ route('admin.applicants.index') }}" class="nav-item">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Applicants</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="nav-item active">
                    <span class="nav-icon">👤</span>
                    <span class="nav-text">User Management</span>
                </a>
                <a href="{{ route('admin.reports') }}" class="nav-item">
                    <span class="nav-icon">📈</span>
                    <span class="nav-text">Reports</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">AD</div>
                    <div class="user-details">
                        <div class="user-name">Admin User</div>
                        <div class="user-role">Department Head</div>
                    </div>
                </div>
                                        <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <span class="logout-icon">🚪</span>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <main class="admin-main">
            <div class="main-content">
                <!-- Users Header -->
                <div class="users-header">
                    <h1 class="users-title">
                        <span class="title-icon">👤</span>
                        User Role Management
                    </h1>
                    <button onclick="showAddUserModal()" class="add-user-btn">
                        <span>➕</span>
                        Add New User
                    </button>
                </div>

                <!-- User Statistics -->
                <div class="users-stats">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <p class="stat-title">Total Users</p>
                        <h3 class="stat-value">8</h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👑</div>
                        <p class="stat-title">Administrators</p>
                        <h3 class="stat-value">3</h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🧑‍🏫</div>
                        <p class="stat-title">Instructors</p>
                        <h3 class="stat-value">4</h3>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🟢</div>
                        <p class="stat-title">Active Users</p>
                        <h3 class="stat-value">7</h3>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="users-table-container">
                    <div class="table-header">
                        <h3>Faculty Users</h3>
                        <div class="search-box">
                            <span class="search-icon">🔍</span>
                            <input type="text" placeholder="Search users..." id="userSearch">
                        </div>
                    </div>
                    
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr data-user-id="1">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">DH</div>
                                        <div class="user-details">
                                            <h4>Dr. Maria Santos</h4>
                                            <p>maria.santos@evsu.edu.ph</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select class="role-dropdown" onchange="updateUserRole(1, this.value)">
                                        <option value="department-head" selected>Department Head</option>
                                        <option value="administrator">Administrator</option>
                                        <option value="instructor">Instructor</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="status-indicator active">
                                        <span class="status-dot"></span>
                                        Active
                                    </div>
                                </td>
                                <td>2024-01-15 09:30 AM</td>
                                <td>
                                    <div class="user-actions">
                                        <button class="action-btn edit" onclick="editUser(1)">
                                            ✏️ Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr data-user-id="2">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">JD</div>
                                        <div class="user-details">
                                            <h4>Prof. John Dela Cruz</h4>
                                            <p>john.delacruz@evsu.edu.ph</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select class="role-dropdown" onchange="updateUserRole(2, this.value)">
                                        <option value="department-head">Department Head</option>
                                        <option value="administrator" selected>Administrator</option>
                                        <option value="instructor">Instructor</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="status-indicator active">
                                        <span class="status-dot"></span>
                                        Active
                                    </div>
                                </td>
                                <td>2024-01-15 08:45 AM</td>
                                <td>
                                    <div class="user-actions">
                                        <button class="action-btn edit" onclick="editUser(2)">
                                            ✏️ Edit
                                        </button>
                                        <button class="action-btn deactivate" onclick="toggleUserStatus(2, 'deactivate')">
                                            ⏸️ Deactivate
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr data-user-id="3">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">AR</div>
                                        <div class="user-details">
                                            <h4>Prof. Anna Reyes</h4>
                                            <p>anna.reyes@evsu.edu.ph</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select class="role-dropdown" onchange="updateUserRole(3, this.value)">
                                        <option value="department-head">Department Head</option>
                                        <option value="administrator" selected>Administrator</option>
                                        <option value="instructor">Instructor</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="status-indicator active">
                                        <span class="status-dot"></span>
                                        Active
                                    </div>
                                </td>
                                <td>2024-01-14 04:20 PM</td>
                                <td>
                                    <div class="user-actions">
                                        <button class="action-btn edit" onclick="editUser(3)">
                                            ✏️ Edit
                                        </button>
                                        <button class="action-btn deactivate" onclick="toggleUserStatus(3, 'deactivate')">
                                            ⏸️ Deactivate
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr data-user-id="4">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">MG</div>
                                        <div class="user-details">
                                            <h4>Prof. Michael Garcia</h4>
                                            <p>michael.garcia@evsu.edu.ph</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select class="role-dropdown" onchange="updateUserRole(4, this.value)">
                                        <option value="department-head">Department Head</option>
                                        <option value="administrator">Administrator</option>
                                        <option value="instructor" selected>Instructor</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="status-indicator active">
                                        <span class="status-dot"></span>
                                        Active
                                    </div>
                                </td>
                                <td>2024-01-14 02:15 PM</td>
                                <td>
                                    <div class="user-actions">
                                        <button class="action-btn edit" onclick="editUser(4)">
                                            ✏️ Edit
                                        </button>
                                        <button class="action-btn deactivate" onclick="toggleUserStatus(4, 'deactivate')">
                                            ⏸️ Deactivate
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr data-user-id="5">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">LT</div>
                                        <div class="user-details">
                                            <h4>Prof. Lisa Torres</h4>
                                            <p>lisa.torres@evsu.edu.ph</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select class="role-dropdown" onchange="updateUserRole(5, this.value)">
                                        <option value="department-head">Department Head</option>
                                        <option value="administrator">Administrator</option>
                                        <option value="instructor" selected>Instructor</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="status-indicator inactive">
                                        <span class="status-dot"></span>
                                        Inactive
                                    </div>
                                </td>
                                <td>2024-01-10 11:30 AM</td>
                                <td>
                                    <div class="user-actions">
                                        <button class="action-btn edit" onclick="editUser(5)">
                                            ✏️ Edit
                                        </button>
                                        <button class="action-btn activate" onclick="toggleUserStatus(5, 'activate')">
                                            ▶️ Activate
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Role Permissions Reference -->
                <div class="permissions-section">
                    <div class="permissions-header">
                        <h3>🔐 Role Permissions Reference</h3>
                    </div>
                    <div class="permissions-grid">
                        <div class="permission-card">
                            <h4>👑 Department Head</h4>
                            <ul class="permission-list">
                                <li>Full system administration</li>
                                <li>Manage all users and roles</li>
                                <li>Generate final reports</li>
                                <li>Configure system settings</li>
                                <li>Access all applicant data</li>
                                <li>Override exam and interview scores</li>
                            </ul>
                        </div>
                        <div class="permission-card">
                            <h4>🔧 Administrator</h4>
                            <ul class="permission-list">
                                <li>Manage exam questions</li>
                                <li>View and manage applicants</li>
                                <li>Generate access codes</li>
                                <li>Schedule interviews</li>
                                <li>Update applicant status</li>
                                <li>Export applicant data</li>
                            </ul>
                        </div>
                        <div class="permission-card">
                            <h4>🧑‍🏫 Instructor</h4>
                            <ul class="permission-list">
                                <li>View assigned applicants</li>
                                <li>Conduct interviews</li>
                                <li>Submit interview scores</li>
                                <li>Add interview notes</li>
                                <li>View exam results</li>
                                <li>Generate basic reports</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Search functionality
        document.getElementById('userSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTableBody tr');
            
            rows.forEach(row => {
                const userName = row.querySelector('.user-details h4').textContent.toLowerCase();
                const userEmail = row.querySelector('.user-details p').textContent.toLowerCase();
                const userRole = row.querySelector('.role-dropdown').value.toLowerCase();
                
                if (userName.includes(searchTerm) || userEmail.includes(searchTerm) || userRole.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Update user role
        function updateUserRole(userId, newRole) {
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            row.classList.add('loading');
            
            // Simulate API call
            setTimeout(() => {
                row.classList.remove('loading');
                row.classList.add('success-flash');
                
                // Show success message
                showNotification(`User role updated to ${newRole.replace('-', ' ')}`, 'success');
                
                setTimeout(() => {
                    row.classList.remove('success-flash');
                }, 1000);
            }, 500);
        }

        // Toggle user status
        function toggleUserStatus(userId, action) {
            const confirmation = confirm(`Are you sure you want to ${action} this user?`);
            if (!confirmation) return;
            
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            const statusIndicator = row.querySelector('.status-indicator');
            const actionBtn = row.querySelector(`.action-btn.${action}`);
            
            row.classList.add('loading');
            
            // Simulate API call
            setTimeout(() => {
                row.classList.remove('loading');
                
                if (action === 'activate') {
                    statusIndicator.className = 'status-indicator active';
                    statusIndicator.innerHTML = '<span class="status-dot"></span>Active';
                    actionBtn.className = 'action-btn deactivate';
                    actionBtn.innerHTML = '⏸️ Deactivate';
                    actionBtn.onclick = () => toggleUserStatus(userId, 'deactivate');
                } else {
                    statusIndicator.className = 'status-indicator inactive';
                    statusIndicator.innerHTML = '<span class="status-dot"></span>Inactive';
                    actionBtn.className = 'action-btn activate';
                    actionBtn.innerHTML = '▶️ Activate';
                    actionBtn.onclick = () => toggleUserStatus(userId, 'activate');
                }
                
                showNotification(`User ${action}d successfully`, 'success');
            }, 500);
        }

        // Edit user
        function editUser(userId) {
            showNotification('Edit user functionality would open a detailed form (demo)', 'info');
        }

        // Show add user modal
        function showAddUserModal() {
            showNotification('Add user modal would open here (demo)', 'info');
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                animation: slideIn 0.3s ease-out;
                max-width: 300px;
            `;
            
            switch(type) {
                case 'success':
                    notification.style.background = 'var(--success-green)';
                    break;
                case 'error':
                    notification.style.background = 'var(--danger-red)';
                    break;
                default:
                    notification.style.background = 'var(--primary-maroon)';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                showAddUserModal();
            }
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('userSearch').focus();
            }
        });

        // Auto-save role changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('role-dropdown')) {
                // Auto-save after 1 second of no changes
                clearTimeout(window.autoSaveTimeout);
                window.autoSaveTimeout = setTimeout(() => {
                    console.log('Auto-saving role changes...');
                }, 1000);
            }
        });
    </script>
</body>
</html>