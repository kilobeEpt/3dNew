<?php

declare(strict_types=1);

namespace App\Repositories;

class MaterialRepository extends BaseRepository
{
    protected string $table = 'materials';

    public function findActive(array $orderBy = ['name' => 'ASC']): array
    {
        return $this->findAll([
            'is_active' => true,
        ], $orderBy);
    }

    public function findByCategory(string $category): array
    {
        return $this->findAll([
            'category' => $category,
            'is_active' => true,
        ], ['name' => 'ASC']);
    }

    public function findBySku(string $sku)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE sku = ? AND deleted_at IS NULL LIMIT 1";
        return $this->database->fetchOne($sql, [$sku]);
    }

    public function findLowStock(float $threshold = 10.0): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE stock_quantity <= ? 
                AND is_active = 1 
                AND deleted_at IS NULL 
                ORDER BY stock_quantity ASC";
        
        return $this->database->fetchAll($sql, [$threshold]);
    }

    public function getCategories(): array
    {
        $sql = "SELECT DISTINCT category FROM `{$this->table}` 
                WHERE category IS NOT NULL 
                AND deleted_at IS NULL 
                ORDER BY category ASC";
        
        return $this->database->fetchAll($sql);
    }
}
