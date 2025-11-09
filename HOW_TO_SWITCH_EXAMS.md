# How to Switch Between Exams

## Current Situation
You have 2 exams:
- **Exam 1** (inactive) - your original exam
- **Exam 2** (active) - currently showing in Question Bank

## To Switch Back to Exam 1

### Method 1: Publish Exam 1 (Recommended)
Since there's no UI selector yet, you can manually activate exam 1:

1. Open browser console (F12) on the Question Bank page
2. Run this JavaScript:
```javascript
fetch('/admin/sets-questions/1/publish', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    'Content-Type': 'application/json'
  }
})
.then(r => r.json())
.then(data => {
  alert(data.message);
  location.reload();
});
```

### Method 2: Database Update
Using Tinker:
```bash
php artisan tinker
```

```php
DB::transaction(function() {
    \App\Models\Exam::where('is_active', true)->update(['is_active' => false]);
    \App\Models\Exam::where('exam_id', 1)->update(['is_active' => true]);
});
```

### Method 3: SQL (Direct)
If you have database access:
```sql
UPDATE exams SET is_active = 0;
UPDATE exams SET is_active = 1 WHERE exam_id = 1;
```

## Understanding "Publish"
- **Publish** = make this exam the active one for applicants
- It automatically deactivates all other exams
- The database constraint ensures only one can be active
- This is safe to do even if applicants are online

## After Switching
- Refresh the Question Bank page
- You'll see "exam 1" in the header
- All questions from exam 1 will display
- Exam 2 is now archived (inactive) but still exists

## Best Practice
Since you only need one exam per semester:
1. Keep the **current semester's exam** active
2. Use "New Semester" button when transitioning to a new semester
3. Old exams remain in database for history/reports
4. Use "Publish" to control which exam applicants see

