<?php

namespace App\Models;

class BannerModel extends BaseUuidModel
{
    protected $table = 'banners';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'banner_name',
        'banner_type',
        'title',
        'subtitle',
        'image_path',
        'button_text',
        'button_link',
        'display_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function listForAdmin(): array
    {
        return $this->orderBy('display_order', 'ASC')
            ->orderBy('updated_at', 'DESC')
            ->findAll();
    }
}
