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
    ) {
    }

    public static function fromArray(array $data): self
    {
        $city = trim((string) ($data['city'] ?? ''));
        $slaDaysRaw = (string) ($data['slaDays'] ?? $data['sla_days'] ?? '');
        $desiRaw = (string) ($data['desi'] ?? '');
        $codRaw = $data['cod'] ?? false;

        if ($city === '' || mb_strlen($city) < 2 || mb_strlen($city) > 100) {
            throw new DomainException('Şehir alanı 2-100 karakter aralığında zorunludur.');
        }

        if ($slaDaysRaw === '' || ! ctype_digit($slaDaysRaw)) {
            throw new DomainException('SLA gün bilgisi zorunludur ve tam sayı olmalıdır.');
        }

        $slaDays = (int) $slaDaysRaw;
        if ($slaDays < 0 || $slaDays > 30) {
            throw new DomainException('SLA gün değeri 0-30 arasında olmalıdır.');
        }

        if ($desiRaw === '' || ! is_numeric($desiRaw)) {
            throw new DomainException('Desi alanı zorunludur ve sayısal olmalıdır.');
        }

        $desi = (float) $desiRaw;
        if ($desi < 0 || $desi > 999) {
            throw new DomainException('Desi değeri 0-999 arasında olmalıdır.');
        }

        return new self(
            city: $city,
            slaDays: $slaDays,
            cod: filter_var($codRaw, FILTER_VALIDATE_BOOLEAN),
            desi: $desi,
            citySlug: self::normalizeCity($city),
        );
    }

    public static function normalizeCity(string $city): string
    {
        $city = trim(mb_strtolower($city, 'UTF-8'));

        $map = [
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