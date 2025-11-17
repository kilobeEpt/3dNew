<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Models\CostEstimate;
use App\Models\CostEstimateItem;
use App\Repositories\CostEstimateRepository;
use App\Services\AuditLogger;

class CostEstimatesController
{
    private Container $container;
    private CostEstimate $estimateModel;
    private CostEstimateItem $itemModel;
    private CostEstimateRepository $estimateRepo;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->estimateModel = new CostEstimate($database);
        $this->itemModel = new CostEstimateItem($database);
        $this->estimateRepo = new CostEstimateRepository($database);
        $this->auditLogger = new AuditLogger($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)($request->query('page') ?? 1);
        $perPage = (int)($request->query('per_page') ?? 20);
        $status = $request->query('status');

        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }

        $result = $this->estimateModel->paginate($page, $perPage, $conditions, ['created_at' => 'DESC']);
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $estimate = $this->estimateRepo->getWithItems($id);

        if (!$estimate) {
            ResponseHelper::notFound('Cost estimate not found');
        }

        ResponseHelper::success($estimate);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        $rules = [
            'customer_name' => 'required|min:2',
            'customer_email' => 'required|email',
            'title' => 'required|min:3',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        // Generate estimate number
        $estimateNumber = $this->estimateRepo->generateEstimateNumber();

        // Calculate totals
        $subtotal = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            }
        }

        $taxRate = $data['tax_rate'] ?? 0;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $totalAmount = round($subtotal + $taxAmount, 2);

        $fillableData = [
            'estimate_number' => $estimateNumber,
            'request_id' => $data['request_id'] ?? null,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_company' => $data['customer_company'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => $data['status'] ?? 'draft',
            'valid_until' => $data['valid_until'] ?? date('Y-m-d', strtotime('+30 days')),
            'notes' => $data['notes'] ?? null,
        ];

        $estimateId = $this->estimateModel->create($fillableData);

        // Create items
        if (isset($data['items']) && is_array($data['items'])) {
            $displayOrder = 1;
            foreach ($data['items'] as $item) {
                $lineTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                
                $this->itemModel->create([
                    'estimate_id' => $estimateId,
                    'item_type' => $item['item_type'] ?? 'service',
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'line_total' => $lineTotal,
                    'display_order' => $displayOrder++,
                ]);
            }
        }

        // Audit log
        $this->auditLogger->logCreate(
            (int)$request->adminUser['id'],
            'cost_estimate',
            (int)$estimateId,
            $fillableData,
            $request->ip(),
            $request->userAgent()
        );

        $estimate = $this->estimateRepo->getWithItems((int)$estimateId);
        ResponseHelper::created($estimate, 'Cost estimate created successfully');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $estimate = $this->estimateModel->find($id);

        if (!$estimate) {
            ResponseHelper::notFound('Cost estimate not found');
        }

        $data = $request->input();

        $updateData = [];
        $allowedFields = [
            'customer_name', 'customer_email', 'customer_phone', 'customer_company',
            'title', 'description', 'subtotal', 'tax_rate', 'tax_amount', 'total_amount',
            'status', 'valid_until', 'notes'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        // Recalculate if items provided
        if (isset($data['items']) && is_array($data['items'])) {
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            }

            $taxRate = $data['tax_rate'] ?? $estimate['tax_rate'];
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $totalAmount = round($subtotal + $taxAmount, 2);

            $updateData['subtotal'] = $subtotal;
            $updateData['tax_amount'] = $taxAmount;
            $updateData['total_amount'] = $totalAmount;

            // Delete old items
            $database = $this->container->get('database');
            $database->query("DELETE FROM cost_estimate_items WHERE estimate_id = ?", [$id]);

            // Create new items
            $displayOrder = 1;
            foreach ($data['items'] as $item) {
                $lineTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                
                $this->itemModel->create([
                    'estimate_id' => $id,
                    'item_type' => $item['item_type'] ?? 'service',
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'line_total' => $lineTotal,
                    'display_order' => $displayOrder++,
                ]);
            }
        }

        if (!empty($updateData)) {
            $this->estimateModel->update($id, $updateData);

            // Audit log
            $this->auditLogger->logUpdate(
                (int)$request->adminUser['id'],
                'cost_estimate',
                $id,
                $estimate,
                $updateData,
                $request->ip(),
                $request->userAgent()
            );
        }

        $updatedEstimate = $this->estimateRepo->getWithItems($id);
        ResponseHelper::success($updatedEstimate, 'Cost estimate updated successfully');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $estimate = $this->estimateModel->find($id);

        if (!$estimate) {
            ResponseHelper::notFound('Cost estimate not found');
        }

        $this->estimateModel->delete($id);

        // Audit log
        $this->auditLogger->logDelete(
            (int)$request->adminUser['id'],
            'cost_estimate',
            $id,
            $estimate,
            $request->ip(),
            $request->userAgent()
        );

        ResponseHelper::success(null, 'Cost estimate deleted successfully');
    }

    public function send(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $estimate = $this->estimateModel->find($id);

        if (!$estimate) {
            ResponseHelper::notFound('Cost estimate not found');
        }

        $this->estimateRepo->markAsSent($id);

        // TODO: Send email to customer

        // Audit log
        $this->auditLogger->log(
            (int)$request->adminUser['id'],
            'send',
            'cost_estimate',
            $id,
            ['status' => $estimate['status']],
            ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')],
            $request->ip(),
            $request->userAgent()
        );

        $updatedEstimate = $this->estimateModel->find($id);
        ResponseHelper::success($updatedEstimate, 'Cost estimate sent successfully');
    }
}
