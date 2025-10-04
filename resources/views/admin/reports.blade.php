@extends('layouts.admin')

@section('title', 'Generate Reports')

@php
    $pageTitle = 'Generate Reports';
    $pageSubtitle = 'Export and analyze examination data for decision making';
@endphp

@section('content')
                <!-- Quick Stats Overview -->
                <div class="stats-grid reports-stats">
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $totalApplicants }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $examCompleted }}</div>
                        <div class="stat-label">Exam Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $admitted }}</div>
                        <div class="stat-label">Admitted</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $passRate }}%</div>
                        <div class="stat-label">Pass Rate</div>
                    </div>
                </div>

                <!-- Primary Report Card -->
                <div class="content-section primary-report-card">
                    <div class="section-content" style="padding: 40px;">
                        <div class="primary-report-layout">
                            <div class="report-icon" aria-hidden="true"></div>
                            <div class="report-content">
                                <h2 class="report-title">Final Applicant Ranking</h2>
                                <p class="report-description">
                                    Comprehensive report containing all applicants ranked by examination scores, 
                                    interview evaluations, and final recommendations. Includes detailed analytics 
                                    and admission recommendations for the Computer Studies Department.
                                </p>
                                <div class="report-meta">
                                    <div class="meta-item"><span class="meta-text">{{ $totalApplicants ?? 145 }} Applicants Included</span></div>
                                    <div class="meta-item"><span class="meta-text">Last Updated: {{ now()->format('M d, Y g:i A') }}</span></div>
                                    <div class="meta-item"><span class="meta-text">Pass Rate: {{ $passRate ?? 78 }}%</span></div>
                                </div>
                            </div>
                            <div class="report-action">
                                <button onclick="generateMainReport()" class="btn-generate-main">
                                    <span class="btn-text">Generate PDF Report</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Filters -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Report Filters</h2>
                        <button onclick="resetFilters()" class="section-action">Reset Filters</button>
                    </div>
                    <div class="section-content" style="padding: 24px 30px;">
                        <form id="reportFiltersForm" class="filters-form">
                            <div class="filters-grid">
                                <div class="filter-group">
                                    <label for="applicantStatus" class="filter-label">Applicant Status</label>
                                    <select id="applicantStatus" name="applicantStatus" class="filter-select">
                                        <option value="all">All Applicants</option>
                                        <option value="recommended">Only Recommended Applicants</option>
                                        <option value="waitlisted">Only Waitlisted Applicants</option>
                                        <option value="not-recommended">Only Not Recommended</option>
                                        <option value="interview-pending">Interview Pending</option>
                                        <option value="exam-completed">Exam Completed</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="scoreRange" class="filter-label">Score Range</label>
                                    <select id="scoreRange" name="scoreRange" class="filter-select">
                                        <option value="all">All Scores</option>
                                        <option value="excellent">Excellent (90-100%)</option>
                                        <option value="good">Good (80-89%)</option>
                                        <option value="satisfactory">Satisfactory (75-79%)</option>
                                        <option value="below-passing">Below Passing (0-74%)</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="dateRange" class="filter-label">Application Period</label>
                                    <select id="dateRange" name="dateRange" class="filter-select">
                                        <option value="all">All Dates</option>
                                        <option value="this-week">This Week</option>
                                        <option value="this-month">This Month</option>
                                        <option value="last-month">Last Month</option>
                                        <option value="custom">Custom Date Range</option>
                                    </select>
                                </div>

                                <div class="filter-group">
                                    <label for="sortBy" class="filter-label">Sort By</label>
                                    <select id="sortBy" name="sortBy" class="filter-select">
                                        <option value="score-desc">Score (Highest First)</option>
                                        <option value="score-asc">Score (Lowest First)</option>
                                        <option value="name-asc">Name (A-Z)</option>
                                        <option value="date-desc">Application Date (Newest)</option>
                                        <option value="date-asc">Application Date (Oldest)</option>
                                        <option value="recommendation">Recommendation Status</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Custom Date Range (hidden by default) -->
                            <div class="custom-date-range" id="customDateRange" style="display: none;">
                                <div class="date-inputs">
                                    <div class="date-group">
                                        <label for="startDate" class="filter-label">Start Date</label>
                                        <input type="date" id="startDate" name="startDate" class="filter-input">
                                    </div>
                                    <div class="date-group">
                                        <label for="endDate" class="filter-label">End Date</label>
                                        <input type="date" id="endDate" name="endDate" class="filter-input">
                                    </div>
                                </div>
                            </div>

                            <div class="filters-actions">
                                <button type="button" onclick="applyFilters()" class="btn-apply-filters">Apply Filters</button>
                                <button type="button" onclick="previewReport()" class="btn-preview">Preview Report</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Additional Reports -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Additional Reports</h2>
                    </div>
                    <div class="section-content" style="padding: 30px;">
                        <div class="additional-reports-grid">
                            <div class="report-card">
                                <div class="report-card-icon" aria-hidden="true"></div>
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Statistical Analysis</h3>
                                    <p class="report-card-description">
                                        Detailed statistics including score distributions, category performance, 
                                        and comparative analysis across different metrics.
                                    </p>
                                </div>
                                <div class="report-card-actions">
                                    <button onclick="generateStatReport()" class="btn-report-action">Generate</button>
                                </div>
                            </div>

                            <div class="report-card">
                                <div class="report-card-icon" aria-hidden="true"></div>
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Interview Summary</h3>
                                    <p class="report-card-description">
                                        Comprehensive interview evaluations, interviewer notes, and 
                                        final recommendations for decision making.
                                    </p>
                                </div>
                                <div class="report-card-actions">
                                    <button onclick="generateInterviewReport()" class="btn-report-action">Generate</button>
                                </div>
                            </div>

                            <div class="report-card">
                                <div class="report-card-icon" aria-hidden="true"></div>
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Question Analytics</h3>
                                    <p class="report-card-description">
                                        Analysis of question difficulty, answer patterns, and performance 
                                        by category to improve future examinations.
                                    </p>
                                </div>
                                <div class="report-card-actions">
                                    <button onclick="generateQuestionReport()" class="btn-report-action">Generate</button>
                                </div>
                            </div>

                            <div class="report-card">
                                <div class="report-card-icon" aria-hidden="true"></div>
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Communication Log</h3>
                                    <p class="report-card-description">
                                        Record of all communications sent to applicants including 
                                        emails, notifications, and system messages.
                                    </p>
                                </div>
                                <div class="report-card-actions">
                                    <button onclick="generateCommReport()" class="btn-report-action">Generate</button>
                                </div>
                            </div>

                            <div class="report-card">
                                <div class="report-card-icon" aria-hidden="true"></div>
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Security Audit</h3>
                                    <p class="report-card-description">
                                        Examination security report including access attempts, 
                                        suspicious activities, and integrity verification.
                                    </p>
                                </div>
                                <div class="report-card-actions">
                                    <button onclick="generateSecurityReport()" class="btn-report-action">Generate</button>
                                </div>
                            </div>

                            <div class="report-card">
                                <div class="report-card-icon" aria-hidden="true"></div>
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Timing Analysis</h3>
                                    <p class="report-card-description">
                                        Examination timing patterns, completion rates, and time 
                                        management analysis for process optimization.
                                    </p>
                                </div>
                                <div class="report-card-actions">
                                    <button onclick="generateTimingReport()" class="btn-report-action">Generate</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report History -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Recent Reports</h2>
                        <button onclick="clearReportHistory()" class="section-action">Clear History</button>
                    </div>
                    <div class="section-content">
                        <table class="data-table reports-history-table">
                            <thead>
                                <tr>
                                    <th>Report Type</th>
                                    <th>Generated By</th>
                                    <th>Date & Time</th>
                                    <th>Filters Applied</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $reportHistory = [
                                        ['type' => 'Final Applicant Ranking', 'user' => 'Dr. Admin', 'date' => now()->format('M d, Y g:i A'), 'filters' => 'All Applicants', 'id' => 1],
                                        ['type' => 'Statistical Analysis', 'user' => 'Prof. Johnson', 'date' => now()->subHours(2)->format('M d, Y g:i A'), 'filters' => 'Recommended Only', 'id' => 2],
                                        ['type' => 'Interview Summary', 'user' => 'Dr. Admin', 'date' => now()->subDay()->format('M d, Y g:i A'), 'filters' => 'Score 80%+', 'id' => 3],
                                        ['type' => 'Question Analytics', 'user' => 'Dr. Smith', 'date' => now()->subDays(2)->format('M d, Y g:i A'), 'filters' => 'All Questions', 'id' => 4],
                                        ['type' => 'Security Audit', 'user' => 'System Admin', 'date' => now()->subDays(3)->format('M d, Y g:i A'), 'filters' => 'All Activities', 'id' => 5],
                                    ];
                                @endphp
                                @foreach($reportHistory as $report)
                                <tr>
                                    <td>
                                        <div class="report-type">
                                            <span class="report-type-name">{{ $report['type'] }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $report['user'] }}</td>
                                    <td>{{ $report['date'] }}</td>
                                    <td>
                                        <span class="filter-badge">{{ $report['filters'] }}</span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button onclick="downloadReport({{ $report['id'] }})" class="action-btn action-btn-download" title="Download Report">Download</button>
                                            <button onclick="viewReport({{ $report['id'] }})" class="action-btn action-btn-view" title="View Report">View</button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection

@push('scripts')
    <!-- Report Preview Modal -->
    <div id="reportPreviewModal" class="modal-overlay" style="display: none;">
        <div class="modal-content report-preview-modal">
            <div class="modal-header">
                <h3>Report Preview</h3>
                <button onclick="closeReportPreview()" class="modal-close">×</button>
            </div>
            <div class="modal-body" id="reportPreviewBody">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button onclick="closeReportPreview()" class="btn-secondary">Close Preview</button>
                <button onclick="downloadPreviewedReport()" class="btn-primary">📄 Download Report</button>
            </div>
        </div>
    </div>

    <script>
        // Report generation functions
        function generateMainReport() {
            const btn = event.target.closest('button');
            const icon = btn.querySelector('.btn-icon');
            const text = btn.querySelector('.btn-text');
            
            btn.disabled = true;
            text.textContent = 'Generating...';
            icon.textContent = '⏳';
            
            setTimeout(() => {
                btn.disabled = false;
                text.textContent = 'Generate PDF Report';
                icon.textContent = '📄';
                
                alert('Final Applicant Ranking report generated successfully! (Demo mode)');
                
                // Add to report history
                addToReportHistory('Final Applicant Ranking', getAppliedFilters());
            }, 2000);
        }

        function applyFilters() {
            const filters = getAppliedFilters();
            console.log('Applied filters:', filters);
            
            // Show loading feedback
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = '🔍 Applying...';
            
            setTimeout(() => {
                btn.disabled = false;
                btn.textContent = '🔍 Apply Filters';
                alert(`Filters applied: ${filters} (Demo mode)`);
            }, 1000);
        }

        function previewReport() {
            const filters = getAppliedFilters();
            
            // Show preview modal with sample content
            const previewBody = document.getElementById('reportPreviewBody');
            previewBody.innerHTML = `
                <div class="report-preview-content">
                    <h4>Report Preview</h4>
                    <p><strong>Applied Filters:</strong> ${filters}</p>
                    <div class="preview-stats">
                        <div class="preview-stat">
                            <span class="stat-label">Total Applicants:</span>
                            <span class="stat-value">67</span>
                        </div>
                        <div class="preview-stat">
                            <span class="stat-label">Average Score:</span>
                            <span class="stat-value">81.5%</span>
                        </div>
                        <div class="preview-stat">
                            <span class="stat-label">Recommended:</span>
                            <span class="stat-value">45</span>
                        </div>
                    </div>
                    <div class="preview-note">
                        <em>This is a preview of the report that will be generated with the selected filters.</em>
                    </div>
                </div>
            `;
            
            document.getElementById('reportPreviewModal').style.display = 'flex';
        }

        function closeReportPreview() {
            document.getElementById('reportPreviewModal').style.display = 'none';
        }

        function downloadPreviewedReport() {
            alert('Report downloaded successfully! (Demo mode)');
            closeReportPreview();
        }

        function getAppliedFilters() {
            const status = document.getElementById('applicantStatus').value;
            const score = document.getElementById('scoreRange').value;
            const date = document.getElementById('dateRange').value;
            const sort = document.getElementById('sortBy').value;
            
            const filters = [];
            if (status !== 'all') filters.push(`Status: ${status}`);
            if (score !== 'all') filters.push(`Score: ${score}`);
            if (date !== 'all') filters.push(`Period: ${date}`);
            filters.push(`Sort: ${sort}`);
            
            return filters.join(', ');
        }

        function resetFilters() {
            document.getElementById('applicantStatus').value = 'all';
            document.getElementById('scoreRange').value = 'all';
            document.getElementById('dateRange').value = 'all';
            document.getElementById('sortBy').value = 'score-desc';
            document.getElementById('customDateRange').style.display = 'none';
        }

        // Additional report functions
        function generateStatReport() {
            alert('Statistical Analysis report generated! (Demo mode)');
            addToReportHistory('Statistical Analysis', 'All Data');
        }

        function generateInterviewReport() {
            alert('Interview Summary report generated! (Demo mode)');
            addToReportHistory('Interview Summary', 'All Interviews');
        }

        function generateQuestionReport() {
            alert('Question Analytics report generated! (Demo mode)');
            addToReportHistory('Question Analytics', 'All Questions');
        }

        function generateCommReport() {
            alert('Communication Log report generated! (Demo mode)');
            addToReportHistory('Communication Log', 'All Messages');
        }

        function generateSecurityReport() {
            alert('Security Audit report generated! (Demo mode)');
            addToReportHistory('Security Audit', 'All Activities');
        }

        function generateTimingReport() {
            alert('Timing Analysis report generated! (Demo mode)');
            addToReportHistory('Timing Analysis', 'All Sessions');
        }

        // Report history functions
        function addToReportHistory(type, filters) {
            console.log(`Added to history: ${type} with filters: ${filters}`);
            // In a real application, this would update the database
        }

        function downloadReport(id) {
            alert(`Downloading report #${id} (Demo mode)`);
        }

        function viewReport(id) {
            alert(`Viewing report #${id} (Demo mode)`);
        }

        function clearReportHistory() {
            if (confirm('Are you sure you want to clear all report history?')) {
                alert('Report history cleared! (Demo mode)');
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Show/hide custom date range
            document.getElementById('dateRange').addEventListener('change', function(e) {
                const customRange = document.getElementById('customDateRange');
                if (e.target.value === 'custom') {
                    customRange.style.display = 'block';
                } else {
                    customRange.style.display = 'none';
                }
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeReportPreview();
            }
        });
    </script>

    <style>
        /* Additional styles for reports page */
        .reports-stats {
            margin-bottom: 30px;
        }

        /* Primary Report Card */
        .primary-report-card {
            margin-bottom: 30px;
            border: 3px solid var(--yellow-primary);
            background: linear-gradient(135deg, var(--yellow-light) 0%, var(--white) 100%);
        }

        .primary-report-layout {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 30px;
            align-items: center;
        }

        .report-icon {
            text-align: center;
        }

        .icon-container {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid var(--yellow-primary);
            box-shadow: 0 8px 20px rgba(128, 0, 32, 0.3);
        }

        .report-emoji {
            font-size: 32px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .report-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--maroon-primary);
            margin: 0 0 12px 0;
        }

        .report-description {
            font-size: 16px;
            color: var(--text-gray);
            line-height: 1.6;
            margin: 0 0 20px 0;
        }

        .report-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--maroon-primary);
        }

        .meta-icon {
            font-size: 16px;
        }

        .btn-generate-main {
            padding: 16px 32px;
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            color: var(--white);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 6px 16px rgba(128, 0, 32, 0.3);
        }

        .btn-generate-main:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--yellow-primary) 0%, var(--yellow-dark) 100%);
            color: var(--maroon-primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(128, 0, 32, 0.4);
        }

        .btn-generate-main:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Filters Form */
        .filters-form {
            display: grid;
            gap: 24px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-label {
            font-weight: 600;
            color: var(--maroon-primary);
            font-size: 14px;
        }

        .filter-select, .filter-input {
            padding: 12px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .filter-select:focus, .filter-input:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        .custom-date-range {
            padding: 20px;
            background: var(--light-gray);
            border-radius: 8px;
            border: 2px solid var(--border-gray);
        }

        .date-inputs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
        }

        .filters-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            padding-top: 20px;
            border-top: 1px solid var(--border-gray);
        }

        .btn-apply-filters, .btn-preview {
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-apply-filters {
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            color: var(--white);
        }

        .btn-apply-filters:hover {
            background: linear-gradient(135deg, var(--yellow-primary) 0%, var(--yellow-dark) 100%);
            color: var(--maroon-primary);
        }

        .btn-preview {
            background: var(--white);
            color: var(--maroon-primary);
            border: 2px solid var(--border-gray);
        }

        .btn-preview:hover {
            background: var(--yellow-light);
            border-color: var(--yellow-primary);
        }

        /* Additional Reports Grid */
        .additional-reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }

        .report-card {
            background: var(--white);
            border: 2px solid var(--border-gray);
            border-radius: 12px;
            padding: 24px;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .report-card:hover {
            border-color: var(--yellow-primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .report-card-icon {
            font-size: 32px;
            text-align: center;
        }

        .report-card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin: 0 0 8px 0;
            text-align: center;
        }

        .report-card-description {
            font-size: 14px;
            color: var(--text-gray);
            line-height: 1.5;
            margin: 0;
            text-align: center;
            flex: 1;
        }

        .report-card-actions {
            text-align: center;
        }

        .btn-report-action {
            padding: 10px 20px;
            background: var(--yellow-light);
            color: var(--maroon-primary);
            border: 2px solid var(--yellow-primary);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            font-size: 14px;
        }

        .btn-report-action:hover {
            background: var(--yellow-primary);
            transform: translateY(-1px);
        }

        /* Report History Table */
        .reports-history-table {
            font-size: 14px;
        }

        .report-type-name {
            font-weight: 600;
            color: var(--maroon-primary);
        }

        .filter-badge {
            background: var(--yellow-light);
            color: var(--maroon-primary);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }

        .action-btn-download {
            background: var(--yellow-light);
            color: var(--maroon-primary);
            border: 1px solid var(--yellow-primary);
        }

        .action-btn-download:hover {
            background: var(--yellow-primary);
        }

        /* Report Preview Modal */
        .report-preview-modal {
            max-width: 700px;
            width: 90%;
        }

        .report-preview-content {
            padding: 20px;
        }

        .preview-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }

        .preview-stat {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .preview-note {
            margin-top: 20px;
            padding: 16px;
            background: var(--yellow-light);
            border-radius: 8px;
            text-align: center;
        }

        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            color: var(--white);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--yellow-primary) 0%, var(--yellow-dark) 100%);
            color: var(--maroon-primary);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--maroon-primary);
            border: 2px solid var(--border-gray);
        }

        .btn-secondary:hover {
            background: var(--yellow-light);
            border-color: var(--yellow-primary);
        }

        .logout-link {
            background: none;
            border: none;
            width: 100%;
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
            font-size: 14px;
            cursor: pointer;
        }

        .logout-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--yellow-primary);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .primary-report-layout {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 20px;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .additional-reports-grid {
                grid-template-columns: 1fr;
            }

            .report-meta {
                flex-direction: column;
                gap: 12px;
            }

            .filters-actions {
                flex-direction: column;
            }

            .date-inputs {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush