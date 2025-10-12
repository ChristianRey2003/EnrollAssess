# Admin Exam Notification - Testing Guide

**Feature:** Enhanced Exam Notification System  
**Date:** October 10, 2025

---

## Prerequisites

Before testing, ensure:

1. ✅ Laravel application is running (`php artisan serve`)
2. ✅ Database is migrated and seeded
3. ✅ At least 5-10 test applicants exist
4. ✅ Some applicants have access codes, some don't
5. ✅ Admin user credentials available
6. ✅ Mail is configured (use `log` driver for testing)

---

## Test Scenario 1: Bulk Notification (Happy Path)

### Setup
1. Ensure 3-5 applicants have access codes
2. Ensure all have valid email addresses

### Steps
1. Navigate to `/admin/applicants`
2. Select 3 applicants with access codes (use checkboxes)
3. Verify bulk actions toolbar appears
4. Click "Send Exam Notifications" button
5. Modal should open showing:
   - "3 applicant(s) will receive this email"
   - Date field (required)
   - Time field (required)
   - Venue field (optional)
   - Special Instructions textarea (optional)
6. Fill in:
   - Date: Tomorrow's date
   - Time: 9:00 AM
   - Venue: "Computer Laboratory 1"
   - Instructions: "Please bring a calculator and ID"
7. Click "Send Email Notifications"
8. Verify success message shows: "Email notifications sent successfully to 3 applicant(s)."
9. Check mail log: `storage/logs/laravel.log`
10. Verify 3 emails were logged with correct content

### Expected Results
- ✅ Modal opens correctly
- ✅ All fields display properly
- ✅ Form submits without errors
- ✅ Success message accurate
- ✅ 3 emails sent
- ✅ Each email contains:
  - Applicant's name
  - Application number
  - Date: "January 16, 2024" (or equivalent)
  - Time: "9:00 AM"
  - Venue: "Computer Laboratory 1"
  - Special Instructions section with text
  - Access code
  - Portal link

---

## Test Scenario 2: Individual Notification

### Steps
1. Navigate to `/admin/applicants`
2. Hover over any applicant row with an access code
3. Floating actions menu appears (View, Edit, 📧, Delete)
4. Click the 📧 (email) icon
5. Modal opens with single applicant selected
6. Fill in:
   - Date: Tomorrow
   - Time: 2:00 PM
   - Venue: "Room 203"
   - Instructions: "Arrive 15 minutes early"
7. Click "Send Email Notifications"
8. Verify success message
9. Check mail log for email

### Expected Results
- ✅ Email icon appears in hover menu
- ✅ Modal opens for single applicant
- ✅ Count shows "1 applicant(s) will receive this email"
- ✅ Email sent successfully
- ✅ Email contains correct time: "2:00 PM"
- ✅ Venue: "Room 203"
- ✅ Instructions appear

---

## Test Scenario 3: Access Code Validation

### Setup
1. Create/select 2 applicants WITHOUT access codes
2. Create/select 2 applicants WITH access codes

### Steps
1. Select all 4 applicants
2. Click "Send Exam Notifications"
3. Fill in date and time
4. Click send
5. Review response

### Expected Results
- ✅ Success message: "Email notifications sent successfully to 2 applicant(s). 2 failed."
- ✅ Error details show:
  - "Applicant [Name] does not have an access code."
  - Listed for both applicants without codes
- ✅ Only 2 emails sent (to those with codes)
- ✅ Modal closes after sending

---

## Test Scenario 4: Email Address Validation

### Setup
1. Find/create applicant with access code but NO email address
2. Use SQL: `UPDATE applicants SET email_address = NULL WHERE applicant_id = X;`

### Steps
1. Select applicant without email
2. Try to send notification

### Expected Results
- ✅ Error: "Applicant [Name] does not have an email address."
- ✅ No email sent
- ✅ Detailed error in response

---

## Test Scenario 5: Optional Fields (Venue & Instructions)

### Test 5A: Empty Venue
1. Send notification
2. Leave venue field empty
3. Submit

**Expected:**
- ✅ Email shows "Exam Venue: To Be Announced"

### Test 5B: Empty Instructions
1. Send notification
2. Leave instructions field empty
3. Submit

**Expected:**
- ✅ Special Instructions section does NOT appear in email
- ✅ Email still sends successfully

### Test 5C: Both Optional Fields Empty
1. Send with only date and time
2. Submit

**Expected:**
- ✅ Venue: "To Be Announced"
- ✅ No special instructions section
- ✅ Email valid and sends

---

## Test Scenario 6: Required Field Validation

### Test 6A: Missing Date
1. Open modal
2. Fill time but leave date empty
3. Try to submit

**Expected:**
- ✅ Browser validation: "Please fill out this field"
- ✅ Form does not submit

### Test 6B: Missing Time
1. Fill date but leave time empty
2. Try to submit

**Expected:**
- ✅ Browser validation error
- ✅ Form does not submit

---

## Test Scenario 7: Special Characters in Instructions

### Steps
1. Fill special instructions with:
   ```
   Important:
   - Bring ID & calculator
   - No phones allowed!
   - Test will be 60 minutes
   
   Questions? Email admin@example.com
   ```
2. Send notification

### Expected Results
- ✅ Line breaks preserved
- ✅ Special characters (-, &, !, @) display correctly
- ✅ No HTML injection
- ✅ Text appears in blue box in email

---

## Test Scenario 8: Long Venue Name

### Steps
1. Enter very long venue:
   ```
   Computer Science Department, Building A, 3rd Floor, Laboratory 301, Western Wing, Main Campus
   ```
2. Send notification

### Expected Results
- ✅ Full text saved
- ✅ Displays correctly in email
- ✅ No truncation
- ✅ Text wraps properly in email

---

## Test Scenario 9: Multiple Bulk Operations

### Steps
1. Select 3 applicants, send notifications (Date: Jan 15, Time: 9 AM)
2. Select 2 different applicants, send again (Date: Jan 16, Time: 2 PM)
3. Check both sets of emails

### Expected Results
- ✅ Each batch has correct date/time
- ✅ No data mixing between operations
- ✅ All emails sent correctly

---

## Test Scenario 10: UI Responsiveness

### Test 10A: Modal on Small Screen
1. Resize browser to 768px width
2. Open notification modal

**Expected:**
- ✅ Modal still readable
- ✅ Fields stack vertically
- ✅ Buttons accessible

### Test 10B: Table with Email Icon
1. Check floating actions on mobile
2. Hover/tap to reveal actions

**Expected:**
- ✅ All 4 icons visible
- ✅ Email icon (📧) between Edit and Delete
- ✅ Tooltips work

---

## Email Content Verification Checklist

For each sent email, verify it contains:

### Header Section
- ✅ Subject: "BSIT Entrance Exam Notification"
- ✅ Professional header with gradient
- ✅ "Computer Studies Department" subtitle

### Greeting
- ✅ "Dear [Applicant Full Name],"

### Info Box
- ✅ Application No: [Number]
- ✅ Exam Date: [Formatted date]
- ✅ Exam Time: [Formatted time]
- ✅ Exam Venue: [Venue or "To Be Announced"]

### Access Code Section
- ✅ Blue dashed border box
- ✅ "Your Access Code" label
- ✅ Code in large monospace font
- ✅ Security reminder text

### Exam Portal Link
- ✅ Green box with button
- ✅ "Go to Exam Portal" button
- ✅ URL: `http://localhost:8000/applicant/login`

### Special Instructions (if provided)
- ✅ Blue solid border box
- ✅ 📋 icon with heading
- ✅ Instructions text (multi-line preserved)

### Standard Instructions
- ✅ Yellow warning box
- ✅ Bullet list:
  - Arrive 15 minutes early
  - Bring valid ID
  - Stable internet connection
  - Desktop/laptop (not mobile)
  - Fullscreen mode enabled
  - No page refresh
  - Complete in one sitting

### Footer
- ✅ "Computer Studies Department"
- ✅ "Do not reply" notice
- ✅ Copyright year
- ✅ "EnrollAssess System"

---

## Developer Testing

### Console Checks
1. Open browser DevTools → Console
2. Perform bulk operation
3. Check for errors

**Expected:**
- ✅ No JavaScript errors
- ✅ Console logs show:
  - "Opening email notification drawer. Selected applicants: [...]"
  - "Sending email request: {...}"
  - "Response status: 200"
  - "Response data: {...}"

### Network Tab
1. Open DevTools → Network
2. Send notification
3. Check POST request to `/admin/applicants/bulk/send-exam-notifications`

**Expected:**
- ✅ Status: 200 OK
- ✅ Request payload contains:
  ```json
  {
    "applicant_ids": [1, 2, 3],
    "exam_date": "January 16, 2024",
    "exam_time": "9:00 AM",
    "exam_venue": "Computer Laboratory 1",
    "special_instructions": "Bring calculator"
  }
  ```
- ✅ Response contains success counts

### Database Checks
No database changes expected (emails only).

---

## Performance Testing

### Test: Send to 50 Applicants
1. Create 50 applicants with access codes
2. Select all
3. Send notification
4. Measure time

**Expected:**
- ✅ Completes in < 30 seconds
- ✅ No timeout errors
- ✅ All emails queued/sent
- ✅ Accurate success count

---

## Error Handling Tests

### Test: Network Failure Simulation
1. Open DevTools → Network
2. Set throttling to "Offline"
3. Try to send notification

**Expected:**
- ✅ Error message: "Network error: Failed to fetch"
- ✅ Button state resets
- ✅ User can retry

### Test: Server Error Simulation
- Temporarily break controller (syntax error)
- Try to send

**Expected:**
- ✅ Error caught gracefully
- ✅ User-friendly message shown
- ✅ No page crash

---

## Regression Testing

Ensure existing features still work:

### ✅ Generate Access Codes
1. Select applicants without codes
2. Click "Generate Codes"
3. Verify still works

### ✅ Bulk Export
1. Select applicants
2. Click "Export"
3. CSV downloads

### ✅ View/Edit/Delete
1. Hover over applicant
2. Click View → Details page loads
3. Click Edit → Edit form loads
4. Click Delete → Confirmation and deletion works

### ✅ Search and Filters
1. Use search bar
2. Filter by status
3. Verify table updates

---

## Mail Log Inspection

To check sent emails:

```bash
# View mail log
tail -f storage/logs/laravel.log | grep -A 50 "BSIT Entrance Exam"

# Or open in editor
code storage/logs/laravel.log
```

Search for:
- Subject line: "BSIT Entrance Exam Notification"
- Recipient emails
- Access codes
- Venue information
- Special instructions

---

## Known Issues / Edge Cases

### Issue: Modal doesn't close after sending
**Fix:** Refresh page as workaround
**Status:** Works as expected (modal should close)

### Issue: Email not formatted on some clients
**Note:** Email uses inline styles for maximum compatibility
**Test:** Forward to Gmail, Outlook, etc.

---

## Test Result Template

```
Test: [Scenario Name]
Date: [Date]
Tester: [Name]
Result: PASS / FAIL
Notes: [Any observations]
Screenshots: [If applicable]
```

---

## Completion Checklist

- [ ] All 10 test scenarios completed
- [ ] Email content verified
- [ ] Developer console clean
- [ ] Network requests valid
- [ ] Performance acceptable
- [ ] Error handling works
- [ ] Regression tests pass
- [ ] Mail logs reviewed
- [ ] Documentation reviewed
- [ ] Ready for production

---

**Testing Status:** Ready for QA  
**Estimated Testing Time:** 30-45 minutes for complete suite

