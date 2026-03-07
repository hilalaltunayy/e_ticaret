<?php

namespace App\Services;

use App\DTO\Marketing\MarketingPageSummaryDTO;
use App\Models\CampaignModel;
use App\Models\CouponModel;

class MarketingService
{
    public function getLandingSummary(): MarketingPageSummaryDTO
    {
        $couponCount = 0;
        $campaignCount = 0;
        $db = db_connect();
        if ($db->tableExists('coupons')) {
            $couponCount = (new CouponModel())->countAllResults();
        }
        if ($db->tableExists('campaigns')) {
            $campaignCount = (new CampaignModel())->countAllResults();
        }

        return new MarketingPageSummaryDTO([
            'couponCount' => $couponCount,
            'campaignCount' => $campaignCount,
            'pricingRuleCount' => 0,
            'couponModuleReady' => true,
            'campaignModuleReady' => true,
            'pricingModuleReady' => false,
        ]);
    }
}
