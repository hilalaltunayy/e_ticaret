<?php

namespace App\Models;

class AuditLogModel extends BaseUuidModel
{
    protected $table = 'audit_logs';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id', 'actor_id', 'actor_role', 'action', 'entity_type', 'entity_id', 'meta_json', 'created_at', 'updated_at', 'deleted_at'
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;

    public function getLatestWithActor(int $limit = 15, ?string $createdFrom = null): array
    {
        $builder = $this->db->table('audit_logs l')
            ->select('l.id, u.username as actor_name, u.email as actor_email, l.actor_role, l.action, l.entity_type, l.entity_id, l.meta_json, l.created_at')
            ->join('users u', 'u.id = l.actor_id', 'left');

        if ($createdFrom !== null && trim($createdFrom) !== '') {
            $builder->where('l.created_at >=', $createdFrom);
        }

        return $builder
            ->orderBy('l.created_at', 'DESC')
            ->orderBy('l.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
