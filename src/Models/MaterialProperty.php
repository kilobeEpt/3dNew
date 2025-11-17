<?php

declare(strict_types=1);

namespace App\Models;

class MaterialProperty extends BaseModel
{
    protected string $table = 'material_properties';
    protected bool $useSoftDeletes = false;
    
    protected array $fillable = [
        'material_id',
        'property_name',
        'property_value',
        'property_type',
        'display_order',
        'is_searchable',
    ];
}
