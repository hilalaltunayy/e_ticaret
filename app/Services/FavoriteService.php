<?php

namespace App\Services;

use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\FavoriteModel;
use App\Models\ProductsModel;

class FavoriteService
{
    private FavoriteModel $favoriteModel;
    private ProductsModel $productsModel;
    private CartModel $cartModel;
    private CartItemModel $cartItemModel;

    public function __construct()
    {
        $this->favoriteModel = new FavoriteModel();
        $this->productsModel = new ProductsModel();
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
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
            if (!is_array($product) || $product === []) {
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
                'stock_message' => $isDigital ? 'Dijital ürün' : (($availableStock > 0) ? 'Stokta mevcut' : 'Stokta yok'),
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
            return ['success' => false, 'message' => 'Ürün favorilere eklenemedi.'];
        }

        $existing = $this->favoriteModel
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if (is_array($existing)) {
            $this->favoriteModel->delete((string) $existing['id']);
            return ['success' => true, 'message' => 'Ürün favorilerden kaldırıldı.', 'state' => 'removed'];
        }

        $inserted = $this->favoriteModel->insert([
            'user_id' => $userId,
            'product_id' => $productId,
            'favorited_price' => (float) ($product['price'] ?? 0),
        ]);

        if ($inserted === false) {
            return ['success' => false, 'message' => 'Favori işlemi başarısız oldu.'];
        }

        return ['success' => true, 'message' => 'Ürün favorilere eklendi.', 'state' => 'added'];
    }

    public function removeFavorite(string $userId, string $productId): array
    {
        $existing = $this->favoriteModel
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if (!is_array($existing)) {
            return ['success' => false, 'message' => 'Favori kaydı bulunamadı.'];
        }

        $this->favoriteModel->delete((string) $existing['id']);
        return ['success' => true, 'message' => 'Ürün favorilerden kaldırıldı.'];
    }

    public function addFavoriteProductToCart(string $userId, string $productId): array
    {
        $favorite = $this->favoriteModel
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
        if (!is_array($favorite)) {
            return ['success' => false, 'message' => 'Ürün favorilerinizde bulunamadı.'];
        }

        $product = $this->findUsableProductForFavorite($productId);
        if ($product === null) {
            return ['success' => false, 'message' => 'Ürün sepete eklenemiyor.'];
        }

        $type = strtolower(trim((string) ($product['type'] ?? '')));
        $isDigital = $type === 'dijital';
        $availableStock = (int) ($product['stock_count'] ?? ($product['stock'] ?? 0)) - (int) ($product['reserved_count'] ?? 0);
        if (!$isDigital && $availableStock <= 0) {
            return ['success' => false, 'message' => 'Stokta olmayan ürün sepete eklenemez.'];
        }

        $cart = $this->cartModel
            ->where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->first();

        if (!is_array($cart)) {
            $newCartId = $this->cartModel->insert([
                'user_id' => $userId,
                'status' => 'ACTIVE',
                'currency' => 'TRY',
            ], true);
            if ($newCartId === false) {
                return ['success' => false, 'message' => 'Sepet oluşturulamadı.'];
            }
            $cart = $this->cartModel->find((string) $newCartId);
        }

        if (!is_array($cart) || empty($cart['id'])) {
            return ['success' => false, 'message' => 'Sepet bulunamadı.'];
        }

        $cartId = (string) $cart['id'];
        $cartItem = $this->cartItemModel
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        $unitPrice = (float) ($product['price'] ?? 0);
        if (is_array($cartItem)) {
            $currentQuantity = max(1, (int) ($cartItem['quantity'] ?? 1));
            $nextQuantity = $isDigital ? 1 : ($currentQuantity + 1);
            $ok = $this->cartItemModel->update((string) $cartItem['id'], [
                'quantity' => $nextQuantity,
                'unit_price_snapshot' => $unitPrice,
            ]);
            if (!$ok) {
                return ['success' => false, 'message' => 'Sepet güncellenemedi.'];
            }
        } else {
            $ok = $this->cartItemModel->insert([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => 1,
                'unit_price_snapshot' => $unitPrice,
            ]);
            if ($ok === false) {
                return ['success' => false, 'message' => 'Sepete eklenemedi.'];
            }
        }

        return ['success' => true, 'message' => 'Ürün sepete eklendi.'];
    }

    private function findUsableProductForFavorite(string $productId): ?array
    {
        $product = $this->productsModel->where('id', $productId)->first();
        if (!is_array($product) || $product === []) {
            return null;
        }

        if ((int) ($product['is_active'] ?? 0) !== 1 || !empty($product['deleted_at'])) {
            return null;
        }

        return $product;
    }
}
