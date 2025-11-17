# Admin Backend Implementation

Complete admin backend system with JWT authentication, role-based permissions, CRUD operations, analytics, and audit logging.

## Quick Start

### 1. Run Database Migrations

```bash
php database/migrate.php
```

This will create the `password_reset_tokens` table (migration 015).

### 2. Seed Admin Users

```bash
php database/seed.php
```

**Default Credentials**:
- **Super Admin**: username: `admin`, password: `admin123`
- **Editor**: username: `editor`, password: `editor123`

⚠️ **IMPORTANT**: Change these passwords immediately in production!

### 3. Configure Environment

Set your JWT secret in `.env`:

```env
JWT_SECRET=your-secure-random-secret-minimum-32-chars
```

Generate a secure secret:
```bash
openssl rand -base64 32
# or
php -r "echo bin2hex(random_bytes(32));"
```

### 4. Test the API

```bash
# Login
curl -X POST http://localhost:8000/api/admin/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Get current user
curl http://localhost:8000/api/admin/auth/me \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## Features Implemented

### ✅ Authentication System
- JWT-based authentication (access + refresh tokens)
- Login/logout endpoints
- Password reset flow (request + reset)
- Token refresh mechanism
- Secure password hashing (Argon2ID/Bcrypt)

### ✅ Authorization
- Role-based access control (RBAC)
- Four permission levels: super_admin, admin, editor, viewer
- AdminAuthMiddleware for JWT validation
- RoleMiddleware for permission checks (ready for use)

### ✅ CRUD Operations
Complete CRUD endpoints for:
- Service Categories
- Services
- Materials
- Pricing Rules
- Gallery Items (with file uploads)
- News Posts
- Site Settings (with bulk update)
- Customer Requests (management)
- Cost Estimates (with items)

### ✅ File Upload Handling
- Image validation (type, size, dimensions)
- Secure file storage with unique naming
- Automatic thumbnail generation (300x300px)
- Supported formats: JPEG, PNG, GIF, WebP
- Max size: 5MB

### ✅ Analytics Endpoints
- Dashboard overview (totals, conversion rates, revenue)
- Popular services by request count
- Request trends over time
- Estimate trends over time
- Recent activity feed
- Conversion statistics (request → estimate → accepted)
- Material usage statistics
- Customer statistics

### ✅ Audit Logging
- All admin actions logged automatically
- Logs include: user, action, resource, old/new values, IP, timestamp
- Audit log viewer with filtering
- Resource history tracking
- User activity statistics

### ✅ Security Features
- JWT token expiration (access: 1hr, refresh: 7 days)
- Password reset tokens (1hr expiration, single use)
- Email enumeration prevention
- SQL injection prevention (prepared statements)
- Input validation on all endpoints
- Rate limiting (configured in middleware)
- CORS configuration

## API Endpoints

### Authentication
```
POST   /api/admin/auth/login                  - Login
POST   /api/admin/auth/logout                 - Logout (protected)
POST   /api/admin/auth/refresh                - Refresh access token
POST   /api/admin/auth/request-password-reset - Request password reset
POST   /api/admin/auth/reset-password         - Reset password
GET    /api/admin/auth/me                     - Get current user (protected)
```

### Service Categories
```
GET    /api/admin/service-categories          - List all
GET    /api/admin/service-categories/{id}     - Get one
POST   /api/admin/service-categories          - Create
PUT    /api/admin/service-categories/{id}     - Update
DELETE /api/admin/service-categories/{id}     - Delete
```

### Services
```
GET    /api/admin/services                    - List with pagination & search
GET    /api/admin/services/{id}               - Get one
POST   /api/admin/services                    - Create
PUT    /api/admin/services/{id}               - Update
DELETE /api/admin/services/{id}               - Delete
```

### Materials
```
GET    /api/admin/materials                   - List with pagination & search
GET    /api/admin/materials/{id}              - Get one
POST   /api/admin/materials                   - Create
PUT    /api/admin/materials/{id}              - Update
DELETE /api/admin/materials/{id}              - Delete
```

### Pricing Rules
```
GET    /api/admin/pricing-rules               - List with pagination
GET    /api/admin/pricing-rules/{id}          - Get one
POST   /api/admin/pricing-rules               - Create
PUT    /api/admin/pricing-rules/{id}          - Update
DELETE /api/admin/pricing-rules/{id}          - Delete
```

### Gallery
```
GET    /api/admin/gallery                     - List with pagination
GET    /api/admin/gallery/{id}                - Get one
POST   /api/admin/gallery                     - Create (with file upload)
PUT    /api/admin/gallery/{id}                - Update (with file upload)
DELETE /api/admin/gallery/{id}                - Delete
```

### News
```
GET    /api/admin/news                        - List with pagination
GET    /api/admin/news/{id}                   - Get one
POST   /api/admin/news                        - Create
PUT    /api/admin/news/{id}                   - Update
DELETE /api/admin/news/{id}                   - Delete
```

### Site Settings
```
GET    /api/admin/settings                    - List all
GET    /api/admin/settings/{key}              - Get one
POST   /api/admin/settings                    - Create
PUT    /api/admin/settings/{key}              - Update
DELETE /api/admin/settings/{key}              - Delete
POST   /api/admin/settings/bulk               - Bulk update
```

### Customer Requests
```
GET    /api/admin/requests                    - List with filters
GET    /api/admin/requests/{id}               - Get one
PUT    /api/admin/requests/{id}               - Update
POST   /api/admin/requests/{id}/assign        - Assign to admin
POST   /api/admin/requests/{id}/status        - Update status
GET    /api/admin/requests-stats              - Get statistics
```

### Cost Estimates
```
GET    /api/admin/estimates                   - List with pagination
GET    /api/admin/estimates/{id}              - Get one (with items)
POST   /api/admin/estimates                   - Create (with items)
PUT    /api/admin/estimates/{id}              - Update (with items)
DELETE /api/admin/estimates/{id}              - Delete
POST   /api/admin/estimates/{id}/send         - Send to customer
```

### Analytics
```
GET    /api/admin/analytics/dashboard         - Dashboard overview
GET    /api/admin/analytics/popular-services  - Popular services
GET    /api/admin/analytics/request-trends    - Request trends
GET    /api/admin/analytics/estimate-trends   - Estimate trends
GET    /api/admin/analytics/recent-activity   - Recent activity
GET    /api/admin/analytics/conversion-stats  - Conversion statistics
GET    /api/admin/analytics/material-usage    - Material usage
GET    /api/admin/analytics/customer-stats    - Customer statistics
```

### Audit Logs
```
GET    /api/admin/audit-logs                  - List with filters
GET    /api/admin/audit-logs/{id}             - Get one
GET    /api/admin/audit-logs/resource/{type}/{id} - Get by resource
GET    /api/admin/audit-logs/event-types      - Get event types
GET    /api/admin/audit-logs/auditable-types  - Get auditable types
```

All protected endpoints require:
```
Authorization: Bearer {access_token}
```

## File Structure

```
src/
  Controllers/Admin/
    AuthController.php              - Authentication
    ServiceCategoriesController.php - Service categories CRUD
    ServicesController.php          - Services CRUD
    MaterialsController.php         - Materials CRUD
    PricingRulesController.php      - Pricing rules CRUD
    GalleryController.php           - Gallery CRUD + uploads
    NewsController.php              - News CRUD
    SiteSettingsController.php      - Settings CRUD + bulk
    CustomerRequestsController.php  - Requests management
    CostEstimatesController.php     - Estimates CRUD
    AnalyticsController.php         - Analytics endpoints
    AuditLogsController.php         - Audit log viewer
  
  Middleware/
    AdminAuthMiddleware.php         - JWT validation
    RoleMiddleware.php              - Permission checks
  
  Services/
    JwtService.php                  - JWT generation/verification
    AuditLogger.php                 - Audit logging helper
  
  Models/
    PasswordResetToken.php          - Password reset tokens
  
  Repositories/
    AuditLogRepository.php          - Audit log queries

api/
  routes_admin.php                  - Admin API routes

database/
  migrations/
    015_create_password_reset_tokens_table.sql
```

## Documentation

- **[ADMIN_API.md](ADMIN_API.md)** - Complete API documentation with examples
- **[ADMIN_SECURITY.md](ADMIN_SECURITY.md)** - Security guide and best practices
- **[DATABASE.md](DATABASE.md)** - Database schema and usage
- **[API.md](API.md)** - General API documentation

## Testing Examples

### Login and Get Token
```bash
# Login
TOKEN=$(curl -s -X POST http://localhost:8000/api/admin/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}' \
  | jq -r '.data.access_token')

echo "Token: $TOKEN"
```

### Create a Service
```bash
curl -X POST http://localhost:8000/api/admin/services \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "category_id": 1,
    "name": "Custom CNC Machining",
    "slug": "custom-cnc-machining",
    "description": "High-precision custom CNC machining services",
    "price_type": "quote",
    "is_visible": true
  }'
```

### Get Dashboard Analytics
```bash
curl http://localhost:8000/api/admin/analytics/dashboard \
  -H "Authorization: Bearer $TOKEN"
```

### Get Audit Logs
```bash
curl "http://localhost:8000/api/admin/audit-logs?per_page=10" \
  -H "Authorization: Bearer $TOKEN"
```

### Upload Gallery Image
```bash
curl -X POST http://localhost:8000/api/admin/gallery \
  -H "Authorization: Bearer $TOKEN" \
  -F "title=Project Photo" \
  -F "category=projects" \
  -F "description=Manufacturing project 2024" \
  -F "image=@/path/to/image.jpg"
```

## Validation Rules

Common validation rules used across endpoints:

- **Email**: Must be valid email format
- **Password**: Minimum 8 characters
- **Username**: Minimum 3 characters
- **Names**: Minimum 3 characters, maximum 200
- **Slugs**: Minimum 3 characters, maximum 200, unique per resource
- **SKU**: Maximum 100 characters, unique

## Error Responses

All errors return JSON with:

```json
{
  "error": true,
  "message": "Error description",
  "errors": {
    "field": ["Validation error"]
  }
}
```

HTTP Status Codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `409` - Conflict (duplicate)
- `422` - Validation Error
- `500` - Server Error

## Security Checklist

Before deploying to production:

- [ ] Change default admin passwords
- [ ] Set strong JWT_SECRET in .env
- [ ] Configure proper CORS origins
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Configure email for password resets
- [ ] Set up SSL/TLS certificates
- [ ] Configure rate limiting
- [ ] Review file upload permissions
- [ ] Test all authentication flows
- [ ] Verify audit logging is working
- [ ] Set up database backups
- [ ] Configure log rotation

## Troubleshooting

### JWT Token Issues
- Ensure JWT_SECRET is set in .env
- Check token expiration (1 hour for access tokens)
- Verify user status is 'active'
- Use refresh token to get new access token

### File Upload Issues
- Check upload directory permissions (755 for directories, 644 for files)
- Verify GD library is installed for thumbnail generation
- Check file size doesn't exceed 5MB
- Ensure file type is allowed (JPEG, PNG, GIF, WebP)

### Database Issues
- Run migrations: `php database/migrate.php`
- Verify database credentials in .env
- Check user has proper permissions
- Ensure password_reset_tokens table exists

## Support

For issues or questions:
- Review API documentation: `ADMIN_API.md`
- Check security guide: `ADMIN_SECURITY.md`
- Review database schema: `DATABASE.md`
- Check coding standards: `CODING_STANDARDS.md`

## License

See main project README for license information.
