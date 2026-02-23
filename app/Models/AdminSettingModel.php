<?php

namespace App\Models;

class AdminSettingModel extends BaseUuidModel
{
    protected $table = 'admin_settings';
    protected $returnType = 'array';
    protected $allowedFields = ['id', 'setting_key', 'setting_value'];
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
}
