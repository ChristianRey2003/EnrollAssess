# Exam Settings Drawer Implementation

## Summary

Successfully implemented a comprehensive "Edit Exam Settings" feature on the Question Bank page (`/admin/sets-questions`) with a right-side drawer interface that allows editing exam configuration without full page reloads.

---

## Implementation Details

### 1. Database Schema

**Migration: `2025_10_08_210000_add_availability_window_to_exams_table.php`**
- Added `starts_at` (timestamp, nullable) - Exam availability start time
- Added `ends_at` (timestamp, nullable) - Exam availability end time

**Existing columns (already in place):**
- `duration_minutes` - Exam duration
- `total_items` - Total number of questions per exam
- `mcq_quota` - Number of multiple choice questions
- `tf_quota` - Number of true/false questions
- `is_active` - Boolean flag for exam status

### 2. Model Updates

**`app/Models/Exam.php`**
- Added `starts_at` and `ends_at` to `$fillable` array
- Added datetime casts for both fields
- Existing validation methods (`validateQuotas()`, `hasEnoughQuestions()`) support new fields

### 3. Controller Updates

**`app/Http/Controllers/ExamController.php` - `update()` method**

Comprehensive validation rules:
- `duration_minutes`: nullable|integer|min:1|max:600 (up to 10 hours)
- `total_items`: nullable|integer|min:1
- `mcq_quota`: nullable|integer|min:0
- `tf_quota`: nullable|integer|min:0
- `is_active`: nullable|boolean
- `starts_at`: nullable|date
- `ends_at`: nullable|date|after_or_equal:starts_at

Custom validation:
- **Quota sum validation**: `mcq_quota + tf_quota ≤ total_items`
- **Available questions check**: Verifies sufficient questions exist for quotas
- **MCQ availability**: Ensures MCQ quota doesn't exceed available MCQ questions
- **T/F availability**: Ensures T/F quota doesn't exceed available T/F questions

Response format:
- Returns JSON with updated exam data including `formatted_duration`
- Returns validation errors with field-specific messages (422 status)
- Supports partial updates (only provided fields are updated)

### 4. UI Updates

**`resources/views/admin/sets-questions.blade.php`**

#### Header Changes
- Changed "Edit" button to "Edit Settings" button
- Button triggers `openEditSettingsDrawer()` function

#### New Settings Drawer
Added a right-side drawer (`#settingsDrawer`) with:

**Form Fields:**
- Duration (minutes) - required, min: 1, max: 600
- Total Items - required, min: 1
- MCQ Quota - optional, min: 0
- True/False Quota - optional, min: 0
- Active toggle - checkbox
- Availability Start - datetime-local input
- Availability End - datetime-local input

**Features:**
- Real-time inline validation error display
- Error highlighting on invalid fields
- Info note about quota constraints
- Responsive design (full-height on mobile)
- Sticky footer with actions always visible

#### CSS Additions
```css
.error-message - Red error text (12px)
.form-control.error - Red border for invalid fields
@media (max-width: 768px) - Mobile responsive drawer
```

### 5. JavaScript Functionality

**New Functions:**

1. **`openEditSettingsDrawer()`**
   - Clears previous errors
   - Opens drawer with overlay

2. **`closeSettingsDrawer()`**
   - Closes drawer
   - Clears all error messages

3. **`clearSettingsErrors()`**
   - Removes all error messages
   - Removes error styling from inputs

4. **`showFieldError(fieldName, message)`**
   - Displays error message under specific field
   - Adds error styling to input

5. **`saveSettings()`**
   - Collects form data
   - Sends PUT request to `/admin/exams/{id}`
   - Handles validation errors (displays inline)
   - On success: updates UI without reload
   - Disables save button during submission

6. **`updateExamInfoDisplay(exam)`**
   - Updates header stats (duration, total items, quotas)
   - Updates form values for next drawer open
   - Handles datetime format conversion

7. **`formatDateTimeLocal(date)`**
   - Converts Date object to `YYYY-MM-DDTHH:mm` format
   - For datetime-local input compatibility

**Event Handlers:**
- **Overlay click**: Closes drawer when clicking outside
- **Esc key**: Closes drawer
- **Form submission**: Prevented (uses AJAX instead)

### 6. Routes

**Existing route (reused):**
```php
PUT /admin/exams/{id}
Middleware: role:department-head,administrator
Controller: ExamController@update
Route name: admin.exams.update
```

### 7. Testing

**`tests/Feature/ExamSettingsUpdateTest.php`**

13 comprehensive tests covering:
- ✅ Successful update with valid data
- ✅ Duration validation (positive integers)
- ✅ Total items validation (positive integers)
- ✅ Quota sum validation (cannot exceed total)
- ✅ MCQ quota vs available questions
- ✅ T/F quota vs available questions
- ✅ End date must be after start date
- ✅ Availability window updates
- ✅ Clearing availability window (set to null)
- ✅ Response includes formatted_duration
- ✅ Authentication required
- ✅ Role-based authorization (dept-head/admin only)
- ✅ Partial updates supported

### 8. Migration Fixes

Fixed legacy migration issues to support fresh database migrations:

**`database/migrations/2025_08_05_133658_create_applicants_table.php`**
- Removed `exam_set_id` foreign key (no longer used)
- Comment added explaining direct instructor assignment

**`database/migrations/2025_08_05_133729_create_questions_table.php`**
- Removed `exam_id` from base migration
- Relies on refactoring migration to add it
- Comment added explaining the approach

---

## User Flow

### Opening the Drawer
1. User clicks "Edit Settings" button on Question Bank page
2. Right-side drawer slides in with dimmed overlay
3. Current exam settings populate the form fields
4. Datetime fields show in local timezone format

### Making Changes
1. User modifies any field(s)
2. Form validates on submission (not on blur)
3. Client sends JSON payload to server
4. Save button shows "Saving..." and disables

### Success Path
1. Server validates and updates exam record
2. Returns updated exam object with formatted values
3. Drawer closes
4. Header stats update instantly (no reload)
5. Success alert shown
6. Form values updated for next open

### Error Path
1. Server returns 422 with validation errors object
2. Errors displayed inline under relevant fields
3. Fields highlighted in red
4. Save button re-enables
5. Drawer remains open for corrections

### Cancel/Dismiss
- Click Cancel button
- Click overlay outside drawer
- Press Esc key
→ All close drawer with no changes

---

## API Request/Response Examples

### Request
```http
PUT /admin/exams/1
Content-Type: application/json
X-CSRF-TOKEN: {token}

{
  "duration_minutes": 90,
  "total_items": 60,
  "mcq_quota": 40,
  "tf_quota": 20,
  "is_active": true,
  "starts_at": "2025-12-01T08:00",
  "ends_at": "2025-12-31T23:59"
}
```

### Success Response (200)
```json
{
  "success": true,
  "message": "Exam settings updated successfully!",
  "exam": {
    "exam_id": 1,
    "title": "BSIT Exam",
    "duration_minutes": 90,
    "formatted_duration": "1h 30m",
    "total_items": 60,
    "mcq_quota": 40,
    "tf_quota": 20,
    "is_active": true,
    "starts_at": "2025-12-01T08:00:00.000000Z",
    "ends_at": "2025-12-31T23:59:00.000000Z",
    "created_at": "...",
    "updated_at": "..."
  }
}
```

### Validation Error Response (422)
```json
{
  "success": false,
  "message": "The sum of MCQ and True/False quotas cannot exceed total items.",
  "errors": {
    "total_items": [
      "The sum of MCQ and True/False quotas cannot exceed total items."
    ]
  }
}
```

---

## Business Logic Guardrails

1. **Quota Validation**
   - Server enforces `mcq_quota + tf_quota ≤ total_items`
   - Checks available questions match quotas
   - Prevents invalid exam configurations

2. **Availability Window**
   - `ends_at` must be after or equal to `starts_at`
   - Both fields are optional (nullable)
   - Can be set or cleared independently

3. **Question Bank Check**
   - Before saving quotas, verifies sufficient active questions exist
   - Prevents quotas that cannot be fulfilled during exam generation

4. **Partial Updates**
   - Only provided fields are updated
   - Omitted fields retain current values
   - Supports incremental configuration

---

## Future Enhancements (Not Implemented)

These can be added later:
- Live quota validation hints (show available questions count)
- Preview of exam generation with current settings
- Bulk edit for multiple exams
- Exam schedule calendar view
- History of settings changes
- Warning before activating exam if quotas invalid
- Auto-calculate total_items from quotas

---

## Files Modified

### Created
- `database/migrations/2025_10_08_210000_add_availability_window_to_exams_table.php`
- `tests/Feature/ExamSettingsUpdateTest.php`
- `EXAM_SETTINGS_DRAWER_IMPLEMENTATION.md` (this document)

### Modified
- `app/Models/Exam.php`
- `app/Http/Controllers/ExamController.php`
- `resources/views/admin/sets-questions.blade.php`
- `database/migrations/2025_08_05_133658_create_applicants_table.php` (migration fix)
- `database/migrations/2025_08_05_133729_create_questions_table.php` (migration fix)

### Routes Used
- `PUT /admin/exams/{id}` (existing route, no changes)

---

## Testing Instructions

### Manual Testing
1. Navigate to `/admin/sets-questions`
2. Click "Edit Settings" button
3. Modify any field(s) and save
4. Verify header updates without reload
5. Try invalid data (quotas > total, end < start)
6. Verify inline error messages appear

### Automated Testing
```bash
php artisan test --filter=ExamSettingsUpdateTest
```

Expected: 13 tests passing

---

## Notes

- **Migration Order**: Base migrations now create minimal tables; refactoring migration adds relationships
- **Backward Compatible**: Existing exams work without `starts_at`/`ends_at` (nullable)
- **Mobile Friendly**: Drawer becomes full-height sheet on small screens
- **Accessibility**: Form labels properly associated, keyboard navigation (Esc to close)
- **Error Handling**: Both client and server validation with clear messaging
- **Performance**: No full page reload, instant UI updates via AJAX

---

## Conclusion

The Edit Exam Settings drawer provides a smooth, modern UX for managing exam configuration directly from the Question Bank page. All validation rules ensure data integrity, and the implementation follows the existing codebase patterns for consistency.

