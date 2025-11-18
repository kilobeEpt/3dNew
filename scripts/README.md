# Maintenance Scripts

This directory contains automated maintenance scripts for the 3D Print Platform.

## Scripts Overview

### Backup Scripts

#### `backup-database.sh`
Creates compressed MySQL database backups.

- **Schedule**: Daily at 4 AM (via cron)
- **Retention**: 30 days
- **Location**: `backups/database/`
- **Notifications**: Emails admin on failure

```bash
# Run manually
bash scripts/backup-database.sh

# Cron entry
0 4 * * * cd /home/c/ch167436/3dPrint && bash scripts/backup-database.sh >> logs/backup.log 2>&1
```

#### `backup-files.sh`
Creates compressed archives of uploads directory.

- **Schedule**: Weekly on Sunday at 5 AM (via cron)
- **Retention**: 56 days (8 weeks)
- **Location**: `backups/files/`
- **Notifications**: Emails admin on failure

```bash
# Run manually
bash scripts/backup-files.sh

# Cron entry
0 5 * * 0 cd /home/c/ch167436/3dPrint && bash scripts/backup-files.sh >> logs/backup.log 2>&1
```

### Monitoring Scripts

#### `check-errors.php`
Monitors application logs for errors and sends email alerts.

- **Schedule**: Hourly (via cron)
- **Threshold**: 5 errors per hour
- **Actions**: Sends email with error summary if threshold exceeded
- **Log**: `logs/monitoring.log`

```bash
# Run manually
php scripts/check-errors.php

# Cron entry
0 * * * * cd /home/c/ch167436/3dPrint && php scripts/check-errors.php >> logs/monitoring.log 2>&1
```

### Maintenance Scripts

#### `rotate-logs.php`
Rotates and compresses old log files.

- **Schedule**: Daily at 3 AM (via cron)
- **Compression**: Files older than 7 days
- **Deletion**: Files older than 30 days
- **Log**: `logs/cron.log`

```bash
# Run manually
php scripts/rotate-logs.php

# Cron entry
0 3 * * * cd /home/c/ch167436/3dPrint && php scripts/rotate-logs.php >> logs/cron.log 2>&1
```

#### `cleanup-temp.php`
Cleans up temporary files and incomplete uploads.

- **Schedule**: Daily at 6 AM (via cron)
- **Actions**: Removes .tmp, .partial files older than 24 hours
- **Log**: `logs/cron.log`

```bash
# Run manually
php scripts/cleanup-temp.php

# Cron entry
0 6 * * * cd /home/c/ch167436/3dPrint && php scripts/cleanup-temp.php >> logs/cron.log 2>&1
```

#### `generate-sitemap.php`
Generates sitemap.xml for search engines.

- **Schedule**: Daily at 2 AM (via cron)
- **Output**: `public_html/sitemap-static.xml`
- **Includes**: Static pages, services, gallery items
- **Log**: `logs/cron.log`

```bash
# Run manually
php scripts/generate-sitemap.php

# Cron entry
0 2 * * * cd /home/c/ch167436/3dPrint && php scripts/generate-sitemap.php >> logs/cron.log 2>&1
```

### Deployment Scripts

#### `deploy.sh`
Automated deployment script for production.

- **Features**: 
  - Checks requirements
  - Installs dependencies
  - Sets permissions
  - Runs migrations
  - Builds assets
  - Verifies configuration

```bash
# Run deployment
bash scripts/deploy.sh production

# Or for development
bash scripts/deploy.sh development
```

#### `verify-deployment.php`
Pre-deployment verification script.

- **Checks**:
  - PHP version and extensions
  - File structure
  - Permissions
  - Configuration
  - Dependencies
  - Database connection
  - Security settings
  - Script executability

```bash
# Run verification
php scripts/verify-deployment.php
```

## Complete Cron Configuration

Add all cron jobs at once:

```cron
# Sitemap Generation (Daily 2 AM)
0 2 * * * cd /home/c/ch167436/3dPrint && php scripts/generate-sitemap.php >> logs/cron.log 2>&1

# Log Rotation (Daily 3 AM)
0 3 * * * cd /home/c/ch167436/3dPrint && php scripts/rotate-logs.php >> logs/cron.log 2>&1

# Database Backup (Daily 4 AM)
0 4 * * * cd /home/c/ch167436/3dPrint && bash scripts/backup-database.sh >> logs/backup.log 2>&1

# File Backup (Weekly Sunday 5 AM)
0 5 * * 0 cd /home/c/ch167436/3dPrint && bash scripts/backup-files.sh >> logs/backup.log 2>&1

# Cleanup Temporary Files (Daily 6 AM)
0 6 * * * cd /home/c/ch167436/3dPrint && php scripts/cleanup-temp.php >> logs/cron.log 2>&1

# Error Monitoring (Hourly)
0 * * * * cd /home/c/ch167436/3dPrint && php scripts/check-errors.php >> logs/monitoring.log 2>&1
```

## Manual Testing

Before setting up cron jobs, test each script manually:

```bash
cd /home/c/ch167436/3dPrint

# Test all scripts
bash scripts/backup-database.sh
bash scripts/backup-files.sh
php scripts/check-errors.php
php scripts/rotate-logs.php
php scripts/cleanup-temp.php
php scripts/generate-sitemap.php

# Check results
tail -n 50 logs/backup.log
tail -n 50 logs/cron.log
tail -n 50 logs/monitoring.log

# Verify backups created
ls -lh backups/database/
ls -lh backups/files/
```

## Monitoring Cron Jobs

### Check if cron jobs are registered

```bash
crontab -l
```

### View cron execution logs

```bash
# System cron log (if accessible)
tail -f /var/log/cron

# Application cron logs
tail -f logs/cron.log
tail -f logs/backup.log
tail -f logs/monitoring.log
```

### Test cron timing

To test without waiting for scheduled time:

```bash
# Run a single cron command manually
cd /home/c/ch167436/3dPrint && php scripts/check-errors.php >> logs/monitoring.log 2>&1
```

## Troubleshooting

### Script not executing

1. **Check permissions**: `ls -l scripts/`
   - Should show `rwxr-xr-x` for .sh and .php files
   - Fix: `chmod +x scripts/*.sh scripts/*.php`

2. **Check PHP path in shebang**:
   - Find PHP: `which php`
   - Update shebang if needed: `#!/usr/bin/php` or `#!/usr/bin/env php`

3. **Check script syntax**:
   ```bash
   php -l scripts/check-errors.php
   bash -n scripts/backup-database.sh
   ```

### Cron job not running

1. **Verify crontab entry**: `crontab -l`
2. **Check cron service**: `systemctl status cron` (if accessible)
3. **Verify paths are absolute** in cron entries
4. **Check email** for cron errors (if configured)

### Email alerts not sending

1. **Test email configuration**:
   ```php
   php -r "mail('test@example.com', 'Test', 'Test message');"
   ```

2. **Check SMTP settings** in `.env`
3. **Verify mailer service** is initialized in bootstrap.php

### Backup failures

1. **Check disk space**: `df -h`
2. **Verify database credentials** in `.env`
3. **Check MySQL connectivity**: `mysql -u user -p -e "SELECT 1;"`
4. **Review backup logs**: `tail -n 100 logs/backup.log`

### Log rotation issues

1. **Check log file permissions**: `ls -l logs/`
2. **Verify disk space**: `df -h`
3. **Test compression**: `gzip -t logs/*.gz`

## Best Practices

1. **Always test scripts manually** before adding to cron
2. **Monitor logs regularly** for errors
3. **Verify backups** can be restored periodically
4. **Keep scripts updated** with the application
5. **Document any customizations** you make
6. **Set up off-site backups** for critical data
7. **Test email alerts** to ensure they're received
8. **Review cron job timing** to avoid peak hours

## Customization

### Adjust backup retention

Edit the script and modify:

```bash
# backup-database.sh
RETENTION_DAYS=30  # Change to desired days

# backup-files.sh
RETENTION_DAYS=56  # Change to desired days
```

### Change error threshold

Edit `scripts/check-errors.php`:

```php
$errorThreshold = 5;  // Change to desired threshold
```

### Modify log rotation

Edit `scripts/rotate-logs.php`:

```php
$compressAfterDays = 7;   // When to compress
$deleteAfterDays = 30;    // When to delete
```

## Support

For issues or questions:
- Review [DEPLOYMENT.md](../DEPLOYMENT.md) for full documentation
- Check application logs: `logs/app.log`
- Review script logs: `logs/cron.log`, `logs/backup.log`, `logs/monitoring.log`

## Script Maintenance

Scripts should be reviewed and updated:
- **Monthly**: Check for optimization opportunities
- **Quarterly**: Review retention policies
- **Annually**: Audit security and efficiency

Last updated: [Current Date]
