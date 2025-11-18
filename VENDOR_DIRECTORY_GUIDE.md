# Vendor Directory Management Guide

## Overview

The `vendor/` directory contains all PHP dependencies installed via Composer. This guide explains how to manage it for deployment.

---

## Current Status

- ✅ `vendor/` is in `.gitignore` (default, recommended for most cases)
- ✅ `composer.lock` is **NOT** in `.gitignore` (required for consistent installations)
- ✅ Dependencies are installed and working locally

---

## Option 1: Keep vendor/ Out of Repository (Recommended)

### ✅ Advantages
- Smaller repository size
- Cleaner Git history
- Standard Composer practice
- Easier to update dependencies

### ❌ Disadvantages
- Requires Composer 2.x on hosting
- Requires internet connection during deployment
- Additional deployment step

### Setup
**Already configured!** No changes needed.

### Deployment
```bash
# On server
composer install --no-dev --optimize-autoloader
```

### Requirements
- Composer 2.2+ on hosting server
- Internet access during deployment

---

## Option 2: Commit vendor/ to Repository

Use this option if:
- Hosting has Composer 1.9.0 (cannot download from Packagist)
- Hosting has no Composer installed
- You want guaranteed reproducible deployments
- Internet access is limited during deployment

### ✅ Advantages
- Works without Composer on server
- Works with Composer 1.x
- Faster deployments (no download needed)
- Guaranteed exact versions

### ❌ Disadvantages
- Large repository size (+5-10 MB)
- Cluttered Git history
- Merge conflicts in vendor/
- Must update vendor/ after every composer update

### Setup

#### Step 1: Remove vendor/ from .gitignore
```bash
# Edit .gitignore
nano .gitignore

# Remove or comment out this line:
# /vendor/
```

Or use sed:
```bash
sed -i 's|^/vendor/|# /vendor/|' .gitignore
```

#### Step 2: Add vendor/ to Git
```bash
git add vendor/
git commit -m "Add vendor dependencies for Composer 1.x compatibility"
```

#### Step 3: Push to repository
```bash
git push origin main
```

### Deployment
```bash
# On server
git pull
# That's it! No composer needed.
```

### Updating Dependencies Later
```bash
# Locally
composer update --no-dev --optimize-autoloader
git add vendor/ composer.lock
git commit -m "Update Composer dependencies"
git push
```

---

## Option 3: Upload vendor/ Manually

Use this option if:
- You cannot update Composer on hosting
- You don't want to commit vendor/ to repository
- You have FTP/SFTP access

### ✅ Advantages
- Works without Composer on server
- Clean repository
- Full control over deployment

### ❌ Disadvantages
- Manual upload process
- Requires FTP/SFTP access
- Error-prone (easy to forget files)
- No version tracking

### Process

#### Step 1: Install locally
```bash
composer install --no-dev --optimize-autoloader
```

#### Step 2: Create archive
```bash
tar -czf vendor.tar.gz vendor/
```

Or zip:
```bash
zip -r vendor.zip vendor/
```

#### Step 3: Upload to server
Use FTP/SFTP to upload `vendor.tar.gz` to your project root.

#### Step 4: Extract on server
```bash
# Via SSH
cd /path/to/project
tar -xzf vendor.tar.gz
rm vendor.tar.gz

# Or via web-based file manager
# Extract vendor.tar.gz in the project root
```

### Updating Dependencies Later
Repeat the entire process with updated vendor/ directory.

---

## Comparison Table

| Feature | vendor/ Ignored | vendor/ Committed | vendor/ Uploaded |
|---------|----------------|-------------------|------------------|
| Repository Size | Small | Large (+5-10MB) | Small |
| Requires Composer 2.x | Yes | No | No |
| Internet on Deploy | Yes | No | No |
| Deployment Speed | Slow | Fast | Medium |
| Version Control | Via lock file | Full tracking | None |
| Merge Conflicts | No | Possible | No |
| Standard Practice | ✅ Yes | ❌ No | ❌ No |
| Works with Composer 1.x | ❌ No | ✅ Yes | ✅ Yes |

---

## Recommended Approach by Scenario

### Scenario 1: Modern Hosting with Composer 2.x
**→ Use Option 1 (vendor/ ignored)**
- Standard practice
- Clean repository
- Easy updates

### Scenario 2: Old Hosting with Composer 1.9.0
**→ Use Option 2 (vendor/ committed)**
- Only reliable solution for Composer 1.x
- Packagist doesn't support Composer 1.x anymore

**Or better:** Ask hosting provider to update Composer to 2.x

### Scenario 3: Hosting with No Composer
**→ Use Option 2 (vendor/ committed)**
- Simplest deployment (just git pull)
- No external tools needed

### Scenario 4: Shared Hosting with Only FTP Access
**→ Use Option 3 (vendor/ uploaded)**
- Works without Git or Composer on server
- Manual but reliable

---

## Current Configuration

### Files Status
```
composer.json       ✅ Updated for PHP 8.2
composer.lock       ✅ Generated, tracked in Git
vendor/             ✅ Installed locally, ignored in Git
.gitignore          ✅ vendor/ is ignored (Option 1)
```

### To Switch to Option 2 (Commit vendor/)
```bash
# Remove vendor/ from .gitignore
sed -i 's|^/vendor/|# /vendor/|' .gitignore

# Add and commit
git add .gitignore vendor/
git commit -m "Add vendor dependencies for deployment"
git push
```

### To Switch Back to Option 1
```bash
# Add vendor/ back to .gitignore
echo "/vendor/" >> .gitignore

# Remove vendor/ from Git (but keep locally)
git rm -r --cached vendor/
git commit -m "Remove vendor from repository"
git push
```

---

## Testing Your Choice

### If using Option 1 (vendor/ ignored):
```bash
# On server
rm -rf vendor/
composer install --no-dev --optimize-autoloader
php test_packages.php
```

### If using Option 2 (vendor/ committed):
```bash
# On server
git pull
php test_packages.php
```

### If using Option 3 (vendor/ uploaded):
```bash
# On server (after upload)
tar -xzf vendor.tar.gz
php test_packages.php
```

All should output:
```
✓ Autoloader works!
✓ PHPMailer version: 6.12.0
✓ Dotenv loaded successfully!
```

---

## Troubleshooting

### "Class not found" errors
→ Autoloader not working. Check:
```bash
ls vendor/autoload.php
php vendor/autoload.php
```

### "composer install" fails with Composer 1.9.0
→ Packagist doesn't support Composer 1.x anymore.
**Solutions:**
1. Update Composer to 2.x
2. Commit vendor/ to repository
3. Upload vendor/ manually

### Merge conflicts in vendor/
→ You committed vendor/ but shouldn't have.
**Solution:**
```bash
# Always regenerate vendor/ after merge
git checkout --theirs vendor/
composer install --no-dev --optimize-autoloader
```

### Large Git repository
→ vendor/ is committed.
**Options:**
1. Keep it (if deployment requires it)
2. Remove vendor/ from Git:
   ```bash
   echo "/vendor/" >> .gitignore
   git rm -r --cached vendor/
   git commit -m "Remove vendor from repository"
   ```

---

## Best Practices

1. **Always commit composer.lock** - Ensures consistent versions
2. **Don't commit vendor/** - Unless you have a good reason (Composer 1.x, no Composer)
3. **Test deployments** - Verify vendor/ works on staging server first
4. **Document your choice** - Let team know which option you're using
5. **Automate if possible** - Use deployment scripts

---

## Support

For help, see:
- [COMPOSER_PHP82_UPGRADE.md](COMPOSER_PHP82_UPGRADE.md) - Full Composer guide
- [COMPOSER_UPGRADE_EN.md](COMPOSER_UPGRADE_EN.md) - Quick reference
- [scripts/setup-composer-dependencies.sh](scripts/setup-composer-dependencies.sh) - Automated setup

---

## Summary

**Default Configuration (Recommended):**
- ✅ `vendor/` in `.gitignore`
- ✅ `composer.lock` tracked in Git
- ✅ Run `composer install` on server

**For Composer 1.9.0 Hosting:**
- ✅ Commit `vendor/` to Git, OR
- ✅ Upload `vendor/` manually, OR
- ✅ Ask hosting to update Composer to 2.x

**Current Status:** Option 1 configured, ready to switch to Option 2 if needed.
