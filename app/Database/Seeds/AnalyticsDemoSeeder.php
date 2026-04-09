<?php

namespace App\Database\Seeds;

use App\Models\BaseUuidModel;
use CodeIgniter\Database\Seeder;

class AnalyticsDemoSeeder extends Seeder
{
    private const PRODUCT_DEFINITIONS = [
        ['key' => 'roman_print', 'name' => 'Analytics Demo Roman Basili', 'category' => 'Roman', 'type' => 'physical', 'price' => 185.00, 'author' => 'Analytics Demo Author 1'],
        ['key' => 'roman_digital', 'name' => 'Analytics Demo Roman Dijital', 'category' => 'Roman', 'type' => 'digital', 'price' => 95.00, 'author' => 'Analytics Demo Author 1'],
        ['key' => 'science_print', 'name' => 'Analytics Demo Bilim Basili', 'category' => 'Bilim', 'type' => 'physical', 'price' => 210.00, 'author' => 'Analytics Demo Author 2'],
        ['key' => 'software_digital', 'name' => 'Analytics Demo Yazilim Dijital', 'category' => 'Yazilim', 'type' => 'digital', 'price' => 145.00, 'author' => 'Analytics Demo Author 3'],
        ['key' => 'children_print', 'name' => 'Analytics Demo Cocuk Basili', 'category' => 'Cocuk', 'type' => 'physical', 'price' => 120.00, 'author' => 'Analytics Demo Author 4'],
    ];

    public function run()
    {
        if (! $this->db->tableExists('products') || ! $this->db->tableExists('orders')) {
            echo "AnalyticsDemoSeeder: products/orders tablolari bulunamadi.\n";

            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->ensureTypes();
        $categoryMap = $this->ensureCategories();
        $authorMap = $this->ensureAuthors($now);
        $typeMap = $this->getTypeMap();
        $productMap = $this->ensureProducts($now, $categoryMap, $authorMap, $typeMap);
        $createdOrderCount = $this->ensureOrders($now, $productMap);

        echo "AnalyticsDemoSeeder: {$createdOrderCount} analytics demo siparisi hazirlandi.\n";
    }

    private function ensureTypes(): void
    {
        if (! $this->db->tableExists('types')) {
            return;
        }

        foreach (['physical', 'digital'] as $typeName) {
            $exists = $this->db->table('types')->select('id')->where('name', $typeName)->get()->getRowArray();
            if ($exists) {
                continue;
            }

            $this->db->table('types')->insert([
                'id' => BaseUuidModel::uuidV4(),
                'name' => $typeName,
            ]);
        }
    }

    private function ensureCategories(): array
    {
        if (! $this->db->tableExists('categories')) {
            return [];
        }

        foreach (['Roman', 'Bilim', 'Yazilim', 'Cocuk'] as $categoryName) {
            $exists = $this->db->table('categories')->select('id')->where('category_name', $categoryName)->get()->getRowArray();
            if ($exists) {
                continue;
            }

            $this->db->table('categories')->insert([
                'id' => BaseUuidModel::uuidV4(),
                'category_name' => $categoryName,
            ]);
        }

        $rows = $this->db->table('categories')->select('id, category_name')->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['category_name']] = (string) $row['id'];
        }

        return $map;
    }

    private function ensureAuthors(string $now): array
    {
        if (! $this->db->tableExists('authors')) {
            return [];
        }

        foreach (['Analytics Demo Author 1', 'Analytics Demo Author 2', 'Analytics Demo Author 3', 'Analytics Demo Author 4'] as $authorName) {
            $exists = $this->db->table('authors')->select('id')->where('name', $authorName)->get()->getRowArray();
            if ($exists) {
                continue;
            }

            $payload = [
                'id' => BaseUuidModel::uuidV4(),
                'name' => $authorName,
            ];

            if ($this->db->fieldExists('bio', 'authors')) {
                $payload['bio'] = 'Analytics test verisi icin olusturulan demo yazar.';
            }
            if ($this->db->fieldExists('created_at', 'authors')) {
                $payload['created_at'] = $now;
            }
            if ($this->db->fieldExists('updated_at', 'authors')) {
                $payload['updated_at'] = $now;
            }

            $this->db->table('authors')->insert($payload);
        }

        $rows = $this->db->table('authors')->select('id, name')->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['name']] = (string) $row['id'];
        }

        return $map;
    }

    private function getTypeMap(): array
    {
        if (! $this->db->tableExists('types')) {
            return [];
        }

        $rows = $this->db->table('types')->select('id, name')->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row['name']] = (string) $row['id'];
        }

        return $map;
    }

    private function ensureProducts(string $now, array $categoryMap, array $authorMap, array $typeMap): array
    {
        $productMap = [];

        foreach (self::PRODUCT_DEFINITIONS as $definition) {
            $existing = $this->db->table('products')
                ->select('id')
                ->where('product_name', $definition['name'])
                ->get()
                ->getRowArray();

            if (! $existing) {
                $payload = [
                    'id' => BaseUuidModel::uuidV4(),
                    'product_name' => $definition['name'],
                ];

                if ($this->db->fieldExists('author_id', 'products')) {
                    $payload['author_id'] = $authorMap[$definition['author']] ?? null;
                }
                if ($this->db->fieldExists('type_id', 'products')) {
                    $payload['type_id'] = $typeMap[$definition['type']] ?? null;
                }
                if ($this->db->fieldExists('category_id', 'products')) {
                    $payload['category_id'] = $categoryMap[$definition['category']] ?? null;
                }
                if ($this->db->fieldExists('author', 'products')) {
                    $payload['author'] = $definition['author'];
                }
                if ($this->db->fieldExists('description', 'products')) {
                    $payload['description'] = 'Analytics dashboard test verisi icin demo urun.';
                }
                if ($this->db->fieldExists('price', 'products')) {
                    $payload['price'] = $definition['price'];
                }
                if ($this->db->fieldExists('stock_count', 'products')) {
                    $payload['stock_count'] = 150;
                }
                if ($this->db->fieldExists('type', 'products')) {
                    $payload['type'] = $definition['type'];
                }
                if ($this->db->fieldExists('image', 'products')) {
                    $payload['image'] = null;
                }
                if ($this->db->fieldExists('is_active', 'products')) {
                    $payload['is_active'] = 1;
                }
                if ($this->db->fieldExists('stock', 'products')) {
                    $payload['stock'] = 150;
                }
                if ($this->db->fieldExists('created_at', 'products')) {
                    $payload['created_at'] = $now;
                }
                if ($this->db->fieldExists('updated_at', 'products')) {
                    $payload['updated_at'] = $now;
                }

                $this->db->table('products')->insert($payload);

                $existing = ['id' => $payload['id']];
            }

            $productMap[$definition['key']] = (string) ($existing['id'] ?? '');
        }

        return $productMap;
    }

    private function ensureOrders(string $now, array $productMap): int
    {
        $createdCount = 0;
        $plans = $this->orderPlans();
        $ordersTable = $this->db->table('orders');
        $hasCustomerName = $this->db->fieldExists('customer_name', 'orders');

        foreach ($plans as $index => $plan) {
            $productId = $productMap[$plan['product_key']] ?? '';
            if ($productId === '') {
                continue;
            }

            $customerName = 'Analytics Demo Order ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            if ($hasCustomerName) {
                $exists = $ordersTable->select('id')->where('customer_name', $customerName)->get()->getRowArray();
                if ($exists) {
                    continue;
                }
            }

            $orderDate = date('Y-m-d H:i:s', strtotime($plan['date']));
            $unitPrice = (float) $plan['unit_price'];
            $quantity = (int) $plan['quantity'];
            $total = round($unitPrice * $quantity, 2);

            $payload = [
                'id' => BaseUuidModel::uuidV4(),
                'product_id' => $productId,
                'quantity' => $quantity,
            ];

            if ($this->db->fieldExists('total_price', 'orders')) {
                $payload['total_price'] = $total;
            }
            if ($this->db->fieldExists('total_amount', 'orders')) {
                $payload['total_amount'] = $total;
            }
            if ($this->db->fieldExists('order_date', 'orders')) {
                $payload['order_date'] = $orderDate;
            }
            if ($hasCustomerName) {
                $payload['customer_name'] = $customerName;
            }
            if ($this->db->fieldExists('status', 'orders')) {
                $payload['status'] = $plan['status'];
            }
            if ($this->db->fieldExists('order_status', 'orders')) {
                $payload['order_status'] = $plan['status'] === 'cancelled' ? 'cancelled' : 'completed';
            }
            if ($this->db->fieldExists('payment_status', 'orders')) {
                $payload['payment_status'] = $plan['status'] === 'cancelled' ? 'refunded' : 'paid';
            }
            if ($this->db->fieldExists('shipping_status', 'orders')) {
                $payload['shipping_status'] = $plan['status'] === 'cancelled' ? 'cancelled' : 'delivered';
            }
            if ($this->db->fieldExists('created_at', 'orders')) {
                $payload['created_at'] = $orderDate;
            }
            if ($this->db->fieldExists('updated_at', 'orders')) {
                $payload['updated_at'] = $now;
            }

            $ordersTable->insert($payload);
            $createdCount++;
        }

        return $createdCount;
    }

    private function orderPlans(): array
    {
        return [
            ['date' => '-29 days 10:15', 'product_key' => 'roman_print', 'quantity' => 2, 'unit_price' => 185.00, 'status' => 'completed'],
            ['date' => '-26 days 14:10', 'product_key' => 'science_print', 'quantity' => 1, 'unit_price' => 210.00, 'status' => 'completed'],
            ['date' => '-23 days 12:30', 'product_key' => 'software_digital', 'quantity' => 3, 'unit_price' => 145.00, 'status' => 'completed'],
            ['date' => '-20 days 09:45', 'product_key' => 'roman_digital', 'quantity' => 2, 'unit_price' => 95.00, 'status' => 'completed'],
            ['date' => '-17 days 16:20', 'product_key' => 'children_print', 'quantity' => 4, 'unit_price' => 120.00, 'status' => 'pending'],
            ['date' => '-14 days 11:40', 'product_key' => 'roman_print', 'quantity' => 1, 'unit_price' => 185.00, 'status' => 'completed'],
            ['date' => '-11 days 15:55', 'product_key' => 'science_print', 'quantity' => 2, 'unit_price' => 210.00, 'status' => 'completed'],
            ['date' => '-9 days 13:05', 'product_key' => 'software_digital', 'quantity' => 2, 'unit_price' => 145.00, 'status' => 'completed'],
            ['date' => '-7 days 18:25', 'product_key' => 'roman_digital', 'quantity' => 5, 'unit_price' => 95.00, 'status' => 'completed'],
            ['date' => '-5 days 10:50', 'product_key' => 'children_print', 'quantity' => 2, 'unit_price' => 120.00, 'status' => 'completed'],
            ['date' => '-3 days 12:10', 'product_key' => 'roman_print', 'quantity' => 3, 'unit_price' => 185.00, 'status' => 'completed'],
            ['date' => '-2 days 17:30', 'product_key' => 'science_print', 'quantity' => 1, 'unit_price' => 210.00, 'status' => 'cancelled'],
            ['date' => '-1 day 11:15', 'product_key' => 'software_digital', 'quantity' => 4, 'unit_price' => 145.00, 'status' => 'completed'],
            ['date' => 'today 09:20', 'product_key' => 'roman_digital', 'quantity' => 2, 'unit_price' => 95.00, 'status' => 'completed'],
            ['date' => 'today 16:40', 'product_key' => 'children_print', 'quantity' => 1, 'unit_price' => 120.00, 'status' => 'pending'],
        ];
    }
}
