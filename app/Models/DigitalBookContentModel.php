<?php

namespace App\Models;

class DigitalBookContentModel extends BaseUuidModel
{
    protected $table = 'digital_book_contents';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'product_id',
        'content_text',
        'source_type',
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

