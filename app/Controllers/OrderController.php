<?php

namespace App\Controllers;

use App\Services\OrdersService;

class OrderController extends BaseController
{
    public function __construct(
        private ?OrdersService $ordersService = null
    ) {
        $this->ordersService = $this->ordersService ?? new OrdersService();
    }

    public function index()
    {
        return redirect()->to(site_url('admin/orders'));
    }

    public function create()
    {
        $rules = [
            'product_id' => 'required|max_length[64]',
            'quantity' => 'required|integer|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator)->with('error', 'Sipariş bilgileri geçersiz.');
        }

        $user = session()->get('user') ?? [];
        $actorUserId = trim((string) ($user['id'] ?? ''));
        if ($actorUserId === '') {
            return redirect()->back()->with('error', 'Kullanıcı oturumu bulunamadı.');
        }

        $productId = trim((string) $this->request->getPost('product_id'));
        $quantity = (int) $this->request->getPost('quantity');

        $orderId = $this->ordersService->createReservedOrder($productId, $quantity, $actorUserId);
        if (! $orderId) {
            return redirect()->back()->withInput()->with('error', 'Sipariş oluşturulamadı. Satılabilir stok yetersiz olabilir.');
        }

        return redirect()->back()->with('success', 'Sipariş rezerve edildi.');
    }
}
