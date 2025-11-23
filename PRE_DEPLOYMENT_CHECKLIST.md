# Pre-Deployment Checklist - EnrollAssess

## ✅ Files to Clean Up Before Deployment

### Files That Should NOT Be Committed:

1. **Test/Development Files:**
   - `EVSU_Entrance_Results_*.xlsx` (test exports)
   - `EVSU_Entrance_Results_*.pdf` (test PDFs)
   - `EVSU_Qualifiers_List_*.pdf` (test PDFs)
   - `applicants_with_access_codes_*.csv` (test data)
   - `enrollassess.sql` (database dump - should be excluded)
   - `background_image.jpg` (test image)
   - `xlsx logo.png` (test image)
   - `NEW TEMPLATE.xlsx` (test template)
   - `130-GSO-Form-*.xlsx` (test file)

2. **Database Files:**
   - `database/database.sqlite` (local development database)

3. **Temporary Files:**
   - `an route list --name=send-exam` (temporary file)

---

## 📋 Pre-Deployment Steps

### Step 1: Update .gitignore

Add these to `.gitignore` if not already there:

```gitignore
# Test files
*.xlsx
*.pdf
*.csv
*.jpg
*.png
!public/*.png
!public/*.jpg
!public/*.ico

# Database dumps
*.sql
database/database.sqlite

# Temporary files
an route*
```

### Step 2: Remove Test Files (Optional but Recommended)

These files are large and not needed in production:

```bash
# Remove test Excel files
rm EVSU_Entrance_Results_*.xlsx
rm NEW\ TEMPLATE.xlsx
rm 130-GSO-Form-*.xlsx

# Remove test PDF files
rm EVSU_Entrance_Results_*.pdf
rm EVSU_Qualifiers_List_*.pdf

# Remove test CSV files
rm applicants_with_access_codes_*.csv

# Remove test images
rm background_image.jpg
rm xlsx\ logo.png

# Remove database dump
rm enrollassess.sql

# Remove temporary files
rm "an route list --name=send-exam"
```

### Step 3: Verify .env is Excluded

Check that `.env` is in `.gitignore` (it should be already).

### Step 4: Check Git Status

```bash
git status
```

Make sure no sensitive files are staged:
- ❌ `.env` files
- ❌ Test data files
- ❌ Database dumps
- ❌ Credentials

### Step 5: Commit Changes

```bash
# Make sure you're on the yanix branch
git checkout yanix

# Add all changes (respects .gitignore)
git add .

# Commit with message
git commit -m "Prepare for production deployment"

# Push to repository
git push origin yanix
```

---

## ✅ Files That SHOULD Be Committed

### Essential Files:
- ✅ All PHP files (`app/`, `routes/`, `config/`, etc.)
- ✅ All Blade templates (`resources/views/`)
- ✅ All JavaScript/CSS (`resources/js/`, `resources/css/`)
- ✅ Database migrations (`database/migrations/`)
- ✅ Database seeders (`database/seeders/`)
- ✅ Configuration files (`composer.json`, `package.json`, `vite.config.js`)
- ✅ Documentation files (`.md` files)
- ✅ `env.production.example` (template file)
- ✅ `.gitignore`
- ✅ `artisan`
- ✅ `README.md`

### Build Files (will be generated on server):
- ✅ `package.json` and `package-lock.json`
- ✅ `composer.json` and `composer.lock`
- ✅ `vite.config.js`
- ✅ `tailwind.config.js`

---

## 🔍 Final Verification

Before pushing to repository, verify:

- [ ] `.env` is NOT in repository (check `.gitignore`)
- [ ] No test Excel/PDF files are committed
- [ ] No database dumps are committed
- [ ] No credentials are hardcoded in code
- [ ] `env.production.example` exists and is up-to-date
- [ ] All migrations are committed
- [ ] All seeders are committed
- [ ] Documentation is up-to-date

---

## 🚀 Ready for Deployment

Once you've completed the checklist:

1. **Push to GitHub/GitLab:**
   ```bash
   # Make sure you're on yanix branch
   git checkout yanix
   
   # Push to repository
   git push origin yanix
   ```

2. **Verify on GitHub:**
   - Check that sensitive files are NOT visible
   - Check that all code files are present

3. **Proceed with deployment:**
   - Follow `DEPLOYMENT_QUICK_START.md`
   - Use `env.production.example` as template for `.env` on server

---

## 📝 Notes

- Test files can be kept locally but shouldn't be in production repository
- Database dumps should be stored separately (not in git)
- All sensitive data should be in `.env` file (not committed)
- Documentation files are fine to commit (helpful for future reference)

