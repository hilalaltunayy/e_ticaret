<?php

namespace App\Services;

use App\Models\OrderModel;
use App\Models\OrderLogModel;
use App\Models\ProductsModel;

class OrdersService
{
    public function __construct(
        private ?OrderModel $orderModel = null,
        private ?OrderLogModel $orderLogModel = null,
        private ?ProductsModel $productsModel = null
    ) {
        $this->orderModel = $this->orderModel ?? new OrderModel();
        $this->orderLogModel = $this->orderLogModel ?? new OrderLogModel();
        $this->productsModel = $this->productsModel ?? new ProductsModel();
    }

    public function applyInlineStatusUpdate(string $identifier, string $field, string $value, array $actor): array
    {
        $allowedFields = ['order_status', 'payment_status'];
        if (trim($identifier) === '' || ! in_array($field, $allowedFields, true) || trim($value) === '') {
            return [
                'success' => false,
                'httpStatus' => 422,
                'message' => "Ge\u{00E7}ersiz istek.",
            ];
        }

        $order = $this->orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return [
                'success' => false,
                'httpStatus' => 404,
                'message' => "Sipari\u{015F} bulunamad\u{0131}.",
            ];
        }

        $actorId = trim((string) ($actor['id'] ?? ''));
        $actorRole = trim((string) ($actor['role'] ?? ''));
        $now = date('Y-m-d H:i:s');
        $update = ['updated_by' => $actorId !== '' ? $actorId : null];

        if ($field === 'order_status') {
            if (! in_array($value, $this->allowedOrderStatuses(), true)) {
                return [
                    'success' => false,
                    'httpStatus' => 422,
                    'message' => "Ge\u{00E7}ersiz sipari\u{015F} durumu.",
                ];
            }

            $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
            $update['order_status'] = $value;
            $update['status'] = $this->mapLegacyStatus($value);
            $update['shipping_status'] = $this->mapShippingStatusByOrderStatus($value);

            if ($value === 'shipped') {
                $update['shipped_at'] = $now;
            }
            if ($value === 'delivered') {
                $update['delivered_at'] = $now;
            }
            if ($value === 'cancelled') {
                $update['cancelled_at'] = $now;
            }
            if ($value === 'return_in_progress') {
                $update['return_started_at'] = $now;
            }
            if ($value === 'return_done') {
                $update['return_completed_at'] = $now;
                $update['returned_at'] = $now;
            }

            $this->orderModel->update((string) $order['id'], $update);
            $this->insertOrderLog(
                (string) $order['id'],
                $actorId,
                $actorRole,
                'status_changed',
                $fromStatus,
                $value,
                "Sipari\u{015F} durumu g\u{00FC}ncellendi."
            );
        } else {
            if (! in_array($value, $this->allowedPaymentStatuses(), true)) {
                return [
                    'success' => false,
                    'httpStatus' => 422,
                    'message' => "Ge\u{00E7}ersiz \u{00F6}deme durumu.",
                ];
            }

            $fromStatus = (string) ($order['payment_status'] ?? '');
            $update['payment_status'] = $value;
            if ($value === 'paid' && empty($order['paid_at'])) {
                $update['paid_at'] = $now;
            }

            $this->orderModel->update((string) $order['id'], $update);
            $this->insertOrderLog(
                (string) $order['id'],
                $actorId,
                $actorRole,
                'payment_status_changed',
                $fromStatus,
                $value,
                "\u{00D6}deme durumu g\u{00FC}ncellendi."
            );
        }

        return [
            'success' => true,
        ];
    }

    public function applyAdminStatusUpdate(
        string $identifier,
        string $orderStatus,
        ?string $paymentStatus,
        array $actor,
        ?array $messages = null
    ): array {
        $toStatus = trim($orderStatus);
        if ($toStatus === '' || ! in_array($toStatus, $this->allowedOrderStatuses(), true)) {
            return [
                'success' => false,
                'type' => 'validation',
            ];
        }

        $normalizedPaymentStatus = trim((string) ($paymentStatus ?? ''));
        if ($normalizedPaymentStatus !== '' && ! in_array($normalizedPaymentStatus, $this->allowedPaymentStatuses(), true)) {
            return [
                'success' => false,
                'type' => 'validation',
            ];
        }

        $order = $this->orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return [
                'success' => false,
                'type' => 'not_found',
            ];
        }

        $actorId = trim((string) ($actor['id'] ?? ''));
        $actorRole = trim((string) ($actor['role'] ?? ''));
        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        $now = date('Y-m-d H:i:s');

        $update = [
            'order_status' => $toStatus,
            'status' => $this->mapLegacyStatus($toStatus),
            'shipping_status' => $this->mapShippingStatusByOrderStatus($toStatus),
            'updated_by' => $actorId !== '' ? $actorId : null,
        ];

        if ($toStatus === 'shipped') {
            $update['shipped_at'] = $now;
        }
        if ($toStatus === 'delivered') {
            $update['delivered_at'] = $now;
        }
        if ($toStatus === 'cancelled') {
            $update['cancelled_at'] = $now;
        }
        if ($toStatus === 'return_in_progress') {
            $update['return_started_at'] = $now;
        }
        if ($toStatus === 'return_done') {
            $update['return_completed_at'] = $now;
            $update['returned_at'] = $now;
        }
        if ($normalizedPaymentStatus !== '') {
            $update['payment_status'] = $normalizedPaymentStatus;
            if ($normalizedPaymentStatus === 'paid' && empty($order['paid_at'])) {
                $update['paid_at'] = $now;
            }
        }

        $this->orderModel->update((string) $order['id'], $update);

        $statusChangedMessage = (string) ($messages['status_changed'] ?? "Sipari\u{015F} durumu g\u{00FC}ncellendi.");
        $paymentChangedMessage = (string) ($messages['payment_status_changed'] ?? "\u{00D6}deme durumu g\u{00FC}ncellendi.");

        $this->insertOrderLog(
            (string) $order['id'],
            $actorId,
            $actorRole,
            'status_changed',
            $fromStatus,
            $toStatus,
            $statusChangedMessage
        );

        if ($normalizedPaymentStatus !== '' && $normalizedPaymentStatus !== (string) ($order['payment_status'] ?? '')) {
            $this->insertOrderLog(
                (string) $order['id'],
                $actorId,
                $actorRole,
                'payment_status_changed',
                (string) ($order['payment_status'] ?? ''),
                $normalizedPaymentStatus,
                $paymentChangedMessage
            );
        }

        return [
            'success' => true,
            'order' => $order,
        ];
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

    public function initializeReservedOrderAfterCreate(string $orderId, string $actorUserId): bool
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            return false;
        }

        $orderNo = 'ORD-' . strtoupper(substr(str_replace('-', '', $orderId), 0, 10));

        return $this->orderModel->update($orderId, [
            'order_no' => $orderNo,
            'payment_method' => 'unknown',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'shipping_status' => 'not_shipped',
            'updated_by' => trim($actorUserId) !== '' ? trim($actorUserId) : null,
        ]);
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

    public function applyShippingUpdate(
        string $identifier,
        ?string $shippingCompany,
        ?string $trackingNumber,
        ?string $shippingStatus,
        array $actor
    ): array {
        $order = $this->orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return [
                'success' => false,
                'type' => 'not_found',
            ];
        }

        $normalizedShippingCompany = trim((string) ($shippingCompany ?? ''));
        $normalizedTrackingNumber = trim((string) ($trackingNumber ?? ''));
        $normalizedShippingStatus = trim((string) ($shippingStatus ?? ''));

        if ($normalizedShippingStatus === '' && $normalizedTrackingNumber !== '') {
            $normalizedShippingStatus = 'shipped';
        }
        if ($normalizedShippingStatus === '') {
            $normalizedShippingStatus = (string) ($order['shipping_status'] ?? 'not_shipped');
        }

        $actorId = trim((string) ($actor['id'] ?? ''));
        $update = [
            'shipping_company' => $normalizedShippingCompany !== '' ? $normalizedShippingCompany : null,
            'tracking_number' => $normalizedTrackingNumber !== '' ? $normalizedTrackingNumber : null,
            'shipping_status' => $normalizedShippingStatus,
            'updated_by' => $actorId !== '' ? $actorId : null,
        ];

        $now = date('Y-m-d H:i:s');
        if ($normalizedShippingStatus === 'shipped' && empty($order['shipped_at'])) {
            $update['shipped_at'] = $now;
        }
        if ($normalizedShippingStatus === 'delivered') {
            $update['delivered_at'] = $now;
            $update['order_status'] = 'delivered';
            $update['status'] = 'completed';
        }
        if ($normalizedShippingStatus === 'returned') {
            $update['order_status'] = 'return_in_progress';
            if (empty($order['return_started_at'])) {
                $update['return_started_at'] = $now;
            }
        }

        $this->orderModel->update((string) $order['id'], $update);

        return [
            'success' => true,
            'order' => $order,
            'shipping_company' => $normalizedShippingCompany,
            'tracking_number' => $normalizedTrackingNumber,
            'shipping_status' => $normalizedShippingStatus,
        ];
    }

    public function startReturnForOrderIdentifier(string $identifier, array $actor): array
    {
        $order = $this->orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return [
                'success' => false,
                'type' => 'not_found',
                'order' => null,
                'from_status' => null,
                'to_status' => null,
            ];
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        $toStatus = 'return_in_progress';
        if ($fromStatus !== 'delivered') {
            return [
                'success' => false,
                'type' => 'invalid_status',
                'order' => $order,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
            ];
        }

        $actorId = trim((string) ($actor['id'] ?? ''));
        $this->orderModel->update((string) $order['id'], [
            'order_status' => $toStatus,
            'shipping_status' => 'returned',
            'return_started_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actorId !== '' ? $actorId : null,
        ]);

        return [
            'success' => true,
            'type' => 'success',
            'order' => $order,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ];
    }

    public function completeReturnForOrderIdentifier(string $identifier, array $actor): array
    {
        $order = $this->orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return [
                'success' => false,
                'type' => 'not_found',
                'order' => null,
                'from_status' => null,
                'to_status' => null,
            ];
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        $toStatus = 'return_done';
        if ($fromStatus !== 'return_in_progress') {
            return [
                'success' => false,
                'type' => 'invalid_status',
                'order' => $order,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
            ];
        }

        $actorId = trim((string) ($actor['id'] ?? ''));
        if (! $this->orderModel->returnOrder((string) $order['id'], $actorId)) {
            return [
                'success' => false,
                'type' => 'failed',
                'order' => $order,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
            ];
        }

        $this->orderModel->update((string) $order['id'], [
            'order_status' => $toStatus,
            'return_completed_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actorId !== '' ? $actorId : null,
        ]);

        return [
            'success' => true,
            'type' => 'success',
            'order' => $order,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ];
    }

    public function datatablesList(array $params): array
    {
        return $this->orderModel->datatablesList($params);
    }

    private function allowedOrderStatuses(): array
    {
        return ['pending', 'preparing', 'packed', 'shipped', 'delivered', 'cancelled', 'return_in_progress', 'return_done'];
    }

    private function allowedPaymentStatuses(): array
    {
        return ['unpaid', 'paid', 'refunded', 'partial_refund', 'failed'];
    }

    private function mapLegacyStatus(string $orderStatus): string
    {
        return match ($orderStatus) {
            'pending' => 'reserved',
            'preparing', 'packed' => 'paid',
            'shipped' => 'shipped',
            'delivered' => 'completed',
            'cancelled' => 'cancelled',
            'return_in_progress', 'return_done' => 'returned',
            default => 'reserved',
        };
    }

    private function mapShippingStatusByOrderStatus(string $orderStatus): string
    {
        return match ($orderStatus) {
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'return_in_progress', 'return_done' => 'returned',
            default => 'not_shipped',
        };
    }

    private function insertOrderLog(
        string $orderId,
        string $actorUserId,
        string $actorRole,
        string $action,
        ?string $fromStatus,
        ?string $toStatus,
        string $message
    ): void {
        $this->orderLogModel->insert([
            'order_id' => $orderId,
            'actor_user_id' => $actorUserId !== '' ? $actorUserId : null,
            'actor_role' => $actorRole !== '' ? $actorRole : null,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'message' => $message,
            'meta_json' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
