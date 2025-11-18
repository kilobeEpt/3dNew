# Auto-Deployment Setup Script Guide

## Overview

The `scripts/setup.sh` script provides a complete, automated deployment solution for the 3D Print Platform. With a single command, it performs all necessary checks, configurations, and installations to get your site up and running.

## Features

✅ **PHP 8.2+ Version Check** - Ensures server meets minimum requirements  
✅ **MySQL Connectivity Test** - Verifies database access  
✅ **Automatic Directory Creation** - Creates all necessary folders  
✅ **Permission Management** - Sets correct permissions (755/644)  
✅ **Interactive .env Configuration** - Guides you through setup  
✅ **Composer Dependency Installation** - Handles Composer 1.x/2.x  
✅ **Database Migrations** - Creates all database tables  
✅ **Database Seeding** - Populates initial data  
✅ **Admin User Creation** - Sets up default admin accounts  
✅ **Comprehensive Verification** - Tests all components  
✅ **Detailed Logging** - Saves complete log to `logs/setup.log`  
✅ **Idempotent** - Safe to run multiple times  

## Quick Start

### Basic Usage

```bash
bash scripts/setup.sh
```

That's it! The script will guide you through the setup process.

## What the Script Does

### Step 1: PHP Version Check
- Verifies PHP 8.2+ is installed
- Checks required PHP extensions:
  - pdo_mysql
  - mbstring
  - openssl
  - json
  - fileinfo

### Step 2: MySQL Availability Check
- Detects MySQL/MariaDB client
- Tests connectivity (performed later with credentials)

### Step 3: Directory Creation
Creates all required directories:
- `logs/` - Application logs
- `uploads/models/` - 3D model files
- `uploads/gallery/` - Gallery images
- `uploads/thumbnails/` - Image thumbnails
- `backups/database/` - Database backups
- `backups/files/` - File backups
- `temp/` - Temporary files
- `storage/cache/` - Application cache
- `storage/sessions/` - Session storage

### Step 4: Permission Setup
- Sets 755 permissions on directories
- Makes scripts executable
- Sets 600 permissions on .env (secure)

### Step 5: Environment Configuration
Interactive prompts for:
- **Database Host** (default: localhost)
- **Database Name** (required)
- **Database User** (required)
- **Database Password** (required)
- **Application URL** (default: http://localhost)
- **Admin Email** (default: admin@example.com)
- **JWT Secret** (auto-generated, 64+ characters)

The script:
- Creates `.env` from `.env.example` if missing
- Updates values with provided configuration
- Sets production defaults (APP_ENV=production, APP_DEBUG=false)
- Generates secure JWT secret automatically

### Step 6: Composer Dependencies
- Detects Composer version (1.x or 2.x)
- Downloads Composer 2.x if needed
- Installs production dependencies
- Verifies autoloader works correctly

### Step 7: Database Connection Test
- Attempts to connect to database
- Validates credentials
- Reports any connection issues

### Step 8: Database Setup
- Runs all migrations from `database/migrations/`
- Executes all seed files from `database/seeds/`
- Creates admin users with default credentials
- Populates initial data (categories, materials, settings)

### Step 9: Final Verification
- Counts database tables
- Checks for core frontend files
- Generates sitemap
- Validates complete deployment

## Output

### Success Output

Upon successful completion, you'll see:

```
🎉 Deployment Complete!

╔════════════════════════════════════════════════════════════════╗
║                                                                ║
║          Your 3D Print Platform is ready to use!               ║
║                                                                ║
╚════════════════════════════════════════════════════════════════╝

📋 Deployment Summary:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🌐 Site URL:           http://yourdomain.com
📁 Project Root:       /home/user/project
🗄️  Database:          your_database
📧 Admin Email:        admin@yourdomain.com

🔐 Admin Access:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Super Admin Account:
  Username:  admin
  Password:  admin123
  Email:     admin@example.com
  Role:      super_admin

Editor Account:
  Username:  editor
  Password:  editor123
  Email:     editor@example.com
  Role:      editor

⚠️  IMPORTANT: Change these default passwords immediately!
```

### Default Admin Credentials

**⚠️ CRITICAL: Change these passwords immediately after first login!**

**Super Admin:**
- Username: `admin`
- Password: `admin123`
- Email: `admin@example.com`
- Role: `super_admin`

**Editor:**
- Username: `editor`
- Password: `editor123`
- Email: `editor@example.com`
- Role: `editor`

## Next Steps After Setup

1. **Access Your Site**
   ```
   http://yourdomain.com
   ```

2. **Access Admin Panel**
   ```
   http://yourdomain.com/admin/
   ```

3. **Change Admin Passwords (CRITICAL!)**
   - Login to admin panel
   - Go to profile settings
   - Update password immediately

4. **Configure Email Settings**
   - Edit `.env` file
   - Set SMTP credentials
   - Test email functionality

5. **Set Up SSL Certificate**
   - See `SSL_SETUP.md` for instructions
   - Enable HTTPS redirect in `.htaccess` after SSL is active

6. **Configure Cron Jobs**
   - Add to crontab (see `DEPLOYMENT.md`):
   ```bash
   0 4 * * * cd /path && bash scripts/backup-database.sh >> logs/backup.log 2>&1
   0 5 * * 0 cd /path && bash scripts/backup-files.sh >> logs/backup.log 2>&1
   0 * * * * cd /path && php scripts/check-errors.php >> logs/monitoring.log 2>&1
   ```

7. **Test All Functionality**
   - Test contact form
   - Test calculator
   - Test admin panel features
   - Test file uploads

## Troubleshooting

### PHP Version Error

**Error:** `PHP 8.2 or higher required!`

**Solution:**
- Upgrade PHP to 8.2 or higher
- Contact your hosting provider for PHP upgrade
- Check available PHP versions: `php -v`

### Missing PHP Extensions

**Error:** `Missing extension: pdo_mysql`

**Solution:**
- Install missing extension via package manager
- On Ubuntu/Debian: `sudo apt-get install php8.2-mysql`
- On shared hosting: contact support or enable in control panel

### Database Connection Failed

**Error:** `Database connection failed: Access denied`

**Solution:**
- Verify database credentials in `.env`
- Check database user has correct permissions
- Ensure database exists
- Test connection manually:
  ```bash
  mysql -h localhost -u username -p database_name
  ```

### Composer Installation Failed

**Error:** `Failed to download Composer`

**Solution:**
- Check internet connectivity
- Download Composer manually: https://getcomposer.org/download/
- Upload `composer.phar` to project root
- Or upload pre-built `vendor/` directory

### Permission Denied Errors

**Error:** `Could not set permissions on logs/`

**Solution:**
- Run as user with appropriate permissions
- On shared hosting, use FTP/File Manager to set permissions:
  - Directories: 755
  - Files: 644
  - `.env`: 600

### Migration Failed

**Error:** `Database migration failed!`

**Solution:**
- Check database credentials
- Verify database user has CREATE, ALTER, DROP permissions
- Check error in output for specific SQL error
- Ensure database charset is utf8mb4

## Re-running the Script

The script is **idempotent** - it's safe to run multiple times:

- If `.env` exists, asks if you want to reconfigure
- Skips already-executed migrations
- Skips already-seeded data (duplicate key protection)
- Creates only missing directories
- Updates only as needed

## Manual Steps (If Script Fails)

If the automated script fails, you can perform steps manually:

### 1. Create .env
```bash
cp .env.example .env
nano .env  # Edit configuration
```

### 2. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Create Directories
```bash
mkdir -p logs uploads/models backups/database backups/files temp storage
chmod 755 logs uploads backups temp storage
```

### 4. Run Migrations
```bash
php database/migrate.php
```

### 5. Seed Database
```bash
php database/seed.php
```

### 6. Verify
```bash
php scripts/verify-deployment.php
```

## Logging

All output is logged to:
```
logs/setup.log
```

View the log:
```bash
tail -f logs/setup.log
```

View last 50 lines:
```bash
tail -n 50 logs/setup.log
```

## Security Notes

1. **JWT_SECRET** - Automatically generated (64+ characters)
2. **.env** - Set to 600 permissions (owner read/write only)
3. **Default Passwords** - Must be changed immediately
4. **Production Mode** - Script sets APP_ENV=production, APP_DEBUG=false
5. **Directory Permissions** - Set to 755 (not 777)

## Shared Hosting Compatibility

The script is designed for shared hosting:
- No sudo/root required
- No system package installation
- Works with Composer 1.9.0 (downloads 2.x)
- Uses standard PHP/MySQL
- Handles common shared hosting limitations

## Environment Variables Set

The script configures these critical variables:

```bash
APP_ENV=production
APP_DEBUG=false
DB_HOST=your-input
DB_NAME=your-input
DB_USER=your-input
DB_PASS=your-input
APP_URL=your-input
SITE_URL=your-input
ADMIN_EMAIL=your-input
JWT_SECRET=auto-generated
```

## Support & Documentation

- **Complete Deployment Guide:** `DEPLOYMENT.md`
- **Quick Start Guide:** `DEPLOYMENT_QUICKSTART.md`
- **Pre-Launch Checklist:** `LAUNCH_CHECKLIST.md`
- **SSL Setup:** `SSL_SETUP.md`
- **Admin API:** `ADMIN_API.md`
- **Public API:** `API_PUBLIC.md`

## Script Maintenance

The script is located at:
```
scripts/setup.sh
```

To update permissions:
```bash
chmod +x scripts/setup.sh
```

To test syntax:
```bash
bash -n scripts/setup.sh
```

## Common Use Cases

### Fresh Installation
```bash
bash scripts/setup.sh
```

### Re-deployment After Code Update
```bash
bash scripts/setup.sh
# Choose 'N' when asked to reconfigure .env
```

### Testing Locally
```bash
bash scripts/setup.sh
# Use localhost for DB_HOST and APP_URL
# After setup:
php -S localhost:8000 -t public_html
```

### Production Deployment
```bash
bash scripts/setup.sh
# Use production values
# Don't forget to set up SSL and cron jobs after!
```

## Contact & Support

If you encounter issues not covered in this guide:

1. Check `logs/setup.log` for detailed error messages
2. Review `DEPLOYMENT.md` for additional troubleshooting
3. Consult `LAUNCH_CHECKLIST.md` for deployment verification
4. Contact your hosting provider for server-specific issues

---

**Version:** 1.0  
**Last Updated:** 2024  
**Compatibility:** PHP 8.2+, MySQL 5.7+/MariaDB 10.2+
