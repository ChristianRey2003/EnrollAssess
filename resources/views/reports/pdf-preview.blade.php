<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSIT Applicant Screening Results - PDF Report</title>
    <style>
        /* PDF-Optimized Styles */
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

        /* Reset and base styles */
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
            font-family: 'Times New Roman', Times, serif;
            color: var(--text-dark);
            line-height: 1.4;
            background: var(--white);
            font-size: 11pt;
        }

        /* Print-specific styles */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            table {
                page-break-inside: avoid;
            }
            
            thead {
                display: table-header-group;
            }
        }

        /* Report Container */
        .report-container {
            max-width: 8.5in;
            margin: 0 auto;
            background: var(--white);
            min-height: 11in;
            padding: 0;
            position: relative;
        }

        /* Header Section */
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--primary-maroon);
        }

        .university-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, var(--primary-maroon), var(--dark-maroon));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 32px;
            font-weight: bold;
        }

        .university-name {
            font-size: 18pt;
            font-weight: bold;
            color: var(--primary-maroon);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .campus-name {
            font-size: 14pt;
            color: var(--text-dark);
            margin-bottom: 15px;
        }

        .department-name {
            font-size: 12pt;
            color: var(--text-gray);
            font-style: italic;
            margin-bottom: 20px;
        }

        .report-title {
            font-size: 16pt;
            font-weight: bold;
            color: var(--dark-maroon);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .report-subtitle {
            font-size: 12pt;
            color: var(--text-gray);
            margin-bottom: 10px;
        }

        /* Report Meta Information */
        .report-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            font-size: 10pt;
            color: var(--text-gray);
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-weight: bold;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        /* Summary Statistics */
        .summary-section {
            margin-bottom: 25px;
            background: var(--light-gray);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-gray);
        }

        .summary-title {
            font-size: 14pt;
            font-weight: bold;
            color: var(--primary-maroon);
            margin-bottom: 15px;
            text-align: center;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
            background: var(--white);
            border-radius: 6px;
            border: 1px solid var(--border-gray);
        }

        .stat-value {
            font-size: 18pt;
            font-weight: bold;
            color: var(--primary-maroon);
            display: block;
        }

        .stat-label {
            font-size: 9pt;
            color: var(--text-gray);
            margin-top: 3px;
        }

        /* Rankings Table */
        .rankings-section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: var(--primary-maroon);
            margin-bottom: 15px;
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
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
            border: 1px solid var(--dark-maroon);
        }

        .rankings-table tbody td {
            padding: 10px 8px;
            border: 1px solid var(--border-gray);
            font-size: 10pt;
            vertical-align: top;
        }

        .rankings-table tbody tr:nth-child(even) {
            background: var(--light-gray);
        }

        .rankings-table tbody tr:hover {
            background: rgba(128, 0, 32, 0.05);
        }

        /* Rank styling */
        .rank-cell {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
        }

        .rank-cell.top-3 {
            background: rgba(255, 215, 0, 0.2);
            color: var(--dark-maroon);
        }

        .rank-cell.top-10 {
            background: rgba(128, 0, 32, 0.1);
            color: var(--primary-maroon);
        }

        /* Applicant name styling */
        .applicant-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .applicant-id {
            font-size: 9pt;
            color: var(--text-gray);
            font-style: italic;
        }

        /* Score styling */
        .score-cell {
            text-align: center;
            font-weight: bold;
        }

        .score-excellent {
            color: #059669;
            background: rgba(5, 150, 105, 0.1);
        }

        .score-good {
            color: #0284C7;
            background: rgba(2, 132, 199, 0.1);
        }

        .score-average {
            color: #D97706;
            background: rgba(217, 119, 6, 0.1);
        }

        .score-below {
            color: #DC2626;
            background: rgba(220, 38, 38, 0.1);
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .status-interview-pending {
            background: rgba(59, 130, 246, 0.2);
            color: #1E40AF;
        }

        /* Footer Section */
        .report-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-gray);
            font-size: 9pt;
            color: var(--text-gray);
        }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 25px;
        }

        .signature-block {
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid var(--text-dark);
            margin-bottom: 5px;
            height: 40px;
        }

        .signature-label {
            font-size: 10pt;
            color: var(--text-dark);
            font-weight: bold;
        }

        .signature-title {
            font-size: 9pt;
            color: var(--text-gray);
            font-style: italic;
        }

        /* Legend */
        .legend-section {
            margin-top: 20px;
            background: var(--light-gray);
            padding: 12px;
            border-radius: 6px;
            border: 1px solid var(--border-gray);
        }

        .legend-title {
            font-size: 11pt;
            font-weight: bold;
            color: var(--primary-maroon);
            margin-bottom: 8px;
        }

        .legend-items {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            font-size: 9pt;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Print Controls (hidden in print) */
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--white);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            border: 1px solid var(--border-gray);
        }

        .print-controls button {
            background: var(--primary-maroon);
            color: var(--white);
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 8px;
            font-size: 11pt;
        }

        .print-controls button:hover {
            background: var(--dark-maroon);
        }

        .print-controls button.secondary {
            background: var(--white);
            color: var(--primary-maroon);
            border: 1px solid var(--primary-maroon);
        }

        .print-controls button.secondary:hover {
            background: var(--light-gray);
        }
    </style>
</head>
<body>
    <!-- Print Controls (visible only on screen) -->
    <div class="print-controls no-print">
        <button onclick="window.print()">🖨️ Print Report</button>
        <button onclick="downloadPDF()" class="secondary">📄 Download PDF</button>
        <button onclick="window.close()" class="secondary">✖️ Close</button>
    </div>

    <div class="report-container">
        <!-- Report Header -->
        <header class="report-header">
            <div class="university-logo">EVSU</div>
            <h1 class="university-name">Eastern Visayas State University</h1>
            <h2 class="campus-name">Ormoc Campus</h2>
            <p class="department-name">Computer Studies Department</p>
            
            <h3 class="report-title">BSIT Applicant Screening Results</h3>
            <p class="report-subtitle">Final Rankings and Recommendations</p>
        </header>

        <!-- Report Meta Information -->
        <div class="report-meta">
            <div class="meta-item">
                <span class="meta-label">Academic Year:</span>
                <span>2024-2025</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Screening Period:</span>
                <span>January 10-25, 2024</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Report Generated:</span>
                <span>January 26, 2024 - 10:30 AM</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Report ID:</span>
                <span>BSIT-2024-001</span>
            </div>
        </div>

        <!-- Summary Statistics -->
        <section class="summary-section">
            <h3 class="summary-title">📊 Screening Summary</h3>
            <div class="summary-stats">
                <div class="stat-item">
                    <span class="stat-value">45</span>
                    <span class="stat-label">Total Applicants</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">42</span>
                    <span class="stat-label">Completed Exams</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">38</span>
                    <span class="stat-label">Interviewed</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">25</span>
                    <span class="stat-label">Recommended</span>
                </div>
            </div>
        </section>

        <!-- Final Rankings Table -->
        <section class="rankings-section">
            <h3 class="section-title">🏆 Final Applicant Rankings</h3>
            
            <table class="rankings-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Rank</th>
                        <th style="width: 30%;">Applicant Name</th>
                        <th style="width: 12%;">Exam Score</th>
                        <th style="width: 15%;">Interview Score</th>
                        <th style="width: 12%;">Final Score</th>
                        <th style="width: 23%;">Recommendation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="rank-cell top-3">1</td>
                        <td>
                            <div class="applicant-name">Maria Christina Santos</div>
                            <div class="applicant-id">ID: APP2024-001</div>
                        </td>
                        <td class="score-cell score-excellent">19/20</td>
                        <td class="score-cell score-excellent">95/100</td>
                        <td class="score-cell score-excellent">94.5</td>
                        <td><span class="status-badge status-recommended">Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell top-3">2</td>
                        <td>
                            <div class="applicant-name">John Michael Reyes</div>
                            <div class="applicant-id">ID: APP2024-007</div>
                        </td>
                        <td class="score-cell score-excellent">18/20</td>
                        <td class="score-cell score-excellent">92/100</td>
                        <td class="score-cell score-excellent">91.0</td>
                        <td><span class="status-badge status-recommended">Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell top-3">3</td>
                        <td>
                            <div class="applicant-name">Anna Patricia Cruz</div>
                            <div class="applicant-id">ID: APP2024-015</div>
                        </td>
                        <td class="score-cell score-excellent">17/20</td>
                        <td class="score-cell score-excellent">90/100</td>
                        <td class="score-cell score-excellent">88.5</td>
                        <td><span class="status-badge status-recommended">Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell top-10">4</td>
                        <td>
                            <div class="applicant-name">Mark Anthony Garcia</div>
                            <div class="applicant-id">ID: APP2024-023</div>
                        </td>
                        <td class="score-cell score-excellent">17/20</td>
                        <td class="score-cell score-good">88/100</td>
                        <td class="score-cell score-excellent">86.5</td>
                        <td><span class="status-badge status-recommended">Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell top-10">5</td>
                        <td>
                            <div class="applicant-name">Sarah Jane Mendoza</div>
                            <div class="applicant-id">ID: APP2024-012</div>
                        </td>
                        <td class="score-cell score-good">16/20</td>
                        <td class="score-cell score-excellent">89/100</td>
                        <td class="score-cell score-excellent">85.4</td>
                        <td><span class="status-badge status-recommended">Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell top-10">6</td>
                        <td>
                            <div class="applicant-name">Robert James Torres</div>
                            <div class="applicant-id">ID: APP2024-031</div>
                        </td>
                        <td class="score-cell score-good">16/20</td>
                        <td class="score-cell score-good">86/100</td>
                        <td class="score-cell score-good">83.2</td>
                        <td><span class="status-badge status-recommended">Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell top-10">7</td>
                        <td>
                            <div class="applicant-name">Michelle Anne Lopez</div>
                            <div class="applicant-id">ID: APP2024-018</div>
                        </td>
                        <td class="score-cell score-good">15/20</td>
                        <td class="score-cell score-excellent">87/100</td>
                        <td class="score-cell score-good">82.5</td>
                        <td><span class="status-badge status-recommended">Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell top-10">8</td>
                        <td>
                            <div class="applicant-name">David Andrew Flores</div>
                            <div class="applicant-id">ID: APP2024-009</div>
                        </td>
                        <td class="score-cell score-good">15/20</td>
                        <td class="score-cell score-good">85/100</td>
                        <td class="score-cell score-good">81.0</td>
                        <td><span class="status-badge status-recommended">Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell top-10">9</td>
                        <td>
                            <div class="applicant-name">Lisa Marie Villanueva</div>
                            <div class="applicant-id">ID: APP2024-026</div>
                        </td>
                        <td class="score-cell score-good">14/20</td>
                        <td class="score-cell score-good">84/100</td>
                        <td class="score-cell score-good">79.2</td>
                        <td><span class="status-badge status-recommended">Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell top-10">10</td>
                        <td>
                            <div class="applicant-name">Christopher Paul Ramos</div>
                            <div class="applicant-id">ID: APP2024-033</div>
                        </td>
                        <td class="score-cell score-good">14/20</td>
                        <td class="score-cell score-good">82/100</td>
                        <td class="score-cell score-good">77.6</td>
                        <td><span class="status-badge status-waitlisted">Waitlisted</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell">11</td>
                        <td>
                            <div class="applicant-name">Jennifer Rose Aquino</div>
                            <div class="applicant-id">ID: APP2024-041</div>
                        </td>
                        <td class="score-cell score-average">13/20</td>
                        <td class="score-cell score-good">81/100</td>
                        <td class="score-cell score-average">75.5</td>
                        <td><span class="status-badge status-waitlisted">Waitlisted</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell">12</td>
                        <td>
                            <div class="applicant-name">Kevin Anthony Dela Cruz</div>
                            <div class="applicant-id">ID: APP2024-019</div>
                        </td>
                        <td class="score-cell score-average">13/20</td>
                        <td class="score-cell score-average">78/100</td>
                        <td class="score-cell score-average">73.8</td>
                        <td><span class="status-badge status-waitlisted">Waitlisted</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell">13</td>
                        <td>
                            <div class="applicant-name">Patricia Mae Gonzales</div>
                            <div class="applicant-id">ID: APP2024-024</div>
                        </td>
                        <td class="score-cell score-average">12/20</td>
                        <td class="score-cell score-average">75/100</td>
                        <td class="score-cell score-average">70.5</td>
                        <td><span class="status-badge status-not-recommended">Not Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell">14</td>
                        <td>
                            <div class="applicant-name">Ryan Carlo Perez</div>
                            <div class="applicant-id">ID: APP2024-036</div>
                        </td>
                        <td class="score-cell score-average">11/20</td>
                        <td class="score-cell score-average">72/100</td>
                        <td class="score-cell score-average">67.6</td>
                        <td><span class="status-badge status-not-recommended">Not Recommended</span></td>
                    </tr>
                    <tr>
                        <td class="rank-cell">15</td>
                        <td>
                            <div class="applicant-name">Stephanie Grace Morales</div>
                            <div class="applicant-id">ID: APP2024-014</div>
                        </td>
                        <td class="score-cell score-below">10/20</td>
                        <td class="score-cell score-average">70/100</td>
                        <td class="score-cell score-below">65.0</td>
                        <td><span class="status-badge status-not-recommended">Not Recommended</span></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Legend -->
        <div class="legend-section">
            <h4 class="legend-title">📝 Scoring Legend</h4>
            <div class="legend-items">
                <div class="legend-item">
                    <span class="status-badge status-recommended">Recommended</span>
                    <span>Score ≥ 75 - Qualified for admission</span>
                </div>
                <div class="legend-item">
                    <span class="status-badge status-waitlisted">Waitlisted</span>
                    <span>Score 70-74.9 - Reserve list</span>
                </div>
                <div class="legend-item">
                    <span class="status-badge status-not-recommended">Not Recommended</span>
                    <span>Score < 70 - Not qualified</span>
                </div>
                <div class="legend-item">
                    <span class="status-badge status-interview-pending">Interview Pending</span>
                    <span>Interview not yet completed</span>
                </div>
            </div>
        </div>

        <!-- Report Footer -->
        <footer class="report-footer">
            <p><strong>Notes:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 15px;">
                <li>Final scores are calculated as: (Exam Score × 0.6) + (Interview Score × 0.4)</li>
                <li>Minimum passing score: 75 points</li>
                <li>Available slots for BSIT Program: 25 students</li>
                <li>Waitlisted applicants may be considered if slots become available</li>
            </ul>

            <div class="signature-section">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Dr. Maria Elena Santos</div>
                    <div class="signature-title">Department Head, Computer Studies</div>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Prof. John Ricardo Dela Cruz</div>
                    <div class="signature-title">Admissions Committee Chair</div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px; font-size: 8pt; color: var(--text-gray);">
                <p>This report is confidential and intended for authorized personnel only.</p>
                <p>© 2024 Eastern Visayas State University - Ormoc Campus | Computer Studies Department</p>
            </div>
        </footer>
    </div>

    <script>
        // Print functionality
        function downloadPDF() {
            // In a real implementation, this would trigger server-side PDF generation
            alert('PDF download would be implemented server-side with libraries like DomPDF or Puppeteer (Demo)');
        }

        // Auto-focus for print dialog
        document.addEventListener('DOMContentLoaded', function() {
            // Add print-friendly styling
            document.body.classList.add('pdf-preview');
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>