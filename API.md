# API Documentation

This document describes the API endpoints available in the platform.

## Base URL

```
http://localhost:8000/api
```

## Authentication

Most endpoints require authentication via Bearer token:

```
Authorization: Bearer <your-token>
```

## Response Format

All API responses follow this format:

### Success Response
```json
{
    "success": true,
    "message": "Success message",
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

## Status Codes

- `200 OK` - Successful request
- `201 Created` - Resource created successfully
- `204 No Content` - Successful request with no content
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

## Endpoints

### Health Check

Check the health status of the API and its services.

**Endpoint:** `GET /api/health`

**Authentication:** Not required

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

**Status Codes:**
- `200 OK` - All services healthy
- `503 Service Unavailable` - One or more services down

**Example:**
```bash
curl http://localhost:8000/api/health
```

---

## Middleware

### CORS Middleware

Handles Cross-Origin Resource Sharing (CORS) headers.

**Applied to:** All API routes

**Configuration:** See `.env` file
- `CORS_ALLOWED_ORIGINS`
- `CORS_ALLOWED_METHODS`
- `CORS_ALLOWED_HEADERS`

### Rate Limit Middleware

Limits the number of requests per IP address.

**Applied to:** All API routes

**Configuration:** See `.env` file
- `API_RATE_LIMIT` - Requests per hour (default: 100)

**Response when limit exceeded:**
```json
{
    "error": true,
    "message": "Too many requests. Please try again later."
}
```

### Auth Middleware

Validates authentication tokens.

**Applied to:** Protected routes

**Headers Required:**
```
Authorization: Bearer <token>
```

**Response when unauthorized:**
```json
{
    "error": true,
    "message": "Unauthorized"
}
```

---

## Creating New Endpoints

### Step 1: Define Route

Add to `api/routes.php`:

```php
use App\Controllers\UserController;

$router->get('/users', UserController::class . '@index');
$router->post('/users', UserController::class . '@store');
$router->get('/users/{id}', UserController::class . '@show');
$router->put('/users/{id}', UserController::class . '@update');
$router->delete('/users/{id}', UserController::class . '@destroy');
```

### Step 2: Create Controller

Create `src/Controllers/UserController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;

class UserController
{
    public function index(Request $request, Response $response, array $params): void
    {
        // Fetch users from database
        $users = [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Smith'],
        ];
        
        ResponseHelper::success($users);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = $params['id'];
        
        // Fetch user from database
        $user = ['id' => $id, 'name' => 'John Doe'];
        
        if (!$user) {
            ResponseHelper::notFound('User not found');
        }
        
        ResponseHelper::success($user);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            ResponseHelper::validationError($validator->errors());
        }

        // Create user in database
        $user = [
            'id' => 3,
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ];

        ResponseHelper::created($user, 'User created successfully');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = $params['id'];
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
        ]);

        if ($validator->fails()) {
            ResponseHelper::validationError($validator->errors());
        }

        // Update user in database
        $user = [
            'id' => $id,
            'name' => $request->input('name'),
        ];

        ResponseHelper::success($user, 'User updated successfully');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $id = $params['id'];
        
        // Delete user from database
        
        ResponseHelper::success(null, 'User deleted successfully');
    }
}
```

### Step 3: Add Middleware (Optional)

Protect routes with authentication:

```php
use App\Middleware\AuthMiddleware;

$router->post('/users', UserController::class . '@store')
    ->middleware(AuthMiddleware::class);
```

Or protect a group of routes:

```php
$router->group(['middleware' => [AuthMiddleware::class]], function($router) {
    $router->post('/users', UserController::class . '@store');
    $router->put('/users/{id}', UserController::class . '@update');
    $router->delete('/users/{id}', UserController::class . '@destroy');
});
```

---

## Validation Rules

Available validation rules:

- `required` - Field must be present and not empty
- `email` - Field must be a valid email address
- `min:n` - Field must be at least n characters
- `max:n` - Field must not exceed n characters
- `numeric` - Field must be numeric
- `integer` - Field must be an integer
- `url` - Field must be a valid URL
- `in:a,b,c` - Field must be one of the specified values
- `regex:pattern` - Field must match the regex pattern
- `confirmed` - Field must have a matching `field_confirmation`

**Example:**
```php
$validator = Validator::make($request->all(), [
    'email' => 'required|email',
    'password' => 'required|min:8|confirmed',
    'age' => 'required|integer|min:18',
    'role' => 'required|in:admin,user,guest',
]);
```

---

## Database Queries

Use the database service:

```php
use App\Core\Container;

$db = Container::getInstance()->get('database');

// Fetch all records
$users = $db->fetchAll('SELECT * FROM users WHERE active = ?', [1]);

// Fetch single record
$user = $db->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);

// Execute query (INSERT, UPDATE, DELETE)
$db->execute('UPDATE users SET name = ? WHERE id = ?', [$name, $id]);

// Get last insert ID
$userId = $db->lastInsertId();
```

---

## Response Helpers

Use response helpers for consistent API responses:

```php
use App\Helpers\Response as ResponseHelper;

// Success response
ResponseHelper::success($data, 'Success message');

// Error response
ResponseHelper::error('Error message', $errors, 400);

// Created response (201)
ResponseHelper::created($data, 'Resource created');

// No content response (204)
ResponseHelper::noContent();

// Not found response (404)
ResponseHelper::notFound('Resource not found');

// Unauthorized response (401)
ResponseHelper::unauthorized('Authentication required');

// Forbidden response (403)
ResponseHelper::forbidden('Insufficient permissions');

// Validation error response (422)
ResponseHelper::validationError($validator->errors());
```

---

## Testing APIs

### Using cURL

```bash
# GET request
curl http://localhost:8000/api/health

# POST request with JSON
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com"}'

# With authentication
curl http://localhost:8000/api/protected \
  -H "Authorization: Bearer your-token-here"

# PUT request
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane Doe"}'

# DELETE request
curl -X DELETE http://localhost:8000/api/users/1
```

### Using JavaScript (Fetch API)

```javascript
// GET request
const data = await window.api.get('/users');

// POST request
const newUser = await window.api.post('/users', {
    name: 'John Doe',
    email: 'john@example.com'
});

// PUT request
const updatedUser = await window.api.put('/users/1', {
    name: 'Jane Doe'
});

// DELETE request
await window.api.delete('/users/1');
```

---

## Best Practices

1. **Always validate input** - Use the Validator helper
2. **Use prepared statements** - Never concatenate SQL
3. **Return appropriate status codes** - Use response helpers
4. **Handle errors gracefully** - Use try-catch blocks
5. **Log errors** - Use the Logger service
6. **Implement rate limiting** - Protect against abuse
7. **Use authentication** - Protect sensitive endpoints
8. **Document your endpoints** - Update this file
9. **Version your API** - Use prefixes like `/api/v1/`
10. **Test thoroughly** - Test all endpoints before deployment

---

## Error Handling

Errors are automatically handled by the ErrorHandler class.

In development (`APP_DEBUG=true`):
- Full error details in response
- Stack traces included
- Detailed logging

In production (`APP_DEBUG=false`):
- Generic error messages
- No stack traces
- Errors logged to file

**Custom exception handling:**
```php
try {
    // Your code
} catch (Exception $e) {
    $logger = Container::getInstance()->get('logger');
    $logger->error($e->getMessage());
    ResponseHelper::error('Something went wrong', null, 500);
}
```

---

## Rate Limiting

Rate limiting is automatically applied to all API routes.

**Configuration:** `.env`
```env
API_RATE_LIMIT=100  # Requests per hour
```

**Response when limit exceeded:**
```json
{
    "error": true,
    "message": "Too many requests. Please try again later."
}
```

**Status Code:** `429 Too Many Requests`

---

## CORS Configuration

Configure CORS in `.env`:

```env
CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization
```

**For production:**
```env
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

---

## Logging

All API requests and errors are logged to `logs/app.log`.

**Log levels:**
- `debug` - Detailed debug information
- `info` - Informational messages
- `warning` - Warning messages
- `error` - Error messages
- `critical` - Critical errors

**Configuration:** `.env`
```env
LOG_LEVEL=info
LOG_FILE=logs/app.log
```

---

For more information, see:
- README.md - Full project documentation
- CODING_STANDARDS.md - Coding guidelines
- INSTALLATION.md - Setup instructions
