<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Models\CustomerRequest;
use App\Repositories\CustomerRequestRepository;
use App\Services\AuditLogger;

class CustomerRequestsController
{
    private Container $container;
    private CustomerRequest $requestModel;
    private CustomerRequestRepository $requestRepo;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->requestModel = new CustomerRequest($database);
        $this->requestRepo = new CustomerRequestRepository($database);
        $this->auditLogger = new AuditLogger($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)($request->query('page') ?? 1);
        $perPage = (int)($request->query('per_page') ?? 20);
        $status = $request->query('status');
        $priority = $request->query('priority');
        $assignedTo = $request->query('assigned_to');

        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }
        if ($priority) {
            $conditions['priority'] = $priority;
        }
        if ($assignedTo) {
            $conditions['assigned_to'] = (int)$assignedTo;
        }

        $result = $this->requestModel->paginate($page, $perPage, $conditions, ['created_at' => 'DESC']);
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $customerRequest = $this->requestModel->find($id);

        if (!$customerRequest) {
            ResponseHelper::notFound('Customer request not found');
        }

        // Get related service
        $database = $this->container->get('database');
        if ($customerRequest['service_id']) {
            $service = $database->fetchOne(
                "SELECT id, name FROM services WHERE id = ?",
                [$customerRequest['service_id']]
            );
            $customerRequest['service'] = $service;
        }

        // Get assigned admin
        if ($customerRequest['assigned_to']) {
            $admin = $database->fetchOne(
                "SELECT id, username, first_name, last_name FROM admin_users WHERE id = ?",
                [$customerRequest['assigned_to']]
            );
            $customerRequest['assigned_admin'] = $admin;
        }

        // Decode JSON fields
        if ($customerRequest['project_details']) {
            $customerRequest['project_details'] = json_decode($customerRequest['project_details'], true);
        }
        if ($customerRequest['attachments']) {
            $customerRequest['attachments'] = json_decode($customerRequest['attachments'], true);
        }

        ResponseHelper::success($customerRequest);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $customerRequest = $this->requestModel->find($id);

        if (!$customerRequest) {
            ResponseHelper::notFound('Customer request not found');
        }

        $data = $request->input();

        $updateData = [];
        $allowedFields = [
            'status', 'priority', 'assigned_to', 'internal_notes'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $this->requestModel->update($id, $updateData);

            // Audit log
            $this->auditLogger->logUpdate(
                (int)$request->adminUser['id'],
                'customer_request',
                $id,
                $customerRequest,
                $updateData,
                $request->ip(),
                $request->userAgent()
            );
        }

        $updatedRequest = $this->requestModel->find($id);
        ResponseHelper::success($updatedRequest, 'Customer request updated successfully');
    }

    public function assign(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $customerRequest = $this->requestModel->find($id);

        if (!$customerRequest) {
            ResponseHelper::notFound('Customer request not found');
        }

        $data = $request->input();

        $rules = ['assigned_to' => 'required|integer'];
        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        $oldAssignedTo = $customerRequest['assigned_to'];
        $this->requestModel->update($id, ['assigned_to' => (int)$data['assigned_to']]);

        // Audit log
        $this->auditLogger->logUpdate(
            (int)$request->adminUser['id'],
            'customer_request',
            $id,
            ['assigned_to' => $oldAssignedTo],
            ['assigned_to' => (int)$data['assigned_to']],
            $request->ip(),
            $request->userAgent()
        );

        $updatedRequest = $this->requestModel->find($id);
        ResponseHelper::success($updatedRequest, 'Request assigned successfully');
    }

    public function updateStatus(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $customerRequest = $this->requestModel->find($id);

        if (!$customerRequest) {
            ResponseHelper::notFound('Customer request not found');
        }

        $data = $request->input();

        $rules = ['status' => 'required'];
        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        $oldStatus = $customerRequest['status'];
        $this->requestModel->update($id, ['status' => $data['status']]);

        // Audit log
        $this->auditLogger->logUpdate(
            (int)$request->adminUser['id'],
            'customer_request',
            $id,
            ['status' => $oldStatus],
            ['status' => $data['status']],
            $request->ip(),
            $request->userAgent()
        );

        $updatedRequest = $this->requestModel->find($id);
        ResponseHelper::success($updatedRequest, 'Request status updated successfully');
    }

    public function statistics(Request $request, Response $response, array $params): void
    {
        $stats = $this->requestRepo->getStatistics();
        ResponseHelper::success($stats);
    }
}
