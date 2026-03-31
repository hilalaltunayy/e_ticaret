<?php

namespace App\Controllers\Admin\Traits;

trait OrderPackingActions
{
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
}
