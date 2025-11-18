#!/bin/bash

##############################################################################
# Verify Restructure Script
# Quick verification that the restructuring was completed successfully
##############################################################################

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Restructure Verification${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PASS=0
FAIL=0

check_file() {
    local file="$1"
    local desc="$2"
    
    if [ -f "${PROJECT_ROOT}/${file}" ]; then
        echo -e "${GREEN}✓${NC} ${desc}: ${file}"
        ((PASS++))
    else
        echo -e "${RED}✗${NC} ${desc}: ${file} ${YELLOW}(not found)${NC}"
        ((FAIL++))
    fi
}

check_dir() {
    local dir="$1"
    local desc="$2"
    
    if [ -d "${PROJECT_ROOT}/${dir}" ]; then
        echo -e "${GREEN}✓${NC} ${desc}: ${dir}/"
        ((PASS++))
    else
        echo -e "${RED}✗${NC} ${desc}: ${dir}/ ${YELLOW}(not found)${NC}"
        ((FAIL++))
    fi
}

echo -e "${BLUE}Checking HTML files in project root...${NC}"
check_file "index.html" "Homepage"
check_file "about.html" "About page"
check_file "services.html" "Services page"
check_file "calculator.html" "Calculator page"
check_file "contact.html" "Contact page"
check_file "gallery.html" "Gallery page"
check_file "materials.html" "Materials page"
check_file "news.html" "News page"
check_file "404.html" "404 error page"
check_file "500.html" "500 error page"
echo ""

echo -e "${BLUE}Checking router and configuration...${NC}"
check_file "index.php" "Main router"
check_file ".htaccess" "Apache/nginx rules"
check_file "bootstrap.php" "Application bootstrap"
echo ""

echo -e "${BLUE}Checking directories...${NC}"
check_dir "assets" "Static assets"
check_dir "assets/css" "CSS files"
check_dir "assets/js" "JavaScript files"
check_dir "assets/images" "Image files"
check_dir "api" "API endpoints"
check_dir "admin" "Admin panel"
check_dir "src" "PHP source code"
check_dir "database" "Database files"
echo ""

echo -e "${BLUE}Checking API and Admin routers...${NC}"
check_file "api/index.php" "API router"
check_file "admin/index.php" "Admin router"
echo ""

echo -e "${BLUE}Checking documentation...${NC}"
check_file "RESTRUCTURE_GUIDE.md" "Restructure guide"
check_file "nginx.conf.example" "nginx config example"
check_file "scripts/deploy-restructure.sh" "Deploy script"
echo ""

# Check router content
echo -e "${BLUE}Verifying router functionality...${NC}"
if [ -f "${PROJECT_ROOT}/index.php" ]; then
    if grep -q "projectRoot = __DIR__" "${PROJECT_ROOT}/index.php"; then
        echo -e "${GREEN}✓${NC} Router uses correct project root"
        ((PASS++))
    else
        echo -e "${RED}✗${NC} Router project root configuration"
        ((FAIL++))
    fi
    
    if grep -q "strpos(\$requestUri, '/api')" "${PROJECT_ROOT}/index.php"; then
        echo -e "${GREEN}✓${NC} Router handles /api/* requests"
        ((PASS++))
    else
        echo -e "${RED}✗${NC} Router missing /api handling"
        ((FAIL++))
    fi
    
    if grep -q "strpos(\$requestUri, '/admin')" "${PROJECT_ROOT}/index.php"; then
        echo -e "${GREEN}✓${NC} Router handles /admin/* requests"
        ((PASS++))
    else
        echo -e "${RED}✗${NC} Router missing /admin handling"
        ((FAIL++))
    fi
    
    if grep -q "serveStaticFile" "${PROJECT_ROOT}/index.php"; then
        echo -e "${GREEN}✓${NC} Router serves static files"
        ((PASS++))
    else
        echo -e "${RED}✗${NC} Router missing static file serving"
        ((FAIL++))
    fi
    
    if grep -q "serveNotFound" "${PROJECT_ROOT}/index.php"; then
        echo -e "${GREEN}✓${NC} Router handles 404 errors"
        ((PASS++))
    else
        echo -e "${RED}✗${NC} Router missing 404 handling"
        ((FAIL++))
    fi
fi
echo ""

# Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Verification Summary${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo -e "  ${GREEN}Passed: ${PASS}${NC}"
echo -e "  ${RED}Failed: ${FAIL}${NC}"
echo ""

if [ $FAIL -eq 0 ]; then
    echo -e "${GREEN}✅ All checks passed!${NC}"
    echo -e "${GREEN}The restructuring is complete and correct.${NC}"
    echo ""
    echo -e "${YELLOW}Next step:${NC} Deploy to production server"
    echo -e "  ${BLUE}bash scripts/deploy-restructure.sh${NC}"
    exit 0
else
    echo -e "${RED}❌ Some checks failed!${NC}"
    echo -e "${YELLOW}Please review the failed items above.${NC}"
    exit 1
fi
