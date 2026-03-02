<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OrdersShippingDemoSeeder extends Seeder
{
    private const DEMO_ORDER_PREFIX = 'DMO-';

    /** @var array<string, bool> */
    private array $orderColumns = [];
    /** @var array<string, bool> */
    private array $orderItemColumns = [];
    /** @var array<string, bool> */
    private array $orderLogColumns = [];
    /** @var array<string, bool> */
    private array $productColumns = [];
    /** @var array<string, bool> */
    private array $userColumns = [];

    public function run()
    {
        $db = db_connect();

        if (! $db->tableExists('orders') || ! $db->tableExists('products')) {
            echo "OrdersShippingDemoSeeder: 'orders' veya 'products' tablosu bulunamadı.\n";
            return;
        }

        $this->orderColumns = $this->fieldMap('orders');
        $this->orderItemColumns = $db->tableExists('order_items') ? $this->fieldMap('order_items') : [];
        $this->orderLogColumns = $db->tableExists('order_logs') ? $this->fieldMap('order_logs') : [];
        $this->productColumns = $this->fieldMap('products');
        $this->userColumns = $db->tableExists('users') ? $this->fieldMap('users') : [];

        $this->cleanupPreviousDemoData();

        $products = $this->ensureProductsPool(25);
        if ($products === []) {
            echo "OrdersShippingDemoSeeder: Demo sipariş üretimi için ürün bulunamadı.\n";
            return;
        }

        $users = $this->ensureUsersPool(30);
        $adminActor = $this->pickAdminActorId();

        $shippingCompanies = ['Yurtiçi Kargo', 'MNG Kargo', 'Aras Kargo', 'PTT Kargo', 'Sürat Kargo'];
        $paymentMethods = ['credit_card', 'bank_transfer', 'cash_on_delivery'];
        $cities = [
            ['city' => 'İstanbul', 'district' => 'Kadıköy', 'postal' => '34710'],
            ['city' => 'Ankara', 'district' => 'Çankaya', 'postal' => '06680'],
            ['city' => 'İzmir', 'district' => 'Karşıyaka', 'postal' => '35550'],
            ['city' => 'Bursa', 'district' => 'Nilüfer', 'postal' => '16140'],
            ['city' => 'Antalya', 'district' => 'Muratpaşa', 'postal' => '07100'],
        ];

        $scenarios = array_merge(
            array_fill(0, 10, 'pending_unpaid'),
            array_fill(0, 10, 'paid_preparing'),
            array_fill(0, 12, 'shipped'),
            array_fill(0, 14, 'delivered'),
            array_fill(0, 8, 'returned'),
            array_fill(0, 6, 'delayed')
        );
        shuffle($scenarios);

        $insertedOrders = 0;
        $insertedOrderItems = 0;
        $insertedOrderLogs = 0;
        $todayPrefix = date('ymd');

        foreach ($scenarios as $index => $scenario) {
            $orderId = $this->uuidV4();
            $orderNo = self::DEMO_ORDER_PREFIX . $todayPrefix . '-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            $baseDate = (new \DateTimeImmutable())
                ->modify('-' . random_int(0, 89) . ' days')
                ->setTime(random_int(9, 21), random_int(0, 59), random_int(0, 59));

            $user = $users[array_rand($users)];
            $customerName = (string) ($user['customer_name'] ?? ('Müşteri ' . ($index + 1)));
            $userId = (string) ($user['id'] ?? '');

            $cityData = $cities[array_rand($cities)];
            $shippingCompany = $shippingCompanies[array_rand($shippingCompanies)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

            $items = $this->buildOrderItems($products, $orderId, $baseDate);
            $firstItem = $items[0];
            $totalQty = array_sum(array_map(static fn (array $item): int => (int) $item['quantity'], $items));
            $totalAmount = array_reduce($items, static fn (float $sum, array $item): float => $sum + (float) $item['line_total'], 0.0);

            $statusData = $this->statusPayload($scenario, $baseDate);
            $tracking = $statusData['tracking_required']
                ? ('TRK' . date('ymd') . str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT))
                : null;

            $orderRow = [
                'id' => $orderId,
                'order_no' => $orderNo,
                'user_id' => $userId !== '' ? $userId : null,
                'product_id' => (string) $firstItem['product_id'],
                'quantity' => $totalQty > 0 ? $totalQty : 1,
                'total_price' => number_format($totalAmount, 2, '.', ''),
                'total_amount' => number_format($totalAmount, 2, '.', ''),
                'customer_name' => $customerName,
                'payment_method' => $paymentMethod,
                'payment_status' => (string) $statusData['payment_status'],
                'status' => (string) $statusData['legacy_status'],
                'order_status' => (string) $statusData['order_status'],
                'shipping_status' => (string) $statusData['shipping_status'],
                'shipping_company' => $shippingCompany,
                'tracking_number' => $tracking,
                'shipping_address_line1' => 'Demo Mah. Sipariş Sok. No: ' . random_int(1, 199),
                'shipping_address_line2' => random_int(0, 1) === 1 ? 'Daire ' . random_int(1, 24) : null,
                'shipping_city' => $cityData['city'],
                'shipping_district' => $cityData['district'],
                'shipping_postal_code' => $cityData['postal'],
                'shipping_country' => 'Türkiye',
                'reserved_at' => $baseDate->format('Y-m-d H:i:s'),
                'paid_at' => $this->formatDateTime($statusData['paid_at']),
                'shipped_at' => $this->formatDateTime($statusData['shipped_at']),
                'delivered_at' => $this->formatDateTime($statusData['delivered_at']),
                'cancelled_at' => null,
                'returned_at' => $this->formatDateTime($statusData['returned_at']),
                'return_started_at' => $this->formatDateTime($statusData['return_started_at']),
                'return_completed_at' => $this->formatDateTime($statusData['return_completed_at']),
                'notes_admin' => 'Demo sipariş verisi',
                'updated_by' => $adminActor,
                'order_date' => $baseDate->format('Y-m-d H:i:s'),
                'created_at' => $baseDate->format('Y-m-d H:i:s'),
                'updated_at' => $this->formatDateTime($statusData['updated_at']) ?? $baseDate->format('Y-m-d H:i:s'),
                'deleted_at' => null,
            ];

            $db->table('orders')->insert($this->pickColumns($orderRow, $this->orderColumns));
            $insertedOrders++;

            if ($this->orderItemColumns !== []) {
                foreach ($items as $item) {
                    $itemRow = [
                        'id' => $this->uuidV4(),
                        'order_id' => $orderId,
                        'product_id' => $item['product_id'],
                        'product_name_snapshot' => $item['product_name_snapshot'],
                        'unit_price' => number_format((float) $item['unit_price'], 2, '.', ''),
                        'quantity' => (int) $item['quantity'],
                        'line_total' => number_format((float) $item['line_total'], 2, '.', ''),
                        'created_at' => $baseDate->modify('+5 minutes')->format('Y-m-d H:i:s'),
                        'updated_at' => $baseDate->modify('+5 minutes')->format('Y-m-d H:i:s'),
                    ];
                    $db->table('order_items')->insert($this->pickColumns($itemRow, $this->orderItemColumns));
                    $insertedOrderItems++;
                }
            }

            if ($this->orderLogColumns !== []) {
                $logs = $this->buildOrderLogs(
                    $orderId,
                    $userId,
                    $statusData,
                    $baseDate,
                    $shippingCompany,
                    $tracking
                );
                foreach ($logs as $log) {
                    $db->table('order_logs')->insert($this->pickColumns($log, $this->orderLogColumns));
                    $insertedOrderLogs++;
                }
            }
        }

        echo "OrdersShippingDemoSeeder: {$insertedOrders} sipariş, {$insertedOrderItems} sipariş kalemi, {$insertedOrderLogs} log kaydı eklendi.\n";
    }

    private function cleanupPreviousDemoData(): void
    {
        $db = db_connect();
        if (! $db->tableExists('orders')) {
            return;
        }

        $demoOrderIds = array_map(
            static fn (array $row): string => (string) $row['id'],
            $db->table('orders')
                ->select('id')
                ->like('order_no', self::DEMO_ORDER_PREFIX, 'after')
                ->get()
                ->getResultArray()
        );

        if ($demoOrderIds === []) {
            return;
        }

        foreach (array_chunk($demoOrderIds, 500) as $chunk) {
            if ($this->orderLogColumns !== []) {
                $db->table('order_logs')->whereIn('order_id', $chunk)->delete();
            }
            if ($this->orderItemColumns !== []) {
                $db->table('order_items')->whereIn('order_id', $chunk)->delete();
            }
            if ($db->tableExists('product_stock_logs')) {
                $productStockLogFields = $this->fieldMap('product_stock_logs');
                if (isset($productStockLogFields['related_order_id'])) {
                    $db->table('product_stock_logs')->whereIn('related_order_id', $chunk)->delete();
                }
            }
            $db->table('orders')->whereIn('id', $chunk)->delete();
        }
    }

    /**
     * @return array<int, array{id:string,product_name:string,price:float}>
     */
    private function ensureProductsPool(int $minimum): array
    {
        $db = db_connect();

        $rows = $this->fetchPrintedProducts();
        if (count($rows) >= $minimum) {
            return $rows;
        }

        $need = $minimum - count($rows);
        $now = date('Y-m-d H:i:s');
        for ($i = 1; $i <= $need; $i++) {
            $productId = $this->uuidV4();
            $price = random_int(90, 620);
            $stock = random_int(35, 240);
            $suffix = str_pad((string) ($i + count($rows)), 2, '0', STR_PAD_LEFT);

            $row = [
                'id' => $productId,
                'product_name' => 'Demo Basılı Ürün ' . $suffix,
                'author' => 'Demo Yazar',
                'description' => 'Sipariş ve kargo testleri için demo ürün.',
                'price' => number_format((float) $price, 2, '.', ''),
                'stock_count' => $stock,
                'reserved_count' => 0,
                'type' => 'basili',
                'image' => null,
                'is_active' => 1,
                'stock' => $stock,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];

            $db->table('products')->insert($this->pickColumns($row, $this->productColumns));
        }

        return $this->fetchPrintedProducts();
    }

    /**
     * @return array<int, array{id:string,customer_name:string}>
     */
    private function ensureUsersPool(int $minimum): array
    {
        $db = db_connect();
        if (! $db->tableExists('users')) {
            return [];
        }

        $rows = $this->fetchCustomerUsers();
        if (count($rows) >= $minimum) {
            return $rows;
        }

        $need = $minimum - count($rows);
        $now = date('Y-m-d H:i:s');
        for ($i = 1; $i <= $need; $i++) {
            $seq = str_pad((string) ($i + count($rows)), 2, '0', STR_PAD_LEFT);
            $email = 'demo.customer' . $seq . '@example.com';

            $exists = $db->table('users')->select('id')->where('email', $email)->get()->getRowArray();
            if ($exists) {
                continue;
            }

            $row = [
                'id' => $this->uuidV4(),
                'username' => 'demo_musteri_' . $seq,
                'email' => $email,
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'user',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];
            $db->table('users')->insert($this->pickColumns($row, $this->userColumns));
        }

        return $this->fetchCustomerUsers();
    }

    private function pickAdminActorId(): ?string
    {
        $db = db_connect();
        if (! $db->tableExists('users')) {
            return null;
        }

        $admin = $db->table('users')->select('id')->where('role', 'admin')->get()->getRowArray();
        if ($admin && ! empty($admin['id'])) {
            return (string) $admin['id'];
        }

        $any = $db->table('users')->select('id')->get()->getRowArray();
        return $any && ! empty($any['id']) ? (string) $any['id'] : null;
    }

    /**
     * @param array<int, array{id:string,product_name:string,price:float}> $products
     * @return array<int, array<string, mixed>>
     */
    private function buildOrderItems(array $products, string $orderId, \DateTimeImmutable $baseDate): array
    {
        $count = random_int(1, 5);
        $items = [];
        for ($i = 0; $i < $count; $i++) {
            $product = $products[array_rand($products)];
            $qty = random_int(1, 3);
            $unitPrice = (float) ($product['price'] ?? random_int(75, 600));
            $lineTotal = $unitPrice * $qty;

            $items[] = [
                'id' => $this->uuidV4(),
                'order_id' => $orderId,
                'product_id' => (string) $product['id'],
                'product_name_snapshot' => (string) ($product['product_name'] ?? 'Demo Ürün'),
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'line_total' => $lineTotal,
                'created_at' => $baseDate->format('Y-m-d H:i:s'),
                'updated_at' => $baseDate->format('Y-m-d H:i:s'),
            ];
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function statusPayload(string $scenario, \DateTimeImmutable $baseDate): array
    {
        $paidAt = null;
        $shippedAt = null;
        $deliveredAt = null;
        $returnStartedAt = null;
        $returnCompletedAt = null;
        $returnedAt = null;
        $updatedAt = $baseDate;

        return match ($scenario) {
            'pending_unpaid' => [
                'payment_status' => 'unpaid',
                'legacy_status' => 'reserved',
                'order_status' => 'pending',
                'shipping_status' => 'not_shipped',
                'tracking_required' => false,
                'paid_at' => $paidAt,
                'shipped_at' => $shippedAt,
                'delivered_at' => $deliveredAt,
                'return_started_at' => $returnStartedAt,
                'return_completed_at' => $returnCompletedAt,
                'returned_at' => $returnedAt,
                'updated_at' => $updatedAt,
            ],
            'paid_preparing' => [
                'payment_status' => 'paid',
                'legacy_status' => 'paid',
                'order_status' => 'preparing',
                'shipping_status' => 'not_shipped',
                'tracking_required' => false,
                'paid_at' => $baseDate->modify('+2 hours'),
                'shipped_at' => $shippedAt,
                'delivered_at' => $deliveredAt,
                'return_started_at' => $returnStartedAt,
                'return_completed_at' => $returnCompletedAt,
                'returned_at' => $returnedAt,
                'updated_at' => $baseDate->modify('+2 hours'),
            ],
            'shipped' => [
                'payment_status' => 'paid',
                'legacy_status' => 'shipped',
                'order_status' => 'shipped',
                'shipping_status' => 'shipped',
                'tracking_required' => true,
                'paid_at' => $baseDate->modify('+2 hours'),
                'shipped_at' => $baseDate->modify('+1 day'),
                'delivered_at' => $deliveredAt,
                'return_started_at' => $returnStartedAt,
                'return_completed_at' => $returnCompletedAt,
                'returned_at' => $returnedAt,
                'updated_at' => $baseDate->modify('+2 days'),
            ],
            'delivered' => [
                'payment_status' => 'paid',
                'legacy_status' => 'completed',
                'order_status' => 'delivered',
                'shipping_status' => 'delivered',
                'tracking_required' => true,
                'paid_at' => $baseDate->modify('+2 hours'),
                'shipped_at' => $baseDate->modify('+1 day'),
                'delivered_at' => $baseDate->modify('+4 days'),
                'return_started_at' => $returnStartedAt,
                'return_completed_at' => $returnCompletedAt,
                'returned_at' => $returnedAt,
                'updated_at' => $baseDate->modify('+4 days'),
            ],
            'returned' => [
                'payment_status' => 'refunded',
                'legacy_status' => 'returned',
                'order_status' => 'return_done',
                'shipping_status' => 'returned',
                'tracking_required' => true,
                'paid_at' => $baseDate->modify('+3 hours'),
                'shipped_at' => $baseDate->modify('+1 day'),
                'delivered_at' => $baseDate->modify('+3 days'),
                'return_started_at' => $baseDate->modify('+6 days'),
                'return_completed_at' => $baseDate->modify('+9 days'),
                'returned_at' => $baseDate->modify('+9 days'),
                'updated_at' => $baseDate->modify('+9 days'),
            ],
            'delayed' => [
                'payment_status' => 'paid',
                'legacy_status' => 'shipped',
                'order_status' => 'shipped',
                'shipping_status' => 'delayed',
                'tracking_required' => true,
                'paid_at' => $baseDate->modify('+4 hours'),
                'shipped_at' => $baseDate->modify('+1 day'),
                'delivered_at' => $deliveredAt,
                'return_started_at' => $returnStartedAt,
                'return_completed_at' => $returnCompletedAt,
                'returned_at' => $returnedAt,
                'updated_at' => $baseDate->modify('+6 days'),
            ],
            default => [
                'payment_status' => 'unpaid',
                'legacy_status' => 'reserved',
                'order_status' => 'pending',
                'shipping_status' => 'not_shipped',
                'tracking_required' => false,
                'paid_at' => $paidAt,
                'shipped_at' => $shippedAt,
                'delivered_at' => $deliveredAt,
                'return_started_at' => $returnStartedAt,
                'return_completed_at' => $returnCompletedAt,
                'returned_at' => $returnedAt,
                'updated_at' => $updatedAt,
            ],
        };
    }

    /**
     * @param array<string, mixed> $statusData
     * @return array<int, array<string, mixed>>
     */
    private function buildOrderLogs(
        string $orderId,
        string $actorUserId,
        array $statusData,
        \DateTimeImmutable $baseDate,
        string $shippingCompany,
        ?string $tracking
    ): array {
        $logs = [];

        $addLog = function (
            string $action,
            ?string $fromStatus,
            ?string $toStatus,
            string $message,
            \DateTimeImmutable $when,
            ?array $meta = null
        ) use (&$logs, $orderId, $actorUserId): void {
            $logs[] = [
                'id' => $this->uuidV4(),
                'order_id' => $orderId,
                'actor_user_id' => $actorUserId !== '' ? $actorUserId : null,
                'actor_role' => 'admin',
                'action' => $action,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'message' => $message,
                'meta_json' => $meta !== null ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'created_at' => $when->format('Y-m-d H:i:s'),
            ];
        };

        $addLog('order_created', null, 'pending', 'Sipariş oluşturuldu.', $baseDate);

        $paidAt = $statusData['paid_at'] ?? null;
        if ($paidAt instanceof \DateTimeImmutable) {
            $addLog('payment_status_changed', 'unpaid', (string) $statusData['payment_status'], 'Ödeme alındı.', $paidAt);
        }

        $shippingStatus = (string) ($statusData['shipping_status'] ?? 'not_shipped');
        $shippedAt = $statusData['shipped_at'] ?? null;
        if ($shippedAt instanceof \DateTimeImmutable && in_array($shippingStatus, ['shipped', 'delivered', 'returned', 'delayed'], true)) {
            $addLog(
                'shipping_updated',
                'not_shipped',
                'shipped',
                'Kargoya verildi.',
                $shippedAt,
                ['shipping_company' => $shippingCompany, 'tracking_number' => $tracking]
            );
            $addLog('shipping_updated', 'shipped', 'shipped', 'Transfer merkezine ulaştı.', $shippedAt->modify('+8 hours'));
            $addLog('shipping_updated', 'shipped', 'shipped', 'Dağıtıma çıktı.', $shippedAt->modify('+1 day'));
        }

        $deliveredAt = $statusData['delivered_at'] ?? null;
        if ($deliveredAt instanceof \DateTimeImmutable) {
            $addLog('shipping_updated', 'shipped', 'delivered', 'Teslim edildi.', $deliveredAt);
            $addLog('status_changed', 'shipped', 'delivered', 'Sipariş durumu güncellendi.', $deliveredAt->modify('+5 minutes'));
        }

        if ($shippingStatus === 'delayed' && $shippedAt instanceof \DateTimeImmutable) {
            $addLog('shipping_updated', 'shipped', 'delayed', 'Teslimat gecikti.', $shippedAt->modify('+3 days'));
        }

        $returnStartedAt = $statusData['return_started_at'] ?? null;
        $returnCompletedAt = $statusData['return_completed_at'] ?? null;
        if ($returnStartedAt instanceof \DateTimeImmutable) {
            $addLog('return_started', 'delivered', 'return_in_progress', 'İade süreci başlatıldı.', $returnStartedAt);
        }
        if ($returnCompletedAt instanceof \DateTimeImmutable) {
            $addLog('return_completed', 'return_in_progress', 'return_done', 'İade tamamlandı.', $returnCompletedAt);
            $addLog('shipping_updated', 'delivered', 'returned', 'İade deposuna ulaştı.', $returnCompletedAt->modify('+2 hours'));
        }

        usort(
            $logs,
            static fn (array $a, array $b): int => strcmp((string) ($a['created_at'] ?? ''), (string) ($b['created_at'] ?? ''))
        );

        return $logs;
    }

    /**
     * @return array<int, array{id:string,product_name:string,price:float}>
     */
    private function fetchPrintedProducts(): array
    {
        $db = db_connect();
        $builder = $db->table('products')->select('id, product_name, price');
        if (isset($this->productColumns['type'])) {
            $builder->where('type', 'basili');
        }
        if (isset($this->productColumns['is_active'])) {
            $builder->where('is_active', 1);
        }
        if (isset($this->productColumns['deleted_at'])) {
            $builder->where('deleted_at', null);
        }

        $rows = $builder->orderBy('created_at', 'DESC')->limit(200)->get()->getResultArray();
        return array_map(static function (array $row): array {
            return [
                'id' => (string) ($row['id'] ?? ''),
                'product_name' => (string) ($row['product_name'] ?? 'Demo Ürün'),
                'price' => (float) ($row['price'] ?? 100),
            ];
        }, array_values(array_filter($rows, static fn (array $row): bool => trim((string) ($row['id'] ?? '')) !== '')));
    }

    /**
     * @return array<int, array{id:string,customer_name:string}>
     */
    private function fetchCustomerUsers(): array
    {
        $db = db_connect();
        if (! $db->tableExists('users')) {
            return [];
        }

        $builder = $db->table('users')->select('id, username, email');
        if (isset($this->userColumns['deleted_at'])) {
            $builder->where('deleted_at', null);
        }
        if (isset($this->userColumns['role'])) {
            $builder->whereIn('role', ['user', 'secretary', 'admin']);
        }
        if (isset($this->userColumns['status'])) {
            $builder->where('status', 'active');
        }

        $rows = $builder->orderBy('created_at', 'DESC')->limit(300)->get()->getResultArray();
        $result = [];
        foreach ($rows as $row) {
            $id = trim((string) ($row['id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $name = trim((string) ($row['username'] ?? ''));
            if ($name === '') {
                $name = trim((string) ($row['email'] ?? 'Müşteri'));
            }
            $result[] = [
                'id' => $id,
                'customer_name' => $name,
            ];
        }

        return $result;
    }

    /**
     * @return array<string, bool>
     */
    private function fieldMap(string $table): array
    {
        $fields = db_connect()->getFieldNames($table);
        $map = [];
        foreach ($fields as $field) {
            $map[strtolower((string) $field)] = true;
        }
        return $map;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, bool> $columns
     * @return array<string, mixed>
     */
    private function pickColumns(array $row, array $columns): array
    {
        $filtered = [];
        foreach ($row as $key => $value) {
            if (isset($columns[strtolower($key)])) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    private function formatDateTime(mixed $value): ?string
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value->format('Y-m-d H:i:s');
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        $str = trim((string) $value);
        return $str !== '' ? $str : null;
    }
}
