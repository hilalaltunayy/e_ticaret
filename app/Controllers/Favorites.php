<?php

namespace App\Controllers;

use App\Services\FavoriteService;
use App\Services\StorefrontHomeService;

class Favorites extends BaseController
{
    private FavoriteService $favoriteService;
    private StorefrontHomeService $storefrontHomeService;

    public function __construct()
    {
        $this->favoriteService = new FavoriteService();
        $this->storefrontHomeService = new StorefrontHomeService();
    }

    public function index()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Favoriler için giriş yapmalısınız.');
        }

        return view('site/favorites/index', [
            'title' => 'Favorilerim',
            'favorites' => $this->favoriteService->getFavoritesForUser($userId),
            'headerMenuItems' => $this->storefrontHomeService->getHeaderMenuItems(),
            'categoryNavItems' => $this->storefrontHomeService->getCategoryNavItems(),
            'searchQuery' => '',
        ]);
    }

    public function toggle()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Favorilere eklemek için giriş yapmalısınız.');
        }

        $productId = trim((string) $this->request->getPost('product_id'));
        if ($productId === '') {
            return redirect()->back()->with('error', 'Ürün bilgisi eksik.');
        }

        $result = $this->favoriteService->toggleFavorite($userId, $productId);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function remove()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'İşlem için giriş yapmalısınız.');
        }

        $productId = trim((string) $this->request->getPost('product_id'));
        if ($productId === '') {
            return redirect()->back()->with('error', 'Ürün bilgisi eksik.');
        }

        $result = $this->favoriteService->removeFavorite($userId, $productId);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function addToCart()
    {
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            return redirect()->to(base_url('login'))->with('error', 'Sepete eklemek için giriş yapmalısınız.');
        }

        $productId = trim((string) $this->request->getPost('product_id'));
        if ($productId === '') {
            return redirect()->back()->with('error', 'Ürün bilgisi eksik.');
        }

        $result = $this->favoriteService->addFavoriteProductToCart($userId, $productId);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function getCurrentUserId(): ?string
    {
        if (!session()->get('isLoggedIn')) {
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
