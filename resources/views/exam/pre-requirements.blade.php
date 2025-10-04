<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Exam Requirements - EnrollAssess</title>
    <link href="{{ asset('css/auth/university-auth.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-maroon: #800020;
            --primary-gold: #FFD700;
            --dark-maroon: #5C0016;
            --light-gold: #FFF8DC;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --border-gray: #E9ECEF;
            --text-gray: #6B7280;
            --text-dark: #1F2937;
            --success-green: #059669;
            --warning-orange: #F59E0B;
            --danger-red: #DC2626;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .requirements-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 0;
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
            animation: slideIn 0.6s ease-out;
            overflow: hidden;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .requirements-header {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
            color: var(--white);
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .requirements-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: rgba(255, 215, 0, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .requirements-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 10px 0;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 1;
        }

        .requirements-header .subtitle {
            font-size: 18px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .progress-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin: 30px 0 0 0;
            position: relative;
            z-index: 1;
        }

        .progress-step {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transition: var(--transition);
        }

        .progress-step.active {
            background: var(--primary-gold);
            transform: scale(1.2);
        }

        .progress-step.completed {
            background: var(--success-green);
        }

        .requirements-content {
            padding: 0;
        }

        .requirement-section {
            border-bottom: 1px solid var(--border-gray);
            transition: var(--transition);
        }

        .requirement-section:last-child {
            border-bottom: none;
        }

        .section-header {
            padding: 30px 40px 20px 40px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--white);
            transition: var(--transition);
        }

        .section-header:hover {
            background: var(--light-gray);
        }

        .section-header.active {
            background: var(--light-gold);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 0;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            color: var(--white);
            flex-shrink: 0;
        }

        .section-icon.instructions {
            background: linear-gradient(135deg, #3B82F6 0%, #1E40AF 100%);
        }

        .section-icon.rules {
            background: linear-gradient(135deg, var(--warning-orange) 0%, #D97706 100%);
        }

        .section-icon.terms {
            background: linear-gradient(135deg, var(--danger-red) 0%, #B91C1C 100%);
        }

        .section-icon.privacy {
            background: linear-gradient(135deg, var(--success-green) 0%, #16A34A 100%);
        }

        .section-title h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--primary-maroon);
            margin: 0;
        }

        .section-status {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: var(--border-gray);
            color: var(--text-gray);
        }

        .status-reading {
            background: #FEF3C7;
            color: #D97706;
        }

        .status-completed {
            background: #DCFCE7;
            color: #16A34A;
        }

        .expand-icon {
            font-size: 20px;
            color: var(--text-gray);
            transition: var(--transition);
        }

        .section-header.active .expand-icon {
            transform: rotate(180deg);
        }

        .section-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .section-content.active {
            max-height: 1000px;
        }

        .content-inner {
            padding: 0 40px 30px 40px;
        }

        .requirement-text {
            color: var(--text-dark);
            line-height: 1.7;
            font-size: 15px;
        }

        .requirement-list {
            margin: 20px 0;
            padding-left: 0;
            list-style: none;
        }

        .requirement-list li {
            margin-bottom: 15px;
            padding-left: 30px;
            position: relative;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .requirement-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            top: 0;
            width: 20px;
            height: 20px;
            background: var(--success-green);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .warning-list li::before {
            content: '⚠';
            background: var(--warning-orange);
        }

        .danger-list li::before {
            content: '✗';
            background: var(--danger-red);
        }

        .violation-warning {
            background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
            border: 2px solid var(--danger-red);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }

        .violation-warning h4 {
            color: var(--danger-red);
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .violation-counter {
            background: var(--danger-red);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .checkbox-section {
            margin-top: 25px;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 12px;
            border: 2px solid transparent;
            transition: var(--transition);
        }

        .checkbox-section.checked {
            border-color: var(--success-green);
            background: rgba(5, 150, 105, 0.05);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .custom-checkbox {
            position: relative;
            width: 24px;
            height: 24px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .custom-checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            cursor: pointer;
        }

        .checkbox-design {
            width: 100%;
            height: 100%;
            border: 2px solid var(--border-gray);
            border-radius: 6px;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .custom-checkbox input[type="checkbox"]:checked + .checkbox-design {
            background: var(--success-green);
            border-color: var(--success-green);
        }

        .checkbox-design::after {
            content: '✓';
            color: var(--white);
            font-size: 14px;
            font-weight: bold;
            opacity: 0;
            transition: var(--transition);
        }

        .custom-checkbox input[type="checkbox"]:checked + .checkbox-design::after {
            opacity: 1;
        }

        .checkbox-label {
            color: var(--text-dark);
            font-size: 14px;
            line-height: 1.6;
            font-weight: 500;
            cursor: pointer;
        }

        .action-buttons {
            padding: 30px 40px;
            background: var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .btn {
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 160px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(128, 0, 32, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(128, 0, 32, 0.4);
        }

        .btn-primary:disabled {
            background: var(--border-gray);
            color: var(--text-gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            background: var(--white);
            color: var(--text-dark);
            border: 2px solid var(--border-gray);
        }

        .btn-secondary:hover {
            border-color: var(--primary-maroon);
            color: var(--primary-maroon);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .completion-status {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--text-gray);
        }

        .completion-count {
            font-weight: 600;
            color: var(--primary-maroon);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .requirements-container {
                border-radius: 16px;
            }

            .requirements-header {
                padding: 30px 20px;
            }

            .requirements-header h1 {
                font-size: 24px;
            }

            .section-header,
            .content-inner {
                padding-left: 20px;
                padding-right: 20px;
            }

            .action-buttons {
                flex-direction: column;
                padding: 20px;
            }

            .btn {
                width: 100%;
            }
        }

        /* Focus and accessibility */
        .section-header:focus {
            outline: 2px solid var(--primary-maroon);
            outline-offset: 2px;
        }

        .custom-checkbox input[type="checkbox"]:focus + .checkbox-design {
            outline: 2px solid var(--primary-maroon);
            outline-offset: 2px;
        }

        .btn:focus {
            outline: 2px solid var(--primary-maroon);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="requirements-container">
        <!-- Header -->
        <div class="requirements-header">
            <h1>Pre-Exam Requirements</h1>
            <p class="subtitle">Please read and acknowledge all requirements before proceeding to the examination</p>
            
            <div class="progress-indicator">
                <div class="progress-step completed" id="step1"></div>
                <div class="progress-step" id="step2"></div>
                <div class="progress-step" id="step3"></div>
                <div class="progress-step" id="step4"></div>
            </div>
        </div>

        <!-- Requirements Content -->
        <div class="requirements-content">
            
            <!-- 1. Exam Instructions -->
            <div class="requirement-section" id="instructions-section">
                <div class="section-header" onclick="toggleSection('instructions')">
                    <div class="section-title">
                        <div class="section-icon instructions">📋</div>
                        <div>
                            <h2>Exam Instructions</h2>
                        </div>
                    </div>
                    <div class="section-status">
                        <span class="status-badge status-pending" id="instructions-status">Pending</span>
                        <span class="expand-icon">▼</span>
                    </div>
                </div>
                <div class="section-content" id="instructions-content">
                    <div class="content-inner">
                        <div class="requirement-text">
                            <p><strong>Welcome to the BSIT Entrance Examination.</strong> Please carefully read the following instructions before starting your exam:</p>
                        </div>
                        
                        <ul class="requirement-list">
                            <li><strong>Duration:</strong> You have 90 minutes to complete 20 questions</li>
                            <li><strong>Question Types:</strong> All questions are multiple choice with 4 options (A, B, C, D)</li>
                            <li><strong>Navigation:</strong> You can only move forward. No returning to previous questions</li>
                            <li><strong>Auto-Save:</strong> Your answers are automatically saved as you progress</li>
                            <li><strong>Time Warning:</strong> You'll receive a warning when 5 minutes remain</li>
                            <li><strong>Auto-Submit:</strong> The exam will automatically submit when time expires</li>
                            <li><strong>Technical Issues:</strong> Contact the exam proctor immediately for any technical problems</li>
                        </ul>

                        <div class="violation-warning">
                            <h4>🚨 Violation Monitoring System</h4>
                            <p>During the exam, our system will monitor for the following violations:</p>
                            <ul class="warning-list">
                                <li>Switching browser tabs or windows</li>
                                <li>Minimizing the browser or switching to desktop</li>
                                <li>Opening other applications</li>
                                <li>Attempting to copy/paste content</li>
                                <li>Using keyboard shortcuts to access developer tools</li>
                            </ul>
                            <p><strong>Important:</strong> You are allowed <span class="violation-counter">0/5</span> violations. When you reach 5 violations, your exam will be automatically submitted.</p>
                        </div>

                        <div class="checkbox-section" id="instructions-checkbox">
                            <div class="checkbox-wrapper">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="instructionsAck" onchange="updateSectionStatus('instructions')">
                                    <div class="checkbox-design"></div>
                                </div>
                                <label for="instructionsAck" class="checkbox-label">
                                    I have read and understood all exam instructions, including the violation monitoring system.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Rules and Regulations -->
            <div class="requirement-section" id="rules-section">
                <div class="section-header" onclick="toggleSection('rules')">
                    <div class="section-title">
                        <div class="section-icon rules">⚖️</div>
                        <div>
                            <h2>Rules and Regulations</h2>
                        </div>
                    </div>
                    <div class="section-status">
                        <span class="status-badge status-pending" id="rules-status">Pending</span>
                        <span class="expand-icon">▼</span>
                    </div>
                </div>
                <div class="section-content" id="rules-content">
                    <div class="content-inner">
                        <div class="requirement-text">
                            <p><strong>Examination Rules and Regulations</strong> - Strict compliance is required:</p>
                        </div>
                        
                        <ul class="requirement-list">
                            <li><strong>Single Session:</strong> You may only take this exam once. No retakes allowed</li>
                            <li><strong>No External Help:</strong> You must work independently without assistance from others</li>
                            <li><strong>No Reference Materials:</strong> Books, notes, calculators, or other aids are prohibited</li>
                            <li><strong>Stable Internet:</strong> Ensure you have a reliable internet connection throughout</li>
                            <li><strong>Quiet Environment:</strong> Take the exam in a quiet, distraction-free location</li>
                            <li><strong>Full Screen Mode:</strong> Keep the exam in full screen mode at all times</li>
                            <li><strong>No Screenshots:</strong> Taking screenshots or recording the exam is strictly forbidden</li>
                        </ul>

                        <div class="violation-warning">
                            <h4>🚫 Prohibited Actions</h4>
                            <ul class="danger-list">
                                <li>Opening additional browser tabs or windows</li>
                                <li>Using search engines, social media, or messaging apps</li>
                                <li>Consulting with other people during the exam</li>
                                <li>Using mobile phones or other devices</li>
                                <li>Attempting to access the browser's developer tools</li>
                                <li>Copying questions or sharing exam content</li>
                            </ul>
                        </div>

                        <div class="checkbox-section" id="rules-checkbox">
                            <div class="checkbox-wrapper">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="rulesAck" onchange="updateSectionStatus('rules')">
                                    <div class="checkbox-design"></div>
                                </div>
                                <label for="rulesAck" class="checkbox-label">
                                    I agree to follow all rules and regulations during the examination. I understand that violations may result in disqualification.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Terms and Agreement -->
            <div class="requirement-section" id="terms-section">
                <div class="section-header" onclick="toggleSection('terms')">
                    <div class="section-title">
                        <div class="section-icon terms">📄</div>
                        <div>
                            <h2>Terms and Agreement</h2>
                        </div>
                    </div>
                    <div class="section-status">
                        <span class="status-badge status-pending" id="terms-status">Pending</span>
                        <span class="expand-icon">▼</span>
                    </div>
                </div>
                <div class="section-content" id="terms-content">
                    <div class="content-inner">
                        <div class="requirement-text">
                            <p><strong>Terms of Service and Examination Agreement</strong></p>
                        </div>
                        
                        <ul class="requirement-list">
                            <li><strong>Intellectual Property:</strong> All exam questions are proprietary to EVSU-Ormoc Campus</li>
                            <li><strong>Academic Integrity:</strong> You commit to maintaining the highest standards of academic honesty</li>
                            <li><strong>Result Validity:</strong> Exam results are valid for the current admission cycle only</li>
                            <li><strong>Technical Monitoring:</strong> Your exam session will be monitored for security purposes</li>
                            <li><strong>Data Collection:</strong> Performance data will be collected for evaluation and improvement</li>
                            <li><strong>Appeal Process:</strong> Technical issues must be reported immediately during the exam</li>
                            <li><strong>Final Authority:</strong> The Computer Studies Department has final authority on all exam matters</li>
                        </ul>

                        <div class="requirement-text">
                            <p><strong>By proceeding with this examination, you acknowledge that:</strong></p>
                        </div>

                        <ul class="requirement-list">
                            <li>You are the person registered for this exam</li>
                            <li>You will not share exam content with others</li>
                            <li>You understand the consequences of academic dishonesty</li>
                            <li>You accept the monitoring and security measures in place</li>
                            <li>You agree to abide by all university policies and procedures</li>
                        </ul>

                        <div class="checkbox-section" id="terms-checkbox">
                            <div class="checkbox-wrapper">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="termsAck" onchange="updateSectionStatus('terms')">
                                    <div class="checkbox-design"></div>
                                </div>
                                <label for="termsAck" class="checkbox-label">
                                    I accept all terms and conditions outlined above. I understand my rights and responsibilities as an exam candidate.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Data Privacy Consent -->
            <div class="requirement-section" id="privacy-section">
                <div class="section-header" onclick="toggleSection('privacy')">
                    <div class="section-title">
                        <div class="section-icon privacy">🔒</div>
                        <div>
                            <h2>Data Privacy Consent</h2>
                        </div>
                    </div>
                    <div class="section-status">
                        <span class="status-badge status-pending" id="privacy-status">Pending</span>
                        <span class="expand-icon">▼</span>
                    </div>
                </div>
                <div class="section-content" id="privacy-content">
                    <div class="content-inner">
                        <div class="requirement-text">
                            <p><strong>Data Privacy Act of 2012 (Republic Act No. 10173) Compliance Notice</strong></p>
                            <p>We inform you that your personal information will be collected and processed for the BSIT screening and enrollment process.</p>
                        </div>
                        
                        <div class="requirement-text">
                            <h4>📝 Data We Collect:</h4>
                        </div>
                        <ul class="requirement-list">
                            <li>Personal information (name, contact details, student ID)</li>
                            <li>Examination scores, responses, and performance metrics</li>
                            <li>Interview assessments and evaluation notes</li>
                            <li>Academic records and previous qualifications</li>
                            <li>Technical data (IP address, browser info, session logs)</li>
                        </ul>

                        <div class="requirement-text">
                            <h4>🎯 Purpose of Data Processing:</h4>
                        </div>
                        <ul class="requirement-list">
                            <li>Evaluation of your application for the BSIT program</li>
                            <li>Ranking and selection of qualified applicants</li>
                            <li>Communication regarding your application status</li>
                            <li>Compliance with university policies and regulations</li>
                            <li>Statistical analysis for program improvement</li>
                            <li>Security monitoring and fraud prevention</li>
                        </ul>

                        <div class="requirement-text">
                            <p><strong>Data Security:</strong> Your information will be stored securely using industry-standard encryption and accessed only by authorized personnel.</p>
                            <p><strong>Data Retention:</strong> We will retain your data only for as long as necessary for the stated purposes, typically for one academic year.</p>
                            <p><strong>Your Rights:</strong> You have the right to access, correct, or request deletion of your personal data in accordance with the Data Privacy Act of 2012.</p>
                        </div>

                        <div class="checkbox-section" id="privacy-checkbox">
                            <div class="checkbox-wrapper">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="privacyAck" onchange="updateSectionStatus('privacy')">
                                    <div class="checkbox-design"></div>
                                </div>
                                <label for="privacyAck" class="checkbox-label">
                                    I have read and understood the data collection notice above. I hereby give my <strong>free and informed consent</strong> for the Eastern Visayas State University - Ormoc Campus, Computer Studies Department to collect, process, and store my personal information for the purposes stated in this notice.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <div class="completion-status">
                <span class="completion-count" id="completionCount">0</span> of 4 requirements completed
            </div>
            <div style="display: flex; gap: 15px;">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                    ← Back
                </a>
                <button id="proceedBtn" class="btn btn-primary" disabled onclick="proceedToExam()">
                    Start Examination →
                </button>
            </div>
        </div>
    </div>

    <script>
        // State management
        let completedSections = {
            instructions: false,
            rules: false,
            terms: false,
            privacy: false
        };

        let currentActiveSection = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-open first section
            toggleSection('instructions');
            updateProgressIndicator();
            updateCompletionStatus();
        });

        // Toggle section visibility
        function toggleSection(sectionName) {
            const header = document.querySelector(`#${sectionName}-section .section-header`);
            const content = document.getElementById(`${sectionName}-content`);
            
            // Close currently active section
            if (currentActiveSection && currentActiveSection !== sectionName) {
                const currentHeader = document.querySelector(`#${currentActiveSection}-section .section-header`);
                const currentContent = document.getElementById(`${currentActiveSection}-content`);
                
                currentHeader.classList.remove('active');
                currentContent.classList.remove('active');
            }
            
            // Toggle current section
            const isActive = header.classList.contains('active');
            
            if (isActive) {
                header.classList.remove('active');
                content.classList.remove('active');
                currentActiveSection = null;
            } else {
                header.classList.add('active');
                content.classList.add('active');
                currentActiveSection = sectionName;
                
                // Update status to reading if not completed
                if (!completedSections[sectionName]) {
                    const statusBadge = document.getElementById(`${sectionName}-status`);
                    statusBadge.textContent = 'Reading';
                    statusBadge.className = 'status-badge status-reading';
                }
            }
        }

        // Update section status when checkbox is changed
        function updateSectionStatus(sectionName) {
            const checkbox = document.getElementById(`${sectionName}Ack`);
            const statusBadge = document.getElementById(`${sectionName}-status`);
            const checkboxSection = document.getElementById(`${sectionName}-checkbox`);
            
            if (checkbox.checked) {
                statusBadge.textContent = 'Completed';
                statusBadge.className = 'status-badge status-completed';
                checkboxSection.classList.add('checked');
                completedSections[sectionName] = true;
                
                // Auto-open next section
                const sections = ['instructions', 'rules', 'terms', 'privacy'];
                const currentIndex = sections.indexOf(sectionName);
                if (currentIndex < sections.length - 1) {
                    const nextSection = sections[currentIndex + 1];
                    if (!completedSections[nextSection]) {
                        setTimeout(() => {
                            toggleSection(nextSection);
                        }, 500);
                    }
                }
            } else {
                statusBadge.textContent = 'Reading';
                statusBadge.className = 'status-badge status-reading';
                checkboxSection.classList.remove('checked');
                completedSections[sectionName] = false;
            }
            
            updateProgressIndicator();
            updateCompletionStatus();
            updateProceedButton();
        }

        // Update progress indicator
        function updateProgressIndicator() {
            const sections = ['instructions', 'rules', 'terms', 'privacy'];
            sections.forEach((section, index) => {
                const step = document.getElementById(`step${index + 1}`);
                if (completedSections[section]) {
                    step.className = 'progress-step completed';
                } else if (currentActiveSection === section) {
                    step.className = 'progress-step active';
                } else {
                    step.className = 'progress-step';
                }
            });
        }

        // Update completion status
        function updateCompletionStatus() {
            const completedCount = Object.values(completedSections).filter(Boolean).length;
            document.getElementById('completionCount').textContent = completedCount;
        }

        // Update proceed button state
        function updateProceedButton() {
            const allCompleted = Object.values(completedSections).every(Boolean);
            const proceedBtn = document.getElementById('proceedBtn');
            
            proceedBtn.disabled = !allCompleted;
            
            if (allCompleted) {
                proceedBtn.style.background = 'linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%)';
            } else {
                proceedBtn.style.background = 'var(--border-gray)';
            }
        }

        // Proceed to exam
        function proceedToExam() {
            const allCompleted = Object.values(completedSections).every(Boolean);
            
            if (!allCompleted) {
                alert('Please complete all requirements before proceeding to the examination.');
                return;
            }

            // Store consent and acknowledgments in localStorage
            localStorage.setItem('examPreRequirements', JSON.stringify({
                instructionsAcknowledged: true,
                rulesAgreed: true,
                termsAccepted: true,
                dataPrivacyConsented: true,
                timestamp: new Date().toISOString(),
                violationCount: 0,
                maxViolations: 5
            }));

            // Show loading state
            const proceedBtn = document.getElementById('proceedBtn');
            proceedBtn.disabled = true;
            proceedBtn.innerHTML = 'Preparing Exam... ⏳';
            
            // Initialize exam session first, then redirect
            initializeExamSession();
        }

        // Initialize exam session
        function initializeExamSession() {
            // Start the exam session via POST request
            fetch('{{ route('exam.start') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    // Applicant ID will be retrieved from session in the controller
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to exam interface
                    window.location.href = data.redirect_url || "{{ route('exam.interface') }}";
                } else {
                    // Handle error
                    const proceedBtn = document.getElementById('proceedBtn');
                    proceedBtn.disabled = false;
                    proceedBtn.innerHTML = 'Start Examination →';
                    
                    alert('Failed to start exam: ' + (data.message || 'Unknown error occurred'));
                }
            })
            .catch(error => {
                console.error('Error starting exam:', error);
                
                const proceedBtn = document.getElementById('proceedBtn');
                proceedBtn.disabled = false;
                proceedBtn.innerHTML = 'Start Examination →';
                
                alert('Failed to start exam. Please try again.');
            });
        }

        // Keyboard accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const allCompleted = Object.values(completedSections).every(Boolean);
                if (allCompleted && document.getElementById('proceedBtn').focus) {
                    proceedToExam();
                }
            }
        });

        // Prevent back button after starting requirements
        window.addEventListener('beforeunload', function(e) {
            const completedCount = Object.values(completedSections).filter(Boolean).length;
            if (completedCount > 0) {
                e.preventDefault();
                e.returnValue = 'Are you sure you want to leave? Your progress may be lost.';
                return e.returnValue;
            }
        });
    </script>
</body>
</html>
