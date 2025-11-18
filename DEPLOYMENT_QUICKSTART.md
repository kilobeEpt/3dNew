# Deployment Quick Start Guide

This is a condensed version of the deployment process. For full details, see [DEPLOYMENT.md](DEPLOYMENT.md).

## Prerequisites

- ✅ PHP 7.4+
- ✅ MySQL 5.7+ / MariaDB 10.2+
- ✅ Composer installed
- ✅ FTP/SSH access
- ✅ SSL certificate ready

## Quick Deployment Steps

### 1. Upload Files (5 min)

```bash
# Via SSH
scp -r . user@server:/home/c/ch167436/3dPrint/
# Exclude: .git, node_modules, .env
```

### 2. Install Dependencies (2 min)

```bash
cd /home/c/ch167436/3dPrint
composer install --no-dev --optimize-autoloader
```

### 3. Configure Environment (3 min)

```bash
cp .env.example .env
nano .env
```

**Critical settings:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_NAME=ch167436_3dprint
DB_USER=ch167436_dbuser
DB_PASS=your_secure_password
JWT_SECRET=<generate_64_char_random_string>
ADMIN_EMAIL=admin@yourdomain.com
SITE_URL=https://yourdomain.com
```

### 4. Set Permissions (1 min)

```bash
chmod 755 logs/ uploads/ backups/
chmod 600 .env
chmod +x scripts/*.sh scripts/*.php
```

### 5. Setup Database (3 min)

```bash
# Create database (via cPanel or command line)
mysql -u root -p
CREATE DATABASE ch167436_3dprint CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Run migrations
php database/migrate.php

# Seed initial data
php database/seed.php
```

### 6. Build Assets (2 min)

```bash
npm install
npm run build
```

### 7. Enable HTTPS (2 min)

**In cPanel:**
1. Go to SSL/TLS Status
2. Run AutoSSL for your domain
3. Wait for certificate activation

**Enable redirect:**

Uncomment in `public_html/.htaccess`, `api/.htaccess`, `admin/.htaccess`:

```apache
# Redirect to HTTPS
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
</IfModule>

# HSTS
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" env=HTTPS
</IfModule>
```

### 8. Configure Cron Jobs (3 min)

Add to crontab (cPanel → Cron Jobs):

```cron
# Sitemap (Daily 2 AM)
0 2 * * * cd /home/c/ch167436/3dPrint && php scripts/generate-sitemap.php >> logs/cron.log 2>&1

# Log Rotation (Daily 3 AM)
0 3 * * * cd /home/c/ch167436/3dPrint && php scripts/rotate-logs.php >> logs/cron.log 2>&1

# Database Backup (Daily 4 AM)
0 4 * * * cd /home/c/ch167436/3dPrint && bash scripts/backup-database.sh >> logs/backup.log 2>&1

# File Backup (Weekly Sunday 5 AM)
0 5 * * 0 cd /home/c/ch167436/3dPrint && bash scripts/backup-files.sh >> logs/backup.log 2>&1

# Cleanup (Daily 6 AM)
0 6 * * * cd /home/c/ch167436/3dPrint && php scripts/cleanup-temp.php >> logs/cron.log 2>&1

# Error Monitoring (Hourly)
0 * * * * cd /home/c/ch167436/3dPrint && php scripts/check-errors.php >> logs/monitoring.log 2>&1
```

### 9. Test Everything (10 min)

```bash
# API Health
curl https://yourdomain.com/api/health

# Test frontend
# Visit: https://yourdomain.com
# Test: Calculator, Contact Form, Gallery

# Test admin
# Visit: https://yourdomain.com/admin
# Login and test CRUD operations
```

### 10. Security Verification (5 min)

```bash
# Check security headers
curl -I https://yourdomain.com | grep -E "Strict-Transport|X-Frame|X-Content"

# SSL Test
# Visit: https://www.ssllabs.com/ssltest/
# Goal: Grade A or A+

# Verify sensitive files blocked
curl -I https://yourdomain.com/.env  # Should be 403 or 404
curl -I https://yourdomain.com/composer.json  # Should be 403 or 404
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 500 Error | Check `logs/app.log`, verify PHP version 7.4+ |
| Database connection failed | Verify credentials in `.env`, test with `mysql -u user -p` |
| HTTPS not working | Check SSL certificate installation in cPanel |
| Emails not sending | Verify SMTP credentials, test with telnet |
| Mod_rewrite not working | Ensure `AllowOverride All` in Apache config |

## Post-Deployment Checklist

- [ ] Site accessible via HTTPS
- [ ] HTTP redirects to HTTPS
- [ ] API endpoints working
- [ ] Forms submitting successfully
- [ ] Emails being received
- [ ] Admin panel login working
- [ ] Security headers present
- [ ] Cron jobs configured
- [ ] Backups running
- [ ] SSL grade A or better
- [ ] No errors in logs

## Need Help?

- Full documentation: [DEPLOYMENT.md](DEPLOYMENT.md)
- Launch checklist: [LAUNCH_CHECKLIST.md](LAUNCH_CHECKLIST.md)
- API docs: [API.md](API.md) and [ADMIN_API.md](ADMIN_API.md)
- Installation guide: [INSTALLATION.md](INSTALLATION.md)

## One-Line Deploy (Automated)

```bash
cd /home/c/ch167436/3dPrint && bash scripts/deploy.sh production
```

This will:
- Check requirements
- Install dependencies
- Set permissions
- Run migrations
- Build assets
- Verify configuration

**Total Time: ~30 minutes** (excluding DNS propagation and SSL issuance)

---

**Ready to go live? Follow the [LAUNCH_CHECKLIST.md](LAUNCH_CHECKLIST.md)!**
