<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Models\AuditLog;

class AuditLogsController
{
    private Container $container;
    private AuditLog $auditLogModel;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->auditLogModel = new AuditLog($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)($request->query('page') ?? 1);
        $perPage = (int)($request->query('per_page') ?? 50);
        $eventType = $request->query('event_type');
        $auditableType = $request->query('auditable_type');
        $userId = $request->query('user_id');

        $conditions = [];
        if ($eventType) {
            $conditions['event_type'] = $eventType;
        }
        if ($auditableType) {
            $conditions['auditable_type'] = $auditableType;
        }
        if ($userId) {
            $conditions['user_id'] = (int)$userId;
        }

        $result = $this->auditLogModel->paginate($page, $perPage, $conditions, ['created_at' => 'DESC']);
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $log = $this->auditLogModel->find($id);

        if (!$log) {
            ResponseHelper::notFound('Audit log not found');
        }

        // Decode JSON fields
        if ($log['old_values']) {
            $log['old_values'] = json_decode($log['old_values'], true);
        }
        if ($log['new_values']) {
            $log['new_values'] = json_decode($log['new_values'], true);
        }

        ResponseHelper::success($log);
    }

    public function byResource(Request $request, Response $response, array $params): void
    {
        $auditableType = $params['type'];
        $auditableId = (int)$params['id'];

        $database = $this->container->get('database');
        $logs = $database->fetchAll("
            SELECT 
                al.*,
                CONCAT(au.first_name, ' ', au.last_name) as admin_name,
                au.username as admin_username
            FROM audit_logs al
            LEFT JOIN admin_users au ON al.user_id = au.id
            WHERE al.auditable_type = ?
            AND al.auditable_id = ?
            ORDER BY al.created_at DESC
        ", [$auditableType, $auditableId]);

        // Decode JSON fields
        foreach ($logs as &$log) {
            if ($log['old_values']) {
                $log['old_values'] = json_decode($log['old_values'], true);
            }
            if ($log['new_values']) {
                $log['new_values'] = json_decode($log['new_values'], true);
            }
        }

        ResponseHelper::success($logs);
    }

    public function eventTypes(Request $request, Response $response, array $params): void
    {
        $database = $this->container->get('database');
        $types = $database->fetchAll("
            SELECT DISTINCT event_type 
            FROM audit_logs 
            ORDER BY event_type
        ");

        ResponseHelper::success(array_column($types, 'event_type'));
    }

    public function auditableTypes(Request $request, Response $response, array $params): void
    {
        $database = $this->container->get('database');
        $types = $database->fetchAll("
            SELECT DISTINCT auditable_type 
            FROM audit_logs 
            ORDER BY auditable_type
        ");

        ResponseHelper::success(array_column($types, 'auditable_type'));
    }
}
