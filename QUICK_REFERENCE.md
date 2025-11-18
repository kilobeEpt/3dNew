# QUICK REFERENCE - Site Deployment

## 🚨 PROBLEM: 403 Forbidden Error

### Quick Diagnostic
```bash
bash scripts/diagnose-403.sh
```

### Quick Fix (Most Common)
```bash
# Fix permissions
find public_html -type d -exec chmod 755 {} \;
find public_html -type f -exec chmod 644 {} \;

# Test again
curl -I https://3dprint-omsk.ru/
```

---

## ⚡ QUICK DEPLOYMENT

### One-Command Deployment
```bash
bash scripts/setup.sh
```

### Manual Step-by-Step (5 minutes)
```bash
# 1. Fix permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod +x scripts/*.sh
chmod 600 .env

# 2. Create directories
mkdir -p logs uploads backups/{database,files} temp storage

# 3. Install dependencies
composer install --no-dev --optimize-autoloader

# 4. Configure .env
cp .env.example .env
nano .env
# Set: DB_HOST, DB_NAME, DB_USER, DB_PASS, JWT_SECRET

# 5. Setup database
php database/migrate.php
php database/seed.php

# 6. Test
curl -I https://3dprint-omsk.ru/
```

---

## 🔍 QUICK TESTS

### Test HTTP Status
```bash
curl -I https://3dprint-omsk.ru/
# Expected: HTTP/2 200 OK
```

### Test Homepage
```bash
curl https://3dprint-omsk.ru/ | head -20
# Expected: HTML content
```

### Test API
```bash
curl https://3dprint-omsk.ru/api/services
# Expected: JSON response
```

### Test Admin
```bash
curl -I https://3dprint-omsk.ru/admin/
# Expected: 200 or 302
```

---

## 📚 DOCUMENTATION INDEX

| File | Purpose | When to Use |
|------|---------|-------------|
| **FINAL_DEPLOYMENT_GUIDE.md** | Complete deployment guide | Full deployment |
| **DEPLOYMENT_FIX_403.md** | Fix 403 errors | Getting 403 error |
| **DEPLOYMENT_CHECKLIST.md** | Verification checklist | Before go-live |
| **FIXES_SUMMARY.md** | What was fixed | Understanding changes |
| **SETUP_README.md** | Auto-setup script guide | Quick deployment |
| **NGINX_ROUTER_DEPLOYMENT.md** | nginx configuration | Server setup |
| **scripts/diagnose-403.sh** | Diagnostic tool | Troubleshooting 403 |

---

## 🛠️ COMMON FIXES

### Fix: 403 Forbidden
```bash
bash scripts/diagnose-403.sh
# Follow the output instructions
```

### Fix: Composer Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### Fix: Database Connection
```bash
# Edit .env
nano .env
# Set correct DB_HOST, DB_NAME, DB_USER, DB_PASS
```

### Fix: Missing Directories
```bash
mkdir -p logs uploads backups/{database,files} temp storage
chmod 755 logs uploads backups temp storage
```

### Fix: JWT Secret
```bash
# Generate secure secret
php -r "echo bin2hex(random_bytes(64)) . PHP_EOL;"
# Copy to .env: JWT_SECRET=generated_secret
```

---

## 🎯 SUCCESS CRITERIA

- [ ] `curl -I https://3dprint-omsk.ru/` returns HTTP 200
- [ ] Site loads in browser
- [ ] All pages accessible
- [ ] Calculator works
- [ ] Forms submit
- [ ] Admin panel accessible
- [ ] No errors in logs

---

## 📞 NEED HELP?

1. **403 Error**: Read `DEPLOYMENT_FIX_403.md`
2. **Full Deployment**: Read `FINAL_DEPLOYMENT_GUIDE.md`
3. **Verification**: Use `DEPLOYMENT_CHECKLIST.md`
4. **Quick Diagnostic**: Run `bash scripts/diagnose-403.sh`

---

## 🐛 FIXED ISSUES

✅ **PHP 8.2 count() error** - Fixed in `scripts/verify-deployment.php:213`
✅ **403 Forbidden** - Documented in `DEPLOYMENT_FIX_403.md`
✅ **Deployment automation** - Created comprehensive guides
✅ **Diagnostic tool** - Created `scripts/diagnose-403.sh`

---

## 📋 DEFAULT CREDENTIALS

**After running setup.sh:**
- Admin: `admin` / `admin123`
- Editor: `editor` / `editor123`

**⚠️ Change these immediately after first login!**

---

## 🚀 DEPLOYMENT COMMAND SUMMARY

```bash
# Quick deployment
bash scripts/setup.sh

# Diagnostic
bash scripts/diagnose-403.sh

# Verification
php scripts/verify-deployment.php

# Test site
curl -I https://3dprint-omsk.ru/

# Check logs
tail -50 logs/app.log
```

---

**Need more details? See FINAL_DEPLOYMENT_GUIDE.md**
