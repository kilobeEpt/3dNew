<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Models\PricingRule;
use App\Services\AuditLogger;

class PricingRulesController
{
    private Container $container;
    private PricingRule $pricingRuleModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->pricingRuleModel = new PricingRule($database);
        $this->auditLogger = new AuditLogger($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)($request->query('page') ?? 1);
        $perPage = (int)($request->query('per_page') ?? 20);
        
        $result = $this->pricingRuleModel->paginate($page, $perPage);
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $rule = $this->pricingRuleModel->find($id);

        if (!$rule) {
            ResponseHelper::notFound('Pricing rule not found');
        }

        ResponseHelper::success($rule);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        $rules = [
            'name' => 'required|min:3|max:200',
            'rule_type' => 'required',
            'applies_to' => 'required',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        $fillableData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'rule_type' => $data['rule_type'],
            'applies_to' => $data['applies_to'],
            'applies_to_id' => $data['applies_to_id'] ?? null,
            'condition_field' => $data['condition_field'] ?? null,
            'condition_operator' => $data['condition_operator'] ?? null,
            'condition_value' => $data['condition_value'] ?? null,
            'price_modifier' => $data['price_modifier'] ?? null,
            'modifier_type' => $data['modifier_type'] ?? 'fixed',
            'priority' => $data['priority'] ?? 0,
            'is_active' => (bool)($data['is_active'] ?? true),
        ];

        $id = $this->pricingRuleModel->create($fillableData);

        // Audit log
        $this->auditLogger->logCreate(
            (int)$request->adminUser['id'],
            'pricing_rule',
            (int)$id,
            $fillableData,
            $request->ip(),
            $request->userAgent()
        );

        $rule = $this->pricingRuleModel->find($id);
        ResponseHelper::created($rule, 'Pricing rule created successfully');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $rule = $this->pricingRuleModel->find($id);

        if (!$rule) {
            ResponseHelper::notFound('Pricing rule not found');
        }

        $data = $request->input();

        $updateData = [];
        $allowedFields = [
            'name', 'description', 'rule_type', 'applies_to', 'applies_to_id',
            'condition_field', 'condition_operator', 'condition_value',
            'price_modifier', 'modifier_type', 'priority', 'is_active'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $this->pricingRuleModel->update($id, $updateData);

            // Audit log
            $this->auditLogger->logUpdate(
                (int)$request->adminUser['id'],
                'pricing_rule',
                $id,
                $rule,
                $updateData,
                $request->ip(),
                $request->userAgent()
            );
        }

        $updatedRule = $this->pricingRuleModel->find($id);
        ResponseHelper::success($updatedRule, 'Pricing rule updated successfully');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $rule = $this->pricingRuleModel->find($id);

        if (!$rule) {
            ResponseHelper::notFound('Pricing rule not found');
        }

        $this->pricingRuleModel->delete($id);

        // Audit log
        $this->auditLogger->logDelete(
            (int)$request->adminUser['id'],
            'pricing_rule',
            $id,
            $rule,
            $request->ip(),
            $request->userAgent()
        );

        ResponseHelper::success(null, 'Pricing rule deleted successfully');
    }
}
