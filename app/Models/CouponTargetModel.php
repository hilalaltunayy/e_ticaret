<?php

namespace App\Models;

class CouponTargetModel extends BaseUuidModel
{
    protected $table = 'coupon_targets';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'coupon_id',
        'target_type',
        'target_id',
        'created_at',
        'updated_at',
    ];

    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getTargetsByCouponIds(array $couponIds): array
    {
        if ($couponIds === []) {
            return [];
        }

        return $this->whereIn('coupon_id', $couponIds)->findAll();
    }

    public function getTargetsByCouponId(string $couponId): array
    {
        return $this->where('coupon_id', $couponId)->findAll();
    }
}

