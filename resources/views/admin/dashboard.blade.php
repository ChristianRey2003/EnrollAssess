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

        .dashboard-wrapper .kpis-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .dashboard-wrapper .period-selector {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-shrink: 0;
        }

        .dashboard-wrapper .period-selector label {
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            white-space: nowrap;
        }

        .dashboard-wrapper .period-selector select {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            background: white;
            cursor: pointer;
            min-width: 140px;
        }

        .dashboard-wrapper .kpis-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 8px;
            flex: 1;
            min-width: 0;
        }

        @media (max-width: 768px) {
            .dashboard-wrapper .kpis-header {
                flex-direction: column;
                align-items: stretch;
            }

            .dashboard-wrapper .period-selector {
                width: 100%;
                justify-content: flex-end;
            }
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

        .dashboard-wrapper .charts-main-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 12px;
            margin-bottom: 15px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .dashboard-wrapper .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            min-width: 0;
        }

        .dashboard-wrapper .chart-card.full-height {
            height: 100%;
        }

        .dashboard-wrapper .chart-container.full-height {
            height: calc(100% - 60px);
            min-height: 400px;
        }

        @media (max-width: 1200px) {
            .dashboard-wrapper .charts-main-container {
                grid-template-columns: 1fr;
            }

            .dashboard-wrapper .charts-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .dashboard-wrapper .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .dashboard-wrapper .chart-card {
            background: white;
            border-radius: 8px;
            padding: 10px 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            min-width: 0; /* Important for grid items to shrink below content size */
            overflow: hidden; /* Prevent content from overflowing */
        }

        .dashboard-wrapper .chart-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .dashboard-wrapper .chart-headline {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .dashboard-wrapper .chart-container {
            position: relative;
            height: 160px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            min-width: 0; /* Important for grid items to shrink below content size */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dashboard-wrapper .chart-container.small {
            height: 140px;
        }

        /* ApexCharts styling improvements */
        .dashboard-wrapper .chart-container #examSexChart,
        .dashboard-wrapper .chart-container #interviewSexChart,
        .dashboard-wrapper .chart-container #cityChart,
        .dashboard-wrapper .chart-container #violationChart,
        .dashboard-wrapper .chart-container #statusChart {
            width: 100% !important;
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
    <!-- KPIs Header with Period Selector -->
    <div class="kpis-header">
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
    </div>

    <!-- Charts Main Container: Left (Long Chart) + Right (4 Small Charts) -->
    <div class="charts-main-container">
        <!-- Left: Top Cities / Municipalities (Long Chart) -->
        <div class="chart-card full-height">
            <div class="chart-title">Top Cities / Municipalities</div>
            <div class="chart-container full-height">
                @if(!empty($analytics['cities']['labels'] ?? []))
                    <div id="cityChart"></div>
                @else
                    <div class="no-data">No data available</div>
                @endif
            </div>
        </div>

        <!-- Right: 4 Small Charts in 2x2 Grid -->
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
                        <div id="examSexChart"></div>
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
                        <div id="interviewSexChart"></div>
                    @else
                        <div class="no-data">No interview scores recorded yet.</div>
                    @endif
                </div>
            </div>

            <!-- Violation Distribution (Pie Chart) -->
            <div class="chart-card">
                <div class="chart-title">Exam Violation Distribution</div>
                <div class="chart-headline">{{ $analytics['violations']['headline'] ?? 'No exam completion data available yet.' }}</div>
                @php
                    $violationTotal = isset($analytics['violations']['total']) ? $analytics['violations']['total'] : 0;
                @endphp
                <div class="chart-container small">
                    @if($violationTotal > 0)
                        <div id="violationChart"></div>
                    @else
                        <div class="no-data">No exam completion data available yet.</div>
                    @endif
                </div>
            </div>

            <!-- Status Distribution (Pie Chart) -->
            <div class="chart-card">
                <div class="chart-title">Applicant Status Distribution</div>
                <div class="chart-headline">{{ $analytics['status_distribution']['headline'] ?? 'No applicant data available yet.' }}</div>
                @php
                    $statusTotal = isset($analytics['status_distribution']['total']) ? $analytics['status_distribution']['total'] : 0;
                @endphp
                <div class="chart-container small">
                    @if($statusTotal > 0)
                        <div id="statusChart"></div>
                    @else
                        <div class="no-data">No applicant data available yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const analytics = @json($analytics);
        
        // Store chart instances for resizing
        const charts = {};

        // Function to resize all charts
        function resizeAllCharts() {
            Object.values(charts).forEach(chart => {
                if (chart && typeof chart.update === 'function') {
                    chart.update();
                }
            });
        }

        // Exam High Scores by Sex (Pie Chart)
        const examData = analytics.exam_scores_by_sex ?? { labels: [], data: [], colors: [] };
        if (examData.data.length && Math.max(...examData.data) > 0) {
            const examChartElement = document.getElementById('examSexChart');
            if (examChartElement) {
                charts.examSexChart = new ApexCharts(examChartElement, {
                    series: examData.data,
                    chart: {
                        type: 'pie',
                        height: 140,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        },
                        fontFamily: 'inherit'
                    },
                    labels: examData.labels,
                    colors: examData.colors,
                    plotOptions: {
                        pie: {
                            expandOnClick: true,
                            donut: {
                                size: '0%'
                            },
                            dataLabels: {
                                offset: 0
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            const score = examData.data[opts.seriesIndex];
                            return score.toFixed(2);
                        },
                        style: {
                            fontSize: '14px',
                            fontWeight: 700,
                            colors: ['#fff']
                        },
                        dropShadow: {
                            enabled: true,
                            blur: 4,
                            opacity: 0.7
                        }
                    },
                    legend: {
                        show: true,
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '10px',
                        fontWeight: 500,
                        markers: {
                            width: 8,
                            height: 8,
                            radius: 4
                        },
                        itemMargin: {
                            horizontal: 6,
                            vertical: 2
                        }
                    },
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function(val, opts) {
                                const label = opts.w.config.labels[opts.seriesIndex];
                                const score = examData.data[opts.seriesIndex];
                                return label + ': ' + score.toFixed(2) + ' points';
                            }
                        },
                        style: {
                            fontSize: '12px'
                        }
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['#fff']
                    }
                });
                charts.examSexChart.render();
            }
        }

        // Interview High Scores by Sex (Pie Chart)
        const interviewData = analytics.interview_scores_by_sex ?? { labels: [], data: [], colors: [] };
        if (interviewData.data.length && Math.max(...interviewData.data) > 0) {
            const interviewChartElement = document.getElementById('interviewSexChart');
            if (interviewChartElement) {
                charts.interviewSexChart = new ApexCharts(interviewChartElement, {
                    series: interviewData.data,
                    chart: {
                        type: 'pie',
                        height: 140,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        },
                        fontFamily: 'inherit'
                    },
                    labels: interviewData.labels,
                    colors: interviewData.colors,
                    plotOptions: {
                        pie: {
                            expandOnClick: true,
                            donut: {
                                size: '0%'
                            },
                            dataLabels: {
                                offset: 0
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            const score = interviewData.data[opts.seriesIndex];
                            return score.toFixed(2);
                        },
                        style: {
                            fontSize: '14px',
                            fontWeight: 700,
                            colors: ['#fff']
                        },
                        dropShadow: {
                            enabled: true,
                            blur: 4,
                            opacity: 0.7
                        }
                    },
                    legend: {
                        show: true,
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '10px',
                        fontWeight: 500,
                        markers: {
                            width: 8,
                            height: 8,
                            radius: 4
                        },
                        itemMargin: {
                            horizontal: 6,
                            vertical: 2
                        }
                    },
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function(val, opts) {
                                const label = opts.w.config.labels[opts.seriesIndex];
                                const score = interviewData.data[opts.seriesIndex];
                                return label + ': ' + score.toFixed(2) + ' points';
                            }
                        },
                        style: {
                            fontSize: '12px'
                        }
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['#fff']
                    }
                });
                charts.interviewSexChart.render();
            }
        }

        // City Top 10 (Vertical Bar Chart)
        const cityData = analytics.cities ?? { labels: [], data: [], color: '#1D4ED8' };
        if (cityData.labels.length > 0) {
            const cityChartElement = document.getElementById('cityChart');
            if (cityChartElement) {
                charts.cityChart = new ApexCharts(cityChartElement, {
                    series: [{
                        name: 'Applicants',
                        data: cityData.data
                    }],
                    chart: {
                        type: 'bar',
                        height: '100%',
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        },
                        fontFamily: 'inherit'
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 8,
                            horizontal: false,
                            columnWidth: '65%',
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val;
                        },
                        offsetY: -15,
                        style: {
                            fontSize: '10px',
                            fontWeight: 600,
                            colors: ['#1f2937']
                        }
                    },
                    xaxis: {
                        categories: cityData.labels,
                        labels: {
                            rotate: -45,
                            rotateAlways: true,
                            style: {
                                fontSize: '9px',
                                fontWeight: 500
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(val) {
                                return val.toFixed(0);
                            },
                            style: {
                                fontSize: '9px',
                                fontWeight: 500
                            }
                        }
                    },
                    colors: [cityData.color],
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function(val) {
                                return val + ' applicants';
                            }
                        },
                        style: {
                            fontSize: '12px'
                        },
                        theme: 'light'
                    },
                    grid: {
                        borderColor: '#e5e7eb',
                        strokeDashArray: 4,
                        xaxis: {
                            lines: {
                                show: false
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        },
                        padding: {
                            top: 0,
                            right: 0,
                            bottom: 0,
                            left: 0
                        }
                    }
                });
                charts.cityChart.render();
            }
        }

        // Violation Distribution (Pie Chart)
        const violationData = analytics.violations ?? { labels: [], data: [], colors: [], total: 0 };
        if (violationData.total > 0) {
            const violationChartElement = document.getElementById('violationChart');
            if (violationChartElement) {
                charts.violationChart = new ApexCharts(violationChartElement, {
                    series: violationData.data,
                    chart: {
                        type: 'pie',
                        height: 140,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        },
                        fontFamily: 'inherit'
                    },
                    labels: violationData.labels,
                    colors: violationData.colors,
                    plotOptions: {
                        pie: {
                            expandOnClick: true,
                            donut: {
                                size: '0%'
                            },
                            dataLabels: {
                                offset: 0
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            const count = violationData.data[opts.seriesIndex];
                            return count;
                        },
                        style: {
                            fontSize: '14px',
                            fontWeight: 700,
                            colors: ['#fff']
                        },
                        dropShadow: {
                            enabled: true,
                            blur: 4,
                            opacity: 0.7
                        }
                    },
                    legend: {
                        show: true,
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '10px',
                        fontWeight: 500,
                        markers: {
                            width: 8,
                            height: 8,
                            radius: 4
                        },
                        itemMargin: {
                            horizontal: 6,
                            vertical: 2
                        }
                    },
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function(val, opts) {
                                const label = opts.w.config.labels[opts.seriesIndex];
                                const count = violationData.data[opts.seriesIndex];
                                const percentage = ((count / violationData.total) * 100).toFixed(1);
                                return label + ': ' + count + ' students (' + percentage + '%)';
                            }
                        },
                        style: {
                            fontSize: '12px'
                        }
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['#fff']
                    }
                });
                charts.violationChart.render();
            }
        }

        // Status Distribution (Pie Chart)
        const statusData = analytics.status_distribution ?? { labels: [], data: [], colors: [], total: 0 };
        if (statusData.total > 0) {
            const statusChartElement = document.getElementById('statusChart');
            if (statusChartElement) {
                charts.statusChart = new ApexCharts(statusChartElement, {
                    series: statusData.data,
                    chart: {
                        type: 'pie',
                        height: 140,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        },
                        fontFamily: 'inherit'
                    },
                    labels: statusData.labels,
                    colors: statusData.colors,
                    plotOptions: {
                        pie: {
                            expandOnClick: true,
                            donut: {
                                size: '0%'
                            },
                            dataLabels: {
                                offset: 0
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            const count = statusData.data[opts.seriesIndex];
                            return count;
                        },
                        style: {
                            fontSize: '14px',
                            fontWeight: 700,
                            colors: ['#fff']
                        },
                        dropShadow: {
                            enabled: true,
                            blur: 4,
                            opacity: 0.7
                        }
                    },
                    legend: {
                        show: true,
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '10px',
                        fontWeight: 500,
                        markers: {
                            width: 8,
                            height: 8,
                            radius: 4
                        },
                        itemMargin: {
                            horizontal: 6,
                            vertical: 2
                        }
                    },
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function(val, opts) {
                                const label = opts.w.config.labels[opts.seriesIndex];
                                const count = statusData.data[opts.seriesIndex];
                                const percentage = ((count / statusData.total) * 100).toFixed(1);
                                return label + ': ' + count + ' applicants (' + percentage + '%)';
                            }
                        },
                        style: {
                            fontSize: '12px'
                        }
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['#fff']
                    }
                });
                charts.statusChart.render();
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
