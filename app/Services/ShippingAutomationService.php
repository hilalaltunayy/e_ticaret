<?php

namespace App\Services;

use App\DTO\Shipping\ShippingSimulationRequestDTO;
use App\Models\ShippingAutomationRuleModel;
use DomainException;

class ShippingAutomationService
{
    public function __construct(private ?ShippingAutomationRuleModel $model = null)
    {
        $this->model = $this->model ?? new ShippingAutomationRuleModel();
    }

    public function getCompanies(): array
    {
        return array_values($this->getCompaniesMap());
    }

    public function list(string $type): array
    {
        $normalizedType = $this->normalizeType($type);

        $rows = $this->model
            ->where('rule_type', $normalizedType)
            ->orderBy('id', 'DESC')
            ->findAll();

        $companiesById = $this->getCompaniesById();
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRowForTable($row, $companiesById);
        }

        return $result;
    }

    public function create(string $type, array $in): string|int
    {
        $data = $this->normalizeAndValidate($type, $in);
        $id = $this->model->insert($data, true);

        if (! $id) {
            throw new DomainException('Kural kaydedilemedi.');
        }

        return $id;
    }

    public function update(string $type, $id, array $in): void
    {
        $rule = $this->find($id);
        $normalizedType = $this->normalizeType($type);

        if (($rule['rule_type'] ?? '') !== $normalizedType) {
            throw new DomainException('Kural tipi uyuşmuyor.');
        }

        $data = $this->normalizeAndValidate($normalizedType, $in);
        if (! $this->model->update($id, $data)) {
            throw new DomainException('Kural güncellenemedi.');
        }
    }

    public function find($id): array
    {
        $row = $this->model->find($id);
        if (! is_array($row)) {
            throw new DomainException('Kural bulunamadı.');
        }

        return $row;
    }

    public function countActiveRules(): int
    {
        return (int) $this->model->where('is_active', 1)->countAllResults();
    }

    public function countAutoAssignmentsLast7Days(): int
    {
        if (! $this->hasCreatedAtField()) {
            return 0;
        }

        return (int) $this->model
            ->whereIn('rule_type', ['city', 'desi'])
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->countAllResults();
    }

    public function calculateSlaRate(): int
    {
        $total = (int) $this->model->countAllResults();
        if ($total === 0) {
            return 0;
        }

        $slaActive = (int) $this->model
            ->where('rule_type', 'sla')
            ->where('is_active', 1)
            ->countAllResults();

        return (int) round(($slaActive / $total) * 100);
    }

    public function calculateAverageDeliveryTime(): string
    {
        $slaActive = (int) $this->model
            ->where('rule_type', 'sla')
            ->where('is_active', 1)
            ->countAllResults();

        $base = 3.5;
        $delta = min($slaActive, 10) * 0.12;
        $value = max(1.8, $base - $delta);

        return number_format($value, 1, '.', '');
    }

    public function normalizeAndValidate(string $type, array $in): array
    {
        $normalizedType = $this->normalizeType($type);

        $primaryCompanyId = trim((string) ($in['primary_company_id'] ?? ''));
        $secondaryCompanyId = trim((string) ($in['secondary_company_id'] ?? ''));
        $isActiveRaw = $in['is_active'] ?? 1;
        $isActive = in_array((string) $isActiveRaw, ['0', '1'], true) ? (int) $isActiveRaw : 1;

        if ($primaryCompanyId === '') {
            throw new DomainException('Öncelikli firma zorunludur.');
        }

        $data = [
            'rule_type' => $normalizedType,
            'city' => null,
            'city_slug' => null,
            'desi_min' => null,
            'desi_max' => null,
            'sla_days' => null,
            'sla_max_days' => null,
            'supports_cod' => 0,
            'priority' => 0,
            'estimated_cost' => null,
            'primary_company_id' => $primaryCompanyId,
            'secondary_company_id' => $secondaryCompanyId !== '' ? $secondaryCompanyId : null,
            'is_active' => $isActive,
        ];

        if ($normalizedType === 'city') {
            $city = trim((string) ($in['city'] ?? ''));
            if ($city === '') {
                throw new DomainException('Şehir zorunludur.');
            }
            $data['city'] = $city;
            $data['city_slug'] = ShippingSimulationRequestDTO::normalizeCity($city);
            return $data;
        }

        if ($normalizedType === 'desi') {
            $desiMinRaw = trim((string) ($in['desi_min'] ?? ''));
            $desiMaxRaw = trim((string) ($in['desi_max'] ?? ''));

            if ($desiMinRaw === '' || $desiMaxRaw === '') {
                throw new DomainException('Desi min ve desi max zorunludur.');
            }
            if (! is_numeric($desiMinRaw) || ! is_numeric($desiMaxRaw)) {
                throw new DomainException('Desi değerleri sayısal olmalıdır.');
            }

            $desiMin = (float) $desiMinRaw;
            $desiMax = (float) $desiMaxRaw;
            if ($desiMin > $desiMax) {
                throw new DomainException('Desi min, desi max değerinden büyük olamaz.');
            }

            $data['desi_min'] = $desiMin;
            $data['desi_max'] = $desiMax;
            return $data;
        }

        if ($normalizedType === 'cod') {
            $data['supports_cod'] = 1;
            return $data;
        }

        $slaDaysRaw = trim((string) ($in['sla_days'] ?? ''));
        if ($slaDaysRaw === '') {
            throw new DomainException('SLA hedef gün zorunludur.');
        }
        if (! ctype_digit($slaDaysRaw) || (int) $slaDaysRaw <= 0) {
            throw new DomainException('SLA hedef gün pozitif bir sayı olmalıdır.');
        }

        $city = trim((string) ($in['city'] ?? ''));
        if ($city !== '') {
            $data['city'] = $city;
            $data['city_slug'] = ShippingSimulationRequestDTO::normalizeCity($city);
        }

        $data['sla_days'] = (int) $slaDaysRaw;
        $data['sla_max_days'] = (int) $slaDaysRaw;

        return $data;
    }

    public function mapRowForTable(array $row, array $companiesById): array
    {
        $primaryId = trim((string) ($row['primary_company_id'] ?? ''));
        $secondaryId = trim((string) ($row['secondary_company_id'] ?? ''));

        $row['primary_company_name'] = $companiesById[$primaryId]['name'] ?? ($primaryId !== '' ? $primaryId : '-');
        $row['secondary_company_name'] = $companiesById[$secondaryId]['name'] ?? ($secondaryId !== '' ? $secondaryId : '-');

        return $row;
    }

    private function normalizeType(string $type): string
    {
        $normalizedType = trim(strtolower($type));
        if (! in_array($normalizedType, ['city', 'desi', 'cod', 'sla'], true)) {
            throw new DomainException('Geçersiz kural tipi.');
        }

        return $normalizedType;
    }

    private function hasCreatedAtField(): bool
    {
        $db = db_connect();
        if (! $db->tableExists('shipping_automation_rules')) {
            return false;
        }

        $fields = array_map(
            static fn ($field): string => strtolower((string) $field),
            $db->getFieldNames('shipping_automation_rules')
        );

        return in_array('created_at', $fields, true);
    }

    private function getCompaniesById(): array
    {
        $map = $this->getCompaniesMap();
        $result = [];

        foreach ($map as $company) {
            $id = trim((string) ($company['id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $result[$id] = $company;
        }

        return $result;
    }

    private function getCompaniesMap(): array
    {
        $db = db_connect();
        if (! $db->tableExists('shipping_companies')) {
            return $this->fallbackCompanies();
        }

        $fields = array_map(
            static fn ($field): string => strtolower((string) $field),
            $db->getFieldNames('shipping_companies')
        );

        if (! in_array('id', $fields, true)) {
            return $this->fallbackCompanies();
        }

        $nameField = null;
        foreach (['name', 'company_name', 'title'] as $candidate) {
            if (in_array($candidate, $fields, true)) {
                $nameField = $candidate;
                break;
            }
        }

        if ($nameField === null) {
            return $this->fallbackCompanies();
        }

        $rows = $db->table('shipping_companies')
            ->select('id, ' . $nameField . ' AS name')
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return $this->fallbackCompanies();
        }

        $map = [];
        foreach ($rows as $row) {
            $id = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($id === '' || $name === '') {
                continue;
            }
            $map[$id] = ['id' => $id, 'name' => $name];
        }

        return $map !== [] ? $map : $this->fallbackCompanies();
    }

    private function fallbackCompanies(): array
    {
        return [
            'yurtici' => ['id' => 'yurtici', 'name' => 'Yurtiçi Kargo'],
            'aras' => ['id' => 'aras', 'name' => 'Aras Kargo'],
            'mng' => ['id' => 'mng', 'name' => 'MNG Kargo'],
            'surat' => ['id' => 'surat', 'name' => 'Sürat Kargo'],
            'ptt' => ['id' => 'ptt', 'name' => 'PTT Kargo'],
            'ups' => ['id' => 'ups', 'name' => 'UPS'],
        ];
    }
}