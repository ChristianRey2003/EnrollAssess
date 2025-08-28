<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Interview Results - {{ config('app.name', 'EnrollAssess') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
    <script src="{{ asset('js/notifications.js') }}" defer></script>
</head>
<body class="admin-page department-head-portal">
    <div class="admin-layout">
        <nav class="admin-sidebar department-head-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/image-removebg-preview.png') }}" alt="University Logo">
                    <div>
                        <h2 class="sidebar-title">EnrollAssess</h2>
                        <p class="sidebar-subtitle">Department Head</p>
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
                    <a href="{{ route('admin.interview-results') }}" class="nav-link active">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Interview Results</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('department-head.analytics') }}" class="nav-link">
                        <span class="nav-icon">📈</span>
                        <span class="nav-text">Analytics</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <span class="nav-icon">⚙️</span>
                        <span class="nav-text">Admin Portal</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">{{ substr(Auth::user()->full_name, 0, 2) }}</div>
                    <div>
                        <div class="user-name">{{ Auth::user()->full_name }}</div>
                        <div class="user-role">Department Head</div>
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

        <main class="admin-main">
            <div class="main-header">
                <div class="header-left">
                    <h1>Interview Results</h1>
                    <p class="header-subtitle">Review completed interviews and make admission decisions</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('department-head.export-interview-results') }}" class="btn-secondary">📊 Export Results</a>
                </div>
            </div>

            <div class="main-content">
                <!-- Filters -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Filter Results</h2>
                        <div class="bulk-actions">
                            <button id="bulkAdmitBtn" class="btn-success" onclick="bulkAdmissionDecision('admit')" disabled>
                                ✅ Admit Selected
                            </button>
                            <button id="bulkRejectBtn" class="btn-danger" onclick="bulkAdmissionDecision('reject')" disabled>
                                ❌ Reject Selected
                            </button>
                        </div>
                    </div>
                    <div class="section-content">
                        <form method="GET" class="filters-form">
                            <div class="filters-grid">
                                <div class="filter-group">
                                    <label>Search Applicant</label>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email...">
                                </div>
                                <div class="filter-group">
                                    <label>Interviewer</label>
                                    <select name="interviewer_id">
                                        <option value="">All Interviewers</option>
                                        @foreach($instructors as $instructor)
                                            <option value="{{ $instructor->user_id }}" {{ request('interviewer_id') == $instructor->user_id ? 'selected' : '' }}>
                                                {{ $instructor->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label>Recommendation</label>
                                    <select name="recommendation">
                                        <option value="">All Recommendations</option>
                                        <option value="highly_recommended" {{ request('recommendation') == 'highly_recommended' ? 'selected' : '' }}>Highly Recommended</option>
                                        <option value="recommended" {{ request('recommendation') == 'recommended' ? 'selected' : '' }}>Recommended</option>
                                        <option value="conditional" {{ request('recommendation') == 'conditional' ? 'selected' : '' }}>Conditional</option>
                                        <option value="not_recommended" {{ request('recommendation') == 'not_recommended' ? 'selected' : '' }}>Not Recommended</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label>Score Range</label>
                                    <select name="score_range">
                                        <option value="">All Scores</option>
                                        <option value="excellent" {{ request('score_range') == 'excellent' ? 'selected' : '' }}>Excellent (90-100)</option>
                                        <option value="very_good" {{ request('score_range') == 'very_good' ? 'selected' : '' }}>Very Good (80-89)</option>
                                        <option value="good" {{ request('score_range') == 'good' ? 'selected' : '' }}>Good (70-79)</option>
                                        <option value="satisfactory" {{ request('score_range') == 'satisfactory' ? 'selected' : '' }}>Satisfactory (60-69)</option>
                                        <option value="needs_improvement" {{ request('score_range') == 'needs_improvement' ? 'selected' : '' }}>Needs Improvement (<60)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn-primary">Apply Filters</button>
                                <a href="{{ route('department-head.interview-results') }}" class="btn-secondary">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="content-section">
                    <div class="section-content">
                        @if($interviews->count() > 0)
                        <div class="table-wrapper">
                            <table class="data-table selectable-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                        </th>
                                        <th>Applicant</th>
                                        <th>Interviewer</th>
                                        <th>Technical</th>
                                        <th>Communication</th>
                                        <th>Analytical</th>
                                        <th>Overall Score</th>
                                        <th>Recommendation</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($interviews as $interview)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="interview-checkbox" value="{{ $interview->interview_id }}" onchange="updateBulkActions()">
                                        </td>
                                        <td>
                                            <div class="applicant-info">
                                                <div class="applicant-name">{{ $interview->applicant->full_name }}</div>
                                                <div class="applicant-email">{{ $interview->applicant->email_address }}</div>
                                                <div class="applicant-app-no">{{ $interview->applicant->application_no }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $interview->interviewer->full_name }}</td>
                                        <td>
                                            <span class="score-mini">{{ $interview->rating_technical ?? 0 }}/40</span>
                                        </td>
                                        <td>
                                            <span class="score-mini">{{ $interview->rating_communication ?? 0 }}/30</span>
                                        </td>
                                        <td>
                                            <span class="score-mini">{{ $interview->rating_problem_solving ?? 0 }}/30</span>
                                        </td>
                                        <td>
                                            <span class="score-badge {{ $interview->overall_score >= 75 ? 'good' : 'needs-improvement' }}">
                                                {{ number_format($interview->overall_score, 1) }}%
                                            </span>
                                        </td>
                                        <td>
                                            <span class="recommendation-badge {{ str_replace('_', '-', $interview->recommendation) }}">
                                                {{ ucfirst(str_replace('_', ' ', $interview->recommendation)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ str_replace(' ', '-', strtolower($interview->applicant->status)) }}">
                                                {{ ucfirst(str_replace('-', ' ', $interview->applicant->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('department-head.interview-detail', $interview->interview_id) }}" 
                                               class="btn-sm btn-primary">View Details</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="pagination-wrapper">
                            {{ $interviews->appends(request()->query())->links() }}
                        </div>
                        @else
                        <div class="empty-state">
                            <div class="empty-icon">📝</div>
                            <h3>No Interview Results Found</h3>
                            <p>No completed interviews match your current filters. Try adjusting your search criteria.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .department-head-portal { --primary-color: #7c3aed; }
        .department-head-sidebar { background: linear-gradient(180deg, var(--primary-color) 0%, #6d28d9 100%); }
        
        .filters-form { background: #f9fafb; padding: 20px; border-radius: 8px; }
        .filters-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .filter-group label { display: block; font-weight: 500; margin-bottom: 5px; color: #374151; }
        .filter-group input, .filter-group select { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; }
        .filter-actions { display: flex; gap: 10px; }
        
        .bulk-actions { display: flex; gap: 10px; }
        .btn-success { background: #10b981; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; }
        .btn-danger { background: #ef4444; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; }
        .btn-success:disabled, .btn-danger:disabled { opacity: 0.5; cursor: not-allowed; }
        
        .selectable-table th:first-child, .selectable-table td:first-child { width: 40px; text-align: center; }
        .score-mini { font-size: 12px; color: #6b7280; font-weight: 500; }
        .score-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .score-badge.good { background: #dcfce7; color: #166534; }
        .score-badge.needs-improvement { background: #fef3c7; color: #92400e; }
        
        .recommendation-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .recommendation-badge.highly-recommended { background: #dcfce7; color: #166534; }
        .recommendation-badge.recommended { background: #dbeafe; color: #1e40af; }
        .recommendation-badge.conditional { background: #fef3c7; color: #92400e; }
        .recommendation-badge.not-recommended { background: #fee2e2; color: #991b1b; }
        
        .status-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .status-badge.status-interview-completed { background: #dbeafe; color: #1e40af; }
        .status-badge.status-admitted { background: #dcfce7; color: #166534; }
        .status-badge.status-rejected { background: #fee2e2; color: #991b1b; }
    </style>

    <script>
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.interview-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkActions();
        }

        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.interview-checkbox:checked');
            const bulkAdmitBtn = document.getElementById('bulkAdmitBtn');
            const bulkRejectBtn = document.getElementById('bulkRejectBtn');
            
            if (checkedBoxes.length > 0) {
                bulkAdmitBtn.disabled = false;
                bulkRejectBtn.disabled = false;
            } else {
                bulkAdmitBtn.disabled = true;
                bulkRejectBtn.disabled = true;
            }
        }

        async function bulkAdmissionDecision(decision) {
            const checkedBoxes = document.querySelectorAll('.interview-checkbox:checked');
            const interviewIds = Array.from(checkedBoxes).map(cb => cb.value);
            
            if (interviewIds.length === 0) {
                window.notifications.warning('Please select interviews to process.');
                return;
            }

            const actionText = decision === 'admit' ? 'admit' : 'reject';
            const confirmed = await window.notifications.confirm(
                `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Applicants`,
                `Are you sure you want to ${actionText} ${interviewIds.length} selected applicant(s)? This action cannot be undone.`,
                {
                    confirmText: actionText.charAt(0).toUpperCase() + actionText.slice(1),
                    type: decision === 'admit' ? 'info' : 'warning',
                    confirmButtonClass: decision === 'admit' ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white'
                }
            );
            
            if (!confirmed) return;

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('{{ route("department-head.bulk-admission-decision") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        interview_ids: interviewIds,
                        decision: decision
                    })
                });

                const data = await response.json();
                if (data.success) {
                    window.notifications.success(data.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    window.notifications.error(data.message || 'An error occurred while processing the request.');
                }
            } catch (error) {
                window.notifications.error('A network error occurred. Please check your connection and try again.');
                console.error('Bulk admission decision error:', error);
            }
        }
    </script>
</body>
</html>
