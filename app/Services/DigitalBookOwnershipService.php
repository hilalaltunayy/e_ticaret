<?php

namespace App\Services;

class DigitalBookOwnershipService
{
    private const ALLOW_PAYMENT_STATUSES = ['paid'];
    private const ALLOW_ORDER_STATUSES = ['delivered'];
    private const ALLOW_LEGACY_STATUSES = ['completed', 'delivered', 'paid'];

    private const DENY_PAYMENT_STATUSES = ['unpaid', 'failed', 'refunded', 'partial_refund'];
    private const DENY_ORDER_STATUSES = ['pending', 'cancelled', 'return_in_progress', 'return_done', 'returned', 'refunded'];
    private const DENY_LEGACY_STATUSES = ['pending', 'reserved', 'cancelled', 'returned', 'refunded'];

    /**
     * DBR erişimi için ownership + status doğrulaması yapar.
     * Bu metot mevcut sipariş akışını değiştirmez; yalnızca okuyucu erişimi için kullanılmak üzere izole edilmiştir.
     */
    public function userCanReadPurchasedDigitalBook(string $userId, string $productId): bool
    {
        $userId = trim($userId);
        $productId = trim($productId);

        if ($userId === '' || $productId === '') {
            return false;
        }

        $db = db_connect();
        if (! $db->tableExists('orders') || ! $db->tableExists('order_items') || ! $db->tableExists('products')) {
            return false;
        }

        $row = $db->table('orders')
            ->select('orders.id')
            ->join('order_items', 'order_items.order_id = orders.id', 'inner')
            ->join('products', 'products.id = order_items.product_id', 'inner')
            ->where('orders.user_id', $userId)
            ->where('orders.deleted_at', null)
            ->where('order_items.deleted_at', null)
            ->where('products.deleted_at', null)
            ->where('order_items.product_id', $productId)
            ->whereIn('LOWER(TRIM(COALESCE(products.type, \'\')))', ['dijital', 'digital', 'ebook', 'e-book'], false)
            ->groupStart()
                ->groupStart()
                    ->whereIn('LOWER(TRIM(COALESCE(orders.payment_status, \'\')))', self::ALLOW_PAYMENT_STATUSES)
                    ->orWhereIn('LOWER(TRIM(COALESCE(orders.order_status, \'\')))', self::ALLOW_ORDER_STATUSES)
                    ->orWhereIn('LOWER(TRIM(COALESCE(orders.status, \'\')))', self::ALLOW_LEGACY_STATUSES)
                ->groupEnd()
                ->groupStart()
                    ->whereNotIn('LOWER(TRIM(COALESCE(orders.payment_status, \'\')))', self::DENY_PAYMENT_STATUSES)
                    ->whereNotIn('LOWER(TRIM(COALESCE(orders.order_status, \'\')))', self::DENY_ORDER_STATUSES)
                    ->whereNotIn('LOWER(TRIM(COALESCE(orders.status, \'\')))', self::DENY_LEGACY_STATUSES)
                ->groupEnd()
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRowArray();

        return is_array($row) && isset($row['id']);
    }
}

