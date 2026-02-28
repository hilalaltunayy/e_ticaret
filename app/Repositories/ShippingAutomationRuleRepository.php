<?php

namespace App\Repositories;

use App\Models\ShippingAutomationRuleModel;

class ShippingAutomationRuleRepository
{
    public function __construct(private ?ShippingAutomationRuleModel $model = null)
    {
        $this->model = $this->model ?? new ShippingAutomationRuleModel();
    }

    public function findActiveRules(): array
    {
        return $this->model->findActiveRules();
    }
}