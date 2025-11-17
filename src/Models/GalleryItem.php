<?php

declare(strict_types=1);

namespace App\Models;

class GalleryItem extends BaseModel
{
    protected string $table = 'gallery_items';
    protected bool $useSoftDeletes = true;
    
    protected array $fillable = [
        'title',
        'slug',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'width',
        'height',
        'thumbnail_path',
        'category',
        'tags',
        'service_id',
        'display_order',
        'is_visible',
        'is_featured',
        'view_count',
        'uploaded_by',
    ];
}
