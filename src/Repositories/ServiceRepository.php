<?php

declare(strict_types=1);

namespace App\Repositories;

class ServiceRepository extends BaseRepository
{
    protected string $table = 'services';

    public function findBySlug(string $slug)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE slug = ? AND deleted_at IS NULL LIMIT 1";
        return $this->database->fetchOne($sql, [$slug]);
    }

    public function findVisible(array $orderBy = ['display_order' => 'ASC']): array
    {
        return $this->findAll([
            'is_visible' => true,
        ], $orderBy);
    }

    public function findFeatured(int $limit = 6): array
    {
        return $this->findAll([
            'is_visible' => true,
            'is_featured' => true,
        ], ['display_order' => 'ASC'], $limit);
    }

    public function findByCategory(int $categoryId, bool $visibleOnly = true): array
    {
        $conditions = ['category_id' => $categoryId];
        
        if ($visibleOnly) {
            $conditions['is_visible'] = true;
        }
        
        return $this->findAll($conditions, ['display_order' => 'ASC']);
    }
}
