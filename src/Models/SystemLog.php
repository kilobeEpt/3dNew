<?php

declare(strict_types=1);

namespace App\Models;

class SystemLog extends BaseModel
{
    protected string $table = 'system_logs';
    protected bool $useSoftDeletes = false;
    
    protected array $fillable = [
        'level',
        'message',
        'context',
        'channel',
        'exception_class',
        'exception_message',
        'exception_trace',
        'file',
        'line',
        'user_id',
        'ip_address',
        'url',
        'method',
    ];
}
