<?php

declare(strict_types=1);

namespace App\Models;

class SiteSetting extends BaseModel
{
    protected string $table = 'site_settings';
    protected bool $useSoftDeletes = false;
    
    protected array $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'group_name',
        'description',
        'is_public',
        'display_order',
    ];
}
