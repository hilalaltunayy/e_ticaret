<?php

namespace App\Controllers;

use App\Services\CartService;
use App\Services\StorefrontHomeService;

class Cart extends BaseController
{
    private CartService $cartService;
    private StorefrontHomeService $storefrontHomeService;

    public function __construct()
    {
        $this->cartService = new CartService();
        $this->storefrontHomeService = new StorefrontHomeService();
    }

    public function index()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Sepetinizi gormek icin giris yapmalisiniz.');
        }

        return view('site/cart/index', [
            'title' => 'Sepetim',
            'cartView' => $this->cartService->getCartViewModel($userId),
            'suggestedProducts' => $this->cartService->getSuggestedProducts($userId, 4),
            'headerMenuItems' => $this->storefrontHomeService->getHeaderMenuItems(),
            'categoryNavItems' => $this->storefrontHomeService->getCategoryNavItems(),
            'searchQuery' => '',
        ]);
    }

    public function add()
    {
        $userId = $this->requireUserIdForAction('Sepete eklemek icin giris yapmalisiniz.');
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Sepete eklemek icin giris yapmalisiniz.');
        }

        $productId = trim((string) $this->request->getPost('product_id'));
        $quantity = max(1, (int) $this->request->getPost('quantity'));
        if ($productId === '') {
            return redirect()->back()->with('error', 'Urun bilgisi eksik.');
        }

        $result = $this->cartService->addProduct($userId, $productId, $quantity);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function increase()
    {
        return $this->handleItemAction('increase');
    }

    public function decrease()
    {
        return $this->handleItemAction('decrease');
    }

    public function update()
    {
        $userId = $this->requireUserIdForAction('Sepetinizi guncellemek icin giris yapmalisiniz.');
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Sepetinizi guncellemek icin giris yapmalisiniz.');
        }

        $cartItemId = trim((string) $this->request->getPost('cart_item_id'));
        $quantity = max(1, (int) $this->request->getPost('quantity'));
        if ($cartItemId === '') {
            return redirect()->back()->with('error', 'Sepet urunu bulunamadi.');
        }

        $result = $this->cartService->updateItemQuantity($userId, $cartItemId, $quantity);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function remove()
    {
        return $this->handleItemAction('remove');
    }

    public function clear()
    {
        $userId = $this->requireUserIdForAction('Sepetinizi temizlemek icin giris yapmalisiniz.');
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Sepetinizi temizlemek icin giris yapmalisiniz.');
        }

        $result = $this->cartService->clearCart($userId);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function handleItemAction(string $action)
    {
        $userId = $this->requireUserIdForAction('Sepetinizi guncellemek icin giris yapmalisiniz.');
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Sepetinizi guncellemek icin giris yapmalisiniz.');
        }

        $cartItemId = trim((string) $this->request->getPost('cart_item_id'));
        if ($cartItemId === '') {
            return redirect()->back()->with('error', 'Sepet urunu bulunamadi.');
        }

        $result = match ($action) {
            'increase' => $this->cartService->increaseItem($userId, $cartItemId),
            'decrease' => $this->cartService->decreaseItem($userId, $cartItemId),
            'remove' => $this->cartService->removeItem($userId, $cartItemId),
            default => ['success' => false, 'message' => 'Gecersiz sepet islemi.'],
        };

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function requireUserIdForAction(string $message): ?string
    {
        $userId = $this->getCurrentUserId();
        return $userId !== null ? $userId : null;
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
