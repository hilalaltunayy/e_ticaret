<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
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
        $orderModel = new OrderModel();

        $orders = $orderModel->getLatestWithProductName(20);
        $summary = [
            'total' => $orderModel->countAllOrders(),
            'reserved' => $orderModel->countOrdersByStatus('reserved'),
            'shipped' => $orderModel->countOrdersByStatus('shipped'),
            'returned' => $orderModel->countOrdersByStatus('returned'),
            'cancelled' => $orderModel->countOrdersByStatus('cancelled'),
        ];

        return view('admin/orders/index', [
            'title' => 'Siparişler',
            'userName' => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole' => $user['role'] ?? '',
            'orders' => $orders,
            'summary' => $summary,
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
            return redirect()->back()->withInput()->with('validation', $this->validator)->with('error', 'SipariÅŸ bilgileri geÃ§ersiz.');
        }

        $user = session()->get('user') ?? [];
        $actorUserId = trim((string) ($user['id'] ?? ''));
        if ($actorUserId === '') {
            return redirect()->back()->with('error', 'KullanÄ±cÄ± oturumu bulunamadÄ±.');
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
            return redirect()->back()->withInput()->with('error', 'SipariÅŸ oluÅŸturulamadÄ±. SatÄ±labilir stok yetersiz olabilir.');
        }

        return redirect()->back()->with('success', 'SipariÅŸ rezerve edildi.');
    }

    public function ship(string $id)
    {
        $user = session()->get('user') ?? [];
        $actorUserId = trim((string) ($user['id'] ?? ''));
        if ($actorUserId === '') {
            return redirect()->back()->with('error', 'KullanÄ±cÄ± oturumu bulunamadÄ±.');
        }

        if (! $this->ordersService->shipOrder($id, $actorUserId)) {
            return redirect()->back()->with('error', 'SipariÅŸ kargoya verilemedi.');
        }

        return redirect()->back()->with('success', 'SipariÅŸ kargoya verildi.');
    }

    public function cancel(string $id)
    {
        $user = session()->get('user') ?? [];
        $actorUserId = trim((string) ($user['id'] ?? ''));
        if ($actorUserId === '') {
            return redirect()->back()->with('error', 'KullanÄ±cÄ± oturumu bulunamadÄ±.');
        }

        if (! $this->ordersService->cancelOrder($id, $actorUserId)) {
            return redirect()->back()->with('error', 'SipariÅŸ iptal edilemedi.');
        }

        return redirect()->back()->with('success', 'SipariÅŸ iptal edildi.');
    }

    public function return(string $id)
    {
        $user = session()->get('user') ?? [];
        $actorUserId = trim((string) ($user['id'] ?? ''));
        if ($actorUserId === '') {
            return redirect()->back()->with('error', 'KullanÄ±cÄ± oturumu bulunamadÄ±.');
        }

        if (! $this->ordersService->returnOrder($id, $actorUserId)) {
            return redirect()->back()->with('error', 'SipariÅŸ iadesi iÅŸlenemedi.');
        }

        return redirect()->back()->with('success', 'SipariÅŸ iadesi iÅŸlendi.');
    }
}
