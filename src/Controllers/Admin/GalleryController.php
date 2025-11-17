<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Models\GalleryItem;
use App\Services\AuditLogger;

class GalleryController
{
    private Container $container;
    private GalleryItem $galleryModel;
    private AuditLogger $auditLogger;
    private string $uploadPath;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->galleryModel = new GalleryItem($database);
        $this->auditLogger = new AuditLogger($database);
        $this->uploadPath = __DIR__ . '/../../../public_html/uploads/gallery/';
        
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $page = (int)($request->query('page') ?? 1);
        $perPage = (int)($request->query('per_page') ?? 20);
        $category = $request->query('category');

        $conditions = [];
        if ($category) {
            $conditions['category'] = $category;
        }

        $result = $this->galleryModel->paginate($page, $perPage, $conditions);
        ResponseHelper::success($result);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $item = $this->galleryModel->find($id);

        if (!$item) {
            ResponseHelper::notFound('Gallery item not found');
        }

        ResponseHelper::success($item);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        $rules = [
            'title' => 'required|min:3|max:200',
            'category' => 'required|max:100',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        $filePath = null;
        $thumbnailPath = null;

        // Handle file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadResult = $this->handleFileUpload($file);
            
            if ($uploadResult['error']) {
                ResponseHelper::error($uploadResult['message'], null, 400);
            }
            
            $filePath = $uploadResult['path'];
            $thumbnailPath = $uploadResult['thumbnail'] ?? null;
        } elseif (isset($data['image_url'])) {
            $filePath = $data['image_url'];
        }

        $fillableData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'media_type' => $data['media_type'] ?? 'image',
            'file_path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'external_url' => $data['external_url'] ?? null,
            'is_featured' => (bool)($data['is_featured'] ?? false),
            'is_visible' => (bool)($data['is_visible'] ?? true),
            'sort_order' => $data['sort_order'] ?? 0,
        ];

        $id = $this->galleryModel->create($fillableData);

        // Audit log
        $this->auditLogger->logCreate(
            (int)$request->adminUser['id'],
            'gallery_item',
            (int)$id,
            $fillableData,
            $request->ip(),
            $request->userAgent()
        );

        $item = $this->galleryModel->find($id);
        ResponseHelper::created($item, 'Gallery item created successfully');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $item = $this->galleryModel->find($id);

        if (!$item) {
            ResponseHelper::notFound('Gallery item not found');
        }

        $data = $request->input();

        $updateData = [];
        $allowedFields = [
            'title', 'description', 'category', 'media_type', 'external_url',
            'is_featured', 'is_visible', 'sort_order'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        // Handle new file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadResult = $this->handleFileUpload($file);
            
            if ($uploadResult['error']) {
                ResponseHelper::error($uploadResult['message'], null, 400);
            }
            
            // Delete old file
            if ($item['file_path'] && file_exists($this->uploadPath . basename($item['file_path']))) {
                unlink($this->uploadPath . basename($item['file_path']));
            }
            if ($item['thumbnail_path'] && file_exists($this->uploadPath . basename($item['thumbnail_path']))) {
                unlink($this->uploadPath . basename($item['thumbnail_path']));
            }
            
            $updateData['file_path'] = $uploadResult['path'];
            $updateData['thumbnail_path'] = $uploadResult['thumbnail'] ?? null;
        }

        if (!empty($updateData)) {
            $this->galleryModel->update($id, $updateData);

            // Audit log
            $this->auditLogger->logUpdate(
                (int)$request->adminUser['id'],
                'gallery_item',
                $id,
                $item,
                $updateData,
                $request->ip(),
                $request->userAgent()
            );
        }

        $updatedItem = $this->galleryModel->find($id);
        ResponseHelper::success($updatedItem, 'Gallery item updated successfully');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $id = (int)$params['id'];
        $item = $this->galleryModel->find($id);

        if (!$item) {
            ResponseHelper::notFound('Gallery item not found');
        }

        // Delete files
        if ($item['file_path'] && file_exists($this->uploadPath . basename($item['file_path']))) {
            unlink($this->uploadPath . basename($item['file_path']));
        }
        if ($item['thumbnail_path'] && file_exists($this->uploadPath . basename($item['thumbnail_path']))) {
            unlink($this->uploadPath . basename($item['thumbnail_path']));
        }

        $this->galleryModel->delete($id);

        // Audit log
        $this->auditLogger->logDelete(
            (int)$request->adminUser['id'],
            'gallery_item',
            $id,
            $item,
            $request->ip(),
            $request->userAgent()
        );

        ResponseHelper::success(null, 'Gallery item deleted successfully');
    }

    private function handleFileUpload(array $file): array
    {
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return ['error' => true, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
        }

        if ($file['size'] > $maxSize) {
            return ['error' => true, 'message' => 'File size exceeds 5MB limit.'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('gallery_', true) . '.' . $extension;
        $fullPath = $this->uploadPath . $filename;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return ['error' => true, 'message' => 'Failed to upload file.'];
        }

        // Generate thumbnail (simple resize)
        $thumbnailFilename = 'thumb_' . $filename;
        $thumbnailPath = $this->uploadPath . $thumbnailFilename;
        $this->createThumbnail($fullPath, $thumbnailPath, 300, 300);

        return [
            'error' => false,
            'path' => '/uploads/gallery/' . $filename,
            'thumbnail' => '/uploads/gallery/' . $thumbnailFilename,
        ];
    }

    private function createThumbnail(string $source, string $destination, int $width, int $height): bool
    {
        try {
            list($sourceWidth, $sourceHeight, $imageType) = getimagesize($source);

            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($source);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($source);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($source);
                    break;
                case IMAGETYPE_WEBP:
                    $sourceImage = imagecreatefromwebp($source);
                    break;
                default:
                    return false;
            }

            $ratio = min($width / $sourceWidth, $height / $sourceHeight);
            $newWidth = (int)($sourceWidth * $ratio);
            $newHeight = (int)($sourceHeight * $ratio);

            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    imagejpeg($thumbnail, $destination, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($thumbnail, $destination, 8);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($thumbnail, $destination);
                    break;
                case IMAGETYPE_WEBP:
                    imagewebp($thumbnail, $destination, 85);
                    break;
            }

            imagedestroy($sourceImage);
            imagedestroy($thumbnail);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
