# Instructor Interview Scheduling - Testing Guide

## Prerequisites
1. Log in as an instructor user
2. Ensure you have applicants assigned to you (`assigned_instructor_id` matches your user ID)
3. Ensure some applicants have completed exams (status: 'exam-completed')

---

## Test Scenario 1: Individual Scheduling from Applicants Page

### Steps:
1. Navigate to `/instructor/applicants`
2. Find an applicant with status "Exam Completed" or "Not scheduled"
3. Click the "Schedule Interview" button
4. Verify the modal opens with:
   - Applicant name pre-filled
   - Date/time picker
   - Notes textarea
   - Email notification checkbox (checked by default)
5. Select a date/time at least 1 hour in the future
6. Add optional notes (e.g., "Please bring your portfolio")
7. Keep the email notification checked
8. Click "Schedule Interview"

### Expected Results:
- ✅ Success message appears: "Interview scheduled successfully! Email notification sent."
- ✅ Page reloads
- ✅ Interview date shows in the table
- ✅ Applicant status updates to "interview-scheduled"
- ✅ Email sent to applicant's email address
- ✅ Button changes from "Schedule Interview" to "Start Interview"

### Test Variations:
- **Without Email**: Uncheck email notification, verify no email sent but scheduling works
- **With Long Notes**: Enter 500+ characters in notes, verify it saves
- **Past Date**: Try selecting a past date, verify validation error

---

## Test Scenario 2: Bulk Scheduling from Applicants Page

### Steps:
1. Navigate to `/instructor/applicants`
2. Check the checkboxes for 3-5 applicants that need scheduling
3. Verify bulk actions bar appears showing "X applicant(s) selected"
4. Click "Schedule Selected" button
5. Verify modal shows list of selected applicant names
6. Set start date/time (e.g., tomorrow at 9:00 AM)
7. Select time interval (e.g., 60 minutes)
8. Keep email notification checked
9. Click "Schedule All"

### Expected Results:
- ✅ Success message: "Successfully scheduled X interview(s). Y email notification(s) sent."
- ✅ Page reloads
- ✅ All selected applicants now have scheduled dates
- ✅ Dates are incremented by chosen interval (9:00 AM, 10:00 AM, 11:00 AM, etc.)
- ✅ All applicants receive email notifications
- ✅ Bulk actions bar disappears
- ✅ Checkboxes are cleared

### Test Variations:
- **Different Intervals**: Test with 30, 45, 90, 120 minute intervals
- **Without Email**: Uncheck email notification, verify scheduling works without emails
- **Large Batch**: Select 10+ applicants, verify all are scheduled correctly
- **Clear Selection**: Click "Clear Selection", verify bulk bar disappears

---

## Test Scenario 3: Scheduling from Schedule Page

### Steps:
1. Navigate to `/instructor/schedule`
2. In the "Pending Scheduling" section, find an interview
3. Click "Schedule Interview" button
4. Fill in date/time and optional notes
5. Toggle email notification checkbox
6. Submit the form

### Expected Results:
- ✅ Interview scheduled successfully
- ✅ Interview moves to "Upcoming Interviews" section
- ✅ Email notification sent if checkbox was checked
- ✅ Page reloads with updated data

---

## Test Scenario 4: Conflict Detection

### Steps:
1. Schedule an interview for tomorrow at 2:00 PM
2. Try to schedule another interview for tomorrow at 2:15 PM (within 30 minutes)
3. Submit the form

### Expected Results:
- ❌ Error message: "You have another interview scheduled within 30 minutes of this time."
- ✅ Form remains open
- ✅ No interview is created

### Test Variations:
- **Exactly 30 minutes apart**: Schedule at 2:00 PM and 2:30 PM - should succeed
- **Same time**: Schedule at same time - should fail
- **Different days**: Schedule at 2:00 PM on different days - should succeed

---

## Test Scenario 5: Email Notification Features

### Steps:
1. Schedule an interview with email notification enabled
2. Check the applicant's email inbox

### Expected Results:
- ✅ Email received with subject "Interview Scheduled - [App Name]"
- ✅ Email shows:
  - Applicant's full name
  - Application number
  - Interview date (formatted: "October 15, 2025")
  - Interview time (formatted: "2:00 PM")
  - Interviewer name (YOUR NAME - the logged-in instructor)
  - Any notes you added
  - Important reminders section

### Verify Instructor Name:
- ✅ The email should show YOUR instructor name, not "TBA" or another instructor

---

## Test Scenario 6: Select All Functionality

### Steps:
1. Navigate to `/instructor/applicants`
2. Click the "Select All" checkbox in the table header
3. Verify all schedulable applicants are selected
4. Uncheck "Select All"
5. Verify all checkboxes are unchecked
6. Manually select 2-3 applicants
7. Click "Select All" again
8. Verify all are selected

### Expected Results:
- ✅ Select all checks all schedulable applicants only
- ✅ Unselect all clears all checkboxes
- ✅ Bulk actions bar appears/disappears correctly
- ✅ Selected count updates accurately

---

## Test Scenario 7: Modal Behavior

### Steps:
1. Open any scheduling modal
2. Click outside the modal (on the dark overlay)
3. Verify modal closes
4. Open modal again
5. Click the "×" close button
6. Verify modal closes
7. Open modal, fill in some data
8. Click "Cancel" button
9. Verify modal closes and form is reset

### Expected Results:
- ✅ Modal closes on overlay click
- ✅ Modal closes on × button
- ✅ Modal closes on Cancel button
- ✅ Form data is cleared when modal closes
- ✅ No data persists when reopening

---

## Test Scenario 8: Authorization & Security

### Steps:
1. Open browser developer tools → Network tab
2. Schedule an interview
3. Note the interview ID in the request
4. Try to schedule a different instructor's interview by manually changing the ID in a new request

### Expected Results:
- ❌ Error 403: "You are not assigned to this interview."
- ✅ No interview is scheduled
- ✅ Authorization check prevents unauthorized scheduling

---

## Test Scenario 9: Responsive Design

### Steps:
1. Open `/instructor/applicants` on desktop (1920px width)
2. Verify layout looks good
3. Resize browser to tablet size (768px)
4. Verify table and modals adapt
5. Resize to mobile size (375px)
6. Verify everything still works

### Expected Results:
- ✅ Desktop: 2-column bulk modal layout
- ✅ Tablet: Readable table, functional modals
- ✅ Mobile: Single-column layouts, bulk actions stack vertically
- ✅ Modals remain centered and usable on all sizes

---

## Test Scenario 10: Error Handling

### Test A - Network Error:
1. Open developer tools
2. Go to Network tab → Throttling → Offline
3. Try to schedule an interview
4. Expected: "An error occurred. Please try again."

### Test B - Invalid Date:
1. Manually set datetime-local input to empty or invalid value via browser console
2. Try to submit
3. Expected: HTML5 validation prevents submission

### Test C - Already Scheduled:
1. Schedule an interview
2. Try to schedule the same interview again (if it appears in bulk list)
3. Expected: Error message in bulk response

---

## Database Verification

### After scheduling, verify in database:

**Interviews Table:**
```sql
SELECT interview_id, applicant_id, interviewer_id, schedule_date, status, notes
FROM interviews
WHERE interviewer_id = [YOUR_USER_ID]
ORDER BY schedule_date DESC;
```

Expected:
- ✅ `schedule_date` is set to your chosen date/time
- ✅ `status` = 'scheduled'
- ✅ `notes` contains your text (if added)
- ✅ `interviewer_id` matches your user ID

**Applicants Table:**
```sql
SELECT applicant_id, first_name, last_name, status
FROM applicants
WHERE assigned_instructor_id = [YOUR_USER_ID];
```

Expected:
- ✅ `status` = 'interview-scheduled' for scheduled applicants

---

## Edge Cases to Test

1. **No Schedulable Applicants**:
   - All applicants already scheduled
   - Expected: No checkboxes appear, bulk bar doesn't show

2. **Single Applicant Bulk Schedule**:
   - Select only 1 applicant for bulk schedule
   - Expected: Works correctly, same as individual

3. **Scheduling at Midnight**:
   - Schedule for 12:00 AM
   - Expected: Works correctly, time formats properly in email

4. **Very Long Notes**:
   - Enter 1000 characters in notes
   - Expected: Saves successfully (max is 1000)

5. **Special Characters in Notes**:
   - Use quotes, apostrophes, emojis
   - Expected: Saves and displays correctly

6. **Rapid Clicking**:
   - Click "Schedule Interview" button multiple times quickly
   - Expected: Only one request sent, no duplicates

---

## Success Criteria

All tests should:
- ✅ Complete without errors
- ✅ Show appropriate success/error messages
- ✅ Update database correctly
- ✅ Send emails when requested
- ✅ Maintain data integrity
- ✅ Provide good user experience
- ✅ Work across different browsers (Chrome, Firefox, Safari, Edge)
- ✅ Work on different screen sizes
- ✅ Enforce security and authorization

---

## Rollback Plan

If issues are found:
1. Routes can be commented out in `routes/instructor.php`
2. Remove scheduling buttons from views
3. Previous functionality remains intact
4. No database schema changes were made, so no migrations to rollback

---

## Performance Notes

- Bulk scheduling uses database transactions for atomic operations
- Email sending is synchronous but failures don't break the flow
- Page reloads after scheduling to ensure data consistency
- Consider using queued emails for production with 50+ bulk schedules

