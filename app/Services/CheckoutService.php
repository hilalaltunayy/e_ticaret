<?php

namespace App\Services;

class CheckoutService
{
    private CartService $cartService;

    public function __construct()
    {
        $this->cartService = new CartService();
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
}
