# EnrollAssess - Deployment Quick Start 🚀

## TL;DR - Fastest Path to Production

### Budget: $18-33/month
### Time: 2-3 hours
### Difficulty: Medium (follow guide carefully)

---

## Pre-Flight Checklist ✈️

Before you start, have these ready:

- [ ] **DigitalOcean Account** (create at digitalocean.com)
- [ ] **Domain Name** (optional but recommended)
- [ ] **Pusher Account** (free tier at pusher.com)
- [ ] **Gmail App Password** (for email notifications)
- [ ] **Your Code Pushed to GitHub/GitLab**

---

## 5-Step Quick Deploy

### Step 1: Create Droplet (5 minutes)
```
1. Log in to DigitalOcean
2. Click "Create" → "Droplets"
3. Choose:
   - Ubuntu 24.04 LTS
   - Basic Plan: $18/month (2GB/2vCPU)
   - Singapore region
4. Set root password or SSH key
5. Create Droplet
6. Note your IP address (e.g., 134.122.45.67)
```

### Step 2: Run Install Script (30 minutes)
```bash
# Connect to your server
ssh root@YOUR_DROPLET_IP

# Update system
apt update && apt upgrade -y

# Run this mega command (installs everything)
apt install -y nginx mysql-server redis-server supervisor curl git unzip \
    software-properties-common && \
    add-apt-repository ppa:ondrej/php -y && apt update && \
    apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip \
    php8.2-gd php8.2-intl libreoffice-calc libreoffice-writer \
    --no-install-recommends && \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt install -y nodejs && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

# Verify installations
php -v        # Should show PHP 8.2.x
node -v       # Should show v20.x.x
composer -v   # Should show version
soffice -v    # Should show LibreOffice version
```

### Step 3: Deploy Your App (20 minutes)
```bash
# Create deployer user
adduser deployer
usermod -aG sudo deployer
su - deployer

# Clone your app
cd /var/www
sudo mkdir enrollassess
sudo chown deployer:www-data enrollassess
cd enrollassess
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git .

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# Configure environment
cp env.production.example .env
nano .env  # Edit with your actual credentials

# Setup database
sudo mysql
CREATE DATABASE enrollassess CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'enrollassess_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON enrollassess.* TO 'enrollassess_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan key:generate
php artisan migrate --force
php artisan storage:link

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
sudo chown -R deployer:www-data /var/www/enrollassess
sudo chmod -R 775 /var/www/enrollassess/storage
sudo chmod -R 775 /var/www/enrollassess/bootstrap/cache
```

### Step 4: Configure Nginx + SSL (15 minutes)
```bash
# Create Nginx config
sudo nano /etc/nginx/sites-available/enrollassess
```

**Paste this (replace YOUR_DOMAIN):**
```nginx
server {
    listen 80;
    server_name YOUR_DOMAIN;
    root /var/www/enrollassess/public;
    index index.php;
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
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

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/enrollassess /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx

# Install SSL (if you have domain pointed)
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d YOUR_DOMAIN
```

### Step 5: Setup Queue Workers (10 minutes)
```bash
# Create supervisor config
sudo nano /etc/supervisor/conf.d/enrollassess-worker.conf
```

**Paste this:**
```ini
[program:enrollassess-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/enrollassess/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
user=deployer
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/enrollassess/storage/logs/worker.log
```

```bash
# Start workers
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start enrollassess-worker:*

# Setup cron
crontab -e
# Add this line:
* * * * * cd /var/www/enrollassess && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🎉 You're Live!

### Create Admin User
```bash
cd /var/www/enrollassess
php artisan tinker

# In tinker:
$user = new App\Models\User();
$user->username = 'admin';
$user->password_hash = Hash::make('YourSecurePassword123!');
$user->full_name = 'System Administrator';
$user->role = 'administrator';
$user->email = 'admin@yourschool.edu';
$user->save();
exit
```

### Test Your Site
```
Visit: https://YOUR_DOMAIN/admin/login
Login with: admin / YourSecurePassword123!
```

---

## Environment Variables to Edit

**Critical ones in `.env`:**
```env
APP_URL=https://YOUR_DOMAIN
APP_DEBUG=false

DB_HOST=127.0.0.1
DB_DATABASE=enrollassess
DB_USERNAME=enrollassess_user
DB_PASSWORD=YOUR_STRONG_PASSWORD

REDIS_PASSWORD=YOUR_REDIS_PASSWORD

MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-gmail-app-password

BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=ap1
```

---

## Cost Breakdown

### Minimum Setup ($18/month)
- Droplet (2GB): $18/month
- Domain: ~$12/year ($1/month)
- SSL: Free (Let's Encrypt)
- Pusher: Free tier
- **Total: ~$19/month**

### Recommended Setup ($33/month)
- Droplet (2GB): $18/month
- Managed MySQL: $15/month
- Domain: ~$12/year
- SSL: Free
- Pusher: Free tier
- **Total: ~$34/month**

---

## Maintenance Commands

### View Logs
```bash
tail -f /var/www/enrollassess/storage/logs/laravel.log
```

### Restart Services
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart enrollassess-worker:*
```

### Update App
```bash
cd /var/www/enrollassess
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci --production && npm run build
php artisan migrate --force
php artisan optimize:clear && php artisan config:cache
sudo supervisorctl restart enrollassess-worker:*
php artisan up
```

### Backup Database
```bash
mysqldump -u enrollassess_user -p enrollassess > backup_$(date +%Y%m%d).sql
```

---

## Troubleshooting

### 500 Error
```bash
# Check permissions
sudo chown -R deployer:www-data /var/www/enrollassess
sudo chmod -R 775 /var/www/enrollassess/storage

# Check logs
tail -f /var/www/enrollassess/storage/logs/laravel.log
```

### Can't Generate PDF
```bash
# Verify LibreOffice
which soffice
soffice --headless --version

# Test conversion
cd /tmp
echo "test" > test.txt
soffice --headless --convert-to pdf test.txt
```

### Queue Not Working
```bash
sudo supervisorctl status
sudo supervisorctl restart enrollassess-worker:*
tail -f /var/www/enrollassess/storage/logs/worker.log
```

---

## Security Checklist

```bash
# Setup firewall
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Install fail2ban
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
```

---

## What's Included

✅ **System:** Ubuntu 24.04 + Nginx + PHP 8.2 + MySQL + Redis  
✅ **App:** Laravel 12 with all dependencies  
✅ **SSL:** Free Let's Encrypt certificate  
✅ **Queue:** Background job processing  
✅ **Cron:** Scheduled tasks  
✅ **LibreOffice:** PDF export capability  
✅ **Security:** Firewall + Rate limiting + Fail2Ban  

---

## Need More Help?

📖 **Full Guide:** See `DIGITALOCEAN_DEPLOYMENT_GUIDE.md` (50+ pages)  
🔧 **Fixes Applied:** See `PRODUCTION_READINESS_FIXES.md`  
💬 **Support:** DigitalOcean Community or Laravel Forums  

---

## Quick Resource Check

```bash
# Check system resources
htop              # Press 'q' to quit
df -h             # Disk space
free -h           # Memory usage
sudo supervisorctl status  # Queue workers
systemctl status nginx php8.2-fpm mysql redis
```

---

**Ready? Let's deploy! 🚀**

Start with Step 1 above, or read the full guide in `DIGITALOCEAN_DEPLOYMENT_GUIDE.md` for detailed explanations.

---

**Last Updated:** November 6, 2025  
**Tested On:** DigitalOcean Ubuntu 24.04 LTS  
**Estimated Setup Time:** 2-3 hours (first time)

