<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Manage Applicants - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Admin Dashboard CSS -->
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
</head>
<body class="admin-page">
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo">
                    <div>
                        <h2 class="sidebar-title">EnrollAssess</h2>
                        <p class="sidebar-subtitle">Admin Portal</p>
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.questions') }}" class="nav-link">
                        <span class="nav-icon">❓</span>
                        <span class="nav-text">Questions</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.applicants') }}" class="nav-link active">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">Applicants</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.reports') }}" class="nav-link">
                        <span class="nav-icon">📈</span>
                        <span class="nav-text">Reports</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-link">
                        <span class="nav-icon">🚪</span>
                        <span class="nav-text">Logout</span>
                    </button>
                </form>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <div class="main-header">
                <div class="header-left">
                    <h1>Applicant Management</h1>
                    <p class="header-subtitle">Track and manage all BSIT entrance examination applicants</p>
                </div>
                <div class="header-right">
                    <div class="header-time">
                        🕐 {{ now()->format('M d, Y g:i A') }}
                    </div>
                    <div class="header-user">
                        {{ auth()->user()->name ?? 'Dr. Admin' }}
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Statistics Overview -->
                <div class="stats-grid applicants-stats">
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-value">{{ $totalApplicants ?? 145 }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-value">{{ $examCompleted ?? 89 }}</div>
                        <div class="stat-label">Exam Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-value">{{ $examPassed ?? 67 }}</div>
                        <div class="stat-label">Exam Passed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-value">{{ $interviewScheduled ?? 23 }}</div>
                        <div class="stat-label">Interview Scheduled</div>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="content-section" style="margin-bottom: 20px;">
                    <div class="section-content" style="padding: 20px 30px;">
                        <div class="applicants-toolbar">
                            <div class="search-filter-group">
                                <div class="search-box">
                                    <input type="text" placeholder="Search applicants..." class="search-input" id="searchInput">
                                    <button class="search-btn">🔍</button>
                                </div>
                                <select class="filter-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending Exam</option>
                                    <option value="in-progress">Exam In Progress</option>
                                    <option value="completed">Exam Completed</option>
                                    <option value="passed">Exam Passed</option>
                                    <option value="failed">Exam Failed</option>
                                    <option value="interview-scheduled">Interview Scheduled</option>
                                    <option value="interview-completed">Interview Completed</option>
                                </select>
                                <select class="filter-select" id="dateFilter">
                                    <option value="">All Dates</option>
                                    <option value="today">Today</option>
                                    <option value="this-week">This Week</option>
                                    <option value="this-month">This Month</option>
                                    <option value="last-month">Last Month</option>
                                </select>
                            </div>
                            <div class="toolbar-actions">
                                <button onclick="showGenerateCodesModal()" class="section-action">
                                    <span class="section-action-icon">🔑</span>
                                    Generate Access Codes
                                </button>
                                <button onclick="exportApplicants()" class="section-action">
                                    <span class="section-action-icon">📊</span>
                                    Export Data
                                </button>
                                <button onclick="sendBulkEmails()" class="section-action">
                                    <span class="section-action-icon">📧</span>
                                    Bulk Email
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Applicants Table -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">All Applicants</h2>
                        <div class="header-stats">
                            <span class="stat-badge">Showing: <span id="visibleCount">15</span></span>
                            <span class="stat-badge">Total: {{ $totalApplicants ?? 145 }}</span>
                        </div>
                    </div>
                    <div class="section-content">
                        <table class="data-table applicants-table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="checkbox-input">
                                    </th>
                                    <th>Applicant Details</th>
                                    <th>Contact Info</th>
                                    <th>Exam Score</th>
                                    <th>Interview Status</th>
                                    <th>Applied Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applicants ?? [] as $applicant)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="checkbox-input applicant-checkbox" value="{{ $applicant->id }}">
                                    </td>
                                    <td>
                                        <div class="applicant-info">
                                            <div class="applicant-name">{{ $applicant->name }}</div>
                                            <div class="applicant-id">ID: {{ $applicant->student_id }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-email">{{ $applicant->email }}</div>
                                            <div class="contact-phone">{{ $applicant->phone }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($applicant->exam_score)
                                            <div class="score-display">
                                                <span class="score-value {{ $applicant->exam_score >= 75 ? 'score-passed' : 'score-failed' }}">
                                                    {{ $applicant->exam_score }}%
                                                </span>
                                                <span class="score-details">{{ $applicant->correct_answers }}/{{ $applicant->total_questions }}</span>
                                            </div>
                                        @else
                                            <span class="score-pending">Not taken</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $applicant->interview_status)) }}">
                                            {{ $applicant->interview_status }}
                                        </span>
                                    </td>
                                    <td>{{ $applicant->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button onclick="viewApplicant({{ $applicant->id }})" class="action-btn action-btn-view" title="View Details">
                                                👁️ View
                                            </button>
                                            <button onclick="emailApplicant({{ $applicant->id }})" class="action-btn action-btn-email" title="Send Email">
                                                📧 Email
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <!-- Demo data when no applicants exist -->
                                <tr>
                                    <td><input type="checkbox" class="checkbox-input applicant-checkbox" value="1"></td>
                                    <td>
                                        <div class="applicant-info">
                                            <div class="applicant-name">John Doe</div>
                                            <div class="applicant-id">ID: 2024-001</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-email">john.doe@email.com</div>
                                            <div class="contact-phone">+1 (555) 123-4567</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="score-display">
                                            <span class="score-value score-passed">85%</span>
                                            <span class="score-details">17/20</span>
                                        </div>
                                    </td>
                                    <td><span class="status-badge status-interview-scheduled">Interview Scheduled</span></td>
                                    <td>{{ now()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button onclick="viewApplicant(1)" class="action-btn action-btn-view">👁️ View</button>
                                            <button onclick="emailApplicant(1)" class="action-btn action-btn-email">📧 Email</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" class="checkbox-input applicant-checkbox" value="2"></td>
                                    <td>
                                        <div class="applicant-info">
                                            <div class="applicant-name">Jane Smith</div>
                                            <div class="applicant-id">ID: 2024-002</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-email">jane.smith@email.com</div>
                                            <div class="contact-phone">+1 (555) 234-5678</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="score-display">
                                            <span class="score-value score-passed">92%</span>
                                            <span class="score-details">18/20</span>
                                        </div>
                                    </td>
                                    <td><span class="status-badge status-interview-completed">Interview Completed</span></td>
                                    <td>{{ now()->subDay()->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button onclick="viewApplicant(2)" class="action-btn action-btn-view">👁️ View</button>
                                            <button onclick="emailApplicant(2)" class="action-btn action-btn-email">📧 Email</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" class="checkbox-input applicant-checkbox" value="3"></td>
                                    <td>
                                        <div class="applicant-info">
                                            <div class="applicant-name">Mike Johnson</div>
                                            <div class="applicant-id">ID: 2024-003</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-email">mike.j@email.com</div>
                                            <div class="contact-phone">+1 (555) 345-6789</div>
                                        </div>
                                    </td>
                                    <td><span class="score-pending">Not taken</span></td>
                                    <td><span class="status-badge status-pending">Pending Exam</span></td>
                                    <td>{{ now()->subDays(2)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button onclick="viewApplicant(3)" class="action-btn action-btn-view">👁️ View</button>
                                            <button onclick="emailApplicant(3)" class="action-btn action-btn-email">📧 Email</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" class="checkbox-input applicant-checkbox" value="4"></td>
                                    <td>
                                        <div class="applicant-info">
                                            <div class="applicant-name">Sarah Williams</div>
                                            <div class="applicant-id">ID: 2024-004</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-email">sarah.w@email.com</div>
                                            <div class="contact-phone">+1 (555) 456-7890</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="score-display">
                                            <span class="score-value score-failed">68%</span>
                                            <span class="score-details">13/20</span>
                                        </div>
                                    </td>
                                    <td><span class="status-badge status-exam-failed">Exam Failed</span></td>
                                    <td>{{ now()->subDays(3)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button onclick="viewApplicant(4)" class="action-btn action-btn-view">👁️ View</button>
                                            <button onclick="emailApplicant(4)" class="action-btn action-btn-email">📧 Email</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" class="checkbox-input applicant-checkbox" value="5"></td>
                                    <td>
                                        <div class="applicant-info">
                                            <div class="applicant-name">David Brown</div>
                                            <div class="applicant-id">ID: 2024-005</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-email">david.brown@email.com</div>
                                            <div class="contact-phone">+1 (555) 567-8901</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="score-display">
                                            <span class="score-value score-passed">78%</span>
                                            <span class="score-details">15/20</span>
                                        </div>
                                    </td>
                                    <td><span class="status-badge status-pending">Pending Interview</span></td>
                                    <td>{{ now()->subDays(4)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button onclick="viewApplicant(5)" class="action-btn action-btn-view">👁️ View</button>
                                            <button onclick="emailApplicant(5)" class="action-btn action-btn-email">📧 Email</button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="pagination">
                            <div class="pagination-info">
                                Showing 1-15 of {{ $totalApplicants ?? 145 }} applicants
                            </div>
                            <div class="pagination-controls">
                                <a href="#" class="page-btn">← Previous</a>
                                <a href="#" class="page-btn active">1</a>
                                <a href="#" class="page-btn">2</a>
                                <a href="#" class="page-btn">3</a>
                                <span class="page-btn" style="border: none; cursor: default;">...</span>
                                <a href="#" class="page-btn">10</a>
                                <a href="#" class="page-btn">Next →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Applicant Details Modal -->
    <div id="applicantModal" class="modal-overlay" style="display: none;">
        <div class="modal-content applicant-modal">
            <div class="modal-header">
                <h3>Applicant Details</h3>
                <button onclick="closeApplicantModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body" id="applicantModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button onclick="closeApplicantModal()" class="btn-secondary">Close</button>
                <button onclick="scheduleInterview()" class="btn-primary">Schedule Interview</button>
            </div>
        </div>
    </div>

    <!-- Include Generate Access Codes Modal -->
    @include('components.generate-access-codes-modal')

    <script>
        let selectedApplicants = [];

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterTable();
        });

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('dateFilter').addEventListener('change', filterTable);

        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            const rows = document.querySelectorAll('.applicants-table tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                let show = true;

                // Search filter
                if (searchTerm) {
                    const name = row.querySelector('.applicant-name')?.textContent.toLowerCase() || '';
                    const email = row.querySelector('.contact-email')?.textContent.toLowerCase() || '';
                    const id = row.querySelector('.applicant-id')?.textContent.toLowerCase() || '';
                    
                    if (!name.includes(searchTerm) && !email.includes(searchTerm) && !id.includes(searchTerm)) {
                        show = false;
                    }
                }

                // Status filter
                if (statusFilter && show) {
                    const statusElement = row.querySelector('.status-badge');
                    if (statusElement) {
                        const rowStatus = statusElement.className.split(' ').find(cls => cls.startsWith('status-'));
                        if (rowStatus !== `status-${statusFilter}`) {
                            show = false;
                        }
                    }
                }

                // Date filter would be implemented here in a real application

                if (show) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('visibleCount').textContent = visibleCount;
        }

        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('.applicant-checkbox');
            checkboxes.forEach(checkbox => {
                if (checkbox.closest('tr').style.display !== 'none') {
                    checkbox.checked = e.target.checked;
                }
            });
            updateSelectedApplicants();
        });

        // Individual checkbox handling
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('applicant-checkbox')) {
                updateSelectedApplicants();
            }
        });

        function updateSelectedApplicants() {
            const checkboxes = document.querySelectorAll('.applicant-checkbox:checked');
            selectedApplicants = Array.from(checkboxes).map(cb => cb.value);
            
            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.applicant-checkbox');
            const visibleCheckboxes = Array.from(allCheckboxes).filter(cb => cb.closest('tr').style.display !== 'none');
            const checkedVisible = visibleCheckboxes.filter(cb => cb.checked);
            
            const selectAllCheckbox = document.getElementById('selectAll');
            if (checkedVisible.length === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (checkedVisible.length === visibleCheckboxes.length) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        }

        // View applicant details
        function viewApplicant(applicantId) {
            // In a real application, this would fetch actual data
            const modalBody = document.getElementById('applicantModalBody');
            modalBody.innerHTML = `
                <div class="applicant-details">
                    <div class="detail-section">
                        <h4>Personal Information</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Full Name:</span>
                                <span class="detail-value">John Doe</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Student ID:</span>
                                <span class="detail-value">2024-001</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value">john.doe@email.com</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Phone:</span>
                                <span class="detail-value">+1 (555) 123-4567</span>
                            </div>
                        </div>
                    </div>
                    <div class="detail-section">
                        <h4>Exam Results</h4>
                        <div class="exam-summary">
                            <div class="exam-score">85%</div>
                            <div class="exam-details">17 out of 20 questions correct</div>
                        </div>
                    </div>
                    <div class="detail-section">
                        <h4>Interview Status</h4>
                        <div class="interview-status">
                            <span class="status-badge status-interview-scheduled">Interview Scheduled</span>
                            <div class="interview-details">Scheduled for: Jan 15, 2024 at 2:00 PM</div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('applicantModal').style.display = 'flex';
        }

        function closeApplicantModal() {
            document.getElementById('applicantModal').style.display = 'none';
        }

        function emailApplicant(applicantId) {
            alert(`Send email to applicant ${applicantId} (Demo mode)`);
        }

        function scheduleInterview() {
            alert('Schedule interview functionality (Demo mode)');
            closeApplicantModal();
        }

        function exportApplicants() {
            alert('Export applicants data functionality (Demo mode)');
        }

        function sendBulkEmails() {
            if (selectedApplicants.length === 0) {
                alert('Please select applicants first');
                return;
            }
            alert(`Send bulk email to ${selectedApplicants.length} selected applicants (Demo mode)`);
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeApplicantModal();
            }
        });
    </script>

    <style>
        /* Additional styles for applicants page */
        .applicants-stats {
            margin-bottom: 30px;
        }

        .applicants-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .search-filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex: 1;
            flex-wrap: wrap;
        }

        .toolbar-actions {
            display: flex;
            gap: 12px;
        }

        .checkbox-input {
            width: 16px;
            height: 16px;
            accent-color: var(--maroon-primary);
            cursor: pointer;
        }

        .applicant-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .applicant-name {
            font-weight: 600;
            color: var(--maroon-primary);
            font-size: 14px;
        }

        .applicant-id {
            font-size: 12px;
            color: var(--text-gray);
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .contact-email {
            font-size: 14px;
            color: var(--maroon-primary);
        }

        .contact-phone {
            font-size: 12px;
            color: var(--text-gray);
        }

        .score-display {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .score-value {
            font-weight: 600;
            font-size: 16px;
        }

        .score-passed {
            color: #22c55e;
        }

        .score-failed {
            color: #ef4444;
        }

        .score-details {
            font-size: 12px;
            color: var(--text-gray);
        }

        .score-pending {
            color: var(--text-gray);
            font-style: italic;
            font-size: 14px;
        }

        /* Status badges */
        .status-pending { background: #f3f4f6; color: #374151; }
        .status-in-progress { background: #fef3c7; color: #92400e; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-passed { background: #dcfce7; color: #166534; }
        .status-failed { background: #fecaca; color: #dc2626; }
        .status-exam-failed { background: #fecaca; color: #dc2626; }
        .status-interview-scheduled { background: #dbeafe; color: #1e40af; }
        .status-interview-completed { background: #f3e8ff; color: #7c3aed; }

        .action-btn-view {
            background: var(--yellow-light);
            color: var(--maroon-primary);
            border: 1px solid var(--yellow-primary);
        }

        .action-btn-view:hover {
            background: var(--yellow-primary);
        }

        .action-btn-email {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #1e40af;
        }

        .action-btn-email:hover {
            background: #1e40af;
            color: var(--white);
        }

        /* Applicant modal styles */
        .applicant-modal {
            max-width: 600px;
            width: 90%;
        }

        .applicant-details {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .detail-section h4 {
            margin: 0 0 16px 0;
            color: var(--maroon-primary);
            font-size: 16px;
            font-weight: 600;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--yellow-primary);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .detail-label {
            font-size: 12px;
            color: var(--text-gray);
            font-weight: 500;
        }

        .detail-value {
            font-size: 14px;
            color: var(--maroon-primary);
            font-weight: 500;
        }

        .exam-summary {
            text-align: center;
            padding: 20px;
            background: var(--yellow-light);
            border-radius: 8px;
        }

        .exam-score {
            font-size: 32px;
            font-weight: 700;
            color: var(--maroon-primary);
            margin-bottom: 8px;
        }

        .exam-details {
            font-size: 14px;
            color: var(--text-gray);
        }

        .interview-status {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }

        .interview-details {
            font-size: 14px;
            color: var(--text-gray);
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

        @media (max-width: 768px) {
            .applicants-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .search-filter-group {
                flex-direction: column;
                gap: 10px;
            }

            .toolbar-actions {
                justify-content: center;
            }

            .applicants-table {
                font-size: 12px;
            }

            .applicants-table th,
            .applicants-table td {
                padding: 8px 6px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>