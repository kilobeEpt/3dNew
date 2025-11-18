# Router Logic Test Cases

This document describes test cases for the PHP nginx router (`/public_html/index.php`).

## Test Cases

### 1. Homepage
**Request**: `GET /`  
**Expected**: Serves `/public_html/index.html`  
**Status**: 200

### 2. Static HTML Pages
**Request**: `GET /about.html`  
**Expected**: Serves `/public_html/about.html`  
**Status**: 200

### 3. Static Assets (CSS)
**Request**: `GET /assets/css/main.css`  
**Expected**: Serves `/public_html/assets/css/main.css` with `Content-Type: text/css`  
**Cache**: 1 year  
**Status**: 200

### 4. Static Assets (JavaScript)
**Request**: `GET /assets/js/app.js`  
**Expected**: Serves `/public_html/assets/js/app.js` with `Content-Type: application/javascript`  
**Cache**: 1 year  
**Status**: 200

### 5. Static Assets (Images)
**Request**: `GET /assets/images/logo.png`  
**Expected**: Serves `/public_html/assets/images/logo.png` with `Content-Type: image/png`  
**Cache**: 1 year  
**Status**: 200

### 6. API Routes
**Request**: `GET /api/services`  
**Expected**: Routes to `/api/index.php`, which handles the request  
**Status**: 200 (or appropriate API response)

### 7. API Routes (Dynamic Resources)
**Request**: `POST /api/cost-estimates`  
**Expected**: Routes to `/api/index.php`, which handles the request  
**Status**: 200/201 (or appropriate API response)

### 8. Admin Panel Routes
**Request**: `GET /admin/login`  
**Expected**: Routes to `/admin/index.php`, which serves the login page  
**Status**: 200

### 9. Admin API Routes
**Request**: `POST /api/admin/auth/login`  
**Expected**: Routes to `/api/index.php`, which handles admin authentication  
**Status**: 200 (or appropriate API response)

### 10. SEO: Sitemap
**Request**: `GET /sitemap.xml`  
**Expected**: Routes to `/api/sitemap.xml` for dynamic generation  
**Status**: 200  
**Content-Type**: `application/xml`

### 11. SEO: Robots.txt
**Request**: `GET /robots.txt`  
**Expected**: Routes to `/api/robots.txt` for dynamic generation  
**Status**: 200  
**Content-Type**: `text/plain`

### 12. Non-existent File
**Request**: `GET /nonexistent-page.html`  
**Expected**: Serves `/public_html/404.html`  
**Status**: 404

### 13. Non-existent Route
**Request**: `GET /some/random/path`  
**Expected**: Serves `/public_html/404.html`  
**Status**: 404

### 14. Directory Traversal Attack
**Request**: `GET /../../../etc/passwd`  
**Expected**: Serves `/public_html/404.html`  
**Status**: 404

### 15. PHP File Access (Security)
**Request**: `GET /some-file.php`  
**Expected**: Serves `/public_html/404.html` (PHP files are blocked)  
**Status**: 404

### 16. Hidden Files Access
**Request**: `GET /.env`  
**Expected**: File is outside public_html, so 404  
**Status**: 404

### 17. Trailing Slash
**Request**: `GET /about.html/`  
**Expected**: Normalized to `/about.html`, then serves the file  
**Status**: 200

### 18. Query String Handling
**Request**: `GET /api/services?active=1`  
**Expected**: Routes to `/api/index.php` with query string preserved  
**Status**: 200

### 19. HTTP Method Handling (POST)
**Request**: `POST /api/contact`  
**Expected**: Routes to `/api/index.php` for handling  
**Status**: 200/201

### 20. HTTP Method Handling (PUT)
**Request**: `PUT /api/admin/services/1`  
**Expected**: Routes to `/api/index.php` for handling  
**Status**: 200

### 21. HTTP Method Handling (DELETE)
**Request**: `DELETE /api/admin/services/1`  
**Expected**: Routes to `/api/index.php` for handling  
**Status**: 200/204

## Manual Testing Commands

### Using cURL

```bash
# Test homepage
curl -I http://3dprint-omsk.ru/

# Test static CSS
curl -I http://3dprint-omsk.ru/assets/css/main.css

# Test API endpoint
curl http://3dprint-omsk.ru/api/services

# Test sitemap
curl http://3dprint-omsk.ru/sitemap.xml

# Test robots.txt
curl http://3dprint-omsk.ru/robots.txt

# Test 404
curl -I http://3dprint-omsk.ru/nonexistent

# Test POST to API
curl -X POST http://3dprint-omsk.ru/api/analytics/events \
  -H "Content-Type: application/json" \
  -d '{"event":"test","category":"test"}'
```

### Using Browser DevTools

1. Open browser DevTools (F12)
2. Navigate to Network tab
3. Visit http://3dprint-omsk.ru/
4. Check:
   - Status codes
   - Content-Type headers
   - Cache-Control headers
5. Test different pages and API endpoints

## Automated Testing

Consider creating automated tests using PHPUnit:

```php
<?php
class RouterTest extends TestCase
{
    public function testHomepageServed()
    {
        $_SERVER['REQUEST_URI'] = '/';
        // Execute router and assert response
    }
    
    public function testStaticFileServed()
    {
        $_SERVER['REQUEST_URI'] = '/assets/css/main.css';
        // Execute router and assert Content-Type
    }
    
    public function testApiRouted()
    {
        $_SERVER['REQUEST_URI'] = '/api/services';
        // Execute router and assert routing
    }
    
    public function testDirectoryTraversalBlocked()
    {
        $_SERVER['REQUEST_URI'] = '/../../../etc/passwd';
        // Execute router and assert 404
    }
}
```

## Expected Behavior Summary

| Request Type | Behavior | Handler |
|--------------|----------|---------|
| `/` | Serve index.html | Static |
| `/*.html` | Serve HTML file | Static |
| `/assets/*` | Serve static asset | Static |
| `/api/*` | Route to API | `/api/index.php` |
| `/admin/*` | Route to admin | `/admin/index.php` |
| `/sitemap.xml` | Route to API | `/api/index.php` |
| `/robots.txt` | Route to API | `/api/index.php` |
| `/*.php` | Block (404) | Security |
| `/../*` | Block (404) | Security |
| Non-existent | 404 page | Error handler |

## Performance Considerations

1. **Static Files**: Served directly with minimal overhead
2. **Caching**: Proper cache headers reduce server load
3. **Early Exit**: Routing logic exits as soon as match is found
4. **No Database**: Static file routing doesn't touch database
5. **realpath()**: Security check has minimal performance impact

## Troubleshooting

### Issue: Static files return 404
- Check file permissions: `chmod 644 /path/to/file`
- Verify file exists in `/public_html/`
- Check nginx error logs

### Issue: API returns HTML instead of JSON
- Verify `/api/index.php` exists and is executable
- Check bootstrap.php is accessible
- Review API error logs in `/logs/`

### Issue: Admin panel not accessible
- Verify `/admin/index.php` exists
- Check session configuration
- Review admin error logs

### Issue: Wrong Content-Type
- Check MIME type mapping in `serveStaticFile()`
- Add missing extension to `$mimeTypes` array
- Clear browser cache

## Security Notes

1. **Directory Traversal**: `realpath()` prevents access to files outside public_html
2. **PHP Files**: PHP files in public_html are blocked from direct access
3. **Hidden Files**: Files starting with `.` outside public_html are inaccessible
4. **Method Handling**: All HTTP methods are supported and passed to handlers
5. **Input Validation**: API and admin handlers validate all input

## Related Files

- `/public_html/index.php` - Main router (this implementation)
- `/api/index.php` - API handler
- `/admin/index.php` - Admin handler
- `/public_html/404.html` - 404 error page
- `/public_html/500.html` - 500 error page (for nginx errors)
