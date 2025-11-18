# Deployment Guides Overview

Complete guide to deploying the 3D Print Platform on various hosting environments.

## 📚 Documentation Index

### Main Guides

| Document | Purpose | When to Use |
|----------|---------|-------------|
| **[README.md](README.md)** | Project overview & quick start | First-time setup |
| **[DEPLOYMENT.md](DEPLOYMENT.md)** | General deployment guide | All deployments |
| **[DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md)** | nginx-specific deployment | nginx hosting or 403 errors |
| **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** | Common issues & solutions | When problems occur |

### Specialized Guides

| Document | Purpose |
|----------|---------|
| **[SETUP_SCRIPT_GUIDE.md](SETUP_SCRIPT_GUIDE.md)** | Automated setup script |
| **[SETUP_README.md](SETUP_README.md)** | Quick setup reference |
| **[DEPLOYMENT_QUICKSTART.md](DEPLOYMENT_QUICKSTART.md)** | 30-minute deployment |
| **[SSL_SETUP.md](SSL_SETUP.md)** | SSL certificate setup |
| **[LAUNCH_CHECKLIST.md](LAUNCH_CHECKLIST.md)** | Pre-launch verification |

### nginx Router Documentation

| Document | Purpose |
|----------|---------|
| **[NGINX_ROUTER_README.md](NGINX_ROUTER_README.md)** | Router implementation |
| **[NGINX_ROUTER_DEPLOYMENT.md](NGINX_ROUTER_DEPLOYMENT.md)** | Router deployment |
| **[NGINX_ROUTER_IMPLEMENTATION.md](NGINX_ROUTER_IMPLEMENTATION.md)** | Technical details |
| **[NGINX_ROUTER_CHECKLIST.md](NGINX_ROUTER_CHECKLIST.md)** | Deployment checklist |

---

## 🚀 Quick Navigation

### I'm getting a 403 Forbidden error on nginx

👉 **Go to:** [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md) - Section "nginx 403 Forbidden - Complete Solution"

**Quick fix steps:**
1. Create test.php file
2. Check file permissions (755/644)
3. Verify nginx document root
4. Contact hosting support with template email

### I need to deploy to a new server

👉 **Go to:** [DEPLOYMENT.md](DEPLOYMENT.md) or run `bash scripts/setup.sh`

**Quick deployment:**
```bash
# Upload all files, then run:
cd /home/c/ch167436/3dPrint
bash scripts/setup.sh
```

### Something isn't working

👉 **Go to:** [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

**Common issues:**
- Database connection failed
- 500 Internal Server Error
- API not working
- Email not sending
- File upload problems

### I want to verify my server

👉 **Run:** `bash scripts/verify-server.sh`

This script checks:
- Server type (Apache/nginx)
- PHP version and extensions
- File permissions
- Directory structure
- nginx configuration
- Database connection

---

## 📋 Deployment Checklist

### Before Deployment

- [ ] Choose hosting provider
- [ ] Determine server type (Apache or nginx)
- [ ] Get SSH/FTP access
- [ ] Get database credentials
- [ ] Obtain SSL certificate
- [ ] Get SMTP credentials
- [ ] Get CAPTCHA keys

### Deployment Process

- [ ] Upload all files
- [ ] Run `bash scripts/setup.sh` (automated)
  - OR follow manual steps in DEPLOYMENT.md
- [ ] Configure .env file
- [ ] Set file permissions
- [ ] Run database migrations
- [ ] Configure cron jobs
- [ ] Set up SSL certificate
- [ ] Test all functionality

### After Deployment

- [ ] Run `bash scripts/verify-server.sh`
- [ ] Test all URLs (see [Post-Deployment Testing](DEPLOYMENT.md#post-deployment-testing))
- [ ] Configure backups
- [ ] Set up monitoring
- [ ] Change default admin password
- [ ] Complete [LAUNCH_CHECKLIST.md](LAUNCH_CHECKLIST.md)

---

## 🔧 Server-Specific Instructions

### Apache Hosting

**Difficulty:** ⭐ Easy

Apache deployment is straightforward:

1. Upload files
2. Run `bash scripts/setup.sh`
3. Done! `.htaccess` files handle routing automatically

**Documentation:**
- [DEPLOYMENT.md](DEPLOYMENT.md) - Sections 1-10
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Apache-specific issues

### nginx Hosting

**Difficulty:** ⭐⭐ Medium

nginx requires additional configuration:

1. Upload files
2. Run `bash scripts/setup.sh`
3. **Important:** Verify nginx configuration
   - Document root must point to `public_html/`
   - Must have `index index.php index.html;`
   - Must have `try_files` directive
   - Must have PHP-FPM configuration
4. Contact hosting support if 403 errors occur

**Documentation:**
- [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md) - Complete nginx guide
- [NGINX_ROUTER_README.md](NGINX_ROUTER_README.md) - Router details
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - nginx-specific issues

### cPanel Hosting

**Difficulty:** ⭐ Easy (Apache) or ⭐⭐ Medium (nginx)

Most cPanel hosting uses Apache, but some use nginx:

1. Check "Web Server" in cPanel dashboard
2. Follow Apache or nginx instructions above
3. Use cPanel tools:
   - File Manager for uploads
   - MySQL Database Wizard
   - SSL/TLS Status for Let's Encrypt
   - Cron Jobs for scheduling

**Documentation:**
- [DEPLOYMENT.md](DEPLOYMENT.md) - Includes cPanel-specific instructions

### VPS/Dedicated Server

**Difficulty:** ⭐⭐⭐ Advanced

Full server control allows complete configuration:

1. Install required software (PHP, nginx/Apache, MySQL)
2. Configure web server (full config access)
3. Upload files
4. Run `bash scripts/setup.sh`
5. Configure firewall, SSL, backups

**Documentation:**
- [DEPLOYMENT.md](DEPLOYMENT.md)
- [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md) - nginx server block config
- [SSL_SETUP.md](SSL_SETUP.md) - Manual SSL setup

---

## 🆘 Getting Help

### Step 1: Run Diagnostics

```bash
# Run server verification script
bash scripts/verify-server.sh

# Check logs
tail -n 100 logs/error.log
tail -n 100 logs/api.log
```

### Step 2: Check Documentation

1. **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Common issues
2. **[DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md)** - nginx 403 errors
3. Search documentation for your error message

### Step 3: Contact Hosting Support

Use email templates from:
- [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md#contact-hosting-support) - nginx issues
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md#contact-hosting-support) - General issues

### Step 4: Check Server Logs

```bash
# Application logs
tail -f logs/error.log

# nginx logs (if accessible)
sudo tail -f /var/log/nginx/error.log

# PHP-FPM logs (if accessible)
sudo tail -f /var/log/php8.2-fpm.log
```

---

## 🎯 Common Scenarios

### Scenario 1: Fresh Deployment

**Goal:** Deploy platform to new server

**Steps:**
1. Read [DEPLOYMENT.md](DEPLOYMENT.md) - Pre-Deployment Checklist
2. Upload files to server
3. Run `bash scripts/setup.sh`
4. Follow prompts
5. Run `bash scripts/verify-server.sh`
6. Complete [LAUNCH_CHECKLIST.md](LAUNCH_CHECKLIST.md)

**Time:** 30-60 minutes

### Scenario 2: nginx 403 Forbidden Error

**Goal:** Fix 403 error on nginx server

**Steps:**
1. Read [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md) - "nginx 403 Forbidden - Complete Solution"
2. Create test.php file
3. Check file permissions
4. Verify nginx document root
5. Contact hosting support if needed (use email template)

**Time:** 15-30 minutes (+ hosting support response time)

### Scenario 3: Migration from Another Host

**Goal:** Move existing deployment to new server

**Steps:**
1. Backup current database: `bash scripts/backup-database.sh`
2. Download all files from old server
3. Upload to new server
4. Run `bash scripts/setup.sh` (will detect existing .env)
5. Import database backup
6. Update .env with new database credentials
7. Test all functionality

**Time:** 1-2 hours

### Scenario 4: Troubleshooting Existing Deployment

**Goal:** Fix issues on already-deployed platform

**Steps:**
1. Run `bash scripts/verify-server.sh`
2. Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for your specific issue
3. Check application logs: `tail -f logs/error.log`
4. Try solutions from documentation
5. Contact hosting support if needed

**Time:** Varies by issue

---

## 📊 Documentation Coverage

### Issues Covered

- ✅ nginx 403 Forbidden errors
- ✅ Database connection issues
- ✅ 500 Internal Server Error
- ✅ API not working
- ✅ Admin panel login problems
- ✅ Email sending failures
- ✅ File upload problems
- ✅ CSS/JS not loading
- ✅ CORS errors
- ✅ Performance issues
- ✅ SSL certificate setup
- ✅ Backup and monitoring
- ✅ Security configuration
- ✅ Cron job setup

### Platforms Covered

- ✅ nginx shared hosting
- ✅ Apache shared hosting
- ✅ cPanel hosting (Apache/nginx)
- ✅ VPS/Dedicated servers
- ✅ Russian hosting providers (timeweb, beget, mchost)
- ✅ International hosting providers

---

## 🔄 Documentation Updates

**Last Updated:** 2024-11-18

**Recent Additions:**
- **NEW:** [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md) - Complete nginx deployment guide
- **NEW:** [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Comprehensive troubleshooting
- **NEW:** `scripts/verify-server.sh` - Server verification script

**Key Features:**
- Complete nginx 403 error solutions
- Hosting support email templates
- Server type detection instructions
- File permission verification
- nginx configuration examples
- Diagnostic scripts

---

## 📞 Support Resources

### Internal Documentation

- All `.md` files in project root
- Code comments in `src/` directory
- Database schema in `database/schema.sql`

### External Resources

- PHP Documentation: https://www.php.net/docs.php
- nginx Documentation: https://nginx.org/en/docs/
- MySQL Documentation: https://dev.mysql.com/doc/
- Composer Documentation: https://getcomposer.org/doc/

### Hosting Provider Docs

- cPanel: https://docs.cpanel.net/
- Timeweb: https://timeweb.com/ru/help/
- Beget: https://beget.com/ru/kb
- mchost: https://mchost.ru/support/

---

**Need immediate help?** Start with [TROUBLESHOOTING.md](TROUBLESHOOTING.md) or run `bash scripts/verify-server.sh`
