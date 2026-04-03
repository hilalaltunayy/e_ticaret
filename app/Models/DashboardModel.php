<?php

namespace App\Models;

class DashboardModel extends BaseUuidModel
{
    protected $table = 'dashboards';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'user_id',
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function __construct(?\CodeIgniter\Database\ConnectionInterface &$db = null, ?\CodeIgniter\Validation\ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        $this->useSoftDeletes = $this->db->fieldExists('deleted_at', $this->table);
    }

    public function findActiveForUser(?string $userId): ?array
    {
        $userId = trim((string) $userId);

        if ($userId !== '') {
            $row = $this->where('is_active', 1)
                ->where('user_id', $userId)
                ->orderBy('updated_at', 'DESC')
                ->first();
            if (is_array($row)) {
                return $row;
            }
        }

        $row = $this->where('is_active', 1)
            ->groupStart()
            ->where('user_id', null)
            ->orWhere('user_id', '')
            ->groupEnd()
            ->orderBy('updated_at', 'DESC')
            ->first();

        return is_array($row) ? $row : null;
    }
}
