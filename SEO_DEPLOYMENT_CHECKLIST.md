# SEO Deployment Checklist

## Pre-Deployment Setup

### 1. Database Configuration
- [ ] Run database migrations: `php database/migrate.php`
- [ ] Seed SEO settings: `php database/seeds/SeoSettingsSeed.php`
- [ ] Verify site_settings table has SEO data:
  ```sql
  SELECT setting_key, setting_value FROM site_settings WHERE group_name IN ('seo', 'contact', 'business', 'social');
  ```

### 2. Environment Configuration
- [ ] Copy `.env.example` to `.env`
- [ ] Set `SITE_URL` to your domain (e.g., `https://yourdomain.com`)
- [ ] Set `CDN_URL` if using a CDN (optional)
- [ ] Verify all other environment variables are set
- [ ] Test database connection

### 3. Update Site Settings
Update the following in your admin panel or database:

**Required:**
- [ ] `site_name` - Your business name
- [ ] `site_description` - Main description (150-160 chars)
- [ ] `site_url` - Full domain URL
- [ ] `contact_email` - Business email
- [ ] `contact_phone` - Business phone

**Recommended:**
- [ ] `site_logo` - Logo URL (min 1200x630px for social sharing)
- [ ] `site_keywords` - Main keywords
- [ ] Business address fields (street, city, state, zip, country)
- [ ] Business coordinates (latitude, longitude) for local SEO
- [ ] Business hours (Schema.org format: "Mo-Fr 09:00-17:00")
- [ ] Social media profile URLs
- [ ] Twitter handle

### 4. Image Preparation
- [ ] Upload a logo image (minimum 1200x630px)
- [ ] Generate WebP versions of key images
- [ ] Create responsive image sets for hero images
- [ ] Compress all images (TinyPNG, ImageOptim, etc.)

### 5. Apache/Web Server Setup
Enable required Apache modules:
```bash
sudo a2enmod deflate
sudo a2enmod expires
sudo a2enmod headers
sudo a2enmod rewrite
sudo a2enmod brotli  # Optional but recommended
sudo systemctl restart apache2
```

Or for shared hosting, verify with your provider that these are enabled.

## Deployment Steps

### 1. Upload Files
- [ ] Upload all project files to server
- [ ] Ensure `.htaccess` is uploaded and readable
- [ ] Set proper file permissions (644 for files, 755 for directories)
- [ ] Create writable directories:
  - `logs/` (755 or 775)
  - `uploads/` (755 or 775)

### 2. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm install --production
npm run build
```

### 3. Configure Database
- [ ] Import database schema
- [ ] Run migrations
- [ ] Seed initial data including SEO settings

### 4. SSL Certificate
- [ ] Install SSL certificate (Let's Encrypt, commercial, etc.)
- [ ] Verify HTTPS works
- [ ] Uncomment HTTPS redirect in `.htaccess`:
  ```apache
  <IfModule mod_rewrite.c>
      RewriteEngine On
      RewriteCond %{HTTPS} off
      RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
  </IfModule>
  ```

## Post-Deployment Testing

### 1. Basic Functionality
- [ ] Homepage loads correctly
- [ ] All menu links work
- [ ] Forms submit successfully
- [ ] Images load properly
- [ ] JavaScript functions work

### 2. SEO Elements
- [ ] View page source and verify meta tags present
- [ ] Check canonical URLs are correct
- [ ] Verify breadcrumb structured data
- [ ] Test sitemap: `https://yourdomain.com/sitemap.xml`
- [ ] Test robots.txt: `https://yourdomain.com/robots.txt`

### 3. Performance Testing
- [ ] Google PageSpeed Insights: https://pagespeed.web.dev/
  - Target: 90+ desktop, 80+ mobile
- [ ] GTmetrix: https://gtmetrix.com/
  - Target: Grade A
- [ ] WebPageTest: https://www.webpagetest.org/
  - Check waterfall, compression, caching

### 4. Mobile Testing
- [ ] Google Mobile-Friendly Test: https://search.google.com/test/mobile-friendly
- [ ] Test on actual mobile devices (iOS, Android)
- [ ] Verify responsive images load correctly
- [ ] Check touch targets are adequate size

### 5. Structured Data Validation
- [ ] Google Rich Results Test: https://search.google.com/test/rich-results
  - Test homepage
  - Test services page
  - Test calculator page
  - Test contact page
- [ ] Verify no errors or critical warnings
- [ ] Check all required fields are populated

### 6. Social Sharing Preview
- [ ] Facebook Debugger: https://developers.facebook.com/tools/debug/
  - Test homepage and key pages
  - Verify images appear correctly (1200x630px)
  - Check title and description
- [ ] LinkedIn Post Inspector: https://www.linkedin.com/post-inspector/
- [ ] Twitter Card Validator: https://cards-dev.twitter.com/validator

### 7. Browser Testing
Test in major browsers:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

## Search Engine Setup

### 1. Google Search Console
- [ ] Add property: https://search.google.com/search-console
- [ ] Verify ownership (HTML file, DNS, or meta tag)
- [ ] Submit sitemap: `https://yourdomain.com/sitemap.xml`
- [ ] Check for crawl errors
- [ ] Set up email notifications for issues

### 2. Bing Webmaster Tools
- [ ] Add site: https://www.bing.com/webmasters
- [ ] Verify ownership
- [ ] Submit sitemap
- [ ] Configure settings

### 3. Google Analytics (Optional)
- [ ] Create GA4 property
- [ ] Install tracking code
- [ ] Set up goals and conversions
- [ ] Link to Search Console

### 4. Google Business Profile (if applicable)
- [ ] Claim/create business profile
- [ ] Verify business information
- [ ] Add photos and description
- [ ] Link to website

## Monitoring Setup

### 1. Uptime Monitoring
Set up monitoring with:
- [ ] UptimeRobot (free): https://uptimerobot.com/
- [ ] Pingdom: https://www.pingdom.com/
- [ ] StatusCake: https://www.statuscake.com/
- Or your hosting provider's monitoring

### 2. Error Tracking
- [ ] Set up error logging and monitoring
- [ ] Configure admin email notifications
- [ ] Test error pages (404, 500)

### 3. Performance Monitoring
- [ ] Set up regular performance audits
- [ ] Monitor Core Web Vitals in Search Console
- [ ] Track page load times

### 4. Search Rankings
Consider tracking with:
- [ ] Google Search Console (free)
- [ ] Ahrefs
- [ ] SEMrush
- [ ] Moz

## Week 1 Post-Launch

### Daily Checks
- [ ] Monitor server errors
- [ ] Check contact form submissions
- [ ] Review analytics for traffic spikes
- [ ] Monitor Core Web Vitals

### Day 3
- [ ] Check Search Console for crawl errors
- [ ] Verify sitemap has been processed
- [ ] Check indexation status

### Day 7
- [ ] Review first week of analytics
- [ ] Check for 404 errors and fix
- [ ] Verify all pages being crawled
- [ ] Review search queries in Search Console

## Month 1 Post-Launch

### Week 2
- [ ] Submit to relevant directories (if applicable)
- [ ] Set up Google Business Posts (if applicable)
- [ ] Review and optimize slow pages

### Week 3
- [ ] Analyze user behavior in analytics
- [ ] Identify pages with high bounce rates
- [ ] Check for broken links
- [ ] Review meta descriptions for improvement

### Week 4
- [ ] Full SEO audit using Screaming Frog or similar
- [ ] Review and update content as needed
- [ ] Check backlink profile
- [ ] Analyze competitor rankings

## Ongoing Maintenance

### Weekly
- [ ] Check for crawl errors in Search Console
- [ ] Monitor uptime and performance
- [ ] Review analytics for anomalies

### Bi-Weekly
- [ ] Check for broken links
- [ ] Review search rankings
- [ ] Update content if needed

### Monthly
- [ ] Full performance audit
- [ ] Review and update meta descriptions
- [ ] Check Core Web Vitals
- [ ] Analyze search queries and optimize
- [ ] Review and respond to user feedback

### Quarterly
- [ ] Comprehensive SEO audit
- [ ] Content refresh and updates
- [ ] Competitor analysis
- [ ] Strategy review and adjustments

### Yearly
- [ ] Full site audit
- [ ] Technology stack review
- [ ] SEO strategy overhaul
- [ ] Major content updates

## Common Issues & Solutions

### Issue: Sitemap not loading
**Solution:** 
- Check .htaccess rewrite rules
- Verify API route is working
- Check database connection
- Review error logs

### Issue: Robots.txt blocked
**Solution:**
- Clear CDN cache if using one
- Check file permissions
- Verify .htaccess redirects work

### Issue: Meta tags not updating
**Solution:**
- Clear browser cache
- Check HTML source (not rendered)
- Purge CDN cache if applicable
- Verify database settings

### Issue: Poor PageSpeed score
**Solution:**
- Enable compression (check .htaccess)
- Optimize images (WebP, compression)
- Verify caching headers
- Check for render-blocking resources

### Issue: Structured data errors
**Solution:**
- Validate JSON-LD syntax
- Check all required fields populated
- Use Rich Results Test for details
- Review Schema.org specifications

## Success Metrics

### SEO KPIs to Track
- Organic traffic growth
- Search rankings for target keywords
- Click-through rate (CTR) from search
- Pages indexed by search engines
- Backlinks acquired
- Domain authority/rating
- Core Web Vitals scores

### Performance KPIs
- Page load time (< 3 seconds)
- Time to Interactive (TTI) (< 5 seconds)
- First Contentful Paint (FCP) (< 1.8 seconds)
- Largest Contentful Paint (LCP) (< 2.5 seconds)
- Cumulative Layout Shift (CLS) (< 0.1)
- First Input Delay (FID) (< 100ms)

### Business KPIs
- Lead generation (contact form submissions)
- Calculator usage
- Quote requests
- Conversion rate
- Bounce rate
- Average session duration
- Pages per session

## Resources

### Documentation
- SEO_GUIDE.md - Comprehensive SEO guide
- SEO_IMPLEMENTATION_SUMMARY.md - Implementation details
- ADMIN_API.md - API documentation

### Tools Used
- Google Search Console
- Google PageSpeed Insights
- Google Rich Results Test
- Facebook Sharing Debugger
- Twitter Card Validator

### Support
- Stack Overflow: https://stackoverflow.com/
- Google Search Central Community: https://support.google.com/webmasters/community
- Schema.org Documentation: https://schema.org/

---

**Note:** This checklist should be completed in order. Mark items as done as you progress. Keep this document updated with your specific deployment notes and customizations.

**Last Updated:** [Date]
**Completed By:** [Name]
**Deployment Date:** [Date]
