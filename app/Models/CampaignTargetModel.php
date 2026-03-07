<?php

namespace App\Models;

class CampaignTargetModel extends BaseUuidModel
{
    protected $table = 'campaign_targets';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'campaign_id',
        'target_type',
        'target_id',
        'created_at',
        'updated_at',
    ];

    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getTargetsByCampaignIds(array $campaignIds): array
    {
        if ($campaignIds === []) {
            return [];
        }

        return $this->whereIn('campaign_id', $campaignIds)->findAll();
    }

    public function getTargetsByCampaignId(string $campaignId): array
    {
        return $this->where('campaign_id', $campaignId)->findAll();
    }
}
