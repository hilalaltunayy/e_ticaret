<?php

namespace App\Models;

class DashboardBlockModel extends BaseUuidModel
{
    protected $table = 'dashboard_blocks';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'dashboard_id',
        'block_type_id',
        'title',
        'config_json',
        'position_x',
        'position_y',
        'width',
        'height',
        'order_index',
        'is_visible',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getBlocksByDashboardId(string $dashboardId): array
    {
        return $this->select('dashboard_blocks.*, dashboard_block_types.code AS block_type_code, dashboard_block_types.name AS block_type_name')
            ->join('dashboard_block_types', 'dashboard_block_types.id = dashboard_blocks.block_type_id', 'left')
            ->where('dashboard_blocks.dashboard_id', $dashboardId)
            ->orderBy('dashboard_blocks.order_index', 'ASC')
            ->orderBy('dashboard_blocks.position_y', 'ASC')
            ->orderBy('dashboard_blocks.position_x', 'ASC')
            ->findAll();
    }

    public function getNextOrderIndex(string $dashboardId): int
    {
        $row = $this->selectMax('order_index')
            ->where('dashboard_id', $dashboardId)
            ->first();

        return ((int) ($row['order_index'] ?? -1)) + 1;
    }

    public function findBlockWithType(string $blockId): ?array
    {
        $row = $this->select('dashboard_blocks.*, dashboard_block_types.code AS block_type_code, dashboard_block_types.name AS block_type_name')
            ->join('dashboard_block_types', 'dashboard_block_types.id = dashboard_blocks.block_type_id', 'left')
            ->where('dashboard_blocks.id', $blockId)
            ->first();

        return is_array($row) ? $row : null;
    }
}
