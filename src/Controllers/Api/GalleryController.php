<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Models\GalleryItem;

class GalleryController
{
    private GalleryItem $model;

    public function __construct()
    {
        $container = Container::getInstance();
        $database = $container->get('database');
        $this->model = new GalleryItem($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)$request->query('page', 1);
        $perPage = min((int)$request->query('per_page', 20), 100);
        $category = $request->query('category');
        $featured = $request->query('featured');
        $serviceId = $request->query('service_id');

        $conditions = ['is_visible' => true];

        if ($category) {
            $conditions['category'] = $category;
        }

        if ($featured === 'true' || $featured === '1') {
            $conditions['is_featured'] = true;
        }

        if ($serviceId) {
            $conditions['service_id'] = (int)$serviceId;
        }

        $result = $this->model->paginate(
            $page,
            $perPage,
            $conditions,
            ['display_order' => 'ASC', 'created_at' => 'DESC']
        );

        $this->setCacheHeaders();
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            ResponseHelper::notFound('Gallery item not found');
        }

        $item = $this->model->find($id);

        if (!$item || !$item['is_visible']) {
            ResponseHelper::notFound('Gallery item not found');
        }

        $container = Container::getInstance();
        $database = $container->get('database');
        $database->execute(
            'UPDATE gallery_items SET view_count = view_count + 1 WHERE id = ?',
            [$id]
        );

        $this->setCacheHeaders();
        ResponseHelper::success($item);
    }

    private function setCacheHeaders(): void
    {
        header('Cache-Control: public, max-age=600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 600) . ' GMT');
    }
}
