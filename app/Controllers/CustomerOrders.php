<?php

namespace App\Controllers;

use App\Services\CustomerOrderService;
use App\Services\StorefrontHomeService;
use CodeIgniter\Exceptions\PageNotFoundException;

class CustomerOrders extends BaseController
{
    private CustomerOrderService $customerOrderService;
    private StorefrontHomeService $storefrontHomeService;

    public function __construct()
    {
        $this->customerOrderService = new CustomerOrderService();
        $this->storefrontHomeService = new StorefrontHomeService();
    }

    public function index()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Siparislerinizi gormek icin giris yapmalisiniz.');
        }

        return view('site/orders/index', [
            'title' => 'Siparislerim',
            'ordersView' => $this->customerOrderService->getOrdersForUser($userId, [
                'scope' => $this->request->getGet('scope'),
                'q' => $this->request->getGet('q'),
            ]),
            'headerMenuItems' => $this->storefrontHomeService->getHeaderMenuItems(),
            'categoryNavItems' => $this->storefrontHomeService->getCategoryNavItems(),
            'searchQuery' => '',
        ]);
    }

    public function show(string $orderNoOrId)
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Siparis detayini gormek icin giris yapmalisiniz.');
        }

        $order = $this->customerOrderService->getOrderDetailForUser($userId, $orderNoOrId);
        if ($order === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('site/orders/show', [
            'title' => 'Siparis Detayi',
            'order' => $order,
            'headerMenuItems' => $this->storefrontHomeService->getHeaderMenuItems(),
            'categoryNavItems' => $this->storefrontHomeService->getCategoryNavItems(),
            'searchQuery' => '',
        ]);
    }

    public function createReturnRequest(string $orderNoOrId)
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'İade talebi oluşturmak için giriş yapmalısınız.');
        }

        $result = $this->customerOrderService->createReturnRequest(
            $userId,
            $orderNoOrId,
            $this->request->getPost('reason'),
            $this->request->getPost('note')
        );

        return redirect()->to(base_url('yardim/siparislerim/' . urlencode($orderNoOrId)))
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function getCurrentUserId(): ?string
    {
        if (! session()->get('isLoggedIn')) {
            return null;
        }

        $fromUser = trim((string) (session()->get('user')['id'] ?? ''));
        if ($fromUser !== '') {
            return $fromUser;
        }

        $fromLegacy = trim((string) session()->get('user_id'));
        return $fromLegacy !== '' ? $fromLegacy : null;
    }
}
