<?php

namespace App\Services;

use App\DTO\Shipping\ShippingSimulationRequestDTO;
use App\Repositories\ShippingAutomationRuleRepository;

class ShippingSimulationService
{
    /** @var callable|null */
    private $companiesResolver;

    public function __construct(
        private ?ShippingAutomationRuleRepository $repository = null,
        ?callable $companiesResolver = null,
    ) {
        $this->repository = $this->repository ?? new ShippingAutomationRuleRepository();
        $this->companiesResolver = $companiesResolver;
    }

    public function simulate(ShippingSimulationRequestDTO $request): array
    {
        $rules = $this->repository->findActiveRules();
        $companies = $this->companiesResolver !== null
            ? (array) call_user_func($this->companiesResolver)
            : $this->getCompaniesById();

        $eligible = [];

        foreach ($rules as $rule) {
            $check = $this->isRuleEligible($rule, $request);
            if (! $check['eligible']) {
                continue;
            }

            $score = $this->scoreRule($rule, $request);
            $companyId = (string) ($rule['primary_company_id'] ?? '');

            $eligible[] = [
                'rule' => $rule,
                'company_id' => $companyId,
                'company_name' => $companies[$companyId]['name'] ?? ($companyId !== '' ? $companyId : '-'),
                'checks' => $check,
                'score' => $score,
            ];
        }

        usort($eligible, function (array $a, array $b): int {
            if ($a['score']['cost'] !== $b['score']['cost']) {
                return $a['score']['cost'] <=> $b['score']['cost'];
            }

            if ($a['score']['sla'] !== $b['score']['sla']) {
                return $a['score']['sla'] <=> $b['score']['sla'];
            }

            return $b['score']['priority'] <=> $a['score']['priority'];
        });

        $selected = $eligible[0] ?? null;

        return [
            'ok' => true,
            'request' => [
                'city' => $request->city,
                'city_slug' => $request->citySlug,
                'sla_days' => $request->slaDays,
                'cod' => $request->cod,
                'desi' => $request->desi,
            ],
            'selected' => $selected ? [
                'company_id' => $selected['company_id'],
                'company_name' => $selected['company_name'],
                'rule_id' => $selected['rule']['id'] ?? null,
                'reason' => [
                    'city_match' => $selected['checks']['city'],
                    'sla_match' => $selected['checks']['sla'],
                    'cod_match' => $selected['checks']['cod'],
                    'desi_match' => $selected['checks']['desi'],
                    'cost' => $selected['score']['cost'],
                    'sla' => $selected['score']['sla'],
                    'priority' => $selected['score']['priority'],
                ],
            ] : null,
            'top_candidates' => array_map(function (array $row): array {
                return [
                    'company_name' => $row['company_name'],
                    'rule_id' => $row['rule']['id'] ?? null,
                    'cost' => $row['score']['cost'],
                    'sla' => $row['score']['sla'],
                    'priority' => $row['score']['priority'],
                ];
            }, array_slice($eligible, 0, 3)),
        ];
    }

    public function normalizeCity(string $city): string
    {
        return ShippingSimulationRequestDTO::normalizeCity($city);
    }

    public function isRuleEligible(array $rule, ShippingSimulationRequestDTO $request): array
    {
        $ruleCity = trim((string) ($rule['city'] ?? ''));
        $ruleCitySlug = trim((string) ($rule['city_slug'] ?? ''));
        if ($ruleCitySlug === '' && $ruleCity !== '') {
            $ruleCitySlug = $this->normalizeCity($ruleCity);
        }

        $cityPass = ($ruleCitySlug === '' || $ruleCitySlug === $request->citySlug);

        $maxSla = $this->resolveSla($rule);
        $slaPass = ($maxSla === null || $maxSla >= $request->slaDays);

        $supportsCod = $this->resolveSupportsCod($rule);
        $codPass = ($supportsCod === $request->cod);

        $desiPass = $this->isDesiInRange($rule, $request->desi);

        return [
            'eligible' => $cityPass && $slaPass && $codPass && $desiPass,
            'city' => $cityPass,
            'sla' => $slaPass,
            'cod' => $codPass,
            'desi' => $desiPass,
        ];
    }

    public function scoreRule(array $rule, ShippingSimulationRequestDTO $request): array
    {
        unset($request);

        $cost = $this->resolveCost($rule);
        $sla = $this->resolveSla($rule) ?? 999;
        $priority = $this->resolvePriority($rule);

        return [
            'cost' => $cost,
            'sla' => $sla,
            'priority' => $priority,
        ];
    }

    private function isDesiInRange(array $rule, float $desi): bool
    {
        $min = $this->toFloatOrNull($rule['desi_min'] ?? null);
        $max = $this->toFloatOrNull($rule['desi_max'] ?? null);

        if ($min !== null && $desi < $min) {
            return false;
        }

        if ($max !== null && $desi > $max) {
            return false;
        }

        return true;
    }

    private function resolveSupportsCod(array $rule): bool
    {
        if (array_key_exists('supports_cod', $rule)) {
            return (int) $rule['supports_cod'] === 1;
        }

        $type = strtolower(trim((string) ($rule['rule_type'] ?? '')));
        if ($type === 'cod') {
            return true;
        }

        $config = $this->readConfig($rule);
        if (array_key_exists('supports_cod', $config)) {
            return filter_var($config['supports_cod'], FILTER_VALIDATE_BOOLEAN);
        }

        return false;
    }

    private function resolveSla(array $rule): ?int
    {
        foreach (['sla_max_days', 'sla_days'] as $field) {
            if (! array_key_exists($field, $rule)) {
                continue;
            }
            $value = $rule[$field];
            if ($value === null || $value === '') {
                continue;
            }
            return (int) $value;
        }

        $config = $this->readConfig($rule);
        if (isset($config['sla_max_days']) && is_numeric((string) $config['sla_max_days'])) {
            return (int) $config['sla_max_days'];
        }

        return null;
    }

    private function resolvePriority(array $rule): int
    {
        if (isset($rule['priority']) && is_numeric((string) $rule['priority'])) {
            return (int) $rule['priority'];
        }

        $config = $this->readConfig($rule);
        if (isset($config['priority']) && is_numeric((string) $config['priority'])) {
            return (int) $config['priority'];
        }

        return 0;
    }

    private function resolveCost(array $rule): float
    {
        if (isset($rule['estimated_cost']) && is_numeric((string) $rule['estimated_cost'])) {
            return (float) $rule['estimated_cost'];
        }

        $config = $this->readConfig($rule);
        foreach (['cost', 'estimated_cost'] as $field) {
            if (isset($config[$field]) && is_numeric((string) $config[$field])) {
                return (float) $config[$field];
            }
        }

        return 999999.0;
    }

    private function readConfig(array $rule): array
    {
        $raw = $rule['config_json'] ?? null;
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function toFloatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric((string) $value)) {
            return null;
        }

        return (float) $value;
    }

    private function getCompaniesById(): array
    {
        $db = db_connect();
        if (! $db->tableExists('shipping_companies')) {
            return $this->fallbackCompanies();
        }

        $fields = array_map(
            static fn ($field): string => strtolower((string) $field),
            $db->getFieldNames('shipping_companies')
        );

        $nameField = null;
        foreach (['name', 'company_name', 'title'] as $candidate) {
            if (in_array($candidate, $fields, true)) {
                $nameField = $candidate;
                break;
            }
        }

        if (! in_array('id', $fields, true) || $nameField === null) {
            return $this->fallbackCompanies();
        }

        $rows = $db->table('shipping_companies')
            ->select('id, ' . $nameField . ' AS name')
            ->get()
            ->getResultArray();

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
