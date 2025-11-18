# nginx 403 Forbidden - Quick Fix Guide

⏱️ **5-Minute Quick Fix** for nginx 403 Forbidden errors

> 📚 **For detailed guide, see:** [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md)

---

## ⚡ Quick Diagnostic (2 minutes)

### Step 1: Create Test File

```bash
cat > /home/c/ch167436/3dPrint/public_html/test.php << 'EOF'
<?php phpinfo(); ?>
EOF

chmod 644 /home/c/ch167436/3dPrint/public_html/test.php
```

**Visit:** `http://yourdomain.com/test.php`

| Result | Meaning | Action |
|--------|---------|--------|
| ✅ PHP info shows | PHP works | Go to Step 3 |
| ❌ 403 Forbidden | nginx/permissions issue | Go to Step 2 |
| 📥 File downloads | PHP-FPM not configured | Contact support (see below) |

### Step 2: Fix Permissions

```bash
cd /home/c/ch167436/3dPrint

# Fix all permissions
find public_html -type d -exec chmod 755 {} \;
find public_html -type f -exec chmod 644 {} \;

# Test again
curl -I http://yourdomain.com/test.php
```

**Still 403?** Go to Step 3.

### Step 3: Check nginx Document Root

```bash
# Find nginx config
sudo grep -r "yourdomain.com" /etc/nginx/

# Look for "root" directive - should be:
# root /home/c/ch167436/3dPrint/public_html;
```

**Wrong root or can't access config?** Contact hosting support (see template below).

---

## 📧 Email Template for Hosting Support

**Copy and paste this:**

```
Subject: nginx 403 Forbidden - Need Document Root Configuration

Hello,

I'm experiencing a 403 Forbidden error on my website yourdomain.com.

Issue: All pages return 403, even test.php with correct permissions (755/644).

Needed Configuration:
1. Set nginx root to: /home/c/ch167436/3dPrint/public_html
2. Add: index index.php index.html;
3. Add: try_files $uri $uri/ /index.php$is_args$args;
4. Configure PHP-FPM for .php files

nginx config needed:
server {
    server_name yourdomain.com www.yourdomain.com;
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

This is urgent - entire site is down.

Thanks!
```

---

## 🔧 Common Fixes

### Fix 1: Wrong Document Root

**Problem:** nginx is looking in wrong directory

**Solution:** Contact hosting support to set root to `/home/c/ch167436/3dPrint/public_html`

### Fix 2: Missing index.php in nginx

**Problem:** nginx doesn't know to use index.php

**Solution:** Add to nginx config: `index index.php index.html;`

### Fix 3: Missing PHP-FPM Configuration

**Problem:** nginx doesn't execute PHP files

**Solution:** Contact support to configure PHP-FPM (see email template above)

### Fix 4: File Permissions

**Problem:** nginx user can't read files

**Solution:** Run permission fix (Step 2 above)

---

## ✅ Verification Tests

After fix is applied, test these:

```bash
# Test 1: PHP execution
curl http://yourdomain.com/test.php
# Expected: PHP info HTML

# Test 2: Homepage
curl http://yourdomain.com/
# Expected: HTML content

# Test 3: API
curl http://yourdomain.com/api/health
# Expected: JSON {"status":"ok"}

# Test 4: Static files
curl -I http://yourdomain.com/assets/css/main.css
# Expected: 200 OK, Content-Type: text/css
```

All should return **200 OK** (not 403).

---

## 🆘 Still Not Working?

1. **Run full diagnostic:** `bash scripts/verify-server.sh`
2. **Read complete guide:** [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md)
3. **Check troubleshooting:** [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
4. **View nginx error log:** `sudo tail -f /var/log/nginx/error.log`

---

## 📚 Full Documentation

- **[DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md)** - Complete nginx deployment guide
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - All common issues
- **[DEPLOYMENT_GUIDES_README.md](DEPLOYMENT_GUIDES_README.md)** - Documentation index

---

**Last Updated:** 2024-11-18
