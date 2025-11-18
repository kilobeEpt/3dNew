#!/bin/bash

###############################################################################
# Setup Composer Dependencies for PHP 8.2
# 
# This script helps install Composer dependencies on servers with old Composer
# versions that cannot download from Packagist anymore (like Composer 1.9.0).
#
# Usage: bash scripts/setup-composer-dependencies.sh
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}Composer Dependencies Setup${NC}"
echo -e "${GREEN}======================================${NC}"
echo ""

# Check PHP version
echo -e "${YELLOW}Checking PHP version...${NC}"
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "PHP version: $PHP_VERSION"

if php -r 'exit(version_compare(PHP_VERSION, "8.2.0", "<") ? 1 : 0);'; then
    echo -e "${GREEN}✓ PHP 8.2+ detected${NC}"
else
    echo -e "${RED}✗ ERROR: PHP 8.2 or higher required!${NC}"
    echo -e "${YELLOW}Current PHP version: $PHP_VERSION${NC}"
    exit 1
fi
echo ""

# Check if Composer is available
echo -e "${YELLOW}Checking Composer availability...${NC}"
if command -v composer &> /dev/null; then
    COMPOSER_CMD="composer"
    COMPOSER_VERSION=$(composer --version 2>&1 | head -n1)
    echo "Composer found: $COMPOSER_VERSION"
    
    # Check if it's Composer 1.x
    if composer --version 2>&1 | grep -q "Composer version 1."; then
        echo -e "${RED}⚠ WARNING: Composer 1.x detected!${NC}"
        echo -e "${YELLOW}Composer 1.x cannot download from Packagist anymore.${NC}"
        echo ""
        echo "Options:"
        echo "1. Update Composer to 2.x: composer self-update"
        echo "2. Use vendor/ directory from this repository"
        echo "3. Upload vendor/ manually from local machine"
        echo ""
        read -p "Do you want to try using existing composer.lock? (y/N) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            echo -e "${YELLOW}Exiting. Please update Composer or use vendor/ directory.${NC}"
            exit 1
        fi
    else
        echo -e "${GREEN}✓ Composer 2.x detected${NC}"
    fi
else
    echo -e "${RED}✗ Composer not found${NC}"
    echo ""
    echo "Please install Composer 2.x:"
    echo "  curl -sS https://getcomposer.org/installer | php"
    echo "  sudo mv composer.phar /usr/local/bin/composer"
    echo ""
    exit 1
fi
echo ""

# Check if composer.json exists
if [ ! -f "composer.json" ]; then
    echo -e "${RED}✗ ERROR: composer.json not found!${NC}"
    exit 1
fi

# Check if composer.lock exists
if [ ! -f "composer.lock" ]; then
    echo -e "${YELLOW}⚠ composer.lock not found. Will run 'composer update'${NC}"
    INSTALL_CMD="update"
else
    echo -e "${GREEN}✓ composer.lock found${NC}"
    INSTALL_CMD="install"
fi
echo ""

# Backup existing vendor directory if it exists
if [ -d "vendor" ]; then
    echo -e "${YELLOW}Backing up existing vendor directory...${NC}"
    BACKUP_DIR="vendor.backup.$(date +%Y%m%d_%H%M%S)"
    mv vendor "$BACKUP_DIR"
    echo -e "${GREEN}✓ Backed up to $BACKUP_DIR${NC}"
    echo ""
fi

# Install dependencies
echo -e "${GREEN}Installing Composer dependencies...${NC}"
echo "Running: composer $INSTALL_CMD --no-dev --optimize-autoloader"
echo ""

if composer $INSTALL_CMD --no-dev --optimize-autoloader; then
    echo ""
    echo -e "${GREEN}✓ Dependencies installed successfully!${NC}"
else
    echo ""
    echo -e "${RED}✗ Failed to install dependencies${NC}"
    echo ""
    echo "If you're using Composer 1.x, try one of these solutions:"
    echo ""
    echo "1. Update Composer to 2.x:"
    echo "   composer self-update"
    echo ""
    echo "2. Use vendor directory from repository (if committed):"
    echo "   git pull  # Make sure vendor/ is in the repository"
    echo ""
    echo "3. Upload vendor/ from local machine:"
    echo "   # On local machine:"
    echo "   composer install --no-dev --optimize-autoloader"
    echo "   tar -czf vendor.tar.gz vendor/"
    echo "   # Upload vendor.tar.gz to server, then:"
    echo "   tar -xzf vendor.tar.gz"
    echo ""
    exit 1
fi
echo ""

# Verify installation
echo -e "${YELLOW}Verifying installation...${NC}"
if [ -f "vendor/autoload.php" ]; then
    echo -e "${GREEN}✓ vendor/autoload.php exists${NC}"
else
    echo -e "${RED}✗ vendor/autoload.php not found!${NC}"
    exit 1
fi

# Test autoloader
echo -e "${YELLOW}Testing autoloader...${NC}"
php -r "require 'vendor/autoload.php'; echo 'Autoloader works!\n';" || {
    echo -e "${RED}✗ Autoloader test failed!${NC}"
    exit 1
}
echo -e "${GREEN}✓ Autoloader test passed${NC}"
echo ""

# Show installed packages
echo -e "${GREEN}Installed packages:${NC}"
composer show --no-dev 2>/dev/null || true
echo ""

echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}✓ Setup completed successfully!${NC}"
echo -e "${GREEN}======================================${NC}"
echo ""
echo "Next steps:"
echo "1. Test your application: php -S localhost:8000 -t public_html"
echo "2. Check configuration: cp .env.example .env && nano .env"
echo "3. Run migrations: php database/migrate.php"
echo ""
