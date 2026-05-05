<?php

namespace App\Models;

class AdminNotificationRecordModel extends BaseUuidModel
{
    protected $table = 'admin_notification_records';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'user_id',
        'notification_key',
        'category_key',
        'severity',
        'title',
        'message',
        'status',
        'source_type',
        'fingerprint',
        'context_json',
        'created_at',
        'updated_at',
        'resolved_at',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getRecentForUser(string $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->limit(max(1, $limit))
            ->findAll();
    }
}
