# nginx Router Deployment Guide

## Quick Start

This guide will help you deploy the PHP-based router for nginx compatibility on shared hosting.

## Prerequisites

- nginx web server (any version)
- PHP 8.2+ with PHP-FPM
- Access to upload files to your hosting
- Basic understanding of file permissions

## Deployment Steps

### Step 1: Upload Files

Upload your project to the server. The structure should be:

```
/home/c/ch167436/3dPrint/
├── public_html/          # Web root (nginx document root)
│   ├── index.php         # Main router (NEW)
│   ├── index.html        # Homepage content
│   ├── about.html
│   ├── contact.html
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── 404.html
├── api/
│   ├── index.php
│   └── routes.php
├── admin/
│   ├── index.php
│   └── routes.php
├── src/
├── vendor/
├── bootstrap.php
└── .env
```

**Critical**: Make sure `/public_html/index.php` is uploaded and is executable.

### Step 2: Set File Permissions

```bash
# Make index.php executable
chmod 644 /home/c/ch167436/3dPrint/public_html/index.php

# Ensure web server can read all files
chmod -R 644 /home/c/ch167436/3dPrint/public_html/*
chmod -R 755 /home/c/ch167436/3dPrint/public_html/assets

# Directories need to be executable
find /home/c/ch167436/3dPrint/public_html -type d -exec chmod 755 {} \;
```

### Step 3: Configure nginx (If Possible)

If you have access to nginx configuration, add this to your server block:

```nginx
server {
    listen 80;
    server_name 3dprint-omsk.ru www.3dprint-omsk.ru;
    root /home/c/ch167436/3dPrint/public_html;
    index index.php index.html;

    # Route all requests through index.php if file doesn't exist
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    # Security: Deny access to hidden files
    location ~ /\. {
        deny all;
    }
}
```

Then reload nginx:

```bash
sudo nginx -t  # Test configuration
sudo systemctl reload nginx
```

### Step 4: Alternative Configuration (Shared Hosting)

If you **cannot** access nginx configuration (common on shared hosting), the router will still work if:

1. nginx is configured to use `index.php` as a default index file (most shared hosting does this)
2. nginx routes requests through PHP-FPM for .php files

Most shared hosting providers with nginx+PHP already have this configuration, so the router will work automatically.

### Step 5: Test the Deployment

Visit your site and test different URLs:

#### Test Homepage
```bash
curl http://3dprint-omsk.ru/
```
Expected: HTML content from index.html

#### Test Static Files
```bash
curl -I http://3dprint-omsk.ru/assets/css/main.css
```
Expected: 
- Status: 200
- Content-Type: text/css

#### Test API
```bash
curl http://3dprint-omsk.ru/api/services
```
Expected: JSON response with services

#### Test SEO Files
```bash
curl http://3dprint-omsk.ru/sitemap.xml
curl http://3dprint-omsk.ru/robots.txt
```
Expected: Dynamically generated content

#### Test 404
```bash
curl -I http://3dprint-omsk.ru/nonexistent
```
Expected: Status 404 with 404.html content

### Step 6: Verify Routing

Check that all routing works correctly:

- [ ] Homepage loads (`/`)
- [ ] Static pages load (`/about.html`, `/contact.html`)
- [ ] CSS files load (`/assets/css/main.css`)
- [ ] JavaScript files load (`/assets/js/app.js`)
- [ ] API endpoints work (`/api/services`)
- [ ] Admin login works (`/admin/login`)
- [ ] Sitemap loads (`/sitemap.xml`)
- [ ] Robots.txt loads (`/robots.txt`)
- [ ] 404 page shows for non-existent files

## Troubleshooting

### Issue: Blank Page

**Symptoms**: All pages show blank/white screen

**Solutions**:
1. Check PHP error logs:
   ```bash
   tail -f /var/log/php8.2-fpm.log
   # or
   tail -f /home/c/ch167436/3dPrint/logs/error.log
   ```

2. Enable error reporting temporarily in index.php:
   ```php
   // Add at the top of public_html/index.php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

3. Verify PHP-FPM is running:
   ```bash
   sudo systemctl status php8.2-fpm
   ```

4. Check file permissions:
   ```bash
   ls -la /home/c/ch167436/3dPrint/public_html/index.php
   ```

### Issue: 404 for All Pages

**Symptoms**: All pages return 404

**Solutions**:
1. Verify index.php is being executed:
   - Add `echo "Router loaded";` at the top of index.php
   - Visit site and check if message appears

2. Check nginx configuration:
   ```bash
   sudo nginx -t
   grep -r "3dprint-omsk.ru" /etc/nginx/
   ```

3. Verify document root is correct:
   - Check nginx server block `root` directive
   - Should point to `/home/c/ch167436/3dPrint/public_html`

4. Check nginx error log:
   ```bash
   sudo tail -f /var/log/nginx/error.log
   ```

### Issue: Static Files Not Loading

**Symptoms**: CSS/JS/images don't load

**Solutions**:
1. Check Content-Type headers:
   ```bash
   curl -I http://3dprint-omsk.ru/assets/css/main.css
   ```

2. Verify files exist:
   ```bash
   ls -la /home/c/ch167436/3dPrint/public_html/assets/css/
   ```

3. Check file permissions:
   ```bash
   chmod 644 /home/c/ch167436/3dPrint/public_html/assets/css/*
   ```

4. Clear browser cache (Ctrl+Shift+R)

### Issue: API Not Working

**Symptoms**: API returns HTML or 404 instead of JSON

**Solutions**:
1. Verify API index.php exists:
   ```bash
   ls -la /home/c/ch167436/3dPrint/api/index.php
   ```

2. Check bootstrap.php is accessible:
   ```bash
   ls -la /home/c/ch167436/3dPrint/bootstrap.php
   ```

3. Review API logs:
   ```bash
   tail -f /home/c/ch167436/3dPrint/logs/api.log
   ```

4. Test API directly:
   ```bash
   cd /home/c/ch167436/3dPrint
   php api/index.php
   ```

### Issue: Admin Panel Not Accessible

**Symptoms**: /admin/login returns 404

**Solutions**:
1. Verify admin index.php exists:
   ```bash
   ls -la /home/c/ch167436/3dPrint/admin/index.php
   ```

2. Check session configuration in php.ini:
   ```bash
   php -i | grep session
   ```

3. Ensure session directory is writable:
   ```bash
   chmod 1777 /var/lib/php/sessions
   ```

### Issue: Sitemap/Robots Not Found

**Symptoms**: /sitemap.xml or /robots.txt return 404

**Solutions**:
1. Verify routing logic in index.php:
   ```php
   // These should be present in index.php
   if ($requestUri === '/sitemap.xml') {
       $_SERVER['REQUEST_URI'] = '/api/sitemap.xml';
       // ...
   }
   ```

2. Check API routes include sitemap and robots:
   ```bash
   grep -r "sitemap\|robots" /home/c/ch167436/3dPrint/api/routes.php
   ```

3. Test API directly:
   ```bash
   curl http://3dprint-omsk.ru/api/sitemap.xml
   curl http://3dprint-omsk.ru/api/robots.txt
   ```

## Performance Optimization

### Enable OPcache

Add to php.ini or create /etc/php/8.2/fpm/conf.d/opcache.ini:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

### Enable Gzip Compression

Add to nginx configuration:

```nginx
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
```

### Set Browser Caching

The router already sets optimal cache headers:
- Static assets (CSS, JS, images, fonts): 1 year cache
- HTML files: no cache

Verify with:
```bash
curl -I http://3dprint-omsk.ru/assets/css/main.css | grep -i cache
```

### Monitor Performance

Use PHP-FPM status page:

```nginx
location /fpm-status {
    access_log off;
    allow 127.0.0.1;
    deny all;
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

Check status:
```bash
curl http://localhost/fpm-status
```

## Security Checklist

- [ ] Remove or secure .env file (should be outside public_html)
- [ ] Ensure /vendor, /src, /logs are outside public_html
- [ ] File permissions: 644 for files, 755 for directories
- [ ] PHP files cannot be downloaded as text
- [ ] Directory listing is disabled
- [ ] SSL certificate is installed and active
- [ ] HTTPS redirect is enabled
- [ ] Security headers are set (X-Frame-Options, CSP, etc.)
- [ ] Rate limiting is enabled for API endpoints
- [ ] Admin panel requires authentication
- [ ] Database credentials are secure

## Monitoring

### Check Error Logs

```bash
# PHP errors
tail -f /var/log/php8.2-fpm.log

# nginx errors
tail -f /var/log/nginx/error.log

# Application logs
tail -f /home/c/ch167436/3dPrint/logs/error.log
tail -f /home/c/ch167436/3dPrint/logs/api.log
```

### Monitor Access Logs

```bash
# nginx access logs
tail -f /var/log/nginx/access.log

# Filter for errors
tail -f /var/log/nginx/access.log | grep " 404 \| 500 \| 502 "
```

### Monitor Server Resources

```bash
# Check disk space
df -h

# Check memory usage
free -m

# Check PHP-FPM processes
ps aux | grep php-fpm

# Check nginx processes
ps aux | grep nginx
```

## Rollback Plan

If deployment fails:

1. **Backup**: Keep a copy of the old configuration
   ```bash
   cp public_html/index.php public_html/index.php.backup
   ```

2. **Restore**: Replace index.php with index.html as default
   ```bash
   mv public_html/index.html public_html/index-old.html
   mv public_html/index.php public_html/index.php.new
   mv public_html/index-old.html public_html/index.php
   ```

3. **Reconfigure nginx**: Change index directive
   ```nginx
   index index.html index.php;
   ```

## Success Criteria

Deployment is successful when:

1. ✅ Homepage loads correctly
2. ✅ All static pages accessible
3. ✅ CSS and JavaScript load properly
4. ✅ Images display correctly
5. ✅ API endpoints respond with JSON
6. ✅ Admin login page accessible
7. ✅ Sitemap.xml accessible
8. ✅ Robots.txt accessible
9. ✅ 404 page shows for non-existent URLs
10. ✅ No PHP errors in logs

## Support

If you encounter issues not covered here:

1. Check application logs in `/logs/`
2. Review nginx error logs
3. Verify PHP-FPM is running
4. Test individual components (API, admin) directly
5. Contact hosting provider if nginx configuration is needed

## Related Documentation

- `/NGINX_ROUTER_README.md` - Complete router documentation
- `/test-router-logic.md` - Test cases and validation
- `/DEPLOYMENT.md` - General deployment guide
- `/DEPLOYMENT_QUICKSTART.md` - Quick deployment guide
- `/SSL_SETUP.md` - SSL certificate installation
