<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Summary Report</title>
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
        }

        .stat-value {
            font-size: 16pt;
            font-weight: bold;
            color: var(--primary-maroon);
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 8pt;
            color: var(--text-gray);
        }

        .rubric-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .rubric-table th,
        .rubric-table td {
            padding: 8px;
            border: 1px solid var(--border-gray);
            text-align: left;
        }

        .rubric-table th {
            background: var(--primary-maroon);
            color: var(--white);
            font-weight: bold;
            font-size: 9pt;
        }

        .rubric-table td {
            font-size: 9pt;
        }

        .rubric-table tr:nth-child(even) {
            background: var(--light-gray);
        }

        .score-bar {
            background: var(--primary-maroon);
            height: 15px;
            display: inline-block;
            min-width: 2px;
        }

        .recommendation-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .recommendation-table th,
        .recommendation-table td {
            padding: 8px;
            border: 1px solid var(--border-gray);
        }

        .recommendation-table th {
            background: var(--primary-maroon);
            color: var(--white);
            font-weight: bold;
            font-size: 9pt;
        }

        .recommendation-table td {
            font-size: 9pt;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-highly {
            background: rgba(5, 150, 105, 0.2);
            color: #065F46;
        }

        .badge-recommended {
            background: rgba(59, 130, 246, 0.2);
            color: #1E40AF;
        }

        .badge-conditional {
            background: rgba(217, 119, 6, 0.2);
            color: #92400E;
        }

        .badge-not {
            background: rgba(220, 38, 38, 0.2);
            color: #991B1B;
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
            <h3 class="report-title">Interview Summary Report</h3>
        </header>

        <!-- Report Meta Information -->
        <div class="report-meta">
            <p><strong>Report Generated:</strong> {{ $generatedAt }} | <strong>Generated By:</strong> {{ $generatedBy }}</p>
        </div>

        <!-- Overview Statistics -->
        <section class="overview-section">
            <h3 class="section-title">Interview Overview</h3>
            
            <div class="stats-grid">
                <div class="stats-row">
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['total'] }}</span>
                        <span class="stat-label">Total Interviews</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['completed'] }}</span>
                        <span class="stat-label">Completed</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ number_format($statistics['average_score'], 2) }}</span>
                        <span class="stat-label">Average Score</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recommendation Distribution -->
        <section class="recommendation-section">
            <h3 class="section-title">Recommendation Distribution</h3>
            
            <div class="stats-grid">
                <div class="stats-row">
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['highly_recommended'] }}</span>
                        <span class="stat-label">Highly Recommended</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['recommended'] }}</span>
                        <span class="stat-label">Recommended</span>
                    </div>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stats-row">
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['conditional'] }}</span>
                        <span class="stat-label">Conditional</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ $statistics['not_recommended'] }}</span>
                        <span class="stat-label">Not Recommended</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Rubric Averages -->
        <section class="rubric-section">
            <h3 class="section-title">Average Rubric Scores (Out of 10)</h3>
            
            <table class="rubric-table">
                <thead>
                    <tr>
                        <th>Criteria</th>
                        <th style="width: 20%; text-align: center;">Average Score</th>
                        <th style="width: 40%;">Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Communication Skills</strong></td>
                        <td style="text-align: center;">{{ number_format($rubricAverages['communication_skills'], 2) }}</td>
                        <td>
                            <div class="score-bar" style="width: {{ ($rubricAverages['communication_skills'] / 10) * 150 }}px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Motivation & Interest</strong></td>
                        <td style="text-align: center;">{{ number_format($rubricAverages['motivation_interest'], 2) }}</td>
                        <td>
                            <div class="score-bar" style="width: {{ ($rubricAverages['motivation_interest'] / 10) * 150 }}px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Problem Solving Attitude</strong></td>
                        <td style="text-align: center;">{{ number_format($rubricAverages['problem_solving_attitude'], 2) }}</td>
                        <td>
                            <div class="score-bar" style="width: {{ ($rubricAverages['problem_solving_attitude'] / 10) * 150 }}px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Program Understanding</strong></td>
                        <td style="text-align: center;">{{ number_format($rubricAverages['program_understanding'], 2) }}</td>
                        <td>
                            <div class="score-bar" style="width: {{ ($rubricAverages['program_understanding'] / 10) * 150 }}px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Personality & Attitude</strong></td>
                        <td style="text-align: center;">{{ number_format($rubricAverages['personality_attitude'], 2) }}</td>
                        <td>
                            <div class="score-bar" style="width: {{ ($rubricAverages['personality_attitude'] / 10) * 150 }}px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>IT Background</strong></td>
                        <td style="text-align: center;">{{ number_format($rubricAverages['it_background'], 2) }}</td>
                        <td>
                            <div class="score-bar" style="width: {{ ($rubricAverages['it_background'] / 10) * 150 }}px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Willingness to Learn</strong></td>
                        <td style="text-align: center;">{{ number_format($rubricAverages['willingness_to_learn'], 2) }}</td>
                        <td>
                            <div class="score-bar" style="width: {{ ($rubricAverages['willingness_to_learn'] / 10) * 150 }}px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Overall Impression</strong></td>
                        <td style="text-align: center;">{{ number_format($rubricAverages['overall_impression'], 2) }}</td>
                        <td>
                            <div class="score-bar" style="width: {{ ($rubricAverages['overall_impression'] / 10) * 150 }}px;"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Top Performers -->
        @if($interviews->isNotEmpty())
        <section class="top-performers-section">
            <h3 class="section-title">Top Interview Performers</h3>
            
            <table class="recommendation-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Applicant Name</th>
                        <th>Overall Score</th>
                        <th>Recommendation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($interviews->take(10) as $index => $interview)
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                        <td>{{ $interview->applicant->full_name }}</td>
                        <td style="text-align: center;">{{ number_format($interview->overall_score, 2) }}</td>
                        <td>
                            @php
                                $badgeClass = match($interview->recommendation) {
                                    'highly_recommended' => 'badge-highly',
                                    'recommended' => 'badge-recommended',
                                    'conditional' => 'badge-conditional',
                                    'not_recommended' => 'badge-not',
                                    default => 'badge-recommended'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ ucwords(str_replace('_', ' ', $interview->recommendation ?? 'N/A')) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
        @endif

        <!-- Report Footer -->
        <footer class="report-footer">
            <p><strong>Interview Evaluation Notes:</strong></p>
            <ul style="margin: 10px 0; text-align: left; padding-left: 40px;">
                <li>Each rubric criterion is scored out of 10 points</li>
                <li>Overall score includes rubric scores plus recommendation weighting</li>
                <li>Recommendations guide admission decisions based on interview performance</li>
            </ul>
            <div style="margin-top: 15px; font-size: 7pt;">
                <p>This report is confidential and intended for authorized personnel only.</p>
                <p>© {{ date('Y') }} Eastern Visayas State University - Ormoc Campus</p>
            </div>
        </footer>
    </div>
</body>
</html>

