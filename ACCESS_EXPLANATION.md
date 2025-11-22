# Understanding Access Control in EnrollAssess

## Overview

Your system has **TWO COMPLETELY SEPARATE** authentication/access systems that cannot access each other:

1. **Admin Login** (`/admin/login`) - For staff (Department Heads, Instructors)
2. **Applicant Access** (`/` or `/applicant/login`) - For students taking the exam

---

## 🔐 How Admin Login Works

### Route: `http://enrollassess.test/admin/login`

**Authentication Method:** Username + Password

**How it works:**
- Uses Laravel's built-in `Auth` system
- Users are stored in the `users` table
- Requires a `User` record with role: `department-head` or `instructor`
- Uses `Auth::login()` and `Auth::check()` for session management

**Protected Routes:**
- All routes under `/admin/*` require admin authentication
- Protected by `ajax.auth` middleware and `role` middleware
- Checks: `Auth::check()` → must be logged in as admin
- Checks: `$user->role` → must be `department-head` or `instructor`

**Creating Admin Accounts:**
1. **Via Admin Panel** (Department Head only):
   - Go to: `/admin/users`
   - Click "Create New User"
   - Fill in: Username, Full Name, Email, Role, Password
   - Roles: `department-head`, `administrator`, or `instructor`

2. **Via Database/Seeder:**
   ```php
   User::create([
       'username' => 'admin_username',
       'password_hash' => Hash::make('password'),
       'full_name' => 'Admin Name',
       'email' => 'admin@example.com',
       'role' => 'department-head', // or 'instructor'
   ]);
   ```

3. **Via Artisan Tinker:**
   ```bash
   php artisan tinker
   ```
   ```php
   $user = new \App\Models\User();
   $user->username = 'new_admin';
   $user->password_hash = \Illuminate\Support\Facades\Hash::make('password123');
   $user->full_name = 'Admin Name';
   $user->email = 'admin@example.com';
   $user->role = 'department-head';
   $user->save();
   ```

---

## 👥 How Applicant Access Works

### Route: `http://enrollassess.test/` (redirects to `/applicant/login`)

**Authentication Method:** Access Code (e.g., `BSIT-12345`)

**How it works:**
- Does NOT use Laravel's `Auth` system
- Uses session storage instead (`$request->session()`)
- Stores `applicant_id` and `access_code` in session
- Checks: `session('applicant_id')` → must have valid session

**Protected Routes:**
- Routes check for `session('applicant_id')` instead of `Auth::check()`
- If no session, redirects to `/applicant/login`
- Routes like `/exam`, `/exam/start`, etc. check session data

**Creating Applicant Access Codes:**
1. **Via Admin Panel:**
   - Go to: `/admin/applicants`
   - Create or import applicants
   - Go to: `/admin/applicants/bulk/generate-access-codes`
   - Generate access codes for selected applicants

2. **When Creating Applicant:**
   - Access code is auto-generated (format: `BSIT-XXXXX`)
   - Stored in `access_codes` table linked to `applicant_id`

3. **Via Database:**
   ```sql
   -- First create applicant
   INSERT INTO applicants (name, email, ...) VALUES (...);
   
   -- Then create access code
   INSERT INTO access_codes (code, applicant_id, is_used, expires_at) 
   VALUES ('BSIT-12345', 1, 0, NULL);
   ```

---

## 🚫 Why They Can't Access Each Other

### Admin Routes Protection:
```php
// routes/web.php
Route::middleware(['ajax.auth'])->prefix('admin')->group(function () {
    // All admin routes here
});

// Middleware checks:
1. Auth::check() → Must be logged in as User
2. $user->role → Must be 'department-head' or 'instructor'
```

### Applicant Routes Protection:
```php
// routes/public.php
// Routes check session directly:
if (!$request->session()->get('applicant_id')) {
    return redirect()->route('applicant.login');
}
```

**Key Differences:**
- **Admins** use `Auth::check()` and `Auth::user()` → checks `users` table
- **Applicants** use `session('applicant_id')` → checks session data (NOT authenticated)

**Why This Works:**
- Admin routes require `Auth::check() === true` → only works if logged in as User
- Applicant routes check `session('applicant_id')` → completely separate check
- An admin session doesn't have `applicant_id` in session
- An applicant session doesn't have `Auth::user()` set

---

## 📋 Summary: What You Need When Deployed

### For Admin Access:
✅ **Create User accounts** in the `users` table with:
- `username` (unique)
- `password_hash` (hashed password)
- `role` = `'department-head'` or `'instructor'`
- `full_name`, `email`

### For Applicant Access:
✅ **Create Applicant records** in the `applicants` table
✅ **Generate Access Codes** in the `access_codes` table:
- `code` (format: `BSIT-XXXXX`)
- `applicant_id` (links to applicant)
- `is_used` = `0` (not used yet)
- `expires_at` = `NULL` or future date

---

## 🔄 Workflow Example

### Admin Login Flow:
1. Visit: `https://yourdomain.com/admin/login`
2. Enter: username + password
3. System checks: `users` table for matching credentials
4. If valid & role is correct: `Auth::login($user)`
5. Redirect to: `/admin/dashboard`
6. Can access all `/admin/*` routes

### Applicant Access Flow:
1. Visit: `https://yourdomain.com/` (redirects to `/applicant/login`)
2. Enter: access code (e.g., `BSIT-12345`)
3. System checks: `access_codes` table for valid, unused code
4. If valid: Store `applicant_id` in session
5. Redirect to: `/exam/pre-requirements`
6. Can access exam routes (checks session, NOT Auth)

---

## ✅ Quick Checklist for Deployment

- [ ] Create at least one `department-head` user account in database
- [ ] Test admin login at `/admin/login`
- [ ] Create applicant records (via import or manual)
- [ ] Generate access codes for applicants
- [ ] Test applicant access with an access code
- [ ] Verify admins cannot access applicant routes
- [ ] Verify applicants cannot access admin routes

---

## 🛠️ Creating First Admin Account

If you need to create the first admin account when deployed:

### Option 1: Via Admin Panel (if you have one admin)
If you already have one admin, log in and use `/admin/users` to create more.

### Option 2: Via Database Directly
```sql
INSERT INTO users (username, password_hash, full_name, role, email, created_at, updated_at)
VALUES (
    'your_admin_username',
    '$2y$12$...',  -- Use Hash::make('your_password') in PHP or Laravel
    'Your Full Name',
    'department-head',
    'your.email@example.com',
    NOW(),
    NOW()
);
```

### Option 3: Via Laravel Tinker
```bash
php artisan tinker
```
```php
\App\Models\User::create([
    'username' => 'admin',
    'password_hash' => \Illuminate\Support\Facades\Hash::make('YourSecurePassword123!'),
    'full_name' => 'Administrator',
    'email' => 'admin@yourdomain.com',
    'role' => 'department-head'
]);
```

### Option 4: Create Seeder
Create a seeder file and run it:
```bash
php artisan make:seeder CreateAdminSeeder
php artisan db:seed --class=CreateAdminSeeder
```

---

## 🔍 Verification Commands

### Check if admin can login:
```bash
php artisan tinker
```
```php
$user = \App\Models\User::where('username', 'your_username')->first();
$user ? 'User exists' : 'User not found';
\Illuminate\Support\Facades\Hash::check('your_password', $user->password_hash);
```

### Check access codes:
```bash
php artisan tinker
```
```php
\App\Models\AccessCode::where('is_used', false)->count(); // Available codes
\App\Models\AccessCode::with('applicant')->get(); // All codes with applicants
```

---

**Remember:** These are two completely separate systems. An admin cannot access applicant routes, and an applicant cannot access admin routes, even on the same domain.
