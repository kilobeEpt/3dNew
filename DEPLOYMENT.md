# Deployment Guide for Shared Hosting

This guide provides comprehensive instructions for deploying the 3D Print Platform to shared hosting environments (both Apache and nginx).

> **Got nginx 403 Forbidden?** See [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md) for complete solution.
> 
> **Need troubleshooting?** See [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for common issues.

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Server Requirements](#server-requirements)
3. [Server Type Detection](#server-type-detection)
4. [Deployment Steps](#deployment-steps)
5. [SSL Certificate Setup](#ssl-certificate-setup)
6. [Database Setup](#database-setup)
7. [Cron Jobs Configuration](#cron-jobs-configuration)
8. [Backup Strategy](#backup-strategy)
9. [Monitoring and Alerts](#monitoring-and-alerts)
10. [Post-Deployment Testing](#post-deployment-testing)
11. [Troubleshooting](#troubleshooting)

---

## Pre-Deployment Checklist

Before deploying, ensure you have:

- [ ] FTP/SFTP access to hosting account
- [ ] SSH access (preferred but optional)
- [ ] MySQL database credentials
- [ ] Domain name configured
- [ ] SSL certificate (Let's Encrypt or commercial)
- [ ] SMTP credentials for email
- [ ] CAPTCHA keys (reCAPTCHA or hCaptcha)
- [ ] Admin email addresses for notifications

---

## Server Requirements

### Minimum Requirements

- **PHP Version**: 7.4 or higher (PHP 8.0+ recommended)
- **MySQL/MariaDB**: 5.7+ / 10.2+
- **Apache Modules**: mod_rewrite, mod_headers, mod_deflate (or mod_brotli)
- **PHP Extensions**:
  - pdo_mysql
  - mbstring
  - openssl
  - json
  - fileinfo
  - gd or imagick (for image processing)
- **Disk Space**: Minimum 500MB (1GB+ recommended for uploads)
- **Memory Limit**: 128MB+ (256MB recommended)

### Verify PHP Version

```bash
php -v
```

If multiple PHP versions are available, ensure you're using 7.4+:

```bash
# Check available versions
ls /usr/bin/php*

# Set default (if using cPanel)
# Go to cPanel > Select PHP Version
```

---

## Server Type Detection

This platform supports both **Apache** and **nginx** web servers. Detect your server type:

```bash
# Method 1: Check running processes
ps aux | grep -E "nginx|httpd|apache2" | grep -v grep

# Method 2: Check HTTP headers
curl -I http://yourdomain.com | grep -i "^server:"

# Method 3: Check cPanel
# Look for "Web Server" in cPanel dashboard
```

### Apache vs nginx

| Feature | Apache | nginx |
|---------|--------|-------|
| **Configuration** | `.htaccess` files | PHP router or server config |
| **Setup Complexity** | Easy (automatic) | Medium (may need config) |
| **Routing** | mod_rewrite | PHP-based or nginx config |
| **Shared Hosting** | Very common | Common (Russia/Europe) |
| **This Platform** | ✅ Works out of the box | ✅ PHP router included |

### nginx Users: Important!

If you have nginx:

1. **Use the PHP router** in `public_html/index.php` (already included)
2. **May need nginx configuration** - see [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md)
3. **403 Forbidden errors?** - see [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md) for complete solution

### Apache Users

- No special configuration needed
- `.htaccess` files handle all routing
- Works immediately after upload

---

## Deployment Steps

### Step 1: Upload Files

#### Target Directory Structure

```
/home/c/ch167436/3dPrint/
├── public_html/          (web root - frontend files)
│   ├── assets/
│   ├── index.html
│   └── .htaccess
├── api/                  (API endpoints)
│   ├── index.php
│   ├── routes.php
│   └── .htaccess
├── admin/                (Admin panel)
│   ├── index.php
│   ├── routes.php
│   └── .htaccess
├── src/                  (PHP source code)
├── vendor/               (Composer dependencies)
├── database/             (Migrations and seeds)
├── templates/            (Email templates)
├── logs/                 (Application logs)
├── uploads/              (User uploads - 3D models)
├── backups/              (Database and file backups)
├── .env                  (Environment configuration)
└── bootstrap.php
```

#### Upload Methods

**Option A: FTP/SFTP (FileZilla, Cyberduck, etc.)**

1. Connect to your server
2. Navigate to `/home/c/ch167436/3dPrint/`
3. Upload all files **except**:
   - `.git/` directory
   - `.env` (will be created separately)
   - `node_modules/` (not needed on production)

**Option B: SSH (Recommended)**

```bash
# From your local machine
cd /path/to/project
tar -czf project.tar.gz . --exclude='.git' --exclude='node_modules' --exclude='.env'
scp project.tar.gz user@yourserver:/home/c/ch167436/

# On the server
ssh user@yourserver
cd /home/c/ch167436/3dPrint
tar -xzf ../project.tar.gz
rm ../project.tar.gz
```

### Step 2: Install Dependencies

```bash
cd /home/c/ch167436/3dPrint

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# If composer is not available globally
php composer.phar install --no-dev --optimize-autoloader
```

**If Composer is not installed:**

```bash
# Install Composer locally
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

### Step 3: Configure Environment

```bash
# Copy example environment file
cp .env.example .env

# Edit configuration (use nano, vi, or cPanel File Manager)
nano .env
```

**Production `.env` Configuration:**

```env
# Application Environment
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=ch167436_3dprint
DB_USER=ch167436_dbuser
DB_PASS=your_secure_database_password
DB_CHARSET=utf8mb4

# Mail Configuration (example with Gmail)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="3D Print Platform"

# Security - CRITICAL: Generate a strong random key
JWT_SECRET=CHANGE_THIS_TO_A_LONG_RANDOM_STRING_min_64_chars
API_RATE_LIMIT=100

# CAPTCHA Settings (choose one)
CAPTCHA_TYPE=recaptcha
RECAPTCHA_SITE_KEY=your-recaptcha-site-key
RECAPTCHA_SECRET=your-recaptcha-secret-key

# Admin Notifications
ADMIN_EMAIL=admin@yourdomain.com

# Logging
LOG_LEVEL=warning
LOG_FILE=logs/app.log

# CORS Settings (adjust based on your domains)
CORS_ALLOWED_ORIGINS=https://yourdomain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization

# SEO Configuration
SITE_URL=https://yourdomain.com
CDN_URL=
```

**Generate a secure JWT_SECRET:**

```bash
# Option 1: Using OpenSSL
openssl rand -base64 64

# Option 2: Using PHP
php -r "echo bin2hex(random_bytes(32));"

# Option 3: Online
# Visit: https://www.grc.com/passwords.htm
```

### Step 4: Set Permissions

```bash
# Make directories writable
chmod 755 logs/
chmod 755 uploads/
chmod 755 backups/

# Secure .env file
chmod 600 .env

# Make scripts executable
chmod +x scripts/*.sh
```

### Step 5: Build Assets

If you modified any CSS/JS files:

```bash
# Option 1: Build locally and upload
npm install
npm run build
# Then upload the minified files

# Option 2: Build on server (if Node.js is available)
cd /home/c/ch167436/3dPrint
npm install
npm run build
```

---

## SSL Certificate Setup

### Option A: Let's Encrypt (Free - Recommended)

Most shared hosting providers (including cPanel) offer free Let's Encrypt SSL certificates.

#### Using cPanel

1. Log into cPanel
2. Navigate to **SSL/TLS Status**
3. Check the domain(s) you want to secure
4. Click **Run AutoSSL**
5. Wait for certificate to be issued (usually < 5 minutes)

#### Verify SSL Installation

```bash
curl -I https://yourdomain.com
```

Look for `HTTP/2 200` or `HTTP/1.1 200` and check for `Strict-Transport-Security` header.

### Option B: Commercial SSL Certificate

1. Purchase SSL certificate from provider (Comodo, DigiCert, etc.)
2. In cPanel: **SSL/TLS** → **Manage SSL Sites**
3. Upload certificate files:
   - Certificate (CRT)
   - Private Key (KEY)
   - Certificate Authority Bundle (CA Bundle)
4. Click **Install Certificate**

### Enable HTTPS Redirect

Once SSL is active, enable HTTPS redirect:

```bash
# Edit public_html/.htaccess
nano /home/c/ch167436/3dPrint/public_html/.htaccess
```

Uncomment these lines (around line 82-87):

```apache
# Redirect to HTTPS (uncomment when SSL certificate is active)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
</IfModule>

# HSTS - HTTP Strict Transport Security (uncomment when SSL is active)
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" env=HTTPS
</IfModule>
```

Repeat for `api/.htaccess` and `admin/.htaccess`.

---

## Database Setup

### Step 1: Create Database

#### Using cPanel

1. Go to **MySQL Databases**
2. Create new database: `ch167436_3dprint`
3. Create user: `ch167436_dbuser`
4. Set a strong password
5. Add user to database with **ALL PRIVILEGES**

#### Using SSH

```bash
mysql -u root -p

CREATE DATABASE ch167436_3dprint CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ch167436_dbuser'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON ch167436_3dprint.* TO 'ch167436_dbuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 2: Run Migrations

```bash
cd /home/c/ch167436/3dPrint

# Run database migrations
php database/migrate.php

# Verify migrations
php database/migrate.php status
```

### Step 3: Seed Initial Data

```bash
# Seed all initial data
php database/seed.php

# Or seed specific tables
php database/seeds/SeoSettingsSeed.php
php database/seeds/AdminUserSeed.php
```

### Step 4: Verify Database

```bash
mysql -u ch167436_dbuser -p ch167436_3dprint -e "SHOW TABLES;"
```

You should see all 17 tables listed.

---

## Cron Jobs Configuration

### Required Cron Jobs

Add these to your crontab (cPanel → Cron Jobs):

#### 1. Sitemap Generation (Daily at 2 AM)

```cron
0 2 * * * cd /home/c/ch167436/3dPrint && php scripts/generate-sitemap.php >> logs/cron.log 2>&1
```

#### 2. Log Rotation (Daily at 3 AM)

```cron
0 3 * * * cd /home/c/ch167436/3dPrint && php scripts/rotate-logs.php >> logs/cron.log 2>&1
```

#### 3. Database Backup (Daily at 4 AM)

```cron
0 4 * * * cd /home/c/ch167436/3dPrint && bash scripts/backup-database.sh >> logs/backup.log 2>&1
```

#### 4. File Backup (Weekly on Sunday at 5 AM)

```cron
0 5 * * 0 cd /home/c/ch167436/3dPrint && bash scripts/backup-files.sh >> logs/backup.log 2>&1
```

#### 5. Cleanup Temporary Files (Daily at 6 AM)

```cron
0 6 * * * cd /home/c/ch167436/3dPrint && php scripts/cleanup-temp.php >> logs/cron.log 2>&1
```

#### 6. Error Monitoring Check (Every hour)

```cron
0 * * * * cd /home/c/ch167436/3dPrint && php scripts/check-errors.php >> logs/monitoring.log 2>&1
```

### Add Cron Jobs via cPanel

1. Log into cPanel
2. Go to **Cron Jobs**
3. Add each cron job with the schedule and command
4. Set email for cron output (optional)

### Add Cron Jobs via SSH

```bash
# Edit crontab
crontab -e

# Add all cron jobs, then save and exit

# Verify cron jobs
crontab -l
```

---

## Backup Strategy

### Database Backups

**Automated Daily Backups** (via cron - see above)

The `scripts/backup-database.sh` script:
- Creates compressed MySQL dump
- Stores in `backups/database/YYYY-MM-DD/`
- Retains last 30 days of backups
- Emails admin on failure

**Manual Backup:**

```bash
# Run backup script
bash scripts/backup-database.sh

# Or directly
mysqldump -u ch167436_dbuser -p ch167436_3dprint | gzip > backup.sql.gz
```

### File Backups

**Automated Weekly Backups** (via cron - see above)

The `scripts/backup-files.sh` script:
- Archives uploads directory
- Stores in `backups/files/YYYY-MM-DD/`
- Retains last 8 weeks of backups

**Manual Backup:**

```bash
# Run backup script
bash scripts/backup-files.sh

# Or directly
tar -czf uploads-backup.tar.gz uploads/
```

### Off-Site Backup

**Important:** Always maintain off-site backups!

**Option 1: Download via FTP** (Scheduled on local machine)

```bash
# Example cron on local machine
0 6 * * * scp -r user@yourserver:/home/c/ch167436/3dPrint/backups /local/backup/path/
```

**Option 2: Cloud Storage** (Recommended)

Install rclone or use cloud provider's CLI:

```bash
# Example with rclone (sync to Google Drive, Dropbox, etc.)
rclone sync /home/c/ch167436/3dPrint/backups remote:3dprint-backups
```

**Option 3: cPanel Backup** (if available)

1. cPanel → **Backup Wizard**
2. Schedule automatic backups
3. Choose remote backup destination (FTP, S3, etc.)

---

## Monitoring and Alerts

### Error Monitoring

The platform includes built-in error monitoring via `scripts/check-errors.php`.

**How it works:**
- Runs hourly via cron
- Checks logs for errors in last hour
- Sends email alert if critical errors found
- Includes error summary and log excerpts

**Configure alerts:**

Edit `scripts/check-errors.php` to adjust:
- Error threshold
- Email recipients
- Error severity levels

### Log Files

Monitor these log files:

- `logs/app.log` - Application errors and warnings
- `logs/cron.log` - Cron job execution logs
- `logs/backup.log` - Backup operation logs
- `logs/monitoring.log` - Monitoring script logs

**Log Rotation:**

Automatic via `scripts/rotate-logs.php` (daily cron):
- Compresses logs older than 7 days
- Deletes logs older than 30 days
- Maintains manageable log sizes

**Manual Log Check:**

```bash
# View recent errors
tail -n 100 logs/app.log | grep ERROR

# Count errors today
grep ERROR logs/app.log | grep "$(date +%Y-%m-%d)" | wc -l

# Monitor in real-time
tail -f logs/app.log
```

### Performance Monitoring

**Check Response Times:**

```bash
# API health check
curl -w "@curl-format.txt" -o /dev/null -s https://yourdomain.com/api/health
```

Create `curl-format.txt`:

```
time_namelookup:  %{time_namelookup}\n
time_connect:  %{time_connect}\n
time_appconnect:  %{time_appconnect}\n
time_pretransfer:  %{time_pretransfer}\n
time_redirect:  %{time_redirect}\n
time_starttransfer:  %{time_starttransfer}\n
----------\n
time_total:  %{time_total}\n
```

**Monitor Disk Usage:**

```bash
# Check disk usage
du -sh /home/c/ch167436/3dPrint/uploads/
du -sh /home/c/ch167436/3dPrint/logs/
du -sh /home/c/ch167436/3dPrint/backups/

# Alert if uploads exceed 1GB
USAGE=$(du -sm /home/c/ch167436/3dPrint/uploads/ | cut -f1)
if [ $USAGE -gt 1024 ]; then
    echo "Warning: Uploads directory exceeds 1GB"
fi
```

---

## Post-Deployment Testing

### End-to-End Test Checklist

Run through this checklist after deployment:

#### 1. Infrastructure Tests

- [ ] **HTTPS Working**: Visit https://yourdomain.com (no certificate warnings)
- [ ] **HTTPS Redirect**: Visit http://yourdomain.com (should redirect to HTTPS)
- [ ] **Security Headers**: Check headers with https://securityheaders.com
- [ ] **SSL Labs Test**: Check SSL with https://www.ssllabs.com/ssltest/

```bash
# Quick header check
curl -I https://yourdomain.com | grep -E "Strict-Transport|X-Frame|X-Content|Content-Security"
```

#### 2. API Tests

```bash
# Health check
curl https://yourdomain.com/api/health

# Services list
curl https://yourdomain.com/api/services

# Materials list
curl https://yourdomain.com/api/materials

# Sitemap
curl https://yourdomain.com/sitemap.xml

# Robots.txt
curl https://yourdomain.com/robots.txt
```

- [ ] **Health endpoint** returns 200 OK
- [ ] **Services API** returns data
- [ ] **Materials API** returns data
- [ ] **Sitemap** is generated correctly
- [ ] **Robots.txt** is accessible

#### 3. Frontend Tests

Visit and test each page:

- [ ] **Homepage** (https://yourdomain.com)
  - [ ] Loads without errors
  - [ ] API health check works
  - [ ] All images load
  - [ ] Navigation works

- [ ] **Services Page** (https://yourdomain.com/services.html)
  - [ ] Services load from API
  - [ ] Images display correctly

- [ ] **Calculator** (https://yourdomain.com/calculator.html)
  - [ ] Form displays correctly
  - [ ] Material selection works
  - [ ] Price calculation works
  - [ ] File upload works (test with small STL file)
  - [ ] Form submission works
  - [ ] Confirmation email received

- [ ] **Gallery** (https://yourdomain.com/gallery.html)
  - [ ] Gallery items load

- [ ] **Contact Form** (https://yourdomain.com/contact.html)
  - [ ] CAPTCHA displays
  - [ ] Form submits successfully
  - [ ] Confirmation email received

#### 4. Admin Panel Tests

- [ ] **Login** (https://yourdomain.com/admin)
  - [ ] Login form displays
  - [ ] Can log in with admin credentials
  - [ ] JWT token generated

- [ ] **Dashboard**
  - [ ] Statistics load
  - [ ] Charts display

- [ ] **Services Management**
  - [ ] Can view services
  - [ ] Can create service
  - [ ] Can edit service
  - [ ] Can delete service

- [ ] **Gallery Management**
  - [ ] Can upload images
  - [ ] Images are processed
  - [ ] Thumbnails generated

#### 5. Email Tests

- [ ] **Contact form email** received by admin
- [ ] **Calculator estimate email** received by admin
- [ ] **Password reset email** sends correctly (if implemented)
- [ ] Emails have correct sender address
- [ ] Email formatting is correct

#### 6. Performance Tests

```bash
# Run basic load test (requires Apache Bench)
ab -n 100 -c 10 https://yourdomain.com/

# Check page load time
curl -o /dev/null -s -w 'Total: %{time_total}s\n' https://yourdomain.com/
```

- [ ] Homepage loads in < 3 seconds
- [ ] API responses in < 500ms
- [ ] Images are optimized/compressed
- [ ] Gzip/Brotli compression active

#### 7. SEO Tests

- [ ] **Meta Tags**: View source and verify:
  - [ ] Title tags on all pages
  - [ ] Meta descriptions
  - [ ] Open Graph tags
  - [ ] Twitter Card tags

- [ ] **Structured Data**: Test with https://search.google.com/test/rich-results
  - [ ] Organization schema
  - [ ] LocalBusiness schema
  - [ ] Service schema

- [ ] **Sitemap**: Test with https://www.xml-sitemaps.com/validate-xml-sitemap.html

#### 8. Security Tests

- [ ] **XSS Protection**: Try injecting `<script>alert('XSS')</script>` in forms
- [ ] **SQL Injection**: Try `' OR '1'='1` in inputs
- [ ] **File Upload**: Try uploading non-allowed file types
- [ ] **CSRF Protection**: Submit forms without CSRF token (should fail)
- [ ] **Rate Limiting**: Make rapid API requests (should be throttled)
- [ ] **Directory Listing**: Try accessing `/uploads/` (should be forbidden)
- [ ] **Sensitive Files**: Try accessing `/.env`, `/composer.json` (should be blocked)

```bash
# Test directory listing
curl -I https://yourdomain.com/uploads/

# Test sensitive file access
curl -I https://yourdomain.com/.env
curl -I https://yourdomain.com/composer.json
```

#### 9. Mobile Tests

- [ ] Test on mobile device (phone/tablet)
- [ ] Responsive design works
- [ ] Touch interactions work
- [ ] Calculator works on mobile

#### 10. Browser Compatibility

Test in multiple browsers:
- [ ] Chrome/Edge
- [ ] Firefox
- [ ] Safari
- [ ] Mobile browsers

---

## Troubleshooting

### Common Issues and Solutions

#### 1. 500 Internal Server Error

**Symptoms:** White screen or 500 error

**Solutions:**

```bash
# Check error logs
tail -n 50 logs/app.log

# Check PHP error log
tail -n 50 /home/c/ch167436/logs/error.log

# Verify .htaccess syntax
apachectl configtest

# Check file permissions
ls -la

# Enable debug mode temporarily
# Edit .env: APP_DEBUG=true
```

Common causes:
- PHP version incompatibility
- Missing PHP extensions
- Syntax error in .htaccess
- Incorrect file permissions
- Missing Composer dependencies

#### 2. Database Connection Failed

**Symptoms:** "Database connection failed" error

**Solutions:**

```bash
# Test database connection
mysql -u ch167436_dbuser -p ch167436_3dprint -e "SELECT 1;"

# Verify credentials in .env
cat .env | grep DB_

# Check if database exists
mysql -u ch167436_dbuser -p -e "SHOW DATABASES;"

# Try using 127.0.0.1 instead of localhost
# Edit .env: DB_HOST=127.0.0.1
```

#### 3. HTTPS Not Working

**Symptoms:** Certificate errors or HTTPS not loading

**Solutions:**

```bash
# Check SSL certificate
openssl s_client -connect yourdomain.com:443

# Verify certificate in cPanel
# cPanel → SSL/TLS Status

# Clear browser cache
# Force refresh (Ctrl+Shift+R)

# Check for mixed content (HTTP resources on HTTPS page)
# Open browser console and look for warnings
```

#### 4. Emails Not Sending

**Symptoms:** Emails not received

**Solutions:**

```bash
# Test SMTP connection
telnet smtp.gmail.com 587

# Check logs for email errors
grep -i "mail" logs/app.log

# Verify SMTP credentials in .env
cat .env | grep MAIL_

# Try different SMTP server or port
# Port 587 (TLS) or 465 (SSL)

# Check spam folder
# Add SPF and DKIM records to DNS
```

#### 5. Mod_rewrite Not Working

**Symptoms:** 404 errors on all pages except index

**Solutions:**

```bash
# Verify mod_rewrite is enabled
apachectl -M | grep rewrite

# Check AllowOverride in Apache config
# Must be "AllowOverride All"

# Verify .htaccess file exists
ls -la api/.htaccess

# Check RewriteBase in .htaccess
# May need: RewriteBase /
```

#### 6. File Upload Failing

**Symptoms:** File uploads fail or timeout

**Solutions:**

```bash
# Check PHP upload limits
php -i | grep -E "upload_max_filesize|post_max_size|max_execution_time"

# Increase limits in php.ini or .htaccess
# php.ini:
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 120

# Or .htaccess:
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 120

# Check directory permissions
chmod 755 uploads/
```

#### 7. Cron Jobs Not Running

**Symptoms:** Backups not created, logs not rotated

**Solutions:**

```bash
# Check cron jobs are registered
crontab -l

# Check cron logs
tail -f /var/log/cron

# Test cron command manually
cd /home/c/ch167436/3dPrint && php scripts/backup-database.sh

# Check script permissions
chmod +x scripts/*.sh

# Verify PHP path
which php
# Use full path in crontab: /usr/bin/php
```

#### 8. High Memory Usage

**Symptoms:** Site slow or crashes

**Solutions:**

```bash
# Check memory limit
php -i | grep memory_limit

# Increase memory limit
# php.ini or .htaccess:
php_value memory_limit 256M

# Monitor memory usage
top -u ch167436

# Optimize database queries
# Add indexes, reduce joins

# Enable OpCache
# php.ini:
opcache.enable=1
opcache.memory_consumption=128
```

#### 9. Slow Page Load

**Symptoms:** Pages take > 3 seconds to load

**Solutions:**

```bash
# Check if compression is working
curl -H "Accept-Encoding: gzip" -I https://yourdomain.com/ | grep -i "content-encoding"

# Verify caching headers
curl -I https://yourdomain.com/assets/css/main.css | grep -i "cache-control"

# Enable OpCache
# Check database query performance
# Optimize images
# Enable CDN
# Minify CSS/JS
```

### Getting Help

If issues persist:

1. **Check Documentation:**
   - README.md
   - INSTALLATION.md
   - API documentation

2. **Review Logs:**
   - `logs/app.log`
   - PHP error log
   - Apache error log

3. **Contact Hosting Support:**
   - PHP version issues
   - Server configuration
   - Module availability

4. **Search Issues:**
   - Error messages
   - Stack Overflow
   - GitHub Issues (if open source)

---

## Launch Checklist

Before announcing the site is live:

### Pre-Launch

- [ ] All deployment steps completed
- [ ] SSL certificate active and HTTPS enforced
- [ ] Database migrated and seeded
- [ ] Environment configured for production (`APP_DEBUG=false`)
- [ ] Strong JWT_SECRET set
- [ ] All cron jobs configured
- [ ] Backups tested and working
- [ ] Monitoring and alerts configured

### Testing

- [ ] All end-to-end tests passed
- [ ] Forms work and emails received
- [ ] Admin panel fully functional
- [ ] Mobile responsive
- [ ] Cross-browser compatible
- [ ] Performance acceptable (< 3s page load)
- [ ] Security headers present
- [ ] No console errors

### SEO

- [ ] All pages have proper meta tags
- [ ] Sitemap generated and submitted to Google
- [ ] Robots.txt accessible
- [ ] Structured data validated
- [ ] Google Analytics configured (if applicable)
- [ ] Google Search Console verified

### Final Checks

- [ ] Domain DNS propagated
- [ ] Email deliverability tested
- [ ] Backup strategy verified
- [ ] Monitoring alerts tested
- [ ] Documentation up to date
- [ ] Admin credentials secured
- [ ] Support contacts identified

### Post-Launch

- [ ] Monitor logs for first 24 hours
- [ ] Check error rates
- [ ] Verify backups running
- [ ] Test user registrations/submissions
- [ ] Monitor performance metrics
- [ ] Review analytics
- [ ] Collect user feedback

---

## Maintenance Schedule

### Daily

- Monitor error logs
- Check backup completion
- Review critical alerts

### Weekly

- Review full logs
- Check disk usage
- Test backup restoration
- Review analytics

### Monthly

- Update dependencies (`composer update`)
- Review security advisories
- Optimize database
- Review and archive old logs
- Test disaster recovery

### Quarterly

- Full security audit
- Performance optimization
- Review and update documentation
- Plan feature updates

---

## Support and Resources

### Documentation

- [Installation Guide](INSTALLATION.md)
- [API Documentation](API.md)
- [Admin API Documentation](ADMIN_API.md)
- [SEO Guide](SEO_GUIDE.md)
- [Security Guide](ADMIN_SECURITY.md)

### PHP Resources

- [PHP Official Documentation](https://www.php.net/docs.php)
- [Composer Documentation](https://getcomposer.org/doc/)
- [PSR Standards](https://www.php-fig.org/psr/)

### Apache Resources

- [.htaccess Guide](https://httpd.apache.org/docs/current/howto/htaccess.html)
- [mod_rewrite Documentation](https://httpd.apache.org/docs/current/mod/mod_rewrite.html)

---

**Deployment completed successfully!** 🚀

Your 3D Print Platform is now live and ready to serve customers.
