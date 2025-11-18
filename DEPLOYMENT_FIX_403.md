# DEPLOYMENT FIX: Solving 403 Forbidden on nginx

## Problem Overview

When deploying to nginx shared hosting, you may encounter a **403 Forbidden** error. This is typically caused by one of the following issues:

1. **Wrong Web Root**: nginx is looking for files in a different directory than `/public_html`
2. **Missing index.php**: nginx is not configured to use `index.php` as a directory index
3. **Permissions Issues**: Files/directories don't have correct permissions
4. **PHP-FPM Not Configured**: nginx can't execute PHP files

---

## Solution 1: Identify the Correct Web Root

### Step 1: Find Where nginx Looks for Files

SSH into your server and check the nginx configuration:

```bash
# Check nginx configuration
cat /etc/nginx/sites-available/default
# OR
cat /etc/nginx/sites-available/3dprint-omsk.ru

# Look for the 'root' directive:
# server {
#     root /path/to/webroot;
#     ...
# }
```

Common web root paths on shared hosting:
- `/home/username/public_html`
- `/home/username/www`
- `/home/username/domains/3dprint-omsk.ru/public_html`
- `/var/www/html`
- `/usr/share/nginx/html`

### Step 2: Verify Current Location

```bash
# Where are your files currently?
cd /home/c/ch167436/3dPrint/public_html
pwd

# Check if this matches nginx root
```

### Step 3: Fix the Mismatch

**Option A: Move files to correct web root**
```bash
# If nginx root is /home/c/ch167436/public_html (without 3dPrint subfolder)
cd /home/c/ch167436
mv 3dPrint/public_html/* public_html/
mv 3dPrint/* .  # Move api, admin, src, etc to parent
```

**Option B: Create symlink**
```bash
# If nginx root is /home/c/ch167436/public_html
cd /home/c/ch167436
ln -s 3dPrint/public_html public_html
```

**Option C: Update nginx config** (requires server access)
```nginx
server {
    root /home/c/ch167436/3dPrint/public_html;
    index index.php index.html;
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    
    # Important: PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Route everything through index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

---

## Solution 2: Fix nginx Configuration for PHP Router

### Required nginx Configuration

Create or update `/etc/nginx/sites-available/3dprint-omsk.ru`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    
    # Web root - ADJUST THIS PATH!
    root /home/c/ch167436/3dPrint/public_html;
    
    # Index files
    index index.php index.html;
    
    # Charset
    charset utf-8;
    
    # Logging
    access_log /var/log/nginx/3dprint-omsk.ru-access.log;
    error_log /var/log/nginx/3dprint-omsk.ru-error.log;
    
    # Main location block - route everything through index.php
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
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(\.env|composer\.json|composer\.lock|README\.md) {
        deny all;
    }
}
```

**Enable the site:**
```bash
sudo ln -s /etc/nginx/sites-available/3dprint-omsk.ru /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## Solution 3: Fix File Permissions

```bash
cd /home/c/ch167436/3dPrint

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make scripts executable
chmod +x scripts/*.sh

# Secure .env
chmod 600 .env

# Ensure nginx can read files
chown -R www-data:www-data public_html/
# OR on shared hosting
chown -R ch167436:ch167436 public_html/
```

---

## Solution 4: Shared Hosting Without nginx Config Access

If you **cannot modify nginx configuration** (typical shared hosting), use the built-in PHP router:

### Step 1: Verify index.php is the Default

Contact your hosting provider to ensure:
1. `index.php` is in the list of directory indexes
2. PHP-FPM is enabled
3. The web root points to your `public_html` directory

### Step 2: Create .user.ini (if allowed)

```ini
; public_html/.user.ini
default_charset = "UTF-8"
max_execution_time = 300
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
```

### Step 3: Test the Router

```bash
# From your local machine
curl -I https://3dprint-omsk.ru/

# Should return:
# HTTP/2 200 OK
# Content-Type: text/html; charset=UTF-8

# Test API routing
curl https://3dprint-omsk.ru/api/services

# Test admin routing  
curl https://3dprint-omsk.ru/admin/

# Test static files
curl -I https://3dprint-omsk.ru/assets/css/style.css
```

---

## Solution 5: Debugging 403 Errors

### Check nginx Error Logs

```bash
sudo tail -f /var/log/nginx/error.log
# OR
tail -f ~/logs/error.log
```

Common error messages and fixes:

1. **"directory index of ... is forbidden"**
   - Missing `index.php` in the directory
   - Add `index index.php index.html;` to nginx config

2. **"open() ... failed (13: Permission denied)"**
   - Wrong file permissions
   - Run: `chmod 644 public_html/index.php`

3. **"FastCGI sent in stderr: Primary script unknown"**
   - PHP-FPM can't find the file
   - Check `root` directive in nginx config
   - Verify `SCRIPT_FILENAME` in fastcgi_params

4. **"Access denied"**
   - PHP security restrictions
   - Check `open_basedir` in php.ini
   - Contact hosting provider

### Check PHP-FPM Status

```bash
sudo systemctl status php8.2-fpm
# Should show: active (running)

# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

### Test PHP Execution

Create a test file:
```bash
echo "<?php phpinfo(); ?>" > public_html/test.php
```

Visit: `https://3dprint-omsk.ru/test.php`

If you see PHP info page, PHP is working. If you get 403, there's a configuration issue.

**Delete test file after testing:**
```bash
rm public_html/test.php
```

---

## Complete Deployment Checklist

After fixing the 403 error, complete the deployment:

```bash
# 1. Install Composer dependencies
cd /home/c/ch167436/3dPrint
composer install --no-dev --optimize-autoloader

# 2. Create .env file
cp .env.example .env
nano .env
# Configure database, JWT_SECRET, etc.

# 3. Create required directories
mkdir -p logs uploads backups/{database,files} temp storage
chmod 755 logs uploads backups temp storage
chmod 600 .env

# 4. Run migrations
php database/migrate.php

# 5. Seed initial data
php database/seed.php

# 6. Test deployment
php scripts/verify-deployment.php

# 7. Test site
curl -I https://3dprint-omsk.ru/
# Should return HTTP/2 200
```

---

## Quick Fix Commands

```bash
# Quick diagnostic
cd /home/c/ch167436/3dPrint
echo "Project root: $(pwd)"
echo "Public HTML exists: $(test -d public_html && echo YES || echo NO)"
echo "Index.php exists: $(test -f public_html/index.php && echo YES || echo NO)"
echo "Index.html exists: $(test -f public_html/index.html && echo YES || echo NO)"
ls -la public_html/index.*

# Quick permission fix
find public_html -type d -exec chmod 755 {} \;
find public_html -type f -exec chmod 644 {} \;

# Quick curl test
curl -I https://3dprint-omsk.ru/
curl -I https://3dprint-omsk.ru/index.php
curl -I https://3dprint-omsk.ru/index.html

# Check what nginx sees
curl -I https://3dprint-omsk.ru/ -H "Host: 3dprint-omsk.ru"
```

---

## Expected Results

After successful deployment:

✅ `curl -I https://3dprint-omsk.ru/` returns **HTTP/2 200**
✅ Browser shows the 3D Print Platform homepage
✅ All pages load correctly (about, services, calculator, etc.)
✅ API endpoints respond: `/api/services`, `/api/materials`
✅ Admin panel loads: `/admin/`
✅ No errors in nginx error log
✅ No errors in PHP-FPM log
✅ No errors in `logs/app.log`

---

## Contact Hosting Support

If you still get 403 after trying all solutions, contact your hosting provider with these questions:

1. What is the exact web root path for domain `3dprint-omsk.ru`?
2. Is `index.php` configured as a directory index?
3. Is PHP 8.2 FPM enabled and working?
4. Can you provide the nginx configuration for my domain?
5. Are there any security restrictions (open_basedir, disable_functions)?
6. Can I get access to nginx error logs?

---

## Additional Resources

- **NGINX_ROUTER_README.md** - Complete nginx router documentation
- **NGINX_ROUTER_DEPLOYMENT.md** - nginx deployment guide
- **DEPLOYMENT.md** - Full deployment documentation
- **SETUP_README.md** - Auto-deployment script guide
- **test-router-logic.md** - Router testing and validation

---

## Support

If you need help, check:
1. nginx error logs: `/var/log/nginx/error.log`
2. PHP-FPM logs: `/var/log/php8.2-fpm.log`
3. Application logs: `logs/app.log`
4. Setup logs: `logs/setup.log`

Good luck with your deployment! 🚀
