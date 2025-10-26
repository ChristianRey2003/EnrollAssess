@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Computer Studies Department';
@endphp

@section('content')
                <!-- Enable real-time updates -->
                <div data-dashboard-live></div>
                
                <!-- Debug info -->
                <div id="debug-info" style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace; font-size: 12px;">
                    <div>Debug: Dashboard loaded</div>
                    <div id="echo-status">Echo status: Loading...</div>
                    <div id="pusher-status">Pusher status: Loading...</div>
                    <div id="last-update">Last update: Never</div>
                    <div id="env-vars">Environment: Loading...</div>
                    <div id="js-errors">JavaScript errors: None</div>
                </div>

                <!-- Statistics Cards (Live Updated) -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value" data-stat="total">{{ $stats['total'] ?? $stats['total_applicants'] ?? 0 }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value" data-stat="exam_completed">{{ $stats['exam_completed'] ?? 0 }}</div>
                        <div class="stat-label">Exams Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value" data-stat="interview_scheduled">{{ $stats['interview_scheduled'] ?? $stats['interviews_scheduled'] ?? 0 }}</div>
                        <div class="stat-label">Interviews Scheduled</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true">⏳</div>
                        <div class="stat-value" data-stat="pending">{{ $stats['pending'] ?? $stats['pending_reviews'] ?? 0 }}</div>
                        <div class="stat-label">Pending Reviews</div>
                    </div>
                </div>

                <!-- Last Updated Timestamp -->
                <div style="text-align: right; padding: 10px 0; font-size: 12px; color: #6B7280;">
                    <span data-last-updated>Last updated: {{ now()->format('M d, Y g:i A') }}</span>
                </div>

                <!-- Recent Activity -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Applicant Activity</h2>
                        <a href="{{ route('admin.applicants.index') }}" class="section-action">View All</a>
                    </div>
                    <div class="section-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Applicant Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_applicants ?? [] as $applicant)
                                <tr>
                                    <td>{{ $applicant->full_name }}</td>
                                    <td>{{ $applicant->email_address }}</td>
                                    <td>
                                        <span class="status-badge status-{{ strtolower($applicant->status) }}">
                                            {{ $applicant->status }}
                                        </span>
                                    </td>
                                    <td>{{ $applicant->score ? $applicant->score . '%' : '--' }}</td>
                                    <td>{{ $applicant->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" 
                                               class="action-btn action-btn-view"
                                               aria-label="View details for {{ $applicant->full_name }}">
                                                <span aria-hidden="true">️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <!-- Demo data when no applicants exist -->
                                <tr>
                                    <td>John Doe</td>
                                    <td>john.doe@email.com</td>
                                    <td><span class="status-badge status-completed">Completed</span></td>
                                    <td>85%</td>
                                    <td>{{ now()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-view" 
                                               aria-label="View details">
                                                <span aria-hidden="true">️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jane Smith</td>
                                    <td>jane.smith@email.com</td>
                                    <td><span class="status-badge status-in-progress">In Progress</span></td>
                                    <td>--</td>
                                    <td>{{ now()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-view" 
                                               aria-label="View details">
                                                <span aria-hidden="true">️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Mike Johnson</td>
                                    <td>mike.j@email.com</td>
                                    <td><span class="status-badge status-pending">Pending</span></td>
                                    <td>--</td>
                                    <td>{{ now()->subDay()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-view" 
                                               aria-label="View details">
                                                <span aria-hidden="true">️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Sarah Williams</td>
                                    <td>sarah.w@email.com</td>
                                    <td><span class="status-badge status-completed">Completed</span></td>
                                    <td>92%</td>
                                    <td>{{ now()->subDay()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-view" 
                                               aria-label="View details">
                                                <span aria-hidden="true">️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>David Brown</td>
                                    <td>david.brown@email.com</td>
                                    <td><span class="status-badge status-in-progress">In Progress</span></td>
                                    <td>--</td>
                                    <td>{{ now()->subDays(2)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="action-btn action-btn-view" 
                                               aria-label="View details">
                                                <span aria-hidden="true">️</span>
                                                <span class="sr-only">View</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Quick Actions</h2>
                    </div>
                    <div class="section-content" style="padding: 24px 30px;">
                        <div class="quick-actions-grid">
                            <a href="{{ route('admin.questions.create') }}" class="quick-action-card">
                                <div class="quick-action-icon"></div>
                                <div class="quick-action-title">Add New Question</div>
                                <div class="quick-action-desc">Create a new exam question</div>
                            </a>
                            <a href="{{ route('admin.applicants.index') }}" class="quick-action-card">
                                <div class="quick-action-icon"></div>
                                <div class="quick-action-title">Generate Report</div>
                                <div class="quick-action-desc">Export applicant data</div>
                            </a>
                            <a href="{{ route('admin.settings') }}" class="quick-action-card">
                                <div class="quick-action-icon">️</div>
                                <div class="quick-action-title">System Settings</div>
                                <div class="quick-action-desc">Configure exam parameters</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
// Enhanced debug script for real-time functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Dashboard page loaded - Starting debug...');
    
    // Check environment variables (simplified)
    try {
        const envVars = {
            broadcastDriver: 'pusher', // Hardcoded since we know it's working
            pusherKey: 'f11dc48551a0d1842558', // From your .env
            pusherCluster: 'ap1' // From your .env
        };
        
        document.getElementById('env-vars').textContent = `Environment: ${JSON.stringify(envVars)}`;
        console.log('Environment variables:', envVars);
    } catch (error) {
        document.getElementById('env-vars').textContent = 'Environment: Error - ' + error.message;
        console.error('Environment check error:', error);
    }
    
    // Check for JavaScript errors
    window.addEventListener('error', function(e) {
        document.getElementById('js-errors').textContent = `JavaScript errors: ${e.message}`;
        console.error('JavaScript error:', e);
    });
    
    // Check if Echo is available (simplified check)
    setTimeout(() => {
        try {
            if (typeof window.Echo !== 'undefined' && window.Echo !== null) {
                document.getElementById('echo-status').textContent = 'Echo status: ✅ Loaded';
                console.log('✅ Echo is available');
            } else {
                document.getElementById('echo-status').textContent = 'Echo status: ❌ Not loaded';
                console.log('❌ Echo is not available');
            }
        } catch (error) {
            document.getElementById('echo-status').textContent = 'Echo status: ❌ Error';
            console.error('Echo check error:', error);
        }
    }, 2000);
    
    // Check if Pusher is available (simplified check)
    setTimeout(() => {
        try {
            if (typeof window.Pusher !== 'undefined') {
                document.getElementById('pusher-status').textContent = 'Pusher status: ✅ Loaded';
                console.log('✅ Pusher is available');
            } else {
                document.getElementById('pusher-status').textContent = 'Pusher status: ❌ Not loaded';
                console.log('❌ Pusher is not available');
            }
        } catch (error) {
            document.getElementById('pusher-status').textContent = 'Pusher status: ❌ Error';
            console.error('Pusher check error:', error);
        }
    }, 2000);
    
    // Test manual stats refresh
    setTimeout(() => {
        console.log('🔄 Testing API endpoint...');
        fetch('/admin/api/dashboard/stats')
            .then(response => {
                console.log('API response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('✅ Stats API response:', data);
                document.getElementById('last-update').textContent = 'Last update: ' + new Date().toLocaleTimeString();
                
                // Update stats manually
                if (data.success && data.stats) {
                    Object.keys(data.stats).forEach(stat => {
                        const element = document.querySelector(`[data-stat="${stat}"]`);
                        if (element) {
                            element.textContent = data.stats[stat];
                        }
                    });
                }
            })
            .catch(error => {
                console.error('❌ Stats API error:', error);
                document.getElementById('last-update').textContent = 'Last update: Error - ' + error.message;
            });
    }, 2000);
    
    // Test if dashboard.js is loading
    setTimeout(() => {
        if (typeof window.DashboardManager !== 'undefined') {
            console.log('✅ DashboardManager is available');
        } else {
            console.log('❌ DashboardManager is not available');
        }
    }, 3000);
    
    // Listen for Echo connection events
    setTimeout(() => {
        if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
            const pusher = window.Echo.connector.pusher;
            
            pusher.connection.bind('connected', () => {
                document.getElementById('echo-status').textContent = 'Echo status: ✅ Connected';
                document.getElementById('pusher-status').textContent = 'Pusher status: ✅ Connected';
                document.getElementById('last-update').textContent = 'Last update: ' + new Date().toLocaleTimeString();
                console.log('🔗 Debug: Echo connection confirmed');
            });
            
            pusher.connection.bind('disconnected', () => {
                document.getElementById('echo-status').textContent = 'Echo status: ⚠️ Disconnected';
                document.getElementById('pusher-status').textContent = 'Pusher status: ⚠️ Disconnected';
                console.warn('⚠️ Debug: Echo disconnected');
            });
            
            pusher.connection.bind('error', (err) => {
                document.getElementById('echo-status').textContent = 'Echo status: ❌ Error';
                document.getElementById('pusher-status').textContent = 'Pusher status: ❌ Error';
                console.error('❌ Debug: Echo connection error:', err);
            });
            
            // Check current connection state
            const state = pusher.connection.state;
            console.log('📡 Current Pusher connection state:', state);
            if (state === 'connected') {
                document.getElementById('echo-status').textContent = 'Echo status: ✅ Connected';
                document.getElementById('pusher-status').textContent = 'Pusher status: ✅ Connected';
            }
        } else {
            console.warn('⚠️ Echo not available yet, will retry...');
            // Retry after more time
            setTimeout(() => {
                if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
                    const state = window.Echo.connector.pusher.connection.state;
                    if (state === 'connected') {
                        document.getElementById('echo-status').textContent = 'Echo status: ✅ Connected';
                        document.getElementById('pusher-status').textContent = 'Pusher status: ✅ Connected';
                    }
                }
            }, 3000);
        }
    }, 2000);
    
    // Listen for statistics update events to update timestamp
    setTimeout(() => {
        if (window.Echo) {
            window.Echo.channel('dashboard')
                .listen('.statistics.updated', (data) => {
                    document.getElementById('last-update').textContent = 'Last update: ' + new Date().toLocaleTimeString() + ' (Real-time)';
                    console.log('⚡ Real-time stats update received:', data);
                });
        }
    }, 3000);
});
</script>
@endsection

@push('styles')
    <style>
        /* Real-time Update Animations */
        .stat-updated {
            animation: pulse 0.6s ease-in-out;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        }

        .toast {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
            border-left: 4px solid var(--maroon-primary);
        }

        .toast-info {
            border-left-color: rgb(59, 130, 246);
        }

        .toast-success {
            border-left-color: rgb(34, 197, 94);
        }

        .toast-warning {
            border-left-color: rgb(245, 158, 11);
        }

        .toast-error {
            border-left-color: rgb(239, 68, 68);
        }

        .toast-hiding {
            animation: slideOut 0.3s ease-in forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .toast-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            color: var(--maroon-primary);
            margin-bottom: 4px;
        }

        .toast-message {
            font-size: 14px;
            color: var(--text-gray);
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-gray);
            cursor: pointer;
            line-height: 1;
            padding: 0;
            transition: var(--transition);
        }

        .toast-close:hover {
            color: var(--maroon-primary);
        }

        /* Activity Feed Styles */
        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-bottom: 1px solid var(--border-gray);
            transition: var(--transition);
        }

        .activity-item:hover {
            background: var(--light-gray);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-new {
            animation: highlightNew 0.6s ease-out;
        }

        @keyframes highlightNew {
            0% {
                background: rgba(255, 215, 0, 0.3);
            }
            100% {
                background: transparent;
            }
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-title {
            font-size: 14px;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .activity-time {
            font-size: 12px;
            color: var(--text-gray);
        }

        .no-activity {
            text-align: center;
            padding: 40px;
            color: var(--text-gray);
        }

        @media (max-width: 768px) {
            .toast-container {
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }
    </style>
@endpush