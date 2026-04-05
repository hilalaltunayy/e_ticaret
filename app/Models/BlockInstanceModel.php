<?php

namespace App\Models;

class BlockInstanceModel extends BaseUuidModel
{
    protected $table = 'block_instances';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'owner_type',
        'owner_version_id',
        'block_type_id',
        'zone',
        'position_x',
        'position_y',
        'width',
        'height',
        'order_index',
        'config_json',
        'is_visible',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByPageVersion(string $pageVersionId): array
    {
        return $this->where('owner_type', 'PAGE')
            ->where('owner_version_id', $pageVersionId)
            ->orderBy('order_index', 'ASC')
            ->findAll();
    }

    public function findDetailedByPageVersion(string $pageVersionId): array
    {
        return $this->select('block_instances.*, block_types.code AS block_type_code, block_types.name AS block_type_name')
            ->join('block_types', 'block_types.id = block_instances.block_type_id', 'left')
            ->where('block_instances.owner_type', 'PAGE')
            ->where('block_instances.owner_version_id', $pageVersionId)
            ->orderBy('block_instances.order_index', 'ASC')
            ->findAll();
    }

    public function findByIdDetailed(string $blockId): ?array
    {
        $row = $this->select('block_instances.*, page_versions.page_id')
            ->join('page_versions', 'page_versions.id = block_instances.owner_version_id', 'left')
            ->where('block_instances.id', $blockId)
            ->first();

        return is_array($row) ? $row : null;
    }

    public function getNextOrderIndex(string $versionId): int
    {
        $row = $this->selectMax('order_index')
            ->where('owner_type', 'PAGE')
            ->where('owner_version_id', $versionId)
            ->first();

        return ((int) ($row['order_index'] ?? -1)) + 1;
    }
}
