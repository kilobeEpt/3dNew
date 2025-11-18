#!/bin/bash

##############################################################################
# Server Verification and Diagnostic Script
# 
# This script performs comprehensive checks to verify:
# - Server type (Apache vs nginx)
# - PHP configuration
# - File permissions
# - Directory structure
# - nginx configuration (if applicable)
# - Common issues
#
# Usage: bash scripts/verify-server.sh
##############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Project paths
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PUBLIC_HTML="$PROJECT_ROOT/public_html"

echo -e "${BLUE}=== 3D Print Platform - Server Verification ===${NC}\n"

##############################################################################
# 1. Detect Server Type
##############################################################################

echo -e "${BLUE}[1/10] Detecting Web Server...${NC}"

WEBSERVER="unknown"

# Check for nginx process
if ps aux | grep -v grep | grep -q nginx; then
    WEBSERVER="nginx"
    echo -e "${GREEN}✓ nginx detected${NC}"
elif ps aux | grep -v grep | grep -qE "httpd|apache2"; then
    WEBSERVER="apache"
    echo -e "${GREEN}✓ Apache detected${NC}"
else
    echo -e "${YELLOW}⚠ Could not detect web server from processes${NC}"
fi

# Try to detect from HTTP headers (if domain is accessible)
if command -v curl &> /dev/null; then
    if [ -f "$PROJECT_ROOT/.env" ]; then
        DOMAIN=$(grep "^APP_URL=" "$PROJECT_ROOT/.env" 2>/dev/null | cut -d'=' -f2 | tr -d '"')
        if [ -n "$DOMAIN" ]; then
            echo "  Testing domain: $DOMAIN"
            SERVER_HEADER=$(curl -s -I "$DOMAIN" 2>/dev/null | grep -i "^server:" | cut -d':' -f2 | tr -d ' ')
            if [ -n "$SERVER_HEADER" ]; then
                echo "  Server header: $SERVER_HEADER"
                if echo "$SERVER_HEADER" | grep -qi "nginx"; then
                    WEBSERVER="nginx"
                elif echo "$SERVER_HEADER" | grep -qiE "apache|httpd"; then
                    WEBSERVER="apache"
                fi
            fi
        fi
    fi
fi

echo "  Detected server: $WEBSERVER"
echo ""

##############################################################################
# 2. Check PHP Version
##############################################################################

echo -e "${BLUE}[2/10] Checking PHP Version...${NC}"

if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
    PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")
    
    echo "  PHP Version: $PHP_VERSION"
    
    if [ "$PHP_MAJOR" -ge 8 ] && [ "$PHP_MINOR" -ge 2 ]; then
        echo -e "${GREEN}✓ PHP 8.2+ detected${NC}"
    elif [ "$PHP_MAJOR" -ge 7 ] && [ "$PHP_MINOR" -ge 4 ]; then
        echo -e "${YELLOW}⚠ PHP $PHP_VERSION detected (works, but 8.2+ recommended)${NC}"
    else
        echo -e "${RED}✗ PHP $PHP_VERSION is too old (minimum: 7.4, recommended: 8.2+)${NC}"
    fi
else
    echo -e "${RED}✗ PHP command not found${NC}"
fi
echo ""

##############################################################################
# 3. Check PHP Extensions
##############################################################################

echo -e "${BLUE}[3/10] Checking Required PHP Extensions...${NC}"

REQUIRED_EXTS=("pdo_mysql" "mbstring" "openssl" "json" "fileinfo")
ALL_EXTS_OK=true

for ext in "${REQUIRED_EXTS[@]}"; do
    if php -m 2>/dev/null | grep -q "^$ext$"; then
        echo -e "  ${GREEN}✓${NC} $ext"
    else
        echo -e "  ${RED}✗${NC} $ext (missing)"
        ALL_EXTS_OK=false
    fi
done

if $ALL_EXTS_OK; then
    echo -e "${GREEN}✓ All required extensions are installed${NC}"
else
    echo -e "${RED}✗ Some required extensions are missing${NC}"
fi
echo ""

##############################################################################
# 4. Check Directory Structure
##############################################################################

echo -e "${BLUE}[4/10] Checking Directory Structure...${NC}"

REQUIRED_DIRS=(
    "public_html"
    "api"
    "admin"
    "src"
    "vendor"
    "database"
    "logs"
    "uploads"
    "backups"
)

ALL_DIRS_OK=true

for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$PROJECT_ROOT/$dir" ]; then
        echo -e "  ${GREEN}✓${NC} $dir/"
    else
        echo -e "  ${RED}✗${NC} $dir/ (missing)"
        ALL_DIRS_OK=false
    fi
done

if $ALL_DIRS_OK; then
    echo -e "${GREEN}✓ All required directories exist${NC}"
else
    echo -e "${RED}✗ Some required directories are missing${NC}"
fi
echo ""

##############################################################################
# 5. Check Critical Files
##############################################################################

echo -e "${BLUE}[5/10] Checking Critical Files...${NC}"

REQUIRED_FILES=(
    "bootstrap.php"
    ".env"
    "public_html/index.php"
    "api/index.php"
    "admin/index.php"
    "vendor/autoload.php"
)

ALL_FILES_OK=true

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$PROJECT_ROOT/$file" ]; then
        echo -e "  ${GREEN}✓${NC} $file"
    else
        echo -e "  ${RED}✗${NC} $file (missing)"
        ALL_FILES_OK=false
    fi
done

if $ALL_FILES_OK; then
    echo -e "${GREEN}✓ All critical files exist${NC}"
else
    echo -e "${RED}✗ Some critical files are missing${NC}"
fi
echo ""

##############################################################################
# 6. Check File Permissions
##############################################################################

echo -e "${BLUE}[6/10] Checking File Permissions...${NC}"

# Check public_html directory
if [ -d "$PUBLIC_HTML" ]; then
    PH_PERMS=$(stat -c "%a" "$PUBLIC_HTML" 2>/dev/null || stat -f "%OLp" "$PUBLIC_HTML" 2>/dev/null)
    if [ "$PH_PERMS" = "755" ] || [ "$PH_PERMS" = "775" ]; then
        echo -e "  ${GREEN}✓${NC} public_html/ permissions: $PH_PERMS"
    else
        echo -e "  ${YELLOW}⚠${NC} public_html/ permissions: $PH_PERMS (should be 755)"
    fi
fi

# Check index.php
if [ -f "$PUBLIC_HTML/index.php" ]; then
    INDEX_PERMS=$(stat -c "%a" "$PUBLIC_HTML/index.php" 2>/dev/null || stat -f "%OLp" "$PUBLIC_HTML/index.php" 2>/dev/null)
    if [ "$INDEX_PERMS" = "644" ] || [ "$INDEX_PERMS" = "664" ]; then
        echo -e "  ${GREEN}✓${NC} public_html/index.php permissions: $INDEX_PERMS"
    else
        echo -e "  ${YELLOW}⚠${NC} public_html/index.php permissions: $INDEX_PERMS (should be 644)"
    fi
fi

# Check .env
if [ -f "$PROJECT_ROOT/.env" ]; then
    ENV_PERMS=$(stat -c "%a" "$PROJECT_ROOT/.env" 2>/dev/null || stat -f "%OLp" "$PROJECT_ROOT/.env" 2>/dev/null)
    if [ "$ENV_PERMS" = "600" ] || [ "$ENV_PERMS" = "400" ]; then
        echo -e "  ${GREEN}✓${NC} .env permissions: $ENV_PERMS"
    else
        echo -e "  ${YELLOW}⚠${NC} .env permissions: $ENV_PERMS (should be 600 for security)"
    fi
fi

# Check writable directories
for dir in logs uploads backups; do
    if [ -d "$PROJECT_ROOT/$dir" ]; then
        DIR_PERMS=$(stat -c "%a" "$PROJECT_ROOT/$dir" 2>/dev/null || stat -f "%OLp" "$PROJECT_ROOT/$dir" 2>/dev/null)
        if [ "$DIR_PERMS" = "755" ] || [ "$DIR_PERMS" = "775" ]; then
            echo -e "  ${GREEN}✓${NC} $dir/ permissions: $DIR_PERMS"
        else
            echo -e "  ${YELLOW}⚠${NC} $dir/ permissions: $DIR_PERMS (should be 755)"
        fi
    fi
done
echo ""

##############################################################################
# 7. Check nginx Router (if nginx detected)
##############################################################################

if [ "$WEBSERVER" = "nginx" ]; then
    echo -e "${BLUE}[7/10] Checking nginx Router...${NC}"
    
    if [ -f "$PUBLIC_HTML/index.php" ]; then
        # Check if it's the nginx router
        if grep -q "Main Entry Point Router for nginx Compatibility" "$PUBLIC_HTML/index.php"; then
            echo -e "${GREEN}✓ nginx PHP router is present${NC}"
            
            # Check file size (should be ~200 lines)
            LINES=$(wc -l < "$PUBLIC_HTML/index.php")
            echo "  Router file size: $LINES lines"
            
            if [ "$LINES" -ge 180 ] && [ "$LINES" -le 220 ]; then
                echo -e "  ${GREEN}✓ Router size looks correct${NC}"
            else
                echo -e "  ${YELLOW}⚠ Router size unexpected (expected ~200 lines)${NC}"
            fi
        else
            echo -e "${RED}✗ nginx router not found in index.php${NC}"
            echo -e "  ${YELLOW}The index.php file exists but doesn't contain the nginx router${NC}"
        fi
    else
        echo -e "${RED}✗ public_html/index.php is missing${NC}"
    fi
else
    echo -e "${BLUE}[7/10] Checking Apache Configuration...${NC}"
    
    if [ -f "$PUBLIC_HTML/.htaccess" ]; then
        echo -e "${GREEN}✓ .htaccess file is present${NC}"
    else
        echo -e "${YELLOW}⚠ .htaccess file is missing${NC}"
    fi
fi
echo ""

##############################################################################
# 8. Test Database Connection
##############################################################################

echo -e "${BLUE}[8/10] Testing Database Connection...${NC}"

if [ -f "$PROJECT_ROOT/.env" ]; then
    # Extract database credentials
    DB_HOST=$(grep "^DB_HOST=" "$PROJECT_ROOT/.env" | cut -d'=' -f2 | tr -d '"')
    DB_NAME=$(grep "^DB_NAME=" "$PROJECT_ROOT/.env" | cut -d'=' -f2 | tr -d '"')
    DB_USER=$(grep "^DB_USER=" "$PROJECT_ROOT/.env" | cut -d'=' -f2 | tr -d '"')
    DB_PASS=$(grep "^DB_PASS=" "$PROJECT_ROOT/.env" | cut -d'=' -f2 | tr -d '"')
    
    if [ -n "$DB_HOST" ] && [ -n "$DB_NAME" ]; then
        echo "  Database: $DB_NAME@$DB_HOST"
        
        # Try to connect using mysql command
        if command -v mysql &> /dev/null; then
            if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" &>/dev/null; then
                echo -e "${GREEN}✓ Database connection successful${NC}"
                
                # Count tables
                TABLE_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | wc -l)
                TABLE_COUNT=$((TABLE_COUNT - 1)) # Subtract header line
                echo "  Tables: $TABLE_COUNT"
                
                if [ "$TABLE_COUNT" -ge 15 ]; then
                    echo -e "  ${GREEN}✓ Database appears to be migrated${NC}"
                else
                    echo -e "  ${YELLOW}⚠ Expected ~17 tables, found $TABLE_COUNT${NC}"
                fi
            else
                echo -e "${RED}✗ Database connection failed${NC}"
                echo -e "  ${YELLOW}Check credentials in .env file${NC}"
            fi
        else
            echo -e "${YELLOW}⚠ mysql command not available, skipping connection test${NC}"
        fi
    else
        echo -e "${YELLOW}⚠ Database credentials not configured in .env${NC}"
    fi
else
    echo -e "${RED}✗ .env file not found${NC}"
fi
echo ""

##############################################################################
# 9. Check nginx Configuration (if nginx and sudo available)
##############################################################################

if [ "$WEBSERVER" = "nginx" ]; then
    echo -e "${BLUE}[9/10] Checking nginx Configuration...${NC}"
    
    if command -v sudo &> /dev/null && [ -d "/etc/nginx" ]; then
        # Try to find config for our domain
        DOMAIN_NAME=$(echo "$DOMAIN" | sed 's|https\?://||' | sed 's|www.||')
        
        if [ -n "$DOMAIN_NAME" ]; then
            echo "  Searching for configuration: $DOMAIN_NAME"
            
            CONFIG_FILES=$(sudo grep -r "$DOMAIN_NAME" /etc/nginx/ 2>/dev/null | cut -d':' -f1 | sort -u)
            
            if [ -n "$CONFIG_FILES" ]; then
                echo -e "${GREEN}✓ Found nginx configuration${NC}"
                echo "$CONFIG_FILES" | while read -r file; do
                    echo "    $file"
                    
                    # Check for critical directives
                    if sudo grep -q "root.*public_html" "$file" 2>/dev/null; then
                        echo -e "      ${GREEN}✓${NC} root directive includes public_html"
                    else
                        echo -e "      ${RED}✗${NC} root directive may be incorrect"
                    fi
                    
                    if sudo grep -q "index.*index.php" "$file" 2>/dev/null; then
                        echo -e "      ${GREEN}✓${NC} index includes index.php"
                    else
                        echo -e "      ${YELLOW}⚠${NC} index may not include index.php"
                    fi
                    
                    if sudo grep -q "try_files" "$file" 2>/dev/null; then
                        echo -e "      ${GREEN}✓${NC} try_files directive present"
                    else
                        echo -e "      ${YELLOW}⚠${NC} try_files directive missing"
                    fi
                    
                    if sudo grep -q "location.*\.php" "$file" 2>/dev/null; then
                        echo -e "      ${GREEN}✓${NC} PHP location block present"
                    else
                        echo -e "      ${RED}✗${NC} PHP location block missing"
                    fi
                done
            else
                echo -e "${YELLOW}⚠ Could not find nginx configuration for $DOMAIN_NAME${NC}"
                echo -e "  ${YELLOW}You may need to contact hosting support${NC}"
            fi
        else
            echo -e "${YELLOW}⚠ Domain not configured in .env, cannot search nginx config${NC}"
        fi
    else
        echo -e "${YELLOW}⚠ Cannot check nginx configuration (no sudo access or /etc/nginx not found)${NC}"
        echo -e "  ${YELLOW}Contact hosting support to verify nginx configuration${NC}"
    fi
else
    echo -e "${BLUE}[9/10] Apache Configuration Check...${NC}"
    echo -e "${GREEN}✓ Apache uses .htaccess files (no special config needed)${NC}"
fi
echo ""

##############################################################################
# 10. Recommendations
##############################################################################

echo -e "${BLUE}[10/10] Recommendations...${NC}"

RECOMMENDATIONS=()

if [ "$WEBSERVER" = "nginx" ]; then
    if [ ! -f "$PUBLIC_HTML/index.php" ]; then
        RECOMMENDATIONS+=("Create public_html/index.php with nginx router")
    fi
    
    RECOMMENDATIONS+=("Verify nginx document root points to: $PUBLIC_HTML")
    RECOMMENDATIONS+=("Ensure nginx has: index index.php index.html;")
    RECOMMENDATIONS+=("Ensure nginx has: try_files directive")
    RECOMMENDATIONS+=("See DEPLOYMENT_NGINX.md for complete nginx setup")
fi

if [ ! -f "$PROJECT_ROOT/.env" ]; then
    RECOMMENDATIONS+=("Create .env file from .env.example")
fi

if [ ! -d "$PROJECT_ROOT/vendor" ]; then
    RECOMMENDATIONS+=("Run: composer install --no-dev --optimize-autoloader")
fi

if [ ${#RECOMMENDATIONS[@]} -eq 0 ]; then
    echo -e "${GREEN}✓ No critical issues found!${NC}"
else
    echo -e "${YELLOW}Recommendations:${NC}"
    for rec in "${RECOMMENDATIONS[@]}"; do
        echo "  • $rec"
    done
fi

echo ""

##############################################################################
# Summary
##############################################################################

echo -e "${BLUE}=== Summary ===${NC}"
echo "Server Type: $WEBSERVER"
echo "PHP Version: ${PHP_VERSION:-not detected}"
echo "Project Root: $PROJECT_ROOT"
echo "Web Root: $PUBLIC_HTML"
echo ""

if [ "$WEBSERVER" = "nginx" ]; then
    echo -e "${YELLOW}=== nginx Users ===${NC}"
    echo "If you're experiencing 403 Forbidden errors:"
    echo "1. Run the diagnostic steps in DEPLOYMENT_NGINX.md"
    echo "2. Contact hosting support with the template email"
    echo "3. See TROUBLESHOOTING.md for common solutions"
    echo ""
fi

echo -e "${GREEN}Verification complete!${NC}"
echo ""
echo "Documentation:"
echo "  • DEPLOYMENT.md - General deployment guide"
echo "  • DEPLOYMENT_NGINX.md - nginx-specific guide and 403 troubleshooting"
echo "  • TROUBLESHOOTING.md - Common issues and solutions"
echo "  • SETUP_SCRIPT_GUIDE.md - Automated setup guide"
echo ""
