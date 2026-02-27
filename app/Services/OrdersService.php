<?php

namespace App\Services;

use App\Models\OrderModel;
use App\Models\ProductsModel;

class OrdersService
{
    public function __construct(
        private ?OrderModel $orderModel = null,
        private ?ProductsModel $productsModel = null
    ) {
        $this->orderModel = $this->orderModel ?? new OrderModel();
        $this->productsModel = $this->productsModel ?? new ProductsModel();
    }

    public function createReservedOrder(string $productId, int $quantity, string $actorUserId, ?string $customerName = null): string|false
    {
        if ($quantity <= 0 || trim($productId) === '' || trim($actorUserId) === '') {
            return false;
        }

        $product = $this->productsModel->getProductForOrder($productId);
        if (!$product) {
            return false;
        }

        $available = (int) ($product['available_stock'] ?? 0);
        if ($available < $quantity) {
            return false;
        }

        $price = (float) ($product['price'] ?? 0);
        $totalAmount = $price * $quantity;

        return $this->orderModel->createOrderReserved(
            $productId,
            $quantity,
            $totalAmount,
            $actorUserId,
            $customerName
        );
    }

    public function shipOrder(string $orderId, string $actorUserId): bool
    {
        return $this->orderModel->markShipped($orderId, $actorUserId);
    }

    public function cancelOrder(string $orderId, string $actorUserId): bool
    {
        return $this->orderModel->cancelOrder($orderId, $actorUserId);
    }

    public function returnOrder(string $orderId, string $actorUserId): bool
    {
        return $this->orderModel->returnOrder($orderId, $actorUserId);
    }

    public function datatablesList(array $params): array
    {
        return $this->orderModel->datatablesList($params);
    }
}
