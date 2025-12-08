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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
        }

        .container {
            background: white;
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: #800020;
            color: white;
            padding: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 12px;
            opacity: 0.9;
        }

        .content {
            padding: 32px;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }

        /* Custom scrollbar styling */
        .content::-webkit-scrollbar {
            width: 8px;
        }

        .content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .content::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        .content::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .progress-indicator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 10px;
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
            top: 12px;
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
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 11px;
            margin-bottom: 6px;
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
            font-size: 10px;
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
            font-size: 16px;
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
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-label-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .form-label-hint {
            font-size: 12px;
            color: #b91c1c;
            font-weight: 600;
            background: #fee2e2;
            padding: 2px 10px;
            border-radius: 999px;
            border: 1px solid #fecaca;
            text-transform: uppercase;
        }


        .form-control {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
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
            min-height: 60px;
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


        @media (max-width: 768px) {
            .form-label-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }

            .form-label-hint {
                font-size: 11px;
                width: 100%;
                text-align: left;
            }
            body {
                padding: 10px;
            }

            .container {
                max-height: 95vh;
            }

            .header {
                padding: 24px;
            }

            .header h1 {
                font-size: 18px;
            }

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
                font-size: 9px;
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

            <form id="basicInfoForm" method="POST" action="{{ route('exam.basic-info.store') }}">
                @csrf

                <!-- Personal Information -->
                <div class="form-section">
                    <div class="section-title">Personal Information</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="sex" class="form-label">Sex <span style="color: #dc2626;">*</span></label>
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
                            <label for="civil_status" class="form-label">Civil Status <span style="color: #dc2626;">*</span></label>
                            <select id="civil_status" name="civil_status" class="form-control" required>
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
                            <label for="date_of_birth" class="form-label">Date of Birth <span style="color: #dc2626;">*</span></label>
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
                            <label for="age" class="form-label">Age <span style="color: #dc2626;">*</span></label>
                            <input 
                                type="number" 
                                id="age" 
                                name="age" 
                                class="form-control @error('age') error @enderror"
                                value="{{ old('age') }}"
                                min="16"
                                max="99"
                                required>
                            <span class="help-text">Auto-filled from date of birth</span>
                            @error('age')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="applicant_type" class="form-label">Applicant Type <span style="color: #dc2626;">*</span></label>
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
                            <label for="is_pwd" class="form-label">Person with Disability (PWD) <span style="color: #dc2626;">*</span></label>
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

                    <div class="form-row">
                        <div class="form-group">
                            <label for="province" class="form-label">Province <span style="color: #dc2626;">*</span></label>
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

                        <div class="form-group" id="city_municipality_group">
                            <label for="city_municipality" class="form-label">City/Municipality <span style="color: #dc2626;">*</span></label>
                            <select id="city_municipality" name="city_municipality" class="form-control @error('city_municipality') error @enderror" required>
                                <option value="">Select City/Municipality</option>
                            </select>
                            <span class="help-text" id="city_help_text">Select province first</span>
                            @error('city_municipality')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row single">
                        <div class="form-group">
                            <label for="complete_address" class="form-label">Complete Address <span style="color: #dc2626;">*</span></label>
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
                </div>

                <!-- Educational Background -->
                <div class="form-section" id="educationalBackgroundSection">
                    <div class="section-title">Educational Background</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="senior_high_school_strand" class="form-label">Senior High School Strand <span style="color: #dc2626;">*</span></label>
                            <select id="senior_high_school_strand" name="senior_high_school_strand" class="form-control @error('senior_high_school_strand') error @enderror">
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
                            <label for="senior_high_school_strand_other" class="form-label">Please Specify Strand <span style="color: #dc2626;">*</span></label>
                            <input 
                                type="text" 
                                id="senior_high_school_strand_other" 
                                name="senior_high_school_strand_other" 
                                class="form-control @error('senior_high_school_strand_other') error @enderror"
                                value="{{ old('senior_high_school_strand_other') }}"
                                placeholder="Enter your strand"
                                required>
                            @error('senior_high_school_strand_other')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row single">
                        <div class="form-group">
                            <div class="form-label-container">
                                <label for="senior_high_school_name" class="form-label">Senior High School Name <span style="color: #dc2626;">*</span></label>
                                <span class="form-label-hint">Do not use abbreviations</span>
                            </div>
                            <input 
                                type="text" 
                                id="senior_high_school_name" 
                                name="senior_high_school_name" 
                                class="form-control @error('senior_high_school_name') error @enderror"
                                value="{{ old('senior_high_school_name') }}"
                                placeholder="E.G ORMOC CITY SENIOR HIGH SCHOOL"
                                minlength="5">
                            @error('senior_high_school_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="form-section">
                    <div class="section-title">Additional Information</div>

                    <div class="form-row single">
                        <div class="form-group">
                            <label for="facebook_link" class="form-label">Facebook Link <span style="color: #dc2626;">*</span></label>
                            <input 
                                type="url" 
                                id="facebook_link" 
                                name="facebook_link" 
                                class="form-control @error('facebook_link') error @enderror"
                                value="{{ old('facebook_link', 'https://www.facebook.com/') }}"
                                placeholder="https://www.facebook.com/yourprofile"
                                required>
                            <span class="help-text">Enter your complete Facebook profile URL</span>
                            @error('facebook_link')
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
                    <button type="submit" class="btn btn-primary" id="submitBtn">
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

            const ageInput = document.getElementById('age');
            if (age >= 16 && age <= 99) {
                ageInput.value = age;
            }

            validateForm();
        });

        // Helper function to convert select to text input
        function convertToTextInput(currentValue = '') {
            const cityGroup = document.getElementById('city_municipality_group');
            const cityField = document.getElementById('city_municipality');
            const helpText = document.getElementById('city_help_text');
            
            // If already an input, just update value and return
            if (cityField && cityField.tagName === 'INPUT') {
                cityField.value = currentValue || cityField.value;
                helpText.textContent = 'Please enter your city/municipality';
                return;
            }
            
            // Get current value if not provided
            if (!currentValue && cityField) {
                currentValue = cityField.value || '';
            }
            
            // Get classes from existing field
            const existingClasses = cityField ? cityField.className : 'form-control @error("city_municipality") error @enderror';
            
            // Create text input
            const textInput = document.createElement('input');
            textInput.type = 'text';
            textInput.id = 'city_municipality';
            textInput.name = 'city_municipality';
            textInput.className = existingClasses;
            textInput.setAttribute('required', 'required');
            textInput.value = currentValue;
            textInput.placeholder = 'Enter City/Municipality';
            
            // Add event listeners for validation
            textInput.addEventListener('input', validateForm);
            textInput.addEventListener('change', validateForm);
            
            // Replace select with input
            if (cityField) {
                cityField.replaceWith(textInput);
            }
            helpText.textContent = 'Please enter your city/municipality';
        }

        // Helper function to convert text input to select
        function convertToSelect() {
            const cityGroup = document.getElementById('city_municipality_group');
            const cityField = document.getElementById('city_municipality');
            const helpText = document.getElementById('city_help_text');
            
            if (cityField && cityField.tagName === 'INPUT') {
                const currentValue = cityField.value;
                
                // Get classes from existing field
                const existingClasses = cityField.className;
                
                // Create select
                const select = document.createElement('select');
                select.id = 'city_municipality';
                select.name = 'city_municipality';
                select.className = existingClasses;
                select.setAttribute('required', 'required');
                select.innerHTML = '<option value="">Select City/Municipality</option>';
                
                // Add event listeners for validation
                select.addEventListener('change', validateForm);
                
                // Replace input with select
                cityField.replaceWith(select);
                helpText.textContent = 'Select City/Municipality';
                
                return select;
            }
            
            return document.getElementById('city_municipality');
        }

        // Province change - update cities
        document.getElementById('province').addEventListener('change', function() {
            const province = this.value;
            const cityGroup = document.getElementById('city_municipality_group');
            const helpText = document.getElementById('city_help_text');
            
            if (!province) {
                // No province selected
                const cityField = document.getElementById('city_municipality');
                if (cityField.tagName === 'SELECT') {
                    cityField.innerHTML = '<option value="">Select City/Municipality</option>';
                } else {
                    convertToSelect();
                    document.getElementById('city_municipality').innerHTML = '<option value="">Select City/Municipality</option>';
                }
                helpText.textContent = 'Select province first';
                validateForm();
                return;
            }

            // Check if cities exist in initial data
            if (citiesByProvince[province] && citiesByProvince[province].length > 0) {
                // Convert to select if it's currently an input
                let citySelect = document.getElementById('city_municipality');
                if (citySelect.tagName === 'INPUT') {
                    citySelect = convertToSelect();
                }
                
                // Clear and populate cities
                citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                citiesByProvince[province].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
                helpText.textContent = 'Select City/Municipality';
            } else {
                // Fetch cities via AJAX
                helpText.textContent = 'Loading cities...';
                
                fetch(`/api/cities-by-province/${encodeURIComponent(province)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.hasCities && data.cities.length > 0) {
                            // Convert to select if it's currently an input
                            let citySelect = document.getElementById('city_municipality');
                            if (citySelect.tagName === 'INPUT') {
                                citySelect = convertToSelect();
                            }
                            
                            // Populate cities
                            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                            data.cities.forEach(city => {
                                const option = document.createElement('option');
                                option.value = city;
                                option.textContent = city;
                                citySelect.appendChild(option);
                            });
                            helpText.textContent = 'Select City/Municipality';
                        } else {
                            // No cities available, convert to text input
                            convertToTextInput();
                        }
                        validateForm();
                    })
                    .catch(error => {
                        console.error('Error fetching cities:', error);
                        // On error, convert to text input as fallback
                        convertToTextInput();
                        helpText.textContent = 'Please enter your city/municipality';
                        validateForm();
                    });
            }

            validateForm();
        });

        // Applicant Type selection - show/hide Educational Background section
        document.getElementById('applicant_type').addEventListener('change', function() {
            const educationalSection = document.getElementById('educationalBackgroundSection');
            const strandField = document.getElementById('senior_high_school_strand');
            const strandOtherField = document.getElementById('strandOtherField');
            const strandOtherInput = document.getElementById('senior_high_school_strand_other');
            const schoolNameField = document.getElementById('senior_high_school_name');
            
            if (this.value === 'ALS passer') {
                // Hide Educational Background section for ALS passers
                educationalSection.style.display = 'none';
                
                // Remove required attributes
                strandField.removeAttribute('required');
                strandOtherInput.removeAttribute('required');
                schoolNameField.removeAttribute('required');
                schoolNameField.removeAttribute('minlength');
                
                // Clear values
                strandField.value = '';
                strandOtherInput.value = '';
                schoolNameField.value = '';
                
                // Hide strand "Others" field if visible
                strandOtherField.classList.remove('show');
            } else {
                // Show Educational Background section for other applicant types
                educationalSection.style.display = 'block';
                
                // Add required attributes back
                strandField.setAttribute('required', 'required');
                schoolNameField.setAttribute('required', 'required');
                schoolNameField.setAttribute('minlength', '5');
                
                // Strand "Others" field requirement handled by its own listener
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

        // Function to highlight empty fields in red
        function highlightEmptyFields() {
            const applicantType = document.getElementById('applicant_type').value;
            const requiredFields = [
                'sex',
                'civil_status',
                'date_of_birth',
                'age',
                'applicant_type',
                'is_pwd',
                'complete_address',
                'province',
                'city_municipality',
                'facebook_link'
            ];

            // Only include educational background fields if not ALS passer
            if (applicantType !== 'ALS passer') {
                requiredFields.push('senior_high_school_strand', 'senior_high_school_name');
            }

            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    const value = field.value ? field.value.trim() : '';
                    if (!value) {
                        field.classList.add('error');
                    } else {
                        // Special validation for school name - must be at least 5 characters
                        if (fieldId === 'senior_high_school_name' && value.length < 5) {
                            field.classList.add('error');
                        } else {
                            field.classList.remove('error');
                        }
                    }
                }
            });

            // Check strand "Others" field if applicable (only if not ALS passer)
            if (applicantType !== 'ALS passer') {
                const strand = document.getElementById('senior_high_school_strand').value;
                const strandOtherField = document.getElementById('senior_high_school_strand_other');
                if (strand === 'Others') {
                    const strandOtherValue = strandOtherField ? strandOtherField.value.trim() : '';
                    if (!strandOtherValue) {
                        strandOtherField.classList.add('error');
                    } else {
                        strandOtherField.classList.remove('error');
                    }
                } else if (strandOtherField) {
                    strandOtherField.classList.remove('error');
                }
            }
        }

        // Form validation (without highlighting - just for checking)
        function validateForm() {
            const sex = document.getElementById('sex').value;
            const civilStatus = document.getElementById('civil_status').value;
            const dob = document.getElementById('date_of_birth').value;
            const age = document.getElementById('age').value;
            const applicantType = document.getElementById('applicant_type').value;
            const isPwd = document.getElementById('is_pwd').value;
            const address = document.getElementById('complete_address').value.trim();
            const province = document.getElementById('province').value;
            const cityField = document.getElementById('city_municipality');
            const city = cityField ? cityField.value.trim() : '';
            const strand = document.getElementById('senior_high_school_strand').value;
            const schoolName = document.getElementById('senior_high_school_name').value.trim();
            const facebookLink = document.getElementById('facebook_link').value.trim();
            
            // Basic URL validation
            let isValidUrl = true;
            if (facebookLink) {
                try {
                    new URL(facebookLink);
                } catch (e) {
                    isValidUrl = false;
                }
            }
            
            // For ALS passers, skip educational background validation
            let isValid = sex && civilStatus && dob && age && applicantType && isPwd && address && province && city && facebookLink && isValidUrl;
            
            // Only validate educational background if not ALS passer
            if (applicantType !== 'ALS passer') {
                // School name must be at least 5 characters (no abbreviations)
                const isValidSchoolName = schoolName && schoolName.length >= 5;
                isValid = isValid && strand && isValidSchoolName;
                
                // Check strand "Others" field if applicable
                if (strand === 'Others') {
                    const strandOther = document.getElementById('senior_high_school_strand_other').value.trim();
                    isValid = isValid && strandOther;
                }
            }

            return isValid;
        }

        // Form submit handler - validate and highlight empty fields ONLY on submit
        document.getElementById('basicInfoForm').addEventListener('submit', function(e) {
            // First, remove all existing error classes
            document.querySelectorAll('.form-control.error').forEach(field => {
                field.classList.remove('error');
            });

            // Check if form is valid
            const isValid = validateForm();

            if (!isValid) {
                e.preventDefault();
                // Only highlight empty fields when submit is clicked
                highlightEmptyFields();
                
                // Scroll to first error field
                const firstError = document.querySelector('.form-control.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                return false;
            }
        });

        // Add event listeners for real-time validation
        const formInputs = document.querySelectorAll('#basicInfoForm input, #basicInfoForm select, #basicInfoForm textarea');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                // Remove error class when user starts typing
                this.classList.remove('error');
                validateForm();
            });
            input.addEventListener('change', function() {
                // Remove error class when user changes value
                this.classList.remove('error');
                validateForm();
            });
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // If old province exists, populate cities
            if (oldProvince) {
                const provinceSelect = document.getElementById('province');
                provinceSelect.value = oldProvince;
                provinceSelect.dispatchEvent(new Event('change'));
                
                // Set old city after cities are populated (with longer timeout for AJAX)
                setTimeout(() => {
                    if (oldCity) {
                        const cityField = document.getElementById('city_municipality');
                        if (cityField) {
                            cityField.value = oldCity;
                            validateForm();
                        }
                    }
                }, 500);
            }

            // Check if applicant type is ALS passer on load
            const applicantTypeSelect = document.getElementById('applicant_type');
            if (applicantTypeSelect.value === 'ALS passer') {
                const educationalSection = document.getElementById('educationalBackgroundSection');
                educationalSection.style.display = 'none';
                
                // Remove required attributes
                document.getElementById('senior_high_school_strand').removeAttribute('required');
                document.getElementById('senior_high_school_strand_other').removeAttribute('required');
                document.getElementById('senior_high_school_name').removeAttribute('required');
                document.getElementById('senior_high_school_name').removeAttribute('minlength');
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

