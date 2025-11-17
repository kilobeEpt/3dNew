<?php

declare(strict_types=1);

namespace App\Repositories;

class CostEstimateRepository extends BaseRepository
{
    protected string $table = 'cost_estimates';

    public function findByStatus(string $status, array $orderBy = ['created_at' => 'DESC']): array
    {
        return $this->findAll([
            'status' => $status,
        ], $orderBy);
    }

    public function findByCustomerEmail(string $email): array
    {
        return $this->findAll([
            'customer_email' => $email,
        ], ['created_at' => 'DESC']);
    }

    public function generateEstimateNumber(): string
    {
        $prefix = 'EST';
        $date = date('Ymd');
        
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` 
                WHERE estimate_number LIKE ? 
                AND DATE(created_at) = CURDATE()";
        
        $result = $this->database->fetchOne($sql, [$prefix . $date . '%']);
        $count = (int)$result['count'] + 1;
        
        return sprintf('%s%s%04d', $prefix, $date, $count);
    }

    public function getWithItems(int $estimateId): ?array
    {
        $estimate = $this->findById($estimateId);
        
        if (!$estimate) {
            return null;
        }
        
        $itemsSql = "SELECT * FROM cost_estimate_items 
                     WHERE estimate_id = ? 
                     ORDER BY display_order ASC";
        
        $items = $this->database->fetchAll($itemsSql, [$estimateId]);
        
        $estimate['items'] = $items;
        
        return $estimate;
    }

    public function markAsSent(int $estimateId): bool
    {
        return $this->update($estimateId, [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markAsViewed(int $estimateId): bool
    {
        $estimate = $this->findById($estimateId);
        
        if ($estimate && !$estimate['viewed_at']) {
            return $this->update($estimateId, [
                'status' => 'viewed',
                'viewed_at' => date('Y-m-d H:i:s'),
            ]);
        }
        
        return true;
    }

    public function markAsAccepted(int $estimateId): bool
    {
        return $this->update($estimateId, [
            'status' => 'accepted',
            'accepted_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
