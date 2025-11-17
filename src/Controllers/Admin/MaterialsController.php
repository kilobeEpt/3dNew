<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Models\Material;
use App\Services\AuditLogger;

class MaterialsController
{
    private Container $container;
    private Material $materialModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->materialModel = new Material($database);
        $this->auditLogger = new AuditLogger($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)($request->query('page') ?? 1);
        $perPage = (int)($request->query('per_page') ?? 20);
        $search = $request->query('search');
        $category = $request->query('category');

        $conditions = [];
        if ($category) {
            $conditions['category'] = $category;
        }

        if ($search) {
            $sql = "SELECT * FROM materials WHERE (name LIKE ? OR sku LIKE ? OR description LIKE ?)";
            if ($category) {
                $sql .= " AND category = ?";
            }
            $sql .= " ORDER BY created_at DESC";
            
            $database = $this->container->get('database');
            $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
            if ($category) {
                $params[] = $category;
            }
            $materials = $database->fetchAll($sql, $params);
            ResponseHelper::success(['data' => $materials]);
            return;
        }

        $result = $this->materialModel->paginate($page, $perPage, $conditions);
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $material = $this->materialModel->find($id);

        if (!$material) {
            ResponseHelper::notFound('Material not found');
        }

        ResponseHelper::success($material);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        $rules = [
            'name' => 'required|min:3|max:200',
            'sku' => 'required|max:100',
            'category' => 'required|max:100',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        // Check if SKU exists
        $existing = $this->materialModel->first(['sku' => $data['sku']]);
        if ($existing) {
            ResponseHelper::error('A material with this SKU already exists', null, 409);
        }

        $fillableData = [
            'name' => $data['name'],
            'sku' => $data['sku'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'unit' => $data['unit'] ?? null,
            'unit_price' => $data['unit_price'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? null,
            'reorder_level' => $data['reorder_level'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? true),
        ];

        $id = $this->materialModel->create($fillableData);

        // Audit log
        $this->auditLogger->logCreate(
            (int)$request->adminUser['id'],
            'material',
            (int)$id,
            $fillableData,
            $request->ip(),
            $request->userAgent()
        );

        $material = $this->materialModel->find($id);
        ResponseHelper::created($material, 'Material created successfully');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $material = $this->materialModel->find($id);

        if (!$material) {
            ResponseHelper::notFound('Material not found');
        }

        $data = $request->input();

        $rules = [
            'name' => 'min:3|max:200',
            'sku' => 'max:100',
            'category' => 'max:100',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        // Check SKU uniqueness if changed
        if (isset($data['sku']) && $data['sku'] !== $material['sku']) {
            $existing = $this->materialModel->first(['sku' => $data['sku']]);
            if ($existing) {
                ResponseHelper::error('A material with this SKU already exists', null, 409);
            }
        }

        $updateData = [];
        $allowedFields = [
            'name', 'sku', 'category', 'description', 'unit', 'unit_price',
            'stock_quantity', 'reorder_level', 'is_active'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $this->materialModel->update($id, $updateData);

            // Audit log
            $this->auditLogger->logUpdate(
                (int)$request->adminUser['id'],
                'material',
                $id,
                $material,
                $updateData,
                $request->ip(),
                $request->userAgent()
            );
        }

        $updatedMaterial = $this->materialModel->find($id);
        ResponseHelper::success($updatedMaterial, 'Material updated successfully');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $material = $this->materialModel->find($id);

        if (!$material) {
            ResponseHelper::notFound('Material not found');
        }

        $this->materialModel->delete($id);

        // Audit log
        $this->auditLogger->logDelete(
            (int)$request->adminUser['id'],
            'material',
            $id,
            $material,
            $request->ip(),
            $request->userAgent()
        );

        ResponseHelper::success(null, 'Material deleted successfully');
    }
}
