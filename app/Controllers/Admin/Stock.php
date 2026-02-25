<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ProductsService;

class Stock extends BaseController
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

    public function index()
    {
        $user = session()->get('user') ?? [];
        $selectedProductId = trim((string) $this->request->getGet('product_id'));
        $products = $this->productsService->getAllActivePrintedProductsForSelect();
        $selectedStockHistory = [];
        $selectedStockMoves = [];
        $selectedProduct = [];

        if ($selectedProductId !== '') {
            $selectedProduct = $this->productsService->getProductStockSnapshot($selectedProductId);
            $selectedStockHistory = $this->productsService->getStockHistoryDaily($selectedProductId, 30);
            $selectedStockMoves = $this->productsService->getLatestStockMoves($selectedProductId, 20);
        }

        $userRole = (string) ($user['role'] ?? '');
        $reasons = array_values(array_filter(self::REASONS, static function (string $reason) use ($userRole): bool {
            return !($reason === 'manuel_duzeltme' && $userRole !== 'admin');
        }));

        return view('admin/stock/index', [
            'title'               => 'Stok Takip Paneli',
            'userName'            => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole'            => $userRole,
            'criticalStocks'      => $this->productsService->getCriticalStockPrintedByAvailable(5),
            'categoryCounts'      => $this->productsService->getCategoryCountsPrinted(),
            'productsForSelect'   => $products,
            'selectedProductId'   => $selectedProductId,
            'selectedProduct'     => $selectedProduct,
            'selectedStockHistory'=> $selectedStockHistory,
            'selectedStockMoves'  => $selectedStockMoves,
            'stockReasons'        => $reasons,
            'allStockReasons'     => self::REASONS,
            'validation'          => session('validation'),
        ]);
    }

    public function moves()
    {
        $user = session()->get('user') ?? [];
        $selectedProductId = trim((string) $this->request->getGet('product_id'));
        $allProducts = $this->productsService->getAllActivePrintedWithStatusForList();
        $selectedProduct = [];
        $selectedStockHistory = [];
        $selectedStockMoves = [];

        if ($selectedProductId !== '') {
            $selectedProduct = $this->productsService->getProductStockSnapshot($selectedProductId);
            $selectedStockHistory = $this->productsService->getStockHistoryDaily($selectedProductId, 30);
            $selectedStockMoves = $this->productsService->getLatestStockMoves($selectedProductId, 20);
        }

        $userRole = (string) ($user['role'] ?? '');
        $reasons = array_values(array_filter(self::REASONS, static function (string $reason) use ($userRole): bool {
            return !($reason === 'manuel_duzeltme' && $userRole !== 'admin');
        }));

        return view('admin/stock/moves', [
            'title'               => 'TÃ¼m BasÄ±lÄ± ÃœrÃ¼nler - Stok Hareketleri',
            'userName'            => $user['name'] ?? ($user['email'] ?? 'Admin'),
            'userRole'            => $userRole,
            'allProducts'         => $allProducts,
            'selectedProductId'   => $selectedProductId,
            'selectedProduct'     => $selectedProduct,
            'selectedStockHistory'=> $selectedStockHistory,
            'selectedStockMoves'  => $selectedStockMoves,
            'stockReasons'        => $reasons,
            'validation'          => session('validation'),
        ]);
    }

    public function move(string $id)
    {
        $rules = [
            'reason' => 'required|in_list[' . implode(',', self::REASONS) . ']',
            'note' => 'required|min_length[3]',
            'direction' => 'required|in_list[+,-]',
            'quantity' => 'required|integer|greater_than[0]',
            'ref_no' => 'permit_empty|max_length[100]',
            'related_order_id' => 'permit_empty|max_length[64]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Stok hareketi bilgileri geÃ§ersiz.');
        }

        $user = session()->get('user') ?? [];
        $actorUserId = trim((string) ($user['id'] ?? ''));
        if ($actorUserId === '') {
            return redirect()->back()->with('error', 'KullanÄ±cÄ± oturumu bulunamadÄ±.');
        }

        $reason = trim((string) $this->request->getPost('reason'));
        $userRole = (string) ($user['role'] ?? '');
        if ($reason === 'manuel_duzeltme' && $userRole !== 'admin') {
            return $this->response->setStatusCode(403, 'Bu iÅŸlem iÃ§in yetkiniz yok.');
        }

        $note = trim((string) $this->request->getPost('note'));
        $direction = (string) $this->request->getPost('direction');
        $quantity = (int) $this->request->getPost('quantity');
        $refNo = trim((string) ($this->request->getPost('ref_no') ?? ''));
        $relatedOrderId = trim((string) ($this->request->getPost('related_order_id') ?? ''));

        $moved = $this->productsService->createStockMovement(
            $id,
            $reason,
            $note,
            $direction,
            $quantity,
            $actorUserId,
            $refNo !== '' ? $refNo : null,
            $relatedOrderId !== '' ? $relatedOrderId : null
        );

        if (! $moved) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Stok hareketi uygulanamadÄ±. SatÄ±labilir stok yetersiz olabilir.');
        }

        return redirect()->back()->with('success', 'Stok hareketi baÅŸarÄ±yla kaydedildi.');
    }

    public function deactivate(string $id)
    {
        if (! $this->productsService->deactivateProduct($id)) {
            return redirect()->back()->with('error', 'ÃœrÃ¼n satÄ±ÅŸ dÄ±ÅŸÄ± bÄ±rakÄ±lamadÄ±.');
        }

        return redirect()->back()->with('success', 'ÃœrÃ¼n satÄ±ÅŸ dÄ±ÅŸÄ± bÄ±rakÄ±ldÄ±.');
    }
}

