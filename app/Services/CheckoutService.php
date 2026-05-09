<?php

namespace App\Services;

use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\ProductsModel;

class CheckoutService
{
    private CartService $cartService;
    private CartModel $cartModel;
    private CartItemModel $cartItemModel;
    private OrderModel $orderModel;
    private OrderItemModel $orderItemModel;
    private ProductsModel $productsModel;

    public function __construct()
    {
        $this->cartService = new CartService();
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->productsModel = new ProductsModel();
    }

    public function buildCheckoutViewModel(string $userId, array $sessionUser = []): array
    {
        $cartView = $this->cartService->getCartViewModel($userId);
        $items = is_array($cartView['items'] ?? null) ? $cartView['items'] : [];

        return [
            'cartView' => $cartView,
            'items' => $items,
            'contact' => [
                'name' => trim((string) ($sessionUser['username'] ?? $sessionUser['name'] ?? '')),
                'email' => trim((string) ($sessionUser['email'] ?? '')),
                'phone' => trim((string) ($sessionUser['phone'] ?? '')),
            ],
            'delivery' => [
                'title' => 'Teslimat bilgileri',
                'description' => 'Adres ve teslimat secenekleri siparis onayi akisi tamamlanirken netlestirilecek.',
                'status' => 'Adres defteri sonraki sprintte eklenecek.',
            ],
            'billing' => [
                'title' => 'Fatura bilgileri',
                'description' => 'Kurumsal veya bireysel fatura tercihleri odeme altyapisi ile birlikte tamamlanacak.',
                'status' => 'Fatura detay formu sonraki sprintte acilacak.',
            ],
            'payment' => [
                'title' => 'Odeme yontemi',
                'description' => 'Sanal POS entegrasyonu sonraki sprintte baglanacak.',
                'button_label' => 'Sanal POS Sonraki Sprintte',
            ],
            'securityNotes' => [
                'Toplam tutar ve urun detaylari bu adimda tekrar kontrol edilir.',
                'Gercek kart cekimi veya odeme islemi bu ekranda yapilmaz.',
                'Dijital erisim, stok dusumu ve siparis kaydi entegrasyon sonrasi acilacak.',
            ],
        ];
    }

    public function completeSimulatedCheckout(string $userId, array $sessionUser = [], array $payload = []): array
    {
        $userId = trim($userId);
        if ($userId === '') {
            return [
                'success' => false,
                'message' => 'Oturum bilgisi bulunamadı.',
                'order_no' => null,
            ];
        }

        $validationErrors = $this->validateCheckoutPayload($payload, $sessionUser);
        if ($validationErrors !== []) {
            return [
                'success' => false,
                'message' => implode(' ', $validationErrors),
                'errors' => $validationErrors,
                'order_no' => null,
            ];
        }

        $cart = $this->cartService->getActiveCartForUser($userId);
        if (! is_array($cart) || empty($cart['id'])) {
            return [
                'success' => false,
                'message' => 'Tamamlanacak aktif sepet bulunamadı.',
                'order_no' => null,
            ];
        }

        $cartView = $this->cartService->getCartViewModel($userId);
        $viewItems = is_array($cartView['items'] ?? null) ? $cartView['items'] : [];
        if ((int) ($cartView['item_count'] ?? 0) <= 0 || $viewItems === []) {
            return [
                'success' => false,
                'message' => 'Ödeme adımına geçmeden önce sepetinizde ürün bulunmalıdır.',
                'order_no' => null,
            ];
        }

        $cartItems = $this->cartItemModel
            ->where('cart_id', (string) $cart['id'])
            ->findAll();

        if ($cartItems === []) {
            return [
                'success' => false,
                'message' => 'Sepet içeriği okunamadı.',
                'order_no' => null,
            ];
        }

        $viewItemMap = [];
        foreach ($viewItems as $viewItem) {
            $key = trim((string) ($viewItem['cart_item_id'] ?? ''));
            if ($key !== '') {
                $viewItemMap[$key] = $viewItem;
            }
        }

        $normalizedItems = [];
        $firstProductId = '';
        $totalQuantity = 0;
        $subtotalAmount = 0.0;

        foreach ($cartItems as $cartItem) {
            $cartItemId = trim((string) ($cartItem['id'] ?? ''));
            $productId = trim((string) ($cartItem['product_id'] ?? ''));

            if ($productId === '') {
                return [
                    'success' => false,
                    'message' => 'Sepetteki bir ürün doğrulanamadı.',
                    'order_no' => null,
                ];
            }

            $product = $this->productsModel->where('id', $productId)->where('deleted_at', null)->first();
            if (! is_array($product) || $product === []) {
                return [
                    'success' => false,
                    'message' => 'Sepetteki bir ürün artık erişilebilir değil.',
                    'order_no' => null,
                ];
            }

            $quantity = max(1, (int) ($cartItem['quantity'] ?? 1));
            $isDigital = $this->isDigitalProduct($product);
            if (! $isDigital && $this->getAvailableStock($product) < $quantity) {
                return [
                    'success' => false,
                    'message' => 'Bazı ürünlerde yeterli stok kalmadığı için sipariş tamamlanamadı.',
                    'order_no' => null,
                ];
            }

            $viewItem = $viewItemMap[$cartItemId] ?? [];
            $unitPrice = (float) ($viewItem['current_price'] ?? $product['price'] ?? $cartItem['unit_price_snapshot'] ?? 0);
            $lineTotal = (float) ($viewItem['line_total_current'] ?? ($unitPrice * $quantity));

            if ($firstProductId === '') {
                $firstProductId = $productId;
            }

            $totalQuantity += $quantity;
            $subtotalAmount += $lineTotal;

            $normalizedItems[] = [
                'product_id' => $productId,
                'product_name_snapshot' => (string) ($product['product_name'] ?? 'Ürün'),
                'author' => trim((string) ($product['author'] ?? '')) !== '' ? (string) $product['author'] : null,
                'product_image' => trim((string) ($product['image'] ?? '')) !== '' ? (string) $product['image'] : null,
                'product_type' => trim((string) ($product['type'] ?? '')) !== '' ? (string) $product['type'] : null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'item_status' => 'PREPARING',
                'reserve_stock' => ! $isDigital,
            ];
        }

        if ($normalizedItems === [] || $firstProductId === '') {
            return [
                'success' => false,
                'message' => 'Sipariş için uygun ürün bulunamadı.',
                'order_no' => null,
            ];
        }

        $orderId = OrderModel::uuidV4();
        $orderNo = 'ORD-' . strtoupper(substr(str_replace('-', '', $orderId), 0, 10));
        $now = date('Y-m-d H:i:s');
        $customerName = $this->resolveCustomerName($sessionUser, $payload);

        $shippingAddress = trim((string) ($payload['delivery_address'] ?? ''));
        $shippingLabel = trim((string) ($payload['delivery_label'] ?? ''));
        $shippingCity = trim((string) ($payload['delivery_city'] ?? ''));
        $shippingDistrict = trim((string) ($payload['delivery_town'] ?? ''));

        $orderData = [
            'id' => $orderId,
            'order_no' => $orderNo,
            'user_id' => $userId,
            'product_id' => $firstProductId,
            'quantity' => $totalQuantity,
            'total_price' => (float) ($cartView['grand_total_current'] ?? $subtotalAmount),
            'total_amount' => (float) ($cartView['grand_total_current'] ?? $subtotalAmount),
            'customer_name' => $customerName !== '' ? $customerName : null,
            'payment_method' => 'mock_checkout',
            'payment_status' => 'paid',
            'status' => 'reserved',
            'order_status' => 'preparing',
            'fulfillment_status' => 'PREPARING',
            'shipping_status' => 'not_shipped',
            'item_count' => $totalQuantity,
            'subtotal_amount' => (float) ($cartView['subtotal_current'] ?? $subtotalAmount),
            'shipping_amount' => 0.0,
            'discount_amount' => 0.0,
            'currency' => (string) ($cartView['currency'] ?? 'TRY'),
            'shipping_address_line1' => $shippingAddress !== '' ? $shippingAddress : null,
            'shipping_address_line2' => $shippingLabel !== '' ? $shippingLabel : null,
            'shipping_city' => $shippingCity !== '' ? $shippingCity : null,
            'shipping_district' => $shippingDistrict !== '' ? $shippingDistrict : null,
            'shipping_country' => 'Türkiye',
            'reserved_at' => $now,
            'paid_at' => $now,
            'order_date' => $now,
            'updated_by' => $userId,
        ];

        $this->orderModel->db->transStart();

        foreach ($normalizedItems as $item) {
            if (! $item['reserve_stock']) {
                continue;
            }

            $reserved = $this->productsModel->reserveStockForOrder(
                (string) $item['product_id'],
                (int) $item['quantity'],
                $orderId,
                $userId
            );

            if (! $reserved) {
                $this->orderModel->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Stok rezervasyonu tamamlanamadı. Lütfen sepetinizi tekrar kontrol edin.',
                    'order_no' => null,
                ];
            }
        }

        $inserted = $this->orderModel->insert($orderData, false);
        if ($inserted === false) {
            $this->orderModel->db->transRollback();
            return [
                'success' => false,
                'message' => 'Sipariş kaydı oluşturulamadı.',
                'order_no' => null,
            ];
        }

        foreach ($normalizedItems as $item) {
            $itemInserted = $this->orderItemModel->insert([
                'order_id' => $orderId,
                'product_id' => (string) $item['product_id'],
                'product_name_snapshot' => (string) $item['product_name_snapshot'],
                'author' => $item['author'],
                'product_image' => $item['product_image'],
                'product_type' => $item['product_type'],
                'unit_price' => (float) $item['unit_price'],
                'quantity' => (int) $item['quantity'],
                'line_total' => (float) $item['line_total'],
                'item_status' => (string) $item['item_status'],
            ]);

            if ($itemInserted === false) {
                $this->orderModel->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Sipariş kalemleri oluşturulamadı.',
                    'order_no' => null,
                ];
            }
        }

        foreach ($normalizedItems as $item) {
            if (! $item['reserve_stock']) {
                continue;
            }

            $stockMoved = $this->productsModel->finalizeReservedForPaidOrder(
                (string) $item['product_id'],
                (int) $item['quantity'],
                $orderId,
                $userId
            );

            if (! $stockMoved) {
                $this->orderModel->db->transRollback();
                return [
                    'success' => false,
                    'message' => 'Bazı basılı ürünlerde stok güncellemesi tamamlanamadı. Sipariş oluşturulmadı.',
                    'order_no' => null,
                ];
            }
        }

        $cartUpdated = $this->cartModel->update((string) $cart['id'], [
            'status' => 'ORDERED',
        ]);

        if (! $cartUpdated) {
            $this->orderModel->db->transRollback();
            return [
                'success' => false,
                'message' => 'Sepet durumu güncellenemedi.',
                'order_no' => null,
            ];
        }

        $this->orderModel->db->transComplete();

        if (! $this->orderModel->db->transStatus()) {
            return [
                'success' => false,
                'message' => 'Sipariş tamamlama işlemi başarısız oldu.',
                'order_no' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Siparişiniz oluşturuldu. Simüle ödeme tamamlandı.',
            'order_id' => $orderId,
            'order_no' => $orderNo,
        ];
    }

    private function resolveCustomerName(array $sessionUser, array $payload): string
    {
        $deliveryName = trim((string) ($payload['delivery_name'] ?? ''));
        if ($deliveryName !== '') {
            return $deliveryName;
        }

        $sessionName = trim((string) ($sessionUser['name'] ?? $sessionUser['username'] ?? ''));
        if ($sessionName !== '') {
            return $sessionName;
        }

        return trim((string) ($sessionUser['email'] ?? ''));
    }

    private function isDigitalProduct(array $product): bool
    {
        return strtolower(trim((string) ($product['type'] ?? ''))) === 'dijital';
    }

    private function validateCheckoutPayload(array $payload, array $sessionUser): array
    {
        $errors = [];

        $customerName = $this->resolveCustomerName($sessionUser, $payload);
        $phone = trim((string) ($payload['delivery_phone'] ?? $payload['contact_phone'] ?? ''));
        $city = trim((string) ($payload['delivery_city'] ?? ''));
        $district = trim((string) ($payload['delivery_town'] ?? ''));
        $address = trim((string) ($payload['delivery_address'] ?? ''));

        if ($customerName === '') {
            $errors[] = 'Ad Soyad alani zorunludur.';
        }

        if ($phone === '') {
            $errors[] = 'Telefon alani zorunludur.';
        }

        if ($city === '') {
            $errors[] = 'Il alani zorunludur.';
        }

        if ($district === '') {
            $errors[] = 'Ilce alani zorunludur.';
        }

        if ($address === '') {
            $errors[] = 'Adres alani zorunludur.';
        }

        return $errors;
    }

    private function getAvailableStock(array $product): int
    {
        $stockCount = (int) ($product['stock_count'] ?? ($product['stock'] ?? 0));
        $reserved = (int) ($product['reserved_count'] ?? 0);

        return max(0, $stockCount - $reserved);
    }
}
