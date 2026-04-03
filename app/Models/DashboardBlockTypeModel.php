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

        if (! $this->db->tableExists($this->table) && $this->db->tableExists('dashboard_blocks')) {
            $this->table = 'dashboard_blocks';
        }

        $this->useSoftDeletes = $this->db->fieldExists('deleted_at', $this->table);
    }

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
