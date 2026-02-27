<?php

namespace App\Models;

class OrderLogModel extends BaseUuidModel
{
    protected $table         = 'order_logs';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id',
        'order_id',
        'actor_user_id',
        'actor_role',
        'action',
        'from_status',
        'to_status',
        'message',
        'meta_json',
        'created_at',
    ];

    protected $useTimestamps = false;
}
