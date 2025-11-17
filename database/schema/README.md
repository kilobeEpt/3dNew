# Database Schema Documentation

## Overview

This document describes the database schema for the Manufacturing Services API platform. The schema is designed for MySQL 8.0+ and supports shared hosting environments.

## Table of Contents

1. [Schema Overview](#schema-overview)
2. [Tables](#tables)
3. [Relationships](#relationships)
4. [Indexes and Performance](#indexes-and-performance)
5. [Migration Instructions](#migration-instructions)
6. [Seed Data](#seed-data)
7. [Data Access Layer](#data-access-layer)

---

## Schema Overview

The database consists of 14 tables organized into the following functional groups:

### Core Tables
- **admin_users**: Administrator accounts
- **site_settings**: Application configuration

### Service Management
- **service_categories**: Hierarchical service categories
- **services**: Available services/products

### Material Management
- **materials**: Materials and components
- **material_properties**: Custom material attributes

### Pricing & Estimation
- **pricing_rules**: Dynamic pricing rules
- **cost_estimates**: Customer cost estimates
- **cost_estimate_items**: Line items for estimates

### Customer Management
- **customer_requests**: Quote requests and inquiries

### Content Management
- **news_posts**: Blog posts and news articles
- **gallery_items**: Portfolio/gallery media

### System Tables
- **audit_logs**: User action audit trail
- **system_logs**: Application errors and events

---

## Tables

### 1. admin_users

**Purpose**: Stores administrator user accounts with role-based access control.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `username` (VARCHAR 50): Unique username
- `email` (VARCHAR 255): Unique email address
- `password` (VARCHAR 255): Bcrypt hashed password
- `first_name` (VARCHAR 100): First name
- `last_name` (VARCHAR 100): Last name
- `role` (ENUM): Role (super_admin, admin, editor, viewer)
- `status` (ENUM): Status (active, inactive, suspended)
- `last_login_at` (TIMESTAMP): Last login timestamp
- `last_login_ip` (VARCHAR 45): Last login IP address
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp
- `deleted_at` (TIMESTAMP): Soft delete timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `username`
- UNIQUE KEY on `email`
- INDEX on `role`
- INDEX on `status`
- INDEX on `deleted_at`

**Features**:
- Soft deletes enabled
- Role-based access control
- Login tracking

---

### 2. service_categories

**Purpose**: Stores hierarchical service categories with parent-child relationships.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `parent_id` (BIGINT UNSIGNED): Parent category ID (self-referencing)
- `name` (VARCHAR 255): Category name
- `slug` (VARCHAR 255): URL-friendly slug
- `description` (TEXT): Category description
- `icon` (VARCHAR 255): Icon identifier
- `image` (VARCHAR 255): Category image path
- `display_order` (INT): Sort order
- `is_visible` (BOOLEAN): Visibility flag
- `meta_title` (VARCHAR 255): SEO meta title
- `meta_description` (TEXT): SEO meta description
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp
- `deleted_at` (TIMESTAMP): Soft delete timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `slug`
- INDEX on `parent_id`
- INDEX on `is_visible`
- INDEX on `display_order`
- INDEX on `deleted_at`

**Foreign Keys**:
- `parent_id` references `service_categories.id`

**Features**:
- Hierarchical categories
- SEO-friendly slugs
- Soft deletes
- Visibility control

---

### 3. services

**Purpose**: Stores available services and products offered.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `category_id` (BIGINT UNSIGNED): Category reference
- `name` (VARCHAR 255): Service name
- `slug` (VARCHAR 255): URL-friendly slug
- `description` (TEXT): Full description
- `short_description` (VARCHAR 500): Short description
- `image` (VARCHAR 255): Service image path
- `price_type` (ENUM): Pricing type (fixed, variable, quote)
- `base_price` (DECIMAL 10,2): Base price
- `unit` (VARCHAR 50): Pricing unit
- `display_order` (INT): Sort order
- `is_visible` (BOOLEAN): Visibility flag
- `is_featured` (BOOLEAN): Featured flag
- `meta_title` (VARCHAR 255): SEO meta title
- `meta_description` (TEXT): SEO meta description
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp
- `deleted_at` (TIMESTAMP): Soft delete timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `slug`
- INDEX on `category_id`
- INDEX on `is_visible`
- INDEX on `is_featured`
- INDEX on `display_order`
- INDEX on `deleted_at`

**Foreign Keys**:
- `category_id` references `service_categories.id`

**Features**:
- Flexible pricing types
- Featured services
- SEO optimization
- Soft deletes

---

### 4. materials

**Purpose**: Stores materials and components used in manufacturing.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `name` (VARCHAR 255): Material name
- `slug` (VARCHAR 255): URL-friendly slug
- `sku` (VARCHAR 100): Stock keeping unit
- `description` (TEXT): Material description
- `category` (VARCHAR 100): Material category
- `unit` (VARCHAR 50): Measurement unit
- `unit_price` (DECIMAL 10,2): Price per unit
- `stock_quantity` (DECIMAL 10,2): Current stock level
- `min_order_quantity` (DECIMAL 10,2): Minimum order quantity
- `supplier` (VARCHAR 255): Supplier name
- `supplier_sku` (VARCHAR 100): Supplier SKU
- `image` (VARCHAR 255): Material image path
- `is_active` (BOOLEAN): Active status
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp
- `deleted_at` (TIMESTAMP): Soft delete timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `slug`
- UNIQUE KEY on `sku`
- INDEX on `category`
- INDEX on `is_active`
- INDEX on `deleted_at`

**Features**:
- Inventory tracking
- Supplier management
- Soft deletes

---

### 5. material_properties

**Purpose**: Stores custom properties and attributes for materials.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `material_id` (BIGINT UNSIGNED): Material reference
- `property_name` (VARCHAR 100): Property name
- `property_value` (TEXT): Property value
- `property_type` (ENUM): Data type (text, number, boolean, date, json)
- `display_order` (INT): Sort order
- `is_searchable` (BOOLEAN): Searchable flag
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `material_id`
- INDEX on `property_name`
- INDEX on `is_searchable`

**Foreign Keys**:
- `material_id` references `materials.id` (CASCADE on delete)

**Features**:
- Flexible key-value attributes
- Type-safe property values
- Searchable properties

---

### 6. pricing_rules

**Purpose**: Stores dynamic pricing rules for services and materials.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `name` (VARCHAR 255): Rule name
- `description` (TEXT): Rule description
- `rule_type` (ENUM): Rule type (service, material, global)
- `target_id` (BIGINT UNSIGNED): Target service/material ID
- `condition_type` (ENUM): Condition type (quantity, date_range, customer_type, total_amount)
- `condition_value` (JSON): Flexible condition parameters
- `discount_type` (ENUM): Discount type (percentage, fixed_amount, fixed_price)
- `discount_value` (DECIMAL 10,2): Discount value
- `priority` (INT): Rule priority
- `is_active` (BOOLEAN): Active status
- `valid_from` (DATE): Valid from date
- `valid_to` (DATE): Valid to date
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp
- `deleted_at` (TIMESTAMP): Soft delete timestamp

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `rule_type`
- INDEX on `target_id`
- INDEX on `is_active`
- INDEX on `priority`
- INDEX on `valid_from, valid_to`
- INDEX on `deleted_at`

**Features**:
- Flexible pricing conditions
- Priority-based rule application
- Date-range validity
- Soft deletes

---

### 7. customer_requests

**Purpose**: Stores customer quote requests and inquiries.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `request_number` (VARCHAR 50): Unique request number
- `customer_name` (VARCHAR 255): Customer name
- `customer_email` (VARCHAR 255): Customer email
- `customer_phone` (VARCHAR 50): Customer phone
- `customer_company` (VARCHAR 255): Company name
- `service_id` (BIGINT UNSIGNED): Requested service
- `subject` (VARCHAR 255): Request subject
- `message` (TEXT): Request message
- `request_type` (ENUM): Request type (quote, inquiry, support, other)
- `priority` (ENUM): Priority (low, normal, high, urgent)
- `status` (ENUM): Status (new, pending, in_progress, completed, cancelled)
- `assigned_to` (BIGINT UNSIGNED): Assigned admin user
- `estimated_budget` (DECIMAL 10,2): Estimated budget
- `notes` (TEXT): Internal notes
- `metadata` (JSON): Additional form data
- `ip_address` (VARCHAR 45): Submitter IP
- `user_agent` (VARCHAR 500): Submitter user agent
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp
- `deleted_at` (TIMESTAMP): Soft delete timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `request_number`
- INDEX on `customer_email`
- INDEX on `service_id`
- INDEX on `status`
- INDEX on `priority`
- INDEX on `assigned_to`
- INDEX on `deleted_at`

**Foreign Keys**:
- `service_id` references `services.id`
- `assigned_to` references `admin_users.id`

**Features**:
- Automatic request numbering
- Assignment tracking
- Priority management
- Soft deletes

---

### 8. cost_estimates

**Purpose**: Stores detailed cost estimates for customer requests.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `estimate_number` (VARCHAR 50): Unique estimate number
- `customer_request_id` (BIGINT UNSIGNED): Related request
- `customer_name` (VARCHAR 255): Customer name
- `customer_email` (VARCHAR 255): Customer email
- `customer_phone` (VARCHAR 50): Customer phone
- `title` (VARCHAR 255): Estimate title
- `description` (TEXT): Estimate description
- `subtotal` (DECIMAL 10,2): Subtotal amount
- `tax_rate` (DECIMAL 5,2): Tax rate percentage
- `tax_amount` (DECIMAL 10,2): Tax amount
- `discount_amount` (DECIMAL 10,2): Discount amount
- `total_amount` (DECIMAL 10,2): Total amount
- `currency` (VARCHAR 3): Currency code
- `status` (ENUM): Status (draft, sent, viewed, accepted, rejected, expired)
- `valid_until` (DATE): Expiration date
- `notes` (TEXT): Internal notes
- `terms_conditions` (TEXT): Terms and conditions
- `created_by` (BIGINT UNSIGNED): Creator admin user
- `sent_at` (TIMESTAMP): Sent timestamp
- `viewed_at` (TIMESTAMP): Viewed timestamp
- `accepted_at` (TIMESTAMP): Accepted timestamp
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp
- `deleted_at` (TIMESTAMP): Soft delete timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `estimate_number`
- INDEX on `customer_request_id`
- INDEX on `customer_email`
- INDEX on `status`
- INDEX on `created_by`
- INDEX on `deleted_at`

**Foreign Keys**:
- `customer_request_id` references `customer_requests.id`
- `created_by` references `admin_users.id`

**Features**:
- Automatic estimate numbering
- Tax calculations
- Status tracking
- Soft deletes

---

### 9. cost_estimate_items

**Purpose**: Stores line items for cost estimates.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `estimate_id` (BIGINT UNSIGNED): Estimate reference
- `item_type` (ENUM): Item type (service, material, custom)
- `item_id` (BIGINT UNSIGNED): Service/Material ID
- `description` (VARCHAR 500): Item description
- `quantity` (DECIMAL 10,2): Quantity
- `unit` (VARCHAR 50): Unit of measurement
- `unit_price` (DECIMAL 10,2): Price per unit
- `line_total` (DECIMAL 10,2): Line total
- `display_order` (INT): Sort order
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `estimate_id`
- INDEX on `item_type, item_id`
- INDEX on `display_order`

**Foreign Keys**:
- `estimate_id` references `cost_estimates.id` (CASCADE on delete)

**Features**:
- Flexible item types
- Automatic line total calculation
- Sortable items

---

### 10. news_posts

**Purpose**: Stores blog posts and news articles.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `author_id` (BIGINT UNSIGNED): Author admin user
- `title` (VARCHAR 255): Post title
- `slug` (VARCHAR 255): URL-friendly slug
- `excerpt` (VARCHAR 500): Short excerpt
- `content` (LONGTEXT): Full content
- `featured_image` (VARCHAR 255): Featured image path
- `category` (VARCHAR 100): Post category
- `tags` (JSON): Post tags
- `status` (ENUM): Status (draft, published, scheduled, archived)
- `is_featured` (BOOLEAN): Featured flag
- `view_count` (INT UNSIGNED): View count
- `published_at` (TIMESTAMP): Publication timestamp
- `meta_title` (VARCHAR 255): SEO meta title
- `meta_description` (TEXT): SEO meta description
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp
- `deleted_at` (TIMESTAMP): Soft delete timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `slug`
- INDEX on `author_id`
- INDEX on `status`
- INDEX on `category`
- INDEX on `is_featured`
- INDEX on `published_at`
- INDEX on `deleted_at`

**Foreign Keys**:
- `author_id` references `admin_users.id`

**Features**:
- Draft/publish workflow
- SEO optimization
- View tracking
- Soft deletes

---

### 11. gallery_items

**Purpose**: Stores portfolio and gallery media items.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `title` (VARCHAR 255): Item title
- `slug` (VARCHAR 255): URL-friendly slug
- `description` (TEXT): Item description
- `file_path` (VARCHAR 255): File path
- `file_type` (ENUM): File type (image, video, document)
- `file_size` (INT UNSIGNED): File size in bytes
- `mime_type` (VARCHAR 100): MIME type
- `width` (INT UNSIGNED): Image width
- `height` (INT UNSIGNED): Image height
- `thumbnail_path` (VARCHAR 255): Thumbnail path
- `category` (VARCHAR 100): Item category
- `tags` (JSON): Item tags
- `service_id` (BIGINT UNSIGNED): Related service
- `display_order` (INT): Sort order
- `is_visible` (BOOLEAN): Visibility flag
- `is_featured` (BOOLEAN): Featured flag
- `view_count` (INT UNSIGNED): View count
- `uploaded_by` (BIGINT UNSIGNED): Uploader admin user
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp
- `deleted_at` (TIMESTAMP): Soft delete timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `slug`
- INDEX on `file_type`
- INDEX on `category`
- INDEX on `service_id`
- INDEX on `is_visible`
- INDEX on `is_featured`
- INDEX on `display_order`
- INDEX on `uploaded_by`
- INDEX on `deleted_at`

**Foreign Keys**:
- `service_id` references `services.id`
- `uploaded_by` references `admin_users.id`

**Features**:
- Multi-media support
- Automatic thumbnails
- View tracking
- Soft deletes

---

### 12. site_settings

**Purpose**: Stores application-wide configuration settings.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `setting_key` (VARCHAR 100): Unique setting key
- `setting_value` (TEXT): Setting value
- `setting_type` (ENUM): Data type (string, number, boolean, json, text)
- `group_name` (VARCHAR 100): Setting group
- `description` (VARCHAR 500): Setting description
- `is_public` (BOOLEAN): Public access flag
- `display_order` (INT): Sort order
- `created_at` (TIMESTAMP): Creation timestamp
- `updated_at` (TIMESTAMP): Last update timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE KEY on `setting_key`
- INDEX on `group_name`
- INDEX on `is_public`

**Features**:
- Type-safe values
- Grouped settings
- Public/private access control

---

### 13. audit_logs

**Purpose**: Stores audit trail for important system actions.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `user_id` (BIGINT UNSIGNED): User reference
- `user_type` (ENUM): User type (admin, customer, system)
- `event_type` (VARCHAR 100): Event type
- `auditable_type` (VARCHAR 100): Table/model name
- `auditable_id` (BIGINT UNSIGNED): Record ID
- `old_values` (JSON): Previous values
- `new_values` (JSON): New values
- `ip_address` (VARCHAR 45): User IP address
- `user_agent` (VARCHAR 500): User agent
- `metadata` (JSON): Additional metadata
- `created_at` (TIMESTAMP): Creation timestamp

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `user_id`
- INDEX on `event_type`
- INDEX on `auditable_type, auditable_id`
- INDEX on `created_at`

**Foreign Keys**:
- `user_id` references `admin_users.id`

**Features**:
- Complete audit trail
- Change tracking
- IP and user agent logging

---

### 14. system_logs

**Purpose**: Stores application errors and system events.

**Columns**:
- `id` (BIGINT UNSIGNED): Primary key
- `level` (ENUM): Log level (emergency, alert, critical, error, warning, notice, info, debug)
- `message` (TEXT): Log message
- `context` (JSON): Log context
- `channel` (VARCHAR 100): Log channel
- `exception_class` (VARCHAR 255): Exception class
- `exception_message` (TEXT): Exception message
- `exception_trace` (LONGTEXT): Exception stack trace
- `file` (VARCHAR 500): File path
- `line` (INT UNSIGNED): Line number
- `user_id` (BIGINT UNSIGNED): User reference
- `ip_address` (VARCHAR 45): User IP address
- `url` (VARCHAR 1000): Request URL
- `method` (VARCHAR 10): HTTP method
- `created_at` (TIMESTAMP): Creation timestamp

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `level`
- INDEX on `channel`
- INDEX on `user_id`
- INDEX on `created_at`

**Features**:
- PSR-3 compliant log levels
- Exception tracking
- Request context

---

## Relationships

### Entity Relationship Diagram

```
┌─────────────────┐
│  admin_users    │
└────────┬────────┘
         │
         ├──────────────────────┐
         │                      │
         │                      │
┌────────▼────────┐    ┌───────▼────────┐
│ customer_       │    │  cost_         │
│ requests        │    │  estimates     │
└────────┬────────┘    └────────┬───────┘
         │                      │
         │              ┌───────▼───────────┐
         │              │ cost_estimate_    │
         │              │ items             │
         │              └───────────────────┘
         │
┌────────▼────────┐
│  services       │
└────────┬────────┘
         │
┌────────▼──────────┐
│ service_          │
│ categories        │
└───────────────────┘


┌─────────────────┐
│  materials      │
└────────┬────────┘
         │
┌────────▼──────────┐
│ material_         │
│ properties        │
└───────────────────┘


┌─────────────────┐
│  news_posts     │
└─────────────────┘

┌─────────────────┐
│ gallery_items   │
└─────────────────┘

┌─────────────────┐
│ pricing_rules   │
└─────────────────┘

┌─────────────────┐
│ site_settings   │
└─────────────────┘

┌─────────────────┐
│  audit_logs     │
└─────────────────┘

┌─────────────────┐
│  system_logs    │
└─────────────────┘
```

### Key Relationships

1. **admin_users → customer_requests**: One-to-many (assigned_to)
2. **admin_users → cost_estimates**: One-to-many (created_by)
3. **admin_users → news_posts**: One-to-many (author_id)
4. **admin_users → gallery_items**: One-to-many (uploaded_by)
5. **admin_users → audit_logs**: One-to-many (user_id)
6. **service_categories → service_categories**: Self-referencing (parent_id)
7. **service_categories → services**: One-to-many (category_id)
8. **services → customer_requests**: One-to-many (service_id)
9. **services → gallery_items**: One-to-many (service_id)
10. **materials → material_properties**: One-to-many (material_id)
11. **customer_requests → cost_estimates**: One-to-many (customer_request_id)
12. **cost_estimates → cost_estimate_items**: One-to-many (estimate_id)

---

## Indexes and Performance

### Indexing Strategy

1. **Primary Keys**: All tables use auto-incrementing BIGINT UNSIGNED primary keys
2. **Foreign Keys**: Indexed automatically for join performance
3. **Unique Constraints**: Applied to slugs, emails, usernames, SKUs
4. **Composite Indexes**: Created for common query patterns
5. **Soft Delete Indexes**: Applied to `deleted_at` columns
6. **Status/Flag Indexes**: Applied to frequently filtered columns

### Performance Considerations

1. **InnoDB Engine**: Used for ACID compliance and foreign key support
2. **UTF8MB4 Charset**: Full Unicode support including emojis
3. **Timestamp Indexes**: Optimized for date-based queries
4. **JSON Columns**: Used for flexible, schema-less data
5. **Soft Deletes**: Prevent accidental data loss
6. **Pagination**: Supported through LIMIT and OFFSET

---

## Migration Instructions

### Prerequisites

- MySQL 8.0+
- PHP 7.4+
- Composer dependencies installed
- Database credentials configured in `.env` file

### Running Migrations

1. **Configure Database**:
   ```bash
   cp .env.example .env
   # Edit .env and set DB_* variables
   ```

2. **Run Migrations**:
   ```bash
   php database/migrate.php
   ```

3. **Verify Migration**:
   ```sql
   USE your_database;
   SHOW TABLES;
   SELECT * FROM migrations;
   ```

### Migration Files

Migrations are located in `/database/migrations/` and are executed in numerical order:

- `001_create_admin_users_table.sql`
- `002_create_service_categories_table.sql`
- `003_create_services_table.sql`
- `004_create_materials_table.sql`
- `005_create_material_properties_table.sql`
- `006_create_pricing_rules_table.sql`
- `007_create_customer_requests_table.sql`
- `008_create_cost_estimates_table.sql`
- `009_create_cost_estimate_items_table.sql`
- `010_create_news_posts_table.sql`
- `011_create_gallery_items_table.sql`
- `012_create_site_settings_table.sql`
- `013_create_audit_logs_table.sql`
- `014_create_system_logs_table.sql`

### Rollback

To rollback migrations, manually drop tables or restore from backup. Automated rollback is not implemented to prevent accidental data loss in production.

---

## Seed Data

### Running Seeds

1. **Run Seeds**:
   ```bash
   php database/seed.php
   ```

2. **Verify Seed Data**:
   ```sql
   SELECT * FROM admin_users;
   SELECT * FROM service_categories;
   SELECT * FROM materials;
   SELECT * FROM site_settings;
   ```

### Seed Files

Seeds are located in `/database/seeds/`:

- `001_seed_admin_users.php`: Default admin accounts
- `002_seed_service_categories.php`: Sample service categories
- `003_seed_materials.php`: Sample materials
- `004_seed_site_settings.php`: Default application settings

### Default Credentials

**Administrator**:
- Username: `admin`
- Password: `admin123`
- Email: `admin@example.com`

**Editor**:
- Username: `editor`
- Password: `editor123`
- Email: `editor@example.com`

**⚠️ IMPORTANT**: Change these credentials immediately in production!

---

## Data Access Layer

### Architecture

The data access layer uses two patterns:

1. **Models** (`/src/Models/`): Active Record pattern with ORM-like features
2. **Repositories** (`/src/Repositories/`): Repository pattern for complex queries

### Using Models

```php
use App\Core\Database;
use App\Models\Service;

$database = new Database([/* config */]);
$serviceModel = new Service($database);

// Find all
$services = $serviceModel->all();

// Find by ID
$service = $serviceModel->find(1);

// Find with conditions
$services = $serviceModel->where(['is_visible' => true]);

// Create
$id = $serviceModel->create([
    'name' => 'Custom Service',
    'slug' => 'custom-service',
    // ...
]);

// Update
$serviceModel->update(1, ['name' => 'Updated Name']);

// Delete (soft delete if enabled)
$serviceModel->delete(1);

// Pagination
$result = $serviceModel->paginate($page = 1, $perPage = 20);
```

### Using Repositories

```php
use App\Repositories\ServiceRepository;

$serviceRepo = new ServiceRepository($database);

// Custom methods
$service = $serviceRepo->findBySlug('custom-service');
$featured = $serviceRepo->findFeatured(6);
$visible = $serviceRepo->findVisible();
$byCategory = $serviceRepo->findByCategory(1);

// Base methods
$service = $serviceRepo->findById(1);
$services = $serviceRepo->findAll(['is_visible' => true]);
$count = $serviceRepo->count(['is_visible' => true]);
$paginated = $serviceRepo->paginate(1, 20, ['is_visible' => true]);
```

### Available Models

- `AdminUser`
- `Service`
- `ServiceCategory`
- `Material`
- `MaterialProperty`
- `PricingRule`
- `CustomerRequest`
- `CostEstimate`
- `CostEstimateItem`
- `NewsPost`
- `GalleryItem`
- `SiteSetting`
- `AuditLog`
- `SystemLog`

### Available Repositories

- `ServiceRepository`
- `MaterialRepository`
- `CustomerRequestRepository`
- `CostEstimateRepository`
- `SiteSettingRepository`

### Pagination Helper

All repositories support pagination with the following response format:

```php
[
    'data' => [...], // Array of records
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

### Security Features

1. **Prepared Statements**: All queries use parameterized statements
2. **Soft Deletes**: Prevent accidental data loss
3. **Fillable Fields**: Whitelist mass-assignable columns
4. **Hidden Fields**: Hide sensitive data (e.g., passwords)
5. **Type Casting**: Automatic data type conversion

---

## Best Practices

1. **Always use migrations** for schema changes
2. **Never store passwords in plain text** - use `password_hash()`
3. **Use soft deletes** for important data
4. **Index foreign keys** for join performance
5. **Use prepared statements** for all queries
6. **Validate input** before database operations
7. **Use transactions** for multi-table operations
8. **Monitor slow queries** and optimize indexes
9. **Backup regularly** before schema changes
10. **Test migrations** on staging environment first

---

## Troubleshooting

### Migration Errors

**Issue**: Foreign key constraint fails
- **Solution**: Check migration order, parent tables must be created first

**Issue**: Duplicate column name
- **Solution**: Migration may have run partially, check table structure

**Issue**: Connection refused
- **Solution**: Verify database credentials in `.env`

### Performance Issues

**Issue**: Slow queries
- **Solution**: Add indexes to frequently queried columns

**Issue**: Large result sets
- **Solution**: Use pagination instead of fetching all records

**Issue**: N+1 query problem
- **Solution**: Use JOIN queries or eager loading

---

## Support

For questions or issues:
1. Check this documentation
2. Review migration files in `/database/migrations/`
3. Review model/repository files in `/src/Models/` and `/src/Repositories/`
4. Check application logs in `/logs/`

---

**Last Updated**: 2024-01-01
**Schema Version**: 1.0.0
**MySQL Version**: 8.0+
