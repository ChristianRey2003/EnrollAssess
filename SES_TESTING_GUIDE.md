# Amazon SES Integration - Testing Guide

## ✅ Implementation Complete - Ready for Testing

**Date:** November 10, 2025  
**Status:** All code changes complete, seeder executed successfully

---

## 🚀 Quick Start Testing

### 1. Verify Database Settings

Run this command to check if SES settings were added:
```bash
php artisan tinker
```

Then in Tinker:
```php
use App\Models\Settings;
Settings::where('group', 'email')->pluck('value', 'key');
// Should show aws_access_key_id, aws_secret_access_key, aws_region
exit
```

### 2. Access Admin Settings Panel

1. Start your development server:
```bash
php artisan serve
```

2. Navigate to: `http://localhost:8000/admin/settings`
3. Click on "📧 Email Settings" tab

### 3. Test UI Field Toggles

**Test SMTP Display (Default):**
- Mail Driver should show: "SMTP (Gmail, etc.)"
- You should see: Host, Port, Username, Password, Encryption fields
- You should NOT see: AWS fields
- Gmail guide should be visible

**Test SES Display:**
- Change Mail Driver to: "Amazon SES"
- SMTP fields should hide
- You should see: AWS Access Key ID, AWS Secret Access Key, AWS Region
- SES guide should be visible (yellow button)
- Gmail guide should hide

**Test Other Mailers:**
- Change to "Log (Testing)"
- Both SMTP and SES fields should hide
- No guides visible

### 4. Test Configuration Saving (SMTP)

1. Select "SMTP (Gmail, etc.)"
2. Keep existing Gmail settings
3. Click "Save Settings"
4. Should see success message: "Successfully updated X settings. Mail configuration reloaded."
5. Refresh page - settings should persist

### 5. Test Configuration Saving (SES) - Without Real Credentials

1. Select "Amazon SES"
2. Fill in dummy data:
   - AWS Access Key ID: `TEST_ACCESS_KEY`
   - AWS Secret Access Key: `TEST_SECRET_KEY`
   - AWS Region: `ap-southeast-1 (Singapore)`
   - Keep From Address and From Name as-is
3. Click "Save Settings"
4. Should save successfully
5. Refresh page - dummy credentials should be saved (but hidden in password fields)

### 6. Test Email Sending (SMTP - if configured)

If you have working Gmail credentials:
1. Select "SMTP (Gmail, etc.)"
2. Ensure credentials are filled in
3. Save settings
4. In "Test Email Configuration" section:
   - Enter your email address
   - Click "Send Test Email"
   - Should receive test email
   - Should see success message

### 7. Test SES Error Handling (Without Real AWS Account)

1. Select "Amazon SES"
2. Fill in fake credentials:
   - AWS Access Key ID: `AKIAFAKEKEY123`
   - AWS Secret Access Key: `fakeSecretKey123456`
   - AWS Region: `ap-southeast-1 (Singapore)`
3. Save settings
4. Try to send test email
5. Should see error (expected - fake credentials)
6. Error message should be clear about the issue

---

## 🧪 Full Integration Testing (With Real AWS Account)

### Prerequisites
- AWS account with SES enabled
- At least one verified email address in SES

### Step 1: AWS SES Setup

1. **Sign in to AWS Console:**
   - Go to https://console.aws.amazon.com/ses/

2. **Choose Region:**
   - Select "Asia Pacific (Singapore) ap-southeast-1" from region dropdown

3. **Verify Email Address:**
   - Click "Verified identities"
   - Click "Create identity"
   - Choose "Email address"
   - Enter your email (e.g., your@email.com)
   - Click "Create identity"
   - Check your email for verification link
   - Click verification link

4. **Create IAM User:**
   - Go to https://console.aws.amazon.com/iam/
   - Click "Users" → "Create user"
   - Username: `enrollassess-ses`
   - Click "Next"
   - Choose "Attach policies directly"
   - Search and select: `AmazonSESFullAccess`
   - Click "Next" → "Create user"

5. **Create Access Keys:**
   - Click on the newly created user
   - Go to "Security credentials" tab
   - Click "Create access key"
   - Choose "Application running outside AWS"
   - Click "Next" → "Create access key"
   - **IMPORTANT:** Copy both:
     - Access Key ID (starts with AKIA...)
     - Secret Access Key (long random string)
   - Click "Done"

### Step 2: Configure EnrollAssess

1. **Go to Admin Settings:**
   - Navigate to: `http://localhost:8000/admin/settings`
   - Click "📧 Email Settings" tab

2. **Select Amazon SES:**
   - Mail Driver: Select "Amazon SES"
   - SES fields should appear

3. **Enter AWS Credentials:**
   - AWS Access Key ID: Paste from Step 1.5
   - AWS Secret Access Key: Paste from Step 1.5
   - AWS Region: Select `ap-southeast-1 (Singapore)`
   - From Address: Must match your verified email
   - From Name: "EnrollAssess System" (or customize)

4. **Save Configuration:**
   - Click "Save Settings"
   - Should see success message

### Step 3: Send Test Email

1. **In "Test Email Configuration" section:**
   - Enter your verified email address
   - Click "Send Test Email"

2. **Expected Results:**
   - Should see: "Test email sent successfully to [email]! Please check your inbox."
   - Check your email inbox
   - Should receive test email from SES

3. **If Error Occurs:**
   - Check if email is verified in AWS SES
   - Verify region matches (ap-southeast-1)
   - Check AWS credentials are correct
   - Look at error message for clues

### Step 4: Test Sandbox Mode Detection

1. **Send to Unverified Email:**
   - In test email field, enter an email that's NOT verified in AWS
   - Click "Send Test Email"

2. **Expected Result:**
   - Should see error about sandbox mode
   - Message should mention verifying email in AWS Console
   - Should include link to AWS SES

### Step 5: Test Queue Email Sending

1. **Clear any running queue workers:**
```bash
php artisan queue:restart
```

2. **Start queue worker:**
```bash
php artisan queue:work
```

3. **Trigger an email via application:**
   - For example, assign an exam to an applicant
   - Email should be queued
   - Queue worker should process it
   - Check that email sends via SES

4. **Check Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

### Step 6: Test Switching Between Mailers

1. **Switch back to SMTP:**
   - Go to Admin Settings
   - Select "SMTP (Gmail, etc.)"
   - Your Gmail credentials should still be there
   - Save and test email
   - Should work with Gmail

2. **Switch back to SES:**
   - Select "Amazon SES"
   - Your AWS credentials should still be saved
   - Test email again
   - Should work with SES

---

## 🐛 Common Issues & Solutions

### Issue: "Settings not saving"
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Try again
```

### Issue: "SES fields not showing"
- Clear browser cache (Ctrl + Shift + Delete)
- Hard refresh page (Ctrl + F5)
- Check JavaScript console for errors

### Issue: "Error: Could not find credentials"
```bash
# Restart queue workers
php artisan queue:restart

# Clear config
php artisan config:clear

# Restart application
php artisan serve
```

### Issue: "Invalid AWS credentials"
- Verify credentials in AWS IAM Console
- Check for extra spaces when copying
- Ensure IAM user has SES permissions
- Verify region matches where you verified emails

### Issue: "Email not received"
- Check spam folder
- Verify sender email in AWS SES
- Check AWS SES sending statistics for bounces
- Ensure recipient email is verified (sandbox mode)

---

## 📊 Verification Checklist

### ✅ Code Implementation
- [x] MailConfigurationService created
- [x] MailConfigServiceProvider updated
- [x] SettingsController updated
- [x] SystemSettingsSeeder updated
- [x] Settings view updated with SES UI
- [x] JavaScript field toggles implemented
- [x] Config/services.php updated
- [x] No linter errors

### ✅ Database
- [x] Seeder executed successfully
- [ ] Verify SES settings exist in database
- [ ] Verify settings have correct types

### ✅ UI/UX
- [ ] SES option appears in mailer dropdown
- [ ] SMTP fields show when SMTP selected
- [ ] SES fields show when SES selected
- [ ] Fields hide appropriately for other mailers
- [ ] Setup guides toggle correctly
- [ ] Mobile responsive (test on phone)

### ✅ Functionality (SMTP)
- [ ] SMTP configuration still works
- [ ] Can save SMTP settings
- [ ] Can send test email via SMTP
- [ ] Queue workers send via SMTP

### ✅ Functionality (SES)
- [ ] Can save SES credentials
- [ ] Can send test email via SES (to verified email)
- [ ] Sandbox error detection works
- [ ] Error messages are user-friendly
- [ ] Queue workers send via SES

### ✅ Edge Cases
- [ ] Can switch between SMTP and SES
- [ ] Settings persist after switching
- [ ] Password fields don't overwrite if left blank
- [ ] Works with log mailer (testing)
- [ ] Cache clearing works

---

## 📝 Test Report Template

Use this template to document your testing:

```
# SES Integration Test Report

Date: _______________
Tester: _______________

## Environment
- PHP Version: _______________
- Laravel Version: 12.x
- Database: _______________

## UI Tests
- [ ] SMTP fields toggle: PASS / FAIL
- [ ] SES fields toggle: PASS / FAIL
- [ ] Setup guides toggle: PASS / FAIL
- [ ] Mobile responsive: PASS / FAIL

## SMTP Tests
- [ ] Save settings: PASS / FAIL
- [ ] Send test email: PASS / FAIL
- [ ] Queue email: PASS / FAIL

## SES Tests (if AWS account available)
- [ ] Save credentials: PASS / FAIL
- [ ] Send to verified email: PASS / FAIL
- [ ] Sandbox error detection: PASS / FAIL
- [ ] Queue email: PASS / FAIL

## Switching Tests
- [ ] SMTP to SES: PASS / FAIL
- [ ] SES to SMTP: PASS / FAIL
- [ ] Settings persist: PASS / FAIL

## Issues Found
1. _______________
2. _______________

## Overall Status
- [ ] All tests passed - Ready for production
- [ ] Minor issues - Needs fixes
- [ ] Major issues - Needs rework

## Notes
_______________
```

---

## 🚀 Next Steps After Testing

### If All Tests Pass:
1. ✅ Mark implementation as production-ready
2. ✅ Deploy to staging environment
3. ✅ Test on staging with real AWS SES
4. ✅ Request AWS SES production access (if needed)
5. ✅ Deploy to production
6. ✅ Monitor email sending statistics

### If Issues Found:
1. Document issues clearly
2. Prioritize issues (critical/high/medium/low)
3. Fix critical issues first
4. Re-test after fixes
5. Update documentation

---

## 📚 Additional Resources

- **Main Implementation Doc:** `AMAZON_SES_IMPLEMENTATION.md`
- **AWS SES Console:** https://console.aws.amazon.com/ses/
- **AWS IAM Console:** https://console.aws.amazon.com/iam/
- **Laravel Mail Docs:** https://laravel.com/docs/12.x/mail

---

## ✅ Ready to Test!

The implementation is complete and ready for comprehensive testing. Follow the steps above in order, and document any issues you encounter.

**Good luck with testing!** 🎉

