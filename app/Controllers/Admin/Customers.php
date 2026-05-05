<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Customers extends BaseController
{
    public function index()
    {
        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $status = trim((string) ($this->request->getGet('status') ?? ''));

        return view('admin/customers/index', [
            'title' => 'Musteriler',
            'customersReport' => $this->buildCustomersReport($search, $status),
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    private function buildCustomersReport(string $search, string $status): array
    {
        $db = db_connect();
        if (! $db->tableExists('users')) {
            return [
                'summary' => $this->emptySummary(),
                'customers' => [],
                'hasStatus' => false,
                'hasRole' => false,
                'hasCreatedAt' => false,
                'lastLoginField' => null,
            ];
        }

        $fields = $db->getFieldNames('users');
        $hasStatus = in_array('status', $fields, true);
        $hasRole = in_array('role', $fields, true);
        $hasCreatedAt = in_array('created_at', $fields, true);
        $lastLoginField = $this->firstExistingField($fields, ['last_login_at', 'last_login']);
        $base = $this->customersBaseBuilder($fields);

        return [
            'summary' => [
                'total' => (clone $base)->countAllResults(),
                'active' => $hasStatus ? $this->countByStatus($fields, ['active', 'enabled']) : 0,
                'inactive' => $hasStatus ? $this->countInactiveCustomers($fields) : 0,
                'new' => $hasCreatedAt ? $this->countNewCustomersToday($fields) : 0,
            ],
            'customers' => $this->customerRows($fields, $search, $status),
            'hasStatus' => $hasStatus,
            'hasRole' => $hasRole,
            'hasCreatedAt' => $hasCreatedAt,
            'lastLoginField' => $lastLoginField,
        ];
    }

    private function customerRows(array $fields, string $search, string $status): array
    {
        $builder = $this->customersBaseBuilder($fields);
        $select = [];

        foreach (['id', 'username', 'name', 'email', 'status', 'role', 'created_at', 'last_login_at', 'last_login'] as $field) {
            if (in_array($field, $fields, true)) {
                $select[] = $field;
            }
        }

        $builder->select($select !== [] ? implode(', ', $select) : '*');

        if ($search !== '') {
            $searchable = array_values(array_filter(
                ['username', 'name', 'email'],
                fn (string $field): bool => in_array($field, $fields, true)
            ));

            if ($searchable !== []) {
                $builder->groupStart();
                foreach ($searchable as $index => $field) {
                    if ($index === 0) {
                        $builder->like($field, $search);
                        continue;
                    }

                    $builder->orLike($field, $search);
                }
                $builder->groupEnd();
            }
        }

        if ($status !== '' && in_array('status', $fields, true)) {
            $builder->where('status', $status);
        }

        $orderField = in_array('created_at', $fields, true)
            ? 'created_at'
            : (in_array('username', $fields, true) ? 'username' : 'id');

        return $builder
            ->orderBy($orderField, $orderField === 'username' ? 'ASC' : 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();
    }

    private function customersBaseBuilder(array $fields)
    {
        $builder = db_connect()->table('users');

        if (in_array('role', $fields, true)) {
            $builder->whereIn('role', ['user', 'customer']);
        }

        if (in_array('deleted_at', $fields, true)) {
            $builder->where('deleted_at', null);
        }

        return $builder;
    }

    private function countByStatus(array $fields, array $statuses): int
    {
        return (int) $this->customersBaseBuilder($fields)
            ->whereIn('status', $statuses)
            ->countAllResults();
    }

    private function countInactiveCustomers(array $fields): int
    {
        return (int) $this->customersBaseBuilder($fields)
            ->groupStart()
            ->whereNotIn('status', ['active', 'enabled'])
            ->orWhere('status', null)
            ->orWhere('status', '')
            ->groupEnd()
            ->countAllResults();
    }

    private function countNewCustomersToday(array $fields): int
    {
        return (int) $this->customersBaseBuilder($fields)
            ->where('created_at >=', date('Y-m-d 00:00:00'))
            ->where('created_at <=', date('Y-m-d 23:59:59'))
            ->countAllResults();
    }

    private function firstExistingField(array $fields, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $fields, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function emptySummary(): array
    {
        return [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'new' => 0,
        ];
    }
}
