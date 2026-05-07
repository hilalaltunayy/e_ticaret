<?php

namespace App\Models;

class ReturnRequestModel extends BaseUuidModel
{
    protected $table = 'return_requests';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'order_id',
        'user_id',
        'status',
        'reason',
        'note',
        'requested_at',
        'resolved_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}
