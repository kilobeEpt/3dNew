<?php

declare(strict_types=1);

return [
    'table' => 'admin_users',
    'data' => [
        [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'role' => 'super_admin',
            'status' => 'active',
        ],
        [
            'username' => 'editor',
            'email' => 'editor@example.com',
            'password' => password_hash('editor123', PASSWORD_BCRYPT),
            'first_name' => 'Content',
            'last_name' => 'Editor',
            'role' => 'editor',
            'status' => 'active',
        ],
    ],
];
