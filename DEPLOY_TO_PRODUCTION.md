# 🚀 Deploy to Production - Quick Guide

## 📋 Pre-Deployment Checklist

Before deploying, make sure you have:
- [ ] All changes committed to Git (or ready to upload)
- [ ] Tested locally that everything works
- [ ] Resend API key ready
- [ ] Production server SSH access
- [ ] Database backup (recommended)

---

## 🎯 Quick Deployment Steps

### Option 1: Using Git (Recommended)

If your code is in a Git repository:

#### Step 1: Commit Your Changes Locally

```bash
# On your local machine (Windows PowerShell)
cd C:\laragon\www\EnrollAssess

# Check what files changed
git status

# Add all changes
git add .

# Commit changes
git commit -m "Add Resend email integration and update email links"

# Push to repository (if using GitHub/GitLab)
git push origin main
```

#### Step 2: Deploy to Server

**Connect to your server:**
```bash
ssh root@YOUR_DROPLET_IP
# or
ssh deployer@YOUR_DROPLET_IP
```

**On the server, run these commands:**
```bash
# Navigate to application directory
cd /var/www/enrollassess

# Put application in maintenance mode
php artisan down

# Pull latest changes
git pull origin main

# Install/update PHP dependencies (including new Resend packages)
composer install --no-dev --optimize-autoloader

# Install/update Node.js dependencies
npm ci --production

# Build frontend assets
npm run build

# Run database migrations (if any)
php artisan migrate --force

# Clear all caches
php artisan optimize:clear

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue workers
sudo supervisorctl restart enrollassess-worker:*

# Set permissions (if needed)
sudo chown -R deployer:www-data /var/www/enrollassess
sudo chmod -R 775 /var/www/enrollassess/storage
sudo chmod -R 775 /var/www/enrollassess/bootstrap/cache

# Bring application back online
php artisan up
```

✅ **Deployment Complete!**

---

### Option 2: Upload via SCP (If not using Git)

#### Step 1: Prepare Files Locally

```bash
# On your local machine (Windows PowerShell)
cd C:\laragon\www\EnrollAssess

# Create a deployment package (exclude unnecessary files)
# Note: You'll need to manually exclude node_modules, vendor, etc.
```

#### Step 2: Upload to Server

```bash
# Upload files to server (from your local machine)
scp -r * root@YOUR_DROPLET_IP:/var/www/enrollassess/

# Or upload specific directories only:
scp -r app config database routes resources root@YOUR_DROPLET_IP:/var/www/enrollassess/
scp composer.json composer.lock package.json root@YOUR_DROPLET_IP:/var/www/enrollassess/
```

#### Step 3: On Server - Install Dependencies

```bash
# SSH into server
ssh root@YOUR_DROPLET_IP

# Navigate to application
cd /var/www/enrollassess

# Put in maintenance mode
php artisan down

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm ci --production

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart workers
sudo supervisorctl restart enrollassess-worker:*

# Set permissions
sudo chown -R deployer:www-data /var/www/enrollassess
sudo chmod -R 775 storage bootstrap/cache

# Bring online
php artisan up
```

---

## ⚙️ Post-Deployment Configuration

### 1. Configure Resend in Admin Panel

After deployment, you need to configure Resend:

1. **Login to admin panel:** `https://enrollassess-evsu.com/admin/login`
2. **Go to:** Settings → Email Settings
3. **Configure:**
   - **Mail Driver:** Select "Resend (Recommended - FREE)"
   - **Resend API Key:** Enter your API key from https://resend.com/api-keys
   - **From Address:** `noreply@enrollassess-evsu.com` (or your verified email)
   - **From Name:** `EnrollAssess System`
4. **Click:** "Save Settings"
5. **Test:** Click "Test Email Configuration" to verify it works

### 2. Verify Everything Works

- [ ] Admin login works
- [ ] Email settings page loads
- [ ] Resend API key saves correctly
- [ ] Test email sends successfully
- [ ] Exam notifications send correctly
- [ ] Email links point to `https://enrollassess-evsu.com/applicant/login`

---

## 🔧 Important Notes

### New Packages Installed

Your `composer.json` now includes:
- `resend/resend-php: ^0.10.0`
- `symfony/resend-mailer: ^7.3`

These will be installed automatically when you run `composer install` on the server.

### Environment Variables

Make sure your `.env` file on the server has:
```env
APP_URL=https://enrollassess-evsu.com
APP_ENV=production
APP_DEBUG=false
```

**Note:** You don't need to add Resend credentials to `.env` - they're stored in the database via the admin panel.

### Database Settings

The Resend API key is stored in the `system_settings` table, not in `.env`. This means:
- ✅ You can change email providers without editing `.env`
- ✅ Settings are managed through the admin panel
- ✅ No need to restart services when changing email settings

---

## 🚨 Troubleshooting

### Issue: "Class 'Resend' not found" after deployment

**Solution:**
```bash
cd /var/www/enrollassess
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan cache:clear
```

### Issue: Email links still use old URL

**Solution:**
```bash
php artisan view:clear
php artisan cache:clear
```

### Issue: Settings not saving

**Solution:**
```bash
# Check database connection
php artisan tinker
>>> App\Models\Settings::count()
# Should return a number

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### Issue: Queue workers not processing emails

**Solution:**
```bash
# Check worker status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart enrollassess-worker:*

# Check logs
tail -f /var/www/enrollassess/storage/logs/worker.log
```

---

## 📝 One-Line Deployment Script

For quick updates, you can create a deployment script:

**On server, create:** `/var/www/enrollassess/deploy.sh`

```bash
#!/bin/bash
cd /var/www/enrollassess
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci --production && npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart enrollassess-worker:*
php artisan up
echo "Deployment complete!"
```

**Make it executable:**
```bash
chmod +x /var/www/enrollassess/deploy.sh
```

**Run it:**
```bash
./deploy.sh
```

---

## ✅ Verification Checklist

After deployment, verify:

- [ ] Website loads: `https://enrollassess-evsu.com`
- [ ] Admin login works
- [ ] Email settings page shows Resend option
- [ ] Can save Resend API key
- [ ] Test email sends successfully
- [ ] Exam notification emails work
- [ ] Email links use production URL
- [ ] Queue workers are running
- [ ] No errors in logs

---

## 🎉 Summary

**Quick deployment command sequence:**

```bash
cd /var/www/enrollassess
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci --production && npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart enrollassess-worker:*
php artisan up
```

**Then configure Resend in admin panel!**

---

**Need help?** Check the full deployment guide: `DEPLOYMENT_STEPS.md`

