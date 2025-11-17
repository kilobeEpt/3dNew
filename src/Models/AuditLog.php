<?php

declare(strict_types=1);

namespace App\Models;

class AuditLog extends BaseModel
{
    protected string $table = 'audit_logs';
    protected bool $useSoftDeletes = false;
    
    protected array $fillable = [
        'user_id',
        'user_type',
        'event_type',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];
}
