<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Models\NewsPost;

class NewsController
{
    private NewsPost $model;

    public function __construct()
    {
        $container = Container::getInstance();
        $database = $container->get('database');
        $this->model = new NewsPost($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)$request->query('page', 1);
        $perPage = min((int)$request->query('per_page', 20), 100);
        $category = $request->query('category');
        $featured = $request->query('featured');
        $search = $request->query('search');

        $container = Container::getInstance();
        $database = $container->get('database');

        $sql = "SELECT * FROM news_posts 
                WHERE status = 'published' 
                AND deleted_at IS NULL
                AND (published_at IS NULL OR published_at <= NOW())";
        
        $queryParams = [];

        if ($category) {
            $sql .= " AND category = ?";
            $queryParams[] = $category;
        }

        if ($featured === 'true' || $featured === '1') {
            $sql .= " AND is_featured = 1";
        }

        if ($search) {
            $sql .= " AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
            $searchTerm = "%{$search}%";
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
        }

        $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as filtered";
        $totalResult = $database->fetchOne($countSql, $queryParams);
        $total = (int)$totalResult['total'];

        $sql .= " ORDER BY is_featured DESC, published_at DESC, created_at DESC";
        $sql .= " LIMIT ? OFFSET ?";
        $queryParams[] = $perPage;
        $queryParams[] = ($page - 1) * $perPage;

        $posts = $database->fetchAll($sql, $queryParams);

        $this->setCacheHeaders();
        ResponseHelper::success([
            'data' => $posts,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $total),
            ]
        ]);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $identifier = $params['id'] ?? null;

        if (!$identifier) {
            ResponseHelper::notFound('News post not found');
        }

        $container = Container::getInstance();
        $database = $container->get('database');

        if (is_numeric($identifier)) {
            $sql = "SELECT * FROM news_posts 
                    WHERE id = ? 
                    AND status = 'published' 
                    AND deleted_at IS NULL
                    AND (published_at IS NULL OR published_at <= NOW())
                    LIMIT 1";
            $post = $database->fetchOne($sql, [(int)$identifier]);
        } else {
            $sql = "SELECT * FROM news_posts 
                    WHERE slug = ? 
                    AND status = 'published' 
                    AND deleted_at IS NULL
                    AND (published_at IS NULL OR published_at <= NOW())
                    LIMIT 1";
            $post = $database->fetchOne($sql, [$identifier]);
        }

        if (!$post) {
            ResponseHelper::notFound('News post not found');
        }

        $database->execute(
            'UPDATE news_posts SET view_count = view_count + 1 WHERE id = ?',
            [$post['id']]
        );

        $this->setCacheHeaders();
        ResponseHelper::success($post);
    }

    private function setCacheHeaders(): void
    {
        header('Cache-Control: public, max-age=300');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 300) . ' GMT');
    }
}
