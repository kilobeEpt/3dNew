<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class AuditLogRepository extends BaseRepository
{
    protected string $table = 'audit_logs';

    public function __construct(Database $database)
    {
        parent::__construct($database);
    }

    public function findByUser(int $userId, int $limit = 50): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->database->fetchAll($sql, [$userId, $limit]);
    }

    public function findByEventType(string $eventType, int $limit = 50): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE event_type = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->database->fetchAll($sql, [$eventType, $limit]);
    }

    public function findByResource(string $auditableType, int $auditableId): array
    {
        $sql = "SELECT al.*, 
                       CONCAT(au.first_name, ' ', au.last_name) as admin_name,
                       au.username as admin_username
                FROM `{$this->table}` al
                LEFT JOIN admin_users au ON al.user_id = au.id
                WHERE al.auditable_type = ? 
                AND al.auditable_id = ?
                ORDER BY al.created_at DESC";
        
        return $this->database->fetchAll($sql, [$auditableType, $auditableId]);
    }

    public function getRecentActivity(int $limit = 20): array
    {
        $sql = "SELECT al.*,
                       CONCAT(au.first_name, ' ', au.last_name) as admin_name,
                       au.username as admin_username
                FROM `{$this->table}` al
                LEFT JOIN admin_users au ON al.user_id = au.id
                WHERE al.user_type = 'admin'
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        return $this->database->fetchAll($sql, [$limit]);
    }

    public function getEventTypes(): array
    {
        $sql = "SELECT DISTINCT event_type FROM `{$this->table}` ORDER BY event_type";
        $results = $this->database->fetchAll($sql);
        return array_column($results, 'event_type');
    }

    public function getAuditableTypes(): array
    {
        $sql = "SELECT DISTINCT auditable_type FROM `{$this->table}` ORDER BY auditable_type";
        $results = $this->database->fetchAll($sql);
        return array_column($results, 'auditable_type');
    }

    public function getStatsByUser(int $days = 30): array
    {
        $sql = "SELECT 
                    u.id,
                    u.username,
                    CONCAT(u.first_name, ' ', u.last_name) as full_name,
                    COUNT(al.id) as total_actions,
                    COUNT(CASE WHEN al.event_type = 'create' THEN 1 END) as creates,
                    COUNT(CASE WHEN al.event_type = 'update' THEN 1 END) as updates,
                    COUNT(CASE WHEN al.event_type = 'delete' THEN 1 END) as deletes
                FROM admin_users u
                LEFT JOIN audit_logs al ON u.id = al.user_id 
                    AND al.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                WHERE u.deleted_at IS NULL
                GROUP BY u.id, u.username, u.first_name, u.last_name
                ORDER BY total_actions DESC";
        
        return $this->database->fetchAll($sql, [$days]);
    }

    public function getStatsByResource(int $days = 30): array
    {
        $sql = "SELECT 
                    auditable_type,
                    COUNT(*) as total_actions,
                    COUNT(CASE WHEN event_type = 'create' THEN 1 END) as creates,
                    COUNT(CASE WHEN event_type = 'update' THEN 1 END) as updates,
                    COUNT(CASE WHEN event_type = 'delete' THEN 1 END) as deletes
                FROM audit_logs
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY auditable_type
                ORDER BY total_actions DESC";
        
        return $this->database->fetchAll($sql, [$days]);
    }
}
