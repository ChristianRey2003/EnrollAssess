@extends('layouts.admin')

@section('title', 'Generate Reports')

@php
    $pageTitle = 'Generate Reports';
    $pageSubtitle = 'Export and analyze examination data for decision making';
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
        
        .floating-actions {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10;
            display: flex;
            flex-direction: row;
        }
        
        .floating-actions .action-btn {
            padding: 6px 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.2s;
            text-decoration: none;
            color: #374151;
            white-space: nowrap;
        }
        
        .floating-actions .action-btn:hover {
            background: #f3f4f6;
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
            padding: 8px 14px;
            font-size: 12px;
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
        
        /* Drawer Styles */
        .drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            display: none;
            z-index: 1000;
            transition: opacity 0.3s ease;
        }

        .drawer-overlay.active {
            display: block;
            opacity: 1;
        }

        .drawer {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            max-width: 85vw;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 8px rgba(0,0,0,0.1);
            z-index: 1001;
            overflow-y: auto;
            transition: right 0.3s ease;
        }

        .drawer.active {
            right: 0;
        }

        .drawer-header {
            position: sticky;
            top: 0;
            background: white;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        .drawer-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .drawer-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .drawer-close:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .drawer-body {
            padding: 16px;
        }

        .drawer-footer {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 12px 16px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .drawer .form-group {
            margin-bottom: 12px;
        }

        .drawer .filter-label {
            font-size: 12px;
            margin-bottom: 4px;
        }

        .drawer .filter-input,
        .drawer .filter-select {
            font-size: 13px;
            padding: 6px 10px;
            height: 36px;
        }

        .drawer .btn-primary-export {
            padding: 6px 12px;
            font-size: 12px;
            height: 36px;
        }

        .drawer .btn-secondary {
            padding: 6px 12px;
            font-size: 12px;
            height: 36px;
        }
        
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
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['qualifiers_count'] ?? 0 }}</div>
                        <div class="stat-label">Qualifiers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['average_overall'] ?? 0 }}</div>
                        <div class="stat-label">Avg Overall Rating</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['average_uee'] ?? 0 }}</div>
                        <div class="stat-label">Avg UEE</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['average_gwa'] ?? 0 }}</div>
                        <div class="stat-label">Avg GWA</div>
                    </div>
                </section>

                <!-- Exam Results Table Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Exam Results</h2>
                    </div>
                    <div class="section-content" style="padding: 20px;">
                        <!-- Results Container -->
                        <div class="applicants-container" style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); border: 1px solid #E5E7EB; overflow: hidden;">
                            <!-- Results Toolbar -->
                            <div class="results-toolbar" style="display: flex; justify-content: space-between; align-items: center; gap: 15px; padding: 20px; border-bottom: 1px solid #E5E7EB;">
                                <div class="toolbar-left" style="display: flex; align-items: center; gap: 10px;">
                                    <div style="position: relative; width: 220px;">
                                        <input type="text" 
                                               id="searchInput" 
                                               class="form-control form-control-sm" 
                                               placeholder="Search..." 
                                               value="{{ request('search') }}"
                                               style="width: 100%; height: 26px; padding: 4px 32px 4px 8px;"
                                               aria-label="Search applicants">
                                        <svg style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; pointer-events: none; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <select id="statusFilter" class="form-select form-select-sm" onchange="applyFilter()" style="width: 180px; min-width: 180px; height: 40px; padding: 4px 28px 4px 8px;">
                                        <option value="">All Status</option>
                                        @foreach($statuses ?? [] as $status)
                                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('-', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <select id="sortFilter" class="form-select form-select-sm" onchange="applySort()" style="width: 180px; min-width: 180px; height: 40px; padding: 4px 28px 4px 8px;">
                                        <option value="exam_completed_at_desc" {{ request('sort_by') == 'exam_completed_at' && request('sort_order') == 'desc' ? 'selected' : (!request('sort_by') ? 'selected' : '') }}>Newest First</option>
                                        <option value="exam_completed_at_asc" {{ request('sort_by') == 'exam_completed_at' && request('sort_order') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                                        <option value="enrollassess_score_desc" {{ request('sort_by') == 'enrollassess_score' && request('sort_order') == 'desc' ? 'selected' : '' }}>Score: High to Low</option>
                                        <option value="enrollassess_score_asc" {{ request('sort_by') == 'enrollassess_score' && request('sort_order') == 'asc' ? 'selected' : '' }}>Score: Low to High</option>
                                        <option value="first_name_asc" {{ request('sort_by') == 'first_name' && request('sort_order') == 'asc' ? 'selected' : '' }}>Name: A to Z</option>
                                        <option value="first_name_desc" {{ request('sort_by') == 'first_name' && request('sort_order') == 'desc' ? 'selected' : '' }}>Name: Z to A</option>
                                    </select>
                                </div>
                                <div class="toolbar-right" style="display: flex; align-items: center; gap: 10px;">
                                    <button type="button" onclick="openEVSUDrawer()" class="btn-primary-export" style="height: 40px; padding: 8px 16px; font-size: 13px;">EVSU Results</button>
                                    <button type="button" onclick="openQualifiersDrawer()" class="btn-primary-export" style="height: 40px; padding: 8px 16px; font-size: 13px;">Qualifiers List</button>
                                    <button type="button" onclick="openSignatureSettingsDrawer()" class="btn-secondary" style="height: 40px; padding: 8px 16px; font-size: 13px; background: #6b7280; color: white; width: 40px; display: flex; align-items: center; justify-content: center;">⚙️</button>
                                </div>
                            </div>

                            <!-- Results Table -->
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                <thead style="background-color: white !important; color: #1F2937 !important;">
                                    <tr>
                                        <th style="font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-left">
                                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'first_name', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                               style="color: inherit; text-decoration: none; font-weight: bold;">
                                                Applicant
                                                @if(request('sort_by') == 'first_name')
                                                    <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </a>
                                        </th>
                                        <th style="font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">
                                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'score', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                               style="color: inherit; text-decoration: none; font-weight: bold;">
                                                UEE
                                                @if(request('sort_by') == 'score')
                                                    <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </a>
                                        </th>
                                        <th style="font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">
                                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'card_tor_gwa', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                               style="color: inherit; text-decoration: none; font-weight: bold;">
                                                GWA
                                                @if(request('sort_by') == 'card_tor_gwa')
                                                    <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </a>
                                        </th>
                                        <th style="font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">
                                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'enrollassess_score', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                               style="color: inherit; text-decoration: none; font-weight: bold;">
                                                EnrollAssess
                                                @if(request('sort_by') == 'enrollassess_score')
                                                    <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </a>
                                        </th>
                                        <th style="font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">
                                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'interview_score', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                               style="color: inherit; text-decoration: none; font-weight: bold;">
                                                Interview
                                                @if(request('sort_by') == 'interview_score')
                                                    <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </a>
                                        </th>
                                        <th style="font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">
                                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'overall_rating', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                               style="color: inherit; text-decoration: none; font-weight: bold;">
                                                Overall
                                                @if(request('sort_by') == 'overall_rating')
                                                    <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </a>
                                        </th>
                                        <th style="font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">
                                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                               style="color: inherit; text-decoration: none; font-weight: bold;">
                                                Status
                                                @if(request('sort_by') == 'status')
                                                    <span>{{ request('sort_order') == 'asc' ? '↑' : '↓' }}</span>
                                                @endif
                                            </a>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applicants ?? [] as $applicant)
                                        @php
                                            $overallRating = $applicant->getOverallRating();
                                            $hasAllScores = $applicant->hasAllRequiredScores();
                                        @endphp
                                        <tr style="position: relative;" 
                                            onmouseover="showActions({{ $applicant->applicant_id }})" 
                                            onmouseout="hideActions({{ $applicant->applicant_id }})">
                                            <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                                <div>{{ $applicant->full_name }}</div>
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
                                            <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                                @if($applicant->score)
                                                    <div>{{ round($applicant->score, 2) }}</div>
                                                @else
                                                    <span style="color: #9ca3af;">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                                @if($applicant->card_tor_gwa)
                                                    <div>{{ round($applicant->card_tor_gwa, 2) }}</div>
                                                @else
                                                    <span style="color: #9ca3af;">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                                @if($applicant->enrollassess_score)
                                                    <div>{{ round($applicant->enrollassess_score, 2) }}%</div>
                                                @else
                                                    <span style="color: #9ca3af;">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                                @if($applicant->interview_score)
                                                    <div>{{ round($applicant->interview_score, 2) }}%</div>
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
                                            <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                                <span class="badge bg-secondary">
                                                    {{ ucwords(str_replace('-', ' ', $applicant->status)) }}
                                                </span>
                                                <!-- Floating Actions -->
                                                <div id="actions-{{ $applicant->applicant_id }}" class="floating-actions" style="display: none;">
                                                    <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}" 
                                                       class="action-btn"
                                                       title="View Details">
                                                        View
                                                    </a>
                                                    @if($applicant->latestInterview)
                                                        <a href="{{ route('admin.interviews.show', $applicant->latestInterview->interview_id) }}" 
                                                           class="action-btn"
                                                           style="color: #800020;"
                                                           title="View Interview">
                                                            Interview
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="text-muted">
                                                    <h5>No exam results found</h5>
                                                    <p class="mb-0">Only applicants who completed the EnrollAssess exam are shown here.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            </div>

                            <!-- Pagination -->
                            @if(isset($applicants) && $applicants->hasPages())
                                <div class="pagination-wrapper" style="padding: 20px;">
                                    {{ $applicants->appends(request()->query())->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Collapsible: Student Information Reports -->
                <div class="content-section collapsible-section">
                    <div class="section-header" onclick="toggleSection('studentInfoReportsSection')">
                        <div>
                            <h2 class="section-title">Student Information Reports</h2>
                        </div>
                        <button class="toggle-btn" id="studentInfoReportsToggle">
                            <span class="toggle-icon">▼</span>
                        </button>
                    </div>
                    <div class="section-content collapsible-content" id="studentInfoReportsSection" style="display: none; padding: 20px;">
                        <div class="additional-reports-grid">
                            <!-- Geographic Performance Report -->
                            <div class="report-card">
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Geographic Performance</h3>
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
                                    <button onclick="generateGeographicReport()" class="btn-report-action">Generate PDF</button>
                                </div>
                            </div>

                            <!-- Strand Distribution Report -->
                            <div class="report-card">
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Strand Distribution</h3>
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
                                    <button onclick="generateStrandReport()" class="btn-report-action">Generate PDF</button>
                                </div>
                            </div>

                            <!-- Demographic Overview Report -->
                            <div class="report-card">
                                <div class="report-card-content">
                                    <h3 class="report-card-title">Demographic Overview</h3>
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
                                    <button onclick="generateDemographicReport()" class="btn-report-action">Generate PDF</button>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px; color: #6B7280;">
                                        <p>No reports generated yet.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
@endsection

@push('modals')
    <!-- EVSU Results Drawer -->
    <div id="evsuDrawerOverlay" class="drawer-overlay" onclick="closeEVSUDrawer()"></div>
    <div id="evsuDrawer" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">EVSU Entrance Results</h3>
            <button type="button" class="drawer-close" onclick="closeEVSUDrawer()">×</button>
        </div>
        <div class="drawer-body">
            <form id="evsuResultsForm" class="export-form">
                <input type="hidden" name="status" value="interview-completed">
                <div class="form-group">
                    <label for="evsu_limit" class="filter-label">Top N Applicants</label>
                    <input id="evsu_limit" name="limit" type="number" min="1" step="1" value="120" class="filter-input" style="width: 100%;">
                </div>
                <div class="form-group">
                    <label for="evsu_sort" class="filter-label">Sort Order</label>
                    <select id="evsu_sort" name="sort" class="filter-select" style="width: 100%;">
                        <option value="overall_desc">Overall Rating (High → Low)</option>
                        <option value="overall_asc">Overall Rating (Low → High)</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="drawer-footer">
            <button type="button" onclick="closeEVSUDrawer()" class="btn-secondary">Cancel</button>
            <button type="button" onclick="generateEVSUFromDrawer('xlsx')" class="btn-primary-export">Export XLSX</button>
            <button type="button" onclick="generateEVSUFromDrawer('pdf')" class="btn-primary-export" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">Export PDF</button>
        </div>
    </div>

    <!-- Qualifiers List Drawer -->
    <div id="qualifiersDrawerOverlay" class="drawer-overlay" onclick="closeQualifiersDrawer()"></div>
    <div id="qualifiersDrawer" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">Qualifiers List</h3>
            <button type="button" class="drawer-close" onclick="closeQualifiersDrawer()">×</button>
        </div>
        <div class="drawer-body">
            <div class="export-form">
                <div class="form-group">
                    <label for="qualifiersSlots" class="filter-label">Number of Slots *</label>
                    <input type="number" 
                           id="qualifiersSlots" 
                           name="qualifiersSlots" 
                           class="filter-input" 
                           placeholder="e.g., 112"
                           min="1"
                           max="500"
                           required
                           style="width: 100%;">
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" onclick="closeQualifiersDrawer()" class="btn-secondary">Cancel</button>
            <button onclick="generateQualifiersFromDrawer('docx')" class="btn-primary-export">Generate DOCX</button>
            <button onclick="generateQualifiersFromDrawer('pdf')" class="btn-primary-export" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">Generate PDF</button>
        </div>
    </div>

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

    <!-- Archive Confirmation Modal -->
    <div id="archiveConfirmModal" class="modal-overlay" onclick="if(event.target === this) closeArchiveConfirmModal()">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Archive All Reports</h3>
                <button onclick="closeArchiveConfirmModal()" class="modal-close">×</button>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div style="margin-bottom: 20px;">
                    <p style="color: #6b7280; margin-bottom: 16px;">You are about to archive all reports. This will move them to the archived section where they can be restored or permanently deleted later.</p>
                    <div style="background: #f3f4f6; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #6b7280; font-weight: 500;">Total Reports:</span>
                            <span style="color: #1f2937; font-weight: 600;" id="archiveModalCount">-</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #6b7280; font-weight: 500;">Total File Size:</span>
                            <span style="color: #1f2937; font-weight: 600;" id="archiveModalSize">-</span>
                        </div>
                    </div>
                    <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 4px;">
                        <p style="margin: 0; color: #92400e; font-size: 13px;">
                            <strong>Note:</strong> Archived reports can be restored from the "Archived Reports" section below.
                        </p>
                    </div>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: #374151; font-weight: 500; font-size: 14px;">
                        <input type="checkbox" id="archiveConfirmCheckbox" onchange="checkArchiveConfirm()" style="width: 18px; height: 18px; cursor: pointer; accent-color: #dc2626;">
                        <span>I confirm that I want to archive all reports</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeArchiveConfirmModal()" class="btn-secondary">Cancel</button>
                <button onclick="confirmArchiveAll()" class="btn-primary" id="archiveConfirmBtn" disabled style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">Archive All Reports</button>
            </div>
        </div>
    </div>

    <!-- Signature Settings Drawer -->
    <div id="signatureSettingsDrawerOverlay" class="drawer-overlay" onclick="closeSignatureSettingsDrawer()"></div>
    <div id="signatureSettingsDrawer" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">Report Signature Settings</h3>
            <button type="button" class="drawer-close" onclick="closeSignatureSettingsDrawer()">×</button>
        </div>
        <div class="drawer-body">
            <p style="margin-bottom: 20px; color: #6b7280; font-size: 14px;">
                Configure the names and titles that appear in report signatures. These settings apply to all future reports (XLSX, DOCX, and PDF).
            </p>

            <form id="signatureSettingsForm">
                <!-- Document Information -->
                <div style="margin-bottom: 25px;">
                    <h4 style="margin-bottom: 15px; color: #1f2937; font-size: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">Document Information</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Control No.</label>
                            <input type="text" id="control_no" name="control_no" class="form-control" style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Revision No.</label>
                            <input type="text" id="revision_no" name="revision_no" class="form-control" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <!-- Prepared By -->
                <div style="margin-bottom: 25px;">
                    <h4 style="margin-bottom: 15px; color: #1f2937; font-size: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">Prepared By</h4>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Name</label>
                            <input type="text" id="prepared_by_name" name="prepared_by_name" class="form-control" style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Title</label>
                            <input type="text" id="prepared_by_title" name="prepared_by_title" class="form-control" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <!-- Noted -->
                <div style="margin-bottom: 25px;">
                    <h4 style="margin-bottom: 15px; color: #1f2937; font-size: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">Noted</h4>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Name</label>
                            <input type="text" id="noted_name" name="noted_name" class="form-control" style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Title</label>
                            <input type="text" id="noted_title" name="noted_title" class="form-control" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <!-- Recommending Approval -->
                <div style="margin-bottom: 25px;">
                    <h4 style="margin-bottom: 15px; color: #1f2937; font-size: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">Recommending Approval</h4>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Name</label>
                            <input type="text" id="recommending_name" name="recommending_name" class="form-control" style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Title</label>
                            <input type="text" id="recommending_title" name="recommending_title" class="form-control" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <!-- Approved -->
                <div style="margin-bottom: 25px;">
                    <h4 style="margin-bottom: 15px; color: #1f2937; font-size: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">Approved</h4>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Name</label>
                            <input type="text" id="approved_name" name="approved_name" class="form-control" style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Title</label>
                            <input type="text" id="approved_title" name="approved_title" class="form-control" style="width: 100%;">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="drawer-footer">
            <button type="button" onclick="closeSignatureSettingsDrawer()" class="btn-secondary">Cancel</button>
            <button type="button" onclick="saveSignatureSettings()" class="btn-primary" id="saveSignatureBtn">Save Settings</button>
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

        // Get filters as object (empty since Advanced Filters removed)
        function getFiltersObject() {
            return {};
        }

        // Get filters as readable string
        function getAppliedFilters() {
            return 'Default filters';
        }

        // Signature Settings Drawer Functions
        function openSignatureSettingsDrawer() {
            const overlay = document.getElementById('signatureSettingsDrawerOverlay');
            const drawer = document.getElementById('signatureSettingsDrawer');
            if (overlay && drawer) {
                overlay.classList.add('active');
                drawer.classList.add('active');
                loadSignatureSettings();
            }
        }

        function closeSignatureSettingsDrawer() {
            const overlay = document.getElementById('signatureSettingsDrawerOverlay');
            const drawer = document.getElementById('signatureSettingsDrawer');
            if (overlay && drawer) {
                overlay.classList.remove('active');
                drawer.classList.remove('active');
            }
        }

        async function loadSignatureSettings() {
            try {
                const response = await fetch('/admin/reports/signature-settings', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();
                if (data.success && data.settings) {
                    document.getElementById('control_no').value = data.settings.control_no || '';
                    document.getElementById('revision_no').value = data.settings.revision_no || '';
                    document.getElementById('prepared_by_name').value = data.settings.prepared_by_name || '';
                    document.getElementById('prepared_by_title').value = data.settings.prepared_by_title || '';
                    document.getElementById('noted_name').value = data.settings.noted_name || '';
                    document.getElementById('noted_title').value = data.settings.noted_title || '';
                    document.getElementById('recommending_name').value = data.settings.recommending_name || '';
                    document.getElementById('recommending_title').value = data.settings.recommending_title || '';
                    document.getElementById('approved_name').value = data.settings.approved_name || '';
                    document.getElementById('approved_title').value = data.settings.approved_title || '';
                }
            } catch (error) {
                console.error('Error loading signature settings:', error);
                showNotification('Failed to load signature settings', 'error');
            }
        }

        async function saveSignatureSettings() {
            const btn = document.getElementById('saveSignatureBtn');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Saving...';

            try {
                const formData = {
                    control_no: document.getElementById('control_no').value,
                    revision_no: document.getElementById('revision_no').value,
                    prepared_by_name: document.getElementById('prepared_by_name').value,
                    prepared_by_title: document.getElementById('prepared_by_title').value,
                    noted_name: document.getElementById('noted_name').value,
                    noted_title: document.getElementById('noted_title').value,
                    recommending_name: document.getElementById('recommending_name').value,
                    recommending_title: document.getElementById('recommending_title').value,
                    approved_name: document.getElementById('approved_name').value,
                    approved_title: document.getElementById('approved_title').value,
                };

                const response = await fetch('/admin/reports/signature-settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(formData),
                });

                const data = await response.json();
                if (data.success) {
                    showNotification('Signature settings saved successfully!', 'success');
                    closeSignatureSettingsDrawer();
                } else {
                    showNotification(data.message || 'Failed to save signature settings', 'error');
                }
            } catch (error) {
                console.error('Error saving signature settings:', error);
                showNotification('Failed to save signature settings', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        }

        // Close modal when clicking outside (handled by onclick in modal-overlay)

        // Drawer Functions
        function openEVSUDrawer() {
            const overlay = document.getElementById('evsuDrawerOverlay');
            const drawer = document.getElementById('evsuDrawer');
            if (overlay && drawer) {
                overlay.classList.add('active');
                drawer.classList.add('active');
            }
        }

        function closeEVSUDrawer() {
            const overlay = document.getElementById('evsuDrawerOverlay');
            const drawer = document.getElementById('evsuDrawer');
            if (overlay && drawer) {
                overlay.classList.remove('active');
                drawer.classList.remove('active');
            }
        }

        function openQualifiersDrawer() {
            const overlay = document.getElementById('qualifiersDrawerOverlay');
            const drawer = document.getElementById('qualifiersDrawer');
            if (overlay && drawer) {
                overlay.classList.add('active');
                drawer.classList.add('active');
            }
        }

        function closeQualifiersDrawer() {
            const overlay = document.getElementById('qualifiersDrawerOverlay');
            const drawer = document.getElementById('qualifiersDrawer');
            if (overlay && drawer) {
                overlay.classList.remove('active');
                drawer.classList.remove('active');
            }
        }

        // Exam Results Table Functions
        function showActions(applicantId) {
            const actionsDiv = document.getElementById('actions-' + applicantId);
            if (actionsDiv) {
                actionsDiv.style.display = 'flex';
            }
        }

        function hideActions(applicantId) {
            const actionsDiv = document.getElementById('actions-' + applicantId);
            if (actionsDiv) {
                actionsDiv.style.display = 'none';
            }
        }

        // Auto-search functionality
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const searchValue = e.target.value.trim();
                
                searchTimeout = setTimeout(function() {
                    updateUrl({ search: searchValue, page: 1 });
                }, 500); // 500ms debounce
            });
        }

        function applyFilter() {
            const status = document.getElementById('statusFilter').value;
            
            updateUrl({
                status: status,
                page: 1
            });
        }

        function applySort() {
            const sortValue = document.getElementById('sortFilter').value;
            
            // Parse the sort value (format: "field_asc" or "field_desc")
            const lastUnderscoreIndex = sortValue.lastIndexOf('_');
            const sortBy = sortValue.substring(0, lastUnderscoreIndex);
            const sortOrder = sortValue.substring(lastUnderscoreIndex + 1);
            
            updateUrl({
                sort_by: sortBy,
                sort_order: sortOrder,
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

        // AJAX Pagination
        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('.pagination a, .pagination-wrapper a');
            
            if (paginationLink && paginationLink.href) {
                e.preventDefault();
                e.stopPropagation();
                const url = paginationLink.href;
                
                if (!url || url === '#' || url === 'javascript:void(0)') return;
                
                const tableBody = document.querySelector('table tbody');
                const paginationWrapper = document.querySelector('.pagination-wrapper');
                
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
                        
                        if (data.applicants.length === 0) {
                            html = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">No exam results found. Only applicants who completed the EnrollAssess exam are shown here.</td></tr>';
                        } else {
                            data.applicants.forEach((applicant) => {
                                const statusText = (applicant.status || '').split('-').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(' ');
                                
                                const score = applicant.score ? Number(applicant.score).toFixed(2) : '<span style="color: #9ca3af;">-</span>';
                                const gwa = applicant.card_tor_gwa ? Number(applicant.card_tor_gwa).toFixed(2) : '<span style="color: #9ca3af;">-</span>';
                                const examScore = applicant.enrollassess_score ? Number(applicant.enrollassess_score).toFixed(2) + '%' : '<span style="color: #9ca3af;">-</span>';
                                const interviewScore = applicant.interview_score ? Number(applicant.interview_score).toFixed(2) + '%' : '<span style="color: #9ca3af;">-</span>';
                                
                                // Build overall rating display
                                let overallRatingHtml = '<span style="color: #9ca3af;">-</span>';
                                if (applicant.overall_rating !== null && applicant.overall_rating !== undefined) {
                                    const rating = Number(applicant.overall_rating);
                                    let ratingClass = 'score-needs-improvement';
                                    if (rating >= 95) ratingClass = 'score-excellent';
                                    else if (rating >= 90) ratingClass = 'score-very-good';
                                    else if (rating >= 85) ratingClass = 'score-good';
                                    else if (rating >= 75) ratingClass = 'score-satisfactory';
                                    else if (rating >= 70) ratingClass = 'score-fair';
                                    
                                    overallRatingHtml = `<div class="score-badge ${ratingClass}" style="display: block; margin-bottom: 2px;">${rating.toFixed(2)}</div>`;
                                }
                                
                                // Build floating actions
                                let interviewLink = '';
                                if (applicant.latest_interview && applicant.latest_interview.interview_id) {
                                    interviewLink = `<a href="/admin/interviews/${applicant.latest_interview.interview_id}" class="action-btn" style="color: #800020;" title="View Interview">Interview</a>`;
                                }
                                
                                html += `<tr style="position: relative;" 
                                    onmouseover="showActions(${applicant.applicant_id})" 
                                    onmouseout="hideActions(${applicant.applicant_id})">
                                    <td>
                                        <div style="font-weight: 500;">${applicant.full_name || ''}</div>
                                        <div style="font-size: 12px; color: #6b7280;">${applicant.application_no || applicant.formatted_applicant_no || 'N/A'}</div>
                                    </td>
                                    <td style="text-align: center;">${score}</td>
                                    <td style="text-align: center;">${gwa}</td>
                                    <td style="text-align: center;">${examScore}</td>
                                    <td style="text-align: center;">${interviewScore}</td>
                                    <td>${overallRatingHtml}</td>
                                    <td style="text-align: center;">
                                        <span class="badge bg-secondary">${statusText}</span>
                                        <div id="actions-${applicant.applicant_id}" class="floating-actions" style="display: none;">
                                            <a href="/admin/applicants/${applicant.applicant_id}" class="action-btn" title="View Details">View</a>
                                            ${interviewLink}
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

        async function generateQualifiersFromDrawer(format = 'docx') {
            const slotsInput = document.getElementById('qualifiersSlots');
            const btn = event.target.closest('button');
            const slots = parseInt(slotsInput.value);

            // Validate slots input
            if (!slots || slots < 1 || slots > 500) {
                showNotification('Please enter a valid number of slots (1-500)', 'error');
                slotsInput.focus();
                return;
            }

            const filters = getFiltersObject();
            filters.slots = slots;

            btn.dataset.originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Generating...';

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
                    closeQualifiersDrawer();
                    await loadReportHistory();
                    
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

        async function generateEVSUFromDrawer(format = 'xlsx') {
            const limitInput = document.getElementById('evsu_limit');
            const sortInput = document.getElementById('evsu_sort');
            const btn = event.target.closest('button');
            
            const limit = parseInt(limitInput.value) || 120;
            const sort = sortInput.value || 'overall_desc';

            const filters = getFiltersObject();
            filters.limit = limit;
            filters.sort = sort;
            filters.status = 'interview-completed';

            btn.dataset.originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Generating...';

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
                    closeEVSUDrawer();
                    await loadReportHistory();
                    
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
                btn.textContent = btn.dataset.originalText || 'Generate PDF';
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
                btn.textContent = btn.dataset.originalText || 'Generate PDF';
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
                btn.textContent = btn.dataset.originalText || 'Generate PDF';
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
                                    <td colspan="4" style="text-align: center; padding: 20px; color: #6B7280;">
                                        <p>No reports generated yet.</p>
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

        // Archive Confirmation Modal Functions
        function clearReportHistory() {
            // Fetch stats first
            fetch('/admin/reports/stats', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.total_count === 0) {
                        showNotification('No reports to archive.', 'info');
                        return;
                    }
                    
                    // Update modal with stats
                    document.getElementById('archiveModalCount').textContent = data.total_count + ' report(s)';
                    document.getElementById('archiveModalSize').textContent = data.formatted_file_size;
                    
                    // Reset confirmation checkbox
                    document.getElementById('archiveConfirmCheckbox').checked = false;
                    document.getElementById('archiveConfirmBtn').disabled = true;
                    
                    // Show modal
                    document.getElementById('archiveConfirmModal').classList.add('active');
                } else {
                    showNotification('Failed to load report statistics.', 'error');
                }
            })
            .catch(error => {
                console.error('Error fetching stats:', error);
                showNotification('Error loading report statistics. Please try again.', 'error');
            });
        }

        function closeArchiveConfirmModal() {
            document.getElementById('archiveConfirmModal').classList.remove('active');
            document.getElementById('archiveConfirmCheckbox').checked = false;
            document.getElementById('archiveConfirmBtn').disabled = true;
        }

        function checkArchiveConfirm() {
            const checkbox = document.getElementById('archiveConfirmCheckbox');
            const btn = document.getElementById('archiveConfirmBtn');
            if (checkbox.checked) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }

        async function confirmArchiveAll() {
            const btn = document.getElementById('archiveConfirmBtn');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Archiving...';

            try {
                const response = await fetch('/admin/reports/archive-all', {
                    method: 'POST',
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
                    showNotification(data.message || `Successfully archived ${data.archived_count} report(s).`, 'success');
                    closeArchiveConfirmModal();
                    loadReportHistory(); // Refresh the history table
                } else {
                    showNotification(data.message || 'Failed to archive reports.', 'error');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (error) {
                console.error('Error archiving reports:', error);
                showNotification('Error archiving reports. Please try again.', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
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
                closeArchiveConfirmModal();
            }
        });
    </script>

    <style>
        /* Additional styles for reports page */

        /* Section Title */
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin: 0;
        }

        /* Section Subtitle */
        .section-subtitle {
            font-size: 12px;
            color: var(--text-gray);
            margin: 4px 0 0 0;
            font-weight: 400;
        }

        /* Primary Reports Grid */
        .primary-reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 16px;
        }

        .primary-report-card {
            background: linear-gradient(135deg, #FFF9E6 0%, var(--white) 100%);
            border: 2px solid var(--yellow-primary);
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: var(--transition);
        }

        .primary-report-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .report-card-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .report-card-header .report-card-icon {
            font-size: 24px;
        }

        .report-card-header .report-card-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin: 0;
        }

        .report-card-description {
            font-size: 12px;
            color: var(--text-gray);
            line-height: 1.5;
            margin: 0 0 16px 0;
        }

        .export-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .help-text {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }

        .btn-primary-export {
            padding: 8px 16px;
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            color: var(--white);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: var(--transition);
            box-shadow: 0 2px 6px rgba(128, 0, 32, 0.2);
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
            gap: 16px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-label {
            font-weight: 500;
            color: var(--maroon-primary);
            font-size: 12px;
        }

        .filter-select, .filter-input {
            padding: 8px 12px;
            border: 1px solid var(--border-gray);
            border-radius: 6px;
            font-size: 13px;
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
            gap: 12px;
            justify-content: center;
            padding-top: 16px;
            border-top: 1px solid var(--border-gray);
        }

        .btn-apply-filters, .btn-preview {
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
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
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        .report-card {
            background: var(--white);
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            padding: 16px;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .report-card:hover {
            border-color: var(--yellow-primary);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .report-card-icon {
            font-size: 24px;
            text-align: center;
        }

        .report-card-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin: 0 0 8px 0;
            text-align: center;
        }

        .report-card-description {
            font-size: 12px;
            color: var(--text-gray);
            line-height: 1.4;
            margin: 0;
            text-align: center;
            flex: 1;
        }

        .report-card-actions {
            text-align: center;
        }

        .btn-report-action {
            padding: 8px 16px;
            background: var(--yellow-light);
            color: var(--maroon-primary);
            border: 1px solid var(--yellow-primary);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            font-size: 12px;
        }

        .btn-report-action:hover {
            background: var(--yellow-primary);
            transform: translateY(-1px);
        }

        /* Report History Table */
        .reports-history-table {
            font-size: 13px;
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
            margin: 12px 0;
            padding: 12px;
            background: var(--light-gray);
            border-radius: 6px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-group-inline {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            min-width: 150px;
        }

        .filter-group-inline .filter-label {
            font-size: 11px;
            font-weight: 500;
            color: var(--maroon-primary);
        }

        .filter-select-sm {
            padding: 6px 10px;
            border: 1px solid var(--border-gray);
            border-radius: 4px;
            font-size: 12px;
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