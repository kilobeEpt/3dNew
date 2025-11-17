<?php

declare(strict_types=1);

namespace App\Models;

class PasswordResetToken extends BaseModel
{
    protected string $table = 'password_reset_tokens';
    protected bool $useSoftDeletes = false;
    
    protected array $fillable = [
        'admin_user_id',
        'token',
        'expires_at',
        'used_at',
    ];
}
