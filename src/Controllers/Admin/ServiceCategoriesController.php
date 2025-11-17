<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Models\ServiceCategory;
use App\Services\AuditLogger;

class ServiceCategoriesController
{
    private Container $container;
    private ServiceCategory $categoryModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->categoryModel = new ServiceCategory($database);
        $this->auditLogger = new AuditLogger($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $categories = $this->categoryModel->all();
        ResponseHelper::success($categories);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $category = $this->categoryModel->find($id);

        if (!$category) {
            ResponseHelper::notFound('Service category not found');
        }

        ResponseHelper::success($category);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        $rules = [
            'name' => 'required|min:3|max:200',
            'slug' => 'required|min:3|max:200',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        // Check if slug exists
        $existing = $this->categoryModel->first(['slug' => $data['slug']]);
        if ($existing) {
            ResponseHelper::error('A category with this slug already exists', null, 409);
        }

        $fillableData = [
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ];

        $id = $this->categoryModel->create($fillableData);

        // Audit log
        $this->auditLogger->logCreate(
            (int)$request->adminUser['id'],
            'service_category',
            (int)$id,
            $fillableData,
            $request->ip(),
            $request->userAgent()
        );

        $category = $this->categoryModel->find($id);
        ResponseHelper::created($category, 'Service category created successfully');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $category = $this->categoryModel->find($id);

        if (!$category) {
            ResponseHelper::notFound('Service category not found');
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
        if (isset($data['slug']) && $data['slug'] !== $category['slug']) {
            $existing = $this->categoryModel->first(['slug' => $data['slug']]);
            if ($existing) {
                ResponseHelper::error('A category with this slug already exists', null, 409);
            }
        }

        $updateData = [];
        $allowedFields = ['parent_id', 'name', 'slug', 'description', 'icon', 'sort_order'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $this->categoryModel->update($id, $updateData);

            // Audit log
            $this->auditLogger->logUpdate(
                (int)$request->adminUser['id'],
                'service_category',
                $id,
                $category,
                $updateData,
                $request->ip(),
                $request->userAgent()
            );
        }

        $updatedCategory = $this->categoryModel->find($id);
        ResponseHelper::success($updatedCategory, 'Service category updated successfully');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $category = $this->categoryModel->find($id);

        if (!$category) {
            ResponseHelper::notFound('Service category not found');
        }

        // Check if category has services
        $database = $this->container->get('database');
        $serviceCount = $database->fetchOne(
            "SELECT COUNT(*) as count FROM services WHERE category_id = ? AND deleted_at IS NULL",
            [$id]
        )['count'];

        if ($serviceCount > 0) {
            ResponseHelper::error('Cannot delete category with associated services', null, 409);
        }

        $this->categoryModel->delete($id);

        // Audit log
        $this->auditLogger->logDelete(
            (int)$request->adminUser['id'],
            'service_category',
            $id,
            $category,
            $request->ip(),
            $request->userAgent()
        );

        ResponseHelper::success(null, 'Service category deleted successfully');
    }
}
