# How to Send Exam Notifications - User Guide

**Feature:** Admin Exam Notification System  
**Date:** October 10, 2025

---

## Overview

This guide shows you how to send exam notifications to applicants with complete exam details (date, time, venue, instructions, and access codes).

---

## Method 1: Bulk Notification (Multiple Applicants)

### Step 1: Select Applicants

1. Go to **Applicants** page (`/admin/applicants`)
2. You'll see a **checkbox column** (first column) in the applicants table
3. Click individual checkboxes to select specific applicants
   - OR click the **checkbox in the header** to select all applicants on the page

![Checkbox Column]
```
☑️ | NO. | APPLICANT NO. | FULL NAME
☑️ |  1  | 2025-0023     | CHRISTIAN REY YAP ALEGRE
☑️ |  2  | 0-25-1-88946  | GABRIEL LOMACO ABRIL
☐  |  3  | 0-25-1-06909  | DANIELLE ANGELO ARREZA
```

### Step 2: Bulk Actions Toolbar Appears

When you select one or more applicants:
- A **blue toolbar** appears showing: "**X selected**"
- You'll see three buttons:
  - **Generate Codes** (blue)
  - **Send Exam Notifications** (green) ← Click this!
  - **Export** (gray)

### Step 3: Fill in Exam Details

A drawer slides in from the right with these fields:

**Required Fields:**
- ✅ **Exam Date** - Select the date of the examination
- ✅ **Exam Time** - Select the start time (e.g., 9:00 AM)

**Optional Fields:**
- 📍 **Exam Venue/Room** - e.g., "Computer Laboratory 1" or "Room 203"
- 📝 **Special Instructions** - Custom notes for applicants (multi-line)

### Step 4: Send Notifications

1. Click **"Send Email Notifications"** button
2. System validates:
   - ✅ All selected applicants have access codes
   - ✅ All have valid email addresses
3. Success message shows: "Email notifications sent successfully to X applicant(s)."
4. If some fail, you'll see: "X applicant(s). Y failed." with details

---

## Method 2: Individual Notification (Single Applicant)

### Step 1: Hover Over Applicant Row

1. Go to **Applicants** page
2. **Hover your mouse** over any applicant row
3. **Floating action buttons** appear on the right:
   - 👁️ View
   - ✏️ Edit
   - 📧 **Send Notification** ← Click this!
   - 🗑️ Delete

### Step 2: Fill in Exam Details

Same drawer opens as bulk method, but with only that applicant selected.

### Step 3: Send

Click **"Send Email Notifications"** and the email goes to just that one applicant.

---

## What Gets Sent in the Email?

Each applicant receives a professional email containing:

### Header Section
- EnrollAssess logo and branding
- "BSIT Entrance Examination" title
- Computer Studies Department

### Personalized Information
- ✅ Applicant's full name: "Dear [Name],"
- ✅ Application number
- ✅ Exam date: "January 15, 2024" (formatted)
- ✅ Exam time: "9:00 AM" (12-hour format)
- ✅ Exam venue: Your custom text or "To Be Announced"

### Access Code Box
- 🔑 Their unique access code (large, highlighted)
- Security reminder text

### Special Instructions (if you provided them)
- 📋 Blue box with your custom instructions
- Supports multiple lines and formatting

### Exam Portal Link
- 🔗 Green button linking to `/applicant/login`
- Direct URL shown below button

### Standard Instructions
- ⚠️ Yellow box with important reminders:
  - Arrive 15 minutes early
  - Bring valid ID
  - Stable internet required
  - Desktop/laptop only
  - Fullscreen mode
  - No page refresh
  - Complete in one sitting

---

## Requirements & Validation

### Before sending, applicants MUST have:

1. ✅ **Access Code Generated**
   - If missing: Email will NOT be sent
   - Error message: "Applicant [Name] does not have an access code."

2. ✅ **Valid Email Address**
   - If missing: Email will NOT be sent
   - Error message: "Applicant [Name] does not have an email address."

### What happens if requirements aren't met?

The system will:
- Skip applicants without access codes or emails
- Send to all qualified applicants
- Show you a summary:
  - ✅ Success count: "Sent to 8 applicants"
  - ❌ Failed count: "2 failed"
  - 📋 Error list with specific reasons

---

## Example Workflow

### Scenario: Send exam notifications for tomorrow's test

1. **Select applicants** who need to take the exam (check their boxes)
2. **Click** "Send Exam Notifications" (green button)
3. **Fill in details:**
   - Date: Tomorrow's date
   - Time: 9:00 AM
   - Venue: "Computer Laboratory 1, 3rd Floor"
   - Instructions:
     ```
     Important reminders:
     - Bring your ID and calculator
     - Arrive 15 minutes early
     - No phones allowed in the exam room
     ```
4. **Click** "Send Email Notifications"
5. **Review** the success message
6. **Check** mail logs if needed: `storage/logs/laravel.log`

---

## Tips & Best Practices

### ✅ DO:
- Generate access codes BEFORE sending notifications
- Double-check exam date and time
- Provide clear venue information
- Include special instructions if there are room changes
- Send notifications at least 24 hours before the exam
- Use the individual notification for last-minute changes

### ❌ DON'T:
- Select applicants without access codes (they'll be skipped)
- Leave venue blank if you know the location
- Send duplicate notifications (confuses applicants)
- Use special characters that might break email formatting

### 📧 Email Best Practices:
- **Venue Examples:**
  - ✅ "Computer Laboratory 1, Building A, 3rd Floor"
  - ✅ "Room 203, ICT Building"
  - ✅ "Main Campus, CS Department Lab"
  - ❌ "Lab" (too vague)

- **Instructions Examples:**
  - ✅ Multi-line, clear bullet points
  - ✅ Specific requirements or changes
  - ✅ Contact information for questions
  - ❌ Very long paragraphs (hard to read)

---

## Troubleshooting

### "I don't see checkboxes in the table"
- **Solution:** Hard refresh the page (Ctrl+Shift+R)
- Clear browser cache if needed

### "Bulk actions toolbar doesn't appear"
- **Solution:** Make sure you've selected at least one checkbox
- Check browser console for JavaScript errors (F12)

### "Send Exam Notifications button is grayed out"
- **Solution:** You must select at least one applicant first

### "Email sent but applicant didn't receive it"
- **Check:** Applicant's spam/junk folder
- **Check:** Email address is correct in database
- **Check:** Mail logs: `storage/logs/laravel.log`
- **Verify:** Mail configuration is working

### "Some emails failed to send"
- **Review:** The error messages in the response
- **Common reasons:**
  - Missing access code
  - Invalid email address
  - Mail server issues
- **Fix:** Generate access codes, update emails, retry

### "Drawer doesn't open"
- **Check:** Browser console for errors (F12)
- **Solution:** Hard refresh page (Ctrl+Shift+R)
- **Verify:** JavaScript files loaded correctly

---

## Visual Guide

### Location of Features

```
┌─────────────────────────────────────────────────┐
│ Applicants Management                           │
├─────────────────────────────────────────────────┤
│ Search: [_______] [Search] [Filters] [Buttons]  │
├─────────────────────────────────────────────────┤
│ ┌─────────────────────────────────────────────┐ │
│ │ 3 selected                                  │ │ ← Bulk Actions
│ │ [Generate Codes] [Send Notifications] [Export] │
│ └─────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────┤
│ ☑️ | NO | NAME        | EMAIL       | STATUS   │ ← Table with checkboxes
│ ☑️ |  1 | John Doe    | john@...    | PENDING  │
│ ☑️ |  2 | Jane Smith  | jane@...    | PENDING  │
│ ☐  |  3 | Bob Wilson  | bob@...     | ADMITTED │
└─────────────────────────────────────────────────┘
```

### Drawer Appearance

```
┌─────────────────────┬──────────────────────┐
│ Applicants Table    │                      │
│ [Regular content]   │  ┌────────────────┐  │
│                     │  │ Send Exam      │  │ ← Drawer slides in
│          [Dark      │  │ Notifications  │  │   from right
│           Overlay]  │  │                │  │
│                     │  │ [Form fields]  │  │
│                     │  │                │  │
│                     │  │ [Cancel][Send] │  │
│                     │  └────────────────┘  │
└─────────────────────┴──────────────────────┘
```

---

## Summary

**To send exam notifications:**

1. ✅ Select applicants (checkboxes)
2. ✅ Click "Send Exam Notifications" (green button)
3. ✅ Fill in date, time, venue, instructions
4. ✅ Click "Send Email Notifications"
5. ✅ Review success/failure summary

**For individual notifications:**
- Hover → Click 📧 icon → Fill form → Send

**Requirements:**
- Access codes must be generated
- Valid email addresses required

---

**Questions or issues?** Check the browser console (F12) or mail logs for debugging.

