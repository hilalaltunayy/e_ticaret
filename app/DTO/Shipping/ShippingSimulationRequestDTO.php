<?php

namespace App\DTO\Shipping;

use DomainException;

class ShippingSimulationRequestDTO
{
    public function __construct(
        public string $city,
        public int $slaDays,
        public bool $cod,
        public float $desi,
        public string $citySlug,
        public string $mode,
    ) {
    }

    public static function fromArray(array $data, array $defaults = []): self
    {
        $city = trim((string) ($data['city'] ?? ''));
        $slaDaysRaw = trim((string) ($data['slaDays'] ?? $data['sla_days'] ?? ($defaults['sla_days'] ?? '')));
        $desiRaw = trim((string) ($data['desi'] ?? ($defaults['desi'] ?? '')));
        $codRaw = $data['cod'] ?? false;
        $mode = trim(strtolower((string) ($data['mode'] ?? ($defaults['mode'] ?? 'dengeli'))));

        if ($city === '' || mb_strlen($city) < 2 || mb_strlen($city) > 100) {
            throw new DomainException('Sehir alani 2-100 karakter araliginda zorunludur.');
        }

        if ($slaDaysRaw === '' || ! ctype_digit($slaDaysRaw)) {
            throw new DomainException('SLA gun bilgisi zorunludur ve tam sayi olmalidir.');
        }

        $slaDays = (int) $slaDaysRaw;
        if ($slaDays < 1 || $slaDays > 30) {
            throw new DomainException('SLA gun degeri 1-30 arasinda olmalidir.');
        }

        if ($desiRaw === '' || ! is_numeric($desiRaw)) {
            throw new DomainException('Desi alani zorunludur ve sayisal olmalidir.');
        }

        $desi = (float) $desiRaw;
        if ($desi < 0 || $desi > 999) {
            throw new DomainException('Desi degeri 0-999 arasinda olmalidir.');
        }

        if (! in_array($mode, ['hizli', 'ekonomik', 'dengeli'], true)) {
            throw new DomainException('Optimizasyon modu gecersiz.');
        }

        return new self(
            city: $city,
            slaDays: $slaDays,
            cod: filter_var($codRaw, FILTER_VALIDATE_BOOLEAN),
            desi: $desi,
            citySlug: self::normalizeCity($city),
            mode: $mode,
        );
    }

    public static function normalizeCity(string $city): string
    {
        $city = trim(mb_strtolower($city, 'UTF-8'));

        $map = [
            'c' => 'c',
            'g' => 'g',
            'i' => 'i',
            'o' => 'o',
            's' => 's',
            'u' => 'u',
            'ç' => 'c',
            'ğ' => 'g',
            'ı' => 'i',
            'i̇' => 'i',
            'ö' => 'o',
            'ş' => 's',
            'ü' => 'u',
        ];

        $city = strtr($city, $map);
        $city = preg_replace('/[^a-z0-9\s-]/u', '', $city) ?? '';
        $city = preg_replace('/\s+/u', '-', $city) ?? '';

        return trim($city, '-');
    }
}
