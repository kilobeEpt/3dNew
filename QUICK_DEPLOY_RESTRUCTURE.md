# 🚀 Quick Deploy - Restructured Project

## What Changed?

**Before:** Files were in `/public_html/` subdirectory  
**After:** Files are in project root  
**Reason:** Fix nginx 403 Forbidden errors

## 5-Minute Deployment

### Step 1: Upload Files 📤

Upload the entire project to: `/home/c/ch167436/3dPrint/`

**What to upload:**
- All HTML files (should be in root, not public_html/)
- assets/ directory
- api/ directory
- admin/ directory
- src/ directory
- database/ directory
- All PHP files (.php, bootstrap.php)
- .htaccess
- .env
- composer.json

### Step 2: Set Permissions 🔒

```bash
cd /home/c/ch167436/3dPrint

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Protect .env
chmod 600 .env

# Make scripts executable
chmod +x scripts/*.sh
chmod +x verify-restructure.sh
```

### Step 3: Verify Structure ✅

```bash
bash verify-restructure.sh
```

**Expected output:**
```
✅ All checks passed: 31/31
```

### Step 4: Test in Browser 🌐

Open: `https://3dprint-omsk.ru/`

**Expected:**
- ✅ Homepage loads (no 403 error)
- ✅ All pages accessible
- ✅ Images and CSS load
- ✅ API works
- ✅ Admin panel accessible

### Step 5: Verify Deployment 📋

```bash
bash scripts/deploy-restructure.sh
```

This will check:
- File structure
- Permissions
- Routing configuration
- File accessibility

## Quick Tests

### Test 1: Homepage
```bash
curl -I https://3dprint-omsk.ru/
```
**Expected:** `HTTP/1.1 200 OK`

### Test 2: Static File
```bash
curl -I https://3dprint-omsk.ru/assets/css/style.css
```
**Expected:** `HTTP/1.1 200 OK`

### Test 3: API
```bash
curl https://3dprint-omsk.ru/api/services
```
**Expected:** JSON response

### Test 4: About Page
```bash
curl -I https://3dprint-omsk.ru/about.html
```
**Expected:** `HTTP/1.1 200 OK`

## Common Issues

### Issue: Still getting 403

**Solution:**
1. Check file locations:
   ```bash
   ls /home/c/ch167436/3dPrint/*.html
   # Should show: index.html, about.html, etc.
   ```

2. Check nginx web root in config:
   ```nginx
   root /home/c/ch167436/3dPrint;  # CORRECT
   # NOT: root /home/c/ch167436/3dPrint/public_html;
   ```

3. Check permissions:
   ```bash
   ls -la /home/c/ch167436/3dPrint
   # Directories should be: drwxr-xr-x (755)
   # Files should be: -rw-r--r-- (644)
   ```

### Issue: Files not found

**Solution:**
Verify you uploaded to the correct location:
```bash
ls /home/c/ch167436/3dPrint/
# Should show: index.html, index.php, assets/, api/, admin/, etc.
# NOT just: public_html/
```

### Issue: API not working

**Solution:**
1. Check .env file exists:
   ```bash
   ls -la /home/c/ch167436/3dPrint/.env
   ```

2. Check database connection in .env

3. Test API directly:
   ```bash
   curl https://3dprint-omsk.ru/api/services
   ```

## File Locations Reference

**✅ CORRECT (New Structure):**
```
/home/c/ch167436/3dPrint/index.html         ← Homepage
/home/c/ch167436/3dPrint/index.php          ← Router
/home/c/ch167436/3dPrint/assets/            ← Static files
/home/c/ch167436/3dPrint/api/               ← API
```

**❌ WRONG (Old Structure):**
```
/home/c/ch167436/3dPrint/public_html/index.html    ← Don't put files here
/home/c/ch167436/3dPrint/public_html/assets/       ← Don't put files here
```

## Checklist

Before going live:

- [ ] All files uploaded to `/home/c/ch167436/3dPrint/`
- [ ] HTML files in project root (not public_html/)
- [ ] assets/ directory in project root
- [ ] Permissions set correctly (755/644/.env=600)
- [ ] verify-restructure.sh passes all checks
- [ ] Homepage loads without 403
- [ ] Static files (CSS/JS/images) load
- [ ] API endpoints return data
- [ ] Admin panel accessible
- [ ] No console errors in browser
- [ ] All navigation links work

## Success Indicators

✅ You'll know it worked when:
1. No 403 Forbidden errors anywhere
2. Homepage loads immediately
3. All CSS and images display
4. API returns JSON data
5. Admin panel login page loads
6. Browser console has no errors

## Need Help?

See full documentation:
- `RESTRUCTURE_GUIDE.md` - Complete guide
- `RESTRUCTURE_COMPLETION.md` - What changed
- `nginx.conf.example` - nginx config reference

Run verification:
```bash
bash verify-restructure.sh
bash scripts/deploy-restructure.sh
```

---

**Deployment Time:** 5-10 minutes  
**Difficulty:** Easy  
**Risk:** Low (easy rollback if needed)
