<?php

declare(strict_types=1);

namespace App\Repositories;

class CustomerRequestRepository extends BaseRepository
{
    protected string $table = 'customer_requests';

    public function findByStatus(string $status, array $orderBy = ['created_at' => 'DESC']): array
    {
        return $this->findAll([
            'status' => $status,
        ], $orderBy);
    }

    public function findByPriority(string $priority): array
    {
        return $this->findAll([
            'priority' => $priority,
        ], ['created_at' => 'DESC']);
    }

    public function findAssignedTo(int $userId): array
    {
        return $this->findAll([
            'assigned_to' => $userId,
        ], ['priority' => 'DESC', 'created_at' => 'DESC']);
    }

    public function findUnassigned(): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE assigned_to IS NULL 
                AND deleted_at IS NULL 
                ORDER BY priority DESC, created_at DESC";
        
        return $this->database->fetchAll($sql);
    }

    public function generateRequestNumber(): string
    {
        $prefix = 'REQ';
        $date = date('Ymd');
        
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}` 
                WHERE request_number LIKE ? 
                AND DATE(created_at) = CURDATE()";
        
        $result = $this->database->fetchOne($sql, [$prefix . $date . '%']);
        $count = (int)$result['count'] + 1;
        
        return sprintf('%s%s%04d', $prefix, $date, $count);
    }

    public function getStatistics(): array
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM `{$this->table}` 
                WHERE deleted_at IS NULL 
                GROUP BY status";
        
        $results = $this->database->fetchAll($sql);
        
        $stats = [
            'total' => 0,
            'by_status' => [],
        ];
        
        foreach ($results as $row) {
            $stats['by_status'][$row['status']] = (int)$row['count'];
            $stats['total'] += (int)$row['count'];
        }
        
        return $stats;
    }
}
