<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistical Analysis Report</title>
    <style>
        :root {
            --primary-maroon: #800020;
            --primary-gold: #FFD700;
            --dark-maroon: #5C0016;
            --text-dark: #1F2937;
            --text-gray: #6B7280;
            --border-gray: #E5E7EB;
            --white: #FFFFFF;
            --light-gray: #F9FAFB;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 0.75in;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: var(--text-dark);
            line-height: 1.4;
            background: var(--white);
            font-size: 10pt;
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-maroon);
        }

        .university-name {
            font-size: 16pt;
            font-weight: bold;
            color: var(--primary-maroon);
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .campus-name {
            font-size: 12pt;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .department-name {
            font-size: 10pt;
            color: var(--text-gray);
            font-style: italic;
            margin-bottom: 15px;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            color: var(--dark-maroon);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .report-meta {
            margin-bottom: 20px;
            font-size: 9pt;
            color: var(--text-gray);
            text-align: center;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: var(--primary-maroon);
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--border-gray);
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stats-row {
            display: table-row;
        }

        .stat-card {
            display: table-cell;
            text-align: center;
            padding: 12px;
            background: var(--light-gray);
            border: 1px solid var(--border-gray);
            width: 33.33%;
        }

        .stat-value {
            font-size: 18pt;
            font-weight: bold;
            color: var(--primary-maroon);
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 9pt;
            color: var(--text-gray);
        }

        .distribution-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .distribution-table th,
        .distribution-table td {
            padding: 8px;
            border: 1px solid var(--border-gray);
            text-align: left;
        }

        .distribution-table th {
            background: var(--primary-maroon);
            color: var(--white);
            font-weight: bold;
            font-size: 9pt;
        }

        .distribution-table td {
            font-size: 9pt;
        }

        .distribution-table tr:nth-child(even) {
            background: var(--light-gray);
        }

        .distribution-bar {
            background: var(--primary-maroon);
            height: 20px;
            display: inline-block;
            min-width: 2px;
        }

        .status-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .status-row {
            display: table-row;
        }

        .status-item {
            display: table-cell;
            padding: 10px;
            border: 1px solid var(--border-gray);
            background: var(--light-gray);
            text-align: center;
            width: 25%;
        }

        .status-count {
            font-size: 14pt;
            font-weight: bold;
            color: var(--primary-maroon);
            display: block;
        }

        .status-label {
            font-size: 8pt;
            color: var(--text-gray);
            text-transform: uppercase;
            margin-top: 3px;
        }

        .report-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--border-gray);
            font-size: 8pt;
            color: var(--text-gray);
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Report Header -->
        <header class="report-header">
            <h1 class="university-name">Eastern Visayas State University</h1>
            <h2 class="campus-name">Ormoc Campus</h2>
            <p class="department-name">Computer Studies Department</p>
            <h3 class="report-title">Statistical Analysis Report</h3>
        </header>

        <!-- Report Meta Information -->
        <div class="report-meta">
            <p><strong>Report Generated:</strong> {{ $generatedAt }} | <strong>Generated By:</strong> {{ $generatedBy }}</p>
        </div>

        <!-- Key Statistics -->
        <section class="statistics-section">
            <h3 class="section-title">Key Performance Metrics</h3>
            
            <div class="stats-grid">
                <div class="stats-row">
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['total'] }}</span>
                        <span class="stat-label">Total Applicants</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['average'] }}%</span>
                        <span class="stat-label">Average Score</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['median'] }}%</span>
                        <span class="stat-label">Median Score</span>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stats-row">
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['highest'] }}%</span>
                        <span class="stat-label">Highest Score</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['lowest'] }}%</span>
                        <span class="stat-label">Lowest Score</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['pass_rate'] }}%</span>
                        <span class="stat-label">Pass Rate</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Score Distribution -->
        <section class="score-distribution-section">
            <h3 class="section-title">Score Distribution</h3>
            
            <table class="distribution-table">
                <thead>
                    <tr>
                        <th>Score Range</th>
                        <th>Count</th>
                        <th>Percentage</th>
                        <th>Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Excellent (90-100%)</strong></td>
                        <td>{{ $scoreDistribution['excellent'] }}</td>
                        <td>{{ $statistics['total'] > 0 ? number_format(($scoreDistribution['excellent'] / $statistics['total']) * 100, 1) : 0 }}%</td>
                        <td>
                            <div class="distribution-bar" style="width: {{ $statistics['total'] > 0 ? ($scoreDistribution['excellent'] / $statistics['total']) * 200 : 0 }}px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Good (80-89%)</strong></td>
                        <td>{{ $scoreDistribution['good'] }}</td>
                        <td>{{ $statistics['total'] > 0 ? number_format(($scoreDistribution['good'] / $statistics['total']) * 100, 1) : 0 }}%</td>
                        <td>
                            <div class="distribution-bar" style="width: {{ $statistics['total'] > 0 ? ($scoreDistribution['good'] / $statistics['total']) * 200 : 0 }}px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Satisfactory (75-79%)</strong></td>
                        <td>{{ $scoreDistribution['satisfactory'] }}</td>
                        <td>{{ $statistics['total'] > 0 ? number_format(($scoreDistribution['satisfactory'] / $statistics['total']) * 100, 1) : 0 }}%</td>
                        <td>
                            <div class="distribution-bar" style="width: {{ $statistics['total'] > 0 ? ($scoreDistribution['satisfactory'] / $statistics['total']) * 200 : 0 }}px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Below Passing (0-74%)</strong></td>
                        <td>{{ $scoreDistribution['below_passing'] }}</td>
                        <td>{{ $statistics['total'] > 0 ? number_format(($scoreDistribution['below_passing'] / $statistics['total']) * 100, 1) : 0 }}%</td>
                        <td>
                            <div class="distribution-bar" style="width: {{ $statistics['total'] > 0 ? ($scoreDistribution['below_passing'] / $statistics['total']) * 200 : 0 }}px;"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Status Distribution -->
        <section class="status-distribution-section">
            <h3 class="section-title">Applicant Status Distribution</h3>
            
            <table class="distribution-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalStatuses = array_sum($statusDistribution);
                    @endphp
                    @foreach($statusDistribution as $status => $count)
                    <tr>
                        <td><strong>{{ ucwords(str_replace('-', ' ', $status)) }}</strong></td>
                        <td>{{ $count }}</td>
                        <td>{{ $totalStatuses > 0 ? number_format(($count / $totalStatuses) * 100, 1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <!-- Report Footer -->
        <footer class="report-footer">
            <p><strong>Analysis Notes:</strong></p>
            <ul style="margin: 10px 0; text-align: left; padding-left: 40px;">
                <li>Pass rate is calculated based on applicants scoring 75% or above</li>
                <li>Score distribution shows the spread of final scores across all applicants</li>
                <li>Final scores combine exam performance (60%) and interview evaluation (40%)</li>
            </ul>
            <div style="margin-top: 15px; font-size: 7pt;">
                <p>This report is confidential and intended for authorized personnel only.</p>
                <p>© {{ date('Y') }} Eastern Visayas State University - Ormoc Campus</p>
            </div>
        </footer>
    </div>
</body>
</html>

