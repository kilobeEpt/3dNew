<?php

declare(strict_types=1);

namespace App\Models;

class Material extends BaseModel
{
    protected string $table = 'materials';
    protected bool $useSoftDeletes = true;
    
    protected array $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'category',
        'unit',
        'unit_price',
        'stock_quantity',
        'min_order_quantity',
        'supplier',
        'supplier_sku',
        'image',
        'is_active',
    ];
}
