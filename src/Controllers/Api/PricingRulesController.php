<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Models\PricingRule;

class PricingRulesController
{
    private PricingRule $model;

    public function __construct()
    {
        $container = Container::getInstance();
        $database = $container->get('database');
        $this->model = new PricingRule($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)$request->query('page', 1);
        $perPage = min((int)$request->query('per_page', 20), 100);
        $ruleType = $request->query('type');

        $conditions = ['is_active' => true];
        
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM pricing_rules 
                WHERE is_active = 1 
                AND deleted_at IS NULL
                AND (valid_from IS NULL OR valid_from <= ?)
                AND (valid_to IS NULL OR valid_to >= ?)";
        
        $params = [$now, $now];

        if ($ruleType) {
            $sql .= " AND rule_type = ?";
            $params[] = $ruleType;
        }

        $sql .= " ORDER BY priority DESC, id ASC";
        
        $container = Container::getInstance();
        $database = $container->get('database');
        $rules = $database->fetchAll($sql, $params);

        $this->setCacheHeaders();
        ResponseHelper::success([
            'data' => $rules,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => count($rules),
                'last_page' => 1,
            ]
        ]);
    }

    private function setCacheHeaders(): void
    {
        header('Cache-Control: public, max-age=300');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 300) . ' GMT');
    }
}
