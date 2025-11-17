# Quick Start Guide

Get up and running in 5 minutes!

## Prerequisites

- PHP 7.4+
- Composer
- MySQL (optional)

## Installation

```bash
# 1. Clone and enter directory
git clone <repository-url>
cd project

# 2. Install dependencies
composer install

# 3. Set up environment
cp .env.example .env
# Edit .env with your settings (optional for basic testing)

# 4. Start development server
./dev-server.sh
# Or: php -S localhost:8000 -t public_html
```

## Test It Works

Open browser: http://localhost:8000

Click the "Check API Health" button.

You should see:
```json
{
    "status": "healthy",
    "timestamp": "2024-01-01T12:00:00+00:00",
    "services": {
        "api": "up"
    }
}
```

## What's Next?

### Create Your First Endpoint

**1. Add route** in `api/routes.php`:
```php
$router->get('/hello', function($request, $response) {
    use App\Helpers\Response as ResponseHelper;
    ResponseHelper::success(['message' => 'Hello World!']);
});
```

**2. Test it:**
```bash
curl http://localhost:8000/api/hello
```

### Create a Controller

**1. Create** `src/Controllers/ExampleController.php`:
```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Response as ResponseHelper;

class ExampleController
{
    public function index(Request $request, Response $response, array $params): void
    {
        $data = [
            'items' => ['Item 1', 'Item 2', 'Item 3']
        ];
        ResponseHelper::success($data);
    }
}
```

**2. Add route** in `api/routes.php`:
```php
use App\Controllers\ExampleController;

$router->get('/items', ExampleController::class . '@index');
```

**3. Test it:**
```bash
curl http://localhost:8000/api/items
```

## Directory Structure

```
project/
├── api/           # REST API endpoints
├── admin/         # Admin panel
├── public_html/   # Frontend
├── src/           # PHP code (PSR-4)
│   ├── Core/      # Framework classes
│   ├── Controllers/
│   ├── Middleware/
│   ├── Helpers/
│   └── Services/
├── templates/     # Email templates
├── logs/          # Log files
└── vendor/        # Composer dependencies
```

## Key Files

- `bootstrap.php` - Initializes services
- `api/routes.php` - Define API routes
- `.env` - Configuration
- `composer.json` - PHP dependencies

## Common Commands

```bash
# Start server
./dev-server.sh

# Install dependencies
composer install

# Build assets
npm install
npm run build

# Verify structure
./verify-structure.sh

# View logs
tail -f logs/app.log
```

## Configuration

Edit `.env` for:
- Database connection
- Mail settings
- Debug mode
- CORS settings
- Rate limiting

## Documentation

- **README.md** - Full documentation
- **API.md** - API reference
- **INSTALLATION.md** - Detailed setup
- **CODING_STANDARDS.md** - Code guidelines

## Need Help?

1. Check the README.md
2. Review API.md for examples
3. Check logs: `logs/app.log`
4. Visit documentation files

## Examples

### POST Request with Validation

```php
use App\Helpers\Validator;
use App\Helpers\Response as ResponseHelper;

public function store(Request $request, Response $response, array $params): void
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'name' => 'required|min:3',
    ]);

    if ($validator->fails()) {
        ResponseHelper::validationError($validator->errors());
    }

    // Save to database
    ResponseHelper::created(['id' => 1, 'name' => $request->input('name')]);
}
```

### Database Query

```php
use App\Core\Container;

$db = Container::getInstance()->get('database');
$users = $db->fetchAll('SELECT * FROM users WHERE active = ?', [1]);
```

### Send Email

```php
use App\Core\Container;

$mailer = Container::getInstance()->get('mailer');
$mailer->send('user@example.com', 'Subject', '<h1>Body</h1>');
```

## Production Deployment

Before deploying:

1. Set `APP_DEBUG=false` in `.env`
2. Run `composer install --no-dev --optimize-autoloader`
3. Build assets: `npm run build`
4. Set proper file permissions
5. Configure HTTPS
6. Set up backups

See INSTALLATION.md for shared hosting setup.

---

**Happy coding! 🚀**
