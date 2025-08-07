<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Privacy Consent - EnrollAssess</title>
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
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--primary-maroon) 0%, var(--dark-maroon) 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .consent-container {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            animation: slideIn 0.6s ease-out;
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

        .consent-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .consent-header h1 {
            color: var(--primary-maroon);
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
            letter-spacing: -0.5px;
        }

        .consent-header .subtitle {
            color: var(--text-gray);
            font-size: 16px;
            font-weight: 400;
        }

        .consent-content {
            margin-bottom: 30px;
        }

        .privacy-notice {
            background: var(--light-gray);
            border-left: 4px solid var(--primary-gold);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .privacy-notice h3 {
            color: var(--primary-maroon);
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 15px 0;
        }

        .privacy-notice p {
            color: var(--text-dark);
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 12px 0;
        }

        .privacy-notice p:last-child {
            margin-bottom: 0;
        }

        .data-purposes {
            margin: 20px 0;
        }

        .data-purposes h4 {
            color: var(--primary-maroon);
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 10px 0;
        }

        .data-purposes ul {
            margin: 0;
            padding-left: 20px;
        }

        .data-purposes li {
            color: var(--text-dark);
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .consent-checkbox {
            background: var(--light-gray);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 2px solid transparent;
            transition: var(--transition);
        }

        .consent-checkbox.checked {
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
            width: 20px;
            height: 20px;
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
            border-radius: 4px;
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
            font-size: 12px;
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
            line-height: 1.5;
            font-weight: 500;
            cursor: pointer;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 140px;
            justify-content: center;
        }

        .btn-primary {
            background: var(--primary-maroon);
            color: var(--white);
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--dark-maroon);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(128, 0, 32, 0.3);
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

        .legal-notice {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border-gray);
            text-align: center;
        }

        .legal-notice p {
            color: var(--text-gray);
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
        }

        .legal-notice a {
            color: var(--primary-maroon);
            text-decoration: none;
        }

        .legal-notice a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            body {
                padding: 15px;
            }

            .consent-container {
                padding: 25px;
            }

            .consent-header h1 {
                font-size: 24px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        /* Focus and accessibility */
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
    <div class="consent-container">
        <div class="consent-header">
            <h1>Data Privacy Consent</h1>
            <p class="subtitle">BSIT Entrance Examination - Data Collection Notice</p>
        </div>

        <div class="consent-content">
            <div class="privacy-notice">
                <h3>📋 Data Collection and Processing Notice</h3>
                <p><strong>In compliance with the Data Privacy Act of 2012 (Republic Act No. 10173)</strong>, we inform you that your personal information will be collected and processed for the purpose of the BSIT screening and enrollment process.</p>
                
                <div class="data-purposes">
                    <h4>📝 Data We Collect:</h4>
                    <ul>
                        <li>Personal information (name, contact details)</li>
                        <li>Examination scores and responses</li>
                        <li>Interview assessments and notes</li>
                        <li>Academic records and qualifications</li>
                    </ul>
                </div>

                <div class="data-purposes">
                    <h4>🎯 Purpose of Data Processing:</h4>
                    <ul>
                        <li>Evaluation of your application for the BSIT program</li>
                        <li>Ranking and selection of qualified applicants</li>
                        <li>Communication regarding your application status</li>
                        <li>Compliance with university policies and regulations</li>
                        <li>Statistical analysis for program improvement</li>
                    </ul>
                </div>

                <p><strong>Data Security:</strong> Your information will be stored securely and accessed only by authorized personnel. We will retain your data only for as long as necessary for the stated purposes.</p>
                
                <p><strong>Your Rights:</strong> You have the right to access, correct, or request deletion of your personal data in accordance with the Data Privacy Act of 2012.</p>
            </div>
        </div>

        <div class="consent-checkbox" id="consentSection">
            <div class="checkbox-wrapper">
                <div class="custom-checkbox">
                    <input type="checkbox" id="consentCheckbox" required>
                    <div class="checkbox-design"></div>
                </div>
                <label for="consentCheckbox" class="checkbox-label">
                    I have read and understood the data collection notice above. I hereby give my <strong>free and informed consent</strong> for the Eastern Visayas State University - Ormoc Campus, Computer Studies Department to collect, process, and store my personal information for the purposes stated in this notice.
                </label>
            </div>
        </div>

        <div class="action-buttons">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                ← Back
            </a>
            <button id="continueBtn" class="btn btn-primary" disabled onclick="proceedToExam()">
                Continue to Exam →
            </button>
        </div>

        <div class="legal-notice">
            <p>
                For questions about data privacy, contact the University Data Protection Officer at 
                <a href="mailto:dpo@evsu.edu.ph">dpo@evsu.edu.ph</a>
            </p>
            <p>© 2024 Eastern Visayas State University - Ormoc Campus</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const consentCheckbox = document.getElementById('consentCheckbox');
            const continueBtn = document.getElementById('continueBtn');
            const consentSection = document.getElementById('consentSection');

            // Handle checkbox state changes
            consentCheckbox.addEventListener('change', function() {
                continueBtn.disabled = !this.checked;
                
                if (this.checked) {
                    consentSection.classList.add('checked');
                    continueBtn.style.background = 'var(--primary-maroon)';
                } else {
                    consentSection.classList.remove('checked');
                    continueBtn.style.background = 'var(--border-gray)';
                }
            });

            // Smooth animation for checkbox interaction
            consentCheckbox.addEventListener('click', function() {
                consentSection.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    consentSection.style.transform = 'scale(1)';
                }, 100);
            });
        });

        function proceedToExam() {
            const consentCheckbox = document.getElementById('consentCheckbox');
            
            if (!consentCheckbox.checked) {
                alert('Please read and accept the data privacy consent to continue.');
                return;
            }

            // Store consent in session/localStorage for tracking
            localStorage.setItem('dataPrivacyConsent', JSON.stringify({
                consented: true,
                timestamp: new Date().toISOString(),
                ip: 'system-tracked' // In real implementation, this would be server-side
            }));

            // Redirect to exam interface
            window.location.href = "{{ route('exam.interface') }}";
        }

        // Prevent back button after consent (optional security measure)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        // Keyboard accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && document.getElementById('consentCheckbox').checked) {
                proceedToExam();
            }
        });
    </script>
</body>
</html>