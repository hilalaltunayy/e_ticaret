<?php

namespace App\Services;

use App\Models\OrderModel;

class OrderShippingService
{
    public function __construct(
        private ?OrdersService $ordersService = null,
        private ?OrderModel $orderModel = null
    ) {
        $this->ordersService = $this->ordersService ?? new OrdersService();
        $this->orderModel = $this->orderModel ?? new OrderModel();
    }

    public function shipOrderByIdentifier(string $identifier, array $actor): array
    {
        $order = $this->orderModel->findByIdOrOrderNo($identifier);
        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        $toStatus = 'shipped';
        $actorId = trim((string) ($actor['id'] ?? ''));

        if (! $this->ordersService->shipOrder($identifier, $actorId)) {
            return [
                'success' => false,
                'type' => 'failed',
                'order' => $order ?: null,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
            ];
        }

        return [
            'success' => true,
            'type' => 'success',
            'order' => $order ?: null,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ];
    }
}
