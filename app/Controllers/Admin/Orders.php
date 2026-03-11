<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderItemModel;
use App\Models\OrderLogModel;
use App\Models\OrderModel;
use App\Models\ProductsModel;
use App\Services\InvoiceService;
use App\Services\OrdersService;
use App\Services\OrdersReportingService;
use App\Services\PackingService;

class Orders extends BaseController
{
    public function __construct(
        private ?OrdersService $ordersService = null,
        private ?OrdersReportingService $ordersReportingService = null,
        private ?InvoiceService $invoiceService = null,
        private ?PackingService $packingService = null
    ) {
        $this->ordersService = $this->ordersService ?? new OrdersService();
        $this->ordersReportingService = $this->ordersReportingService ?? new OrdersReportingService();
        $this->invoiceService = $this->invoiceService ?? new InvoiceService();
        $this->packingService = $this->packingService ?? new PackingService();
    }

    public function index()
    {
        $user = session()->get('user') ?? [];

        return view('admin/orders/index', [
            'title' => "Siparisler",
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

        $payload = $this->ordersReportingService->buildAnalyticsPayload(
            (string) ($this->request->getGet('range') ?? 'daily')
        );

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

        $payload = $this->ordersReportingService->buildStatusDistributionPayload(
            (string) ($this->request->getGet('range') ?? 'weekly')
        );

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
                'actions' => '<a href="' . esc($detailHref) . '" class="btn btn-sm btn-outline-primary">Detay Gor</a>',
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
                'message' => "Gecersiz istek.",
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        $actor = $this->getActor();
        $result = $this->ordersService->applyInlineStatusUpdate($orderId, $field, $value, $actor);
        if (! (bool) ($result['success'] ?? false)) {
            return $this->response->setStatusCode((int) ($result['httpStatus'] ?? 422))->setJSON([
                'success' => false,
                'message' => (string) ($result['message'] ?? "Gecersiz istek."),
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Durum guncellendi.",
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
        $showData = $this->ordersReportingService->getShowData($identifier, $this->invoiceService);

        if ($showData === null) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        return view('admin/orders/show', [
            'title' => 'Siparis Detayi',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'order' => $showData['order'],
            'items' => $showData['items'],
            'logs' => $showData['logs'],
            'invoice' => $showData['invoice'],
            'canCreateInvoice' => $showData['canCreateInvoice'],
            'invoiceBlockMessage' => $showData['invoiceBlockMessage'],
        ]);
    }

    public function packingLabel(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return redirect()->back()->with('error', 'Yetkisiz istek.');
        }

        $user = session()->get('user') ?? [];
        $actor = $this->getActor();
        $bundle = $this->packingService->getOrCreateSessionForOrderIdentifier(
            $identifier,
            $actor['id'] !== '' ? $actor['id'] : null
        );

        if (! is_array($bundle) || $bundle === []) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $order = (array) ($bundle['order'] ?? []);
        $session = (array) ($bundle['session'] ?? []);
        if ($session === []) {
            return redirect()->to(site_url('admin/orders/' . (string) ($order['id'] ?? $identifier)))
                ->with('error', 'Paket dogrulama oturumu bulunamadi.');
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
        $actor = $this->getActor();

        $bundle = $this->packingService->getOrCreateSessionForOrderIdentifier(
            $identifier,
            $actor['id'] !== '' ? $actor['id'] : null
        );
        if (! is_array($bundle) || $bundle === []) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $order = (array) ($bundle['order'] ?? []);
        $verifyPayload = $this->packingService->buildVerifyPayload(
            (string) ($order['id'] ?? $identifier),
            $actor['id'] !== '' ? $actor['id'] : null
        );

        if (! is_array($verifyPayload) || $verifyPayload === []) {
            return redirect()->to(site_url('admin/orders/' . (string) ($order['id'] ?? $identifier)))
                ->with('error', 'Paket dogrulama oturumu bulunamadi.');
        }

        $session = (array) ($verifyPayload['session'] ?? []);
        $expectedItems = (array) ($verifyPayload['expectedItems'] ?? []);
        $scanState = (array) ($verifyPayload['scanState'] ?? ['items' => [], 'unknown_scans' => []]);
        $verification = (array) ($verifyPayload['verification'] ?? []);
        $debug = (array) ($verifyPayload['debug'] ?? []);

        log_message('debug', 'Packing verify session debug: order_id={order_id}, session_id={session_id}, status={status}, expected_raw_len={expected_raw_len}, scanned_raw_len={scanned_raw_len}, expected_count={expected_count}, scanned_items_count={scanned_items_count}', [
            'order_id' => (string) ($order['id'] ?? ''),
            'session_id' => (string) ($session['id'] ?? ''),
            'status' => (string) ($session['status'] ?? ''),
            'expected_raw_len' => (int) ($debug['expected_raw_len'] ?? 0),
            'scanned_raw_len' => (int) ($debug['scanned_raw_len'] ?? 0),
            'expected_count' => (int) ($debug['expected_count'] ?? 0),
            'scanned_items_count' => (int) ($debug['scanned_items_count'] ?? 0),
        ]);

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

        $result = $this->packingService->applyScanForOrderIdentifier($identifier, $scanCode, $qty);
        $type = (string) ($result['type'] ?? '');

        if ($type === 'not_found') {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Siparis bulunamadi.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        if ($type === 'session_not_found') {
            return $this->response->setStatusCode(409)->setJSON([
                'success' => false,
                'message' => 'Acik paket dogrulama oturumu bulunamadi.',
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        if (! (bool) ($result['success'] ?? false)) {
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

        $result = $this->packingService->finishPackingForOrderIdentifier($identifier);
        $type = (string) ($result['type'] ?? '');
        $order = is_array($result['order'] ?? null) ? $result['order'] : null;
        $orderId = (string) ($order['id'] ?? $identifier);

        if ($type === 'not_found') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Siparis bulunamadi.',
                ]);
            }

            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        if ($type === 'session_not_found') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'Acik paket dogrulama oturumu bulunamadi.',
                ]);
            }

            return redirect()->to(site_url('admin/orders/' . $orderId . '/packing/verify'))
                ->with('error', 'Acik paket dogrulama oturumu bulunamadi.');
        }

        if ($type === 'cannot_finish') {
            $verification = $result['verification'] ?? null;
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

            return redirect()->to(site_url('admin/orders/' . $orderId . '/packing/verify'))
                ->with('error', 'Dogrulama tamamlanamadi. Eksik, fazla veya bilinmeyen okutma var.');
        }

        if ($type === 'save_failed' || ! (bool) ($result['success'] ?? false)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Paket dogrulamasi tamamlanamadi.',
                ]);
            }

            return redirect()->to(site_url('admin/orders/' . $orderId . '/packing/verify'))
                ->with('error', 'Paket dogrulamasi tamamlanamadi.');
        }

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

        return redirect()->to(site_url('admin/orders/' . $orderId . '/packing/verify'))
            ->with('success', 'Paket dogrulamasi tamamlandi.');
    }
    public function createInvoice(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return redirect()->back()->with('error', 'Yetkisiz istek.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $invoiceEligibility = $this->invoiceService->evaluateInvoiceEligibility($order);
        if (! ($invoiceEligibility['allowed'] ?? false)) {
            return redirect()
                ->to(site_url('admin/orders/' . (string) $order['id']))
                ->with('error', (string) ($invoiceEligibility['message'] ?? 'Fatura olusturulamadi.'));
        }

        $result = $this->invoiceService->createForOrder((string) $order['id']);
        $invoice = is_array($result['invoice'] ?? null) ? $result['invoice'] : null;

        if (($result['code'] ?? '') === 'already_exists') {
            return redirect()
                ->to(site_url('admin/orders/' . (string) $order['id']))
                ->with('error', 'Fatura olusturulamadi.');
        }

        if (! ($result['success'] ?? false)) {
            return redirect()
                ->to(site_url('admin/orders/' . (string) $order['id']))
                ->with('error', (string) ($result['message'] ?? 'Fatura olusturulamadi.'));
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
                'Fatura olusturuldu.',
            );
        }

        return redirect()
            ->to(site_url('admin/orders/' . (string) $order['id']))
            ->with('success', 'Fatura olusturuldu.');
    }

    public function viewInvoice(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return $this->response->setStatusCode(403)->setBody('Yetkisiz istek.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $invoice = $this->invoiceService->findByOrderId((string) $order['id']);
        if (! $invoice) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Fatura bulunamadi.');
        }

        $pdfPath = $this->resolveInvoicePdfPath((string) ($invoice['pdf_path'] ?? ''));
        if ($pdfPath === null) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Fatura PDF dosyasi bulunamadi.');
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
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $invoice = $this->invoiceService->findByOrderId((string) $order['id']);
        if (! $invoice) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Fatura bulunamadi.');
        }

        $pdfPath = $this->resolveInvoicePdfPath((string) ($invoice['pdf_path'] ?? ''));
        if ($pdfPath === null) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Fatura PDF dosyasi bulunamadi.');
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
            return redirect()->back()->withInput()->with('validation', $this->validator)->with('error', 'Siparis olusturulamadi.');
        }

        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'Kullanici bulunamadi.');
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
            return redirect()->back()->withInput()->with('error', 'Siparis olusturulamadi.');
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
        $this->logOrderAction($orderId, $actor['id'], $actor['role'], 'order_created', null, 'pending', 'Siparis basariyla olusturuldu.');

        return redirect()->back()->with('success', 'Siparis basariyla olusturuldu.');
    }

    public function ship(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'Kullanici bulunamadi.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($id);
        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');

        if (! $this->ordersService->shipOrder($id, $actor['id'])) {
            return redirect()->back()->with('error', 'Kargoya verilemedi.');
        }

        $this->logOrderAction($id, $actor['id'], $actor['role'], 'order_shipped', $fromStatus, 'shipped', 'Kargoya verildi.');

        return redirect()->back()->with('success', 'Kargoya verildi.');
    }

    public function cancel(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'Kullanici bulunamadi.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($id);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        if ($fromStatus === 'cancelled') {
            return redirect()->back()->with('success', 'Iptal edildi.');
        }

        if (in_array($fromStatus, ['delivered', 'return_in_progress', 'return_done', 'returned'], true)) {
            return redirect()->back()->with('error', 'Gecersiz durum gecisi.');
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
            return redirect()->back()->with('error', 'Iptal edilemedi.');
        }

        $this->logOrderAction($id, $actor['id'], $actor['role'], 'order_cancelled', $fromStatus, 'cancelled', 'Iptal edildi.');

        return redirect()->back()->with('success', 'Iptal edildi.');
    }

    public function return(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'Kullanici bulunamadi.');
        }

        $order = (new OrderModel())->findByIdOrOrderNo($id);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');

        if (! $this->ordersService->returnOrder($id, $actor['id'])) {
            return redirect()->back()->with('error', 'Iade tamamlanamadi.');
        }

        $this->logOrderAction($id, $actor['id'], $actor['role'], 'return_completed', $fromStatus, 'return_done', 'Iade tamamlandi.');

        return redirect()->back()->with('success', 'Iade tamamlandi.');
    }
    public function updateStatus(string $identifier)
    {
        $rules = [
            'order_status' => 'required',
            'payment_status' => 'permit_empty',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Gecersiz istek.');
        }

        $actor = $this->getActor();
        $result = $this->ordersService->applyAdminStatusUpdate(
            $identifier,
            (string) $this->request->getPost('order_status'),
            trim((string) ($this->request->getPost('payment_status') ?? '')),
            $actor,
            [
                'status_changed' => 'Durum guncellendi.',
                'payment_status_changed' => 'Durum guncellendi.',
            ]
        );

        if (($result['type'] ?? '') === 'not_found') {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        if (! (bool) ($result['success'] ?? false)) {
            return redirect()->back()->withInput()->with('error', 'Durum guncellenemedi.');
        }

        return redirect()->back()->with('success', 'Durum guncellendi.');
    }
    public function updateShipping(string $identifier)
    {
        $rules = [
            'shipping_company' => 'permit_empty|max_length[120]',
            'tracking_number' => 'permit_empty|max_length[120]',
            'shipping_status' => 'permit_empty|in_list[not_shipped,shipped,delivered,returned]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Gecersiz istek.');
        }

        $actor = $this->getActor();
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
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
            'Kargo bilgisi guncellendi.',
            [
                'shipping_company' => $shippingCompany,
                'tracking_number' => $trackingNumber,
            ]
        );

        return redirect()->back()->with('success', 'Kargo bilgisi guncellendi.');
    }

    public function addNote(string $identifier)
    {
        $rules = ['note' => 'required|min_length[2]|max_length[2000]'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Gecersiz istek.');
        }

        $actor = $this->getActor();
        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
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

        return redirect()->back()->with('success', 'Not eklendi.');
    }

    public function startReturn(string $identifier)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'Kullanici bulunamadi.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        if ($fromStatus !== 'delivered') {
            return redirect()->back()->with('error', 'Gecersiz durum gecisi.');
        }

        $orderModel->update((string) $order['id'], [
            'order_status' => 'return_in_progress',
            'shipping_status' => 'returned',
            'return_started_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actor['id'] !== '' ? $actor['id'] : null,
        ]);

        $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'return_started', $fromStatus, 'return_in_progress', 'Iade baslatildi.');

        return redirect()->back()->with('success', 'Iade baslatildi.');
    }

    public function completeReturn(string $identifier)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'Kullanici dogrulamasi gerekli.');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $fromStatus = (string) ($order['order_status'] ?? $order['status'] ?? '');
        if ($fromStatus !== 'return_in_progress') {
            return redirect()->back()->with('error', 'Islem tamamlanamadi.');
        }

        if (! $this->ordersService->returnOrder((string) $order['id'], $actor['id'])) {
            return redirect()->back()->with('error', 'Islem tamamlanamadi.');
        }

        $orderModel->update((string) $order['id'], [
            'order_status' => 'return_done',
            'return_completed_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actor['id'] !== '' ? $actor['id'] : null,
        ]);

        $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'return_completed', $fromStatus, 'return_done', 'Iade tamamlandi.');

        return redirect()->back()->with('success', 'Islem basariyla tamamlandi.');
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
            'product_name_snapshot' => (string) ($product['product_name'] ?? 'Bilinmeyen urun'),
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
                'name' => trim((string) ($item['name'] ?? ($item['product_name'] ?? "Urun"))),
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
        return $this->ordersReportingService->getSummaryCounts();
    }

    private function paymentStatusBadge(string $status): string
    {
        $labels = [
            'unpaid' => "Odenmedi",
            'paid' => "Odendi",
            'refunded' => "Iade Edildi",
            'partial_refund' => "Kismi Iade",
            'failed' => "Basarisiz",
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
            'preparing' => "Hazirlaniyor",
            'packed' => 'Paketlendi',
            'shipped' => 'Kargoya Verildi',
            'delivered' => 'Teslim Edildi',
            'cancelled' => "Iptal Edildi",
            'return_in_progress' => "Iade Surecinde",
            'return_done' => "Iade Tamamlandi",
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
            'not_shipped' => "Hazirlanmadi",
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim',
            'returned' => "Iade",
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
                'unpaid' => "Odenmedi",
                'paid' => "Odendi",
                'refunded' => "Iade Edildi",
                'partial_refund' => "Kismi iade",
                'failed' => "Basarisiz",
            ];
        }

        return [
            'pending' => 'Beklemede',
            'preparing' => "Hazirlaniyor",
            'packed' => 'Paketlendi',
            'shipped' => 'Kargoya Verildi',
            'delivered' => 'Teslim Edildi',
            'cancelled' => "Iptal Edildi",
            'return_in_progress' => "Iade Surecinde",
            'return_done' => "Iade Tamamlandi",
        ];
    }
}
