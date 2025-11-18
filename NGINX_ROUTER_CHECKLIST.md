# nginx Router Deployment Checklist

Quick checklist for deploying the PHP-based nginx router.

## Pre-Deployment

- [ ] Backup current `/public_html/` directory
- [ ] Review `/NGINX_ROUTER_README.md` documentation
- [ ] Review `/NGINX_ROUTER_DEPLOYMENT.md` deployment guide
- [ ] Verify PHP 8.2+ is available on server
- [ ] Verify PHP-FPM is running

## File Upload

- [ ] Upload `/public_html/index.php` to server
- [ ] Verify file uploaded successfully: `ls -la public_html/index.php`
- [ ] Set correct permissions: `chmod 644 public_html/index.php`

## Configuration (Optional)

If you have nginx access:
- [ ] Update nginx server block with `try_files` directive
- [ ] Test nginx configuration: `sudo nginx -t`
- [ ] Reload nginx: `sudo systemctl reload nginx`

If you don't have nginx access:
- [ ] Skip - router will work with default shared hosting configuration

## Testing

### Basic Tests
- [ ] **Homepage**: Visit `http://3dprint-omsk.ru/` - should show homepage
- [ ] **About Page**: Visit `http://3dprint-omsk.ru/about.html` - should load
- [ ] **CSS**: Check browser DevTools - CSS files should load (200 status)
- [ ] **JavaScript**: Check browser console - no JS errors
- [ ] **Images**: Verify images display correctly

### API Tests
- [ ] **Services API**: `curl http://3dprint-omsk.ru/api/services` - should return JSON
- [ ] **Materials API**: `curl http://3dprint-omsk.ru/api/materials` - should return JSON
- [ ] **Health Check**: `curl http://3dprint-omsk.ru/api/health` - should return status

### Admin Tests
- [ ] **Admin Login**: Visit `http://3dprint-omsk.ru/admin/login` - should show login page
- [ ] **Admin Dashboard**: Login and check dashboard loads

### SEO Tests
- [ ] **Sitemap**: Visit `http://3dprint-omsk.ru/sitemap.xml` - should show XML
- [ ] **Robots**: Visit `http://3dprint-omsk.ru/robots.txt` - should show robots file

### Error Handling
- [ ] **404 Page**: Visit `http://3dprint-omsk.ru/nonexistent` - should show 404 page
- [ ] **Invalid URL**: Try `http://3dprint-omsk.ru/../../../etc/passwd` - should get 404

## Verification

### Headers Check
```bash
# Check CSS Content-Type
curl -I http://3dprint-omsk.ru/assets/css/main.css | grep -i content-type
# Should be: Content-Type: text/css; charset=UTF-8

# Check cache headers
curl -I http://3dprint-omsk.ru/assets/css/main.css | grep -i cache
# Should have: Cache-Control: public, max-age=31536000, immutable
```

### Response Status
```bash
# Check homepage
curl -I http://3dprint-omsk.ru/ | grep HTTP
# Should be: HTTP/1.1 200 OK

# Check API
curl -I http://3dprint-omsk.ru/api/services | grep HTTP
# Should be: HTTP/1.1 200 OK

# Check 404
curl -I http://3dprint-omsk.ru/nonexistent | grep HTTP
# Should be: HTTP/1.1 404 Not Found
```

## Monitoring (First 24 Hours)

- [ ] Monitor error logs: `tail -f /var/log/php8.2-fpm.log`
- [ ] Monitor nginx logs: `tail -f /var/log/nginx/error.log`
- [ ] Check application logs: `tail -f logs/error.log`
- [ ] Monitor server load: `top` or `htop`
- [ ] Check disk space: `df -h`

## Post-Deployment

- [ ] Test from different devices (desktop, mobile, tablet)
- [ ] Test from different browsers (Chrome, Firefox, Safari, Edge)
- [ ] Test from different networks (home, mobile, VPN)
- [ ] Verify SSL certificate works (if HTTPS)
- [ ] Test all main user flows:
  - [ ] Submit cost estimate
  - [ ] Contact form
  - [ ] Gallery browsing
  - [ ] News reading
  - [ ] Service pages
- [ ] Update documentation if any issues found

## Rollback Plan

If deployment fails:

1. **Option 1: Revert index.php**
   ```bash
   mv public_html/index.php public_html/index.php.new
   # Restore old setup if you have a backup
   ```

2. **Option 2: Rename index.php**
   ```bash
   mv public_html/index.php public_html/router.php
   # This makes nginx skip to index.html
   ```

3. **Option 3: Contact hosting support**
   - Provide error logs
   - Request nginx configuration check

## Success Criteria

Deployment is successful when ALL of these are true:

✅ Homepage loads correctly  
✅ All static pages accessible (about, contact, services, etc.)  
✅ CSS styling applied correctly  
✅ JavaScript functionality works  
✅ Images display properly  
✅ API endpoints return JSON responses  
✅ Admin panel accessible and functional  
✅ Sitemap.xml accessible  
✅ Robots.txt accessible  
✅ 404 page displays for non-existent URLs  
✅ No errors in PHP/nginx logs  
✅ Response times acceptable (< 2 seconds)  
✅ All HTTP methods work (GET, POST, PUT, DELETE)  

## Troubleshooting Quick Reference

| Issue | Quick Fix |
|-------|-----------|
| Blank page | Check PHP error logs |
| 404 for all pages | Verify index.php exists and nginx config |
| CSS not loading | Check file permissions and Content-Type |
| API returns HTML | Verify /api/index.php exists |
| Admin not working | Check /admin/index.php and sessions |
| Slow performance | Enable OPcache and gzip |

## Support Resources

- **Documentation**: `/NGINX_ROUTER_README.md`
- **Deployment Guide**: `/NGINX_ROUTER_DEPLOYMENT.md`
- **Test Cases**: `/test-router-logic.md`
- **Implementation Details**: `/NGINX_ROUTER_IMPLEMENTATION.md`

## Final Steps

- [ ] Document any deployment issues encountered
- [ ] Update internal documentation if needed
- [ ] Notify team that deployment is complete
- [ ] Schedule follow-up check in 1 week

---

**Date Deployed**: _______________  
**Deployed By**: _______________  
**Server**: 3dprint-omsk.ru  
**PHP Version**: _______________  
**nginx Version**: _______________  
**Issues Encountered**: _______________  
**Resolution Time**: _______________  

---

## Notes

Use this space to document any specific issues or observations:

```
[Your notes here]
```
