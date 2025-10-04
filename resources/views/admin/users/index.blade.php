@extends('layouts.admin')

@section('title', 'User Management')

@php
    $pageTitle = 'User Management';
    $pageSubtitle = 'Manage faculty accounts, roles, and permissions';
@endphp

@section('content')
                <!-- Quick Stats -->
                <div class="stats-grid users-stats">
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['total_users'] }}</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['department_heads'] }}</div>
                        <div class="stat-label">Department Heads</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['administrators'] }}</div>
                        <div class="stat-label">Administrators</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['instructors'] }}</div>
                        <div class="stat-label">Instructors</div>
                    </div>
                </div>

                <!-- User Management Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Faculty Accounts</h2>
                        <div class="section-actions">
                            <a href="{{ route('admin.users.export') }}" class="btn-secondary" id="exportUsers">Export CSV</a>
                            <a href="{{ route('admin.users.create') }}" class="btn-primary">
                                Add New User
                            </a>
                        </div>
                    </div>
                    <div class="section-content">
                        <!-- Search and Filter -->
                        <div class="search-filter-bar">
                            <form method="GET" class="search-form">
                                <div class="search-controls">
                                    <div class="search-input-group">
                                        <input type="text" name="search" placeholder="Search users by name, username, or email..." 
                                               value="{{ request('search') }}" class="search-input">
                                        <button type="submit" class="search-btn">Search</button>
                                    </div>
                                    
                                    <div class="filter-group">
                                        <select name="role" class="filter-select">
                                            <option value="">All Roles</option>
                                            <option value="department-head" {{ request('role') == 'department-head' ? 'selected' : '' }}>Department Head</option>
                                            <option value="administrator" {{ request('role') == 'administrator' ? 'selected' : '' }}>Administrator</option>
                                            <option value="instructor" {{ request('role') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                                        </select>
                                        
                                        <select name="status" class="filter-select">
                                            <option value="">All Status</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Recently Active</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        
                                        <button type="submit" class="btn-secondary">Filter</button>
                                        
                                        @if(request()->hasAny(['search', 'role', 'status']))
                                            <a href="{{ route('admin.users.index') }}" class="btn-clear">Clear</a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Users Table -->
                        <div class="table-wrapper">
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Last Login</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    {{ substr($user->full_name, 0, 2) }}
                                                </div>
                                                <div>
                                                    <div class="user-name">{{ $user->full_name }}</div>
                                                    <div class="user-created">Joined {{ $user->created_at->format('M Y') }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="username">{{ $user->username }}</td>
                                        <td>
                                            <span class="role-badge role-{{ str_replace('-', '', $user->role) }}">
                                                {{ ucfirst(str_replace('-', ' ', $user->role)) }}
                                            </span>
                                        </td>
                                        <td class="email">{{ $user->email }}</td>
                                        <td class="last-login">
                                            <span title="{{ $user->updated_at->format('M d, Y g:i A') }}">
                                                {{ $user->updated_at->diffForHumans() }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $daysSinceLogin = $user->updated_at->diffInDays(now());
                                            @endphp
                                            <span class="status-badge {{ $daysSinceLogin <= 7 ? 'active' : ($daysSinceLogin <= 30 ? 'moderate' : 'inactive') }}">
                                                {{ $daysSinceLogin <= 7 ? 'Active' : ($daysSinceLogin <= 30 ? 'Moderate' : 'Inactive') }}
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <div class="action-buttons">
                                                <a href="{{ route('admin.users.show', $user->user_id) }}" 
                                                   class="btn-sm btn-info" title="View Details">
                                                    View
                                                </a>
                                                
                                                @if($user->user_id !== auth()->id())
                                                    <a href="{{ route('admin.users.edit', $user->user_id) }}" 
                                                       class="btn-sm btn-warning" title="Edit User">
                                                        Edit
                                                    </a>
                                                    
                                                    <button onclick="resetPassword({{ $user->user_id }}, '{{ $user->full_name }}')" 
                                                            class="btn-sm btn-secondary" title="Reset Password">
                                                        Reset
                                                    </button>
                                                    
                                                    <button onclick="deleteUser({{ $user->user_id }}, '{{ $user->full_name }}')" 
                                                            class="btn-sm btn-danger" title="Delete User">
                                                        Delete
                                                    </button>
                                                @else
                                                    <span class="text-muted small">Your Account</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="no-data">
                                            <div class="empty-state">
                                                <div class="empty-icon">👤</div>
                                                <h3>No users found</h3>
                                                <p>No users match your current search criteria.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($users->hasPages())
                        <div class="pagination-wrapper">
                            {{ $users->withQueryString()->links() }}
                        </div>
                        @endif
                    </div>
                </div>
@endsection

@push('scripts')
    <!-- Password Reset Modal -->
    <div id="passwordResetModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Password Reset Successful</h3>
                <button onclick="closePasswordModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <div class="password-reset-info">
                    <div class="reset-icon">🔑</div>
                    <p>Password has been reset successfully for <strong id="resetUserName"></strong>.</p>
                    <div class="temp-password-box">
                        <label>Temporary Password:</label>
                        <div class="password-display">
                            <code id="tempPassword"></code>
                            <button onclick="copyPassword()" class="copy-btn" title="Copy to clipboard">📋</button>
                        </div>
                    </div>
                    <p class="reset-note">Please share this temporary password securely with the user and ask them to change it on their next login.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closePasswordModal()" class="btn-primary">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Delete user function
        function deleteUser(userId, userName) {
            if (!confirm(`Are you sure you want to delete the user account for "${userName}"?\n\nThis action cannot be undone and will remove all their access to the system.`)) {
                return;
            }

            fetch(`/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Network error occurred. Please try again.');
                console.error('Error:', error);
            });
        }

        // Reset password function
        function resetPassword(userId, userName) {
            if (!confirm(`Reset password for "${userName}"?\n\nThis will generate a new temporary password that you'll need to share with the user.`)) {
                return;
            }

            fetch(`/admin/users/${userId}/reset-password`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('resetUserName').textContent = userName;
                    document.getElementById('tempPassword').textContent = data.temp_password;
                    document.getElementById('passwordResetModal').style.display = 'flex';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Network error occurred. Please try again.');
                console.error('Error:', error);
            });
        }

        // Close password modal
        function closePasswordModal() {
            document.getElementById('passwordResetModal').style.display = 'none';
        }

        // Copy password to clipboard
        function copyPassword() {
            const password = document.getElementById('tempPassword').textContent;
            navigator.clipboard.writeText(password).then(() => {
                const copyBtn = document.querySelector('.copy-btn');
                copyBtn.textContent = '✅';
                setTimeout(() => {
                    copyBtn.textContent = '📋';
                }, 2000);
            });
        }

        // Export functionality
        document.getElementById('exportUsers').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Build export URL with current filters
            const urlParams = new URLSearchParams(window.location.search);
            const exportUrl = this.href + '?' + urlParams.toString();
            
            window.location.href = exportUrl;
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closePasswordModal();
            }
        });
    </script>

    <style>
        /* User Management Specific Styles */
        .users-stats .stat-card {
            border-left: 4px solid #3b82f6;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .users-table th,
        .users-table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-gray);
        }

        .users-table th {
            background: var(--light-gray);
            font-weight: 600;
            color: var(--maroon-primary);
            font-size: 14px;
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
            background: var(--maroon-primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }

        .user-created {
            font-size: 12px;
            color: var(--text-gray);
        }

        .username {
            font-family: monospace;
            color: var(--text-dark);
            font-weight: 500;
        }

        .email {
            color: var(--text-gray);
            font-size: 13px;
        }

        .role-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-departmenthead { background: #fef3c7; color: #92400e; }
        .role-administrator { background: #dbeafe; color: #1e40af; }
        .role-instructor { background: #dcfce7; color: #166534; }

        .status-badge {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-badge.active { background: #dcfce7; color: #166534; }
        .status-badge.moderate { background: #fef3c7; color: #92400e; }
        .status-badge.inactive { background: #fee2e2; color: #dc2626; }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 6px 8px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-sm.btn-info { background: #3b82f6; color: white; }
        .btn-sm.btn-warning { background: #f59e0b; color: white; }
        .btn-sm.btn-secondary { background: #6b7280; color: white; }
        .btn-sm.btn-danger { background: #dc2626; color: white; }

        .btn-sm:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }

        .search-filter-bar {
            margin-bottom: 20px;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .search-controls {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input-group {
            display: flex;
            flex: 1;
            min-width: 300px;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid var(--border-gray);
            border-radius: 8px 0 0 8px;
            font-size: 14px;
        }

        .search-btn {
            padding: 10px 15px;
            background: var(--maroon-primary);
            color: var(--white);
            border: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-select {
            padding: 10px;
            border: 2px solid var(--border-gray);
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-clear {
            padding: 10px 15px;
            background: var(--text-gray);
            color: var(--white);
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        /* Password Reset Modal Styles */
        .password-reset-info {
            text-align: center;
            padding: 20px;
        }

        .reset-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .temp-password-box {
            margin: 20px 0;
            padding: 15px;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .temp-password-box label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--maroon-primary);
        }

        .password-display {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
        }

        .password-display code {
            background: var(--white);
            padding: 8px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            border: 2px solid var(--border-gray);
        }

        .copy-btn {
            background: var(--maroon-primary);
            color: var(--white);
            border: none;
            padding: 8px 10px;
            border-radius: 6px;
            cursor: pointer;
        }

        .reset-note {
            font-size: 13px;
            color: var(--text-gray);
            margin-top: 15px;
            font-style: italic;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .empty-state h3 {
            margin: 0 0 10px 0;
            color: var(--maroon-primary);
        }

        .empty-state p {
            color: var(--text-gray);
            margin: 0;
        }

        @media (max-width: 768px) {
            .search-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-input-group {
                min-width: auto;
            }
            
            .filter-group {
                flex-wrap: wrap;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
@endpush