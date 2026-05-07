<?php

namespace App\Services;

use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\PaymentModel;
use App\Models\ReturnRequestModel;
use App\Models\ShipmentEventModel;
use App\Models\ShipmentModel;

class CustomerOrderService
{
    private OrderModel $orderModel;
    private OrderItemModel $orderItemModel;
    private PaymentModel $paymentModel;
    private ReturnRequestModel $returnRequestModel;
    private ShipmentModel $shipmentModel;
    private ShipmentEventModel $shipmentEventModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->paymentModel = new PaymentModel();
        $this->returnRequestModel = new ReturnRequestModel();
        $this->shipmentModel = new ShipmentModel();
        $this->shipmentEventModel = new ShipmentEventModel();
        helper('product_media');
    }

    public function getOrdersForUser(string $userId, array $filters = []): array
    {
        $scope = strtolower(trim((string) ($filters['scope'] ?? 'all')));
        $search = trim((string) ($filters['q'] ?? ''));

        if (! in_array($scope, ['all', 'active', 'delivered', 'cancelled_return'], true)) {
            $scope = 'all';
        }

        $builder = $this->orderModel
            ->builder()
            ->select('orders.*')
            ->where('orders.user_id', $userId)
            ->where('orders.deleted_at', null);

        if ($search !== '') {
            $builder->groupStart()
                ->like('orders.order_no', $search)
                ->orLike('orders.id', $search)
                ->groupEnd();
        }

        $rows = $builder
            ->orderBy('COALESCE(orders.order_date, orders.created_at)', 'DESC', false)
            ->get()
            ->getResultArray();

        $orders = [];
        foreach ($rows as $row) {
            $item = $this->buildOrderListItem($row);
            if (! $this->matchesScope($item, $scope)) {
                continue;
            }
            $orders[] = $item;
        }

        return [
            'scope' => $scope,
            'search' => $search,
            'orders' => $orders,
        ];
    }

    public function getOrderDetailForUser(string $userId, string $orderNoOrId): ?array
    {
        $order = $this->getOwnedOrderRecord($userId, $orderNoOrId);
        if (! is_array($order)) {
            return null;
        }

        $items = $this->buildOrderItems((string) $order['id']);
        $shipment = $this->shipmentModel
            ->where('order_id', (string) $order['id'])
            ->first();
        $payment = $this->paymentModel
            ->where('order_id', (string) $order['id'])
            ->orderBy('created_at', 'DESC')
            ->first();
        $returnRequest = $this->findLatestReturnRequest((string) $order['id'], $userId);

        $subtotal = $this->resolveAmount(
            $order,
            'subtotal_amount',
            array_sum(array_map(static fn(array $item): float => (float) ($item['line_total'] ?? 0), $items))
        );
        $shippingAmount = $this->resolveAmount($order, 'shipping_amount', 0.0);
        $discountAmount = $this->resolveAmount($order, 'discount_amount', 0.0);
        $totalAmount = $this->resolveAmount($order, 'total_amount', max(0, $subtotal + $shippingAmount - $discountAmount));
        $status = $this->normalizeOrderStatus($order);
        $paymentStatus = $this->normalizePaymentStatus((string) ($order['payment_status'] ?? $payment['status'] ?? 'PENDING'));
        $fulfillmentStatus = $this->normalizeFulfillmentStatus($order, $shipment);

        return [
            'id' => (string) ($order['id'] ?? ''),
            'order_number' => (string) ($order['order_no'] ?? $order['id'] ?? ''),
            'order_date' => (string) ($order['order_date'] ?? $order['created_at'] ?? ''),
            'status' => $status,
            'status_label' => $this->mapOrderStatusLabel($status),
            'status_badge_class' => $this->mapStatusBadgeClass($status),
            'payment_status' => $paymentStatus,
            'payment_status_label' => $this->mapPaymentStatusLabel($paymentStatus),
            'fulfillment_status' => $fulfillmentStatus,
            'fulfillment_status_label' => $this->mapFulfillmentStatusLabel($fulfillmentStatus),
            'carrier' => (string) ($shipment['carrier'] ?? $order['shipping_company'] ?? ''),
            'tracking_number' => (string) ($shipment['tracking_number'] ?? $order['tracking_number'] ?? ''),
            'estimated_delivery_at' => (string) ($shipment['estimated_delivery_at'] ?? $order['estimated_delivery_at'] ?? ''),
            'delivered_at' => (string) ($shipment['delivered_at'] ?? $order['delivered_at'] ?? ''),
            'subtotal_amount' => $subtotal,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'currency' => (string) ($order['currency'] ?? $payment['currency'] ?? 'TRY'),
            'items' => $items,
            'timeline' => $this->getShipmentTimeline((string) $order['id']),
            'return_state' => $this->getReturnDisplayState($order, $returnRequest),
        ];
    }

    public function createReturnRequest(string $userId, string $orderNoOrId, ?string $reason = null, ?string $note = null): array
    {
        $order = $this->getOwnedOrderRecord($userId, $orderNoOrId);
        if (! is_array($order)) {
            return ['success' => false, 'message' => 'Sipariş bulunamadı.'];
        }

        if ($this->normalizeOrderStatus($order) !== 'DELIVERED') {
            return ['success' => false, 'message' => 'İade talebi yalnızca teslim edilen siparişler için oluşturulabilir.'];
        }

        $existing = $this->findLatestReturnRequest((string) $order['id'], $userId);
        if (is_array($existing) && ! in_array((string) ($existing['status'] ?? ''), ['REJECTED', 'CANCELLED'], true)) {
            return ['success' => false, 'message' => 'Bu sipariş için zaten bir iade talebiniz bulunuyor.'];
        }

        $cleanReason = trim((string) $reason);
        $cleanNote = trim((string) $note);
        if ($cleanReason === '') {
            $cleanReason = 'Müşteri iade talebi oluşturdu.';
        }

        $requestedAt = date('Y-m-d H:i:s');
        $inserted = $this->returnRequestModel->insert([
            'order_id' => (string) $order['id'],
            'user_id' => $userId,
            'status' => 'PENDING',
            'reason' => $cleanReason,
            'note' => $cleanNote !== '' ? $cleanNote : null,
            'requested_at' => $requestedAt,
        ]);

        if ($inserted === false) {
            return ['success' => false, 'message' => 'İade talebiniz oluşturulamadı.'];
        }

        $this->orderModel->update((string) $order['id'], [
            'status' => 'RETURN_REQUESTED',
            'order_status' => 'RETURN_REQUESTED',
            'fulfillment_status' => 'RETURN_REQUESTED',
        ]);

        return ['success' => true, 'message' => 'İade talebiniz alındı.'];
    }

    public function getShipmentTimeline(string $orderId): array
    {
        $shipment = $this->shipmentModel->where('order_id', $orderId)->first();
        $order = $this->orderModel->find($orderId);

        $steps = [
            'ORDER_RECEIVED' => ['title' => 'Sipariş Alındı'],
            'PREPARING' => ['title' => 'Sipariş Hazırlanıyor'],
            'SHIPPED' => ['title' => 'Kargoya Verildi'],
            'IN_TRANSIT' => ['title' => 'Yolda'],
            'OUT_FOR_DELIVERY' => ['title' => 'Dağıtıma Çıktı'],
            'DELIVERED' => ['title' => 'Teslim Edildi'],
        ];

        $events = [];
        if (is_array($shipment) && ! empty($shipment['id'])) {
            $events = $this->shipmentEventModel
                ->where('shipment_id', (string) $shipment['id'])
                ->orderBy('event_time', 'ASC')
                ->orderBy('created_at', 'ASC')
                ->findAll();
        }

        $eventMap = [];
        foreach ($events as $event) {
            $normalized = $this->normalizeTimelineStatus((string) ($event['status'] ?? ''));
            if (! isset($steps[$normalized])) {
                continue;
            }

            $eventMap[$normalized] = [
                'title' => trim((string) ($event['title'] ?? '')) !== '' ? (string) $event['title'] : $steps[$normalized]['title'],
                'note' => (string) ($event['note'] ?? ''),
                'location' => (string) ($event['location'] ?? ''),
                'time' => (string) ($event['event_time'] ?? $event['created_at'] ?? ''),
            ];
        }

        if ($eventMap === []) {
            $eventMap = $this->buildFallbackTimelineMap($order, $shipment, $steps);
        }

        $currentStageIndex = $this->resolveCurrentTimelineIndex($order, $shipment);
        $timeline = [];
        $index = 0;

        foreach ($steps as $key => $step) {
            $event = $eventMap[$key] ?? null;
            $state = 'pending';

            if ($event !== null || $index < $currentStageIndex) {
                $state = 'completed';
            } elseif ($index === $currentStageIndex) {
                $state = 'current';
            }

            if ($currentStageIndex >= 5 && $key === 'DELIVERED') {
                $state = 'completed';
            }

            $timeline[] = [
                'key' => $key,
                'title' => (string) ($event['title'] ?? $step['title']),
                'note' => (string) ($event['note'] ?? ''),
                'location' => (string) ($event['location'] ?? ''),
                'time' => (string) ($event['time'] ?? ''),
                'state' => $state,
            ];
            $index++;
        }

        return $timeline;
    }

    public function mapOrderStatusLabel(string $status): string
    {
        return match ($status) {
            'DELIVERED' => 'Teslim Edildi',
            'OUT_FOR_DELIVERY' => 'Dağıtıma Çıktı',
            'SHIPPED', 'IN_TRANSIT' => 'Kargoda',
            'PREPARING' => 'Hazırlanıyor',
            'PAID' => 'Ödeme Alındı',
            'CANCELLED' => 'İptal Edildi',
            'RETURN_REQUESTED' => 'İade Talep Edildi',
            'REFUNDED', 'RETURNED' => 'İade Tamamlandı',
            default => 'Sipariş Alındı',
        };
    }

    public function mapPaymentStatusLabel(string $status): string
    {
        return match ($status) {
            'PAID' => 'Ödendi',
            'FAILED' => 'Başarısız',
            'REFUNDED' => 'İade Edildi',
            default => 'Beklemede',
        };
    }

    public function mapFulfillmentStatusLabel(string $status): string
    {
        return match ($status) {
            'DELIVERED' => 'Teslim Edildi',
            'OUT_FOR_DELIVERY' => 'Dağıtıma Çıktı',
            'SHIPPED', 'IN_TRANSIT' => 'Kargoda',
            'CANCELLED' => 'İptal Edildi',
            'RETURN_REQUESTED' => 'İade Talep Edildi',
            default => 'Hazırlanıyor',
        };
    }

    public function getReturnDisplayState(array $order, ?array $returnRequest = null): array
    {
        $status = $this->normalizeOrderStatus($order);

        if (is_array($returnRequest)) {
            $requestStatus = strtoupper(trim((string) ($returnRequest['status'] ?? 'PENDING')));

            return [
                'label' => 'İade Talebi',
                'message' => 'İade talebiniz alındı.',
                'can_request' => false,
                'button_label' => $this->mapReturnRequestStatusLabel($requestStatus),
                'status' => $requestStatus,
                'status_label' => $this->mapReturnRequestStatusLabel($requestStatus),
                'requested_at' => (string) ($returnRequest['requested_at'] ?? $returnRequest['created_at'] ?? ''),
                'reason' => (string) ($returnRequest['reason'] ?? ''),
                'note' => (string) ($returnRequest['note'] ?? ''),
            ];
        }

        if (in_array($status, ['RETURNED', 'REFUNDED'], true)) {
            return [
                'label' => 'İade Durumu',
                'message' => 'Bu sipariş için iade süreci tamamlanmış görünüyor.',
                'can_request' => false,
                'button_label' => 'İade Tamamlandı',
            ];
        }

        if ($status === 'DELIVERED') {
            return [
                'label' => 'İade Talebi',
                'message' => 'Teslim edilen siparişler için iade talebi oluşturabilirsiniz.',
                'can_request' => true,
                'button_label' => 'İade Talebi Oluştur',
            ];
        }

        return [
            'label' => 'İade Talebi',
            'message' => 'İade talebi teslimat sonrası açılır.',
            'can_request' => false,
            'button_label' => 'Teslimat Sonrası Açılır',
        ];
    }

    private function buildOrderListItem(array $order): array
    {
        $items = $this->orderItemModel
            ->where('order_id', (string) ($order['id'] ?? ''))
            ->orderBy('created_at', 'ASC')
            ->findAll();
        $shipment = $this->shipmentModel
            ->where('order_id', (string) ($order['id'] ?? ''))
            ->first();
        $returnRequest = $this->findLatestReturnRequest((string) ($order['id'] ?? ''), (string) ($order['user_id'] ?? ''));

        $thumbnails = [];
        $itemNames = [];
        foreach (array_slice($items, 0, 3) as $item) {
            $image = trim((string) ($item['product_image'] ?? ''));
            if ($image !== '') {
                $thumbnails[] = product_image_url($image);
            }
            $itemNames[] = (string) ($item['product_name_snapshot'] ?? 'Ürün');
        }

        $status = $this->normalizeOrderStatus($order);
        $fulfillmentStatus = $this->normalizeFulfillmentStatus($order, $shipment);
        $paymentStatus = $this->normalizePaymentStatus((string) ($order['payment_status'] ?? 'PENDING'));

        return [
            'order_id' => (string) ($order['id'] ?? ''),
            'order_number' => (string) ($order['order_no'] ?? $order['id'] ?? ''),
            'order_date' => (string) ($order['order_date'] ?? $order['created_at'] ?? ''),
            'status' => $status,
            'status_label' => $this->mapOrderStatusLabel($status),
            'status_badge_class' => $this->mapStatusBadgeClass($status),
            'payment_status_label' => $this->mapPaymentStatusLabel($paymentStatus),
            'fulfillment_status_label' => $this->mapFulfillmentStatusLabel($fulfillmentStatus),
            'item_count' => (int) ($order['item_count'] ?? count($items) ?: (int) ($order['quantity'] ?? 0)),
            'item_names' => $itemNames,
            'thumbnails' => $thumbnails,
            'total_amount' => $this->resolveAmount($order, 'total_amount', (float) ($order['total_price'] ?? 0)),
            'currency' => (string) ($order['currency'] ?? 'TRY'),
            'estimated_delivery_at' => (string) ($shipment['estimated_delivery_at'] ?? $order['estimated_delivery_at'] ?? ''),
            'delivered_at' => (string) ($shipment['delivered_at'] ?? $order['delivered_at'] ?? ''),
            'tracking_number' => (string) ($shipment['tracking_number'] ?? $order['tracking_number'] ?? ''),
            'carrier' => (string) ($shipment['carrier'] ?? $order['shipping_company'] ?? ''),
            'detail_url' => base_url('yardim/siparislerim/' . urlencode((string) ($order['order_no'] ?? $order['id'] ?? ''))),
            'has_return_request' => is_array($returnRequest),
            'return_request_status_label' => is_array($returnRequest)
                ? $this->mapReturnRequestStatusLabel((string) ($returnRequest['status'] ?? 'PENDING'))
                : '',
        ];
    }

    private function buildOrderItems(string $orderId): array
    {
        $rows = $this->orderItemModel
            ->where('order_id', $orderId)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        $items = [];
        foreach ($rows as $row) {
            $productId = trim((string) ($row['product_id'] ?? ''));
            $status = $this->normalizeItemStatus((string) ($row['item_status'] ?? 'PREPARING'));

            $items[] = [
                'product_id' => $productId,
                'product_name' => (string) ($row['product_name_snapshot'] ?? 'Ürün'),
                'author' => (string) ($row['author'] ?? ''),
                'image_url' => product_image_url((string) ($row['product_image'] ?? '')),
                'product_type' => (string) ($row['product_type'] ?? ''),
                'quantity' => max(1, (int) ($row['quantity'] ?? 1)),
                'unit_price' => (float) ($row['unit_price'] ?? 0),
                'line_total' => (float) ($row['line_total'] ?? 0),
                'item_status' => $status,
                'item_status_label' => $this->mapItemStatusLabel($status),
                'item_status_badge_class' => $this->mapStatusBadgeClass($status),
                'detail_url' => $productId !== '' ? base_url('products/detail/' . $productId) : null,
            ];
        }

        return $items;
    }

    private function buildFallbackTimelineMap(?array $order, ?array $shipment, array $steps): array
    {
        $map = [];
        $map['ORDER_RECEIVED'] = [
            'title' => $steps['ORDER_RECEIVED']['title'],
            'note' => 'Siparişiniz başarıyla alındı.',
            'location' => '',
            'time' => (string) ($order['order_date'] ?? $order['created_at'] ?? ''),
        ];

        $statusIndex = $this->resolveCurrentTimelineIndex($order, $shipment);
        if ($statusIndex >= 1) {
            $map['PREPARING'] = [
                'title' => $steps['PREPARING']['title'],
                'note' => 'Siparişiniz paketleme sürecinde.',
                'location' => '',
                'time' => (string) ($order['paid_at'] ?? $order['updated_at'] ?? ''),
            ];
        }
        if ($statusIndex >= 2) {
            $map['SHIPPED'] = [
                'title' => $steps['SHIPPED']['title'],
                'note' => 'Gönderiniz kargo firmasına teslim edildi.',
                'location' => '',
                'time' => (string) ($shipment['shipped_at'] ?? $order['shipped_at'] ?? ''),
            ];
        }
        if ($statusIndex >= 3) {
            $map['IN_TRANSIT'] = [
                'title' => $steps['IN_TRANSIT']['title'],
                'note' => 'Gönderiniz transfer sürecinde ilerliyor.',
                'location' => '',
                'time' => (string) ($shipment['shipped_at'] ?? $order['shipped_at'] ?? ''),
            ];
        }
        if ($statusIndex >= 4) {
            $map['OUT_FOR_DELIVERY'] = [
                'title' => $steps['OUT_FOR_DELIVERY']['title'],
                'note' => 'Paketiniz dağıtım aracına yüklendi.',
                'location' => '',
                'time' => (string) ($shipment['estimated_delivery_at'] ?? $order['estimated_delivery_at'] ?? ''),
            ];
        }
        if ($statusIndex >= 5) {
            $map['DELIVERED'] = [
                'title' => $steps['DELIVERED']['title'],
                'note' => 'Teslimat tamamlandı.',
                'location' => '',
                'time' => (string) ($shipment['delivered_at'] ?? $order['delivered_at'] ?? ''),
            ];
        }

        return $map;
    }

    private function resolveCurrentTimelineIndex(?array $order, ?array $shipment): int
    {
        $status = $this->normalizeFulfillmentStatus($order ?? [], $shipment);

        return match ($status) {
            'DELIVERED' => 5,
            'OUT_FOR_DELIVERY' => 4,
            'IN_TRANSIT' => 3,
            'SHIPPED' => 2,
            'PREPARING' => 1,
            default => 0,
        };
    }

    private function normalizeOrderStatus(array $order): string
    {
        $candidates = [
            (string) ($order['status'] ?? ''),
            (string) ($order['order_status'] ?? ''),
            (string) ($order['fulfillment_status'] ?? ''),
            (string) ($order['shipping_status'] ?? ''),
        ];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeStatusToken($candidate);
            if ($normalized !== 'PENDING') {
                return $normalized;
            }
        }

        return 'PENDING';
    }

    private function normalizeFulfillmentStatus(array $order, ?array $shipment): string
    {
        $candidate = trim((string) ($order['fulfillment_status'] ?? ''));
        if ($candidate !== '') {
            return $this->normalizeStatusToken($candidate);
        }

        if (is_array($shipment) && trim((string) ($shipment['status'] ?? '')) !== '') {
            return $this->normalizeStatusToken((string) $shipment['status']);
        }

        if (trim((string) ($order['shipping_status'] ?? '')) !== '') {
            return $this->normalizeStatusToken((string) $order['shipping_status']);
        }

        if (trim((string) ($order['order_status'] ?? '')) !== '') {
            return $this->normalizeStatusToken((string) $order['order_status']);
        }

        return 'PREPARING';
    }

    private function normalizePaymentStatus(string $status): string
    {
        $status = strtoupper(trim($status));

        return match ($status) {
            'PAID' => 'PAID',
            'FAILED' => 'FAILED',
            'REFUNDED' => 'REFUNDED',
            default => 'PENDING',
        };
    }

    private function normalizeItemStatus(string $status): string
    {
        return $this->normalizeStatusToken($status);
    }

    private function normalizeTimelineStatus(string $status): string
    {
        return match ($this->normalizeStatusToken($status)) {
            'PENDING' => 'ORDER_RECEIVED',
            'PREPARING' => 'PREPARING',
            'SHIPPED' => 'SHIPPED',
            'IN_TRANSIT' => 'IN_TRANSIT',
            'OUT_FOR_DELIVERY' => 'OUT_FOR_DELIVERY',
            'DELIVERED' => 'DELIVERED',
            default => 'ORDER_RECEIVED',
        };
    }

    private function normalizeStatusToken(string $status): string
    {
        $value = strtoupper(trim($status));

        return match ($value) {
            'PAID' => 'PAID',
            'PREPARING', 'PENDING_FULFILLMENT' => 'PREPARING',
            'SHIPPED' => 'SHIPPED',
            'IN_TRANSIT', 'DELAYED' => 'IN_TRANSIT',
            'OUT_FOR_DELIVERY' => 'OUT_FOR_DELIVERY',
            'DELIVERED', 'COMPLETED' => 'DELIVERED',
            'CANCELLED', 'CANCELED' => 'CANCELLED',
            'RETURN_REQUESTED', 'RETURN_IN_PROGRESS' => 'RETURN_REQUESTED',
            'RETURN_DONE', 'RETURNED' => 'RETURNED',
            'REFUNDED' => 'REFUNDED',
            default => 'PENDING',
        };
    }

    private function mapItemStatusLabel(string $status): string
    {
        return match ($status) {
            'DELIVERED' => 'Teslim Edildi',
            'SHIPPED', 'IN_TRANSIT', 'OUT_FOR_DELIVERY' => 'Kargoda',
            'CANCELLED' => 'İptal Edildi',
            'RETURN_REQUESTED' => 'İade Talep Edildi',
            'RETURNED', 'REFUNDED' => 'İade Edildi',
            default => 'Hazırlanıyor',
        };
    }

    private function mapStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'DELIVERED' => 'success',
            'OUT_FOR_DELIVERY', 'SHIPPED', 'IN_TRANSIT' => 'info',
            'CANCELLED', 'REFUNDED', 'RETURNED' => 'danger',
            'RETURN_REQUESTED' => 'warning',
            default => 'secondary',
        };
    }

    private function mapReturnRequestStatusLabel(string $status): string
    {
        return match (strtoupper(trim($status))) {
            'APPROVED' => 'Onaylandı',
            'REJECTED' => 'Reddedildi',
            'CANCELLED' => 'İptal Edildi',
            'COMPLETED' => 'Tamamlandı',
            default => 'Beklemede',
        };
    }

    private function resolveAmount(array $row, string $field, float $fallback): float
    {
        if (array_key_exists($field, $row) && $row[$field] !== null && $row[$field] !== '') {
            return (float) $row[$field];
        }

        return $fallback;
    }

    private function matchesScope(array $order, string $scope): bool
    {
        if ($scope === 'all') {
            return true;
        }

        $status = strtoupper(trim((string) ($order['status'] ?? 'PENDING')));
        $hasReturnRequest = (bool) ($order['has_return_request'] ?? false);

        if ($scope === 'active') {
            return in_array($status, ['PENDING', 'PAID', 'PREPARING', 'SHIPPED', 'IN_TRANSIT', 'OUT_FOR_DELIVERY'], true);
        }

        if ($scope === 'delivered') {
            return $status === 'DELIVERED';
        }

        if ($scope === 'cancelled_return') {
            return $hasReturnRequest || in_array($status, ['CANCELLED', 'RETURN_REQUESTED', 'RETURNED', 'REFUNDED'], true);
        }

        return true;
    }

    private function getOwnedOrderRecord(string $userId, string $orderNoOrId): ?array
    {
        $identifier = trim($orderNoOrId);
        if ($identifier === '') {
            return null;
        }

        $row = $this->orderModel
            ->builder()
            ->select('orders.*')
            ->where('orders.user_id', $userId)
            ->where('orders.deleted_at', null)
            ->groupStart()
            ->where('orders.id', $identifier)
            ->orWhere('orders.order_no', $identifier)
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    private function findLatestReturnRequest(string $orderId, string $userId): ?array
    {
        if (! $this->returnRequestModel->db->tableExists('return_requests')) {
            return null;
        }

        $row = $this->returnRequestModel
            ->where('order_id', $orderId)
            ->where('user_id', $userId)
            ->orderBy('requested_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();

        return is_array($row) ? $row : null;
    }
}
