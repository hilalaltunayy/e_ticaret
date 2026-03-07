<?php

namespace App\Models;

class CouponRedemptionModel extends BaseUuidModel
{
    protected $table = 'coupon_redemptions';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'coupon_id',
        'user_id',
        'order_id',
        'coupon_code_snapshot',
        'discount_amount',
        'created_at',
    ];

    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
}

