<?php

declare(strict_types=1);

return [
    'table' => 'service_categories',
    'data' => [
        [
            'name' => 'Manufacturing',
            'slug' => 'manufacturing',
            'description' => 'Custom manufacturing and fabrication services',
            'icon' => 'fa-industry',
            'display_order' => 1,
            'is_visible' => true,
        ],
        [
            'name' => 'Metal Fabrication',
            'slug' => 'metal-fabrication',
            'description' => 'Precision metal cutting, bending, and welding',
            'icon' => 'fa-tools',
            'display_order' => 2,
            'is_visible' => true,
        ],
        [
            'name' => 'CNC Machining',
            'slug' => 'cnc-machining',
            'description' => 'Computer numerical control machining services',
            'icon' => 'fa-cogs',
            'display_order' => 3,
            'is_visible' => true,
        ],
        [
            'name' => '3D Printing',
            'slug' => '3d-printing',
            'description' => 'Rapid prototyping and additive manufacturing',
            'icon' => 'fa-cube',
            'display_order' => 4,
            'is_visible' => true,
        ],
        [
            'name' => 'Assembly',
            'slug' => 'assembly',
            'description' => 'Product assembly and quality control',
            'icon' => 'fa-puzzle-piece',
            'display_order' => 5,
            'is_visible' => true,
        ],
    ],
];
