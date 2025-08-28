<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Admin Dashboard CSS -->
    <link href="{{ asset('css/admin/admin-dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin/applicants.css') }}" rel="stylesheet">
    <!-- Component CSS -->
    <link href="{{ asset('css/components/status-badges.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components/buttons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components/modals.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components/forms.css') }}" rel="stylesheet">
</head>
<body class="admin-page">
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <span class="logo-icon">📚</span>
                    <span class="logo-text">EnrollAssess</span>
                </div>
                <div class="sidebar-subtitle">Admin Portal</div>
            </div>

            <div class="nav-menu">
                <div class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.applicants.index') }}" class="nav-link active">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">Applicants</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.exams.index') }}" class="nav-link">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Exams</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.questions.index') }}" class="nav-link">
                        <span class="nav-icon">❓</span>
                        <span class="nav-text">Questions</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link">
                        <span class="nav-icon">👨‍💼</span>
                        <span class="nav-text">User Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.reports') }}" class="nav-link">
                        <span class="nav-icon">📈</span>
                        <span class="nav-text">Reports</span>
                    </a>
                </div>
                
                <!-- Department Head Features -->
                <div class="nav-section">
                    <div class="nav-section-title">Department Head</div>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.interview-results') }}" class="nav-link">
                        <span class="nav-icon">🎤</span>
                        <span class="nav-text">Interview Results</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.analytics') }}" class="nav-link">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Analytics</span>
                    </a>
                </div>
            </div>

            <div class="nav-bottom">
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
            <div class="admin-header">
                <div class="header-left">
                    <h1 class="page-title">Applicants Management</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">Admin</span>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-current">Applicants</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-user">
                        Welcome, {{ auth()->user()->full_name ?? 'Dr. Admin' }}
                    </div>
                </div>
            </div>

            <div class="admin-content">
                <!-- Success/Error Messages -->
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                <!-- Statistics Section -->
                <section class="stats-section">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value">{{ $stats['total_applicants'] ?? 0 }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-value">{{ $stats['with_access_codes'] ?? 0 }}</div>
                        <div class="stat-label">With Access Codes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📝</div>
                        <div class="stat-value">{{ $stats['exam_completed'] ?? 0 }}</div>
                        <div class="stat-label">Exam Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-value">{{ $stats['pending_admission'] ?? 0 }}</div>
                        <div class="stat-label">Pending Admission</div>
                    </div>
                </section>

                <!-- Toolbar -->
                <div class="applicants-toolbar">
                    <div class="toolbar-left">
                        <div class="search-group">
                            <input type="text" 
                                   id="searchInput" 
                                   class="form-control search-input" 
                                   placeholder="Search applicants..." 
                                   value="{{ request('search') }}"
                                   aria-label="Search applicants">
                        </div>
                        <button onclick="performSearch()" class="btn btn-secondary">
                            🔍 Search
                        </button>
                    </div>
                    <div class="toolbar-right">
                        <div class="filter-group">
                            <select id="statusFilter" class="form-control filter-select" onchange="applyFilter()" aria-label="Filter by status">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="exam_completed" {{ request('status') == 'exam_completed' ? 'selected' : '' }}>Exam Completed</option>
                                <option value="interview_scheduled" {{ request('status') == 'interview_scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                                <option value="interview_completed" {{ request('status') == 'interview_completed' ? 'selected' : '' }}>Interview Completed</option>
                                <option value="admitted" {{ request('status') == 'admitted' ? 'selected' : '' }}>Admitted</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <select id="examSetFilter" class="form-control filter-select" onchange="applyFilter()" aria-label="Filter by exam set">
                                <option value="">All Exam Sets</option>
                                @foreach($examSets ?? [] as $examSet)
                                    <option value="{{ $examSet->id }}" {{ request('exam_set_id') == $examSet->id ? 'selected' : '' }}>
                                        {{ $examSet->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <a href="{{ route('admin.applicants.create') }}" class="btn btn-primary">
                            ➕ Add Applicant
                        </a>
                        <a href="{{ route('admin.applicants.import') }}" class="btn btn-secondary">
                            📁 Import
                        </a>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div id="bulkActions" class="bulk-actions">
                    <div class="bulk-info">
                        <span id="selectedCount">0 selected</span>
                    </div>
                    <div class="bulk-buttons">
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

                <!-- Applicants Table -->
                <div class="applicants-table">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" 
                                           id="tableSelectAll" 
                                           class="checkbox-input" 
                                           onchange="toggleTableSelectAll()"
                                           aria-label="Select all applicants">
                                </th>
                                <th>Applicant Info</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Access Code</th>
                                <th>Exam Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applicants ?? [] as $applicant)
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               class="checkbox-input applicant-checkbox" 
                                               value="{{ $applicant->applicant_id }}"
                                               onchange="updateBulkActions()"
                                               aria-label="Select {{ $applicant->full_name }}">
                                    </td>
                                    <td>
                                        <div class="applicant-info">
                                            <div class="applicant-name">{{ $applicant->full_name }}</div>
                                            <div class="applicant-id">ID: {{ $applicant->application_no }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-email">{{ $applicant->email_address }}</div>
                                            <div class="contact-phone">{{ $applicant->phone_number }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $applicant->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $applicant->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($applicant->accessCode)
                                            <div class="access-code-display">{{ $applicant->accessCode->code }}</div>
                                        @else
                                            <span class="no-code">Not Generated</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($applicant->score)
                                            <div class="score-display">
                                                <span class="score-value {{ $applicant->score >= 75 ? 'score-passed' : 'score-failed' }}">
                                                    {{ $applicant->score }}%
                                                </span>
                                                <span class="score-details">{{ $applicant->correct_answers }}/{{ $applicant->total_questions }}</span>
                                            </div>
                                        @else
                                            <span class="no-score">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" 
                                               class="action-btn action-btn-view" 
                                               aria-label="View details for {{ $applicant->full_name }}">
                                                <span aria-hidden="true">👁️</span>
                                                <span class="sr-only">View Details</span>
                                            </a>
                                            <a href="{{ route('admin.applicants.edit', $applicant->applicant_id) }}" 
                                               class="action-btn action-btn-edit" 
                                               aria-label="Edit {{ $applicant->full_name }}">
                                                <span aria-hidden="true">✏️</span>
                                                <span class="sr-only">Edit</span>
                                            </a>
                                            @if(!$applicant->accessCode)
                                            <button onclick="generateSingleAccessCode({{ $applicant->applicant_id }})" 
                                                    class="action-btn action-btn-code" 
                                                    aria-label="Generate access code for {{ $applicant->full_name }}">
                                                <span aria-hidden="true">🔑</span>
                                                <span class="sr-only">Generate Access Code</span>
                                            </button>
                                            @endif
                                            <button onclick="deleteApplicant({{ $applicant->applicant_id }})" 
                                                    class="action-btn action-btn-delete" 
                                                    aria-label="Delete {{ $applicant->full_name }}">
                                                <span aria-hidden="true">🗑️</span>
                                                <span class="sr-only">Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8">
                                        <div class="empty-state">
                                            <div class="empty-icon">📋</div>
                                            <div class="empty-title">No applicants found</div>
                                            <div class="empty-message">
                                                @if(request()->hasAny(['search', 'status', 'exam_set_id']))
                                                    Try adjusting your search criteria or filters.
                                                @else
                                                    Start by importing applicants or adding them manually.
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(isset($applicants) && $applicants->hasPages())
                    <div class="pagination-wrapper">
                        {{ $applicants->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Generate Access Codes Modal -->
    <div id="generateCodesModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Generate Access Codes</h3>
                <button type="button" class="modal-close" onclick="closeGenerateCodesModal()" aria-label="Close modal">×</button>
            </div>
            <div class="modal-body">
                <form id="generateCodesForm">
                    <div class="form-group">
                        <label for="expiry_hours" class="form-label required">Expiry Hours</label>
                        <input type="number" 
                               id="expiry_hours" 
                               name="expiry_hours" 
                               class="form-control" 
                               value="72" 
                               min="1" 
                               max="168"
                               required
                               aria-describedby="expiry-help">
                        <div id="expiry-help" class="form-help">How many hours should the access code be valid?</div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" 
                                   id="send_email" 
                                   name="send_email" 
                                   class="checkbox-input" 
                                   checked>
                            <label for="send_email" class="checkbox-label">
                                Send email notifications to applicants
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeGenerateCodesModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmGenerateAccessCodes()">Generate Codes</button>
            </div>
        </div>
    </div>

    <!-- Assign Exam Sets Modal -->
    <div id="assignSetsModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Assign Exam Sets</h3>
                <button type="button" class="modal-close" onclick="closeAssignSetsModal()" aria-label="Close modal">×</button>
            </div>
            <div class="modal-body">
                <form id="assignSetsForm">
                    <div class="form-group">
                        <label for="exam_set_id" class="form-label required">Exam Set</label>
                        <select id="exam_set_id" 
                                name="exam_set_id" 
                                class="form-control form-select" 
                                required
                                aria-describedby="exam-set-help">
                            <option value="">Select an exam set</option>
                            @foreach($examSets ?? [] as $examSet)
                                <option value="{{ $examSet->id }}">{{ $examSet->title }}</option>
                            @endforeach
                        </select>
                        <div id="exam-set-help" class="form-help">Choose which exam set to assign to selected applicants</div>
                    </div>
                    <div class="form-group">
                        <label for="assignment_strategy" class="form-label">Assignment Strategy</label>
                        <select id="assignment_strategy" 
                                name="assignment_strategy" 
                                class="form-control form-select"
                                aria-describedby="strategy-help">
                            <option value="replace">Replace existing assignments</option>
                            <option value="only_unassigned">Only assign to unassigned applicants</option>
                        </select>
                        <div id="strategy-help" class="form-help">How to handle applicants who already have exam assignments</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAssignSetsModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmAssignExamSets()">Assign Sets</button>
            </div>
        </div>
    </div>

    <!-- Modern JavaScript Modules -->
    <script src="{{ asset('js/utils/modal-manager.js') }}" defer></script>
    <script src="{{ asset('js/utils/form-validator.js') }}" defer></script>
    <script src="{{ asset('js/modules/applicant-manager.js') }}" defer></script>
</body>
</html>
