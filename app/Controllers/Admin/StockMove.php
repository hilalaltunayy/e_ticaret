<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ProductsService;

class StockMove extends BaseController
{
    private const REASONS = [
        'depo_girisi',
        'depo_transferi',
        'iade_alindi',
        'tedarikci_girisi',
        'hasarli_urun',
        'kayip_urun',
        'kampanya_promosyon',
        'manuel_duzeltme',
        'hediye_gonderimi',
        'sayim_duzeltme',
    ];
public function __construct(
        private ?ProductsService $productsService = null
    ) {
        $this->productsService = $this->productsService ?? new ProductsService();
    }

    public function create(string $productId)
    {
        return redirect()->to(site_url('admin/stock/moves') . '?product_id=' . urlencode($productId) . '#stock-detail');
    }

    public function store(string $productId)
    {
        $rules = [
            'direction' => 'required|in_list[in,out]',
            'quantity' => 'required|integer|greater_than_equal_to[1]',
            'reason' => 'required|in_list[' . implode(',', self::REASONS) . ']',
            'note' => 'required|min_length[3]',
        ];

        $messages = [
            'direction' => [
                'in_list' => 'YÃ¶n alanÄ± GiriÅŸ/Ã‡Ä±kÄ±ÅŸ olmalÄ±dÄ±r.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'LÃ¼tfen form alanlarÄ±nÄ± kontrol edin.');
        }

        $user = session()->get('user') ?? [];
        $role = (string) ($user['role'] ?? '');
        $actorUserId = trim((string) (session()->get('user_id') ?? ($user['id'] ?? '')));
        if ($actorUserId === '') {
            return redirect()->back()->withInput()->with('error', 'KullanÄ±cÄ± oturumu doÄŸrulanamadÄ±.');
        }

        $reason = trim((string) $this->request->getPost('reason'));
        if ($reason === 'manuel_duzeltme' && $role !== 'admin') {
            return $this->response->setStatusCode(403, 'Bu iÅŸlem iÃ§in yetkiniz yok.');
        }

        $snapshot = $this->productsService->getProductStockSnapshot($productId);
        if (empty($snapshot)) {
            return redirect()->to(site_url('admin/stock'))->with('error', 'ÃœrÃ¼n bulunamadÄ±.');
        }

        $direction = (string) $this->request->getPost('direction');
        $quantity = (int) $this->request->getPost('quantity');
        $note = trim((string) $this->request->getPost('note'));
        $delta = $direction === 'in' ? $quantity : -$quantity;

        $stockCount = (int) ($snapshot['stock_count'] ?? 0);
        $sellable = (int) ($snapshot['sellable'] ?? 0);
        if ($direction === 'out' && ($quantity > $sellable || $quantity > $stockCount)) {
            return redirect()->back()->withInput()->with('error', 'Stok Ã§Ä±kÄ±ÅŸÄ± iÃ§in satÄ±labilir stok yetersiz.');
        }

        $saved = $this->productsService->applyStockMove($productId, $delta, [
            'reason' => $reason,
            'note' => $note,
            'actor_user_id' => $actorUserId,
            'ref_no' => null,
            'related_order_id' => null,
        ]);

        if (! $saved) {
            return redirect()->back()->withInput()->with('error', 'Stok hareketi kaydedilemedi.');
        }

        return redirect()->to(site_url('admin/stock/moves') . '?product_id=' . urlencode($productId) . '#stock-detail')
            ->with('success', 'Stok hareketi baÅŸarÄ±yla kaydedildi.');
    }
}

