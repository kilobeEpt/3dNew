<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

use App\Core\Container;

$container = Container::getInstance();
$database = $container->get('database');

$seoSettings = [
    [
        'setting_key' => 'site_name',
        'setting_value' => 'Manufacturing Platform',
        'setting_type' => 'string',
        'group_name' => 'seo',
        'description' => 'Site name for SEO and branding',
        'is_public' => true,
        'display_order' => 1,
    ],
    [
        'setting_key' => 'site_description',
        'setting_value' => 'Modern manufacturing platform providing quality services, materials, and precision craftsmanship',
        'setting_type' => 'text',
        'group_name' => 'seo',
        'description' => 'Default site description for meta tags',
        'is_public' => true,
        'display_order' => 2,
    ],
    [
        'setting_key' => 'site_keywords',
        'setting_value' => 'manufacturing, 3D printing, CNC machining, precision manufacturing, custom fabrication',
        'setting_type' => 'string',
        'group_name' => 'seo',
        'description' => 'Default keywords for SEO',
        'is_public' => true,
        'display_order' => 3,
    ],
    [
        'setting_key' => 'site_url',
        'setting_value' => 'https://example.com',
        'setting_type' => 'string',
        'group_name' => 'seo',
        'description' => 'Base URL for canonical links and schema',
        'is_public' => true,
        'display_order' => 4,
    ],
    [
        'setting_key' => 'site_logo',
        'setting_value' => '/assets/images/logo.png',
        'setting_type' => 'string',
        'group_name' => 'seo',
        'description' => 'Logo URL for OG and schema (min 1200x630px)',
        'is_public' => true,
        'display_order' => 5,
    ],
    [
        'setting_key' => 'contact_email',
        'setting_value' => 'info@example.com',
        'setting_type' => 'string',
        'group_name' => 'contact',
        'description' => 'Business contact email',
        'is_public' => true,
        'display_order' => 10,
    ],
    [
        'setting_key' => 'contact_phone',
        'setting_value' => '+1 (555) 123-4567',
        'setting_type' => 'string',
        'group_name' => 'contact',
        'description' => 'Business contact phone',
        'is_public' => true,
        'display_order' => 11,
    ],
    [
        'setting_key' => 'business_street',
        'setting_value' => '123 Main Street',
        'setting_type' => 'string',
        'group_name' => 'business',
        'description' => 'Street address',
        'is_public' => true,
        'display_order' => 20,
    ],
    [
        'setting_key' => 'business_city',
        'setting_value' => 'San Francisco',
        'setting_type' => 'string',
        'group_name' => 'business',
        'description' => 'City',
        'is_public' => true,
        'display_order' => 21,
    ],
    [
        'setting_key' => 'business_state',
        'setting_value' => 'CA',
        'setting_type' => 'string',
        'group_name' => 'business',
        'description' => 'State/Region',
        'is_public' => true,
        'display_order' => 22,
    ],
    [
        'setting_key' => 'business_zip',
        'setting_value' => '94102',
        'setting_type' => 'string',
        'group_name' => 'business',
        'description' => 'ZIP/Postal Code',
        'is_public' => true,
        'display_order' => 23,
    ],
    [
        'setting_key' => 'business_country',
        'setting_value' => 'US',
        'setting_type' => 'string',
        'group_name' => 'business',
        'description' => 'Country Code',
        'is_public' => true,
        'display_order' => 24,
    ],
    [
        'setting_key' => 'business_latitude',
        'setting_value' => '37.7749',
        'setting_type' => 'string',
        'group_name' => 'business',
        'description' => 'Business location latitude',
        'is_public' => true,
        'display_order' => 25,
    ],
    [
        'setting_key' => 'business_longitude',
        'setting_value' => '-122.4194',
        'setting_type' => 'string',
        'group_name' => 'business',
        'description' => 'Business location longitude',
        'is_public' => true,
        'display_order' => 26,
    ],
    [
        'setting_key' => 'business_hours',
        'setting_value' => 'Mo-Fr 09:00-17:00',
        'setting_type' => 'string',
        'group_name' => 'business',
        'description' => 'Business operating hours (Schema.org format)',
        'is_public' => true,
        'display_order' => 27,
    ],
    [
        'setting_key' => 'business_price_range',
        'setting_value' => '$$',
        'setting_type' => 'string',
        'group_name' => 'business',
        'description' => 'Price range indicator ($, $$, $$$, $$$$)',
        'is_public' => true,
        'display_order' => 28,
    ],
    [
        'setting_key' => 'social_facebook',
        'setting_value' => '',
        'setting_type' => 'string',
        'group_name' => 'social',
        'description' => 'Facebook profile URL',
        'is_public' => true,
        'display_order' => 30,
    ],
    [
        'setting_key' => 'social_twitter',
        'setting_value' => '',
        'setting_type' => 'string',
        'group_name' => 'social',
        'description' => 'Twitter profile URL',
        'is_public' => true,
        'display_order' => 31,
    ],
    [
        'setting_key' => 'social_linkedin',
        'setting_value' => '',
        'setting_type' => 'string',
        'group_name' => 'social',
        'description' => 'LinkedIn profile URL',
        'is_public' => true,
        'display_order' => 32,
    ],
    [
        'setting_key' => 'social_instagram',
        'setting_value' => '',
        'setting_type' => 'string',
        'group_name' => 'social',
        'description' => 'Instagram profile URL',
        'is_public' => true,
        'display_order' => 33,
    ],
    [
        'setting_key' => 'twitter_handle',
        'setting_value' => '',
        'setting_type' => 'string',
        'group_name' => 'social',
        'description' => 'Twitter handle (without @)',
        'is_public' => true,
        'display_order' => 34,
    ],
];

try {
    echo "Seeding SEO settings...\n";
    
    foreach ($seoSettings as $setting) {
        $existing = $database->query(
            "SELECT id FROM site_settings WHERE setting_key = :key",
            ['key' => $setting['setting_key']]
        );
        
        if (empty($existing)) {
            $database->query(
                "INSERT INTO site_settings (setting_key, setting_value, setting_type, group_name, description, is_public, display_order) 
                 VALUES (:key, :value, :type, :group, :description, :is_public, :display_order)",
                [
                    'key' => $setting['setting_key'],
                    'value' => $setting['setting_value'],
                    'type' => $setting['setting_type'],
                    'group' => $setting['group_name'],
                    'description' => $setting['description'],
                    'is_public' => $setting['is_public'],
                    'display_order' => $setting['display_order'],
                ]
            );
            echo "  ✓ Added: {$setting['setting_key']}\n";
        } else {
            echo "  - Skipped (exists): {$setting['setting_key']}\n";
        }
    }
    
    echo "\nSEO settings seeded successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Update these settings in the admin panel or database\n";
    echo "2. Set SITE_URL in your .env file\n";
    echo "3. Upload a logo image (min 1200x630px)\n";
    echo "4. Configure social media URLs\n";
    echo "5. Test sitemap at /sitemap.xml\n";
    echo "6. Test robots.txt at /robots.txt\n";
    
} catch (Exception $e) {
    echo "Error seeding SEO settings: " . $e->getMessage() . "\n";
    exit(1);
}
