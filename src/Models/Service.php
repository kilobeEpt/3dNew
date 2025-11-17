<?php

declare(strict_types=1);

namespace App\Models;

class Service extends BaseModel
{
    protected string $table = 'services';
    protected bool $useSoftDeletes = true;
    
    protected array $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'image',
        'price_type',
        'base_price',
        'unit',
        'display_order',
        'is_visible',
        'is_featured',
        'meta_title',
        'meta_description',
    ];
}
