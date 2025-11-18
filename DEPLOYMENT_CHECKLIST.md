# DEPLOYMENT CHECKLIST - 3D Print Platform

Complete checklist for deploying the site and ensuring everything works correctly.

---

## PRE-DEPLOYMENT CHECKS

### Server Requirements
- [ ] PHP 8.2 or higher installed
- [ ] MySQL/MariaDB 5.7+ installed and running
- [ ] nginx web server configured
- [ ] Composer 2.x available
- [ ] SSH access to server
- [ ] Domain DNS configured (A record points to server IP)

### PHP Extensions
- [ ] pdo_mysql
- [ ] mbstring
- [ ] openssl
- [ ] json
- [ ] fileinfo
- [ ] curl
- [ ] zip

**Verify:** `php -m | grep -E 'pdo|mbstring|openssl|json|fileinfo'`

---

## DEPLOYMENT STEPS

### 1. Upload Files
- [ ] Files uploaded to server (via FTP, SFTP, or git)
- [ ] Files in correct directory (e.g., `/home/c/ch167436/3dPrint`)
- [ ] `.git` directory excluded (or kept for updates)
- [ ] All directories present: `api/`, `admin/`, `public_html/`, `src/`, etc.

### 2. Fix 403 Forbidden Error
- [ ] Run diagnostic: `bash scripts/diagnose-403.sh`
- [ ] Identified nginx web root path
- [ ] Files moved to correct location OR nginx config updated
- [ ] `index.php` in nginx directory index list
- [ ] PHP-FPM configured and running
- [ ] File permissions set correctly (755 dirs, 644 files)
- [ ] Test: `curl -I https://3dprint-omsk.ru/` returns HTTP 200

### 3. File Permissions
- [ ] Directories set to 755: `find . -type d -exec chmod 755 {} \;`
- [ ] Files set to 644: `find . -type f -exec chmod 644 {} \;`
- [ ] Scripts executable: `chmod +x scripts/*.sh`
- [ ] `.env` secured: `chmod 600 .env`
- [ ] Owner set correctly (www-data or user): `chown -R user:user .`

### 4. Create Required Directories
- [ ] `logs/` created and writable (755)
- [ ] `uploads/` created and writable (755)
- [ ] `uploads/models/` created (755)
- [ ] `uploads/gallery/` created (755)
- [ ] `backups/database/` created (755)
- [ ] `backups/files/` created (755)
- [ ] `temp/` created (755)
- [ ] `storage/` created (755)

### 5. Install Dependencies
- [ ] Composer available: `composer --version` shows 2.x
- [ ] Dependencies installed: `composer install --no-dev --optimize-autoloader`
- [ ] `vendor/` directory exists
- [ ] Packages verified: `php test_packages.php` succeeds

### 6. Configure Environment
- [ ] `.env` file created from `.env.example`
- [ ] `APP_ENV=production` set
- [ ] `APP_DEBUG=false` set
- [ ] `APP_URL` set to correct domain
- [ ] `DB_HOST` configured
- [ ] `DB_NAME` configured
- [ ] `DB_USER` configured
- [ ] `DB_PASS` configured
- [ ] `JWT_SECRET` generated (64+ characters)
- [ ] Email settings configured (MAIL_*)
- [ ] `.env` permissions set to 600

### 7. Database Setup
- [ ] Database created in MySQL
- [ ] Database user has permissions (SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER)
- [ ] Connection tested: `mysql -h localhost -u user -p database`
- [ ] Migrations run: `php database/migrate.php`
- [ ] Seed data loaded: `php database/seed.php`
- [ ] Tables verified: `SHOW TABLES;` returns 17+ tables

### 8. nginx Configuration
- [ ] nginx config file created/updated
- [ ] `root` directive points to correct path
- [ ] `index index.php index.html;` directive present
- [ ] PHP-FPM location block configured
- [ ] Security headers configured
- [ ] Configuration tested: `sudo nginx -t`
- [ ] nginx reloaded: `sudo systemctl reload nginx`

### 9. Run Auto-Setup
- [ ] Setup script executed: `bash scripts/setup.sh`
- [ ] Script completed without errors
- [ ] Default admin users created
- [ ] Setup log reviewed: `tail -50 logs/setup.log`

### 10. Verification
- [ ] Verification script run: `php scripts/verify-deployment.php`
- [ ] All checks passed (✓)
- [ ] No errors in output

---

## POST-DEPLOYMENT TESTING

### HTTP/HTTPS Testing
- [ ] `curl -I https://3dprint-omsk.ru/` returns HTTP 200
- [ ] `curl https://3dprint-omsk.ru/` returns HTML content
- [ ] Browser loads homepage without errors
- [ ] No 403 Forbidden errors
- [ ] No 404 Not Found errors
- [ ] No 500 Internal Server errors

### Frontend Testing
- [ ] Homepage loads (`/`)
- [ ] About page loads (`/about.html`)
- [ ] Services page loads (`/services.html`)
- [ ] Materials page loads (`/materials.html`)
- [ ] Gallery page loads (`/gallery.html`)
- [ ] News page loads (`/news.html`)
- [ ] Contact page loads (`/contact.html`)
- [ ] Calculator page loads (`/calculator.html`)
- [ ] All images load
- [ ] All CSS loads
- [ ] All JavaScript loads
- [ ] No console errors in browser DevTools

### Calculator Testing
- [ ] Calculator form displays
- [ ] Material dropdown populates
- [ ] Quality dropdown populates
- [ ] Dimensions input works
- [ ] Infill slider works
- [ ] Price calculates in real-time
- [ ] File upload works (STL, OBJ, 3MF)
- [ ] Form submission works
- [ ] Success message displays
- [ ] Form data saved to database

### API Testing
- [ ] `/api/services` returns JSON (200)
- [ ] `/api/materials` returns JSON (200)
- [ ] `/api/pricing-rules` returns JSON (200)
- [ ] `/api/gallery` returns JSON (200)
- [ ] `/api/news` returns JSON (200)
- [ ] `/api/settings` returns JSON (200)
- [ ] `/api/sitemap.xml` returns XML (200)
- [ ] `/api/robots.txt` returns text (200)
- [ ] CORS headers present
- [ ] CSRF token endpoint works: `/api/csrf-token`

### Admin Panel Testing
- [ ] Admin panel loads: `https://3dprint-omsk.ru/admin/`
- [ ] Login page displays
- [ ] Can login with default credentials: `admin` / `admin123`
- [ ] Dashboard loads after login
- [ ] All menu items accessible
- [ ] Services management works (list, create, edit, delete)
- [ ] Materials management works
- [ ] Pricing rules management works
- [ ] Gallery management works
- [ ] News management works
- [ ] Customer requests visible
- [ ] Cost estimates visible
- [ ] Analytics dashboard works
- [ ] Audit logs visible
- [ ] Can logout successfully

### Security Testing
- [ ] Changed default admin password
- [ ] Changed editor password
- [ ] `.env` file not accessible via browser
- [ ] `/vendor/` not accessible via browser
- [ ] `/src/` not accessible via browser
- [ ] Directory listing disabled
- [ ] HTTPS redirects working
- [ ] Security headers present (check with browser DevTools)
- [ ] CSRF protection working
- [ ] Authentication required for admin endpoints

### Email Testing
- [ ] Contact form sends email
- [ ] Calculator submission sends email
- [ ] Test email via admin panel or script
- [ ] Email received correctly
- [ ] Email formatting correct

### File Upload Testing
- [ ] Can upload 3D model files (STL, OBJ, 3MF)
- [ ] Files saved to `uploads/models/`
- [ ] File size limit enforced (5MB)
- [ ] File type validation works
- [ ] Gallery image upload works (admin panel)
- [ ] Images saved to `uploads/gallery/`

---

## MAINTENANCE SETUP

### Cron Jobs
- [ ] Crontab edited: `crontab -e`
- [ ] Sitemap generation scheduled (2 AM daily)
- [ ] Log rotation scheduled (3 AM daily)
- [ ] Database backup scheduled (4 AM daily)
- [ ] File backup scheduled (5 AM Sunday)
- [ ] Temp cleanup scheduled (6 AM daily)
- [ ] Error monitoring scheduled (hourly)
- [ ] Test cron: `run-parts --test /etc/cron.daily`

### Backups
- [ ] Backup directories exist
- [ ] Database backup script tested: `bash scripts/backup-database.sh`
- [ ] File backup script tested: `bash scripts/backup-files.sh`
- [ ] Backups created successfully
- [ ] Backup files readable
- [ ] Off-site backup configured (optional)

### Monitoring
- [ ] Error monitoring script tested: `php scripts/check-errors.php`
- [ ] Email alerts configured
- [ ] Log files accessible
- [ ] Disk space sufficient (check: `df -h`)

### SSL Certificate
- [ ] SSL certificate installed
- [ ] Certificate valid and not expired
- [ ] HTTPS works: `https://3dprint-omsk.ru/`
- [ ] HTTP redirects to HTTPS
- [ ] HSTS header enabled (after testing)
- [ ] Certificate auto-renewal configured (Let's Encrypt)

---

## PERFORMANCE CHECKS

### Server Performance
- [ ] Page load time < 2 seconds
- [ ] API response time < 500ms
- [ ] Images optimized (WebP support if possible)
- [ ] CSS minified
- [ ] JavaScript minified
- [ ] Gzip compression enabled
- [ ] Browser caching configured

### Database Performance
- [ ] Indexes created on foreign keys
- [ ] Slow query log reviewed
- [ ] Database size reasonable
- [ ] Connection pooling configured (if applicable)

---

## SEO & ANALYTICS

### SEO
- [ ] Sitemap accessible: `/sitemap.xml`
- [ ] Robots.txt accessible: `/robots.txt`
- [ ] Meta tags present on all pages
- [ ] Open Graph tags configured
- [ ] Twitter Card tags configured
- [ ] Structured data (Schema.org) present
- [ ] Canonical URLs set
- [ ] 404 page configured

### Analytics
- [ ] Analytics events table created
- [ ] Analytics JavaScript loaded
- [ ] Events tracked (calculator views, submissions, etc.)
- [ ] Admin analytics dashboard works

---

## LOG CHECKS

### Application Logs
- [ ] `logs/app.log` - no critical errors
- [ ] `logs/error.log` - no PHP errors
- [ ] `logs/setup.log` - setup completed successfully

### Server Logs
- [ ] `/var/log/nginx/access.log` - requests logging
- [ ] `/var/log/nginx/error.log` - no errors
- [ ] `/var/log/php8.2-fpm.log` - no errors

---

## DOCUMENTATION

### User Documentation
- [ ] Admin user guide created/updated
- [ ] README updated with deployment info
- [ ] API documentation accessible

### Technical Documentation
- [ ] DEPLOYMENT.md reviewed
- [ ] NGINX_ROUTER_README.md reviewed
- [ ] ADMIN_API.md reviewed
- [ ] Database schema documented

---

## SECURITY HARDENING

### File Security
- [ ] `.env` not in version control (in `.gitignore`)
- [ ] Sensitive files blocked via `.htaccess` or nginx
- [ ] Upload directories secured (no PHP execution)
- [ ] Composer dev dependencies not installed in production

### Application Security
- [ ] SQL injection protection (prepared statements)
- [ ] XSS protection (input sanitization)
- [ ] CSRF protection enabled
- [ ] Rate limiting configured
- [ ] Input validation on all forms
- [ ] File upload validation

### Server Security
- [ ] Firewall configured (ports 80, 443, 22 only)
- [ ] SSH key authentication enabled
- [ ] Fail2ban configured (optional)
- [ ] Server updates applied
- [ ] PHP security settings configured (`disable_functions`, etc.)

---

## FINAL VERIFICATION

### Automated Tests
- [ ] `php scripts/verify-deployment.php` - all checks pass
- [ ] `bash scripts/diagnose-403.sh` - all checks pass
- [ ] `php test_packages.php` - all packages loaded

### Manual Tests
- [ ] Site loads in Chrome
- [ ] Site loads in Firefox
- [ ] Site loads in Safari
- [ ] Site loads in Edge
- [ ] Mobile responsive (test on mobile device)
- [ ] All forms submit correctly
- [ ] All links work
- [ ] No broken images
- [ ] No JavaScript errors

### Production Readiness
- [ ] `APP_DEBUG=false` in `.env`
- [ ] Default passwords changed
- [ ] Admin email configured
- [ ] Backup strategy in place
- [ ] Monitoring configured
- [ ] SSL certificate valid
- [ ] Domain propagated (DNS)

---

## GO-LIVE CHECKLIST

### Before Go-Live
- [ ] All above checklists completed
- [ ] Stakeholders notified
- [ ] Maintenance window scheduled
- [ ] Rollback plan prepared

### Go-Live
- [ ] DNS switched to production server
- [ ] Site accessible via domain
- [ ] All functionality works
- [ ] No errors in logs
- [ ] Monitoring active

### After Go-Live
- [ ] Monitor logs for 24 hours
- [ ] Test all critical features
- [ ] Verify backups running
- [ ] Respond to any issues
- [ ] Document any issues encountered

---

## ISSUES RESOLVED

### PHP 8.2 Compatibility
- [x] Fixed `count()` error in `scripts/verify-deployment.php:213`
- [x] Changed `count($tables)` to `count($tablesResult->fetchAll())`
- [x] No more PDOStatement count errors

### nginx 403 Forbidden
- [x] Created `DEPLOYMENT_FIX_403.md` guide
- [x] Created `scripts/diagnose-403.sh` diagnostic tool
- [x] Documented multiple solutions (config, symlink, move files)
- [x] Created complete nginx configuration example

### Deployment Automation
- [x] `scripts/setup.sh` - auto-deployment script
- [x] `scripts/diagnose-403.sh` - 403 diagnostic tool
- [x] `FINAL_DEPLOYMENT_GUIDE.md` - complete guide
- [x] Updated README.md with quick deployment links

---

## SUPPORT CONTACTS

### Technical Support
- Hosting Provider Support
- Domain Registrar Support
- SSL Certificate Provider

### Emergency Contacts
- Server Admin
- Database Admin
- Development Team

---

## SUCCESS CRITERIA

✅ All items in this checklist completed
✅ Site returns HTTP 200 (not 403)
✅ Frontend fully functional
✅ Admin panel accessible and working
✅ API endpoints responding correctly
✅ No PHP errors in logs
✅ Database fully initialized
✅ Backups configured and running
✅ SSL certificate active
✅ Monitoring in place
✅ Site ready for production use

---

**Deployment Date:** _________________

**Deployed By:** _________________

**Verified By:** _________________

**Sign-off:** _________________

---

**🎉 CONGRATULATIONS! Your site is deployed and ready for production! 🚀**
