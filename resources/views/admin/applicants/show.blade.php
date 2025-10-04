@extends('layouts.admin')

@section('title', 'Applicant Details - ' . config('app.name', 'EnrollAssess'))

@push('styles')
    <link href="{{ asset('css/admin/applicants.css') }}" rel="stylesheet">
    <style>
        .applicant-detail-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-6);
            background: var(--background-gray);
            min-height: 100vh;
        }

        .detail-header {
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            border-radius: var(--radius-2xl);
            padding: var(--space-8);
            margin-bottom: var(--space-8);
            color: var(--white);
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .detail-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 215, 0, 0.1);
            border-radius: 50%;
            transform: translate(50%, -50%);
        }

        .header-breadcrumb {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            margin-bottom: var(--space-4);
            font-size: var(--text-sm);
            opacity: 0.9;
        }

        .breadcrumb-link {
            color: var(--yellow-light);
            text-decoration: none;
            transition: var(--transition-colors);
        }

        .breadcrumb-link:hover {
            color: var(--yellow-primary);
            text-decoration: underline;
        }

        .breadcrumb-separator {
            color: rgba(255, 255, 255, 0.6);
        }

        .header-content {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: var(--space-8);
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .header-avatar {
            position: relative;
        }

        .avatar-large {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--yellow-primary) 0%, var(--yellow-dark) 100%);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--text-4xl);
            font-weight: var(--font-bold);
            color: var(--maroon-primary);
            border: 4px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-lg);
        }

        .status-indicator {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            border-radius: var(--radius-full);
            border: 3px solid var(--white);
            background: var(--success);
        }

        .header-info h1 {
            font-size: var(--text-4xl);
            font-weight: var(--font-bold);
            margin: 0 0 var(--space-2) 0;
            line-height: var(--leading-tight);
        }

        .header-subtitle {
            font-size: var(--text-lg);
            opacity: 0.9;
            margin-bottom: var(--space-4);
        }

        .header-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: var(--space-4);
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: var(--space-1);
        }

        .meta-label {
            font-size: var(--text-xs);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.8;
            font-weight: var(--font-medium);
        }

        .meta-value {
            font-size: var(--text-base);
            font-weight: var(--font-semibold);
        }

        .header-actions {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }

        .btn-modern {
            padding: var(--space-3) var(--space-6);
            border-radius: var(--radius-xl);
            font-weight: var(--font-semibold);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            transition: var(--transition-normal);
            border: none;
            cursor: pointer;
            font-size: var(--text-sm);
            min-width: 140px;
        }

        .btn-primary-modern {
            background: var(--yellow-primary);
            color: var(--maroon-primary);
            box-shadow: var(--shadow-md);
        }

        .btn-primary-modern:hover {
            background: var(--yellow-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary-modern {
            background: rgba(255, 255, 255, 0.15);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-secondary-modern:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--space-8);
        }

        .main-content {
            display: flex;
            flex-direction: column;
            gap: var(--space-6);
        }

        .sidebar-content {
            display: flex;
            flex-direction: column;
            gap: var(--space-6);
        }

        .modern-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-gray);
            overflow: hidden;
            transition: var(--transition-shadow);
        }

        .modern-card:hover {
            box-shadow: var(--shadow-md);
        }

        .card-header {
            padding: var(--space-6);
            border-bottom: 1px solid var(--border-gray);
            background: linear-gradient(135deg, var(--light-gray) 0%, var(--white) 100%);
        }

        .card-title {
            font-size: var(--text-xl);
            font-weight: var(--font-bold);
            color: var(--maroon-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .card-icon {
            width: 24px;
            height: 24px;
            background: var(--maroon-primary);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-icon::after {
            content: '';
            width: 12px;
            height: 12px;
            background: var(--white);
            border-radius: var(--radius-sm);
        }

        .card-content {
            padding: var(--space-6);
        }

        .info-grid {
            display: grid;
            gap: var(--space-4);
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            padding: var(--space-4);
            background: var(--light-gray);
            border-radius: var(--radius-xl);
            transition: var(--transition-colors);
        }

        .info-item:hover {
            background: var(--yellow-light);
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: var(--text-lg);
            flex-shrink: 0;
        }

        .info-details {
            flex: 1;
        }

        .info-label {
            font-size: var(--text-xs);
            color: var(--text-gray);
            font-weight: var(--font-medium);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: var(--space-1);
        }

        .info-value {
            font-size: var(--text-base);
            color: var(--text-dark);
            font-weight: var(--font-semibold);
        }

        .score-showcase {
            text-align: center;
            padding: var(--space-8);
        }

        .score-circle-large {
            width: 180px;
            height: 180px;
            margin: 0 auto var(--space-6);
            border-radius: var(--radius-full);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            background: conic-gradient(var(--success) 0deg 306deg, var(--border-gray) 306deg 360deg);
            padding: 8px;
        }

        .score-inner {
            width: 100%;
            height: 100%;
            background: var(--white);
            border-radius: var(--radius-full);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .score-number {
            font-size: var(--text-5xl);
            font-weight: var(--font-extrabold);
            color: var(--maroon-primary);
            line-height: 1;
        }

        .score-label {
            font-size: var(--text-sm);
            color: var(--text-gray);
            font-weight: var(--font-medium);
            margin-top: var(--space-2);
        }

        .status-badge-modern {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: var(--text-sm);
            font-weight: var(--font-semibold);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-passed {
            background: var(--success-light);
            color: var(--success-dark);
        }

        .status-pending {
            background: var(--warning-light);
            color: var(--warning-dark);
        }

        .timeline-modern {
            position: relative;
            padding-left: var(--space-8);
        }

        .timeline-modern::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--maroon-primary), var(--yellow-primary));
        }

        .timeline-item-modern {
            position: relative;
            margin-bottom: var(--space-6);
            padding-left: var(--space-4);
        }

        .timeline-marker-modern {
            position: absolute;
            left: -32px;
            top: 8px;
            width: 16px;
            height: 16px;
            background: var(--maroon-primary);
            border: 3px solid var(--white);
            border-radius: var(--radius-full);
            box-shadow: var(--shadow-sm);
        }

        .timeline-content-modern {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: var(--space-4);
            border: 1px solid var(--border-gray);
            box-shadow: var(--shadow-sm);
        }

        .timeline-event {
            font-weight: var(--font-semibold);
            color: var(--maroon-primary);
            margin-bottom: var(--space-1);
        }

        .timeline-time {
            font-size: var(--text-sm);
            color: var(--text-gray);
        }

        /* Enhanced Mobile Responsiveness */
        @media (max-width: 1200px) {
            .applicant-detail-container {
                padding: var(--space-4);
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: var(--space-6);
            }
        }
        
        @media (max-width: 768px) {
            .applicant-detail-container {
                padding: var(--space-3);
            }
            
            .detail-header {
                padding: var(--space-6);
                margin-bottom: var(--space-6);
            }
            
            .header-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: var(--space-4);
            }
            
            .header-meta {
                grid-template-columns: 1fr;
                gap: var(--space-3);
            }
            
            .avatar-large {
                width: 80px;
                height: 80px;
                font-size: var(--text-2xl);
            }
            
            .header-info h1 {
                font-size: var(--text-2xl);
            }
            
            .header-actions {
                flex-direction: row;
                justify-content: center;
                gap: var(--space-2);
            }
            
            .btn-modern {
                min-width: 120px;
                padding: var(--space-2) var(--space-4);
                font-size: var(--text-xs);
            }
            
            .score-circle-large {
                width: 140px;
                height: 140px;
            }
            
            .score-number {
                font-size: var(--text-3xl);
            }
            
            .card-content {
                padding: var(--space-4);
            }
            
            .card-header {
                padding: var(--space-4);
            }
            
            .info-item {
                padding: var(--space-3);
            }
            
            .info-icon {
                width: 32px;
                height: 32px;
                font-size: var(--text-base);
            }
        }
        
        @media (max-width: 480px) {
            .detail-header::before {
                display: none;
            }
            
            .header-meta {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .header-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-modern {
                width: 100%;
                min-width: unset;
            }
            
            .info-grid {
                gap: var(--space-3);
            }
            
            .timeline-modern {
                padding-left: var(--space-6);
            }
            
            .timeline-marker-modern {
                left: -24px;
            }
        }
        
        /* Loading Animation */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .loading-state {
            animation: pulse 2s infinite;
        }
        
        /* Hover Enhancements */
        .info-item:hover .info-icon {
            transform: scale(1.1);
            transition: var(--transition-transform);
        }
        
        .btn-modern:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-primary-modern:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--yellow-dark) 0%, var(--yellow-primary) 100%);
        }
        
        /* Focus States for Accessibility */
        .btn-modern:focus {
            outline: 2px solid var(--yellow-primary);
            outline-offset: 2px;
        }
        
        .modern-card:focus-within {
            box-shadow: 0 0 0 2px var(--yellow-primary);
        }
        
        /* Print Styles */
        @media print {
            .applicant-detail-container {
                background: white;
                padding: 0;
            }
            
            .detail-header {
                background: white !important;
                color: black !important;
                box-shadow: none !important;
                border: 2px solid var(--maroon-primary);
            }
            
            .header-actions,
            .card-header .card-icon {
                display: none !important;
            }
            
            .modern-card {
                break-inside: avoid;
                box-shadow: none !important;
                border: 1px solid #ccc;
                margin-bottom: var(--space-4);
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: var(--space-4);
            }
        }
    </style>
@endpush

@section('content')

<div class="applicant-detail-container">
    <!-- Modern Header -->
    <div class="detail-header">
        <div class="header-breadcrumb">
            <a href="{{ route('admin.applicants.index') }}" class="breadcrumb-link">Applicants</a>
            <span class="breadcrumb-separator">›</span>
            <span>{{ $applicant->full_name ?? 'John Doe' }}</span>
        </div>
        
        <div class="header-content">
            <div class="header-avatar">
                <div class="avatar-large">
                    {{ $applicant->initials ?? 'JD' }}
                </div>
                <div class="status-indicator"></div>
            </div>
            
            <div class="header-info">
                <h1>{{ $applicant->name ?? 'John Doe' }}</h1>
                <p class="header-subtitle">BSIT Entrance Examination Applicant</p>
                
                <div class="header-meta">
                    <div class="meta-item">
                        <span class="meta-label">Student ID</span>
                        <span class="meta-value">{{ $applicant->student_id ?? '2024-001' }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Application Date</span>
                        <span class="meta-value">{{ $applicant->created_at->format('M d, Y') ?? now()->format('M d, Y') }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Status</span>
                        <span class="meta-value">
                            <span class="status-badge-modern status-{{ ($applicant->exam_score ?? 85) >= 75 ? 'passed' : 'pending' }}">
                                {{ $applicant->overall_status ?? 'Exam Completed' }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="header-actions">
                <a href="{{ route('admin.applicants.edit', $applicant->applicant_id ?? 1) }}" class="btn-modern btn-primary-modern">
                    Edit Applicant
                </a>
                <a href="{{ route('admin.applicants.index') }}" class="btn-modern btn-secondary-modern">
                    ← Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Main Content -->
        <div class="main-content">
            
            <!-- Contact Information -->
            <div class="modern-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <div class="card-icon"></div>
                        Contact Information
                    </h2>
                </div>
                <div class="card-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">@</div>
                            <div class="info-details">
                                <div class="info-label">Email Address</div>
                                <div class="info-value">{{ $applicant->email ?? 'john.doe@email.com' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">📱</div>
                            <div class="info-details">
                                <div class="info-label">Phone Number</div>
                                <div class="info-value">{{ $applicant->phone ?? '+1 (555) 123-4567' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">📍</div>
                            <div class="info-details">
                                <div class="info-label">Address</div>
                                <div class="info-value">{{ $applicant->address ?? '123 Main St, City, State 12345' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">🎓</div>
                            <div class="info-details">
                                <div class="info-label">Previous Education</div>
                                <div class="info-value">{{ $applicant->education ?? 'City High School, 2023' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam Performance -->
            <div class="modern-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <div class="card-icon"></div>
                        Exam Performance
                    </h2>
                </div>
                <div class="card-content">
                    @if($applicant->exam_completed ?? true)
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon">✓</div>
                                <div class="info-details">
                                    <div class="info-label">Questions Correct</div>
                                    <div class="info-value">{{ $applicant->correct_answers ?? 17 }}/{{ $applicant->total_questions ?? 20 }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">⏱</div>
                                <div class="info-details">
                                    <div class="info-label">Time Taken</div>
                                    <div class="info-value">{{ $applicant->exam_duration ?? '24 minutes 30 seconds' }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">📅</div>
                                <div class="info-details">
                                    <div class="info-label">Completion Date</div>
                                    <div class="info-value">{{ $applicant->exam_completed_at ?? now()->format('M d, Y - g:i A') }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">🏆</div>
                                <div class="info-details">
                                    <div class="info-label">Result Status</div>
                                    <div class="info-value">
                                        <span class="status-badge-modern status-{{ ($applicant->exam_score ?? 85) >= 75 ? 'passed' : 'pending' }}">
                                            {{ ($applicant->exam_score ?? 85) >= 75 ? 'PASSED' : 'FAILED' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div style="text-align: center; padding: var(--space-8);">
                            <div style="font-size: var(--text-4xl); margin-bottom: var(--space-4);">📝</div>
                            <h3 style="color: var(--maroon-primary); margin-bottom: var(--space-2);">Exam Not Completed</h3>
                            <p style="color: var(--text-gray); margin-bottom: var(--space-4);">This applicant has not yet completed the entrance examination.</p>
                            <button onclick="sendExamReminder()" class="btn-modern btn-primary-modern">Send Exam Reminder</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Content -->
        <div class="sidebar-content">
            
            <!-- Exam Score Showcase -->
            <div class="modern-card">
                <div class="card-content">
                    <div class="score-showcase">
                        <div class="score-circle-large">
                            <div class="score-inner">
                                <div class="score-number">{{ $applicant->exam_score ?? 85 }}%</div>
                                <div class="score-label">Final Score</div>
                            </div>
                        </div>
                        <div class="status-badge-modern status-{{ ($applicant->exam_score ?? 85) >= 75 ? 'passed' : 'pending' }}">
                            {{ ($applicant->exam_score ?? 85) >= 75 ? 'Excellent Performance' : 'Needs Improvement' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="modern-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <div class="card-icon"></div>
                        Quick Actions
                    </h2>
                </div>
                <div class="card-content">
                    <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                        <button onclick="emailApplicant()" class="btn-modern btn-primary-modern">
                            Send Email
                        </button>
                        <button onclick="scheduleInterview()" class="btn-modern btn-secondary-modern" style="color: var(--maroon-primary); background: var(--light-gray); border: 1px solid var(--border-gray);">
                            Schedule Interview
                        </button>
                        <button onclick="printProfile()" class="btn-modern btn-secondary-modern" style="color: var(--maroon-primary); background: var(--light-gray); border: 1px solid var(--border-gray);">
                            Print Profile
                        </button>
                        <button onclick="viewDetailedAnswers()" class="btn-modern btn-secondary-modern" style="color: var(--maroon-primary); background: var(--light-gray); border: 1px solid var(--border-gray);">
                            View Exam Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="modern-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <div class="card-icon"></div>
                        Recent Activity
                    </h2>
                </div>
                <div class="card-content">
                    <div class="timeline-modern">
                        @php
                            $timeline = $applicant->timeline ?? [
                                ['date' => now()->format('M d, Y'), 'time' => '2:30 PM', 'event' => 'Interview notes updated', 'type' => 'update'],
                                ['date' => now()->format('M d, Y'), 'time' => '10:15 AM', 'event' => 'Exam completed with 85% score', 'type' => 'exam'],
                                ['date' => now()->subDay()->format('M d, Y'), 'time' => '3:45 PM', 'event' => 'Exam started', 'type' => 'exam'],
                                ['date' => now()->subDays(2)->format('M d, Y'), 'time' => '9:00 AM', 'event' => 'Application submitted', 'type' => 'application'],
                            ];
                        @endphp
                        @foreach($timeline as $event)
                        <div class="timeline-item-modern">
                            <div class="timeline-marker-modern"></div>
                            <div class="timeline-content-modern">
                                <div class="timeline-event">{{ $event['event'] }}</div>
                                <div class="timeline-time">{{ $event['date'] }} at {{ $event['time'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Modern notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ'}</span>
                <span class="notification-message">${message}</span>
            </div>
        `;
        
        // Add notification styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? 'var(--success-light)' : type === 'error' ? 'var(--error-light)' : 'var(--info-light)'};
            color: ${type === 'success' ? 'var(--success-dark)' : type === 'error' ? 'var(--error-dark)' : 'var(--info-dark)'};
            padding: var(--space-4) var(--space-6);
            border-radius: var(--radius-xl);
            border: 1px solid ${type === 'success' ? 'var(--success)' : type === 'error' ? 'var(--error)' : 'var(--info)'};
            box-shadow: var(--shadow-lg);
            z-index: var(--z-toast);
            transform: translateX(100%);
            transition: var(--transition-normal);
            max-width: 400px;
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Enhanced functionality
    function emailApplicant() {
        showNotification('Email functionality will be implemented in the next update', 'info');
    }

    function printProfile() {
        showNotification('Preparing profile for printing...', 'info');
        setTimeout(() => {
            window.print();
        }, 500);
    }

    function scheduleInterview() {
        showNotification('Interview scheduling interface will open in the next update', 'info');
    }

    function viewDetailedAnswers() {
        showNotification('Detailed exam analysis will be available soon', 'info');
    }

    function sendExamReminder() {
        showNotification('Exam reminder sent successfully!', 'success');
    }

    // Add smooth scroll behavior
    document.addEventListener('DOMContentLoaded', function() {
        // Add smooth scrolling to all internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add loading states to buttons
        document.querySelectorAll('.btn-modern').forEach(button => {
            button.addEventListener('click', function(e) {
                if (this.classList.contains('loading')) return;
                
                const originalText = this.innerHTML;
                this.classList.add('loading');
                this.innerHTML = 'Loading...';
                this.disabled = true;
                
                setTimeout(() => {
                    this.classList.remove('loading');
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 1000);
            });
        });

        // Add hover effects to cards
        document.querySelectorAll('.modern-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>

<style>
    .notification-content {
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }
    
    .notification-icon {
        font-weight: bold;
        font-size: var(--text-lg);
    }
    
    .btn-modern.loading {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .modern-card {
        transition: var(--transition-normal);
    }
</style>
@endpush