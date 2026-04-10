<?php

namespace App\Models;

class NotificationDeliveryLogModel extends BaseUuidModel
{
    protected $table = 'notification_delivery_logs';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'channel',
        'recipient_email',
        'subject',
        'template_type',
        'source_type',
        'status',
        'error_message',
        'sent_at',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
