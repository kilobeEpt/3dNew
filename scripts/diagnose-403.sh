#!/bin/bash

###############################################################################
# 403 Forbidden Diagnostic Script
# 
# This script helps diagnose and fix 403 Forbidden errors on nginx
#
# Usage: bash scripts/diagnose-403.sh
###############################################################################

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Get project root
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

echo -e "${BLUE}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                                ║${NC}"
echo -e "${BLUE}║              403 Forbidden Diagnostic Tool                     ║${NC}"
echo -e "${BLUE}║                                                                ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Function to print status
print_ok() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warn() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "${BLUE}→${NC} $1"
}

echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Step 1: Project Structure Check${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"

print_info "Current directory: $(pwd)"
print_info "Current user: $(whoami)"

# Check project structure
if [ -d "public_html" ]; then
    print_ok "public_html directory exists"
else
    print_error "public_html directory NOT FOUND!"
    echo "  Expected: $PROJECT_ROOT/public_html"
fi

if [ -f "public_html/index.php" ]; then
    print_ok "public_html/index.php exists"
else
    print_error "public_html/index.php NOT FOUND!"
fi

if [ -f "public_html/index.html" ]; then
    print_ok "public_html/index.html exists"
else
    print_error "public_html/index.html NOT FOUND!"
fi

if [ -d "api" ]; then
    print_ok "api directory exists"
else
    print_error "api directory NOT FOUND!"
fi

if [ -d "admin" ]; then
    print_ok "admin directory exists"
else
    print_error "admin directory NOT FOUND!"
fi

if [ -f "bootstrap.php" ]; then
    print_ok "bootstrap.php exists"
else
    print_error "bootstrap.php NOT FOUND!"
fi

echo ""
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Step 2: File Permissions Check${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"

# Check permissions
if [ -r "public_html/index.php" ]; then
    print_ok "index.php is readable"
    print_info "Permissions: $(ls -lh public_html/index.php | awk '{print $1, $3, $4}')"
else
    print_error "index.php is NOT readable!"
fi

if [ -x "public_html/index.php" ]; then
    print_warn "index.php is executable (not necessary but OK)"
else
    print_ok "index.php is not executable (correct)"
fi

echo ""
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Step 3: Web Root Detection${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"

print_info "Trying to detect nginx web root..."

# Common web root locations
WEB_ROOTS=(
    "/home/c/ch167436/public_html"
    "/home/c/ch167436/www"
    "/home/c/ch167436/domains/3dprint-omsk.ru/public_html"
    "/var/www/html"
    "/usr/share/nginx/html"
    "/home/$(whoami)/public_html"
    "/home/$(whoami)/www"
)

for root in "${WEB_ROOTS[@]}"; do
    if [ -d "$root" ]; then
        print_info "Found possible web root: $root"
        if [ -f "$root/index.php" ]; then
            print_ok "  - Has index.php"
        fi
        if [ -f "$root/index.html" ]; then
            print_ok "  - Has index.html"
        fi
    fi
done

echo ""
print_info "Current project location: $PROJECT_ROOT"
print_info "If the above doesn't match a web root, you may need to:"
echo "  1. Move files to the correct web root"
echo "  2. Create a symlink"
echo "  3. Update nginx configuration"

echo ""
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Step 4: PHP Check${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"

if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1)
    print_ok "PHP is installed: $PHP_VERSION"
    
    # Check PHP version
    PHP_VER=$(php -r "echo PHP_VERSION;" | cut -d. -f1,2)
    if (( $(echo "$PHP_VER >= 8.2" | bc -l) )); then
        print_ok "PHP version is 8.2+ (required)"
    else
        print_error "PHP version is $PHP_VER (need 8.2+)"
    fi
else
    print_error "PHP is NOT installed or not in PATH!"
fi

echo ""
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Step 5: Composer Check${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"

if [ -d "vendor" ]; then
    print_ok "vendor directory exists"
else
    print_error "vendor directory NOT FOUND! Run: composer install"
fi

if [ -f "composer.lock" ]; then
    print_ok "composer.lock exists"
else
    print_warn "composer.lock not found"
fi

echo ""
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Step 6: Configuration Check${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"

if [ -f ".env" ]; then
    print_ok ".env file exists"
    
    # Check critical env variables
    if grep -q "^DB_HOST=" .env 2>/dev/null; then
        print_ok "DB_HOST is set"
    else
        print_warn "DB_HOST not set in .env"
    fi
    
    if grep -q "^DB_NAME=" .env 2>/dev/null; then
        print_ok "DB_NAME is set"
    else
        print_warn "DB_NAME not set in .env"
    fi
    
    if grep -q "^JWT_SECRET=" .env 2>/dev/null; then
        JWT_LEN=$(grep "^JWT_SECRET=" .env | cut -d= -f2 | wc -c)
        if [ $JWT_LEN -gt 64 ]; then
            print_ok "JWT_SECRET is set and long enough"
        else
            print_error "JWT_SECRET is too short (need 64+ chars)"
        fi
    else
        print_error "JWT_SECRET not set in .env"
    fi
else
    print_error ".env file NOT FOUND! Run: cp .env.example .env"
fi

echo ""
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Step 7: Directory Permissions Check${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"

# Check writable directories
DIRS=("logs" "uploads" "backups" "temp" "storage")
for dir in "${DIRS[@]}"; do
    if [ -d "$dir" ]; then
        if [ -w "$dir" ]; then
            print_ok "$dir is writable"
        else
            print_error "$dir exists but is NOT writable!"
        fi
    else
        print_warn "$dir directory does not exist"
    fi
done

echo ""
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Step 8: Suggested Fixes${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"

echo ""
echo -e "${GREEN}Quick Fix Commands:${NC}"
echo ""
echo "# Fix permissions:"
echo "find public_html -type d -exec chmod 755 {} \;"
echo "find public_html -type f -exec chmod 644 {} \;"
echo ""
echo "# Create missing directories:"
echo "mkdir -p logs uploads backups/{database,files} temp storage"
echo "chmod 755 logs uploads backups temp storage"
echo ""
echo "# Install dependencies:"
echo "composer install --no-dev --optimize-autoloader"
echo ""
echo "# Create .env:"
echo "cp .env.example .env"
echo "nano .env"
echo ""
echo "# Run setup script:"
echo "bash scripts/setup.sh"
echo ""

echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Step 9: Testing URLs${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════════${NC}"

echo ""
echo "Test these URLs from your browser or curl:"
echo ""
echo "  https://3dprint-omsk.ru/"
echo "  https://3dprint-omsk.ru/index.php"
echo "  https://3dprint-omsk.ru/index.html"
echo "  https://3dprint-omsk.ru/api/services"
echo "  https://3dprint-omsk.ru/admin/"
echo ""
echo "Expected results:"
echo "  ✓ HTTP 200 response"
echo "  ✓ HTML/JSON content"
echo "  ✓ No 403 Forbidden errors"
echo ""

echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}Diagnostic Complete!${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
echo ""
echo "For more help, see:"
echo "  - DEPLOYMENT_FIX_403.md"
echo "  - NGINX_ROUTER_DEPLOYMENT.md"
echo "  - DEPLOYMENT.md"
echo ""
