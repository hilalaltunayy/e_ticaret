<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SyncShippingCompaniesSeeder extends Seeder
{
    public function run()
    {
        $db = db_connect();

        if (! $db->tableExists('orders')) {
            echo "SyncShippingCompaniesSeeder: 'orders' tablosu bulunamadı.\n";
            return;
        }

        if (! $db->tableExists('shipping_companies')) {
            echo "SyncShippingCompaniesSeeder: 'shipping_companies' tablosu bulunamadı.\n";
            return;
        }

        $orderFields = array_map(
            static fn ($field): string => strtolower((string) $field),
            $db->getFieldNames('orders')
        );
        if (! in_array('shipping_company', $orderFields, true)) {
            echo "SyncShippingCompaniesSeeder: 'orders.shipping_company' alanı bulunamadı.\n";
            return;
        }

        $companyFields = array_map(
            static fn ($field): string => strtolower((string) $field),
            $db->getFieldNames('shipping_companies')
        );
        if (! in_array('name', $companyFields, true)) {
            echo "SyncShippingCompaniesSeeder: 'shipping_companies.name' alanı bulunamadı.\n";
            return;
        }

        $existingRows = $db->table('shipping_companies')
            ->select('name')
            ->get()
            ->getResultArray();

        $existingMap = [];
        foreach ($existingRows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $existingMap[$this->normalizeName($name)] = true;
        }

        $sourceRows = $db->table('orders')
            ->select('shipping_company')
            ->where('shipping_company IS NOT NULL', null, false)
            ->where('shipping_company !=', '')
            ->distinct()
            ->get()
            ->getResultArray();

        $now = date('Y-m-d H:i:s');
        $inserted = 0;

        foreach ($sourceRows as $row) {
            $name = trim((string) ($row['shipping_company'] ?? ''));
            if ($name === '') {
                continue;
            }

            $key = $this->normalizeName($name);
            if ($key === '' || isset($existingMap[$key])) {
                continue;
            }

            $payload = [
                'name' => $name,
            ];

            if (in_array('integration_type', $companyFields, true)) {
                $payload['integration_type'] = 'Yok';
            }
            if (in_array('note', $companyFields, true)) {
                $payload['note'] = null;
            }
            if (in_array('is_active', $companyFields, true)) {
                $payload['is_active'] = 1;
            }
            if (in_array('created_at', $companyFields, true)) {
                $payload['created_at'] = $now;
            }
            if (in_array('updated_at', $companyFields, true)) {
                $payload['updated_at'] = $now;
            }

            $ok = $db->table('shipping_companies')->insert($payload);
            if ($ok) {
                $existingMap[$key] = true;
                $inserted++;
            }
        }

        echo "SyncShippingCompaniesSeeder: {$inserted} yeni firma eklendi.\n";
    }

    private function normalizeName(string $name): string
    {
        $value = trim($name);
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }
}
