@extends('layouts.admin')

@section('title', 'Exam Results')

@php
    $pageTitle = 'Exam Results';
    $pageSubtitle = 'View applicant scores, evaluations, and overall admission ratings';
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
            <div class="stat-value">{{ $stats['qualifiers_count'] ?? 0 }}</div>
            <div class="stat-label">Qualifiers</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['average_overall'] ?? 0 }}</div>
            <div class="stat-label">Avg Overall Rating</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['average_uee'] ?? 0 }}</div>
            <div class="stat-label">Avg UEE</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['average_gwa'] ?? 0 }}</div>
            <div class="stat-label">Avg GWA</div>
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
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'score', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                           style="color: inherit; text-decoration: none;">
                            UEE
                            @if(request('sort_by') == 'score')
                                <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'card_tor_gwa', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                           style="color: inherit; text-decoration: none;">
                            GWA
                            @if(request('sort_by') == 'card_tor_gwa')
                                <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'enrollassess_score', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                           style="color: inherit; text-decoration: none;">
                            EnrollAssess
                            @if(request('sort_by') == 'enrollassess_score')
                                <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'interview_score', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                           style="color: inherit; text-decoration: none;">
                            Interview
                            @if(request('sort_by') == 'interview_score')
                                <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'overall_rating', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                           style="color: inherit; text-decoration: none;">
                            Overall
                            @if(request('sort_by') == 'overall_rating')
                                <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </a>
                    </th>
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
                    @php
                        $overallRating = $applicant->getOverallRating();
                        $hasAllScores = $applicant->hasAllRequiredScores();
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight: 500;">{{ $applicant->full_name }}</div>
                            <div style="font-size: 12px; color: #6b7280;">{{ $applicant->application_no }}</div>
                            @if(!$hasAllScores)
                                @php
                                    $missing = $applicant->getMissingScores();
                                @endphp
                                <div style="font-size: 11px; color: #dc2626; margin-top: 2px;">
                                    Missing: {{ implode(', ', array_map(fn($s) => match($s) {
                                        'University Entrance Examination' => 'UEE',
                                        'CARD/TOR GWA' => 'GWA',
                                        'EnrollAssess Exam' => 'Exam',
                                        'Interview Evaluation' => 'Interview',
                                        default => $s
                                    }, $missing)) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($applicant->score)
                                <div style="font-weight: 500;">{{ round($applicant->score, 2) }}</div>
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($applicant->card_tor_gwa)
                                <div style="font-weight: 500;">{{ round($applicant->card_tor_gwa, 2) }}</div>
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($applicant->enrollassess_score)
                                <div style="font-weight: 500;">{{ round($applicant->enrollassess_score, 2) }}%</div>
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($applicant->interview_score)
                                <div style="font-weight: 500;">{{ round($applicant->interview_score, 2) }}%</div>
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($overallRating)
                                @php
                                    $rating = $overallRating['overall_rating'];
                                    $class = 'score-needs-improvement';
                                    if ($rating >= 95) $class = 'score-excellent';
                                    elseif ($rating >= 90) $class = 'score-very-good';
                                    elseif ($rating >= 85) $class = 'score-good';
                                    elseif ($rating >= 75) $class = 'score-satisfactory';
                                    elseif ($rating >= 70) $class = 'score-fair';
                                    
                                    $scoringService = app(\App\Services\AdmissionScoringService::class);
                                    $verbal = $scoringService->getVerbalDescription($rating);
                                @endphp
                                <div class="score-badge {{ $class }}" style="display: block; margin-bottom: 2px;">
                                    {{ round($rating, 2) }}
                                </div>
                                <div style="font-size: 11px; color: #6b7280;">{{ $verbal }}</div>
                            @else
                                <span style="color: #9ca3af;">-</span>
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
                                    <a href="{{ route('admin.interviews.show', $applicant->latestInterview->interview_id) }}" 
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
                        <td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">
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
    
    updateUrl({
        status: status,
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

// AJAX Pagination
document.addEventListener('click', function(e) {
    const paginationLink = e.target.closest('.pagination a, .pagination-wrapper a');
    
    if (paginationLink && paginationLink.href) {
        e.preventDefault();
        e.stopPropagation();
        const url = paginationLink.href;
        
        if (!url || url === '#' || url === 'javascript:void(0)') return;
        
        const tableBody = document.querySelector('table tbody');
        const paginationWrapper = document.querySelector('.pagination-wrapper') || document.querySelector('[style*="margin-top: 20px"]');
        
        if (tableBody) {
            tableBody.style.opacity = '0.5';
            tableBody.style.pointerEvents = 'none';
        }
        if (paginationWrapper) {
            paginationWrapper.style.opacity = '0.5';
            paginationWrapper.style.pointerEvents = 'none';
        }
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.applicants && tableBody) {
                let html = '';
                const from = data.pagination.from || 0;
                
                if (data.applicants.length === 0) {
                    html = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">No exam results found. Only applicants who completed the EnrollAssess exam are shown here.</td></tr>';
                } else {
                    data.applicants.forEach((applicant, index) => {
                        // This is a simplified version - you may need to adjust based on your actual data structure
                        const statusClass = (applicant.status || '').replace(/-/g, '-');
                        const statusText = (applicant.status || '').split('-').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(' ');
                        
                        const score = applicant.score ? Number(applicant.score).toFixed(2) : '<span style="color: #9ca3af;">-</span>';
                        const gwa = applicant.card_tor_gwa ? Number(applicant.card_tor_gwa).toFixed(2) : '<span style="color: #9ca3af;">-</span>';
                        const examScore = applicant.enrollassess_score ? Number(applicant.enrollassess_score).toFixed(2) + '%' : '<span style="color: #9ca3af;">-</span>';
                        const interviewScore = applicant.interview_score ? Number(applicant.interview_score).toFixed(2) + '%' : '<span style="color: #9ca3af;">-</span>';
                        
                        html += `<tr>
                            <td>
                                <div style="font-weight: 500;">${applicant.full_name || ''}</div>
                                <div style="font-size: 12px; color: #6b7280;">${applicant.application_no || applicant.formatted_applicant_no || 'N/A'}</div>
                            </td>
                            <td>${score}</td>
                            <td>${gwa}</td>
                            <td>${examScore}</td>
                            <td>${interviewScore}</td>
                            <td>-</td>
                            <td><span class="status-badge status-${statusClass}">${statusText}</span></td>
                            <td>
                                <div style="display: flex; gap: 4px;">
                                    <a href="/admin/applicants/${applicant.applicant_id}" class="btn btn-secondary" style="padding: 2px 6px; font-size: 11px;">View</a>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                
                tableBody.innerHTML = html;
                tableBody.style.opacity = '1';
                tableBody.style.pointerEvents = '';
                
                if (data.pagination_html && paginationWrapper) {
                    paginationWrapper.innerHTML = data.pagination_html;
                }
                
                if (paginationWrapper) {
                    paginationWrapper.style.opacity = '1';
                    paginationWrapper.style.pointerEvents = '';
                }
                
                window.history.pushState({}, '', url);
            }
        })
        .catch(error => {
            console.error('Pagination error:', error);
            window.location.href = url;
        });
    }
});

// Removed score filter Enter handlers as score filters were removed
</script>
@endpush
