# Single Active Exam Implementation

## Overview
The system now enforces **one active exam at a time** (per semester) with database-level constraint and improved UX messaging.

## What Changed

### 1. Database Constraint (Migration)
**File:** `database/migrations/2025_11_05_155318_enforce_single_active_exam_constraint.php`

- Added a **unique partial index** on the `exams` table
- Uses a generated column `active_flag` that is:
  - `1` when `is_active = true`
  - `NULL` when `is_active = false`
- MySQL unique indexes ignore NULL values, so only one row can have `active_flag = 1`
- **Result:** Database prevents multiple active exams (constraint error if violated)

### 2. Controller Updates
**File:** `app/Http/Controllers/SetsQuestionsController.php`

#### `index()` method
- Always shows the single active exam
- Falls back to latest exam if none is active
- Comment clarifies "single active exam mode"

#### `newSemester()` method
- Archives current active exam (sets `is_active = false`)
- Creates new exam as **draft** (`is_active = false`)
- User must explicitly publish when ready
- Improved success message: "Review questions and publish when ready"

#### `publishExam()` method
- Wrapped in transaction for safety
- Deactivates all other exams
- Activates the selected exam
- Comment clarifies "single active exam" enforcement
- Success message: "This is now the active exam for applicants"

### 3. View/UI Updates
**File:** `resources/views/admin/sets-questions.blade.php`

#### Button Labels
- Changed "New Exam" → **"New Semester"**
- Clarifies this is for semester turnover, not arbitrary exam creation

#### Modal Title
- "Create New Exam" → **"Create New Semester Exam"**

#### Warning Message (in modal)
```
Important: Creating a new exam will archive the current active exam. 
Only one exam can be active at a time (per semester). 
The new exam starts as a draft - publish it when ready.
```

#### Publish Function
- **Fixed URL mismatch:** `/admin/sets-questions/publish-exam/${id}` → `/admin/sets-questions/${id}/publish`
- Now matches route definition: `Route::post('/{id}/publish', ...)`
- Improved confirmation message: "...and deactivate any previous exam"

## How It Works

### Semester Workflow
1. **Start of semester:** Admin clicks "New Semester"
2. **Current exam archived:** Previous active exam set to `is_active = false`
3. **New exam created as draft:** New exam starts with `is_active = false`
4. **Admin builds question bank:** Add/edit/review questions
5. **Publish when ready:** Admin clicks "Publish" button
6. **Single active exam enforced:** DB constraint + transaction ensure only one active

### Database Enforcement
- Trying to set two exams to active will fail with MySQL error
- Transaction in `publishExam()` ensures atomic swap
- Migration auto-fixed existing data (set most recent as active)

### What About Old Exams?
- **They're preserved** for audit/history
- `is_active = false` but data remains in database
- Can be manually archived with "Archive Old Exams" button (marks exams >6 months as archived)
- Can be queried for reports, comparisons, etc.

## Testing the Constraint

### Manual Test (Tinker)
```bash
php artisan tinker
```

```php
// Should succeed: only one active at a time
$exam1 = \App\Models\Exam::first();
$exam1->update(['is_active' => true]);

// Should fail with constraint error:
$exam2 = \App\Models\Exam::skip(1)->first();
$exam2->update(['is_active' => true]);
```

Expected error: `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'unique_active_exam'`

## Benefits

1. **Data integrity:** Database prevents accidental multi-active state
2. **Clear mental model:** "One semester, one exam"
3. **Audit trail:** History preserved for reports and analysis
4. **Safe transitions:** Transaction-wrapped publish prevents race conditions
5. **Better UX:** Clear labels and warnings explain behavior

## Migration Notes

- **Safe to run:** Migration auto-fixes existing data
- **Rollback:** `php artisan migrate:rollback` removes constraint
- **Production:** Test on staging first; migration sets most recent exam as active

## Future Enhancements (Optional)

- Add "Switch to Draft" button to unpublish active exam
- Show inactive exams in a separate "History" view
- Add "Clone from previous semester" quick action
- Automatic archiving scheduler (background job)

## Current State

After implementation:
- ✅ Database constraint enforced
- ✅ Controller logic updated
- ✅ UI labels clarified
- ✅ Publish URL fixed
- ✅ Warning messages added
- ✅ Migration applied successfully

**Active exams:** 1  
**Total exams:** 2  
**Status:** Working as intended

