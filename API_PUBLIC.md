# Public API Documentation

Complete documentation for the public-facing REST API endpoints.

## Table of Contents

1. [Overview](#overview)
2. [Authentication & Security](#authentication--security)
3. [Response Format](#response-format)
4. [Pagination](#pagination)
5. [Endpoints](#endpoints)
6. [Error Handling](#error-handling)
7. [Rate Limiting](#rate-limiting)
8. [Examples](#examples)

## Overview

The Public API provides access to services, materials, pricing rules, gallery items, news posts, and site settings. It also allows submissions of cost estimate requests and contact inquiries.

### Base URL

```
http://localhost:8000/api
```

For production:
```
https://yourdomain.com/api
```

### Content Type

All requests and responses use `application/json` content type.

## Authentication & Security

### Public Endpoints

Most GET endpoints are public and do not require authentication. They include caching headers for optimal performance.

### CAPTCHA Protection

POST endpoints (cost estimates and contact requests) require CAPTCHA verification to prevent spam and abuse.

**Supported CAPTCHA providers:**
- Google reCAPTCHA v2/v3
- hCaptcha

**Configuration:**
```env
CAPTCHA_TYPE=recaptcha  # or hcaptcha
RECAPTCHA_SITE_KEY=your-site-key
RECAPTCHA_SECRET=your-secret-key
```

**Usage:**
Include the CAPTCHA response token in your POST request:
```json
{
  "captcha_token": "03AGdBq24PBCbwiDRdFRP...",
  ...
}
```

### CSRF Protection

Same-origin requests to POST endpoints require a CSRF token to prevent Cross-Site Request Forgery attacks.

**Get CSRF token:**
```
GET /api/csrf-token
```

**Include in request:**
```json
{
  "csrf_token": "a1b2c3d4e5f6...",
  ...
}
```

Or in header:
```
X-Csrf-Token: a1b2c3d4e5f6...
```

### CORS

Configure allowed origins in `.env`:
```env
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Csrf-Token
```

For development, use `*`:
```env
CORS_ALLOWED_ORIGINS=*
```

## Response Format

### Success Response

```json
{
  "success": true,
  "message": "Success",
  "data": { ... }
}
```

### Error Response

```json
{
  "error": true,
  "message": "Error message",
  "errors": { ... }
}
```

### Status Codes

- `200 OK` - Successful GET request
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

## Pagination

List endpoints support pagination via query parameters:

**Parameters:**
- `page` (integer, default: 1) - Page number
- `per_page` (integer, default: 20, max: 100) - Items per page

**Response format:**
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

## Endpoints

### Health Check

Check API health status.

```
GET /api/health
```

**Response:**
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

---

### Services

#### List Services

Retrieve paginated list of visible services.

```
GET /api/services
```

**Query Parameters:**
- `page` (integer) - Page number
- `per_page` (integer) - Items per page
- `category` (integer) - Filter by category ID
- `featured` (boolean) - Filter featured only
- `search` (string) - Search by name

**Cache:** 5 minutes (300 seconds)

**Example:**
```bash
curl http://localhost:8000/api/services?page=1&per_page=10&featured=true
```

#### Get Service

Retrieve single service by ID or slug.

```
GET /api/services/{id}
```

**Parameters:**
- `id` - Service ID (integer) or slug (string)

**Cache:** 5 minutes (300 seconds)

---

### Materials

#### List Materials

Retrieve paginated list of active materials.

```
GET /api/materials
```

**Query Parameters:**
- `page` (integer) - Page number
- `per_page` (integer) - Items per page
- `category` (string) - Filter by category name
- `search` (string) - Search by name

**Cache:** 10 minutes (600 seconds)

#### Get Material

Retrieve single material by ID.

```
GET /api/materials/{id}
```

#### Get Material Categories

Retrieve all unique material categories.

```
GET /api/materials/categories
```

---

### Pricing Rules

#### List Pricing Rules

Retrieve active pricing rules and presets.

```
GET /api/pricing-rules
```

**Query Parameters:**
- `type` (string) - Filter by rule type (discount, markup, custom)

**Cache:** 5 minutes (300 seconds)

---

### Gallery

#### List Gallery Items

Retrieve paginated list of visible gallery items.

```
GET /api/gallery
```

**Query Parameters:**
- `page` (integer) - Page number
- `per_page` (integer) - Items per page
- `category` (string) - Filter by category
- `featured` (boolean) - Filter featured only
- `service_id` (integer) - Filter by service

**Cache:** 10 minutes (600 seconds)

#### Get Gallery Item

Retrieve single gallery item and increment view count.

```
GET /api/gallery/{id}
```

---

### News

#### List News Posts

Retrieve paginated list of published news posts.

```
GET /api/news
```

**Query Parameters:**
- `page` (integer) - Page number
- `per_page` (integer) - Items per page
- `category` (string) - Filter by category
- `featured` (boolean) - Filter featured only
- `search` (string) - Search in title, excerpt, content

**Cache:** 5 minutes (300 seconds)

#### Get News Post

Retrieve single news post by ID or slug and increment view count.

```
GET /api/news/{id}
```

**Parameters:**
- `id` - Post ID (integer) or slug (string)

---

### Settings

#### Get Public Settings

Retrieve public site settings.

```
GET /api/settings
```

**Query Parameters:**
- `group` (string) - Filter by settings group (contact, general, etc.)

**Cache:** 1 hour (3600 seconds)

**Example Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "site_name": "Manufacturing Platform",
    "contact_email": "info@example.com",
    "tax_rate": 8.5,
    "currency": "USD"
  }
}
```

---

### Cost Estimate Submissions

#### Submit Cost Estimate Request

Submit a new cost estimate request.

```
POST /api/cost-estimates
```

**Request Body:**
```json
{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+1-555-0123",
  "title": "Custom CNC Machining Project",
  "description": "Need precision machining for aluminum parts",
  "items": [
    {
      "item_type": "service",
      "item_id": 1,
      "description": "CNC Machining - 10 hours",
      "quantity": 10,
      "unit": "hours",
      "unit_price": 75.00
    },
    {
      "item_type": "material",
      "item_id": 2,
      "description": "Aluminum Sheet 6061",
      "quantity": 5,
      "unit": "sqft",
      "unit_price": 50.00
    }
  ],
  "tax_rate": 8.5,
  "discount_amount": 0,
  "currency": "USD",
  "notes": "Rush order needed",
  "captcha_token": "03AGdBq24...",
  "csrf_token": "a1b2c3d4e5f6..."
}
```

**Validation Rules:**
- `customer_name` - required, 2-100 characters
- `customer_email` - required, valid email, max 100 characters
- `customer_phone` - optional, max 20 characters
- `title` - required, 5-200 characters
- `description` - optional, max 2000 characters
- `items` - required array, min 1 item
  - `description` - required, 3-500 characters
  - `quantity` - required, numeric
  - `unit` - required, max 20 characters
  - `unit_price` - required, numeric
- `captcha_token` - required

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Cost estimate submitted successfully",
  "data": {
    "estimate_number": "EST20240101001",
    "estimate_id": 1,
    "total_amount": 1085.00
  }
}
```

**Email Notification:**
An HTML email notification is sent to the admin email address configured in site settings.

---

### Contact Requests

#### Submit Contact Request

Submit a new contact/inquiry request.

```
POST /api/contact
```

**Request Body:**
```json
{
  "customer_name": "Jane Smith",
  "customer_email": "jane@example.com",
  "customer_phone": "+1-555-0456",
  "customer_company": "Acme Corp",
  "service_id": 1,
  "subject": "Inquiry about CNC services",
  "message": "I would like to get more information about your CNC machining services...",
  "request_type": "quote",
  "estimated_budget": "$5000-$10000",
  "captcha_token": "03AGdBq24...",
  "csrf_token": "a1b2c3d4e5f6..."
}
```

**Validation Rules:**
- `customer_name` - required, 2-100 characters
- `customer_email` - required, valid email, max 100 characters
- `customer_phone` - optional, max 20 characters
- `subject` - required, 5-200 characters
- `message` - required, 10-2000 characters
- `request_type` - optional, one of: general, quote, support, partnership
- `captcha_token` - required

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Contact request submitted successfully",
  "data": {
    "request_number": "REQ20240101001",
    "request_id": 1
  }
}
```

**Email Notification:**
An HTML email notification is sent to the admin email address with reply-to set to customer's email.

---

## Error Handling

### Validation Errors (422)

```json
{
  "error": true,
  "message": "Validation failed",
  "errors": {
    "customer_email": [
      "The customer_email must be a valid email address"
    ],
    "captcha_token": [
      "The captcha_token field is required"
    ]
  }
}
```

### Not Found (404)

```json
{
  "error": true,
  "message": "Resource not found",
  "errors": null
}
```

### CAPTCHA Verification Failed (422)

```json
{
  "error": true,
  "message": "CAPTCHA verification failed",
  "errors": null
}
```

### CSRF Token Invalid (403)

```json
{
  "error": true,
  "message": "CSRF token validation failed",
  "errors": null
}
```

## Rate Limiting

All endpoints are protected by rate limiting to prevent abuse.

**Default Limit:** 100 requests per hour per IP address

**Configuration:**
```env
API_RATE_LIMIT=100
```

**Response when limit exceeded (429):**
```json
{
  "error": true,
  "message": "Too many requests. Please try again later.",
  "errors": null
}
```

## Examples

### JavaScript (Fetch API)

#### Get Services
```javascript
fetch('http://localhost:8000/api/services?page=1&per_page=10')
  .then(response => response.json())
  .then(data => {
    console.log(data.data.data); // Array of services
  });
```

#### Submit Cost Estimate with CAPTCHA
```javascript
// Get CSRF token first
const csrfResponse = await fetch('http://localhost:8000/api/csrf-token');
const csrfData = await csrfResponse.json();
const csrfToken = csrfData.data.csrf_token;

// Get reCAPTCHA token
const captchaToken = await grecaptcha.execute('YOUR_SITE_KEY', {action: 'submit'});

// Submit estimate
const response = await fetch('http://localhost:8000/api/cost-estimates', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    customer_name: 'John Doe',
    customer_email: 'john@example.com',
    title: 'Custom Project',
    items: [
      {
        description: 'Service item',
        quantity: 10,
        unit: 'hours',
        unit_price: 75.00
      }
    ],
    captcha_token: captchaToken,
    csrf_token: csrfToken
  })
});

const result = await response.json();
console.log(result);
```

### cURL

#### Get Services
```bash
curl -X GET "http://localhost:8000/api/services?page=1&per_page=10"
```

#### Get Service by ID
```bash
curl -X GET "http://localhost:8000/api/services/1"
```

#### Get Service by Slug
```bash
curl -X GET "http://localhost:8000/api/services/custom-cnc-machining"
```

#### Submit Contact Request
```bash
curl -X POST "http://localhost:8000/api/contact" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Jane Smith",
    "customer_email": "jane@example.com",
    "subject": "Inquiry about services",
    "message": "I would like more information...",
    "captcha_token": "03AGdBq24...",
    "csrf_token": "a1b2c3d4e5f6..."
  }'
```

### Python (requests)

```python
import requests

# Get services
response = requests.get('http://localhost:8000/api/services', params={
    'page': 1,
    'per_page': 10,
    'featured': True
})
data = response.json()
services = data['data']['data']

# Submit contact request
csrf_response = requests.get('http://localhost:8000/api/csrf-token')
csrf_token = csrf_response.json()['data']['csrf_token']

contact_data = {
    'customer_name': 'John Doe',
    'customer_email': 'john@example.com',
    'subject': 'Inquiry',
    'message': 'I need more information',
    'captcha_token': 'YOUR_CAPTCHA_TOKEN',
    'csrf_token': csrf_token
}

response = requests.post('http://localhost:8000/api/contact', json=contact_data)
result = response.json()
print(result)
```

## Support

For API support and questions:
- Email: admin@example.com
- Check logs: `logs/app.log`
- Review OpenAPI spec: `openapi.yaml`

## Version History

### Version 1.0.0 (Current)
- Initial public API release
- GET endpoints for services, materials, pricing rules, gallery, news, settings
- POST endpoints for cost estimates and contact requests
- CAPTCHA integration (reCAPTCHA/hCaptcha)
- CSRF protection
- Email notifications
- Rate limiting
- Comprehensive documentation
