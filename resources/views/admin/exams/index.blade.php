@extends('layouts.admin')

@section('title', 'Exam Management')

@php
    $pageTitle = 'Exam Management';
    $pageSubtitle = 'Create and manage examination templates and their question sets';
@endphp

@section('content')
                <!-- Search and Filter Section -->
                <div class="content-section" style="margin-bottom: 20px;">
                    <div class="section-content" style="padding: 20px 30px;">
                        <div class="exams-toolbar">
                            <div class="search-filter-group">
                                <div class="search-box">
                                    <input type="text" placeholder="Search exams..." class="search-input" id="searchInput" value="{{ request('search') }}">
                                    <button class="search-btn" onclick="performSearch()">🔍</button>
                                </div>
                                <select class="filter-select" id="statusFilter" name="status" onchange="applyFilter()">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <a href="{{ route('admin.exams.create') }}" class="section-action">
                                <span class="section-action-icon">➕</span>
                                Create New Exam
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Exams Table -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Exams List</h2>
                        <div class="header-stats">
                            <span class="stat-badge">Total: {{ $examStats['total'] ?? 0 }}</span>
                            <span class="stat-badge">Active: {{ $examStats['active'] ?? 0 }}</span>
                            <span class="stat-badge">Sets: {{ $examStats['total_sets'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="section-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Exam Title</th>
                                    <th style="width: 120px;">Duration</th>
                                    <th style="width: 100px;">Sets</th>
                                    <th style="width: 120px;">Questions</th>
                                    <th style="width: 80px;">Status</th>
                                    <th style="width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exams as $exam)
                                <tr>
                                    <td>#{{ $exam->exam_id }}</td>
                                    <td>
                                        <div class="exam-title">
                                            <strong>{{ $exam->title }}</strong>
                                            @if($exam->description)
                                                <div class="exam-description">{{ Str::limit($exam->description, 60) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="duration-badge">{{ $exam->formatted_duration }}</span>
                                    </td>
                                    <td>
                                        <span class="sets-count">{{ $exam->examSets->count() }} sets</span>
                                    </td>
                                    <td>
                                        @php
                                            $totalQuestions = $exam->examSets->sum(function($set) {
                                                return $set->questions->count();
                                            });
                                        @endphp
                                        <span class="questions-count">{{ $totalQuestions }} questions</span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $exam->is_active ? 'active' : 'inactive' }}">
                                            {{ $exam->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('admin.exams.show', $exam->exam_id) }}" class="action-btn action-btn-view" title="View Details">
                                                👁️
                                            </a>
                                            <a href="{{ route('admin.exam-sets.index', $exam->exam_id) }}" class="action-btn action-btn-sets" title="Manage Sets">
                                                📋
                                            </a>
                                            <a href="{{ route('admin.exams.edit', $exam->exam_id) }}" class="action-btn action-btn-edit" title="Edit Exam">
                                                ✏️
                                            </a>
                                            <button onclick="toggleStatus({{ $exam->exam_id }})" class="action-btn action-btn-toggle" title="Toggle Status">
                                                {{ $exam->is_active ? '🔒' : '🔓' }}
                                            </button>
                                            <button onclick="duplicateExam({{ $exam->exam_id }})" class="action-btn action-btn-duplicate" title="Duplicate Exam">
                                                📄
                                            </button>
                                            <button onclick="deleteExam({{ $exam->exam_id }})" class="action-btn action-btn-delete" title="Delete Exam">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <div class="empty-content">
                                            <span class="empty-icon">📝</span>
                                            <h3>No Exams Found</h3>
                                            <p>No exams have been created yet. Click the "Create New Exam" button to get started.</p>
                                            <a href="{{ route('admin.exams.create') }}" class="btn-primary">
                                                ➕ Create Your First Exam
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        @if($exams->hasPages())
                        <div class="pagination">
                            <div class="pagination-info">
                                Showing {{ $exams->firstItem() ?? 0 }}-{{ $exams->lastItem() ?? 0 }} of {{ $exams->total() }} exams
                            </div>
                            <div class="pagination-controls">
                                {{ $exams->appends(request()->query())->links() }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
        </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
                <button onclick="closeDeleteModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this exam? This action cannot be undone and will also delete all associated exam sets and questions.</p>
            </div>
            <div class="modal-footer">
                <button onclick="closeDeleteModal()" class="btn-secondary">Cancel</button>
                <button onclick="confirmDelete()" class="btn-danger">Delete Exam</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let examToDelete = null;

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

        // Status filter
        function applyFilter() {
            const status = document.getElementById('statusFilter').value;
            const url = new URL(window.location);
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            window.location = url;
        }

        // Toggle exam status
        function toggleStatus(examId) {
            if (confirm('Are you sure you want to toggle the status of this exam?')) {
                fetch(`/admin/exams/${examId}/toggle-status`, {
                    method: 'POST',
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
                        alert(data.message || 'Failed to toggle status');
                    }
                })
                .catch(error => {
                    alert('An error occurred while toggling the status');
                });
            }
        }

        // Duplicate exam
        function duplicateExam(examId) {
            if (confirm('Are you sure you want to duplicate this exam? This will create a copy with all sets and questions.')) {
                fetch(`/admin/exams/${examId}/duplicate`, {
                    method: 'POST',
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
                        alert(data.message || 'Failed to duplicate exam');
                    }
                })
                .catch(error => {
                    alert('An error occurred while duplicating the exam');
                });
            }
        }

        // Delete exam
        function deleteExam(examId) {
            examToDelete = examId;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            examToDelete = null;
        }

        function confirmDelete() {
            if (examToDelete) {
                fetch(`/admin/exams/${examToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeDeleteModal();
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete exam');
                    }
                })
                .catch(error => {
                    alert('An error occurred while deleting the exam');
                });
            }
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Additional styles for exams page */
        .exams-toolbar {
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

        .search-box {
            position: relative;
            flex: 1;
            max-width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 10px 40px 10px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        .search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
        }

        .filter-select {
            padding: 10px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            background: var(--white);
            cursor: pointer;
            min-width: 150px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--yellow-primary);
        }

        .exam-title strong {
            color: var(--maroon-primary);
            font-size: 14px;
        }

        .exam-description {
            color: var(--text-gray);
            font-size: 12px;
            margin-top: 2px;
        }

        .duration-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .sets-count, .questions-count {
            color: var(--text-gray);
            font-size: 12px;
        }

        .action-btn-view { background: #e0f2fe; color: #0277bd; }
        .action-btn-sets { background: #f3e5f5; color: #7b1fa2; }
        .action-btn-duplicate { background: #fff3e0; color: #ef6c00; }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-content {
            max-width: 300px;
            margin: 0 auto;
        }

        .empty-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-content h3 {
            margin: 0 0 8px 0;
            color: var(--text-dark);
            font-size: 18px;
        }

        .empty-content p {
            margin: 0 0 20px 0;
            color: var(--text-gray);
            line-height: 1.5;
        }

        .empty-content .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .exams-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .search-filter-group {
                flex-direction: column;
                gap: 10px;
            }

            .search-box {
                max-width: none;
            }
        }
    </style>
@endpush