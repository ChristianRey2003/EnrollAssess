# Basic Information Form - Testing Guide

## Quick Start Testing

### Prerequisites
1. Database migration completed: `php artisan migrate`
2. Active exam exists in the system
3. Test applicant with valid access code

### Test Flow (Happy Path)

1. **Access Code Entry**
   - Navigate to applicant login: `/`
   - Enter valid access code
   - Should redirect to pre-requirements

2. **Pre-Requirements Page**
   - Review instructions
   - Check all 3 checkboxes
   - "Continue" button should enable
   - Click "Continue"
   - Should redirect to `/exam/basic-info`

3. **Basic Information Form**
   - Verify progress indicator shows step 3 active
   - Fill in all required fields:
     - Sex: Select any option
     - Date of Birth: Select a date (try: 2005-01-15)
     - Age: Should auto-fill to 20 (verify it works)
     - Complete Address: Enter test address
     - Province: Select "Leyte"
     - City: Should show Leyte cities, select "Ormoc City"
     - Senior High School Strand: Select "STEM"
     - Senior High School Name: Enter "Test High School"
   - "Proceed to Exam" button should enable
   - Click "Proceed to Exam"
   - Should redirect to `/exam/start-form`

4. **Exam Start Page**
   - Verify progress indicator shows step 4 active
   - Should show welcome message with applicant name
   - Should show exam information
   - Click "Start Exam"
   - Should redirect to exam interface

### Test Scenarios

#### Scenario 1: Age Auto-Calculation
**Test:** Age automatically calculates from date of birth
- Fill Date of Birth: `2005-06-15` (person born June 15, 2005)
- Age dropdown should auto-select: `20` (current age)
- Verify calculation is correct

#### Scenario 2: Province/City Filtering
**Test:** City dropdown filters based on province
- Select Province: "Leyte"
- City dropdown should show: Ormoc City, Baybay City, Tacloban, etc.
- Select Province: "Metro Manila"
- City dropdown should show: Manila, Quezon City, Makati City, etc.

#### Scenario 3: Strand "Others" Field
**Test:** Specification field appears when "Others" is selected
- Select Strand: "ABM"
- Specification field should be hidden
- Select Strand: "Others"
- Specification field should appear and be required
- Enter: "Arts and Design"
- Form should be valid

#### Scenario 4: Form Validation
**Test:** Cannot submit incomplete form
- Leave any required field empty
- "Proceed to Exam" button should remain disabled
- Fill all required fields
- Button should enable

#### Scenario 5: Skip If Already Completed
**Test:** Cannot fill form twice
- Complete basic info form once
- Navigate to `/exam/basic-info` again
- Should redirect to `/exam/start-form` with info message

#### Scenario 6: Cannot Skip Basic Info
**Test:** Cannot start exam without basic info
- Fresh applicant session (new access code)
- Skip pre-requirements and basic info
- Try to access `/exam/start-form` or call `/exam/start` endpoint
- Should redirect to `/exam/basic-info` with error message

### Validation Testing

#### Required Fields
Test each required field by leaving it empty:
- Sex
- Date of Birth
- Age
- Complete Address
- Province
- City/Municipality
- Senior High School Strand
- Senior High School Name

**Expected:** Button stays disabled, red border on empty fields

#### Optional Fields
Leave these empty and verify form can still submit:
- Civil Status

#### Date of Birth Validation
Test invalid dates:
- Future date: Tomorrow
  - **Expected:** Browser validation error or backend error
- Very old date: 150 years ago
  - **Expected:** Backend validation error

#### Age Validation
Test invalid ages:
- Age < 16: Select age 15
  - **Expected:** Backend validation error
- Age > 99: Select age 100
  - **Expected:** Backend validation error

#### Province/City Validation
Test mismatched combinations:
- Province: "Leyte"
- City: "Quezon City" (Manila city)
  - **Expected:** Backend validation error

### Edge Cases

#### Case 1: Back Button
- Complete form halfway
- Click "Back" button
- Should go to previous page
- Return to form
- Data should persist (from old() values if validation failed)

#### Case 2: Browser Refresh
- Fill form completely
- Refresh page before submitting
- Form should be empty (no data saved)

#### Case 3: Direct URL Access
- Try to access `/exam/start-form` without completing basic info
  - **Expected:** Redirect to `/exam/basic-info`
- Try to access `/exam/basic-info` without access code
  - **Expected:** Redirect to login

#### Case 4: Duplicate Submission
- Submit form
- Use browser back button
- Submit again
  - **Expected:** Should update existing record or skip

### Mobile Testing

Test on mobile devices or responsive mode:
- Form should be single column on small screens
- Date picker should use native mobile picker
- Dropdowns should use native mobile dropdowns
- Buttons should be full width on mobile
- All text should be readable
- Progress indicator should shrink appropriately

### Browser Testing

Test on different browsers:
- Chrome
- Firefox
- Safari
- Edge

Verify:
- Date picker works
- Dropdowns work
- Form validation works
- Styling is consistent

### Performance Testing

1. **Page Load Time**
   - Form should load in < 2 seconds
   - Dropdowns should populate instantly

2. **Form Submission**
   - Should complete in < 1 second
   - Should show loading state on button

3. **Age Calculation**
   - Should calculate instantly on date change
   - No noticeable lag

### Security Testing

1. **CSRF Protection**
   - Remove CSRF token from form
   - Try to submit
   - **Expected:** 419 error

2. **Session Validation**
   - Clear session
   - Try to access `/exam/basic-info`
   - **Expected:** Redirect to login

3. **SQL Injection**
   - Enter SQL in text fields: `'; DROP TABLE applicants; --`
   - **Expected:** Safely stored as text

4. **XSS Attack**
   - Enter script in text field: `<script>alert('xss')</script>`
   - **Expected:** Escaped and safely stored

### Accessibility Testing

1. **Keyboard Navigation**
   - Tab through all fields
   - Should move in logical order
   - Should be able to select all options

2. **Screen Reader**
   - Test with screen reader (if available)
   - Labels should be read correctly
   - Error messages should be announced

3. **Color Contrast**
   - Verify text is readable
   - Error messages are clear
   - Disabled state is obvious

### Database Verification

After completing form, check database:

```sql
SELECT * FROM applicant_basic_infos WHERE applicant_id = [test_applicant_id];
```

Verify:
- All fields saved correctly
- `completed_at` is set
- Timestamps are correct
- Relationships work

### Error Handling

1. **Network Error During Submit**
   - Disconnect network
   - Try to submit
   - **Expected:** Error message, form data preserved

2. **Server Error**
   - Cause server error (invalid database connection)
   - Try to submit
   - **Expected:** Graceful error message

3. **Validation Errors**
   - Submit invalid data
   - **Expected:** Stay on form with error messages

### Integration Testing

1. **Full Flow**
   - Complete entire flow from access code to exam
   - Verify data persists at each step
   - Verify redirects work correctly

2. **Admin View**
   - Complete basic info as applicant
   - Log in as admin
   - View applicant details
   - Verify basic info is accessible (if implemented)

### Regression Testing

Verify existing functionality still works:
- Access code verification
- Pre-requirements page
- Exam interface
- Exam submission
- Results display

### Common Issues to Check

1. **Age doesn't auto-fill**
   - Check browser console for JavaScript errors
   - Verify date format is correct
   - Check if age dropdown has options 16-99

2. **Cities don't load**
   - Check province is selected
   - Check browser console for errors
   - Verify province name matches data exactly

3. **Button stays disabled**
   - Check all required fields are filled
   - Check strand "Others" specification if applicable
   - Check browser console for JavaScript errors

4. **Form doesn't submit**
   - Check CSRF token is present
   - Check network tab for errors
   - Verify backend validation rules

5. **Redirect doesn't work**
   - Check session has applicant_id
   - Check basic info saved successfully
   - Check routes are registered

### Quick Test Checklist

- [ ] Form displays correctly
- [ ] All fields present and labeled
- [ ] Progress indicator shows step 3
- [ ] Age auto-calculates from DOB
- [ ] Cities filter by province
- [ ] Strand "Others" shows specification field
- [ ] Button disabled until form complete
- [ ] Form submits successfully
- [ ] Redirects to exam start page
- [ ] Cannot skip basic info
- [ ] Cannot fill form twice
- [ ] Data saves to database correctly
- [ ] Validation works on all fields
- [ ] Error messages display correctly
- [ ] Mobile responsive
- [ ] Works in all browsers

### Test Data

**Valid Test Data:**
```
Sex: Male
Date of Birth: 2005-06-15
Age: 20 (auto-filled)
Civil Status: Single
Complete Address: 123 Test Street, Barangay Test, Ormoc City
Province: Leyte
City: Ormoc City
Senior High School Strand: STEM
Senior High School Name: Eastern Visayas State University - Ormoc Campus
```

**Valid Test Data (Others Strand):**
```
Sex: Female
Date of Birth: 2004-03-20
Age: 21
Civil Status: (leave empty)
Complete Address: 456 Sample Ave, Brgy. Sample, Tacloban City
Province: Leyte
City: Tacloban
Senior High School Strand: Others
Strand Specification: Arts and Design Track
Senior High School Name: Sample National High School
```

### Expected Results Summary

1. **Successful Submission:**
   - Form data saved to database
   - `completed_at` timestamp set
   - Redirect to `/exam/start-form`
   - Success message displayed

2. **Skip on Repeat:**
   - If already completed, redirect immediately
   - Info message: "Already completed"

3. **Cannot Skip:**
   - Cannot access exam without completion
   - Redirect back to form if missing

4. **Validation Errors:**
   - Stay on form
   - Show error messages
   - Highlight invalid fields
   - Preserve entered data

### Support

If issues are found:
1. Check browser console for JavaScript errors
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify database migration ran: Check `applicant_basic_infos` table exists
4. Verify routes registered: `php artisan route:list --name=exam`
5. Clear cache: `php artisan cache:clear`

### Reporting Issues

When reporting issues, include:
- Browser and version
- Steps to reproduce
- Expected vs actual behavior
- Screenshots if applicable
- Console errors if any
- Network errors if any

### Success Criteria

All tests pass when:
- Form displays correctly on all devices
- All validations work as expected
- Data saves correctly to database
- Flow integration works seamlessly
- No JavaScript errors
- No backend errors
- Good user experience maintained

