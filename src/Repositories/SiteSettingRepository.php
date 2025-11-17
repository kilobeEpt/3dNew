<?php

declare(strict_types=1);

namespace App\Repositories;

class SiteSettingRepository extends BaseRepository
{
    protected string $table = 'site_settings';

    public function findByKey(string $key)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE setting_key = ? LIMIT 1";
        return $this->database->fetchOne($sql, [$key]);
    }

    public function getValue(string $key, $default = null)
    {
        $setting = $this->findByKey($key);
        
        if (!$setting) {
            return $default;
        }
        
        return $this->castValue($setting['setting_value'], $setting['setting_type']);
    }

    public function setValue(string $key, $value): bool
    {
        $setting = $this->findByKey($key);
        
        if (!$setting) {
            return false;
        }
        
        $stringValue = $this->valueToString($value, $setting['setting_type']);
        
        return $this->update($setting['id'], [
            'setting_value' => $stringValue,
        ]);
    }

    public function findByGroup(string $group): array
    {
        return $this->findAll([
            'group_name' => $group,
        ], ['display_order' => 'ASC']);
    }

    public function findPublic(): array
    {
        return $this->findAll([
            'is_public' => true,
        ], ['group_name' => 'ASC', 'display_order' => 'ASC']);
    }

    public function getAllAsArray(): array
    {
        $settings = $this->findAll([], ['setting_key' => 'ASC']);
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->castValue(
                $setting['setting_value'],
                $setting['setting_type']
            );
        }
        
        return $result;
    }

    protected function castValue($value, string $type)
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

    protected function valueToString($value, string $type): string
    {
        switch ($type) {
            case 'json':
                return json_encode($value);
            case 'boolean':
                return $value ? 'true' : 'false';
            default:
                return (string)$value;
        }
    }
}
