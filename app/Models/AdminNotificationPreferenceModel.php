<?php

namespace App\Models;

class AdminNotificationPreferenceModel extends BaseUuidModel
{
    protected $table = 'admin_notification_preferences';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'user_id',
        'user_role',
        'notification_key',
        'category_key',
        'is_enabled',
        'threshold_value',
        'created_at',
        'updated_at',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getForUser(string $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('category_key', 'ASC')
            ->orderBy('notification_key', 'ASC')
            ->findAll();
    }
}
