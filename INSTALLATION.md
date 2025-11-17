# Installation Guide

This guide will walk you through setting up the project on your local machine or shared hosting environment.

## Quick Start (Local Development)

### Prerequisites

Ensure you have the following installed:
- PHP 7.4 or higher
- Composer
- MySQL/MariaDB (optional, for database features)
- Node.js and npm (optional, for asset building)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd project
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```
   
   This will install:
   - vlucas/phpdotenv (for environment variable management)
   - phpmailer/phpmailer (for email functionality)

3. **Set up environment configuration**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` and configure your settings:
   ```env
   APP_ENV=development
   APP_DEBUG=true
   APP_URL=http://localhost:8000
   
   # Database (if using)
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=your_database
   DB_USER=your_username
   DB_PASS=your_password
   
   # Mail settings (if using)
   MAIL_HOST=smtp.example.com
   MAIL_PORT=587
   MAIL_USERNAME=your_email@example.com
   MAIL_PASSWORD=your_password
   ```

4. **Set up permissions**
   ```bash
   chmod -R 755 logs/
   ```

5. **Install Node.js dependencies (optional)**
   ```bash
   npm install
   ```

6. **Build assets (optional)**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php -S localhost:8000 -t public_html
   ```
   
   Or use the provided script:
   ```bash
   ./dev-server.sh
   ```

8. **Test the installation**
   - Visit: http://localhost:8000
   - Click "Check API Health" button
   - You should see a JSON response with API status

## Shared Hosting Setup

### cPanel Setup

1. **Upload files**
   - Upload all files to your hosting account via FTP/SFTP
   - Or use File Manager in cPanel

2. **Directory Structure**
   
   If your hosting uses `public_html` as the web root:
   ```
   /home/username/
   ├── public_html/          (already your web root)
   │   └── (frontend files)
   ├── api/
   ├── admin/
   ├── src/
   ├── vendor/
   └── (other files)
   ```
   
   If you need to reorganize:
   - Move contents of `public_html/` to your web root
   - Keep `api/`, `admin/`, `src/`, etc. one level above web root

3. **Set up subdomains (recommended)**
   
   In cPanel:
   - Create subdomain: `api.yourdomain.com` → point to `/api`
   - Create subdomain: `admin.yourdomain.com` → point to `/admin`

4. **Configure PHP**
   - Ensure PHP 7.4+ is enabled
   - Enable mod_rewrite (usually enabled by default)

5. **Install Composer dependencies**
   
   Via SSH:
   ```bash
   cd /path/to/project
   composer install --no-dev --optimize-autoloader
   ```
   
   Or use cPanel's Terminal feature if available

6. **Set up .env file**
   ```bash
   cp .env.example .env
   nano .env  # Edit the file
   ```

7. **Set permissions**
   ```bash
   chmod -R 755 logs/
   chmod 644 .env
   ```

### Alternative: Subdirectory Setup

If you want to use subdirectories instead of subdomains:

1. Access structure:
   - Frontend: `http://yourdomain.com/`
   - API: `http://yourdomain.com/api/`
   - Admin: `http://yourdomain.com/admin/`

2. Ensure `.htaccess` files are in place:
   - `/public_html/.htaccess`
   - `/api/.htaccess`
   - `/admin/.htaccess`

## Database Setup (Optional)

If you're using database features:

1. **Create a database**
   - In cPanel: MySQL Databases
   - Create a new database
   - Create a user and grant all privileges

2. **Import schema (when you create one)**
   ```bash
   mysql -u username -p database_name < schema.sql
   ```

3. **Update .env**
   ```env
   DB_HOST=localhost
   DB_NAME=your_database
   DB_USER=your_user
   DB_PASS=your_password
   ```

## Verification

### Health Check

Test the API endpoint:

```bash
curl http://localhost:8000/api/health
```

Expected response:
```json
{
    "status": "healthy",
    "timestamp": "2024-01-01T12:00:00+00:00",
    "services": {
        "api": "up",
        "database": "up"
    }
}
```

### Frontend

1. Visit your frontend URL
2. Click "Check API Health"
3. Should display the JSON response

## Troubleshooting

### .htaccess not working

**Problem**: 404 errors or routes not working

**Solution**:
1. Verify Apache's `mod_rewrite` is enabled:
   ```bash
   # On Ubuntu/Debian
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. Check Apache configuration allows `.htaccess`:
   ```apache
   <Directory /path/to/project>
       AllowOverride All
   </Directory>
   ```

### Composer not found on shared hosting

**Problem**: Can't run `composer install`

**Solution**:
1. Install Composer locally:
   ```bash
   curl -sS https://getcomposer.org/installer | php
   php composer.phar install
   ```

2. Or install dependencies locally and upload the `vendor/` directory

### Permission denied errors

**Problem**: Can't write to logs/

**Solution**:
```bash
chmod -R 755 logs/
chown -R www-data:www-data logs/  # Linux
# or
chown -R _www:_www logs/  # macOS
```

### Database connection failed

**Problem**: Can't connect to database

**Solution**:
1. Verify credentials in `.env`
2. Check if database server is running
3. Verify firewall allows connection
4. Use `127.0.0.1` instead of `localhost` if socket issues occur

### Email not sending

**Problem**: Emails aren't being sent

**Solution**:
1. Verify SMTP credentials in `.env`
2. Check if port is open (587 for TLS, 465 for SSL)
3. Some hosts block outgoing SMTP - check with hosting provider
4. Try using mail provider's SMTP (Gmail, SendGrid, etc.)

### 500 Internal Server Error

**Problem**: White screen or 500 error

**Solution**:
1. Enable debug mode in `.env`:
   ```env
   APP_DEBUG=true
   ```
2. Check `logs/app.log` for errors
3. Verify PHP version: `php -v` (must be 7.4+)
4. Check PHP error logs

## Next Steps

After installation:

1. **Review Configuration**
   - Check all settings in `.env`
   - Update CORS settings if needed

2. **Create Your First Endpoint**
   - Add routes in `api/routes.php`
   - Create controllers in `src/Controllers/`
   - See README.md for examples

3. **Customize Frontend**
   - Edit `public_html/index.html`
   - Modify styles in `public_html/assets/css/main.css`
   - Update scripts in `public_html/assets/js/main.js`

4. **Set Up Admin Panel**
   - Implement authentication in `src/Middleware/AuthMiddleware.php`
   - Create admin controllers
   - Add admin routes

5. **Read Documentation**
   - README.md - Full project documentation
   - CODING_STANDARDS.md - Coding guidelines
   - API documentation (create as you build)

## Security Checklist

Before going to production:

- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Use strong `JWT_SECRET`
- [ ] Configure proper CORS settings
- [ ] Implement proper authentication
- [ ] Set restrictive file permissions
- [ ] Keep dependencies updated
- [ ] Use HTTPS (SSL certificate)
- [ ] Implement rate limiting
- [ ] Regular backups
- [ ] Monitor logs

## Support

For questions or issues:
1. Check the README.md
2. Review CODING_STANDARDS.md
3. Check logs/app.log
4. Open an issue in the repository
