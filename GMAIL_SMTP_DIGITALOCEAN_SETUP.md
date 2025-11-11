# Gmail SMTP Setup for DigitalOcean Production Deployment

**Status**: ✅ Complete Guide  
**Target**: DigitalOcean Droplet (Ubuntu 24.04 LTS)  
**Email Service**: Gmail SMTP (FREE - 500 emails/day)

---

## 🎯 Overview

Your EnrollAssess system uses **database-based email configuration**, which means:
- ✅ Email settings are stored in the database (`system_settings` table)
- ✅ No need to edit `.env` file for email changes
- ✅ Admin can configure through web interface at `/admin/settings`
- ✅ Changes apply immediately (no server restart needed)
- ✅ Falls back to `.env` if database settings not available

---

## 🚀 Quick Setup (After Deployment)

### Step 1: Verify Network Connectivity

DigitalOcean droplets **allow outbound SMTP by default**, but let's verify:

```bash
# SSH into your DigitalOcean droplet
ssh deployer@your_droplet_ip

# Test Gmail SMTP connectivity (port 587)
telnet smtp.gmail.com 587

# If telnet is not installed:
sudo apt install telnet -y

# Expected output:
# Trying 142.250.xxx.xxx...
# Connected to smtp.gmail.com.
# Escape character is '^]'.
# 220 smtp.gmail.com ESMTP ...

# Press Ctrl+C to exit
```

**If connection fails:**
- Check firewall rules (should allow outbound on port 587)
- Verify DNS resolution: `nslookup smtp.gmail.com`
- Check DigitalOcean cloud firewall (if configured)

### Step 2: Configure Firewall (If Needed)

```bash
# Check current firewall status
sudo ufw status

# If UFW is active, ensure outbound is allowed (default: YES)
# Only need to allow OUTBOUND on port 587 (not inbound)

# Verify outbound rules
sudo ufw status verbose

# If port 587 is blocked (unlikely), allow it:
sudo ufw allow out 587/tcp
sudo ufw allow out 465/tcp  # Alternative SSL port (if using)
```

**Note**: DigitalOcean droplets allow all outbound traffic by default. You only need to configure this if you've specifically restricted outbound traffic.

### Step 3: Get Gmail App Password

1. **Login to Google Account**
   - Go to https://myaccount.google.com
   - Use the Gmail account you want to send emails from

2. **Enable 2-Step Verification** (Required)
   - Security → 2-Step Verification → Turn On
   - Follow the setup wizard
   - Use phone number or authenticator app

3. **Generate App Password**
   - Security → App passwords
   - Select "Mail" as app type
   - Select "Other (Custom name)" as device
   - Name it "EnrollAssess Production"
   - Click "Generate"
   - **Copy the 16-character password** (e.g., `abcd efgh ijkl mnop`)
   - **Important**: Remove spaces when entering (use: `abcdefghijklmnop`)
   - Save this password securely (you can't see it again!)

### Step 4: Configure Email Settings in Admin Panel

1. **Access Admin Panel**
   - Go to: `https://your-domain.com/admin/settings`
   - Login as Administrator or Department Head

2. **Navigate to Email Settings**
   - Click "Email Settings" tab
   - Or scroll to email section

3. **Enter Gmail Configuration**
   ```
   Mail Driver: smtp (already set)
   SMTP Host: smtp.gmail.com (already set)
   SMTP Port: 587 (already set)
   Username: your-email@gmail.com (enter your Gmail address)
   Password: abcdefghijklmnop (paste 16-character app password, NO SPACES)
   Encryption: tls (already set)
   From Address: your-email@gmail.com (same as username)
   From Name: EnrollAssess System (or your school name)
   ```

4. **Test Email Configuration**
   - Enter a test email address (your personal email)
   - Click "Send Test Email"
   - Check your inbox (and spam folder)
   - If you receive the test email → Configuration is working! ✅

5. **Save Settings**
   - Click "Save Settings"
   - You should see: "Successfully updated X settings. Mail configuration reloaded."
   - Done! ✅

---

## 🔧 Alternative: Using .env File (Fallback)

If you prefer to use `.env` file instead of database settings (or as fallback):

```bash
# SSH into your droplet
ssh deployer@your_droplet_ip

# Navigate to application directory
cd /var/www/enrollassess

# Edit .env file
nano .env

# Add or update these lines:
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="EnrollAssess System"

# Save and exit (Ctrl+X, Y, Enter)

# Clear config cache
php artisan config:clear
php artisan cache:clear
```

**Note**: Database settings take precedence over `.env` settings. The system will use database settings if available, and fall back to `.env` only if database settings are empty.

---

## 🧪 Testing Email After Deployment

### Method 1: Through Admin Panel (Recommended)

1. Login to admin panel
2. Go to `/admin/settings`
3. Click "Email Settings" tab
4. Enter test email address
5. Click "Send Test Email"
6. Check inbox for test message

### Method 2: Through Command Line (Tinker)

```bash
# SSH into droplet
ssh deployer@your_droplet_ip

# Navigate to application
cd /var/www/enrollassess

# Start Tinker
php artisan tinker

# Send test email
use Illuminate\Support\Facades\Mail;

Mail::raw('Test email from EnrollAssess production server', function($message) {
    $message->to('your-test-email@gmail.com')
            ->subject('Test Email from Production');
});

# Expected output: (no error means success)
# Check your inbox for the test email

# Exit Tinker
exit
```

### Method 3: Test Actual System Email

1. **Test Access Code Email**
   - Create a test applicant
   - Generate access code
   - Check if applicant receives email with access code

2. **Test Interview Notification**
   - Assign interview to applicant
   - Check if interviewer receives email notification

---

## 🐛 Troubleshooting

### Issue 1: "Connection Timeout" Error

**Symptoms:**
- Email test fails with "Connection timeout" or "Could not connect to SMTP server"

**Solutions:**

```bash
# 1. Test network connectivity
telnet smtp.gmail.com 587

# 2. Check if firewall is blocking
sudo ufw status
sudo ufw allow out 587/tcp

# 3. Check DNS resolution
nslookup smtp.gmail.com

# 4. Test from server directly
curl -v telnet://smtp.gmail.com:587

# 5. Check if ISP/Data Center blocks port 587
# Some hosting providers block SMTP ports
# Contact DigitalOcean support if issue persists
```

### Issue 2: "Authentication Failed" Error

**Symptoms:**
- Email test fails with "Authentication failed" or "Invalid credentials"

**Solutions:**

1. **Verify App Password**
   - Ensure 2-Step Verification is enabled
   - Generate a new App Password
   - Remove spaces from password (16 characters, no spaces)
   - Copy password exactly as shown

2. **Check Username Format**
   - Use full email address: `your-email@gmail.com`
   - Not just: `your-email` (missing @gmail.com)

3. **Verify Settings in Database**
   ```bash
   # SSH into droplet
   ssh deployer@your_droplet_ip
   cd /var/www/enrollassess
   
   php artisan tinker
   
   # Check current settings
   use App\Models\Settings;
   Settings::getGroup('email');
   
   # Verify values are correct
   exit
   ```

4. **Clear Cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

### Issue 3: "Email Sent but Not Received"

**Symptoms:**
- Test email shows "Success" but email doesn't arrive

**Solutions:**

1. **Check Spam Folder**
   - Gmail may mark emails as spam initially
   - Check spam/junk folder

2. **Check Gmail Send Limits**
   - Free Gmail: 500 emails/day limit
   - If exceeded, emails will be rejected
   - Check Gmail account for warnings

3. **Check Application Logs**
   ```bash
   # View email-related logs
   tail -f storage/logs/laravel.log | grep -i mail
   
   # Or view all logs
   tail -f storage/logs/laravel.log
   ```

4. **Verify From Address**
   - Must match Gmail account address
   - Cannot send from different email address
   - Use same email in "Username" and "From Address"

### Issue 4: "SSL/TLS Handshake Failed"

**Symptoms:**
- Error: "SSL handshake failed" or "TLS connection failed"

**Solutions:**

1. **Check PHP OpenSSL Extension**
   ```bash
   php -m | grep openssl
   # Should show: openssl
   
   # If not installed:
   sudo apt install php8.2-openssl -y
   sudo systemctl restart php8.2-fpm
   ```

2. **Verify Encryption Setting**
   - Port 587 → Use `tls`
   - Port 465 → Use `ssl`
   - Don't mix them up!

3. **Check System Time**
   ```bash
   # SSL certificates require correct system time
   date
   
   # If time is wrong, sync it:
   sudo apt install ntp -y
   sudo systemctl enable ntp
   sudo systemctl start ntp
   ```

### Issue 5: "Database Settings Not Loading"

**Symptoms:**
- Email settings saved but not applying
- Still using `.env` settings instead of database

**Solutions:**

1. **Verify Service Provider Registered**
   ```bash
   # Check bootstrap/providers.php
   cat bootstrap/providers.php
   
   # Should contain:
   # App\Providers\MailConfigServiceProvider::class,
   ```

2. **Check Database Settings**
   ```bash
   php artisan tinker
   
   use App\Models\Settings;
   $settings = Settings::getGroup('email');
   $settings->toArray();
   
   # Verify all email settings are present
   exit
   ```

3. **Clear All Caches**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   
   # Restart PHP-FPM
   sudo systemctl restart php8.2-fpm
   ```

4. **Check Application Logs**
   ```bash
   tail -f storage/logs/laravel.log
   # Look for MailConfigServiceProvider errors
   ```

### Issue 6: "Queued Emails Not Sending"

**Symptoms:**
- Emails are queued but not being sent
- Queue workers running but emails stuck in queue

**Solutions:**

1. **Verify Queue Workers Running**
   ```bash
   # Check Supervisor status
   sudo supervisorctl status
   
   # Should show: enrollassess-worker: RUNNING
   # If not running, start it:
   sudo supervisorctl start enrollassess-worker:*
   ```

2. **Check Queue Configuration**
   ```bash
   # Verify queue connection is Redis
   php artisan tinker
   config('queue.default');
   # Should return: 'redis'
   exit
   ```

3. **Verify Mail Config Loads in Queue Workers**
   - Mail config should load automatically in queue workers
   - If not, restart queue workers:
   ```bash
   sudo supervisorctl restart enrollassess-worker:*
   ```

4. **Check Failed Jobs**
   ```bash
   # View failed jobs
   php artisan queue:failed
   
   # Retry failed jobs
   php artisan queue:retry all
   
   # Or retry specific job
   php artisan queue:retry {job-id}
   ```

5. **Test Queue Processing**
   ```bash
   # Process queue manually to see errors
   php artisan queue:work --once --verbose
   ```

---

## 🔒 Security Best Practices

### 1. Use App Password (Not Regular Password)
- ✅ **DO**: Use Gmail App Password (16 characters)
- ❌ **DON'T**: Use your regular Gmail password
- ❌ **DON'T**: Enable "Less Secure Apps" (deprecated)

### 2. Protect App Password
- Store in database (encrypted in future versions)
- Don't commit to Git
- Don't share in emails or chat
- Rotate periodically (generate new one every 6-12 months)

### 3. Limit Email Sending
- Monitor email usage (500/day limit for free Gmail)
- Implement rate limiting for email sending
- Queue emails if sending many at once
- Consider Google Workspace ($6/month) for higher limits (2,000/day)

### 4. Monitor Email Delivery
- Check logs regularly for email failures
- Set up alerts for email sending errors
- Monitor bounce rates
- Verify email delivery to recipients

---

## 📊 Gmail Limits & Considerations

### Free Gmail Account Limits:
- ✅ **500 emails per day** (sufficient for most schools)
- ✅ **100 recipients per message** (for bulk emails)
- ✅ **25 MB attachment size** (for reports)
- ✅ **No cost** (completely free)

### When to Upgrade to Google Workspace:
- Need more than 500 emails/day
- Need custom domain email (e.g., `noreply@yourschool.edu`)
- Need larger attachment size (50 MB)
- Need better deliverability
- **Cost**: $6/user/month (gives 2,000 emails/day)

### Alternative SMTP Providers (If Needed):
1. **SendGrid** (Free tier: 100 emails/day)
2. **Mailgun** (Free tier: 5,000 emails/month)
3. **Amazon SES** (Pay as you go: $0.10 per 1,000 emails)
4. **Postmark** (Paid: $15/month for 10,000 emails)

---

## ✅ Production Deployment Checklist

Before going live, verify:

- [ ] Gmail account has 2-Step Verification enabled
- [ ] Gmail App Password generated and saved securely
- [ ] Email settings configured in `/admin/settings`
- [ ] Test email sent successfully from admin panel
- [ ] Test email received in inbox (not spam)
- [ ] Network connectivity verified (telnet smtp.gmail.com 587)
- [ ] Firewall allows outbound on port 587
- [ ] PHP OpenSSL extension installed
- [ ] System time is correct (for SSL certificates)
- [ ] Email settings saved in database (verify via Tinker)
- [ ] Application logs checked (no email errors)
- [ ] Queue workers running (Supervisor status: RUNNING)
- [ ] Queued emails processing correctly (test with real email)
- [ ] From address matches Gmail account
- [ ] From name set to school name
- [ ] Email limits understood (500/day for free Gmail)
- [ ] Redis queue connection working (if using queued emails)

---

## 🚀 Post-Deployment Steps

### 1. Monitor Email Sending

```bash
# Watch email logs in real-time
tail -f /var/www/enrollassess/storage/logs/laravel.log | grep -i mail

# Check for email errors
grep -i "mail\|smtp\|email" /var/www/enrollassess/storage/logs/laravel.log | tail -50
```

### 2. Test Real-World Scenarios

- [ ] Send access code to test applicant
- [ ] Send interview notification to interviewer
- [ ] Send bulk emails (if applicable)
- [ ] Verify emails arrive in inbox (not spam)
- [ ] Check email formatting and content

### 3. Set Up Email Monitoring (Optional)

```bash
# Create a cron job to check email health
crontab -e

# Add this line (checks email every hour):
0 * * * * cd /var/www/enrollassess && php artisan tinker --execute="Mail::raw('Health check', fn(\$m) => \$m->to('admin@yourschool.edu')->subject('Email Health Check'));" >> /var/log/email-health.log 2>&1
```

### 4. Document Configuration

- Save Gmail App Password in secure password manager
- Document email settings for future reference
- Note any custom configurations
- Keep backup of email settings (database export)

---

## 📞 Support & Resources

### Gmail App Password Help:
- https://support.google.com/accounts/answer/185833

### DigitalOcean Network Documentation:
- https://docs.digitalocean.com/products/networking/

### Laravel Mail Documentation:
- https://laravel.com/docs/12.x/mail

### Troubleshooting Resources:
- Check application logs: `storage/logs/laravel.log`
- Check PHP error logs: `/var/log/php8.2-fpm.log`
- Check Nginx error logs: `/var/log/nginx/error.log`

---

## 🎓 Summary

### How It Works:
1. **Database Settings**: Email configuration stored in `system_settings` table
2. **Service Provider**: `MailConfigServiceProvider` loads settings on every request and queue job
3. **Gmail SMTP**: Connects to `smtp.gmail.com:587` using TLS encryption
4. **App Password**: Authenticates using Gmail App Password (not regular password)
5. **Admin Interface**: Settings can be changed through `/admin/settings` (no server access needed)
6. **Queue Workers**: Mail config automatically loads in queue workers (emails sent asynchronously)

### Key Points:
- ✅ **No server configuration needed** after initial deployment
- ✅ **Changes apply immediately** (no restart required)
- ✅ **Free Gmail account** works perfectly (500 emails/day)
- ✅ **Database-based** settings (user-friendly)
- ✅ **Fallback to .env** if database unavailable
- ✅ **Works on DigitalOcean** out of the box (no special configuration)

### Next Steps:
1. Deploy your application to DigitalOcean
2. Configure email settings through admin panel
3. Test email sending
4. Go live! 🚀

---

**Last Updated**: November 2025  
**System**: EnrollAssess v1.0  
**Deployment Target**: DigitalOcean Ubuntu 24.04 LTS  
**Email Service**: Gmail SMTP (Free)

