@extends('layouts.admin')

@section('title', 'Generate Reports')

@php
    $pageTitle = 'Generate Reports';
    $pageSubtitle = 'Export and analyze examination data for decision making';
@endphp

@section('content')
                <!-- Primary Reports Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">📊 Primary Reports</h2>
                        <p class="section-subtitle">Official EVSU exports for entrance examination results</p>
                    </div>
                    <div class="section-content" style="padding: 30px;">
                        <div class="primary-reports-grid">
                            <!-- EVSU Entrance Results XLSX -->
                            <div class="primary-report-card">
                                <div class="report-card-header">
                                    <div class="report-card-icon">📊</div>
                                    <h3 class="report-card-title">EVSU Entrance Results</h3>
                                </div>
                                <p class="report-card-description">
                                    Official EVSU-formatted export with applicants ranked by Overall Admission Rating
                                    (60% UEE, 30% GWA, 10% Interview/Skill). Complete scoring breakdown included.
                                </p>
                                <form id="evsuResultsForm" class="export-form">
                                    <input type="hidden" name="status" value="interview-completed">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="evsu_limit" class="filter-label">Top N Applicants</label>
                                            <input id="evsu_limit" name="limit" type="number" min="1" step="1" value="120" class="filter-input">
                                        </div>
                                        <div class="form-group">
                                            <label for="evsu_sort" class="filter-label">Sort Order</label>
                                            <select id="evsu_sort" name="sort" class="filter-select">
                                                <option value="overall_desc">Overall Rating (High → Low)</option>
                                                <option value="overall_asc">Overall Rating (Low → High)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 10px;">
                                        <button type="button" onclick="generateEVSUResults('xlsx')" class="btn-primary-export">📥 Export XLSX</button>
                                        <button type="button" onclick="generateEVSUResults('pdf')" class="btn-primary-export" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">📄 Export PDF</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Qualifiers List -->
                            <div class="primary-report-card">
                                <div class="report-card-header">
                                    <div class="report-card-icon">📄</div>
                                    <h3 class="report-card-title">Qualifiers List</h3>
                                </div>
                                <p class="report-card-description">
                                    Official list of qualified applicants based on overall rating, sorted alphabetically.
                                    Available in Word (editable) or PDF format for official submission.
                                </p>
                                <div class="export-form">
                                    <div class="form-group">
                                        <label for="qualifiersSlots" class="filter-label">Number of Slots (Required) *</label>
                                        <input type="number" 
                                               id="qualifiersSlots" 
                                               name="qualifiersSlots" 
                                               class="filter-input" 
                                               placeholder="e.g., 112"
                                               min="1"
                                               max="500"
                                               required>
                                        <span class="help-text">Enter the number of available slots for qualified applicants</span>
                                    </div>
                                    <div style="display: flex; gap: 10px;">
                                        <button onclick="generateQualifiersReport('docx')" class="btn-primary-export">📥 Generate DOCX</button>
                                        <button onclick="generateQualifiersReport('pdf')" class="btn-primary-export" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">📄 Generate PDF</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collapsible: Report Filters -->
                <div class="content-section collapsible-section">
                    <div class="section-header" onclick="toggleSection('filtersSection')">
                        <div>
                            <h2 class="section-title">🔍 Advanced Filters</h2>
                            <p class="section-subtitle">Filter criteria for PDF reports (optional)</p>
                        </div>
                        <button class="toggle-btn" id="filtersToggle">
                            <span class="toggle-icon">▼</span>
                        </button>
                    </div>
                    <div class="section-content collapsible-content" id="filtersSection" style="display: none; padding: 24px 30px;">
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
                                <button type="button" onclick="resetFilters()" class="btn-secondary">Reset Filters</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Collapsible: Student Information Reports -->
                <div class="content-section collapsible-section">
                    <div class="section-header" onclick="toggleSection('studentInfoReportsSection')">
                        <div>
                            <h2 class="section-title">📊 Student Information Reports</h2>
                            <p class="section-subtitle">Reports based on student basic information data</p>
                        </div>
                        <button class="toggle-btn" id="studentInfoReportsToggle">
                            <span class="toggle-icon">▼</span>
                        </button>
                    </div>
                    <div class="section-content collapsible-content" id="studentInfoReportsSection" style="display: none; padding: 30px;">
                        <div class="additional-reports-grid">
                            <!-- Geographic Performance Report -->
                            <div class="report-card">
                                <div class="report-card-icon" aria-hidden="true">🗺️</div>
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Geographic Performance Report</h3>
                                    <p class="report-card-description">
                                        Performance analysis by geographic location including applicants by province, 
                                        average exam scores by region, and top performing areas.
                                    </p>
                                </div>
                                <form class="report-filters-form">
                                    <div class="filter-group-inline">
                                        <label class="filter-label">Province</label>
                                        <select id="geoProvince" class="filter-select-sm">
                                            <option value="all">All Provinces</option>
                                            <option value="Leyte">Leyte</option>
                                            <option value="Southern Leyte">Southern Leyte</option>
                                            <option value="Biliran">Biliran</option>
                                            <option value="Samar">Samar</option>
                                            <option value="Eastern Samar">Eastern Samar</option>
                                            <option value="Northern Samar">Northern Samar</option>
                                        </select>
                                    </div>
                                    <div class="filter-group-inline">
                                        <label class="filter-label">Status</label>
                                        <select id="geoStatus" class="filter-select-sm">
                                            <option value="all">All Status</option>
                                            <option value="exam-completed">Exam Completed</option>
                                            <option value="interview-completed">Interview Completed</option>
                                            <option value="admitted">Admitted</option>
                                        </select>
                                    </div>
                                </form>
                                <div class="report-card-actions">
                                    <button onclick="generateGeographicReport()" class="btn-report-action">📄 Generate PDF</button>
                                </div>
                            </div>

                            <!-- Strand Distribution Report -->
                            <div class="report-card">
                                <div class="report-card-icon" aria-hidden="true">🎓</div>
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Strand Distribution Report</h3>
                                    <p class="report-card-description">
                                        Senior High School strand analysis showing distribution across ABM, STEM, HUMSS, TVL, 
                                        and Others with percentages and trends.
                                    </p>
                                </div>
                                <form class="report-filters-form">
                                    <div class="filter-group-inline">
                                        <label class="filter-label">Strand</label>
                                        <select id="strandFilter" class="filter-select-sm">
                                            <option value="all">All Strands</option>
                                            <option value="ABM">ABM</option>
                                            <option value="STEM">STEM</option>
                                            <option value="HUMSS">HUMSS</option>
                                            <option value="TVL">TVL</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>
                                    <div class="filter-group-inline">
                                        <label class="filter-label">Status</label>
                                        <select id="strandStatus" class="filter-select-sm">
                                            <option value="all">All Status</option>
                                            <option value="exam-completed">Exam Completed</option>
                                            <option value="interview-completed">Interview Completed</option>
                                            <option value="admitted">Admitted</option>
                                        </select>
                                    </div>
                                </form>
                                <div class="report-card-actions">
                                    <button onclick="generateStrandReport()" class="btn-report-action">📄 Generate PDF</button>
                                </div>
                            </div>

                            <!-- Demographic Overview Report -->
                            <div class="report-card">
                                <div class="report-card-icon" aria-hidden="true">👥</div>
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Demographic Overview Report</h3>
                                    <p class="report-card-description">
                                        Comprehensive demographic breakdown including age distribution, gender, civil status, 
                                        applicant type, and PWD representation statistics.
                                    </p>
                                </div>
                                <form class="report-filters-form">
                                    <div class="filter-group-inline">
                                        <label class="filter-label">Age Range</label>
                                        <select id="demoAgeRange" class="filter-select-sm">
                                            <option value="all">All Ages</option>
                                            <option value="16-20">16-20 years</option>
                                            <option value="21-25">21-25 years</option>
                                            <option value="26-30">26-30 years</option>
                                            <option value="31+">31+ years</option>
                                        </select>
                                    </div>
                                    <div class="filter-group-inline">
                                        <label class="filter-label">Status</label>
                                        <select id="demoStatus" class="filter-select-sm">
                                            <option value="all">All Status</option>
                                            <option value="exam-completed">Exam Completed</option>
                                            <option value="interview-completed">Interview Completed</option>
                                            <option value="admitted">Admitted</option>
                                        </select>
                                    </div>
                                </form>
                                <div class="report-card-actions">
                                    <button onclick="generateDemographicReport()" class="btn-report-action">📄 Generate PDF</button>
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
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: #6B7280;">
                                        <p>No reports generated yet. Generate your first report using the options above.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection

@push('modals')
    <!-- Report Preview Modal -->
    <div id="reportPreviewModal" class="modal-overlay">
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
                <button onclick="downloadPreviewedReport()" class="btn-primary"> Download Report</button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        // Get CSRF token with error handling
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfTokenMeta) {
            console.error('CSRF token meta tag not found!');
        }
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.content : '';

        // Utility function to show notifications
        function showNotification(message, type = 'success') {
            // You can use a toast library or create a simple notification
            console.log(`[${type.toUpperCase()}] ${message}`);
            alert(message);
        }

        // Get filters as object
        function getFiltersObject() {
            const filters = {
                applicantStatus: document.getElementById('applicantStatus').value,
                scoreRange: document.getElementById('scoreRange').value,
                dateRange: document.getElementById('dateRange').value,
                sortBy: document.getElementById('sortBy').value,
            };

            if (filters.dateRange === 'custom') {
                filters.startDate = document.getElementById('startDate').value;
                filters.endDate = document.getElementById('endDate').value;
            }

            return filters;
        }

        // Get filters as readable string
        function getAppliedFilters() {
            const filters = getFiltersObject();
            const parts = [];
            
            if (filters.applicantStatus !== 'all') parts.push(`Status: ${filters.applicantStatus}`);
            if (filters.scoreRange !== 'all') parts.push(`Score: ${filters.scoreRange}`);
            if (filters.dateRange !== 'all') parts.push(`Period: ${filters.dateRange}`);
            parts.push(`Sort: ${filters.sortBy}`);
            
            return parts.join(', ');
        }

        // Generate report function
        async function generateReport(type, buttonElement = null) {
            const filters = getFiltersObject();
            
            if (buttonElement) {
                buttonElement.disabled = true;
                const originalText = buttonElement.textContent;
                buttonElement.textContent = 'Generating...';
            }

            try {
                const response = await fetch('/admin/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ type, filters }),
                });

                const text = await response.text();
                const data = JSON.parse(text);

                if (data.success) {
                    showNotification(`${data.report.type} generated successfully! Click download in Recent Reports to get the file.`);
                    loadReportHistory();
                    
                    // Offer to download immediately
                    if (confirm('Report generated successfully! Download now?')) {
                        window.location.href = `/admin/reports/${data.report.id}/download`;
                    }
                } else {
                    showNotification(data.message || 'Failed to generate report', 'error');
                }
            } catch (error) {
                console.error('Error generating report:', error);
                showNotification('Error generating report. Please try again.', 'error');
            } finally {
                if (buttonElement) {
                    buttonElement.disabled = false;
                    buttonElement.textContent = buttonElement.dataset.originalText || 'Generate';
                }
            }
        }

        // Main report generation
        function generateMainReport() {
            const btn = event.target.closest('button');
            btn.dataset.originalText = btn.textContent;
            generateReport('final_ranking', btn);
        }

        // Additional report functions
        function generateStatReport() {
            const btn = event.target.closest('button');
            btn.dataset.originalText = btn.textContent;
            generateReport('statistical_analysis', btn);
        }

        function generateInterviewReport() {
            const btn = event.target.closest('button');
            btn.dataset.originalText = btn.textContent;
            generateReport('interview_summary', btn);
        }

        // Qualifiers List Report (Word DOCX)
        async function generateQualifiersReport(format = 'docx') {
            const btn = event.target.closest('button');
            const slotsInput = document.getElementById('qualifiersSlots');
            const slots = parseInt(slotsInput.value);

            // Validate slots input
            if (!slots || slots < 1 || slots > 500) {
                showNotification('Please enter a valid number of slots (1-500)', 'error');
                slotsInput.focus();
                return;
            }

            // Get base filters and add slots
            const filters = getFiltersObject();
            filters.slots = slots;

            btn.dataset.originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Generating...';

            // Determine report type based on format
            const reportType = format === 'pdf' ? 'qualifiers_list_pdf' : 'qualifiers_list';

            try {
                const response = await fetch('/admin/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        type: reportType,
                        filters: filters
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(`Qualifiers list ${format.toUpperCase()} generated successfully!`, 'success');
                    
                    // Refresh report history
                    await loadReportHistory();
                    
                    // Download the report
                    if (data.report && data.report.id) {
                        window.location.href = `/admin/reports/${data.report.id}/download`;
                    }
                } else {
                    showNotification(data.message || 'Failed to generate report', 'error');
                }
            } catch (error) {
                console.error('Error generating qualifiers report:', error);
                showNotification('Failed to generate report. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = btn.dataset.originalText || `Generate ${format.toUpperCase()}`;
            }
        }

        async function generateEVSUResults(format = 'xlsx') {
            const btn = event.target.closest('button');
            const limitInput = document.getElementById('evsu_limit');
            const sortInput = document.getElementById('evsu_sort');
            
            const limit = parseInt(limitInput.value) || 120;
            const sort = sortInput.value || 'overall_desc';

            // Get base filters and add EVSU-specific parameters
            const filters = getFiltersObject();
            filters.limit = limit;
            filters.sort = sort;
            filters.status = 'interview-completed';

            btn.dataset.originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Generating...';

            // Determine report type based on format
            const reportType = format === 'pdf' ? 'evsu_results_pdf' : 'evsu_results';

            try {
                const response = await fetch('/admin/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        type: reportType,
                        filters: filters
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification(`EVSU Results ${format.toUpperCase()} generated successfully!`, 'success');
                    
                    // Refresh report history
                    await loadReportHistory();
                    
                    // Download the report
                    if (data.report && data.report.id) {
                        window.location.href = `/admin/reports/${data.report.id}/download`;
                    }
                } else {
                    showNotification(data.message || 'Failed to generate report', 'error');
                }
            } catch (error) {
                console.error('Error generating EVSU results:', error);
                showNotification('Failed to generate report. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = btn.dataset.originalText || `Export ${format.toUpperCase()}`;
            }
        }

        // Student Information Reports
        async function generateGeographicReport() {
            const btn = event.target.closest('button');
            const provinceFilter = document.getElementById('geoProvince').value;
            const statusFilter = document.getElementById('geoStatus').value;

            const filters = {
                province: provinceFilter,
                status: statusFilter
            };

            btn.dataset.originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Generating...';

            try {
                const response = await fetch('/admin/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        type: 'geographic_performance',
                        filters: filters
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('Geographic Performance Report generated successfully!', 'success');
                    await loadReportHistory();
                    
                    if (data.report && data.report.id) {
                        window.location.href = `/admin/reports/${data.report.id}/download`;
                    }
                } else {
                    showNotification(data.message || 'Failed to generate report', 'error');
                }
            } catch (error) {
                console.error('Error generating geographic report:', error);
                showNotification('Failed to generate report. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = btn.dataset.originalText || '📄 Generate PDF';
            }
        }

        async function generateStrandReport() {
            const btn = event.target.closest('button');
            const strandFilter = document.getElementById('strandFilter').value;
            const statusFilter = document.getElementById('strandStatus').value;

            const filters = {
                strand: strandFilter,
                status: statusFilter
            };

            btn.dataset.originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Generating...';

            try {
                const response = await fetch('/admin/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        type: 'strand_distribution',
                        filters: filters
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('Strand Distribution Report generated successfully!', 'success');
                    await loadReportHistory();
                    
                    if (data.report && data.report.id) {
                        window.location.href = `/admin/reports/${data.report.id}/download`;
                    }
                } else {
                    showNotification(data.message || 'Failed to generate report', 'error');
                }
            } catch (error) {
                console.error('Error generating strand report:', error);
                showNotification('Failed to generate report. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = btn.dataset.originalText || '📄 Generate PDF';
            }
        }

        async function generateDemographicReport() {
            const btn = event.target.closest('button');
            const ageRangeFilter = document.getElementById('demoAgeRange').value;
            const statusFilter = document.getElementById('demoStatus').value;

            const filters = {
                age_range: ageRangeFilter,
                status: statusFilter
            };

            btn.dataset.originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Generating...';

            try {
                const response = await fetch('/admin/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        type: 'demographic_overview',
                        filters: filters
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('Demographic Overview Report generated successfully!', 'success');
                    await loadReportHistory();
                    
                    if (data.report && data.report.id) {
                        window.location.href = `/admin/reports/${data.report.id}/download`;
                    }
                } else {
                    showNotification(data.message || 'Failed to generate report', 'error');
                }
            } catch (error) {
                console.error('Error generating demographic report:', error);
                showNotification('Failed to generate report. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = btn.dataset.originalText || '📄 Generate PDF';
            }
        }

        // Preview report
        async function previewReport() {
            console.log('Preview button clicked');
            
            if (!csrfToken) {
                alert('CSRF token is missing. Please refresh the page.');
                return;
            }
            
            const filters = getFiltersObject();
            const modal = document.getElementById('reportPreviewModal');
            const previewBody = document.getElementById('reportPreviewBody');
            
            if (!modal || !previewBody) {
                console.error('Modal elements not found!', { modal, previewBody });
                alert('Modal elements not found. Please refresh the page.');
                return;
            }
            
            // Show modal with loading state
            previewBody.innerHTML = '<p style="text-align: center; padding: 20px;">Loading preview...</p>';
            modal.classList.add('active', 'show');
            modal.style.display = 'flex';
            modal.style.zIndex = '999999';
            document.body.classList.add('modal-open');
            
            // Debug: Check if modal is visible
            setTimeout(() => {
                const computedStyle = window.getComputedStyle(modal);
                const rect = modal.getBoundingClientRect();
                console.log('Modal computed styles:', {
                    display: computedStyle.display,
                    zIndex: computedStyle.zIndex,
                    position: computedStyle.position,
                    visibility: computedStyle.visibility,
                    opacity: computedStyle.opacity,
                    top: rect.top,
                    left: rect.left,
                    width: rect.width,
                    height: rect.height
                });
                console.log('Is modal in viewport?', rect.top >= 0 && rect.left >= 0);
            }, 100);

            try {
                console.log('Fetching preview with filters:', filters);
                
                const response = await fetch('/admin/reports/preview', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ 
                        type: 'final_ranking',
                        filters 
                    }),
                });

                console.log('Response status:', response.status);
                
                const raw = await response.text();
                console.log('Response text (first 200 chars):', raw.substring(0, 200));
                
                let result;
                try {
                    result = JSON.parse(raw);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Raw response:', raw);
                    previewBody.innerHTML = `<p style="text-align: center; padding: 20px; color: red;">Server returned invalid response. Status: ${response.status}<br>Please check console for details.</p>`;
                    return;
                }

                if (result.success) {
                    const data = result.data;
                    previewBody.innerHTML = `
                        <div class="report-preview-content">
                            <h4>Report Preview</h4>
                            <p><strong>Applied Filters:</strong> ${getAppliedFilters()}</p>
                            <div class="preview-stats">
                                <div class="preview-stat">
                                    <span class="stat-label">Total Applicants:</span>
                                    <span class="stat-value">${data.total_applicants || 0}</span>
                                </div>
                                <div class="preview-stat">
                                    <span class="stat-label">Average Score:</span>
                                    <span class="stat-value">${data.average_score || 0}%</span>
                                </div>
                                <div class="preview-stat">
                                    <span class="stat-label">Recommended:</span>
                                    <span class="stat-value">${data.recommended || 0}</span>
                                </div>
                            </div>
                            <div class="preview-note">
                                <em>This is a preview of the report that will be generated with the selected filters.</em>
                            </div>
                        </div>
                    `;
                } else {
                    previewBody.innerHTML = `<p style="text-align: center; padding: 20px; color: red;">Error: ${result.message || 'Failed to load preview'}</p>`;
                }
            } catch (error) {
                console.error('Error loading preview:', error);
                previewBody.innerHTML = `<p style="text-align: center; padding: 20px; color: red;">Error: ${error.message}<br>Please check console for details.</p>`;
            }
        }

        function closeReportPreview() {
            const modal = document.getElementById('reportPreviewModal');
            modal.classList.remove('active', 'show');
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
        }

        function downloadPreviewedReport() {
            closeReportPreview();
            generateMainReport();
        }

        // Load report history
        async function loadReportHistory() {
            try {
                const response = await fetch('/admin/reports/history', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const text = await response.text();
                const data = JSON.parse(text);

                if (data.success) {
                    if (data.reports.length > 0) {
                        updateReportHistoryTable(data.reports);
                    } else {
                        // Show empty state
                        const tbody = document.querySelector('.reports-history-table tbody');
                        if (tbody) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: #6B7280;">
                                        <p>No reports generated yet. Generate your first report using the options above.</p>
                                    </td>
                                </tr>
                            `;
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading report history:', error);
            }
        }

        // Update report history table
        function updateReportHistoryTable(reports) {
            const tbody = document.querySelector('.reports-history-table tbody');
            if (!tbody) return;

            tbody.innerHTML = reports.map(report => `
                <tr>
                    <td>
                        <div class="report-type">
                            <span class="report-type-name">${report.type}</span>
                        </div>
                    </td>
                    <td>${report.generated_by}</td>
                    <td>${report.created_at}</td>
                    <td>
                        <span class="filter-badge">${report.file_size}</span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button onclick="downloadReport(${report.id})" class="action-btn action-btn-download" title="Download Report">Download</button>
                            <button onclick="deleteReport(${report.id})" class="action-btn action-btn-delete" title="Delete Report">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // Download report
        function downloadReport(id) {
            console.log('Download report clicked, ID:', id);
            if (!id) {
                alert('Invalid report ID');
                return;
            }
            
            // Show loading message
            const btn = event.target;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Downloading...';
            
            // Direct download - let the controller handle file existence check
            window.location.href = `/admin/reports/${id}/download`;
            
            // Re-enable button after a short delay
            setTimeout(() => {
                btn.disabled = false;
                btn.textContent = originalText;
            }, 1000);
        }

        // Delete report
        async function deleteReport(id) {
            if (!confirm('Are you sure you want to delete this report?')) {
                return;
            }

            try {
                const response = await fetch(`/admin/reports/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                const text = await response.text();
                const data = JSON.parse(text);

                if (data.success) {
                    showNotification('Report deleted successfully!');
                    loadReportHistory();
                } else {
                    showNotification(data.message || 'Failed to delete report', 'error');
                }
            } catch (error) {
                console.error('Error deleting report:', error);
                showNotification('Error deleting report. Please try again.', 'error');
            }
        }

        function viewReport(id) {
            downloadReport(id);
        }

        function clearReportHistory() {
            if (confirm('Are you sure you want to clear all report history? This will delete all generated reports.')) {
                showNotification('Bulk delete functionality coming soon!', 'info');
            }
        }

        function applyFilters() {
            const filters = getAppliedFilters();
            showNotification(`Filters configured: ${filters}`, 'info');
        }

        function resetFilters() {
            document.getElementById('applicantStatus').value = 'all';
            document.getElementById('scoreRange').value = 'all';
            document.getElementById('dateRange').value = 'all';
            document.getElementById('sortBy').value = 'score-desc';
            document.getElementById('customDateRange').style.display = 'none';
        }

        // Toggle collapsible sections
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const toggleBtn = event.currentTarget.querySelector('.toggle-icon');
            
            if (section.style.display === 'none' || section.style.display === '') {
                section.style.display = 'block';
                if (toggleBtn) toggleBtn.textContent = '▲';
            } else {
                section.style.display = 'none';
                if (toggleBtn) toggleBtn.textContent = '▼';
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Reports page loaded');
            console.log('CSRF Token:', csrfToken ? 'Present' : 'MISSING');
            console.log('Preview modal element:', document.getElementById('reportPreviewModal') ? 'Found' : 'NOT FOUND');
            
            // Load report history on page load
            loadReportHistory();

            // Show/hide custom date range
            const dateRangeSelect = document.getElementById('dateRange');
            if (dateRangeSelect) {
                dateRangeSelect.addEventListener('change', function(e) {
                    const customRange = document.getElementById('customDateRange');
                    if (customRange) {
                        if (e.target.value === 'custom') {
                            customRange.style.display = 'block';
                        } else {
                            customRange.style.display = 'none';
                        }
                    }
                });
            }
        });

        // Close modal when clicking outside or pressing ESC
        window.addEventListener('click', function(e) {
            if (e.target.id === 'reportPreviewModal' || e.target.classList.contains('modal-overlay')) {
                closeReportPreview();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReportPreview();
            }
        });
    </script>

    <style>
        /* Additional styles for reports page */

        /* Section Subtitle */
        .section-subtitle {
            font-size: 14px;
            color: var(--text-gray);
            margin: 4px 0 0 0;
            font-weight: 400;
        }

        /* Primary Reports Grid */
        .primary-reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
        }

        .primary-report-card {
            background: linear-gradient(135deg, #FFF9E6 0%, var(--white) 100%);
            border: 3px solid var(--yellow-primary);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
        }

        .primary-report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .report-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .report-card-header .report-card-icon {
            font-size: 32px;
        }

        .report-card-header .report-card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--maroon-primary);
            margin: 0;
        }

        .report-card-description {
            font-size: 14px;
            color: var(--text-gray);
            line-height: 1.6;
            margin: 0 0 20px 0;
        }

        .export-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .btn-primary-export {
            padding: 14px 28px;
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            color: var(--white);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(128, 0, 32, 0.3);
        }

        .btn-primary-export:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--yellow-primary) 0%, var(--yellow-dark) 100%);
            color: var(--maroon-primary);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(128, 0, 32, 0.4);
        }

        /* Collapsible Sections */
        .collapsible-section .section-header {
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
            transition: var(--transition);
        }

        .collapsible-section .section-header:hover {
            background: rgba(255, 215, 0, 0.05);
        }

        .toggle-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            transition: var(--transition);
        }

        .toggle-icon {
            font-size: 18px;
            color: var(--maroon-primary);
            font-weight: bold;
        }

        .collapsible-content {
            transition: max-height 0.3s ease-out;
        }

        /* Coming Soon Badge */
        .badge-coming-soon {
            display: inline-block;
            background: #FFA500;
            color: white;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
            margin-left: 8px;
            vertical-align: middle;
        }

        /* Disabled Report Cards */
        .report-card-disabled {
            opacity: 0.6;
            pointer-events: none;
        }

        .report-card-disabled .report-card-title {
            color: #999;
        }

        .report-card-disabled button {
            opacity: 0.5;
            cursor: not-allowed;
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

        .action-btn-delete {
            background: rgba(220, 38, 38, 0.1);
            color: #DC2626;
            border: 1px solid #DC2626;
        }

        .action-btn-delete:hover {
            background: #DC2626;
            color: var(--white);
        }

        /* Report Preview Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999999 !important;
            pointer-events: auto;
        }

        .modal-overlay.active {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .modal-content {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            z-index: 1000000;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-bottom: 2px solid var(--border-gray);
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 32px;
            color: var(--text-gray);
            cursor: pointer;
            line-height: 1;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .modal-close:hover {
            color: var(--maroon-primary);
            transform: scale(1.1);
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 30px;
            border-top: 1px solid var(--border-gray);
        }

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

        /* Report Filters Form Styles */
        .report-filters-form {
            margin: 16px 0;
            padding: 16px;
            background: var(--light-gray);
            border-radius: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .filter-group-inline {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            min-width: 150px;
        }

        .filter-group-inline .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--maroon-primary);
        }

        .filter-select-sm {
            padding: 8px 12px;
            border: 2px solid var(--border-gray);
            border-radius: 6px;
            font-size: 13px;
            transition: var(--transition);
            background: var(--white);
        }

        .filter-select-sm:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
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

            .report-filters-form {
                flex-direction: column;
            }

            .filter-group-inline {
                min-width: 100%;
            }
        }
    </style>
@endpush