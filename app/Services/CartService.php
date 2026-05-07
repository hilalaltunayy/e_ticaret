<?php

namespace App\Services;

use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\ProductsModel;

class CartService
{
    private CartModel $cartModel;
    private CartItemModel $cartItemModel;
    private ProductsModel $productsModel;

    public function __construct()
    {
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
        $this->productsModel = new ProductsModel();
        helper('product_media');
    }

    public function getActiveCartForUser(string $userId): ?array
    {
        if (trim($userId) === '') {
            return null;
        }

        $cart = $this->cartModel
            ->where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->first();

        return is_array($cart) ? $cart : null;
    }

    public function getOrCreateActiveCart(string $userId): ?array
    {
        $cart = $this->getActiveCartForUser($userId);
        if ($cart !== null) {
            return $cart;
        }

        $newCartId = $this->cartModel->insert([
            'user_id' => $userId,
            'status' => 'ACTIVE',
            'currency' => 'TRY',
        ], true);

        if ($newCartId === false) {
            return null;
        }

        $cart = $this->cartModel->find((string) $newCartId);
        return is_array($cart) ? $cart : null;
    }

    public function getCartViewModel(string $userId): array
    {
        $cart = $this->getActiveCartForUser($userId);
        $items = [];
        $itemCount = 0;
        $subtotalCurrent = 0.0;
        $subtotalSnapshot = 0.0;
        $totalSavings = 0.0;
        $hasUnavailableItems = false;
        $hasStockWarnings = false;
        $hasPriceDrops = false;

        if ($cart !== null) {
            $cartItems = $this->cartItemModel
                ->where('cart_id', (string) $cart['id'])
                ->findAll();

            foreach ($cartItems as $cartItem) {
                $item = $this->buildCartItemViewModel($cartItem);
                if ($item === null) {
                    continue;
                }

                $items[] = $item;
                $itemCount += (int) $item['quantity'];
                $subtotalCurrent += (float) $item['line_total_current'];
                $subtotalSnapshot += (float) $item['line_total_snapshot'];
                $totalSavings += (float) $item['saving_total'];
                $hasUnavailableItems = $hasUnavailableItems || ! (bool) $item['is_available'];
                $hasStockWarnings = $hasStockWarnings || (bool) $item['has_stock_warning'];
                $hasPriceDrops = $hasPriceDrops || (bool) $item['is_price_dropped'];
            }
        }

        return [
            'cart' => $cart,
            'items' => $items,
            'item_count' => $itemCount,
            'subtotal_current' => $subtotalCurrent,
            'subtotal_snapshot' => $subtotalSnapshot,
            'total_savings' => $totalSavings,
            'grand_total_current' => $subtotalCurrent,
            'currency' => 'TRY',
            'has_unavailable_items' => $hasUnavailableItems,
            'has_stock_warnings' => $hasStockWarnings,
            'has_price_drops' => $hasPriceDrops,
            'shipping_info' => 'Kargo ucreti odeme adiminda hesaplanacak.',
        ];
    }

    public function addProduct(string $userId, string $productId, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);
        $product = $this->findAddableProduct($productId);
        if ($product === null) {
            return ['success' => false, 'message' => 'Urun sepete eklenemedi.'];
        }

        $cart = $this->getOrCreateActiveCart($userId);
        if ($cart === null || empty($cart['id'])) {
            return ['success' => false, 'message' => 'Sepet olusturulamadi.'];
        }

        $cartId = (string) $cart['id'];
        $item = $this->cartItemModel
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        $isDigital = $this->isDigitalProduct($product);
        $availableStock = $this->getAvailableStock($product);
        $targetQuantity = $isDigital ? 1 : $quantity;

        if (is_array($item)) {
            $currentQuantity = max(1, (int) ($item['quantity'] ?? 1));
            $nextQuantity = $isDigital ? 1 : ($currentQuantity + $targetQuantity);
            if (! $isDigital && $nextQuantity > $availableStock) {
                return ['success' => false, 'message' => 'Stok miktari asilamaz.'];
            }

            $updated = $this->cartItemModel->update((string) $item['id'], [
                'quantity' => $nextQuantity,
            ]);

            if (! $updated) {
                return ['success' => false, 'message' => 'Sepet guncellenemedi.'];
            }

            return ['success' => true, 'message' => 'Urun sepetinize eklendi.'];
        }

        if (! $isDigital && $targetQuantity > $availableStock) {
            return ['success' => false, 'message' => 'Stok miktari asilamaz.'];
        }

        $inserted = $this->cartItemModel->insert([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $targetQuantity,
            'unit_price_snapshot' => (float) ($product['price'] ?? 0),
        ]);

        if ($inserted === false) {
            return ['success' => false, 'message' => 'Urun sepete eklenemedi.'];
        }

        return ['success' => true, 'message' => 'Urun sepetinize eklendi.'];
    }

    public function increaseItem(string $userId, string $cartItemId): array
    {
        $itemContext = $this->findOwnedCartItem($userId, $cartItemId);
        if ($itemContext === null) {
            return ['success' => false, 'message' => 'Sepet urunu bulunamadi.'];
        }

        $product = $itemContext['product'];
        if ($this->isDigitalProduct($product)) {
            return ['success' => false, 'message' => 'Dijital urun adet 1 olarak tutulur.'];
        }

        $availableStock = $this->getAvailableStock($product);
        $currentQuantity = max(1, (int) ($itemContext['item']['quantity'] ?? 1));
        if ($availableStock <= $currentQuantity) {
            return ['success' => false, 'message' => 'Stok miktari asilamaz.'];
        }

        $ok = $this->cartItemModel->update($cartItemId, [
            'quantity' => $currentQuantity + 1,
        ]);

        return $ok
            ? ['success' => true, 'message' => 'Urun adedi guncellendi.']
            : ['success' => false, 'message' => 'Urun adedi guncellenemedi.'];
    }

    public function decreaseItem(string $userId, string $cartItemId): array
    {
        $itemContext = $this->findOwnedCartItem($userId, $cartItemId);
        if ($itemContext === null) {
            return ['success' => false, 'message' => 'Sepet urunu bulunamadi.'];
        }

        $product = $itemContext['product'];
        if ($this->isDigitalProduct($product)) {
            return ['success' => false, 'message' => 'Dijital urun adet 1 olarak tutulur.'];
        }

        $currentQuantity = max(1, (int) ($itemContext['item']['quantity'] ?? 1));
        $nextQuantity = max(1, $currentQuantity - 1);
        if ($nextQuantity === $currentQuantity) {
            return ['success' => false, 'message' => 'Urun adedi 1 altina dusurulemez.'];
        }

        $ok = $this->cartItemModel->update($cartItemId, [
            'quantity' => $nextQuantity,
        ]);

        return $ok
            ? ['success' => true, 'message' => 'Urun adedi guncellendi.']
            : ['success' => false, 'message' => 'Urun adedi guncellenemedi.'];
    }

    public function updateItemQuantity(string $userId, string $cartItemId, int $quantity): array
    {
        $quantity = max(1, $quantity);
        $itemContext = $this->findOwnedCartItem($userId, $cartItemId);
        if ($itemContext === null) {
            return ['success' => false, 'message' => 'Sepet urunu bulunamadi.'];
        }

        $product = $itemContext['product'];
        if ($this->isDigitalProduct($product)) {
            $quantity = 1;
        } elseif ($quantity > $this->getAvailableStock($product)) {
            return ['success' => false, 'message' => 'Stok miktari asilamaz.'];
        }

        $ok = $this->cartItemModel->update($cartItemId, [
            'quantity' => $quantity,
        ]);

        return $ok
            ? ['success' => true, 'message' => 'Urun adedi guncellendi.']
            : ['success' => false, 'message' => 'Urun adedi guncellenemedi.'];
    }

    public function removeItem(string $userId, string $cartItemId): array
    {
        $itemContext = $this->findOwnedCartItem($userId, $cartItemId);
        if ($itemContext === null) {
            return ['success' => false, 'message' => 'Sepet urunu bulunamadi.'];
        }

        $ok = $this->cartItemModel->delete($cartItemId);

        return $ok
            ? ['success' => true, 'message' => 'Urun sepetten kaldirildi.']
            : ['success' => false, 'message' => 'Urun sepetten kaldirilamadi.'];
    }

    public function clearCart(string $userId): array
    {
        $cart = $this->getActiveCartForUser($userId);
        if ($cart === null || empty($cart['id'])) {
            return ['success' => false, 'message' => 'Bos sepet temizlenemedi.'];
        }

        $items = $this->cartItemModel->where('cart_id', (string) $cart['id'])->findAll();
        foreach ($items as $item) {
            $this->cartItemModel->delete((string) ($item['id'] ?? ''));
        }

        return ['success' => true, 'message' => 'Sepet temizlendi.'];
    }

    public function getSuggestedProducts(string $userId, int $limit = 4): array
    {
        $limit = max(1, min($limit, 8));
        $excludedProductIds = [];
        $cart = $this->getActiveCartForUser($userId);
        if ($cart !== null && ! empty($cart['id'])) {
            $rows = $this->cartItemModel
                ->select('product_id')
                ->where('cart_id', (string) $cart['id'])
                ->findAll();

            $excludedProductIds = array_values(array_filter(array_map(
                static fn(array $row): string => trim((string) ($row['product_id'] ?? '')),
                $rows
            )));
        }

        $builder = $this->productsModel
            ->builder()
            ->select('products.*')
            ->where('products.is_active', 1)
            ->where('products.deleted_at', null)
            ->orderBy('CASE WHEN products.type = ' . $this->productsModel->db->escape('basili') . ' AND GREATEST(products.stock_count - products.reserved_count, 0) > 0 THEN 0 WHEN products.type = ' . $this->productsModel->db->escape('dijital') . ' THEN 1 ELSE 2 END', '', false)
            ->orderBy('products.updated_at', 'DESC')
            ->limit($limit);

        if ($excludedProductIds !== []) {
            $builder->whereNotIn('products.id', $excludedProductIds);
        }

        $rows = $builder->get()->getResultArray();
        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'product_id' => (string) ($row['id'] ?? ''),
                'title' => (string) ($row['product_name'] ?? ''),
                'author' => (string) ($row['author'] ?? ''),
                'type' => (string) ($row['type'] ?? ''),
                'price' => (float) ($row['price'] ?? 0),
                'image_url' => product_image_url((string) ($row['image'] ?? '')),
                'detail_url' => base_url('products/detail/' . (string) ($row['id'] ?? '')),
                'is_digital' => $this->isDigitalProduct($row),
                'can_add_to_cart' => $this->isProductCartAddable($row),
            ];
        }

        return $items;
    }

    private function buildCartItemViewModel(array $cartItem): ?array
    {
        $product = $this->productsModel
            ->where('id', (string) ($cartItem['product_id'] ?? ''))
            ->first();

        if (! is_array($product) || $product === []) {
            return null;
        }

        $isDigital = $this->isDigitalProduct($product);
        $isActive = (int) ($product['is_active'] ?? 0) === 1 && empty($product['deleted_at']);
        $availableStock = $this->getAvailableStock($product);
        $quantity = max(1, (int) ($cartItem['quantity'] ?? 1));
        $currentPrice = (float) ($product['price'] ?? 0);
        $snapshotPrice = (float) ($cartItem['unit_price_snapshot'] ?? 0);
        $savingPerUnit = max(0, $snapshotPrice - $currentPrice);
        $savingTotal = $savingPerUnit * $quantity;
        $isPriceDropped = $currentPrice < $snapshotPrice;
        $hasStockWarning = ! $isDigital && $availableStock < $quantity;
        $isAvailable = $isActive && ($isDigital || $availableStock > 0);

        $stockMessage = $isDigital
            ? 'Dijital urun'
            : ($availableStock <= 0
                ? 'Stokta yok'
                : ($hasStockWarning ? 'Sinirli stok' : 'Stokta mevcut'));

        return [
            'cart_item_id' => (string) ($cartItem['id'] ?? ''),
            'product_id' => (string) ($product['id'] ?? ''),
            'product_name' => (string) ($product['product_name'] ?? ''),
            'author' => (string) ($product['author'] ?? ''),
            'type' => strtolower(trim((string) ($product['type'] ?? ''))),
            'is_digital' => $isDigital,
            'quantity' => $quantity,
            'current_price' => $currentPrice,
            'snapshot_price' => $snapshotPrice,
            'line_total_current' => $currentPrice * $quantity,
            'line_total_snapshot' => $snapshotPrice * $quantity,
            'is_price_dropped' => $isPriceDropped,
            'saving_per_unit' => $savingPerUnit,
            'saving_total' => $savingTotal,
            'available_stock' => max(0, $availableStock),
            'has_stock_warning' => $hasStockWarning,
            'stock_message' => $stockMessage,
            'is_available' => $isAvailable,
            'can_increase' => ! $isDigital && $isActive && $availableStock > $quantity,
            'can_decrease' => ! $isDigital && $quantity > 1,
            'can_add_more' => ! $isDigital && $isActive && $availableStock > $quantity,
            'image_url' => product_image_url((string) ($product['image'] ?? '')),
            'detail_url' => base_url('products/detail/' . (string) ($product['id'] ?? '')),
        ];
    }

    private function findOwnedCartItem(string $userId, string $cartItemId): ?array
    {
        $cart = $this->getActiveCartForUser($userId);
        if ($cart === null || empty($cart['id'])) {
            return null;
        }

        $item = $this->cartItemModel
            ->where('id', $cartItemId)
            ->where('cart_id', (string) $cart['id'])
            ->first();

        if (! is_array($item)) {
            return null;
        }

        $product = $this->productsModel
            ->where('id', (string) ($item['product_id'] ?? ''))
            ->first();

        if (! is_array($product)) {
            return null;
        }

        return [
            'cart' => $cart,
            'item' => $item,
            'product' => $product,
        ];
    }

    private function findAddableProduct(string $productId): ?array
    {
        $product = $this->productsModel->where('id', $productId)->first();
        if (! is_array($product) || $product === []) {
            return null;
        }

        if ((int) ($product['is_active'] ?? 0) !== 1 || ! empty($product['deleted_at'])) {
            return null;
        }

        if (! $this->isDigitalProduct($product) && $this->getAvailableStock($product) <= 0) {
            return null;
        }

        return $product;
    }

    private function isDigitalProduct(array $product): bool
    {
        return strtolower(trim((string) ($product['type'] ?? ''))) === 'dijital';
    }

    private function getAvailableStock(array $product): int
    {
        $stockCount = (int) ($product['stock_count'] ?? ($product['stock'] ?? 0));
        $reserved = (int) ($product['reserved_count'] ?? 0);
        return max(0, $stockCount - $reserved);
    }

    private function isProductCartAddable(array $product): bool
    {
        $isActive = (int) ($product['is_active'] ?? 0) === 1 && empty($product['deleted_at']);
        return $isActive && ($this->isDigitalProduct($product) || $this->getAvailableStock($product) > 0);
    }
}
