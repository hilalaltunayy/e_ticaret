<?php

namespace App\DTO\Shipping;

class AutomationRuleDTO
{
    public string $rule_type = '';
    public ?string $city = null;
    public ?string $desi_min = null;
    public ?string $desi_max = null;
    public ?string $sla_days = null;
    public ?string $primary_company_id = null;
    public ?string $secondary_company_id = null;
    public int $is_active = 1;

    public static function fromRequest(array $in): self
    {
        $dto = new self();
        $dto->rule_type = trim((string) ($in['rule_type'] ?? ''));
        $dto->is_active = isset($in['is_active']) && (string) $in['is_active'] !== '0' ? 1 : 0;
        $dto->city = self::nullIfEmpty($in['city'] ?? null);
        $dto->desi_min = self::nullIfEmpty($in['desi_min'] ?? null);
        $dto->desi_max = self::nullIfEmpty($in['desi_max'] ?? null);
        $dto->sla_days = self::nullIfEmpty($in['sla_days'] ?? null);
        $dto->primary_company_id = self::nullIfEmpty($in['primary_company_id'] ?? null);
        $dto->secondary_company_id = self::nullIfEmpty($in['secondary_company_id'] ?? null);

        return $dto;
    }

    public function toArray(): array
    {
        return [
            'rule_type' => $this->rule_type,
            'city' => $this->city,
            'desi_min' => $this->desi_min,
            'desi_max' => $this->desi_max,
            'sla_days' => $this->sla_days,
            'primary_company_id' => $this->primary_company_id,
            'secondary_company_id' => $this->secondary_company_id,
            'is_active' => $this->is_active,
        ];
    }

    private static function nullIfEmpty($value): ?string
    {
        $clean = trim((string) $value);
        return $clean === '' ? null : $clean;
    }
}
