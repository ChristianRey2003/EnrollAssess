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
                    <a href="{{ route('admin.exams.index') }}" class="nav-link">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Exams</span>
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
            <div class="main-header">
                <div class="header-left">
                    <h1>Applicant Management</h1>
                    <p class="header-subtitle">Import, manage, and assign applicants to exams</p>
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
                <!-- Statistics Section -->
                <div class="stats-section">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value">{{ $stats['pending'] }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-value">{{ $stats['exam_completed'] }}</div>
                        <div class="stat-label">Exam Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🔑</div>
                        <div class="stat-value">{{ $stats['with_access_codes'] }}</div>
                        <div class="stat-label">With Access Codes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">❌</div>
                        <div class="stat-value">{{ $stats['without_access_codes'] }}</div>
                        <div class="stat-label">Without Codes</div>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="content-section" style="margin-bottom: 20px;">
                    <div class="section-content" style="padding: 20px 30px;">
                        <div class="applicants-toolbar">
                            <div class="search-filter-group">
                                <div class="search-box">
                                    <input type="text" placeholder="Search applicants..." class="search-input" id="searchInput" value="{{ request('search') }}">
                                    <button class="search-btn" onclick="performSearch()">🔍</button>
                                </div>
                                <select class="filter-select" id="statusFilter" name="status" onchange="applyFilter()">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="exam-completed" {{ request('status') == 'exam-completed' ? 'selected' : '' }}>Exam Completed</option>
                                    <option value="interview-scheduled" {{ request('status') == 'interview-scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                                    <option value="interview-completed" {{ request('status') == 'interview-completed' ? 'selected' : '' }}>Interview Completed</option>
                                    <option value="admitted" {{ request('status') == 'admitted' ? 'selected' : '' }}>Admitted</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                <select class="filter-select" id="examSetFilter" name="exam_set_id" onchange="applyFilter()">
                                    <option value="">All Exam Sets</option>
                                    @foreach($examSets as $examSet)
                                        <option value="{{ $examSet->exam_set_id }}" {{ request('exam_set_id') == $examSet->exam_set_id ? 'selected' : '' }}>
                                            {{ $examSet->exam->title }} - {{ $examSet->set_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="toolbar-actions">
                                <a href="{{ route('admin.applicants.import') }}" class="btn-secondary">
                                    📤 Import Applicants
                                </a>
                                <a href="{{ route('admin.applicants.create') }}" class="btn-primary">
                                    ➕ Add Individual
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions Section -->
                <div class="content-section" style="margin-bottom: 20px;">
                    <div class="section-content" style="padding: 15px 30px;">
                        <div class="bulk-actions">
                            <div class="bulk-select">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                <label for="selectAll">Select All</label>
                                <span id="selectedCount" class="selected-count">0 selected</span>
                            </div>
                            <div class="bulk-action-buttons" id="bulkActions" style="display: none;">
                                <button onclick="showGenerateAccessCodesModal()" class="bulk-btn bulk-btn-codes">
                                    🔑 Generate Access Codes
                                </button>
                                <button onclick="showAssignExamSetsModal()" class="bulk-btn bulk-btn-assign">
                                    📋 Assign Exam Sets
                                </button>
                                <button onclick="bulkExport()" class="bulk-btn bulk-btn-export">
                                    📊 Export Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Applicants Table -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Applicants List</h2>
                        <div class="header-stats">
                            <span class="stat-badge">Total: {{ $applicants->total() ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="section-content">
                        @if($applicants->count() > 0)
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" id="tableSelectAll" onchange="toggleTableSelectAll()">
                                            </th>
                                            <th style="width: 100px;">App. No.</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th style="width: 120px;">Exam Set</th>
                                            <th style="width: 100px;">Access Code</th>
                                            <th style="width: 80px;">Status</th>
                                            <th style="width: 80px;">Score</th>
                                            <th style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($applicants as $applicant)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="applicant-checkbox" value="{{ $applicant->applicant_id }}" onchange="updateBulkActions()">
                                            </td>
                                            <td>
                                                <span class="app-number">#{{ $applicant->application_no }}</span>
                                            </td>
                                            <td>
                                                <div class="applicant-info">
                                                    <div class="applicant-avatar">{{ $applicant->initials }}</div>
                                                    <div class="applicant-details">
                                                        <strong>{{ $applicant->full_name }}</strong>
                                                        @if($applicant->phone_number)
                                                            <div class="phone">{{ $applicant->phone_number }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $applicant->email_address }}</td>
                                            <td>
                                                @if($applicant->examSet)
                                                    <div class="exam-set-info">
                                                        <div class="set-name">{{ $applicant->examSet->set_name }}</div>
                                                        <div class="exam-title">{{ $applicant->examSet->exam->title }}</div>
                                                    </div>
                                                @else
                                                    <span class="not-assigned">Not Assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($applicant->accessCode)
                                                    <div class="access-code">
                                                        <code>{{ $applicant->accessCode->code }}</code>
                                                        @if($applicant->accessCode->is_used)
                                                            <span class="code-status used">Used</span>
                                                        @else
                                                            <span class="code-status active">Active</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="no-code">No Code</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="status-badge status-{{ str_replace('-', '_', $applicant->status) }}">
                                                    {{ ucfirst(str_replace('-', ' ', $applicant->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($applicant->score)
                                                    <div class="score-info">
                                                        <div class="score">{{ $applicant->score }}</div>
                                                        <div class="percentage">{{ $applicant->exam_percentage }}%</div>
                                                    </div>
                                                @else
                                                    <span class="no-score">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" class="action-btn action-btn-view" title="View Details">
                                                        👁️
                                                    </a>
                                                    <a href="{{ route('admin.applicants.edit', $applicant->applicant_id) }}" class="action-btn action-btn-edit" title="Edit">
                                                        ✏️
                                                    </a>
                                                    @if(!$applicant->accessCode)
                                                    <button onclick="generateSingleAccessCode({{ $applicant->applicant_id }})" class="action-btn action-btn-code" title="Generate Access Code">
                                                        🔑
                                                    </button>
                                                    @endif
                                                    <button onclick="deleteApplicant({{ $applicant->applicant_id }})" class="action-btn action-btn-delete" title="Delete">
                                                        🗑️
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($applicants->hasPages())
                            <div class="pagination">
                                <div class="pagination-info">
                                    Showing {{ $applicants->firstItem() ?? 0 }}-{{ $applicants->lastItem() ?? 0 }} of {{ $applicants->total() }} applicants
                                </div>
                                <div class="pagination-controls">
                                    {{ $applicants->appends(request()->query())->links() }}
                                </div>
                            </div>
                            @endif
                        @else
                            <div class="empty-state">
                                <div class="empty-content">
                                    <span class="empty-icon">👥</span>
                                    <h3>No Applicants Found</h3>
                                    <p>No applicants have been added yet. Import a CSV file or add individual applicants to get started.</p>
                                    <div class="empty-actions">
                                        <a href="{{ route('admin.applicants.import') }}" class="btn-primary">
                                            📤 Import from CSV
                                        </a>
                                        <a href="{{ route('admin.applicants.create') }}" class="btn-secondary">
                                            ➕ Add Individual
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Generate Access Codes Modal -->
    <div id="generateCodesModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Generate Access Codes</h3>
                <button onclick="closeGenerateCodesModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <form id="generateCodesForm">
                    <div class="form-group">
                        <label for="expiry_hours" class="form-label">Expiry Time (Hours)</label>
                        <input type="number" id="expiry_hours" name="expiry_hours" class="form-control" value="72" min="1" max="720">
                        <div class="form-help">Access codes will expire after this many hours (default: 72 hours)</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="closeGenerateCodesModal()" class="btn-secondary">Cancel</button>
                <button onclick="confirmGenerateAccessCodes()" class="btn-primary">Generate Codes</button>
            </div>
        </div>
    </div>

    <!-- Assign Exam Sets Modal -->
    <div id="assignSetsModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Assign Exam Sets</h3>
                <button onclick="closeAssignSetsModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <form id="assignSetsForm">
                    <div class="form-group">
                        <label for="exam_set_id" class="form-label">Exam Set</label>
                        <select id="exam_set_id" name="exam_set_id" class="form-control" required>
                            <option value="">Select Exam Set</option>
                            @foreach($examSets as $examSet)
                                <option value="{{ $examSet->exam_set_id }}">
                                    {{ $examSet->exam->title }} - {{ $examSet->set_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="assignment_strategy" class="form-label">Assignment Strategy</label>
                        <select id="assignment_strategy" name="assignment_strategy" class="form-control" required>
                            <option value="same">Assign same set to all selected applicants</option>
                            <option value="random">Randomly assign different sets from the same exam</option>
                        </select>
                        <div class="form-help">Random assignment helps prevent cheating by giving different question sets</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="closeAssignSetsModal()" class="btn-secondary">Cancel</button>
                <button onclick="confirmAssignExamSets()" class="btn-primary">Assign Sets</button>
            </div>
        </div>
    </div>

    <script>
        let selectedApplicants = [];

        // Search functionality
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value;
            const url = new URL(window.location);
            if (searchTerm.trim()) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }
            window.location = url;
        }

        // Enter key search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Filter functionality
        function applyFilter() {
            const status = document.getElementById('statusFilter').value;
            const examSetId = document.getElementById('examSetFilter').value;
            const url = new URL(window.location);
            
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            
            if (examSetId) {
                url.searchParams.set('exam_set_id', examSetId);
            } else {
                url.searchParams.delete('exam_set_id');
            }
            
            window.location = url;
        }

        // Bulk selection
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.applicant-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            
            updateBulkActions();
        }

        function toggleTableSelectAll() {
            const tableSelectAll = document.getElementById('tableSelectAll');
            const checkboxes = document.querySelectorAll('.applicant-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = tableSelectAll.checked;
            });
            
            updateBulkActions();
        }

        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.applicant-checkbox:checked');
            const count = checkboxes.length;
            
            selectedApplicants = Array.from(checkboxes).map(cb => cb.value);
            
            document.getElementById('selectedCount').textContent = `${count} selected`;
            document.getElementById('bulkActions').style.display = count > 0 ? 'flex' : 'none';
        }

        // Bulk operations
        function showGenerateAccessCodesModal() {
            if (selectedApplicants.length === 0) {
                alert('Please select applicants first.');
                return;
            }
            document.getElementById('generateCodesModal').style.display = 'flex';
        }

        function closeGenerateCodesModal() {
            document.getElementById('generateCodesModal').style.display = 'none';
        }

        function confirmGenerateAccessCodes() {
            const expiryHours = document.getElementById('expiry_hours').value;
            
            fetch('/admin/applicants/generate-access-codes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    applicant_ids: selectedApplicants,
                    expiry_hours: expiryHours
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred while generating access codes');
            });
            
            closeGenerateCodesModal();
        }

        function showAssignExamSetsModal() {
            if (selectedApplicants.length === 0) {
                alert('Please select applicants first.');
                return;
            }
            document.getElementById('assignSetsModal').style.display = 'flex';
        }

        function closeAssignSetsModal() {
            document.getElementById('assignSetsModal').style.display = 'none';
        }

        function confirmAssignExamSets() {
            const examSetId = document.getElementById('exam_set_id').value;
            const strategy = document.getElementById('assignment_strategy').value;
            
            if (!examSetId) {
                alert('Please select an exam set.');
                return;
            }
            
            fetch('/admin/applicants/assign-exam-sets', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    applicant_ids: selectedApplicants,
                    exam_set_id: examSetId,
                    assignment_strategy: strategy
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred while assigning exam sets');
            });
            
            closeAssignSetsModal();
        }

        // Individual operations
        function generateSingleAccessCode(applicantId) {
            if (confirm('Generate access code for this applicant?')) {
                fetch('/admin/applicants/generate-access-codes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        applicant_ids: [applicantId],
                        expiry_hours: 72
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred while generating access code');
                });
            }
        }

        function deleteApplicant(applicantId) {
            if (confirm('Are you sure you want to delete this applicant? This action cannot be undone.')) {
                fetch(`/admin/applicants/${applicantId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred while deleting the applicant');
                });
            }
        }

        function bulkExport() {
            if (selectedApplicants.length === 0) {
                alert('Please select applicants first.');
                return;
            }
            
            const url = new URL('/admin/applicants/export/with-access-codes', window.location.origin);
            const filters = new URLSearchParams(window.location.search);
            
            filters.forEach((value, key) => {
                url.searchParams.set(key, value);
            });
            
            window.open(url.toString(), '_blank');
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeGenerateCodesModal();
                closeAssignSetsModal();
            }
        });
    </script>

    <style>
        /* Additional styles for applicants page */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-gray);
            text-align: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--maroon-primary);
            margin-bottom: 4px;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 12px;
            font-weight: 500;
        }

        .applicants-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .search-filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex: 1;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 300px;
        }

        .bulk-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .bulk-select {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .selected-count {
            color: var(--text-gray);
            font-size: 14px;
        }

        .bulk-action-buttons {
            display: flex;
            gap: 10px;
        }

        .bulk-btn {
            padding: 8px 16px;
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: var(--transition);
        }

        .bulk-btn-codes { background: #e3f2fd; color: #1976d2; }
        .bulk-btn-assign { background: #f3e5f5; color: #7b1fa2; }
        .bulk-btn-export { background: #e8f5e8; color: #2e7d32; }

        .applicant-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .applicant-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--maroon-primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 12px;
        }

        .applicant-details strong {
            display: block;
            color: var(--text-dark);
            font-size: 14px;
        }

        .phone {
            color: var(--text-gray);
            font-size: 12px;
        }

        .app-number {
            color: var(--maroon-primary);
            font-weight: 600;
            font-size: 12px;
        }

        .exam-set-info .set-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 13px;
        }

        .exam-set-info .exam-title {
            color: var(--text-gray);
            font-size: 11px;
        }

        .not-assigned, .no-code, .no-score {
            color: var(--text-gray);
            font-style: italic;
            font-size: 12px;
        }

        .access-code code {
            background: var(--light-gray);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-family: monospace;
        }

        .code-status {
            display: block;
            font-size: 10px;
            margin-top: 2px;
        }

        .code-status.used { color: #d32f2f; }
        .code-status.active { color: #2e7d32; }

        .score-info .score {
            font-weight: 600;
            color: var(--text-dark);
        }

        .score-info .percentage {
            color: var(--text-gray);
            font-size: 11px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background: #fff3e0; color: #ef6c00; }
        .status-exam_completed { background: #e3f2fd; color: #1976d2; }
        .status-interview_scheduled { background: #f3e5f5; color: #7b1fa2; }
        .status-interview_completed { background: #fce4ec; color: #c2185b; }
        .status-admitted { background: #e8f5e8; color: #2e7d32; }
        .status-rejected { background: #ffebee; color: #d32f2f; }

        .action-btn-code { background: #e3f2fd; color: #1976d2; border-color: #bbdefb; }

        .empty-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 768px) {
            .stats-section {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

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

            .bulk-actions {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</body>
</html>