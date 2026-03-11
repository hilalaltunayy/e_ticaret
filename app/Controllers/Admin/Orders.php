<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderLogModel;
use App\Models\OrderModel;
use App\Presenters\OrderDatatablePresenter;
use App\Services\InvoiceService;
use App\Services\OrderCreationService;
use App\Services\OrderNoteService;
use App\Services\OrderShippingService;
use App\Services\OrdersService;
use App\Services\OrdersReportingService;
use App\Services\PackingService;

class Orders extends BaseController
{
    public function __construct(
        private ?OrdersService $ordersService = null,
        private ?OrdersReportingService $ordersReportingService = null,
        private ?InvoiceService $invoiceService = null,
        private ?PackingService $packingService = null,
        private ?OrderNoteService $orderNoteService = null,
        private ?OrderCreationService $orderCreationService = null,
        private ?OrderShippingService $orderShippingService = null
    ) {
        $this->ordersService = $this->ordersService ?? new OrdersService();
        $this->ordersReportingService = $this->ordersReportingService ?? new OrdersReportingService();
        $this->invoiceService = $this->invoiceService ?? new InvoiceService();
        $this->packingService = $this->packingService ?? new PackingService();
        $this->orderNoteService = $this->orderNoteService ?? new OrderNoteService();
        $this->orderCreationService = $this->orderCreationService ?? new OrderCreationService();
        $this->orderShippingService = $this->orderShippingService ?? new OrderShippingService();
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
            return $this->unauthorizedJsonResponse();
        }

        return $this->response->setJSON($this->withCsrf([
            'success' => true,
            'summary' => $this->getSummaryCounts(),
        ]));
    }

    public function analytics()
    {
        if (! $this->canManageOrders()) {
            $payload = [
                'success' => false,
                'message' => 'Yetkisiz istek.',
            ];

            return $this->utf8JsonResponse($payload, 403);
        }

        $payload = $this->ordersReportingService->buildAnalyticsPayload(
            (string) ($this->request->getGet('range') ?? 'daily')
        );

        return $this->utf8JsonResponse($payload);
    }

    public function statusDistribution()
    {
        if (! $this->canManageOrders()) {
            $payload = [
                'success' => false,
                'message' => 'Yetkisiz istek.',
            ];

            return $this->utf8JsonResponse($payload, 403);
        }

        $payload = $this->ordersReportingService->buildStatusDistributionPayload(
            (string) ($this->request->getGet('range') ?? 'weekly')
        );

        return $this->utf8JsonResponse($payload);
    }

    public function datatables()
    {
        $params = $this->request->getGet();
        $result = $this->ordersService->datatablesList($params);
        $data = (new OrderDatatablePresenter())->formatRows((array) ($result['data'] ?? []));

        $payload = [
            'draw' => (int) ($params['draw'] ?? 0),
            'recordsTotal' => (int) ($result['recordsTotal'] ?? 0),
            'recordsFiltered' => (int) ($result['recordsFiltered'] ?? 0),
            'data' => $data,
        ];

        return $this->utf8JsonResponse($payload);
    }

    public function inlineStatusUpdate()
    {
        if (! $this->canManageOrders()) {
            return $this->unauthorizedJsonResponse();
        }

        $orderId = trim((string) ($this->request->getPost('order_id') ?? ''));
        $field = trim((string) ($this->request->getPost('field') ?? ''));
        $value = trim((string) ($this->request->getPost('value') ?? ''));

        if ($orderId === '' || ! in_array($field, ['order_status', 'payment_status'], true) || $value === '') {
            return $this->jsonErrorResponse(422, "Gecersiz istek.");
        }

        $actor = $this->getActor();
        $result = $this->ordersService->applyInlineStatusUpdate($orderId, $field, $value, $actor);
        if (! (bool) ($result['success'] ?? false)) {
            return $this->jsonErrorResponse(
                (int) ($result['httpStatus'] ?? 422),
                (string) ($result['message'] ?? "Gecersiz istek.")
            );
        }

        return $this->response->setJSON($this->withCsrf([
            'success' => true,
            'message' => "Durum guncellendi.",
            'summary' => $this->getSummaryCounts(),
        ]));
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
            return $this->unauthorizedJsonResponse();
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
            return $this->jsonErrorResponse(422, 'Barkod veya ISBN zorunludur.');
        }

        $result = $this->packingService->applyScanForOrderIdentifier($identifier, $scanCode, $qty);
        $type = (string) ($result['type'] ?? '');

        if ($type === 'not_found') {
            return $this->jsonErrorResponse(404, 'Siparis bulunamadi.');
        }

        if ($type === 'session_not_found') {
            return $this->jsonErrorResponse(409, 'Acik paket dogrulama oturumu bulunamadi.');
        }

        if (! (bool) ($result['success'] ?? false)) {
            return $this->jsonErrorResponse(422, (string) ($result['message'] ?? 'Okutma kaydedilemedi.'), [
                'verification' => $result['verification'] ?? null,
            ]);
        }

        return $this->response->setJSON($this->withCsrf([
            'success' => true,
            'message' => (string) ($result['message'] ?? 'Okutma kaydedildi.'),
            'verification' => $result['verification'] ?? null,
        ]));
    }

    public function packingFinish(string $identifier)
    {
        $isAjax = $this->request->isAJAX();
        if (! $this->canManageOrders()) {
            if ($isAjax) {
                return $this->unauthorizedJsonResponse();
            }

            return redirect()->back()->with('error', 'Yetkisiz istek.');
        }

        $result = $this->packingService->finishPackingForOrderIdentifier($identifier);
        $type = (string) ($result['type'] ?? '');
        $order = is_array($result['order'] ?? null) ? $result['order'] : null;
        $orderId = (string) ($order['id'] ?? $identifier);

        if ($type === 'not_found') {
            if ($isAjax) {
                return $this->jsonErrorResponse(404, 'Siparis bulunamadi.', [], false);
            }

            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        if ($type === 'session_not_found') {
            if ($isAjax) {
                return $this->jsonErrorResponse(409, 'Acik paket dogrulama oturumu bulunamadi.', [], false);
            }

            return redirect()->to(site_url('admin/orders/' . $orderId . '/packing/verify'))
                ->with('error', 'Acik paket dogrulama oturumu bulunamadi.');
        }

        if ($type === 'cannot_finish') {
            $verification = $result['verification'] ?? null;
            if ($isAjax) {
                return $this->jsonErrorResponse(422, 'Dogrulama tamamlanamadi. Eksik, fazla veya bilinmeyen okutma var.', [
                    'verification' => $verification,
                ]);
            }

            return redirect()->to(site_url('admin/orders/' . $orderId . '/packing/verify'))
                ->with('error', 'Dogrulama tamamlanamadi. Eksik, fazla veya bilinmeyen okutma var.');
        }

        if ($type === 'save_failed' || ! (bool) ($result['success'] ?? false)) {
            if ($isAjax) {
                return $this->jsonErrorResponse(500, 'Paket dogrulamasi tamamlanamadi.', [], false);
            }

            return redirect()->to(site_url('admin/orders/' . $orderId . '/packing/verify'))
                ->with('error', 'Paket dogrulamasi tamamlanamadi.');
        }

        if ($isAjax) {
            return $this->response->setJSON($this->withCsrf([
                'success' => true,
                'message' => 'Paket dogrulamasi tamamlandi.',
            ]));
        }

        return redirect()->to(site_url('admin/orders/' . $orderId . '/packing/verify'))
            ->with('success', 'Paket dogrulamasi tamamlandi.');
    }
    public function createInvoice(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return redirect()->back()->with('error', 'Yetkisiz istek.');
        }

        $result = $this->invoiceService->createForOrderIdentifier($identifier);
        $order = is_array($result['order'] ?? null) ? $result['order'] : null;
        $invoice = is_array($result['invoice'] ?? null) ? $result['invoice'] : null;

        if (($result['code'] ?? '') === 'order_not_found') {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

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

        $pdf = $this->resolveInvoicePdfResponseData($identifier);
        if ($pdf instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $pdf;
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $pdf['fileName'] . '"')
            ->setBody($pdf['body']);
    }

    public function downloadInvoice(string $identifier)
    {
        if (! $this->canManageOrders()) {
            return $this->response->setStatusCode(403)->setBody('Yetkisiz istek.');
        }

        $pdf = $this->resolveInvoicePdfResponseData($identifier);
        if ($pdf instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $pdf;
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $pdf['fileName'] . '"')
            ->setBody($pdf['body']);
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

        $result = $this->orderCreationService->createManualOrder(
            trim((string) $this->request->getPost('product_id')),
            (int) $this->request->getPost('quantity'),
            trim((string) ($this->request->getPost('customer_name') ?? '')),
            $actor
        );
        if (! (bool) ($result['success'] ?? false)) {
            return redirect()->back()->withInput()->with('error', 'Siparis olusturulamadi.');
        }

        $orderId = (string) ($result['order_id'] ?? '');
        $this->logOrderAction(
            $orderId,
            $actor['id'],
            $actor['role'],
            'order_created',
            ($result['from_status'] ?? null) !== null ? (string) $result['from_status'] : null,
            ($result['to_status'] ?? null) !== null ? (string) $result['to_status'] : null,
            'Siparis basariyla olusturuldu.'
        );

        return redirect()->back()->with('success', 'Siparis basariyla olusturuldu.');
    }

    public function ship(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'Kullanici bulunamadi.');
        }

        $result = $this->orderShippingService->shipOrderByIdentifier($id, $actor);
        if (! (bool) ($result['success'] ?? false)) {
            return redirect()->back()->with('error', 'Kargoya verilemedi.');
        }

        $this->logOrderAction(
            $id,
            $actor['id'],
            $actor['role'],
            'order_shipped',
            (string) ($result['from_status'] ?? ''),
            (string) ($result['to_status'] ?? ''),
            'Kargoya verildi.'
        );

        return redirect()->back()->with('success', 'Kargoya verildi.');
    }

    public function cancel(string $id)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'Kullanici bulunamadi.');
        }

        $result = $this->ordersService->cancelOrderByIdentifier($id, $actor);
        if (($result['type'] ?? '') === 'not_found') {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        if (($result['type'] ?? '') === 'already_cancelled') {
            return redirect()->back()->with('success', 'Iptal edildi.');
        }

        if (($result['type'] ?? '') === 'invalid_status') {
            return redirect()->back()->with('error', 'Gecersiz durum gecisi.');
        }

        if (! (bool) ($result['success'] ?? false)) {
            return redirect()->back()->with('error', 'Iptal edilemedi.');
        }

        $this->logOrderAction(
            $id,
            $actor['id'],
            $actor['role'],
            'order_cancelled',
            (string) ($result['from_status'] ?? ''),
            (string) ($result['to_status'] ?? ''),
            'Iptal edildi.'
        );

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
        $result = $this->ordersService->applyShippingUpdate(
            $identifier,
            (string) ($this->request->getPost('shipping_company') ?? ''),
            (string) ($this->request->getPost('tracking_number') ?? ''),
            (string) ($this->request->getPost('shipping_status') ?? ''),
            $actor
        );

        if (($result['type'] ?? '') === 'not_found') {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $order = (array) ($result['order'] ?? []);
        $shippingCompany = (string) ($result['shipping_company'] ?? '');
        $trackingNumber = (string) ($result['tracking_number'] ?? '');
        $shippingStatus = (string) ($result['shipping_status'] ?? '');
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
        $result = $this->orderNoteService->addAdminNoteToOrderIdentifier(
            $identifier,
            (string) $this->request->getPost('note'),
            $actor
        );
        if (($result['type'] ?? '') === 'not_found') {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $order = (array) ($result['order'] ?? []);
        $note = (string) ($result['note'] ?? '');
        $this->logOrderAction((string) $order['id'], $actor['id'], $actor['role'], 'admin_note_added', null, null, $note);

        return redirect()->back()->with('success', 'Not eklendi.');
    }

    public function startReturn(string $identifier)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'Kullanici bulunamadi.');
        }

        $result = $this->ordersService->startReturnForOrderIdentifier($identifier, $actor);
        if (($result['type'] ?? '') === 'not_found') {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        if (! (bool) ($result['success'] ?? false)) {
            return redirect()->back()->with('error', 'Gecersiz durum gecisi.');
        }

        $order = (array) ($result['order'] ?? []);
        $this->logOrderAction(
            (string) $order['id'],
            $actor['id'],
            $actor['role'],
            'return_started',
            (string) ($result['from_status'] ?? ''),
            (string) ($result['to_status'] ?? ''),
            'Iade baslatildi.'
        );

        return redirect()->back()->with('success', 'Iade baslatildi.');
    }

    public function completeReturn(string $identifier)
    {
        $actor = $this->getActor();
        if ($actor['id'] === '') {
            return redirect()->back()->with('error', 'Kullanici dogrulamasi gerekli.');
        }

        $result = $this->ordersService->completeReturnForOrderIdentifier($identifier, $actor);
        if (($result['type'] ?? '') === 'not_found') {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        if (! (bool) ($result['success'] ?? false)) {
            return redirect()->back()->with('error', 'Islem tamamlanamadi.');
        }

        $order = (array) ($result['order'] ?? []);
        $this->logOrderAction(
            (string) $order['id'],
            $actor['id'],
            $actor['role'],
            'return_completed',
            (string) ($result['from_status'] ?? ''),
            (string) ($result['to_status'] ?? ''),
            'Iade tamamlandi.'
        );

        return redirect()->back()->with('success', 'Islem basariyla tamamlandi.');
    }

    private function resolveInvoicePdfResponseData(string $identifier)
    {
        $order = (new OrderModel())->findByIdOrOrderNo($identifier);
        if (! $order) {
            return redirect()->to(site_url('admin/orders'))->with('error', 'Siparis bulunamadi.');
        }

        $invoice = $this->invoiceService->findByOrderId((string) $order['id']);
        if (! $invoice) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Fatura bulunamadi.');
        }

        $path = trim((string) ($invoice['pdf_path'] ?? ''));
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));
        $pdfPath = WRITEPATH . $normalized;

        if ($path === '' || ! is_file($pdfPath)) {
            return redirect()->to(site_url('admin/orders/' . (string) $order['id']))->with('error', 'Fatura PDF dosyasi bulunamadi.');
        }

        return [
            'fileName' => basename($pdfPath),
            'body' => (string) file_get_contents($pdfPath),
        ];
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

    private function unauthorizedJsonResponse()
    {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => 'Yetkisiz istek.',
        ]);
    }

    private function csrfPayload(): array
    {
        return [
            'token' => csrf_token(),
            'hash' => csrf_hash(),
        ];
    }

    private function withCsrf(array $payload): array
    {
        $payload['csrf'] = $this->csrfPayload();

        return $payload;
    }

    private function jsonErrorResponse(int $status, string $message, array $extra = [], bool $withCsrf = true)
    {
        $payload = array_merge([
            'success' => false,
            'message' => $message,
        ], $extra);

        if ($withCsrf) {
            $payload = $this->withCsrf($payload);
        }

        return $this->response->setStatusCode($status)->setJSON($payload);
    }

    private function utf8JsonResponse(array $payload, ?int $status = null)
    {
        $response = $this->response;
        if ($status !== null) {
            $response = $response->setStatusCode($status);
        }

        return $response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setBody((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
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

    private function getSummaryCounts(): array
    {
        return $this->ordersReportingService->getSummaryCounts();
    }

}
