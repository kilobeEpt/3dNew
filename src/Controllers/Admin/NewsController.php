<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Models\NewsPost;
use App\Services\AuditLogger;

class NewsController
{
    private Container $container;
    private NewsPost $newsModel;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->newsModel = new NewsPost($database);
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

        $result = $this->newsModel->paginate($page, $perPage, $conditions);
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $post = $this->newsModel->find($id);

        if (!$post) {
            ResponseHelper::notFound('News post not found');
        }

        ResponseHelper::success($post);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        $rules = [
            'title' => 'required|min:3|max:200',
            'slug' => 'required|min:3|max:200',
            'content' => 'required',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        // Check if slug exists
        $existing = $this->newsModel->first(['slug' => $data['slug']]);
        if ($existing) {
            ResponseHelper::error('A post with this slug already exists', null, 409);
        }

        $fillableData = [
            'author_id' => (int)$request->adminUser['id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'featured_image' => $data['featured_image'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'published_at' => $data['published_at'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ];

        $id = $this->newsModel->create($fillableData);

        // Audit log
        $this->auditLogger->logCreate(
            (int)$request->adminUser['id'],
            'news_post',
            (int)$id,
            $fillableData,
            $request->ip(),
            $request->userAgent()
        );

        $post = $this->newsModel->find($id);
        ResponseHelper::created($post, 'News post created successfully');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $post = $this->newsModel->find($id);

        if (!$post) {
            ResponseHelper::notFound('News post not found');
        }

        $data = $request->input();

        $rules = [
            'title' => 'min:3|max:200',
            'slug' => 'min:3|max:200',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        // Check slug uniqueness if changed
        if (isset($data['slug']) && $data['slug'] !== $post['slug']) {
            $existing = $this->newsModel->first(['slug' => $data['slug']]);
            if ($existing) {
                ResponseHelper::error('A post with this slug already exists', null, 409);
            }
        }

        $updateData = [];
        $allowedFields = [
            'title', 'slug', 'excerpt', 'content', 'featured_image',
            'status', 'published_at', 'meta_title', 'meta_description'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $this->newsModel->update($id, $updateData);

            // Audit log
            $this->auditLogger->logUpdate(
                (int)$request->adminUser['id'],
                'news_post',
                $id,
                $post,
                $updateData,
                $request->ip(),
                $request->userAgent()
            );
        }

        $updatedPost = $this->newsModel->find($id);
        ResponseHelper::success($updatedPost, 'News post updated successfully');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $post = $this->newsModel->find($id);

        if (!$post) {
            ResponseHelper::notFound('News post not found');
        }

        $this->newsModel->delete($id);

        // Audit log
        $this->auditLogger->logDelete(
            (int)$request->adminUser['id'],
            'news_post',
            $id,
            $post,
            $request->ip(),
            $request->userAgent()
        );

        ResponseHelper::success(null, 'News post deleted successfully');
    }
}
