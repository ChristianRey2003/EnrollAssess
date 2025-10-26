# Digital Ocean Deployment Guide 🚀

## EnrollAssess System - Phase 2 Complete

This guide will help you deploy the EnrollAssess system with real-time updates and analytics to Digital Ocean.

---

## 📋 Prerequisites

- Digital Ocean account (create at https://digitalocean.com)
- Domain name (optional, but recommended)
- Basic command line knowledge
- SSH client

---

## 🖥️ Server Setup

### Step 1: Create Droplet

1. **Log in to Digital Ocean**
2. **Click "Create" → "Droplets"**
3. **Choose Configuration:**
   - **Image:** Ubuntu 22.04 LTS
   - **Plan:** Basic ($6/month minimum for production)
     - 1 GB RAM
     - 1 vCPU
     - 25 GB SSD
   - **Datacenter:** Choose closest to your users
   - **Authentication:** SSH key (recommended) or Password
   - **Hostname:** enrollassess-production

4. **Click "Create Droplet"**

---

## 🔧 Server Initial Configuration

### Step 2: Connect to Your Server

```bash
ssh root@your_droplet_ip
```

### Step 3: Update System

```bash
apt update && apt upgrade -y
```

### Step 4: Create Non-root User

```bash
adduser enrollassess
usermod -aG sudo enrollassess
```

Switch to new user:
```bash
su - enrollassess
```

---

## 📦 Install Required Software

### Step 5: Install LEMP Stack

#### Install Nginx
```bash
sudo apt install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx
```

#### Install MySQL
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

Set root password and answer:
- Remove anonymous users? **Yes**
- Disallow root login remotely? **Yes**
- Remove test database? **Yes**
- Reload privilege tables? **Yes**

#### Install PHP 8.2
```bash
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd -y
```

### Step 6: Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

Verify:
```bash
composer --version
```

### Step 7: Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

Verify:
```bash
node --version
npm --version
```

### Step 8: Install Redis (for caching)

```bash
sudo apt install redis-server -y
sudo systemctl start redis
sudo systemctl enable redis
```

### Step 9: Install Supervisor (for queues)

```bash
sudo apt install supervisor -y
sudo systemctl start supervisor
sudo systemctl enable supervisor
```

---

## 📁 Deploy Application

### Step 10: Clone Repository

```bash
cd /var/www
sudo git clone YOUR_REPOSITORY_URL enrollassess
sudo chown -R enrollassess:enrollassess /var/www/enrollassess
cd enrollassess
```

### Step 11: Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Step 12: Set Permissions

```bash
sudo chown -R enrollassess:www-data /var/www/enrollassess
sudo chmod -R 755 /var/www/enrollassess
sudo chmod -R 775 /var/www/enrollassess/storage
sudo chmod -R 775 /var/www/enrollassess/bootstrap/cache
```

---

## ⚙️ Configure Application

### Step 13: Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```bash
nano .env
```

Update these values:
```env
APP_NAME="EnrollAssess"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your_domain_or_ip

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=enrollassess
DB_USERNAME=enrollassess_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

BROADCAST_DRIVER=log
# If using Pusher, change to:
# BROADCAST_DRIVER=pusher
# PUSHER_APP_ID=your_app_id
# PUSHER_APP_KEY=your_app_key
# PUSHER_APP_SECRET=your_app_secret
# PUSHER_APP_CLUSTER=your_cluster

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 14: Setup Database

```bash
sudo mysql
```

```sql
CREATE DATABASE enrollassess CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'enrollassess_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON enrollassess.* TO 'enrollassess_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Run migrations:
```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## 🌐 Configure Nginx

### Step 15: Create Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/enrollassess
```

Add this configuration:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your_domain_or_ip;
    root /var/www/enrollassess/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/enrollassess /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔄 Configure Queue Worker

### Step 16: Setup Supervisor for Queues

```bash
sudo nano /etc/supervisor/conf.d/enrollassess-worker.conf
```

Add:
```ini
[program:enrollassess-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/enrollassess/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=enrollassess
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/enrollassess/storage/logs/worker.log
stopwaitsecs=3600
```

Update supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start enrollassess-worker:*
```

---

## ⏰ Configure Cron Job

### Step 17: Setup Laravel Scheduler

```bash
crontab -e
```

Add:
```cron
* * * * * cd /var/www/enrollassess && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔒 Setup SSL (Optional but Recommended)

### Step 18: Install Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
```

### Step 19: Obtain SSL Certificate

```bash
sudo certbot --nginx -d your_domain.com -d www.your_domain.com
```

Follow prompts. Certbot will automatically:
- Obtain certificate
- Update Nginx configuration
- Setup auto-renewal

---

## 🎯 Enable Real-time Updates (Optional)

### Option 1: Using Pusher (Recommended)

1. **Sign up at https://pusher.com (free tier available)**
2. **Create new app**
3. **Get credentials**
4. **Update `.env`:**
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster

VITE_BROADCAST_DRIVER=pusher
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

5. **Rebuild assets:**
```bash
npm run build
```

6. **Clear cache:**
```bash
php artisan config:clear
php artisan cache:clear
```

### Option 2: Without Real-time (Polling)

The system works perfectly with 30-second polling without any additional setup!

Just keep:
```env
BROADCAST_DRIVER=log
```

---

## 🔍 Verification Checklist

### Test Your Deployment

- [ ] Website loads: `http://your_domain_or_ip`
- [ ] Login works
- [ ] Dashboard displays
- [ ] Statistics update (wait 30 seconds)
- [ ] Analytics page loads with charts
- [ ] Email sending works (test email)
- [ ] File uploads work
- [ ] Database queries execute
- [ ] Queue jobs process
- [ ] No errors in logs

### Check Logs

```bash
# Laravel logs
tail -f /var/www/enrollassess/storage/logs/laravel.log

# Nginx error log
sudo tail -f /var/nginx/error.log

# Queue worker log
sudo tail -f /var/www/enrollassess/storage/logs/worker.log
```

---

## 🔧 Maintenance Commands

### Update Application

```bash
cd /var/www/enrollassess
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear
sudo supervisorctl restart enrollassess-worker:*
```

### Clear All Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
redis-cli FLUSHALL
```

### Restart Services

```bash
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart redis
sudo supervisorctl restart enrollassess-worker:*
```

---

## 📊 Monitoring

### Check System Resources

```bash
# CPU and Memory
htop

# Disk usage
df -h

# Database size
sudo mysql -e "SELECT table_schema AS 'Database', 
ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' 
FROM information_schema.TABLES 
GROUP BY table_schema;"
```

### Monitor Queue

```bash
# Check queue status
php artisan queue:work --once

# Monitor in real-time
sudo supervisorctl tail -f enrollassess-worker:enrollassess-worker_00 stdout
```

---

## 🚨 Troubleshooting

### Issue: 500 Internal Server Error

**Solution:**
```bash
# Check permissions
sudo chown -R enrollassess:www-data /var/www/enrollassess
sudo chmod -R 775 storage bootstrap/cache

# Check logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### Issue: Charts Not Loading

**Solution:**
```bash
# Rebuild assets
npm run build

# Check if files exist
ls -la public/build/

# Clear browser cache
```

### Issue: Real-time Updates Not Working

**Solution:**
1. Check Pusher credentials in `.env`
2. Verify `VITE_` variables are set
3. Rebuild assets: `npm run build`
4. Check browser console for errors
5. Polling fallback should still work (30s)

### Issue: Queue Jobs Not Processing

**Solution:**
```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart enrollassess-worker:*

# Check worker log
sudo tail -f /var/www/enrollassess/storage/logs/worker.log
```

---

## 💰 Cost Estimate

### Monthly Costs (Digital Ocean)

**Minimum Setup:**
- **Droplet (1GB):** $6/month
- **Backups (optional):** $1.20/month
- **Domain (yearly):** ~$12/year = $1/month
- **SSL Certificate:** FREE (Let's Encrypt)
- **Total:** ~$8-9/month

**Recommended Setup:**
- **Droplet (2GB):** $12/month
- **Backups:** $2.40/month
- **Managed Database:** $15/month (optional)
- **CDN:** $0-5/month (pay as you go)
- **Total:** ~$15-35/month

**With Real-time (Pusher):**
- **Basic Plan:** FREE (200k messages/day)
- **Pusher Standard:** $49/month (unlimited messages)

---

## 🎉 Deployment Complete!

Your EnrollAssess system is now live with:

✅ **Real-time dashboard updates** (with polling fallback)  
✅ **Interactive analytics** with charts  
✅ **Secure SSL** encryption (if configured)  
✅ **Auto-scaling** queue workers  
✅ **Optimized performance** with Redis caching  
✅ **Professional deployment** on Digital Ocean  

### Access Your System

- **Website:** http://your_domain_or_ip
- **Admin Login:** Use credentials from database seeder
- **Analytics:** /admin/analytics

### Need Help?

- Check logs: `storage/logs/laravel.log`
- Review this guide
- Check Digital Ocean documentation
- Contact support team

---

**Congratulations! 🎊 Your system is production-ready!**

