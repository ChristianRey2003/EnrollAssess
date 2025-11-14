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

    /* Override main-content padding for this page */
    .main-content {
        padding: 20px !important;
    }

    .user-detail-container {
        padding: 0;
        max-width: 1000px;
        width: 100%;
        margin: 0 auto;
    }

    .user-profile-card {
        background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
        border-radius: 8px;
        padding: 20px;
        color: var(--white);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .user-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-gold), #DAA520);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-maroon);
        font-weight: 700;
        font-size: 32px;
        border: 3px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        flex-shrink: 0;
    }

    .user-avatar-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .user-profile-info {
        flex: 1;
    }

    .user-profile-info h1 {
        margin: 0 0 6px 0;
        font-size: 20px;
        font-weight: 700;
    }

    .user-profile-info .user-email {
        margin: 0 0 10px 0;
        font-size: 13px;
        opacity: 0.9;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.2);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .info-card {
        background: var(--white);
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
    }

    .info-card h3 {
        color: var(--primary-maroon);
        font-size: 14px;
        font-weight: 700;
        margin: 0 0 12px 0;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border-gray);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid var(--border-gray);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: var(--text-gray);
        font-size: 12px;
    }

    .info-value {
        color: var(--text-dark);
        font-weight: 500;
        font-size: 12px;
        text-align: right;
    }

    .stats-card {
        background: var(--white);
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-gray);
        margin-bottom: 15px;
    }

    .stats-card h3 {
        color: var(--primary-maroon);
        font-size: 14px;
        font-weight: 700;
        margin: 0 0 12px 0;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border-gray);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
    }

    .stat-item {
        text-align: center;
        padding: 10px;
        background: var(--light-gray);
        border-radius: 6px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary-maroon);
        margin: 0 0 4px 0;
    }

    .stat-label {
        font-size: 11px;
        color: var(--text-gray);
        margin: 0;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
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
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 10px;
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
            padding: 0;
        }

        .user-profile-card {
            flex-direction: column;
            text-align: center;
            padding: 15px;
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
            @if($user->profile_picture_url)
                <img src="{{ $user->profile_picture_url }}" alt="{{ $user->full_name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            @else
                {{ $user->initials }}
            @endif
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
                    Instructor
                @endif
            </span>
        </div>
    </div>

    <!-- Account Information -->
    <div class="info-grid">
        <div class="info-card">
            <h3>Account Information</h3>
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
            <h3>Activity Timeline</h3>
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
        <h3>Interview Statistics</h3>
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

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            Cancel
        </a>
        @if($user->user_id === auth()->id())
            <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">
                My Profile
            </a>
        @else
            <a href="{{ route('admin.users.edit', $user->user_id) }}" class="btn btn-primary">
                Edit User
            </a>
            <button type="button" class="btn btn-warning" onclick="resetPassword()">
                Reset Password
            </button>
        @endif
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

