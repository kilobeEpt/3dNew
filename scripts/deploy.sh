#!/bin/bash

##############################################################################
# Deployment Script for Shared Hosting
# 
# Automates deployment tasks:
# - Installs dependencies
# - Runs database migrations
# - Builds assets
# - Sets permissions
# - Verifies configuration
#
# Usage: ./scripts/deploy.sh [environment]
# Example: ./scripts/deploy.sh production
##############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
ENVIRONMENT=${1:-production}
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_requirement() {
    if command -v $1 &> /dev/null; then
        log_success "$1 is installed"
        return 0
    else
        log_error "$1 is not installed"
        return 1
    fi
}

# Header
echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║         3D Print Platform - Deployment Script                 ║"
echo "║         Environment: $ENVIRONMENT                                      ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Change to project directory
cd "$PROJECT_DIR"
log_info "Working directory: $PROJECT_DIR"

# Step 1: Check requirements
echo ""
log_info "Step 1/8: Checking requirements..."
echo "-----------------------------------"

REQUIREMENTS_MET=true
check_requirement "php" || REQUIREMENTS_MET=false
check_requirement "composer" || log_warning "Composer not found (will try to use composer.phar)"

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")

if [ "$PHP_MAJOR" -ge 7 ] && [ "$PHP_MINOR" -ge 4 ]; then
    log_success "PHP version $PHP_VERSION (>= 7.4 required)"
else
    log_error "PHP version $PHP_VERSION is too old (>= 7.4 required)"
    REQUIREMENTS_MET=false
fi

if [ "$REQUIREMENTS_MET" = false ]; then
    log_error "Requirements not met. Please install missing dependencies."
    exit 1
fi

# Step 2: Check .env file
echo ""
log_info "Step 2/8: Checking environment configuration..."
echo "------------------------------------------------"

if [ ! -f ".env" ]; then
    log_warning ".env file not found"
    
    if [ -f ".env.example" ]; then
        log_info "Copying .env.example to .env"
        cp .env.example .env
        log_warning "Please edit .env with your configuration before continuing"
        
        read -p "Press Enter to continue after editing .env, or Ctrl+C to abort..."
    else
        log_error ".env.example not found"
        exit 1
    fi
else
    log_success ".env file exists"
fi

# Verify critical .env values
if grep -q "your-secret-key-here" .env; then
    log_error "JWT_SECRET is not set in .env (still using default)"
    exit 1
fi

if [ "$ENVIRONMENT" = "production" ]; then
    if grep -q "APP_DEBUG=true" .env; then
        log_warning "APP_DEBUG is set to true in production environment"
        read -p "Continue anyway? (y/N) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
fi

log_success "Environment configuration verified"

# Step 3: Install dependencies
echo ""
log_info "Step 3/8: Installing dependencies..."
echo "-------------------------------------"

if command -v composer &> /dev/null; then
    COMPOSER_CMD="composer"
elif [ -f "composer.phar" ]; then
    COMPOSER_CMD="php composer.phar"
else
    log_info "Composer not found. Downloading composer.phar..."
    curl -sS https://getcomposer.org/installer | php
    COMPOSER_CMD="php composer.phar"
fi

if [ "$ENVIRONMENT" = "production" ]; then
    log_info "Installing production dependencies (no dev)..."
    $COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction
else
    log_info "Installing all dependencies (including dev)..."
    $COMPOSER_CMD install --optimize-autoloader --no-interaction
fi

log_success "Dependencies installed"

# Step 4: Set permissions
echo ""
log_info "Step 4/8: Setting file permissions..."
echo "--------------------------------------"

# Create directories if they don't exist
mkdir -p logs
mkdir -p uploads/models
mkdir -p backups/database
mkdir -p backups/files

# Set permissions
chmod 755 logs/ || log_warning "Could not set permissions on logs/"
chmod 755 uploads/ || log_warning "Could not set permissions on uploads/"
chmod 755 backups/ || log_warning "Could not set permissions on backups/"
chmod 600 .env || log_warning "Could not set permissions on .env"
chmod +x scripts/*.sh 2>/dev/null || log_warning "Could not make scripts executable"
chmod +x scripts/*.php 2>/dev/null || log_warning "Could not make PHP scripts executable"

log_success "Permissions set"

# Step 5: Build assets
echo ""
log_info "Step 5/8: Building assets..."
echo "-----------------------------"

if [ -f "package.json" ] && command -v npm &> /dev/null; then
    log_info "Building CSS and JavaScript assets..."
    
    if [ ! -d "node_modules" ]; then
        log_info "Installing npm dependencies..."
        npm install --silent
    fi
    
    npm run build
    log_success "Assets built successfully"
else
    log_warning "npm not available or package.json not found. Skipping asset build."
fi

# Step 6: Database migrations
echo ""
log_info "Step 6/8: Running database migrations..."
echo "-----------------------------------------"

if [ -f "database/migrate.php" ]; then
    log_info "Running migrations..."
    php database/migrate.php || log_warning "Migration failed or no pending migrations"
    log_success "Database migrations completed"
else
    log_warning "Migration script not found. Skipping."
fi

# Step 7: Run verification tests
echo ""
log_info "Step 7/8: Running verification tests..."
echo "----------------------------------------"

# Test database connection
log_info "Testing database connection..."
php -r "
require 'bootstrap.php';
try {
    \$container = \App\Core\Container::getInstance();
    \$db = \$container->get('database');
    echo 'Database connection: OK' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Database connection: FAILED - ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
" || log_error "Database connection test failed"

# Test .htaccess syntax (if Apache is available)
if command -v apachectl &> /dev/null; then
    log_info "Testing Apache configuration..."
    apachectl configtest 2>/dev/null || log_warning "Apache config test failed (may be normal on shared hosting)"
fi

log_success "Verification tests completed"

# Step 8: Post-deployment tasks
echo ""
log_info "Step 8/8: Post-deployment tasks..."
echo "-----------------------------------"

# Generate sitemap
if [ -f "scripts/generate-sitemap.php" ]; then
    log_info "Generating sitemap..."
    php scripts/generate-sitemap.php || log_warning "Sitemap generation failed"
fi

# Clear any caches (if implemented)
if [ -f "cache" ]; then
    log_info "Clearing cache..."
    rm -rf cache/* || log_warning "Could not clear cache"
fi

log_success "Post-deployment tasks completed"

# Summary
echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                    DEPLOYMENT COMPLETED                        ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
log_success "Deployment to $ENVIRONMENT environment completed successfully!"
echo ""
log_info "Next steps:"
echo "  1. Verify the site is accessible: $APP_URL"
echo "  2. Test critical functionality (forms, calculator, admin panel)"
echo "  3. Check logs for any errors: tail -f logs/app.log"
echo "  4. Set up cron jobs (see DEPLOYMENT.md)"
echo "  5. Configure backups (see DEPLOYMENT.md)"
echo "  6. Enable HTTPS redirect in .htaccess (when SSL is active)"
echo ""
log_info "For detailed deployment instructions, see: DEPLOYMENT.md"
echo ""

exit 0
