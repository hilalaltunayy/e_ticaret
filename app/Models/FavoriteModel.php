<?php

namespace App\Models;

class FavoriteModel extends BaseUuidModel
{
    protected $table = 'favorites';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'user_id',
        'product_id',
        'favorited_price',
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
