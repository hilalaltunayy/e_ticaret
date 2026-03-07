<?php

namespace App\DTO\Marketing;

class CampaignDTO
{
    public string $name = '';
    public string $slug = '';
    public string $campaign_type = 'cart_discount';
    public string $discount_type = 'percent';
    public ?float $discount_value = null;
    public ?float $min_cart_amount = null;
    public ?string $starts_at = null;
    public ?string $ends_at = null;
    public int $priority = 0;
    public int $stop_further_rules = 0;
    public int $is_active = 1;
    /** @var string[] */
    public array $category_ids = [];
    /** @var string[] */
    public array $product_ids = [];

    public static function fromRequest(array $input): self
    {
        $dto = new self();
        $dto->name = trim((string) ($input['name'] ?? ''));
        $dto->slug = trim((string) ($input['slug'] ?? ''));
        $dto->campaign_type = trim((string) ($input['campaign_type'] ?? 'cart_discount'));
        $dto->discount_type = trim((string) ($input['discount_type'] ?? 'percent'));
        $dto->discount_value = self::toNullableFloat($input['discount_value'] ?? null);
        $dto->min_cart_amount = self::toNullableFloat($input['min_cart_amount'] ?? null);
        $dto->starts_at = self::toNullableString($input['starts_at'] ?? null);
        $dto->ends_at = self::toNullableString($input['ends_at'] ?? null);
        $dto->priority = self::toInt($input['priority'] ?? 0);
        $dto->stop_further_rules = isset($input['stop_further_rules']) && (string) $input['stop_further_rules'] === '1' ? 1 : 0;
        $dto->is_active = isset($input['is_active']) && (string) $input['is_active'] === '0' ? 0 : 1;
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

    private static function toInt($value): int
    {
        return (int) trim((string) $value);
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

