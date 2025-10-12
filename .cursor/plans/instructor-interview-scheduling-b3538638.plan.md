<!-- b3538638-d61d-433e-b471-d10aa3d9711e b64a2074-fc07-440a-b1ff-7c70dbbe662a -->
# Instructor Interview Scheduling Implementation

## Overview

Enable instructors to schedule interviews for their assigned applicants with both individual and bulk scheduling capabilities, optional email notifications, and integration with existing `InterviewScheduleMail`.

## Implementation Details

### 1. Update Applicants Page UI

**File:** `resources/views/instructor/applicants.blade.php`

Add scheduling buttons to each applicant card:

- "Schedule Interview" button for applicants with pending interviews (status = 'assigned' or schedule_date = null)
- Show existing schedule date/time for already scheduled interviews
- Add bulk selection checkboxes for multi-select functionality
- Add "Schedule Selected" button in toolbar when applicants are selected

### 2. Enhance Schedule Page

**File:** `resources/views/instructor/schedule.blade.php`

Enhance existing modal to include:

- Optional email notification checkbox
- Better time picker with recommended time slots
- Validation for scheduling conflicts (same instructor, overlapping times)
- Success feedback with email confirmation status

Add bulk scheduling interface:

- Multi-select functionality for pending interviews
- Batch schedule with auto-incrementing time slots
- Option to spread across multiple days
- Email notification toggle for all

### 3. Create Controller Methods

**File:** `app/Http/Controllers/InstructorController.php`

Add new methods:

```php
// Individual scheduling from applicants page
scheduleInterview(Request $request, $interviewId)
// Bulk scheduling for multiple interviews
bulkScheduleInterviews(Request $request)
// Send notification after scheduling
sendScheduleNotification($interviewId)
```

Validation requirements:

- Ensure instructor owns the interview (via assigned_instructor_id)
- Schedule date must be in the future (minimum 1 hour ahead)
- Check for instructor's scheduling conflicts
- Validate time slot availability

### 4. Add Routes

**File:** `routes/instructor.php`

New routes:

```php
Route::post('/interviews/{interview}/schedule', [InstructorController::class, 'scheduleInterview'])->name('interviews.schedule');
Route::post('/interviews/bulk-schedule', [InstructorController::class, 'bulkScheduleInterviews'])->name('interviews.bulk-schedule');
Route::post('/interviews/{interview}/send-notification', [InstructorController::class, 'sendScheduleNotification'])->name('interviews.send-notification');
```

### 5. Update Email Template

**File:** `resources/views/emails/interview-schedule.blade.php`

Modify to dynamically show instructor name from the `$instructor` variable (already passed). The template already supports this at line 105: `{{ $instructor->name ?? 'TBA' }}` - no changes needed, just ensure the instructor data is properly passed.

### 6. Email Integration

Use existing `InterviewScheduleMail` class which already:

- Accepts `Applicant` and `Interview` models
- Passes instructor name to template
- Formats date and time properly

Add email sending in controller with optional toggle:

```php
if ($request->notify_email) {
    Mail::to($applicant->email_address)->send(
        new InterviewScheduleMail($applicant, $interview)
    );
}
```

### 7. Frontend JavaScript

Add to both `applicants.blade.php` and `schedule.blade.php`:

- Bulk selection handler with checkboxes
- Schedule modal with datetime picker
- Email notification toggle
- Form validation (date/time format, future dates only)
- AJAX submission with error handling
- Success notifications with email status

### 8. UI Enhancements

- Time slot suggestions (9:00 AM, 10:30 AM, 1:00 PM, 2:30 PM, 4:00 PM)
- Conflict detection UI feedback
- Loading states during scheduling
- Toast notifications for success/failure
- Email send status indicator

## Key Features

- Individual scheduling from applicants page
- Bulk scheduling from both applicants and schedule pages
- Optional email notifications (checkbox)
- Uses existing `InterviewScheduleMail` template
- Conflict detection for instructor's schedule
- Responsive UI with professional styling
- Consistent with existing instructor portal design

### To-dos

- [ ] Add scheduling buttons and bulk selection to instructor applicants page
- [ ] Enhance existing schedule modal with email toggle and better time picker
- [ ] Add bulk scheduling interface to schedule page
- [ ] Implement scheduleInterview, bulkScheduleInterviews, and sendScheduleNotification methods
- [ ] Add new interview scheduling routes to instructor.php
- [ ] Integrate InterviewScheduleMail with optional notification sending
- [ ] Implement JavaScript for bulk selection, modals, and AJAX submission
- [ ] Test individual scheduling, bulk scheduling, email notifications, and conflict detection