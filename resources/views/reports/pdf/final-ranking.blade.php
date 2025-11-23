<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Applicant Ranking Report</title>
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
            display: table;
            width: 100%;
            margin-bottom: 20px;
            font-size: 9pt;
            color: var(--text-gray);
        }

        .meta-row {
            display: table-row;
        }

        .meta-label {
            display: table-cell;
            font-weight: bold;
            color: var(--text-dark);
            padding: 3px 10px 3px 0;
            width: 30%;
        }

        .meta-value {
            display: table-cell;
            padding: 3px 0;
        }

        .summary-section {
            margin-bottom: 20px;
            background: var(--light-gray);
            padding: 12px;
            border-radius: 6px;
            border: 1px solid var(--border-gray);
        }

        .summary-title {
            font-size: 12pt;
            font-weight: bold;
            color: var(--primary-maroon);
            margin-bottom: 10px;
            text-align: center;
        }

        .summary-stats {
            display: table;
            width: 100%;
        }

        .stat-row {
            display: table-row;
        }

        .stat-cell {
            display: table-cell;
            text-align: center;
            padding: 8px;
            background: var(--white);
            border: 1px solid var(--border-gray);
            width: 33.33%;
        }

        .stat-value {
            font-size: 14pt;
            font-weight: bold;
            color: var(--primary-maroon);
            display: block;
        }

        .stat-label {
            font-size: 8pt;
            color: var(--text-gray);
            margin-top: 2px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: var(--primary-maroon);
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--border-gray);
        }

        .rankings-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background: var(--white);
        }

        .rankings-table thead th {
            background: var(--primary-maroon);
            color: var(--white);
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
            border: 1px solid var(--dark-maroon);
        }

        .rankings-table tbody td {
            padding: 6px;
            border: 1px solid var(--border-gray);
            font-size: 9pt;
            vertical-align: top;
        }

        .rankings-table tbody tr:nth-child(even) {
            background: var(--light-gray);
        }

        .rank-cell {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
        }

        .rank-cell.top-3 {
            background: rgba(255, 215, 0, 0.2);
            color: var(--dark-maroon);
        }

        .rank-cell.top-10 {
            background: rgba(128, 0, 32, 0.1);
            color: var(--primary-maroon);
        }

        .applicant-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .applicant-id {
            font-size: 8pt;
            color: var(--text-gray);
            font-style: italic;
        }

        .score-cell {
            text-align: center;
            font-weight: bold;
        }

        .score-excellent {
            color: #059669;
        }

        .score-good {
            color: #0284C7;
        }

        .score-average {
            color: #D97706;
        }

        .score-below {
            color: #DC2626;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-recommended {
            background: rgba(5, 150, 105, 0.2);
            color: #065F46;
        }

        .status-waitlisted {
            background: rgba(217, 119, 6, 0.2);
            color: #92400E;
        }

        .status-not-recommended {
            background: rgba(220, 38, 38, 0.2);
            color: #991B1B;
        }

        .report-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--border-gray);
            font-size: 8pt;
            color: var(--text-gray);
        }

        .signature-section {
            margin-top: 40px;
            width: 100%;
        }

        .signature-top-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .signature-bottom-section {
            display: table;
            width: 100%;
            margin-top: 20px;
        }

        .signature-divider {
            width: 100%;
            height: 2px;
            background-color: var(--text-dark);
            margin: 20px 0;
        }

        .signature-block {
            display: table-cell;
            text-align: left;
            width: 50%;
            padding: 0 30px;
            vertical-align: top;
        }

        .signature-label {
            font-size: 9pt;
            color: var(--text-dark);
            font-style: italic;
            margin-bottom: 8px;
        }

        .signature-name {
            font-size: 11pt;
            color: var(--text-dark);
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            padding-bottom: 3px;
            display: block;
            width: 250px;
            border-bottom: 1.5px solid var(--text-dark);
        }

        .signature-name-line {
            display: none;
        }

        .signature-title {
            font-size: 9pt;
            color: var(--text-dark);
            margin-top: 5px;
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
            <h3 class="report-title">Final Applicant Ranking Report</h3>
        </header>

        <!-- Report Meta Information -->
        <div class="report-meta">
            <div class="meta-row">
                <div class="meta-label">Academic Year:</div>
                <div class="meta-value">{{ date('Y') }}-{{ date('Y') + 1 }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Report Generated:</div>
                <div class="meta-value">{{ $generatedAt }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Generated By:</div>
                <div class="meta-value">{{ $generatedBy }}</div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <section class="summary-section">
            <h3 class="summary-title">Report Summary</h3>
            <div class="summary-stats">
                <div class="stat-row">
                    <div class="stat-cell">
                        <span class="stat-value">{{ $totalApplicants }}</span>
                        <span class="stat-label">Total Applicants</span>
                    </div>
                    <div class="stat-cell">
                        <span class="stat-value">{{ $recommended }}</span>
                        <span class="stat-label">Recommended</span>
                    </div>
                    <div class="stat-cell">
                        <span class="stat-value">{{ number_format($averageScore, 2) }}%</span>
                        <span class="stat-label">Average Score</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final Rankings Table -->
        <section class="rankings-section">
            <h3 class="section-title">Applicant Rankings</h3>
            
            <table class="rankings-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Rank</th>
                        <th style="width: 28%;">Applicant Name</th>
                        <th style="width: 14%;">Exam Score</th>
                        <th style="width: 14%;">Interview Score</th>
                        <th style="width: 14%;">Final Score</th>
                        <th style="width: 22%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicants as $index => $applicant)
                    <tr>
                        <td class="rank-cell {{ $index < 3 ? 'top-3' : ($index < 10 ? 'top-10' : '') }}">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            <div class="applicant-name">{{ $applicant->full_name }}</div>
                            <div class="applicant-id">ID: {{ $applicant->application_no }}</div>
                        </td>
                        <td class="score-cell {{ $applicant->enrollassess_score >= 90 ? 'score-excellent' : ($applicant->enrollassess_score >= 80 ? 'score-good' : ($applicant->enrollassess_score >= 75 ? 'score-average' : 'score-below')) }}">
                            {{ number_format($applicant->enrollassess_score ?? 0, 2) }}%
                        </td>
                        <td class="score-cell {{ $applicant->interview_score >= 90 ? 'score-excellent' : ($applicant->interview_score >= 80 ? 'score-good' : ($applicant->interview_score >= 75 ? 'score-average' : 'score-below')) }}">
                            {{ number_format($applicant->interview_score ?? 0, 2) }}%
                        </td>
                        <td class="score-cell {{ $applicant->final_score >= 90 ? 'score-excellent' : ($applicant->final_score >= 80 ? 'score-good' : ($applicant->final_score >= 75 ? 'score-average' : 'score-below')) }}">
                            {{ number_format($applicant->final_score, 2) }}%
                        </td>
                        <td>
                            <span class="status-badge status-{{ $applicant->recommendation }}">
                                {{ ucwords(str_replace('_', ' ', $applicant->recommendation)) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <!-- Report Footer -->
        <footer class="report-footer">
            <p><strong>Notes:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 10px;">
                <li>Final scores are calculated as: (Exam Score × 0.6) + (Interview Score × 0.4)</li>
                <li>Minimum passing score: 75 points</li>
                <li>Recommended: Score ≥ 75 | Waitlisted: Score 70-74.9 | Not Recommended: Score < 70</li>
            </ul>

            <div class="signature-section">
                <!-- Top Section: Prepared by and Noted -->
                <div class="signature-top-section">
                    <div class="signature-block">
                        <div class="signature-label">Prepared by:</div>
                        <div class="signature-name">{{ $signatures['prepared_by_name'] ?? 'JOSEPH JAYMEL S. MORPOS' }}</div>
                        <div class="signature-title">{{ $signatures['prepared_by_title'] ?? 'Head, Computer Studies Department' }}</div>
                    </div>
                    <div class="signature-block">
                        <div class="signature-label">Noted:</div>
                        <div class="signature-name">{{ $signatures['noted_name'] ?? 'DR. JEFFRY V. OCAY' }}</div>
                        <div class="signature-title">{{ $signatures['noted_title'] ?? 'Director, Ormoc Campus' }}</div>
                    </div>
                </div>

                <!-- Divider Bar -->
                <div class="signature-divider"></div>

                <!-- Bottom Section: Recommending Approval and Approved -->
                <div class="signature-bottom-section">
                    <div class="signature-block">
                        <div class="signature-label">Recommending Approval:</div>
                        <div class="signature-name">{{ $signatures['recommending_name'] ?? 'LYDIA M. MORANTE, D.A.' }}</div>
                        <div class="signature-title">{{ $signatures['recommending_title'] ?? 'Vice President for Academic Affairs' }}</div>
                    </div>
                    <div class="signature-block">
                        <div class="signature-label">Approved:</div>
                        <div class="signature-name">{{ $signatures['approved_name'] ?? 'DENNIS C. DE PAZ, PH.D.' }}</div>
                        <div class="signature-title">{{ $signatures['approved_title'] ?? 'University President' }}</div>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 15px; font-size: 7pt; color: var(--text-gray);">
                <p>This report is confidential and intended for authorized personnel only.</p>
                <p>© {{ date('Y') }} Eastern Visayas State University - Ormoc Campus</p>
            </div>
        </footer>
    </div>
</body>
</html>

