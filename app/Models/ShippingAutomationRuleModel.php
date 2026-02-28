<?php

namespace App\Models;

class ShippingAutomationRuleModel extends BaseUuidModel
{
    protected $table = 'shipping_automation_rules';
    protected $returnType = 'array';
    protected $allowedFields = [
        'rule_type',
        'city',
        'city_slug',
        'desi_min',
        'desi_max',
        'sla_days',
        'sla_max_days',
        'supports_cod',
        'estimated_cost',
        'priority',
        'config_json',
        'primary_company_id',
        'secondary_company_id',
        'is_active',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'rule_type' => 'required|in_list[city,desi,cod,sla]',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];

    public function findActiveRules(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('priority', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }
}