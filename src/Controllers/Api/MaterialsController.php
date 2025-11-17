<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Repositories\MaterialRepository;
use App\Models\Material;

class MaterialsController
{
    private MaterialRepository $repository;
    private Material $model;

    public function __construct()
    {
        $container = Container::getInstance();
        $database = $container->get('database');
        $this->repository = new MaterialRepository($database);
        $this->model = new Material($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)$request->query('page', 1);
        $perPage = min((int)$request->query('per_page', 20), 100);
        $category = $request->query('category');
        $search = $request->query('search');

        $conditions = ['is_active' => true];

        if ($category) {
            $conditions['category'] = $category;
        }

        if ($search) {
            $materials = $this->repository->search('name', $search, $conditions, $perPage);
            $result = [
                'data' => $materials,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => count($materials),
                    'last_page' => 1,
                    'from' => count($materials) > 0 ? 1 : 0,
                    'to' => count($materials),
                ]
            ];
        } else {
            $result = $this->repository->paginate(
                $page,
                $perPage,
                $conditions,
                ['name' => 'ASC']
            );
        }

        $this->setCacheHeaders();
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            ResponseHelper::notFound('Material not found');
        }

        $material = $this->repository->findById($id);

        if (!$material || !$material['is_active']) {
            ResponseHelper::notFound('Material not found');
        }

        $this->setCacheHeaders();
        ResponseHelper::success($material);
    }

    public function categories(Request $request, Response $response, array $params): void
    {
        $categories = $this->repository->getCategories();
        
        $this->setCacheHeaders();
        ResponseHelper::success($categories);
    }

    private function setCacheHeaders(): void
    {
        header('Cache-Control: public, max-age=600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 600) . ' GMT');
    }
}
