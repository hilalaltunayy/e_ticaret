<?php

namespace App\Models;

class AdminNoteModel extends BaseUuidModel
{
    protected $table = 'admin_notes';
    protected $returnType = 'array';
    protected $allowedFields = ['id', 'admin_id', 'note', 'created_at', 'updated_at', 'deleted_at'];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;

    public function getLatest(int $limit = 20): array
    {
        return $this->builder()
            ->select('id, admin_id, note, created_at, updated_at')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
