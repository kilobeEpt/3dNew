#!/bin/bash

echo "=== Verifying Project Structure ==="
echo ""

check_file() {
    if [ -f "$1" ]; then
        echo "✓ $1"
        return 0
    else
        echo "✗ $1 (missing)"
        return 1
    fi
}

check_dir() {
    if [ -d "$1" ]; then
        echo "✓ $1/"
        return 0
    else
        echo "✗ $1/ (missing)"
        return 1
    fi
}

echo "Core Files:"
check_file "composer.json"
check_file "package.json"
check_file ".env.example"
check_file ".gitignore"
check_file "bootstrap.php"
check_file "README.md"
check_file "CODING_STANDARDS.md"

echo ""
echo "API Structure:"
check_dir "api"
check_file "api/index.php"
check_file "api/routes.php"
check_file "api/.htaccess"

echo ""
echo "Admin Structure:"
check_dir "admin"
check_file "admin/index.php"
check_file "admin/routes.php"
check_file "admin/.htaccess"
check_dir "admin/views"

echo ""
echo "Frontend Structure:"
check_dir "public_html"
check_file "public_html/index.html"
check_file "public_html/.htaccess"
check_dir "public_html/assets"
check_dir "public_html/assets/css"
check_dir "public_html/assets/js"
check_dir "public_html/assets/images"
check_file "public_html/assets/css/main.css"
check_file "public_html/assets/js/main.js"

echo ""
echo "Source Code (PSR-4):"
check_dir "src"
check_dir "src/Core"
check_file "src/Core/Container.php"
check_file "src/Core/Config.php"
check_file "src/Core/Database.php"
check_file "src/Core/Router.php"
check_file "src/Core/Request.php"
check_file "src/Core/Response.php"
check_file "src/Core/Logger.php"
check_file "src/Core/ErrorHandler.php"

echo ""
echo "Controllers:"
check_dir "src/Controllers"
check_file "src/Controllers/HealthController.php"
check_dir "src/Controllers/Admin"
check_file "src/Controllers/Admin/DashboardController.php"

echo ""
echo "Middleware:"
check_dir "src/Middleware"
check_file "src/Middleware/CorsMiddleware.php"
check_file "src/Middleware/AuthMiddleware.php"
check_file "src/Middleware/RateLimitMiddleware.php"

echo ""
echo "Helpers:"
check_dir "src/Helpers"
check_file "src/Helpers/Response.php"
check_file "src/Helpers/Validator.php"

echo ""
echo "Services:"
check_dir "src/Services"
check_file "src/Services/Mailer.php"

echo ""
echo "Templates:"
check_dir "templates"
check_dir "templates/email"
check_file "templates/email/base.html"

echo ""
echo "Build Scripts:"
check_dir "build"
check_file "build/minify-css.js"
check_file "build/minify-js.js"

echo ""
echo "=== Structure Verification Complete ==="
