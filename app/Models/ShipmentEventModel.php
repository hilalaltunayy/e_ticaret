<?php

namespace App\Models;

class ShipmentEventModel extends BaseUuidModel
{
    protected $table = 'shipment_events';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'shipment_id',
        'status',
        'note',
        'location',
        'event_time',
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
