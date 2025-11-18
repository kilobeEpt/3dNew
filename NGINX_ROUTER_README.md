# PHP Router for nginx Compatibility

## Overview

This project now includes a PHP-based router (`/public_html/index.php`) that enables full compatibility with nginx servers on shared hosting environments without requiring access to nginx configuration files.

## Problem Solved

On nginx servers, `.htaccess` files (which work on Apache) are not supported. Without proper nginx configuration, requests cannot be routed to the appropriate handlers (API, admin panel, or static files). This router solves that problem by handling all routing through PHP.

## How It Works

The router (`/public_html/index.php`) is the main entry point for all requests and handles routing based on the URI:

### 1. API Requests (`/api/*`)
- All requests starting with `/api` are routed to `/api/index.php`
- The existing API router handles all API endpoints
- Examples:
  - `GET /api/services` → API services endpoint
  - `POST /api/cost-estimate` → Cost estimate submission
  - `GET /api/admin/auth/login` → Admin authentication

### 1a. SEO Files (Special Routes)
- `/sitemap.xml` → Routed to `/api/sitemap.xml` (dynamic generation)
- `/robots.txt` → Routed to `/api/robots.txt` (dynamic generation)
- These files are generated dynamically by the API for SEO purposes

### 2. Admin Panel Requests (`/admin/*`)
- All requests starting with `/admin` are routed to `/admin/index.php`
- The existing admin router handles all admin endpoints
- Examples:
  - `GET /admin/login` → Admin login page
  - `POST /admin/api/auth/login` → Admin authentication
  - `GET /admin/dashboard` → Admin dashboard

### 3. Static Files
- CSS, JavaScript, images, fonts, and HTML files are served directly
- Proper `Content-Type` headers are set based on file extension
- Cache headers are set for optimal performance:
  - Static assets (CSS, JS, images, fonts): 1 year cache
  - HTML files: no cache
- Examples:
  - `GET /assets/css/main.css` → Stylesheet
  - `GET /assets/js/app.js` → JavaScript file
  - `GET /index.html` → Homepage

### 4. HTML Pages
- Individual HTML pages are served if they exist
- Examples:
  - `GET /` → `/public_html/index.html`
  - `GET /about.html` → `/public_html/about.html`
  - `GET /contact.html` → `/public_html/contact.html`

### 5. 404 Errors
- If a requested file doesn't exist, the router serves `/public_html/404.html`
- Returns proper HTTP 404 status code

## Security Features

1. **Directory Traversal Protection**: Uses `realpath()` to prevent access to files outside `/public_html/`
2. **PHP File Protection**: Blocks direct access to `.php` files to prevent source code exposure
3. **Path Normalization**: Removes trailing slashes and validates paths
4. **Secure Headers**: Sets appropriate security headers and cache policies for different file types
5. **Request Method Forwarding**: All HTTP methods (GET, POST, PUT, DELETE, etc.) are properly forwarded to handlers

## nginx Configuration

### Minimal Required Configuration

For the router to work, nginx needs only this minimal configuration:

```nginx
server {
    listen 80;
    server_name 3dprint-omsk.ru;
    root /home/c/ch167436/3dPrint/public_html;
    index index.php index.html;

    # Route all requests through index.php
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Alternative: No Configuration Required

If you cannot access nginx configuration at all, you can use this workaround:

1. Set nginx to use `index.php` as the default index file
2. All requests will automatically go through `/public_html/index.php`
3. The router will handle everything

Most shared hosting providers with nginx+PHP have this setup by default.

## Supported MIME Types

The router correctly identifies and serves files with appropriate Content-Type headers:

### Web Files
- HTML: `text/html; charset=UTF-8`
- CSS: `text/css; charset=UTF-8`
- JavaScript: `application/javascript; charset=UTF-8`
- JSON: `application/json; charset=UTF-8`

### Images
- JPEG: `image/jpeg`
- PNG: `image/png`
- GIF: `image/gif`
- SVG: `image/svg+xml`
- WebP: `image/webp`
- ICO: `image/x-icon`

### Fonts
- WOFF: `font/woff`
- WOFF2: `font/woff2`
- TTF: `font/ttf`
- OTF: `font/otf`
- EOT: `application/vnd.ms-fontobject`

### 3D Model Files
- STL: `application/vnd.ms-pki.stl`
- OBJ: `text/plain`
- 3MF: `application/vnd.ms-package.3dmanufacturing-3dmodel+xml`
- STEP/STP: `application/step`

### Other
- PDF: `application/pdf`
- ZIP: `application/zip`
- XML: `application/xml`
- TXT: `text/plain; charset=UTF-8`

## Testing the Router

### Test Static Files
```bash
# Homepage
curl http://3dprint-omsk.ru/

# CSS file
curl -I http://3dprint-omsk.ru/assets/css/main.css

# JavaScript file
curl -I http://3dprint-omsk.ru/assets/js/app.js

# About page
curl http://3dprint-omsk.ru/about.html
```

### Test SEO Files
```bash
# Sitemap (dynamically generated)
curl http://3dprint-omsk.ru/sitemap.xml

# Robots.txt (dynamically generated)
curl http://3dprint-omsk.ru/robots.txt
```

### Test API Endpoints
```bash
# Get services
curl http://3dprint-omsk.ru/api/services

# Get materials
curl http://3dprint-omsk.ru/api/materials

# Submit cost estimate (with data)
curl -X POST http://3dprint-omsk.ru/api/cost-estimate \
  -H "Content-Type: application/json" \
  -d '{"material_id":1,"quality":"high","width":10,"length":10,"height":10}'
```

### Test Admin Panel
```bash
# Admin login page
curl http://3dprint-omsk.ru/admin/login

# Admin API endpoints require authentication
curl -X POST http://3dprint-omsk.ru/admin/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'
```

### Test 404 Handling
```bash
# Non-existent file
curl -I http://3dprint-omsk.ru/nonexistent-page

# Should return 404 status code and show 404.html
```

## Performance Considerations

1. **Static File Serving**: Files are served directly from disk with efficient `readfile()` function
2. **Caching**: Proper cache headers reduce server load for repeat visitors
3. **Minimal Overhead**: Routing logic is simple and fast
4. **No Database Queries**: Static file routing doesn't touch the database

## Troubleshooting

### Issue: Blank Page
- Check PHP error logs: `tail -f /path/to/logs/error.log`
- Verify PHP-FPM is running: `systemctl status php8.2-fpm`
- Check file permissions on `/public_html/index.php`

### Issue: API/Admin Not Working
- Verify paths to `/api/index.php` and `/admin/index.php` are correct
- Check that `bootstrap.php` exists and is accessible
- Review logs in `/logs/` directory

### Issue: 404 for All Requests
- Confirm nginx is routing requests to `index.php`
- Check nginx configuration: `nginx -t`
- Verify `try_files` directive in nginx config

### Issue: Wrong Content-Type
- Check the MIME type mapping in `serveStaticFile()` function
- Add missing extensions to the `$mimeTypes` array if needed

## Maintenance

### Adding New MIME Types
Edit `/public_html/index.php` and add to the `$mimeTypes` array in `serveStaticFile()`:

```php
$mimeTypes = [
    // ... existing types ...
    'newext' => 'application/new-type',
];
```

### Modifying Routing Logic
The routing priority is:
1. API routes (`/api/*`)
2. Admin routes (`/admin/*`)
3. Static files (if they exist)
4. 404 page

To change this, modify the main routing logic in `/public_html/index.php`.

## Migration from Apache

If you're migrating from Apache with `.htaccess`:

1. **No Changes Needed**: The router replicates `.htaccess` routing functionality
2. **Remove mod_rewrite Dependencies**: Router doesn't require mod_rewrite
3. **Keep .htaccess Files**: They won't hurt on nginx (they're just ignored)

## Related Files

- `/public_html/index.php` - Main router (this file)
- `/api/index.php` - API route handler
- `/admin/index.php` - Admin panel route handler
- `/src/Core/Router.php` - Core routing class used by API and admin
- `/public_html/.htaccess` - Apache configuration (not used on nginx)
- `/api/.htaccess` - Apache API configuration (not used on nginx)
- `/admin/.htaccess` - Apache admin configuration (not used on nginx)

## Summary

With this router in place, your application is now fully compatible with nginx shared hosting without requiring any server configuration changes. All routing is handled through PHP, making deployment simple and portable across different hosting environments.
