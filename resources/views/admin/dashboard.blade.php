@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Student Basic Information Analytics';
@endphp

@push('body-class')
dashboard-page
@endpush

@push('styles')
    <style>
        /* Dashboard-specific scoped styles */
        .dashboard-page .main-content {
            padding: 0;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .dashboard-page .main-header {
            margin-bottom: 0;
            width: 100%;
            max-width: 100%;
        }

        .dashboard-wrapper {
            padding: 15px 20px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        
        /* Ensure dashboard adjusts when sidebar is collapsed */
        .admin-main.sidebar-collapsed .dashboard-wrapper {
            width: 100%;
            max-width: 100%;
        }

        .dashboard-wrapper .period-selector {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 12px;
            gap: 8px;
            align-items: center;
        }

        .dashboard-wrapper .period-selector label {
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
        }

        .dashboard-wrapper .period-selector select {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            background: white;
            cursor: pointer;
        }

        .dashboard-wrapper .kpis-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 8px;
            margin-bottom: 15px;
        }

        .dashboard-wrapper .kpi-card {
            background: white;
            border-radius: 8px;
            padding: 10px 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }

        .dashboard-wrapper .kpi-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .dashboard-wrapper .kpi-value {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
        }

        .dashboard-wrapper .charts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 15px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            min-width: 0; /* Important for grid items to shrink below content size */
        }

        @media (max-width: 1024px) {
            .dashboard-wrapper .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .dashboard-wrapper .chart-card {
            background: white;
            border-radius: 8px;
            padding: 12px 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            min-width: 0; /* Important for grid items to shrink below content size */
            overflow: hidden; /* Prevent content from overflowing */
        }

        .dashboard-wrapper .chart-title {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .dashboard-wrapper .chart-headline {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .dashboard-wrapper .chart-container {
            position: relative;
            height: 220px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            min-width: 0; /* Important for grid items to shrink below content size */
        }

        .dashboard-wrapper .chart-container.small {
            height: 180px;
        }
        
        .dashboard-wrapper .chart-title,
        .dashboard-wrapper .chart-headline {
            min-width: 0; /* Allow text to wrap if needed */
            word-wrap: break-word;
        }

        .dashboard-wrapper .no-data {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #9ca3af;
            font-size: 13px;
            text-align: center;
        }
    </style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <!-- Period Selector -->
    <div class="period-selector">
        <label for="periodSelect">Period:</label>
        <select id="periodSelect" onchange="window.location.href='?period=' + this.value">
            <option value="7" {{ $period == 7 ? 'selected' : '' }}>Last 7 Days</option>
            <option value="30" {{ $period == 30 ? 'selected' : '' }}>Last 30 Days</option>
            <option value="90" {{ $period == 90 ? 'selected' : '' }}>Last 90 Days</option>
            <option value="0" {{ $period == 0 ? 'selected' : '' }}>All Time</option>
        </select>
    </div>

    <!-- KPIs -->
    <div class="kpis-grid">
        <div class="kpi-card">
            <div class="kpi-label">Forms Completed</div>
            <div class="kpi-value">{{ number_format($analytics['kpis']['total_completed']) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Male Applicant Count</div>
            <div class="kpi-value">{{ number_format($analytics['kpis']['male_count'] ?? 0) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Female Applicant Count</div>
            <div class="kpi-value">{{ number_format($analytics['kpis']['female_count'] ?? 0) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Top City / Municipality</div>
            <div class="kpi-value" style="font-size: 18px;">{{ $analytics['kpis']['top_city'] ?? 'N/A' }}</div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <!-- Exam High Scores by Sex -->
        <div class="chart-card">
            <div class="chart-title">Exam High Score by Sex</div>
            <div class="chart-headline">{{ $analytics['exam_scores_by_sex']['headline'] ?? 'No exam scores recorded yet.' }}</div>
            @php
                $examMax = isset($analytics['exam_scores_by_sex']['data'])
                    ? collect($analytics['exam_scores_by_sex']['data'])->max()
                    : 0;
            @endphp
            <div class="chart-container small">
                @if($examMax > 0)
                    <canvas id="examSexChart"></canvas>
                @else
                    <div class="no-data">No exam scores recorded yet.</div>
                @endif
            </div>
        </div>

        <!-- Interview High Scores by Sex -->
        <div class="chart-card">
            <div class="chart-title">Interview High Score by Sex</div>
            <div class="chart-headline">{{ $analytics['interview_scores_by_sex']['headline'] ?? 'No interview scores recorded yet.' }}</div>
            @php
                $interviewMax = isset($analytics['interview_scores_by_sex']['data'])
                    ? collect($analytics['interview_scores_by_sex']['data'])->max()
                    : 0;
            @endphp
            <div class="chart-container small">
                @if($interviewMax > 0)
                    <canvas id="interviewSexChart"></canvas>
                @else
                    <div class="no-data">No interview scores recorded yet.</div>
                @endif
            </div>
        </div>

        <!-- City Top 10 (Vertical Bar) -->
        <div class="chart-card">
            <div class="chart-title">Top Cities / Municipalities</div>
            <div class="chart-container">
                @if(!empty($analytics['cities']['labels'] ?? []))
                    <canvas id="cityChart"></canvas>
                @else
                    <div class="no-data">No data available</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const analytics = @json($analytics);
        
        // Store chart instances for resizing
        const charts = {};

        // Function to resize all charts
        function resizeAllCharts() {
            Object.values(charts).forEach(chart => {
                if (chart && typeof chart.resize === 'function') {
                    chart.resize();
                }
            });
        }

        // Exam High Scores by Sex (Horizontal Bar Chart)
        const examData = analytics.exam_scores_by_sex ?? { labels: [], data: [], colors: [] };
        if (examData.data.length && Math.max(...examData.data) > 0) {
            const examChartElement = document.getElementById('examSexChart');
            if (examChartElement) {
                charts.examSexChart = new Chart(examChartElement, {
                    type: 'bar',
                    data: {
                        labels: examData.labels,
                        datasets: [{
                            data: examData.data,
                            backgroundColor: examData.colors,
                            borderRadius: 6,
                            maxBarThickness: 26,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: context => `${context.label}: ${context.formattedValue}`
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }

        // Interview High Scores by Sex (Horizontal Bar Chart)
        const interviewData = analytics.interview_scores_by_sex ?? { labels: [], data: [], colors: [] };
        if (interviewData.data.length && Math.max(...interviewData.data) > 0) {
            const interviewChartElement = document.getElementById('interviewSexChart');
            if (interviewChartElement) {
                charts.interviewSexChart = new Chart(interviewChartElement, {
                    type: 'bar',
                    data: {
                        labels: interviewData.labels,
                        datasets: [{
                            data: interviewData.data,
                            backgroundColor: interviewData.colors,
                            borderRadius: 6,
                            maxBarThickness: 26,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: context => `${context.label}: ${context.formattedValue}`
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                suggestedMax: 80,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }

        // City Top 10 (Vertical Bar Chart)
        const cityData = analytics.cities ?? { labels: [], data: [], color: '#1D4ED8' };
        if (cityData.labels.length > 0) {
            const cityChartElement = document.getElementById('cityChart');
            if (cityChartElement) {
                charts.cityChart = new Chart(cityChartElement, {
                    type: 'bar',
                    data: {
                        labels: cityData.labels,
                        datasets: [{
                            label: 'Applicants',
                            data: cityData.data,
                            backgroundColor: cityData.color,
                            borderRadius: 6,
                            maxBarThickness: 40,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: context => `${context.label}: ${context.formattedValue} applicants`
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45,
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }

        // Listen for sidebar toggle to resize charts
        // Hook into the global toggleSidebar function
        const originalToggleSidebar = window.toggleSidebar;
        if (typeof originalToggleSidebar === 'function') {
            window.toggleSidebar = function() {
                originalToggleSidebar();
                // Resize charts after sidebar animation completes (300ms transition + 50ms buffer)
                setTimeout(resizeAllCharts, 350);
            };
        }

        // Also listen for class changes on admin-main to catch any sidebar state changes
        const adminMain = document.querySelector('.admin-main');
        if (adminMain) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        // Debounce resize to avoid multiple calls
                        setTimeout(resizeAllCharts, 350);
                    }
                });
            });
            observer.observe(adminMain, { attributes: true, attributeFilter: ['class'] });
        }

        // Also use ResizeObserver to detect container size changes
        const mainContent = document.querySelector('.main-content');
        if (mainContent && window.ResizeObserver) {
            const resizeObserver = new ResizeObserver(() => {
                resizeAllCharts();
            });
            resizeObserver.observe(mainContent);
        }

        // Fallback: Listen for window resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(resizeAllCharts, 250);
        });
    });
</script>
@endpush
