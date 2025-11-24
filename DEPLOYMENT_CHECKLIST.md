# 🚀 EnrollAssess Deployment Checklist

## ⏰ BEFORE DEPLOYMENT (Do This Now)

### ✅ Step 1: Get Pusher Credentials (5 minutes)
- [ ] Sign up at https://pusher.com (free tier available)
- [ ] Create new app
- [ ] Choose cluster: **ap1** (Asia-Pacific)
- [ ] Copy and save these values:
  - [ ] `PUSHER_APP_ID` = ________________
  - [ ] `PUSHER_APP_KEY` = ________________
  - [ ] `PUSHER_APP_SECRET` = ________________
  - [ ] `PUSHER_APP_CLUSTER` = ap1

**Note:** Your `env.production.example` already has Pusher values (lines 70-73). Verify if these are correct or update them.

---

### ✅ Step 2: Get Amazon SES Credentials (15-30 minutes)
- [ ] Sign up for AWS account (if you don't have one)
- [ ] Go to AWS Console → SES (Simple Email Service)
- [ ] Verify your sender email address (or domain)
- [ ] Go to IAM → Users → Create user with SES permissions
- [ ] Generate Access Key ID and Secret Access Key
- [ ] Save these credentials securely:
  - [ ] `AWS_ACCESS_KEY_ID` = ________________
  - [ ] `AWS_SECRET_ACCESS_KEY` = ________________
  - [ ] `AWS_DEFAULT_REGION` = ap-southeast-1
  - [ ] `MAIL_FROM_ADDRESS` = your-verified-email@your-domain.com

**Important:** Your system stores SES credentials in the database (admin panel → Settings → Email), but you'll need AWS credentials ready to enter there.

---

### ✅ Step 3: Domain Setup (Optional but Recommended)
- [ ] Purchase domain name (if you don't have one)
- [ ] Or decide to use droplet IP initially
- [ ] Note: `APP_URL` = https://your-domain.com (or http://droplet-ip)

---

### ✅ Step 4: Clean Up Files Before Committing
- [ ] Remove test files (Excel, PDF, CSV, images) - see `PRE_DEPLOYMENT_CHECKLIST.md`
- [ ] Verify `.env` is in `.gitignore` (should already be there)
- [ ] Check `git status` - ensure no sensitive files are staged
- [ ] Commit and push code to GitHub/GitLab (yanix branch)

---

## ⏳ DURING DEPLOYMENT (On Server)

### ✅ Step 1: Create `.env` File
```bash
cp env.production.example .env
nano .env
```

### ✅ Step 2: Fill in Required Values in `.env`

#### **Application Settings** (Lines 4-10)
```env
APP_NAME="EnrollAssess"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE  # ← Will be generated automatically
APP_DEBUG=false
APP_URL=https://your-domain.com  # ← CHANGE THIS
```

#### **Database Settings** (Lines 19-25)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=enrollassess
DB_USERNAME=enrollassess_user
DB_PASSWORD=your-strong-database-password  # ← CHANGE THIS (create during setup)
```

#### **Redis Settings** (Lines 32-38)
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null  # ← Set if you configured Redis with password
REDIS_PORT=6379
```

#### **Mail Settings** (Lines 54-65)
```env
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=your-verified-email@your-domain.com  # ← CHANGE THIS
MAIL_FROM_NAME="EnrollAssess System"

AWS_ACCESS_KEY_ID=your-aws-access-key-id  # ← CHANGE THIS (from Step 2)
AWS_SECRET_ACCESS_KEY=your-aws-secret-key  # ← CHANGE THIS (from Step 2)
AWS_DEFAULT_REGION=ap-southeast-1
```

#### **Pusher Settings** (Lines 67-73)
```env
BROADCAST_CONNECTION=pusher
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-pusher-app-id  # ← CHANGE THIS (from Step 1)
PUSHER_APP_KEY=your-pusher-key  # ← CHANGE THIS (from Step 1)
PUSHER_APP_SECRET=your-pusher-secret  # ← CHANGE THIS (from Step 1)
PUSHER_APP_CLUSTER=ap1
```

#### **Frontend Configuration** (Lines 75-79)
```env
VITE_BROADCAST_DRIVER=pusher
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"  # ← Uses value from above
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"  # ← Uses value from above
VITE_APP_NAME="${APP_NAME}"  # ← Uses value from above
```

### ✅ Step 3: Generate Application Key
```bash
php artisan key:generate
```
This automatically fills in `APP_KEY` in your `.env` file.

### ✅ Step 4: Create Database
```bash
sudo mysql
CREATE DATABASE enrollassess CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'enrollassess_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON enrollassess.* TO 'enrollassess_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Then update `DB_PASSWORD` in `.env` with the password you just created.

### ✅ Step 5: Run Migrations
```bash
php artisan migrate --force
php artisan storage:link
```

### ✅ Step 6: Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📋 Quick Reference: What to Change in `.env`

| Variable | Where to Get It | Status |
|----------|----------------|--------|
| `APP_URL` | Your domain or droplet IP | ⚠️ **MUST CHANGE** |
| `APP_KEY` | Auto-generated with `php artisan key:generate` | ✅ Auto |
| `DB_PASSWORD` | Create during database setup | ⚠️ **MUST CHANGE** |
| `AWS_ACCESS_KEY_ID` | AWS IAM (Step 2 above) | ⚠️ **MUST CHANGE** |
| `AWS_SECRET_ACCESS_KEY` | AWS IAM (Step 2 above) | ⚠️ **MUST CHANGE** |
| `MAIL_FROM_ADDRESS` | Your verified SES email | ⚠️ **MUST CHANGE** |
| `PUSHER_APP_ID` | Pusher dashboard (Step 1 above) | ⚠️ **MUST CHANGE** |
| `PUSHER_APP_KEY` | Pusher dashboard (Step 1 above) | ⚠️ **MUST CHANGE** |
| `PUSHER_APP_SECRET` | Pusher dashboard (Step 1 above) | ⚠️ **MUST CHANGE** |
| `REDIS_PASSWORD` | Only if you set Redis password | ⚠️ Optional |

---

## 🔐 Security Checklist

- [ ] `APP_DEBUG=false` (line 8)
- [ ] `APP_ENV=production` (line 6)
- [ ] Strong database password (at least 16 characters)
- [ ] `.env` file is NOT committed to git
- [ ] All credentials stored securely (password manager recommended)

---

## 📚 Additional Resources

- **Full Deployment Guide:** `DEPLOYMENT_QUICK_START.md`
- **Environment Setup:** `ENV_SETUP_GUIDE.md`
- **Pre-Deployment Cleanup:** `PRE_DEPLOYMENT_CHECKLIST.md`
- **DigitalOcean Guide:** `DIGITALOCEAN_DEPLOYMENT_GUIDE.md`

---

## ✅ Final Checklist Before Going Live

- [ ] All credentials prepared (Pusher, AWS SES)
- [ ] `.env` file configured with all values
- [ ] Database created and migrations run
- [ ] Application key generated
- [ ] Nginx configured and SSL installed
- [ ] Queue workers running (Supervisor)
- [ ] Admin user created
- [ ] Test login works
- [ ] Email sending works (test from admin panel)
- [ ] Redis connection works

---

**Last Updated:** Based on `env.production.example` and deployment guides  
**Estimated Setup Time:** 2-3 hours (first time)

