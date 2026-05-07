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
        'author',
        'product_image',
        'product_type',
        'unit_price',
        'quantity',
        'line_total',
        'item_status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
