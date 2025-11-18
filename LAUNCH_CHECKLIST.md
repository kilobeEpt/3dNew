# Launch Checklist - 3D Print Platform

Complete this checklist before launching the site to production. Mark each item as you complete it.

---

## Pre-Deployment Setup

### Server & Hosting
- [ ] Hosting account active with sufficient resources (PHP 7.4+, MySQL, 500MB+ disk space)
- [ ] Domain name registered and DNS configured
- [ ] FTP/SFTP credentials obtained
- [ ] SSH access verified (if available)
- [ ] cPanel or equivalent admin panel access confirmed

### SSL Certificate
- [ ] SSL certificate obtained (Let's Encrypt or commercial)
- [ ] SSL certificate installed and active
- [ ] HTTPS working without certificate warnings
- [ ] Verified with SSL Labs: https://www.ssllabs.com/ssltest/
- [ ] Grade A or better on SSL Labs test

### Database
- [ ] MySQL database created
- [ ] Database user created with strong password
- [ ] User granted all privileges on database
- [ ] Database connection tested successfully

### Email
- [ ] SMTP server credentials obtained
- [ ] SMTP authentication working
- [ ] Test email sent successfully
- [ ] Email deliverability confirmed (check spam folders)
- [ ] SPF record added to DNS (recommended)
- [ ] DKIM configured (recommended)

### Third-Party Services
- [ ] CAPTCHA service configured (reCAPTCHA or hCaptcha)
- [ ] CAPTCHA keys tested on forms
- [ ] Google Analytics setup (if using)
- [ ] Google Search Console verified

---

## File Deployment

### Upload Files
- [ ] All project files uploaded to server
- [ ] Files in correct directory structure:
  - [ ] `/home/c/ch167436/3dPrint/public_html/` (frontend)
  - [ ] `/home/c/ch167436/3dPrint/api/` (API)
  - [ ] `/home/c/ch167436/3dPrint/admin/` (admin panel)
  - [ ] `/home/c/ch167436/3dPrint/src/` (source code)
  - [ ] `/home/c/ch167436/3dPrint/vendor/` (dependencies)
- [ ] `.git` directory excluded from upload
- [ ] `node_modules` directory excluded from upload

### Dependencies
- [ ] Composer dependencies installed (`composer install --no-dev --optimize-autoloader`)
- [ ] Autoloader optimized for production
- [ ] Node.js dependencies installed (if building assets on server)
- [ ] CSS/JS assets built and minified

### Configuration
- [ ] `.env` file created from `.env.example`
- [ ] All `.env` variables configured:
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`
  - [ ] `APP_URL` set to production domain
  - [ ] Database credentials configured
  - [ ] SMTP credentials configured
  - [ ] `JWT_SECRET` set to strong random string (64+ characters)
  - [ ] `ADMIN_EMAIL` set to your email
  - [ ] CAPTCHA keys configured
  - [ ] `SITE_URL` set to production HTTPS URL
- [ ] `.env` file permissions set to 600 (chmod 600 .env)

### File Permissions
- [ ] `logs/` directory writable (chmod 755)
- [ ] `uploads/` directory writable (chmod 755)
- [ ] `backups/` directory created and writable (chmod 755)
- [ ] `.env` file secured (chmod 600)
- [ ] Scripts made executable (chmod +x scripts/*.sh)

### .htaccess Files
- [ ] `.htaccess` present in `public_html/`
- [ ] `.htaccess` present in `api/`
- [ ] `.htaccess` present in `admin/`
- [ ] HTTPS redirect enabled (uncommented in all .htaccess files)
- [ ] HSTS header enabled (uncommented in all .htaccess files)
- [ ] Mod_rewrite working (test by visiting any page)

---

## Database Setup

### Migration & Seeding
- [ ] Database migrations executed (`php database/migrate.php`)
- [ ] All 17 tables created successfully
- [ ] Database seeds executed (`php database/seed.php`)
- [ ] SEO settings seeded (`php database/seeds/SeoSettingsSeed.php`)
- [ ] Admin user created
- [ ] Sample data loaded (if desired)

### Database Verification
- [ ] Connect to database via command line or phpMyAdmin
- [ ] Verify all tables exist: `SHOW TABLES;`
- [ ] Check table structure: `DESCRIBE users;`
- [ ] Verify initial data: `SELECT COUNT(*) FROM services;`

---

## Security Configuration

### Application Security
- [ ] `APP_DEBUG=false` in production `.env`
- [ ] Strong `JWT_SECRET` set (64+ random characters)
- [ ] Error display disabled in production
- [ ] All passwords hashed (never stored in plain text)
- [ ] SQL injection protection verified (prepared statements)
- [ ] XSS protection enabled (input validation and escaping)
- [ ] CSRF protection active on all POST endpoints

### Server Security
- [ ] Directory listing disabled (Options -Indexes in .htaccess)
- [ ] Sensitive files blocked (.env, composer.json, etc.)
- [ ] File upload restrictions enforced (type, size validation)
- [ ] Rate limiting configured
- [ ] Admin panel protected by authentication

### Security Headers
- [ ] HTTPS enforced (all HTTP requests redirect to HTTPS)
- [ ] HSTS header active (Strict-Transport-Security)
- [ ] X-Content-Type-Options: nosniff
- [ ] X-Frame-Options: DENY or SAMEORIGIN
- [ ] X-XSS-Protection: 1; mode=block
- [ ] Content-Security-Policy configured
- [ ] Referrer-Policy set
- [ ] X-Powered-By header removed

### Verify Security Headers
```bash
curl -I https://yourdomain.com | grep -E "Strict-Transport|X-Frame|X-Content|Content-Security"
```
- [ ] All security headers present

### Security Testing
- [ ] Test forms with XSS payloads (should be blocked/escaped)
- [ ] Test SQL injection attempts (should fail)
- [ ] Test file upload restrictions (non-allowed types rejected)
- [ ] Test CSRF protection (requests without token rejected)
- [ ] Verify sensitive files not accessible:
  - [ ] `/.env` returns 403 or 404
  - [ ] `/composer.json` returns 403 or 404
  - [ ] `/database/` returns 403 or 404

---

## Cron Jobs

### Configure Cron Tasks
- [ ] Sitemap generation (daily at 2 AM):
  ```
  0 2 * * * cd /home/c/ch167436/3dPrint && php scripts/generate-sitemap.php >> logs/cron.log 2>&1
  ```
- [ ] Log rotation (daily at 3 AM):
  ```
  0 3 * * * cd /home/c/ch167436/3dPrint && php scripts/rotate-logs.php >> logs/cron.log 2>&1
  ```
- [ ] Database backup (daily at 4 AM):
  ```
  0 4 * * * cd /home/c/ch167436/3dPrint && bash scripts/backup-database.sh >> logs/backup.log 2>&1
  ```
- [ ] File backup (weekly on Sunday at 5 AM):
  ```
  0 5 * * 0 cd /home/c/ch167436/3dPrint && bash scripts/backup-files.sh >> logs/backup.log 2>&1
  ```
- [ ] Cleanup temp files (daily at 6 AM):
  ```
  0 6 * * * cd /home/c/ch167436/3dPrint && php scripts/cleanup-temp.php >> logs/cron.log 2>&1
  ```
- [ ] Error monitoring (hourly):
  ```
  0 * * * * cd /home/c/ch167436/3dPrint && php scripts/check-errors.php >> logs/monitoring.log 2>&1
  ```

### Verify Cron Jobs
- [ ] Cron jobs added to crontab (`crontab -l`)
- [ ] Cron email notifications configured
- [ ] Test cron scripts manually:
  ```bash
  cd /home/c/ch167436/3dPrint
  php scripts/generate-sitemap.php
  php scripts/rotate-logs.php
  bash scripts/backup-database.sh
  bash scripts/backup-files.sh
  php scripts/cleanup-temp.php
  php scripts/check-errors.php
  ```

---

## Backup & Monitoring

### Backup System
- [ ] Database backup script tested
- [ ] File backup script tested
- [ ] Backup retention configured (30 days for DB, 56 days for files)
- [ ] Backup directory has sufficient space
- [ ] Test backup restoration:
  ```bash
  # Extract latest database backup
  gunzip -c backups/database/database_backup_YYYY-MM-DD.sql.gz > restore.sql
  # Restore to test database
  mysql -u user -p test_db < restore.sql
  ```
- [ ] Off-site backup configured (FTP, S3, Google Drive, etc.)
- [ ] Backup monitoring alerts working

### Error Monitoring
- [ ] Error monitoring script tested
- [ ] Email alerts configured and tested
- [ ] Error threshold set appropriately
- [ ] Logs readable and properly formatted

### Log Files
- [ ] Log rotation working
- [ ] Logs compressed after 7 days
- [ ] Logs deleted after 30 days
- [ ] Log directory not growing unbounded

---

## Functional Testing

### API Endpoints
Test all API endpoints:

```bash
# Health check
curl https://yourdomain.com/api/health

# Services
curl https://yourdomain.com/api/services

# Materials
curl https://yourdomain.com/api/materials

# Pricing rules
curl https://yourdomain.com/api/pricing-rules

# Gallery
curl https://yourdomain.com/api/gallery

# News
curl https://yourdomain.com/api/news

# Settings
curl https://yourdomain.com/api/settings

# Sitemap
curl https://yourdomain.com/sitemap.xml

# Robots
curl https://yourdomain.com/robots.txt
```

- [ ] `/api/health` returns 200 OK
- [ ] `/api/services` returns service data
- [ ] `/api/materials` returns materials
- [ ] `/api/pricing-rules` returns pricing rules
- [ ] `/api/gallery` returns gallery items
- [ ] `/api/news` returns news articles
- [ ] `/api/settings` returns site settings
- [ ] `/sitemap.xml` is valid XML
- [ ] `/robots.txt` is accessible

### Frontend Pages

Test each page thoroughly:

**Homepage (index.html)**
- [ ] Page loads without errors
- [ ] All images display correctly
- [ ] Navigation menu works
- [ ] Hero section displays
- [ ] API health check button works
- [ ] Footer links work
- [ ] Mobile responsive

**Services Page (services.html)**
- [ ] Services load from API
- [ ] Service cards display correctly
- [ ] Images load properly
- [ ] Descriptions readable
- [ ] CTA buttons work
- [ ] Mobile responsive

**Calculator (calculator.html)**
- [ ] Form displays correctly
- [ ] Material selection dropdown works
- [ ] Quality selection works
- [ ] Dimension inputs accept numbers
- [ ] Unit conversion (mm/cm) works
- [ ] Infill percentage slider works
- [ ] Price calculation updates in real-time
- [ ] File upload works (test with small STL file < 5MB)
- [ ] Form validation works (required fields)
- [ ] CAPTCHA displays
- [ ] Form submission succeeds
- [ ] Confirmation message displays
- [ ] Email received by admin
- [ ] Mobile responsive

**Gallery Page (gallery.html)**
- [ ] Gallery items load from API
- [ ] Images display in grid
- [ ] Lightbox/modal works on click
- [ ] Image captions display
- [ ] Pagination works (if implemented)
- [ ] Mobile responsive

**About Page (about.html)**
- [ ] Content displays correctly
- [ ] Images load
- [ ] Links work
- [ ] Mobile responsive

**Contact Page (contact.html)**
- [ ] Contact form displays
- [ ] CAPTCHA displays
- [ ] Form validation works
- [ ] Form submission succeeds
- [ ] Confirmation message displays
- [ ] Email received by admin
- [ ] Mobile responsive

### Admin Panel

Test admin functionality:

**Login**
- [ ] Login page loads
- [ ] Can log in with admin credentials
- [ ] Invalid credentials rejected
- [ ] JWT token generated
- [ ] Token stored in localStorage/sessionStorage
- [ ] Token included in API requests

**Dashboard**
- [ ] Dashboard loads after login
- [ ] Statistics display correctly
- [ ] Charts render properly
- [ ] Recent activity shows
- [ ] Quick links work

**Services Management**
- [ ] Services list displays
- [ ] Can view service details
- [ ] Can create new service
- [ ] Can edit existing service
- [ ] Can delete service
- [ ] Image upload works
- [ ] Validation works
- [ ] Changes reflected on frontend

**Materials Management**
- [ ] Materials list displays
- [ ] CRUD operations work
- [ ] Price per gram updates correctly

**Pricing Rules**
- [ ] Pricing rules list displays
- [ ] Can create/edit/delete rules
- [ ] Rules apply correctly in calculator

**Gallery Management**
- [ ] Gallery items list displays
- [ ] Can upload images
- [ ] Thumbnails generated
- [ ] Can edit captions
- [ ] Can delete images
- [ ] Changes reflected on frontend

**News Management** (if implemented)
- [ ] News articles list displays
- [ ] CRUD operations work
- [ ] Rich text editor works
- [ ] Image upload works

**Site Settings**
- [ ] Settings page loads
- [ ] Can update site settings
- [ ] SEO settings editable
- [ ] Contact info editable
- [ ] Changes saved successfully

**Customer Requests**
- [ ] Calculator submissions display
- [ ] Contact form submissions display
- [ ] Can view request details
- [ ] Can mark as processed/completed

---

## Email Testing

### Test All Email Types
- [ ] **Contact form submission**: Email received by admin
- [ ] **Calculator estimate request**: Email received by admin
- [ ] **Password reset** (if implemented): Email received by user
- [ ] **Admin notifications**: Alerts working

### Email Content Verification
- [ ] Sender address correct (`MAIL_FROM_ADDRESS`)
- [ ] Sender name correct (`MAIL_FROM_NAME`)
- [ ] Subject lines appropriate
- [ ] Email body formatted correctly
- [ ] HTML emails render properly
- [ ] Plain text fallback works
- [ ] Links in emails work
- [ ] Emails not flagged as spam

---

## SEO Configuration

### Meta Tags
Verify on all pages:
- [ ] Unique `<title>` tag (50-60 characters)
- [ ] Meta description (150-160 characters)
- [ ] Meta keywords (if using)
- [ ] Canonical URL (`<link rel="canonical">`)
- [ ] Open Graph tags (og:title, og:description, og:image)
- [ ] Twitter Card tags
- [ ] Mobile viewport tag

### Structured Data
Test with: https://search.google.com/test/rich-results

- [ ] Organization schema on homepage
- [ ] LocalBusiness schema (if applicable)
- [ ] Service schema on services page
- [ ] Breadcrumb schema
- [ ] WebSite schema with search action
- [ ] All structured data validates

### Sitemap & Robots
- [ ] Sitemap generated: `/sitemap.xml`
- [ ] Sitemap is valid XML (test: https://www.xml-sitemaps.com/validate-xml-sitemap.html)
- [ ] Sitemap includes all important pages
- [ ] Robots.txt accessible: `/robots.txt`
- [ ] Robots.txt allows search engines
- [ ] Robots.txt references sitemap
- [ ] Sitemap submitted to Google Search Console

### Performance
- [ ] Gzip/Brotli compression enabled
- [ ] Browser caching configured
- [ ] Images optimized/compressed
- [ ] CSS/JS minified
- [ ] Page load time < 3 seconds
- [ ] First Contentful Paint < 1.8s
- [ ] Time to Interactive < 3.8s

Test with:
- Google PageSpeed Insights: https://pagespeed.web.dev/
- GTmetrix: https://gtmetrix.com/
- WebPageTest: https://www.webpagetest.org/

---

## Performance Testing

### Load Testing
```bash
# Simple load test (requires Apache Bench)
ab -n 100 -c 10 https://yourdomain.com/

# Test API endpoint
ab -n 50 -c 5 https://yourdomain.com/api/services
```

- [ ] Homepage handles 100 requests without errors
- [ ] API handles 50 requests without errors
- [ ] No 500 errors under load
- [ ] Response times acceptable (< 500ms)

### Resource Usage
- [ ] Check disk usage: `du -sh /home/c/ch167436/3dPrint`
- [ ] Monitor memory usage during peak load
- [ ] Database query performance acceptable
- [ ] No memory leaks after extended use

---

## Browser & Device Testing

### Desktop Browsers
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### Mobile Devices
- [ ] iPhone Safari
- [ ] Android Chrome
- [ ] Tablet (iPad/Android)

### Compatibility Checks
- [ ] Layout renders correctly in all browsers
- [ ] JavaScript works in all browsers
- [ ] Forms submit successfully in all browsers
- [ ] No console errors in any browser
- [ ] Touch interactions work on mobile
- [ ] Responsive breakpoints work

---

## Analytics & Tracking

### Google Analytics (if using)
- [ ] GA tracking code installed
- [ ] Tracking ID correct
- [ ] Real-time tracking working
- [ ] Events configured (form submissions, calculator usage)
- [ ] Goals set up
- [ ] E-commerce tracking (if applicable)

### Google Search Console
- [ ] Domain verified
- [ ] Sitemap submitted
- [ ] No crawl errors
- [ ] Coverage report looks good

---

## Documentation

### Update Documentation
- [ ] README.md updated with production info
- [ ] DEPLOYMENT.md complete and accurate
- [ ] API documentation current
- [ ] Admin user guide created
- [ ] Internal documentation updated

### Access & Credentials
- [ ] Document all credentials securely (use password manager)
- [ ] Admin credentials documented
- [ ] Database credentials documented
- [ ] FTP/SSH credentials documented
- [ ] API keys documented
- [ ] SSL certificate details documented
- [ ] Backup access information documented

---

## Legal & Compliance

### Legal Pages
- [ ] Privacy Policy page created
- [ ] Terms of Service page created
- [ ] Cookie Policy (if using cookies beyond essentials)
- [ ] Legal pages linked in footer

### GDPR Compliance (if applicable)
- [ ] Cookie consent banner (if required)
- [ ] Data collection disclosed
- [ ] Privacy policy covers all data handling
- [ ] User data deletion process documented

---

## Final Pre-Launch Tasks

### Communication
- [ ] Notify stakeholders of launch date
- [ ] Prepare launch announcement (email, social media)
- [ ] Plan marketing campaign
- [ ] Set up support channels (email, phone, chat)

### Monitoring Setup
- [ ] Uptime monitoring configured (UptimeRobot, Pingdom, etc.)
- [ ] Error tracking configured (Sentry, Rollbar, etc.)
- [ ] Performance monitoring set up
- [ ] Alert thresholds configured
- [ ] On-call schedule established (if team)

### Final Verification
- [ ] Complete end-to-end test as a user
- [ ] Test complete user journey:
  1. Visit homepage
  2. Browse services
  3. Use calculator
  4. Submit estimate request
  5. Submit contact form
  6. Receive confirmation emails
- [ ] All critical functionality working
- [ ] No console errors
- [ ] No PHP errors in logs
- [ ] SSL certificate valid
- [ ] Security headers present

### Soft Launch (Optional)
- [ ] Soft launch to limited audience
- [ ] Gather feedback
- [ ] Fix any issues discovered
- [ ] Monitor performance and errors

---

## Launch Day

### Go Live
- [ ] Final database backup
- [ ] Final file backup
- [ ] DNS changes propagated (if switching from old site)
- [ ] All stakeholders notified
- [ ] Launch announcement published

### Post-Launch Monitoring (First 24 Hours)
- [ ] Monitor error logs continuously: `tail -f logs/app.log`
- [ ] Check for 404 errors
- [ ] Monitor server resources (CPU, memory, disk)
- [ ] Track user submissions (calculator, contact form)
- [ ] Verify emails being sent and received
- [ ] Monitor uptime
- [ ] Check analytics for traffic
- [ ] Respond to any user reports immediately

### First Week Tasks
- [ ] Daily log review
- [ ] Performance monitoring
- [ ] User feedback collection
- [ ] Bug fixes as needed
- [ ] Content updates based on feedback

---

## Rollback Plan

In case of critical issues:

### Rollback Procedure
1. [ ] Keep previous version backup available
2. [ ] Document rollback steps:
   - Restore previous code version
   - Restore previous database backup
   - Update DNS if needed
   - Clear caches
3. [ ] Test rollback procedure before launch
4. [ ] Have rollback plan approved by stakeholders

---

## Success Metrics

Define and track:
- [ ] Uptime target: 99.9%
- [ ] Page load time target: < 3 seconds
- [ ] Form submission success rate: > 95%
- [ ] Email delivery rate: > 98%
- [ ] Zero critical errors in first week
- [ ] User satisfaction score (if collecting)

---

## Completion Sign-Off

**Deployment completed by:** _________________________ Date: ___________

**Tested and verified by:** _________________________ Date: ___________

**Approved for launch by:** _________________________ Date: ___________

---

## Notes & Issues

Use this space to document any issues encountered and their resolutions:

```
[Date] Issue: Description
Resolution: How it was fixed
Follow-up: Any remaining tasks
```

---

**Congratulations on your launch! 🚀**

For ongoing maintenance, refer to:
- DEPLOYMENT.md (deployment procedures)
- README.md (general documentation)
- ADMIN_API.md (API documentation)
- SEO_GUIDE.md (SEO best practices)
