<?php

namespace App\DTO\Marketing;

class CouponDTO
{
    public string $code = '';
    public string $coupon_kind = 'discount';
    public string $discount_type = 'none';
    public ?float $discount_value = null;
    public ?float $min_cart_amount = null;
    public ?int $max_usage_total = null;
    public ?int $max_usage_per_user = null;
    public ?string $starts_at = null;
    public ?string $ends_at = null;
    public int $is_active = 1;
    public int $is_first_order_only = 0;
    /** @var string[] */
    public array $category_ids = [];
    /** @var string[] */
    public array $product_ids = [];

    public static function fromRequest(array $input): self
    {
        $dto = new self();
        $dto->code = trim((string) ($input['code'] ?? ''));
        $dto->coupon_kind = trim((string) ($input['coupon_kind'] ?? 'discount'));
        $dto->discount_type = trim((string) ($input['discount_type'] ?? 'none'));
        $dto->discount_value = self::toNullableFloat($input['discount_value'] ?? null);
        $dto->min_cart_amount = self::toNullableFloat($input['min_cart_amount'] ?? null);
        $dto->max_usage_total = self::toNullableInt($input['max_usage_total'] ?? null);
        $dto->max_usage_per_user = self::toNullableInt($input['max_usage_per_user'] ?? null);
        $dto->starts_at = self::toNullableString($input['starts_at'] ?? null);
        $dto->ends_at = self::toNullableString($input['ends_at'] ?? null);
        $dto->is_active = isset($input['is_active']) && (string) $input['is_active'] === '0' ? 0 : 1;
        $dto->is_first_order_only = isset($input['is_first_order_only']) && (string) $input['is_first_order_only'] === '1' ? 1 : 0;
        $dto->category_ids = self::normalizeIdList($input['category_ids'] ?? []);
        $dto->product_ids = self::normalizeIdList($input['product_ids'] ?? []);

        return $dto;
    }

    private static function toNullableString($value): ?string
    {
        $clean = trim((string) $value);
        return $clean === '' ? null : $clean;
    }

    private static function toNullableFloat($value): ?float
    {
        $clean = trim((string) $value);
        if ($clean === '') {
            return null;
        }
        return (float) $clean;
    }

    private static function toNullableInt($value): ?int
    {
        $clean = trim((string) $value);
        if ($clean === '') {
            return null;
        }
        return (int) $clean;
    }

    /**
     * @param mixed $raw
     * @return string[]
     */
    private static function normalizeIdList($raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $items = [];
        foreach ($raw as $item) {
            $id = trim((string) $item);
            if ($id !== '') {
                $items[] = $id;
            }
        }
        return array_values(array_unique($items));
    }
}

