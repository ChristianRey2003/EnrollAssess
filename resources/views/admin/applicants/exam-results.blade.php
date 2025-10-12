@extends('layouts.admin')

@section('title', 'Exam Results')

@php
    $pageTitle = 'Exam Results';
    $pageSubtitle = 'View EnrollAssess exam scores and interview evaluations';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/applicants.css') }}" rel="stylesheet">
    <style>
        .score-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .score-excellent { background: #d1fae5; color: #065f46; }
        .score-very-good { background: #dbeafe; color: #1e40af; }
        .score-good { background: #fef3c7; color: #92400e; }
        .score-satisfactory { background: #fed7aa; color: #9a3412; }
        .score-fair { background: #fecaca; color: #991b1b; }
        .score-needs-improvement { background: #f3f4f6; color: #374151; }
        
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .results-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 12px 0;
            flex-wrap: wrap;
        }
        
        .toolbar-left, .toolbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-control {
            height: 32px;
            padding: 4px 8px;
            font-size: 13px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }
        
        .btn {
            height: 32px;
            padding: 4px 12px;
            font-size: 13px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-primary {
            background: #991b1b;
            color: white;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .results-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .data-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .data-table tr:hover {
            background: rgba(255, 215, 0, 0.1);
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-exam-completed { background: #dbeafe; color: #1e40af; }
        .status-interview-available { background: #d1fae5; color: #065f46; }
        .status-interview-claimed { background: #fed7aa; color: #9a3412; }
        .status-interview-scheduled { background: #e0e7ff; color: #3730a3; }
        .status-interview-completed { background: #f3e8ff; color: #6b21a8; }
        .status-admitted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fecaca; color: #991b1b; }
        
        @media (max-width: 768px) {
            .results-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .toolbar-left, .toolbar-right {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>
@endpush

@section('content')
    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_with_scores'] ?? 0 }}</div>
            <div class="stat-label">With EnrollAssess Scores</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['with_interview_scores'] ?? 0 }}</div>
            <div class="stat-label">With Interview Scores</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['average_enrollassess'] ?? 0 }}%</div>
            <div class="stat-label">Avg EnrollAssess Score</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['average_interview'] ?? 0 }}%</div>
            <div class="stat-label">Avg Interview Score</div>
        </div>
    </section>

    <!-- Results Toolbar -->
    <div class="results-toolbar">
        <div class="toolbar-left">
            <input type="text" 
                   id="searchInput" 
                   class="form-control" 
                   placeholder="Search applicants..." 
                   value="{{ request('search') }}"
                   style="width: 200px;"
                   aria-label="Search applicants">
            <button onclick="performSearch()" class="btn btn-secondary">Search</button>
        </div>
        <div class="toolbar-right">
            <select id="statusFilter" class="form-control" onchange="applyFilter()" style="width: 140px;">
                <option value="">All Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ ucwords(str_replace('-', ' ', $status)) }}
                    </option>
                @endforeach
            </select>
            <select id="courseFilter" class="form-control" onchange="applyFilter()" style="width: 140px;">
                <option value="">All Courses</option>
                @foreach($courses as $course)
                    <option value="{{ $course }}" {{ request('course') == $course ? 'selected' : '' }}>
                        {{ $course }}
                    </option>
                @endforeach
            </select>
            <input type="number" 
                   id="scoreMinFilter" 
                   class="form-control" 
                   placeholder="Min Score" 
                   value="{{ request('score_min') }}"
                   style="width: 100px;"
                   min="0" max="100">
            <input type="number" 
                   id="scoreMaxFilter" 
                   class="form-control" 
                   placeholder="Max Score" 
                   value="{{ request('score_max') }}"
                   style="width: 100px;"
                   min="0" max="100">
            <button onclick="applyFilter()" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.applicants.index') }}" class="btn btn-secondary">Back to Applicants</a>
        </div>
    </div>

    <!-- Results Table -->
    <div class="results-table">
        <table class="data-table">
            <thead>
                <tr>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'first_name', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                           style="color: inherit; text-decoration: none;">
                            Applicant
                            @if(request('sort_by') == 'first_name')
                                <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th>Course</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'enrollassess_score', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                           style="color: inherit; text-decoration: none;">
                            EnrollAssess Score
                            @if(request('sort_by') == 'enrollassess_score')
                                <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'interview_score', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                           style="color: inherit; text-decoration: none;">
                            Interview Score
                            @if(request('sort_by') == 'interview_score')
                                <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th>Exam Set</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                           style="color: inherit; text-decoration: none;">
                            Status
                            @if(request('sort_by') == 'status')
                                <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applicants as $applicant)
                    <tr>
                        <td>
                            <div style="font-weight: 500;">{{ $applicant->full_name }}</div>
                            <div style="font-size: 12px; color: #6b7280;">{{ $applicant->application_no }}</div>
                            <div style="font-size: 12px; color: #6b7280;">{{ $applicant->email_address }}</div>
                        </td>
                        <td>{{ $applicant->preferred_course ?? 'N/A' }}</td>
                        <td>
                            @if($applicant->enrollassess_score || $applicant->exam_percentage)
                                @php
                                    $percentage = $applicant->exam_percentage ?? $applicant->enrollassess_score ?? 0;
                                    $class = 'score-needs-improvement';
                                    if ($percentage >= 95) $class = 'score-excellent';
                                    elseif ($percentage >= 85) $class = 'score-very-good';
                                    elseif ($percentage >= 75) $class = 'score-good';
                                    elseif ($percentage >= 65) $class = 'score-satisfactory';
                                    elseif ($percentage >= 50) $class = 'score-fair';
                                @endphp
                                <div class="score-badge {{ $class }}">
                                    {{ round($percentage, 2) }}%
                                </div>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                                    {{ $applicant->results->where('is_correct', true)->count() }}/{{ $applicant->results->count() }} correct
                                </div>
                            @else
                                <span style="color: #9ca3af;">No score</span>
                            @endif
                        </td>
                        <td>
                            @if($applicant->interview_score)
                                @php
                                    $interviewScore = $applicant->interview_score;
                                    $class = 'score-needs-improvement';
                                    if ($interviewScore >= 95) $class = 'score-excellent';
                                    elseif ($interviewScore >= 85) $class = 'score-very-good';
                                    elseif ($interviewScore >= 75) $class = 'score-good';
                                    elseif ($interviewScore >= 65) $class = 'score-satisfactory';
                                    elseif ($interviewScore >= 50) $class = 'score-fair';
                                @endphp
                                <div class="score-badge {{ $class }}">
                                    {{ $interviewScore }}%
                                </div>
                            @else
                                <span style="color: #9ca3af;">Not evaluated</span>
                            @endif
                        </td>
                        <td>
                            @if($applicant->accessCode && $applicant->accessCode->exam)
                                <div style="font-weight: 500;">{{ $applicant->accessCode->exam->title }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $applicant->accessCode->exam->duration_minutes ?? 'N/A' }} mins</div>
                            @else
                                <span style="color: #9ca3af;">No exam assigned</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ str_replace('-', '-', $applicant->status) }}">
                                {{ ucwords(str_replace('-', ' ', $applicant->status)) }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" 
                                   class="btn btn-secondary" 
                                   style="padding: 2px 6px; font-size: 11px;"
                                   title="View Details">
                                    View
                                </a>
                                @if($applicant->latestInterview)
                                    <a href="{{ route('admin.interview-detail', $applicant->latestInterview->interview_id) }}" 
                                       class="btn btn-primary" 
                                       style="padding: 2px 6px; font-size: 11px;"
                                       title="View Interview">
                                        Interview
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                            No exam results found. Only applicants who completed the EnrollAssess exam are shown here.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($applicants->hasPages())
        <div style="margin-top: 20px; display: flex; justify-content: center;">
            {{ $applicants->appends(request()->query())->links() }}
        </div>
    @endif
@endsection

@push('scripts')
<script>
function performSearch() {
    const searchValue = document.getElementById('searchInput').value;
    updateUrl({ search: searchValue, page: 1 });
}

function applyFilter() {
    const status = document.getElementById('statusFilter').value;
    const course = document.getElementById('courseFilter').value;
    const scoreMin = document.getElementById('scoreMinFilter').value;
    const scoreMax = document.getElementById('scoreMaxFilter').value;
    
    updateUrl({
        status: status,
        course: course,
        score_min: scoreMin,
        score_max: scoreMax,
        page: 1
    });
}

function updateUrl(params) {
    const url = new URL(window.location);
    
    Object.keys(params).forEach(key => {
        if (params[key] && params[key] !== '') {
            url.searchParams.set(key, params[key]);
        } else {
            url.searchParams.delete(key);
        }
    });
    
    window.location.href = url.toString();
}

// Handle Enter key in search input
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        performSearch();
    }
});

// Handle Enter key in score filters
document.getElementById('scoreMinFilter').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilter();
    }
});

document.getElementById('scoreMaxFilter').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilter();
    }
});
</script>
@endpush
