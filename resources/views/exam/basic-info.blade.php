<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Basic Information - EnrollAssess</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            max-width: 800px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: #800020;
            color: white;
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .progress-indicator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 0 20px;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .progress-step::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: -1;
        }

        .progress-step:last-child::after {
            display: none;
        }

        .progress-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .progress-step.completed .progress-number {
            background: #10b981;
            color: white;
        }

        .progress-step.active .progress-number {
            background: #800020;
            color: white;
        }

        .progress-label {
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }

        .progress-step.active .progress-label {
            color: #1f2937;
            font-weight: 600;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .form-section:last-of-type {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row.single {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-label.required::after {
            content: ' *';
            color: #dc2626;
        }

        .form-control {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #800020;
        }

        .form-control.error {
            border-color: #dc2626;
        }

        .form-control:disabled {
            background: #f3f4f6;
            cursor: not-allowed;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .error-message {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
        }

        .help-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .conditional-field {
            display: none;
        }

        .conditional-field.show {
            display: block;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .button-container {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-primary {
            background: #800020;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #660019;
        }

        .btn-primary:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .required-note {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 24px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 6px;
        }

        @media (max-width: 768px) {
            .content {
                padding: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .progress-indicator {
                padding: 0;
            }

            .progress-label {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Basic Information</h1>
            <p>Please complete your information before taking the exam</p>
        </div>

        <div class="content">
            <!-- Progress Indicator -->
            <div class="progress-indicator">
                <div class="progress-step completed">
                    <div class="progress-number">1</div>
                    <div class="progress-label">Access Code</div>
                </div>
                <div class="progress-step completed">
                    <div class="progress-number">2</div>
                    <div class="progress-label">Instructions</div>
                </div>
                <div class="progress-step active">
                    <div class="progress-number">3</div>
                    <div class="progress-label">Basic Info</div>
                </div>
                <div class="progress-step">
                    <div class="progress-number">4</div>
                    <div class="progress-label">Exam</div>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    Please correct the errors below and try again.
                </div>
            @endif

            <div class="required-note">
                Fields marked with <span style="color: #dc2626;">*</span> are required.
            </div>

            <form id="basicInfoForm" method="POST" action="{{ route('exam.basic-info.store') }}">
                @csrf

                <!-- Personal Information -->
                <div class="form-section">
                    <div class="section-title">Personal Information</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="sex" class="form-label required">Sex</label>
                            <select id="sex" name="sex" class="form-control @error('sex') error @enderror" required>
                                <option value="">Select Sex</option>
                                @foreach($sexOptions as $option)
                                    <option value="{{ $option }}" {{ old('sex') == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sex')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="civil_status" class="form-label">Civil Status</label>
                            <select id="civil_status" name="civil_status" class="form-control">
                                <option value="">Select Civil Status</option>
                                @foreach($civilStatusOptions as $option)
                                    <option value="{{ $option }}" {{ old('civil_status') == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                            @error('civil_status')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_of_birth" class="form-label required">Date of Birth</label>
                            <input 
                                type="date" 
                                id="date_of_birth" 
                                name="date_of_birth" 
                                class="form-control @error('date_of_birth') error @enderror"
                                value="{{ old('date_of_birth') }}"
                                max="{{ date('Y-m-d') }}"
                                required>
                            @error('date_of_birth')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="age" class="form-label required">Age</label>
                            <select id="age" name="age" class="form-control @error('age') error @enderror" required>
                                <option value="">Select Age</option>
                                @foreach($ageOptions as $ageOption)
                                    <option value="{{ $ageOption }}" {{ old('age') == $ageOption ? 'selected' : '' }}>
                                        {{ $ageOption }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="help-text">Auto-filled from date of birth</span>
                            @error('age')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="applicant_type" class="form-label required">Applicant Type</label>
                            <select id="applicant_type" name="applicant_type" class="form-control @error('applicant_type') error @enderror" required>
                                <option value="">Select Applicant Type</option>
                                @foreach($applicantTypeOptions as $option)
                                    <option value="{{ $option }}" {{ old('applicant_type') == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                            @error('applicant_type')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="is_pwd" class="form-label required">Person with Disability (PWD)</label>
                            <select id="is_pwd" name="is_pwd" class="form-control @error('is_pwd') error @enderror" required>
                                <option value="">Select Option</option>
                                @foreach($pwdStatusOptions as $option)
                                    <option value="{{ $option }}" {{ old('is_pwd') == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                            @error('is_pwd')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="form-section">
                    <div class="section-title">Address Information</div>

                    <div class="form-row single">
                        <div class="form-group">
                            <label for="complete_address" class="form-label required">Complete Address</label>
                            <textarea 
                                id="complete_address" 
                                name="complete_address" 
                                class="form-control @error('complete_address') error @enderror"
                                placeholder="Street, Barangay, etc."
                                required>{{ old('complete_address') }}</textarea>
                            @error('complete_address')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="province" class="form-label required">Province</label>
                            <select id="province" name="province" class="form-control @error('province') error @enderror" required>
                                <option value="">Select Province</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province }}" {{ old('province') == $province ? 'selected' : '' }}>
                                        {{ $province }}
                                    </option>
                                @endforeach
                            </select>
                            @error('province')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="city_municipality" class="form-label required">City/Municipality</label>
                            <select id="city_municipality" name="city_municipality" class="form-control @error('city_municipality') error @enderror" required>
                                <option value="">Select City/Municipality</option>
                            </select>
                            <span class="help-text">Select province first</span>
                            @error('city_municipality')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Educational Background -->
                <div class="form-section">
                    <div class="section-title">Educational Background</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="senior_high_school_strand" class="form-label required">Senior High School Strand</label>
                            <select id="senior_high_school_strand" name="senior_high_school_strand" class="form-control @error('senior_high_school_strand') error @enderror" required>
                                <option value="">Select Strand</option>
                                @foreach($strandOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('senior_high_school_strand') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('senior_high_school_strand')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group conditional-field" id="strandOtherField">
                            <label for="senior_high_school_strand_other" class="form-label required">Please Specify Strand</label>
                            <input 
                                type="text" 
                                id="senior_high_school_strand_other" 
                                name="senior_high_school_strand_other" 
                                class="form-control @error('senior_high_school_strand_other') error @enderror"
                                value="{{ old('senior_high_school_strand_other') }}"
                                placeholder="Enter your strand">
                            @error('senior_high_school_strand_other')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row single">
                        <div class="form-group">
                            <label for="senior_high_school_name" class="form-label required">Senior High School Name</label>
                            <input 
                                type="text" 
                                id="senior_high_school_name" 
                                name="senior_high_school_name" 
                                class="form-control @error('senior_high_school_name') error @enderror"
                                value="{{ old('senior_high_school_name') }}"
                                placeholder="Enter school name"
                                required>
                            @error('senior_high_school_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="button-container">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Back
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        Proceed to Exam
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Cities data from PHP
        const citiesByProvince = @json($citiesByProvince);
        const oldCity = "{{ old('city_municipality') }}";
        const oldProvince = "{{ old('province') }}";

        // Auto-calculate age from date of birth
        document.getElementById('date_of_birth').addEventListener('change', function() {
            const dob = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }

            const ageSelect = document.getElementById('age');
            if (age >= 16 && age <= 99) {
                ageSelect.value = age;
            }

            validateForm();
        });

        // Province change - update cities
        document.getElementById('province').addEventListener('change', function() {
            const province = this.value;
            const citySelect = document.getElementById('city_municipality');
            
            // Clear current options
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            
            if (province && citiesByProvince[province]) {
                // Add cities for selected province
                citiesByProvince[province].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            } else if (province) {
                // If province not in list, enable free input (using input instead of select)
                // For now, just show a message in help text
            }

            validateForm();
        });

        // Strand selection - show/hide "Others" field
        document.getElementById('senior_high_school_strand').addEventListener('change', function() {
            const strandOtherField = document.getElementById('strandOtherField');
            const strandOtherInput = document.getElementById('senior_high_school_strand_other');
            
            if (this.value === 'Others') {
                strandOtherField.classList.add('show');
                strandOtherInput.required = true;
            } else {
                strandOtherField.classList.remove('show');
                strandOtherInput.required = false;
                strandOtherInput.value = '';
            }

            validateForm();
        });

        // Form validation
        function validateForm() {
            const sex = document.getElementById('sex').value;
            const dob = document.getElementById('date_of_birth').value;
            const age = document.getElementById('age').value;
            const applicantType = document.getElementById('applicant_type').value;
            const isPwd = document.getElementById('is_pwd').value;
            const address = document.getElementById('complete_address').value.trim();
            const province = document.getElementById('province').value;
            const city = document.getElementById('city_municipality').value;
            const strand = document.getElementById('senior_high_school_strand').value;
            const schoolName = document.getElementById('senior_high_school_name').value.trim();
            
            let isValid = sex && dob && age && applicantType && isPwd && address && province && city && strand && schoolName;

            // Check strand "Others" field if applicable
            if (strand === 'Others') {
                const strandOther = document.getElementById('senior_high_school_strand_other').value.trim();
                isValid = isValid && strandOther;
            }

            document.getElementById('submitBtn').disabled = !isValid;
        }

        // Add event listeners for real-time validation
        const formInputs = document.querySelectorAll('#basicInfoForm input, #basicInfoForm select, #basicInfoForm textarea');
        formInputs.forEach(input => {
            input.addEventListener('input', validateForm);
            input.addEventListener('change', validateForm);
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // If old province exists, populate cities
            if (oldProvince) {
                const provinceSelect = document.getElementById('province');
                provinceSelect.value = oldProvince;
                provinceSelect.dispatchEvent(new Event('change'));
                
                // Set old city after cities are populated
                setTimeout(() => {
                    if (oldCity) {
                        document.getElementById('city_municipality').value = oldCity;
                    }
                }, 100);
            }

            // Check if strand is "Others" on load
            const strandSelect = document.getElementById('senior_high_school_strand');
            if (strandSelect.value === 'Others') {
                document.getElementById('strandOtherField').classList.add('show');
                document.getElementById('senior_high_school_strand_other').required = true;
            }

            // Initial validation
            validateForm();
        });
    </script>
</body>
</html>

