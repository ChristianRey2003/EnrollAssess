# Pre-Deployment Environment Variables Checklist

## ✅ Prepare NOW (Before Creating Droplet)

These you can set up right away:

### 1. Pusher Account & Credentials ⭐ **DO THIS NOW**
- [ ] Sign up at https://pusher.com (free tier available)
- [ ] Create a new app
- [ ] Choose cluster: **ap1** (Asia-Pacific - closest to Philippines)
- [ ] Copy these values (save them securely):
  - [ ] `PUSHER_APP_ID` = ________________
  - [ ] `PUSHER_APP_KEY` = ________________
  - [ ] `PUSHER_APP_SECRET` = ________________
  - [ ] `PUSHER_APP_CLUSTER` = ap1

**Time needed:** 5 minutes  
**Why now:** You'll need this during deployment

---

### 2. Amazon SES Credentials ⭐ **DO THIS NOW**
- [ ] Sign up for AWS account (if you don't have one)
- [ ] Go to AWS Console → SES (Simple Email Service)
- [ ] Verify your sender email address (or domain)
- [ ] Create IAM user with SES permissions
- [ ] Generate Access Key ID and Secret Access Key
- [ ] Save these credentials securely:
  - [ ] `AWS_ACCESS_KEY_ID` = ________________
  - [ ] `AWS_SECRET_ACCESS_KEY` = ________________
  - [ ] `AWS_REGION` = ap-southeast-1 (or your preferred region)
  - [ ] `MAIL_FROM_ADDRESS` = your-verified-email@your-domain.com

**Time needed:** 15-30 minutes  
**Why now:** You'll need this during deployment  
**Note:** Your system uses database configuration for SES, but you'll need AWS credentials ready

---

### 3. Domain Name (Optional but Recommended)
- [ ] Purchase domain name (if you don't have one)
- [ ] Or decide to use droplet IP initially
- [ ] Note: `APP_URL` = https://your-domain.com (or http://droplet-ip)

**Time needed:** 10-30 minutes (if purchasing)  
**Why now:** Helps with SSL setup, but can be done later

---

## ⏳ Prepare DURING Deployment

These you'll set up on the server:

### 1. Database Credentials
- [ ] Create database: `enrollassess`
- [ ] Create database user: `enrollassess_user`
- [ ] Set strong database password
- [ ] Values:
  - `DB_HOST` = 127.0.0.1
  - `DB_PORT` = 3306
  - `DB_DATABASE` = enrollassess
  - `DB_USERNAME` = enrollassess_user
  - `DB_PASSWORD` = (you'll create this during setup)

**When:** During Step 4 (Database Setup)  
**Why then:** You create these on the server

---

### 2. Application Key
- [ ] Run: `php artisan key:generate`
- [ ] This auto-generates `APP_KEY`

**When:** During Step 3 (Deploy Application)  
**Why then:** Generated on server automatically

---

### 3. Redis Password (Optional)
- [ ] If you set Redis password, use it
- [ ] If not, leave as `null`
- [ ] Values:
  - `REDIS_HOST` = 127.0.0.1
  - `REDIS_PASSWORD` = null (or your password)
  - `REDIS_PORT` = 6379

**When:** During Step 2 (Install Software)  
**Why then:** Configured on server

---

## 📋 Complete Checklist

### Before Creating Droplet:
- [x] Pusher account created
- [x] Pusher credentials saved
- [x] Amazon SES account set up
- [x] AWS Access Key ID saved
- [x] AWS Secret Access Key saved
- [x] Email address verified in SES
- [ ] Domain name (optional)

### During Deployment:
- [ ] Database password created
- [ ] Application key generated
- [ ] All values filled in `.env` file

---

## 🎯 Recommended Timeline

### This Week (Before Droplet):
1. **Day 1:** Set up Pusher account (5 min)
2. **Day 1:** Set up Gmail app password (5 min)
3. **Day 1-2:** Purchase domain (if needed) (10-30 min)

**Total time:** 20-40 minutes

### During Deployment:
- Fill in database credentials (created on server)
- Generate application key (automatic)
- Copy all values into `.env` file

**Total time:** 10-15 minutes

---

## 💡 Why Prepare Now?

**Benefits:**
- ✅ Faster deployment (less waiting during setup)
- ✅ Less stress (credentials ready)
- ✅ Can test Pusher/Gmail setup before deployment
- ✅ Smoother deployment process

**What happens if you don't prepare:**
- ⚠️ You'll need to pause deployment to set up Pusher
- ⚠️ You'll need to pause to get Gmail app password
- ⚠️ Deployment takes longer (30-60 min extra)

---

## 🔐 Where to Store Credentials (Securely)

**Options:**
1. **Password Manager** (Recommended)
   - LastPass, 1Password, Bitwarden
   - Encrypted and secure

2. **Encrypted Note**
   - Keepass, encrypted text file
   - Don't use plain text files

3. **Physical Note** (Temporary)
   - Write down, use during deployment
   - Destroy after deployment

**Never:**
- ❌ Store in email
- ❌ Store in plain text files
- ❌ Commit to git
- ❌ Share in chat/messages

---

## 📝 Quick Reference Template

Save this template and fill it in:

```
=== PUSHER CREDENTIALS ===
App ID: ________________
App Key: ________________
App Secret: ________________
Cluster: ap1

=== AMAZON SES CREDENTIALS ===
AWS Access Key ID: ________________
AWS Secret Access Key: ________________
AWS Region: ap-southeast-1
Verified Email: ________________

=== DOMAIN (Optional) ===
Domain: ________________
Or use droplet IP: ________________

=== DATABASE (Will create during deployment) ===
Database Name: enrollassess
Database User: enrollassess_user
Database Password: (create during setup)
```

---

## ✅ Action Items

**Do NOW (before droplet):**
1. [ ] Create Pusher account → Get credentials
2. [ ] Set up Amazon SES → Get AWS credentials
3. [ ] Verify email address in SES
4. [ ] Save credentials securely
5. [ ] (Optional) Purchase domain

**Do DURING deployment:**
1. [ ] Create database and user
2. [ ] Generate application key
3. [ ] Fill in `.env` file with all values
4. [ ] Test connections

---

**Bottom Line:** Prepare Pusher and Amazon SES credentials NOW. Everything else can be done during deployment!

**Note:** Your system configures SES through the admin panel (database settings), but you'll need AWS credentials ready to enter there.

