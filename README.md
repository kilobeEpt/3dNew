# PHP API Platform with Admin Panel

A modern, lightweight PHP-based API platform with built-in admin panel support, designed for shared hosting environments.

## Features

- **PSR-4 Autoloading**: Composer-based autoloading with organized namespace structure
- **Environment Configuration**: `.env` file support for managing environment-specific settings
- **Service Container**: Dependency injection container for managing application services
- **Lightweight Router**: RESTful routing with middleware support
- **Middleware System**: Built-in CORS, Authentication, and Rate Limiting middleware
- **Database Abstraction**: PDO-based database layer with prepared statements
- **Email Service**: PHPMailer integration for sending emails
- **Request Validation**: Flexible validation helper for input data
- **Error Handling**: Environment-aware error handling with detailed debugging
- **Logging**: File-based logging with configurable log levels
- **Asset Management**: CSS/JS minification support via npm scripts

## Directory Structure

```
project/
├── api/                    # API endpoints
│   ├── index.php          # API front controller
│   ├── routes.php         # API route definitions
│   └── .htaccess          # Apache rewrite rules
├── admin/                 # Admin panel
│   ├── index.php          # Admin front controller
│   ├── routes.php         # Admin route definitions
│   ├── views/             # Admin view templates
│   └── .htaccess          # Apache rewrite rules
├── public_html/           # Public frontend
│   ├── index.html         # Main HTML entry point
│   └── assets/            # Static assets
│       ├── css/           # Stylesheets
│       ├── js/            # JavaScript files
│       ├── images/        # Image assets
│       └── dist/          # Minified/built assets
├── src/                   # PHP source code (PSR-4)
│   ├── Core/              # Core framework classes
│   │   ├── Container.php  # Service container
│   │   ├── Config.php     # Configuration loader
│   │   ├── Database.php   # Database connection
│   │   ├── Router.php     # HTTP router
│   │   ├── Request.php    # HTTP request wrapper
│   │   ├── Response.php   # HTTP response wrapper
│   │   ├── Logger.php     # Logging service
│   │   └── ErrorHandler.php # Error handling
│   ├── Controllers/       # Controller classes
│   ├── Middleware/        # Middleware classes
│   │   ├── CorsMiddleware.php
│   │   ├── AuthMiddleware.php
│   │   └── RateLimitMiddleware.php
│   ├── Helpers/           # Helper classes
│   │   ├── Response.php   # Response helper
│   │   └── Validator.php  # Validation helper
│   └── Services/          # Service classes
│       └── Mailer.php     # Email service
├── templates/             # Shared templates
│   └── email/             # Email templates
├── logs/                  # Application logs
├── build/                 # Build scripts
│   ├── minify-css.js      # CSS minification
│   └── minify-js.js       # JS minification
├── bootstrap.php          # Application bootstrap
├── composer.json          # PHP dependencies
├── package.json           # Node.js dependencies
├── .env.example           # Environment variables template
└── .gitignore             # Git ignore rules
```

## Installation

### Prerequisites

- PHP 7.4 or higher
- Composer
- MySQL/MariaDB (optional, for database features)
- Node.js and npm (optional, for asset building)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd project
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure environment variables**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` and set your configuration:
   - Database credentials
   - Mail server settings
   - Application environment (development/production)
   - Security settings

4. **Set up permissions**
   ```bash
   chmod -R 755 logs/
   chmod -R 755 public_html/assets/
   ```

5. **Install Node.js dependencies (optional)**
   ```bash
   npm install
   ```

6. **Build assets (optional)**
   ```bash
   npm run build
   ```

### Shared Hosting Setup

For shared hosting environments (cPanel, Plesk, etc.):

1. Upload all files to your hosting account
2. Point your domain to the `public_html` directory
3. Create a subdomain or directory for the API (e.g., `api.yourdomain.com` → `/api`)
4. Create a subdomain or directory for admin (e.g., `admin.yourdomain.com` → `/admin`)
5. Ensure `.htaccess` files are enabled and `mod_rewrite` is active

## Environment Variables

### Application Settings
- `APP_ENV`: Application environment (`development`, `production`)
- `APP_DEBUG`: Enable/disable debug mode (`true`, `false`)
- `APP_URL`: Application base URL

### Database Configuration
- `DB_HOST`: Database host
- `DB_PORT`: Database port (default: 3306)
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password
- `DB_CHARSET`: Database character set (default: utf8mb4)

### Mail Configuration
- `MAIL_HOST`: SMTP server host
- `MAIL_PORT`: SMTP server port
- `MAIL_USERNAME`: SMTP username
- `MAIL_PASSWORD`: SMTP password
- `MAIL_ENCRYPTION`: Encryption type (`tls`, `ssl`)
- `MAIL_FROM_ADDRESS`: Default sender email
- `MAIL_FROM_NAME`: Default sender name

### Security Settings
- `JWT_SECRET`: Secret key for JWT tokens
- `API_RATE_LIMIT`: API rate limit per hour (default: 100)

### CORS Settings
- `CORS_ALLOWED_ORIGINS`: Allowed origins (`*` for all)
- `CORS_ALLOWED_METHODS`: Allowed HTTP methods
- `CORS_ALLOWED_HEADERS`: Allowed headers

### Logging
- `LOG_LEVEL`: Minimum log level (`debug`, `info`, `warning`, `error`, `critical`)
- `LOG_FILE`: Path to log file (default: `logs/app.log`)

## Usage

### Creating API Routes

Add routes in `api/routes.php`:

```php
use App\Controllers\UserController;

$router->get('/users', UserController::class . '@index');
$router->get('/users/{id}', UserController::class . '@show');
$router->post('/users', UserController::class . '@store');
$router->put('/users/{id}', UserController::class . '@update');
$router->delete('/users/{id}', UserController::class . '@destroy');
```

### Creating Controllers

Create a controller in `src/Controllers/`:

```php
<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Response as ResponseHelper;

class UserController
{
    public function index(Request $request, Response $response, array $params): void
    {
        $users = []; // Fetch from database
        ResponseHelper::success($users);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = $params['id'];
        $user = []; // Fetch from database
        
        if (!$user) {
            ResponseHelper::notFound('User not found');
        }
        
        ResponseHelper::success($user);
    }
}
```

### Using Middleware

Apply middleware to routes:

```php
use App\Middleware\AuthMiddleware;

$router->get('/protected', function($request, $response) {
    ResponseHelper::success(['message' => 'Protected content']);
})->middleware(AuthMiddleware::class);
```

Or apply to route groups:

```php
$router->group(['middleware' => [AuthMiddleware::class]], function($router) {
    $router->get('/profile', ProfileController::class . '@show');
    $router->put('/profile', ProfileController::class . '@update');
});
```

### Request Validation

Validate incoming data:

```php
use App\Helpers\Validator;
use App\Helpers\Response as ResponseHelper;

$validator = Validator::make($request->all(), [
    'email' => 'required|email',
    'password' => 'required|min:8',
    'age' => 'numeric|min:18',
]);

if ($validator->fails()) {
    ResponseHelper::validationError($validator->errors());
}
```

### Database Queries

Use the database service:

```php
use App\Core\Container;

$db = Container::getInstance()->get('database');

// Fetch all
$users = $db->fetchAll('SELECT * FROM users WHERE active = ?', [1]);

// Fetch one
$user = $db->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);

// Insert
$db->execute('INSERT INTO users (name, email) VALUES (?, ?)', [$name, $email]);
$lastId = $db->lastInsertId();
```

### Sending Emails

Send emails using the mailer service:

```php
use App\Core\Container;

$mailer = Container::getInstance()->get('mailer');
$mailer->send(
    'user@example.com',
    'Welcome!',
    '<h1>Welcome to our platform!</h1>'
);
```

### Logging

Log messages:

```php
use App\Core\Container;

$logger = Container::getInstance()->get('logger');

$logger->debug('Debug message');
$logger->info('Info message');
$logger->warning('Warning message');
$logger->error('Error message');
$logger->critical('Critical message');
```

## API Endpoints

### Health Check

**GET** `/api/health`

Returns the health status of the API and its services.

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

## Development

### Running Locally

For local development, you can use PHP's built-in server:

```bash
# Start API server
cd api && php -S localhost:8000

# Or start from project root
php -S localhost:8000 -t public_html
```

### Building Assets

```bash
# Build CSS and JS
npm run build

# Build only CSS
npm run build:css

# Build only JS
npm run build:js
```

### Watching for Changes

```bash
npm run watch
```

## Testing

The health check endpoint can be used to verify the setup:

```bash
curl http://localhost:8000/api/health
```

Or visit the frontend and click the "Check API Health" button.

## Security Considerations

1. **Never commit `.env` file**: Keep sensitive credentials out of version control
2. **Use HTTPS in production**: Encrypt data in transit
3. **Implement proper authentication**: The provided AuthMiddleware is a placeholder
4. **Validate all inputs**: Use the Validator helper for all user inputs
5. **Keep dependencies updated**: Regularly update Composer and npm packages
6. **Set appropriate file permissions**: Restrict write access to necessary directories only
7. **Enable error logging in production**: Set `APP_DEBUG=false` in production

## Troubleshooting

### Apache .htaccess not working
- Ensure `mod_rewrite` is enabled
- Check that `AllowOverride All` is set in Apache configuration

### Database connection errors
- Verify database credentials in `.env`
- Ensure database server is running
- Check firewall rules

### Permission errors
- Set proper permissions: `chmod -R 755 logs/`
- Ensure web server user has write access to logs directory

### Email not sending
- Verify SMTP credentials in `.env`
- Check firewall/port access to SMTP server
- Review logs for detailed error messages

## License

MIT License

## Support

For issues and questions, please open an issue in the repository.
