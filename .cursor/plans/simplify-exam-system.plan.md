<!-- c09de06b-7000-4d24-be65-fc8488a9df3a 55173dc8-d930-4f13-b2c1-33fa7a6c38af -->
# Simplify EnrollAssess Exam System & Complete Features

## Phase 1: Simplify Exam System (Remove Assignment Complexity)

### 1.1 Update ExamController Logic

**File:** `app/Http/Controllers/ExamController.php`

Modify `startExam()` method (lines 402-506):

- Remove exam_id requirement from access code validation (lines 426-442)
- Replace with single active exam lookup using `Exam::where('is_active', true)->first()`
- Keep existing timing window validation (`isAvailable()`)
- Maintain existing question selection service integration

### 1.2 Update AccessCode Model

**File:** `app/Models/AccessCode.php`

- Keep `exam_id` column and relationship (backward compatibility per 5b)
- Update `hasExamAssigned()` method to be optional/deprecated
- Keep model methods intact for historical data

### 1.3 Update Admin Exam Assignment Interface

**Files to modify:**

- Search for exam assignment views and remove/simplify UI
- Remove exam assignment dropdowns from access code generation

## Phase 2: Complete Instructor Bulk Scheduling

### 2.1 Enhance Instructor Schedule View

**File:** `resources/views/instructor/schedule.blade.php`

Add bulk scheduling interface:

- Checkbox selection for multiple applicants
- Start time picker (datetime input)
- Interval selector (15, 30, 45, 60 minutes dropdown)
- Email notification toggle
- "Bulk Schedule" button

### 2.2 Update Instructor Routes

**File:** `routes/instructor.php`

Routes already exist (lines 74-76):

- `POST /interviews/{interview}/schedule` - scheduleInterview()
- `POST /interviews/bulk-schedule` - bulkScheduleInterviews()
- `POST /interviews/{interview}/send-notification` - sendScheduleNotification()

### 2.3 Verify Controller Implementation

**File:** `app/Http/Controllers/InstructorController.php`

Methods already implemented (lines 395-589):

- `scheduleInterview()` - Individual scheduling with conflict detection
- `bulkScheduleInterviews()` - Bulk scheduling with time distribution
- `sendScheduleNotification()` - Email notifications

**Validation needed:** Ensure bulk scheduling correctly auto-distributes time slots

## Phase 3: Add Exam Timing Settings Management

### 3.1 Update Exam Settings View

**File:** `resources/views/admin/exams/edit.blade.php` or `show.blade.php`

Add timing window controls:

- Start datetime picker (`starts_at`)
- End datetime picker (`ends_at`)
- Clear/Remove timing window button
- Timezone display (Asia/Manila)

### 3.2 Settings Controller Logic

**File:** `app/Http/Controllers/ExamController.php`

`update()` method already handles timing (lines 151-285):

- Validates `starts_at` and `ends_at` (line 164)
- Converts to UTC for storage (lines 249-250)
- Updates exam availability

**Model validation in:** `app/Models/Exam.php` (lines 168-228)

## Phase 4: Testing & Validation

### 4.1 Test Complete Workflow

- Verify single active exam selection
- Test timing window restrictions
- Validate bulk scheduling auto-distribution
- Check email notifications

### 4.2 Review UI/UX

- Ensure mobile responsiveness
- Add loading states
- Confirmation dialogs for bulk operations

## Key Files Summary

**Controllers:**

- `app/Http/Controllers/ExamController.php` - Simplify startExam()
- `app/Http/Controllers/InstructorController.php` - Verify bulk scheduling

**Models:**

- `app/Models/Exam.php` - Timing validation (already implemented)
- `app/Models/AccessCode.php` - Keep for backward compatibility

**Views:**

- `resources/views/instructor/schedule.blade.php` - Add bulk UI
- `resources/views/admin/exams/edit.blade.php` - Verify timing controls

**Routes:**

- `routes/instructor.php` - Already has bulk scheduling routes

## Implementation Notes

1. **Exam Assignment:** Keep `exam_id` column nullable, stop using it in new flow
2. **Active Exam:** Use `is_active` flag + timing window validation (both required)
3. **Bulk Scheduling:** Auto-distribute with configurable intervals (15-60 min)
4. **Settings:** Timing window already supported in model/controller, verify UI
5. **Backward Compatibility:** Historical assignments remain intact

### To-dos

- [ ] Update ExamController::startExam() to use single active exam instead of exam_id assignment
- [ ] Verify and test InstructorController bulk scheduling implementation
- [ ] Create bulk scheduling interface in instructor schedule view
- [ ] Verify exam timing window controls exist in admin interface
- [ ] Test complete exam workflow with simplified system