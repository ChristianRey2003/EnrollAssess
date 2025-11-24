# EnrollAssess - Step-by-Step Deployment Guide

## Your Setup
- **Droplet:** $48/month (8 GB RAM, 2 Intel CPUs, 160 GB NVMe SSD)
- **Managed MySQL Database:** $15/month (already created)
- **Region:** Singapore
- **OS:** Ubuntu 24.04 LTS

---

## STEP 1: Connect to Your Droplet (2 minutes)

1. **Get your droplet IP address** from DigitalOcean dashboard
2. **Open PowerShell or Git Bash** on your Windows machine
3. **Connect via SSH:**

```bash
ssh root@YOUR_DROPLET_IP
```

Replace `YOUR_DROPLET_IP` with your actual IP (e.g., `ssh root@134.122.45.67`)

4. **Type `yes`** when asked about fingerprint
5. **Enter your SSH key passphrase** (if you set one) or password

✅ **You should now be logged into your server**

---

## STEP 2: Update System & Install Basic Tools (5 minutes)

Run these commands one by one:

```bash
# Update package lists
apt update

# Upgrade system packages
apt upgrade -y

# ⚠️ NOTE: If you see a configuration file conflict prompt during upgrade:
#   - Select "keep the local version currently installed" (usually option 2)
#   - This preserves your SSH configuration and other custom settings
#   - Press Tab to select, then Enter to confirm

# Install essential tools
apt install -y curl git unzip software-properties-common
```

---

## STEP 3: Install Nginx Web Server (3 minutes)

```bash
# Install Nginx
apt install -y nginx

# Start and enable Nginx
systemctl start nginx
systemctl enable nginx

# Check if it's running
systemctl status nginx
```

✅ **Test:** Open `http://YOUR_DROPLET_IP` in your browser - you should see "Welcome to nginx"

---

## STEP 4: Install PHP 8.2 (5 minutes)

```bash
# Add PHP repository
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP 8.2 and all required extensions
apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip \
    php8.2-gd php8.2-intl php8.2-soap

# Verify installation
php -v
```

✅ **Should show:** PHP 8.2.x

```bash
# Configure PHP for production
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 20M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 20M/' /etc/php/8.2/fpm/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini

# Restart PHP-FPM
systemctl restart php8.2-fpm
```

---

## STEP 5: Install Composer (2 minutes)

```bash
# Download and install Composer
cd ~
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Verify
composer --version
```

---

## STEP 6: Install Node.js & NPM (3 minutes)

```bash
# Install Node.js 20.x LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Verify
node -v   # Should show v20.x.x
npm -v    # Should show 10.x.x
```

---

## STEP 7: Install Redis (3 minutes)

```bash
# Install Redis
apt install -y redis-server

# Configure Redis
sed -i 's/supervised no/supervised systemd/' /etc/redis/redis.conf

# Set a password (replace YOUR_REDIS_PASSWORD with a strong password)
sed -i 's/# requirepass foobared/requirepass YOUR_REDIS_PASSWORD/' /etc/redis/redis.conf

# Restart Redis
systemctl restart redis
systemctl enable redis

# Test Redis
redis-cli
AUTH YOUR_REDIS_PASSWORD
PING
# Should return: PONG
exit
```

**⚠️ IMPORTANT:** Remember the Redis password you set - you'll need it for `.env` file

---

## STEP 8: Install LibreOffice (2 minutes)

```bash
# Install LibreOffice (required for PDF exports)
apt install -y libreoffice-calc libreoffice-writer --no-install-recommends

# Verify
soffice --version
```

---

## STEP 9: Install Supervisor (for Queue Workers) (1 minute)

```bash
# Install Supervisor
apt install -y supervisor

# Start and enable
systemctl start supervisor
systemctl enable supervisor
```

---

## STEP 10: Create Deployer User (2 minutes)

```bash
# Create deployer user
adduser deployer

# Add to sudo group
usermod -aG sudo deployer

# Copy SSH keys to deployer (if using SSH keys)
rsync --archive --chown=deployer:deployer ~/.ssh /home/deployer

# Switch to deployer user
su - deployer
```

---

## STEP 11: Get Managed Database Connection Details (5 minutes)

1. **Go to DigitalOcean Dashboard**
2. **Click on "Databases"** in left menu
3. **Click on your MySQL database**
4. **Go to "Connection Details" tab**
5. **Note down these details:**
   - **Host:** (e.g., `db-mysql-sgp1-12345-do-user-1234567-0.db.ondigitalocean.com`)
   - **Port:** (usually `25060`)
   - **Database:** (e.g., `defaultdb`)
   - **Username:** (e.g., `doadmin`)
   - **Password:** (click "Show" to reveal)
   - **SSL Mode:** Required

**⚠️ IMPORTANT:** Write these down - you'll need them in the next step!

---

## STEP 12: Deploy Your Application (15 minutes)

### 12.1: Create Application Directory

```bash
# Create web directory
sudo mkdir -p /var/www/enrollassess
sudo chown -R deployer:www-data /var/www/enrollassess

# Navigate to directory
cd /var/www/enrollassess
```

### 12.2: Clone Your Repository

**Option A: If your code is on GitHub/GitLab (Public Repository)**

```bash
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git .
```

**Option B: If your code is on GitHub/GitLab (Private Repository)**

```bash
# First, set up SSH key on GitHub/GitLab, then:
git clone git@github.com:YOUR_USERNAME/YOUR_REPO.git .
```

**Option C: Upload via SCP (from your local machine)**

On your **Windows machine** (PowerShell), run:

```bash
# Navigate to your project folder
cd C:\laragon\www\EnrollAssess

# Upload files to server
scp -r * root@YOUR_DROPLET_IP:/var/www/enrollassess/
```

Then on server:

```bash
cd /var/www/enrollassess
sudo chown -R deployer:www-data /var/www/enrollassess
```

### 12.3: Install Dependencies

```bash
# Install PHP dependencies (production mode)
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm ci --production

# Build frontend assets
npm run build
```

### 12.4: Configure Environment File

```bash
# Copy production environment file
cp env.production.example .env

# Edit environment file
nano .env
```

**In the nano editor, update these values:**

```env
APP_NAME="EnrollAssess"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR_DOMAIN_OR_IP

# Database Configuration (from Step 11)
DB_CONNECTION=mysql
DB_HOST=YOUR_MANAGED_DB_HOST
DB_PORT=YOUR_MANAGED_DB_PORT
DB_DATABASE=YOUR_MANAGED_DB_NAME
DB_USERNAME=YOUR_MANAGED_DB_USERNAME
DB_PASSWORD=YOUR_MANAGED_DB_PASSWORD

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=YOUR_REDIS_PASSWORD_FROM_STEP_7
REDIS_PORT=6379

# Cache/Session/Queue
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail Configuration (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-gmail-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourschool.edu
MAIL_FROM_NAME="${APP_NAME}"

# Broadcasting (Pusher) - Get from pusher.com
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=ap1

# Vite Broadcasting
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# LibreOffice Path
LIBREOFFICE_PATH=/usr/bin/soffice

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

**To save in nano:** Press `Ctrl+X`, then `Y`, then `Enter`

### 12.5: Generate Application Key

```bash
php artisan key:generate
```

### 12.6: Set Permissions

```bash
# Set ownership
sudo chown -R deployer:www-data /var/www/enrollassess

# Set directory permissions
sudo find /var/www/enrollassess -type d -exec chmod 755 {} \;
sudo find /var/www/enrollassess -type f -exec chmod 644 {} \;

# Storage and cache must be writable
sudo chmod -R 775 /var/www/enrollassess/storage
sudo chmod -R 775 /var/www/enrollassess/bootstrap/cache

# Ensure www-data can write to storage
sudo chgrp -R www-data /var/www/enrollassess/storage
sudo chgrp -R www-data /var/www/enrollassess/bootstrap/cache
```

### 12.7: Run Database Migrations

```bash
# Run database migrations
php artisan migrate --force

# Link storage
php artisan storage:link
```

### 12.8: Optimize Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

---

## STEP 13: Configure Nginx (10 minutes)

### 13.1: Create Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/enrollassess
```

**Paste this configuration** (replace `YOUR_DOMAIN_OR_IP`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name YOUR_DOMAIN_OR_IP;  # Replace with your domain or IP

    root /var/www/enrollassess/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Logging
    access_log /var/log/nginx/enrollassess-access.log;
    error_log /var/log/nginx/enrollassess-error.log;

    # Max upload size
    client_max_body_size 20M;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

**Save:** `Ctrl+X`, then `Y`, then `Enter`

### 13.2: Enable Site

```bash
# Create symbolic link
sudo ln -s /etc/nginx/sites-available/enrollassess /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t
```

✅ **Should show:** `syntax is ok`, `test is successful`

```bash
# Restart Nginx
sudo systemctl restart nginx
```

### 13.3: Install SSL Certificate (If you have a domain)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate (replace with your domain)
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Follow prompts:
# - Enter email address
# - Agree to terms
# - Choose: Redirect HTTP to HTTPS (option 2)
```

✅ **Certbot will auto-configure Nginx for HTTPS**

---

## STEP 14: Configure Queue Workers (5 minutes)

### 14.1: Create Supervisor Configuration

```bash
sudo nano /etc/supervisor/conf.d/enrollassess-worker.conf
```

**Paste this configuration:**

```ini
[program:enrollassess-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/enrollassess/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deployer
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/enrollassess/storage/logs/worker.log
stopwaitsecs=3600
```

**Save:** `Ctrl+X`, then `Y`, then `Enter`

### 14.2: Start Queue Workers

```bash
# Reread supervisor config
sudo supervisorctl reread

# Update supervisor
sudo supervisorctl update

# Start workers
sudo supervisorctl start enrollassess-worker:*

# Check status
sudo supervisorctl status
```

✅ **Should show:** `enrollassess-worker:enrollassess-worker_00 RUNNING`

---

## STEP 15: Configure Scheduled Tasks (Cron) (2 minutes)

```bash
# Edit crontab for deployer user
crontab -e

# Choose nano editor (option 1) if prompted

# Add this line at the end:
* * * * * cd /var/www/enrollassess && php artisan schedule:run >> /dev/null 2>&1

# Save: Ctrl+X, then Y, then Enter
```

---

## STEP 16: Setup Firewall (3 minutes)

```bash
# Install UFW
sudo apt install -y ufw

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

---

## STEP 17: Create Admin User (5 minutes)

```bash
cd /var/www/enrollassess

# Open Laravel Tinker
php artisan tinker
```

**In tinker, run these commands:**

```php
$user = new App\Models\User();
$user->username = 'admin';
$user->password_hash = Hash::make('YourSecurePassword123!');
$user->full_name = 'System Administrator';
$user->role = 'administrator';
$user->email = 'admin@yourschool.edu';
$user->save();
exit
```

**⚠️ IMPORTANT:** Replace `YourSecurePassword123!` with a strong password and remember it!

---

## STEP 18: Test Your Application (5 minutes)

1. **Visit your site:**
   - If you have domain: `https://yourdomain.com`
   - If using IP: `http://YOUR_DROPLET_IP`

2. **Test admin login:**
   - Go to: `https://yourdomain.com/admin/login`
   - Username: `admin`
   - Password: `YourSecurePassword123!` (or what you set)

3. **Check these pages:**
   - Homepage loads
   - Admin login works
   - No errors in browser console

---

## STEP 19: Verify Services (5 minutes)

Run these commands to verify everything is running:

```bash
# Check PHP-FPM
sudo systemctl status php8.2-fpm

# Check Nginx
sudo systemctl status nginx

# Check Redis
redis-cli -a YOUR_REDIS_PASSWORD PING
# Should return: PONG
exit

# Check Queue Workers
sudo supervisorctl status

# Check LibreOffice
soffice --version

# Check application logs
tail -f /var/www/enrollassess/storage/logs/laravel.log
```

---

## ✅ DEPLOYMENT COMPLETE!

Your EnrollAssess application should now be live!

### Next Steps:

1. **Configure Email Settings** (if not done in .env):
   - Login to admin panel
   - Go to Settings → Email Settings
   - Enter Gmail credentials

2. **Test Critical Features:**
   - [ ] Create an exam
   - [ ] Generate access codes
   - [ ] Send test email
   - [ ] Generate reports (XLSX)
   - [ ] Test PDF export

3. **Setup Monitoring:**
   - Enable DigitalOcean monitoring in dashboard
   - Set up email alerts for high CPU/memory usage

---

## Quick Reference Commands

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

### Update Application
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

---

## Troubleshooting

### 500 Internal Server Error
```bash
# Check permissions
sudo chown -R deployer:www-data /var/www/enrollassess
sudo chmod -R 775 /var/www/enrollassess/storage

# Check logs
tail -f /var/www/enrollassess/storage/logs/laravel.log
tail -f /var/log/nginx/enrollassess-error.log
```

### Database Connection Error
- Verify managed database connection details in `.env`
- Check if database is accessible from droplet (should be in same region)
- Verify SSL mode is set correctly

### Queue Not Processing
```bash
sudo supervisorctl status
sudo supervisorctl restart enrollassess-worker:*
tail -f /var/www/enrollassess/storage/logs/worker.log
```

---

**Need help? Check the full guide:** `DIGITALOCEAN_DEPLOYMENT_GUIDE.md`

