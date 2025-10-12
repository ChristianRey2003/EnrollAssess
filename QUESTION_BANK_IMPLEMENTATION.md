# Question Bank Implementation Summary

## Overview
Implemented a comprehensive question bank system with per-student exam generation, replacing the fixed exam set approach while maintaining backward compatibility with legacy flows.

## Database Changes

### New Tables
1. **exam_assignments**
   - Tracks per-student exam instances
   - Fields: `id`, `exam_id`, `applicant_id`, `status`, `generated_at`, `timestamps`
   - Unique constraint on `(exam_id, applicant_id)`
   - Status: `pending`, `in_progress`, `completed`, `expired`

2. **exam_assignment_questions**
   - Stores the generated question set for each assignment
   - Fields: `id`, `exam_assignment_id`, `question_id`, `position`, `option_order` (JSON), `timestamps`
   - Persists MCQ option shuffle per question
   - Index on `(exam_assignment_id, position)`

### Modified Tables
1. **exams**
   - Added: `total_items` (int, nullable) - number of questions to generate
   - Added: `mcq_quota` (int, nullable) - optional MCQ question count
   - Added: `tf_quota` (int, nullable) - optional True/False question count
   - Quota validation: `mcq_quota + tf_quota ≤ total_items`

2. **questions**
   - Made `exam_set_id` nullable (migration handles existing data)
   - Added: `correct_answer` (boolean, nullable) - for new-style TF questions
   - Legacy TF using two options still supported

## New Models

### ExamAssignment
- Relationships: `exam()`, `applicant()`, `assignedQuestions()`, `questions()`
- Scopes: `pending()`, `inProgress()`, `completed()`, `expired()`
- Methods: `isGenerated()`, `markAsStarted()`, `markAsCompleted()`, `markAsExpired()`

### ExamAssignmentQuestion
- Relationships: `examAssignment()`, `question()`
- Methods: `getShuffledOptions()` - returns options in persisted shuffle order
- Scopes: `ordered()`

## Services

### QuestionSelectionService
**Core Methods:**
- `generateAssignment(examId, applicantId)` - Generate per-student exam, idempotent
- `selectQuestions(totalItems, mcqQuota?, tfQuota?)` - Select from bank with quota enforcement
- `generateBulkAssignments(examId, applicantIds[])` - Batch generation
- `regenerateAssignment(assignmentId)` - Admin regeneration (only if not completed)
- `getAssignmentForExam(examId, applicantId, autoGenerate)` - Retrieve/generate for exam start
- `validateExamConfiguration(examId)` - Pre-flight validation

**Features:**
- Quota enforcement (MCQ/TF counts)
- Randomization (questions shuffled, MCQ options shuffled per question)
- Idempotent (won't regenerate if already exists)
- Transactional (atomic assignment creation)
- Validation (sufficient questions in bank, quotas valid)

## Controllers

### ExamAssignmentController (NEW)
- `showAssignForm()` - Show assignment UI
- `assignExam()` - Assign exams to applicants (replaces `assignExamSets`)
- `index()` - List all assignments with filters
- `show(assignmentId)` - View assignment details
- `regenerate(assignmentId)` - Regenerate questions (admin)
- `destroy(assignmentId)` - Delete assignment (not if completed)

### ExamController (MODIFIED)
**Updated Methods:**
- `startExam()` - Now checks `latestExamAssignment` first, falls back to `examSet` (legacy)
  - `startExamFromAssignment()` - New flow: load from exam_assignments
  - `startExamFromExamSet()` - Legacy flow: unchanged behavior
- `getExamInterface()` - Detects `use_assignment` flag in session
  - `getExamInterfaceFromAssignment()` - Render from assigned questions with shuffled options
  - `getExamInterfaceFromExamSet()` - Legacy rendering

**Session Structure (New Flow):**
```php
[
    'applicant_id' => int,
    'exam_assignment_id' => int,
    'use_assignment' => true,
    'started_at' => timestamp,
    'duration_minutes' => int,
    'current_section' => 0,
    'sections_completed' => [],
    'answers' => []
]
```

**Session Structure (Legacy Flow):**
```php
[
    'applicant_id' => int,
    'exam_set_id' => int,
    'use_assignment' => false,
    'started_at' => timestamp,
    'duration_minutes' => int,
    'current_section' => 0,
    'sections_completed' => [],
    'answers' => []
]
```

## Routes

### New Routes (admin.php)
```php
// Question Bank Assignments
Route::prefix('exam-assignments')->name('exam-assignments.')->group(function () {
    Route::get('/', 'ExamAssignmentController@index')->name('index');
    Route::get('/assign', 'ExamAssignmentController@showAssignForm')->name('assign-form');
    Route::post('/assign', 'ExamAssignmentController@assignExam')->name('assign');
    Route::get('/{assignmentId}', 'ExamAssignmentController@show')->name('show');
    Route::post('/{assignmentId}/regenerate', 'ExamAssignmentController@regenerate')->name('regenerate');
    Route::delete('/{assignmentId}', 'ExamAssignmentController@destroy')->name('destroy');
});
```

### Deprecated Routes (Graceful)
- `/admin/applicants/bulk/assign-exam-sets` → Returns 410 Gone with redirect to new endpoint
- `/admin/applicants/assign-exam-sets` → Redirects to `/admin/exam-assignments/assign`
- Legacy routes return clear deprecation messages

## Migration Path

### For New Deployments
1. Run migration: `php artisan migrate`
2. Configure exams with `total_items` and optional quotas
3. Use `/admin/exam-assignments/assign` to assign exams
4. Applicants with assignments use new flow automatically

### For Existing Deployments
1. **Backward Compatible**: Applicants with `exam_set_id` still work via legacy flow
2. **Gradual Migration**:
   - Existing applicants: Continue using `exam_set_id` until exam completed
   - New applicants: Assigned via new question bank flow
3. **Manual Backfill** (optional):
   - Create `exam_assignments` for existing applicants if needed
   - Populate from their current `exam_set` questions

### Testing Both Flows
- **Legacy Test**: Assign `exam_set_id` to applicant → start exam → renders from exam set
- **New Test**: Create exam assignment → applicant starts → renders from assignment with shuffled options

## Question Management Updates (TODO)

### Planned UI Changes
1. **Hide Essay/Short Answer** in creation UI (no deletion, just hide UI)
2. **TF Toggle Option**: Allow `correct_answer` boolean instead of two options
3. **Bank-Wide Questions**: Indicate questions are no longer tied to specific sets

## Interview Pool Removal (TODO)

### Planned Changes
1. Remove pool claiming UI and logic
2. Admin directly assigns `interviewer_id` to interviews
3. Keep pool columns for history, don't drop yet
4. Update interview assignment workflow to direct assignment

## Validation & Guards

### QuestionSelectionService Validation
- ✅ Exam must have `total_items` configured
- ✅ Quota sum cannot exceed `total_items`
- ✅ Sufficient MCQ questions if `mcq_quota` set
- ✅ Sufficient TF questions if `tf_quota` set
- ✅ Sufficient total questions in bank
- ✅ Cannot regenerate completed assignments

### Assignment Flow
- ✅ Idempotent: won't duplicate assignments for same (exam_id, applicant_id)
- ✅ Transactional: all questions persisted atomically
- ✅ Stable shuffle: same shuffled options every time applicant renders

## Key Benefits

1. **True Randomization**: Each applicant gets different questions
2. **Quota Control**: Enforce question type distribution
3. **Stable Experience**: Once generated, questions and order don't change
4. **Non-Breaking**: Legacy flows continue to work
5. **Scalable**: No need to create N exam sets for randomization
6. **Analytics-Ready**: Track which questions each applicant saw

## Files Modified

### New Files
- `database/migrations/2025_10_08_100000_add_question_bank_features.php`
- `app/Models/ExamAssignment.php`
- `app/Models/ExamAssignmentQuestion.php`
- `app/Services/QuestionSelectionService.php`
- `app/Http/Controllers/ExamAssignmentController.php`
- `QUESTION_BANK_IMPLEMENTATION.md` (this file)

### Modified Files
- `app/Models/Exam.php` - Added relationships, fillable columns
- `app/Models/Applicant.php` - Added `examAssignments()`, `latestExamAssignment()` relationships
- `app/Models/Question.php` - Added `correct_answer`, `examAssignmentQuestions()` relationship
- `app/Http/Controllers/ExamController.php` - Dual flow support in `startExam()` and `getExamInterface()`
- `routes/admin.php` - New routes, deprecated old routes with graceful errors

## Next Steps

1. ✅ Migrations created and run
2. ✅ Models and relationships
3. ✅ Service layer with validation
4. ✅ Controller endpoints
5. ✅ Routes configured
6. ⏳ Create admin UI for exam assignment
7. ⏳ Update question management UI (hide essay, add TF toggle)
8. ⏳ Remove interview pool dependencies
9. ⏳ Test both legacy and new flows
10. ⏳ Documentation for admins

## Testing Checklist

### New Flow Tests
- [ ] Validate exam configuration before assignment
- [ ] Generate assignment for single applicant
- [ ] Generate bulk assignments
- [ ] Verify question randomization (different per applicant)
- [ ] Verify MCQ option shuffle persistence
- [ ] Start exam from assignment
- [ ] Resume exam from assignment (same questions, same order)
- [ ] Complete exam, mark assignment as completed
- [ ] Attempt to regenerate completed assignment (should fail)
- [ ] Insufficient questions error handling
- [ ] Quota validation errors

### Legacy Flow Tests
- [ ] Applicant with `exam_set_id` can start exam
- [ ] Legacy flow renders correctly
- [ ] Legacy flow completion works
- [ ] No interference between flows

### Edge Cases
- [ ] Applicant with both `exam_set_id` and `exam_assignment` (assignment takes precedence)
- [ ] Exam with no `total_items` (falls back to exam set if available)
- [ ] Empty question bank
- [ ] Quotas exceed available questions

## Rollback Plan

If issues arise:
1. Stop using new assignment endpoint
2. Continue with legacy `exam_set_id` flow (unaffected)
3. Optional: Run down migration to remove new tables
4. No data loss: legacy data untouched

---

**Implementation Date**: October 8, 2025  
**Migration Status**: ✅ Completed  
**Backward Compatibility**: ✅ Maintained  
**Production Ready**: ⏳ Pending UI and final testing

