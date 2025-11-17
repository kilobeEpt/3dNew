<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Models\SiteSetting;
use App\Repositories\SiteSettingRepository;
use App\Services\AuditLogger;

class SiteSettingsController
{
    private Container $container;
    private SiteSetting $settingModel;
    private SiteSettingRepository $settingRepo;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->settingModel = new SiteSetting($database);
        $this->settingRepo = new SiteSettingRepository($database);
        $this->auditLogger = new AuditLogger($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $group = $request->query('group');

        if ($group) {
            $settings = $this->settingRepo->findByGroup($group);
        } else {
            $settings = $this->settingModel->all();
        }

        ResponseHelper::success($settings);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $key = $params['key'];
        $setting = $this->settingModel->first(['key' => $key]);

        if (!$setting) {
            ResponseHelper::notFound('Setting not found');
        }

        ResponseHelper::success($setting);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        $rules = [
            'key' => 'required|min:2|max:100',
            'value' => 'required',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        // Check if key exists
        $existing = $this->settingModel->first(['key' => $data['key']]);
        if ($existing) {
            ResponseHelper::error('A setting with this key already exists', null, 409);
        }

        $fillableData = [
            'key' => $data['key'],
            'value' => $data['value'],
            'type' => $data['type'] ?? 'string',
            'group' => $data['group'] ?? 'general',
            'label' => $data['label'] ?? null,
            'description' => $data['description'] ?? null,
            'is_public' => (bool)($data['is_public'] ?? false),
        ];

        $id = $this->settingModel->create($fillableData);

        // Audit log
        $this->auditLogger->logCreate(
            (int)$request->adminUser['id'],
            'site_setting',
            (int)$id,
            $fillableData,
            $request->ip(),
            $request->userAgent()
        );

        $setting = $this->settingModel->find($id);
        ResponseHelper::created($setting, 'Setting created successfully');
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $key = $params['key'];
        $setting = $this->settingModel->first(['key' => $key]);

        if (!$setting) {
            ResponseHelper::notFound('Setting not found');
        }

        $data = $request->input();

        $updateData = [];
        $allowedFields = ['value', 'type', 'group', 'label', 'description', 'is_public'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $this->settingModel->update($setting['id'], $updateData);

            // Audit log
            $this->auditLogger->logUpdate(
                (int)$request->adminUser['id'],
                'site_setting',
                (int)$setting['id'],
                $setting,
                $updateData,
                $request->ip(),
                $request->userAgent()
            );
        }

        $updatedSetting = $this->settingModel->find($setting['id']);
        ResponseHelper::success($updatedSetting, 'Setting updated successfully');
    }

    public function destroy(Request $request, Response $response, array $params): void
    {
        $key = $params['key'];
        $setting = $this->settingModel->first(['key' => $key]);

        if (!$setting) {
            ResponseHelper::notFound('Setting not found');
        }

        $this->settingModel->delete($setting['id']);

        // Audit log
        $this->auditLogger->logDelete(
            (int)$request->adminUser['id'],
            'site_setting',
            (int)$setting['id'],
            $setting,
            $request->ip(),
            $request->userAgent()
        );

        ResponseHelper::success(null, 'Setting deleted successfully');
    }

    public function bulk(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        if (!isset($data['settings']) || !is_array($data['settings'])) {
            ResponseHelper::error('Settings array is required', null, 400);
        }

        $updated = [];
        foreach ($data['settings'] as $key => $value) {
            $setting = $this->settingModel->first(['key' => $key]);
            if ($setting) {
                $oldValue = $setting['value'];
                $this->settingModel->update($setting['id'], ['value' => $value]);
                
                // Audit log
                $this->auditLogger->logUpdate(
                    (int)$request->adminUser['id'],
                    'site_setting',
                    (int)$setting['id'],
                    ['value' => $oldValue],
                    ['value' => $value],
                    $request->ip(),
                    $request->userAgent()
                );
                
                $updated[] = $key;
            }
        }

        ResponseHelper::success([
            'updated' => $updated,
            'count' => count($updated),
        ], 'Settings updated successfully');
    }
}
