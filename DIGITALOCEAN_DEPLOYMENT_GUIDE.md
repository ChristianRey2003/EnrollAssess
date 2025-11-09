# DigitalOcean Deployment Guide - EnrollAssess System

## Table of Contents
1. [System Requirements Analysis](#system-requirements-analysis)
2. [Recommended DigitalOcean Plan](#recommended-digitalocean-plan)
3. [Pre-Deployment Preparation](#pre-deployment-preparation)
4. [Step-by-Step Deployment](#step-by-step-deployment)
5. [Post-Deployment Configuration](#post-deployment-configuration)
6. [Monitoring & Maintenance](#monitoring--maintenance)

---

## System Requirements Analysis

### Your System Profile
Based on analysis of your EnrollAssess codebase:

**Database Tables:** 20+ tables including:
- Users (staff/admins/instructors)
- Applicants + Basic Info
- Exams, Questions, Question Options
- Access Codes, Results
- Interviews
- Generated Reports
- Sessions, Cache, Jobs, Notifications

**Storage Usage:**
- **Database:** ~50-200 MB (estimated for 1,000-5,000 applicants)
- **File Storage:** 
  - Generated reports (XLSX/PDF/DOCX): ~5-10 MB per report
  - Estimated 100-500 MB for first year of operation
  - Temp files auto-cleaned (LibreOffice conversions)
- **Total Storage Need:** 10-20 GB is sufficient

**RAM Usage:**
- Laravel 12 + PHP 8.2: ~128-256 MB per process
- Redis (cache + sessions + queues): ~100-200 MB
- MySQL: ~256-512 MB
- LibreOffice headless conversions: ~150-300 MB per conversion (temporary spike)
- **Peak RAM Need:** 2 GB minimum, 4 GB recommended

**CPU Usage:**
- Normal web requests: Low
- Exam submissions: Low-medium (database writes)
- Report generation (PDF/Excel): Medium-high (LibreOffice conversion)
- Real-time broadcasting (Pusher): Low (offloaded to Pusher service)
- **CPU Need:** 2 vCPUs recommended for smooth operation

**Traffic Estimates:**
- Typical entrance exam season: 50-500 concurrent applicants
- Admin/instructor users: 5-20 concurrent
- Page load: ~500 KB - 2 MB (with assets)
- **Bandwidth Need:** 1-2 TB/month sufficient

---

## Recommended DigitalOcean Plan

### 🏆 **RECOMMENDED: Basic Droplet + Managed Services**

#### **Option A: Standard Setup (Best Value)**
**Total Monthly Cost: ~$30-40/month**

1. **Droplet: Basic - $18/month**
   - 2 GB RAM / 2 vCPUs
   - 60 GB SSD Disk
   - 3 TB Transfer
   - **Why:** Handles your app + LibreOffice conversions smoothly

2. **Managed MySQL Database: $15/month**
   - 1 GB RAM / 1 vCPU
   - 10 GB Disk
   - **Why:** Automated backups, automatic failover, maintenance

3. **Managed Redis: $15/month** (Optional but recommended)
   - 1 GB RAM
   - **Why:** Cache + sessions + queues in production
   - **Alternative:** Use on-droplet Redis (free) if budget-tight

**Total: $18/month (without managed services) or $33-48/month (full setup)**

---

#### **Option B: All-in-One Droplet (Budget Setup)**
**Total Monthly Cost: $18/month**

1. **Droplet: Basic - $18/month**
   - 2 GB RAM / 2 vCPUs
   - 60 GB SSD Disk
   - 3 TB Transfer
   - Install: MySQL + Redis + App on same server

**Pros:**
- Lower cost
- Simple setup
- Good for <500 applicants per season

**Cons:**
- Manual backups needed
- Single point of failure
- You manage database updates

---

#### **Option C: High-Performance Setup (For Large Scale)**
**Total Monthly Cost: ~$60-80/month**

1. **Droplet: Regular - $48/month**
   - 4 GB RAM / 2 vCPUs
   - 80 GB SSD Disk
   - 4 TB Transfer

2. **Managed MySQL: $15/month**
3. **Managed Redis: $15/month**

**Use this if:**
- Expecting 1,000+ concurrent applicants
- Multiple campuses/departments
- Heavy report generation load

---

### 📊 **Plan Comparison**

| Feature | Budget ($18) | Standard ($33-48) | High-Perf ($60-80) |
|---------|-------------|-------------------|-------------------|
| RAM | 2 GB | 2 GB + Managed | 4 GB + Managed |
| Storage | 60 GB | 60 GB + 10 GB DB | 80 GB + 10 GB DB |
| Concurrent Users | 100-200 | 300-500 | 1,000+ |
| Backups | Manual | Automated | Automated |
| Recommended For | Small school | Medium institution | Large university |

---

## Pre-Deployment Preparation

### 1. Code Preparation Checklist
```bash
# On your local machine (Laragon)

# 1. Run tests
php artisan test

# 2. Optimize autoloader
composer install --optimize-autoloader --no-dev

# 3. Build frontend assets
npm ci
npm run build

# 4. Clear local development cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 5. Export database structure (for migration reference)
php artisan schema:dump

# 6. Verify all migrations are committed
git status
```

### 2. Gather Required Credentials

Create a checklist document with these items:

- [ ] **Domain name** (e.g., enrollassess.yourschool.edu)
- [ ] **SSL Certificate** (Let's Encrypt - free, auto-setup)
- [ ] **Pusher Account** (free tier: 100 connections, 200k messages/day)
  - App ID, Key, Secret, Cluster
  - Sign up: https://pusher.com
- [ ] **Email Service** (Gmail App Password or SMTP)
  - Email address
  - App password (not regular password)
- [ ] **Database Password** (generate strong password)
- [ ] **Redis Password** (if using managed Redis)
- [ ] **Application Key** (generated during deployment)

### 3. Repository Setup

```bash
# Initialize Git repository (if not done)
git init
git add .
git commit -m "Initial production-ready version"

# Create a GitHub/GitLab repository
# Push your code
git remote add origin https://github.com/yourusername/enrollassess.git
git branch -M main
git push -u origin main
```

---

## Step-by-Step Deployment

### Phase 1: DigitalOcean Account & Droplet Setup

#### Step 1.1: Create DigitalOcean Account
1. Go to: https://www.digitalocean.com
2. Sign up with your school email
3. Add payment method (credit card required)
4. *Optional:* Apply student/education credits if available

#### Step 1.2: Create Droplet
1. Click **Create** → **Droplets**
2. **Choose Region:** Singapore (closest to Philippines)
3. **Choose Image:** Ubuntu 24.04 LTS (or 22.04)
4. **Choose Size:** 
   - Basic plan: $18/month (2 GB / 2 vCPU)
5. **Authentication:** SSH Key (recommended) or Password
   - If SSH: Generate key on your laptop and paste public key
   - If Password: Create strong password (you'll need it)
6. **Hostname:** enrollassess-production
7. **Tags:** production, enrollassess
8. Click **Create Droplet**

Wait 1-2 minutes for droplet creation. Note the **IP address** (e.g., 134.122.45.67).

#### Step 1.3: Configure DNS (Domain Setup)
1. Go to your domain registrar (e.g., Namecheap, GoDaddy)
2. Add **A Record**:
   - Host: `@` (or subdomain like `exam`)
   - Value: Your droplet IP (e.g., 134.122.45.67)
   - TTL: Automatic or 300
3. Add **CNAME Record** (optional):
   - Host: `www`
   - Value: `@` or your domain
4. Wait 5-30 minutes for DNS propagation

---

### Phase 2: Server Initial Setup

#### Step 2.1: Connect to Server
```bash
# From your laptop terminal (PowerShell or Git Bash)
ssh root@your_droplet_ip

# Example:
ssh root@134.122.45.67

# Type 'yes' to accept fingerprint
# Enter password if using password auth
```

#### Step 2.2: Update System
```bash
# Update package lists
apt update && apt upgrade -y

# Install basic tools
apt install -y curl git unzip software-properties-common
```

#### Step 2.3: Create Non-Root User
```bash
# Create deployer user
adduser deployer

# Add to sudo group
usermod -aG sudo deployer

# Set up SSH for deployer (if using SSH keys)
rsync --archive --chown=deployer:deployer ~/.ssh /home/deployer

# Switch to deployer user
su - deployer
```

---

### Phase 3: Install Server Software

#### Step 3.1: Install Nginx Web Server
```bash
sudo apt install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Check status
sudo systemctl status nginx

# Test: Visit http://your_droplet_ip in browser
# You should see "Welcome to nginx"
```

#### Step 3.2: Install PHP 8.2
```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 and required extensions
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip \
    php8.2-gd php8.2-intl php8.2-soap

# Verify installation
php -v
# Should show: PHP 8.2.x

# Configure PHP for production
sudo sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/upload_max_filesize = .*/upload_max_filesize = 20M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/post_max_size = .*/post_max_size = 20M/' /etc/php/8.2/fpm/php.ini
sudo sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

#### Step 3.3: Install Composer
```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify
composer --version
```

#### Step 3.4: Install Node.js & NPM
```bash
# Install Node.js 20.x LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verify
node -v  # Should show v20.x.x
npm -v   # Should show 10.x.x
```

#### Step 3.5: Install MySQL (Option A: On-Droplet)
```bash
# Install MySQL 8
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation
# Press Y for all security options
# Set root password: [create strong password]

# Create database and user
sudo mysql

# Run these SQL commands:
CREATE DATABASE enrollassess CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'enrollassess_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';
GRANT ALL PRIVILEGES ON enrollassess.* TO 'enrollassess_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Or Step 3.5 Alternative: Use Managed MySQL**
```bash
# Skip local MySQL installation
# Create Managed Database in DigitalOcean Dashboard:
# 1. Click Create → Databases
# 2. Choose MySQL 8
# 3. Select $15/month plan (1GB RAM)
# 4. Choose same region as droplet
# 5. Note: Host, Port, User, Password, Database name
```

#### Step 3.6: Install Redis (Option A: On-Droplet)
```bash
# Install Redis
sudo apt install -y redis-server

# Configure Redis for production
sudo sed -i 's/supervised no/supervised systemd/' /etc/redis/redis.conf
sudo sed -i 's/# requirepass foobared/requirepass your_redis_password_here/' /etc/redis/redis.conf

# Restart Redis
sudo systemctl restart redis
sudo systemctl enable redis

# Test
redis-cli
AUTH your_redis_password_here
PING
# Should return: PONG
EXIT
```

**Or Step 3.6 Alternative: Use Managed Redis**
```bash
# Skip local Redis installation
# Create Managed Redis in DigitalOcean Dashboard:
# 1. Click Create → Databases
# 2. Choose Redis
# 3. Select $15/month plan (1GB RAM)
# 4. Choose same region as droplet
# 5. Note: Host, Port, Password
```

#### Step 3.7: Install LibreOffice (Critical for PDF Export)
```bash
# Install LibreOffice headless (for PDF conversion)
sudo apt install -y libreoffice-calc libreoffice-writer --no-install-recommends

# Verify installation
soffice --version
# Should show: LibreOffice 7.x.x

# Test headless mode
soffice --headless --help
# Should show help text without errors
```

#### Step 3.8: Install Supervisor (For Queue Workers)
```bash
# Install Supervisor
sudo apt install -y supervisor

# Start and enable
sudo systemctl start supervisor
sudo systemctl enable supervisor
```

---

### Phase 4: Deploy Application

#### Step 4.1: Create Application Directory
```bash
# Create web directory
sudo mkdir -p /var/www/enrollassess
sudo chown -R deployer:www-data /var/www/enrollassess

# Navigate to directory
cd /var/www/enrollassess
```

#### Step 4.2: Clone Repository
```bash
# Clone from GitHub (use your actual repository URL)
git clone https://github.com/yourusername/enrollassess.git .

# Or if using private repository:
# Set up deploy key in GitHub first, then:
git clone git@github.com:yourusername/enrollassess.git .
```

#### Step 4.3: Install Dependencies
```bash
# Install PHP dependencies (production mode)
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm ci --production

# Build frontend assets
npm run build
```

#### Step 4.4: Configure Environment
```bash
# Copy production environment file
cp env.production.example .env

# Edit environment file
nano .env
```

**Edit `.env` with your production values:**
```env
APP_NAME="EnrollAssess"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://enrollassess.yourschool.edu

# Generate this in next step
APP_KEY=

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1  # Or your managed database host
DB_PORT=3306       # Or your managed database port
DB_DATABASE=enrollassess
DB_USERNAME=enrollassess_user
DB_PASSWORD=your_strong_password_here

# Redis Configuration
REDIS_HOST=127.0.0.1  # Or your managed Redis host
REDIS_PASSWORD=your_redis_password_here
REDIS_PORT=6379       # Or your managed Redis port

# Cache/Session/Queue
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourschool.edu
MAIL_FROM_NAME="${APP_NAME}"

# Broadcasting (Pusher)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-key
PUSHER_APP_SECRET=your-pusher-secret
PUSHER_APP_CLUSTER=ap1

# Vite Broadcasting
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# LibreOffice Path (usually auto-detected on Linux)
LIBREOFFICE_PATH=/usr/bin/soffice

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

Save with `Ctrl+X`, then `Y`, then `Enter`.

#### Step 4.5: Generate Application Key
```bash
php artisan key:generate

# This will update APP_KEY in .env automatically
```

#### Step 4.6: Set Permissions
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

#### Step 4.7: Run Migrations
```bash
# Run database migrations
php artisan migrate --force

# Seed initial data if you have seeders
# php artisan db:seed --force
```

#### Step 4.8: Link Storage
```bash
# Create symbolic link for public storage
php artisan storage:link
```

#### Step 4.9: Optimize Application
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

### Phase 5: Configure Nginx

#### Step 5.1: Create Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/enrollassess
```

**Paste this configuration:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name enrollassess.yourschool.edu;  # Change to your domain

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

Save with `Ctrl+X`, then `Y`, then `Enter`.

#### Step 5.2: Enable Site
```bash
# Create symbolic link
sudo ln -s /etc/nginx/sites-available/enrollassess /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t
# Should show: syntax is ok, test is successful

# Restart Nginx
sudo systemctl restart nginx
```

#### Step 5.3: Install SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate (replace with your domain)
sudo certbot --nginx -d enrollassess.yourschool.edu

# Follow prompts:
# - Enter email address
# - Agree to terms
# - Choose: Redirect HTTP to HTTPS (option 2)

# Certbot will auto-configure Nginx for HTTPS

# Test auto-renewal
sudo certbot renew --dry-run
```

---

### Phase 6: Configure Queue Workers

#### Step 6.1: Create Supervisor Configuration
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

Save with `Ctrl+X`, then `Y`, then `Enter`.

#### Step 6.2: Start Queue Workers
```bash
# Reread supervisor config
sudo supervisorctl reread

# Update supervisor
sudo supervisorctl update

# Start workers
sudo supervisorctl start enrollassess-worker:*

# Check status
sudo supervisorctl status
# Should show: enrollassess-worker:enrollassess-worker_00 RUNNING
```

---

### Phase 7: Configure Scheduled Tasks (Cron)

```bash
# Edit crontab for deployer user
crontab -e

# Add this line at the end:
* * * * * cd /var/www/enrollassess && php artisan schedule:run >> /dev/null 2>&1

# Save and exit
```

---

## Post-Deployment Configuration

### 1. Create Admin User
```bash
cd /var/www/enrollassess

# Option A: Use tinker
php artisan tinker

# Then run:
$user = new App\Models\User();
$user->username = 'admin';
$user->password_hash = Hash::make('your_secure_password');
$user->full_name = 'System Administrator';
$user->role = 'administrator';
$user->email = 'admin@yourschool.edu';
$user->save();
exit

# Option B: Use seeder (if you created one)
# php artisan db:seed --class=AdminUserSeeder
```

### 2. Test the Application
```bash
# Visit your domain in browser
https://enrollassess.yourschool.edu

# Check these pages:
# - Homepage (should redirect to login)
# - Admin login: https://enrollassess.yourschool.edu/admin/login
# - Health check: https://enrollassess.yourschool.edu/up
```

### 3. Verify Services
```bash
# Check PHP-FPM
sudo systemctl status php8.2-fpm

# Check Nginx
sudo systemctl status nginx

# Check Redis
redis-cli -a your_redis_password_here PING

# Check MySQL
mysql -u enrollassess_user -p -e "SHOW DATABASES;"

# Check Queue Workers
sudo supervisorctl status

# Check LibreOffice
soffice --version
```

### 4. Test Critical Features
- [ ] Admin login works
- [ ] Create/edit exam works
- [ ] Generate access codes works
- [ ] Applicant can take exam
- [ ] Reports generate (XLSX)
- [ ] PDF export works (LibreOffice conversion)
- [ ] Interview assignment works
- [ ] Real-time updates work (Pusher)

---

## Monitoring & Maintenance

### Daily Checks
```bash
# Check application logs
tail -f /var/www/enrollassess/storage/logs/laravel.log

# Check Nginx logs
sudo tail -f /var/log/nginx/enrollassess-error.log

# Check queue worker logs
sudo tail -f /var/www/enrollassess/storage/logs/worker.log

# Check disk space
df -h

# Check memory usage
free -h
```

### Weekly Maintenance
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Clear old logs (keep last 30 days)
find /var/www/enrollassess/storage/logs -name "*.log" -mtime +30 -delete

# Optimize database
cd /var/www/enrollassess
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Backup Strategy

#### Manual Backup Script
Create `/home/deployer/backup.sh`:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/deployer/backups"

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u enrollassess_user -pyour_password enrollassess | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup application files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C /var/www/enrollassess \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs' \
    .

# Keep only last 7 backups
ls -t $BACKUP_DIR/db_*.sql.gz | tail -n +8 | xargs rm -f
ls -t $BACKUP_DIR/files_*.tar.gz | tail -n +8 | xargs rm -f

echo "Backup completed: $DATE"
```

Make executable and add to cron:
```bash
chmod +x /home/deployer/backup.sh

# Add to crontab (daily at 2 AM)
crontab -e
# Add line:
0 2 * * * /home/deployer/backup.sh >> /home/deployer/backup.log 2>&1
```

### Updating the Application

```bash
# Navigate to app directory
cd /var/www/enrollassess

# Put app in maintenance mode
php artisan down

# Pull latest code
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart enrollassess-worker:*

# Bring app back online
php artisan up
```

---

## Troubleshooting Common Issues

### Issue: 500 Internal Server Error
```bash
# Check PHP error log
sudo tail -f /var/log/nginx/enrollassess-error.log

# Check Laravel log
tail -f /var/www/enrollassess/storage/logs/laravel.log

# Check permissions
sudo chown -R deployer:www-data /var/www/enrollassess
sudo chmod -R 775 /var/www/enrollassess/storage
```

### Issue: Queue not processing
```bash
# Check worker status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart enrollassess-worker:*

# Check worker log
tail -f /var/www/enrollassess/storage/logs/worker.log
```

### Issue: PDF export fails
```bash
# Verify LibreOffice installation
which soffice
soffice --version

# Test headless conversion
cd /tmp
soffice --headless --convert-to pdf test.xlsx

# Check LibreOffice is accessible by www-data
sudo -u www-data soffice --headless --help
```

### Issue: Broadcasting not working
```bash
# Verify Pusher credentials in .env
grep PUSHER /var/www/enrollassess/.env

# Clear config cache
php artisan config:clear
php artisan config:cache

# Check browser console for errors
# Check Pusher dashboard for connection attempts
```

### Issue: Out of Memory
```bash
# Check current memory usage
free -h

# Increase PHP memory limit
sudo nano /etc/php/8.2/fpm/php.ini
# Change: memory_limit = 512M (or higher)

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Consider upgrading to larger droplet if persistent
```

---

## Security Best Practices

### 1. Firewall Setup
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

### 2. Fail2Ban (Prevent Brute Force)
```bash
# Install Fail2Ban
sudo apt install -y fail2ban

# Copy default config
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Start and enable
sudo systemctl start fail2ban
sudo systemctl enable fail2ban
```

### 3. Regular Security Updates
```bash
# Enable automatic security updates
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure --priority=low unattended-upgrades
```

### 4. Disable Root SSH Login
```bash
sudo nano /etc/ssh/sshd_config

# Change line:
PermitRootLogin no

# Restart SSH
sudo systemctl restart sshd
```

---

## Cost Optimization Tips

1. **Start small:** Begin with Basic $18 droplet, upgrade only if needed
2. **Use managed services selectively:** MySQL managed ($15) gives backups, Redis can run on droplet
3. **Pusher free tier:** 100 connections, 200k messages/day (sufficient for most schools)
4. **DigitalOcean Spaces:** Only add if you need cloud storage (not required initially)
5. **Snapshot backups:** Take weekly snapshots ($1.20/month for 20GB) as backup plan
6. **Monitor usage:** Use DigitalOcean monitoring to track actual resource usage

---

## Summary: Recommended First Setup

**For most schools, start with this:**

**Monthly Cost: $18-33**
- Droplet: Basic $18/month (2GB/2vCPU)
- MySQL: On-droplet (free) or Managed $15/month
- Redis: On-droplet (free)
- Pusher: Free tier
- SSL: Let's Encrypt (free)
- Domain: $10-15/year (separate cost)

**Scale up to $48-60/month if:**
- 500+ concurrent users during exams
- Multiple simultaneous PDF exports slow down
- Memory warnings in logs
- Database queries become slow

---

## Quick Reference Commands

```bash
# View application
https://your-domain.com

# SSH to server
ssh deployer@your_droplet_ip

# View logs
tail -f /var/www/enrollassess/storage/logs/laravel.log

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart enrollassess-worker:*

# Clear cache
php artisan optimize:clear

# Update app
cd /var/www/enrollassess && git pull && php artisan migrate --force

# Run backup
/home/deployer/backup.sh
```

---

## Support Resources

- **DigitalOcean Docs:** https://docs.digitalocean.com
- **Laravel Docs:** https://laravel.com/docs/12.x/deployment
- **Pusher Docs:** https://pusher.com/docs
- **Let's Encrypt:** https://letsencrypt.org/getting-started
- **LibreOffice Headless:** https://help.libreoffice.org/latest/en-US/text/shared/guide/start_parameters.html

---

**Ready to deploy? Follow the steps above carefully. Good luck! 🚀**

