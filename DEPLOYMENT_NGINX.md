# nginx Deployment Guide and 403 Troubleshooting

Complete guide for deploying the 3D Print Platform on nginx servers and resolving 403 Forbidden errors.

## Table of Contents

1. [Quick Start](#quick-start)
2. [nginx 403 Forbidden - Complete Solution](#nginx-403-forbidden---complete-solution)
3. [nginx Configuration](#nginx-configuration)
4. [Deployment Steps](#deployment-steps)
5. [Verification Tests](#verification-tests)
6. [Contact Hosting Support](#contact-hosting-support)

---

## Quick Start

**Got a 403 Forbidden error?** Jump to [nginx 403 Forbidden Solution](#nginx-403-forbidden---complete-solution)

**Fresh deployment?** Follow [Deployment Steps](#deployment-steps)

**Need nginx config?** See [nginx Configuration](#nginx-configuration)

---

## nginx 403 Forbidden - Complete Solution

### Symptoms

- ❌ All pages return "403 Forbidden"
- ❌ Even `/test.php` returns 403
- ❌ Browser shows "403 Forbidden nginx/1.x.x"
- ❌ nginx error log: "access forbidden by rule" or "directory index forbidden"

### Why This Happens

nginx 403 errors occur when:

1. **Document root is wrong** - nginx is looking in the wrong directory
2. **Index files missing** - nginx can't find index.php or index.html
3. **PHP-FPM not configured** - nginx doesn't know how to run PHP
4. **File permissions** - nginx user can't read your files
5. **Security rules** - nginx configuration blocks access

### Solution: 5-Step Diagnostic

#### Step 1: Create Test File

```bash
# Create simple test file
cat > /home/c/ch167436/3dPrint/public_html/test.php << 'EOF'
<?php
echo "<h1>PHP Works!</h1>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
phpinfo();
EOF

# Set correct permissions
chmod 644 /home/c/ch167436/3dPrint/public_html/test.php

# Test in browser
# Visit: http://3dprint-omsk.ru/test.php
```

**What to expect:**

| Result | Meaning | Next Step |
|--------|---------|-----------|
| ✅ PHP info displays | PHP works, routing issue | Go to Step 5 |
| ❌ 403 Forbidden | nginx/permissions problem | Go to Step 2 |
| 📥 File downloads | PHP-FPM not configured | Contact support (see below) |
| ⚪ Blank page | PHP error | Check logs: `tail -f logs/error.log` |

#### Step 2: Verify File Permissions

```bash
# Check current permissions
ls -la /home/c/ch167436/3dPrint/public_html/

# Expected output:
# drwxr-xr-x (755) for directories
# -rw-r--r-- (644) for files

# Fix ALL permissions
cd /home/c/ch167436/3dPrint

# Fix directory permissions
find public_html -type d -exec chmod 755 {} \;

# Fix file permissions  
find public_html -type f -exec chmod 644 {} \;

# Verify parent directory
chmod 755 /home/c/ch167436/3dPrint/public_html

# Test again
curl -I http://3dprint-omsk.ru/test.php
```

**Still 403?** Go to Step 3.

#### Step 3: Check nginx Document Root

This is the **most common cause** of 403 errors!

```bash
# Find nginx configuration
sudo grep -r "3dprint-omsk.ru" /etc/nginx/
sudo grep -r "ch167436" /etc/nginx/

# View full config
sudo cat /etc/nginx/sites-available/default
# OR
sudo cat /etc/nginx/conf.d/default.conf
```

**Look for the `root` directive:**

```nginx
server {
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    root /home/c/ch167436/3dPrint/public_html;  # ← THIS MUST BE CORRECT!
    ...
}
```

**Common mistakes:**

| Wrong ❌ | Correct ✅ | Issue |
|----------|-----------|-------|
| `root /home/c/ch167436/3dPrint;` | `root /home/c/ch167436/3dPrint/public_html;` | Missing /public_html |
| `root /var/www/html;` | `root /home/c/ch167436/3dPrint/public_html;` | Wrong path entirely |
| `root /home/c/ch167436;` | `root /home/c/ch167436/3dPrint/public_html;` | Missing /3dPrint/public_html |

**If document root is wrong:**
- You have nginx config access? → Fix it (see [nginx Configuration](#nginx-configuration))
- Shared hosting? → Contact support (see [Contact Hosting Support](#contact-hosting-support))

#### Step 4: Check nginx User Permissions

```bash
# Find nginx user
ps aux | grep nginx | grep -v grep | head -1

# Common users: nginx, www-data, apache

# Test if nginx user can read your files
sudo -u www-data ls /home/c/ch167436/3dPrint/public_html/
# OR
sudo -u nginx ls /home/c/ch167436/3dPrint/public_html/
```

**If this command fails:**

```bash
# Option 1: Make files world-readable (recommended for shared hosting)
chmod 755 /home/c/ch167436/3dPrint
chmod 755 /home/c/ch167436/3dPrint/public_html
chmod -R 755 /home/c/ch167436/3dPrint/public_html/assets

# Option 2: Change ownership (requires sudo, VPS only)
sudo chown -R www-data:www-data /home/c/ch167436/3dPrint/public_html
```

#### Step 5: Check nginx Error Logs

```bash
# View recent errors
sudo tail -n 100 /var/log/nginx/error.log

# Watch live (open in separate terminal)
sudo tail -f /var/log/nginx/error.log

# Then visit your site to see real-time errors
```

**Common errors and fixes:**

```
[error] directory index of "/home/c/ch167436/3dPrint/public_html/" is forbidden
→ FIX: Add index directive to nginx config
    index index.php index.html;

[error] access forbidden by rule
→ FIX: Check nginx security rules, remove overly restrictive deny rules

[error] open() "/path/to/file" failed (13: Permission denied)
→ FIX: File permissions - chmod 644 for files, 755 for dirs

[error] FastCGI sent in stderr: "Primary script unknown"
→ FIX: Wrong SCRIPT_FILENAME in fastcgi_param
    Should be: $document_root$fastcgi_script_name

[error] connect() to unix:/var/run/php/php8.2-fpm.sock failed
→ FIX: PHP-FPM not running or wrong socket path
    Check: ps aux | grep php-fpm
    Start: sudo systemctl start php8.2-fpm
```

### Still Getting 403?

If you've tried all the above and **still** getting 403:

1. **Contact Hosting Support** (see template below)
2. **Request VPS/dedicated server** for full nginx control
3. **Switch to Apache hosting** (platform supports both)

---

## Contact Hosting Support

### Template Email for 403 Forbidden

```
Subject: Urgent: nginx 403 Forbidden - Document Root Configuration Required

Hello Support Team,

I am experiencing a 403 Forbidden error on all pages of my website and need your assistance with nginx configuration.

=== ACCOUNT INFORMATION ===
Domain: 3dprint-omsk.ru (including www.3dprint-omsk.ru)
Account: ch167436
Server: [your server name/IP]

=== CURRENT ISSUE ===
- All pages return "403 Forbidden"
- Even simple test.php files return 403
- File permissions are correct (verified):
  - Directories: 755 (drwxr-xr-x)
  - Files: 644 (-rw-r--r--)
- Files are readable by web server user

=== PROJECT STRUCTURE ===
Project location: /home/c/ch167436/3dPrint/
Web root (document root): /home/c/ch167436/3dPrint/public_html/

Contents of public_html/:
- index.php (main router for nginx)
- index.html (homepage)
- assets/ (CSS, JS, images)
- API and admin accessible via subdirectories

=== REQUIRED nginx CONFIGURATION ===

Please configure nginx for my domain with the following settings:

```nginx
server {
    listen 80;
    listen [::]:80;
    
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    
    # CRITICAL: Document root must point to public_html
    root /home/c/ch167436/3dPrint/public_html;
    
    # Index files
    index index.php index.html;
    
    # Main routing (required for PHP router)
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
    
    # PHP processing (required for PHP execution)
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
    
    # Security: Block hidden files
    location ~ /\. {
        deny all;
    }
}
```

=== PHP REQUIREMENTS ===
- PHP Version: 8.2+ (currently installed)
- PHP Extensions: pdo_mysql, mbstring, openssl, json, fileinfo
- PHP-FPM must be enabled and running

=== REQUEST SUMMARY ===
1. Set nginx document root to: /home/c/ch167436/3dPrint/public_html
2. Add index directive: index index.php index.html
3. Add try_files directive for routing through index.php
4. Configure PHP-FPM processing for .php files
5. Ensure PHP-FPM service is running

=== URGENCY ===
This is blocking the entire website from functioning. I would appreciate a quick response.

Please let me know if you need any additional information or access.

Thank you for your help!

Best regards,
[Your Name]
```

### Alternative: Short Support Request

If your hosting has a ticket system with character limits:

```
Subject: nginx 403 Forbidden - Need Document Root Fix

Issue: All pages return 403 Forbidden on domain 3dprint-omsk.ru

Needed:
1. Set nginx root to: /home/c/ch167436/3dPrint/public_html
2. Add: index index.php index.html;
3. Add: try_files $uri $uri/ /index.php$is_args$args;
4. Configure PHP-FPM for .php files

File permissions are already correct (755/644).

Urgent - entire site is down.

Thanks!
```

---

## nginx Configuration

### Complete nginx Server Block

Save this as `/etc/nginx/sites-available/3dprint-omsk.ru`:

```nginx
server {
    listen 80;
    listen [::]:80;
    
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    root /home/c/ch167436/3dPrint/public_html;
    index index.php index.html;
    
    # Logging
    access_log /var/log/nginx/3dprint_access.log;
    error_log /var/log/nginx/3dprint_error.log;
    
    # Main routing - CRITICAL for PHP router
    location / {
        # Try file, then directory, then route to index.php
        try_files $uri $uri/ /index.php$is_args$args;
    }
    
    # PHP-FPM processing
    location ~ \.php$ {
        # Security: Don't execute if file doesn't exist
        try_files $uri =404;
        
        # PHP-FPM socket (adjust path if needed)
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        
        # FastCGI parameters
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        
        # Timeouts
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }
    
    # Security: Block access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Security: Block access to sensitive files
    location ~* \.(env|log|sql)$ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Optimize static file serving
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|otf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Disable access to /api and /admin if accessed directly
    # (these are routed through index.php)
    location ~ ^/(api|admin)/ {
        try_files $uri $uri/ /index.php$is_args$args;
    }
    
    # Client upload size (for 3D model files)
    client_max_body_size 20M;
    
    # Compression
    gzip on;
    gzip_vary on;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;
}
```

### Enable the Configuration

```bash
# Create symlink to enable
sudo ln -s /etc/nginx/sites-available/3dprint-omsk.ru /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# If test passes, reload nginx
sudo systemctl reload nginx

# Check status
sudo systemctl status nginx
```

### nginx with SSL (After SSL Certificate is installed)

```nginx
# HTTP to HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    
    # Redirect all HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    root /home/c/ch167436/3dPrint/public_html;
    index index.php index.html;
    
    # SSL certificates (adjust paths)
    ssl_certificate /etc/letsencrypt/live/3dprint-omsk.ru/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/3dprint-omsk.ru/privkey.pem;
    
    # SSL settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Rest of configuration same as HTTP version...
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }
    
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTPS on;
    }
    
    # ... other locations ...
}
```

---

## Deployment Steps

### Step 1: Upload Files

```bash
# Option A: Via FTP/SFTP
# - Connect to server
# - Upload all files to /home/c/ch167436/3dPrint/
# - Ensure public_html/ directory is uploaded correctly

# Option B: Via SSH + Git
ssh user@yourserver
cd /home/c/ch167436/
git clone https://github.com/yourusername/3dprint.git 3dPrint
cd 3dPrint

# Option C: Via tar.gz
# On local machine:
tar -czf project.tar.gz . --exclude='.git' --exclude='node_modules'
scp project.tar.gz user@yourserver:/home/c/ch167436/

# On server:
ssh user@yourserver
cd /home/c/ch167436/
mkdir -p 3dPrint
tar -xzf project.tar.gz -C 3dPrint/
```

### Step 2: Set Permissions

```bash
cd /home/c/ch167436/3dPrint

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make scripts executable
chmod +x scripts/*.sh

# Secure .env (will be created in next step)
chmod 600 .env.example

# Create writable directories
mkdir -p logs uploads backups temp storage
chmod 755 logs uploads backups temp storage
```

### Step 3: Run Setup Script

```bash
cd /home/c/ch167436/3dPrint

# Run automated setup
bash scripts/setup.sh

# Follow prompts to configure:
# - Database connection
# - Admin email
# - SMTP settings
# - JWT secret (auto-generated)
```

The setup script will:
- ✅ Check PHP 8.2+ and required extensions
- ✅ Create necessary directories
- ✅ Install Composer dependencies
- ✅ Configure .env file
- ✅ Run database migrations
- ✅ Seed initial data
- ✅ Create admin users (admin/admin123)

### Step 4: Configure nginx

**If you have nginx config access:**

```bash
# Create nginx config
sudo nano /etc/nginx/sites-available/3dprint-omsk.ru

# Paste configuration from above
# Save and exit (Ctrl+X, Y, Enter)

# Enable site
sudo ln -s /etc/nginx/sites-available/3dprint-omsk.ru /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx
```

**If you DON'T have nginx config access (shared hosting):**

Contact hosting support with the [template email above](#contact-hosting-support).

### Step 5: Verify Deployment

Run verification tests (see [Verification Tests](#verification-tests) below).

---

## Verification Tests

### Test 1: PHP Execution

```bash
# Create test file (if not already created)
echo '<?php phpinfo(); ?>' > /home/c/ch167436/3dPrint/public_html/test.php

# Test via curl
curl http://3dprint-omsk.ru/test.php

# Expected: HTML with PHP info
# Should see PHP version, extensions, etc.

# Clean up
rm /home/c/ch167436/3dPrint/public_html/test.php
```

✅ **Pass**: PHP info displays  
❌ **Fail**: 403, blank page, or file downloads → Check PHP-FPM configuration

### Test 2: Homepage

```bash
# Test homepage
curl http://3dprint-omsk.ru/

# Expected: HTML content from index.html
# Should see <!DOCTYPE html>, <head>, <body>, etc.
```

✅ **Pass**: HTML content displays  
❌ **Fail**: 403 or 404 → Check document root and index directive

### Test 3: Static Files

```bash
# Test CSS file
curl -I http://3dprint-omsk.ru/assets/css/main.css

# Expected headers:
# HTTP/1.1 200 OK
# Content-Type: text/css

# Test JS file
curl -I http://3dprint-omsk.ru/assets/js/app.js

# Expected headers:
# HTTP/1.1 200 OK
# Content-Type: application/javascript
```

✅ **Pass**: 200 OK with correct Content-Type  
❌ **Fail**: 403 or wrong Content-Type → Check static file serving in nginx

### Test 4: API Endpoints

```bash
# Test health endpoint
curl http://3dprint-omsk.ru/api/health

# Expected: JSON {"status":"ok","timestamp":...}

# Test services endpoint
curl http://3dprint-omsk.ru/api/services

# Expected: JSON array of services
```

✅ **Pass**: JSON response received  
❌ **Fail**: HTML, 403, or 404 → Check API routing and /api/index.php

### Test 5: Admin Panel

```bash
# Test admin login page
curl http://3dprint-omsk.ru/admin/login

# Expected: HTML with login form or redirect to login
```

✅ **Pass**: HTML or redirect received  
❌ **Fail**: 403 or 404 → Check admin routing and /admin/index.php

### Test 6: SEO Files

```bash
# Test sitemap
curl http://3dprint-omsk.ru/sitemap.xml

# Expected: XML sitemap

# Test robots.txt
curl http://3dprint-omsk.ru/robots.txt

# Expected: robots.txt content
```

✅ **Pass**: XML/text content received  
❌ **Fail**: 404 → Check routing in public_html/index.php

### Test 7: 404 Handling

```bash
# Test non-existent page
curl -I http://3dprint-omsk.ru/nonexistent-page

# Expected:
# HTTP/1.1 404 Not Found
```

✅ **Pass**: 404 status code  
❌ **Fail**: 200 or other → Check 404 handling in router

### Complete Test Script

Save as `test-deployment.sh`:

```bash
#!/bin/bash

echo "=== nginx Deployment Verification ==="
echo ""

DOMAIN="http://3dprint-omsk.ru"

echo "Test 1: PHP Execution"
curl -s -o /dev/null -w "Status: %{http_code}\n" $DOMAIN/test.php
echo ""

echo "Test 2: Homepage"
curl -s -o /dev/null -w "Status: %{http_code}\n" $DOMAIN/
echo ""

echo "Test 3: CSS File"
curl -s -o /dev/null -w "Status: %{http_code} | Content-Type: %{content_type}\n" $DOMAIN/assets/css/main.css
echo ""

echo "Test 4: API Health"
curl -s -o /dev/null -w "Status: %{http_code}\n" $DOMAIN/api/health
echo ""

echo "Test 5: Admin Panel"
curl -s -o /dev/null -w "Status: %{http_code}\n" $DOMAIN/admin/login
echo ""

echo "Test 6: Sitemap"
curl -s -o /dev/null -w "Status: %{http_code}\n" $DOMAIN/sitemap.xml
echo ""

echo "Test 7: 404 Page"
curl -s -o /dev/null -w "Status: %{http_code}\n" $DOMAIN/nonexistent
echo ""

echo "=== Tests Complete ==="
echo "All tests should return 200 OK (except 404 test should return 404)"
```

Run tests:

```bash
chmod +x test-deployment.sh
./test-deployment.sh
```

---

## Troubleshooting Quick Reference

| Issue | Quick Fix |
|-------|-----------|
| 403 Forbidden | Check nginx document root, file permissions, nginx error log |
| PHP files download | Enable PHP-FPM in nginx config |
| 404 on all pages | Add `try_files` directive to nginx config |
| CSS/JS not loading | Check static file serving, clear browser cache |
| API returns HTML | Verify `/api/index.php` exists and is executable |
| Admin 404 | Verify `/admin/index.php` exists and is executable |
| Blank pages | Enable error display, check PHP error logs |
| Database connection failed | Check `.env` database credentials |

---

## Additional Resources

- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Complete troubleshooting guide for all issues
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - General deployment guide (Apache + nginx)
- **[NGINX_ROUTER_DEPLOYMENT.md](NGINX_ROUTER_DEPLOYMENT.md)** - Detailed nginx router documentation
- **[SETUP_SCRIPT_GUIDE.md](SETUP_SCRIPT_GUIDE.md)** - Automated setup script guide
- **[README.md](README.md)** - Project overview and quick start

---

## Summary Checklist

Before contacting support, verify:

- [ ] Files uploaded to correct location (`/home/c/ch167436/3dPrint/`)
- [ ] `public_html/` directory exists and contains `index.php`
- [ ] File permissions: 755 for directories, 644 for files
- [ ] Test file (`test.php`) created and accessible
- [ ] nginx error log checked (`/var/log/nginx/error.log`)
- [ ] PHP-FPM is running (`ps aux | grep php-fpm`)

If all above checked and still 403 → Contact hosting support with template email.

---

**Last Updated:** 2024-11-18  
**Platform Version:** 1.0  
**nginx Versions Tested:** 1.18, 1.20, 1.22, 1.24
