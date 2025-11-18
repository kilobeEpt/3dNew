# Quick Setup Guide

## One-Command Deployment

Deploy the entire 3D Print Platform with a single command:

```bash
bash scripts/setup.sh
```

## What It Does

✅ Checks PHP 8.2+ and required extensions  
✅ Tests MySQL/MariaDB connectivity  
✅ Creates all necessary directories  
✅ Sets correct permissions  
✅ Configures `.env` interactively  
✅ Installs Composer dependencies  
✅ Runs database migrations  
✅ Seeds initial data  
✅ Creates admin users  
✅ Verifies complete deployment  

## Requirements

- **PHP:** 8.2 or higher
- **MySQL/MariaDB:** 5.7+ / 10.2+
- **Extensions:** pdo_mysql, mbstring, openssl, json, fileinfo
- **Composer:** Any version (script downloads 2.x if needed)

## Interactive Prompts

The script will ask you for:

1. **Database Host** (default: localhost)
2. **Database Name** (required)
3. **Database User** (required)
4. **Database Password** (required)
5. **Application URL** (default: http://localhost)
6. **Admin Email** (default: admin@example.com)

JWT secret is generated automatically.

## Default Admin Credentials

**⚠️ CHANGE IMMEDIATELY AFTER FIRST LOGIN!**

```
Username: admin
Password: admin123
Email:    admin@example.com
Role:     super_admin
```

## After Setup

1. Visit your site: `http://yourdomain.com`
2. Access admin panel: `http://yourdomain.com/admin/`
3. **Change admin password immediately!**
4. Configure email settings in `.env`
5. Set up SSL certificate
6. Configure cron jobs
7. Test all functionality

## Troubleshooting

### Check Logs
```bash
tail -f logs/setup.log
```

### Verify Deployment
```bash
php scripts/verify-deployment.php
```

### Manual Migration
```bash
php database/migrate.php
php database/seed.php
```

## Re-running

The script is **idempotent** - safe to run multiple times. It will:
- Skip already-created directories
- Skip already-executed migrations
- Ask before reconfiguring `.env`

## Documentation

- **Complete Guide:** `SETUP_SCRIPT_GUIDE.md`
- **Deployment:** `DEPLOYMENT.md`
- **Quick Start:** `DEPLOYMENT_QUICKSTART.md`
- **Checklist:** `LAUNCH_CHECKLIST.md`

---

**Need help?** See `SETUP_SCRIPT_GUIDE.md` for detailed documentation.
