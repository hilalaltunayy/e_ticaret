<?php

namespace App\Models;

class OrderItemModel extends BaseUuidModel
{
    protected $table         = 'order_items';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id',
        'order_id',
        'product_id',
        'product_name_snapshot',
        'unit_price',
        'quantity',
        'line_total',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
