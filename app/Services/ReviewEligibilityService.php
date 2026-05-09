<?php

namespace App\Services;

class ReviewEligibilityService
{
    public function canUserReviewProduct(string $userId, string $productId): bool
    {
        $userId = trim($userId);
        $productId = trim($productId);

        if ($userId === '' || $productId === '') {
            return false;
        }

        $db = db_connect();
        if (! $db->tableExists('orders') || ! $db->tableExists('order_items')) {
            return false;
        }

        $row = $db->table('orders')
            ->select('orders.id')
            ->join('order_items', 'order_items.order_id = orders.id', 'inner')
            ->where('orders.user_id', $userId)
            ->where('orders.deleted_at', null)
            ->where('order_items.deleted_at', null)
            ->where('order_items.product_id', $productId)
            ->groupStart()
                ->whereIn('orders.order_status', ['shipped', 'delivered', 'return_in_progress', 'return_done'])
                ->orWhere('orders.payment_status', 'paid')
                ->orWhereIn('orders.status', ['paid', 'shipped', 'completed', 'returned'])
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRowArray();

        return is_array($row) && isset($row['id']);
    }
}
