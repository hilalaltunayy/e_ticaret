<?php

namespace App\DTO\Marketing;

class MarketingPageSummaryDTO
{
    public int $couponCount = 0;
    public int $campaignCount = 0;
    public int $pricingRuleCount = 0;
    public bool $couponModuleReady = true;
    public bool $campaignModuleReady = false;
    public bool $pricingModuleReady = false;

    public function __construct(array $data = [])
    {
        $this->couponCount = (int) ($data['couponCount'] ?? 0);
        $this->campaignCount = (int) ($data['campaignCount'] ?? 0);
        $this->pricingRuleCount = (int) ($data['pricingRuleCount'] ?? 0);
        $this->couponModuleReady = (bool) ($data['couponModuleReady'] ?? true);
        $this->campaignModuleReady = (bool) ($data['campaignModuleReady'] ?? false);
        $this->pricingModuleReady = (bool) ($data['pricingModuleReady'] ?? false);
    }
}

