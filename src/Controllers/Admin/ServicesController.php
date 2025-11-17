<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Models\Service;
use App\Services\AuditLogger;

class ServicesController
{
    private Container $container;
    private Service $serviceModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->serviceModel = new Service($database);
        $this->auditLogger = new AuditLogger($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)($request->query('page') ?? 1);
        $perPage = (int)($request->query('per_page') ?? 20);
        $search = $request->query('search');

        if ($search) {
            $sql = "SELECT * FROM services WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC";
            $database = $this->container->get('database');
            $services = $database->fetchAll($sql, ["%{$search}%", "%{$search}%"]);
            ResponseHelper::success(['data' => $services]);
            return;
        }

        $result = $this->serviceModel->paginate($page, $perPage);
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $service = $this->serviceModel->find($id);

        if (!$service) {
            ResponseHelper::notFound('Service not found');
        }

        ResponseHelper::success($service);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        $rules = [
            'category_id' => 'required|integer',
            'name' => 'required|min:3|max:200',
            'slug' => 'required|min:3|max:200',
            'description' => 'required',
            'price_type' => 'required',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        // Check if slug exists
        $existing = $this->serviceModel->first(['slug' => $data['slug']]);
        if ($existing) {
            ResponseHelper::error('A service with this slug already exists', null, 409);
        }

        $fillableData = [
            'category_id' => (int)$data['category_id'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'short_description' => $data['short_description'] ?? null,
            'price_type' => $data['price_type'],
            'base_price' => $data['base_price'] ?? null,
            'pricing_unit' => $data['pricing_unit'] ?? null,
            'lead_time_days' => $data['lead_time_days'] ?? null,
            'minimum_quantity' => $data['minimum_quantity'] ?? null,
            'is_featured' => (bool)($data['is_featured'] ?? false),
            'is_visible' => (bool)($data['is_visible'] ?? true),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ];

        $id = $this->serviceModel->create($fillableData);

        // Audit log
        $this->auditLogger->logCreate(
            (int)$request->adminUser['id'],
            'service',
            (int)$id,
            $fillableData,
            $request->ip(),
            $request->userAgent()
        );

        $service = $this->serviceModel->find($id);
        ResponseHelper::created($service, 'Service created successfully');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $service = $this->serviceModel->find($id);

        if (!$service) {
            ResponseHelper::notFound('Service not found');
        }

        $data = $request->input();

        $rules = [
            'name' => 'min:3|max:200',
            'slug' => 'min:3|max:200',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        // Check slug uniqueness if changed
        if (isset($data['slug']) && $data['slug'] !== $service['slug']) {
            $existing = $this->serviceModel->first(['slug' => $data['slug']]);
            if ($existing) {
                ResponseHelper::error('A service with this slug already exists', null, 409);
            }
        }

        $updateData = [];
        $allowedFields = [
            'category_id', 'name', 'slug', 'description', 'short_description',
            'price_type', 'base_price', 'pricing_unit', 'lead_time_days',
            'minimum_quantity', 'is_featured', 'is_visible', 'meta_title',
            'meta_description', 'sort_order'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $this->serviceModel->update($id, $updateData);

            // Audit log
            $this->auditLogger->logUpdate(
                (int)$request->adminUser['id'],
                'service',
                $id,
                $service,
                $updateData,
                $request->ip(),
                $request->userAgent()
            );
        }

        $updatedService = $this->serviceModel->find($id);
        ResponseHelper::success($updatedService, 'Service updated successfully');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $service = $this->serviceModel->find($id);

        if (!$service) {
            ResponseHelper::notFound('Service not found');
        }

        $this->serviceModel->delete($id);

        // Audit log
        $this->auditLogger->logDelete(
            (int)$request->adminUser['id'],
            'service',
            $id,
            $service,
            $request->ip(),
            $request->userAgent()
        );

        ResponseHelper::success(null, 'Service deleted successfully');
    }
}
