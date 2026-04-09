<?php

namespace App\Models;

class DashboardBlockInstanceModel extends BaseUuidModel
{
    protected $table = 'dashboard_block_instances';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'dashboard_id',
        'block_type_id',
        'block_id',
        'title',
        'position_x',
        'position_y',
        'width',
        'height',
        'order_index',
        'is_visible',
        'config_json',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function __construct(?\CodeIgniter\Database\ConnectionInterface &$db = null, ?\CodeIgniter\Validation\ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        if ($this->db->tableExists('dashboard_block_instances') && $this->db->tableExists('dashboard_blocks')) {
            $requiredColumns = ['title', 'width', 'height', 'is_visible'];
            $missingColumns = array_filter(
                $requiredColumns,
                fn(string $column): bool => ! $this->db->fieldExists($column, 'dashboard_block_instances')
            );

            if ($missingColumns !== []) {
                $this->table = 'dashboard_blocks';
            }
        } elseif (! $this->db->tableExists($this->table) && $this->db->tableExists('dashboard_blocks')) {
            $this->table = 'dashboard_blocks';
        }

        $this->useSoftDeletes = $this->db->fieldExists('deleted_at', $this->table);
    }

    public function getInstancesByDashboardId(string $dashboardId): array
    {
        $relationColumn = $this->relationColumn();
        $typeTable = $this->blockTypeTable();

        return $this->select('dashboard_block_instances.*, ' . $typeTable . '.code AS block_type_code, ' . $typeTable . '.name AS block_type_name')
            ->join($typeTable, $typeTable . '.id = dashboard_block_instances.' . $relationColumn, 'left')
            ->where('dashboard_block_instances.dashboard_id', $dashboardId)
            ->orderBy('dashboard_block_instances.order_index', 'ASC')
            ->orderBy('dashboard_block_instances.position_y', 'ASC')
            ->orderBy('dashboard_block_instances.position_x', 'ASC')
            ->findAll();
    }

    public function getNextOrderIndex(string $dashboardId): int
    {
        $row = $this->selectMax('order_index')
            ->where('dashboard_id', $dashboardId)
            ->first();

        return ((int) ($row['order_index'] ?? -1)) + 1;
    }

    public function findInstanceWithBlock(string $instanceId): ?array
    {
        $relationColumn = $this->relationColumn();
        $typeTable = $this->blockTypeTable();

        $row = $this->select('dashboard_block_instances.*, ' . $typeTable . '.code AS block_type_code, ' . $typeTable . '.name AS block_type_name')
            ->join($typeTable, $typeTable . '.id = dashboard_block_instances.' . $relationColumn, 'left')
            ->where('dashboard_block_instances.id', $instanceId)
            ->first();

        return is_array($row) ? $row : null;
    }

    public function relationColumn(): string
    {
        return $this->db->fieldExists('block_type_id', $this->table) ? 'block_type_id' : 'block_id';
    }

    public function blockTypeTable(): string
    {
        return $this->db->tableExists('dashboard_block_types') ? 'dashboard_block_types' : 'dashboard_blocks';
    }
}
