#!/bin/bash

# Development Server Startup Script

echo "==================================="
echo "  PHP Development Server"
echo "==================================="
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in PATH"
    echo "Please install PHP 7.4 or higher"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "PHP Version: $PHP_VERSION"

# Check if .env exists
if [ ! -f ".env" ]; then
    echo ""
    echo "Warning: .env file not found"
    echo "Creating from .env.example..."
    cp .env.example .env
    echo "Please edit .env with your configuration"
    echo ""
fi

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo ""
    echo "Warning: vendor/ directory not found"
    echo "Running: composer install"
    
    if command -v composer &> /dev/null; then
        composer install
    else
        echo "Error: Composer not found. Please install dependencies manually:"
        echo "  curl -sS https://getcomposer.org/installer | php"
        echo "  php composer.phar install"
        exit 1
    fi
fi

# Set default host and port
HOST="${1:-localhost}"
PORT="${2:-8000}"

echo ""
echo "Starting server at: http://$HOST:$PORT"
echo ""
echo "Available URLs:"
echo "  Frontend:  http://$HOST:$PORT/"
echo "  API:       http://$HOST:$PORT/api/health"
echo "  Admin:     http://$HOST:$PORT/admin/login"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Start PHP built-in server
php -S "$HOST:$PORT" -t public_html
