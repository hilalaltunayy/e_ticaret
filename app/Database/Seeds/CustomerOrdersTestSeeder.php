<?php

namespace App\Database\Seeds;

use App\Models\BaseUuidModel;
use CodeIgniter\Database\Seeder;

class CustomerOrdersTestSeeder extends Seeder
{
    private const TEST_EMAIL = 'orders.test@test.local';
    private const TEST_PASSWORD = 'Test12345!';
    private const DELIVERED_ORDER_NO = 'TEST-ORD-DELIVERED-001';
    private const ACTIVE_ORDER_NO = 'TEST-ORD-ACTIVE-001';
    private const RETURN_ORDER_NO = 'TEST-ORD-RETURN-001';

    public function run()
    {
        if (! $this->requiredTablesExist()) {
            echo "CustomerOrdersTestSeeder: gerekli tablolar bulunamadi.\n";
            return;
        }

        $userId = $this->upsertUser();
        $products = $this->upsertProducts();

        $deliveredOrderId = $this->upsertDeliveredOrder($userId, $products);
        $activeOrderId = $this->upsertActiveOrder($userId, $products);
        $returnOrderId = $this->upsertReturnOrder($userId, $products);

        $this->upsertDeliveredPaymentsAndShipping($deliveredOrderId);
        $this->upsertActivePaymentsAndShipping($activeOrderId);
        $this->upsertReturnPaymentsAndShipping($returnOrderId);
        $this->upsertReturnRequest($returnOrderId, $userId);

        echo "CustomerOrdersTestSeeder: test siparisleri hazirlandi.\n";
        echo 'Login email: ' . self::TEST_EMAIL . "\n";
        echo 'Login password: ' . self::TEST_PASSWORD . "\n";
    }

    private function requiredTablesExist(): bool
    {
        foreach (['users', 'products', 'orders', 'order_items', 'payments', 'shipments', 'shipment_events', 'return_requests'] as $table) {
            if (! $this->db->tableExists($table)) {
                return false;
            }
        }

        return true;
    }

    private function upsertUser(): string
    {
        $builder = $this->db->table('users');
        $user = $builder->where('email', self::TEST_EMAIL)->get()->getRowArray();

        $data = [
            'username' => 'Orders Test Customer',
            'email' => self::TEST_EMAIL,
            'password' => password_hash(self::TEST_PASSWORD, PASSWORD_DEFAULT),
            'role' => 'user',
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if (is_array($user) && ! empty($user['id'])) {
            $builder->where('id', (string) $user['id'])->update($data);
            return (string) $user['id'];
        }

        $id = BaseUuidModel::uuidV4();
        $builder->insert(array_merge($data, [
            'id' => $id,
            'created_at' => date('Y-m-d H:i:s'),
        ]));

        return $id;
    }

    private function upsertProducts(): array
    {
        return [
            'roman' => $this->upsertProduct('Sipariş Test Romanı', 'Test Romancı', 240.00, 'basili', 12),
            'ebook' => $this->upsertProduct('Sipariş Test E-Kitap', 'Test Dijital Yazar', 95.00, 'dijital', 0),
            'cocuk' => $this->upsertProduct('Sipariş Test Çocuk Kitabı', 'Test Çocuk Yazarı', 175.00, 'basili', 9),
        ];
    }

    private function upsertProduct(string $name, string $author, float $price, string $type, int $stock): array
    {
        $builder = $this->db->table('products');
        $product = $builder->where('product_name', $name)->get()->getRowArray();
        $now = date('Y-m-d H:i:s');

        $data = [
            'product_name' => $name,
            'author' => $author,
            'description' => 'Customer orders test seeder urunu.',
            'price' => $price,
            'stock_count' => $stock,
            'stock' => $stock,
            'reserved_count' => 0,
            'type' => $type,
            'image' => null,
            'is_active' => 1,
            'updated_at' => $now,
            'deleted_at' => null,
        ];

        if (is_array($product) && ! empty($product['id'])) {
            $builder->where('id', (string) $product['id'])->update($data);
            return ['id' => (string) $product['id']] + $data;
        }

        $id = BaseUuidModel::uuidV4();
        $builder->insert(array_merge($data, [
            'id' => $id,
            'created_at' => $now,
        ]));

        return ['id' => $id] + $data;
    }

    private function upsertDeliveredOrder(string $userId, array $products): string
    {
        $builder = $this->db->table('orders');
        $order = $builder->where('order_no', self::DELIVERED_ORDER_NO)->get()->getRowArray();
        $orderId = (string) ($order['id'] ?? BaseUuidModel::uuidV4());

        $orderDate = '2026-04-07 10:30:00';
        $deliveredAt = '2026-04-10 15:45:00';
        $estimatedDelivery = '2026-04-11 18:00:00';
        $subtotal = 240.00 + 95.00 + 175.00;
        $shipping = 0.00;
        $discount = 15.00;
        $total = $subtotal + $shipping - $discount;

        $data = [
            'id' => $orderId,
            'order_no' => self::DELIVERED_ORDER_NO,
            'user_id' => $userId,
            'product_id' => (string) $products['roman']['id'],
            'quantity' => 3,
            'total_price' => $total,
            'total_amount' => $total,
            'customer_name' => 'Orders Test Customer',
            'payment_method' => 'credit_card',
            'payment_status' => 'PAID',
            'status' => 'DELIVERED',
            'order_status' => 'DELIVERED',
            'fulfillment_status' => 'DELIVERED',
            'shipping_status' => 'DELIVERED',
            'shipping_company' => 'Yurtiçi Kargo',
            'tracking_number' => 'TRK-TEST-DEL-001',
            'item_count' => 3,
            'subtotal_amount' => $subtotal,
            'shipping_amount' => $shipping,
            'discount_amount' => $discount,
            'currency' => 'TRY',
            'estimated_delivery_at' => $estimatedDelivery,
            'paid_at' => '2026-04-07 10:45:00',
            'shipped_at' => '2026-04-08 16:20:00',
            'delivered_at' => $deliveredAt,
            'order_date' => $orderDate,
            'updated_at' => $deliveredAt,
            'deleted_at' => null,
        ];

        if ($order) {
            $builder->where('id', $orderId)->update($data);
        } else {
            $builder->insert($data + ['created_at' => $orderDate]);
        }

        $this->upsertOrderItems($orderId, [
            ['product' => $products['roman'], 'unit_price' => 240.00, 'quantity' => 1, 'item_status' => 'DELIVERED'],
            ['product' => $products['ebook'], 'unit_price' => 95.00, 'quantity' => 1, 'item_status' => 'DELIVERED'],
            ['product' => $products['cocuk'], 'unit_price' => 175.00, 'quantity' => 1, 'item_status' => 'DELIVERED'],
        ], $orderDate);

        return $orderId;
    }

    private function upsertActiveOrder(string $userId, array $products): string
    {
        $builder = $this->db->table('orders');
        $order = $builder->where('order_no', self::ACTIVE_ORDER_NO)->get()->getRowArray();
        $orderId = (string) ($order['id'] ?? BaseUuidModel::uuidV4());

        $createdAt = date('Y-m-d H:i:s', strtotime('-2 days'));
        $estimatedDelivery = date('Y-m-d H:i:s', strtotime('+3 days'));
        $subtotal = (240.00 * 1) + (175.00 * 2);
        $shipping = 24.90;
        $discount = 0.00;
        $total = $subtotal + $shipping;

        $data = [
            'id' => $orderId,
            'order_no' => self::ACTIVE_ORDER_NO,
            'user_id' => $userId,
            'product_id' => (string) $products['roman']['id'],
            'quantity' => 3,
            'total_price' => $total,
            'total_amount' => $total,
            'customer_name' => 'Orders Test Customer',
            'payment_method' => 'credit_card',
            'payment_status' => 'PAID',
            'status' => 'SHIPPED',
            'order_status' => 'SHIPPED',
            'fulfillment_status' => 'SHIPPED',
            'shipping_status' => 'IN_TRANSIT',
            'shipping_company' => 'Aras Kargo',
            'tracking_number' => 'TRK-TEST-ACT-001',
            'item_count' => 3,
            'subtotal_amount' => $subtotal,
            'shipping_amount' => $shipping,
            'discount_amount' => $discount,
            'currency' => 'TRY',
            'estimated_delivery_at' => $estimatedDelivery,
            'paid_at' => date('Y-m-d H:i:s', strtotime('-2 days +20 minutes')),
            'shipped_at' => date('Y-m-d H:i:s', strtotime('-1 day +4 hours')),
            'order_date' => $createdAt,
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if ($order) {
            $builder->where('id', $orderId)->update($data);
        } else {
            $builder->insert($data + ['created_at' => $createdAt]);
        }

        $this->upsertOrderItems($orderId, [
            ['product' => $products['roman'], 'unit_price' => 240.00, 'quantity' => 1, 'item_status' => 'DELIVERED'],
            ['product' => $products['cocuk'], 'unit_price' => 175.00, 'quantity' => 2, 'item_status' => 'SHIPPED'],
        ], $createdAt);

        return $orderId;
    }

    private function upsertReturnOrder(string $userId, array $products): string
    {
        $builder = $this->db->table('orders');
        $order = $builder->where('order_no', self::RETURN_ORDER_NO)->get()->getRowArray();
        $orderId = (string) ($order['id'] ?? BaseUuidModel::uuidV4());

        $createdAt = date('Y-m-d H:i:s', strtotime('-5 days'));
        $deliveredAt = date('Y-m-d H:i:s', strtotime('-2 days'));
        $subtotal = 240.00 + 175.00;
        $shipping = 0.00;
        $discount = 0.00;
        $total = $subtotal;

        $data = [
            'id' => $orderId,
            'order_no' => self::RETURN_ORDER_NO,
            'user_id' => $userId,
            'product_id' => (string) $products['roman']['id'],
            'quantity' => 2,
            'total_price' => $total,
            'total_amount' => $total,
            'customer_name' => 'Orders Test Customer',
            'payment_method' => 'credit_card',
            'payment_status' => 'PAID',
            'status' => 'RETURN_REQUESTED',
            'order_status' => 'RETURN_REQUESTED',
            'fulfillment_status' => 'RETURN_REQUESTED',
            'shipping_status' => 'DELIVERED',
            'shipping_company' => 'MNG Kargo',
            'tracking_number' => 'TRK-TEST-RET-001',
            'item_count' => 2,
            'subtotal_amount' => $subtotal,
            'shipping_amount' => $shipping,
            'discount_amount' => $discount,
            'currency' => 'TRY',
            'estimated_delivery_at' => date('Y-m-d H:i:s', strtotime('-1 days')),
            'paid_at' => date('Y-m-d H:i:s', strtotime('-5 days +15 minutes')),
            'shipped_at' => date('Y-m-d H:i:s', strtotime('-4 days +4 hours')),
            'delivered_at' => $deliveredAt,
            'order_date' => $createdAt,
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if ($order) {
            $builder->where('id', $orderId)->update($data);
        } else {
            $builder->insert($data + ['created_at' => $createdAt]);
        }

        $this->upsertOrderItems($orderId, [
            ['product' => $products['roman'], 'unit_price' => 240.00, 'quantity' => 1, 'item_status' => 'RETURN_REQUESTED'],
            ['product' => $products['cocuk'], 'unit_price' => 175.00, 'quantity' => 1, 'item_status' => 'RETURN_REQUESTED'],
        ], $createdAt);

        return $orderId;
    }

    private function upsertOrderItems(string $orderId, array $items, string $createdAt): void
    {
        $builder = $this->db->table('order_items');

        foreach ($items as $item) {
            $product = (array) ($item['product'] ?? []);
            $productId = (string) ($product['id'] ?? '');
            if ($productId === '') {
                continue;
            }

            $existing = $builder
                ->where('order_id', $orderId)
                ->where('product_id', $productId)
                ->get()
                ->getRowArray();

            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = (float) ($item['unit_price'] ?? 0);

            $data = [
                'order_id' => $orderId,
                'product_id' => $productId,
                'product_name_snapshot' => (string) ($product['product_name'] ?? 'Sipariş Test Ürünü'),
                'author' => (string) ($product['author'] ?? ''),
                'product_image' => (string) ($product['image'] ?? ''),
                'product_type' => (string) ($product['type'] ?? ''),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $quantity,
                'item_status' => (string) ($item['item_status'] ?? 'PREPARING'),
                'updated_at' => date('Y-m-d H:i:s'),
                'deleted_at' => null,
            ];

            if ($existing && ! empty($existing['id'])) {
                $builder->where('id', (string) $existing['id'])->update($data);
                continue;
            }

            $builder->insert($data + [
                'id' => BaseUuidModel::uuidV4(),
                'created_at' => $createdAt,
            ]);
        }
    }

    private function upsertDeliveredPaymentsAndShipping(string $orderId): void
    {
        $this->upsertPayment($orderId, 'mock', 'PAID', 495.00, 'TRY', 'PAY-TEST-DEL-001', '2026-04-07 10:45:00');
        $shipmentId = $this->upsertShipment($orderId, 'Yurtiçi Kargo', 'TRK-TEST-DEL-001', 'DELIVERED', '2026-04-11 18:00:00', '2026-04-08 16:20:00', '2026-04-10 15:45:00');

        $this->upsertShipmentEvent($shipmentId, 'PENDING', 'Sipariş Alındı', 'Siparişiniz alındı.', 'İstanbul', '2026-04-07 10:30:00');
        $this->upsertShipmentEvent($shipmentId, 'PREPARING', 'Sipariş Hazırlanıyor', 'Ürünleriniz paketleniyor.', 'İstanbul', '2026-04-07 12:10:00');
        $this->upsertShipmentEvent($shipmentId, 'SHIPPED', 'Kargoya Verildi', 'Paketiniz kargoya teslim edildi.', 'İstanbul', '2026-04-08 16:20:00');
        $this->upsertShipmentEvent($shipmentId, 'IN_TRANSIT', 'Yolda', 'Transfer süreci devam ediyor.', 'Kocaeli', '2026-04-09 08:45:00');
        $this->upsertShipmentEvent($shipmentId, 'OUT_FOR_DELIVERY', 'Dağıtıma Çıktı', 'Teslimat aracına yüklendi.', 'Ankara', '2026-04-10 09:10:00');
        $this->upsertShipmentEvent($shipmentId, 'DELIVERED', 'Teslim Edildi', 'Gönderi alıcıya teslim edildi.', 'Ankara', '2026-04-10 15:45:00');
    }

    private function upsertActivePaymentsAndShipping(string $orderId): void
    {
        $amount = 614.90;
        $now = date('Y-m-d H:i:s');
        $this->upsertPayment($orderId, 'mock', 'PAID', $amount, 'TRY', 'PAY-TEST-ACT-001', $now);
        $shipmentId = $this->upsertShipment(
            $orderId,
            'Aras Kargo',
            'TRK-TEST-ACT-001',
            'IN_TRANSIT',
            date('Y-m-d H:i:s', strtotime('+3 days')),
            date('Y-m-d H:i:s', strtotime('-1 day +4 hours')),
            null
        );

        $this->upsertShipmentEvent($shipmentId, 'PENDING', 'Sipariş Alındı', 'Siparişiniz sisteme kaydedildi.', 'İstanbul', date('Y-m-d H:i:s', strtotime('-2 days')));
        $this->upsertShipmentEvent($shipmentId, 'PREPARING', 'Sipariş Hazırlanıyor', 'Paketleme süreci tamamlandı.', 'İstanbul', date('Y-m-d H:i:s', strtotime('-2 days +3 hours')));
        $this->upsertShipmentEvent($shipmentId, 'SHIPPED', 'Kargoya Verildi', 'Kargo firmasına teslim edildi.', 'İstanbul', date('Y-m-d H:i:s', strtotime('-1 day +4 hours')));
        $this->upsertShipmentEvent($shipmentId, 'IN_TRANSIT', 'Yolda', 'Dağıtım merkezine doğru ilerliyor.', 'Sakarya', date('Y-m-d H:i:s', strtotime('-10 hours')));
    }

    private function upsertReturnPaymentsAndShipping(string $orderId): void
    {
        $this->upsertPayment($orderId, 'mock', 'PAID', 415.00, 'TRY', 'PAY-TEST-RET-001', date('Y-m-d H:i:s', strtotime('-5 days +15 minutes')));
        $shipmentId = $this->upsertShipment(
            $orderId,
            'MNG Kargo',
            'TRK-TEST-RET-001',
            'DELIVERED',
            date('Y-m-d H:i:s', strtotime('-1 day')),
            date('Y-m-d H:i:s', strtotime('-4 days +4 hours')),
            date('Y-m-d H:i:s', strtotime('-2 days'))
        );

        $this->upsertShipmentEvent($shipmentId, 'PENDING', 'Sipariş Alındı', 'Siparişiniz alındı.', 'İstanbul', date('Y-m-d H:i:s', strtotime('-5 days')));
        $this->upsertShipmentEvent($shipmentId, 'PREPARING', 'Sipariş Hazırlanıyor', 'Ürünleriniz hazırlandı.', 'İstanbul', date('Y-m-d H:i:s', strtotime('-5 days +3 hours')));
        $this->upsertShipmentEvent($shipmentId, 'SHIPPED', 'Kargoya Verildi', 'Kargo firmasına teslim edildi.', 'İstanbul', date('Y-m-d H:i:s', strtotime('-4 days +4 hours')));
        $this->upsertShipmentEvent($shipmentId, 'IN_TRANSIT', 'Yolda', 'Transfer süreci devam ediyor.', 'Bursa', date('Y-m-d H:i:s', strtotime('-3 days')));
        $this->upsertShipmentEvent($shipmentId, 'OUT_FOR_DELIVERY', 'Dağıtıma Çıktı', 'Dağıtım aracına yüklendi.', 'Bursa', date('Y-m-d H:i:s', strtotime('-2 days +4 hours')));
        $this->upsertShipmentEvent($shipmentId, 'DELIVERED', 'Teslim Edildi', 'Gönderi teslim edildi.', 'Bursa', date('Y-m-d H:i:s', strtotime('-2 days')));
    }

    private function upsertReturnRequest(string $orderId, string $userId): void
    {
        $builder = $this->db->table('return_requests');
        $request = $builder->where('order_id', $orderId)->where('user_id', $userId)->get()->getRowArray();

        $data = [
            'order_id' => $orderId,
            'user_id' => $userId,
            'status' => 'PENDING',
            'reason' => 'Ürün beklentiyi karşılamadı.',
            'note' => 'Müşteri test iade talebi oluşturdu.',
            'requested_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if ($request && ! empty($request['id'])) {
            $builder->where('id', (string) $request['id'])->update($data);
            return;
        }

        $builder->insert($data + [
            'id' => BaseUuidModel::uuidV4(),
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);
    }

    private function upsertPayment(string $orderId, string $provider, string $status, float $amount, string $currency, string $txnId, string $createdAt): void
    {
        $builder = $this->db->table('payments');
        $payment = $builder->where('order_id', $orderId)->where('provider_txn_id', $txnId)->get()->getRowArray();

        $data = [
            'order_id' => $orderId,
            'provider' => $provider,
            'status' => $status,
            'amount' => $amount,
            'currency' => $currency,
            'provider_txn_id' => $txnId,
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if ($payment && ! empty($payment['id'])) {
            $builder->where('id', (string) $payment['id'])->update($data);
            return;
        }

        $builder->insert($data + [
            'id' => BaseUuidModel::uuidV4(),
            'created_at' => $createdAt,
        ]);
    }

    private function upsertShipment(string $orderId, string $carrier, string $trackingNumber, string $status, ?string $estimatedDeliveryAt, ?string $shippedAt, ?string $deliveredAt): string
    {
        $builder = $this->db->table('shipments');
        $shipment = $builder->where('order_id', $orderId)->get()->getRowArray();
        $shipmentId = (string) ($shipment['id'] ?? BaseUuidModel::uuidV4());

        $data = [
            'id' => $shipmentId,
            'order_id' => $orderId,
            'carrier' => $carrier,
            'tracking_number' => $trackingNumber,
            'status' => $status,
            'estimated_delivery_at' => $estimatedDeliveryAt,
            'shipped_at' => $shippedAt,
            'delivered_at' => $deliveredAt,
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if ($shipment) {
            $builder->where('id', $shipmentId)->update($data);
        } else {
            $builder->insert($data + ['created_at' => $shippedAt ?? date('Y-m-d H:i:s')]);
        }

        return $shipmentId;
    }

    private function upsertShipmentEvent(string $shipmentId, string $status, string $title, string $note, string $location, string $eventTime): void
    {
        $builder = $this->db->table('shipment_events');
        $event = $builder
            ->where('shipment_id', $shipmentId)
            ->where('status', $status)
            ->where('title', $title)
            ->get()
            ->getRowArray();

        $data = [
            'shipment_id' => $shipmentId,
            'status' => $status,
            'title' => $title,
            'note' => $note,
            'location' => $location,
            'event_time' => $eventTime,
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];

        if ($event && ! empty($event['id'])) {
            $builder->where('id', (string) $event['id'])->update($data);
            return;
        }

        $builder->insert($data + [
            'id' => BaseUuidModel::uuidV4(),
            'created_at' => $eventTime,
        ]);
    }
}
