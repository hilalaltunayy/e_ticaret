<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
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
            'title' => 'Orders',
            'userEmail' => $user['email'] ?? '',
            'userRole'  => $user['role'] ?? '',
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
            return redirect()->back()->withInput()->with('validation', $this->validator)->with('error', 'Sipariş bilgileri geçersiz.');
        }

        $user = session()->get('user') ?? [];
        $actorUserId = trim((string) ($user['id'] ?? ''));
        if ($actorUserId === '') {
            return redirect()->back()->with('error', 'Kullanıcı oturumu bulunamadı.');
        }

        $productId = trim((string) $this->request->getPost('product_id'));
        $quantity = (int) $this->request->getPost('quantity');
        $customerName = trim((string) ($this->request->getPost('customer_name') ?? ''));

        $orderId = $this->ordersService->createReservedOrder(
            $productId,
            $quantity,
            $actorUserId,
            $customerName !== '' ? $customerName : null
        );

        if (! $orderId) {
            return redirect()->back()->withInput()->with('error', 'Sipariş oluşturulamadı. Satılabilir stok yetersiz olabilir.');
        }

        return redirect()->back()->with('success', 'Sipariş rezerve edildi.');
    }

    public function ship(string $id)
    {
        $user = session()->get('user') ?? [];
        $actorUserId = trim((string) ($user['id'] ?? ''));
        if ($actorUserId === '') {
            return redirect()->back()->with('error', 'Kullanıcı oturumu bulunamadı.');
        }

        if (! $this->ordersService->shipOrder($id, $actorUserId)) {
            return redirect()->back()->with('error', 'Sipariş kargoya verilemedi.');
        }

        return redirect()->back()->with('success', 'Sipariş kargoya verildi.');
    }

    public function cancel(string $id)
    {
        $user = session()->get('user') ?? [];
        $actorUserId = trim((string) ($user['id'] ?? ''));
        if ($actorUserId === '') {
            return redirect()->back()->with('error', 'Kullanıcı oturumu bulunamadı.');
        }

        if (! $this->ordersService->cancelOrder($id, $actorUserId)) {
            return redirect()->back()->with('error', 'Sipariş iptal edilemedi.');
        }

        return redirect()->back()->with('success', 'Sipariş iptal edildi.');
    }

    public function return(string $id)
    {
        $user = session()->get('user') ?? [];
        $actorUserId = trim((string) ($user['id'] ?? ''));
        if ($actorUserId === '') {
            return redirect()->back()->with('error', 'Kullanıcı oturumu bulunamadı.');
        }

        if (! $this->ordersService->returnOrder($id, $actorUserId)) {
            return redirect()->back()->with('error', 'Sipariş iadesi işlenemedi.');
        }

        return redirect()->back()->with('success', 'Sipariş iadesi işlendi.');
    }
}
