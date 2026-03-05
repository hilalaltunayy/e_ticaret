<?php

namespace App\Models;

class PackingSessionModel extends BaseUuidModel
{
    protected $table         = 'packing_sessions';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id',
        'order_id',
        'package_code',
        'status',
        'expected_items_json',
        'scanned_items_json',
        'verified_at',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
