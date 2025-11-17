# Exam Recovery/Resume Implementation

**Date:** January 2025  
**Feature:** Exam Recovery After PC Shutdown  
**Status:** ✅ Complete

---

## Overview

This implementation adds the ability for applicants to **resume their exam** after a PC shutdown, browser crash, or network interruption. Previously, exam progress was only stored in browser session, which would be lost in these scenarios.

---

## Problem Solved

### Before Implementation
- ❌ Exam session stored only in browser session
- ❌ PC shutdown = lost progress
- ❌ Browser crash = lost progress  
- ❌ Network interruption = lost progress
- ❌ Applicant must restart exam from beginning

### After Implementation
- ✅ Exam attempts stored in database
- ✅ Auto-save functionality persists answers
- ✅ Automatic recovery on page reload
- ✅ Time remaining calculated from original start time
- ✅ Applicant can resume exactly where they left off

---

## Implementation Details

### 1. Database Schema

**Table:** `exam_attempts`

```sql
- attempt_id (primary key)
- applicant_id (foreign key)
- exam_id (foreign key)
- attempt_token (UUID, unique)
- question_ids (JSON array)
- started_at (timestamp)
- duration_minutes (integer)
- answers (JSON object)
- current_section (integer)
- sections_completed (JSON array)
- status (enum: in_progress, completed, expired, abandoned)
- violation_count (integer)
- last_activity_at (timestamp)
- completed_at (timestamp, nullable)
- created_at, updated_at
```

**Indexes:**
- `applicant_id + status` (for quick lookup)
- `exam_id + status` (for exam analytics)
- `attempt_token` (for tracking)
- `last_activity_at` (for cleanup)

### 2. Model: ExamAttempt

**Location:** `app/Models/ExamAttempt.php`

**Key Methods:**
- `isExpired()` - Check if attempt exceeded time limit
- `canBeResumed()` - Check if attempt can be resumed
- `getTimeRemainingAttribute()` - Calculate remaining time
- `saveAnswers(array $answers)` - Save answers to database
- `markAsCompleted()` - Mark attempt as completed
- `markAsExpired()` - Mark attempt as expired
- `updateActivity()` - Update last activity timestamp
- `getActiveAttempt($applicantId, $examId)` - Static method to find active attempt

### 3. Controller Updates

#### ExamController

**Changes:**

1. **`startExam()` method:**
   - Checks for existing active attempt before creating new one
   - Creates `ExamAttempt` record in database
   - Stores `attempt_id` and `attempt_token` in session

2. **`getExamInterface()` method:**
   - If session is lost, attempts to recover from database
   - Restores exam session from `ExamAttempt` record
   - Updates last activity timestamp

3. **`submitSection()` method:**
   - Saves answers to database attempt
   - Updates section progress in database

4. **`autoSave()` method (NEW):**
   - Endpoint for periodic auto-save
   - Saves answers to database every 30 seconds (client-side)
   - Updates violation count if provided

#### ExamSubmissionController

**Changes:**

1. **`completeExam()` method:**
   - Attempts to recover from database if session lost
   - Uses existing `attempt_token` from database attempt
   - Marks `ExamAttempt` as completed
   - Handles submission even if session expired

### 4. Routes

**New Route:**
```php
Route::post('/exam/auto-save', [ExamController::class, 'autoSave'])
    ->name('exam.auto-save')
    ->middleware(['no.cache']);
```

---

## How It Works

### Starting an Exam

1. Applicant clicks "Start Exam"
2. System checks for existing active attempt
3. If found and not expired → Resume existing attempt
4. If not found → Create new `ExamAttempt` record
5. Store `attempt_id` and `attempt_token` in session
6. Redirect to exam interface

### During Exam

1. **Auto-save (every 30 seconds):**
   - Client sends answers to `/exam/auto-save`
   - Server saves to `ExamAttempt.answers` JSON field
   - Updates `last_activity_at` timestamp

2. **Section submission:**
   - Answers saved to database immediately
   - Section progress updated
   - `current_section` incremented

3. **Time remaining:**
   - Calculated from `started_at` + `duration_minutes`
   - Uses database attempt if available
   - Falls back to session if needed

### Recovery After Shutdown

1. Applicant logs back in with access code
2. System checks for active `ExamAttempt` in database
3. If found and not expired:
   - Restore exam session from database
   - Load saved answers
   - Restore section progress
   - Calculate remaining time from original start
4. If expired:
   - Mark attempt as expired
   - Prevent resume
   - Require new exam start

### Completing Exam

1. Submit final answers
2. System recovers from database if session lost
3. Calculate score
4. Mark `ExamAttempt` as completed
5. Store results in `results` table
6. Clear session

---

## Frontend Integration

### Auto-Save Implementation

The frontend should call auto-save periodically:

```javascript
// Auto-save every 30 seconds
setInterval(function() {
    fetch('/exam/auto-save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            answers: currentAnswers,
            violation_count: violationCount
        })
    });
}, 30000);
```

### Resume Detection

The frontend can detect if exam was resumed:

```javascript
// Check if exam was resumed
if (examSession.resumed) {
    showNotification('Resuming your previous exam attempt...');
}
```

---

## Data Flow

### Normal Flow (No Interruption)
```
Start Exam → Create Attempt → Auto-save → Submit Section → Complete → Mark Completed
```

### Recovery Flow (After Shutdown)
```
Shutdown → Login → Check Database → Restore Attempt → Continue → Complete
```

---

## Edge Cases Handled

1. **Multiple Active Attempts:**
   - System prevents multiple active attempts per applicant
   - Uses `latest('started_at')` to get most recent

2. **Expired Attempts:**
   - Automatically marked as expired
   - Cannot be resumed
   - Applicant must start new exam

3. **Session Loss During Submission:**
   - Recovery from database attempt
   - Uses stored `attempt_token`
   - Completes submission successfully

4. **Time Calculation:**
   - Always uses original `started_at` from database
   - Prevents time manipulation
   - Accurate remaining time calculation

---

## Database Queries

### Find Active Attempt
```php
ExamAttempt::where('applicant_id', $applicantId)
    ->where('exam_id', $examId)
    ->where('status', 'in_progress')
    ->latest('started_at')
    ->first();
```

### Save Answers
```php
$attempt->saveAnswers($answers);
// Updates: answers (merged), last_activity_at
```

### Mark Completed
```php
$attempt->markAsCompleted();
// Updates: status = 'completed', completed_at = now()
```

---

## Testing Scenarios

### Test 1: Normal Exam Flow
1. Start exam
2. Answer questions
3. Submit sections
4. Complete exam
5. ✅ Verify attempt marked as completed

### Test 2: PC Shutdown Recovery
1. Start exam
2. Answer some questions
3. Simulate shutdown (close browser)
4. Log back in
5. ✅ Verify exam resumes with saved answers

### Test 3: Expired Attempt
1. Start exam
2. Wait for time to expire
3. Try to resume
4. ✅ Verify attempt marked as expired
5. ✅ Verify new exam must be started

### Test 4: Auto-Save
1. Start exam
2. Answer questions
3. Wait 30+ seconds
4. Check database
5. ✅ Verify answers saved in `exam_attempts.answers`

### Test 5: Session Loss During Submission
1. Start exam
2. Answer all questions
3. Clear session (simulate)
4. Submit exam
5. ✅ Verify recovery from database
6. ✅ Verify successful submission

---

## Performance Considerations

1. **JSON Storage:**
   - Answers stored as JSON for flexibility
   - Efficient for read/write operations
   - No need for separate answer table

2. **Indexes:**
   - Indexed on `applicant_id + status` for fast lookup
   - Indexed on `last_activity_at` for cleanup queries

3. **Auto-Save Frequency:**
   - 30 seconds balance between data safety and server load
   - Can be adjusted based on needs

4. **Cleanup:**
   - Expired attempts can be cleaned up periodically
   - Completed attempts can be archived
   - Abandoned attempts (no activity for 24h) can be marked

---

## Future Enhancements

1. **Real-time Sync:**
   - WebSocket for real-time answer sync
   - Immediate save on answer change

2. **Attempt History:**
   - View previous attempts
   - Compare attempts
   - Analytics on attempt patterns

3. **Admin Dashboard:**
   - View active attempts
   - Monitor exam progress
   - Identify stuck attempts

4. **Cleanup Job:**
   - Scheduled job to mark expired attempts
   - Archive old completed attempts
   - Clean up abandoned attempts

---

## Migration

**File:** `database/migrations/2025_11_16_122044_create_exam_attempts_table.php`

**Run:**
```bash
php artisan migrate
```

**Rollback:**
```bash
php artisan migrate:rollback --step=1
```

---

## Files Modified

1. ✅ `database/migrations/2025_11_16_122044_create_exam_attempts_table.php` (NEW)
2. ✅ `app/Models/ExamAttempt.php` (NEW)
3. ✅ `app/Http/Controllers/ExamController.php` (UPDATED)
4. ✅ `app/Http/Controllers/ExamSubmissionController.php` (UPDATED)
5. ✅ `routes/public.php` (UPDATED)

---

## Summary

✅ **Database persistence** - Exam attempts stored in database  
✅ **Auto-save** - Answers saved periodically  
✅ **Recovery** - Automatic resume after shutdown  
✅ **Time tracking** - Accurate time remaining calculation  
✅ **Session recovery** - Handles session loss gracefully  
✅ **Expiration handling** - Prevents resuming expired attempts  

**Status:** Production Ready ✅

---

**Last Updated:** January 2025

