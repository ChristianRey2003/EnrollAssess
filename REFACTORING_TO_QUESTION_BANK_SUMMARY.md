# System Refactoring: Question Bank & Direct Instructor Assignment

## Overview
This document summarizes the major refactoring changes to the EnrollAssess system based on stakeholder requirements:

1. **Removed Exam Sets** - Replaced with a direct Question Bank approach
2. **Removed Interview Pool** - Replaced with direct instructor assignment by admin

---

## Key Changes

### 1. Database Schema Changes

#### Migration: `2025_10_08_200000_refactor_to_question_bank_and_direct_assignment.php`

**Questions Table:**
- ✅ Removed `exam_set_id` foreign key
- ✅ Added `exam_id` foreign key (questions now belong directly to exams)
- ✅ Data migration: Moved existing questions from exam_sets to exams

**Applicants Table:**
- ✅ Removed `exam_set_id` foreign key  
- ✅ Added `assigned_instructor_id` foreign key (direct instructor assignment)

**Interviews Table:**
- ✅ Removed pool-related columns: `pool_status`, `claimed_by`, `claimed_at`, `priority_level`, `dh_override`, `assignment_notes`
- ✅ Updated `status` enum to: `['scheduled', 'completed', 'cancelled', 'rescheduled']`
- ✅ Made `interviewer_id` NOT NULL (always assigned by admin)

**Removed Tables:**
- ✅ `exam_sets` - No longer needed
- ✅ `exam_assignments` - Replaced with on-the-fly random selection
- ✅ `exam_assignment_questions` - No longer needed

---

### 2. Model Updates

#### Exam Model (`app/Models/Exam.php`)
- ✅ Removed `examSets()` relationship
- ✅ Added `questions()` relationship (direct to questions)
- ✅ Added `activeQuestions()`, `multipleChoiceQuestions()`, `trueFalseQuestions()`
- ✅ Added helper methods: `hasEnoughQuestions()`, `validateQuotas()`
- ✅ Added attributes: `total_questions`, `mcq_count`, `tf_count`

#### Question Model (`app/Models/Question.php`)
- ✅ Changed `exam_set_id` to `exam_id` in fillable
- ✅ Replaced `examSet()` relationship with `exam()`
- ✅ Removed `examAssignmentQuestions()` relationship

#### Applicant Model (`app/Models/Applicant.php`)
- ✅ Changed `exam_set_id` to `assigned_instructor_id` in fillable
- ✅ Replaced `examSet()` relationship with `assignedInstructor()`
- ✅ Removed `examAssignments()` and `latestExamAssignment()` relationships
- ✅ Updated `getExamPercentageAttribute()` to use `enrollassess_score` directly

#### Interview Model (`app/Models/Interview.php`)
- ✅ Removed all pool-related fields from fillable
- ✅ Removed pool-related casts (`claimed_at`, `dh_override`)
- ✅ Removed `claimedBy()` relationship
- ✅ Removed all pool management scopes and methods
- ✅ Kept core interview functionality (scheduling, completion, ratings)

---

### 3. Services

#### QuestionSelectionService (`app/Services/QuestionSelectionService.php`)
**Complete rewrite** for simpler on-the-fly random selection:
- ✅ `selectQuestionsForApplicant()` - Selects random questions per student using seeded randomization
- ✅ `validateExamConfiguration()` - Validates exam has enough questions
- ✅ `getShuffledOptions()` - Returns shuffled MCQ options per student
- ✅ `getQuestionBankStats()` - Returns question bank statistics

**Key Features:**
- Uses `exam_id` and `applicant_id` as seed for consistent per-student randomization
- No database persistence of assignments (questions selected on exam start)
- Supports MCQ and T/F only (as per requirements)
- Quota enforcement (MCQ quota + T/F quota = total items)

---

### 4. Controllers (To be updated)

#### ApplicantController
- [ ] TODO: Add `bulkAssignInstructors()` method for assigning applicants to instructors
- [ ] TODO: Remove exam set assignment logic

#### ExamController  
- [ ] TODO: Update to work with question bank (direct questions under exam)
- [ ] TODO: Add question bank management methods

#### InstructorController
- [ ] TODO: Add `scheduleInterview()` method
- [ ] TODO: Add `sendInterviewNotification()` method  
- [ ] TODO: Add `rescheduleInterview()` method
- [ ] TODO: Remove pool-related methods

---

### 5. Routes

#### Admin Routes (`routes/admin.php`)
**Removed:**
- ✅ ExamSetController routes
- ✅ ExamAssignmentController routes
- ✅ Interview pool routes
- ✅ Exam set assignment routes

**Added:**
- ✅ `POST /applicants/bulk/assign-instructors` - Bulk assign applicants to instructors

**Simplified:**
- ✅ Removed pool override routes
- ✅ Streamlined interview routes (removed claim/release)

#### Instructor Routes (`routes/instructor.php`)
**Removed:**
- ✅ Interview pool routes (`/interview-pool/*`)

**Added:**
- ✅ `POST /interviews/{interview}/schedule` - Schedule interview
- ✅ `POST /interviews/{interview}/send-notification` - Send email notification
- ✅ `POST /interviews/{interview}/reschedule` - Reschedule interview

---

### 6. Mail

#### New: InterviewScheduleMail (`app/Mail/InterviewScheduleMail.php`)
- ✅ Created for instructor-sent interview notifications
- ✅ Email view: `resources/views/emails/interview-schedule.blade.php`
- ✅ Includes applicant info, schedule details, interviewer name

---

### 7. Deleted Files

**Controllers:**
- ✅ `app/Http/Controllers/ExamSetController.php`
- ✅ `app/Http/Controllers/ExamAssignmentController.php`

**Models:**
- ✅ `app/Models/ExamSet.php`
- ✅ `app/Models/ExamAssignment.php`
- ✅ `app/Models/ExamAssignmentQuestion.php`

**Views:**
- ✅ `resources/views/admin/exam-assignments/index.blade.php`
- ✅ `resources/views/admin/exam-assignments/show.blade.php`
- ✅ `resources/views/admin/applicants/assign-exam-sets.blade.php`
- ✅ `resources/views/admin/applicants/assign-exams.blade.php`
- ✅ `resources/views/instructor/interview-pool.blade.php`

---

### 8. Views (To be created/updated)

#### Admin Views
- [ ] TODO: Question bank management UI (manage questions under exam)
- [ ] TODO: Instructor assignment UI (assign applicants to instructors)
- [ ] TODO: Update applicant list to show assigned instructor
- [ ] TODO: Update Sets & Questions page to remove Sets, show question bank

#### Instructor Views
- [ ] TODO: Interview scheduling UI (for assigned applicants)
- [ ] TODO: Email notification UI (send interview invites)
- [ ] TODO: Update dashboard to show assigned applicants

---

### 9. Exam Interface

**Changes needed:**
- [ ] TODO: Update to use `QuestionSelectionService::selectQuestionsForApplicant()`
- [ ] TODO: Remove exam assignment lookup
- [ ] TODO: Generate questions on-the-fly when student starts exam
- [ ] TODO: Use seeded randomization for consistent question sets per student

---

### 10. Tests

**To be updated:**
- [ ] TODO: Update `DualExamFlowTest.php` for question bank flow
- [ ] TODO: Update `ExamAssignmentControllerTest.php` or remove it
- [ ] TODO: Update `QuestionSelectionServiceTest.php` for new service methods
- [ ] TODO: Create tests for instructor assignment workflow
- [ ] TODO: Create tests for random question selection consistency

---

## New Workflow

### Exam Creation & Management
1. Admin creates an Exam with:
   - `total_items` (e.g., 30)
   - `mcq_quota` (e.g., 20)
   - `tf_quota` (e.g., 10)

2. Admin adds questions directly to the exam (no sets):
   - Questions are added with `exam_id`
   - System validates there are enough questions for quotas

3. When student starts exam:
   - System selects random M questions from N total
   - Uses `exam_id + applicant_id` as seed for consistency
   - Questions shuffled and presented to student

### Applicant & Interview Workflow
1. Admin imports applicants
2. Admin assigns applicants directly to instructors
   - Bulk assign: Select applicants → assign to instructor
   - Creates Interview record with `interviewer_id` set

3. Instructor views assigned applicants
4. Instructor schedules interview (sets date/time)
5. Instructor sends email notification to applicant
6. Instructor conducts interview and submits ratings

---

## Migration Steps

### To apply changes:
```bash
# Run the new migration
php artisan migrate

# This will:
# 1. Move questions from exam_sets to exams
# 2. Drop exam_sets table
# 3. Drop exam_assignments tables
# 4. Add assigned_instructor_id to applicants
# 5. Remove pool columns from interviews
```

### Rollback (if needed):
```bash
php artisan migrate:rollback

# This will restore:
# - exam_sets table
# - exam_assignments tables
# - Pool columns in interviews
# - Original foreign keys
```

---

## Benefits

### Question Bank Approach
✅ **Simpler** - No need to manage sets
✅ **Fairer** - Each student gets unique random questions
✅ **Flexible** - Easy to add/remove questions from pool
✅ **Efficient** - No database storage of assignments
✅ **Consistent** - Seeded randomization ensures same questions per student

### Direct Instructor Assignment
✅ **Clear ownership** - Each applicant has assigned instructor
✅ **Simpler workflow** - No claiming/releasing complexity
✅ **Better tracking** - Easy to see instructor workload
✅ **Matches school protocol** - Admin assigns, instructor schedules

---

## Next Steps

1. ✅ Run migration
2. [ ] Update controllers (ApplicantController, ExamController, InstructorController)
3. [ ] Create/update admin views for question bank and instructor assignment
4. [ ] Create/update instructor views for scheduling and notifications
5. [ ] Update exam interface to use random selection
6. [ ] Update tests
7. [ ] Test end-to-end workflow
8. [ ] Deploy to production

---

## Files Modified

**Migrations:**
- `database/migrations/2025_10_08_200000_refactor_to_question_bank_and_direct_assignment.php` (new)

**Models:**
- `app/Models/Exam.php` (updated)
- `app/Models/Question.php` (updated)
- `app/Models/Applicant.php` (updated)
- `app/Models/Interview.php` (updated)

**Services:**
- `app/Services/QuestionSelectionService.php` (rewritten)

**Mail:**
- `app/Mail/InterviewScheduleMail.php` (new)

**Views:**
- `resources/views/emails/interview-schedule.blade.php` (new)

**Routes:**
- `routes/admin.php` (updated)
- `routes/instructor.php` (updated)

**Documentation:**
- `REFACTORING_TO_QUESTION_BANK_SUMMARY.md` (this file)

---

## Breaking Changes

⚠️ **Warning**: This is a major refactoring that breaks backward compatibility.

**Removed Features:**
- Exam Sets management
- Exam Assignments persistence
- Interview Pool (claiming/releasing)
- Set-based question organization

**Data Migration:**
- Existing questions will be automatically moved from sets to exams
- Existing exam_set_id in applicants will be dropped (data loss)
- Existing pool-related interview data will be dropped (data loss)

**Recommendation**: Backup database before migration.

---

*Last Updated: October 8, 2025*

