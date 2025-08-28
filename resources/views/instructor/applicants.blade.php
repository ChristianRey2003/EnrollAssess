<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>My Applicants - {{ config('app.name', 'EnrollAssess') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Admin Dashboard CSS -->
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
</head>
<body class="admin-page instructor-portal">
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="admin-sidebar instructor-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo">
                    <div>
                        <h2 class="sidebar-title">EnrollAssess</h2>
                        <p class="sidebar-subtitle">Instructor Portal</p>
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('instructor.dashboard') }}" class="nav-link">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('instructor.applicants') }}" class="nav-link active">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">My Applicants</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('instructor.schedule') }}" class="nav-link">
                        <span class="nav-icon">📅</span>
                        <span class="nav-text">Schedule</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('instructor.interview-history') }}" class="nav-link">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Interview History</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('instructor.guidelines') }}" class="nav-link">
                        <span class="nav-icon">📋</span>
                        <span class="nav-text">Guidelines</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="instructor-info">
                    <div class="instructor-avatar">{{ substr(Auth::user()->full_name, 0, 2) }}</div>
                    <div>
                        <div class="instructor-name">{{ Auth::user()->full_name }}</div>
                        <div class="instructor-role">Instructor</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
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
            <div class="main-header instructor-header">
                <div class="header-left">
                    <h1>My Assigned Applicants</h1>
                    <p class="header-subtitle">Manage interviews and evaluations for your assigned applicants</p>
                </div>
                <div class="header-right">
                    <div class="header-stats">
                        <div class="stat-item">
                            <span class="stat-value">{{ $assignedApplicants->count() }}</span>
                            <span class="stat-label">Total Assigned</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">{{ $assignedApplicants->where('status', 'exam-completed')->count() }}</span>
                            <span class="stat-label">Pending Interviews</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="main-content">
                <!-- Filters and Search -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Applicant Management</h2>
                        <div class="section-actions">
                            <div class="search-box">
                                <input type="text" id="searchApplicants" placeholder="Search applicants..." class="search-input">
                                <span class="search-icon">🔍</span>
                            </div>
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Status</option>
                                <option value="exam-completed">Ready for Interview</option>
                                <option value="interview-completed">Interview Completed</option>
                                <option value="admitted">Admitted</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-content">
                        @if($assignedApplicants->count() > 0)
                        <div class="applicants-grid">
                            @foreach($assignedApplicants as $applicant)
                            <div class="applicant-card" data-status="{{ $applicant->status }}">
                                <div class="applicant-header">
                                    <div class="applicant-avatar">
                                        {{ substr($applicant->full_name, 0, 2) }}
                                    </div>
                                    <div class="applicant-info">
                                        <h3 class="applicant-name">{{ $applicant->full_name }}</h3>
                                        <p class="applicant-email">{{ $applicant->email_address }}</p>
                                        <p class="applicant-app-no">{{ $applicant->application_no }}</p>
                                    </div>
                                    <div class="applicant-status">
                                        <span class="status-badge status-{{ str_replace(' ', '-', strtolower($applicant->status)) }}">
                                            {{ ucfirst(str_replace('-', ' ', $applicant->status)) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="applicant-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Exam Score:</span>
                                        <span class="detail-value">
                                            @if($applicant->score)
                                                <span class="score-badge {{ $applicant->score >= 70 ? 'good' : 'needs-improvement' }}">
                                                    {{ number_format($applicant->score, 1) }}%
                                                </span>
                                            @else
                                                <span class="score-badge pending">Pending</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Exam Set:</span>
                                        <span class="detail-value">{{ $applicant->examSet->name ?? 'Not assigned' }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Phone:</span>
                                        <span class="detail-value">{{ $applicant->phone_number }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Education:</span>
                                        <span class="detail-value">{{ $applicant->education_background }}</span>
                                    </div>
                                </div>

                                <div class="applicant-actions">
                                    @php
                                        $interview = $applicant->interviews->where('interviewer_id', Auth::user()->user_id)->first();
                                    @endphp
                                    
                                    @if($applicant->status === 'exam-completed')
                                        <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                           class="btn-primary">
                                            🎯 Start Interview
                                        </a>
                                    @elseif($interview && $interview->status === 'completed')
                                        <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                           class="btn-secondary">
                                            📋 View Results
                                        </a>
                                        @if($interview->overall_score)
                                            <div class="interview-score">
                                                Interview Score: <strong>{{ number_format($interview->overall_score, 1) }}%</strong>
                                            </div>
                                        @endif
                                    @elseif($interview && $interview->status === 'assigned')
                                        <a href="{{ route('instructor.interview.show', $applicant->applicant_id) }}" 
                                           class="btn-warning">
                                            ⏳ Continue Interview
                                        </a>
                                    @else
                                        <span class="btn-disabled">
                                            Waiting for Exam Completion
                                        </span>
                                    @endif
                                    
                                    <a class="btn-outline" href="{{ route('instructor.applicant.portfolio', $applicant->applicant_id) }}">
                                        📄 View Details
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="empty-state">
                            <div class="empty-icon">👥</div>
                            <h3>No Applicants Assigned</h3>
                            <p>You don't have any applicants assigned for interviews yet. Contact the administrator if you expect to have applicants assigned.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Applicant Details Modal -->
    <div id="applicantModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalApplicantName">Applicant Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalApplicantDetails">
                <!-- Will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <style>
        /* Instructor Portal Styles */
        .instructor-portal {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
        }

        .instructor-sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #1e40af 100%);
        }

        .instructor-header {
            border-bottom: 3px solid var(--primary-color);
        }

        .header-stats {
            display: flex;
            gap: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-gray);
        }

        .section-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding: 8px 35px 8px 12px;
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            width: 250px;
        }

        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            background: white;
        }

        .applicants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }

        .applicant-card {
            background: white;
            border: 1px solid var(--border-gray);
            border-radius: 12px;
            padding: 20px;
            transition: var(--transition);
        }

        .applicant-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .applicant-header {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }

        .applicant-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }

        .applicant-info {
            flex: 1;
        }

        .applicant-name {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .applicant-email {
            margin: 0 0 3px 0;
            color: var(--text-gray);
            font-size: 14px;
        }

        .applicant-app-no {
            margin: 0;
            color: var(--primary-color);
            font-size: 12px;
            font-weight: 500;
        }

        .applicant-details {
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-label {
            font-weight: 500;
            color: var(--text-gray);
            font-size: 14px;
        }

        .detail-value {
            font-weight: 500;
            color: var(--text-dark);
            font-size: 14px;
        }

        .applicant-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .applicant-actions .btn-primary,
        .applicant-actions .btn-secondary,
        .applicant-actions .btn-warning,
        .applicant-actions .btn-outline,
        .applicant-actions .btn-disabled {
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background: var(--text-gray);
            color: white;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-outline {
            background: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-disabled {
            background: #f3f4f6;
            color: var(--text-gray);
            cursor: not-allowed;
        }

        .interview-score {
            margin-top: 8px;
            padding: 8px;
            background: #ecfdf5;
            border-radius: 6px;
            font-size: 12px;
            color: #166534;
            text-align: center;
        }

        .instructor-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .instructor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--white);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .instructor-name {
            color: var(--white);
            font-weight: 600;
            font-size: 14px;
        }

        .instructor-role {
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid var(--border-gray);
            text-align: right;
        }

        @media (max-width: 768px) {
            .applicants-grid {
                grid-template-columns: 1fr;
            }

            .section-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input {
                width: 100%;
            }
        }
    </style>

    <script>
        // Search functionality
        document.getElementById('searchApplicants').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.applicant-card');
            
            cards.forEach(card => {
                const name = card.querySelector('.applicant-name').textContent.toLowerCase();
                const email = card.querySelector('.applicant-email').textContent.toLowerCase();
                const appNo = card.querySelector('.applicant-app-no').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm) || appNo.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function(e) {
            const filterValue = e.target.value;
            const cards = document.querySelectorAll('.applicant-card');
            
            cards.forEach(card => {
                if (!filterValue || card.dataset.status === filterValue) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Modal functionality
        function viewApplicantDetails(applicantId) {
            // In a real implementation, this would fetch data via AJAX
            document.getElementById('applicantModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('applicantModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('applicantModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>