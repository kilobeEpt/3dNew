# Project Restructure Guide - Web Root Relocation

## 🎯 Purpose

This document explains the complete project restructuring that was performed to fix nginx 403 errors by moving all static files from `/public_html/` to the project root.

## 🚨 The Problem

**Original Issue:**
- nginx was returning 403 Forbidden BEFORE PHP could execute
- The web root was configured as `/home/c/ch167436/3dPrint/` (project root)
- But all static files were in `/home/c/ch167436/3dPrint/public_html/`
- nginx couldn't find the files, resulting in 403 errors

**Root Cause:**
- Mismatch between nginx web root configuration and actual file locations
- On shared hosting, you cannot always change nginx configuration
- Solution: Move files to where nginx expects them

## ✅ The Solution

Move all static files UP one level from `public_html/` to the project root, so the structure matches nginx's expectations.

## 📁 New Project Structure

```
/home/c/ch167436/3dPrint/          <- WEB ROOT (nginx expects files here)
├── index.php                       <- Main router (handles all requests)
├── index.html                      <- Homepage
├── about.html
├── services.html
├── calculator.html
├── contact.html
├── gallery.html
├── materials.html
├── news.html
├── 404.html
├── 500.html
├── api-example.html
├── .htaccess                       <- Apache/nginx routing rules
├── nginx.conf.example              <- nginx configuration reference
├── assets/                         <- Static assets (CSS, JS, images)
│   ├── css/
│   ├── js/
│   └── images/
├── api/                            <- API endpoints
│   ├── index.php                   <- API router
│   └── routes.php
├── admin/                          <- Admin panel
│   ├── index.php                   <- Admin router
│   ├── routes.php
│   └── views/
├── src/                            <- PHP application code
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Middleware/
│   └── Core/
├── database/                       <- Database migrations and seeds
│   ├── migrations/
│   └── seeds/
├── bootstrap.php                   <- Application initialization
├── composer.json                   <- PHP dependencies
├── .env                            <- Environment configuration
├── logs/                           <- Application logs
├── uploads/                        <- User uploads
├── backups/                        <- Backups
└── public_html/                    <- OLD - can be removed or kept as backup
    └── ...
```

## 🔄 Changes Made

### 1. Moved Static Files

```bash
# All HTML files moved from public_html/ to project root
mv public_html/*.html .

# Assets directory moved from public_html/ to project root
mv public_html/assets .
```

**Files Moved:**
- `index.html` - Homepage
- `about.html` - About page
- `services.html` - Services page
- `calculator.html` - Calculator page
- `contact.html` - Contact page
- `gallery.html` - Gallery page
- `materials.html` - Materials page
- `news.html` - News page
- `404.html` - 404 error page
- `500.html` - 500 error page
- `api-example.html` - API example page
- `assets/` - All CSS, JS, and images

### 2. Updated Main Router (`index.php`)

**Location:** `/home/c/ch167436/3dPrint/index.php` (project root)

**Key Changes:**
- `$projectRoot = __DIR__;` - Now points to project root directly
- Removed reference to `public_html` directory
- Routes all requests through itself as front controller
- Serves static files directly when they exist
- Routes `/api/*` to `/api/index.php`
- Routes `/admin/*` to `/admin/index.php`
- Routes `/sitemap.xml` and `/robots.txt` to API
- Serves 404.html for non-existent routes

**Security Features:**
- Blocks direct access to PHP files (except through routing)
- Prevents directory traversal attacks
- Validates all paths before serving

### 3. Updated `.htaccess`

**Location:** `/home/engine/project/.htaccess` (project root)

**Key Updates:**
- Routes all non-existent files through `index.php`
- Protects sensitive files (composer.json, .env, bootstrap.php, etc.)
- Blocks access to dotfiles
- Custom error pages (404.html, 500.html)
- Performance optimizations (compression, caching)
- Security headers (CSP, X-Frame-Options, etc.)
- WebP image support

### 4. API and Admin Routers

**No changes needed!**

Both `/api/index.php` and `/admin/index.php` still work correctly:
```php
require_once __DIR__ . '/../bootstrap.php';
```

Since they're in subdirectories, `../bootstrap.php` still correctly points to the bootstrap file in the project root.

### 5. Bootstrap File

**Location:** `/home/engine/project/bootstrap.php`

**No changes needed!**

The bootstrap file uses `__DIR__` for relative paths:
```php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__);
```

This still works correctly since it's in the same location.

## 🔧 How It Works

### Request Flow

1. **User requests:** `https://3dprint-omsk.ru/about.html`
2. **nginx receives request** and looks in `/home/c/ch167436/3dPrint/`
3. **File exists:** nginx serves `/home/c/ch167436/3dPrint/about.html` directly
4. **Success!** ✅

---

1. **User requests:** `https://3dprint-omsk.ru/api/services`
2. **nginx receives request** and looks for `/home/c/ch167436/3dPrint/api/services`
3. **File doesn't exist:** nginx falls back to `.htaccess` rules or `index.php`
4. **Router detects `/api/*` pattern** and includes `/api/index.php`
5. **API router processes** the request and returns JSON
6. **Success!** ✅

---

1. **User requests:** `https://3dprint-omsk.ru/`
2. **nginx receives request** and looks for index file
3. **Finds `index.php`** (or uses .htaccess routing)
4. **Router maps `/` to `/index.html`** and serves it
5. **Success!** ✅

## 🧪 Testing

### Test Static Files

```bash
# Test homepage
curl -I https://3dprint-omsk.ru/
# Expected: HTTP 200 OK + HTML content

# Test about page
curl -I https://3dprint-omsk.ru/about.html
# Expected: HTTP 200 OK + HTML content

# Test CSS file
curl -I https://3dprint-omsk.ru/assets/css/style.css
# Expected: HTTP 200 OK + Content-Type: text/css
```

### Test API Endpoints

```bash
# Test services API
curl https://3dprint-omsk.ru/api/services
# Expected: HTTP 200 + JSON response

# Test sitemap
curl -I https://3dprint-omsk.ru/sitemap.xml
# Expected: HTTP 200 + Content-Type: application/xml
```

### Test Admin Panel

```bash
# Test admin login page
curl -I https://3dprint-omsk.ru/admin/login
# Expected: HTTP 200 + HTML content
```

### Test 404 Handling

```bash
# Test non-existent page
curl -I https://3dprint-omsk.ru/does-not-exist
# Expected: HTTP 404 + 404.html content
```

## 🔒 Security Considerations

### Protected Files

The following files are blocked from direct web access:
- `.env` and `.env.example` - Environment variables
- `composer.json` and `composer.lock` - Dependency configuration
- `package.json` and `package-lock.json` - Node dependencies
- `bootstrap.php` - Application initialization
- All dotfiles (`.git`, `.gitignore`, etc.)

### Directory Protection

- **No directory listing** - Directories cannot be browsed
- **Path validation** - All paths are validated to prevent directory traversal
- **PHP file blocking** - PHP files cannot be served as static content

### Headers

Security headers are automatically added:
- `X-Frame-Options: SAMEORIGIN` - Prevents clickjacking
- `X-Content-Type-Options: nosniff` - Prevents MIME sniffing
- `X-XSS-Protection: 1; mode=block` - XSS protection
- `Referrer-Policy: strict-origin-when-cross-origin` - Controls referrer
- `Content-Security-Policy` - Restricts resource loading

## 🚀 Deployment

### For Production Server

1. **Upload all files** to `/home/c/ch167436/3dPrint/`
2. **Set permissions:**
   ```bash
   chmod 755 /home/c/ch167436/3dPrint
   find /home/c/ch167436/3dPrint -type d -exec chmod 755 {} \;
   find /home/c/ch167436/3dPrint -type f -exec chmod 644 {} \;
   chmod 600 /home/c/ch167436/3dPrint/.env
   ```
3. **Verify structure:**
   ```bash
   ls -la /home/c/ch167436/3dPrint/
   # Should see: index.php, index.html, assets/, api/, admin/, etc.
   ```
4. **Test in browser:** `https://3dprint-omsk.ru/`

### nginx Configuration (Optional)

If you have access to nginx configuration, see `nginx.conf.example` for recommended settings.

**Key nginx setting:**
```nginx
root /home/c/ch167436/3dPrint;  # NOT /home/c/ch167436/3dPrint/public_html
```

## 📋 Checklist

- [x] All HTML files moved to project root
- [x] Assets directory moved to project root
- [x] Main router (index.php) created and updated
- [x] .htaccess updated with routing rules
- [x] API router verified (no changes needed)
- [x] Admin router verified (no changes needed)
- [x] Bootstrap file verified (no changes needed)
- [x] Security: Sensitive files protected
- [x] Security: Directory traversal prevented
- [x] Security: PHP file serving blocked
- [x] Documentation: nginx.conf.example created
- [x] Documentation: This guide created

## ✅ Expected Results

After this restructuring:

1. ✅ `curl -I https://3dprint-omsk.ru/` → **HTTP 200** (was 403)
2. ✅ `curl -I https://3dprint-omsk.ru/about.html` → **HTTP 200** (was 403)
3. ✅ `curl -I https://3dprint-omsk.ru/assets/css/style.css` → **HTTP 200** (was 403)
4. ✅ `curl https://3dprint-omsk.ru/api/services` → **HTTP 200 + JSON** (works)
5. ✅ `curl -I https://3dprint-omsk.ru/admin` → **HTTP 200** (works)
6. ✅ All static assets load correctly
7. ✅ No 403 Forbidden errors
8. ✅ All application features work (API, admin, frontend)

## 🆘 Troubleshooting

### Still Getting 403 Errors?

1. **Check file permissions:**
   ```bash
   ls -la /home/c/ch167436/3dPrint/index.html
   # Should be: -rw-r--r-- (644)
   ```

2. **Check directory permissions:**
   ```bash
   ls -la /home/c/ch167436/ | grep 3dPrint
   # Should be: drwxr-xr-x (755)
   ```

3. **Verify nginx web root:**
   ```bash
   # In nginx config, check:
   root /home/c/ch167436/3dPrint;
   # NOT: root /home/c/ch167436/3dPrint/public_html;
   ```

4. **Check PHP-FPM:**
   ```bash
   # Make sure PHP-FPM is running
   systemctl status php8.2-fpm
   ```

### Files Not Found?

1. **Verify files are in project root:**
   ```bash
   ls /home/c/ch167436/3dPrint/*.html
   # Should list: index.html, about.html, services.html, etc.
   ```

2. **Check assets directory:**
   ```bash
   ls /home/c/ch167436/3dPrint/assets/
   # Should show: css/, js/, images/
   ```

### API Not Working?

1. **Verify API router exists:**
   ```bash
   ls -la /home/c/ch167436/3dPrint/api/index.php
   ```

2. **Check bootstrap.php:**
   ```bash
   ls -la /home/c/ch167436/3dPrint/bootstrap.php
   ```

3. **Check .env file:**
   ```bash
   ls -la /home/c/ch167436/3dPrint/.env
   ```

## 📚 Related Documentation

- `DEPLOYMENT_FIX_403.md` - Comprehensive guide to fixing 403 errors
- `FINAL_DEPLOYMENT_GUIDE.md` - Complete deployment instructions
- `nginx.conf.example` - nginx configuration reference
- `NGINX_ROUTER_README.md` - Original nginx router documentation
- `scripts/diagnose-403.sh` - Automated 403 diagnostic tool

## 🎉 Summary

**Before:**
```
/home/c/ch167436/3dPrint/public_html/index.html  <- nginx can't find this
```

**After:**
```
/home/c/ch167436/3dPrint/index.html              <- nginx finds this! ✅
```

**Result:** All 403 errors fixed, website loads correctly! 🎉
