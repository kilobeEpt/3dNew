<?php

declare(strict_types=1);

namespace App\Models;

class CustomerRequest extends BaseModel
{
    protected string $table = 'customer_requests';
    protected bool $useSoftDeletes = true;
    
    protected array $fillable = [
        'request_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'service_id',
        'subject',
        'message',
        'request_type',
        'priority',
        'status',
        'assigned_to',
        'estimated_budget',
        'notes',
        'metadata',
        'ip_address',
        'user_agent',
    ];
}
