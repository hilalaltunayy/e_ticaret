<?php

namespace App\Models;

class BlockTypeModel extends BaseUuidModel
{
    protected $table = 'block_types';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'code',
        'name',
        'description',
        'schema_json',
        'default_config_json',
        'allowed_zones',
        'is_active',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findActiveOrdered(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function findByCode(string $code): ?array
    {
        $row = $this->where('code', $code)->first();

        return is_array($row) ? $row : null;
    }
}
