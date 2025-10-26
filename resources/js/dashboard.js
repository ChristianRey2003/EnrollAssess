/**
 * Real-time Dashboard Updates
 * Handles live statistics, notifications, and activity feed
 */

import Echo from './echo';
import { Chart, registerables } from 'chart.js';

// Register Chart.js components
Chart.register(...registerables);

class DashboardManager {
    constructor() {
        this.updateInterval = 30000; // 30 seconds fallback polling
        this.charts = {};
        this.useBroadcasting = typeof Echo !== 'undefined' && Echo !== null;
        
        this.init();
    }

    init() {
        console.log('Initializing Dashboard Manager...');
        console.log('Broadcasting enabled:', this.useBroadcasting);

        // Initialize real-time updates
        if (this.useBroadcasting) {
            this.initializeBroadcasting();
        } else {
            this.initializePolling();
        }

        // Initialize charts if on dashboard
        if (document.querySelector('[data-chart]')) {
            this.initializeCharts();
        }

        // Initialize notification listener
        this.initializeNotifications();
    }

    /**
     * Initialize real-time broadcasting with Pusher
     */
    initializeBroadcasting() {
        console.log('Setting up real-time broadcasting...');

        // Listen to dashboard channel
        Echo.channel('dashboard')
            .listen('.applicant.created', (data) => {
                console.log('New applicant:', data);
                this.handleApplicantCreated(data);
                this.refreshStats();
                this.showToast('New Applicant', data.message, 'info');
            })
            .listen('.exam.completed', (data) => {
                console.log('Exam completed:', data);
                this.handleExamCompleted(data);
                this.refreshStats();
                this.showToast('Exam Completed', data.message, 'success');
            })
            .listen('.interview.scheduled', (data) => {
                console.log('Interview scheduled:', data);
                this.handleInterviewScheduled(data);
                this.refreshStats();
                this.showToast('Interview Scheduled', data.message, 'info');
            })
            .listen('.interview.completed', (data) => {
                console.log('Interview completed:', data);
                this.handleInterviewCompleted(data);
                this.refreshStats();
                this.showToast('Interview Completed', data.message, 'success');
            })
            .listen('.statistics.updated', (data) => {
                console.log('Statistics updated:', data);
                this.updateStatistics(data.stats);
            });

        // Still poll stats every 30 seconds as backup
        setInterval(() => this.refreshStats(), this.updateInterval);
    }

    /**
     * Initialize polling fallback (if broadcasting not available)
     */
    initializePolling() {
        console.log('Using polling for updates (every 30 seconds)...');
        
        // Initial load
        this.refreshStats();
        this.loadRecentActivity();

        // Poll every 30 seconds
        setInterval(() => {
            this.refreshStats();
            this.loadRecentActivity();
        }, this.updateInterval);
    }

    /**
     * Refresh dashboard statistics
     */
    async refreshStats() {
        try {
            const response = await fetch('/admin/api/dashboard/stats', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to fetch stats');
            }

            const data = await response.json();
            
            if (data.success) {
                this.updateStatistics(data.stats);
                this.updateTimestamp(data.timestamp);
            }
        } catch (error) {
            console.error('Error refreshing stats:', error);
        }
    }

    /**
     * Update statistics display
     */
    updateStatistics(stats) {
        console.log('📊 Updating statistics:', stats);
        
        // Update stat cards with animation
        Object.keys(stats).forEach(key => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                const currentValue = parseInt(element.textContent) || 0;
                const newValue = stats[key];
                
                if (currentValue !== newValue) {
                    this.animateValue(element, currentValue, newValue, 500);
                }
            }
        });
        
        // Update debug timestamp
        const debugUpdate = document.getElementById('last-update');
        if (debugUpdate) {
            debugUpdate.textContent = 'Last update: ' + new Date().toLocaleTimeString() + ' ⚡';
        }
    }

    /**
     * Animate number change
     */
    animateValue(element, start, end, duration) {
        const range = end - start;
        const increment = range / (duration / 16); // 60fps
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                element.textContent = Math.round(end);
                clearInterval(timer);
            } else {
                element.textContent = Math.round(current);
            }
        }, 16);

        // Add pulse animation
        element.classList.add('stat-updated');
        setTimeout(() => element.classList.remove('stat-updated'), 600);
    }

    /**
     * Update last updated timestamp
     */
    updateTimestamp(timestamp) {
        const element = document.querySelector('[data-last-updated]');
        if (element) {
            element.textContent = `Last updated: ${timestamp}`;
        }
    }

    /**
     * Load recent activity feed
     */
    async loadRecentActivity(limit = 10) {
        try {
            const response = await fetch(`/admin/api/dashboard/activity?limit=${limit}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to fetch activity');
            }

            const data = await response.json();
            
            if (data.success) {
                this.renderActivityFeed(data.activities);
            }
        } catch (error) {
            console.error('Error loading activity:', error);
        }
    }

    /**
     * Render activity feed
     */
    renderActivityFeed(activities) {
        const container = document.querySelector('[data-activity-feed]');
        if (!container) return;

        if (activities.length === 0) {
            container.innerHTML = '<p class="no-activity">No recent activity</p>';
            return;
        }

        container.innerHTML = activities.map(activity => {
            const icon = this.getActivityIcon(activity.type);
            const color = this.getActivityColor(activity.type);
            
            return `
                <div class="activity-item" data-activity-id="${activity.id}">
                    <div class="activity-icon" style="background-color: ${color}">
                        ${icon}
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">${this.getActivityTitle(activity)}</div>
                        <div class="activity-time">${activity.time_ago}</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    getActivityIcon(type) {
        const icons = {
            'applicant': '👤',
            'interview': '📅',
            'exam': '📝',
        };
        return icons[type] || '•';
    }

    getActivityColor(type) {
        const colors = {
            'applicant': 'rgba(128, 0, 32, 0.1)',
            'interview': 'rgba(255, 215, 0, 0.3)',
            'exam': 'rgba(59, 130, 246, 0.1)',
        };
        return colors[type] || 'rgba(107, 114, 128, 0.1)';
    }

    getActivityTitle(activity) {
        if (activity.type === 'applicant') {
            return `<strong>${activity.name}</strong> registered`;
        } else if (activity.type === 'interview') {
            return `Interview ${activity.status} for <strong>${activity.applicant_name}</strong>`;
        }
        return activity.name;
    }

    /**
     * Handle new applicant event
     */
    handleApplicantCreated(data) {
        // Add to activity feed
        this.prependActivity({
            id: data.id,
            type: 'applicant',
            name: data.name,
            time_ago: 'Just now',
        });
    }

    /**
     * Handle exam completed event
     */
    handleExamCompleted(data) {
        this.prependActivity({
            id: data.id,
            type: 'exam',
            name: data.name,
            score: data.score,
            time_ago: 'Just now',
        });
    }

    /**
     * Handle interview scheduled event
     */
    handleInterviewScheduled(data) {
        this.prependActivity({
            id: data.id,
            type: 'interview',
            applicant_name: data.applicant_name,
            status: 'scheduled',
            time_ago: 'Just now',
        });
    }

    /**
     * Handle interview completed event
     */
    handleInterviewCompleted(data) {
        this.prependActivity({
            id: data.id,
            type: 'interview',
            applicant_name: data.applicant_name,
            status: 'completed',
            time_ago: 'Just now',
        });
    }

    /**
     * Prepend activity to feed
     */
    prependActivity(activity) {
        const container = document.querySelector('[data-activity-feed]');
        if (!container) return;

        const icon = this.getActivityIcon(activity.type);
        const color = this.getActivityColor(activity.type);
        
        const activityHTML = `
            <div class="activity-item activity-new" data-activity-id="${activity.id}">
                <div class="activity-icon" style="background-color: ${color}">
                    ${icon}
                </div>
                <div class="activity-content">
                    <div class="activity-title">${this.getActivityTitle(activity)}</div>
                    <div class="activity-time">${activity.time_ago}</div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('afterbegin', activityHTML);

        // Remove animation class after animation completes
        setTimeout(() => {
            const newItem = container.querySelector('.activity-new');
            if (newItem) {
                newItem.classList.remove('activity-new');
            }
        }, 600);

        // Keep only last 10 items
        const items = container.querySelectorAll('.activity-item');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
    }

    /**
     * Show toast notification
     */
    showToast(title, message, type = 'info') {
        // Check if toast container exists
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${this.getToastIcon(type)}</div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        container.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('toast-hiding');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    getToastIcon(type) {
        const icons = {
            'info': 'ℹ️',
            'success': '✅',
            'warning': '⚠️',
            'error': '❌',
        };
        return icons[type] || 'ℹ️';
    }

    /**
     * Initialize charts
     */
    initializeCharts() {
        console.log('Initializing charts...');

        // Find all chart elements
        document.querySelectorAll('[data-chart]').forEach(canvas => {
            const chartType = canvas.dataset.chart;
            const chartId = canvas.id;

            if (chartType && chartId) {
                this.loadChartData(chartType, chartId, canvas);
            }
        });
    }

    /**
     * Load chart data from API
     */
    async loadChartData(type, chartId, canvas) {
        try {
            const response = await fetch(`/admin/api/dashboard/charts/${type}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to fetch chart data');
            }

            const result = await response.json();
            
            if (result.success) {
                this.renderChart(chartId, canvas, result.data);
            }
        } catch (error) {
            console.error(`Error loading chart ${type}:`, error);
        }
    }

    /**
     * Render a chart
     */
    renderChart(chartId, canvas, data) {
        // Destroy existing chart if any
        if (this.charts[chartId]) {
            this.charts[chartId].destroy();
        }

        // Create new chart
        this.charts[chartId] = new Chart(canvas, {
            type: data.type || 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    },
                },
                scales: data.type !== 'pie' && data.type !== 'doughnut' ? {
                    y: {
                        beginAtZero: true,
                    },
                } : undefined,
            },
        });
    }

    /**
     * Initialize notification system
     */
    initializeNotifications() {
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Load unread count
        this.updateNotificationCount();

        // Poll notification count every minute
        setInterval(() => this.updateNotificationCount(), 60000);
    }

    /**
     * Update notification count badge
     */
    async updateNotificationCount() {
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
}

// Initialize dashboard manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.dashboardManager = new DashboardManager();
    });
} else {
    window.dashboardManager = new DashboardManager();
}

export default DashboardManager;

