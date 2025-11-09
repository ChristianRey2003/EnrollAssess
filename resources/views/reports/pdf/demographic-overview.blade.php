<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demographic Overview Report</title>
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

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            color: var(--dark-maroon);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .report-meta {
            font-size: 9pt;
            color: var(--text-gray);
            margin-bottom: 20px;
        }

        .meta-item {
            display: inline-block;
            margin-right: 20px;
        }

        .summary-boxes {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-box {
            display: table-cell;
            padding: 12px;
            background: var(--light-gray);
            border: 1px solid var(--border-gray);
            text-align: center;
            width: 50%;
        }

        .summary-value {
            font-size: 20pt;
            font-weight: bold;
            color: var(--primary-maroon);
        }

        .summary-label {
            font-size: 9pt;
            color: var(--text-gray);
            margin-top: 5px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: var(--primary-maroon);
            margin: 20px 0 12px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--border-gray);
        }

        .demographics-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .demo-column {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }

        th {
            background: var(--primary-maroon);
            color: var(--white);
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid var(--border-gray);
        }

        tr:nth-child(even) {
            background: var(--light-gray);
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .percentage-bar {
            background: var(--light-gray);
            height: 18px;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .percentage-fill {
            background: var(--primary-maroon);
            height: 100%;
            float: left;
        }

        .percentage-text {
            position: absolute;
            width: 100%;
            text-align: center;
            line-height: 18px;
            font-size: 7.5pt;
            font-weight: bold;
            color: var(--text-dark);
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid var(--border-gray);
            font-size: 8pt;
            color: var(--text-gray);
        }

        .signature-section {
            margin-top: 50px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid var(--text-dark);
            margin: 60px 30px 5px 30px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="report-header">
        <div class="university-name">Eastern Visayas State University</div>
        <div class="campus-name">Ormoc City Campus</div>
        <div class="report-title">Demographic Overview Report</div>
    </div>

    <!-- Meta Information -->
    <div class="report-meta">
        <div class="meta-item"><strong>Generated:</strong> {{ $generatedAt }}</div>
        <div class="meta-item"><strong>Generated By:</strong> {{ $generatedBy }}</div>
        @if(isset($filters['age_range']) && $filters['age_range'] !== 'all')
            <div class="meta-item"><strong>Age Range Filter:</strong> {{ $filters['age_range'] }} years</div>
        @endif
        @if(isset($filters['status']) && $filters['status'] !== 'all')
            <div class="meta-item"><strong>Status Filter:</strong> {{ ucfirst(str_replace('-', ' ', $filters['status'])) }}</div>
        @endif
    </div>

    <!-- Summary Statistics -->
    <div class="summary-boxes">
        <div class="summary-box">
            <div class="summary-value">{{ $totalApplicants }}</div>
            <div class="summary-label">Total Applicants</div>
        </div>
        <div class="summary-box">
            <div class="summary-value">{{ number_format($averageAge ?? 0, 1) }}</div>
            <div class="summary-label">Average Age</div>
        </div>
    </div>

    <!-- Gender and Age Distribution -->
    <div class="demographics-grid">
        <div class="demo-column">
            <div class="section-title">👥 Gender Distribution</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Gender</th>
                        <th style="width: 20%;" class="text-center">Count</th>
                        <th style="width: 40%;">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($genderData as $data)
                        <tr>
                            <td><strong>{{ $data['gender'] }}</strong></td>
                            <td class="text-center">{{ $data['count'] }}</td>
                            <td>
                                <div class="percentage-bar">
                                    <div class="percentage-fill" style="width: {{ $data['percentage'] }}%;"></div>
                                    <div class="percentage-text">{{ $data['percentage'] }}%</div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="demo-column">
            <div class="section-title">📅 Age Distribution</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Age Range</th>
                        <th style="width: 20%;" class="text-center">Count</th>
                        <th style="width: 40%;">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ageData as $data)
                        <tr>
                            <td><strong>{{ $data['range'] }}</strong></td>
                            <td class="text-center">{{ $data['count'] }}</td>
                            <td>
                                <div class="percentage-bar">
                                    <div class="percentage-fill" style="width: {{ $data['percentage'] }}%;"></div>
                                    <div class="percentage-text">{{ $data['percentage'] }}%</div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Civil Status Distribution -->
    <div class="section-title">💍 Civil Status Distribution</div>
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Civil Status</th>
                <th style="width: 15%;" class="text-center">Count</th>
                <th style="width: 15%;" class="text-center">Percentage</th>
                <th style="width: 40%;">Distribution</th>
            </tr>
        </thead>
        <tbody>
            @forelse($civilStatusData as $data)
                <tr>
                    <td><strong>{{ $data['status'] }}</strong></td>
                    <td class="text-center">{{ $data['count'] }}</td>
                    <td class="text-center">{{ $data['percentage'] }}%</td>
                    <td>
                        <div class="percentage-bar">
                            <div class="percentage-fill" style="width: {{ $data['percentage'] }}%;"></div>
                            <div class="percentage-text">{{ $data['percentage'] }}%</div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Applicant Type Distribution -->
    <div class="section-title">📝 Applicant Type Distribution</div>
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Applicant Type</th>
                <th style="width: 15%;" class="text-center">Count</th>
                <th style="width: 15%;" class="text-center">Percentage</th>
                <th style="width: 40%;">Distribution</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicantTypeData as $data)
                <tr>
                    <td><strong>{{ $data['type'] }}</strong></td>
                    <td class="text-center">{{ $data['count'] }}</td>
                    <td class="text-center">{{ $data['percentage'] }}%</td>
                    <td>
                        <div class="percentage-bar">
                            <div class="percentage-fill" style="width: {{ $data['percentage'] }}%;"></div>
                            <div class="percentage-text">{{ $data['percentage'] }}%</div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- PWD Statistics -->
    <div class="section-title">♿ PWD (Person with Disability) Statistics</div>
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">PWD Status</th>
                <th style="width: 15%;" class="text-center">Count</th>
                <th style="width: 15%;" class="text-center">Percentage</th>
                <th style="width: 40%;">Distribution</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pwdData as $data)
                <tr>
                    <td><strong>{{ $data['status'] }}</strong></td>
                    <td class="text-center">{{ $data['count'] }}</td>
                    <td class="text-center">{{ $data['percentage'] }}%</td>
                    <td>
                        <div class="percentage-bar">
                            <div class="percentage-fill" style="width: {{ $data['percentage'] }}%;"></div>
                            <div class="percentage-text">{{ $data['percentage'] }}%</div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div style="text-align: center; margin-bottom: 10px;">
            <strong>Eastern Visayas State University - Ormoc City Campus</strong><br>
            Automated Report Generation System
        </div>
        <div style="text-align: center; font-size: 7pt;">
            This is a computer-generated report. Generated on {{ $generatedAt }}
        </div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <strong>Prepared By</strong><br>
                {{ $generatedBy }}
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <strong>Reviewed By</strong><br>
                Department Head
            </div>
        </div>
    </div>
</body>
</html>

