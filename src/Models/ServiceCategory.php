<?php

declare(strict_types=1);

namespace App\Models;

class ServiceCategory extends BaseModel
{
    protected string $table = 'service_categories';
    protected bool $useSoftDeletes = true;
    
    protected array $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'display_order',
        'is_visible',
        'meta_title',
        'meta_description',
    ];
}
