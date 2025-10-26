# Analytics Database Column Fix ✅

## Problem

**Error:** `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'percentage' in 'field list'`

**Location:** `/admin/analytics-dashboard`

**Cause:** The AnalyticsController was trying to query a `percentage` column from the `results` table, but that column doesn't exist.

---

## Database Structure

### Results Table
The `results` table stores **individual question answers**, not exam scores:

**Columns:**
- `result_id`
- `applicant_id`
- `question_id`
- `answer_text`
- `selected_option_id`
- `is_correct`
- `points_earned` ← Per-question points, not total score
- `answered_at`

### Applicants Table
The **exam scores** are actually stored in the `applicants` table:

**Score Columns:**
- `enrollassess_score` ← Exam score (0-100)
- `interview_score` ← Interview score
- `score` ← Legacy/external score field

---

## What Was Fixed

Updated `app/Http/Controllers/AnalyticsController.php` to query the correct table and column:

### 1. Average Exam Score (Line 44)
**Before:**
```php
$avgExamScore = Result::avg('percentage') ?? 0;
```

**After:**
```php
$avgExamScore = Applicant::whereNotNull('enrollassess_score')
    ->avg('enrollassess_score') ?? 0;
```

### 2. Score Distribution (Line 67-74)
**Before:**
```php
$results = Result::select(
    DB::raw('FLOOR(percentage / 10) * 10 as score_range'),
    DB::raw('COUNT(*) as count')
)
->groupBy('score_range')
->orderBy('score_range')
->get();
```

**After:**
```php
$results = Applicant::select(
    DB::raw('FLOOR(enrollassess_score / 10) * 10 as score_range'),
    DB::raw('COUNT(*) as count')
)
->whereNotNull('enrollassess_score')
->groupBy('score_range')
->orderBy('score_range')
->get();
```

### 3. Performance Trends (Line 131-139)
**Before:**
```php
$examScores = Result::select(
    DB::raw('DATE(created_at) as date'),
    DB::raw('AVG(percentage) as avg_score')
)
->where('created_at', '>=', $startDate)
->groupBy('date')
->orderBy('date')
->get();
```

**After:**
```php
$examScores = Applicant::select(
    DB::raw('DATE(updated_at) as date'),
    DB::raw('AVG(enrollassess_score) as avg_score')
)
->whereNotNull('enrollassess_score')
->where('updated_at', '>=', $startDate)
->groupBy('date')
->orderBy('date')
->get();
```

---

## Why This Happened

The analytics controller was written based on a generic assumption that exam results would have a `percentage` column. However, your system's database schema stores:

1. **Individual answer data** in `results` table (per-question)
2. **Total exam scores** in `applicants` table (overall score)

This is actually a **better design** because:
- Allows detailed question-level analysis
- Keeps applicant data centralized
- Supports multiple scoring methods

---

## Testing

### 1. Clear Caches
```bash
php artisan optimize:clear
```

### 2. Visit Analytics Dashboard
```
http://enrollassess.test/admin/analytics-dashboard
```

**Expected:** Page should load without errors now!

### 3. Check Charts
The following charts should now work:
- ✅ Overview statistics
- ✅ Score distribution histogram
- ✅ Performance trends over time
- ✅ All other charts

---

## What Data Will Show

### If You Have Applicants with Scores
- Charts will display actual data
- Score distributions will show ranges
- Trends will show over time

### If Database is Empty/No Scores
- Charts will show "No data available"
- Empty state is normal for fresh installations
- Add test data to see charts populate

---

## Adding Test Data (Optional)

If you want to see charts with data:

```bash
php artisan tinker
```

```php
// Update some applicants with exam scores
use App\Models\Applicant;

$applicants = Applicant::limit(20)->get();

foreach ($applicants as $applicant) {
    $applicant->enrollassess_score = rand(65, 98);
    $applicant->save();
}

echo "Updated " . $applicants->count() . " applicants with scores\n";

exit
```

Then refresh the analytics dashboard - charts should now show data!

---

## Current Status

✅ **FIXED**: Database column issue resolved  
✅ **Using**: Correct `applicants.enrollassess_score` column  
✅ **Queries**: All analytics queries updated  
✅ **Charts**: Should now render without errors  

---

## Files Modified

1. `app/Http/Controllers/AnalyticsController.php`
   - Fixed `getOverviewStats()` method
   - Fixed `getScoreDistribution()` method  
   - Fixed `getPerformanceTrends()` method

---

## Next Steps

1. **Visit the analytics page** - Should work now!
2. **Add some exam scores** - To see charts populate
3. **Test all charts** - Verify data displays correctly

---

## Additional Fix: Interview Scores

### Second Issue Found
**Error:** `Column 'total_score' not found in interviews table`

The interviews table uses `overall_score`, not `total_score`.

### Fixed References:
1. ✅ `AnalyticsController::getOverviewStats()` - Changed to `overall_score`
2. ✅ `AnalyticsController::getScoreDistribution()` - Changed to `overall_score`
3. ✅ `AnalyticsController::getPerformanceTrends()` - Changed to `overall_score`
4. ✅ `AnalyticsController::getInstructorWorkload()` - Changed to `overall_score`
5. ✅ `InterviewCompleted` event - Changed to `overall_score`

### Interview Scoring System
The interviews table has 8 individual rubric criteria (0-10 each):
- communication_skills
- motivation_interest
- problem_solving_attitude
- program_understanding
- personality_attitude
- it_background
- willingness_to_learn
- overall_impression

These are summed into `overall_score` (max 80 points).

---

## Summary

Two column name mismatches were fixed:
1. ✅ Exam scores: `results.percentage` → `applicants.enrollassess_score`
2. ✅ Interview scores: `interviews.total_score` → `interviews.overall_score`

**Both issues are now completely fixed!** 🎉

Visit: http://enrollassess.test/admin/analytics-dashboard

