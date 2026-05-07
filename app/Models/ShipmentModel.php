<?php

namespace App\Models;

class ShipmentModel extends BaseUuidModel
{
    protected $table = 'shipments';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'order_id',
        'carrier',
        'tracking_number',
        'status',
        'estimated_delivery_at',
        'shipped_at',
        'delivered_at',
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
