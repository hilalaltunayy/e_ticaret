<?php

namespace App\Models;

class AdminSettingModel extends BaseUuidModel
{
    protected $table = 'admin_settings';
    protected $returnType = 'array';
    protected $allowedFields = ['id', 'setting_key', 'setting_value', 'updated_at'];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;

    public function getByKeyPrefix(string $prefix): array
    {
        return $this->builder()
            ->select('setting_key, setting_value')
            ->like('setting_key', $prefix, 'after')
            ->get()
            ->getResultArray();
    }

    public function getMapByKeys(array $keys): array
    {
        $keys = array_values(array_filter(array_map(static fn ($k) => trim((string) $k), $keys), static fn ($k) => $k !== ''));
        if ($keys === []) {
            return [];
        }

        $rows = $this->builder()
            ->select('setting_key, setting_value')
            ->whereIn('setting_key', $keys)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if ($key === '') {
                continue;
            }
            $map[$key] = (string) ($row['setting_value'] ?? '');
        }

        return $map;
    }

    public function setValue(string $key, string $value): bool
    {
        $key = trim($key);
        if ($key === '') {
            return false;
        }

        $existing = $this->where('setting_key', $key)->first();
        $now = date('Y-m-d H:i:s');

        if (is_array($existing) && isset($existing['id'])) {
            return (bool) $this->update((string) $existing['id'], [
                'setting_value' => $value,
                'updated_at' => $now,
            ]);
        }

        return (bool) $this->insert([
            'setting_key' => $key,
            'setting_value' => $value,
            'updated_at' => $now,
        ]);
    }
}
