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

        if ($rules === []) {
            return $this->buildEmptyResponse(
                $request,
                'Aktif otomasyon kurali bulunamadi. Saglikli bir oneri icin sehir, desi, kapida odeme ve SLA kurallari tanimlanmalidir.',
                [],
                ['city kurali', 'desi kurali', 'cod kurali', 'sla kurali']
            );
        }

        $candidateIds = $this->collectCandidateIds($rules, $companies);
        $candidates = [];

        foreach ($candidateIds as $companyId) {
            $candidate = $this->analyzeCandidate($companyId, $companies, $rules, $request);
            if ($candidate['matched_rule_count'] === 0) {
                continue;
            }
            $candidates[] = $candidate;
        }

        if ($candidates === []) {
            return $this->buildEmptyResponse(
                $request,
                'Girilen kriterlere tam veya kismi uyum saglayan aktif kural bulunamadi. Sehir, desi, kapida odeme veya SLA kurallari genisletilmelidir.',
                [],
                $this->buildNeedsDataList($rules)
            );
        }

        $candidates = $this->applyCostScores($candidates);
        foreach ($candidates as &$candidate) {
            $candidate['total_score'] = $this->calculateTotalScore($candidate, $request->mode);
        }
        unset($candidate);

        usort($candidates, function (array $left, array $right): int {
            if ($left['total_score'] !== $right['total_score']) {
                return $right['total_score'] <=> $left['total_score'];
            }

            if ($left['matched_rule_count'] !== $right['matched_rule_count']) {
                return $right['matched_rule_count'] <=> $left['matched_rule_count'];
            }

            if ($left['priority'] !== $right['priority']) {
                return $right['priority'] <=> $left['priority'];
            }

            return strcmp((string) $left['company_name'], (string) $right['company_name']);
        });

        $selected = $candidates[0];
        $topCandidates = array_slice($candidates, 0, 3);

        return [
            'request' => [
                'city' => $request->city,
                'city_slug' => $request->citySlug,
                'sla_days' => $request->slaDays,
                'cod' => $request->cod,
                'desi' => $request->desi,
                'mode' => $request->mode,
            ],
            'selected' => [
                'company_id' => $selected['company_id'],
                'company_name' => $selected['company_name'],
                'summary' => $this->buildSummary($selected, $request),
                'reason_lines' => $selected['reason_lines'],
                'price_label' => $selected['price_label'],
                'score' => $selected['total_score'],
                'matched_rule_types' => $selected['matched_rule_types'],
                'reason' => [
                    'city_match' => in_array('city', $selected['matched_rule_types'], true),
                    'sla_match' => in_array('sla', $selected['matched_rule_types'], true),
                    'cod_match' => ! $request->cod || in_array('cod', $selected['matched_rule_types'], true),
                    'desi_match' => in_array('desi', $selected['matched_rule_types'], true),
                    'cost' => $selected['price_label'],
                    'sla' => $selected['sla_display'],
                    'priority' => $selected['priority'],
                ],
            ],
            'top_candidates' => array_map(function (array $candidate): array {
                return [
                    'company_name' => $candidate['company_name'],
                    'score' => $candidate['total_score'],
                    'price_label' => $candidate['price_label'],
                    'matched_rule_count' => $candidate['matched_rule_count'],
                    'matched_rule_types' => $candidate['matched_rule_types'],
                    'cost' => $candidate['price_label'],
                    'sla' => $candidate['sla_display'],
                    'priority' => $candidate['priority'],
                ];
            }, $topCandidates),
            'fallback_message' => null,
            'needs_data' => $this->buildNeedsDataList($rules),
        ];
    }

    private function analyzeCandidate(
        string $companyId,
        array $companies,
        array $rules,
        ShippingSimulationRequestDTO $request
    ): array {
        $company = $companies[$companyId] ?? ['id' => $companyId, 'name' => $companyId];
        $cityRule = $this->findBestRuleMatch($rules, 'city', $companyId, function (array $rule) use ($request): bool {
            return $this->matchesCity($rule, $request);
        });
        $desiRule = $this->findBestRuleMatch($rules, 'desi', $companyId, function (array $rule) use ($request): bool {
            return $this->matchesDesi($rule, $request->desi);
        });
        $slaRule = $this->findBestRuleMatch($rules, 'sla', $companyId, function (array $rule) use ($request): bool {
            return $this->matchesSla($rule, $request);
        });
        $codRule = $request->cod
            ? $this->findBestRuleMatch($rules, 'cod', $companyId, static fn (array $rule): bool => (int) ($rule['supports_cod'] ?? 0) === 1)
            : null;

        $reasonLines = [];
        $matchedRuleTypes = [];
        $matchedRuleCount = 0;

        $cityScore = 0.0;
        if ($cityRule !== null) {
            $cityScore = $cityRule['role'] === 'primary' ? 1.0 : 0.65;
            $matchedRuleTypes[] = 'city';
            $matchedRuleCount++;
            $reasonLines[] = 'Sehir kurali eslesmesi bulundu.';
        }

        $desiScore = 0.0;
        if ($desiRule !== null) {
            $desiScore = $desiRule['role'] === 'primary' ? 1.0 : 0.65;
            $matchedRuleTypes[] = 'desi';
            $matchedRuleCount++;
            $reasonLines[] = 'Desi araligi kurali ile uyumlu.';
        }

        $slaScore = 0.0;
        if ($slaRule !== null) {
            $ruleSla = max(1, (int) ($slaRule['rule']['sla_max_days'] ?? $slaRule['rule']['sla_days'] ?? $request->slaDays));
            $gap = max(0, $ruleSla - $request->slaDays);
            $slaScore = max(0.2, 1 - ($gap * 0.25));
            if ($ruleSla <= $request->slaDays) {
                $slaScore = min(1.0, $slaScore + 0.15);
            }
            $matchedRuleTypes[] = 'sla';
            $matchedRuleCount++;
            $reasonLines[] = 'SLA hedefi ile uyumlu kural bulundu.';
        }

        $codScore = $request->cod ? 0.0 : 0.5;
        if ($request->cod && $codRule !== null) {
            $codScore = $codRule['role'] === 'primary' ? 1.0 : 0.65;
            $matchedRuleTypes[] = 'cod';
            $matchedRuleCount++;
            $reasonLines[] = 'Kapida odeme destegi bulunan kural eslesti.';
        }

        $priority = max(
            $this->resolvePriority($cityRule['rule'] ?? []),
            $this->resolvePriority($desiRule['rule'] ?? []),
            $this->resolvePriority($slaRule['rule'] ?? []),
            $this->resolvePriority($codRule['rule'] ?? [])
        );

        $estimatedPrice = $this->pickBestEstimatedPrice([
            $cityRule['rule'] ?? null,
            $desiRule['rule'] ?? null,
            $slaRule['rule'] ?? null,
            $codRule['rule'] ?? null,
        ], $request->desi);

        if ($estimatedPrice === null) {
            $reasonLines[] = 'Fiyat verisi olmadigi icin maliyet karsilastirmasi sinirli yapildi.';
        } else {
            $reasonLines[] = 'Tahmini fiyat verisi mevcut.';
        }

        return [
            'company_id' => $companyId,
            'company_name' => (string) ($company['name'] ?? $companyId),
            'matched_rule_count' => $matchedRuleCount,
            'matched_rule_types' => array_values(array_unique($matchedRuleTypes)),
            'reason_lines' => $reasonLines,
            'city_score' => $cityScore,
            'desi_score' => $desiScore,
            'sla_score' => $slaScore,
            'cod_score' => $codScore,
            'cost_score' => 0.0,
            'priority_score' => $priority > 0 ? min(1.0, $priority / 100) : 0.0,
            'priority' => $priority,
            'estimated_price' => $estimatedPrice,
            'sla_display' => $slaRule !== null
                ? (string) ((int) ($slaRule['rule']['sla_max_days'] ?? $slaRule['rule']['sla_days'] ?? 0))
                : 'Kural yok',
            'price_label' => $estimatedPrice === null
                ? 'Fiyat verisi yok'
                : number_format($estimatedPrice, 2, ',', '.') . ' TL (tahmini)',
        ];
    }

    private function calculateTotalScore(array $candidate, string $mode): int
    {
        $weights = match ($mode) {
            'hizli' => ['sla' => 40, 'city' => 20, 'desi' => 10, 'cod' => 10, 'priority' => 10, 'cost' => 10],
            'ekonomik' => ['sla' => 10, 'city' => 10, 'desi' => 15, 'cod' => 10, 'priority' => 20, 'cost' => 35],
            default => ['sla' => 25, 'city' => 20, 'desi' => 15, 'cod' => 10, 'priority' => 10, 'cost' => 20],
        };

        $score = 0.0;
        $score += $candidate['sla_score'] * $weights['sla'];
        $score += $candidate['city_score'] * $weights['city'];
        $score += $candidate['desi_score'] * $weights['desi'];
        $score += $candidate['cod_score'] * $weights['cod'];
        $score += $candidate['priority_score'] * $weights['priority'];
        $score += $candidate['cost_score'] * $weights['cost'];

        return (int) round($score);
    }

    private function applyCostScores(array $candidates): array
    {
        $prices = array_values(array_filter(array_map(
            static fn (array $candidate): ?float => $candidate['estimated_price'],
            $candidates
        ), static fn (?float $price): bool => $price !== null));

        if ($prices === []) {
            return $candidates;
        }

        $min = min($prices);
        $max = max($prices);

        foreach ($candidates as &$candidate) {
            if ($candidate['estimated_price'] === null) {
                $candidate['cost_score'] = 0.0;
                continue;
            }

            if ($max === $min) {
                $candidate['cost_score'] = 1.0;
                continue;
            }

            $candidate['cost_score'] = ($max - $candidate['estimated_price']) / ($max - $min);
        }
        unset($candidate);

        return $candidates;
    }

    private function findBestRuleMatch(array $rules, string $type, string $companyId, callable $matcher): ?array
    {
        $matches = [];

        foreach ($rules as $rule) {
            if (($rule['rule_type'] ?? '') !== $type) {
                continue;
            }
            if (! $matcher($rule)) {
                continue;
            }

            $primaryId = trim((string) ($rule['primary_company_id'] ?? ''));
            $secondaryId = trim((string) ($rule['secondary_company_id'] ?? ''));
            if ($primaryId === $companyId) {
                $matches[] = ['rule' => $rule, 'role' => 'primary'];
            } elseif ($secondaryId === $companyId) {
                $matches[] = ['rule' => $rule, 'role' => 'secondary'];
            }
        }

        if ($matches === []) {
            return null;
        }

        usort($matches, function (array $left, array $right): int {
            $leftPriority = $this->resolvePriority($left['rule']);
            $rightPriority = $this->resolvePriority($right['rule']);
            if ($leftPriority !== $rightPriority) {
                return $rightPriority <=> $leftPriority;
            }

            if ($left['role'] !== $right['role']) {
                return $left['role'] === 'primary' ? -1 : 1;
            }

            return strcmp((string) ($left['rule']['id'] ?? ''), (string) ($right['rule']['id'] ?? ''));
        });

        return $matches[0];
    }

    private function matchesCity(array $rule, ShippingSimulationRequestDTO $request): bool
    {
        $citySlug = trim((string) ($rule['city_slug'] ?? ''));
        if ($citySlug === '') {
            $city = trim((string) ($rule['city'] ?? ''));
            $citySlug = $city !== '' ? ShippingSimulationRequestDTO::normalizeCity($city) : '';
        }

        return $citySlug !== '' && $citySlug === $request->citySlug;
    }

    private function matchesDesi(array $rule, float $desi): bool
    {
        $min = $this->toFloatOrNull($rule['desi_min'] ?? null);
        $max = $this->toFloatOrNull($rule['desi_max'] ?? null);

        if ($min !== null && $desi < $min) {
            return false;
        }

        if ($max !== null && $desi > $max) {
            return false;
        }

        return $min !== null || $max !== null;
    }

    private function matchesSla(array $rule, ShippingSimulationRequestDTO $request): bool
    {
        $ruleSla = (int) ($rule['sla_max_days'] ?? $rule['sla_days'] ?? 0);
        if ($ruleSla <= 0) {
            return false;
        }

        $city = trim((string) ($rule['city'] ?? ''));
        if ($city === '' && trim((string) ($rule['city_slug'] ?? '')) === '') {
            return $ruleSla <= $request->slaDays;
        }

        return $this->matchesCity($rule, $request) && $ruleSla <= $request->slaDays;
    }

    private function pickBestEstimatedPrice(array $rules, float $desi): ?float
    {
        $prices = [];
        foreach ($rules as $rule) {
            if (! is_array($rule)) {
                continue;
            }
            $price = $this->resolveCost($rule, $desi);
            if ($price !== null) {
                $prices[] = $price;
            }
        }

        return $prices === [] ? null : min($prices);
    }

    private function resolvePriority(array $rule): int
    {
        $priority = $rule['priority'] ?? null;
        if ($priority !== null && is_numeric((string) $priority)) {
            return (int) $priority;
        }

        $config = $this->readConfig($rule);
        if (isset($config['priority']) && is_numeric((string) $config['priority'])) {
            return (int) $config['priority'];
        }

        return 0;
    }

    private function resolveCost(array $rule, float $desi): ?float
    {
        if (isset($rule['estimated_cost']) && is_numeric((string) $rule['estimated_cost'])) {
            return round((float) $rule['estimated_cost'], 2);
        }

        $config = $this->readConfig($rule);
        $hasBase = isset($config['base_cost']) && is_numeric((string) $config['base_cost']);
        $hasPerDesi = isset($config['cost_per_desi']) && is_numeric((string) $config['cost_per_desi']);

        if (! $hasBase && ! $hasPerDesi) {
            return null;
        }

        $base = $hasBase ? (float) $config['base_cost'] : 0.0;
        $perDesi = $hasPerDesi ? (float) $config['cost_per_desi'] : 0.0;

        return round($base + ($desi * $perDesi), 2);
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

    private function collectCandidateIds(array $rules, array $companies): array
    {
        $ids = [];

        foreach ($rules as $rule) {
            foreach (['primary_company_id', 'secondary_company_id'] as $field) {
                $id = trim((string) ($rule[$field] ?? ''));
                if ($id !== '') {
                    $ids[$id] = true;
                }
            }
        }

        foreach ($companies as $id => $company) {
            $candidateId = trim((string) ($company['id'] ?? $id));
            if ($candidateId !== '') {
                $ids[$candidateId] = true;
            }
        }

        return array_keys($ids);
    }

    private function buildSummary(array $selected, ShippingSimulationRequestDTO $request): string
    {
        $parts = [];
        if (in_array('city', $selected['matched_rule_types'], true)) {
            $parts[] = 'sehir';
        }
        if (in_array('desi', $selected['matched_rule_types'], true)) {
            $parts[] = 'desi';
        }
        if (in_array('sla', $selected['matched_rule_types'], true)) {
            $parts[] = 'SLA';
        }
        if ($request->cod && in_array('cod', $selected['matched_rule_types'], true)) {
            $parts[] = 'kapida odeme';
        }

        $criteria = $parts === [] ? 'mevcut kurallara' : implode(', ', $parts) . ' kriterlerine';

        return $selected['company_name'] . ' secilen ' . $criteria . ' gore en uygun secenektir.';
    }

    private function buildNeedsDataList(array $rules): array
    {
        $types = [];
        foreach ($rules as $rule) {
            $type = trim((string) ($rule['rule_type'] ?? ''));
            if ($type !== '') {
                $types[$type] = true;
            }
        }

        $needs = [];
        foreach (['city', 'desi', 'cod', 'sla'] as $type) {
            if (! isset($types[$type])) {
                $needs[] = $type . ' kurali';
            }
        }

        return $needs;
    }

    private function buildEmptyResponse(
        ShippingSimulationRequestDTO $request,
        string $message,
        array $candidates,
        array $needsData
    ): array
    {
        return [
            'request' => [
                'city' => $request->city,
                'city_slug' => $request->citySlug,
                'sla_days' => $request->slaDays,
                'cod' => $request->cod,
                'desi' => $request->desi,
                'mode' => $request->mode,
            ],
            'selected' => null,
            'top_candidates' => $candidates,
            'fallback_message' => $message,
            'needs_data' => $needsData,
        ];
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
            'yurtici' => ['id' => 'yurtici', 'name' => 'Yurtici Kargo'],
            'aras' => ['id' => 'aras', 'name' => 'Aras Kargo'],
            'mng' => ['id' => 'mng', 'name' => 'MNG Kargo'],
            'surat' => ['id' => 'surat', 'name' => 'Surat Kargo'],
            'ptt' => ['id' => 'ptt', 'name' => 'PTT Kargo'],
            'ups' => ['id' => 'ups', 'name' => 'UPS'],
        ];
    }
}
