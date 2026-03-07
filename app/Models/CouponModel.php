<?php

namespace App\Models;

class CouponModel extends BaseUuidModel
{
    protected $table = 'coupons';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id',
        'code',
        'coupon_kind',
        'discount_type',
        'discount_value',
        'min_cart_amount',
        'max_usage_total',
        'max_usage_per_user',
        'starts_at',
        'ends_at',
        'is_active',
        'is_first_order_only',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function findByCode(string $code): ?array
    {
        $row = $this->where('code', $code)->first();
        return is_array($row) ? $row : null;
    }

    public function listForAdmin(): array
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }
}
