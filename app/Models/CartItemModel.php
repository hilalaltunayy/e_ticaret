<?php

namespace App\Models;

class CartItemModel extends BaseUuidModel
{
    protected $table = 'cart_items';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'cart_id',
        'product_id',
        'quantity',
        'unit_price_snapshot',
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
