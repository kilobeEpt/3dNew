#!/bin/bash

##############################################################################
# Deploy Script for Restructured Project
# This script helps deploy the restructured project to production
##############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Restructured Project Deployment${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Configuration
PROJECT_ROOT="/home/c/ch167436/3dPrint"
BACKUP_DIR="/home/c/ch167436/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo -e "${YELLOW}Configuration:${NC}"
echo -e "  Project Root: ${PROJECT_ROOT}"
echo -e "  Backup Dir: ${BACKUP_DIR}"
echo ""

# Function to check if we're on production server
check_production() {
    if [ ! -d "/home/c/ch167436" ]; then
        echo -e "${RED}✗ This doesn't appear to be the production server${NC}"
        echo -e "${YELLOW}ℹ This script is designed for: /home/c/ch167436/3dPrint${NC}"
        echo -e "${YELLOW}ℹ Running in test mode...${NC}"
        PROJECT_ROOT="$(pwd)"
        BACKUP_DIR="$(pwd)/backups"
        echo ""
    fi
}

# Check current directory structure
check_structure() {
    echo -e "${BLUE}Step 1: Checking current structure...${NC}"
    
    if [ -f "${PROJECT_ROOT}/index.html" ]; then
        echo -e "${GREEN}✓ index.html found in project root${NC}"
    else
        echo -e "${RED}✗ index.html not found in project root${NC}"
        echo -e "${YELLOW}  Have you moved the files from public_html/?${NC}"
        exit 1
    fi
    
    if [ -d "${PROJECT_ROOT}/assets" ]; then
        echo -e "${GREEN}✓ assets/ directory found in project root${NC}"
    else
        echo -e "${RED}✗ assets/ directory not found in project root${NC}"
        exit 1
    fi
    
    if [ -f "${PROJECT_ROOT}/index.php" ]; then
        echo -e "${GREEN}✓ index.php (router) found in project root${NC}"
    else
        echo -e "${RED}✗ index.php not found in project root${NC}"
        exit 1
    fi
    
    echo ""
}

# Check file permissions
check_permissions() {
    echo -e "${BLUE}Step 2: Checking file permissions...${NC}"
    
    # Check directory permissions (should be 755)
    if [ -d "${PROJECT_ROOT}" ]; then
        perms=$(stat -c "%a" "${PROJECT_ROOT}" 2>/dev/null || stat -f "%Lp" "${PROJECT_ROOT}" 2>/dev/null)
        if [ "$perms" = "755" ]; then
            echo -e "${GREEN}✓ Project root permissions: ${perms}${NC}"
        else
            echo -e "${YELLOW}⚠ Project root permissions: ${perms} (should be 755)${NC}"
            read -p "Fix permissions? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                chmod 755 "${PROJECT_ROOT}"
                echo -e "${GREEN}✓ Fixed project root permissions${NC}"
            fi
        fi
    fi
    
    # Check .env permissions (should be 600)
    if [ -f "${PROJECT_ROOT}/.env" ]; then
        perms=$(stat -c "%a" "${PROJECT_ROOT}/.env" 2>/dev/null || stat -f "%Lp" "${PROJECT_ROOT}/.env" 2>/dev/null)
        if [ "$perms" = "600" ]; then
            echo -e "${GREEN}✓ .env permissions: ${perms}${NC}"
        else
            echo -e "${YELLOW}⚠ .env permissions: ${perms} (should be 600)${NC}"
            read -p "Fix permissions? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                chmod 600 "${PROJECT_ROOT}/.env"
                echo -e "${GREEN}✓ Fixed .env permissions${NC}"
            fi
        fi
    fi
    
    echo ""
}

# Test that files are accessible
test_files() {
    echo -e "${BLUE}Step 3: Testing file accessibility...${NC}"
    
    files=(
        "index.html"
        "about.html"
        "services.html"
        "calculator.html"
        "contact.html"
        "404.html"
        "500.html"
        "assets/css/style.css"
        "assets/js/main.js"
    )
    
    for file in "${files[@]}"; do
        if [ -f "${PROJECT_ROOT}/${file}" ]; then
            echo -e "${GREEN}✓${NC} ${file}"
        else
            echo -e "${RED}✗${NC} ${file} ${YELLOW}(not found)${NC}"
        fi
    done
    
    echo ""
}

# Create backup before deployment
create_backup() {
    echo -e "${BLUE}Step 4: Creating backup...${NC}"
    
    if [ ! -d "${BACKUP_DIR}" ]; then
        mkdir -p "${BACKUP_DIR}"
        echo -e "${GREEN}✓ Created backup directory${NC}"
    fi
    
    backup_file="${BACKUP_DIR}/pre-restructure-backup-${TIMESTAMP}.tar.gz"
    
    if [ -d "${PROJECT_ROOT}/public_html" ]; then
        echo -e "${YELLOW}ℹ Backing up old public_html directory...${NC}"
        tar -czf "${backup_file}" -C "${PROJECT_ROOT}" public_html 2>/dev/null || true
        echo -e "${GREEN}✓ Backup created: ${backup_file}${NC}"
    else
        echo -e "${YELLOW}ℹ No public_html directory to backup${NC}"
    fi
    
    echo ""
}

# Verify nginx compatibility
verify_routing() {
    echo -e "${BLUE}Step 5: Verifying routing configuration...${NC}"
    
    if [ -f "${PROJECT_ROOT}/index.php" ]; then
        # Check if router handles /api
        if grep -q "strpos(\$requestUri, '/api')" "${PROJECT_ROOT}/index.php"; then
            echo -e "${GREEN}✓ Router handles /api/* requests${NC}"
        else
            echo -e "${RED}✗ Router missing /api handling${NC}"
        fi
        
        # Check if router handles /admin
        if grep -q "strpos(\$requestUri, '/admin')" "${PROJECT_ROOT}/index.php"; then
            echo -e "${GREEN}✓ Router handles /admin/* requests${NC}"
        else
            echo -e "${RED}✗ Router missing /admin handling${NC}"
        fi
        
        # Check if router handles static files
        if grep -q "serveStaticFile" "${PROJECT_ROOT}/index.php"; then
            echo -e "${GREEN}✓ Router has static file serving${NC}"
        else
            echo -e "${RED}✗ Router missing static file serving${NC}"
        fi
    fi
    
    echo ""
}

# Print summary and next steps
print_summary() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${GREEN}✅ Restructure Verification Complete!${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo ""
    echo -e "${YELLOW}Next Steps:${NC}"
    echo ""
    echo -e "1. ${BLUE}Test the website:${NC}"
    echo -e "   curl -I https://3dprint-omsk.ru/"
    echo -e "   ${YELLOW}Expected: HTTP 200 OK${NC}"
    echo ""
    echo -e "2. ${BLUE}Test static files:${NC}"
    echo -e "   curl -I https://3dprint-omsk.ru/assets/css/style.css"
    echo -e "   ${YELLOW}Expected: HTTP 200 OK${NC}"
    echo ""
    echo -e "3. ${BLUE}Test API:${NC}"
    echo -e "   curl https://3dprint-omsk.ru/api/services"
    echo -e "   ${YELLOW}Expected: JSON response${NC}"
    echo ""
    echo -e "4. ${BLUE}Test admin panel:${NC}"
    echo -e "   curl -I https://3dprint-omsk.ru/admin"
    echo -e "   ${YELLOW}Expected: HTTP 200 OK${NC}"
    echo ""
    echo -e "5. ${BLUE}Open in browser:${NC}"
    echo -e "   https://3dprint-omsk.ru/"
    echo -e "   ${YELLOW}Should load without 403 errors${NC}"
    echo ""
    echo -e "${GREEN}🎉 If all tests pass, the restructuring is successful!${NC}"
    echo ""
}

# Main execution
main() {
    check_production
    check_structure
    check_permissions
    test_files
    create_backup
    verify_routing
    print_summary
}

# Run main function
main
