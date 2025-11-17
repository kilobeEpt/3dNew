<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\AuditLog;

class AuditLogger
{
    private Database $database;
    private AuditLog $auditLogModel;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->auditLogModel = new AuditLog($database);
    }

    public function log(
        int $userId,
        string $eventType,
        string $auditableType,
        ?int $auditableId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        $this->auditLogModel->create([
            'user_id' => $userId,
            'user_type' => 'admin',
            'event_type' => $eventType,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function logCreate(
        int $userId,
        string $resourceType,
        int $resourceId,
        array $data,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        $this->log(
            $userId,
            'create',
            $resourceType,
            $resourceId,
            null,
            $data,
            $ipAddress,
            $userAgent
        );
    }

    public function logUpdate(
        int $userId,
        string $resourceType,
        int $resourceId,
        array $oldData,
        array $newData,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        $this->log(
            $userId,
            'update',
            $resourceType,
            $resourceId,
            $oldData,
            $newData,
            $ipAddress,
            $userAgent
        );
    }

    public function logDelete(
        int $userId,
        string $resourceType,
        int $resourceId,
        array $data,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        $this->log(
            $userId,
            'delete',
            $resourceType,
            $resourceId,
            $data,
            null,
            $ipAddress,
            $userAgent
        );
    }

    public function logLogin(
        int $userId,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        $this->log(
            $userId,
            'login',
            'admin_user',
            $userId,
            null,
            ['login_at' => date('Y-m-d H:i:s')],
            $ipAddress,
            $userAgent
        );
    }

    public function logLogout(
        int $userId,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        $this->log(
            $userId,
            'logout',
            'admin_user',
            $userId,
            null,
            ['logout_at' => date('Y-m-d H:i:s')],
            $ipAddress,
            $userAgent
        );
    }
}
