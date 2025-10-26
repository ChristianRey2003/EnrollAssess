@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@php
    $pageTitle = 'Analytics Dashboard';
    $pageSubtitle = 'Data insights and performance metrics';
@endphp

@section('content')
                <!-- Quick Stats Overview -->
                <div class="stats-grid analytics-stats">
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value" data-stat="total_applicants">{{ $stats['total_applicants'] ?? 0 }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value" data-stat="exam_completed">{{ $stats['exam_completed'] ?? 0 }}</div>
                        <div class="stat-label">Exams Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value" data-stat="admitted">{{ $stats['admitted'] ?? 0 }}</div>
                        <div class="stat-label">Admitted</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['pass_rate'] ?? 0 }}%</div>
                        <div class="stat-label">Pass Rate</div>
                    </div>
                </div>

                <!-- Chart Controls -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Data Visualization</h2>
                        <div class="section-actions">
                            <select id="chartPeriod" class="chart-period-select">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="60">Last 60 days</option>
                                <option value="90">Last 90 days</option>
                            </select>
                            <button onclick="loadCharts()" class="btn-refresh">
                                 Refresh All
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Application Trends -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Application Trends</h2>
                    </div>
                    <div class="section-content">
                        <div class="chart-grid">
                            <div class="chart-container chart-loading">
                                <h3 class="chart-title">New Applicants Over Time</h3>
                                <div class="chart-wrapper">
                                    <canvas 
                                        id="applicantsChart" 
                                        data-chart-type="line"
                                        data-endpoint="/admin/api/dashboard/charts/applicants"
                                    ></canvas>
                                </div>
                            </div>

                            <div class="chart-container chart-loading">
                                <h3 class="chart-title">Status Distribution</h3>
                                <div class="chart-wrapper">
                                    <canvas 
                                        id="statusChart" 
                                        data-chart-type="doughnut"
                                        data-endpoint="/admin/api/dashboard/charts/status_distribution"
                                    ></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exam Performance -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Exam Performance</h2>
                    </div>
                    <div class="section-content">
                        <div class="chart-grid">
                            <div class="chart-container chart-loading">
                                <h3 class="chart-title">Exam Completions</h3>
                                <div class="chart-wrapper">
                                    <canvas 
                                        id="examsChart" 
                                        data-chart-type="line"
                                        data-endpoint="/admin/api/dashboard/charts/exams"
                                    ></canvas>
                                </div>
                            </div>

                            <div class="chart-container chart-loading">
                                <h3 class="chart-title">Score Distribution</h3>
                                <div class="chart-wrapper">
                                    <canvas 
                                        id="scoresChart" 
                                        data-chart-type="bar"
                                        data-endpoint="/admin/analytics-dashboard/score-distribution?type=exam"
                                    ></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interview Analytics -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Interview Analytics</h2>
                    </div>
                    <div class="section-content">
                        <div class="chart-grid">
                            <div class="chart-container chart-loading">
                                <h3 class="chart-title">Interview Trends</h3>
                                <div class="chart-wrapper">
                                    <canvas 
                                        id="interviewsChart" 
                                        data-chart-type="line"
                                        data-endpoint="/admin/api/dashboard/charts/interviews"
                                    ></canvas>
                                </div>
                            </div>

                            <div class="chart-container chart-loading">
                                <h3 class="chart-title">Instructor Workload</h3>
                                <div class="chart-wrapper">
                                    <canvas 
                                        id="workloadChart" 
                                        data-chart-type="bar"
                                        data-endpoint="/admin/analytics-dashboard/instructor-workload"
                                    ></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Performance Metrics</h2>
                    </div>
                    <div class="section-content">
                        <div class="chart-grid">
                            <div class="chart-container chart-loading">
                                <h3 class="chart-title">Performance Trends</h3>
                                <div class="chart-wrapper">
                                    <canvas 
                                        id="performanceChart" 
                                        data-chart-type="line"
                                        data-endpoint="/admin/analytics-dashboard/performance-trends"
                                    ></canvas>
                                </div>
                            </div>

                            <div class="chart-container chart-loading">
                                <h3 class="chart-title">Conversion Funnel</h3>
                                <div class="chart-wrapper chart-funnel">
                                    <div id="funnelChart" data-endpoint="/admin/analytics-dashboard/conversion-funnel"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time to Completion -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Process Efficiency</h2>
                    </div>
                    <div class="section-content">
                        <div class="chart-container chart-loading">
                            <h3 class="chart-title">Time to Completion Distribution</h3>
                            <div class="chart-wrapper">
                                    <canvas 
                                        id="timeChart" 
                                        data-chart-type="bar"
                                        data-endpoint="/admin/analytics-dashboard/time-to-completion"
                                    ></canvas>
                            </div>
                        </div>
                    </div>
                </div>
@endsection

@section('scripts')
<script>
// Simple chart loading function
function loadCharts() {
    console.log('Loading charts...');
    
    // Show loading indicators
    document.querySelectorAll('.chart-loading').forEach(chart => {
        chart.innerHTML = '<div class="loading-spinner">Loading chart...</div>';
    });
    
    // Load charts one by one to avoid overwhelming the server
    setTimeout(() => {
        loadChart('applicantsChart', '/admin/analytics-dashboard/performance-trends');
    }, 100);
    
    setTimeout(() => {
        loadChart('statusChart', '/admin/analytics-dashboard/score-distribution');
    }, 500);
    
    setTimeout(() => {
        loadChart('examsChart', '/admin/analytics-dashboard/performance-trends');
    }, 1000);
}

function loadChart(canvasId, endpoint) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    fetch(endpoint)
        .then(response => response.json())
        .then(data => {
            // Simple chart rendering
            const ctx = canvas.getContext('2d');
            // Basic chart implementation here
            console.log('Chart loaded:', canvasId, data);
        })
        .catch(error => {
            console.error('Chart loading error:', error);
            canvas.parentElement.innerHTML = '<div class="chart-error">Chart failed to load</div>';
        });
}

// Load charts when page is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Analytics page loaded');
    // Don't auto-load charts, let user click refresh
});
</script>
@endsection

@push('styles')
    <style>
        .analytics-stats {
            margin-bottom: 30px;
        }

        .chart-period-select {
            padding: 8px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            margin-right: 10px;
        }

        .btn-refresh {
            padding: 8px 16px;
            background: var(--yellow-light);
            border: 2px solid var(--yellow-primary);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-refresh:hover {
            background: var(--yellow-primary);
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            padding: 30px;
        }

        .chart-container {
            background: var(--white);
            border: 2px solid var(--border-gray);
            border-radius: 12px;
            padding: 24px;
            position: relative;
        }

        .chart-loading::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border: 4px solid var(--border-gray);
            border-top-color: var(--maroon-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .chart-loaded::before {
            display: none;
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin: 0 0 20px 0;
            text-align: center;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
        }

        .chart-funnel {
            height: auto;
            min-height: 300px;
        }

        .chart-error {
            border-color: var(--red);
        }

        .chart-error-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--red);
            font-weight: 600;
            text-align: center;
        }

        .section-actions {
            display: flex;
            align-items: center;
        }

        /* Funnel Chart Styles */
        .funnel-stage {
            background: var(--light-gray);
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
        }

        .funnel-stage:hover {
            border-color: var(--yellow-primary);
            transform: translateX(5px);
        }

        .funnel-stage-name {
            font-weight: 600;
            color: var(--maroon-primary);
        }

        .funnel-stage-stats {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .funnel-stage-count {
            font-size: 24px;
            font-weight: 700;
            color: var(--maroon-primary);
        }

        .funnel-stage-percentage {
            font-size: 14px;
            color: var(--text-gray);
            background: var(--yellow-light);
            padding: 4px 12px;
            border-radius: 20px;
        }

        @media (max-width: 768px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }

            .section-actions {
                flex-direction: column;
                gap: 10px;
            }

            .chart-period-select {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Load conversion funnel data
        async function loadConversionFunnel() {
            try {
                const response = await fetch('/admin/analytics-dashboard/conversion-funnel', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const result = await response.json();
                
                if (result.success && result.data) {
                    renderFunnel(result.data);
                }
            } catch (error) {
                console.error('Error loading funnel:', error);
            }
        }

        function renderFunnel(data) {
            const container = document.getElementById('funnelChart');
            if (!container) return;

            container.innerHTML = data.map(stage => `
                <div class="funnel-stage">
                    <div class="funnel-stage-name">${stage.stage}</div>
                    <div class="funnel-stage-stats">
                        <div class="funnel-stage-count">${stage.count}</div>
                        <div class="funnel-stage-percentage">${stage.percentage}%</div>
                    </div>
                </div>
            `).join('');

            const funnelContainer = container.closest('.chart-container');
            if (funnelContainer) {
                funnelContainer.classList.remove('chart-loading');
            }
        }

        // Handle period change
        document.getElementById('chartPeriod')?.addEventListener('change', function(e) {
            const period = e.target.value;
            // Update all chart endpoints with new period
            document.querySelectorAll('[data-endpoint]').forEach(canvas => {
                const endpoint = canvas.dataset.endpoint;
                if (endpoint.includes('?')) {
                    canvas.dataset.endpoint = endpoint.split('?')[0] + '?period=' + period;
                } else {
                    canvas.dataset.endpoint = endpoint + '?period=' + period;
                }
            });
            
            // Refresh all charts
            if (window.chartsManager) {
                window.chartsManager.refreshAllCharts();
            }
            loadConversionFunnel();
        });

        // Load funnel on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadConversionFunnel();
        });
    </script>
@endpush

