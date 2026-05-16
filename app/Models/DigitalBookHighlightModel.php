<?php

namespace App\Models;

class DigitalBookHighlightModel extends BaseUuidModel
{
    protected $table = 'digital_book_highlights';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'user_id',
        'product_id',
        'page_no',
        'selected_text',
        'start_offset',
        'end_offset',
        'color',
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
