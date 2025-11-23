# Environment File Setup Guide

## Quick Setup

### For Local Development:

1. **Copy the example file:**
   ```bash
   cp .env.example .env
   ```

2. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

3. **Update database credentials:**
   - Edit `.env` file
   - Update `DB_PASSWORD` with your local MySQL password
   - Update `DB_DATABASE` if different

4. **Update mail settings (optional for local):**
   - Use Mailtrap for testing: https://mailtrap.io
   - Or set `MAIL_MAILER=log` to log emails to file

---

## For Production Deployment (DigitalOcean)

### Step 1: On Your Server

After cloning the repository, copy the production template:

```bash
cp env.production.example .env
```

### Step 2: Edit .env File

```bash
nano .env
```

### Step 3: Fill in These Required Values

#### 1. Application Settings
```env
APP_NAME="EnrollAssess"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com  # ← Change this
```

#### 2. Database Settings (On-Server MySQL)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=enrollassess
DB_USERNAME=enrollassess_user  # ← Change this
DB_PASSWORD=your-strong-password  # ← Change this
```

#### 3. Redis Settings (On-Server Redis)
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null  # ← Set password if you configured Redis with password
REDIS_PORT=6379
```

#### 4. Mail Settings (Amazon SES)
**Note:** Your system uses database configuration for SES (configured in admin panel), but you can also set defaults in `.env`:

```env
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=your-verified-email@your-domain.com  # ← Change this (must be verified in SES)
MAIL_FROM_NAME="EnrollAssess"

# AWS SES Credentials (will be configured in admin panel, but can set defaults here)
AWS_ACCESS_KEY_ID=your-aws-access-key-id  # ← Get from AWS IAM
AWS_SECRET_ACCESS_KEY=your-aws-secret-key  # ← Get from AWS IAM
AWS_DEFAULT_REGION=ap-southeast-1  # ← Change to your preferred region
```

**How to get Amazon SES credentials:**
1. Sign up for AWS account (if needed)
2. Go to AWS Console → IAM → Users
3. Create new user with SES permissions (or use existing)
4. Create Access Key → Save Access Key ID and Secret
5. Go to SES → Verify your email address (or domain)
6. Enter credentials in admin panel: Settings → Email Configuration

**Important:** Your system stores SES credentials in database (admin panel), not in `.env` file. The `.env` values above are optional defaults.

#### 5. Pusher Settings (Real-time Broadcasting)
```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-pusher-app-id  # ← Get from pusher.com
PUSHER_APP_KEY=your-pusher-key  # ← Get from pusher.com
PUSHER_APP_SECRET=your-pusher-secret  # ← Get from pusher.com
PUSHER_APP_CLUSTER=ap1
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

**How to get Pusher credentials:**
1. Sign up at https://pusher.com (free tier available)
2. Create a new app
3. Choose cluster: `ap1` (Asia-Pacific)
4. Copy App ID, Key, and Secret

### Step 4: Generate Application Key

```bash
php artisan key:generate
```

This will automatically fill in `APP_KEY` in your `.env` file.

---

## Required Values Checklist

Before deployment, make sure you have:

- [ ] **Domain name** (or droplet IP for initial setup)
- [ ] **Database password** (strong password for MySQL user)
- [ ] **Amazon SES credentials** (AWS Access Key ID and Secret from AWS IAM)
- [ ] **Verified email address** (verified in Amazon SES)
- [ ] **Pusher credentials** (App ID, Key, Secret from pusher.com)

---

## Optional Settings

### If Using Managed Database (Not Recommended for Your Setup)

If you decide to use DigitalOcean Managed MySQL later:

```env
DB_HOST=your-db-cluster-do-user-123456-0.db.ondigitalocean.com
DB_PORT=25060
DB_DATABASE=defaultdb
DB_USERNAME=doadmin
DB_PASSWORD=your-managed-db-password
DB_SSL_MODE=require
```

### If Using Managed Redis (Not Recommended for Your Setup)

If you decide to use DigitalOcean Managed Redis later:

```env
REDIS_HOST=your-redis-cluster-do-user-123456-0.db.ondigitalocean.com
REDIS_PASSWORD=your-redis-password
REDIS_PORT=25061
```

---

## Security Notes

1. **Never commit `.env` file** - It's already in `.gitignore`
2. **Use strong passwords** - At least 16 characters, mix of letters, numbers, symbols
3. **Keep credentials secure** - Don't share `.env` file
4. **Rotate passwords periodically** - Especially database and email passwords

---

## Testing Your .env Configuration

### Test Database Connection:
```bash
php artisan tinker
DB::connection()->getPdo();
# Should return: PDO object
exit
```

### Test Email Configuration:
```bash
php artisan tinker
Mail::raw('Test email', function($message) {
    $message->to('your-email@gmail.com')->subject('Test');
});
```

### Test Redis Connection:
```bash
php artisan tinker
Redis::ping();
# Should return: "PONG"
exit
```

---

## Common Issues

### Issue: "No application encryption key has been specified"
**Solution:** Run `php artisan key:generate`

### Issue: "SQLSTATE[HY000] [1045] Access denied"
**Solution:** Check database credentials in `.env` file

### Issue: "Connection refused" (Redis)
**Solution:** Make sure Redis is running: `sudo systemctl status redis`

### Issue: "Could not send email"
**Solution:** 
- Check AWS SES credentials are correct (in admin panel → Settings → Email)
- Make sure email address is verified in Amazon SES
- Check AWS region is correct (ap-southeast-1 recommended)
- Verify IAM user has SES permissions
- Check SES is out of sandbox mode (if sending to unverified emails)

---

## Quick Reference

### Local Development:
- Use `.env.example` as template
- `APP_DEBUG=true`
- `QUEUE_CONNECTION=sync`
- `SESSION_DRIVER=file`
- `CACHE_STORE=file`

### Production:
- Use `env.production.example` as template
- `APP_DEBUG=false`
- `QUEUE_CONNECTION=redis`
- `SESSION_DRIVER=redis`
- `CACHE_STORE=redis`

---

**Remember:** The `.env` file is environment-specific. You'll create a new one on your production server with production values.

