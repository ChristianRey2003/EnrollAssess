# Phase 2 Testing Guide 🧪

## Quick Tests to Verify Everything Works

---

## ✅ Test 1: Routes are Registered

```bash
php artisan route:clear
php artisan route:list --name=admin.analytics
php artisan route:list --path=api/dashboard
```

**Expected Output:**
- Should show 8 analytics routes
- Should show 4 dashboard API routes
- No errors

**Status:** ✅ PASS (if you see the routes)

---

## ✅ Test 2: Dashboard Loads

1. Start server: `php artisan serve`
2. Visit: `http://localhost:8000/admin/dashboard`
3. Wait 30 seconds
4. Open browser console (F12)

**Expected:**
- Dashboard loads without errors
- Stats display correctly
- After 30s, console shows: "Refreshing stats..."
- Numbers may update with animation

**What to Check:**
- No JavaScript errors in console
- Stats cards visible
- "Last updated" timestamp appears

---

## ✅ Test 3: Analytics Dashboard

1. Visit: `http://localhost:8000/admin/analytics`
2. Wait for charts to load

**Expected:**
- Page loads without errors
- Multiple chart containers visible
- Charts render (may take 3-5 seconds)
- No console errors

**What to Check:**
- At least 7 chart containers
- Loading spinners appear then disappear
- Charts display (if you have data)
- Period selector works

---

## ✅ Test 4: API Endpoints

Test each API endpoint:

```bash
# Test stats endpoint
curl http://localhost:8000/admin/api/dashboard/stats

# Expected: JSON with statistics
```

Or use browser:
- Visit: `http://localhost:8000/admin/api/dashboard/stats`
- Should see JSON response

---

## ✅ Test 5: Real-time Updates (Polling)

1. Open dashboard in browser
2. Open browser console (F12)
3. Watch console for 60 seconds

**Expected Output in Console:**
```
Initializing Dashboard Manager...
Broadcasting enabled: false
Using polling for updates (every 30 seconds)...
```

Then after 30 seconds:
```
(Stats data logged)
```

**Status:** ✅ PASS if you see updates every 30 seconds

---

## ✅ Test 6: Chart Data

Visit each chart endpoint in browser:

1. `http://localhost:8000/admin/analytics/score-distribution?type=exam`
2. `http://localhost:8000/admin/analytics/conversion-funnel`
3. `http://localhost:8000/admin/analytics/instructor-workload`

**Expected:**
- JSON responses with `success: true`
- Data arrays (may be empty if no data in database)

---

## ✅ Test 7: Notifications Table

```bash
php artisan tinker
```

Then in tinker:
```php
// Check if table exists
Schema::hasTable('notifications')
// Should return: true

// Check structure
DB::select("DESCRIBE notifications")
// Should show columns: id, type, notifiable_type, notifiable_id, data, read_at, created_at, updated_at

exit
```

---

## ✅ Test 8: Assets Built

Check if files exist:
```bash
ls public/build/assets/dashboard*
ls public/build/assets/charts*
ls public/build/assets/chart-*
```

**Expected:**
- `dashboard-*.js` file exists
- `charts-*.js` file exists
- `chart-*.js` file exists (Chart.js library)

**Status:** ✅ PASS (files exist from previous build)

---

## 🚀 Test 9: Create Test Data

If you don't have data for charts:

```bash
php artisan tinker
```

```php
// Create test applicants
use App\Models\Applicant;

for ($i = 0; $i < 20; $i++) {
    Applicant::create([
        'first_name' => 'Test',
        'last_name' => 'User ' . $i,
        'email' => 'test' . $i . '@example.com',
        'status' => ['pending', 'exam-completed', 'admitted', 'rejected'][rand(0, 3)],
        'created_at' => now()->subDays(rand(1, 30)),
    ]);
}

exit
```

Then refresh analytics page - charts should now show data!

---

## 🐛 Common Issues & Solutions

### Issue 1: Route Not Defined Error

**Error:** `Route [admin.analytics.index] not defined`

**Solution:**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

Then refresh browser.

---

### Issue 2: Charts Not Loading

**Symptoms:** Charts show loading spinner forever

**Check:**
1. Browser console for errors
2. API endpoints return data:
   ```
   http://localhost:8000/admin/api/dashboard/charts/applicants
   ```

**Solution:**
- Verify database has some data
- Check `storage/logs/laravel.log` for errors
- Ensure Chart.js loaded: Open console and type `Chart`

---

### Issue 3: Stats Not Updating

**Symptoms:** Dashboard loads but stats don't update after 30s

**Check:**
1. Browser console (F12)
2. Look for: "Initializing Dashboard Manager..."
3. Network tab - check for API calls every 30s

**Solution:**
- Hard refresh: Ctrl+Shift+R
- Check if JavaScript loaded correctly
- Verify `dashboard-*.js` file exists in `public/build/assets/`

---

### Issue 4: Notification Errors

**Error:** Something about notifications table

**Solution:**
```bash
php artisan migrate
```

Or if already migrated:
```bash
php artisan migrate:status
```

Check if `2025_10_25_123007_create_notifications_table` shows "Ran"

---

### Issue 5: Charts Show "No Data"

**This is Normal!** If database is empty or has very little data.

**Solutions:**
1. Run seeders: `php artisan db:seed`
2. Create test data (see Test 9 above)
3. Import real applicant data
4. Use the system normally - data will accumulate

---

## ✅ Success Checklist

Mark each as complete:

- [ ] Routes registered (Test 1)
- [ ] Dashboard loads (Test 2)
- [ ] Analytics page loads (Test 3)
- [ ] API endpoints work (Test 4)
- [ ] Polling updates work (Test 5)
- [ ] Chart data returns (Test 6)
- [ ] Notifications table exists (Test 7)
- [ ] Assets built (Test 8)
- [ ] Test data created (Test 9 - optional)

---

## 🎯 If All Tests Pass

**Congratulations!** Phase 2 is working perfectly!

You can now:
1. Use the live dashboard
2. View analytics and charts
3. Deploy to production
4. Optionally enable Pusher for real-time

---

## 📞 Still Having Issues?

### Check Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan optimize:clear
```

### Rebuild Assets
```bash
npm run build
php artisan config:clear
```

### Verify Installation
```bash
# Check if packages installed
npm list laravel-echo pusher-js chart.js
composer show pusher/pusher-php-server
```

---

## 🚀 Next Steps

If all tests pass:

1. **Test with Real Data**
   - Import applicants
   - Create exams
   - Schedule interviews
   - Watch analytics populate

2. **Deploy to Production**
   - Follow `DIGITAL_OCEAN_SETUP.md`

3. **Enable Real-time (Optional)**
   - Follow `BROADCASTING_SETUP.md`

4. **Customize**
   - Add more charts
   - Adjust refresh intervals
   - Customize notifications

---

**Happy Testing! 🎉**

