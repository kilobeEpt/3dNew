# PHP API Platform with Admin Panel

A modern, lightweight PHP-based API platform with built-in admin panel support, designed for shared hosting environments.

> **🚨 nginx 403 Forbidden?** See [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md) for complete solution.
>
> **📚 New to deployment?** See [DEPLOYMENT_GUIDES_README.md](DEPLOYMENT_GUIDES_README.md) for all guides.
>
> **🆘 Need help?** See [TROUBLESHOOTING.md](TROUBLESHOOTING.md) or run `bash scripts/verify-server.sh`

## Features

- **PSR-4 Autoloading**: Composer-based autoloading with organized namespace structure
- **Environment Configuration**: `.env` file support for managing environment-specific settings
- **Service Container**: Dependency injection container for managing application services
- **Lightweight Router**: RESTful routing with middleware support
- **Middleware System**: Built-in CORS, Authentication, Rate Limiting, and CSRF middleware
- **Database Abstraction**: PDO-based database layer with prepared statements
- **Database Migrations**: SQL-based migration system with tracking
- **Database Seeding**: PHP-based seed system for initial data
- **Active Record Models**: ORM-like models with CRUD operations and soft deletes
- **Repository Pattern**: Advanced data access layer with pagination and search
- **Email Service**: PHPMailer integration for sending emails with HTML templates
- **Request Validation**: Flexible validation helper for input data
- **Error Handling**: Environment-aware error handling with detailed debugging
- **Logging**: File-based logging with configurable log levels
- **Asset Management**: CSS/JS minification support via npm scripts
- **Public API**: REST endpoints with CAPTCHA, CSRF protection, and email notifications
- **OpenAPI Documentation**: Complete API specification with Swagger support

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
│   ├── Models/            # Active Record models
│   │   ├── BaseModel.php  # Base model class
│   │   ├── AdminUser.php  # Admin user model
│   │   ├── Service.php    # Service model
│   │   └── ...            # Other models
│   ├── Repositories/      # Repository classes
│   │   ├── BaseRepository.php # Base repository
│   │   ├── ServiceRepository.php
│   │   └── ...            # Other repositories
│   ├── Helpers/           # Helper classes
│   │   ├── Response.php   # Response helper
│   │   └── Validator.php  # Validation helper
│   └── Services/          # Service classes
│       └── Mailer.php     # Email service
├── database/              # Database management
│   ├── migrations/        # SQL migration files
│   ├── seeds/             # Database seed files
│   ├── schema/            # Schema documentation
│   ├── migrate.php        # Migration runner
│   └── seed.php           # Seed runner
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

- PHP 8.2 or higher
- Composer 2.x (or Composer 1.9.0+ with automatic 2.x download)
- MySQL/MariaDB 5.7+ / 10.2+
- Node.js and npm (optional, for asset building)

### Quick Setup (Automated) ⚡

**Deploy everything with a single command:**

```bash
bash scripts/setup.sh
```

The automated setup script will:
- ✅ Check PHP version and extensions
- ✅ Test MySQL connectivity
- ✅ Create all necessary directories
- ✅ Set correct permissions
- ✅ Configure `.env` interactively
- ✅ Install Composer dependencies
- ✅ Run database migrations
- ✅ Seed initial data
- ✅ Create admin users
- ✅ Verify complete deployment

**Default Admin Credentials:**
- Username: `admin` / Password: `admin123`
- **⚠️ Change immediately after first login!**

**See:** `SETUP_README.md` for quick guide, `SETUP_SCRIPT_GUIDE.md` for complete documentation.

---

### Manual Setup Steps

If you prefer manual installation or the automated script fails:

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd project
   ```

2. **Install PHP dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Configure environment variables**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` and set your configuration:
   - Database credentials
   - Mail server settings
   - Application environment (development/production)
   - Security settings (JWT_SECRET - minimum 64 characters!)

4. **Configure CAPTCHA (for public API)**
   
   Get your keys from:
   - reCAPTCHA: https://www.google.com/recaptcha/admin
   - hCaptcha: https://dashboard.hcaptcha.com/signup
   
   Add to `.env`:
   ```env
   CAPTCHA_TYPE=recaptcha
   RECAPTCHA_SITE_KEY=your-site-key
   RECAPTCHA_SECRET=your-secret-key
   ADMIN_EMAIL=admin@example.com
   ```

5. **Set up permissions**
   ```bash
   chmod -R 755 logs/
   chmod -R 755 public_html/assets/
   ```

6. **Install Node.js dependencies (optional)**
   ```bash
   npm install
   ```

7. **Build assets (optional)**
   ```bash
   npm run build
   ```

8. **Run database migrations**
   ```bash
   php database/migrate.php
   ```

9. **Seed initial data (optional)**
   ```bash
   php database/seed.php
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
- `CAPTCHA_TYPE`: CAPTCHA provider (`recaptcha` or `hcaptcha`)
- `RECAPTCHA_SITE_KEY`: reCAPTCHA site key
- `RECAPTCHA_SECRET`: reCAPTCHA secret key
- `HCAPTCHA_SITE_KEY`: hCaptcha site key
- `HCAPTCHA_SECRET`: hCaptcha secret key
- `ADMIN_EMAIL`: Admin email for notifications

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

### Database

The platform includes a comprehensive database management system with migrations, seeds, and data access layers.

#### Running Migrations

```bash
# Run all pending migrations
php database/migrate.php
```

Migrations are tracked automatically and only executed once.

#### Seeding Data

```bash
# Insert initial data
php database/seed.php
```

#### Using Models (Active Record Pattern)

Models provide ORM-like CRUD operations with soft delete support:

```php
use App\Core\Container;
use App\Models\Service;

$database = Container::getInstance()->get('database');
$serviceModel = new Service($database);

// Find all
$services = $serviceModel->all();

// Find by ID
$service = $serviceModel->find(1);

// Find with conditions
$activeServices = $serviceModel->where(['is_visible' => true]);

// Create
$id = $serviceModel->create([
    'name' => 'Custom Service',
    'slug' => 'custom-service',
    'price_type' => 'quote',
]);

// Update
$serviceModel->update($id, ['name' => 'Updated Name']);

// Delete (soft delete if enabled)
$serviceModel->delete($id);

// Pagination
$result = $serviceModel->paginate($page = 1, $perPage = 20);
```

#### Using Repositories (Repository Pattern)

Repositories provide advanced query methods:

```php
use App\Repositories\ServiceRepository;

$serviceRepo = new ServiceRepository($database);

// Custom methods
$service = $serviceRepo->findBySlug('custom-service');
$featured = $serviceRepo->findFeatured(6);
$visible = $serviceRepo->findVisible();

// Base methods
$paginated = $serviceRepo->paginate(1, 20, ['is_visible' => true]);
$count = $serviceRepo->count(['is_visible' => true]);
$results = $serviceRepo->search('name', 'aluminum');
```

See [DATABASE.md](DATABASE.md) for complete documentation.

### Raw Database Queries

For direct database access:

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

### Testing the API

1. **Health check endpoint:**
   ```bash
   curl http://localhost:8000/api/health
   ```

2. **Test public API endpoints:**
   ```bash
   # List services
   curl http://localhost:8000/api/services
   
   # List materials
   curl http://localhost:8000/api/materials
   
   # Get public settings
   curl http://localhost:8000/api/settings
   ```

3. **Interactive testing:**
   Open in browser:
   ```
   http://localhost:8000/api-example.html
   ```

Or visit the frontend and click the "Check API Health" button.

## Security Considerations

1. **Never commit `.env` file**: Keep sensitive credentials out of version control
2. **Use HTTPS in production**: Encrypt data in transit
3. **Configure CAPTCHA**: Use reCAPTCHA or hCaptcha for form submissions
4. **Restrict CORS origins**: Set specific domains in production, not `*`
5. **Implement proper authentication**: The provided AuthMiddleware is a placeholder
6. **Validate all inputs**: Use the Validator helper for all user inputs
7. **Keep dependencies updated**: Regularly update Composer and npm packages
8. **Set appropriate file permissions**: Restrict write access to necessary directories only
9. **Enable error logging in production**: Set `APP_DEBUG=false` in production
10. **Monitor rate limits**: Adjust `API_RATE_LIMIT` based on your needs

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

## Documentation

### 🚀 Deployment & Troubleshooting (NEW!)

- **[DEPLOYMENT_GUIDES_README.md](DEPLOYMENT_GUIDES_README.md)** - 📚 **START HERE** - Complete documentation overview
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - General deployment guide (Apache & nginx)
- **[DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md)** - **nginx 403 Forbidden solution** & nginx-specific deployment
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Common issues and solutions (database, email, uploads, etc.)
- **[SETUP_SCRIPT_GUIDE.md](SETUP_SCRIPT_GUIDE.md)** - Automated setup script (`bash scripts/setup.sh`)

**Quick Actions:**
```bash
# Automated setup
bash scripts/setup.sh

# Verify server configuration
bash scripts/verify-server.sh
```

### API & Database Documentation

- [DATABASE.md](DATABASE.md) - Complete database documentation, migrations, seeds, and data access layers
- [API.md](API.md) - General API endpoint documentation
- [API_PUBLIC.md](API_PUBLIC.md) - **Public API documentation with examples**
- [ADMIN_API.md](ADMIN_API.md) - Admin API documentation
- [QUICKSTART_API.md](QUICKSTART_API.md) - **Quick start guide for the public API**
- [openapi.yaml](openapi.yaml) - **OpenAPI/Swagger specification**

### Setup & Configuration

- [INSTALLATION.md](INSTALLATION.md) - Detailed installation guide
- [QUICKSTART.md](QUICKSTART.md) - Quick start guide
- [SSL_SETUP.md](SSL_SETUP.md) - SSL certificate setup
- [LAUNCH_CHECKLIST.md](LAUNCH_CHECKLIST.md) - Pre-launch verification checklist

### nginx Router

- [NGINX_ROUTER_README.md](NGINX_ROUTER_README.md) - Complete nginx router documentation
- [NGINX_ROUTER_DEPLOYMENT.md](NGINX_ROUTER_DEPLOYMENT.md) - nginx router deployment guide
- [NGINX_ROUTER_IMPLEMENTATION.md](NGINX_ROUTER_IMPLEMENTATION.md) - Implementation details
- [NGINX_ROUTER_CHECKLIST.md](NGINX_ROUTER_CHECKLIST.md) - Deployment checklist

### Development

- [CODING_STANDARDS.md](CODING_STANDARDS.md) - Code style and standards
- [database/schema/README.md](database/schema/README.md) - Database schema documentation
- [database/TESTING.md](database/TESTING.md) - Database testing guide

### SEO & Features

- [SEO_GUIDE.md](SEO_GUIDE.md) - Complete SEO implementation guide
- [CALCULATOR_FEATURE.md](CALCULATOR_FEATURE.md) - 3D printing calculator feature

## License

MIT License

## Support

For issues and questions:

1. **Check documentation** - [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
2. **Run diagnostics** - `bash scripts/verify-server.sh`
3. **Open an issue** in the repository

**nginx 403 Forbidden?** See [DEPLOYMENT_NGINX.md](DEPLOYMENT_NGINX.md#nginx-403-forbidden---complete-solution)
