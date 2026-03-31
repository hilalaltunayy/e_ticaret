<?php

namespace App\Services;

use App\Models\PriceRuleModel;

class PriceService
{
    public function __construct(private ?PriceRuleModel $priceRuleModel = null)
    {
        $this->priceRuleModel = $this->priceRuleModel ?? new PriceRuleModel();
    }

    public function applyRules(float $basePrice, array $context): float
    {
        unset($context);

        $price = max(0, round($basePrice, 2));
        $rules = $this->priceRuleModel->getActiveGlobalRules();

        foreach ($rules as $rule) {
            $type = trim(strtolower((string) ($rule['type'] ?? '')));
            $value = (float) ($rule['value'] ?? 0);

            if ($value <= 0) {
                continue;
            }

            if ($type === 'percentage') {
                $price -= ($price * $value) / 100;
            } elseif ($type === 'fixed') {
                $price -= $value;
            }

            $price = max(0, round($price, 2));
        }

        return $price;
    }
}
