<?php

namespace App\Services;

use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\FavoriteModel;
use App\Models\OrderModel;
use App\Models\UserModel;

class AccountService
{
    private UserModel $userModel;
    private FavoriteModel $favoriteModel;
    private CartModel $cartModel;
    private CartItemModel $cartItemModel;
    private OrderModel $orderModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->favoriteModel = new FavoriteModel();
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
        $this->orderModel = new OrderModel();
    }

    public function getAccountOverview(string $userId): array
    {
        $user = $this->userModel->find($userId);
        if (! is_array($user) || $user === []) {
            return [
                'user' => null,
                'stats' => $this->emptyStats(),
                'latestOrder' => null,
            ];
        }

        return [
            'user' => $user,
            'stats' => $this->getAccountStats($userId),
            'latestOrder' => $this->getLatestOrderSummary($userId),
        ];
    }

    public function updateProfile(string $userId, array $input): array
    {
        $user = $this->userModel->find($userId);
        if (! is_array($user) || $user === []) {
            return [
                'success' => false,
                'message' => 'Hesap bilgileri bulunamadı.',
            ];
        }

        $username = trim((string) ($input['username'] ?? ''));
        if ($username === '') {
            return [
                'success' => false,
                'message' => 'Ad Soyad alanı boş bırakılamaz.',
                'errors' => ['username' => 'Ad Soyad alanı boş bırakılamaz.'],
            ];
        }

        if (mb_strlen($username, 'UTF-8') < 2) {
            return [
                'success' => false,
                'message' => 'Ad Soyad en az 2 karakter olmalıdır.',
                'errors' => ['username' => 'Ad Soyad en az 2 karakter olmalıdır.'],
            ];
        }

        $updated = $this->userModel->update($userId, [
            'username' => $username,
        ]);

        if (! $updated) {
            return [
                'success' => false,
                'message' => 'Profil bilgileri güncellenemedi.',
            ];
        }

        $freshUser = $this->userModel->find($userId);
        if (is_array($freshUser)) {
            $this->refreshSessionUser($freshUser);
        }

        return [
            'success' => true,
            'message' => 'Profil bilgileriniz güncellendi.',
        ];
    }

    public function updatePassword(string $userId, string $currentPassword, string $newPassword, string $confirmPassword): array
    {
        $user = $this->userModel->find($userId);
        if (! is_array($user) || $user === []) {
            return [
                'success' => false,
                'message' => 'Hesap bilgileri bulunamadı.',
            ];
        }

        $currentPassword = trim($currentPassword);
        $newPassword = trim($newPassword);
        $confirmPassword = trim($confirmPassword);

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return [
                'success' => false,
                'message' => 'Lütfen tüm şifre alanlarını doldurun.',
            ];
        }

        if (! password_verify($currentPassword, (string) ($user['password'] ?? ''))) {
            return [
                'success' => false,
                'message' => 'Mevcut şifreniz doğrulanamadı.',
            ];
        }

        if (mb_strlen($newPassword, 'UTF-8') < 8) {
            return [
                'success' => false,
                'message' => 'Yeni şifre en az 8 karakter olmalıdır.',
            ];
        }

        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Yeni şifre tekrarı eşleşmiyor.',
            ];
        }

        if ($currentPassword === $newPassword) {
            return [
                'success' => false,
                'message' => 'Yeni şifre mevcut şifreden farklı olmalıdır.',
            ];
        }

        $updated = $this->userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        if (! $updated) {
            return [
                'success' => false,
                'message' => 'Şifre güncellenemedi.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Şifreniz başarıyla güncellendi.',
        ];
    }

    public function getAccountStats(string $userId): array
    {
        $favoriteCount = $this->favoriteModel
            ->where('user_id', $userId)
            ->countAllResults();

        $ordersCount = $this->orderModel
            ->where('user_id', $userId)
            ->countAllResults();

        $activeCart = $this->cartModel
            ->where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->first();

        $cartItemCount = 0;
        if (is_array($activeCart) && ! empty($activeCart['id'])) {
            $cartItems = $this->cartItemModel
                ->where('cart_id', (string) $activeCart['id'])
                ->findAll();

            foreach ($cartItems as $item) {
                $cartItemCount += max(1, (int) ($item['quantity'] ?? 1));
            }
        }

        return [
            'orders_count' => $ordersCount,
            'favorites_count' => $favoriteCount,
            'cart_item_count' => $cartItemCount,
            'security_label' => 'Şifrenizi güncelleyin',
        ];
    }

    private function getLatestOrderSummary(string $userId): ?array
    {
        $order = $this->orderModel
            ->where('user_id', $userId)
            ->orderBy('order_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (! is_array($order) || $order === []) {
            return null;
        }

        $identifier = trim((string) ($order['order_no'] ?? $order['id'] ?? ''));
        if ($identifier === '') {
            return null;
        }

        $status = trim((string) ($order['status'] ?? $order['order_status'] ?? 'PENDING_PAYMENT'));

        return [
            'order_number' => trim((string) ($order['order_no'] ?? '')) !== '' ? (string) $order['order_no'] : $identifier,
            'order_date' => $this->formatDateTime((string) ($order['order_date'] ?? $order['created_at'] ?? '')),
            'status' => $status,
            'status_label' => $this->mapOrderStatusLabel($status),
            'total_amount' => (float) ($order['total_amount'] ?? 0),
            'currency' => (string) ($order['currency'] ?? 'TRY'),
            'detail_url' => base_url('yardim/siparislerim/' . urlencode($identifier)),
        ];
    }

    private function mapOrderStatusLabel(string $status): string
    {
        return match (strtoupper(trim($status))) {
            'PENDING_PAYMENT' => 'Ödeme Bekleniyor',
            'PAID' => 'Ödeme Alındı',
            'PREPARING' => 'Hazırlanıyor',
            'SHIPPED', 'IN_TRANSIT' => 'Kargoda',
            'OUT_FOR_DELIVERY' => 'Dağıtıma Çıktı',
            'DELIVERED' => 'Teslim Edildi',
            'CANCELLED' => 'İptal Edildi',
            'RETURN_REQUESTED' => 'İade Talebi Alındı',
            'RETURNED' => 'İade Edildi',
            'REFUNDED' => 'İade Ödendi',
            default => 'Sipariş Alındı',
        };
    }

    private function formatDateTime(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '-';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return date('d.m.Y H:i', $timestamp);
    }

    private function refreshSessionUser(array $user): void
    {
        $sessionUser = session()->get('user');
        $sessionUser = is_array($sessionUser) ? $sessionUser : [];
        $sessionUser['id'] = (string) ($user['id'] ?? ($sessionUser['id'] ?? ''));
        $sessionUser['role'] = (string) ($user['role'] ?? ($sessionUser['role'] ?? ''));
        $sessionUser['email'] = (string) ($user['email'] ?? ($sessionUser['email'] ?? ''));
        $sessionUser['name'] = (string) ($user['username'] ?? ($sessionUser['name'] ?? ''));

        session()->set([
            'user' => $sessionUser,
            'user_id' => (string) ($user['id'] ?? session()->get('user_id')),
            'role' => (string) ($user['role'] ?? session()->get('role')),
        ]);
    }

    private function emptyStats(): array
    {
        return [
            'orders_count' => 0,
            'favorites_count' => 0,
            'cart_item_count' => 0,
            'security_label' => 'Şifrenizi güncelleyin',
        ];
    }
}
