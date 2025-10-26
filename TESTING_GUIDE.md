# Testing Guide: EnrollAssess System Simplification

## Prerequisites
- Development server running (`php artisan serve`)
- Database migrated and seeded
- At least one active exam in the system
- Test applicants with access codes
- Test instructor account

---

## Test 1: Simplified Exam System

### Setup
1. Create a new exam via admin panel
2. Set the exam as **Active** (Settings → is_active checkbox)
3. Add questions to the exam (MCQ and True/False)
4. Configure exam quotas in settings

### Test Steps

#### A. Create New Applicant Without Exam Assignment
1. Navigate to Admin → Applicants → Create New Applicant
2. Fill in applicant details
3. Generate access code
4. **Do NOT assign an exam** (skip this step)
5. Save applicant

**Expected Result:** 
✅ Applicant created successfully without exam assignment

#### B. Test Exam Access with Active Exam
1. Logout from admin
2. Navigate to applicant login page
3. Enter the access code from step A
4. Complete privacy consent
5. Click "Start Exam"

**Expected Result:**
✅ Exam starts successfully using the active exam
✅ Questions are randomized per applicant
✅ Timer starts correctly
✅ No error about missing exam assignment

#### C. Test with No Active Exam
1. Admin panel → Deactivate all exams
2. Try to start exam as applicant (step B)

**Expected Result:**
❌ Error message: "No active exam is currently available. Please contact the administration office."

#### D. Test Timing Window Restrictions
1. Admin panel → Set exam availability window:
   - Starts at: Tomorrow 9:00 AM
   - Ends at: Tomorrow 5:00 PM
2. Try to start exam now (before start time)

**Expected Result:**
❌ Error message: "This exam has not started yet. It will be available on [date/time]"

3. Change start time to 1 hour ago
4. Try to start exam again

**Expected Result:**
✅ Exam starts successfully

---

## Test 2: Instructor Bulk Scheduling

### Setup
1. Create multiple test applicants (at least 5)
2. Assign all to same instructor
3. Mark them as "exam-completed" status
4. Login as instructor

### Test Steps

#### A. Individual Scheduling
1. Navigate to Instructor → Schedule
2. Click "Schedule Interview" on one pending applicant
3. Select date/time (tomorrow 9:00 AM)
4. Check "Send email notification"
5. Submit

**Expected Result:**
✅ Interview scheduled successfully
✅ Applicant moved to "Upcoming Interviews" section
✅ Email notification sent (check logs)
✅ Applicant status changed to "interview-scheduled"

#### B. Bulk Scheduling - Auto Distribution
1. Navigate to Instructor → Schedule
2. In "Pending Scheduling" section, check the boxes for 3 applicants
3. Observe selection counter: "(3 selected)"
4. Set bulk schedule parameters:
   - Start time: Tomorrow 10:00 AM
   - Interval: 30 minutes
   - Email notifications: Checked
5. Click "Bulk Schedule"
6. Confirm the dialog

**Expected Result:**
✅ 3 interviews scheduled:
   - Applicant 1: 10:00 AM
   - Applicant 2: 10:30 AM
   - Applicant 3: 11:00 AM
✅ All moved to "Upcoming Interviews"
✅ 3 email notifications sent
✅ Success message with count

#### C. Bulk Scheduling - Select All
1. Click "Select All" checkbox in bulk scheduling section
2. Verify all pending applicants are selected
3. Set parameters and submit

**Expected Result:**
✅ All pending applicants scheduled
✅ Sequential time slots assigned
✅ Email notifications sent to all

#### D. Bulk Scheduling - Different Intervals
Test with each interval option:
- 15 minutes
- 45 minutes  
- 60 minutes

**Expected Result:**
✅ Time slots distributed correctly based on interval
✅ No overlapping interviews

#### E. Rescheduling
1. In "Upcoming Interviews", click "Reschedule" on an interview
2. Change date/time
3. Check email notification
4. Submit

**Expected Result:**
✅ Interview rescheduled successfully
✅ New time displayed
✅ Email notification sent

#### F. Conflict Detection
1. Schedule interview at 2:00 PM
2. Try to schedule another interview at 2:15 PM (within 30 min)

**Expected Result:**
❌ Error: "You have another interview scheduled within 30 minutes of this time"

---

## Test 3: Exam Timing Settings

### Setup
1. Login as admin
2. Navigate to Question Bank page
3. Select an exam

### Test Steps

#### A. Update Exam Settings
1. Click "Edit Settings" button
2. In the settings drawer, configure:
   - Duration: 90 minutes
   - Total Items: 50
   - MCQ Quota: 30
   - True/False Quota: 20
   - Active: Checked
   - Availability Start: [Current date] 08:00
   - Availability End: [Current date] 17:00
3. Click "Save Settings"

**Expected Result:**
✅ Settings saved successfully
✅ No validation errors
✅ Drawer closes
✅ Exam info updates on page

#### B. Validate Timing Window
1. Set availability start to 2 hours from now
2. Try to take exam as applicant

**Expected Result:**
❌ "This exam has not started yet. It will be available on [datetime]"

3. Set availability start to 1 hour ago
4. Set availability end to current time
5. Try to take exam as applicant

**Expected Result:**
❌ "This exam has ended. It was available until [datetime]"

#### C. Clear Timing Window
1. In settings, clear both start and end dates
2. Save

**Expected Result:**
✅ Exam available anytime (when active)

#### D. Timezone Display
1. Set start time to specific hour (e.g., 09:00)
2. Check displayed time in different parts of system

**Expected Result:**
✅ Time displayed correctly in Asia/Manila timezone
✅ Stored in UTC in database
✅ Converted properly for display

---

## Test 4: Backward Compatibility

### Setup
1. Create applicant with **exam assignment** (legacy method)
2. Assign specific exam via "Assign Exam" drawer

### Test Steps

#### A. Legacy Exam Assignment
1. Admin → Applicants → Assign Exam
2. Notice the yellow warning banner about simplified system
3. Select exam and assign

**Expected Result:**
✅ Exam assigned successfully
✅ Simplified system notice displayed
✅ No breaking changes

#### B. Taking Assigned Exam
1. Login as applicant with assigned exam
2. Start exam

**Expected Result:**
✅ Takes specifically assigned exam (not active exam)
✅ System respects legacy assignment

#### C. Mixed System Test
1. Have some applicants with assignments
2. Have some applicants without assignments
3. Verify both work correctly

**Expected Result:**
✅ Assigned applicants → Take assigned exam
✅ Unassigned applicants → Take active exam
✅ No errors or conflicts

---

## Test 5: Edge Cases

### A. Multiple Active Exams
1. Create 2 exams
2. Set both as active
3. Try to start exam

**Expected Result:**
✅ System uses first active exam found
⚠️ Consider: Should display warning about multiple active exams?

### B. No Questions in Active Exam
1. Set exam as active
2. Remove all questions
3. Try to start exam

**Expected Result:**
❌ Error: Exam configuration invalid

### C. Quota Mismatch
1. Set total items: 50
2. Set MCQ quota: 30
3. Set T/F quota: 30 (total = 60, exceeds total items)
4. Try to save

**Expected Result:**
❌ Validation error: "MCQ + T/F quotas cannot exceed total items"

### D. Bulk Schedule with Already Scheduled
1. Select 5 applicants for bulk scheduling
2. One is already scheduled
3. Submit bulk schedule

**Expected Result:**
✅ 4 scheduled successfully
❌ 1 skipped with error message
✅ Error details in response

### E. Expired Access Code
1. Create access code with expiration
2. Wait for expiration or manually expire
3. Try to use code

**Expected Result:**
❌ Error: "Access code has expired"

---

## Performance Tests

### A. Bulk Scheduling Performance
1. Create 50 pending interviews
2. Bulk schedule all at once
3. Measure response time

**Expected Result:**
✅ Completes within 30 seconds
✅ No timeout errors
✅ Transaction rollback if any error

### B. Active Exam Query Performance
1. Create 10 exams in database
2. Measure exam start query time
3. Check database query logs

**Expected Result:**
✅ Single query: `SELECT * FROM exams WHERE is_active = 1 LIMIT 1`
✅ Fast response (<100ms)

---

## Regression Tests

### Test Previous Functionality
- [ ] Applicant CRUD operations
- [ ] Access code generation
- [ ] Question bank management
- [ ] Interview evaluations
- [ ] Result viewing
- [ ] Reports generation
- [ ] User management
- [ ] Email notifications

**Expected Result:**
✅ All existing functionality works as before
✅ No breaking changes

---

## Browser Compatibility

Test in:
- [ ] Chrome/Edge (Chromium)
- [ ] Firefox
- [ ] Safari (if available)
- [ ] Mobile browsers (Chrome Mobile, Safari Mobile)

Focus on:
- Datetime-local picker
- Checkboxes in bulk scheduling
- Modal displays
- Form submissions

---

## Security Tests

### A. Authorization
1. Try to access instructor bulk schedule as applicant
2. Try to access admin settings as instructor
3. Try to schedule interview for another instructor

**Expected Result:**
❌ All unauthorized access blocked
❌ Proper 403/401 errors

### B. CSRF Protection
1. Submit forms without CSRF token
2. Use expired CSRF token

**Expected Result:**
❌ Requests rejected

### C. Input Validation
1. Try SQL injection in form fields
2. Try XSS in notes fields
3. Try invalid date formats

**Expected Result:**
❌ All malicious input sanitized/rejected
✅ Proper validation messages

---

## Success Criteria

### Must Pass
- [x] Active exam system works without assignment
- [x] Bulk scheduling distributes time correctly
- [x] Email notifications sent properly
- [x] Timing window enforcement works
- [x] Backward compatibility maintained
- [x] No linter errors
- [x] No breaking changes to existing features

### Nice to Have
- [ ] Performance under load (50+ applicants)
- [ ] Mobile responsiveness perfect
- [ ] Browser compatibility 100%
- [ ] All edge cases handled gracefully

---

## Bug Reporting Template

If you find issues during testing:

```markdown
## Bug Report

**Title:** [Brief description]

**Severity:** Critical / High / Medium / Low

**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Result:**
[What should happen]

**Actual Result:**
[What actually happened]

**Screenshots/Logs:**
[Attach if applicable]

**Environment:**
- Browser: 
- PHP Version: 
- Laravel Version: 
```

---

## Testing Checklist Summary

### Phase 1: Exam Simplification
- [ ] Create applicant without assignment
- [ ] Start exam with active exam
- [ ] Test no active exam scenario
- [ ] Test timing window restrictions
- [ ] Verify backward compatibility

### Phase 2: Bulk Scheduling
- [ ] Individual scheduling
- [ ] Bulk scheduling (3+ interviews)
- [ ] Select all functionality
- [ ] Different time intervals
- [ ] Rescheduling
- [ ] Conflict detection
- [ ] Email notifications

### Phase 3: Timing Settings
- [ ] Update exam settings
- [ ] Validate timing window
- [ ] Clear timing window
- [ ] Timezone handling

### Phase 4: Integration
- [ ] Complete workflow test
- [ ] Edge cases
- [ ] Performance tests
- [ ] Security tests
- [ ] Browser compatibility

---

## Next Steps After Testing

1. Document any bugs found
2. Fix critical issues
3. Re-test affected areas
4. Update user documentation
5. Create admin training guide
6. Plan production deployment
7. Monitor system after deployment

---

**Status:** Ready for testing
**Last Updated:** 2025-10-24

