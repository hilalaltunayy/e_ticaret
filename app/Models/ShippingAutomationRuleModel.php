<?php

namespace App\Models;

class ShippingAutomationRuleModel extends BaseUuidModel
{
    protected $table = 'shipping_automation_rules';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'rule_type',
        'city',
        'desi_min',
        'desi_max',
        'sla_days',
        'primary_company_id',
        'secondary_company_id',
        'is_active',
        'created_at',
        'updated_at',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'rule_type' => 'required|in_list[city,desi,cod,sla]',
        'city' => 'permit_empty|max_length[120]',
        'desi_min' => 'permit_empty|decimal',
        'desi_max' => 'permit_empty|decimal',
        'sla_days' => 'permit_empty|integer|greater_than_equal_to[1]',
        'primary_company_id' => 'permit_empty|max_length[120]',
        'secondary_company_id' => 'permit_empty|max_length[120]',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];
}
