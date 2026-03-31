<?php

namespace App\Models;

class PriceRuleModel extends BaseUuidModel
{
    protected $table = 'price_rules';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'name',
        'type',
        'value',
        'target',
        'target_id',
        'priority',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getActiveGlobalRules(): array
    {
        return $this->where('is_active', 1)
            ->where('target', 'global')
            ->orderBy('priority', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
