<?php

namespace App\Models;

class CampaignModel extends BaseUuidModel
{
    protected $table = 'campaigns';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'name',
        'slug',
        'campaign_type',
        'discount_type',
        'discount_value',
        'min_cart_amount',
        'starts_at',
        'ends_at',
        'priority',
        'stop_further_rules',
        'is_active',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function listForAdmin(): array
    {
        return $this->orderBy('priority', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}

