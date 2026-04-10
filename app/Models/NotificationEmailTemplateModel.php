<?php

namespace App\Models;

class NotificationEmailTemplateModel extends BaseUuidModel
{
    protected $table = 'notification_email_templates';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'template_name',
        'template_type',
        'subject',
        'message',
        'is_active',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function listForAdmin(): array
    {
        return $this->orderBy('updated_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
