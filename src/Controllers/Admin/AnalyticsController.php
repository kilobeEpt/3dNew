<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;

class AnalyticsController
{
    private Container $container;

    public function __construct()
    {
        $this->container = Container::getInstance();
    }

    public function dashboard(Request $request, Response $response, array $params): void
    {
        $database = $this->container->get('database');

        // Total counts
        $totalRequests = $database->fetchOne("SELECT COUNT(*) as count FROM customer_requests WHERE deleted_at IS NULL")['count'];
        $totalEstimates = $database->fetchOne("SELECT COUNT(*) as count FROM cost_estimates WHERE deleted_at IS NULL")['count'];
        $totalServices = $database->fetchOne("SELECT COUNT(*) as count FROM services WHERE deleted_at IS NULL")['count'];
        $totalMaterials = $database->fetchOne("SELECT COUNT(*) as count FROM materials WHERE deleted_at IS NULL")['count'];

        // Requests by status
        $requestsByStatus = $database->fetchAll("
            SELECT status, COUNT(*) as count 
            FROM customer_requests 
            WHERE deleted_at IS NULL 
            GROUP BY status
        ");

        // Estimates by status
        $estimatesByStatus = $database->fetchAll("
            SELECT status, COUNT(*) as count 
            FROM cost_estimates 
            WHERE deleted_at IS NULL 
            GROUP BY status
        ");

        // Recent requests (last 30 days)
        $recentRequestsCount = $database->fetchOne("
            SELECT COUNT(*) as count 
            FROM customer_requests 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND deleted_at IS NULL
        ")['count'];

        // Conversion rate (requests to estimates)
        $conversionRate = $totalRequests > 0 ? round(($totalEstimates / $totalRequests) * 100, 2) : 0;

        // Acceptance rate (accepted estimates)
        $acceptedEstimates = $database->fetchOne("
            SELECT COUNT(*) as count 
            FROM cost_estimates 
            WHERE status = 'accepted'
            AND deleted_at IS NULL
        ")['count'];
        $acceptanceRate = $totalEstimates > 0 ? round(($acceptedEstimates / $totalEstimates) * 100, 2) : 0;

        // Total revenue (accepted estimates)
        $totalRevenue = $database->fetchOne("
            SELECT COALESCE(SUM(total_amount), 0) as total 
            FROM cost_estimates 
            WHERE status = 'accepted'
            AND deleted_at IS NULL
        ")['total'];

        ResponseHelper::success([
            'summary' => [
                'total_requests' => (int)$totalRequests,
                'total_estimates' => (int)$totalEstimates,
                'total_services' => (int)$totalServices,
                'total_materials' => (int)$totalMaterials,
                'recent_requests' => (int)$recentRequestsCount,
                'conversion_rate' => (float)$conversionRate,
                'acceptance_rate' => (float)$acceptanceRate,
                'total_revenue' => (float)$totalRevenue,
            ],
            'requests_by_status' => $requestsByStatus,
            'estimates_by_status' => $estimatesByStatus,
        ]);
    }

    public function popularServices(Request $request, Response $response, array $params): void
    {
        $database = $this->container->get('database');
        $limit = (int)($request->query('limit') ?? 10);

        $popularServices = $database->fetchAll("
            SELECT 
                s.id,
                s.name,
                COUNT(cr.id) as request_count,
                COUNT(DISTINCT ce.id) as estimate_count
            FROM services s
            LEFT JOIN customer_requests cr ON s.id = cr.service_id AND cr.deleted_at IS NULL
            LEFT JOIN cost_estimates ce ON cr.id = ce.request_id AND ce.deleted_at IS NULL
            WHERE s.deleted_at IS NULL
            GROUP BY s.id, s.name
            ORDER BY request_count DESC
            LIMIT ?
        ", [$limit]);

        ResponseHelper::success($popularServices);
    }

    public function requestTrends(Request $request, Response $response, array $params): void
    {
        $database = $this->container->get('database');
        $days = (int)($request->query('days') ?? 30);

        $trends = $database->fetchAll("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM customer_requests
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND deleted_at IS NULL
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ", [$days]);

        ResponseHelper::success($trends);
    }

    public function estimateTrends(Request $request, Response $response, array $params): void
    {
        $database = $this->container->get('database');
        $days = (int)($request->query('days') ?? 30);

        $trends = $database->fetchAll("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count,
                COALESCE(SUM(total_amount), 0) as total_amount
            FROM cost_estimates
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND deleted_at IS NULL
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ", [$days]);

        ResponseHelper::success($trends);
    }

    public function recentActivity(Request $request, Response $response, array $params): void
    {
        $database = $this->container->get('database');
        $limit = (int)($request->query('limit') ?? 20);

        $activities = $database->fetchAll("
            SELECT 
                al.id,
                al.event_type,
                al.auditable_type,
                al.auditable_id,
                al.created_at,
                CONCAT(au.first_name, ' ', au.last_name) as admin_name,
                au.username as admin_username
            FROM audit_logs al
            LEFT JOIN admin_users au ON al.user_id = au.id
            WHERE al.user_type = 'admin'
            ORDER BY al.created_at DESC
            LIMIT ?
        ", [$limit]);

        ResponseHelper::success($activities);
    }

    public function conversionStats(Request $request, Response $response, array $params): void
    {
        $database = $this->container->get('database');
        $days = (int)($request->query('days') ?? 30);

        $stats = $database->fetchOne("
            SELECT 
                COUNT(DISTINCT cr.id) as total_requests,
                COUNT(DISTINCT ce.id) as total_estimates,
                COUNT(DISTINCT CASE WHEN ce.status = 'sent' THEN ce.id END) as sent_estimates,
                COUNT(DISTINCT CASE WHEN ce.status = 'viewed' THEN ce.id END) as viewed_estimates,
                COUNT(DISTINCT CASE WHEN ce.status = 'accepted' THEN ce.id END) as accepted_estimates,
                COUNT(DISTINCT CASE WHEN ce.status = 'rejected' THEN ce.id END) as rejected_estimates,
                COALESCE(SUM(CASE WHEN ce.status = 'accepted' THEN ce.total_amount END), 0) as total_revenue
            FROM customer_requests cr
            LEFT JOIN cost_estimates ce ON cr.id = ce.request_id AND ce.deleted_at IS NULL
            WHERE cr.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND cr.deleted_at IS NULL
        ", [$days]);

        $totalRequests = (int)$stats['total_requests'];
        $totalEstimates = (int)$stats['total_estimates'];
        $sentEstimates = (int)$stats['sent_estimates'];
        $viewedEstimates = (int)$stats['viewed_estimates'];
        $acceptedEstimates = (int)$stats['accepted_estimates'];

        $stats['request_to_estimate_rate'] = $totalRequests > 0 
            ? round(($totalEstimates / $totalRequests) * 100, 2) 
            : 0;

        $stats['sent_to_viewed_rate'] = $sentEstimates > 0 
            ? round(($viewedEstimates / $sentEstimates) * 100, 2) 
            : 0;

        $stats['viewed_to_accepted_rate'] = $viewedEstimates > 0 
            ? round(($acceptedEstimates / $viewedEstimates) * 100, 2) 
            : 0;

        $stats['overall_acceptance_rate'] = $totalEstimates > 0 
            ? round(($acceptedEstimates / $totalEstimates) * 100, 2) 
            : 0;

        ResponseHelper::success($stats);
    }

    public function materialUsage(Request $request, Response $response, array $params): void
    {
        $database = $this->container->get('database');
        $limit = (int)($request->query('limit') ?? 10);

        $usage = $database->fetchAll("
            SELECT 
                m.id,
                m.name,
                m.sku,
                m.category,
                COUNT(cei.id) as usage_count,
                COALESCE(SUM(cei.quantity), 0) as total_quantity,
                COALESCE(SUM(cei.line_total), 0) as total_value
            FROM materials m
            LEFT JOIN cost_estimate_items cei ON m.id = cei.item_id AND cei.item_type = 'material'
            WHERE m.deleted_at IS NULL
            GROUP BY m.id, m.name, m.sku, m.category
            ORDER BY usage_count DESC
            LIMIT ?
        ", [$limit]);

        ResponseHelper::success($usage);
    }

    public function customerStats(Request $request, Response $response, array $params): void
    {
        $database = $this->container->get('database');

        // Top customers by request count
        $topCustomers = $database->fetchAll("
            SELECT 
                customer_email,
                customer_name,
                COUNT(DISTINCT cr.id) as request_count,
                COUNT(DISTINCT ce.id) as estimate_count,
                COALESCE(SUM(CASE WHEN ce.status = 'accepted' THEN ce.total_amount END), 0) as total_revenue
            FROM customer_requests cr
            LEFT JOIN cost_estimates ce ON cr.id = ce.request_id AND ce.deleted_at IS NULL
            WHERE cr.deleted_at IS NULL
            GROUP BY customer_email, customer_name
            ORDER BY request_count DESC
            LIMIT 20
        ");

        // New customers (last 30 days)
        $newCustomers = $database->fetchOne("
            SELECT COUNT(DISTINCT customer_email) as count
            FROM customer_requests
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND deleted_at IS NULL
        ")['count'];

        ResponseHelper::success([
            'top_customers' => $topCustomers,
            'new_customers_last_30_days' => (int)$newCustomers,
        ]);
    }
}
