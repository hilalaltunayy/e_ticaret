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
            ->select($this->updatedAtExpression() . ' AS updated_at', false);

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
        $searchTerm = trim((string) ($params['search']['value'] ?? ''));
        if ($searchTerm === '') {
            return;
        }

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
}
