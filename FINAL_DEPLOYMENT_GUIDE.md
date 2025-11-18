# FINAL DEPLOYMENT GUIDE - Complete Site Setup

## 🎯 OBJECTIVE: Deploy Working Site with NO 403 Errors

This guide provides step-by-step instructions to deploy the 3D Print Platform on nginx hosting and solve all issues including 403 Forbidden errors, PHP 8.2 compatibility, and complete site functionality.

---

## ⚡ QUICK START (5 Minutes)

```bash
# 1. SSH into your server
ssh ch167436@3dprint-omsk.ru

# 2. Navigate to project directory
cd /home/c/ch167436/3dPrint

# 3. Run diagnostic
bash scripts/diagnose-403.sh

# 4. Fix permissions
find public_html -type d -exec chmod 755 {} \;
find public_html -type f -exec chmod 644 {} \;
mkdir -p logs uploads backups/{database,files} temp storage
chmod 755 logs uploads backups temp storage

# 5. Install dependencies (if vendor missing)
composer install --no-dev --optimize-autoloader

# 6. Configure environment
cp .env.example .env
nano .env
# Set DB_HOST, DB_NAME, DB_USER, DB_PASS, JWT_SECRET (64+ chars)

# 7. Run auto-setup
bash scripts/setup.sh

# 8. Test deployment
curl -I https://3dprint-omsk.ru/
# Should return: HTTP/2 200 OK
```

---

## 🔧 DETAILED STEP-BY-STEP DEPLOYMENT

### Step 1: Pre-Deployment Checklist

**Server Requirements:**
- ✅ PHP 8.2 or higher
- ✅ MySQL/MariaDB 5.7+
- ✅ nginx web server
- ✅ Composer 2.x
- ✅ PHP extensions: pdo_mysql, mbstring, openssl, json, fileinfo

**Verify Requirements:**
```bash
php -v                           # Should show 8.2+
php -m | grep -E 'pdo|mbstring'  # Check extensions
composer --version               # Should show 2.x
mysql --version                  # Check MySQL
```

---

### Step 2: Fix 403 Forbidden Error

#### Problem: nginx Returns 403 Forbidden

**Diagnosis:**
```bash
# Run diagnostic script
bash scripts/diagnose-403.sh

# Check nginx error logs
tail -f /var/log/nginx/error.log
# OR (shared hosting)
tail -f ~/logs/error.log
```

**Common Causes & Solutions:**

#### Cause 1: Wrong Web Root

nginx is looking in wrong directory (e.g., `/home/c/ch167436/public_html` but files are in `/home/c/ch167436/3dPrint/public_html`)

**Solution A: Check nginx config**
```bash
# Find nginx config
cat /etc/nginx/sites-available/3dprint-omsk.ru
# Look for: root /path/to/directory;

# If you have access, update it to:
root /home/c/ch167436/3dPrint/public_html;
```

**Solution B: Move files to correct location**
```bash
# If nginx expects /home/c/ch167436/public_html
cd /home/c/ch167436

# Option 1: Move everything
mv 3dPrint/* .
mv 3dPrint/.* . 2>/dev/null || true

# Option 2: Symlink
ln -s 3dPrint/public_html public_html
ln -s 3dPrint/api api
ln -s 3dPrint/admin admin
```

**Solution C: Contact hosting support**
```
Ask: "What is the exact web root path for domain 3dprint-omsk.ru?"
Request: "Please configure nginx to use /home/c/ch167436/3dPrint/public_html as web root"
```

#### Cause 2: index.php Not in Directory Index List

**Solution:** Ensure nginx config has:
```nginx
index index.php index.html index.htm;
```

#### Cause 3: PHP-FPM Not Configured

**Check:**
```bash
systemctl status php8.2-fpm
```

**nginx config should have:**
```nginx
location ~ \.php$ {
    try_files $uri =404;
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

#### Cause 4: File Permissions

**Fix:**
```bash
# Set correct permissions
cd /home/c/ch167436/3dPrint
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod +x scripts/*.sh
chmod 600 .env

# If nginx runs as www-data
chown -R www-data:www-data public_html/

# If on shared hosting
chown -R ch167436:ch167436 .
```

---

### Step 3: Complete nginx Configuration

**Required nginx Configuration:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    
    # IMPORTANT: Adjust this path!
    root /home/c/ch167436/3dPrint/public_html;
    
    # Directory indexes
    index index.php index.html;
    
    charset utf-8;
    
    # Logging
    access_log /var/log/nginx/3dprint-omsk.ru-access.log;
    error_log /var/log/nginx/3dprint-omsk.ru-error.log;
    
    # Main location - route through index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Security - deny sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(\.env|composer\.json|composer\.lock|package\.json) {
        deny all;
    }
    
    # Deny access to directories above web root
    location ~ \.\./  {
        deny all;
    }
}
```

**Enable and reload:**
```bash
sudo ln -sf /etc/nginx/sites-available/3dprint-omsk.ru /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

### Step 4: Install Dependencies

```bash
cd /home/c/ch167436/3dPrint

# Check if Composer is available
composer --version

# Install dependencies
composer install --no-dev --optimize-autoloader

# Verify installation
php test_packages.php
```

**If Composer is not available:**
```bash
# Download Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Or use setup script
bash scripts/setup-composer-dependencies.sh
```

---

### Step 5: Configure Environment

```bash
# Create .env file
cp .env.example .env
nano .env
```

**Required Configuration:**
```ini
# Application
APP_NAME="3D Print Omsk"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://3dprint-omsk.ru

# Database (IMPORTANT!)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
DB_CHARSET=utf8mb4

# JWT Authentication (CRITICAL!)
# Generate 64+ character random string
JWT_SECRET=your_64_plus_character_random_string_here
JWT_ACCESS_TOKEN_TTL=3600
JWT_REFRESH_TOKEN_TTL=604800

# Email Configuration
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@3dprint-omsk.ru
MAIL_FROM_NAME="3D Print Omsk"

# Logging
LOG_FILE=logs/app.log
LOG_LEVEL=info

# SEO
SITE_NAME="3D Print Omsk"
SITE_DESCRIPTION="Профессиональная 3D печать в Омске"
SITE_URL=https://3dprint-omsk.ru
```

**Generate JWT_SECRET:**
```bash
# Generate secure random string (64+ chars)
php -r "echo bin2hex(random_bytes(32)) . bin2hex(random_bytes(32)) . PHP_EOL;"

# Or
openssl rand -hex 64
```

**Secure .env:**
```bash
chmod 600 .env
```

---

### Step 6: Create Required Directories

```bash
# Create directories
mkdir -p logs
mkdir -p uploads/{models,gallery,temp}
mkdir -p backups/{database,files}
mkdir -p temp
mkdir -p storage

# Set permissions
chmod 755 logs uploads backups temp storage
chmod 755 uploads/models uploads/gallery uploads/temp
chmod 755 backups/database backups/files
```

---

### Step 7: Database Setup

```bash
# Run migrations
php database/migrate.php

# Seed initial data
php database/seed.php

# Verify database
php scripts/verify-deployment.php
```

**If migration fails, create database manually:**
```bash
mysql -u your_user -p
```

```sql
CREATE DATABASE IF NOT EXISTS your_database_name 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE your_database_name;

-- Run migration files in order
SOURCE database/migrations/001_create_services_table.sql;
SOURCE database/migrations/002_create_materials_table.sql;
-- ... etc
```

---

### Step 8: Run Auto-Setup Script

```bash
# Run complete setup
bash scripts/setup.sh

# This script will:
# ✅ Check PHP version and extensions
# ✅ Test database connection
# ✅ Create directories with correct permissions
# ✅ Install Composer dependencies
# ✅ Run migrations
# ✅ Seed database
# ✅ Create default admin users
# ✅ Verify deployment
```

**Default Admin Credentials** (created by setup script):
- **Super Admin:** `admin` / `admin123` (email: `admin@example.com`)
- **Editor:** `editor` / `editor123` (email: `editor@example.com`)

**⚠️ CRITICAL:** Change these passwords immediately after first login!

---

### Step 9: Verify Deployment

```bash
# Run verification script
php scripts/verify-deployment.php

# Test site with curl
curl -I https://3dprint-omsk.ru/
# Expected: HTTP/2 200 OK

# Test homepage
curl https://3dprint-omsk.ru/ | head -20
# Expected: HTML content

# Test API
curl https://3dprint-omsk.ru/api/services
# Expected: JSON response

# Test admin (should redirect to login)
curl -I https://3dprint-omsk.ru/admin/
# Expected: 302 redirect or 200 OK

# Test static files
curl -I https://3dprint-omsk.ru/assets/css/style.css
# Expected: 200 OK, Content-Type: text/css
```

---

### Step 10: Final Checks

**Browser Testing:**
1. Visit `https://3dprint-omsk.ru/`
   - ✅ Homepage loads correctly
   - ✅ Images display
   - ✅ CSS styles applied
   - ✅ JavaScript works

2. Test Navigation:
   - ✅ About page
   - ✅ Services page
   - ✅ Calculator page
   - ✅ Contact page
   - ✅ Gallery page

3. Test Calculator:
   - ✅ Calculator form loads
   - ✅ Real-time price calculation works
   - ✅ File upload works
   - ✅ Form submission works

4. Test Admin Panel:
   - Visit `https://3dprint-omsk.ru/admin/`
   - ✅ Login page loads
   - ✅ Can login with credentials
   - ✅ Dashboard loads
   - ✅ All admin functions work

**Check Logs:**
```bash
# Check for errors
tail -50 logs/app.log
tail -50 logs/error.log
tail -50 /var/log/nginx/error.log
```

**No errors should be present!**

---

### Step 11: Setup Cron Jobs

```bash
# Edit crontab
crontab -e
```

**Add these cron jobs:**
```cron
# Daily sitemap generation (2 AM)
0 2 * * * cd /home/c/ch167436/3dPrint && php scripts/generate-sitemap.php >> logs/cron.log 2>&1

# Daily log rotation (3 AM)
0 3 * * * cd /home/c/ch167436/3dPrint && php scripts/rotate-logs.php >> logs/cron.log 2>&1

# Daily database backup (4 AM)
0 4 * * * cd /home/c/ch167436/3dPrint && bash scripts/backup-database.sh >> logs/backup.log 2>&1

# Weekly file backup (Sunday 5 AM)
0 5 * * 0 cd /home/c/ch167436/3dPrint && bash scripts/backup-files.sh >> logs/backup.log 2>&1

# Daily temp cleanup (6 AM)
0 6 * * * cd /home/c/ch167436/3dPrint && php scripts/cleanup-temp.php >> logs/cron.log 2>&1

# Hourly error monitoring
0 * * * * cd /home/c/ch167436/3dPrint && php scripts/check-errors.php >> logs/monitoring.log 2>&1
```

---

### Step 12: Setup SSL Certificate

**If using Let's Encrypt:**
```bash
sudo certbot --nginx -d 3dprint-omsk.ru -d www.3dprint-omsk.ru
```

**If using shared hosting:**
- Access hosting control panel
- Find "SSL/TLS" or "Let's Encrypt" section
- Enable SSL for domain `3dprint-omsk.ru`
- Wait for certificate issuance (5-15 minutes)

**Enable HTTPS redirect:**

Edit `public_html/.htaccess`:
```apache
# Uncomment these lines:
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**Enable HSTS:**

Edit `public_html/.htaccess`:
```apache
# Uncomment this line after SSL is active:
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
```

---

## ✅ DEPLOYMENT SUCCESS CHECKLIST

After completing all steps, verify:

- [ ] `curl -I https://3dprint-omsk.ru/` returns **HTTP/2 200**
- [ ] Homepage loads in browser without errors
- [ ] All pages are accessible (about, services, calculator, etc.)
- [ ] Images, CSS, and JavaScript load correctly
- [ ] Calculator works and calculates prices
- [ ] Contact form submits successfully
- [ ] Admin panel is accessible at `/admin/`
- [ ] Can login to admin panel
- [ ] API endpoints respond: `/api/services`, `/api/materials`
- [ ] Database has all tables and data
- [ ] No errors in `logs/app.log`
- [ ] No errors in nginx error log
- [ ] SSL certificate is active (HTTPS works)
- [ ] Cron jobs are configured
- [ ] Backups are working

---

## 🐛 TROUBLESHOOTING

### Issue: Still Getting 403 Forbidden

**Solution:**
```bash
# Run diagnostic
bash scripts/diagnose-403.sh

# Check nginx error log
tail -f /var/log/nginx/error.log

# Test PHP execution
echo "<?php phpinfo(); ?>" > public_html/test.php
curl https://3dprint-omsk.ru/test.php
rm public_html/test.php

# Contact hosting support with this info:
#   - Domain: 3dprint-omsk.ru
#   - Project path: /home/c/ch167436/3dPrint
#   - Web root should be: /home/c/ch167436/3dPrint/public_html
#   - Error: 403 Forbidden on index.php
```

### Issue: Database Connection Failed

**Solution:**
```bash
# Test database connection
mysql -h localhost -u your_user -p your_database

# Verify .env settings
grep DB_ .env

# Check if database exists
mysql -u your_user -p -e "SHOW DATABASES;"

# Create database if missing
mysql -u your_user -p -e "CREATE DATABASE your_database_name;"
```

### Issue: Composer Dependencies Not Installing

**Solution:**
```bash
# Check Composer version
composer --version  # Should be 2.x

# Clear cache
composer clear-cache

# Install with verbose output
composer install --no-dev --optimize-autoloader -vvv

# Alternative: use setup script
bash scripts/setup-composer-dependencies.sh
```

### Issue: count() Error on PDOStatement

**Already Fixed!** The issue in `scripts/verify-deployment.php:213` has been fixed.

To verify:
```bash
grep -A2 "SHOW TABLES" scripts/verify-deployment.php
# Should show: $tablesResult->fetchAll()
```

### Issue: JWT Authentication Not Working

**Solution:**
```bash
# Check JWT_SECRET length
grep JWT_SECRET .env | wc -c
# Should be 64+ characters

# Generate new secret
php -r "echo bin2hex(random_bytes(64)) . PHP_EOL;"

# Update .env
nano .env
# JWT_SECRET=your_new_secret_here
```

### Issue: File Upload Not Working

**Solution:**
```bash
# Check upload directories
ls -la uploads/
chmod 755 uploads uploads/models uploads/gallery
chown -R www-data:www-data uploads/

# Check PHP settings
php -i | grep upload_max_filesize
php -i | grep post_max_size

# If needed, create .user.ini
cat > public_html/.user.ini << EOF
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
EOF
```

---

## 📞 SUPPORT & RESOURCES

**Documentation:**
- `DEPLOYMENT_FIX_403.md` - Complete 403 error guide
- `NGINX_ROUTER_DEPLOYMENT.md` - nginx configuration
- `DEPLOYMENT.md` - Full deployment guide
- `SETUP_README.md` - Auto-setup script guide
- `ADMIN_API.md` - Admin panel documentation
- `API_PUBLIC.md` - Public API documentation

**Scripts:**
- `scripts/diagnose-403.sh` - Diagnostic tool
- `scripts/setup.sh` - Auto-deployment
- `scripts/verify-deployment.php` - Verification
- `scripts/backup-database.sh` - Database backups
- `scripts/check-errors.php` - Error monitoring

**Logs:**
- `logs/app.log` - Application log
- `logs/setup.log` - Setup script log
- `logs/error.log` - PHP errors
- `/var/log/nginx/error.log` - nginx errors

**Contact Hosting Support:**
If issues persist after following this guide, contact support with:
1. Domain name: `3dprint-omsk.ru`
2. Project path: `/home/c/ch167436/3dPrint`
3. Web root needed: `/home/c/ch167436/3dPrint/public_html`
4. PHP version required: 8.2+
5. Error description and logs

---

## 🎉 CONGRATULATIONS!

Your 3D Print Platform is now deployed and ready for production!

**Next Steps:**
1. Change default admin passwords
2. Configure email settings for notifications
3. Add your content (services, materials, gallery)
4. Test all functionality thoroughly
5. Monitor logs regularly
6. Setup regular backups
7. Consider setting up monitoring/alerting

**Need Help?** Check the documentation files or contact support.

**Good luck! 🚀**
