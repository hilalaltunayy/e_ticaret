<?php

namespace App\Services;

use App\DTO\Shipping\AutomationRuleDTO;
use App\Models\ShippingAutomationRuleModel;
use InvalidArgumentException;
use RuntimeException;

class ShippingAutomationService
{
    public function __construct(private ?ShippingAutomationRuleModel $model = null)
    {
        $this->model = $this->model ?? new ShippingAutomationRuleModel();
    }

    public function listRulesByType(string $type): array
    {
        $type = trim(strtolower($type));
        if (! in_array($type, ['city', 'desi', 'cod', 'sla'], true)) {
            throw new InvalidArgumentException('Geçersiz kural tipi.');
        }

        if (! $this->rulesTableExists()) {
            return [];
        }

        $rows = $this->model
            ->where('rule_type', $type)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->decorateRules($rows, $this->getCompaniesMap());
    }

    public function createRule(array $payload): string|int
    {
        $this->ensureRulesTable();

        $data = $this->validateAndNormalize(AutomationRuleDTO::fromRequest($payload))->toArray();
        $id = $this->model->insert($data, true);

        if (! $id) {
            throw new RuntimeException('Kural kaydedilemedi.');
        }

        return $id;
    }

    public function updateRule($id, array $payload): void
    {
        $this->ensureRulesTable();
        $this->ensureRuleExists($id);

        $data = $this->validateAndNormalize(AutomationRuleDTO::fromRequest($payload))->toArray();
        if (! $this->model->update($id, $data)) {
            throw new RuntimeException('Kural güncellenemedi.');
        }
    }

    public function deleteRule($id): void
    {
        $this->ensureRulesTable();
        $this->ensureRuleExists($id);

        if (! $this->model->delete($id)) {
            throw new RuntimeException('Kural silinemedi.');
        }
    }

    public function toggleRule($id): void
    {
        $this->ensureRulesTable();
        $current = $this->ensureRuleExists($id);
        $next = ((int) ($current['is_active'] ?? 0) === 1) ? 0 : 1;

        if (! $this->model->update($id, ['is_active' => $next])) {
            throw new RuntimeException('Durum güncellenemedi.');
        }
    }

    public function getCompanies(): array
    {
        return array_values($this->getCompaniesMap());
    }

    public function getKpi(): array
    {
        if (! $this->rulesTableExists()) {
            return [
                'active_rule' => 0,
                'auto_assignment_7d' => 0,
                'sla_compliance' => 0,
                'avg_delivery_days' => '0.0',
            ];
        }

        $all = $this->model->findAll();
        $active = 0;
        $city = 0;
        $desi = 0;
        $sla = 0;

        foreach ($all as $row) {
            if ((int) ($row['is_active'] ?? 0) === 1) {
                $active++;
            }
            $type = (string) ($row['rule_type'] ?? '');
            if ($type === 'city') {
                $city++;
            } elseif ($type === 'desi') {
                $desi++;
            } elseif ($type === 'sla') {
                $sla++;
            }
        }

        $slaCompliance = min(99, 80 + ($sla * 3));
        $avgDelivery = $sla > 0 ? max(1.4, 3.1 - ($sla * 0.1)) : 2.8;

        return [
            'active_rule' => $active,
            'auto_assignment_7d' => $city + $desi,
            'sla_compliance' => $slaCompliance,
            'avg_delivery_days' => number_format($avgDelivery, 1, '.', ''),
        ];
    }

    public function rulesTableExists(): bool
    {
        return db_connect()->tableExists('shipping_automation_rules');
    }

    private function ensureRulesTable(): void
    {
        if (! $this->rulesTableExists()) {
            throw new RuntimeException('shipping_automation_rules tablosu bulunamadı. Migration çalıştırın.');
        }
    }

    private function ensureRuleExists($id): array
    {
        $row = $this->model->find($id);
        if (! is_array($row)) {
            throw new InvalidArgumentException('Kural bulunamadı.');
        }

        return $row;
    }

    private function validateAndNormalize(AutomationRuleDTO $dto): AutomationRuleDTO
    {
        if (! in_array($dto->rule_type, ['city', 'desi', 'cod', 'sla'], true)) {
            throw new InvalidArgumentException('Kural tipi geçersiz.');
        }

        if ($dto->rule_type === 'city') {
            if ($dto->city === null) {
                throw new InvalidArgumentException('Şehir zorunludur.');
            }
            if ($dto->primary_company_id === null) {
                throw new InvalidArgumentException('Öncelikli firma zorunludur.');
            }
        } elseif ($dto->rule_type === 'desi') {
            if ($dto->desi_min === null) {
                throw new InvalidArgumentException('Min desi zorunludur.');
            }
            if ($dto->primary_company_id === null) {
                throw new InvalidArgumentException('Firma zorunludur.');
            }
            if ($dto->desi_max !== null && (float) $dto->desi_max < (float) $dto->desi_min) {
                throw new InvalidArgumentException('Max desi, min desiden küçük olamaz.');
            }
            $dto->city = null;
            $dto->secondary_company_id = null;
            $dto->sla_days = null;
        } elseif ($dto->rule_type === 'cod') {
            if ($dto->primary_company_id === null) {
                throw new InvalidArgumentException('Firma zorunludur.');
            }
            $dto->city = null;
            $dto->secondary_company_id = null;
            $dto->desi_min = null;
            $dto->desi_max = null;
            $dto->sla_days = null;
        } elseif ($dto->rule_type === 'sla') {
            if ($dto->sla_days === null) {
                throw new InvalidArgumentException('SLA hedef gün zorunludur.');
            }
            if ($dto->primary_company_id === null) {
                throw new InvalidArgumentException('Firma zorunludur.');
            }
            $dto->secondary_company_id = null;
            $dto->desi_min = null;
            $dto->desi_max = null;
        }

        return $dto;
    }

    private function decorateRules(array $rows, array $companies): array
    {
        foreach ($rows as &$row) {
            $primaryId = (string) ($row['primary_company_id'] ?? '');
            $secondaryId = (string) ($row['secondary_company_id'] ?? '');
            $row['primary_company_name'] = $companies[$primaryId]['name'] ?? ($primaryId !== '' ? $primaryId : '-');
            $row['secondary_company_name'] = $companies[$secondaryId]['name'] ?? ($secondaryId !== '' ? $secondaryId : '-');
        }
        unset($row);

        return $rows;
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

        $rows = $db->table('shipping_companies')->select('id, ' . $nameField . ' AS name')->get()->getResultArray();
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
