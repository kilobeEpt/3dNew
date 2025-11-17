# Public API Implementation

This document provides an overview of the public API implementation.

## Overview

The public API provides REST endpoints for:
- **Reading data**: Services, materials, pricing rules, gallery items, news posts, and site settings
- **Submitting requests**: Cost estimate submissions and contact requests

## Features Implemented

### ✅ GET Endpoints

All GET endpoints include:
- JSON response format
- Pagination support (page, per_page parameters)
- Filtering capabilities
- Search functionality
- Cache headers for optimal performance
- Consistent error handling

**Endpoints:**
- `GET /api/services` - List services with pagination/filtering
- `GET /api/services/{id}` - Get single service by ID or slug
- `GET /api/materials` - List materials with pagination/filtering
- `GET /api/materials/{id}` - Get single material
- `GET /api/materials/categories` - List material categories
- `GET /api/pricing-rules` - List active pricing rules
- `GET /api/gallery` - List gallery items with pagination/filtering
- `GET /api/gallery/{id}` - Get single gallery item
- `GET /api/news` - List news posts with pagination/filtering/search
- `GET /api/news/{id}` - Get single news post by ID or slug
- `GET /api/settings` - Get public site settings

### ✅ POST Endpoints

Both POST endpoints include:
- Input validation and sanitization
- CAPTCHA verification (reCAPTCHA/hCaptcha)
- CSRF protection for same-origin requests
- Rate limiting
- Email notifications to admin
- Prepared statements for SQL injection protection
- Transaction support for data integrity
- Comprehensive error handling

**Endpoints:**
- `POST /api/cost-estimates` - Submit cost estimate request
- `POST /api/contact` - Submit contact/inquiry request

### ✅ Security Features

1. **CAPTCHA Integration**
   - Support for reCAPTCHA v2/v3 and hCaptcha
   - Configurable via environment variables
   - Server-side verification

2. **CSRF Protection**
   - Token-based protection for same-origin requests
   - Session-based token storage
   - Automatic validation via middleware

3. **Rate Limiting**
   - 100 requests per hour per IP (configurable)
   - Prevents abuse and spam
   - Automatic cleanup of expired rate limit data

4. **Input Sanitization**
   - Comprehensive validation rules
   - Type checking and length constraints
   - SQL injection prevention via prepared statements

5. **CORS Configuration**
   - Configurable allowed origins
   - Proper preflight handling
   - Production-ready defaults

### ✅ Email Notifications

1. **HTML Email Templates**
   - Professional, responsive design
   - Separate templates for cost estimates and contact requests
   - Template variables for dynamic content

2. **PHPMailer Integration**
   - SMTP support with authentication
   - Error handling and logging
   - Reply-to functionality for contact requests

3. **Configurable Recipients**
   - Admin email from site settings
   - Fallback to configured mail address
   - Support for multiple recipients

### ✅ Documentation

1. **OpenAPI Specification** (`openapi.yaml`)
   - Complete API specification
   - Request/response schemas
   - Status codes and error responses
   - Can be used with Swagger UI

2. **Markdown Documentation** (`API_PUBLIC.md`)
   - Comprehensive guide
   - Examples in multiple languages
   - Security considerations
   - Rate limiting details

3. **Interactive Example** (`public_html/api-example.html`)
   - Live API demonstration
   - Working code examples
   - Form validation examples

## Configuration

### Environment Variables

Add to `.env` file:

```env
# CAPTCHA Settings
CAPTCHA_TYPE=recaptcha
RECAPTCHA_SITE_KEY=your-recaptcha-site-key
RECAPTCHA_SECRET=your-recaptcha-secret-key
HCAPTCHA_SITE_KEY=your-hcaptcha-site-key
HCAPTCHA_SECRET=your-hcaptcha-secret-key

# Admin Notifications
ADMIN_EMAIL=admin@example.com

# Rate Limiting
API_RATE_LIMIT=100

# CORS
CORS_ALLOWED_ORIGINS=https://yourdomain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Csrf-Token
```

### Site Settings

Configure in database `site_settings` table:
- `admin_email` - Email address for notifications
- `site_name` - Used in email templates
- `tax_rate` - Default tax rate for estimates
- Other public settings accessible via API

## Usage Examples

### JavaScript (Fetch)

```javascript
// Fetch services
const response = await fetch('/api/services?page=1&per_page=10');
const data = await response.json();
console.log(data.data.data); // Array of services

// Submit contact with CAPTCHA and CSRF
const csrfToken = await fetch('/api/csrf-token')
    .then(r => r.json())
    .then(d => d.data.csrf_token);

const captchaToken = await grecaptcha.execute('SITE_KEY', {action: 'submit'});

await fetch('/api/contact', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        customer_name: 'John Doe',
        customer_email: 'john@example.com',
        subject: 'Inquiry',
        message: 'I need information...',
        captcha_token: captchaToken,
        csrf_token: csrfToken
    })
});
```

### cURL

```bash
# Get services
curl http://localhost:8000/api/services?page=1

# Submit contact request
curl -X POST http://localhost:8000/api/contact \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "subject": "Inquiry",
    "message": "I need more information",
    "captcha_token": "TOKEN",
    "csrf_token": "TOKEN"
  }'
```

## Testing

### Manual Testing

1. **Start development server:**
   ```bash
   php -S localhost:8000 -t public_html
   ```

2. **Test GET endpoints:**
   ```bash
   curl http://localhost:8000/api/health
   curl http://localhost:8000/api/services
   curl http://localhost:8000/api/materials
   ```

3. **Test with browser:**
   Open `http://localhost:8000/api-example.html`

### API Testing Tools

- **Postman**: Import `openapi.yaml` for automatic collection
- **Insomnia**: Supports OpenAPI import
- **Swagger UI**: Host the OpenAPI spec for interactive docs
- **cURL**: Command-line testing (examples in documentation)

## Architecture

### Controllers

Located in `src/Controllers/Api/`:
- `ServicesController.php` - Service endpoints
- `MaterialsController.php` - Material endpoints
- `PricingRulesController.php` - Pricing rule endpoints
- `GalleryController.php` - Gallery endpoints
- `NewsController.php` - News endpoints
- `SettingsController.php` - Settings endpoints
- `CostEstimatesController.php` - Cost estimate submissions
- `ContactController.php` - Contact request submissions
- `CsrfController.php` - CSRF token generation

### Middleware

Located in `src/Middleware/`:
- `CorsMiddleware.php` - CORS headers
- `RateLimitMiddleware.php` - Rate limiting
- `CsrfMiddleware.php` - CSRF token validation

### Helpers

Located in `src/Helpers/`:
- `Response.php` - Consistent API responses
- `Validator.php` - Input validation
- `Captcha.php` - CAPTCHA verification

### Email Templates

Located in `templates/email/`:
- `cost_estimate_notification.html` - Estimate notifications
- `contact_notification.html` - Contact notifications

## Database

All endpoints use:
- **Prepared statements** - SQL injection prevention
- **Soft deletes** - Data recovery capability
- **Transactions** - Data integrity for complex operations
- **Indexes** - Optimized query performance

## Logging

All operations are logged:
- Request submissions
- Email sending
- Errors and exceptions
- Rate limit violations

Logs location: `logs/app.log`

## Performance

### Caching

GET endpoints include cache headers:
- Services: 5 minutes
- Materials: 10 minutes
- Gallery: 10 minutes
- News: 5 minutes
- Settings: 1 hour

### Optimization

- Pagination limits (max 100 per page)
- Indexed database columns
- Efficient SQL queries
- Minimal data transfer

## Error Handling

All errors return consistent format:
```json
{
  "error": true,
  "message": "Error description",
  "errors": { /* field-specific errors */ }
}
```

Status codes:
- 200: Success
- 201: Created
- 400: Bad request
- 404: Not found
- 422: Validation failed
- 429: Rate limit exceeded
- 500: Server error

## Deployment

### Production Checklist

- [ ] Update CORS origins in `.env`
- [ ] Configure CAPTCHA keys
- [ ] Set admin email
- [ ] Enable HTTPS
- [ ] Review rate limits
- [ ] Test email delivery
- [ ] Monitor logs
- [ ] Setup log rotation

### Security Hardening

- Use HTTPS only
- Restrict CORS origins
- Enable rate limiting
- Regular security updates
- Monitor for suspicious activity
- Implement IP blacklisting if needed

## Support

For issues or questions:
1. Check logs: `logs/app.log`
2. Review documentation: `API_PUBLIC.md`
3. Check OpenAPI spec: `openapi.yaml`
4. Contact: admin@example.com

## License

See main project license.
