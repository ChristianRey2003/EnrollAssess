@extends('layouts.admin')

@section('title', 'Applicants Management')

@php
    $pageTitle = 'Applicants Management';
    $pageSubtitle = 'Track and manage all BSIT entrance examination applicants';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/applicants.css') }}" rel="stylesheet">
    <style>
        /* Override main-content padding for this page */
        .main-content {
            padding: 20px !important;
        }

        .applicants-container {
            width: 100%;
            max-width: 100%;
        }

        .btn {
            font-size: 12px;
            padding: 8px 14px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
        }

        .btn-primary {
            background: #800020;
            color: white;
        }

        .btn-primary:hover {
            background: #5C0016;
        }

        .btn-secondary {
            background: #F8F9FA;
            color: #1F2937;
            border: 1px solid #E9ECEF;
        }

        .btn-secondary:hover {
            background: #E9ECEF;
        }

        .btn-success {
            background: #059669;
            color: white;
        }

        .btn-success:hover {
            background: #047857;
        }

        /* Dropdown Menu Styles */
        .actions-dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-toggle {
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid #E9ECEF;
            background: #F8F9FA;
            color: #1F2937;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .dropdown-toggle:hover {
            background: #E9ECEF;
            border-color: #800020;
            color: #800020;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 4px;
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            min-width: 180px;
            z-index: 1000;
            overflow: hidden;
        }

        .actions-dropdown.active .dropdown-menu {
            display: block;
        }

        .dropdown-item {
            display: block;
            padding: 10px 16px;
            color: #374151;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s;
            border: none;
            width: 100%;
            text-align: left;
            background: none;
        }

        .dropdown-item:hover {
            background: #F9FAFB;
            color: #800020;
        }

        .dropdown-divider {
            height: 1px;
            background: #E5E7EB;
            margin: 4px 0;
        }

        .dropdown-item-icon {
            display: inline-block;
            margin-right: 8px;
            vertical-align: middle;
            width: 16px;
            height: 16px;
        }

        /* Pagination spacing */
        .pagination-wrapper {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .pagination-wrapper .relative.z-0.inline-flex {
            margin-left: 20px;
        }

        /* Limit pagination to 5 page numbers - CSS hiding rules removed */

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
        }
        
        .floating-actions .action-btn {
            padding: 6px 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        
        .floating-actions .action-btn:hover {
            background: #f3f4f6;
        }
        
        .floating-actions .action-btn-delete:hover {
            background: #fee2e2;
        }
        
        tr:hover {
            background-color: rgba(255, 215, 0, 0.1) !important;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: #1f2937;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Ensure table stays within container */
        .applicants-table {
            max-width: 100%;
            overflow-x: auto;
        }
        
        .data-table {
            table-layout: fixed;
            width: 100%;
            max-width: 100%;
        }
        
        .status-badge {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-word;
        }
        
        /* Table header styling */
        .table thead {
            background-color: white !important;
            color: #1F2937 !important;
        }
        
        .table thead th {
            background-color: white !important;
            color: #1F2937 !important;
            border-color: #E5E7EB !important;
            font-weight: bold !important;
        }
        
        /* Compact toolbar responsive styles */
        @media (max-width: 1200px) {
            .applicants-toolbar {
                flex-direction: column !important;
                gap: 10px !important;
                align-items: stretch !important;
            }
            
            .toolbar-left, .toolbar-right {
                justify-content: center !important;
                flex-wrap: wrap !important;
            }
            
            .toolbar-right {
                gap: 6px !important;
            }
        }
        
        @media (max-width: 768px) {
            .applicants-toolbar input[type="text"] {
                width: 150px !important;
            }
            
            .applicants-toolbar select {
                width: 100px !important;
            }
            
            .toolbar-right a {
                padding: 4px 8px !important;
                font-size: 12px !important;
            }
        }

        /* Drawer Header Override */
        .drawer-header {
            position: sticky;
            top: 0;
            background: white;
            padding: 10px 10px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        /* Export Access Codes Drawer - Narrower Width */
        #exportAccessCodesDrawer {
            width: 450px;
            max-width: 90vw;
        }
    </style>
@endpush

@section('content')

    @php
        $delegation = null;
        $isDelegated = false;
        if (auth()->check() && auth()->user()->role === 'instructor') {
            $delegation = auth()->user()->delegatedPermissions()
                ->whereIn('permission', [
                    'applicants.view', 
                    'applicants.create', 
                    'applicants.edit', 
                    'applicants.delete', 
                    'applicants.assign', 
                    'applicants.schedule_exam',
                    'applicants.bulk_operations',
                    'applicants.import'
                ])
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', now());
                })
                ->first();
            
            if ($delegation && !$delegation->isExpired()) {
                $isDelegated = true;
            }
        }
    @endphp

    <!-- Delegation Expiration Indicator -->
    @if($isDelegated && $delegation)
        @php
            $effectiveExpiresAt = $delegation->getEffectiveExpiresAt();
            $isExpiringSoon = false;
            $timeRemainingText = '';
            
            if ($effectiveExpiresAt) {
                $isExpiringSoon = $effectiveExpiresAt->diffInHours(now()) < 2 && $effectiveExpiresAt->isFuture();
                $timeRemaining = now()->diff($effectiveExpiresAt);
                
                if ($timeRemaining->invert === 0) {
                    // Future expiration
                    if ($timeRemaining->days > 0) {
                        $timeRemainingText = $timeRemaining->days . 'd ' . $timeRemaining->h . 'h';
                    } elseif ($timeRemaining->h > 0) {
                        $timeRemainingText = $timeRemaining->h . 'h ' . $timeRemaining->i . 'm';
                    } elseif ($timeRemaining->i > 0) {
                        $timeRemainingText = $timeRemaining->i . 'm';
                    } else {
                        $timeRemainingText = 'Less than a minute';
                    }
                } else {
                    // Past expiration
                    $timeRemainingText = 'Expired';
                }
            }
        @endphp
        <div class="delegation-indicator {{ $isExpiringSoon ? 'expiring-soon' : '' }}" id="delegationIndicator" style="margin-bottom: 20px; padding: 12px 16px; background: {{ $isExpiringSoon ? '#FEF3C7' : '#EFF6FF' }}; border: 2px solid {{ $isExpiringSoon ? '#FDE68A' : '#BFDBFE' }}; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg style="width: 20px; height: 20px; color: {{ $isExpiringSoon ? '#F59E0B' : '#3B82F6' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span style="font-weight: 600; color: {{ $isExpiringSoon ? '#92400E' : '#1E40AF' }}; font-size: 14px;">
                    Delegation Access
                </span>
            </div>
            @if($effectiveExpiresAt)
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: {{ $isExpiringSoon ? '#92400E' : '#1E40AF' }}; font-size: 13px;">
                        @if($timeRemainingText === 'Expired')
                            <strong>Expired</strong>
                        @else
                            Expires in: <strong id="delegationTimer">{{ $timeRemainingText }}</strong>
                        @endif
                    </span>
                    <span style="color: {{ $isExpiringSoon ? '#92400E' : '#60A5FA' }}; font-size: 12px;">
                        ({{ $effectiveExpiresAt->format('M d, Y g:i A') }})
                    </span>
                </div>
            @endif
        </div>
        @if($effectiveExpiresAt)
            <script>
                // Live countdown timer for delegation
                (function() {
                    const expiresAt = new Date('{{ $effectiveExpiresAt->toIso8601String() }}');
                    const timerEl = document.getElementById('delegationTimer');
                    const indicatorEl = document.getElementById('delegationIndicator');
                    
                    if (!timerEl || !indicatorEl) return;
                    
                    function updateTimer() {
                        const now = new Date();
                        const timeRemaining = expiresAt - now;
                        
                        if (timeRemaining <= 0) {
                            timerEl.textContent = 'Expired';
                            indicatorEl.style.background = '#FEE2E2';
                            indicatorEl.style.borderColor = '#FECACA';
                            const svg = indicatorEl.querySelector('svg');
                            const spans = indicatorEl.querySelectorAll('span');
                            if (svg) svg.style.color = '#DC2626';
                            spans.forEach(span => span.style.color = '#DC2626');
                            return;
                        }
                        
                        const hours = Math.floor(timeRemaining / (1000 * 60 * 60));
                        const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);
                        const days = Math.floor(hours / 24);
                        const remainingHours = hours % 24;
                        
                        let timeText = '';
                        if (days > 0) {
                            timeText = days + 'd ' + remainingHours + 'h';
                        } else if (hours > 0) {
                            timeText = hours + 'h ' + minutes + 'm';
                        } else if (minutes > 0) {
                            timeText = minutes + 'm ' + seconds + 's';
                        } else {
                            timeText = seconds + 's';
                        }
                        
                        timerEl.textContent = timeText;
                        
                        // Update styling if expiring soon (< 2 hours)
                        const isExpiringSoon = hours < 2;
                        if (isExpiringSoon) {
                            indicatorEl.style.background = '#FEF3C7';
                            indicatorEl.style.borderColor = '#FDE68A';
                            const svg = indicatorEl.querySelector('svg');
                            const spans = indicatorEl.querySelectorAll('span');
                            if (svg) svg.style.color = '#F59E0B';
                            spans.forEach(span => {
                                if (span.textContent.includes('Expires')) {
                                    span.style.color = '#92400E';
                                }
                            });
                        }
                    }
                    
                    // Update every second
                    setInterval(updateTimer, 1000);
                    updateTimer(); // Initial update
                })();
            </script>
        @endif
    @endif

                <!-- Statistics Section -->
                <section class="stats-section">
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['total_applicants'] ?? 0 }}</div>
                        <div class="stat-label">Total Applicants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['exam_completed'] ?? 0 }}</div>
                        <div class="stat-label">Exam Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['interview_completed'] ?? 0 }}</div>
                        <div class="stat-label">Interview Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" aria-hidden="true"></div>
                        <div class="stat-value">{{ $stats['qualified'] ?? 0 }}</div>
                        <div class="stat-label">Qualified (Overall ≥ 75)</div>
                    </div>
                </section>

                <!-- Applicants Container -->
                <div class="applicants-container" style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); border: 1px solid #E5E7EB; overflow: hidden;">
                    <!-- Compact Toolbar -->
                    <div class="applicants-toolbar" style="display: flex; justify-content: space-between; align-items: center; gap: 15px; padding: 20px; border-bottom: 1px solid #E5E7EB;">
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
                            <select id="statusFilter" 
                                    class="form-select form-select-sm" 
                                    onchange="applyFilter()" 
                                    style="width: 180px; min-width: 180px; height: 40px; padding: 4px 28px 4px 8px;"
                                    aria-label="Filter by status">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="exam-scheduled" {{ request('status') == 'exam-scheduled' ? 'selected' : '' }}>Scheduled for Exam</option>
                                <option value="exam-completed" {{ request('status') == 'exam-completed' ? 'selected' : '' }}>Exam Completed</option>
                                <option value="exam-no-show" {{ request('status') == 'exam-no-show' ? 'selected' : '' }}>Exam No-Show</option>
                                <option value="interview-scheduled" {{ request('status') == 'interview-scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                                <option value="interview-completed" {{ request('status') == 'interview-completed' ? 'selected' : '' }}>Interview Completed</option>
                            </select>
                        </div>
                        <div class="toolbar-right" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <!-- Most Common Actions - Keep Visible for Better UX -->
                            @if(auth()->user()->hasPermission('applicants.create'))
                            <a href="{{ route('admin.applicants.create') }}" 
                               class="btn btn-primary" 
                               style="white-space: nowrap;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add
                            </a>
                            @endif
                            
                            @if(auth()->user()->hasPermission('applicants.import'))
                            <a href="{{ route('admin.applicants.import') }}" 
                               class="btn btn-secondary" 
                               style="white-space: nowrap;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 4px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Import
                            </a>
                            @endif
                            
                            @if(auth()->user()->hasPermission('applicants.assign'))
                            <a href="{{ route('admin.applicants.assign') }}" 
                               class="btn btn-success" 
                               style="white-space: nowrap;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 4px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                Assign
                            </a>
                            @endif
                            
                            <!-- Less Frequent Actions - In Dropdown -->
                            <div class="actions-dropdown" id="moreActionsDropdown">
                                <button type="button" class="dropdown-toggle" onclick="toggleDropdown('moreActionsDropdown')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                    More
                                </button>
                                <div class="dropdown-menu">
                                    <button type="button" class="dropdown-item" onclick="showGenerateAccessCodesModal(); toggleDropdown('moreActionsDropdown');">
                                        <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        </svg>
                                        Generate Codes
                                    </button>
                                    @if(auth()->user()->hasPermission('applicants.schedule_exam'))
                                    <button type="button" class="dropdown-item" onclick="openScheduleExamDrawer(); toggleDropdown('moreActionsDropdown');">
                                        <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Schedule Exam
                                    </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('applicants.schedule_exam'))
                                    <button type="button" class="dropdown-item" onclick="openEmailNotificationDrawer(); toggleDropdown('moreActionsDropdown');">
                                        <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Send Notifications
                                    </button>
                                    @endif
                                    @if(auth()->user()->role === 'department-head')
                                    <button type="button" class="dropdown-item" onclick="openExportAccessCodesDrawer(); toggleDropdown('moreActionsDropdown');">
                                        <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Export Access Codes
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Compact Bulk Actions -->
                    <div id="bulkActions" class="bulk-actions" style="display: none; background: #eff6ff; border-bottom: 1px solid #bfdbfe; padding: 6px 20px; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; width: 100%;">
                            <span id="selectedCount" style="font-size: 12px; font-weight: 500; color: #1e40af;">0 selected</span>
                            <div style="display: flex; gap: 8px;">
                                @if(auth()->user()->hasPermission('applicants.delete'))
                                <button onclick="bulkDeleteApplicants()" class="btn" style="padding: 4px 12px; font-size: 12px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; display: inline-flex; align-items: center;">
                                    <svg width="14" height="14" style="margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete Selected
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Applicants Table -->
                    <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead style="background-color: white !important; color: #1F2937 !important;">
                            <tr>
                                <th style="width: 40px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">
                                    <input type="checkbox" 
                                           id="selectAll" 
                                           onchange="toggleSelectAll()"
                                           class="form-check-input"
                                           style="cursor: pointer;">
                                </th>
                                <th style="width: 40px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">No.</th>
                                <th style="width: 120px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-left">Applicant no.</th>
                                <th style="width: 180px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-left">Full name</th>
                                <th style="width: 200px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-left">Contact information</th>
                                {{-- <th style="width: 120px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Preferred course</th> --}}
                                <th style="width: 100px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Weighted exam % (60%)</th>
                                {{-- <th style="width: 120px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Verbal description</th> --}}
                                <th style="width: 180px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Exam Scheduled</th>
                                <th style="width: 120px; font-size: 0.85rem; font-weight: bold; color: #1F2937 !important; background-color: white !important; padding: 12px 8px;" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applicants ?? [] as $index => $applicant)
                                <tr style="position: relative;" 
                                    onmouseover="showActions({{ $applicant->applicant_id }})" 
                                    onmouseout="hideActions({{ $applicant->applicant_id }})">
                                    <td class="text-center">
                                        <input type="checkbox" 
                                               class="form-check-input applicant-checkbox" 
                                               value="{{ $applicant->applicant_id }}"
                                               onchange="updateBulkActions()"
                                               style="cursor: pointer;">
                                    </td>
                                    <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                        {{ ($applicants->currentPage() - 1) * $applicants->perPage() + $index + 1 }}
                                    </td>
                                    <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                        <div class="applicant-info">
                                            <div class="applicant-name" style="font-weight: 500; color: #1F2937;">{{ $applicant->application_no ?: $applicant->formatted_applicant_no }}</div>
                                        </div>
                                    </td>
                                    <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                        <div class="applicant-info">
                                            <div class="applicant-name" style="font-weight: 500; color: #1F2937; margin-bottom: 4px;">{{ $applicant->full_name }}</div>
                                            @if($applicant->assignedInstructor)
                                                <div class="applicant-email" style="font-size: 12px; color: #6B7280;">
                                                    Instructor: {{ $applicant->assignedInstructor->full_name }}
                                                </div>
                                            @else
                                                <div class="applicant-email" style="font-size: 12px; color: #9ca3af;">
                                                    No Instructor
                                                </div>
                                            @endif
                                            @if($applicant->accessCode)
                                                <div style="font-size: 12px; color: #6B7280; margin-top: 2px;">
                                                    {{ $applicant->accessCode->code }}
                                                    @if($applicant->accessCode->exam_id)
                                                        <div style="color: #059669; margin-top: 2px;">
                                                            <span style="display: inline-block; width: 4px; height: 4px; border-radius: 50%; background: #059669; margin-right: 4px;"></span>
                                                            {{ $applicant->accessCode->exam->title }} <span style="color: #6b7280; font-size: 11px;">(Legacy)</span>
                                                        </div>
                                                    @else
                                                        <div style="color: #3b82f6; margin-top: 2px;">
                                                            <span style="display: inline-block; width: 4px; height: 4px; border-radius: 50%; background: #3b82f6; margin-right: 4px;"></span>
                                                            Uses active exam
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <!-- Floating Actions -->
                                        <div id="actions-{{ $applicant->applicant_id }}" class="floating-actions" style="display: none;">
                                            <a href="{{ route('admin.applicants.show', $applicant->applicant_id) }}"
                                               class="action-btn action-btn-view"
                                               title="View applicant information">
                                                View
                                            </a>
                                            @if(auth()->user()->hasPermission('applicants.edit'))
                                            <a href="{{ route('admin.applicants.edit', $applicant->applicant_id) }}"
                                               class="action-btn action-btn-edit"
                                               title="Edit applicant">
                                                Edit
                                            </a>
                                            @endif
                                            <!-- Hidden: Assign Exam (Legacy - kept for potential future use) -->
                                            @if($applicant->accessCode)
                                                <button onclick="showSingleAssignExamModal({{ $applicant->applicant_id }})"
                                                        class="action-btn action-btn-assign"
                                                        title="Assign exam{{ $applicant->accessCode && $applicant->accessCode->exam_id ? ' (' . $applicant->accessCode->exam->title . ')' : '' }}"
                                                        style="display: none; background: #8b5cf6;">
                                                    Assign Exam
                                                </button>
                                            @endif
                                            @if(auth()->user()->hasPermission('applicants.schedule_exam'))
                                            <button onclick="sendIndividualNotification({{ $applicant->applicant_id }})"
                                                    class="action-btn action-btn-notify"
                                                    title="Send exam notification">
                                                Email
                                            </button>
                                            @endif
                                            @if(auth()->user()->hasPermission('applicants.delete'))
                                            <button onclick="deleteApplicant({{ $applicant->applicant_id }})"
                                                    class="action-btn action-btn-delete"
                                                    title="Delete applicant">
                                                Delete
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                        <div class="contact-info">
                                            <div class="applicant-name" style="font-weight: 500; color: #1F2937; margin-bottom: 4px;">{{ $applicant->email_address }}</div>
                                            @if($applicant->phone_number)
                                                <div class="applicant-email" style="font-size: 12px; color: #6B7280;">{{ $applicant->phone_number }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    {{-- Preferred course column - hidden but data still in system --}}
                                    {{-- <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                        <div class="applicant-name" style="font-weight: 500; color: #1F2937;">{{ $applicant->preferred_course ?: '-' }}</div>
                                    </td> --}}
                                    <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                        @if($applicant->score !== null)
                                            <div class="applicant-name" style="font-weight: 500; color: #1F2937;">{{ number_format((float) $applicant->score, 2) }}%</div>
                                        @else
                                            <span class="applicant-email" style="font-size: 12px; color: #6B7280;">-</span>
                                        @endif
                                    </td>
                                    {{-- Verbal description column - hidden but data still in system --}}
                                    {{-- <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                        <div class="applicant-name" style="font-weight: 500; color: #1F2937;">{{ $applicant->computed_verbal_description ?: '-' }}</div>
                                    </td> --}}
                                    <td class="text-center" style="font-size: 12px; font-weight: normal;">
                                        @php
                                            $schedule = $applicant->latestExamSchedule;
                                        @endphp
                                        @if($schedule)
                                            <div style="font-weight: 500; color: #1F2937;">
                                                {{ $schedule->scheduled_date->format('M d, Y') }}
                                            </div>
                                            <div style="font-size: 11px; color: #6B7280;">
                                                {{ \Carbon\Carbon::parse($schedule->scheduled_time)->format('g:i A') }}
                                            </div>
                                            @if($schedule->venue)
                                                <div style="font-size: 11px; color: #6B7280; margin-top: 2px;">
                                                    {{ Str::limit($schedule->venue, 20) }}
                                                </div>
                                            @endif
                                        @else
                                            <span style="color: #9ca3af;">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="padding: 6px 4px;">
                                        <span class="status-badge status-pending" style="font-size: 9px; padding: 2px 4px; border-radius: 3px; background: #fef3c7; color: #92400e; font-weight: 500; white-space: nowrap; display: inline-block;">
                                            @php
                                                $status = $applicant->status;
                                                $statusMap = [
                                                    'exam-scheduled' => 'EXAM SCHEDULED',
                                                    'exam-completed' => 'EXAM DONE',
                                                    'exam-no-show' => 'EXAM NO-SHOW',
                                                    'interview-available' => 'INTERVIEW READY',
                                                    'interview-scheduled' => 'INTERVIEW SET',
                                                    'interview-completed' => 'INTERVIEW DONE',
                                                    'admitted' => 'ADMITTED',
                                                    'rejected' => 'REJECTED',
                                                    'pending' => 'PENDING'
                                                ];
                                                echo $statusMap[$status] ?? strtoupper(str_replace('-', ' ', $status));
                                            @endphp
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <h5>No applicants found</h5>
                                            <p class="mb-0">
                                                @if(request()->hasAny(['search', 'status']))
                                                    Try adjusting your search criteria or filters.
                                                @else
                                                    Start by importing applicants or adding them manually.
                                                @endif
                                            </p>
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
                            {{ $applicants->onEachSide(2)->links() }}
                        </div>
                    @endif
                </div>
    </div>

    <!-- Generate Access Codes Drawer -->
    <div id="generateCodesDrawerOverlay" class="drawer-overlay" onclick="closeGenerateCodesDrawer()"></div>
    <div id="generateCodesDrawer" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">Generate Access Codes</h3>
            <button type="button" class="drawer-close" onclick="closeGenerateCodesDrawer()">×</button>
        </div>
        
        <div class="drawer-body">
            <!-- Selected Applicants Info -->
            <div style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <div style="font-weight: 600; margin-bottom: 4px;">Selected Applicants</div>
                <div style="font-size: 14px; color: #6b7280;">
                    <span id="codesSelectedCount">0</span> applicant(s) will receive access codes
                </div>
            </div>

            <!-- Important Notice -->
            <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; margin-bottom: 20px;">
                <div style="font-weight: 600; color: #1e40af; margin-bottom: 4px;">What This Does:</div>
                <ul style="margin: 8px 0; padding-left: 20px; font-size: 14px; color: #1e40af;">
                    <li>Generates unique access codes for selected applicants</li>
                    <li>Codes can be used to access the examination portal</li>
                    <li>Codes will expire after the specified time period</li>
                    <li>Optionally sends email notifications with the codes</li>
                </ul>
            </div>

            <!-- Expiry Hours -->
            <div style="margin-bottom: 20px;">
                <label for="expiry_hours" style="font-weight: 600; margin-bottom: 8px; display: block;">
                    Expiry Hours <span style="color: #ef4444;">*</span>
                </label>
                <input type="number" 
                       id="expiry_hours" 
                       name="expiry_hours" 
                       class="form-control" 
                       value="72" 
                       min="1" 
                       max="168"
                       required
                       style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                <small style="color: #6b7280; font-size: 12px;">How many hours should the access code be valid?</small>
            </div>

            <!-- Email Notification Option -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px;">
                    <input type="checkbox" 
                           id="send_email" 
                           name="send_email" 
                           class="checkbox-input" 
                           checked
                           style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="send_email" style="font-size: 14px; font-weight: 500; color: #374151; cursor: pointer; flex: 1;">
                        Send email notifications to applicants
                    </label>
                </div>
                <small style="color: #6b7280; font-size: 12px; display: block; margin-top: 6px;">
                    An email with the access code will be sent to each applicant's registered email address
                </small>
            </div>

            <!-- Security Notice -->
            <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px;">
                <div style="font-weight: 600; color: #92400e; margin-bottom: 4px;">Security Notice</div>
                <div style="font-size: 13px; color: #78350f;">
                    Access codes are unique and cannot be regenerated for the same applicant. Keep codes secure and only share them with authorized applicants.
                </div>
            </div>
        </div>
        
        <div class="drawer-footer">
            <button type="button" class="btn btn-secondary" onclick="closeGenerateCodesDrawer()">Cancel</button>
            <button type="button" class="btn btn-primary" id="generateCodesButton" onclick="confirmGenerateAccessCodes()">
                Generate Codes
            </button>
        </div>
    </div>

    <!-- Export Access Codes Drawer -->
    <div id="exportAccessCodesDrawerOverlay" class="drawer-overlay" onclick="closeExportAccessCodesDrawer()"></div>
    <div id="exportAccessCodesDrawer" class="drawer">
        <div class="drawer-header">
            <h3 class="drawer-title">Export Access Codes</h3>
            <button type="button" class="drawer-close" onclick="closeExportAccessCodesDrawer()">×</button>
        </div>
        
        <div class="drawer-body">
            <div style="margin-bottom: 20px;">
                <p style="font-size: 14px; color: #374151; margin: 0 0 12px 0;">
                    Export a PDF containing all scheduled applicants with their access codes. This PDF can be shared with school officers.
                </p>
            </div>

            <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; margin-bottom: 20px;">
                <div style="font-weight: 600; color: #1e40af; margin-bottom: 4px;">Export Information</div>
                <div style="font-size: 13px; color: #1e3a8a;">
                    The PDF will include:
                    <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                        <li>Applicant Name</li>
                        <li>Access Code</li>
                    </ul>
                </div>
            </div>

            <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px;">
                <div style="font-weight: 600; color: #92400e; margin-bottom: 4px;">Note</div>
                <div style="font-size: 13px; color: #78350f;">
                    Only applicants with access codes will be included in the export.
                </div>
            </div>
        </div>
        
        <div class="drawer-footer">
            <button type="button" class="btn btn-secondary" onclick="closeExportAccessCodesDrawer()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="exportAccessCodesPDF()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 6px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export PDF
            </button>
        </div>
    </div>


@endsection

<!-- Include Exam Notification Modal -->
@include('components.exam-notification-modal')
@include('components.schedule-exam-modal')

<!-- Include Assign Exam Modal -->
@include('admin.applicants.partials.assign-exam-modal')

@push('scripts')
    <script src="{{ asset('js/modules/applicant-manager.js') }}" defer></script>
    <script>
        function applyFilter() {
            const status = document.getElementById('statusFilter').value;
            const url = new URL(window.location);
            
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            
            // Reset to first page when filtering
            url.searchParams.delete('page');
            
            window.location.href = url.toString();
        }

        // Toggle dropdown menu
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('active');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('.actions-dropdown');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(event.target)) {
                    dropdown.classList.remove('active');
                }
            });
        });

        function showActions(applicantId) {
            document.getElementById('actions-' + applicantId).style.display = 'flex';
        }
        
        function hideActions(applicantId) {
            document.getElementById('actions-' + applicantId).style.display = 'none';
        }

        // Bulk delete function with fallback
        function bulkDeleteApplicants() {
            // Try to use the applicant manager if available
            if (window.applicantManager && typeof window.applicantManager.bulkDelete === 'function') {
                window.applicantManager.bulkDelete();
                return;
            }
            
            // Fallback implementation
            const checkboxes = document.querySelectorAll('.applicant-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Please select applicants first.');
                return;
            }

            const count = checkboxes.length;
            const confirmed = confirm(`Are you sure you want to delete ${count} selected applicant(s)? This action cannot be undone.`);
            
            if (!confirmed) return;

            // Convert to integers and filter out invalid values
            const applicantIds = Array.from(checkboxes)
                .map(cb => parseInt(cb.value))
                .filter(id => !isNaN(id) && id > 0);

            if (applicantIds.length === 0) {
                alert('No valid applicant IDs found.');
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                alert('CSRF token not found. Please refresh the page and try again.');
                return;
            }

            // Show loading
            const loadingMsg = document.createElement('div');
            loadingMsg.textContent = 'Deleting applicants...';
            loadingMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #3b82f6; color: white; padding: 12px 20px; border-radius: 6px; z-index: 10000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
            document.body.appendChild(loadingMsg);

            fetch('/admin/applicants/bulk/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ applicant_ids: applicantIds })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || `HTTP ${response.status}: ${response.statusText}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (loadingMsg.parentNode) {
                    document.body.removeChild(loadingMsg);
                }
                if (data.success) {
                    alert(data.message || 'Applicants deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete applicants'));
                }
            })
            .catch(error => {
                if (loadingMsg.parentNode) {
                    document.body.removeChild(loadingMsg);
                }
                console.error('Bulk delete error:', error);
                alert('An error occurred while deleting applicants: ' + (error.message || 'Please try again.'));
            });
        }

        // Send individual exam notification
        function sendIndividualNotification(applicantId) {
            console.log('Sending individual notification to applicant:', applicantId);
            
            // Set the global selectedApplicants to just this applicant
            window.selectedApplicants = [applicantId];
            
            // Also update the applicant manager if available
            if (window.applicantManager) {
                window.applicantManager.selectedApplicants.clear();
                window.applicantManager.selectedApplicants.add(applicantId);
            }
            
            // Open the email notification drawer
            openEmailNotificationDrawer();
        }

        // Show bulk assign exam drawer
        function showAssignExamModal() {
            const selected = document.querySelectorAll('.applicant-checkbox:checked');
            if (selected.length === 0) {
                alert('Please select at least one applicant');
                return;
            }

            const overlay = document.getElementById('assignExamDrawerOverlay');
            const drawer = document.getElementById('assignExamDrawer');
            
            if (!overlay || !drawer) {
                console.error('Bulk assign exam drawer elements not found');
                return;
            }
            
            overlay.classList.add('active');
            drawer.classList.add('active');
            
            document.getElementById('bulk_selected_count').textContent = selected.length;
            
            // Show exam details when exam is selected
            const examSelect = document.getElementById('bulk_exam_id');
            if (examSelect) {
                // Remove any existing event listeners by cloning
                const newExamSelect = examSelect.cloneNode(true);
                examSelect.parentNode.replaceChild(newExamSelect, examSelect);
                
                newExamSelect.addEventListener('change', function() {
                    const option = this.options[this.selectedIndex];
                    if (this.value) {
                        document.getElementById('examDuration').textContent = option.dataset.duration || 'N/A';
                        document.getElementById('examQuestions').textContent = option.dataset.total || 'N/A';
                        document.getElementById('examDetails').style.display = 'block';
                    } else {
                        document.getElementById('examDetails').style.display = 'none';
                    }
                });
            }
        }

        function closeAssignExamDrawer() {
            const overlay = document.getElementById('assignExamDrawerOverlay');
            const drawer = document.getElementById('assignExamDrawer');
            
            if (overlay && drawer) {
                overlay.classList.remove('active');
                drawer.classList.remove('active');
                
                // Reset form
                document.getElementById('bulk_exam_id').value = '';
                document.getElementById('examDetails').style.display = 'none';
            }
        }

        async function submitBulkExamAssignment() {
            const examId = document.getElementById('bulk_exam_id').value;
            if (!examId) {
                alert('Please select an exam');
                return;
            }

            const selectedCheckboxes = document.querySelectorAll('.applicant-checkbox:checked');
            const applicantIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));

            if (applicantIds.length === 0) {
                alert('No applicants selected');
                return;
            }

            const btn = document.getElementById('bulkAssignBtn');
            btn.disabled = true;
            btn.textContent = 'Assigning...';

            try {
                const response = await fetch('/admin/applicants/assign-exam', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        applicant_ids: applicantIds,
                        exam_id: examId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    closeAssignExamDrawer();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.textContent = 'Assign Exam';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while assigning exam');
                btn.disabled = false;
                btn.textContent = 'Assign Exam';
            }
        }

        // Show single assign exam drawer
        function showSingleAssignExamModal(applicantId) {
            const overlay = document.getElementById('singleAssignExamDrawerOverlay');
            const drawer = document.getElementById('singleAssignExamDrawer');
            
            if (!overlay || !drawer) {
                console.error('Single assign exam drawer elements not found');
                return;
            }
            
            overlay.classList.add('active');
            drawer.classList.add('active');
            
            document.getElementById('single_applicant_id').value = applicantId;
            
            // Find applicant name from the table
            try {
                const row = document.querySelector(`input.applicant-checkbox[value="${applicantId}"]`).closest('tr');
                const nameElement = row.querySelector('.applicant-name .font-medium');
                const name = nameElement ? nameElement.textContent.trim() : 'Applicant #' + applicantId;
                document.getElementById('single_applicant_name').textContent = name;
            } catch (error) {
                console.error('Error finding applicant name:', error);
                document.getElementById('single_applicant_name').textContent = 'Applicant #' + applicantId;
            }
            
            // Show exam details when exam is selected
            const examSelect = document.getElementById('single_exam_id');
            if (examSelect) {
                // Remove any existing event listeners by cloning
                const newExamSelect = examSelect.cloneNode(true);
                examSelect.parentNode.replaceChild(newExamSelect, examSelect);
                
                newExamSelect.addEventListener('change', function() {
                    const option = this.options[this.selectedIndex];
                    if (this.value) {
                        document.getElementById('singleExamDuration').textContent = option.dataset.duration || 'N/A';
                        document.getElementById('singleExamQuestions').textContent = option.dataset.total || 'N/A';
                        document.getElementById('singleExamDetails').style.display = 'block';
                    } else {
                        document.getElementById('singleExamDetails').style.display = 'none';
                    }
                });
            }
        }

        function closeSingleAssignExamDrawer() {
            const overlay = document.getElementById('singleAssignExamDrawerOverlay');
            const drawer = document.getElementById('singleAssignExamDrawer');
            
            if (overlay && drawer) {
                overlay.classList.remove('active');
                drawer.classList.remove('active');
                
                // Reset form
                document.getElementById('single_exam_id').value = '';
                document.getElementById('singleExamDetails').style.display = 'none';
            }
        }

        async function submitSingleExamAssignment() {
            const applicantId = document.getElementById('single_applicant_id').value;
            const examId = document.getElementById('single_exam_id').value;
            
            if (!examId) {
                alert('Please select an exam');
                return;
            }

            const btn = document.getElementById('singleAssignBtn');
            btn.disabled = true;
            btn.textContent = 'Assigning...';

            try {
                const response = await fetch(`/admin/applicants/${applicantId}/assign-exam`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        exam_id: examId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    closeSingleAssignExamDrawer();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.textContent = 'Assign Exam';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while assigning exam');
                btn.disabled = false;
                btn.textContent = 'Assign Exam';
            }
        }

        // Generate Codes Drawer Functions
        function openGenerateCodesDrawer() {
            // Get selected applicants
            const selectedApplicants = window.selectedApplicants || 
                                       (window.applicantManager ? Array.from(window.applicantManager.selectedApplicants) : []);
            
            if (!selectedApplicants || selectedApplicants.length === 0) {
                alert('Please select at least one applicant first.');
                return;
            }
            
            const overlay = document.getElementById('generateCodesDrawerOverlay');
            const drawer = document.getElementById('generateCodesDrawer');
            
            if (overlay && drawer) {
                overlay.classList.add('active');
                drawer.classList.add('active');
                
                // Update selected count
                const countSpan = document.getElementById('codesSelectedCount');
                if (countSpan) {
                    countSpan.textContent = selectedApplicants.length;
                }
            } else {
                console.error('Generate codes drawer elements not found');
            }
        }

        function closeGenerateCodesDrawer() {
            const overlay = document.getElementById('generateCodesDrawerOverlay');
            const drawer = document.getElementById('generateCodesDrawer');
            
            if (overlay && drawer) {
                overlay.classList.remove('active');
                drawer.classList.remove('active');
            }
        }

        // Export Access Codes Drawer Functions
        function openExportAccessCodesDrawer() {
            const overlay = document.getElementById('exportAccessCodesDrawerOverlay');
            const drawer = document.getElementById('exportAccessCodesDrawer');
            
            if (overlay && drawer) {
                overlay.classList.add('active');
                drawer.classList.add('active');
            }
        }

        function closeExportAccessCodesDrawer() {
            const overlay = document.getElementById('exportAccessCodesDrawerOverlay');
            const drawer = document.getElementById('exportAccessCodesDrawer');
            
            if (overlay && drawer) {
                overlay.classList.remove('active');
                drawer.classList.remove('active');
            }
        }

        function exportAccessCodesPDF() {
            // Show loading state
            const exportBtn = event.target.closest('button');
            const originalText = exportBtn.innerHTML;
            exportBtn.disabled = true;
            exportBtn.innerHTML = '<span>Exporting...</span>';

            // Redirect to export route
            window.location.href = '{{ route("admin.applicants.export.access-codes-pdf") }}';

            // Re-enable button after a delay (in case of error)
            setTimeout(() => {
                exportBtn.disabled = false;
                exportBtn.innerHTML = originalText;
            }, 3000);
        }

        // Archive All Functions
        // Make functions global
        window.openGenerateCodesDrawer = openGenerateCodesDrawer;
        window.closeGenerateCodesDrawer = closeGenerateCodesDrawer;
        window.openExportAccessCodesDrawer = openExportAccessCodesDrawer;
        window.closeExportAccessCodesDrawer = closeExportAccessCodesDrawer;
        window.exportAccessCodesPDF = exportAccessCodesPDF;

        // Auto-search functionality
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const searchValue = e.target.value.trim();
                
                searchTimeout = setTimeout(function() {
                    const url = new URL(window.location);
                    if (searchValue) {
                        url.searchParams.set('search', searchValue);
                    } else {
                        url.searchParams.delete('search');
                    }
                    url.searchParams.delete('page'); // Reset to first page
                    window.location.href = url.toString();
                }, 500); // 500ms debounce
            });
        }

        // Pagination pages 6 and 7 hiding removed

        // Helper function to format time
        function formatTime(timeString) {
            if (!timeString) return '';
            const [hours, minutes] = timeString.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${ampm}`;
        }

        // AJAX Pagination - Use event delegation to catch all pagination links
        document.addEventListener('click', function(e) {
            // Check if click is on a pagination link (could be direct <a> or nested in <span>)
            let paginationLink = e.target.closest('.pagination a, .pagination-wrapper a');
            
            // Also check if the clicked element itself is a pagination link
            if (!paginationLink && (e.target.classList.contains('pagination') || e.target.closest('.pagination'))) {
                paginationLink = e.target.tagName === 'A' ? e.target : e.target.closest('a');
            }
            
            if (paginationLink && paginationLink.href) {
                e.preventDefault();
                e.stopPropagation();
                const url = paginationLink.href;
                
                if (!url || url === '#' || url === 'javascript:void(0)') return;
                
                // Show loading state
                const tableBody = document.querySelector('.table-responsive tbody, .table tbody');
                const paginationWrapper = document.querySelector('.pagination-wrapper');
                
                if (tableBody) {
                    tableBody.style.opacity = '0.5';
                    tableBody.style.pointerEvents = 'none';
                }
                if (paginationWrapper) {
                    paginationWrapper.style.opacity = '0.5';
                    paginationWrapper.style.pointerEvents = 'none';
                }
                
                // Fetch new page
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.applicants && tableBody) {
                        // Build new table rows
                        let html = '';
                        const currentPage = data.pagination.current_page;
                        const perPage = data.pagination.per_page;
                        const from = data.pagination.from || 0;
                        
                        if (data.applicants.length === 0) {
                            html = '<tr><td colspan="8" class="text-center py-8"><div class="empty-state"><div class="empty-title">No applicants found</div></div></td></tr>';
                        } else {
                            data.applicants.forEach((applicant, index) => {
                                const rowNum = (from - 1) + index + 1;
                                
                                // Status badge mapping
                                const statusMap = {
                                    'exam-scheduled': 'EXAM SCHEDULED',
                                    'exam-completed': 'EXAM DONE',
                                    'exam-no-show': 'EXAM NO-SHOW',
                                    'interview-available': 'INTERVIEW READY',
                                    'interview-scheduled': 'INTERVIEW SET',
                                    'interview-completed': 'INTERVIEW DONE',
                                    'admitted': 'ADMITTED',
                                    'rejected': 'REJECTED',
                                    'pending': 'PENDING'
                                };
                                const status = applicant.status || 'pending';
                                const statusText = statusMap[status] || status.replace(/-/g, ' ').toUpperCase();
                                
                                // Build instructor info
                                let instructorName = '';
                                if (applicant.assigned_instructor) {
                                    instructorName = applicant.assigned_instructor.full_name || 
                                        ((applicant.assigned_instructor.first_name || '') + ' ' + 
                                         (applicant.assigned_instructor.middle_name || '') + ' ' + 
                                         (applicant.assigned_instructor.last_name || '')).trim();
                                }
                                const instructorInfo = applicant.assigned_instructor 
                                    ? `<div class="applicant-email" style="font-size: 12px; color: #6B7280;">Instructor: ${instructorName}</div>`
                                    : `<div class="applicant-email" style="font-size: 12px; color: #9ca3af;">No Instructor</div>`;
                                
                                // Build access code info
                                let accessCodeInfo = '';
                                if (applicant.access_code) {
                                    accessCodeInfo = `<div style="font-size: 12px; color: #6B7280; margin-top: 2px;">${applicant.access_code.code || ''}`;
                                    if (applicant.access_code.exam_id && applicant.access_code.exam) {
                                        accessCodeInfo += `<div style="color: #059669; margin-top: 2px;"><span style="display: inline-block; width: 4px; height: 4px; border-radius: 50%; background: #059669; margin-right: 4px;"></span>${applicant.access_code.exam.title} <span style="color: #6b7280; font-size: 11px;">(Legacy)</span></div>`;
                                    } else {
                                        accessCodeInfo += `<div style="color: #3b82f6; margin-top: 2px;"><span style="display: inline-block; width: 4px; height: 4px; border-radius: 50%; background: #3b82f6; margin-right: 4px;"></span>Uses active exam</div>`;
                                    }
                                    accessCodeInfo += '</div>';
                                }
                                
                                // Build phone number info
                                const phoneInfo = applicant.phone_number 
                                    ? `<div class="applicant-email" style="font-size: 12px; color: #6B7280;">${applicant.phone_number}</div>`
                                    : '';
                                
                                // Build score display
                                const scoreDisplay = applicant.score !== null 
                                    ? `<div class="applicant-name" style="font-weight: 500; color: #1F2937;">${Number(applicant.score).toFixed(2)}%</div>`
                                    : `<span class="applicant-email" style="font-size: 12px; color: #6B7280;">-</span>`;
                                
                                // Build exam schedule display
                                let examScheduleDisplay = '<span style="color: #9ca3af;">-</span>';
                                if (applicant.latest_exam_schedule) {
                                    const schedule = applicant.latest_exam_schedule;
                                    const scheduleDate = schedule.scheduled_date ? new Date(schedule.scheduled_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
                                    const scheduleTime = schedule.scheduled_time ? formatTime(schedule.scheduled_time) : '';
                                    const venue = schedule.venue || '';
                                    
                                    examScheduleDisplay = '';
                                    if (scheduleDate) {
                                        examScheduleDisplay += `<div style="font-weight: 500; color: #1F2937;">${scheduleDate}</div>`;
                                    }
                                    if (scheduleTime) {
                                        examScheduleDisplay += `<div style="font-size: 11px; color: #6B7280;">${scheduleTime}</div>`;
                                    }
                                    if (venue) {
                                        examScheduleDisplay += `<div style="font-size: 11px; color: #6B7280; margin-top: 2px;">${venue.length > 20 ? venue.substring(0, 20) + '...' : venue}</div>`;
                                    }
                                }
                                
                                html += `<tr style="position: relative;" 
                                    onmouseover="showActions(${applicant.applicant_id})" 
                                    onmouseout="hideActions(${applicant.applicant_id})">
                                    <td class="text-center">
                                        <input type="checkbox" 
                                               class="form-check-input applicant-checkbox" 
                                               value="${applicant.applicant_id}"
                                               onchange="updateBulkActions()"
                                               style="cursor: pointer;">
                                    </td>
                                    <td class="text-center" style="font-size: 13px; font-weight: normal;">${rowNum}</td>
                                    <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                        <div class="applicant-info">
                                            <div class="applicant-name" style="font-weight: 500; color: #1F2937;">${applicant.application_no || applicant.formatted_applicant_no || 'N/A'}</div>
                                        </div>
                                    </td>
                                    <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                        <div class="applicant-info">
                                            <div class="applicant-name" style="font-weight: 500; color: #1F2937; margin-bottom: 4px;">${applicant.full_name || ((applicant.first_name || '') + ' ' + (applicant.middle_name || '') + ' ' + (applicant.last_name || '')).trim() || 'N/A'}</div>
                                            ${instructorInfo}
                                            ${accessCodeInfo}
                                        </div>
                                        <div id="actions-${applicant.applicant_id}" class="floating-actions" style="display: none;">
                                            <a href="/admin/applicants/${applicant.applicant_id}"
                                               class="action-btn action-btn-view"
                                               title="View applicant information">
                                                View
                                            </a>
                                            ${data.permissions && data.permissions.can_edit ? `<a href="/admin/applicants/${applicant.applicant_id}/edit"
                                               class="action-btn action-btn-edit"
                                               title="Edit applicant">
                                                Edit
                                            </a>` : ''}
                                            ${data.permissions && data.permissions.can_schedule_exam ? `<button onclick="sendIndividualNotification(${applicant.applicant_id})"
                                                    class="action-btn action-btn-notify"
                                                    title="Send exam notification">
                                                Email
                                            </button>` : ''}
                                            ${data.permissions && data.permissions.can_delete ? `<button onclick="deleteApplicant(${applicant.applicant_id})"
                                                    class="action-btn action-btn-delete"
                                                    title="Delete applicant">
                                                Delete
                                            </button>` : ''}
                                        </div>
                                    </td>
                                    <td class="text-left" style="font-size: 13px; font-weight: normal;">
                                        <div class="contact-info">
                                            <div class="applicant-name" style="font-weight: 500; color: #1F2937; margin-bottom: 4px;">${applicant.email_address || ''}</div>
                                            ${phoneInfo}
                                        </div>
                                    </td>
                                    <td class="text-center" style="font-size: 13px; font-weight: normal;">
                                        ${scoreDisplay}
                                    </td>
                                    <td class="text-center" style="font-size: 12px; font-weight: normal;">
                                        ${examScheduleDisplay}
                                    </td>
                                    <td class="text-center" style="padding: 6px 4px;">
                                        <span class="status-badge status-pending" style="font-size: 9px; padding: 2px 4px; border-radius: 3px; background: #fef3c7; color: #92400e; font-weight: 500; white-space: nowrap; display: inline-block;">${statusText}</span>
                                    </td>
                                </tr>`;
                            });
                        }
                        
                        tableBody.innerHTML = html;
                        tableBody.style.opacity = '1';
                        tableBody.style.pointerEvents = '';
                        
                        // Update pagination HTML if provided
                        if (data.pagination_html && paginationWrapper) {
                            paginationWrapper.innerHTML = data.pagination_html;
                            // Pages 6 and 7 now visible
                        }
                        
                        if (paginationWrapper) {
                            paginationWrapper.style.opacity = '1';
                            paginationWrapper.style.pointerEvents = '';
                        }
                        
                        // Update URL without reload
                        window.history.pushState({}, '', url);
                        
                        // Re-initialize any event handlers that might be needed
                        if (typeof updateBulkActions === 'function') {
                            updateBulkActions();
                        }
                    }
                })
                .catch(error => {
                    console.error('Pagination error:', error);
                    // Fallback to full page reload if AJAX fails
                    window.location.href = url;
                });
            }
        });
    </script>
@endpush
