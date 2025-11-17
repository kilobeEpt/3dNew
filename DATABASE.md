# Database Guide

Complete guide for database schema, migrations, seeds, and data access layer.

## Quick Start

### 1. Configure Database

```bash
# Copy environment file
cp .env.example .env

# Edit .env and set database credentials
nano .env
```

Set these variables:
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
DB_CHARSET=utf8mb4
```

### 2. Run Migrations

```bash
# Create all database tables
php database/migrate.php
```

Expected output:
```
✓ Connected to database: your_database_name

→ Executing: 001_create_admin_users_table.sql... ✓ Success
→ Executing: 002_create_service_categories_table.sql... ✓ Success
→ Executing: 003_create_services_table.sql... ✓ Success
...

═══════════════════════════════════════════
Migration Summary:
  • Executed: 14
  • Skipped:  0
  • Total:    14
═══════════════════════════════════════════

✓ All migrations completed successfully!
```

### 3. Seed Initial Data

```bash
# Insert default data
php database/seed.php
```

Expected output:
```
✓ Connected to database: your_database_name

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

### 4. Verify Installation

```bash
# Connect to MySQL
mysql -u your_user -p your_database_name

# Check tables
SHOW TABLES;

# Check seed data
SELECT * FROM admin_users;
SELECT * FROM service_categories;
SELECT * FROM site_settings;
```

## Database Schema

### Tables Overview

| Table | Purpose | Records |
|-------|---------|---------|
| `admin_users` | Administrator accounts | Seeded with 2 users |
| `service_categories` | Service categories (hierarchical) | Seeded with 5 categories |
| `services` | Available services/products | Empty (add via admin) |
| `materials` | Materials and components | Seeded with 5 materials |
| `material_properties` | Custom material attributes | Empty |
| `pricing_rules` | Dynamic pricing rules | Empty |
| `customer_requests` | Customer quote requests | Empty |
| `cost_estimates` | Cost estimates | Empty |
| `cost_estimate_items` | Estimate line items | Empty |
| `news_posts` | Blog posts and news | Empty |
| `gallery_items` | Portfolio/gallery media | Empty |
| `site_settings` | Application settings | Seeded with 10 settings |
| `audit_logs` | User action audit trail | Empty |
| `system_logs` | Application errors/events | Empty |

### Default Credentials

After seeding, you can login with:

**Super Admin**:
- Username: `admin`
- Password: `admin123`
- Email: `admin@example.com`

**Editor**:
- Username: `editor`
- Password: `editor123`
- Email: `editor@example.com`

**⚠️ SECURITY WARNING**: Change these credentials immediately in production!

## Data Access Layer

### Using Models (Active Record Pattern)

Models provide ORM-like functionality with automatic soft deletes and fillable fields.

#### Basic CRUD Operations

```php
use App\Core\Database;
use App\Models\Service;
use App\Core\Container;

// Get database instance from container
$container = Container::getInstance();
$database = $container->get('database');

// Initialize model
$serviceModel = new Service($database);

// Find all records
$services = $serviceModel->all();

// Find by primary key
$service = $serviceModel->find(1);

// Find with conditions
$activeServices = $serviceModel->where(['is_visible' => true]);

// Find first matching record
$featured = $serviceModel->first(['is_featured' => true]);

// Create new record
$id = $serviceModel->create([
    'category_id' => 1,
    'name' => 'Custom CNC Machining',
    'slug' => 'custom-cnc-machining',
    'description' => 'High-precision CNC machining services',
    'price_type' => 'quote',
    'is_visible' => true,
]);

// Update record
$serviceModel->update($id, [
    'name' => 'Updated Service Name',
]);

// Delete record (soft delete if enabled)
$serviceModel->delete($id);

// Pagination
$result = $serviceModel->paginate($page = 1, $perPage = 20);
// Returns: ['data' => [...], 'pagination' => [...]]
```

#### Available Models

```php
use App\Models\AdminUser;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Material;
use App\Models\MaterialProperty;
use App\Models\PricingRule;
use App\Models\CustomerRequest;
use App\Models\CostEstimate;
use App\Models\CostEstimateItem;
use App\Models\NewsPost;
use App\Models\GalleryItem;
use App\Models\SiteSetting;
use App\Models\AuditLog;
use App\Models\SystemLog;
```

### Using Repositories (Repository Pattern)

Repositories provide advanced query methods and business logic.

#### Service Repository

```php
use App\Repositories\ServiceRepository;

$serviceRepo = new ServiceRepository($database);

// Find by slug
$service = $serviceRepo->findBySlug('custom-cnc-machining');

// Find visible services
$visibleServices = $serviceRepo->findVisible();

// Find featured services (limited)
$featured = $serviceRepo->findFeatured(6);

// Find by category
$categoryServices = $serviceRepo->findByCategory(1, $visibleOnly = true);

// Standard repository methods
$service = $serviceRepo->findById(1);
$services = $serviceRepo->findAll(['is_visible' => true]);
$count = $serviceRepo->count(['is_visible' => true]);
```

#### Material Repository

```php
use App\Repositories\MaterialRepository;

$materialRepo = new MaterialRepository($database);

// Find active materials
$materials = $materialRepo->findActive();

// Find by category
$metals = $materialRepo->findByCategory('Metals');

// Find by SKU
$material = $materialRepo->findBySku('AL-6061-001');

// Find low stock items
$lowStock = $materialRepo->findLowStock($threshold = 10.0);

// Get all categories
$categories = $materialRepo->getCategories();
```

#### Customer Request Repository

```php
use App\Repositories\CustomerRequestRepository;

$requestRepo = new CustomerRequestRepository($database);

// Find by status
$newRequests = $requestRepo->findByStatus('new');

// Find by priority
$urgent = $requestRepo->findByPriority('urgent');

// Find assigned to user
$myRequests = $requestRepo->findAssignedTo($userId);

// Find unassigned
$unassigned = $requestRepo->findUnassigned();

// Generate unique request number
$requestNumber = $requestRepo->generateRequestNumber();
// Returns: REQ20240101001

// Get statistics
$stats = $requestRepo->getStatistics();
// Returns: ['total' => 100, 'by_status' => ['new' => 20, ...]]
```

#### Cost Estimate Repository

```php
use App\Repositories\CostEstimateRepository;

$estimateRepo = new CostEstimateRepository($database);

// Find by status
$sent = $estimateRepo->findByStatus('sent');

// Find by customer email
$customerEstimates = $estimateRepo->findByCustomerEmail('customer@example.com');

// Generate unique estimate number
$estimateNumber = $estimateRepo->generateEstimateNumber();
// Returns: EST20240101001

// Get estimate with items
$estimate = $estimateRepo->getWithItems($estimateId);
// Returns estimate with 'items' array

// Update status
$estimateRepo->markAsSent($estimateId);
$estimateRepo->markAsViewed($estimateId);
$estimateRepo->markAsAccepted($estimateId);
```

#### Site Setting Repository

```php
use App\Repositories\SiteSettingRepository;

$settingRepo = new SiteSettingRepository($database);

// Get setting value
$siteName = $settingRepo->getValue('site_name', 'Default Name');
$taxRate = $settingRepo->getValue('tax_rate', 0); // Auto-casts to float

// Set setting value
$settingRepo->setValue('site_name', 'My Manufacturing Site');

// Find by group
$contactSettings = $settingRepo->findByGroup('contact');

// Find public settings (for frontend)
$publicSettings = $settingRepo->findPublic();

// Get all settings as array
$allSettings = $settingRepo->getAllAsArray();
// Returns: ['site_name' => 'value', 'tax_rate' => 8.5, ...]
```

### Pagination Helper

All repositories support pagination:

```php
$result = $repository->paginate(
    $page = 1,
    $perPage = 20,
    $conditions = ['status' => 'active'],
    $orderBy = ['created_at' => 'DESC']
);

// Response format:
[
    'data' => [
        // Array of records
    ],
    'pagination' => [
        'current_page' => 1,
        'per_page' => 20,
        'total' => 100,
        'last_page' => 5,
        'from' => 1,
        'to' => 20,
    ]
]
```

### Search Helper

```php
// Search by column
$results = $repository->search(
    $column = 'name',
    $term = 'aluminum',
    $additionalConditions = ['is_active' => true],
    $limit = 20
);
```

## Advanced Usage

### Working with Relationships

```php
// Get customer request with related data
$request = $customerRequestModel->find($id);

// Get related service
$serviceSql = "SELECT * FROM services WHERE id = ?";
$service = $database->fetchOne($serviceSql, [$request['service_id']]);

// Get assigned admin
$adminSql = "SELECT * FROM admin_users WHERE id = ?";
$admin = $database->fetchOne($adminSql, [$request['assigned_to']]);
```

### Creating Estimates with Items

```php
use App\Models\CostEstimate;
use App\Models\CostEstimateItem;

// Create estimate
$estimateModel = new CostEstimate($database);
$estimateId = $estimateModel->create([
    'estimate_number' => 'EST20240101001',
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'title' => 'Custom Manufacturing Quote',
    'subtotal' => 1000.00,
    'tax_rate' => 8.5,
    'tax_amount' => 85.00,
    'total_amount' => 1085.00,
    'status' => 'draft',
]);

// Add items
$itemModel = new CostEstimateItem($database);

$itemModel->create([
    'estimate_id' => $estimateId,
    'item_type' => 'service',
    'item_id' => 1,
    'description' => 'CNC Machining - 10 hours',
    'quantity' => 10,
    'unit' => 'hours',
    'unit_price' => 75.00,
    'line_total' => 750.00,
    'display_order' => 1,
]);

$itemModel->create([
    'estimate_id' => $estimateId,
    'item_type' => 'material',
    'item_id' => 2,
    'description' => 'Aluminum Sheet 6061 - 5 sqft',
    'quantity' => 5,
    'unit' => 'sqft',
    'unit_price' => 50.00,
    'line_total' => 250.00,
    'display_order' => 2,
]);
```

### Audit Logging

```php
use App\Models\AuditLog;

$auditModel = new AuditLog($database);

$auditModel->create([
    'user_id' => $adminId,
    'user_type' => 'admin',
    'event_type' => 'update',
    'auditable_type' => 'services',
    'auditable_id' => $serviceId,
    'old_values' => json_encode(['name' => 'Old Name']),
    'new_values' => json_encode(['name' => 'New Name']),
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
]);
```

### System Logging

```php
use App\Models\SystemLog;

$logModel = new SystemLog($database);

$logModel->create([
    'level' => 'error',
    'message' => 'Database connection failed',
    'context' => json_encode(['host' => 'localhost', 'port' => 3306]),
    'channel' => 'database',
    'file' => __FILE__,
    'line' => __LINE__,
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
]);
```

## Migration Management

### Running Migrations

```bash
# Run all pending migrations
php database/migrate.php
```

The migration script:
- Tracks executed migrations in `migrations` table
- Executes only new migrations
- Provides detailed output
- Stops on first error

### Creating New Migrations

1. Create new file in `/database/migrations/`:
   ```
   015_create_your_table.sql
   ```

2. Follow naming convention:
   - Format: `{number}_create_{table}_table.sql`
   - Number: Sequential, three digits
   - Name: Descriptive, snake_case

3. Write SQL:
   ```sql
   -- Migration: Create your_table
   -- Description: Purpose of this table
   -- Created: 2024-01-01

   CREATE TABLE IF NOT EXISTS `your_table` (
       `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
       `name` VARCHAR(255) NOT NULL,
       `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       PRIMARY KEY (`id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

4. Run migration:
   ```bash
   php database/migrate.php
   ```

## Seed Management

### Running Seeds

```bash
# Run all seed files
php database/seed.php
```

The seed script:
- Executes all PHP files in `/database/seeds/`
- Skips duplicate entries gracefully
- Provides detailed output

### Creating New Seeds

1. Create new file in `/database/seeds/`:
   ```
   005_seed_your_data.php
   ```

2. Follow naming convention:
   - Format: `{number}_seed_{description}.php`
   - Number: Sequential, three digits

3. Write seed:
   ```php
   <?php
   
   declare(strict_types=1);
   
   return [
       'table' => 'your_table',
       'data' => [
           [
               'name' => 'First Record',
               // ... more fields
           ],
           [
               'name' => 'Second Record',
               // ... more fields
           ],
       ],
   ];
   ```

4. Run seed:
   ```bash
   php database/seed.php
   ```

## Best Practices

### Security

1. **Always use prepared statements**:
   ```php
   // Good
   $stmt = $database->query("SELECT * FROM users WHERE id = ?", [$id]);
   
   // Bad - SQL injection risk!
   $stmt = $database->query("SELECT * FROM users WHERE id = {$id}");
   ```

2. **Hash passwords**:
   ```php
   $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
   ```

3. **Validate input**:
   ```php
   use App\Helpers\Validator;
   
   $validator = Validator::make($data, [
       'email' => 'required|email',
       'name' => 'required|min:3|max:255',
   ]);
   ```

### Performance

1. **Use pagination for large datasets**:
   ```php
   $result = $model->paginate(1, 20);
   ```

2. **Use indexes** on frequently queried columns

3. **Limit columns** in SELECT:
   ```php
   $users = $model->all(['id', 'name', 'email']);
   ```

4. **Use soft deletes** to prevent data loss

### Data Integrity

1. **Use transactions** for multi-table operations:
   ```php
   $pdo = $database->getConnection();
   
   try {
       $pdo->beginTransaction();
       
       // Multiple operations
       $model1->create($data1);
       $model2->create($data2);
       
       $pdo->commit();
   } catch (Exception $e) {
       $pdo->rollBack();
       throw $e;
   }
   ```

2. **Validate foreign keys** before insertion

3. **Use appropriate data types** in migrations

## Troubleshooting

### Migration Issues

**Problem**: Migration fails with foreign key constraint error
- **Solution**: Check migration order, parent tables must exist first

**Problem**: Duplicate column error
- **Solution**: Migration may have run partially, check table structure:
  ```sql
  DESCRIBE your_table;
  ```

**Problem**: Connection refused
- **Solution**: Verify `.env` database credentials

### Seed Issues

**Problem**: Duplicate entry error
- **Solution**: Seeds may have already run, this is normal

**Problem**: Foreign key constraint fails
- **Solution**: Ensure parent records exist (e.g., categories before services)

### Performance Issues

**Problem**: Slow queries
- **Solution**: Add indexes to frequently queried columns
  ```sql
  CREATE INDEX idx_column_name ON table_name (column_name);
  ```

**Problem**: Large result sets
- **Solution**: Use pagination instead of fetching all records

## Documentation

Full documentation available in:
- `/database/schema/README.md` - Complete schema documentation
- `/database/schema/ER_DIAGRAM.txt` - Visual entity relationships
- This file - Usage guide

## Support

For issues or questions:
1. Check this documentation
2. Review migration files in `/database/migrations/`
3. Review seed files in `/database/seeds/`
4. Check model files in `/src/Models/`
5. Check repository files in `/src/Repositories/`
6. Check application logs in `/logs/`

---

**Last Updated**: 2024-01-01
**Schema Version**: 1.0.0
