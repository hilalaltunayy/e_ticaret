<?php

namespace App\Services;

use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\ProductsModel;

class OrderCreationService
{
    public function __construct(
        private ?OrdersService $ordersService = null,
        private ?OrderModel $orderModel = null,
        private ?OrderItemModel $orderItemModel = null,
        private ?ProductsModel $productsModel = null
    ) {
        $this->ordersService = $this->ordersService ?? new OrdersService();
        $this->orderModel = $this->orderModel ?? new OrderModel();
        $this->orderItemModel = $this->orderItemModel ?? new OrderItemModel();
        $this->productsModel = $this->productsModel ?? new ProductsModel();
    }

    public function createManualOrder(string $productId, int $quantity, ?string $customerName, array $actor): array
    {
        $actorId = trim((string) ($actor['id'] ?? ''));
        $normalizedCustomerName = trim((string) ($customerName ?? ''));
        $orderId = $this->ordersService->createReservedOrder(
            trim($productId),
            $quantity,
            $actorId,
            $normalizedCustomerName !== '' ? $normalizedCustomerName : null
        );

        if (! $orderId) {
            return [
                'success' => false,
                'type' => 'failed',
                'order_id' => null,
                'from_status' => null,
                'to_status' => null,
            ];
        }

        $orderNo = 'ORD-' . strtoupper(substr(str_replace('-', '', $orderId), 0, 10));
        $this->orderModel->update($orderId, [
            'order_no' => $orderNo,
            'payment_method' => 'unknown',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'shipping_status' => 'not_shipped',
            'updated_by' => $actorId,
        ]);

        $this->upsertOrderItemSnapshot($orderId);

        return [
            'success' => true,
            'type' => 'success',
            'order_id' => $orderId,
            'from_status' => null,
            'to_status' => 'pending',
        ];
    }

    private function upsertOrderItemSnapshot(string $orderId): void
    {
        $db = db_connect();
        if (! $db->tableExists('order_items')) {
            return;
        }

        $order = $this->orderModel->find($orderId);
        if (! $order) {
            return;
        }

        $product = $this->productsModel
            ->select('product_name, price')
            ->where('id', (string) ($order['product_id'] ?? ''))
            ->first();

        $quantity = max(1, (int) ($order['quantity'] ?? 1));
        $unitPrice = (float) ($product['price'] ?? 0);
        $lineTotal = (float) ($order['total_amount'] ?? ($unitPrice * $quantity));

        $exists = $this->orderItemModel->where('order_id', $orderId)->countAllResults();
        if ($exists > 0) {
            return;
        }

        $this->orderItemModel->insert([
            'order_id' => $orderId,
            'product_id' => (string) ($order['product_id'] ?? ''),
            'product_name_snapshot' => (string) ($product['product_name'] ?? 'Bilinmeyen urun'),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
        ]);
    }
}
