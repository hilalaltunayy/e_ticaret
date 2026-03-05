<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\OrderItemModel;
use App\Models\OrderLogModel;
use App\Models\OrderModel;
use App\Models\PackingSessionModel;
use App\Models\ProductsModel;
use App\Services\InvoiceService;
use App\Services\OrdersService;
use App\Services\PackingService;

class Orders extends BaseController
{
    public function __construct(
        private ?OrdersService $ordersService = null,
        private ?InvoiceService $invoiceService = null,
        private ?PackingService $packingService = null
    ) {
        $this->ordersService = $this->ordersService ?? new OrdersService();
        $this->invoiceService = $this->invoiceService ?? new InvoiceService();
        $this->packingService = $this->packingService ?? new PackingService();
    }

    public function index()
    {
        $user = session()->get('user') ?? [];

        return view('admin/orders/index', [
            'title' => 'SipariÃ…Å¸ler',
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
            'categories' => ['Son 7 GÃƒÂ¼n'],
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
                'message' => 'GeÃƒÂ§ersiz istek.',
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
                'message' => 'SipariÃ…Å¸ bulunamadÃ„Â±.',
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
                'message' => 'GeÃƒÂ§ersiz sipariÃ…Å¸ durumu.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        if ($field === 'payment_status' && ! in_array($value, $allowedPaymentStatuses, true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'GeÃƒÂ§ersiz ÃƒÂ¶deme durumu.',
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
            $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'status_changed', $fromStatus, $value, 'SipariÃ…Å¸ durumu gÃƒÂ¼ncellendi.');
        } else {
            $fromStatus = (string) ($order['payment_status'] ?? '');
            $update['payment_status'] = $value;
            if ($value === 'paid' && empty($order['paid_at'])) {
                $update['paid_at'] = $now;
            }

            $orderModel->update((string) $order['id'], $update);
            $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'payment_status_changed', $fromStatus, $value, 'ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œdeme durumu gÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¼ncellendi.');
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Durum gÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¼ncellendi.',
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
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
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
        $invoice = (new InvoiceModel())->findByOrderId((string) $order['id']);
        $invoiceEligibility = $this->invoiceService->evaluateInvoiceEligibility($order);
        $canCreateInvoice = (bool) ($invoiceEligibility['allowed'] ?? false);
        $invoiceBlockMessage = (string) ($invoiceEligibility['message'] ?? '');

        return view('admin/orders/show', [
            'title' => 'SipariÃ…Å¸ DetayÃ„Â±',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'order' => $order,
            'items' => $items,
            'logs' => $logs,
            'invoice' => $invoice,
            'canCreateInvoice' => $canCreateInvoice,
            'invoiceBlockMessage' => $invoiceBlockMessage,
        ]);
    }

    public function packingLabel(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return redirect()->back()->with('error', 'Yetkisiz istek.');
        }

        $user = session()->get('user') ?? [];
        $order = (new OrderModel())->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃƒâ€¦Ã…Â¸ bulunamadÃƒâ€Ã‚Â±.');
        }

        $actor = $this->getActor();
        $session = $this->packingService->createOrGetSession((string) $order['id'], $actor['id'] !== '' ? $actor['id'] : null);
        if (! is_array($session) || $session === []) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Paket doÃƒâ€Ã…Â¸rulama oturumu oluÃƒâ€¦Ã…Â¸turulamadÃƒâ€Ã‚Â±.');
        }

        $verifyUrl = site_url('admin/orders/' . (string) $order['id'] . '/packing/verify');

        return view('admin/orders/packing_label', [
            'title' => 'Paket Etiketi',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'order' => $order,
            'session' => $session,
            'verifyUrl' => $verifyUrl,
        ]);
    }

    public function packingVerify(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return redirect()->back()->with('error', 'Yetkisiz istek.');
        }

        $user = session()->get('user') ?? [];
        $order = (new OrderModel())->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $actor = $this->getActor();
        $session = $this->packingService->createOrGetSession((string) $order['id'], $actor['id'] !== '' ? $actor['id'] : null);
        if (! is_array($session) || $session === []) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Paket dogrulama oturumu bulunamadi.');
        }

        $session = (new PackingSessionModel())->find((string) ($session['id'] ?? '')) ?? $session;
        if (! is_array($session) || $session === []) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Paket dogrulama oturumu bulunamadi.');
        }

        $expectedItems = $this->decodeExpectedItemsForView((string) ($session['expected_items_json'] ?? ''));
        $scanState = $this->normalizeScanStateForView((string) ($session['scanned_items_json'] ?? ''));

        $expectedJson = json_encode($expectedItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $scanStateJson = json_encode($scanState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $session['expected_items_json'] = is_string($expectedJson) ? $expectedJson : '[]';
        $session['scanned_items_json'] = is_string($scanStateJson) ? $scanStateJson : '{"items":[],"unknown_scans":[]}';

        $verification = $this->packingService->getVerificationState($session);

        return view('admin/orders/packing_verify', [
            'title' => 'Paket Dogrulama',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'order' => $order,
            'session' => $session,
            'verification' => $verification,
            'expectedItems' => $expectedItems,
            'scanState' => $scanState,
        ]);
    }

    public function packingScan(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Yetkisiz istek.',
            ]);
        }

        $order = (new OrderModel())->findByIdOrOrderNo($identifier);
        if (! $order) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Siparis bulunamadi.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        $packingSessionModel = new PackingSessionModel();
        $session = $packingSessionModel
            ->where('order_id', (string) $order['id'])
            ->where('status', 'open')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (! is_array($session) || $session === []) {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'Acik paket dogrulama oturumu bulunamadi.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        $scanCode = trim((string) ($this->request->getPost('barcode') ?? ''));
        if ($scanCode === '') {
            $scanCode = trim((string) ($this->request->getPost('isbn') ?? ''));
        }
        if ($scanCode === '') {
            $scanCode = trim((string) ($this->request->getPost('product_id') ?? ''));
        }
        $qty = max(1, (int) ($this->request->getPost('qty') ?? 1));

        if ($scanCode === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Barkod veya ISBN zorunludur.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        $result = $this->packingService->applyScan($session, $scanCode, $qty);
        if (! ($result['success'] ?? false)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => (string) ($result['message'] ?? 'Okutma kaydedilemedi.'),
                'verification' => $result['verification'] ?? null,
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        $normalizedScannedJson = $this->packingService->normalizeScannedItemsJson(
            (string) ($result['scanned_json'] ?? '{"items":[],"unknown_scans":[]}')
        );

        $packingSessionModel->update((string) $session['id'], [
            'scanned_items_json' => $normalizedScannedJson,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => (string) ($result['message'] ?? 'Okutma kaydedildi.'),
            'verification' => $result['verification'] ?? null,
            'csrf' => [
                'token' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ]);
    }

    public function packingFinish(string $identifier)
    {
        if (! $this->canManageOrders()) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => 'Yetkisiz istek.',
                ]);
            }

            return redirect()->back()->with('error', 'Yetkisiz istek.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($identifier);
        if (! $order) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Siparis bulunamadi.',
                ]);
            }

            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $packingSessionModel = new PackingSessionModel();
        $session = $packingSessionModel
            ->where('order_id', (string) $order['id'])
            ->where('status', 'open')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (! is_array($session) || $session === []) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'Acik paket dogrulama oturumu bulunamadi.',
                ]);
            }

            return redirect()->to(site_url('admin/orders/' . (string) $order['id'] . '/packing/verify'))
                ->with('error', 'Acik paket dogrulama oturumu bulunamadi.');
        }

        $verification = $this->packingService->getVerificationState($session);
        if (! (bool) ($verification['can_finish'] ?? false)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Dogrulama tamamlanamadi. Eksik, fazla veya bilinmeyen okutma var.',
                    'verification' => $verification,
                    'csrf' => [
                        'token' => csrf_token(),
                        'hash' => csrf_hash(),
                    ],
                ]);
            }

            return redirect()->to(site_url('admin/orders/' . (string) $order['id'] . '/packing/verify'))
                ->with('error', 'Dogrulama tamamlanamadi. Eksik, fazla veya bilinmeyen okutma var.');
        }

        $normalizedScannedJson = $this->packingService->normalizeScannedItemsJson(
            (string) ($session['scanned_items_json'] ?? '')
        );

        $packingSessionModel->update((string) $session['id'], [
            'status' => 'verified',
            'verified_at' => date('Y-m-d H:i:s'),
            'scanned_items_json' => $normalizedScannedJson,
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Paket dogrulamasi tamamlandi.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        return redirect()->to(site_url('admin/orders/' . (string) $order['id'] . '/packing/verify'))
            ->with('success', 'Paket dogrulamasi tamamlandi.');
    }
    public function createInvoice(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return redirect()->back()->with('error', 'Yetkisiz istek.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
        }

        $invoiceEligibility = $this->invoiceService->evaluateInvoiceEligibility($order);
        if (! ($invoiceEligibility['allowed'] ?? false)) {
            return redirect()
                ->to(site_url('admin/orders/' . (string) $order['id']))
                ->with('error', (string) ($invoiceEligibility['message'] ?? 'Bu sipariÃ…Å¸ iÃƒÂ§in fatura oluÃ…Å¸turulamaz.'));
        }

        $result = $this->invoiceService->createForOrder((string) $order['id']);
        $invoice = is_array($result['invoice'] ?? null) ? $result['invoice'] : null;

        if (($result['code'] ?? '') === 'already_exists') {
            return redirect()
                ->to(site_url('admin/orders/' . (string) $order['id']))
                ->with('error', 'Bu sipariÃ…Å¸ iÃƒÂ§in fatura zaten oluÃ…Å¸turulmuÃ…Å¸.');
        }

        if (! ($result['success'] ?? false)) {
            return redirect()
                ->to(site_url('admin/orders/' . (string) $order['id']))
                ->with('error', (string) ($result['message'] ?? 'Fatura oluÃ…Å¸turulamadÃ„Â±.'));
        }

        if ($invoice !== null) {
            $actor = $this->getActor();
            $this->logOrderAction(
                (string) $order['id'],
                $actor['id'],
                $actor['role'],
                'invoice_generated',
                null,
                null,
                'Fatura oluÃ…Å¸turuldu: ' . (string) ($invoice['invoice_no'] ?? '-')
            );
        }

        return redirect()
            ->to(site_url('admin/orders/' . (string) $order['id']))
            ->with('success', 'Fatura oluÃ…Å¸turuldu.');
    }

    public function viewInvoice(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return $this->response->setStatusCode(403)->setBody('Yetkisiz istek.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
        }

        $invoice = $this->invoiceService->findByOrderId((string) $order['id']);
        if (! $invoice) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Fatura bulunamadÃ„Â±.');
        }

        $pdfPath = $this->resolveInvoicePdfPath((string) ($invoice['pdf_path'] ?? ''));
        if ($pdfPath === null) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Fatura PDF dosyasÃ„Â± bulunamadÃ„Â±.');
        }

        $fileName = basename($pdfPath);
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->setBody((string) file_get_contents($pdfPath));
    }

    public function downloadInvoice(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return $this->response->setStatusCode(403)->setBody('Yetkisiz istek.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
        }

        $invoice = $this->invoiceService->findByOrderId((string) $order['id']);
        if (! $invoice) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Fatura bulunamadÃ„Â±.');
        }

        $pdfPath = $this->resolveInvoicePdfPath((string) ($invoice['pdf_path'] ?? ''));
        if ($pdfPath === null) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Fatura PDF dosyasÃ„Â± bulunamadÃ„Â±.');
        }

        $fileName = basename($pdfPath);
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setBody((string) file_get_contents($pdfPath));
    }

    public function create()
    {
        $rules = [
            'product_id' => 'required|max_length[64]',
            'quantity' => 'required|integer|greater_than[0]',
            'customer_name' => 'permit_empty|max_length[191]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator)->with('error', 'SipariÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¸ bilgileri geÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â§ersiz.');
        }

        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'KullanÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â±cÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â± oturumu bulunamadÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â±.');
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
            return redirect()->back()->withInput()->with('error', 'SipariÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¸ oluÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¸turulamadÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â±. SatÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â±labilir stok yetersiz olabilir.');
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
        $this->logOrderAction($orderId, $actor['id'], $actor['role'], 'order_created', null, 'pending', 'SipariÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¸ oluÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¸turuldu.');

        return redirect()->back()->with('success', 'SipariÃ…Å¸ rezerve edildi.');
    }

    public function ship(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'KullanÃ„Â±cÃ„Â± oturumu bulunamadÃ„Â±.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($id);
        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');

        if (! $this->ordersService->shipOrder($id, $actor['id'])) {
            return redirect()->back()->with('error', 'SipariÃ…Å¸ kargoya verilemedi.');
        }

        $this->logOrderAction($id, $actor['id'], $actor['role'], 'order_shipped', $fromStatus, 'shipped', 'SipariÃ…Å¸ kargoya verildi.');

        return redirect()->back()->with('success', 'SipariÃ…Å¸ kargoya verildi.');
    }

    public function cancel(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'KullanÃ„Â±cÃ„Â± oturumu bulunamadÃ„Â±.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($id);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        if ($fromStatus === 'cancelled') {
            return redirect()->back()->with('success', 'SipariÃ…Å¸ zaten iptal edilmiÃ…Å¸.');
        }

        if (in_array($fromStatus, ['delivered', 'return_in_progress', 'return_done', 'returned'], true)) {
            return redirect()->back()->with('error', 'Teslim edilmiÃ…Å¸ veya iade sÃƒÂ¼recindeki sipariÃ…Å¸ iptal edilemez.');
        }

        $cancelled = $this->ordersService->cancelOrder($id, $actor['id']);
        if (! $cancelled) {
            $now = date('Y-m-d H:i:s');
            $cancelled = $orderModel->update((string) $order['id'], [
                'status' => 'cancelled',
                'order_status' => 'cancelled',
                'shipping_status' => 'not_shipped',
                'cancelled_at' => $now,
                'updated_by' => $actor['id'],
            ]);
        }

        if (! $cancelled) {
            return redirect()->back()->with('error', 'SipariÃ…Å¸ iptal edilemedi.');
        }

        $this->logOrderAction($id, $actor['id'], $actor['role'], 'order_cancelled', $fromStatus, 'cancelled', 'SipariÃ…Å¸ iptal edildi.');

        return redirect()->back()->with('success', 'SipariÃ…Å¸ iptal edildi.');
    }

    public function return(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'KullanÃ„Â±cÃ„Â± oturumu bulunamadÃ„Â±.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($id);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');

        if (! $this->ordersService->returnOrder($id, $actor['id'])) {
            return redirect()->back()->with('error', 'SipariÃ…Å¸ iadesi iÃ…Å¸lenemedi.');
        }

        $this->logOrderAction($id, $actor['id'], $actor['role'], 'return_completed', $fromStatus, 'return_done', 'İade tamamlandÃ„Â±.');

        return redirect()->back()->with('success', 'SipariÃ…Å¸ iadesi iÃ…Å¸lendi.');
    }

    public function updateStatus(string $identifier)
    {
        $rules = [
            'order_status' => 'required|in_list[pending,preparing,packed,shipped,delivered,cancelled,return_in_progress,return_done]',
            'payment_status' => 'permit_empty|in_list[unpaid,paid,refunded,partial_refund,failed]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Durum gÃƒÂ¼ncelleme verisi geÃƒÂ§ersiz.');
        }

        $actor = $this->getActor();
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
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
        $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'status_changed', $fromStatus, $toStatus, 'SipariÃ…Å¸ durumu gÃƒÂ¼ncellendi.');

        if ($paymentStatus !== '' && $paymentStatus !== (string) ($order['payment_status'] ?? '')) {
            $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'payment_status_changed', (string) ($order['payment_status'] ?? ''), $paymentStatus, 'Ãƒâ€“deme durumu gÃƒÂ¼ncellendi.');
        }

        return redirect()->back()->with('success', 'SipariÃ…Å¸ durumu gÃƒÂ¼ncellendi.');
    }

    public function updateShipping(string $identifier)
    {
        $rules = [
            'shipping_company' => 'permit_empty|max_length[120]',
            'tracking_number' => 'permit_empty|max_length[120]',
            'shipping_status' => 'permit_empty|in_list[not_shipped,shipped,delivered,returned]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Kargo bilgileri geÃƒÂ§ersiz.');
        }

        $actor = $this->getActor();
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
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
            'Kargo bilgisi gÃƒÂ¼ncellendi.',
            [
                'shipping_company' => $shippingCompany,
                'tracking_number' => $trackingNumber,
            ]
        );

        return redirect()->back()->with('success', 'Kargo bilgisi gÃƒÂ¼ncellendi.');
    }

    public function addNote(string $identifier)
    {
        $rules = ['note' => 'required|min_length[2]|max_length[2000]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Not alanÃ„Â± geÃƒÂ§ersiz.');
        }

        $actor = $this->getActor();
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
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

        return redirect()->back()->with('success', 'SipariÃ…Å¸ notu eklendi.');
    }

    public function startReturn(string $identifier)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'KullanÃ„Â±cÃ„Â± oturumu bulunamadÃ„Â±.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        if ($fromStatus !== 'delivered') {
            return redirect()->back()->with('error', 'Teslim edilmemiÃ…Å¸ sipariÃ…Å¸ iÃƒÂ§in iade baÃ…Å¸latÃ„Â±lamaz.');
        }

        $orderModel->update((string) $order['id'], [
            'order_status' => 'return_in_progress',
            'shipping_status' => 'returned',
            'return_started_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actor['id'] !== '' ? $actor['id'] : null,
        ]);

        $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'return_started', $fromStatus, 'return_in_progress', 'İade sÃƒÂ¼reci baÃ…Å¸latÃ„Â±ldÃ„Â±.');

        return redirect()->back()->with('success', 'İade sÃƒÂ¼reci baÃ…Å¸latÃ„Â±ldÃ„Â±.');
    }

    public function completeReturn(string $identifier)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'KullanÃ„Â±cÃ„Â± oturumu bulunamadÃ„Â±.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'SipariÃ…Å¸ bulunamadÃ„Â±.');
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        if ($fromStatus !== 'return_in_progress') {
            return redirect()->back()->with('error', 'İade tamamlamak iÃƒÂ§in sipariÃ…Å¸ iade sÃƒÂ¼recinde olmalÃ„Â±dÃ„Â±r.');
        }

        if (! $this->ordersService->returnOrder((string) $order['id'], $actor['id'])) {
            return redirect()->back()->with('error', 'İade tamamlanamadÃ„Â±.');
        }

        $orderModel->update((string) $order['id'], [
            'order_status' => 'return_done',
            'return_completed_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actor['id'] !== '' ? $actor['id'] : null,
        ]);

        $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'return_completed', $fromStatus, 'return_done', 'İade tamamlandÃ„Â±.');

        return redirect()->back()->with('success', 'İade tamamlandÃ„Â±.');
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
            'product_name_snapshot' => (string) ($product['product_name'] ?? 'ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â¦ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œrÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¼n'),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
        ]);
    }

    private function decodeExpectedItemsForView(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        $items = [];
        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }

            $items[] = [
                'product_id' => trim((string) ($item['product_id'] ?? '')),
                'name' => trim((string) ($item['name'] ?? ($item['product_name'] ?? 'Urun'))),
                'qty' => max(1, (int) ($item['qty'] ?? ($item['expected_qty'] ?? 1))),
                'barcode' => trim((string) ($item['barcode'] ?? ($item['product_id'] ?? ''))),
                'isbn' => trim((string) ($item['isbn'] ?? '')),
            ];
        }

        return $items;
    }

    private function normalizeScanStateForView(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return ['items' => [], 'unknown_scans' => []];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return ['items' => [], 'unknown_scans' => []];
        }

        if (array_is_list($decoded)) {
            $items = [];
            foreach ($decoded as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $code = trim((string) ($row['code'] ?? $row['barcode'] ?? ''));
                if ($code === '') {
                    continue;
                }
                $items[] = [
                    'code' => $code,
                    'qty' => max(1, (int) ($row['qty'] ?? 1)),
                    'expected_key' => trim((string) ($row['expected_key'] ?? '')),
                    'name' => trim((string) ($row['name'] ?? '')),
                ];
            }

            return ['items' => $items, 'unknown_scans' => []];
        }

        $items = [];
        foreach ((array) ($decoded['items'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $code = trim((string) ($row['code'] ?? $row['barcode'] ?? ''));
            if ($code === '') {
                continue;
            }
            $items[] = [
                'code' => $code,
                'qty' => max(1, (int) ($row['qty'] ?? 1)),
                'expected_key' => trim((string) ($row['expected_key'] ?? '')),
                'name' => trim((string) ($row['name'] ?? '')),
            ];
        }

        $unknownScans = [];
        foreach ((array) ($decoded['unknown_scans'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $code = trim((string) ($row['code'] ?? $row['barcode'] ?? ''));
            if ($code === '') {
                continue;
            }
            $unknownScans[] = [
                'code' => $code,
                'qty' => max(1, (int) ($row['qty'] ?? 1)),
            ];
        }

        return [
            'items' => $items,
            'unknown_scans' => $unknownScans,
        ];
    }

    private function resolveInvoicePdfPath(string $relativePath): ?string
    {
        $path = trim($relativePath);
        if ($path === '') {
            return null;
        }

        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));
        $absolute = WRITEPATH . $normalized;
        if (! is_file($absolute)) {
            return null;
        }

        return $absolute;
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


