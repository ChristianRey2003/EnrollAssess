<!-- f5e80e07-350f-4e62-b857-ed3c65b091fb ecac993d-fbdb-41ac-8695-dc2890219153 -->
# Exam Assignment System Implementation

## Overview

Add exam assignment capability to access codes with clean UI integration, no new columns, and both individual and bulk assignment options.

## User Requirements

- Enhanced Access Code column (no new column, no emojis)
- Add "Assigned Exam" to export CSV
- Both individual AND bulk exam assignment

---

## Phase 1: Database & Core Models

### 1.1 Database Migration

**File:** `database/migrations/YYYY_MM_DD_add_exam_id_to_access_codes.php`

Add `exam_id` column to `access_codes` table:

- Column: `exam_id` (bigint unsigned, nullable, after applicant_id)
- Foreign key: references `exams(exam_id)` on delete cascade
- Index for performance

### 1.2 Model Update

**File:** `app/Models/AccessCode.php`

- Add `exam_id` to `$fillable` array
- Add `exam()` relationship method
- Add helper method `hasExamAssigned()` to check if exam_id exists
- Add helper method `getExamStatusText()` for display

---

## Phase 2: Backend Logic

### 2.1 New Controller Method - Bulk Assign

**File:** `app/Http/Controllers/ApplicantController.php`

**New method:** `assignExamToApplicants(Request $request)`

Logic:

1. Validate request (applicant_ids array, exam_id required)
2. Get applicants with access codes
3. Filter: only applicants that have access codes
4. Update access_codes.exam_id for valid applicants
5. Track success/skipped/error counts
6. Return JSON response with results

### 2.2 New Controller Method - Individual Assign

**File:** `app/Http/Controllers/ApplicantController.php`

**New method:** `assignExamToApplicant(Request $request, $applicantId)`

Logic:

1. Validate exam_id
2. Find applicant
3. Check if has access code
4. Update access code with exam_id
5. Return JSON response

### 2.3 Update Exam Start Validation

**File:** `app/Http/Controllers/ExamController.php`

**Update method:** `startExam()` at line 402

Current code:

```php
$accessCode = $applicant->accessCode;
if (!$accessCode || !$accessCode->exam) {
```

Change to validate exam_id properly:

- Check if `$accessCode->exam_id` is NULL
- If NULL: Return error "No exam assigned to your access code"
- If valid: Proceed with exam selection

### 2.4 Update Export to Include Exam

**File:** `app/Http/Controllers/ApplicantController.php`

**Update method:** `exportWithAccessCodes()` at line 707

Changes:

- Update CSV header to include "Assigned Exam" column
- For each applicant, add exam name:
  - If access code exists and has exam: Show exam title
  - If access code exists but no exam: Show "No Exam Assigned"
  - If no access code: Show "No Access Code"

---

## Phase 3: Routes

**File:** `routes/admin.php`

Add new routes:

```php
// Bulk assign exam
Route::post('/applicants/assign-exam', [ApplicantController::class, 'assignExamToApplicants'])
    ->name('applicants.assign-exam');

// Individual assign exam  
Route::post('/applicants/{applicant}/assign-exam', [ApplicantController::class, 'assignExamToApplicant'])
    ->name('applicants.assign-exam-single');
```

---

## Phase 4: Frontend - Enhanced Access Code Column

### 4.1 Update Applicants Table Display

**File:** `resources/views/admin/applicants/index.blade.php`

**Location:** Access Code column in the table (around line 250)

**Current display:**

```blade
<td>
    @if($applicant->accessCode)
        {{ $applicant->accessCode->code }}
    @else
        <span class="badge badge-warning">No Code</span>
    @endif
</td>
```

**New display:**

```blade
<td>
    @if($applicant->accessCode)
        <div>
            <strong>{{ $applicant->accessCode->code }}</strong>
            @if($applicant->accessCode->exam_id)
                <div style="font-size: 11px; color: #059669; margin-top: 2px;">
                    <span style="display: inline-block; width: 4px; height: 4px; border-radius: 50%; background: #059669; margin-right: 4px;"></span>
                    {{ $applicant->accessCode->exam->title }}
                </div>
            @else
                <div style="font-size: 11px; color: #f59e0b; margin-top: 2px;">
                    <span style="display: inline-block; width: 4px; height: 4px; border-radius: 50%; background: #f59e0b; margin-right: 4px;"></span>
                    No exam assigned
                </div>
            @endif
        </div>
    @else
        <span class="badge badge-warning">No Access Code</span>
    @endif
</td>
```

**Styling:**

- Green dot + exam title (small text below code)
- Yellow dot + "No exam assigned" (small text below code)
- No emojis, clean professional look

---

## Phase 5: Frontend - Bulk Assign Exam Modal

### 5.1 Create Assign Exam Modal

**File:** `resources/views/admin/applicants/partials/assign-exam-modal.blade.php` (NEW)

Modal structure:

- Header: "Assign Exam to Applicants"
- Body:
  - Display selected count
  - Dropdown to select exam (fetch active exams)
  - Show exam details (duration, total items)
  - Validation message if some don't have access codes
- Footer: Cancel and "Assign Exam" buttons

### 5.2 Include Modal in Main View

**File:** `resources/views/admin/applicants/index.blade.php`

Add after existing modals (around line 450):

```blade
@include('admin.applicants.partials.assign-exam-modal')
```

### 5.3 Add Bulk Action Button

**File:** `resources/views/admin/applicants/index.blade.php`

**Location:** Bulk actions section (around line 177)

Add button between "Generate Codes" and "Send Exam Notifications":

```html
<button onclick="showAssignExamModal()" 
        class="bulk-btn" 
        style="height: 28px; padding: 4px 8px; font-size: 12px; background: #8b5cf6; color: white;">
    Assign Exam
</button>
```

---

## Phase 6: Frontend - Individual Assign (Actions Dropdown)

### 6.1 Add to Actions Dropdown

**File:** `resources/views/admin/applicants/index.blade.php`

**Location:** In each row's actions dropdown (around line 280)

Add menu item:

```blade
<button onclick="showSingleAssignExamModal({{ $applicant->applicant_id }})" 
        class="dropdown-item">
    <span style="margin-right: 8px;">Assign Exam</span>
    @if($applicant->accessCode && $applicant->accessCode->exam_id)
        <small style="color: #6b7280;">({{ $applicant->accessCode->exam->title }})</small>
    @endif
</button>
```

### 6.2 Create Single Assign Modal

**File:** `resources/views/admin/applicants/partials/assign-exam-modal.blade.php`

Add second modal for individual assignment:

- Similar to bulk modal but for single applicant
- Shows applicant name
- Dropdown to select exam
- Button: "Assign Exam"

---

## Phase 7: JavaScript Implementation

### 7.1 Update Applicant Manager

**File:** `public/js/modules/applicant-manager.js`

**Add new methods:**

```javascript
// Show bulk assign modal
async showAssignExamModal() {
    // Fetch active exams
    // Populate modal
    // Show modal
}

// Bulk assign exam
async bulkAssignExam(examId) {
    // Get selected applicant IDs
    // POST to /admin/applicants/assign-exam
    // Handle response
    // Show success/error notifications
    // Reload page
}

// Show single assign modal
async showSingleAssignExamModal(applicantId) {
    // Fetch active exams
    // Populate modal with applicant info
    // Show modal
}

// Single assign exam
async assignExamToSingle(applicantId, examId) {
    // POST to /admin/applicants/{id}/assign-exam
    // Handle response
    // Show notification
    // Reload page
}
```

### 7.2 Add Modal Control Functions

**File:** `resources/views/admin/applicants/index.blade.php` (inline scripts)

Add functions:

- `showAssignExamModal()`
- `closeAssignExamModal()`
- `showSingleAssignExamModal(applicantId)`
- `closeSingleAssignExamModal()`
- `submitBulkExamAssignment()`
- `submitSingleExamAssignment()`

---

## Phase 8: Data Migration (for existing records)

### 8.1 Handle Existing Access Codes

**Strategy:** Leave exam_id as NULL for existing codes

After migration runs:

- All existing access_codes will have exam_id = NULL
- Admin must manually assign exams using new UI
- Ensures clean data and explicit assignment

**Optional seeder for testing:**

- Can create a seeder to assign default exam to all existing codes
- Only for development/testing

---

## Phase 9: Validation & Error Handling

### 9.1 Backend Validation

- Validate exam_id exists in exams table
- Validate exam is active
- Validate applicant has access code before assignment
- Return proper error messages

### 9.2 Frontend Validation

- Disable "Assign Exam" if no applicants selected
- Show warning if some selected applicants lack access codes
- Confirm before overwriting existing exam assignments

### 9.3 Exam Start Validation

- Check exam_id is not NULL
- Check exam is still active
- Show user-friendly error if no exam assigned

---

## Testing Checklist

After implementation:

1. Generate access code (exam_id = NULL)
2. View applicant table - see "No exam assigned" under code
3. Bulk select applicants → Assign exam → Verify updates
4. Individual assign via dropdown → Verify updates
5. View applicant table - see exam name under code
6. Export CSV - verify "Assigned Exam" column present
7. Applicant tries to start exam without assignment → See error
8. Applicant with assignment starts exam → Works normally
9. Try to assign exam to applicant without access code → See skip message
10. Assign exam to applicant who already has exam → Confirm overwrite

---

## Files Summary

### New Files (2)

- `database/migrations/YYYY_MM_DD_add_exam_id_to_access_codes.php`
- `resources/views/admin/applicants/partials/assign-exam-modal.blade.php`

### Modified Files (6)

- `app/Models/AccessCode.php` - Add relationship and helpers
- `app/Http/Controllers/ApplicantController.php` - Add 2 methods, update export
- `app/Http/Controllers/ExamController.php` - Update validation
- `routes/admin.php` - Add 2 routes
- `resources/views/admin/applicants/index.blade.php` - Update table column, add buttons, add modals
- `public/js/modules/applicant-manager.js` - Add assignment methods

---

## Implementation Order

1. Database migration (add exam_id column)
2. Model update (AccessCode relationships)
3. Backend methods (assign exam logic)
4. Routes (API endpoints)
5. Export update (add column)
6. Enhanced access code column display
7. Bulk assign modal + button
8. Individual assign dropdown + modal
9. JavaScript functionality
10. Exam start validation update
11. Testing

### To-dos

- [ ] Create migration to add exam_id to access_codes table with foreign key
- [ ] Update AccessCode model with exam relationship and helper methods
- [ ] Create assignExamToApplicants method for bulk assignment
- [ ] Create assignExamToApplicant method for individual assignment
- [ ] Add routes for bulk and individual exam assignment
- [ ] Update exportWithAccessCodes to include Assigned Exam column
- [ ] Update ExamController startExam to validate exam_id exists
- [ ] Enhance Access Code column to show exam status below code
- [ ] Create assign-exam-modal.blade.php with bulk assignment UI
- [ ] Add Assign Exam button to bulk actions bar
- [ ] Add Assign Exam option to individual row actions dropdown
- [ ] Implement JavaScript methods for exam assignment in applicant-manager.js
- [ ] Test all exam assignment workflows and validation