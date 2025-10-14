<!-- Bulk Assign Exam Drawer -->
<div id="assignExamDrawerOverlay" class="assign-drawer-overlay" onclick="closeAssignExamDrawer()"></div>
<div id="assignExamDrawer" class="assign-drawer">
    <div class="assign-drawer-header">
        <h3 class="assign-drawer-title">Assign Exam to Applicants</h3>
        <button type="button" class="assign-drawer-close" onclick="closeAssignExamDrawer()">×</button>
    </div>
    
    <div class="assign-drawer-body">
        <!-- Selected Applicants Info -->
        <div style="background: #f3f4f6; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <div style="font-weight: 600; margin-bottom: 4px;">Selected Applicants</div>
            <div style="font-size: 14px; color: #6b7280;">
                <span id="bulk_selected_count">0</span> applicant(s) will be assigned
            </div>
            <div id="bulk_warning" style="display: none; font-size: 12px; color: #f59e0b; margin-top: 6px;">
                <span id="bulk_no_code_count">0</span> applicant(s) will be skipped (no access code)
            </div>
        </div>

        <!-- Select Exam -->
        <div style="margin-bottom: 20px;">
            <label for="bulk_exam_id" style="font-weight: 600; margin-bottom: 8px; display: block;">
                Select Exam <span style="color: #ef4444;">*</span>
            </label>
            <select id="bulk_exam_id" name="exam_id" class="form-control" required style="width: 100%;">
                <option value="">Choose an exam...</option>
                @forelse($exams ?? [] as $exam)
                    <option value="{{ $exam->exam_id }}" 
                            data-duration="{{ $exam->duration_minutes }}"
                            data-total="{{ $exam->total_items }}">
                        {{ $exam->title }}
                    </option>
                @empty
                    <option value="" disabled>No active exams available</option>
                @endforelse
            </select>
            <small style="color: #6b7280; font-size: 12px;">Choose the exam to assign to selected applicants</small>
        </div>

        <!-- Exam Details -->
        <div id="examDetails" style="display: none; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; margin-bottom: 20px;">
            <h4 style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Exam Details</h4>
            <div style="font-size: 12px; color: #6b7280;">
                <div style="margin-bottom: 4px;">
                    <strong>Duration:</strong> <span id="examDuration"></span> minutes
                </div>
                <div>
                    <strong>Questions:</strong> <span id="examQuestions"></span> items
                </div>
            </div>
        </div>

        <!-- Important Notice -->
        @if(!empty($exams))
        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; margin-bottom: 20px;">
            <div style="font-weight: 600; color: #1e40af; margin-bottom: 4px;">What This Does:</div>
            <ul style="margin: 8px 0; padding-left: 20px; font-size: 14px; color: #1e40af;">
                <li>Links the selected exam to each applicant's access code</li>
                <li>Applicants will only be able to take this specific exam</li>
                <li>You can change the assigned exam later if needed</li>
                <li>Only applicants with access codes will be assigned</li>
            </ul>
        </div>
        @endif
    </div>
    
    <div class="assign-drawer-footer">
        <button type="button" class="btn btn-secondary" onclick="closeAssignExamDrawer()">Cancel</button>
        <button type="button" class="btn btn-primary" id="bulkAssignBtn" onclick="submitBulkExamAssignment()" {{ empty($exams) ? 'disabled' : '' }}>
            Assign Exam
        </button>
    </div>
</div>

<!-- Single Assign Exam Drawer -->
<div id="singleAssignExamDrawerOverlay" class="assign-drawer-overlay" onclick="closeSingleAssignExamDrawer()"></div>
<div id="singleAssignExamDrawer" class="assign-drawer">
    <div class="assign-drawer-header">
        <h3 class="assign-drawer-title">Assign Exam</h3>
        <button type="button" class="assign-drawer-close" onclick="closeSingleAssignExamDrawer()">×</button>
    </div>
    
    <div class="assign-drawer-body">
        <input type="hidden" id="single_applicant_id" name="applicant_id">
        
        <!-- Applicant Info -->
        <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; margin-bottom: 20px;">
            <div style="font-size: 13px; color: #374151;">
                <strong>Applicant:</strong> <span id="single_applicant_name"></span>
            </div>
        </div>

        <!-- Select Exam -->
        <div style="margin-bottom: 20px;">
            <label for="single_exam_id" style="font-weight: 600; margin-bottom: 8px; display: block;">
                Select Exam <span style="color: #ef4444;">*</span>
            </label>
            <select id="single_exam_id" name="exam_id" class="form-control" required style="width: 100%;">
                <option value="">Choose an exam...</option>
                @forelse($exams ?? [] as $exam)
                    <option value="{{ $exam->exam_id }}" 
                            data-duration="{{ $exam->duration_minutes }}"
                            data-total="{{ $exam->total_items }}">
                        {{ $exam->title }}
                    </option>
                @empty
                    <option value="" disabled>No active exams available</option>
                @endforelse
            </select>
            <small style="color: #6b7280; font-size: 12px;">Choose the exam to assign to this applicant</small>
        </div>

        <!-- Exam Details -->
        <div id="singleExamDetails" style="display: none; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; margin-bottom: 20px;">
            <h4 style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Exam Details</h4>
            <div style="font-size: 12px; color: #6b7280;">
                <div style="margin-bottom: 4px;">
                    <strong>Duration:</strong> <span id="singleExamDuration"></span> minutes
                </div>
                <div>
                    <strong>Questions:</strong> <span id="singleExamQuestions"></span> items
                </div>
            </div>
        </div>

        <!-- No Exams Notice -->
        @if(empty($exams))
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin-bottom: 20px;">
            <div style="font-weight: 600; color: #92400e; margin-bottom: 4px;">⚠ No Active Exams</div>
            <div style="font-size: 13px; color: #78350f;">
                There are currently no active exams available for assignment. 
                <a href="{{ route('admin.exams.index') }}" style="color: #92400e; text-decoration: underline;">Create or activate an exam first</a>.
            </div>
        </div>
        @endif

        <!-- Important Notice -->
        @if(!empty($exams))
        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px;">
            <div style="font-weight: 600; color: #1e40af; margin-bottom: 4px;">What This Does:</div>
            <ul style="margin: 8px 0; padding-left: 20px; font-size: 14px; color: #1e40af;">
                <li>Links the selected exam to this applicant's access code</li>
                <li>The applicant will only be able to take this specific exam</li>
                <li>You can change the assigned exam later if needed</li>
            </ul>
        </div>
        @endif
    </div>
    
    <div class="assign-drawer-footer">
        <button type="button" class="btn btn-secondary" onclick="closeSingleAssignExamDrawer()">Cancel</button>
        <button type="button" class="btn btn-primary" id="singleAssignBtn" onclick="submitSingleExamAssignment()" {{ empty($exams) ? 'disabled' : '' }}>
            Assign Exam
        </button>
    </div>
</div>

<style>
    /* Assign Drawer Overlay */
    .assign-drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        display: none;
        z-index: 1000;
        transition: opacity 0.3s ease;
    }

    .assign-drawer-overlay.active {
        display: block;
        opacity: 1;
    }

    /* Assign Drawer Panel */
    .assign-drawer {
        position: fixed;
        top: 0;
        right: -600px;
        width: 600px;
        max-width: 90vw;
        height: 100vh;
        background: white;
        box-shadow: -2px 0 8px rgba(0,0,0,0.1);
        z-index: 1001;
        overflow-y: auto;
        transition: right 0.3s ease;
    }

    .assign-drawer.active {
        right: 0;
    }

    .assign-drawer-header {
        position: sticky;
        top: 0;
        background: white;
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 10;
    }

    .assign-drawer-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .assign-drawer-close {
        background: none;
        border: none;
        font-size: 28px;
        color: #6b7280;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: background 0.2s;
    }

    .assign-drawer-close:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .assign-drawer-body {
        padding: 24px;
    }

    .assign-drawer-footer {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    /* Form styles inside assign drawer */
    #assignExamDrawer select,
    #singleAssignExamDrawer select {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
    }
    
    #assignExamDrawer select:focus,
    #singleAssignExamDrawer select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Button styles in assign drawer */
    .assign-drawer-footer .btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        font-size: 14px;
        transition: all 0.2s;
    }

    .assign-drawer-footer .btn-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .assign-drawer-footer .btn-secondary:hover {
        background: #e5e7eb;
    }

    .assign-drawer-footer .btn-primary {
        background: #8b5cf6;
        color: white;
    }

    .assign-drawer-footer .btn-primary:hover {
        background: #7c3aed;
    }

    .assign-drawer-footer .btn-primary:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }
</style>

