<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Applicant Details - {{ config('app.name', 'EnrollAssess') }}</title>

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
                    <h1>Applicant Details</h1>
                    <p class="header-subtitle">Complete screening journey and assessment</p>
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
                <!-- Breadcrumb -->
                <div class="breadcrumb">
                    <a href="{{ route('admin.applicants') }}" class="breadcrumb-link">Applicants</a>
                    <span class="breadcrumb-separator">›</span>
                    <span class="breadcrumb-current">{{ $applicant->name ?? 'John Doe' }}</span>
                </div>

                <!-- Applicant Profile Header -->
                <div class="content-section profile-header">
                    <div class="section-content" style="padding: 30px;">
                        <div class="profile-layout">
                            <div class="profile-avatar">
                                <div class="avatar-circle">
                                    <span class="avatar-initials">{{ $applicant->initials ?? 'JD' }}</span>
                                </div>
                                <div class="profile-status">
                                    <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $applicant->overall_status ?? 'exam-completed')) }}">
                                        {{ $applicant->overall_status ?? 'Exam Completed' }}
                                    </span>
                                </div>
                            </div>
                            <div class="profile-info">
                                <h2 class="profile-name">{{ $applicant->name ?? 'John Doe' }}</h2>
                                <div class="profile-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">Student ID:</span>
                                        <span class="meta-value">{{ $applicant->student_id ?? '2024-001' }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Application Date:</span>
                                        <span class="meta-value">{{ $applicant->created_at->format('M d, Y') ?? now()->format('M d, Y') }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Last Updated:</span>
                                        <span class="meta-value">{{ $applicant->updated_at->format('M d, Y g:i A') ?? now()->format('M d, Y g:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-actions">
                                <button onclick="emailApplicant()" class="btn-primary">
                                    📧 Send Email
                                </button>
                                <button onclick="printProfile()" class="btn-secondary">
                                    🖨️ Print Profile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Contact Information</h2>
                        <button onclick="editContact()" class="section-action">
                            ✏️ Edit Contact
                        </button>
                    </div>
                    <div class="section-content" style="padding: 24px 30px;">
                        <div class="contact-grid">
                            <div class="contact-item">
                                <div class="contact-icon">📧</div>
                                <div class="contact-details">
                                    <div class="contact-label">Email Address</div>
                                    <div class="contact-value">{{ $applicant->email ?? 'john.doe@email.com' }}</div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">📞</div>
                                <div class="contact-details">
                                    <div class="contact-label">Phone Number</div>
                                    <div class="contact-value">{{ $applicant->phone ?? '+1 (555) 123-4567' }}</div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">🏠</div>
                                <div class="contact-details">
                                    <div class="contact-label">Address</div>
                                    <div class="contact-value">{{ $applicant->address ?? '123 Main St, City, State 12345' }}</div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">🎓</div>
                                <div class="contact-details">
                                    <div class="contact-label">Previous Education</div>
                                    <div class="contact-value">{{ $applicant->education ?? 'City High School, 2023' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exam Results Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Exam Results</h2>
                        <button onclick="viewDetailedAnswers()" class="section-action">
                            🔍 View Detailed Answers
                        </button>
                    </div>
                    <div class="section-content" style="padding: 30px;">
                        @if($applicant->exam_completed ?? true)
                        <div class="exam-results-layout">
                            <div class="results-summary">
                                <div class="score-circle {{ ($applicant->exam_score ?? 85) >= 75 ? 'score-passed' : 'score-failed' }}">
                                    <div class="score-number">{{ $applicant->exam_score ?? 85 }}%</div>
                                    <div class="score-label">Final Score</div>
                                </div>
                                <div class="exam-details">
                                    <div class="exam-meta">
                                        <div class="meta-row">
                                            <span class="meta-label">Questions Correct:</span>
                                            <span class="meta-value">{{ $applicant->correct_answers ?? 17 }}/{{ $applicant->total_questions ?? 20 }}</span>
                                        </div>
                                        <div class="meta-row">
                                            <span class="meta-label">Time Taken:</span>
                                            <span class="meta-value">{{ $applicant->exam_duration ?? '24 minutes 30 seconds' }}</span>
                                        </div>
                                        <div class="meta-row">
                                            <span class="meta-label">Completion Date:</span>
                                            <span class="meta-value">{{ $applicant->exam_completed_at ?? now()->format('M d, Y - g:i A') }}</span>
                                        </div>
                                        <div class="meta-row">
                                            <span class="meta-label">Result Status:</span>
                                            <span class="meta-value">
                                                <span class="result-badge {{ ($applicant->exam_score ?? 85) >= 75 ? 'result-passed' : 'result-failed' }}">
                                                    {{ ($applicant->exam_score ?? 85) >= 75 ? 'PASSED' : 'FAILED' }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Category Breakdown -->
                            <div class="category-breakdown">
                                <h4 class="breakdown-title">Performance by Category</h4>
                                <div class="category-list">
                                    @php
                                        $categories = $applicant->category_scores ?? [
                                            ['name' => 'Programming', 'score' => 83, 'correct' => 5, 'total' => 6],
                                            ['name' => 'Database', 'score' => 80, 'correct' => 4, 'total' => 5],
                                            ['name' => 'Networking', 'score' => 100, 'correct' => 4, 'total' => 4],
                                            ['name' => 'Data Structures', 'score' => 100, 'correct' => 3, 'total' => 3],
                                            ['name' => 'Software Engineering', 'score' => 50, 'correct' => 1, 'total' => 2],
                                        ];
                                    @endphp
                                    @foreach($categories as $category)
                                    <div class="category-row">
                                        <div class="category-name">{{ $category['name'] }}</div>
                                        <div class="category-progress">
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: {{ $category['score'] }}%"></div>
                                            </div>
                                            <div class="progress-text">{{ $category['correct'] }}/{{ $category['total'] }} ({{ $category['score'] }}%)</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="no-exam-results">
                            <div class="no-results-icon">📝</div>
                            <div class="no-results-text">
                                <h3>Exam Not Completed</h3>
                                <p>This applicant has not yet completed the entrance examination.</p>
                                <button onclick="sendExamReminder()" class="btn-primary">Send Exam Reminder</button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Interview Details Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Interview Details</h2>
                        <button onclick="scheduleInterview()" class="section-action">
                            📅 Schedule Interview
                        </button>
                    </div>
                    <div class="section-content" style="padding: 30px;">
                        <form id="interviewForm" class="interview-form">
                            @csrf
                            <input type="hidden" name="applicant_id" value="{{ $applicant->id ?? 1 }}">
                            
                            <!-- Interview Status -->
                            <div class="form-group">
                                <label for="interview_status" class="form-label">Interview Status</label>
                                <select id="interview_status" name="interview_status" class="form-select">
                                    <option value="not-scheduled" {{ ($applicant->interview_status ?? 'scheduled') == 'not-scheduled' ? 'selected' : '' }}>Not Scheduled</option>
                                    <option value="scheduled" {{ ($applicant->interview_status ?? 'scheduled') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="completed" {{ ($applicant->interview_status ?? 'scheduled') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ ($applicant->interview_status ?? 'scheduled') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <!-- Interview Date/Time -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="interview_date" class="form-label">Interview Date</label>
                                    <input type="date" id="interview_date" name="interview_date" class="form-control" value="{{ $applicant->interview_date ?? '2024-01-15' }}">
                                </div>
                                <div class="form-group">
                                    <label for="interview_time" class="form-label">Interview Time</label>
                                    <input type="time" id="interview_time" name="interview_time" class="form-control" value="{{ $applicant->interview_time ?? '14:00' }}">
                                </div>
                            </div>

                            <!-- Interviewer -->
                            <div class="form-group">
                                <label for="interviewer" class="form-label">Interviewer</label>
                                <select id="interviewer" name="interviewer" class="form-select">
                                    <option value="">Select Interviewer</option>
                                    <option value="dr-smith" {{ ($applicant->interviewer ?? 'dr-smith') == 'dr-smith' ? 'selected' : '' }}>Dr. Smith</option>
                                    <option value="prof-johnson" {{ ($applicant->interviewer ?? 'dr-smith') == 'prof-johnson' ? 'selected' : '' }}>Prof. Johnson</option>
                                    <option value="dr-williams" {{ ($applicant->interviewer ?? 'dr-smith') == 'dr-williams' ? 'selected' : '' }}>Dr. Williams</option>
                                </select>
                            </div>

                            <!-- Private Notes -->
                            <div class="form-group">
                                <label for="private_notes" class="form-label">Private Notes</label>
                                <textarea id="private_notes" name="private_notes" class="form-textarea" rows="4" placeholder="Add private notes about the applicant's interview...">{{ $applicant->private_notes ?? 'Applicant shows strong technical knowledge and communication skills. Recommended for further consideration.' }}</textarea>
                            </div>

                            <!-- Final Recommendation -->
                            <div class="form-group">
                                <label for="final_recommendation" class="form-label">Final Recommendation</label>
                                <select id="final_recommendation" name="final_recommendation" class="form-select recommendation-select">
                                    <option value="">Select Recommendation</option>
                                    <option value="recommended" {{ ($applicant->final_recommendation ?? 'recommended') == 'recommended' ? 'selected' : '' }}>Recommended for Admission</option>
                                    <option value="waitlisted" {{ ($applicant->final_recommendation ?? 'recommended') == 'waitlisted' ? 'selected' : '' }}>Waitlisted</option>
                                    <option value="not-recommended" {{ ($applicant->final_recommendation ?? 'recommended') == 'not-recommended' ? 'selected' : '' }}>Not Recommended</option>
                                </select>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="button" onclick="saveInterview()" class="btn-primary">
                                    💾 Save Changes
                                </button>
                                <button type="button" onclick="sendInterviewEmail()" class="btn-secondary">
                                    📧 Send Interview Email
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Activity Timeline</h2>
                    </div>
                    <div class="section-content" style="padding: 30px;">
                        <div class="timeline">
                            @php
                                $timeline = $applicant->timeline ?? [
                                    ['date' => now()->format('M d, Y'), 'time' => '2:30 PM', 'event' => 'Interview notes updated', 'type' => 'update'],
                                    ['date' => now()->format('M d, Y'), 'time' => '10:15 AM', 'event' => 'Exam completed with 85% score', 'type' => 'exam'],
                                    ['date' => now()->subDay()->format('M d, Y'), 'time' => '3:45 PM', 'event' => 'Exam started', 'type' => 'exam'],
                                    ['date' => now()->subDays(2)->format('M d, Y'), 'time' => '9:00 AM', 'event' => 'Application submitted', 'type' => 'application'],
                                ];
                            @endphp
                            @foreach($timeline as $event)
                            <div class="timeline-item">
                                <div class="timeline-marker timeline-{{ $event['type'] }}"></div>
                                <div class="timeline-content">
                                    <div class="timeline-event">{{ $event['event'] }}</div>
                                    <div class="timeline-time">{{ $event['date'] }} at {{ $event['time'] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function emailApplicant() {
            alert('Send email to applicant functionality (Demo mode)');
        }

        function printProfile() {
            window.print();
        }

        function editContact() {
            alert('Edit contact information functionality (Demo mode)');
        }

        function viewDetailedAnswers() {
            alert('View detailed exam answers functionality (Demo mode)');
        }

        function sendExamReminder() {
            alert('Send exam reminder email functionality (Demo mode)');
        }

        function scheduleInterview() {
            document.getElementById('interview_status').value = 'scheduled';
            document.getElementById('interview_date').focus();
        }

        function saveInterview() {
            const form = document.getElementById('interviewForm');
            const formData = new FormData(form);
            
            // Show loading state
            const saveBtn = event.target;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '💾 Saving...';
            
            // Simulate saving
            setTimeout(() => {
                alert('Interview details saved successfully! (Demo mode)');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '💾 Save Changes';
            }, 1000);
        }

        function sendInterviewEmail() {
            const status = document.getElementById('interview_status').value;
            const date = document.getElementById('interview_date').value;
            const time = document.getElementById('interview_time').value;
            
            if (status === 'scheduled' && date && time) {
                alert(`Interview email sent with details: ${date} at ${time} (Demo mode)`);
            } else {
                alert('Please complete interview scheduling details first.');
            }
        }

        // Auto-save functionality
        let saveTimeout;
        function autoSave() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                console.log('Auto-saving changes...');
            }, 3000);
        }

        // Add auto-save listeners
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('#interviewForm input, #interviewForm textarea, #interviewForm select');
            inputs.forEach(input => {
                input.addEventListener('change', autoSave);
            });

            // Update recommendation color
            const recommendationSelect = document.getElementById('final_recommendation');
            updateRecommendationColor();
            
            recommendationSelect.addEventListener('change', updateRecommendationColor);
        });

        function updateRecommendationColor() {
            const select = document.getElementById('final_recommendation');
            const value = select.value;
            
            select.className = 'form-select recommendation-select';
            if (value === 'recommended') {
                select.classList.add('recommendation-approved');
            } else if (value === 'waitlisted') {
                select.classList.add('recommendation-waitlisted');
            } else if (value === 'not-recommended') {
                select.classList.add('recommendation-rejected');
            }
        }
    </script>

    <style>
        /* Additional styles for applicant details page */
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--text-gray);
        }

        .breadcrumb-link {
            color: var(--maroon-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-link:hover {
            text-decoration: underline;
        }

        .breadcrumb-separator {
            margin: 0 8px;
            color: var(--text-gray);
        }

        .breadcrumb-current {
            color: var(--text-gray);
        }

        /* Profile Header */
        .profile-header {
            margin-bottom: 30px;
        }

        .profile-layout {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 30px;
            align-items: center;
        }

        .profile-avatar {
            text-align: center;
        }

        .avatar-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--maroon-primary) 0%, var(--maroon-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            border: 4px solid var(--yellow-primary);
        }

        .avatar-initials {
            font-size: 24px;
            font-weight: 700;
            color: var(--white);
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--maroon-primary);
            margin: 0 0 16px 0;
        }

        .profile-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .meta-label {
            font-size: 12px;
            color: var(--text-gray);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .meta-value {
            font-size: 14px;
            color: var(--maroon-primary);
            font-weight: 600;
        }

        .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Contact Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 12px;
            border: 1px solid var(--border-gray);
        }

        .contact-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .contact-details {
            flex: 1;
        }

        .contact-label {
            font-size: 12px;
            color: var(--text-gray);
            font-weight: 500;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .contact-value {
            font-size: 14px;
            color: var(--maroon-primary);
            font-weight: 500;
        }

        /* Exam Results */
        .exam-results-layout {
            display: grid;
            gap: 30px;
        }

        .results-summary {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 30px;
            align-items: center;
            padding: 24px;
            background: var(--yellow-light);
            border-radius: 12px;
            border: 2px solid var(--yellow-primary);
        }

        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 6px solid;
            position: relative;
        }

        .score-circle.score-passed {
            background: #dcfce7;
            border-color: #22c55e;
        }

        .score-circle.score-failed {
            background: #fecaca;
            border-color: #ef4444;
        }

        .score-number {
            font-size: 28px;
            font-weight: 700;
            color: var(--maroon-primary);
            line-height: 1;
        }

        .score-label {
            font-size: 12px;
            color: var(--text-gray);
            font-weight: 500;
            margin-top: 4px;
        }

        .exam-meta {
            display: grid;
            gap: 12px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-gray);
        }

        .meta-row:last-child {
            border-bottom: none;
        }

        .result-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .result-passed {
            background: #dcfce7;
            color: #166534;
        }

        .result-failed {
            background: #fecaca;
            color: #dc2626;
        }

        /* Category Breakdown */
        .breakdown-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--maroon-primary);
            margin: 0 0 16px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--yellow-primary);
        }

        .category-list {
            display: grid;
            gap: 16px;
        }

        .category-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 16px;
            align-items: center;
            padding: 12px;
            background: var(--white);
            border-radius: 8px;
            border: 1px solid var(--border-gray);
        }

        .category-name {
            font-weight: 500;
            color: var(--maroon-primary);
            font-size: 14px;
        }

        .category-progress {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .progress-bar {
            flex: 1;
            height: 8px;
            background: var(--border-gray);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--maroon-primary) 0%, var(--yellow-primary) 100%);
            border-radius: 4px;
            transition: width 0.6s ease;
        }

        .progress-text {
            font-size: 12px;
            color: var(--text-gray);
            font-weight: 500;
            min-width: 80px;
            text-align: right;
        }

        /* No Exam Results */
        .no-exam-results {
            text-align: center;
            padding: 40px;
        }

        .no-results-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .no-results-text h3 {
            color: var(--maroon-primary);
            margin: 0 0 8px 0;
        }

        .no-results-text p {
            color: var(--text-gray);
            margin: 0 0 20px 0;
        }

        /* Interview Form */
        .interview-form {
            display: grid;
            gap: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-label {
            font-weight: 600;
            color: var(--maroon-primary);
            font-size: 14px;
        }

        .form-control, .form-select, .form-textarea {
            padding: 12px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--yellow-primary);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.15);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
            line-height: 1.5;
        }

        .recommendation-select.recommendation-approved {
            border-color: #22c55e;
            background: #dcfce7;
        }

        .recommendation-select.recommendation-waitlisted {
            border-color: #f59e0b;
            background: #fef3c7;
        }

        .recommendation-select.recommendation-rejected {
            border-color: #ef4444;
            background: #fecaca;
        }

        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid var(--border-gray);
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-gray);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 24px;
        }

        .timeline-marker {
            position: absolute;
            left: -25px;
            top: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid var(--white);
        }

        .timeline-application { background: var(--maroon-primary); }
        .timeline-exam { background: var(--yellow-primary); }
        .timeline-update { background: #22c55e; }

        .timeline-content {
            background: var(--white);
            padding: 16px;
            border-radius: 8px;
            border: 1px solid var(--border-gray);
        }

        .timeline-event {
            font-weight: 500;
            color: var(--maroon-primary);
            margin-bottom: 4px;
        }

        .timeline-time {
            font-size: 12px;
            color: var(--text-gray);
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
            transform: translateY(-1px);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-layout {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 20px;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .results-summary {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .category-row {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>