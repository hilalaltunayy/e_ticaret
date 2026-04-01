<?php

namespace App\Models;

class DashboardBlockTypeModel extends BaseUuidModel
{
    protected $table = 'dashboard_block_types';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'code',
        'name',
        'description',
        'default_config',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getActiveTypes(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function findActiveById(string $id): ?array
    {
        $row = $this->where('id', $id)
            ->where('is_active', 1)
            ->first();

        return is_array($row) ? $row : null;
    }
}
