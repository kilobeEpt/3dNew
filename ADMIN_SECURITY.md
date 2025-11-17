# Admin Backend Security Guide

Comprehensive security practices and implementation details for the admin backend system.

## Table of Contents

1. [Authentication](#authentication)
2. [Authorization](#authorization)
3. [Password Security](#password-security)
4. [JWT Token Management](#jwt-token-management)
5. [File Upload Security](#file-upload-security)
6. [Audit Logging](#audit-logging)
7. [API Security](#api-security)
8. [Environment Configuration](#environment-configuration)
9. [Production Checklist](#production-checklist)

---

## Authentication

### Implementation

The admin backend uses JWT (JSON Web Tokens) for stateless authentication:

- **Access Tokens**: Short-lived (1 hour), used for API requests
- **Refresh Tokens**: Long-lived (7 days), used to obtain new access tokens

### Login Flow

1. User submits credentials (username/password)
2. Server validates credentials against hashed password
3. Server generates access token and refresh token
4. Tokens are returned to client
5. Client stores tokens securely
6. Client includes access token in `Authorization` header for protected requests

### Token Structure

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

### Implementation Details

**File**: `src/Services/JwtService.php`

```php
// Generate access token
$accessToken = $jwtService->generateAccessToken($userId, $role);

// Verify token
$payload = $jwtService->verify($token);
if (!$payload) {
    // Token invalid or expired
}
```

### Session Management

- No server-side sessions required (stateless)
- Token expiration handled by JWT payload
- Logout is client-side (remove tokens)
- Server logs logout action for audit trail

---

## Authorization

### Role-Based Access Control (RBAC)

Four permission levels:

1. **super_admin**: Full system access
   - All CRUD operations
   - User management
   - System settings
   - Analytics and reports

2. **admin**: Standard administrative access
   - Most CRUD operations
   - Limited user management
   - Analytics viewing

3. **editor**: Content management
   - Create/edit content (news, gallery)
   - View services and materials
   - No access to settings or users

4. **viewer**: Read-only access
   - View-only permissions
   - Analytics viewing
   - No create/update/delete

### Middleware Implementation

**AdminAuthMiddleware**: Validates JWT and loads user

```php
// File: src/Middleware/AdminAuthMiddleware.php
// Verifies token and attaches user to request
$request->adminUser = $adminUser;
```

**RoleMiddleware**: Checks user permissions

```php
// Usage in routes (future implementation)
$router->delete('/users/{id}', UserController::class . '@destroy')
    ->middleware([
        AdminAuthMiddleware::class,
        RoleMiddleware::only(['super_admin'])
    ]);
```

### Permission Checking

All protected endpoints require valid JWT token.
User status must be 'active'.
Inactive or suspended users are rejected.

---

## Password Security

### Hashing Algorithm

**Primary**: Argon2ID (recommended)
**Fallback**: Bcrypt

```php
// Using Argon2ID (if available)
$hash = password_hash($password, PASSWORD_ARGON2ID);

// Fallback to Bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);

// Verification
if (password_verify($plainPassword, $hash)) {
    // Password correct
}
```

### Password Requirements

- **Minimum Length**: 8 characters
- **Recommendation**: 12+ characters with mixed case, numbers, symbols
- **Validation**: Enforced at API level

### Password Reset Flow

1. User requests password reset (email)
2. Server generates secure random token (32 bytes)
3. Token is hashed (SHA-256) and stored with expiration (1 hour)
4. Email sent with reset link containing unhashed token
5. User clicks link and submits new password
6. Server verifies token, updates password, marks token as used

**Security Features**:
- Tokens expire after 1 hour
- Tokens can only be used once
- Tokens are hashed in database
- Email enumeration prevention (always return success)

**Implementation**:

```php
// Generate token
$token = bin2hex(random_bytes(32)); // 64-character hex string
$hashedToken = hash('sha256', $token);

// Store in database
$resetTokenModel->create([
    'admin_user_id' => $userId,
    'token' => $hashedToken,
    'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
]);

// Verify token
$hashedToken = hash('sha256', $submittedToken);
$tokenRecord = $resetTokenModel->first(['token' => $hashedToken]);
```

---

## JWT Token Management

### Token Generation

**Access Token Payload**:
```json
{
  "user_id": 1,
  "role": "super_admin",
  "type": "access",
  "iat": 1704067200,
  "exp": 1704070800
}
```

**Refresh Token Payload**:
```json
{
  "user_id": 1,
  "type": "refresh",
  "iat": 1704067200,
  "exp": 1704672000
}
```

### Security Considerations

1. **Secret Key**: Must be strong and unique
   ```env
   # Generate with: openssl rand -base64 32
   JWT_SECRET=random-secure-string-at-least-32-chars
   ```

2. **Token Storage** (Client-side):
   - **Access Token**: Memory or sessionStorage (short-lived)
   - **Refresh Token**: httpOnly cookie (recommended) or secure storage
   - **Never**: localStorage (XSS vulnerable)

3. **Token Validation**:
   - Signature verification (HMAC SHA-256)
   - Expiration check
   - Type verification (access vs refresh)
   - User status check (active)

4. **Token Refresh**:
   - Only refresh tokens can generate new access tokens
   - Refresh tokens are long-lived but should be rotated
   - Invalid refresh token requires re-login

### Implementation

```php
// File: src/Services/JwtService.php

// Secret from environment
$jwtSecret = $_ENV['JWT_SECRET'] ?? 'fallback-insecure-key';
$jwtService = new JwtService($jwtSecret);

// Generate tokens
$accessToken = $jwtService->generateAccessToken($userId, $role);
$refreshToken = $jwtService->generateRefreshToken($userId);

// Verify and decode
$payload = $jwtService->verify($token);
if ($payload && $payload['type'] === 'access') {
    // Valid access token
}
```

---

## File Upload Security

### Allowed File Types

**Gallery/Images**:
- image/jpeg (.jpg, .jpeg)
- image/png (.png)
- image/gif (.gif)
- image/webp (.webp)

### Validation Steps

1. **MIME Type Check**: Verify file type from upload
2. **File Extension**: Validate extension matches MIME type
3. **File Size**: Maximum 5MB per file
4. **File Content**: Use getimagesize() to verify actual image
5. **Unique Naming**: Generate unique filename to prevent overwrites

### Implementation

```php
// File: src/Controllers/Admin/GalleryController.php

private function handleFileUpload(array $file): array
{
    // 1. Validate MIME type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => true, 'message' => 'Invalid file type'];
    }

    // 2. Validate file size (5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['error' => true, 'message' => 'File too large'];
    }

    // 3. Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('gallery_', true) . '.' . $extension;

    // 4. Move file to secure location
    $uploadPath = __DIR__ . '/../../../public_html/uploads/gallery/';
    move_uploaded_file($file['tmp_name'], $uploadPath . $filename);

    // 5. Generate thumbnail
    $this->createThumbnail($uploadPath . $filename, ...);

    return ['error' => false, 'path' => '/uploads/gallery/' . $filename];
}
```

### Storage Structure

```
public_html/
  uploads/
    gallery/
      gallery_abc123.jpg
      thumb_gallery_abc123.jpg
    news/
      news_def456.jpg
```

### Security Best Practices

- Store uploads outside document root when possible
- Use unique filenames (prevent overwrites and enumeration)
- Validate file content, not just extension
- Generate thumbnails server-side
- Set proper file permissions (644 for files, 755 for directories)
- Consider virus scanning for production

---

## Audit Logging

### What is Logged

All administrative actions are tracked:

- **Create**: New resource creation
- **Update**: Resource modifications (with old/new values)
- **Delete**: Resource deletion
- **Login**: User authentication
- **Logout**: User session end
- **Password Reset**: Password changes

### Log Structure

Each audit log entry contains:

```json
{
  "id": 123,
  "user_id": 1,
  "user_type": "admin",
  "event_type": "update",
  "auditable_type": "service",
  "auditable_id": 5,
  "old_values": {"name": "Old Name", "price": 100},
  "new_values": {"name": "New Name", "price": 150},
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2024-01-15 10:30:00"
}
```

### Implementation

**File**: `src/Services/AuditLogger.php`

```php
// Automatic logging in controllers
$this->auditLogger->logCreate(
    $userId,
    'service',
    $serviceId,
    $data,
    $request->ip(),
    $request->userAgent()
);

$this->auditLogger->logUpdate(
    $userId,
    'service',
    $serviceId,
    $oldData,
    $newData,
    $request->ip(),
    $request->userAgent()
);

$this->auditLogger->logDelete(
    $userId,
    'service',
    $serviceId,
    $data,
    $request->ip(),
    $request->userAgent()
);
```

### Viewing Audit Logs

```bash
# Get all audit logs
GET /api/admin/audit-logs?page=1&per_page=50

# Filter by event type
GET /api/admin/audit-logs?event_type=update

# Filter by resource
GET /api/admin/audit-logs?auditable_type=service

# Get logs for specific resource
GET /api/admin/audit-logs/resource/service/5
```

### Retention Policy

- Audit logs are never automatically deleted
- Implement manual archival process after 1-2 years
- Consider regulatory requirements (GDPR, SOX, etc.)

---

## API Security

### Rate Limiting

Implemented via `RateLimitMiddleware`:

- Default: 100 requests per IP per minute
- Configurable via `API_RATE_LIMIT` environment variable
- Returns 429 Too Many Requests when exceeded

### CORS (Cross-Origin Resource Sharing)

Configured in `CorsMiddleware`:

```env
CORS_ALLOWED_ORIGINS=https://yourdomain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization
```

**Production**: Specify exact origins, not wildcard `*`

### Input Validation

All endpoints validate input using `Validator` helper:

```php
$rules = [
    'email' => 'required|email',
    'password' => 'required|min:8',
    'username' => 'required|min:3|max:50',
];

$errors = Validator::validate($data, $rules);
if (!empty($errors)) {
    ResponseHelper::validationError($errors);
}
```

### SQL Injection Prevention

All database queries use prepared statements:

```php
// Good: Parameterized query
$database->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);

// Bad: Never concatenate user input
// $database->fetchOne("SELECT * FROM users WHERE id = $id");
```

### XSS Prevention

- API returns JSON only (not rendered HTML)
- Client must sanitize before displaying
- No user input echoed directly

---

## Environment Configuration

### Required Variables

```env
# JWT Secret (CRITICAL)
JWT_SECRET=your-secure-random-secret-minimum-32-chars

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=production_db
DB_USER=app_user
DB_PASS=secure_password

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Mail (for password reset)
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@example.com
MAIL_PASSWORD=mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com

# Security
API_RATE_LIMIT=100
CORS_ALLOWED_ORIGINS=https://yourdomain.com
```

### Generating Secure Keys

```bash
# JWT Secret
openssl rand -base64 32

# Or using PHP
php -r "echo bin2hex(random_bytes(32));"
```

---

## Production Checklist

### Before Deployment

- [ ] Change default admin password
- [ ] Set strong JWT_SECRET in .env
- [ ] Configure proper CORS origins
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Configure email for password resets
- [ ] Set up SSL/TLS certificates
- [ ] Configure rate limiting
- [ ] Set up database backups
- [ ] Configure log rotation
- [ ] Review file upload permissions
- [ ] Test password reset flow
- [ ] Verify all audit logging works
- [ ] Test authentication flow
- [ ] Verify authorization checks

### Database Security

```sql
-- Create dedicated database user
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'secure_password';

-- Grant minimum required permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON app_database.* TO 'app_user'@'localhost';

-- Do NOT grant DROP, ALTER, or admin privileges
FLUSH PRIVILEGES;
```

### File Permissions

```bash
# Application files
find /path/to/app -type f -exec chmod 644 {} \;
find /path/to/app -type d -exec chmod 755 {} \;

# Writable directories
chmod 755 logs/
chmod 755 public_html/uploads/

# Sensitive files
chmod 600 .env
```

### Server Configuration

**Apache (.htaccess)**:
```apache
# Disable directory listing
Options -Indexes

# Prevent access to sensitive files
<FilesMatch "\.(env|sql|md|log)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

**Nginx**:
```nginx
# Rate limiting
limit_req_zone $binary_remote_addr zone=api:10m rate=100r/m;

location /api/admin {
    limit_req zone=api burst=20;
    # ... other config
}
```

### Monitoring

- Set up error logging and monitoring
- Monitor failed login attempts
- Track API usage and rate limits
- Alert on unusual audit log patterns
- Monitor file upload activity

### Regular Maintenance

- Update dependencies regularly
- Review audit logs periodically
- Rotate JWT secrets annually
- Archive old audit logs
- Test backup restoration
- Review user access and permissions

---

## Security Incident Response

### If JWT Secret is Compromised

1. Generate new JWT_SECRET immediately
2. Update environment configuration
3. All existing tokens become invalid
4. Force all users to re-login
5. Review audit logs for suspicious activity

### If Passwords are Compromised

1. Force password reset for affected users
2. Invalidate all active sessions
3. Review audit logs
4. Notify affected users
5. Consider implementing 2FA

### If Unauthorized Access Detected

1. Review audit logs for entry point
2. Disable compromised accounts
3. Change all secrets and passwords
4. Review recent changes in audit logs
5. Restore from backup if necessary

---

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [JWT Best Practices](https://tools.ietf.org/html/rfc8725)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)

---

## Support

For security concerns or questions:
- Review this documentation
- Check audit logs for suspicious activity
- Follow incident response procedures
- Keep all credentials secure and private
