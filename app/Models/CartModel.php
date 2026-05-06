<?php

namespace App\Models;

class CartModel extends BaseUuidModel
{
    protected $table = 'carts';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'user_id',
        'status',
        'currency',
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
