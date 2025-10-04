@extends('layouts.admin')

@section('title', isset($applicant) ? 'Edit Applicant' : 'Add New Applicant')

@php
    $pageTitle = isset($applicant) ? 'Edit Applicant' : 'Add New Applicant';
    $pageSubtitle = 'Manage applicant information and exam assignment';
@endphp

@push('styles')
    <link href="{{ asset('css/admin/applicants.css') }}" rel="stylesheet">
@endpush

@section('content')

<!-- Header Section -->
<div class="page-header">
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">{{ $pageTitle }}</h1>
            <p class="page-subtitle">{{ $pageSubtitle }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.applicants.index') }}" class="btn-secondary">
                ← Back to Applicants
            </a>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="content-section">
    <div class="form-container">
        <form id="applicantForm" method="POST" action="{{ isset($applicant) ? route('admin.applicants.update', $applicant->applicant_id) : route('admin.applicants.store') }}">
            @csrf
            @if(isset($applicant))
                @method('PUT')
            @endif

            <!-- Personal Information Section -->
            <div class="form-section">
                <h3 class="section-title">Personal Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               class="form-control" 
                               value="{{ old('first_name', $applicant->first_name ?? '') }}" 
                               required>
                        @error('first_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" 
                               id="middle_name" 
                               name="middle_name" 
                               class="form-control" 
                               value="{{ old('middle_name', $applicant->middle_name ?? '') }}">
                        @error('middle_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               class="form-control" 
                               value="{{ old('last_name', $applicant->last_name ?? '') }}" 
                               required>
                        @error('last_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="preferred_course" class="form-label">Preferred Course</label>
                        <input type="text" 
                               id="preferred_course" 
                               name="preferred_course" 
                               class="form-control" 
                               value="{{ old('preferred_course', $applicant->preferred_course ?? '') }}"
                               placeholder="e.g., Bachelor of Science in Information Technology">
                        @error('preferred_course')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="email_address" class="form-label">Email Address *</label>
                        <input type="email" 
                               id="email_address" 
                               name="email_address" 
                               class="form-control" 
                               value="{{ old('email_address', $applicant->email_address ?? '') }}" 
                               required>
                        @error('email_address')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone_number" class="form-label">Contact Number</label>
                        <input type="tel" 
                               id="phone_number" 
                               name="phone_number" 
                               class="form-control" 
                               value="{{ old('phone_number', $applicant->phone_number ?? '') }}"
                               placeholder="e.g., 09123456789">
                        @error('phone_number')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="application_no" class="form-label">Application Number</label>
                        <input type="text" 
                               id="application_no" 
                               name="application_no" 
                               class="form-control" 
                               value="{{ old('application_no', $applicant->application_no ?? '') }}"
                               placeholder="Auto-generated if left empty"
                               readonly>
                        @error('application_no')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            </div>

            <!-- Exam Assignment Section -->
            <div class="form-section">
                <h3 class="section-title">Exam Assignment</h3>
                
                <div class="form-group">
                    <label for="exam_set_id" class="form-label">Exam Set *</label>
                    <select id="exam_set_id" name="exam_set_id" class="form-control" required>
                        <option value="">Select Exam Set</option>
                        @foreach($examSets as $examSet)
                            <option value="{{ $examSet->exam_set_id }}" 
                                    {{ old('exam_set_id', $applicant->exam_set_id ?? '') == $examSet->exam_set_id ? 'selected' : '' }}>
                                {{ $examSet->exam->title }} - {{ $examSet->set_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('exam_set_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="pending" {{ old('status', $applicant->status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="exam-assigned" {{ old('status', $applicant->status ?? '') == 'exam-assigned' ? 'selected' : '' }}>Exam Assigned</option>
                            <option value="exam-completed" {{ old('status', $applicant->status ?? '') == 'exam-completed' ? 'selected' : '' }}>Exam Completed</option>
                            <option value="interview-scheduled" {{ old('status', $applicant->status ?? '') == 'interview-scheduled' ? 'selected' : '' }}>Interview Scheduled</option>
                            <option value="interview-completed" {{ old('status', $applicant->status ?? '') == 'interview-completed' ? 'selected' : '' }}>Interview Completed</option>
                            <option value="admitted" {{ old('status', $applicant->status ?? '') == 'admitted' ? 'selected' : '' }}>Admitted</option>
                            <option value="rejected" {{ old('status', $applicant->status ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="verbal_description" class="form-label">Verbal Description</label>
                        <select id="verbal_description" name="verbal_description" class="form-control">
                            <option value="">Auto-compute from exam score</option>
                            <option value="Excellent" {{ old('verbal_description', $applicant->verbal_description ?? '') == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                            <option value="Very Good" {{ old('verbal_description', $applicant->verbal_description ?? '') == 'Very Good' ? 'selected' : '' }}>Very Good</option>
                            <option value="Good" {{ old('verbal_description', $applicant->verbal_description ?? '') == 'Good' ? 'selected' : '' }}>Good</option>
                            <option value="Satisfactory" {{ old('verbal_description', $applicant->verbal_description ?? '') == 'Satisfactory' ? 'selected' : '' }}>Satisfactory</option>
                            <option value="Fair" {{ old('verbal_description', $applicant->verbal_description ?? '') == 'Fair' ? 'selected' : '' }}>Fair</option>
                            <option value="Needs Improvement" {{ old('verbal_description', $applicant->verbal_description ?? '') == 'Needs Improvement' ? 'selected' : '' }}>Needs Improvement</option>
                        </select>
                        @error('verbal_description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" onclick="previewApplicant()" class="btn-secondary">
                    Preview Information
                </button>
                <button type="submit" class="btn-primary" id="saveButton">
                    {{ isset($applicant) ? 'Update Applicant' : 'Add Applicant' }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="modal-overlay" style="display: none;">
    <div class="modal-content preview-modal">
        <div class="modal-header">
            <h3>Applicant Information Preview</h3>
            <button onclick="closePreviewModal()" class="modal-close">×</button>
        </div>
        <div class="modal-body">
            <div class="preview-applicant">
                <div class="preview-section">
                    <h4>Personal Information</h4>
                    <div id="previewPersonal"></div>
                </div>
                <div class="preview-section">
                    <h4>Exam Assignment</h4>
                    <div id="previewExam"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closePreviewModal()" class="btn-secondary">Close Preview</button>
            <button onclick="submitForm()" class="btn-primary">Looks Good - {{ isset($applicant) ? 'Update' : 'Add' }} Applicant</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Form validation and preview
    function previewApplicant() {
        const form = document.getElementById('applicantForm');
        const formData = new FormData(form);
        
        // Personal Information Preview
        const personalInfo = `
            <div class="preview-item">
                <strong>Name:</strong> ${formData.get('first_name') || ''} ${formData.get('middle_name') || ''} ${formData.get('last_name') || ''}
            </div>
            <div class="preview-item">
                <strong>Preferred Course:</strong> ${formData.get('preferred_course') || 'Not specified'}
            </div>
            <div class="preview-item">
                <strong>Email:</strong> ${formData.get('email_address') || 'Not provided'}
            </div>
            <div class="preview-item">
                <strong>Phone:</strong> ${formData.get('phone_number') || 'Not provided'}
            </div>
            <div class="preview-item">
                <strong>Application No:</strong> ${formData.get('application_no') || 'Auto-generated'}
            </div>
        `;
        
        // Exam Assignment Preview
        const examSetSelect = document.getElementById('exam_set_id');
        const selectedOption = examSetSelect.options[examSetSelect.selectedIndex];
        const examInfo = `
            <div class="preview-item">
                <strong>Exam Set:</strong> ${selectedOption.text || 'Not selected'}
            </div>
            <div class="preview-item">
                <strong>Status:</strong> ${formData.get('status') || 'Pending'}
            </div>
        `;
        
        document.getElementById('previewPersonal').innerHTML = personalInfo;
        document.getElementById('previewExam').innerHTML = examInfo;
        
        // Show modal
        document.getElementById('previewModal').style.display = 'flex';
    }
    
    function closePreviewModal() {
        document.getElementById('previewModal').style.display = 'none';
    }
    
    function submitForm() {
        document.getElementById('applicantForm').submit();
    }
    
    // Form validation
    document.getElementById('applicantForm').addEventListener('submit', function(e) {
        const requiredFields = ['first_name', 'last_name', 'email_address', 'exam_set_id'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            } else {
                input.classList.remove('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('previewModal');
        if (e.target === modal) {
            closePreviewModal();
        }
    });
</script>
@endpush