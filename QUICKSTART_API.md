# Public API Quick Start Guide

Get started with the public API in 5 minutes.

## Step 1: Configure Environment

Copy and edit your `.env` file:

```bash
cp .env.example .env
nano .env
```

Add your CAPTCHA keys:

```env
# For reCAPTCHA
CAPTCHA_TYPE=recaptcha
RECAPTCHA_SITE_KEY=your-site-key-here
RECAPTCHA_SECRET=your-secret-key-here

# For hCaptcha (alternative)
CAPTCHA_TYPE=hcaptcha
HCAPTCHA_SITE_KEY=your-site-key-here
HCAPTCHA_SECRET=your-secret-key-here

# Admin email for notifications
ADMIN_EMAIL=admin@example.com
```

Get CAPTCHA keys:
- reCAPTCHA: https://www.google.com/recaptcha/admin
- hCaptcha: https://dashboard.hcaptcha.com/signup

## Step 2: Start the Server

```bash
php -S localhost:8000 -t public_html
```

Or use the provided script:
```bash
./dev-server.sh
```

## Step 3: Test the API

### Test GET Endpoints

```bash
# Health check
curl http://localhost:8000/api/health

# List services
curl http://localhost:8000/api/services

# Get single service
curl http://localhost:8000/api/services/1

# List materials
curl http://localhost:8000/api/materials

# List gallery items
curl http://localhost:8000/api/gallery

# List news
curl http://localhost:8000/api/news

# Get public settings
curl http://localhost:8000/api/settings
```

### Test with Browser

Open the interactive example:
```
http://localhost:8000/api-example.html
```

## Step 4: Integrate with Frontend

### JavaScript Example

```javascript
// Fetch services
async function getServices() {
    const response = await fetch('/api/services?page=1&per_page=10');
    const data = await response.json();
    
    if (data.success) {
        console.log('Services:', data.data.data);
        console.log('Total:', data.data.pagination.total);
    }
}

// Submit contact request
async function submitContact(formData) {
    // Get CSRF token
    const csrfResponse = await fetch('/api/csrf-token');
    const csrfData = await csrfResponse.json();
    const csrfToken = csrfData.data.csrf_token;
    
    // Get CAPTCHA token (example with reCAPTCHA v3)
    const captchaToken = await grecaptcha.execute('YOUR_SITE_KEY', {
        action: 'submit'
    });
    
    // Submit form
    const response = await fetch('/api/contact', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            customer_name: formData.name,
            customer_email: formData.email,
            subject: formData.subject,
            message: formData.message,
            captcha_token: captchaToken,
            csrf_token: csrfToken
        })
    });
    
    const result = await response.json();
    
    if (result.success) {
        console.log('Request submitted:', result.data.request_number);
    } else {
        console.error('Error:', result.message);
    }
}
```

### HTML Form Example

```html
<!DOCTYPE html>
<html>
<head>
    <title>Contact Form</title>
    <script src="https://www.google.com/recaptcha/api.js"></script>
</head>
<body>
    <form id="contactForm">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="subject" placeholder="Subject" required>
        <textarea name="message" placeholder="Message" required></textarea>
        
        <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>
        
        <button type="submit">Submit</button>
    </form>
    
    <script>
        document.getElementById('contactForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const captchaResponse = grecaptcha.getResponse();
            
            if (!captchaResponse) {
                alert('Please complete the CAPTCHA');
                return;
            }
            
            // Get CSRF token
            const csrfResponse = await fetch('/api/csrf-token');
            const csrfData = await csrfResponse.json();
            
            // Submit
            const response = await fetch('/api/contact', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    customer_name: formData.get('name'),
                    customer_email: formData.get('email'),
                    subject: formData.get('subject'),
                    message: formData.get('message'),
                    captcha_token: captchaResponse,
                    csrf_token: csrfData.data.csrf_token
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Thank you! Request #' + result.data.request_number);
                e.target.reset();
                grecaptcha.reset();
            } else {
                alert('Error: ' + result.message);
            }
        });
    </script>
</body>
</html>
```

## Common Queries

### Pagination

```javascript
// Get page 2 with 20 items
fetch('/api/services?page=2&per_page=20')
```

### Filtering

```javascript
// Get featured services only
fetch('/api/services?featured=true')

// Get services by category
fetch('/api/services?category=1')

// Get materials by category
fetch('/api/materials?category=Metals')

// Get gallery by service
fetch('/api/gallery?service_id=1')
```

### Searching

```javascript
// Search services
fetch('/api/services?search=cnc')

// Search materials
fetch('/api/materials?search=aluminum')

// Search news
fetch('/api/news?search=manufacturing')
```

### Combining Filters

```javascript
// Featured services in category 1
fetch('/api/services?category=1&featured=true&per_page=5')

// Materials search in category
fetch('/api/materials?category=Metals&search=aluminum')
```

## Response Format

### Success Response

```json
{
    "success": true,
    "message": "Success",
    "data": {
        "data": [...],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 100,
            "last_page": 5,
            "from": 1,
            "to": 20
        }
    }
}
```

### Error Response

```json
{
    "error": true,
    "message": "Validation failed",
    "errors": {
        "customer_email": [
            "The customer_email must be a valid email address"
        ]
    }
}
```

## Error Handling

```javascript
async function fetchWithErrorHandling(url) {
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.error) {
            // Handle API error
            console.error('API Error:', data.message);
            if (data.errors) {
                // Handle validation errors
                Object.entries(data.errors).forEach(([field, messages]) => {
                    console.error(`${field}:`, messages.join(', '));
                });
            }
            return null;
        }
        
        return data.data;
        
    } catch (error) {
        // Handle network error
        console.error('Network Error:', error.message);
        return null;
    }
}
```

## Rate Limiting

The API is rate-limited to 100 requests per hour per IP address by default.

When rate limit is exceeded, you'll get:

```json
{
    "error": true,
    "message": "Too many requests. Please try again later.",
    "errors": null
}
```

Status code: `429 Too Many Requests`

## Caching

GET endpoints include cache headers:

- Services: 5 minutes
- Materials: 10 minutes  
- Gallery: 10 minutes
- News: 5 minutes
- Settings: 1 hour

Use conditional requests for efficiency:

```javascript
fetch('/api/services', {
    headers: {
        'If-None-Match': etag,
        'If-Modified-Since': lastModified
    }
});
```

## Next Steps

1. **Read Full Documentation**: [API_PUBLIC.md](API_PUBLIC.md)
2. **Review OpenAPI Spec**: [openapi.yaml](openapi.yaml)
3. **Check Examples**: Open `api-example.html` in browser
4. **Test Endpoints**: Use cURL or Postman
5. **Integrate CAPTCHA**: Setup reCAPTCHA or hCaptcha
6. **Configure Email**: Test notification emails
7. **Deploy**: Follow deployment checklist in [API_README.md](API_README.md)

## Troubleshooting

### CAPTCHA not working

- Check your site key and secret key
- Verify CAPTCHA_TYPE matches your provider
- Test with real CAPTCHA token (not "DEMO_TOKEN")

### Email not sending

- Check SMTP configuration in `.env`
- Verify admin email in site_settings table
- Check logs: `logs/app.log`

### CORS errors

- Add your frontend domain to `CORS_ALLOWED_ORIGINS`
- Ensure proper headers are allowed
- Test with `curl -H "Origin: http://yourdomain.com"`

### Rate limit issues

- Adjust `API_RATE_LIMIT` in `.env`
- Clear rate limit cache: `rm -rf logs/rate_limit/*`
- Check IP address detection

## Support

- Documentation: [API_PUBLIC.md](API_PUBLIC.md)
- OpenAPI Spec: [openapi.yaml](openapi.yaml)
- Issues: Check `logs/app.log`
- Questions: Contact admin@example.com
