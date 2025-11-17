# Admin API Documentation

Complete documentation for the admin backend API endpoints with authentication, CRUD operations, analytics, and audit logging.

## Table of Contents

1. [Authentication](#authentication)
2. [Authorization](#authorization)
3. [Services Management](#services-management)
4. [Materials Management](#materials-management)
5. [Pricing Rules](#pricing-rules)
6. [Gallery Management](#gallery-management)
7. [News Management](#news-management)
8. [Site Settings](#site-settings)
9. [Customer Requests](#customer-requests)
10. [Cost Estimates](#cost-estimates)
11. [Analytics](#analytics)
12. [Audit Logs](#audit-logs)
13. [Security Practices](#security-practices)
14. [Error Handling](#error-handling)

---

## Authentication

### Login

**POST** `/api/admin/auth/login`

Authenticate an admin user and receive JWT tokens.

**Request Body:**
```json
{
  "username": "admin",
  "password": "admin123"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "username": "admin",
      "email": "admin@example.com",
      "first_name": "Admin",
      "last_name": "User",
      "role": "super_admin",
      "status": "active"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### Logout

**POST** `/api/admin/auth/logout`

Logout the current admin user (requires authentication).

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Logout successful",
  "data": null
}
```

### Refresh Token

**POST** `/api/admin/auth/refresh`

Refresh an expired access token using a refresh token.

**Request Body:**
```json
{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Token refreshed",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### Request Password Reset

**POST** `/api/admin/auth/request-password-reset`

Request a password reset link via email.

**Request Body:**
```json
{
  "email": "admin@example.com"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "If the email exists, a password reset link has been sent",
  "data": null
}
```

### Reset Password

**POST** `/api/admin/auth/reset-password`

Reset password using a token from email.

**Request Body:**
```json
{
  "token": "abc123def456...",
  "password": "newSecurePassword123",
  "password_confirmation": "newSecurePassword123"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Password has been reset successfully",
  "data": null
}
```

### Get Current User

**GET** `/api/admin/auth/me`

Get the currently authenticated admin user's details.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "first_name": "Admin",
    "last_name": "User",
    "role": "super_admin",
    "status": "active"
  }
}
```

---

## Authorization

All protected endpoints require a valid JWT token in the `Authorization` header:

```
Authorization: Bearer {access_token}
```

### Roles

- **super_admin**: Full access to all resources
- **admin**: Access to most resources
- **editor**: Limited access to content management
- **viewer**: Read-only access

---

## Services Management

### List Services

**GET** `/api/admin/services?page=1&per_page=20&search=cnc`

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)
- `search` (optional): Search term

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "category_id": 1,
        "name": "CNC Machining",
        "slug": "cnc-machining",
        "description": "High-precision CNC machining services",
        "price_type": "quote",
        "is_visible": true,
        "created_at": "2024-01-01 10:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 50,
      "last_page": 3
    }
  }
}
```

### Get Service

**GET** `/api/admin/services/{id}`

**Response:** `200 OK`

### Create Service

**POST** `/api/admin/services`

**Request Body:**
```json
{
  "category_id": 1,
  "name": "Custom CNC Machining",
  "slug": "custom-cnc-machining",
  "description": "High-precision custom CNC machining",
  "short_description": "Custom machining services",
  "price_type": "quote",
  "base_price": 100.00,
  "pricing_unit": "hour",
  "is_featured": true,
  "is_visible": true,
  "sort_order": 10
}
```

**Response:** `201 Created`

### Update Service

**PUT** `/api/admin/services/{id}`

**Request Body:** Same as create (all fields optional)

**Response:** `200 OK`

### Delete Service

**DELETE** `/api/admin/services/{id}`

**Response:** `200 OK`

---

## Materials Management

### List Materials

**GET** `/api/admin/materials?page=1&per_page=20&search=aluminum&category=Metals`

**Response:** Similar to services list

### Create Material

**POST** `/api/admin/materials`

**Request Body:**
```json
{
  "name": "Aluminum 6061",
  "sku": "AL-6061-001",
  "category": "Metals",
  "description": "High-quality aluminum alloy",
  "unit": "kg",
  "unit_price": 25.50,
  "stock_quantity": 500,
  "reorder_level": 50,
  "is_active": true
}
```

**Response:** `201 Created`

### Update/Delete Material

Similar to services endpoints.

---

## Pricing Rules

### List Pricing Rules

**GET** `/api/admin/pricing-rules?page=1&per_page=20`

### Create Pricing Rule

**POST** `/api/admin/pricing-rules`

**Request Body:**
```json
{
  "name": "Volume Discount - 10%",
  "description": "10% discount for orders over $1000",
  "rule_type": "discount",
  "applies_to": "all",
  "condition_field": "total_amount",
  "condition_operator": ">=",
  "condition_value": "1000",
  "price_modifier": 10,
  "modifier_type": "percentage",
  "priority": 1,
  "is_active": true
}
```

**Response:** `201 Created`

---

## Gallery Management

### List Gallery Items

**GET** `/api/admin/gallery?page=1&per_page=20&category=projects`

### Create Gallery Item (with file upload)

**POST** `/api/admin/gallery`

**Content-Type:** `multipart/form-data`

**Form Data:**
```
title: "Manufacturing Project 2024"
description: "Custom manufacturing project"
category: "projects"
image: [file]
is_featured: true
is_visible: true
```

**Accepted Image Types:**
- image/jpeg
- image/png
- image/gif
- image/webp

**Max File Size:** 5MB

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Gallery item created successfully",
  "data": {
    "id": 1,
    "title": "Manufacturing Project 2024",
    "file_path": "/uploads/gallery/gallery_123456.jpg",
    "thumbnail_path": "/uploads/gallery/thumb_gallery_123456.jpg"
  }
}
```

---

## News Management

### List News Posts

**GET** `/api/admin/news?page=1&per_page=20&status=published`

### Create News Post

**POST** `/api/admin/news`

**Request Body:**
```json
{
  "title": "New Manufacturing Capabilities",
  "slug": "new-manufacturing-capabilities",
  "excerpt": "We're excited to announce...",
  "content": "Full article content here...",
  "featured_image": "/uploads/news/image.jpg",
  "status": "published",
  "published_at": "2024-01-15 10:00:00",
  "meta_title": "SEO Title",
  "meta_description": "SEO description"
}
```

**Response:** `201 Created`

---

## Site Settings

### List All Settings

**GET** `/api/admin/settings?group=general`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "key": "site_name",
      "value": "My Manufacturing Site",
      "type": "string",
      "group": "general",
      "is_public": true
    }
  ]
}
```

### Get Single Setting

**GET** `/api/admin/settings/{key}`

### Create Setting

**POST** `/api/admin/settings`

**Request Body:**
```json
{
  "key": "tax_rate",
  "value": "8.5",
  "type": "float",
  "group": "billing",
  "label": "Tax Rate (%)",
  "description": "Default tax rate for estimates",
  "is_public": false
}
```

### Update Setting

**PUT** `/api/admin/settings/{key}`

**Request Body:**
```json
{
  "value": "9.0"
}
```

### Bulk Update Settings

**POST** `/api/admin/settings/bulk`

**Request Body:**
```json
{
  "settings": {
    "site_name": "New Site Name",
    "tax_rate": "9.5",
    "contact_email": "info@example.com"
  }
}
```

---

## Customer Requests

### List Customer Requests

**GET** `/api/admin/requests?page=1&per_page=20&status=new&priority=urgent&assigned_to=1`

**Response:** `200 OK`

### Get Customer Request

**GET** `/api/admin/requests/{id}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "request_number": "REQ20240101001",
    "service_id": 1,
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "status": "new",
    "priority": "normal",
    "assigned_to": null,
    "project_details": {
      "description": "Custom part manufacturing",
      "quantity": 100
    },
    "service": {
      "id": 1,
      "name": "CNC Machining"
    },
    "created_at": "2024-01-01 10:00:00"
  }
}
```

### Update Customer Request

**PUT** `/api/admin/requests/{id}`

**Request Body:**
```json
{
  "status": "in_progress",
  "priority": "urgent",
  "assigned_to": 2,
  "internal_notes": "Customer called for follow-up"
}
```

### Assign Request

**POST** `/api/admin/requests/{id}/assign`

**Request Body:**
```json
{
  "assigned_to": 2
}
```

### Update Request Status

**POST** `/api/admin/requests/{id}/status`

**Request Body:**
```json
{
  "status": "completed"
}
```

### Get Request Statistics

**GET** `/api/admin/requests-stats`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "total": 150,
    "by_status": {
      "new": 20,
      "in_progress": 30,
      "completed": 100
    },
    "by_priority": {
      "normal": 100,
      "urgent": 50
    }
  }
}
```

---

## Cost Estimates

### List Cost Estimates

**GET** `/api/admin/estimates?page=1&per_page=20&status=sent`

### Get Cost Estimate

**GET** `/api/admin/estimates/{id}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "estimate_number": "EST20240101001",
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "title": "Custom Manufacturing Quote",
    "subtotal": 1000.00,
    "tax_rate": 8.5,
    "tax_amount": 85.00,
    "total_amount": 1085.00,
    "status": "sent",
    "valid_until": "2024-02-01",
    "items": [
      {
        "id": 1,
        "item_type": "service",
        "description": "CNC Machining - 10 hours",
        "quantity": 10,
        "unit": "hours",
        "unit_price": 75.00,
        "line_total": 750.00
      },
      {
        "id": 2,
        "item_type": "material",
        "description": "Aluminum 6061",
        "quantity": 5,
        "unit": "kg",
        "unit_price": 50.00,
        "line_total": 250.00
      }
    ]
  }
}
```

### Create Cost Estimate

**POST** `/api/admin/estimates`

**Request Body:**
```json
{
  "request_id": 1,
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+1234567890",
  "title": "Custom Manufacturing Quote",
  "description": "Quote for custom parts",
  "tax_rate": 8.5,
  "valid_until": "2024-02-01",
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
      "description": "Aluminum 6061",
      "quantity": 5,
      "unit": "kg",
      "unit_price": 50.00
    }
  ]
}
```

**Response:** `201 Created`

### Update Cost Estimate

**PUT** `/api/admin/estimates/{id}`

**Request Body:** Same as create (all fields optional)

### Delete Cost Estimate

**DELETE** `/api/admin/estimates/{id}`

### Send Estimate to Customer

**POST** `/api/admin/estimates/{id}/send`

Marks the estimate as sent and sends email to customer.

**Response:** `200 OK`

---

## Analytics

### Dashboard Overview

**GET** `/api/admin/analytics/dashboard`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_requests": 150,
      "total_estimates": 100,
      "total_services": 25,
      "total_materials": 50,
      "recent_requests": 20,
      "conversion_rate": 66.67,
      "acceptance_rate": 45.00,
      "total_revenue": 125000.00
    },
    "requests_by_status": [
      {"status": "new", "count": 20},
      {"status": "in_progress", "count": 30}
    ],
    "estimates_by_status": [
      {"status": "sent", "count": 40},
      {"status": "accepted", "count": 45}
    ]
  }
}
```

### Popular Services

**GET** `/api/admin/analytics/popular-services?limit=10`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "CNC Machining",
      "request_count": 75,
      "estimate_count": 50
    }
  ]
}
```

### Request Trends

**GET** `/api/admin/analytics/request-trends?days=30`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {"date": "2024-01-01", "count": 5},
    {"date": "2024-01-02", "count": 8}
  ]
}
```

### Estimate Trends

**GET** `/api/admin/analytics/estimate-trends?days=30`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {"date": "2024-01-01", "count": 3, "total_amount": 5000.00},
    {"date": "2024-01-02", "count": 5, "total_amount": 7500.00}
  ]
}
```

### Recent Activity

**GET** `/api/admin/analytics/recent-activity?limit=20`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "event_type": "create",
      "auditable_type": "service",
      "auditable_id": 5,
      "admin_name": "Admin User",
      "created_at": "2024-01-15 10:30:00"
    }
  ]
}
```

### Conversion Statistics

**GET** `/api/admin/analytics/conversion-stats?days=30`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "total_requests": 100,
    "total_estimates": 75,
    "sent_estimates": 70,
    "viewed_estimates": 60,
    "accepted_estimates": 45,
    "rejected_estimates": 15,
    "total_revenue": 125000.00,
    "request_to_estimate_rate": 75.00,
    "sent_to_viewed_rate": 85.71,
    "viewed_to_accepted_rate": 75.00,
    "overall_acceptance_rate": 60.00
  }
}
```

### Material Usage

**GET** `/api/admin/analytics/material-usage?limit=10`

**Response:** `200 OK`

### Customer Statistics

**GET** `/api/admin/analytics/customer-stats`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "top_customers": [
      {
        "customer_email": "john@example.com",
        "customer_name": "John Doe",
        "request_count": 10,
        "estimate_count": 8,
        "total_revenue": 25000.00
      }
    ],
    "new_customers_last_30_days": 15
  }
}
```

---

## Audit Logs

### List Audit Logs

**GET** `/api/admin/audit-logs?page=1&per_page=50&event_type=create&auditable_type=service&user_id=1`

**Query Parameters:**
- `event_type`: Filter by event type (create, update, delete, login, logout)
- `auditable_type`: Filter by resource type
- `user_id`: Filter by admin user

**Response:** `200 OK`

### Get Audit Log Details

**GET** `/api/admin/audit-logs/{id}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "event_type": "update",
    "auditable_type": "service",
    "auditable_id": 5,
    "old_values": {
      "name": "Old Service Name",
      "price": 100.00
    },
    "new_values": {
      "name": "New Service Name",
      "price": 150.00
    },
    "ip_address": "192.168.1.1",
    "created_at": "2024-01-15 10:30:00"
  }
}
```

### Get Audit Logs for Specific Resource

**GET** `/api/admin/audit-logs/resource/{type}/{id}`

Example: `/api/admin/audit-logs/resource/service/5`

**Response:** `200 OK`

### Get Available Event Types

**GET** `/api/admin/audit-logs/event-types`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": ["create", "update", "delete", "login", "logout", "password_reset"]
}
```

### Get Available Auditable Types

**GET** `/api/admin/audit-logs/auditable-types`

**Response:** `200 OK`

---

## Security Practices

### Password Security

- **Hashing Algorithm**: Argon2ID (or Bcrypt as fallback)
- **Minimum Length**: 8 characters
- **Password Reset**: Tokens expire after 1 hour
- **Token Storage**: SHA-256 hashed in database

### JWT Configuration

- **Access Token Expiration**: 1 hour
- **Refresh Token Expiration**: 7 days
- **Algorithm**: HS256
- **Secret Key**: Set `JWT_SECRET` in `.env` file

**Important:** Change the default JWT secret in production:
```env
JWT_SECRET=your-secure-random-secret-key-here
```

### File Upload Security

- **Allowed Types**: JPEG, PNG, GIF, WebP
- **Max Size**: 5MB
- **Storage**: Unique filenames with timestamp
- **Validation**: MIME type checking
- **Thumbnails**: Automatically generated at 300x300px

### Authorization Middleware

All protected endpoints check:
1. Valid JWT token
2. User is active
3. User has appropriate role permissions

### Audit Logging

All admin actions are logged with:
- User ID and role
- Action type (create, update, delete)
- Old and new values
- IP address and user agent
- Timestamp

---

## Error Handling

### Standard Error Response

```json
{
  "error": true,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### HTTP Status Codes

- `200 OK`: Successful request
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required or failed
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `409 Conflict`: Resource conflict (e.g., duplicate slug)
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server error

### Validation Errors

```json
{
  "error": true,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required", "The email must be valid"],
    "password": ["The password must be at least 8 characters"]
  }
}
```

---

## Quick Start Example

```bash
# 1. Login
curl -X POST http://localhost:8000/api/admin/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# 2. Use the access token for authenticated requests
export TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."

# 3. Get dashboard analytics
curl http://localhost:8000/api/admin/analytics/dashboard \
  -H "Authorization: Bearer $TOKEN"

# 4. Create a new service
curl -X POST http://localhost:8000/api/admin/services \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "category_id": 1,
    "name": "Custom Service",
    "slug": "custom-service",
    "description": "Service description",
    "price_type": "quote"
  }'

# 5. Get audit logs
curl http://localhost:8000/api/admin/audit-logs?per_page=10 \
  -H "Authorization: Bearer $TOKEN"
```

---

## Environment Variables

Required environment variables for admin backend:

```env
# JWT Configuration
JWT_SECRET=your-secure-random-secret-key-change-this

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database
DB_USER=your_user
DB_PASS=your_password

# Mail (for password reset)
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME=Your App

# App
APP_URL=http://localhost:8000
```

---

## Testing

Run database migrations and seeds:

```bash
# Run migrations
php database/migrate.php

# Seed admin users
php database/seed.php
```

Default admin credentials:
- Username: `admin`
- Password: `admin123`

**⚠️ Change these credentials immediately in production!**

---

## Support

For issues or questions, refer to:
- Project documentation
- Database schema: `DATABASE.md`
- API guidelines: `API.md`
- Coding standards: `CODING_STANDARDS.md`
