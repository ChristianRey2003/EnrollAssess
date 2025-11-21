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
            padding: 8px 36px 8px 14px;
            border: 1px solid rgba(128, 0, 32, 0.2);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            color: #800020;
            cursor: pointer;
            min-width: 140px;
            transition: all 0.3s ease;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23800020' d='M6 9L1 4h10z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
        }

        .dashboard-wrapper .period-selector select:hover {
            border-color: rgba(128, 0, 32, 0.4);
            box-shadow: 0 2px 4px rgba(128, 0, 32, 0.1);
        }

        .dashboard-wrapper .period-selector select:focus {
            outline: none;
            border-color: #800020;
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
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
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(128, 0, 32, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(128, 0, 32, 0.1);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            text-align: center;
        }

        .dashboard-wrapper .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #800020 0%, #FFD700 100%);
        }

        .dashboard-wrapper .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 32, 0.15), 0 2px 6px rgba(0, 0, 0, 0.1);
            border-color: rgba(128, 0, 32, 0.2);
        }

        .dashboard-wrapper .kpi-value {
            font-size: 32px;
            font-weight: 700;
            color: #800020;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .dashboard-wrapper .kpi-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            line-height: 1.4;
        }

        .dashboard-wrapper .charts-main-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 15px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .dashboard-wrapper .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
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
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 12px;
            padding: 16px 18px;
            box-shadow: 0 2px 8px rgba(128, 0, 32, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(128, 0, 32, 0.1);
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            min-width: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .dashboard-wrapper .chart-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 32, 0.15), 0 2px 6px rgba(0, 0, 0, 0.1);
            border-color: rgba(128, 0, 32, 0.2);
        }

        .dashboard-wrapper .chart-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #800020 0%, #FFD700 100%);
        }

        .dashboard-wrapper .chart-card .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .dashboard-wrapper .chart-filter {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 160px;
        }

        .dashboard-wrapper .chart-filter label {
            font-size: 11px;
            color: #6b7280;
        }

        .dashboard-wrapper .chart-filter select {
            border: 1px solid rgba(128, 0, 32, 0.2);
            border-radius: 6px;
            padding: 6px 28px 6px 10px;
            font-size: 12px;
            font-weight: 500;
            color: #800020;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%23800020' d='M5 7L1 3h8z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 8px center !important;
        }

        .dashboard-wrapper .chart-filter select:hover {
            border-color: rgba(128, 0, 32, 0.4);
            box-shadow: 0 2px 4px rgba(128, 0, 32, 0.1);
        }

        .dashboard-wrapper .chart-filter select:focus {
            outline: none;
            border-color: #800020;
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
        }

        .dashboard-wrapper .chart-title {
            font-size: 15px;
            font-weight: 700;
            color: #800020;
            margin-bottom: 8px;
            letter-spacing: -0.3px;
        }

        .dashboard-wrapper .chart-headline {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
            line-height: 1.4;
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
        .dashboard-wrapper .chart-container #statusChart,
        .dashboard-wrapper .chart-container #cityChart {
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
            <div class="kpi-value">{{ number_format($analytics['kpis']['total_applicants'] ?? 0) }}</div>
            <div class="kpi-label">Total Applicants</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ number_format($analytics['kpis']['finished_exam'] ?? 0) }}</div>
            <div class="kpi-label">Applicants Who Finished the Exam</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ number_format($analytics['kpis']['interview_completed'] ?? 0) }}</div>
            <div class="kpi-label">Interview Completed</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value">{{ number_format($analytics['kpis']['fully_screened'] ?? 0) }}</div>
            <div class="kpi-label">Applicants Fully Screened</div>
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

    <!-- Charts Main Container -->
    <div class="charts-main-container">
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

            <!-- Status Distribution (Pie Chart) -->
            @php
                $pendingFilterOptions = $analytics['pending_interview_distribution']['options'] ?? [];
                $pendingOverall = $analytics['pending_interview_distribution']['overall'] ?? null;
                $pendingOverallHeadline = $pendingOverall['headline'] ?? ($analytics['status_distribution']['headline'] ?? 'No applicant data available yet.');
            @endphp
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                    <div class="chart-title">Applicant Status Distribution</div>
                    <div class="chart-headline" data-status-headline>{{ $pendingOverallHeadline }}</div>
                    </div>
                    @if(!empty($pendingFilterOptions))
                        <div class="chart-filter">
                            <label for="statusInstructorFilter">Instructor</label>
                            <select id="statusInstructorFilter">
                                <option value="all">All instructors</option>
                                @foreach($pendingFilterOptions as $option)
                                    <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                @php
                    $statusTotal = isset($pendingOverall['data']) ? array_sum($pendingOverall['data']) : 0;
                @endphp
                <div class="chart-container small">
                    @if($statusTotal > 0)
                        <div id="statusChart"></div>
                    @else
                        <div class="no-data">No applicant data available yet.</div>
                    @endif
                </div>
            </div>

            <!-- City Distribution Chart -->
            @php
                $cityData = $analytics['cities'] ?? ['labels' => [], 'data' => [], 'color' => '#800020'];
                $cityTotal = !empty($cityData['data']) ? array_sum($cityData['data']) : 0;
                $cityHeadline = $cityTotal > 0 
                    ? sprintf('Top %d cities/municipalities with %d total applicants.', min(count($cityData['labels']), 10), $cityTotal)
                    : 'No city data available yet.';
            @endphp
            <div class="chart-card">
                <div class="chart-title">City Distribution</div>
                <div class="chart-headline">{{ $cityHeadline }}</div>
                <div class="chart-container small">
                    @if($cityTotal > 0 && !empty($cityData['labels']))
                        <div id="cityChart"></div>
                    @else
                        <div class="no-data">No city data available yet.</div>
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

        const pendingDistribution = analytics.pending_interview_distribution ?? {};
        const pendingOverall = pendingDistribution.overall ?? { labels: [], data: [], colors: [], headline: 'No applicant data available yet.' };
        const statusInstructorDatasets = pendingDistribution.datasets ?? {};
        const defaultStatusDataset = {
            labels: pendingOverall.labels || [],
            data: pendingOverall.data || [],
            colors: pendingOverall.colors || [],
            headline: pendingOverall.headline || 'No applicant data available yet.'
        };
        const statusHeadlineElement = document.querySelector('[data-status-headline]');

        function applyStatusDataset(dataset) {
            if (!charts.statusChart || !dataset) {
                return;
            }
            const datasetTotal = (dataset.data || []).reduce((sum, value) => sum + value, 0);
            charts.statusChart.updateOptions({
                labels: dataset.labels,
                colors: dataset.colors,
                dataLabels: {
                    formatter: function(val, opts) {
                        const count = dataset.data[opts.seriesIndex];
                        return count;
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val, opts) {
                            const label = dataset.labels[opts.seriesIndex];
                            const count = dataset.data[opts.seriesIndex];
                            const percentage = datasetTotal > 0 ? ((count / datasetTotal) * 100).toFixed(1) : 0;
                            return label + ': ' + count + ' applicants (' + percentage + '%)';
                        }
                    }
                }
            });
            charts.statusChart.updateSeries(dataset.data);
            if (statusHeadlineElement) {
                statusHeadlineElement.textContent = dataset.headline;
            }
        }

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

        // Pending vs Interview Distribution (Pie Chart)
        const statusTotal = (pendingOverall.data || []).reduce((sum, value) => sum + value, 0);
        if (statusTotal > 0) {
            const statusChartElement = document.getElementById('statusChart');
            if (statusChartElement) {
                charts.statusChart = new ApexCharts(statusChartElement, {
                    series: pendingOverall.data,
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
                    labels: pendingOverall.labels,
                    colors: pendingOverall.colors,
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
                            const currentData = opts.w.config.series || pendingOverall.data;
                            const count = currentData[opts.seriesIndex];
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
                                const currentLabels = opts.w.config.labels || pendingOverall.labels;
                                const currentData = opts.w.config.series || pendingOverall.data;
                                const label = currentLabels[opts.seriesIndex];
                                const count = currentData[opts.seriesIndex];
                                const currentTotal = currentData.reduce((sum, value) => sum + value, 0);
                                const percentage = currentTotal > 0 ? ((count / currentTotal) * 100).toFixed(1) : 0;
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

                const statusFilter = document.getElementById('statusInstructorFilter');
                const pendingDatasetCache = {};
                pendingDatasetCache['all'] = defaultStatusDataset;
                Object.keys(statusInstructorDatasets).forEach(key => {
                    pendingDatasetCache[key] = statusInstructorDatasets[key];
                });

                if (statusFilter) {
                    statusFilter.addEventListener('change', function() {
                        const value = this.value;
                        if (value === 'all') {
                            applyStatusDataset(defaultStatusDataset);
                        } else {
                            const dataset = pendingDatasetCache[value] || statusInstructorDatasets[value];
                            if (dataset) {
                                applyStatusDataset(dataset);
                            }
                        }
                    });
                }
            }
        }

        // City Distribution Chart (Horizontal Bar Chart)
        const cityData = analytics.cities ?? { labels: [], data: [], color: '#800020' };
        if (cityData.labels.length > 0 && cityData.data.length > 0) {
            const cityChartElement = document.getElementById('cityChart');
            if (cityChartElement) {
                const cityColors = cityData.labels.map((label, index) => {
                    const colors = ['#800020', '#FFD700', '#5c0017', '#E6C200', '#a0002a', '#FFF8DC'];
                    return colors[index % colors.length];
                });

                charts.cityChart = new ApexCharts(cityChartElement, {
                    series: [{
                        name: 'Applicants',
                        data: cityData.data
                    }],
                    chart: {
                        type: 'bar',
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
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 6,
                            dataLabels: {
                                position: 'right'
                            },
                            distributed: true
                        }
                    },
                    colors: cityColors,
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val;
                        },
                        style: {
                            fontSize: '11px',
                            fontWeight: 600,
                            colors: ['#1f2937']
                        },
                        offsetX: 5
                    },
                    xaxis: {
                        categories: cityData.labels,
                        labels: {
                            style: {
                                colors: '#6b7280',
                                fontSize: '11px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#374151',
                                fontWeight: 500,
                                fontSize: '11px'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e5e7eb',
                        strokeDashArray: 3
                    },
                    tooltip: {
                        theme: 'light',
                        y: {
                            formatter: function(value) {
                                return value + ' applicant(s)';
                            }
                        },
                        style: {
                            fontSize: '12px'
                        }
                    }
                });
                charts.cityChart.render();
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
