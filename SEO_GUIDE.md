# SEO Optimization Guide

## Overview
This document provides comprehensive guidelines for SEO implementation and verification for the Manufacturing Platform.

## Implemented SEO Features

### 1. Meta Tags
All pages now include:
- **Title tags**: Unique, descriptive titles for each page (50-60 characters)
- **Meta descriptions**: Compelling descriptions for search results (150-160 characters)
- **Meta keywords**: Relevant keywords for page content
- **Canonical URLs**: Prevent duplicate content issues
- **Viewport meta**: Mobile-responsive design support

### 2. Open Graph Tags (Social Media)
Facebook, LinkedIn, and other platforms will properly display:
- `og:title` - Page title
- `og:description` - Page description
- `og:type` - Content type (website, article, etc.)
- `og:url` - Canonical URL
- `og:site_name` - Site name
- `og:image` - Share image (when configured)
- `og:locale` - Language locale

### 3. Twitter Card Tags
Twitter-specific meta tags for enhanced sharing:
- `twitter:card` - Card type (summary_large_image)
- `twitter:title` - Tweet title
- `twitter:description` - Tweet description
- `twitter:image` - Share image
- `twitter:site` - Twitter handle (when configured)

### 4. Structured Data (Schema.org JSON-LD)

#### Organization Schema
Present on all pages, includes:
- Organization name and description
- Logo and contact information
- Social media profiles
- Business address and coordinates

#### LocalBusiness Schema
For pages showcasing local services:
- Business hours
- Geographic coordinates
- Price range
- Service area

#### BreadcrumbList Schema
Navigation breadcrumbs on all sub-pages:
- Hierarchical page structure
- Improved navigation in search results

#### Service Schema
On the calculator and services pages:
- Service type and description
- Provider information
- Availability and service channels

#### WebSite Schema
Home page includes:
- Site name and description
- Site URL

### 5. Sitemap.xml
Auto-generated sitemap accessible at `/sitemap.xml`:
- Static pages (home, services, materials, etc.)
- Dynamic content (services, materials, news, gallery items)
- Last modification dates
- Change frequency hints
- Priority indicators

**Update frequency**: Regenerated on each request (consider caching in production)

### 6. Robots.txt
Auto-generated robots.txt accessible at `/robots.txt`:
- Allow crawling of public content
- Disallow admin, API, logs, and private directories
- Sitemap reference for search engines

### 7. Performance Optimizations

#### Compression
- **Gzip**: Enabled for text resources
- **Brotli**: Enabled if available (higher compression)

#### Browser Caching
- Images: 1 year cache
- CSS/JS: 1 month cache
- Fonts: 1 year cache with immutable flag
- HTML: No-cache (always fresh)

#### Resource Hints
- **Preconnect**: Early connection to font providers
- **DNS-prefetch**: Resolve domains early

#### JavaScript Optimization
- **defer**: All scripts use defer attribute
- **Module scripts**: ES6 modules for modern browsers
- Non-blocking script loading

#### Image Optimization
- **WebP support**: Automatic WebP serving when supported
- **Lazy loading**: Images load as needed (implement in templates)
- **Responsive images**: srcset for different screen sizes
- **Picture element**: Modern image delivery

### 8. Security Headers
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`

## Configuration

### Site Settings
Configure SEO settings in the `site_settings` database table:

```sql
-- Basic Settings
site_name               - Your site name
site_description        - Main site description
site_keywords          - Default keywords
site_url               - Base URL (e.g., https://example.com)
site_logo              - Logo URL for OG/schema

-- Contact Information
contact_email          - Business email
contact_phone          - Business phone

-- Business Information
business_address       - Full address
business_street        - Street address
business_city          - City
business_state         - State/region
business_zip           - ZIP/postal code
business_country       - Country (e.g., US)
business_latitude      - GPS latitude
business_longitude     - GPS longitude
business_hours         - Opening hours (e.g., Mo-Fr 09:00-17:00)
business_price_range   - Price indicator (e.g., $$)

-- Social Media
social_facebook        - Facebook profile URL
social_twitter         - Twitter profile URL
social_linkedin        - LinkedIn profile URL
social_instagram       - Instagram profile URL
twitter_handle         - Twitter @handle
```

### Environment Variables
Add to `.env` file:
```
SITE_URL=https://yourdomain.com
CDN_URL=https://cdn.yourdomain.com  # Optional
```

## SEO Checklist

### Pre-Launch
- [ ] Configure all site_settings in database
- [ ] Set SITE_URL in .env file
- [ ] Upload site logo and ensure it's at least 1200x630px
- [ ] Configure social media profiles in settings
- [ ] Add business address and coordinates
- [ ] Set up Google Search Console
- [ ] Set up Bing Webmaster Tools
- [ ] Create and submit sitemap
- [ ] Verify robots.txt is accessible

### Content Optimization
- [ ] Each page has unique title (50-60 chars)
- [ ] Each page has unique description (150-160 chars)
- [ ] Images have descriptive alt text
- [ ] Headings follow hierarchy (H1 -> H2 -> H3)
- [ ] Internal linking structure is logical
- [ ] URLs are clean and descriptive
- [ ] Content is original and valuable

### Technical SEO
- [ ] Site loads in under 3 seconds
- [ ] Mobile-friendly (responsive design)
- [ ] HTTPS enabled
- [ ] No broken links (404 errors)
- [ ] XML sitemap is valid
- [ ] Robots.txt is properly configured
- [ ] Structured data validates (Google Rich Results Test)
- [ ] Core Web Vitals pass (LCP, FID, CLS)

### Ongoing Monitoring
- [ ] Monitor search rankings weekly
- [ ] Check Google Search Console for errors
- [ ] Review analytics monthly
- [ ] Update content regularly
- [ ] Monitor page speed
- [ ] Check for crawl errors
- [ ] Update sitemap when adding pages

## Testing and Verification

### 1. Meta Tags
```bash
curl -I https://yourdomain.com
```
Check for proper headers and meta tags in HTML.

### 2. Structured Data
Test with Google's Rich Results Test:
- https://search.google.com/test/rich-results
- Enter your page URLs
- Fix any errors or warnings

### 3. Sitemap
Verify sitemap is accessible:
- Visit: https://yourdomain.com/sitemap.xml
- Validate: https://www.xml-sitemaps.com/validate-xml-sitemap.html
- Submit to Google Search Console

### 4. Robots.txt
Check robots.txt:
- Visit: https://yourdomain.com/robots.txt
- Test in Google Search Console's robots.txt Tester

### 5. Mobile-Friendly Test
- https://search.google.com/test/mobile-friendly

### 6. Page Speed
Test with:
- Google PageSpeed Insights: https://pagespeed.web.dev/
- GTmetrix: https://gtmetrix.com/
- WebPageTest: https://www.webpagetest.org/

**Target Scores:**
- PageSpeed: 90+ (Desktop), 80+ (Mobile)
- GTmetrix: Grade A
- Core Web Vitals: All metrics in "Good" range

### 7. Open Graph Preview
Test social sharing:
- Facebook: https://developers.facebook.com/tools/debug/
- LinkedIn: https://www.linkedin.com/post-inspector/
- Twitter: https://cards-dev.twitter.com/validator

## Advanced Optimizations

### Image Optimization
1. **Generate WebP versions:**
```php
use App\Helpers\ImageOptimizer;

// Generate WebP from existing image
ImageOptimizer::generateWebP('/path/to/image.jpg');

// Generate responsive image set
ImageOptimizer::generateResponsiveImages('/path/to/image.jpg', [320, 640, 1024, 1920]);

// Use picture element in templates
echo ImageOptimizer::pictureElement('/path/to/image.jpg', 'Alt text', ['loading' => 'lazy']);
```

2. **Lazy Loading:**
Add to images:
```html
<img src="image.jpg" alt="Description" loading="lazy">
```

### Critical CSS
Inline critical above-the-fold CSS in `<head>`:
```html
<style>
/* Critical CSS here */
</style>
```

### Preload Key Resources
Add to pages with specific large resources:
```html
<link rel="preload" as="image" href="/hero-image.jpg">
<link rel="preload" as="font" href="/fonts/main.woff2" type="font/woff2" crossorigin>
```

### Service Worker (PWA)
Consider implementing for offline capability and faster repeat visits.

## Common Issues and Solutions

### Issue: Sitemap not updating
**Solution**: Sitemap regenerates on each request. If using caching, clear cache or implement cache invalidation.

### Issue: Schema errors in Rich Results Test
**Solution**: Validate JSON-LD syntax, ensure all required fields are present, check data types match schema.org specifications.

### Issue: Slow page load
**Solution**: 
1. Enable compression (gzip/brotli)
2. Optimize images (WebP, compression)
3. Defer JavaScript
4. Use CDN for static assets
5. Enable browser caching

### Issue: Duplicate content
**Solution**: Ensure canonical tags are present on all pages pointing to the preferred URL.

### Issue: Missing meta descriptions
**Solution**: Add unique descriptions to each page, don't duplicate across pages.

## Monitoring Tools

### Essential Tools
1. **Google Search Console** - Crawl errors, index status, search queries
2. **Google Analytics** - Traffic, user behavior, conversions
3. **Google PageSpeed Insights** - Performance metrics
4. **Bing Webmaster Tools** - Alternative search engine optimization

### Optional Tools
- **Ahrefs** - Backlink analysis, keyword research
- **SEMrush** - Comprehensive SEO toolkit
- **Screaming Frog** - Technical SEO crawling
- **GTmetrix** - Performance monitoring

## Best Practices

### Content
- Write for users first, search engines second
- Use natural language, avoid keyword stuffing
- Keep content fresh and updated
- Include internal and external links
- Use multimedia (images, videos)

### Technical
- Maintain fast load times (< 3 seconds)
- Ensure mobile responsiveness
- Fix broken links promptly
- Keep URLs clean and descriptive
- Use HTTPS everywhere

### Links
- Build quality backlinks
- Internal linking structure
- Use descriptive anchor text
- Avoid excessive outbound links

## Support and Resources

### Documentation
- Schema.org: https://schema.org/
- Google Search Central: https://developers.google.com/search
- Mozilla Web Docs: https://developer.mozilla.org/

### Validation Tools
- HTML Validator: https://validator.w3.org/
- CSS Validator: https://jigsaw.w3.org/css-validator/
- Rich Results Test: https://search.google.com/test/rich-results

## Changelog

### v1.0.0 (Initial Implementation)
- Dynamic meta tags system
- Open Graph and Twitter Cards
- Schema.org structured data
- Sitemap.xml generation
- Robots.txt generation
- Canonical URLs
- Performance optimizations (compression, caching, defer)
- Image optimization utilities
- SEO documentation

## Next Steps

1. **Implement PHP-based page rendering** to dynamically inject SEO data from database
2. **Add admin interface** for managing SEO settings per page
3. **Create image optimization pipeline** to auto-generate WebP and responsive versions on upload
4. **Implement caching** for sitemap and frequently accessed pages
5. **Add A/B testing** for meta descriptions and titles
6. **Monitor and iterate** based on search performance data
