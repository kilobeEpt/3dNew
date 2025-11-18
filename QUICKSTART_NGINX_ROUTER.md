# nginx Router Quick Start

**5-Minute Guide to Understanding and Deploying the nginx Router**

## What Is It?

A PHP-based front controller that enables your application to work on nginx servers without requiring access to nginx configuration files. Perfect for shared hosting environments.

## How It Works

```
User Request → nginx → /public_html/index.php → Router Logic → Handler
```

All requests go through `/public_html/index.php`, which:
1. Routes API requests to `/api/index.php`
2. Routes admin requests to `/admin/index.php`
3. Serves static files (CSS, JS, images)
4. Handles 404 errors

## Quick Deploy (3 Steps)

### Step 1: Upload
Upload `/public_html/index.php` to your server.

### Step 2: Set Permissions
```bash
chmod 644 /path/to/public_html/index.php
```

### Step 3: Test
Visit your site: `http://yourdomain.com/`

**That's it!** If your hosting has standard nginx+PHP configuration, the router will work automatically.

## Verify It's Working

### Test URLs
```bash
# Homepage (should show HTML)
curl http://yourdomain.com/

# API (should return JSON)
curl http://yourdomain.com/api/services

# Sitemap (should return XML)
curl http://yourdomain.com/sitemap.xml
```

### Expected Results
- ✅ Homepage loads with CSS/JS
- ✅ API returns JSON responses
- ✅ Admin panel accessible
- ✅ Static files load correctly
- ✅ 404 page shows for non-existent URLs

## What It Routes

| Request | Routes To | Example |
|---------|-----------|---------|
| `/` | `index.html` | Homepage |
| `/about.html` | Static file | About page |
| `/assets/css/main.css` | Static file | Stylesheet |
| `/api/services` | `/api/index.php` | API endpoint |
| `/admin/login` | `/admin/index.php` | Admin panel |
| `/sitemap.xml` | `/api/sitemap.xml` | SEO sitemap |
| `/robots.txt` | `/api/robots.txt` | SEO robots |
| `/nonexistent` | `404.html` | Error page |

## Troubleshooting

### Blank Page?
Check PHP error log: `tail -f /var/log/php-fpm.log`

### 404 Everywhere?
Verify index.php exists: `ls -la public_html/index.php`

### CSS Not Loading?
Check file permissions: `chmod -R 644 public_html/assets/`

### API Not Working?
Verify API exists: `ls -la api/index.php`

## nginx Configuration (Optional)

If you have nginx access, add this for better performance:

```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

But this is **optional** - the router works without it on most shared hosting.

## File Structure

```
/home/yourdomain/
├── public_html/              # Web root
│   ├── index.php            # ← Router (NEW)
│   ├── index.html           # Homepage content
│   ├── assets/              # CSS, JS, images
│   └── 404.html             # Error page
├── api/
│   └── index.php            # API handler
├── admin/
│   └── index.php            # Admin handler
└── src/                     # PHP source code
```

## Security Features

- ✅ **Directory Traversal Protection**: Can't access files outside public_html
- ✅ **PHP File Protection**: PHP files can't be downloaded as text
- ✅ **Path Validation**: All paths are validated before serving
- ✅ **Proper Headers**: Correct Content-Type for all file types
- ✅ **Cache Control**: Optimal caching for performance

## Performance

- **Fast**: Simple routing logic, early exits
- **Cached**: Static assets cached for 1 year
- **Efficient**: Uses native `readfile()` for files
- **OPcache Ready**: PHP code can be cached

## Need More Info?

- **Complete Documentation**: `/NGINX_ROUTER_README.md`
- **Deployment Guide**: `/NGINX_ROUTER_DEPLOYMENT.md`
- **Test Cases**: `/test-router-logic.md`
- **Checklist**: `/NGINX_ROUTER_CHECKLIST.md`

## Common Questions

### Q: Do I need to modify nginx configuration?
**A:** No, not on most shared hosting. The router works with default nginx+PHP configuration.

### Q: Will it work on Apache?
**A:** Yes! The router is compatible with both Apache and nginx.

### Q: What if I have existing .htaccess files?
**A:** They'll be ignored on nginx but work fine on Apache. The router provides the same functionality for nginx.

### Q: Does it affect performance?
**A:** Minimal impact. The routing logic is simple and fast, and static files are cached properly.

### Q: Is it secure?
**A:** Yes. Multiple security layers prevent directory traversal, PHP file exposure, and other attacks.

### Q: Can I customize the routing?
**A:** Yes. Edit `/public_html/index.php` to add custom routing logic.

## Quick Reference

| Action | Command |
|--------|---------|
| Upload router | SCP/FTP `index.php` to `public_html/` |
| Set permissions | `chmod 644 public_html/index.php` |
| Test homepage | `curl http://yourdomain.com/` |
| Test API | `curl http://yourdomain.com/api/services` |
| Check errors | `tail -f /var/log/php-fpm.log` |
| View logs | `tail -f logs/error.log` |

## Support

Encountering issues? Check:
1. **Logs**: PHP-FPM, nginx, application logs
2. **Permissions**: Files should be 644, directories 755
3. **PHP Version**: Requires PHP 8.2+
4. **File Paths**: Verify all paths are correct

---

**Status**: ✅ Ready for Production  
**Compatibility**: nginx, Apache, shared hosting  
**PHP Version**: 8.2+  
**Configuration Required**: None (optional optimization available)  
