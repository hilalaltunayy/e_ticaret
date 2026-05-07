<?php

namespace App\Services;

use App\Models\FavoriteModel;
use App\Models\ProductsModel;

class FavoriteService
{
    private FavoriteModel $favoriteModel;
    private ProductsModel $productsModel;
    private CartService $cartService;

    public function __construct()
    {
        $this->favoriteModel = new FavoriteModel();
        $this->productsModel = new ProductsModel();
        $this->cartService = new CartService();
        helper('product_media');
    }

    public function getFavoritesForUser(string $userId): array
    {
        $rows = $this->favoriteModel->where('user_id', $userId)->findAll();
        if ($rows === []) {
            return [];
        }

        $items = [];
        foreach ($rows as $row) {
            $product = $this->productsModel->where('id', (string) ($row['product_id'] ?? ''))->first();
            if (! is_array($product) || $product === []) {
                continue;
            }

            $currentPrice = (float) ($product['price'] ?? 0);
            $favoritedPrice = (float) ($row['favorited_price'] ?? 0);
            $type = strtolower(trim((string) ($product['type'] ?? '')));
            $isActive = (int) ($product['is_active'] ?? 0) === 1 && empty($product['deleted_at']);
            $isDigital = $type === 'dijital';
            $availableStock = (int) ($product['stock_count'] ?? ($product['stock'] ?? 0)) - (int) ($product['reserved_count'] ?? 0);
            $canAddToCart = $isActive && ($isDigital || $availableStock > 0);

            $items[] = [
                'favorite_id' => (string) ($row['id'] ?? ''),
                'product_id' => (string) ($product['id'] ?? ''),
                'product_name' => (string) ($product['product_name'] ?? ''),
                'author' => (string) ($product['author'] ?? ''),
                'type' => $type,
                'image_url' => product_image_url((string) ($product['image'] ?? '')),
                'detail_url' => base_url('products/detail/' . (string) ($product['id'] ?? '')),
                'favorited_price' => $favoritedPrice,
                'current_price' => $currentPrice,
                'is_price_dropped' => $currentPrice < $favoritedPrice,
                'is_active' => $isActive,
                'is_digital' => $isDigital,
                'available_stock' => max(0, $availableStock),
                'can_add_to_cart' => $canAddToCart,
                'stock_message' => $isDigital ? 'Dijital urun' : (($availableStock > 0) ? 'Stokta mevcut' : 'Stokta yok'),
                'favorited_at' => (string) ($row['created_at'] ?? ''),
            ];
        }

        usort($items, static fn(array $a, array $b) => strcmp((string) ($b['favorited_at'] ?? ''), (string) ($a['favorited_at'] ?? '')));

        return $items;
    }

    public function isFavorite(string $userId, string $productId): bool
    {
        return $this->favoriteModel
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first() !== null;
    }

    public function toggleFavorite(string $userId, string $productId): array
    {
        $product = $this->findUsableProductForFavorite($productId);
        if ($product === null) {
            return ['success' => false, 'message' => 'Urun favorilere eklenemedi.'];
        }

        $existing = $this->favoriteModel
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if (is_array($existing)) {
            $this->favoriteModel->delete((string) $existing['id']);
            return ['success' => true, 'message' => 'Urun favorilerden kaldirildi.', 'state' => 'removed'];
        }

        $inserted = $this->favoriteModel->insert([
            'user_id' => $userId,
            'product_id' => $productId,
            'favorited_price' => (float) ($product['price'] ?? 0),
        ]);

        if ($inserted === false) {
            return ['success' => false, 'message' => 'Favori islemi basarisiz oldu.'];
        }

        return ['success' => true, 'message' => 'Urun favorilere eklendi.', 'state' => 'added'];
    }

    public function removeFavorite(string $userId, string $productId): array
    {
        $existing = $this->favoriteModel
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if (! is_array($existing)) {
            return ['success' => false, 'message' => 'Favori kaydi bulunamadi.'];
        }

        $this->favoriteModel->delete((string) $existing['id']);
        return ['success' => true, 'message' => 'Urun favorilerden kaldirildi.'];
    }

    public function addFavoriteProductToCart(string $userId, string $productId): array
    {
        $favorite = $this->favoriteModel
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if (! is_array($favorite)) {
            return ['success' => false, 'message' => 'Urun favorilerinizde bulunamadi.'];
        }

        return $this->cartService->addProduct($userId, $productId, 1);
    }

    private function findUsableProductForFavorite(string $productId): ?array
    {
        $product = $this->productsModel->where('id', $productId)->first();
        if (! is_array($product) || $product === []) {
            return null;
        }

        if ((int) ($product['is_active'] ?? 0) !== 1 || ! empty($product['deleted_at'])) {
            return null;
        }

        return $product;
    }
}
