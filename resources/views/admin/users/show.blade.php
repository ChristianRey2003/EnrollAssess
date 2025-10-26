@extends('layouts.admin')

@section('title', 'User Details')

@php
    $pageTitle = $user->full_name;
    $pageSubtitle = 'User account information and activity';
@endphp

@push('styles')
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
        --info-blue: #3B82F6;
        --transition: all 0.3s ease;
    }

    .user-detail-container {
        padding: 30px;
        max-width: 1000px;
        margin: 0 auto;
    }

    .user-profile-card {
        background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
        border-radius: 12px;
        padding: 40px;
        color: var(--white);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .user-avatar-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-gold), #DAA520);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-maroon);
        font-weight: 700;
        font-size: 48px;
        border: 4px solid rgba(255, 255, 255, 0.2);
    }

    .user-profile-info {
        flex: 1;
    }

    .user-profile-info h1 {
        margin: 0 0 10px 0;
        font-size: 32px;
        font-weight: 700;
    }

    .user-profile-info .user-email {
        margin: 0 0 15px 0;
        font-size: 16px;
        opacity: 0.9;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.2);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-card {
        background: var(--white);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
    }

    .info-card h3 {
        color: var(--primary-maroon);
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--border-gray);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-gray);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: var(--text-gray);
        font-size: 14px;
    }

    .info-value {
        color: var(--text-dark);
        font-weight: 500;
        font-size: 14px;
        text-align: right;
    }

    .stats-card {
        background: var(--white);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
        margin-bottom: 20px;
    }

    .stats-card h3 {
        color: var(--primary-maroon);
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--border-gray);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }

    .stat-item {
        text-align: center;
        padding: 15px;
        background: var(--light-gray);
        border-radius: 8px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--primary-maroon);
        margin: 0 0 5px 0;
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-gray);
        margin: 0;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary-maroon);
        color: var(--white);
    }

    .btn-primary:hover {
        background: var(--dark-maroon);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(128, 0, 32, 0.3);
    }

    .btn-secondary {
        background: var(--light-gray);
        color: var(--text-dark);
        border: 2px solid var(--border-gray);
    }

    .btn-secondary:hover {
        background: var(--border-gray);
    }

    .btn-warning {
        background: var(--warning-orange);
        color: var(--white);
    }

    .btn-warning:hover {
        background: #B45309;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(217, 119, 6, 0.3);
    }

    .badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-active {
        background: rgba(5, 150, 105, 0.1);
        color: var(--success-green);
    }

    .badge-inactive {
        background: rgba(107, 114, 128, 0.1);
        color: var(--text-gray);
    }

    @media (max-width: 768px) {
        .user-detail-container {
            padding: 15px;
        }

        .user-profile-card {
            flex-direction: column;
            text-align: center;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="user-detail-container">
    <!-- User Profile Card -->
    <div class="user-profile-card">
        <div class="user-avatar-large">
            {{ strtoupper(substr($user->full_name, 0, 2)) }}
        </div>
        <div class="user-profile-info">
            <h1>{{ $user->full_name }}</h1>
            <p class="user-email"> {{ $user->email }}</p>
            <span class="role-badge">
                @if($user->role === 'department-head')
                     Department Head
                @elseif($user->role === 'administrator')
                     Administrator
                @else
                    🧑‍ Instructor
                @endif
            </span>
        </div>
    </div>

    <!-- Account Information -->
    <div class="info-grid">
        <div class="info-card">
            <h3> Account Information</h3>
            <div class="info-row">
                <span class="info-label">Username</span>
                <span class="info-value">{{ $user->username }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">User ID</span>
                <span class="info-value">#{{ $user->user_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Account Status</span>
                <span class="info-value">
                    @if($userStats['days_since_login'] < 30)
                        <span class="badge badge-active">Active</span>
                    @else
                        <span class="badge badge-inactive">Inactive</span>
                    @endif
                </span>
            </div>
        </div>

        <div class="info-card">
            <h3> Activity Timeline</h3>
            <div class="info-row">
                <span class="info-label">Created</span>
                <span class="info-value">{{ $userStats['created_date']->format('M d, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Last Login</span>
                <span class="info-value">{{ $userStats['last_login']->format('M d, Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Days Since Login</span>
                <span class="info-value">{{ $userStats['days_since_login'] }} days ago</span>
            </div>
        </div>
    </div>

    <!-- Activity Statistics (for instructors) -->
    @if($user->role === 'instructor' && isset($relatedData['assigned_interviews']))
    <div class="stats-card">
        <h3> Interview Statistics</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <h4 class="stat-value">{{ $relatedData['assigned_interviews'] ?? 0 }}</h4>
                <p class="stat-label">Interviews Assigned</p>
            </div>
            <div class="stat-item">
                <h4 class="stat-value">{{ $relatedData['completed_interviews'] ?? 0 }}</h4>
                <p class="stat-label">Interviews Completed</p>
            </div>
            <div class="stat-item">
                <h4 class="stat-value">
                    @php
                        $total = $relatedData['assigned_interviews'] ?? 0;
                        $completed = $relatedData['completed_interviews'] ?? 0;
                        $rate = $total > 0 ? round(($completed / $total) * 100) : 0;
                    @endphp
                    {{ $rate }}%
                </h4>
                <p class="stat-label">Completion Rate</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Role Permissions -->
    <div class="stats-card">
        <h3> Role Permissions</h3>
        <ul style="padding-left: 20px; color: var(--text-gray); margin: 0;">
            @if($user->role === 'department-head')
                <li>Full system administration</li>
                <li>Manage all users and roles</li>
                <li>Generate final reports</li>
                <li>Configure system settings</li>
                <li>Access all applicant data</li>
                <li>Override exam and interview scores</li>
            @elseif($user->role === 'administrator')
                <li>Manage exam questions</li>
                <li>View and manage applicants</li>
                <li>Generate access codes</li>
                <li>Schedule interviews</li>
                <li>Update applicant status</li>
                <li>Export applicant data</li>
            @else
                <li>View assigned applicants</li>
                <li>Conduct interviews</li>
                <li>Submit interview scores</li>
                <li>Add interview notes</li>
                <li>View exam results</li>
                <li>Generate basic reports</li>
            @endif
        </ul>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <span>←</span> Back to List
        </a>
        <a href="{{ route('admin.users.edit', $user->user_id) }}" class="btn btn-primary">
            <span>️</span> Edit User
        </a>
        <button type="button" class="btn btn-warning" onclick="resetPassword()">
            <span></span> Reset Password
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function resetPassword() {
        if (!confirm('Are you sure you want to reset this user\'s password? A temporary password will be generated.')) {
            return;
        }

        fetch('{{ route('admin.users.reset-password', $user->user_id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Password reset successful!\n\nTemporary Password: ${data.temp_password}\n\nPlease provide this to the user and ask them to change it after login.`);
            } else {
                alert('Failed to reset password: ' + data.message);
            }
        })
        .catch(error => {
            alert('Failed to reset password. Please try again.');
            console.error('Error:', error);
        });
    }
</script>
@endpush

