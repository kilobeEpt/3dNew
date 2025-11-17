# Database Testing Guide

This guide helps verify that the database schema, migrations, seeds, and data access layer work correctly.

## Prerequisites

Before testing, ensure you have:

1. **MySQL 8.0+** installed
2. **PHP 7.4+** with PDO MySQL extension
3. **Composer** dependencies installed
4. **Database credentials** configured in `.env`

## Setup Test Database

### Option 1: Local MySQL

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE manufacturing_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Create test user
mysql -u root -p -e "CREATE USER 'test_user'@'localhost' IDENTIFIED BY 'test_password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON manufacturing_test.* TO 'test_user'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"
```

### Option 2: Shared Hosting

1. Create database via cPanel or hosting control panel
2. Note the database name, username, and password
3. Update `.env` file

## Configure Environment

```bash
# Copy example file
cp .env.example .env

# Edit configuration
nano .env
```

Set these values:
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=manufacturing_test
DB_USER=test_user
DB_PASS=test_password
DB_CHARSET=utf8mb4
```

## Run Tests

### Test 1: Run Migrations

```bash
php database/migrate.php
```

**Expected Output**:
```
✓ Connected to database: manufacturing_test

→ Executing: 001_create_admin_users_table.sql... ✓ Success
→ Executing: 002_create_service_categories_table.sql... ✓ Success
→ Executing: 003_create_services_table.sql... ✓ Success
→ Executing: 004_create_materials_table.sql... ✓ Success
→ Executing: 005_create_material_properties_table.sql... ✓ Success
→ Executing: 006_create_pricing_rules_table.sql... ✓ Success
→ Executing: 007_create_customer_requests_table.sql... ✓ Success
→ Executing: 008_create_cost_estimates_table.sql... ✓ Success
→ Executing: 009_create_cost_estimate_items_table.sql... ✓ Success
→ Executing: 010_create_news_posts_table.sql... ✓ Success
→ Executing: 011_create_gallery_items_table.sql... ✓ Success
→ Executing: 012_create_site_settings_table.sql... ✓ Success
→ Executing: 013_create_audit_logs_table.sql... ✓ Success
→ Executing: 014_create_system_logs_table.sql... ✓ Success

═══════════════════════════════════════════
Migration Summary:
  • Executed: 14
  • Skipped:  0
  • Total:    14
═══════════════════════════════════════════

✓ All migrations completed successfully!
```

**Verify**:
```bash
mysql -u test_user -p manufacturing_test -e "SHOW TABLES;"
```

Expected tables:
- admin_users
- audit_logs
- cost_estimate_items
- cost_estimates
- customer_requests
- gallery_items
- material_properties
- materials
- migrations
- news_posts
- pricing_rules
- service_categories
- services
- site_settings
- system_logs

### Test 2: Run Seeds

```bash
php database/seed.php
```

**Expected Output**:
```
✓ Connected to database: manufacturing_test

→ Processing: 001_seed_admin_users.php... ✓ Inserted 2 record(s)
→ Processing: 002_seed_service_categories.php... ✓ Inserted 5 record(s)
→ Processing: 003_seed_materials.php... ✓ Inserted 5 record(s)
→ Processing: 004_seed_site_settings.php... ✓ Inserted 10 record(s)

═══════════════════════════════════════════
Seeding Summary:
  • Files Processed: 4
  • Records Inserted: 22
  • Total Files: 4
═══════════════════════════════════════════

✓ Database seeded successfully!
```

**Verify**:
```sql
-- Check admin users
SELECT id, username, email, role FROM admin_users;

-- Check service categories
SELECT id, name, slug FROM service_categories;

-- Check materials
SELECT id, name, sku, unit_price FROM materials;

-- Check site settings
SELECT setting_key, setting_value, group_name FROM site_settings;
```

### Test 3: Verify Foreign Keys

```sql
-- Check foreign key constraints
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    REFERENCED_TABLE_SCHEMA = 'manufacturing_test'
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY
    TABLE_NAME;
```

Expected constraints:
- service_categories → service_categories (parent_id)
- services → service_categories (category_id)
- material_properties → materials (material_id)
- customer_requests → services (service_id)
- customer_requests → admin_users (assigned_to)
- cost_estimates → customer_requests (customer_request_id)
- cost_estimates → admin_users (created_by)
- cost_estimate_items → cost_estimates (estimate_id)
- news_posts → admin_users (author_id)
- gallery_items → services (service_id)
- gallery_items → admin_users (uploaded_by)
- audit_logs → admin_users (user_id)

### Test 4: Verify Indexes

```sql
-- Check indexes for a table
SHOW INDEX FROM services;
```

Expected indexes on `services`:
- PRIMARY (id)
- uk_services_slug (slug) [UNIQUE]
- idx_services_category_id (category_id)
- idx_services_is_visible (is_visible)
- idx_services_is_featured (is_featured)
- idx_services_display_order (display_order)
- idx_services_deleted_at (deleted_at)

### Test 5: Test Model Operations

Create a test file: `test_models.php`

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Core\Container;
use App\Models\Service;
use App\Models\Material;

$container = Container::getInstance();
$database = $container->get('database');

echo "Testing Models...\n\n";

// Test Service Model
echo "1. Testing Service Model:\n";
$serviceModel = new Service($database);

// Find all
$services = $serviceModel->all();
echo "   - Found " . count($services) . " services\n";

// Create
try {
    $serviceId = $serviceModel->create([
        'category_id' => 1,
        'name' => 'Test Service',
        'slug' => 'test-service-' . time(),
        'description' => 'This is a test service',
        'price_type' => 'quote',
        'is_visible' => true,
    ]);
    echo "   - Created service with ID: {$serviceId}\n";
    
    // Update
    $serviceModel->update($serviceId, ['name' => 'Updated Test Service']);
    echo "   - Updated service\n";
    
    // Find
    $service = $serviceModel->find($serviceId);
    echo "   - Found service: {$service['name']}\n";
    
    // Delete (soft delete)
    $serviceModel->delete($serviceId);
    echo "   - Soft deleted service\n";
} catch (Exception $e) {
    echo "   - Error: " . $e->getMessage() . "\n";
}

echo "\n2. Testing Material Model:\n";
$materialModel = new Material($database);

// Find all
$materials = $materialModel->all();
echo "   - Found " . count($materials) . " materials\n";

// Test pagination
$result = $materialModel->paginate(1, 2);
echo "   - Paginated: " . count($result['data']) . " items on page " . $result['pagination']['current_page'] . "\n";

echo "\n✓ Model tests completed!\n";
```

Run the test:
```bash
php test_models.php
```

### Test 6: Test Repository Operations

Create a test file: `test_repositories.php`

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Core\Container;
use App\Repositories\ServiceRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\SiteSettingRepository;

$container = Container::getInstance();
$database = $container->get('database');

echo "Testing Repositories...\n\n";

// Test Service Repository
echo "1. Testing Service Repository:\n";
$serviceRepo = new ServiceRepository($database);

$visible = $serviceRepo->findVisible();
echo "   - Found " . count($visible) . " visible services\n";

$featured = $serviceRepo->findFeatured(3);
echo "   - Found " . count($featured) . " featured services\n";

// Test Material Repository
echo "\n2. Testing Material Repository:\n";
$materialRepo = new MaterialRepository($database);

$active = $materialRepo->findActive();
echo "   - Found " . count($active) . " active materials\n";

$categories = $materialRepo->getCategories();
echo "   - Found " . count($categories) . " material categories\n";

// Test Site Setting Repository
echo "\n3. Testing Site Setting Repository:\n";
$settingRepo = new SiteSettingRepository($database);

$siteName = $settingRepo->getValue('site_name', 'Default');
echo "   - Site name: {$siteName}\n";

$taxRate = $settingRepo->getValue('tax_rate', 0);
echo "   - Tax rate: {$taxRate}%\n";

$publicSettings = $settingRepo->findPublic();
echo "   - Found " . count($publicSettings) . " public settings\n";

echo "\n✓ Repository tests completed!\n";
```

Run the test:
```bash
php test_repositories.php
```

### Test 7: Test Pagination

```php
<?php

require_once __DIR__ . '/bootstrap.php';

use App\Core\Container;
use App\Models\SiteSetting;

$container = Container::getInstance();
$database = $container->get('database');

$settingModel = new SiteSetting($database);

echo "Testing Pagination...\n\n";

for ($page = 1; $page <= 3; $page++) {
    $result = $settingModel->paginate($page, 3);
    
    echo "Page {$page}:\n";
    echo "  Items: " . count($result['data']) . "\n";
    echo "  From: " . $result['pagination']['from'] . "\n";
    echo "  To: " . $result['pagination']['to'] . "\n";
    echo "  Total: " . $result['pagination']['total'] . "\n";
    echo "  Last Page: " . $result['pagination']['last_page'] . "\n\n";
}

echo "✓ Pagination test completed!\n";
```

### Test 8: Test Soft Deletes

```sql
-- Insert test record
INSERT INTO services (category_id, name, slug, price_type, is_visible) 
VALUES (1, 'Test Service', 'test-service', 'quote', 1);

-- Get ID
SET @service_id = LAST_INSERT_ID();

-- Verify it exists
SELECT COUNT(*) as count FROM services WHERE id = @service_id AND deleted_at IS NULL;
-- Should return: count = 1

-- Soft delete
UPDATE services SET deleted_at = CURRENT_TIMESTAMP WHERE id = @service_id;

-- Verify it's soft deleted
SELECT COUNT(*) as count FROM services WHERE id = @service_id AND deleted_at IS NULL;
-- Should return: count = 0

-- Verify it still exists in table
SELECT COUNT(*) as count FROM services WHERE id = @service_id;
-- Should return: count = 1
```

### Test 9: Test Audit Logging

```php
<?php

require_once __DIR__ . '/bootstrap.php';

use App\Core\Container;
use App\Models\AuditLog;

$container = Container::getInstance();
$database = $container->get('database');

$auditModel = new AuditLog($database);

// Create audit log
$logId = $auditModel->create([
    'user_id' => 1,
    'user_type' => 'admin',
    'event_type' => 'create',
    'auditable_type' => 'services',
    'auditable_id' => 1,
    'old_values' => null,
    'new_values' => json_encode(['name' => 'New Service']),
    'ip_address' => '127.0.0.1',
]);

echo "Created audit log with ID: {$logId}\n";

// Retrieve logs
$logs = $auditModel->where(['user_id' => 1]);
echo "Found " . count($logs) . " audit logs for user\n";

echo "✓ Audit logging test completed!\n";
```

## Test Checklist

- [ ] Migrations execute without errors
- [ ] All 14 tables created successfully
- [ ] `migrations` tracking table created
- [ ] Seeds execute without errors
- [ ] 22+ records inserted
- [ ] Foreign keys created correctly
- [ ] Indexes created on all specified columns
- [ ] Model CRUD operations work
- [ ] Repository custom methods work
- [ ] Pagination works correctly
- [ ] Soft deletes function properly
- [ ] Audit logging works
- [ ] No SQL injection vulnerabilities
- [ ] Performance is acceptable

## Performance Benchmarks

Expected query performance on typical hardware:

- Simple SELECT by ID: < 1ms
- SELECT with JOIN: < 5ms
- Paginated SELECT: < 10ms
- INSERT: < 5ms
- UPDATE: < 5ms
- Complex queries with multiple JOINs: < 50ms

If queries are slower, check:
1. Indexes are properly created
2. Database server configuration
3. Table sizes and data volume

## Troubleshooting

### Migration Errors

**Error**: `Access denied for user`
- **Fix**: Check database credentials in `.env`

**Error**: `Unknown database`
- **Fix**: Create database first: `CREATE DATABASE manufacturing_test;`

**Error**: `Foreign key constraint fails`
- **Fix**: Check migration order, parent tables must be created first

**Error**: `Table already exists`
- **Fix**: Drop and recreate database, or skip migration manually

### Seed Errors

**Error**: `Duplicate entry`
- **Fix**: Normal if running seeds multiple times (unique constraints)

**Error**: `Cannot add or update a child row`
- **Fix**: Ensure migrations have run successfully first

### Performance Issues

**Problem**: Slow queries
- **Fix**: Add missing indexes
- **Fix**: Optimize query using EXPLAIN
- **Fix**: Consider database server tuning

**Problem**: Large result sets
- **Fix**: Always use pagination for large datasets

## Cleanup

To reset the test database:

```bash
# Drop all tables
mysql -u test_user -p manufacturing_test -e "
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS admin_users, audit_logs, cost_estimate_items, cost_estimates, 
customer_requests, gallery_items, material_properties, materials, migrations, 
news_posts, pricing_rules, service_categories, services, site_settings, system_logs;
SET FOREIGN_KEY_CHECKS = 1;
"

# Or drop entire database
mysql -u root -p -e "DROP DATABASE IF EXISTS manufacturing_test;"
```

Then re-run migrations and seeds.

## Production Deployment

Before deploying to production:

1. **Backup existing data** if any
2. **Test migrations** on staging environment
3. **Review seed data** - may not want default passwords in production
4. **Update credentials** in production `.env`
5. **Run migrations**: `php database/migrate.php`
6. **Run seeds** (optional): `php database/seed.php`
7. **Change default passwords** immediately
8. **Verify foreign keys** are working
9. **Test critical queries** for performance
10. **Enable error logging** but disable debug mode

## Support

For issues during testing:
1. Check this guide
2. Review error messages carefully
3. Check MySQL error log
4. Review `/logs/app.log`
5. Check `/database/schema/README.md` for schema details

---

**Last Updated**: 2024-01-01
**Tested On**: MySQL 8.0, PHP 7.4+
