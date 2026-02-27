<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderItemModel;
use App\Models\OrderLogModel;
use App\Models\OrderModel;
use App\Models\ProductsModel;
use App\Services\OrdersService;

class Orders extends BaseController
{
    public function __construct(
        private ?OrdersService $ordersService = null
    ) {
        $this->ordersService = $this->ordersService ?? new OrdersService();
    }

    public function index()
    {
        $user = session()->get('user') ?? [];

        return view('admin/orders/index', [
            'title' => 'Siparişler',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'summary' => $this->getSummaryCounts(),
        ]);
    }

    public function summary()
    {
        if (! $this->canManageOrders()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Yetkisiz istek.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'summary' => $this->getSummaryCounts(),
            'csrf' => [
                'token' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ]);
    }

    public function analytics()
    {
        if (! $this->canManageOrders()) {
            $payload = [
                'success' => false,
                'message' => 'Yetkisiz istek.',
            ];

            return $this->response
                ->setStatusCode(403)
                ->setHeader('Content-Type', 'application/json; charset=utf-8')
                ->setBody((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $range = strtolower(trim((string) ($this->request->getGet('range') ?? 'daily')));
        if (! in_array($range, ['daily', 'weekly', 'monthly'], true)) {
            $range = 'daily';
        }

        $db = db_connect();
        $today = new \DateTimeImmutable('today');
        $categories = [];
        $rowsByLabel = [];
        $labelExpr = "DATE_FORMAT(DATE(COALESCE(order_date, created_at)), '%Y-%m-%d')";
        $fromDate = $today->modify('-29 days')->format('Y-m-d');

        if ($range === 'weekly') {
            $labelExpr = "DATE_FORMAT(DATE_SUB(DATE(COALESCE(order_date, created_at)), INTERVAL WEEKDAY(DATE(COALESCE(order_date, created_at))) DAY), '%Y-%m-%d')";
            $weekStart = $today->modify('-11 weeks')->modify('monday this week');
            $fromDate = $weekStart->format('Y-m-d');

            for ($i = 0; $i < 12; $i++) {
                $categories[] = $weekStart->modify('+' . $i . ' week')->format('Y-m-d');
            }
        } elseif ($range === 'monthly') {
            $labelExpr = "DATE_FORMAT(DATE(COALESCE(order_date, created_at)), '%Y-%m')";
            $monthStart = $today->modify('first day of this month')->modify('-11 months');
            $fromDate = $monthStart->format('Y-m-d');

            for ($i = 0; $i < 12; $i++) {
                $categories[] = $monthStart->modify('+' . $i . ' month')->format('Y-m');
            }
        } else {
            for ($i = 0; $i < 30; $i++) {
                $categories[] = $today->modify('-' . (29 - $i) . ' days')->format('Y-m-d');
            }
        }

        $rows = $db->table('orders')
            ->select($labelExpr . ' AS period_label', false)
            ->select('COALESCE(SUM(total_amount), 0) AS total_amount', false)
            ->where("DATE(COALESCE(order_date, created_at)) >= " . $db->escape($fromDate), null, false)
            ->groupBy('period_label')
            ->orderBy('period_label', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $key = trim((string) ($row['period_label'] ?? ''));
            if ($key === '') {
                continue;
            }

            $rowsByLabel[$key] = round((float) ($row['total_amount'] ?? 0), 2);
        }

        $seriesData = [];
        foreach ($categories as $label) {
            $seriesData[] = $rowsByLabel[$label] ?? 0;
        }

        $payload = [
            'success' => true,
            'range' => $range,
            'categories' => $categories,
            'series' => [[
                'name' => 'Toplam Tutar',
                'data' => $seriesData,
            ]],
            'currency' => '&#8378;',
            'csrf' => [
                'token' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ];

        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setBody((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function statusDistribution()
    {
        if (! $this->canManageOrders()) {
            $payload = [
                'success' => false,
                'message' => 'Yetkisiz istek.',
            ];

            return $this->response
                ->setStatusCode(403)
                ->setHeader('Content-Type', 'application/json; charset=utf-8')
                ->setBody((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $range = strtolower(trim((string) ($this->request->getGet('range') ?? 'weekly')));
        if ($range !== 'weekly') {
            $range = 'weekly';
        }

        $statuses = [
            'pending' => 'Beklemede',
            'preparing' => 'Hazırlanıyor',
            'packed' => 'Paketlendi',
            'shipped' => 'Kargoya Verildi',
            'delivered' => 'Teslim Edildi',
            'cancelled' => 'İptal Edildi',
            'return_in_progress' => 'İade Sürecinde',
            'return_done' => 'İade Tamamlandı',
        ];

        $dateFrom = (new \DateTimeImmutable('today'))->modify('-6 days')->format('Y-m-d');
        $db = db_connect();
        $rows = $db->table('orders')
            ->select('COALESCE(NULLIF(order_status, \'\'), status) AS normalized_status', false)
            ->select('COUNT(*) AS total_count', false)
            ->where("DATE(COALESCE(order_date, created_at)) >= " . $db->escape($dateFrom), null, false)
            ->groupBy('normalized_status')
            ->get()
            ->getResultArray();

        $countsByStatus = [];
        foreach ($rows as $row) {
            $key = trim((string) ($row['normalized_status'] ?? ''));
            if ($key === '') {
                continue;
            }

            $countsByStatus[$key] = (int) ($row['total_count'] ?? 0);
        }

        $series = [];
        foreach ($statuses as $statusKey => $label) {
            $series[] = [
                'name' => $label,
                'data' => [$countsByStatus[$statusKey] ?? 0],
            ];
        }

        $payload = [
            'success' => true,
            'range' => $range,
            'categories' => ['Son 7 Gün'],
            'series' => $series,
            'csrf' => [
                'token' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ];

        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setBody((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function datatables()
    {
        $params = $this->request->getGet();
        $result = $this->ordersService->datatablesList($params);
        $rows = $result['data'] ?? [];

        $data = array_map(function (array $row) {
            $id = (string) ($row['id'] ?? '');
            $orderNo = trim((string) ($row['order_no'] ?? ''));
            if ($orderNo === '') {
                $orderNo = '#' . strtoupper(substr(str_replace('-', '', $id), 0, 8));
            }

            $date = (string) ($row['created_at'] ?? $row['order_date'] ?? '-');
            $amount = number_format((float) ($row['total_amount'] ?? 0), 2, ',', '.');
            $detailHref = $id !== '' ? site_url('admin/orders/' . $id) : '#';

            return [
                'order_no' => esc($orderNo),
                'customer' => esc((string) ($row['customer_display'] ?? '-')),
                'date' => esc($date),
                'total_amount' => $amount . ' &#8378;',
                'payment_status' => $this->renderInlineStatusDropdown(
                    $id,
                    'payment_status',
                    (string) ($row['payment_status'] ?? 'unpaid')
                ),
                'order_status' => $this->renderInlineStatusDropdown(
                    $id,
                    'order_status',
                    (string) ($row['order_status'] ?? $row['status'] ?? 'pending')
                ),
                'shipping_status' => $this->shippingStatusBadge((string) ($row['shipping_status'] ?? 'not_shipped')),
                'actions' => '<a href="' . esc($detailHref) . '" class="btn btn-sm btn-outline-primary">Detay Gör</a>',
            ];
        }, $rows);

        $payload = [
            'draw' => (int) ($params['draw'] ?? 0),
            'recordsTotal' => (int) ($result['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
            'data' => $data,
        ];

        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setBody((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    public function inlineStatusUpdate()
    {
        if (! $this->canManageOrders()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Yetkisiz istek.',
            ]);
        }

        $orderId = trim((string) ($this->request->getPost('order_id') ?? ''));
        $field = trim((string) ($this->request->getPost('field') ?? ''));
        $value = trim((string) ($this->request->getPost('value') ?? ''));

        if ($orderId === '' || ! in_array($field, ['order_status', 'payment_status'], true) || $value === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Geçersiz istek.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($orderId);
        if (! $order) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Sipariş bulunamadı.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        $allowedOrderStatuses = ['pending', 'preparing', 'packed', 'shipped', 'delivered', 'cancelled', 'return_in_progress', 'return_done'];
        $allowedPaymentStatuses = ['unpaid', 'paid', 'refunded', 'partial_refund', 'failed'];

        if ($field === 'order_status' && ! in_array($value, $allowedOrderStatuses, true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Geçersiz sipariş durumu.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        if ($field === 'payment_status' && ! in_array($value, $allowedPaymentStatuses, true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Geçersiz ödeme durumu.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        $actor = $this->getActor();
        $now = date('Y-m-d H:i:s');
        $update = ['updated_by' => $actor['id'] !== '' ? $actor['id'] : null];

        if ($field === 'order_status') {
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

            $orderModel->update((string) $order['id'], $update);
            $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'status_changed', $fromStatus, $value, 'SipariÃƒâ€¦Ã…Â¸ durumu gÃƒÆ’Ã‚Â¼ncellendi.');
        } else {
            $fromStatus = (string) ($order['payment_status'] ?? '');
            $update['payment_status'] = $value;
            if ($value === 'paid' && empty($order['paid_at'])) {
                $update['paid_at'] = $now;
            }

            $orderModel->update((string) $order['id'], $update);
            $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'payment_status_changed', $fromStatus, $value, 'ÃƒÆ’Ã¢â‚¬â€œdeme durumu gÃƒÆ’Ã‚Â¼ncellendi.');
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Durum gÃƒÆ’Ã‚Â¼ncellendi.',
            'summary' => $this->getSummaryCounts(),
            'csrf' => [
                'token' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ]);
    }

    public function show(string $identifier)
    {
        $user = session()->get('user') ?? [];
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);

        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃƒâ€¦Ã…Â¸ bulunamadÃƒâ€Ã‚Â±.');
        }

        $items = $orderModel->getOrderItems((string) $order['id']);
        if ($items === []) {
            $qty = max(1, (int) ($order['quantity'] ?? 1));
            $total = (float) ($order['total_amount'] ?? 0);
            $unitPrice = $qty > 0 ? ($total / $qty) : $total;
            $items = [[
                'product_name_snapshot' => (string) ($order['product_name'] ?? '-'),
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'line_total' => $total,
            ]];
        }

        $logs = $orderModel->getOrderLogs((string) $order['id']);

        return view('admin/orders/show', [
            'title' => 'Sipariş Detayı',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'order' => $order,
            'items' => $items,
            'logs' => $logs,
        ]);
    }

    public function create()
    {
        $rules = [
            'product_id' => 'required|max_length[64]',
            'quantity' => 'required|integer|greater_than[0]',
            'customer_name' => 'permit_empty|max_length[191]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator)->with('error', 'SipariÃƒâ€¦Ã…Â¸ bilgileri geÃƒÆ’Ã‚Â§ersiz.');
        }

        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'KullanÃƒâ€Ã‚Â±cÃƒâ€Ã‚Â± oturumu bulunamadÃƒâ€Ã‚Â±.');
        }

        $productId = trim((string) $this->request->getPost('product_id'));
        $quantity = (int) $this->request->getPost('quantity');
        $customerName = trim((string) ($this->request->getPost('customer_name') ?? ''));

        $orderId = $this->ordersService->createReservedOrder(
            $productId,
            $quantity,
            $actor['id'],
            $customerName !== '' ? $customerName : null
        );

        if (! $orderId) {
            return redirect()->back()->withInput()->with('error', 'SipariÃƒâ€¦Ã…Â¸ oluÃƒâ€¦Ã…Â¸turulamadÃƒâ€Ã‚Â±. SatÃƒâ€Ã‚Â±labilir stok yetersiz olabilir.');
        }

        $orderNo = 'ORD-' . strtoupper(substr(str_replace('-', '', $orderId), 0, 10));
        (new OrderModel())->update($orderId, [
            'order_no' => $orderNo,
            'payment_method' => 'unknown',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'shipping_status' => 'not_shipped',
            'updated_by' => $actor['id'],
        ]);

        $this->upsertOrderItemSnapshot($orderId);
        $this->logOrderAction($orderId, $actor['id'], $actor['role'], 'order_created', null, 'pending', 'SipariÃƒâ€¦Ã…Â¸ oluÃƒâ€¦Ã…Â¸turuldu.');

        return redirect()->back()->with('success', 'SipariÃƒâ€¦Ã…Â¸ rezerve edildi.');
    }

    public function ship(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'KullanÃƒâ€Ã‚Â±cÃƒâ€Ã‚Â± oturumu bulunamadÃƒâ€Ã‚Â±.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($id);
        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');

        if (! $this->ordersService->shipOrder($id, $actor['id'])) {
            return redirect()->back()->with('error', 'SipariÃƒâ€¦Ã…Â¸ kargoya verilemedi.');
        }

        $this->logOrderAction($id, $actor['id'], $actor['role'], 'order_shipped', $fromStatus, 'shipped', 'SipariÃƒâ€¦Ã…Â¸ kargoya verildi.');

        return redirect()->back()->with('success', 'SipariÃƒâ€¦Ã…Â¸ kargoya verildi.');
    }

    public function cancel(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'KullanÃƒâ€Ã‚Â±cÃƒâ€Ã‚Â± oturumu bulunamadÃƒâ€Ã‚Â±.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($id);
        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');

        if (! $this->ordersService->cancelOrder($id, $actor['id'])) {
            return redirect()->back()->with('error', 'SipariÃƒâ€¦Ã…Â¸ iptal edilemedi.');
        }

        $this->logOrderAction($id, $actor['id'], $actor['role'], 'order_cancelled', $fromStatus, 'cancelled', 'SipariÃƒâ€¦Ã…Â¸ iptal edildi.');

        return redirect()->back()->with('success', 'SipariÃƒâ€¦Ã…Â¸ iptal edildi.');
    }

    public function return(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'KullanÃƒâ€Ã‚Â±cÃƒâ€Ã‚Â± oturumu bulunamadÃƒâ€Ã‚Â±.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($id);
        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');

        if (! $this->ordersService->returnOrder($id, $actor['id'])) {
            return redirect()->back()->with('error', 'SipariÃƒâ€¦Ã…Â¸ iadesi iÃƒâ€¦Ã…Â¸lenemedi.');
        }

        $this->logOrderAction($id, $actor['id'], $actor['role'], 'return_completed', $fromStatus, 'return_done', 'Ãƒâ€Ã‚Â°ade tamamlandÃƒâ€Ã‚Â±.');

        return redirect()->back()->with('success', 'SipariÃƒâ€¦Ã…Â¸ iadesi iÃƒâ€¦Ã…Â¸lendi.');
    }

    public function updateStatus(string $identifier)
    {
        $rules = [
            'order_status' => 'required|in_list[pending,preparing,packed,shipped,delivered,cancelled,return_in_progress,return_done]',
            'payment_status' => 'permit_empty|in_list[unpaid,paid,refunded,partial_refund,failed]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Durum gÃƒÆ’Ã‚Â¼ncelleme verisi geÃƒÆ’Ã‚Â§ersiz.');
        }

        $actor = $this->getActor();
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃƒâ€¦Ã…Â¸ bulunamadÃƒâ€Ã‚Â±.');
        }

        $toStatus = (string) $this->request->getPost('order_status');
        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        $paymentStatus = trim((string) ($this->request->getPost('payment_status') ?? ''));
        $shippingStatus = $this->mapShippingStatusByOrderStatus($toStatus);

        $update = [
            'order_status' => $toStatus,
            'status' => $this->mapLegacyStatus($toStatus),
            'shipping_status' => $shippingStatus,
            'updated_by' => $actor['id'] !== '' ? $actor['id'] : null,
        ];

        $now = date('Y-m-d H:i:s');
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
        if ($paymentStatus !== '') {
            $update['payment_status'] = $paymentStatus;
            if ($paymentStatus === 'paid' && empty($order['paid_at'])) {
                $update['paid_at'] = $now;
            }
        }

        $orderModel->update((string) $order['id'], $update);
        $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'status_changed', $fromStatus, $toStatus, 'SipariÃƒâ€¦Ã…Â¸ durumu gÃƒÆ’Ã‚Â¼ncellendi.');

        if ($paymentStatus !== '' && $paymentStatus !== (string) ($order['payment_status'] ?? '')) {
            $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'payment_status_changed', (string) ($order['payment_status'] ?? ''), $paymentStatus, 'ÃƒÆ’Ã¢â‚¬â€œdeme durumu gÃƒÆ’Ã‚Â¼ncellendi.');
        }

        return redirect()->back()->with('success', 'SipariÃƒâ€¦Ã…Â¸ durumu gÃƒÆ’Ã‚Â¼ncellendi.');
    }

    public function updateShipping(string $identifier)
    {
        $rules = [
            'shipping_company' => 'permit_empty|max_length[120]',
            'tracking_number' => 'permit_empty|max_length[120]',
            'shipping_status' => 'permit_empty|in_list[not_shipped,shipped,delivered,returned]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Kargo bilgileri geÃƒÆ’Ã‚Â§ersiz.');
        }

        $actor = $this->getActor();
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃƒâ€¦Ã…Â¸ bulunamadÃƒâ€Ã‚Â±.');
        }

        $shippingCompany = trim((string) ($this->request->getPost('shipping_company') ?? ''));
        $trackingNumber = trim((string) ($this->request->getPost('tracking_number') ?? ''));
        $shippingStatus = trim((string) ($this->request->getPost('shipping_status') ?? ''));

        if ($shippingStatus === '' && $trackingNumber !== '') {
            $shippingStatus = 'shipped';
        }
        if ($shippingStatus === '') {
            $shippingStatus = (string) ($order['shipping_status'] ?? 'not_shipped');
        }

        $update = [
            'shipping_company' => $shippingCompany !== '' ? $shippingCompany : null,
            'tracking_number' => $trackingNumber !== '' ? $trackingNumber : null,
            'shipping_status' => $shippingStatus,
            'updated_by' => $actor['id'] !== '' ? $actor['id'] : null,
        ];

        $now = date('Y-m-d H:i:s');
        if ($shippingStatus === 'shipped' && empty($order['shipped_at'])) {
            $update['shipped_at'] = $now;
        }
        if ($shippingStatus === 'delivered') {
            $update['delivered_at'] = $now;
            $update['order_status'] = 'delivered';
            $update['status'] = 'completed';
        }
        if ($shippingStatus === 'returned') {
            $update['order_status'] = 'return_in_progress';
            if (empty($order['return_started_at'])) {
                $update['return_started_at'] = $now;
            }
        }

        $orderModel->update((string) $order['id'], $update);
        $this->logOrderAction(
            (string) $order['id'],
            $actor['id'],
            $actor['role'],
            'shipping_updated',
            (string) ($order['shipping_status'] ?? ''),
            $shippingStatus,
            'Kargo bilgileri gÃƒÆ’Ã‚Â¼ncellendi.',
            [
                'shipping_company' => $shippingCompany,
                'tracking_number' => $trackingNumber,
            ]
        );

        return redirect()->back()->with('success', 'Kargo bilgileri gÃƒÆ’Ã‚Â¼ncellendi.');
    }

    public function addNote(string $identifier)
    {
        $rules = ['note' => 'required|min_length[2]|max_length[2000]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Not alanÃƒâ€Ã‚Â± geÃƒÆ’Ã‚Â§ersiz.');
        }

        $actor = $this->getActor();
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃƒâ€¦Ã…Â¸ bulunamadÃƒâ€Ã‚Â±.');
        }

        $note = trim((string) $this->request->getPost('note'));
        $prefix = '[' . date('Y-m-d H:i') . '] ' . ($actor['role'] !== '' ? $actor['role'] : 'admin');
        $existing = trim((string) ($order['notes_admin'] ?? ''));
        $updatedNote = $existing === '' ? ($prefix . ': ' . $note) : ($existing . PHP_EOL . $prefix . ': ' . $note);

        $orderModel->update((string) $order['id'], [
            'notes_admin' => $updatedNote,
            'updated_by' => $actor['id'] !== '' ? $actor['id'] : null,
        ]);

        $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'admin_note_added', null, null, $note);

        return redirect()->back()->with('success', 'SipariÃƒâ€¦Ã…Â¸ notu eklendi.');
    }

    public function startReturn(string $identifier)
    {
        $actor = $this->getActor();
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃƒâ€¦Ã…Â¸ bulunamadÃƒâ€Ã‚Â±.');
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        $orderModel->update((string) $order['id'], [
            'order_status' => 'return_in_progress',
            'shipping_status' => 'returned',
            'return_started_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actor['id'] !== '' ? $actor['id'] : null,
        ]);

        $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'return_started', $fromStatus, 'return_in_progress', 'Ãƒâ€Ã‚Â°ade sÃƒÆ’Ã‚Â¼reci baÃƒâ€¦Ã…Â¸latÃƒâ€Ã‚Â±ldÃƒâ€Ã‚Â±.');

        return redirect()->back()->with('success', 'Ãƒâ€Ã‚Â°ade sÃƒÆ’Ã‚Â¼reci baÃƒâ€¦Ã…Â¸latÃƒâ€Ã‚Â±ldÃƒâ€Ã‚Â±.');
    }

    public function completeReturn(string $identifier)
    {
        $actor = $this->getActor();
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃƒâ€¦Ã…Â¸ bulunamadÃƒâ€Ã‚Â±.');
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        if (! $this->ordersService->returnOrder((string) $order['id'], $actor['id'])) {
            return redirect()->back()->with('error', 'Ãƒâ€Ã‚Â°ade tamamlanamadÃƒâ€Ã‚Â±.');
        }

        $orderModel->update((string) $order['id'], [
            'order_status' => 'return_done',
            'return_completed_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actor['id'] !== '' ? $actor['id'] : null,
        ]);

        $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'return_completed', $fromStatus, 'return_done', 'Ãƒâ€Ã‚Â°ade tamamlandÃƒâ€Ã‚Â±.');

        return redirect()->back()->with('success', 'Ãƒâ€Ã‚Â°ade tamamlandÃƒâ€Ã‚Â±.');
    }

    private function upsertOrderItemSnapshot(string $orderId): void
    {
        $db = db_connect();
        if (! $db->tableExists('order_items')) {
            return;
        }

        $order = (new OrderModel())->find($orderId);
        if (! $order) {
            return;
        }

        $product = (new ProductsModel())
            ->select('product_name, price')
            ->where('id', (string) ($order['product_id'] ?? ''))
            ->first();

        $quantity = max(1, (int) ($order['quantity'] ?? 1));
        $unitPrice = (float) ($product['price'] ?? 0);
        $lineTotal = (float) ($order['total_amount'] ?? ($unitPrice * $quantity));

        $itemModel = new OrderItemModel();
        $exists = $itemModel->where('order_id', $orderId)->countAllResults();
        if ($exists > 0) {
            return;
        }

        $itemModel->insert([
            'order_id' => $orderId,
            'product_id' => (string) ($order['product_id'] ?? ''),
            'product_name_snapshot' => (string) ($product['product_name'] ?? 'ÃƒÆ’Ã…â€œrÃƒÆ’Ã‚Â¼n'),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
        ]);
    }

    private function getActor(): array
    {
        $user = session()->get('user') ?? [];
        return [
            'id' => trim((string) ($user['id'] ?? '')),
            'role' => trim((string) ($user['role'] ?? 'admin')),
        ];
    }

    private function canManageOrders(): bool
    {
        $user = session()->get('user') ?? [];
        $role = strtolower((string) ($user['role'] ?? ''));
        return in_array($role, ['admin', 'secretary'], true);
    }

    private function logOrderAction(
        string $orderId,
        string $actorUserId,
        string $actorRole,
        string $action,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $message = null,
        ?array $meta = null
    ): void {
        $db = db_connect();
        if (! $db->tableExists('order_logs')) {
            return;
        }

        $metaJson = $meta !== null ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null;

        (new OrderLogModel())->insert([
            'order_id' => $orderId,
            'actor_user_id' => $actorUserId !== '' ? $actorUserId : null,
            'actor_role' => $actorRole !== '' ? $actorRole : null,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'message' => $message,
            'meta_json' => $metaJson,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
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

    private function getSummaryCounts(): array
    {
        return [
            'total' => (new OrderModel())->countAllOrders(),
            'pending' => $this->countByOrderStatus(['pending', 'preparing']),
            'shipped' => $this->countByOrderStatus(['shipped', 'delivered']),
            'returned' => $this->countByOrderStatus(['return_in_progress', 'return_done']),
            'cancelled' => $this->countByOrderStatus(['cancelled']),
        ];
    }

    private function countByOrderStatus(array $statuses): int
    {
        return (int) (new OrderModel())
            ->builder()
            ->groupStart()
            ->whereIn('order_status', $statuses)
            ->orWhereIn('status', $statuses)
            ->groupEnd()
            ->countAllResults();
    }

    private function paymentStatusBadge(string $status): string
    {
        $labels = [
            'unpaid' => 'Ödenmedi',
            'paid' => 'Ödendi',
            'refunded' => 'İade Edildi',
            'partial_refund' => 'Kısmi İade',
            'failed' => 'Başarısız',
        ];
        $label = $labels[$status] ?? $labels['unpaid'];

        return match ($status) {
            'paid' => '<span class="badge bg-light-success text-success">' . esc($label) . '</span>',
            'refunded' => '<span class="badge bg-light-warning text-warning">' . esc($label) . '</span>',
            'partial_refund' => '<span class="badge bg-light-info text-info">' . esc($label) . '</span>',
            'failed' => '<span class="badge bg-light-danger text-danger">' . esc($label) . '</span>',
            default => '<span class="badge bg-light-secondary text-secondary">' . esc($label) . '</span>',
        };
    }

    private function orderStatusBadge(string $status): string
    {
        $labels = [
            'pending' => 'Beklemede',
            'preparing' => 'Hazırlanıyor',
            'packed' => 'Paketlendi',
            'shipped' => 'Kargoya Verildi',
            'delivered' => 'Teslim Edildi',
            'cancelled' => 'İptal Edildi',
            'return_in_progress' => 'İade Sürecinde',
            'return_done' => 'İade Tamamlandı',
        ];
        $label = $labels[$status] ?? $labels['pending'];

        return match ($status) {
            'preparing', 'shipped' => '<span class="badge bg-light-primary text-primary">' . esc($label) . '</span>',
            'packed' => '<span class="badge bg-light-info text-info">' . esc($label) . '</span>',
            'delivered' => '<span class="badge bg-light-success text-success">' . esc($label) . '</span>',
            'cancelled' => '<span class="badge bg-light-danger text-danger">' . esc($label) . '</span>',
            'return_in_progress' => '<span class="badge bg-light-warning text-warning">' . esc($label) . '</span>',
            'return_done' => '<span class="badge bg-light-dark text-dark">' . esc($label) . '</span>',
            default => '<span class="badge bg-light-secondary text-secondary">' . esc($label) . '</span>',
        };
    }

    private function shippingStatusBadge(string $status): string
    {
        $labels = [
            'not_shipped' => 'Hazırlanmadı',
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim',
            'returned' => 'İade',
        ];
        $label = $labels[$status] ?? $labels['not_shipped'];

        return match ($status) {
            'shipped' => '<span class="badge bg-light-primary text-primary">' . esc($label) . '</span>',
            'delivered' => '<span class="badge bg-light-success text-success">' . esc($label) . '</span>',
            'returned' => '<span class="badge bg-light-warning text-warning">' . esc($label) . '</span>',
            default => '<span class="badge bg-light-secondary text-secondary">' . esc($label) . '</span>',
        };
    }

    private function renderInlineStatusDropdown(string $orderId, string $field, string $current): string
    {
        $currentBadge = $field === 'payment_status'
            ? $this->paymentStatusBadge($current)
            : $this->orderStatusBadge($current);

        $options = $this->statusOptions($field);
        $items = '';
        foreach ($options as $value => $label) {
            $items .= '<li><a href="#" class="dropdown-item js-inline-status-item" data-order-id="' . esc($orderId) . '" data-field="' . esc($field) . '" data-value="' . esc($value) . '">' . esc($label) . '</a></li>';
        }

        return '<div class="dropdown d-inline-block">'
            . '<a href="#" class="text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">' . $currentBadge . '</a>'
            . '<ul class="dropdown-menu">' . $items . '</ul>'
            . '</div>';
    }

    private function statusOptions(string $field): array
    {
        if ($field === 'payment_status') {
            return [
                'unpaid' => 'Ödenmedi',
                'paid' => 'Ödendi',
                'refunded' => 'İade Edildi',
                'partial_refund' => 'Kısmi İade',
                'failed' => 'Başarısız',
            ];
        }

        return [
            'pending' => 'Beklemede',
            'preparing' => 'Hazırlanıyor',
            'packed' => 'Paketlendi',
            'shipped' => 'Kargoya Verildi',
            'delivered' => 'Teslim Edildi',
            'cancelled' => 'İptal Edildi',
            'return_in_progress' => 'İade Sürecinde',
            'return_done' => 'İade Tamamlandı',
        ];
    }
}
