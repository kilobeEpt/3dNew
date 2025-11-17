<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Repositories\ServiceRepository;
use App\Models\Service;

class ServicesController
{
    private ServiceRepository $repository;
    private Service $model;

    public function __construct()
    {
        $container = Container::getInstance();
        $database = $container->get('database');
        $this->repository = new ServiceRepository($database);
        $this->model = new Service($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)$request->query('page', 1);
        $perPage = min((int)$request->query('per_page', 20), 100);
        $category = $request->query('category');
        $featured = $request->query('featured');
        $search = $request->query('search');

        $conditions = ['is_visible' => true];

        if ($category) {
            $conditions['category_id'] = (int)$category;
        }

        if ($featured === 'true' || $featured === '1') {
            $conditions['is_featured'] = true;
        }

        if ($search) {
            $services = $this->repository->search('name', $search, $conditions, $perPage);
            $result = [
                'data' => $services,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => count($services),
                    'last_page' => 1,
                    'from' => count($services) > 0 ? 1 : 0,
                    'to' => count($services),
                ]
            ];
        } else {
            $result = $this->repository->paginate(
                $page,
                $perPage,
                $conditions,
                ['display_order' => 'ASC', 'name' => 'ASC']
            );
        }

        $this->setCacheHeaders();
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $identifier = $params['id'] ?? null;

        if (!$identifier) {
            ResponseHelper::notFound('Service not found');
        }

        if (is_numeric($identifier)) {
            $service = $this->repository->findById((int)$identifier);
        } else {
            $service = $this->repository->findBySlug($identifier);
        }

        if (!$service || !$service['is_visible']) {
            ResponseHelper::notFound('Service not found');
        }

        $this->setCacheHeaders();
        ResponseHelper::success($service);
    }

    private function setCacheHeaders(): void
    {
        header('Cache-Control: public, max-age=300');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 300) . ' GMT');
    }
}
