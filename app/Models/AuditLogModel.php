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

    public function getLatestWithActor(int $limit = 15): array
    {
        return $this->db->table('audit_logs l')
            ->select('l.id, u.username as actor_name, l.actor_role, l.action, l.entity_type, l.entity_id, l.meta_json, l.created_at')
            ->join('users u', 'u.id = l.actor_id', 'left')
            ->orderBy('l.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
