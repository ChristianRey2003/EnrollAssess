@extends('layouts.admin')

@section('title', 'Question Bank Management')

@php
    $pageTitle = 'Question Bank';
    $pageSubtitle = 'Manage your exam question bank';
@endphp

@push('styles')
<style>
    .content-section {
        padding: 20px;
        max-width: 1400px;
    }

    .section-header {
        padding: 10px 10px;
        border-bottom: 1px solid var(--border-gray);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .section-actions {
        display: flex;
        gap: 8px;
        position: relative;
    }

    /* Dropdown Menu Styles */
    .actions-dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-toggle {
        padding: 8px 14px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid #d1d5db;
        background: white;
        color: #6b7280;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .dropdown-toggle:hover {
        background: #f9fafb;
        border-color: #991b1b;
        color: #991b1b;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 4px;
        background: white;
        border: 1px solid #e5e7eb;
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
        background: #f9fafb;
        color: #991b1b;
    }

    .dropdown-divider {
        height: 1px;
        background: #e5e7eb;
        margin: 4px 0;
    }

    /* Icon button for overview */
    .btn-icon-only {
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid #d1d5db;
        background: white;
        color: #6b7280;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
    }

    .btn-icon-only:hover {
        background: #f9fafb;
        border-color: #991b1b;
        color: #991b1b;
    }

    .btn-primary, .btn-outline, .btn-success, .btn-secondary {
        padding: 8px 14px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.15s;
    }

    .btn-primary {
        background: #991b1b;
        color: white;
    }

    .btn-primary:hover {
        background: #7f1d1d;
    }

    .btn-outline {
        background: white;
        color: #6b7280;
        border: 1px solid #d1d5db;
    }

    .btn-outline:hover {
        background: #f9fafb;
        border-color: #991b1b;
        color: #991b1b;
    }

    .btn-success {
        background: #059669;
        color: white;
    }

    .btn-success:hover {
        background: #047857;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    /* Exam Info - Compact */
    .exam-info-card {
        background: #fafafa;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }

    .exam-info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .exam-info-header h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .exam-info-header p {
        color: #6b7280;
        font-size: 14px;
        margin: 4px 0 0 0;
    }

    .exam-meta {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        font-size: 13px;
        color: #6b7280;
        margin-top: 8px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .meta-label {
        color: #9ca3af;
    }

    .status-badge {
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-draft {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Statistics - Inline Compact */
    .stats-grid {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .stat-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 10px 14px;
        display: flex;
        align-items: baseline;
        gap: 6px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
    }

    .stat-label {
        font-size: 13px;
        color: #6b7280;
    }

    /* Toolbar - Compact */
    .toolbar {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 10px 12px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .toolbar-left {
        display: flex;
        gap: 8px;
        flex: 1;
    }

    .toolbar-right {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .search-box {
        position: relative;
        width: 220px;
    }

    .search-box input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 15px;
    }

    .search-box input:focus {
        outline: none;
        border-color: #991b1b;
    }

    .filter-select {
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 15px;
        background: white;
        color: #374151;
    }

    .filter-select:focus {
        outline: none;
        border-color: #991b1b;
    }

    /* Questions List - Compact */
    .questions-container {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }

    .question-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: start;
        transition: background 0.15s;
    }

    .question-item:hover {
        background: #fafafa;
    }

    .question-item:last-child {
        border-bottom: none;
    }

    .question-content {
        flex: 1;
    }

    .question-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .question-number {
        background: #991b1b;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 600;
        min-width: 32px;
        text-align: center;
    }

    .question-type-badge {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .type-multiple_choice {
        background: #eff6ff;
        color: #1e40af;
    }

    .type-true_false {
        background: #f0fdf4;
        color: #166534;
    }

    /* .type-essay removed */

    .question-text {
        color: #1f2937;
        font-size: 15px;
        line-height: 1.4;
        margin-bottom: 6px;
    }

    .question-meta {
        display: flex;
        gap: 12px;
        font-size: 13px;
        color: #9ca3af;
    }

    .question-actions {
        display: flex;
        gap: 4px;
        margin-left: 12px;
    }

    .btn-icon {
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 15px;
        cursor: pointer;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .btn-icon:hover {
        background: #f9fafb;
        color: #1f2937;
        border-color: #d1d5db;
    }

    .btn-icon.danger:hover {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }

    /* Empty State */
    .empty-state {
        padding: 48px 20px;
        text-align: center;
    }

    .empty-state h4 {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 6px 0;
    }

    .empty-state p {
        color: #6b7280;
        font-size: 15px;
        margin: 0 0 20px 0;
    }

    /* Drawer */
    .drawer-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.4);
        z-index: 1000;
    }

    .drawer-overlay.active {
        display: block;
    }

    .drawer-content {
        position: fixed;
        top: 0;
        right: -600px;
        width: 600px;
        height: 100vh;
        background: white;
        box-shadow: -2px 0 8px rgba(0,0,0,0.1);
        transition: right 0.3s ease;
        display: flex;
        flex-direction: column;
        z-index: 1001;
    }

    .drawer-overlay.active .drawer-content {
        right: 0;
    }

    .drawer-header {
        padding: 10px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .drawer-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .drawer-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }

    .drawer-close:hover {
        background: #f3f4f6;
    }

    .drawer-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
    }

    .drawer-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-shrink: 0;
    }

    .form-group {
        margin-bottom: 14px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 5px;
    }

    .form-control {
        width: 100%;
        padding: 7px 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 15px;
    }

    .form-control:focus {
        outline: none;
        border-color: #991b1b;
        box-shadow: 0 0 0 2px rgba(153, 27, 27, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 60px;
    }

    .error-message {
        display: block;
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
    }

    .form-control.error {
        border-color: #ef4444;
    }

    /* Mobile responsiveness for drawer */
    @media (max-width: 768px) {
        .drawer-content {
            width: 100%;
            right: -100%;
        }
        
        .drawer-footer {
            position: sticky;
            bottom: 0;
            background: white;
        }
</style>
@endpush

@section('content')
    <!-- Delegation Expiration Indicator -->
    @if(isset($isDelegated) && $isDelegated && isset($delegation))
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

<div class="content-section">
    <!-- Header -->
    <div class="section-header">
        <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
            <h2 class="section-title">Question Bank</h2>
            @if($currentExam)
                <span style="font-size: 16px; color: #6b7280;">–</span>
                <span style="font-size: 18px; font-weight: 600; color: #991b1b;">{{ $currentExam->title }}</span>
            @endif
        </div>
        <div class="section-actions">
            @if($currentExam)
                <button type="button" 
                        @if(auth()->user()->hasPermission('questions.manage_exam_settings'))
                            onclick="openEditSettingsDrawer()" 
                        @else
                            disabled
                            title="You do not have permission to manage exam settings"
                            style="opacity: 0.6; cursor: not-allowed; display: inline-flex; align-items: center; gap: 6px;"
                        @endif
                        class="btn-secondary" 
                        style="display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Exam Settings
                </button>
                <div class="actions-dropdown" id="headerActionsDropdown">
                    <button type="button" class="dropdown-toggle" onclick="toggleDropdown('headerActionsDropdown')">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                        </svg>
                        Actions
                    </button>
                    <div class="dropdown-menu">
                        <button type="button" 
                                @if(auth()->user()->hasPermission('questions.manage_exam_settings'))
                                    onclick="showNewSemesterDrawer(); toggleDropdown('headerActionsDropdown');"
                                    class="dropdown-item"
                                @else
                                    class="dropdown-item"
                                    disabled
                                    style="opacity: 0.6; cursor: not-allowed;"
                                    title="You do not have permission to create question banks"
                                @endif
                                >
                            <svg width="16" height="16" style="display: inline-block; margin-right: 8px; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Question Bank
                        </button>
                    </div>
                </div>
            @else
                <button 
                    @if(auth()->user()->hasPermission('questions.manage_exam_settings'))
                        onclick="showCreateExamModal()" 
                    @else
                        disabled
                        title="You do not have permission to create exams"
                        style="opacity: 0.6; cursor: not-allowed;"
                    @endif
                    class="btn-primary">
                    Setup First Exam
                </button>
            @endif
        </div>
    </div>

    @if($currentExam)
        <!-- Compact Status Bar with Quick Stats -->
        <div style="background: #fafafa; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap; font-size: 13px; color: #6b7280;">
                <span style="color: #1f2937; font-weight: 500;">{{ $stats['total_questions'] }} questions</span>
                <span style="color: #059669;">{{ $stats['active_questions'] }} active</span>
                <span style="color: #1e40af;">{{ $stats['mcq_count'] }} MCQ</span>
                <span style="color: #166534;">{{ $stats['tf_count'] }} T/F</span>
                @if($stats['draft_questions'] > 0)
                    <span style="color: #991b1b;">{{ $stats['draft_questions'] }} drafts</span>
                @endif
            </div>
            <div style="display: flex; gap: 6px; align-items: center;">
                <button onclick="toggleOverviewPanel()" class="btn-icon-only" title="Toggle Overview">
                    <span id="overviewToggleIcon">▼</span>
                </button>
                @if(!$currentExam->is_active)
                    <button 
                        @if(auth()->user()->hasPermission('questions.manage_exam_settings'))
                            onclick="publishExam({{ $currentExam->exam_id }})" 
                        @else
                            disabled
                            title="You do not have permission to publish exams"
                            style="opacity: 0.6; cursor: not-allowed; padding: 4px 10px; font-size: 13px;"
                        @endif
                        class="btn-success" 
                        style="padding: 4px 10px; font-size: 13px;">
                        Publish
                    </button>
                @endif
            </div>
        </div>

        <!-- Collapsible Overview Panel -->
        <div id="overviewPanel" style="display: none; margin-bottom: 16px;">
            <!-- Exam Info Card -->
            <div class="exam-info-card">
                <div style="display: flex; justify-content: space-between; align-items: start; gap: 16px;">
                    <div style="flex: 1;">
                        <div style="font-size: 13px; color: #6b7280; margin-bottom: 8px;">
                            {{ $currentExam->description }}
                        </div>
                        <div class="exam-meta" style="margin: 0;">
                            <span class="meta-item"><span class="meta-label">Duration:</span> {{ $currentExam->formatted_duration }}</span>
                            <span class="meta-item"><span class="meta-label">Created:</span> {{ $currentExam->created_at->format('M d, Y') }}</span>
                            @if($currentExam->total_items)
                                <span class="meta-item"><span class="meta-label">Exam Size:</span> {{ $currentExam->total_items }} items</span>
                            @endif
                            @if($currentExam->mcq_quota || $currentExam->tf_quota)
                                <span class="meta-item"><span class="meta-label">Quota:</span> MCQ:{{ $currentExam->mcq_quota ?? 0 }} / TF:{{ $currentExam->tf_quota ?? 0 }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($quotaProgress && ($quotaProgress['total_items'] > 0 || $quotaProgress['mcq_quota'] > 0 || $quotaProgress['tf_quota'] > 0))
            <!-- Quota Progress Indicators -->
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px;">
                <div style="font-size: 14px; font-weight: 600; color: #1f2937; margin-bottom: 12px;">Quota Progress</div>
                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                    @if($quotaProgress['total_items'] > 0)
                    <div style="flex: 1; min-width: 150px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-size: 12px; color: #6b7280;">Total Items</span>
                            <span style="font-size: 12px; font-weight: 600; color: #1f2937;">{{ $stats['active_questions'] }} / {{ $quotaProgress['total_items'] }}</span>
                        </div>
                        <div style="height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: {{ min(100, ($stats['active_questions'] / $quotaProgress['total_items']) * 100) }}%; background: {{ $stats['active_questions'] >= $quotaProgress['total_items'] ? '#059669' : '#991b1b' }};"></div>
                        </div>
                    </div>
                    @endif
                    @if($quotaProgress['mcq_quota'] > 0)
                    <div style="flex: 1; min-width: 150px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-size: 12px; color: #6b7280;">MCQ Quota</span>
                            <span style="font-size: 12px; font-weight: 600; color: {{ $quotaProgress['mcq_available'] >= $quotaProgress['mcq_quota'] ? '#059669' : '#991b1b' }};">{{ $quotaProgress['mcq_available'] }} / {{ $quotaProgress['mcq_quota'] }}</span>
                        </div>
                        <div style="height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: {{ min(100, ($quotaProgress['mcq_available'] / $quotaProgress['mcq_quota']) * 100) }}%; background: {{ $quotaProgress['mcq_available'] >= $quotaProgress['mcq_quota'] ? '#059669' : '#991b1b' }};"></div>
                        </div>
                    </div>
                    @endif
                    @if($quotaProgress['tf_quota'] > 0)
                    <div style="flex: 1; min-width: 150px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-size: 12px; color: #6b7280;">T/F Quota</span>
                            <span style="font-size: 12px; font-weight: 600; color: {{ $quotaProgress['tf_available'] >= $quotaProgress['tf_quota'] ? '#059669' : '#991b1b' }};">{{ $quotaProgress['tf_available'] }} / {{ $quotaProgress['tf_quota'] }}</span>
                        </div>
                        <div style="height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: {{ min(100, ($quotaProgress['tf_available'] / $quotaProgress['tf_quota']) * 100) }}%; background: {{ $quotaProgress['tf_available'] >= $quotaProgress['tf_quota'] ? '#059669' : '#991b1b' }};"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Clickable Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card" onclick="filterByType('')" style="cursor: pointer;" title="Click to show all">
                    <span class="stat-value">{{ $stats['total_questions'] }}</span>
                    <span class="stat-label">Total Questions</span>
                </div>
                <div class="stat-card" onclick="filterByStatus('active')" style="cursor: pointer;" title="Click to filter active">
                    <span class="stat-value" style="color: #059669;">{{ $stats['active_questions'] }}</span>
                    <span class="stat-label">Active</span>
                </div>
                <div class="stat-card" onclick="filterByType('multiple_choice')" style="cursor: pointer;" title="Click to filter MCQ">
                    <span class="stat-value" style="color: #1e40af;">{{ $stats['mcq_count'] }}</span>
                    <span class="stat-label">MCQ</span>
                </div>
                <div class="stat-card" onclick="filterByType('true_false')" style="cursor: pointer;" title="Click to filter T/F">
                    <span class="stat-value" style="color: #166534;">{{ $stats['tf_count'] }}</span>
                    <span class="stat-label">True/False</span>
                </div>
                <div class="stat-card" onclick="filterByStatus('draft')" style="cursor: pointer;" title="Click to filter drafts">
                    <span class="stat-value" style="color: #991b1b;">{{ $stats['draft_questions'] }}</span>
                    <span class="stat-label">Drafts</span>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <form method="GET" action="{{ route('admin.sets-questions.index') }}" id="filterForm">
            <div class="toolbar">
                <div class="toolbar-left">
                    <div class="search-box">
                        <input type="text" 
                               name="search" 
                               id="searchInput" 
                               class="form-control form-control-sm" 
                               placeholder="Search..." 
                               value="{{ request('search') }}"
                               style="width: 100%; height: 26px; padding: 4px 32px 4px 8px;">
                        <svg style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; pointer-events: none; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <select class="form-select form-select-sm" name="type" id="typeFilter" onchange="this.form.submit()" style="width: 180px; min-width: 180px; height: 40px; padding: 4px 28px 4px 8px;">
                        <option value="">All Types</option>
                        <option value="multiple_choice" {{ request('type') === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                        <option value="true_false" {{ request('type') === 'true_false' ? 'selected' : '' }}>True/False</option>
                    </select>
                    <select class="form-select form-select-sm" name="status" id="statusFilter" onchange="this.form.submit()" style="width: 180px; min-width: 180px; height: 40px; padding: 4px 28px 4px 8px;">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                @if($currentExam)
                <div class="toolbar-right">
                    @if(auth()->user()->hasPermission('questions.create'))
                    <button type="button" onclick="showImportDrawer()" class="btn-secondary" style="padding: 8px 14px; border-radius: 6px; border: 1px solid #d1d5db; cursor: pointer; font-size: 12px; font-weight: 500; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; background: white; color: #374151;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Import Questions
                    </button>
                    @endif
                    @if(auth()->user()->hasPermission('questions.create'))
                        <button type="button" onclick="showAddQuestionModal()" class="btn-primary" style="padding: 8px 14px; border-radius: 6px; border: none; cursor: pointer; font-size: 12px; font-weight: 500; transition: var(--transition); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Question
                        </button>
                    @endif
                </div>
                @endif
            </div>
        </form>

        <!-- Bulk Actions Bar -->
        <div id="bulkActionsBar" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 10px 12px; margin-bottom: 12px; align-items: center; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <span id="selectedCount" style="font-size: 14px; font-weight: 500; color: #1e40af;">0 selected</span>
                <div style="display: flex; gap: 6px;">
                    <button onclick="bulkUpdateStatus(true)" class="btn-outline" style="padding: 5px 12px; font-size: 13px;">Activate Selected</button>
                    <button onclick="bulkUpdateStatus(false)" class="btn-outline" style="padding: 5px 12px; font-size: 13px;">Deactivate Selected</button>
                    @if(auth()->user()->hasPermission('questions.delete'))
                        <button onclick="bulkDelete()" class="btn-outline" style="padding: 5px 12px; font-size: 13px; color: #991b1b; border-color: #fecaca;">Delete Selected</button>
                    @endif
                </div>
            </div>
            <button onclick="clearSelection()" style="background: none; border: none; color: #6b7280; cursor: pointer; font-size: 13px;">Clear</button>
        </div>

        <!-- Questions List -->
        <div class="questions-container" id="questionsList">
            @if($questions->count() > 0)
                <div style="padding: 8px 16px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 12px;">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" style="cursor: pointer;">
                    <label for="selectAll" style="font-size: 13px; color: #6b7280; cursor: pointer; margin: 0;">Select All</label>
                </div>
            @endif
            @forelse($questions as $question)
                <div class="question-item" 
                     data-question-id="{{ $question->question_id }}"
                     data-type="{{ $question->question_type }}" 
                     data-status="{{ $question->is_active ? 'active' : 'draft' }}"
                     data-text="{{ strtolower($question->question_text) }}">
                    <div style="display: flex; align-items: start; gap: 12px; flex: 1;">
                        <input type="checkbox" class="question-checkbox" value="{{ $question->question_id }}" onchange="updateBulkActions()" style="margin-top: 4px; cursor: pointer;">
                        <div class="question-content" style="flex: 1;">
                            <div class="question-text" style="margin-bottom: 6px;">{{ $question->question_text }}</div>
                            <div class="question-meta">
                                <span>Type: {{ ucwords(str_replace('_', ' ', $question->question_type)) }} Points: {{ $question->points }}</span>
                                @if(!$question->is_active)
                                    <span class="status-badge status-draft" style="margin-left: 8px;">Draft</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="question-actions">
                        @if(auth()->user()->hasPermission('questions.edit'))
                            <button onclick="editQuestion({{ $question->question_id }})" class="btn-icon" title="Edit">
                                Edit
                            </button>
                            <button onclick="toggleQuestionStatus({{ $question->question_id }})" class="btn-icon" title="Toggle Status">
                                {{ $question->is_active ? 'Hide' : 'Show' }}
                            </button>
                        @endif
                        @if(auth()->user()->hasPermission('questions.delete'))
                            <button onclick="deleteQuestion({{ $question->question_id }})" class="btn-icon danger" title="Delete">
                                Delete
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <h4>No Questions Found</h4>
                    <p>@if(request()->hasAny(['search', 'type', 'status'])) No questions match your filters. @else Start building your question bank by adding your first question. @endif</p>
                    @if(auth()->user()->hasPermission('questions.create'))
                        <button onclick="showAddQuestionModal()" class="btn-primary">
                            Add First Question
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($questions->hasPages())
        <div style="margin-top: 16px; display: flex; justify-content: center; align-items: center; gap: 8px;">
            {{ $questions->links() }}
        </div>
        @endif
    @else
        <!-- No Exam Setup -->
        <div class="questions-container">
            <div class="empty-state">
                <h4>No Exam Configured</h4>
                <p>Create your first exam to start building your question bank.</p>
                <button onclick="showCreateExamModal()" class="btn-primary">
                    Setup First Exam
                </button>
            </div>
        </div>
    @endif
</div>

<!-- Add/Edit Question Drawer -->
<div id="questionDrawer" class="drawer-overlay">
    <div class="drawer-content">
        <div class="drawer-header">
            <h3 id="drawerTitle">Add Question</h3>
            <button class="drawer-close" onclick="closeQuestionDrawer()">×</button>
        </div>
        <div class="drawer-body">
            <form id="questionForm">
                <input type="hidden" id="questionId" name="question_id">
                <input type="hidden" name="exam_id" value="{{ $currentExam->exam_id ?? '' }}">
                
                <div class="form-group">
                    <label class="form-label">Question Type</label>
                    <select class="form-control" name="question_type" id="questionType" required onchange="handleTypeChange()">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Question Text</label>
                    <textarea class="form-control" name="question_text" id="questionText" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Points</label>
                    <input type="number" class="form-control" name="points" id="questionPoints" value="1" min="1" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Order Number (Optional)</label>
                    <input type="number" class="form-control" name="order_number" id="questionOrder" min="1">
                </div>

                <div class="form-group">
                    <label class="form-label">Explanation (Optional)</label>
                    <textarea class="form-control" name="explanation" id="questionExplanation" rows="2"></textarea>
                </div>

                <div id="optionsContainer" style="display: none;">
                    <label class="form-label">Answer Options</label>
                    <div id="optionsList"></div>
                    <button type="button" onclick="addOption()" class="btn-outline" style="margin-top: 8px;">Add Option</button>
                </div>
            </form>
        </div>
        <div class="drawer-footer">
            <button class="btn-secondary" onclick="closeQuestionDrawer()">Cancel</button>
            <button class="btn-primary" onclick="saveQuestion()" id="saveQuestionBtn">Save Question</button>
        </div>
    </div>
</div>

<!-- Edit Exam Settings Drawer -->
<div id="settingsDrawer" class="drawer-overlay">
    <div class="drawer-content">
        <div class="drawer-header">
            <h3>Exam Settings</h3>
            <button class="drawer-close" onclick="closeSettingsDrawer()">×</button>
        </div>
        <div class="drawer-body">
            <form id="settingsForm">
                <input type="hidden" id="exam_id" name="exam_id" value="{{ $currentExam->exam_id ?? '' }}">
                
                <div class="form-group">
                    <label class="form-label">Duration (minutes) <span style="color: #ef4444;">*</span></label>
                    <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                           value="{{ $currentExam->duration_minutes ?? '' }}" min="1" max="600" required>
                    <span class="error-message" id="error_duration_minutes"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Total Items <span style="color: #ef4444;">*</span></label>
                    <input type="number" class="form-control" id="total_items" name="total_items" 
                           value="{{ $currentExam->total_items ?? '' }}" min="1" required>
                    <span class="error-message" id="error_total_items"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Multiple Choice Quota</label>
                    <input type="number" class="form-control" id="mcq_quota" name="mcq_quota" 
                           value="{{ $currentExam->mcq_quota ?? 0 }}" min="0">
                    <span class="error-message" id="error_mcq_quota"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">True/False Quota</label>
                    <input type="number" class="form-control" id="tf_quota" name="tf_quota" 
                           value="{{ $currentExam->tf_quota ?? 0 }}" min="0">
                    <span class="error-message" id="error_tf_quota"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="is_active" name="is_active" 
                               {{ $currentExam && $currentExam->is_active ? 'checked' : '' }}>
                        Active
                    </label>
                    <small style="color: #6b7280; font-size: 13px;">Make this exam available for applicants</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Availability Start</label>
                    <input type="datetime-local" class="form-control" id="starts_at" name="starts_at" 
                           value="{{ $currentExam && $currentExam->starts_at ? $currentExam->starts_at->format('Y-m-d\TH:i') : '' }}">
                    <span class="error-message" id="error_starts_at"></span>
                    <small style="color: #6b7280; font-size: 13px;">When can applicants start taking the exam?</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Availability End</label>
                    <input type="datetime-local" class="form-control" id="ends_at" name="ends_at" 
                           value="{{ $currentExam && $currentExam->ends_at ? $currentExam->ends_at->format('Y-m-d\TH:i') : '' }}">
                    <span class="error-message" id="error_ends_at"></span>
                    <small style="color: #6b7280; font-size: 13px;">When should the exam become unavailable?</small>
                </div>

                <div style="padding: 12px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 4px; margin-top: 16px;">
                    <div style="font-size: 13px; color: #92400e;">
                        <strong>Note:</strong> MCQ quota + TF quota must equal or be less than total items.
                    </div>
                </div>
            </form>
        </div>
        <div class="drawer-footer">
            <button class="btn-secondary" onclick="closeSettingsDrawer()">Cancel</button>
            <button class="btn-primary" onclick="saveSettings()" id="saveSettingsBtn">Save Settings</button>
        </div>
    </div>
</div>

<!-- New Semester Drawer (Add Question Bank) -->
<div id="newSemesterDrawer" class="drawer-overlay">
    <div class="drawer-content">
        <div class="drawer-header">
            <h3 id="newSemesterDrawerTitle">Add Question Bank</h3>
            <button class="drawer-close" onclick="closeNewSemesterDrawer()">×</button>
        </div>
        <div class="drawer-body">
            <form id="newSemesterForm">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">
                        Exam Title <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="newSemester_title" 
                           name="title" 
                           placeholder="e.g., EnrollAssess - First Semester 2025" 
                           required>
                    <span class="error-message" id="newSemester_error_title"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Description
                    </label>
                    <textarea class="form-control" 
                              id="newSemester_description" 
                              name="description" 
                              rows="3" 
                              placeholder="Brief description of this exam..."></textarea>
                    <span class="error-message" id="newSemester_error_description"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Duration (minutes) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="number" 
                           class="form-control" 
                           id="newSemester_duration_minutes" 
                           name="duration_minutes" 
                           min="5" 
                           max="480" 
                           value="60" 
                           required>
                    <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                        <button type="button" onclick="document.getElementById('newSemester_duration_minutes').value=30" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">30 min</button>
                        <button type="button" onclick="document.getElementById('newSemester_duration_minutes').value=60" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">1 hour</button>
                        <button type="button" onclick="document.getElementById('newSemester_duration_minutes').value=90" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">1.5 hours</button>
                        <button type="button" onclick="document.getElementById('newSemester_duration_minutes').value=120" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">2 hours</button>
                    </div>
                    <span class="error-message" id="newSemester_error_duration_minutes"></span>
                </div>

                <div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 4px; padding: 12px; margin-top: 16px;">
                    <p style="margin: 0; font-size: 13px; color: #92400e; line-height: 1.5;">
                        <strong style="color: #78350f;">Important:</strong> Creating a new exam will archive the current active exam. Only one exam can be active at a time (per semester). The new exam starts as a draft - publish it when ready.
                    </p>
                </div>
            </form>
        </div>
        <div class="drawer-footer">
            <button class="btn-secondary" onclick="closeNewSemesterDrawer()">Cancel</button>
            <button class="btn-primary" onclick="saveNewSemester()" id="saveNewSemesterBtn">Create Exam</button>
        </div>
    </div>
</div>

<!-- Import Questions Drawer -->
<div id="importDrawer" class="drawer-overlay">
    <div class="drawer-content">
        <div class="drawer-header">
            <h3>Import Questions from CSV</h3>
            <button class="drawer-close" onclick="closeImportDrawer()">×</button>
        </div>
        <div class="drawer-body">
            <div class="form-group">
                <label class="form-label">
                    CSV File <span style="color: #ef4444;">*</span>
                </label>
                <input type="file" 
                       id="importCsvFile" 
                       name="csv_file" 
                       accept=".csv,.txt"
                       class="form-control"
                       required>
                <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">
                    Upload a CSV file with questions. Maximum file size: 2MB
                </small>
                <span class="error-message" id="import_error_file"></span>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" id="importAsDraft" name="import_as_draft" value="1">
                    <span style="margin-left: 8px;">Import as draft (inactive questions)</span>
                </label>
                <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">
                    If checked, imported questions will be inactive and won't appear in exams until activated.
                </small>
            </div>

            <div style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin-top: 20px;">
                <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 600; color: #1f2937;">CSV Format Requirements:</h4>
                <ul style="margin: 0; padding-left: 20px; color: #6b7280; font-size: 13px; line-height: 1.8;">
                    <li><strong>Multiple Choice:</strong> Question Text, Question Type (multiple_choice), Points, Option 1, Option 2, Option 3, Option 4, Correct Answer (Option 1-4)</li>
                    <li><strong>True/False:</strong> Question Text, Question Type (true_false), Points, Option 1 (empty), Option 2 (empty), Option 3 (empty), Option 4 (empty), Correct Answer (True/False)</li>
                    <li>Download the template below to see the exact format</li>
                </ul>
            </div>

            <div style="margin-top: 20px;">
                <a href="{{ route('admin.sets-questions.import.template') }}" 
                   class="btn-secondary" 
                   style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; text-decoration: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download CSV Template
                </a>
            </div>

            <div id="importResults" style="display: none; margin-top: 20px; padding: 12px; border-radius: 6px; font-size: 13px;"></div>
        </div>
        <div class="drawer-footer">
            <button class="btn-secondary" onclick="closeImportDrawer()">Cancel</button>
            <button class="btn-primary" onclick="processImport()" id="processImportBtn">Import Questions</button>
        </div>
    </div>
</div>

<!-- Create Exam Modal (for first exam setup only) -->
<div id="examModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 8px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="examModalTitle" style="margin: 0; font-size: 20px; font-weight: 600; color: #1f2937;">Setup First Exam</h3>
            <button onclick="closeExamModal()" style="background: none; border: none; font-size: 28px; color: #6b7280; cursor: pointer; padding: 0; width: 32px; height: 32px;">&times;</button>
        </div>
        
        <div style="padding: 24px;">
            <form id="examForm">
                @csrf
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Exam Title <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="exam_title" 
                           name="title" 
                           placeholder="e.g., EnrollAssess - First Semester 2025" 
                           required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;">
                    <span class="error-message" id="exam_error_title" style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;"></span>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Description
                    </label>
                    <textarea class="form-control" 
                              id="exam_description" 
                              name="description" 
                              rows="2" 
                              placeholder="Brief description of this exam..."
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
                    <span class="error-message" id="exam_error_description" style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;"></span>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Duration (minutes) <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="number" 
                           class="form-control" 
                           id="exam_duration_minutes" 
                           name="duration_minutes" 
                           min="5" 
                           max="480" 
                           value="60" 
                           required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;">
                    <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                        <button type="button" onclick="document.getElementById('exam_duration_minutes').value=30" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">30 min</button>
                        <button type="button" onclick="document.getElementById('exam_duration_minutes').value=60" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">1 hour</button>
                        <button type="button" onclick="document.getElementById('exam_duration_minutes').value=90" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">1.5 hours</button>
                        <button type="button" onclick="document.getElementById('exam_duration_minutes').value=120" style="padding: 4px 12px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; cursor: pointer;">2 hours</button>
                    </div>
                    <span class="error-message" id="exam_error_duration_minutes" style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;"></span>
                </div>

                <div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 4px; padding: 12px; margin-bottom: 20px;">
                    <p style="margin: 0; font-size: 13px; color: #92400e; line-height: 1.5;">
                        <strong style="color: #78350f;">Important:</strong> Creating a new exam will archive the current active exam. Only one exam can be active at a time (per semester). The new exam starts as a draft - publish it when ready.
                    </p>
                </div>
            </form>
        </div>

        <div style="padding: 16px 24px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" 
                    onclick="closeExamModal()" 
                    class="btn-outline"
                    style="padding: 8px 16px; background: white; color: #6b7280; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer;">
                Cancel
            </button>
            <button type="button" 
                    onclick="saveExam()" 
                    id="saveExamBtn"
                    class="btn-primary"
                    style="padding: 8px 16px; background: #991b1b; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer;">
                Create Exam
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
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

    // Toggle overview panel
    function toggleOverviewPanel() {
        const panel = document.getElementById('overviewPanel');
        const icon = document.getElementById('overviewToggleIcon');
        
        if (panel.style.display === 'none') {
            panel.style.display = 'block';
            icon.textContent = '▲';
        } else {
            panel.style.display = 'none';
            icon.textContent = '▼';
        }
    }

    // Filter by type (clickable stats cards)
    function filterByType(type) {
        const form = document.getElementById('filterForm');
        const typeInput = form.querySelector('[name="type"]');
        typeInput.value = type;
        form.submit();
    }

    // Filter by status (clickable stats cards)
    function filterByStatus(status) {
        const form = document.getElementById('filterForm');
        const statusInput = form.querySelector('[name="status"]');
        statusInput.value = status;
        form.submit();
    }

    // Bulk selection functions
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.question-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.question-checkbox:checked');
        const bulkBar = document.getElementById('bulkActionsBar');
        const selectedCount = document.getElementById('selectedCount');
        
        if (checkedBoxes.length > 0) {
            bulkBar.style.display = 'flex';
            selectedCount.textContent = `${checkedBoxes.length} selected`;
        } else {
            bulkBar.style.display = 'none';
        }
        
        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.question-checkbox');
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.checked = allCheckboxes.length > 0 && checkedBoxes.length === allCheckboxes.length;
        }
    }

    function clearSelection() {
        document.querySelectorAll('.question-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('selectAll');
        if (selectAll) selectAll.checked = false;
        updateBulkActions();
    }

    function getSelectedQuestionIds() {
        return Array.from(document.querySelectorAll('.question-checkbox:checked')).map(cb => parseInt(cb.value));
    }

    // Bulk update status
    function bulkUpdateStatus(status) {
        const ids = getSelectedQuestionIds();
        if (ids.length === 0) {
            alert('Please select at least one question.');
            return;
        }

        const action = status ? 'activate' : 'deactivate';
        if (!confirm(`Are you sure you want to ${action} ${ids.length} selected question(s)?`)) {
            return;
        }

        fetch('{{ route("admin.sets-questions.bulk-update-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                question_ids: ids,
                status: status
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
        .catch(error => alert('Error: ' + error.message));
    }

    // Bulk delete
    function bulkDelete() {
        const ids = getSelectedQuestionIds();
        if (ids.length === 0) {
            alert('Please select at least one question.');
            return;
        }

        if (!confirm(`Are you sure you want to delete ${ids.length} selected question(s)? This action cannot be undone.`)) {
            return;
        }

        fetch('{{ route("admin.sets-questions.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                question_ids: ids
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
        .catch(error => alert('Error: ' + error.message));
    }


    // Show add question drawer
    function showAddQuestionModal() {
        document.getElementById('drawerTitle').textContent = 'Add Question';
        document.getElementById('questionForm').reset();
        document.getElementById('questionId').value = '';
        document.getElementById('questionDrawer').classList.add('active');
        handleTypeChange();
    }

    // Close question drawer
    function closeQuestionDrawer() {
        document.getElementById('questionDrawer').classList.remove('active');
    }

    // Handle question type change
    function handleTypeChange() {
        const type = document.getElementById('questionType').value;
        const optionsContainer = document.getElementById('optionsContainer');
        
        if (type === 'multiple_choice' || type === 'true_false') {
            optionsContainer.style.display = 'block';
            if (type === 'true_false') {
                // Auto-populate True/False options
                document.getElementById('optionsList').innerHTML = `
                    <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                        <input type="text" class="form-control" name="options[]" value="True" readonly>
                        <label style="display: flex; align-items: center; gap: 4px;">
                            <input type="radio" name="correct_option" value="0" required> Correct
                        </label>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" class="form-control" name="options[]" value="False" readonly>
                        <label style="display: flex; align-items: center; gap: 4px;">
                            <input type="radio" name="correct_option" value="1" required> Correct
                        </label>
                    </div>
                `;
            } else {
                // Clear for multiple choice
                document.getElementById('optionsList').innerHTML = '';
                addOption();
                addOption();
            }
        } else {
            optionsContainer.style.display = 'none';
        }
    }

    // Add option for MCQ
    let optionCount = 0;
    function addOption() {
        const optionsList = document.getElementById('optionsList');
        const optionHtml = `
            <div class="option-item" style="display: flex; gap: 8px; margin-bottom: 8px;">
                <input type="text" class="form-control" name="options[]" placeholder="Option text" required>
                <label style="display: flex; align-items: center; gap: 4px; white-space: nowrap;">
                    <input type="radio" name="correct_option" value="${optionCount}" required> Correct
                </label>
                <button type="button" onclick="this.parentElement.remove()" class="btn-icon danger">×</button>
            </div>
        `;
        optionsList.insertAdjacentHTML('beforeend', optionHtml);
        optionCount++;
    }

    // Save question
    function saveQuestion() {
        const form = document.getElementById('questionForm');
        const formData = new FormData(form);
        const questionId = document.getElementById('questionId').value;
        
        // Collect options
        const questionType = document.getElementById('questionType').value;
        if (questionType === 'multiple_choice' || questionType === 'true_false') {
            const options = [];
            const optionInputs = document.querySelectorAll('#optionsList input[name="options[]"]');
            const correctRadio = document.querySelector('input[name="correct_option"]:checked');
            
            optionInputs.forEach((option, index) => {
                options.push({
                    option_text: option.value,
                    is_correct: correctRadio && parseInt(correctRadio.value) === index
                });
            });
            
            formData.append('options', JSON.stringify(options));
        }
        
        const url = questionId ? `/admin/questions/${questionId}` : '/admin/questions';
        const method = questionId ? 'PUT' : 'POST';
        
        const saveBtn = document.getElementById('saveQuestionBtn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeQuestionDrawer();
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save question'));
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Question';
            }
        })
        .catch(error => {
            alert('Error saving question: ' + error.message);
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Question';
        });
    }

    // Edit question
    function editQuestion(id) {
        fetch(`/admin/questions/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch question');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.question) {
                    const question = data.question;
                    
                    document.getElementById('drawerTitle').textContent = 'Edit Question';
                    document.getElementById('questionId').value = question.question_id;
                    document.getElementById('questionType').value = question.question_type;
                    document.getElementById('questionText').value = question.question_text;
                    document.getElementById('questionPoints').value = question.points;
                    document.getElementById('questionOrder').value = question.order_number || '';
                    document.getElementById('questionExplanation').value = question.explanation || '';
                    
                    // Set up options container first
                    const optionsContainer = document.getElementById('optionsContainer');
                    const optionsList = document.getElementById('optionsList');
                    
                    if (question.question_type === 'multiple_choice' || question.question_type === 'true_false') {
                        optionsContainer.style.display = 'block';
                        optionsList.innerHTML = '';
                        optionCount = 0;
                        
                        // Load options if they exist
                        if (question.options && question.options.length > 0) {
                            if (question.question_type === 'true_false') {
                                // True/False options - readonly
                                question.options.forEach((option, index) => {
                                    const escapedText = (option.option_text || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                                    const optionHtml = `
                                        <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                                            <input type="text" class="form-control" name="options[]" value="${escapedText}" readonly>
                                            <label style="display: flex; align-items: center; gap: 4px;">
                                                <input type="radio" name="correct_option" value="${optionCount}" ${option.is_correct ? 'checked' : ''} required> Correct
                                            </label>
                                        </div>
                                    `;
                                    optionsList.insertAdjacentHTML('beforeend', optionHtml);
                                    optionCount++;
                                });
                            } else {
                                // Multiple choice options - editable and deletable
                                question.options.forEach((option, index) => {
                                    const escapedText = (option.option_text || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                                    const optionHtml = `
                                        <div class="option-item" style="display: flex; gap: 8px; margin-bottom: 8px;">
                                            <input type="text" class="form-control" name="options[]" value="${escapedText}" required>
                                            <label style="display: flex; align-items: center; gap: 4px; white-space: nowrap;">
                                                <input type="radio" name="correct_option" value="${optionCount}" ${option.is_correct ? 'checked' : ''} required> Correct
                                            </label>
                                            <button type="button" onclick="this.parentElement.remove()" class="btn-icon danger">×</button>
                                        </div>
                                    `;
                                    optionsList.insertAdjacentHTML('beforeend', optionHtml);
                                    optionCount++;
                                });
                            }
                        } else if (question.question_type === 'multiple_choice') {
                            // If no options for MCQ, add default empty ones
                            addOption();
                            addOption();
                        }
                    } else {
                        optionsContainer.style.display = 'none';
                    }
                    
                    document.getElementById('questionDrawer').classList.add('active');
                } else {
                    alert('Error loading question: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading question: ' + error.message);
            });
    }


    // Toggle question status
    function toggleQuestionStatus(id) {
        fetch(`/admin/questions/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
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
        .catch(error => alert('Error: ' + error.message));
    }

    // Delete question
    function deleteQuestion(id) {
        if (confirm('Are you sure you want to delete this question?')) {
            fetch(`/admin/questions/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
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
            .catch(error => alert('Error: ' + error.message));
        }
    }

    // Publish exam (make it the single active exam)
    function publishExam(id) {
        if (confirm('Publish this exam? It will become the active exam for applicants and deactivate any previous exam.')) {
            fetch(`/admin/sets-questions/${id}/publish`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }

    // Show new semester drawer
    function showNewSemesterDrawer() {
        document.getElementById('newSemesterDrawerTitle').textContent = 'Add Question Bank';
        document.getElementById('newSemesterForm').reset();
        document.getElementById('newSemesterDrawer').classList.add('active');
        clearNewSemesterErrors();
    }

    // Close new semester drawer
    function closeNewSemesterDrawer() {
        document.getElementById('newSemesterDrawer').classList.remove('active');
        clearNewSemesterErrors();
    }

    // Clear new semester form errors
    function clearNewSemesterErrors() {
        document.querySelectorAll('#newSemesterForm .error-message').forEach(el => el.textContent = '');
        document.querySelectorAll('#newSemesterForm .form-control.error').forEach(el => el.classList.remove('error'));
    }

    // Show new semester field error
    function showNewSemesterFieldError(fieldName, message) {
        const errorEl = document.getElementById('newSemester_error_' + fieldName);
        const inputEl = document.getElementById('newSemester_' + fieldName);
        
        if (errorEl) {
            errorEl.textContent = message;
        }
        if (inputEl) {
            inputEl.classList.add('error');
        }
    }

    // Save new semester (create new exam)
    function saveNewSemester() {
        const form = document.getElementById('newSemesterForm');
        const formData = new FormData(form);
        const submitBtn = document.getElementById('saveNewSemesterBtn');
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';
        
        clearNewSemesterErrors();
        
        fetch('/admin/exams', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close drawer and reload page to show new exam
                closeNewSemesterDrawer();
                window.location.reload();
            } else {
                // Show validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showNewSemesterFieldError(field, data.errors[field][0]);
                    });
                } else if (data.message) {
                    alert(data.message);
                }
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Exam';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to create exam. Please try again.');
            
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Exam';
        });
    }

    // Show create exam modal
    function showCreateExamModal() {
        document.getElementById('examModalTitle').textContent = 'Setup First Exam';
        document.getElementById('examForm').reset();
        document.getElementById('examModal').style.display = 'flex';
        clearExamErrors();
    }

    // Close exam modal
    function closeExamModal() {
        document.getElementById('examModal').style.display = 'none';
        clearExamErrors();
    }

    // Show import drawer
    function showImportDrawer() {
        document.getElementById('importDrawer').classList.add('active');
        document.getElementById('importCsvFile').value = '';
        document.getElementById('importAsDraft').checked = false;
        document.getElementById('importResults').style.display = 'none';
        document.getElementById('importResults').innerHTML = '';
        document.getElementById('import_error_file').textContent = '';
    }

    // Close import drawer
    function closeImportDrawer() {
        document.getElementById('importDrawer').classList.remove('active');
        document.getElementById('importCsvFile').value = '';
        document.getElementById('importAsDraft').checked = false;
        document.getElementById('importResults').style.display = 'none';
        document.getElementById('importResults').innerHTML = '';
        document.getElementById('import_error_file').textContent = '';
    }

    // Process import
    function processImport() {
        const fileInput = document.getElementById('importCsvFile');
        const importAsDraft = document.getElementById('importAsDraft').checked;
        const processBtn = document.getElementById('processImportBtn');
        const resultsDiv = document.getElementById('importResults');
        const errorSpan = document.getElementById('import_error_file');

        // Clear previous errors
        errorSpan.textContent = '';
        resultsDiv.style.display = 'none';
        resultsDiv.innerHTML = '';

        // Validate file
        if (!fileInput.files || fileInput.files.length === 0) {
            errorSpan.textContent = 'Please select a CSV file.';
            return;
        }

        const file = fileInput.files[0];
        if (file.size > 2048 * 1024) {
            errorSpan.textContent = 'File size must be less than 2MB.';
            return;
        }

        // Create form data
        const formData = new FormData();
        formData.append('csv_file', file);
        if (importAsDraft) {
            formData.append('import_as_draft', '1');
        }
        formData.append('_token', '{{ csrf_token() }}');

        // Disable button and show loading
        processBtn.disabled = true;
        processBtn.textContent = 'Importing...';

        // Send request
        fetch('{{ route("admin.sets-questions.import") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                resultsDiv.style.display = 'block';
                resultsDiv.style.background = '#d1fae5';
                resultsDiv.style.borderColor = '#10b981';
                resultsDiv.style.color = '#065f46';
                
                let html = '<strong>✓ Import Successful!</strong><br>';
                html += data.message + '<br><br>';
                
                if (data.results) {
                    html += '<strong>Summary:</strong><br>';
                    html += `• Total rows processed: ${data.results.total}<br>`;
                    html += `• Successfully imported: ${data.results.successful}<br>`;
                    if (data.results.failed > 0) {
                        html += `• Failed: ${data.results.failed}<br>`;
                    }
                    
                    if (data.results.errors && data.results.errors.length > 0) {
                        html += '<br><strong>Errors:</strong><br>';
                        html += '<ul style="margin: 8px 0 0 0; padding-left: 20px;">';
                        data.results.errors.slice(0, 10).forEach(error => {
                            html += `<li style="margin: 4px 0;">${error}</li>`;
                        });
                        if (data.results.errors.length > 10) {
                            html += `<li>... and ${data.results.errors.length - 10} more errors</li>`;
                        }
                        html += '</ul>';
                    }
                }
                
                resultsDiv.innerHTML = html;
                
                // Reload page after 2 seconds to show new questions
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                // Show error message
                resultsDiv.style.display = 'block';
                resultsDiv.style.background = '#fee2e2';
                resultsDiv.style.borderColor = '#ef4444';
                resultsDiv.style.color = '#991b1b';
                resultsDiv.innerHTML = '<strong>✗ Import Failed</strong><br>' + data.message;
                
                if (data.errors && data.errors.length > 0) {
                    resultsDiv.innerHTML += '<br><ul style="margin: 8px 0 0 0; padding-left: 20px;">';
                    data.errors.forEach(error => {
                        resultsDiv.innerHTML += `<li>${error}</li>`;
                    });
                    resultsDiv.innerHTML += '</ul>';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultsDiv.style.display = 'block';
            resultsDiv.style.background = '#fee2e2';
            resultsDiv.style.borderColor = '#ef4444';
            resultsDiv.style.color = '#991b1b';
            resultsDiv.innerHTML = '<strong>✗ Import Failed</strong><br>An error occurred while processing the import. Please try again.';
        })
        .finally(() => {
            // Re-enable button
            processBtn.disabled = false;
            processBtn.textContent = 'Import Questions';
        });
    }

    // Clear exam form errors
    function clearExamErrors() {
        document.querySelectorAll('#examForm .error-message').forEach(el => el.textContent = '');
        document.querySelectorAll('#examForm .form-control.error').forEach(el => el.classList.remove('error'));
    }

    // Show exam field error
    function showExamFieldError(fieldName, message) {
        const errorEl = document.getElementById('exam_error_' + fieldName);
        const inputEl = document.getElementById('exam_' + fieldName);
        
        if (errorEl) {
            errorEl.textContent = message;
        }
        if (inputEl) {
            inputEl.classList.add('error');
        }
    }

    // Save exam (create)
    function saveExam() {
        const form = document.getElementById('examForm');
        const formData = new FormData(form);
        const submitBtn = document.getElementById('saveExamBtn');
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';
        
        clearExamErrors();
        
        fetch('/admin/exams', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to show new exam
                window.location.reload();
            } else {
                // Show validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showExamFieldError(field, data.errors[field][0]);
                    });
                } else if (data.message) {
                    alert(data.message);
                }
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Exam';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to create exam. Please try again.');
            
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Exam';
        });
    }

    // Open edit settings drawer
    function openEditSettingsDrawer() {
        clearSettingsErrors();
        document.getElementById('settingsDrawer').classList.add('active');
    }

    // Close settings drawer
    function closeSettingsDrawer() {
        document.getElementById('settingsDrawer').classList.remove('active');
        clearSettingsErrors();
    }

    // Clear all error messages
    function clearSettingsErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
    }

    // Show error for specific field
    function showFieldError(fieldName, message) {
        const errorEl = document.getElementById('error_' + fieldName);
        const inputEl = document.getElementById(fieldName);
        
        if (errorEl) {
            errorEl.textContent = message;
        }
        if (inputEl) {
            inputEl.classList.add('error');
        }
    }

    // Save exam settings
    function saveSettings() {
        const examId = document.getElementById('exam_id').value;
        if (!examId) {
            alert('Exam ID not found');
            return;
        }

        clearSettingsErrors();

        const saveBtn = document.getElementById('saveSettingsBtn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        // Collect form data
        const payload = {
            duration_minutes: parseInt(document.getElementById('duration_minutes').value),
            total_items: parseInt(document.getElementById('total_items').value),
            mcq_quota: parseInt(document.getElementById('mcq_quota').value) || 0,
            tf_quota: parseInt(document.getElementById('tf_quota').value) || 0,
            is_active: document.getElementById('is_active').checked,
            starts_at: document.getElementById('starts_at').value || null,
            ends_at: document.getElementById('ends_at').value || null,
        };

        fetch(`/admin/exams/${examId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeSettingsDrawer();
                
                // Update the UI with new values without full reload
                updateExamInfoDisplay(data.exam);
                
                // Show success message
                alert(data.message || 'Exam settings updated successfully!');
            } else {
                // Handle validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorMessages = data.errors[field];
                        if (Array.isArray(errorMessages) && errorMessages.length > 0) {
                            showFieldError(field, errorMessages[0]);
                        }
                    });
                } else {
                    alert(data.message || 'Failed to update settings');
                }
                
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Settings';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving settings');
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Settings';
        });
    }

    // Update exam info display after save
    function updateExamInfoDisplay(exam) {
        // Update duration
        const durationEl = document.querySelector('.exam-meta .meta-item:nth-child(1)');
        if (durationEl && exam.formatted_duration) {
            durationEl.innerHTML = `<span class="meta-label">Duration:</span> ${exam.formatted_duration}`;
        }

        // Update exam size
        const sizeEl = document.querySelector('.exam-meta .meta-item:nth-child(4)');
        if (sizeEl && exam.total_items) {
            sizeEl.innerHTML = `<span class="meta-label">Exam Size:</span> ${exam.total_items} items`;
        }

        // Update quotas
        const quotaEl = document.querySelector('.exam-meta .meta-item:nth-child(5)');
        if (quotaEl) {
            const mcq = exam.mcq_quota || 0;
            const tf = exam.tf_quota || 0;
            quotaEl.innerHTML = `<span class="meta-label">Quota:</span> MCQ:${mcq} / TF:${tf}`;
        }

        // Update form values for next open
        document.getElementById('duration_minutes').value = exam.duration_minutes || '';
        document.getElementById('total_items').value = exam.total_items || '';
        document.getElementById('mcq_quota').value = exam.mcq_quota || 0;
        document.getElementById('tf_quota').value = exam.tf_quota || 0;
        document.getElementById('is_active').checked = exam.is_active;
        
        if (exam.starts_at) {
            // Convert server datetime to local datetime-local format
            const startsAt = new Date(exam.starts_at);
            document.getElementById('starts_at').value = formatDateTimeLocal(startsAt);
        }
        
        if (exam.ends_at) {
            const endsAt = new Date(exam.ends_at);
            document.getElementById('ends_at').value = formatDateTimeLocal(endsAt);
        }
    }

    // Format date for datetime-local input
    function formatDateTimeLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    // Close drawer on outside click or Esc
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('drawer-overlay')) {
            if (event.target.id === 'questionDrawer') {
                closeQuestionDrawer();
            } else if (event.target.id === 'importDrawer') {
                closeImportDrawer();
            } else if (event.target.id === 'settingsDrawer') {
                closeSettingsDrawer();
            } else if (event.target.id === 'newSemesterDrawer') {
                closeNewSemesterDrawer();
            }
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeQuestionDrawer();
            closeSettingsDrawer();
            closeNewSemesterDrawer();
            closeExamModal();
        }
    });

    // Close modal when clicking outside
    document.getElementById('examModal')?.addEventListener('click', function(event) {
        if (event.target === this) {
            closeExamModal();
        }
    });

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
</script>
@endpush
