#!/bin/bash

###############################################################################
# Complete Auto-Deployment Setup Script
# 
# This script fully automates the deployment of the 3D Print Platform.
# It performs all necessary checks, configurations, and installations
# to get the site up and running with a single command.
#
# Features:
# - PHP 8.2+ version check
# - MySQL connectivity check
# - Automatic directory creation with correct permissions
# - Interactive .env configuration
# - Composer dependency installation
# - Database migrations and seeding
# - Admin user creation
# - Complete deployment verification
#
# Usage: bash scripts/setup.sh
#
# The script is idempotent - safe to run multiple times.
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m' # No Color

# Get project root
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

# Create logs directory if it doesn't exist
mkdir -p logs

# Setup logging (log to both file and console)
LOG_FILE="logs/setup.log"
exec > >(tee -a "$LOG_FILE")
exec 2>&1

# Print functions
print_header() {
    echo ""
    echo -e "${BLUE}════════════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN}  $1${NC}"
    echo -e "${BLUE}════════════════════════════════════════════════════════════════${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${CYAN}→ $1${NC}"
}

# Main header
echo ""
echo -e "${MAGENTA}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${MAGENTA}║                                                                ║${NC}"
echo -e "${MAGENTA}║           3D Print Platform - Auto-Deployment Setup           ║${NC}"
echo -e "${MAGENTA}║                                                                ║${NC}"
echo -e "${MAGENTA}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${CYAN}Starting automated setup process...${NC}"
echo -e "${CYAN}Logs are being saved to: ${LOG_FILE}${NC}"
echo ""

# Step 1: Check PHP Version
print_header "Step 1/9: Checking PHP Version"

print_info "Detecting PHP version..."
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "PHP version: $PHP_VERSION"

if php -r 'exit(version_compare(PHP_VERSION, "8.2.0", "<") ? 1 : 0);'; then
    print_success "PHP 8.2+ detected (version $PHP_VERSION)"
else
    print_error "PHP 8.2 or higher required!"
    print_error "Current PHP version: $PHP_VERSION"
    echo ""
    echo "Please upgrade PHP to 8.2 or higher before continuing."
    exit 1
fi

# Check required PHP extensions
print_info "Checking required PHP extensions..."
REQUIRED_EXTENSIONS=("pdo_mysql" "mbstring" "openssl" "json" "fileinfo")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -r "exit(extension_loaded('$ext') ? 0 : 1);"; then
        print_success "Extension available: $ext"
    else
        print_error "Missing extension: $ext"
        MISSING_EXTENSIONS+=("$ext")
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -ne 0 ]; then
    print_error "Missing required PHP extensions: ${MISSING_EXTENSIONS[*]}"
    echo "Please install the missing extensions before continuing."
    exit 1
fi

print_success "All required PHP extensions are available"

# Step 2: Check MySQL
print_header "Step 2/9: Checking MySQL Availability"

print_info "Checking if MySQL/MariaDB is accessible..."

# We'll check MySQL connectivity later with actual credentials
# For now, just check if mysql client is available
if command -v mysql &> /dev/null; then
    print_success "MySQL client available"
    MYSQL_VERSION=$(mysql --version | head -n1)
    echo "MySQL client: $MYSQL_VERSION"
else
    print_warning "MySQL client not found in PATH"
    print_info "MySQL connectivity will be tested with actual credentials later"
fi

# Step 3: Create Directories
print_header "Step 3/9: Creating Required Directories"

DIRECTORIES=(
    "logs"
    "uploads"
    "uploads/models"
    "uploads/gallery"
    "uploads/thumbnails"
    "backups"
    "backups/database"
    "backups/files"
    "temp"
    "storage"
    "storage/cache"
    "storage/sessions"
)

print_info "Creating directory structure..."
for dir in "${DIRECTORIES[@]}"; do
    if [ -d "$dir" ]; then
        print_success "Directory exists: $dir/"
    else
        mkdir -p "$dir"
        print_success "Created directory: $dir/"
    fi
done

# Step 4: Set Permissions
print_header "Step 4/9: Setting File Permissions"

print_info "Setting directory permissions (755)..."
for dir in "${DIRECTORIES[@]}"; do
    if [ -d "$dir" ]; then
        chmod 755 "$dir" 2>/dev/null && print_success "Set permissions: $dir/" || print_warning "Could not set permissions: $dir/"
    fi
done

print_info "Setting script permissions (executable)..."
chmod +x scripts/*.sh 2>/dev/null && print_success "Scripts are executable" || print_warning "Could not make scripts executable"
chmod +x scripts/*.php 2>/dev/null && print_success "PHP scripts are executable" || print_warning "Could not make PHP scripts executable"
chmod +x database/*.php 2>/dev/null && print_success "Database scripts are executable" || print_warning "Could not make database scripts executable"

# Step 5: Configure Environment (.env)
print_header "Step 5/9: Configuring Environment"

if [ -f ".env" ]; then
    print_success ".env file already exists"
    read -p "Do you want to reconfigure .env? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_info "Skipping .env configuration"
        SKIP_ENV_CONFIG=true
    else
        SKIP_ENV_CONFIG=false
    fi
else
    print_info ".env file not found"
    if [ ! -f ".env.example" ]; then
        print_error ".env.example not found!"
        exit 1
    fi
    print_info "Creating .env from .env.example..."
    cp .env.example .env
    print_success "Created .env file"
    SKIP_ENV_CONFIG=false
fi

if [ "$SKIP_ENV_CONFIG" != "true" ]; then
    print_info "Configuring environment variables..."
    echo ""
    echo -e "${YELLOW}Please provide the following configuration:${NC}"
    echo ""
    
    # Database configuration
    echo -e "${CYAN}═══ Database Configuration ═══${NC}"
    read -p "Database Host [localhost]: " DB_HOST
    DB_HOST=${DB_HOST:-localhost}
    
    read -p "Database Name: " DB_NAME
    while [ -z "$DB_NAME" ]; do
        print_error "Database name is required!"
        read -p "Database Name: " DB_NAME
    done
    
    read -p "Database User: " DB_USER
    while [ -z "$DB_USER" ]; do
        print_error "Database user is required!"
        read -p "Database User: " DB_USER
    done
    
    read -sp "Database Password: " DB_PASS
    echo ""
    while [ -z "$DB_PASS" ]; do
        print_error "Database password is required!"
        read -sp "Database Password: " DB_PASS
        echo ""
    done
    
    echo ""
    echo -e "${CYAN}═══ Application Configuration ═══${NC}"
    read -p "Application URL [http://localhost]: " APP_URL
    APP_URL=${APP_URL:-http://localhost}
    
    read -p "Admin Email [admin@example.com]: " ADMIN_EMAIL
    ADMIN_EMAIL=${ADMIN_EMAIL:-admin@example.com}
    
    # Generate JWT secret
    print_info "Generating secure JWT secret..."
    if command -v openssl &> /dev/null; then
        JWT_SECRET=$(openssl rand -base64 64 | tr -d '\n')
    else
        JWT_SECRET=$(php -r "echo bin2hex(random_bytes(32));")
    fi
    print_success "JWT secret generated (${#JWT_SECRET} characters)"
    
    echo ""
    print_info "Updating .env file..."
    
    # Update .env file with provided values
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        sed -i '' "s|^DB_HOST=.*|DB_HOST=$DB_HOST|" .env
        sed -i '' "s|^DB_NAME=.*|DB_NAME=$DB_NAME|" .env
        sed -i '' "s|^DB_USER=.*|DB_USER=$DB_USER|" .env
        sed -i '' "s|^DB_PASS=.*|DB_PASS=$DB_PASS|" .env
        sed -i '' "s|^APP_URL=.*|APP_URL=$APP_URL|" .env
        sed -i '' "s|^ADMIN_EMAIL=.*|ADMIN_EMAIL=$ADMIN_EMAIL|" .env
        sed -i '' "s|^JWT_SECRET=.*|JWT_SECRET=$JWT_SECRET|" .env
        sed -i '' "s|^APP_ENV=.*|APP_ENV=production|" .env
        sed -i '' "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
        sed -i '' "s|^SITE_URL=.*|SITE_URL=$APP_URL|" .env
    else
        # Linux
        sed -i "s|^DB_HOST=.*|DB_HOST=$DB_HOST|" .env
        sed -i "s|^DB_NAME=.*|DB_NAME=$DB_NAME|" .env
        sed -i "s|^DB_USER=.*|DB_USER=$DB_USER|" .env
        sed -i "s|^DB_PASS=.*|DB_PASS=$DB_PASS|" .env
        sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" .env
        sed -i "s|^ADMIN_EMAIL=.*|ADMIN_EMAIL=$ADMIN_EMAIL|" .env
        sed -i "s|^JWT_SECRET=.*|JWT_SECRET=$JWT_SECRET|" .env
        sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
        sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
        sed -i "s|^SITE_URL=.*|SITE_URL=$APP_URL|" .env
    fi
    
    # Set secure permissions on .env
    chmod 600 .env
    
    print_success "Environment configured successfully"
fi

# Verify .env has required values
print_info "Verifying .env configuration..."
source .env 2>/dev/null || true

if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
    print_error ".env configuration is incomplete!"
    print_error "Please ensure DB_NAME and DB_USER are set in .env"
    exit 1
fi

if grep -q "your-secret-key-here" .env 2>/dev/null; then
    print_error "JWT_SECRET is still set to default value!"
    print_error "Please set a secure JWT_SECRET in .env"
    exit 1
fi

print_success "Environment configuration verified"

# Step 6: Install Composer Dependencies
print_header "Step 6/9: Installing Composer Dependencies"

print_info "Checking Composer availability..."

if command -v composer &> /dev/null; then
    COMPOSER_CMD="composer"
    COMPOSER_VERSION=$(composer --version 2>&1 | head -n1)
    print_success "Composer found: $COMPOSER_VERSION"
    
    # Check if it's Composer 1.x
    if composer --version 2>&1 | grep -q "Composer version 1."; then
        print_warning "Composer 1.x detected!"
        print_warning "Composer 1.x cannot download from Packagist anymore."
        print_info "Attempting to download Composer 2.x..."
        
        # Try to download Composer 2.x
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php --quiet
        rm composer-setup.php
        
        if [ -f "composer.phar" ]; then
            print_success "Composer 2.x downloaded"
            COMPOSER_CMD="php composer.phar"
        else
            print_error "Failed to download Composer 2.x"
            print_info "Please update Composer manually or upload vendor/ directory"
            exit 1
        fi
    else
        print_success "Composer 2.x detected"
    fi
else
    print_warning "Composer not found"
    print_info "Downloading Composer 2.x..."
    
    # Download Composer
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    rm composer-setup.php
    
    if [ -f "composer.phar" ]; then
        print_success "Composer downloaded successfully"
        COMPOSER_CMD="php composer.phar"
    else
        print_error "Failed to download Composer"
        exit 1
    fi
fi

print_info "Installing dependencies (this may take a few minutes)..."

if [ -f "composer.lock" ]; then
    print_info "Using composer.lock (install mode)"
    $COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction --quiet
else
    print_info "No composer.lock found (update mode)"
    $COMPOSER_CMD update --no-dev --optimize-autoloader --no-interaction --quiet
fi

# Verify installation
if [ -f "vendor/autoload.php" ]; then
    print_success "Composer dependencies installed successfully"
else
    print_error "vendor/autoload.php not found!"
    print_error "Composer installation failed"
    exit 1
fi

# Test autoloader
print_info "Testing autoloader..."
php -r "require 'vendor/autoload.php'; echo 'OK';" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    print_success "Autoloader test passed"
else
    print_error "Autoloader test failed!"
    exit 1
fi

# Step 7: Test Database Connection
print_header "Step 7/9: Testing Database Connection"

print_info "Attempting to connect to database..."

# Create a test script to check database connectivity
TEST_RESULT=$(php -r "
require 'vendor/autoload.php';
use Dotenv\Dotenv;

\$dotenv = Dotenv::createImmutable(__DIR__);
\$dotenv->safeLoad();

try {
    \$dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        \$_ENV['DB_HOST'] ?? 'localhost',
        \$_ENV['DB_PORT'] ?? 3306,
        \$_ENV['DB_NAME'] ?? '',
        \$_ENV['DB_CHARSET'] ?? 'utf8mb4'
    );
    
    \$pdo = new PDO(\$dsn, \$_ENV['DB_USER'] ?? '', \$_ENV['DB_PASS'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo 'SUCCESS';
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
}
" 2>&1)

if [[ "$TEST_RESULT" == "SUCCESS" ]]; then
    print_success "Database connection successful"
else
    print_error "Database connection failed!"
    echo "$TEST_RESULT"
    echo ""
    print_error "Please check your database credentials in .env"
    exit 1
fi

# Step 8: Run Database Migrations and Seeds
print_header "Step 8/9: Setting Up Database"

# Run migrations
print_info "Running database migrations..."
echo ""
php database/migrate.php

if [ $? -eq 0 ]; then
    echo ""
    print_success "Database migrations completed"
else
    echo ""
    print_error "Database migration failed!"
    exit 1
fi

# Run seeds
echo ""
print_info "Seeding database with initial data..."
echo ""
php database/seed.php

if [ $? -eq 0 ]; then
    echo ""
    print_success "Database seeded successfully"
else
    echo ""
    print_warning "Database seeding failed (may be normal if data already exists)"
fi

# Step 9: Final Verification
print_header "Step 9/9: Final Verification"

print_info "Verifying deployment..."

# Check database tables
print_info "Checking database tables..."
TABLE_COUNT=$(php -r "
require 'bootstrap.php';
\$container = App\Core\Container::getInstance();
\$db = \$container->get('database');
\$tables = \$db->query('SHOW TABLES');
echo count(\$tables);
" 2>&1)

if [ "$TABLE_COUNT" -gt 0 ]; then
    print_success "Database has $TABLE_COUNT tables"
else
    print_error "No tables found in database!"
    exit 1
fi

# Check frontend files
print_info "Checking frontend files..."
FRONTEND_FILES=(
    "public_html/index.html"
    "public_html/calculator.html"
    "public_html/services.html"
    "api/index.php"
    "admin/index.php"
)

MISSING_FILES=()
for file in "${FRONTEND_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_success "File exists: $file"
    else
        print_warning "File missing: $file"
        MISSING_FILES+=("$file")
    fi
done

if [ ${#MISSING_FILES[@]} -eq 0 ]; then
    print_success "All core files are present"
fi

# Generate sitemap
print_info "Generating sitemap..."
if [ -f "scripts/generate-sitemap.php" ]; then
    php scripts/generate-sitemap.php > /dev/null 2>&1 && print_success "Sitemap generated" || print_warning "Sitemap generation failed"
fi

# Final Summary
print_header "🎉 Deployment Complete!"

echo -e "${GREEN}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                                ║${NC}"
echo -e "${GREEN}║          Your 3D Print Platform is ready to use!               ║${NC}"
echo -e "${GREEN}║                                                                ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""

echo -e "${CYAN}📋 Deployment Summary:${NC}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}🌐 Site URL:${NC}           $APP_URL"
echo -e "${YELLOW}📁 Project Root:${NC}       $PROJECT_ROOT"
echo -e "${YELLOW}🗄️  Database:${NC}          $DB_NAME"
echo -e "${YELLOW}📧 Admin Email:${NC}        $ADMIN_EMAIL"
echo ""

echo -e "${CYAN}🔐 Admin Access:${NC}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${GREEN}Super Admin Account:${NC}"
echo -e "  Username:  ${YELLOW}admin${NC}"
echo -e "  Password:  ${YELLOW}admin123${NC}"
echo -e "  Email:     ${YELLOW}admin@example.com${NC}"
echo -e "  Role:      ${YELLOW}super_admin${NC}"
echo ""
echo -e "${GREEN}Editor Account:${NC}"
echo -e "  Username:  ${YELLOW}editor${NC}"
echo -e "  Password:  ${YELLOW}editor123${NC}"
echo -e "  Email:     ${YELLOW}editor@example.com${NC}"
echo -e "  Role:      ${YELLOW}editor${NC}"
echo ""
echo -e "${RED}⚠️  IMPORTANT: Change these default passwords immediately!${NC}"
echo ""

echo -e "${CYAN}📝 Next Steps:${NC}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "  1. Access your site:     $APP_URL"
echo "  2. Access admin panel:   $APP_URL/admin/"
echo "  3. Test the calculator:  $APP_URL/calculator.html"
echo "  4. Change admin passwords (CRITICAL!)"
echo "  5. Configure email settings in .env"
echo "  6. Set up SSL certificate (see SSL_SETUP.md)"
echo "  7. Configure cron jobs (see DEPLOYMENT.md):"
echo "     bash scripts/backup-database.sh"
echo "     bash scripts/backup-files.sh"
echo "     php scripts/check-errors.php"
echo "  8. Enable HTTPS redirect in .htaccess (when SSL is active)"
echo ""

echo -e "${CYAN}📚 Documentation:${NC}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "  • DEPLOYMENT.md              - Complete deployment guide"
echo "  • ADMIN_API.md               - Admin API documentation"
echo "  • API_PUBLIC.md              - Public API documentation"
echo "  • SSL_SETUP.md               - SSL certificate setup"
echo "  • LAUNCH_CHECKLIST.md        - Pre-launch checklist"
echo "  • SEO_GUIDE.md               - SEO optimization guide"
echo ""

echo -e "${CYAN}🔍 Troubleshooting:${NC}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "  • Check logs:           tail -f logs/app.log"
echo "  • Check setup log:      tail -f logs/setup.log"
echo "  • Verify deployment:    php scripts/verify-deployment.php"
echo "  • Test locally:         php -S localhost:8000 -t public_html"
echo ""

echo -e "${GREEN}✓ Setup completed successfully!${NC}"
echo -e "${GREEN}  Log saved to: $LOG_FILE${NC}"
echo ""

exit 0
