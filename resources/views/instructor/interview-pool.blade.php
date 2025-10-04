@extends('layouts.instructor')

@section('title', 'Interview Pool')

@php
    $pageTitle = 'Interview Pool';
    $pageSubtitle = 'Available interviews for immediate assignment';
@endphp

@push('styles')
    <style>
        :root {
            --primary-maroon: #800020;
            --primary-gold: #FFD700;
            --success-green: #059669;
            --warning-orange: #F59E0B;
            --danger-red: #DC2626;
        }

        .pool-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }


        .pool-stats {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            flex: 1;
            min-width: 120px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-maroon);
            margin: 0;
        }

        .stat-label {
            color: #6B7280;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .pool-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }

        .pool-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .section-header {
            padding: 24px;
            background: #F9FAFB;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1F2937;
            margin: 0;
        }

        .section-count {
            background: var(--primary-maroon);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .section-content {
            padding: 24px;
            max-height: 600px;
            overflow-y: auto;
        }

        .interview-card {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }

        .interview-card:hover {
            border-color: var(--primary-maroon);
            box-shadow: 0 4px 16px rgba(128, 0, 32, 0.1);
        }

        .interview-card:last-child {
            margin-bottom: 0;
        }

        .applicant-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .applicant-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 4px;
        }

        .applicant-email {
            color: #6B7280;
            font-size: 0.875rem;
        }

        .priority-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .priority-high {
            background: #FEE2E2;
            color: var(--danger-red);
        }

        .priority-medium {
            background: #FEF3C7;
            color: var(--warning-orange);
        }

        .priority-low {
            background: #DCFCE7;
            color: var(--success-green);
        }

        .interview-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
            font-size: 0.875rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6B7280;
        }

        .meta-icon {
            width: 16px;
            height: 16px;
            opacity: 0.7;
        }

        .interview-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #800020 !important;
            color: white !important;
        }

        .btn-primary:hover {
            background: #5C0016 !important;
            color: white !important;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger-red);
            color: white;
        }

        .btn-danger:hover {
            background: #B91C1C;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-maroon);
            border: 2px solid var(--primary-maroon);
        }

        .btn-outline:hover {
            background: var(--primary-maroon);
            color: white;
        }

        .filters-section {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 32px;
        }

        .filters-form {
            display: flex;
            gap: 16px;
            align-items: end;
        }

        .form-group {
            flex: 1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-maroon);
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #6B7280;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            opacity: 0.5;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: var(--success-green);
        }

        .notification.error {
            background: var(--danger-red);
        }

        @media (max-width: 768px) {
            .pool-sections {
                grid-template-columns: 1fr;
            }
            
            .filters-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .interview-meta {
                grid-template-columns: 1fr;
            }
            
            .pool-stats {
                flex-direction: column;
                gap: 12px;
            }
            
            .stat-card {
                min-width: auto;
                justify-content: space-between;
            }
        }
    </style>
@endpush

@section('content')
<div class="pool-container">

    <!-- Pool Statistics -->
    <div class="pool-stats">
        <div class="stat-card">
            <div class="stat-value">{{ $poolStats['total_available'] }}</div>
            <div class="stat-label">Available Interviews</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $myClaimedInterviews->count() }}</div>
            <div class="stat-label">My Claimed Interviews</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $poolStats['high_priority'] }}</div>
            <div class="stat-label">High Priority</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $poolStats['claimed_by_dh'] + $poolStats['claimed_by_instructors'] }}</div>
            <div class="stat-label">Total Claimed</div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <form method="GET" action="{{ route('instructor.interview-pool.index') }}" class="filters-form">
            <div class="form-group">
                <label class="form-label">Search Applicants</label>
                <input type="text" name="search" class="form-input" 
                       placeholder="Search by name or email..."
                       value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Priority Level</label>
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="high" {{ ($filters['priority'] ?? '') == 'high' ? 'selected' : '' }}>High Priority</option>
                    <option value="medium" {{ ($filters['priority'] ?? '') == 'medium' ? 'selected' : '' }}>Medium Priority</option>
                    <option value="low" {{ ($filters['priority'] ?? '') == 'low' ? 'selected' : '' }}>Low Priority</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>

    <!-- Pool Sections -->
    <div class="pool-sections">
        <!-- Available Interviews -->
        <div class="pool-section">
            <div class="section-header">
                <h2 class="section-title">Available Interviews</h2>
                <span class="section-count" id="available-count">{{ $availableInterviews->count() }}</span>
            </div>
            <div class="section-content" id="available-interviews">
                @forelse($availableInterviews as $interview)
                    <div class="interview-card" data-interview-id="{{ $interview->interview_id }}">
                        <div class="applicant-info">
                            <div>
                                <div class="applicant-name">{{ $interview->applicant->first_name }} {{ $interview->applicant->last_name }}</div>
                                <div class="applicant-email">{{ $interview->applicant->email }}</div>
                            </div>
                            <span class="priority-badge priority-{{ $interview->priority_level }}">
                                {{ ucfirst($interview->priority_level) }} Priority
                            </span>
                        </div>
                        
                        <div class="interview-meta">
                            <div class="meta-item">
                                <span class="meta-icon">📊</span>
                                <span>Exam Score: {{ $interview->applicant->exam_percentage ?? 'N/A' }}%</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon">📅</span>
                                <span>Added: {{ $interview->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="interview-actions">
                            <button onclick="claimInterview({{ $interview->interview_id }})" 
                                    class="btn btn-primary claim-btn">
                                <span class="btn-text">Claim Interview</span>
                                <span class="loading-spinner" style="display: none;"></span>
                            </button>
                            <button onclick="viewApplicant({{ $interview->applicant->applicant_id }})" 
                                    class="btn btn-outline">
                                View Details
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <h3>No Available Interviews</h3>
                        <p>All interviews have been claimed or there are no interviews in the pool at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- My Claimed Interviews -->
        <div class="pool-section">
            <div class="section-header">
                <h2 class="section-title">My Claimed Interviews</h2>
                <span class="section-count" id="claimed-count">{{ $myClaimedInterviews->count() }}</span>
            </div>
            <div class="section-content" id="claimed-interviews">
                @forelse($myClaimedInterviews as $interview)
                    <div class="interview-card" data-interview-id="{{ $interview->interview_id }}">
                        <div class="applicant-info">
                            <div>
                                <div class="applicant-name">{{ $interview->applicant->first_name }} {{ $interview->applicant->last_name }}</div>
                                <div class="applicant-email">{{ $interview->applicant->email }}</div>
                            </div>
                            <span class="priority-badge priority-{{ $interview->priority_level }}">
                                {{ ucfirst($interview->priority_level) }} Priority
                            </span>
                        </div>
                        
                        <div class="interview-meta">
                            <div class="meta-item">
                                <span class="meta-icon">📊</span>
                                <span>Exam Score: {{ $interview->applicant->exam_percentage ?? 'N/A' }}%</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon">⏰</span>
                                <span>Claimed: {{ $interview->time_since_claimed }}</span>
                            </div>
                        </div>

                        <div class="interview-actions">
                            <a href="{{ route('instructor.interview.show', $interview->applicant) }}" 
                               class="btn btn-primary">
                                Conduct Interview
                            </a>
                            <button onclick="releaseInterview({{ $interview->interview_id }})" 
                                    class="btn btn-danger release-btn">
                                <span class="btn-text">Release</span>
                                <span class="loading-spinner" style="display: none;"></span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">🎯</div>
                        <h3>No Claimed Interviews</h3>
                        <p>You haven't claimed any interviews yet. Pick up available interviews from the left panel.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Notification Container -->
<div id="notification-container"></div>
@endsection

@push('scripts')
<script>
    // Auto-refresh data every 30 seconds
    setInterval(refreshPoolData, 30000);

    // Claim interview function
    function claimInterview(interviewId) {
        const button = document.querySelector(`[data-interview-id="${interviewId}"] .claim-btn`);
        const buttonText = button.querySelector('.btn-text');
        const spinner = button.querySelector('.loading-spinner');
        
        // Show loading state
        button.disabled = true;
        buttonText.style.display = 'none';
        spinner.style.display = 'inline-block';
        
        fetch(`/instructor/interview-pool/${interviewId}/claim`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                refreshPoolData();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to claim interview. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button state
            button.disabled = false;
            buttonText.style.display = 'inline';
            spinner.style.display = 'none';
        });
    }

    // Release interview function
    function releaseInterview(interviewId) {
        if (!confirm('Are you sure you want to release this interview back to the pool?')) {
            return;
        }
        
        const button = document.querySelector(`[data-interview-id="${interviewId}"] .release-btn`);
        const buttonText = button.querySelector('.btn-text');
        const spinner = button.querySelector('.loading-spinner');
        
        // Show loading state
        button.disabled = true;
        buttonText.style.display = 'none';
        spinner.style.display = 'inline-block';
        
        fetch(`/instructor/interview-pool/${interviewId}/release`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                refreshPoolData();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to release interview. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button state
            button.disabled = false;
            buttonText.style.display = 'inline';
            spinner.style.display = 'none';
        });
    }

    // View applicant details
    function viewApplicant(applicantId) {
        window.open(`/instructor/applicants/${applicantId}`, '_blank');
    }

    // Refresh pool data
    function refreshPoolData() {
        // Get current filters
        const urlParams = new URLSearchParams(window.location.search);
        const filters = {
            priority: urlParams.get('priority') || '',
            search: urlParams.get('search') || ''
        };
        
        // Refresh available interviews
        fetch(`{{ route('instructor.interview-pool.available') }}?${new URLSearchParams(filters)}`)
            .then(response => response.json())
            .then(data => {
                updateAvailableInterviews(data.interviews);
                document.getElementById('available-count').textContent = data.count;
            })
            .catch(error => console.error('Error refreshing available interviews:', error));
        
        // Refresh claimed interviews
        fetch(`{{ route('instructor.interview-pool.my-claimed') }}`)
            .then(response => response.json())
            .then(data => {
                updateClaimedInterviews(data.interviews);
                document.getElementById('claimed-count').textContent = data.count;
            })
            .catch(error => console.error('Error refreshing claimed interviews:', error));
    }

    // Update available interviews UI
    function updateAvailableInterviews(interviews) {
        const container = document.getElementById('available-interviews');
        
        if (interviews.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <h3>No Available Interviews</h3>
                    <p>All interviews have been claimed or there are no interviews in the pool at the moment.</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = interviews.map(interview => `
            <div class="interview-card" data-interview-id="${interview.interview_id}">
                <div class="applicant-info">
                    <div>
                        <div class="applicant-name">${interview.applicant.first_name} ${interview.applicant.last_name}</div>
                        <div class="applicant-email">${interview.applicant.email}</div>
                    </div>
                    <span class="priority-badge priority-${interview.priority_level}">
                        ${interview.priority_level.charAt(0).toUpperCase() + interview.priority_level.slice(1)} Priority
                    </span>
                </div>
                
                <div class="interview-meta">
                    <div class="meta-item">
                        <span class="meta-icon">📊</span>
                        <span>Exam Score: ${interview.applicant.exam_percentage || 'N/A'}%</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-icon">📅</span>
                        <span>Added: ${new Date(interview.created_at).toLocaleDateString()}</span>
                    </div>
                </div>

                <div class="interview-actions">
                    <button onclick="claimInterview(${interview.interview_id})" class="btn btn-primary claim-btn">
                        <span class="btn-text">Claim Interview</span>
                        <span class="loading-spinner" style="display: none;"></span>
                    </button>
                    <button onclick="viewApplicant(${interview.applicant.applicant_id})" class="btn btn-outline">
                        View Details
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Update claimed interviews UI
    function updateClaimedInterviews(interviews) {
        const container = document.getElementById('claimed-interviews');
        
        if (interviews.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">🎯</div>
                    <h3>No Claimed Interviews</h3>
                    <p>You haven't claimed any interviews yet. Pick up available interviews from the left panel.</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = interviews.map(interview => `
            <div class="interview-card" data-interview-id="${interview.interview_id}">
                <div class="applicant-info">
                    <div>
                        <div class="applicant-name">${interview.applicant.first_name} ${interview.applicant.last_name}</div>
                        <div class="applicant-email">${interview.applicant.email}</div>
                    </div>
                    <span class="priority-badge priority-${interview.priority_level}">
                        ${interview.priority_level.charAt(0).toUpperCase() + interview.priority_level.slice(1)} Priority
                    </span>
                </div>
                
                <div class="interview-meta">
                    <div class="meta-item">
                        <span class="meta-icon">📊</span>
                        <span>Exam Score: ${interview.applicant.exam_percentage || 'N/A'}%</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-icon">⏰</span>
                        <span>Claimed: ${interview.time_since_claimed || 'Just now'}</span>
                    </div>
                </div>

                <div class="interview-actions">
                    <a href="/instructor/interview/applicants/${interview.applicant.applicant_id}" class="btn btn-primary">
                        Conduct Interview
                    </a>
                    <button onclick="releaseInterview(${interview.interview_id})" class="btn btn-danger release-btn">
                        <span class="btn-text">Release</span>
                        <span class="loading-spinner" style="display: none;"></span>
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Show notification
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.getElementById('notification-container').appendChild(notification);
        
        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);
        
        // Hide and remove notification
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Interview Pool initialized');
    });
</script>
@endpush
