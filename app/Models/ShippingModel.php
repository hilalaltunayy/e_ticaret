<?php

namespace App\Models;

class ShippingModel
{
    private \CodeIgniter\Database\BaseConnection $db;

    /** @var array<string, bool> */
    private array $orderFields = [];

    public function __construct()
    {
        $this->db = db_connect();

        if ($this->db->tableExists('orders')) {
            foreach ($this->db->getFieldNames('orders') as $fieldName) {
                $this->orderFields[strtolower((string) $fieldName)] = true;
            }
        }
    }

    public function datatablesList(array $params): array
    {
        $baseBuilder = $this->db->table('orders o')
            ->select($this->selectField('id', 'id'), false)
            ->select($this->selectField('order_no', 'order_no', 'o.id'), false)
            ->select($this->selectField('customer_name', 'customer_name', "'-'"), false)
            ->select($this->selectField('shipping_company', 'shipping_company', "'-'"), false)
            ->select($this->trackingExpression() . ' AS tracking_no', false)
            ->select($this->statusExpression() . ' AS shipping_status', false)
            ->select($this->updatedAtExpression() . ' AS updated_at', false)
            ->select($this->dateOnlyExpression('shipped_at') . ' AS shipped_date', false)
            ->select($this->dateOnlyExpression('delivered_at') . ' AS delivered_date', false);

        if ($this->hasField('deleted_at')) {
            $baseBuilder->where('o.deleted_at', null);
        }

        $recordsTotal = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = clone $baseBuilder;
        $this->applyDatatablesFilters($filteredBuilder, $params);
        $recordsFiltered = (clone $filteredBuilder)->countAllResults();

        $columnMap = [
            'order_no' => $this->hasField('order_no') ? 'o.order_no' : ($this->hasField('id') ? 'o.id' : 'updated_at'),
            'customer_name' => $this->hasField('customer_name') ? 'o.customer_name' : 'updated_at',
            'shipping_company' => $this->hasField('shipping_company') ? 'o.shipping_company' : 'updated_at',
            'tracking_no' => $this->hasField('tracking_number')
                ? 'o.tracking_number'
                : ($this->hasField('tracking_no') ? 'o.tracking_no' : 'updated_at'),
            'shipping_status' => $this->hasField('shipping_status')
                ? 'o.shipping_status'
                : ($this->hasField('order_status') ? 'o.order_status' : ($this->hasField('status') ? 'o.status' : 'updated_at')),
            'updated_at' => $this->sortUpdatedAtColumn(),
            'shipped_date' => $this->hasField('shipped_at') ? 'DATE(o.shipped_at)' : 'updated_at',
            'delivered_date' => $this->hasField('delivered_at') ? 'DATE(o.delivered_at)' : 'updated_at',
        ];

        $orderIndex = (int) ($params['order'][0]['column'] ?? 5);
        $orderDirRaw = strtolower((string) ($params['order'][0]['dir'] ?? 'desc'));
        $orderDir = $orderDirRaw === 'asc' ? 'ASC' : 'DESC';
        $columnName = (string) ($params['columns'][$orderIndex]['data'] ?? 'updated_at');
        $filteredBuilder->orderBy($columnMap[$columnName] ?? $columnMap['updated_at'], $orderDir);

        $length = (int) ($params['length'] ?? 10);
        $start = max(0, (int) ($params['start'] ?? 0));
        if ($length !== -1) {
            $length = max(10, min(100, $length));
            $filteredBuilder->limit($length, $start);
        }

        return [
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $filteredBuilder->get()->getResultArray(),
        ];
    }

    private function applyDatatablesFilters(\CodeIgniter\Database\BaseBuilder $builder, array $params): void
    {
        $kpiFilter = trim((string) ($params['kpi_filter'] ?? ''));
        if ($kpiFilter !== '') {
            $this->applyKpiFilter($builder, $kpiFilter);
        }

        $searchTerm = trim((string) ($params['search']['value'] ?? ''));
        if ($searchTerm !== '') {
            $hasAny = false;
            $builder->groupStart();
            foreach ([
                'order_no',
                'customer_name',
                'tracking_number',
                'tracking_no',
                'shipping_company',
            ] as $column) {
                if (! $this->hasField($column)) {
                    continue;
                }

                if ($hasAny) {
                    $builder->orLike('o.' . $column, $searchTerm);
                } else {
                    $builder->like('o.' . $column, $searchTerm);
                    $hasAny = true;
                }
            }

            if (! $hasAny && $this->hasField('id')) {
                $builder->like('o.id', $searchTerm);
                $hasAny = true;
            }

            if (! $hasAny) {
                $builder->where('1 = 0', null, false);
            }

            $builder->groupEnd();
        }

        $columnSearches = $params['columns'] ?? [];
        foreach ($columnSearches as $column) {
            $columnName = (string) ($column['name'] ?? $column['data'] ?? '');
            $columnValue = trim((string) ($column['search']['value'] ?? ''));
            if ($columnName === '' || $columnValue === '') {
                continue;
            }

            if ($columnName === 'shipping_status_raw') {
                $this->applyStatusFilter($builder, $columnValue);
                continue;
            }

            if ($columnName === 'shipped_date') {
                if ($this->hasField('shipped_at')) {
                    $builder->where('DATE(o.shipped_at)', $columnValue);
                } else {
                    $builder->where('1 = 0', null, false);
                }
                continue;
            }

            if ($columnName === 'delivered_filter') {
                if ($columnValue === '1') {
                    $this->applyDeliveredFilter($builder);
                }
                continue;
            }

            if ($columnName === 'problem_filter') {
                if ($columnValue === '1') {
                    $this->applyProblemFilter($builder);
                }
                continue;
            }
        }
    }

    private function applyKpiFilter(\CodeIgniter\Database\BaseBuilder $builder, string $kpiFilter): void
    {
        $filter = strtolower($kpiFilter);

        if ($filter === 'shipped_today') {
            if ($this->hasField('shipped_at')) {
                $builder->where('DATE(o.shipped_at)', date('Y-m-d'));
            } else {
                $builder->where('1 = 0', null, false);
            }
            return;
        }

        if ($filter === 'in_transit') {
            if ($this->hasField('shipping_status')) {
                $statuses = ['not_shipped', 'preparing', 'ready', 'shipped', 'delayed', 'cancelled'];
                $builder->groupStart()
                    ->where('o.shipping_status IS NULL', null, false)
                    ->orWhere('o.shipping_status', '')
                    ->orWhereIn('o.shipping_status', $statuses)
                    ->groupEnd();
            } else {
                $builder->where('1 = 0', null, false);
            }

            if ($this->hasField('delivered_at')) {
                $builder->where('o.delivered_at', null);
            }

            return;
        }

        if ($filter === 'delivered') {
            $this->applyDeliveredFilter($builder);
            return;
        }

        if ($filter === 'problem') {
            $this->applyProblemFilter($builder);
        }
    }

    private function hasField(string $fieldName): bool
    {
        return isset($this->orderFields[strtolower($fieldName)]);
    }

    private function selectField(string $fieldName, string $alias, string $fallbackExpr = "''"): string
    {
        if ($this->hasField($fieldName)) {
            return 'o.' . $fieldName . ' AS ' . $alias;
        }

        return $fallbackExpr . ' AS ' . $alias;
    }

    private function trackingExpression(): string
    {
        if ($this->hasField('tracking_number')) {
            return "COALESCE(NULLIF(o.tracking_number, ''), '-')";
        }

        if ($this->hasField('tracking_no')) {
            return "COALESCE(NULLIF(o.tracking_no, ''), '-')";
        }

        return "'-'";
    }

    private function statusExpression(): string
    {
        if ($this->hasField('shipping_status')) {
            return "COALESCE(NULLIF(o.shipping_status, ''), 'not_shipped')";
        }

        if ($this->hasField('order_status')) {
            return "COALESCE(NULLIF(o.order_status, ''), 'pending')";
        }

        if ($this->hasField('status')) {
            return "COALESCE(NULLIF(o.status, ''), 'pending')";
        }

        return "'not_shipped'";
    }

    private function updatedAtExpression(): string
    {
        $candidates = [];
        foreach (['updated_at', 'delivered_at', 'shipped_at', 'created_at'] as $field) {
            if ($this->hasField($field)) {
                $candidates[] = 'o.' . $field;
            }
        }

        if ($candidates === []) {
            return 'NULL';
        }

        return 'COALESCE(' . implode(', ', $candidates) . ')';
    }

    private function sortUpdatedAtColumn(): string
    {
        foreach (['updated_at', 'delivered_at', 'shipped_at', 'created_at'] as $field) {
            if ($this->hasField($field)) {
                return 'o.' . $field;
            }
        }

        return $this->hasField('id') ? 'o.id' : '1';
    }

    /**
     * @return array<string, int>
     */
    public function kpiStats(): array
    {
        $today = date('Y-m-d');
        $builder = $this->db->table('orders o')
            ->select($this->statusExpression() . ' AS shipping_status', false)
            ->select($this->selectField('shipped_at', 'shipped_at', 'NULL') . ', ' . $this->selectField('delivered_at', 'delivered_at', 'NULL'), false);

        if ($this->hasField('deleted_at')) {
            $builder->where('o.deleted_at', null);
        }

        $rows = $builder->get()->getResultArray();

        $stats = [
            'shipped_today' => 0,
            'in_transit' => 0,
            'delivered' => 0,
            'problem' => 0,
            'status_preparing' => 0,
            'status_shipped' => 0,
            'status_delivered' => 0,
            'status_returned' => 0,
            'status_delayed' => 0,
        ];

        foreach ($rows as $row) {
            $status = strtolower(trim((string) ($row['shipping_status'] ?? 'not_shipped')));
            $statusGroup = $this->statusGroup($status);
            $shippedAt = trim((string) ($row['shipped_at'] ?? ''));
            $deliveredAt = trim((string) ($row['delivered_at'] ?? ''));
            $isDelivered = $deliveredAt !== '' || $statusGroup === 'delivered';
            $isReturned = $statusGroup === 'returned';
            $isDelayed = $statusGroup === 'delayed';

            if ($shippedAt !== '' && substr($shippedAt, 0, 10) === $today) {
                $stats['shipped_today']++;
            }

            if (! $isDelivered && ! $isReturned && in_array($statusGroup, ['preparing', 'shipped', 'delayed'], true)) {
                $stats['in_transit']++;
            }

            if ($isDelivered) {
                $stats['delivered']++;
            }

            if ($isDelayed) {
                $stats['problem']++;
            }

            if ($statusGroup === 'preparing') {
                $stats['status_preparing']++;
            } elseif ($statusGroup === 'shipped') {
                $stats['status_shipped']++;
            } elseif ($statusGroup === 'delivered') {
                $stats['status_delivered']++;
            } elseif ($statusGroup === 'returned') {
                $stats['status_returned']++;
            } elseif ($statusGroup === 'delayed') {
                $stats['status_delayed']++;
            }
        }

        return $stats;
    }

    private function dateOnlyExpression(string $fieldName): string
    {
        if ($this->hasField($fieldName)) {
            return 'DATE(o.' . $fieldName . ')';
        }

        return 'NULL';
    }

    private function applyStatusFilter(\CodeIgniter\Database\BaseBuilder $builder, string $filter): void
    {
        $map = match ($filter) {
            'preparing' => ['not_shipped', 'preparing', 'ready'],
            'shipped' => ['shipped'],
            'delayed' => ['delayed', 'cancelled'],
            'in_transit' => ['not_shipped', 'preparing', 'ready', 'shipped', 'delayed', 'cancelled'],
            default => [],
        };

        if ($map === []) {
            return;
        }

        if ($this->hasField('shipping_status')) {
            $builder->whereIn('LOWER(COALESCE(NULLIF(o.shipping_status, \'\'), \'not_shipped\'))', $map, false);
        } else {
            $builder->where('1 = 0', null, false);
        }
    }

    private function applyDeliveredFilter(\CodeIgniter\Database\BaseBuilder $builder): void
    {
        $hasCondition = false;
        $builder->groupStart();
        if ($this->hasField('delivered_at')) {
            $builder->where('o.delivered_at IS NOT NULL', null, false);
            $hasCondition = true;
        }
        if ($this->hasField('shipping_status')) {
            if ($hasCondition) {
                $builder->orWhere('LOWER(COALESCE(NULLIF(o.shipping_status, \'\'), \'not_shipped\'))', 'delivered');
            } else {
                $builder->where('LOWER(COALESCE(NULLIF(o.shipping_status, \'\'), \'not_shipped\'))', 'delivered');
            }
            $hasCondition = true;
        }
        $builder->groupEnd();

        if (! $hasCondition) {
            $builder->where('1 = 0', null, false);
        }
    }

    private function applyProblemFilter(\CodeIgniter\Database\BaseBuilder $builder): void
    {
        if ($this->hasField('shipping_status')) {
            $builder->whereIn('o.shipping_status', ['delayed', 'cancelled']);
            return;
        }

        $builder->where('1 = 0', null, false);
    }

    private function statusGroup(string $status): string
    {
        return match ($status) {
            'not_shipped', 'preparing', 'ready' => 'preparing',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'returned', 'return_in_progress' => 'returned',
            'delayed', 'cancelled' => 'delayed',
            default => 'preparing',
        };
    }
}
