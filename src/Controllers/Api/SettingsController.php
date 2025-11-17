<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Repositories\SiteSettingRepository;

class SettingsController
{
    private SiteSettingRepository $repository;

    public function __construct()
    {
        $container = Container::getInstance();
        $database = $container->get('database');
        $this->repository = new SiteSettingRepository($database);
    }

    public function index(Request $request, Response $response, array $params): void
    {
        $group = $request->query('group');

        if ($group) {
            $settings = $this->repository->findByGroup($group);
            $settings = array_filter($settings, fn($s) => $s['is_public']);
        } else {
            $settings = $this->repository->findPublic();
        }

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->castValue(
                $setting['setting_value'],
                $setting['setting_type']
            );
        }

        $this->setCacheHeaders();
        ResponseHelper::success($result);
    }

    private function castValue($value, string $type)
    {
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (float)$value : $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    private function setCacheHeaders(): void
    {
        header('Cache-Control: public, max-age=3600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
    }
}
