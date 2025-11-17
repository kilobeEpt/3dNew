<?php

declare(strict_types=1);

namespace App\Models;

class AdminUser extends BaseModel
{
    protected string $table = 'admin_users';
    protected bool $useSoftDeletes = true;
    
    protected array $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'status',
        'last_login_at',
        'last_login_ip',
    ];
    
    protected array $hidden = [
        'password',
    ];
}
