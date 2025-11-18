# Troubleshooting Guide

Comprehensive guide for diagnosing and fixing common issues with the 3D Print Platform.

## Table of Contents

1. [nginx 403 Forbidden Error](#nginx-403-forbidden-error)
2. [Database Connection Issues](#database-connection-issues)
3. [500 Internal Server Error](#500-internal-server-error)
4. [API Not Working](#api-not-working)
5. [Admin Panel Issues](#admin-panel-issues)
6. [Email Not Sending](#email-not-sending)
7. [File Upload Problems](#file-upload-problems)
8. [CSS/JS Not Loading](#cssjs-not-loading)
9. [CORS Errors](#cors-errors)
10. [Performance Issues](#performance-issues)
11. [Reset Admin Password](#reset-admin-password)
12. [Clear Cache and Temp Files](#clear-cache-and-temp-files)
13. [Check Logs](#check-logs)
14. [Contact Hosting Support](#contact-hosting-support)

---

## nginx 403 Forbidden Error

### Symptoms
- All pages return "403 Forbidden"
- Even simple test.php returns 403
- nginx error: "access forbidden by rule" or "directory index forbidden"

### Root Causes
1. nginx document root is not set to `/public_html/`
2. nginx not configured to execute index.php
3. PHP-FPM not configured properly
4. File permissions too restrictive
5. nginx security rules blocking access

### Solution 1: Verify nginx Document Root

**Check current document root:**

```bash
# SSH into server
ssh user@yourserver

# Find nginx configuration for your domain
grep -r "3dprint-omsk.ru" /etc/nginx/
# or
grep -r "root" /etc/nginx/sites-available/default
```

**Expected nginx configuration:**

```nginx
server {
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    root /home/c/ch167436/3dPrint/public_html;  # MUST point to public_html
    index index.php index.html;
    
    # This is critical for the router to work
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
}
```

**If document root is wrong:**
- Contact hosting support (see [Contact Hosting Support](#contact-hosting-support))
- Request to set document root to: `/home/c/ch167436/3dPrint/public_html`

### Solution 2: Test PHP Execution

Create a test file to verify PHP is working:

```bash
# Create test.php in public_html
cat > /home/c/ch167436/3dPrint/public_html/test.php << 'EOF'
<?php
phpinfo();
echo "\n\nPHP is working correctly!\n";
?>
EOF

# Set permissions
chmod 644 /home/c/ch167436/3dPrint/public_html/test.php

# Test from browser
# Visit: https://3dprint-omsk.ru/test.php
```

**If test.php returns 403:**
- PHP-FPM is not configured for your domain
- Contact hosting support to enable PHP-FPM

**If test.php shows PHP info:**
- PHP is working, the issue is with routing
- Proceed to Solution 3

### Solution 3: Check File Permissions

```bash
# Check permissions
ls -la /home/c/ch167436/3dPrint/public_html/

# Correct permissions should be:
# Directories: 755 (drwxr-xr-x)
# Files: 644 (-rw-r--r--)
# PHP files: 644 (-rw-r--r--)

# Fix permissions if needed
cd /home/c/ch167436/3dPrint

# Set directory permissions
find public_html -type d -exec chmod 755 {} \;

# Set file permissions
find public_html -type f -exec chmod 644 {} \;
```

### Solution 4: Verify nginx Router is Present

```bash
# Check index.php exists
ls -la /home/c/ch167436/3dPrint/public_html/index.php

# Verify it's the router (should be ~200 lines)
wc -l /home/c/ch167436/3dPrint/public_html/index.php

# Check it's readable
head -20 /home/c/ch167436/3dPrint/public_html/index.php
```

**Expected output:**
- File exists and is ~200 lines
- Contains "Main Entry Point Router for nginx Compatibility"
- Permissions: `-rw-r--r--` (644)

### Solution 5: Test Direct API Access

Sometimes the issue is with routing, but direct access works:

```bash
# Test API directly (bypassing router)
curl http://3dprint-omsk.ru/api/index.php

# If this works, the issue is with nginx routing to index.php
```

### Solution 6: Enable PHP Error Display

Temporarily enable PHP errors to see what's happening:

```bash
# Add to the top of public_html/index.php
cat > /tmp/debug.php << 'EOF'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
EOF

# Backup original
cp public_html/index.php public_html/index.php.backup

# Add debug code
cat /tmp/debug.php public_html/index.php.backup > public_html/index.php

# Test again
curl http://3dprint-omsk.ru/

# Restore original after debugging
mv public_html/index.php.backup public_html/index.php
```

### Solution 7: Workaround - Alternative Paths

If nginx refuses to serve from `/home/c/ch167436/3dPrint/public_html`, try alternative paths:

**Option A: Symlink (if allowed)**

```bash
# Create symlink from web root to your public_html
ln -s /home/c/ch167436/3dPrint/public_html /var/www/html/3dprint

# Ask hosting support to point nginx to: /var/www/html/3dprint
```

**Option B: Move Files (not recommended)**

```bash
# Only if hosting support confirms the web root location
# DO NOT do this without confirmation!

# Example if web root is /var/www/html
cp -r /home/c/ch167436/3dPrint/public_html/* /var/www/html/
```

### Solution 8: Contact Hosting Support

If none of the above works, contact hosting support with this information:

```
Subject: nginx 403 Forbidden - Need Document Root Configuration

Hello,

I'm experiencing a 403 Forbidden error on my website 3dprint-omsk.ru.

Current setup:
- Domain: 3dprint-omsk.ru
- Project path: /home/c/ch167436/3dPrint/
- Web root should be: /home/c/ch167436/3dPrint/public_html/
- PHP version: 8.2
- Server: nginx

Issue:
- All pages return 403 Forbidden
- Even test.php returns 403
- File permissions are correct (755 for dirs, 644 for files)

Request:
Please configure nginx to:
1. Set document root to: /home/c/ch167436/3dPrint/public_html
2. Set index files to: index.php index.html
3. Enable PHP-FPM for .php files
4. Add fallback routing: try_files $uri $uri/ /index.php$is_args$args;

nginx configuration needed:
server {
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    root /home/c/ch167436/3dPrint/public_html;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}

Thank you!
```

---

## Database Connection Issues

### Symptoms
- "Database connection failed" error
- "Access denied for user" error
- "Unknown database" error

### Solution 1: Verify Database Credentials

```bash
# Check .env file
cat /home/c/ch167436/3dPrint/.env | grep DB_

# Test connection directly
mysql -h localhost -u ch167436_dbuser -p ch167436_3dprint
# Enter password when prompted
```

**If connection fails:**
- Verify username and password are correct
- Check database name is correct
- Verify user has permissions on database

### Solution 2: Check Database Exists

```bash
# List all databases
mysql -u ch167436_dbuser -p -e "SHOW DATABASES;"

# If database doesn't exist, create it
mysql -u root -p -e "CREATE DATABASE ch167436_3dprint CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Solution 3: Grant Permissions

```bash
# Grant all permissions
mysql -u root -p << EOF
GRANT ALL PRIVILEGES ON ch167436_3dprint.* TO 'ch167436_dbuser'@'localhost';
FLUSH PRIVILEGES;
EOF
```

### Solution 4: Update .env Configuration

```bash
# Edit .env
nano /home/c/ch167436/3dPrint/.env

# Update these lines:
DB_HOST=localhost
DB_PORT=3306
DB_NAME=ch167436_3dprint
DB_USER=ch167436_dbuser
DB_PASS=your_actual_password
DB_CHARSET=utf8mb4
```

---

## 500 Internal Server Error

### Symptoms
- White screen or "500 Internal Server Error"
- No specific error message

### Solution 1: Check PHP Error Logs

```bash
# Check application logs
tail -n 50 /home/c/ch167436/3dPrint/logs/error.log

# Check PHP-FPM logs
tail -n 50 /var/log/php8.2-fpm.log

# Check nginx error logs
sudo tail -n 50 /var/log/nginx/error.log
```

### Solution 2: Enable Debug Mode

```bash
# Edit .env
nano /home/c/ch167436/3dPrint/.env

# Change these:
APP_ENV=development
APP_DEBUG=true

# Test the site

# IMPORTANT: Disable after debugging
APP_ENV=production
APP_DEBUG=false
```

### Solution 3: Check PHP Version

```bash
# Check PHP version
php -v

# Should be PHP 8.2+
# If wrong version, update via cPanel or contact support
```

### Solution 4: Verify Composer Dependencies

```bash
# Reinstall dependencies
cd /home/c/ch167436/3dPrint
composer install --no-dev --optimize-autoloader

# Check autoloader
php -r "require 'vendor/autoload.php'; echo 'OK';"
```

### Solution 5: Check File Permissions

```bash
# Verify bootstrap.php is readable
ls -la /home/c/ch167436/3dPrint/bootstrap.php

# Verify src/ directory is readable
ls -la /home/c/ch167436/3dPrint/src/

# Fix if needed
chmod 644 bootstrap.php
chmod -R 755 src/
```

---

## API Not Working

### Symptoms
- API returns HTML instead of JSON
- API returns 404 Not Found
- API returns empty response

### Solution 1: Test API Directly

```bash
# Test health endpoint
curl http://3dprint-omsk.ru/api/health

# Test with full URL
curl http://3dprint-omsk.ru/api/index.php

# Check response headers
curl -I http://3dprint-omsk.ru/api/services
```

### Solution 2: Verify API Files Exist

```bash
# Check API directory
ls -la /home/c/ch167436/3dPrint/api/

# Should contain:
# - index.php
# - routes.php
# - .htaccess
```

### Solution 3: Check API Logs

```bash
# View API logs
tail -f /home/c/ch167436/3dPrint/logs/api.log

# Check for errors
grep ERROR /home/c/ch167436/3dPrint/logs/api.log
```

### Solution 4: Test Routing

```bash
# Create test API endpoint
cat > /home/c/ch167436/3dPrint/api/test-direct.php << 'EOF'
<?php
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'message' => 'Direct API access works']);
EOF

# Test direct access
curl http://3dprint-omsk.ru/api/test-direct.php

# If this works but /api/test fails, routing is the issue
```

---

## Admin Panel Issues

### Symptoms
- Cannot access /admin/login
- Login page returns 404
- Login fails with "Invalid credentials"
- Session expires immediately

### Solution 1: Verify Admin Files Exist

```bash
# Check admin directory
ls -la /home/c/ch167436/3dPrint/admin/

# Should contain:
# - index.php
# - routes.php
# - .htaccess
```

### Solution 2: Check JWT Configuration

```bash
# Verify JWT_SECRET in .env
cat /home/c/ch167436/3dPrint/.env | grep JWT_SECRET

# Should be at least 64 characters
# If not set, generate one:
openssl rand -base64 64
```

### Solution 3: Create Admin User

```bash
# Run admin user seed
cd /home/c/ch167436/3dPrint
php database/seeds/AdminUserSeed.php

# Default credentials created:
# Username: admin
# Password: admin123
# Email: admin@example.com
```

### Solution 4: Test Admin API

```bash
# Test login endpoint
curl -X POST http://3dprint-omsk.ru/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Should return JWT token
```

---

## Email Not Sending

### Symptoms
- Contact form submits but no email received
- "Mail send failed" error
- Emails go to spam

### Solution 1: Verify SMTP Configuration

```bash
# Check .env mail settings
cat /home/c/ch167436/3dPrint/.env | grep MAIL_

# Required settings:
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="3D Print Platform"
```

### Solution 2: Test SMTP Connection

```bash
# Create test script
cat > /home/c/ch167436/3dPrint/test-email.php << 'EOF'
<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = $_ENV['MAIL_HOST'];
$mail->Port = $_ENV['MAIL_PORT'];
$mail->SMTPAuth = true;
$mail->Username = $_ENV['MAIL_USERNAME'];
$mail->Password = $_ENV['MAIL_PASSWORD'];
$mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];

$mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
$mail->addAddress($_ENV['ADMIN_EMAIL']);
$mail->Subject = 'Test Email';
$mail->Body = 'This is a test email from 3D Print Platform.';

try {
    $mail->send();
    echo "Email sent successfully!\n";
} catch (Exception $e) {
    echo "Email failed: " . $mail->ErrorInfo . "\n";
}
EOF

# Run test
php /home/c/ch167436/3dPrint/test-email.php
```

### Solution 3: Use Gmail App Password

If using Gmail:

1. Go to Google Account → Security
2. Enable 2-Step Verification
3. Generate App Password
4. Use App Password in `MAIL_PASSWORD` (not your regular password)

### Solution 4: Check Mail Logs

```bash
# Check for mail errors
grep -i "mail" /home/c/ch167436/3dPrint/logs/error.log

# Check PHPMailer debug logs
grep -i "phpmailer" /home/c/ch167436/3dPrint/logs/app.log
```

---

## File Upload Problems

### Symptoms
- File upload fails with "File too large"
- Upload returns 413 or 500 error
- Files not saving to uploads directory

### Solution 1: Check Upload Directory

```bash
# Verify directory exists
ls -la /home/c/ch167436/3dPrint/uploads/

# Create if missing
mkdir -p /home/c/ch167436/3dPrint/uploads/models

# Set permissions
chmod 755 /home/c/ch167436/3dPrint/uploads/
chmod 755 /home/c/ch167436/3dPrint/uploads/models/
```

### Solution 2: Check PHP Upload Limits

```bash
# Check current limits
php -i | grep -E "upload_max_filesize|post_max_size|max_file_uploads"

# Expected:
# upload_max_filesize => 10M
# post_max_size => 12M
# max_file_uploads => 20
```

**To increase limits:**

Create `.user.ini` in public_html:

```bash
cat > /home/c/ch167436/3dPrint/public_html/.user.ini << EOF
upload_max_filesize = 20M
post_max_size = 25M
max_file_uploads = 20
memory_limit = 256M
EOF
```

### Solution 3: Check nginx Client Body Size

```bash
# nginx might have client_max_body_size limit
# Contact hosting support to increase:

# Add to nginx server block:
client_max_body_size 20M;
```

### Solution 4: Test Upload

```bash
# Create test upload script
cat > /home/c/ch167436/3dPrint/public_html/test-upload.html << 'EOF'
<!DOCTYPE html>
<html>
<body>
<form action="test-upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="testfile">
    <input type="submit" value="Upload">
</form>
</body>
</html>
EOF

cat > /home/c/ch167436/3dPrint/public_html/test-upload.php << 'EOF'
<?php
if ($_FILES['testfile']['error'] === UPLOAD_ERR_OK) {
    echo "Upload successful!<br>";
    echo "File: " . $_FILES['testfile']['name'] . "<br>";
    echo "Size: " . $_FILES['testfile']['size'] . " bytes<br>";
} else {
    echo "Upload failed with error: " . $_FILES['testfile']['error'];
}
EOF

# Visit: http://3dprint-omsk.ru/test-upload.html
```

---

## CSS/JS Not Loading

### Symptoms
- Page loads but no styling
- Browser console shows 404 for CSS/JS files
- Files return wrong MIME type

### Solution 1: Disable Browser Cache

**Chrome/Edge:**
- Press F12 (Developer Tools)
- Right-click Refresh button
- Select "Empty Cache and Hard Reload"

**Firefox:**
- Press Ctrl+Shift+Delete
- Clear "Cached Web Content"

### Solution 2: Check File Paths

```bash
# Verify files exist
ls -la /home/c/ch167436/3dPrint/public_html/assets/css/
ls -la /home/c/ch167436/3dPrint/public_html/assets/js/

# Check for minified versions
ls -la /home/c/ch167436/3dPrint/public_html/assets/css/*.min.css
ls -la /home/c/ch167436/3dPrint/public_html/assets/js/*.min.js
```

### Solution 3: Test Direct Access

```bash
# Test CSS file directly
curl -I http://3dprint-omsk.ru/assets/css/main.css

# Should return:
# Content-Type: text/css
# Status: 200 OK
```

### Solution 4: Check MIME Types

```bash
# Verify nginx router serves correct MIME types
# Check public_html/index.php getMimeType() function

# Test with curl
curl -I http://3dprint-omsk.ru/assets/css/main.css | grep Content-Type
curl -I http://3dprint-omsk.ru/assets/js/app.js | grep Content-Type
```

---

## CORS Errors

### Symptoms
- Browser console: "CORS policy: No 'Access-Control-Allow-Origin' header"
- API calls from frontend fail
- OPTIONS requests return error

### Solution 1: Check CORS Configuration

```bash
# Verify .env CORS settings
cat /home/c/ch167436/3dPrint/.env | grep CORS_

# Should have:
CORS_ALLOWED_ORIGINS=https://yourdomain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization
```

### Solution 2: Test CORS Headers

```bash
# Test API endpoint
curl -I -X OPTIONS http://3dprint-omsk.ru/api/services \
  -H "Origin: https://3dprint-omsk.ru"

# Should return:
# Access-Control-Allow-Origin: https://3dprint-omsk.ru
# Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS
```

### Solution 3: Update CORS Middleware

If CORS still not working, check API middleware:

```bash
# Verify CorsMiddleware is loaded
grep -r "CorsMiddleware" /home/c/ch167436/3dPrint/api/
```

---

## Performance Issues

### Symptoms
- Pages load slowly (>3 seconds)
- High server load
- API responses delayed

### Solution 1: Enable OPcache

```bash
# Check if OPcache is enabled
php -i | grep opcache.enable

# If not enabled, create .user.ini
cat >> /home/c/ch167436/3dPrint/public_html/.user.ini << EOF
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
EOF
```

### Solution 2: Check Database Performance

```bash
# Check slow queries
mysql -u ch167436_dbuser -p ch167436_3dprint -e "SHOW PROCESSLIST;"

# Check table sizes
mysql -u ch167436_dbuser -p ch167436_3dprint -e "
SELECT table_name, 
       ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'ch167436_3dprint'
ORDER BY (data_length + index_length) DESC;"
```

### Solution 3: Clear Logs

```bash
# Check log file sizes
du -sh /home/c/ch167436/3dPrint/logs/*

# Clear old logs
cd /home/c/ch167436/3dPrint
php scripts/rotate-logs.php
```

### Solution 4: Monitor Resources

```bash
# Check disk usage
df -h

# Check memory
free -m

# Check CPU
top -bn1 | grep "Cpu(s)"
```

---

## Reset Admin Password

### Method 1: Using Database

```bash
# Generate password hash
php -r "echo password_hash('new_password', PASSWORD_DEFAULT);"

# Update in database
mysql -u ch167436_dbuser -p ch167436_3dprint << EOF
UPDATE admins 
SET password = '$2y$10$generated_hash_from_above' 
WHERE username = 'admin';
EOF
```

### Method 2: Re-run Seed

```bash
# WARNING: This will reset to default credentials
cd /home/c/ch167436/3dPrint
php database/seeds/AdminUserSeed.php

# Default credentials:
# Username: admin
# Password: admin123
```

### Method 3: Create New Admin

```bash
# Create script
cat > /home/c/ch167436/3dPrint/create-admin.php << 'EOF'
<?php
require 'bootstrap.php';

$db = $container->get('database');
$username = 'newadmin';
$password = password_hash('secure_password', PASSWORD_DEFAULT);
$email = 'newadmin@example.com';

$db->query(
    "INSERT INTO admins (username, password, email, role, status) 
     VALUES (?, ?, ?, 'super_admin', 'active')",
    [$username, $password, $email]
);

echo "Admin created: $username / secure_password\n";
EOF

# Run script
php /home/c/ch167436/3dPrint/create-admin.php
```

---

## Clear Cache and Temp Files

### Clear Application Cache

```bash
cd /home/c/ch167436/3dPrint

# Clear temp files
php scripts/cleanup-temp.php

# Or manually
rm -rf temp/*
```

### Clear Browser Cache

**All Browsers:**
- Press Ctrl+Shift+Delete (Windows/Linux)
- Press Cmd+Shift+Delete (Mac)
- Select "Cached Images and Files"
- Click Clear

### Clear OPcache

```bash
# Create cache clear script
cat > /home/c/ch167436/3dPrint/public_html/clear-cache.php << 'EOF'
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!";
} else {
    echo "OPcache is not enabled.";
}
EOF

# Visit: http://3dprint-omsk.ru/clear-cache.php

# Delete after use
rm /home/c/ch167436/3dPrint/public_html/clear-cache.php
```

---

## Check Logs

### Application Logs

```bash
# View recent errors
tail -n 100 /home/c/ch167436/3dPrint/logs/error.log

# Follow logs in real-time
tail -f /home/c/ch167436/3dPrint/logs/error.log

# Search for specific error
grep "Database connection failed" /home/c/ch167436/3dPrint/logs/error.log

# Count errors today
grep "$(date +%Y-%m-%d)" /home/c/ch167436/3dPrint/logs/error.log | wc -l
```

### nginx Logs

```bash
# nginx error log
sudo tail -n 100 /var/log/nginx/error.log

# nginx access log
sudo tail -n 100 /var/log/nginx/access.log

# Filter for 404/500 errors
sudo tail -f /var/log/nginx/access.log | grep -E " (404|500|502|503) "
```

### PHP-FPM Logs

```bash
# PHP-FPM error log
sudo tail -n 100 /var/log/php8.2-fpm.log

# Follow in real-time
sudo tail -f /var/log/php8.2-fpm.log
```

### Cron Logs

```bash
# View cron execution logs
tail -n 100 /home/c/ch167436/3dPrint/logs/cron.log

# View backup logs
tail -n 100 /home/c/ch167436/3dPrint/logs/backup.log

# Check cron jobs are running
grep "cron" /var/log/syslog | tail -20
```

---

## Contact Hosting Support

When contacting hosting support, provide this information:

### Template Email

```
Subject: Technical Support Required - [Specific Issue]

Hello Support Team,

I need assistance with my hosting account for 3dprint-omsk.ru.

Account Information:
- Domain: 3dprint-omsk.ru
- Account: ch167436
- Server: [your server name]
- Project path: /home/c/ch167436/3dPrint/

Issue:
[Describe the specific problem]

What I've Tried:
[List troubleshooting steps you've already done]

Requested Action:
[Specific request, e.g., "Please configure nginx document root"]

Additional Information:
- PHP Version: 8.2
- Server: nginx
- Database: MySQL/MariaDB

Please let me know if you need any additional information.

Thank you!
```

### Common Support Requests

#### Request 1: Configure nginx Document Root

```
Please configure nginx for domain 3dprint-omsk.ru:

Document root: /home/c/ch167436/3dPrint/public_html
Index files: index.php index.html
PHP version: 8.2

nginx configuration needed:
server {
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    root /home/c/ch167436/3dPrint/public_html;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

#### Request 2: Enable PHP-FPM

```
Please enable PHP-FPM for my domain 3dprint-omsk.ru.

Requirements:
- PHP version: 8.2
- PHP extensions: pdo_mysql, mbstring, openssl, json, fileinfo
- PHP-FPM socket: /var/run/php/php8.2-fpm.sock

Current issue:
PHP files return 403 Forbidden or are downloaded instead of executed.
```

#### Request 3: Increase Upload Limits

```
Please increase PHP upload limits for domain 3dprint-omsk.ru:

Requested limits:
- upload_max_filesize: 20M
- post_max_size: 25M
- max_file_uploads: 20
- memory_limit: 256M

Also increase nginx client_max_body_size to 20M.

These limits are needed for 3D model file uploads.
```

#### Request 4: SSL Certificate Issue

```
I need help with SSL certificate for 3dprint-omsk.ru.

Issue: [Describe SSL problem]

Requested action:
- Install Let's Encrypt SSL certificate for 3dprint-omsk.ru and www.3dprint-omsk.ru
- Enable automatic renewal
- Configure HTTPS redirect
```

### Hosting Support Contact Methods

Most hosting providers offer:

1. **Support Ticket** (cPanel → Support)
2. **Live Chat** (check hosting website)
3. **Email** (support@yourhosting.com)
4. **Phone** (check hosting documentation)

**Response Time:**
- Shared hosting: Usually 24-48 hours
- VPS/Dedicated: Usually 4-12 hours
- Emergency issues: Use phone support

---

## Quick Diagnostic Script

Create a comprehensive diagnostic script:

```bash
cat > /home/c/ch167436/3dPrint/diagnose.php << 'EOF'
<?php
echo "=== 3D Print Platform Diagnostics ===\n\n";

// PHP Version
echo "PHP Version: " . PHP_VERSION . "\n";

// Check Extensions
$required = ['pdo_mysql', 'mbstring', 'openssl', 'json', 'fileinfo'];
echo "\nRequired PHP Extensions:\n";
foreach ($required as $ext) {
    echo "  $ext: " . (extension_loaded($ext) ? "✓" : "✗") . "\n";
}

// Check Files
echo "\nCritical Files:\n";
$files = [
    'bootstrap.php',
    'vendor/autoload.php',
    '.env',
    'public_html/index.php',
    'api/index.php',
    'admin/index.php'
];
foreach ($files as $file) {
    echo "  $file: " . (file_exists($file) ? "✓" : "✗") . "\n";
}

// Check Directories
echo "\nWritable Directories:\n";
$dirs = ['logs', 'uploads', 'backups', 'temp'];
foreach ($dirs as $dir) {
    $writable = is_dir($dir) && is_writable($dir);
    echo "  $dir: " . ($writable ? "✓" : "✗") . "\n";
}

// Check .env
if (file_exists('.env')) {
    require 'vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    echo "\nEnvironment Configuration:\n";
    echo "  APP_ENV: " . ($_ENV['APP_ENV'] ?? 'not set') . "\n";
    echo "  DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
    echo "  DB_NAME: " . ($_ENV['DB_NAME'] ?? 'not set') . "\n";
    echo "  JWT_SECRET: " . (isset($_ENV['JWT_SECRET']) ? "✓ set" : "✗ not set") . "\n";
}

// Test Database
if (isset($_ENV['DB_HOST'])) {
    echo "\nDatabase Connection: ";
    try {
        $pdo = new PDO(
            "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
            $_ENV['DB_USER'],
            $_ENV['DB_PASS']
        );
        echo "✓ Connected\n";
        
        // Check tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "  Tables: " . count($tables) . "\n";
    } catch (PDOException $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Diagnostics Complete ===\n";
EOF

# Run diagnostics
cd /home/c/ch167436/3dPrint
php diagnose.php
```

---

## Additional Resources

- [DEPLOYMENT.md](DEPLOYMENT.md) - Complete deployment guide
- [NGINX_ROUTER_DEPLOYMENT.md](NGINX_ROUTER_DEPLOYMENT.md) - nginx-specific deployment
- [SETUP_SCRIPT_GUIDE.md](SETUP_SCRIPT_GUIDE.md) - Auto-deployment script guide
- [README.md](README.md) - Project documentation
- [API.md](API.md) - API documentation

---

**Last Updated:** 2024-11-18
