<?php

namespace App\Services;

use App\Models\ProductReviewModel;
use App\Models\ProductsModel;

class ReviewService
{
    private ProductReviewModel $productReviewModel;
    private ReviewEligibilityService $reviewEligibilityService;
    private ProductsModel $productsModel;

    public function __construct()
    {
        $this->productReviewModel = new ProductReviewModel();
        $this->reviewEligibilityService = new ReviewEligibilityService();
        $this->productsModel = new ProductsModel();
    }

    public function getApprovedReviewsForProduct(string $productId): array
    {
        $productId = trim($productId);
        if ($productId === '') {
            return [];
        }

        return db_connect()->table('product_reviews')
            ->select('product_reviews.id, product_reviews.rating, product_reviews.title, product_reviews.comment, product_reviews.created_at, users.username, users.email')
            ->join('users', 'users.id = product_reviews.user_id', 'left')
            ->where('product_reviews.product_id', $productId)
            ->where('product_reviews.status', 'approved')
            ->where('product_reviews.deleted_at', null)
            ->orderBy('product_reviews.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getReviewSummaryForProduct(string $productId): array
    {
        return [
            'average_rating' => $this->productReviewModel->getAverageRatingForProduct($productId),
            'review_count' => $this->productReviewModel->getReviewCountForProduct($productId),
        ];
    }

    public function createReview(string $userId, string $productId, array $payload): array
    {
        $userId = trim($userId);
        $productId = trim($productId);

        if ($userId === '' || $productId === '') {
            return ['success' => false, 'message' => 'Yorum gonderilemedi.'];
        }

        $product = $this->productsModel
            ->where('id', $productId)
            ->where('deleted_at', null)
            ->first();
        if (! is_array($product)) {
            return ['success' => false, 'message' => 'Urun bulunamadi.'];
        }

        $rating = (int) ($payload['rating'] ?? 0);
        $title = trim((string) ($payload['title'] ?? ''));
        $comment = trim((string) ($payload['comment'] ?? ''));

        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Lutfen 1 ile 5 arasinda bir puan secin.'];
        }

        if (mb_strlen($title, 'UTF-8') > 255) {
            return ['success' => false, 'message' => 'Yorum basligi en fazla 255 karakter olabilir.'];
        }

        if ($title === '' && $comment === '') {
            return ['success' => false, 'message' => 'Yorum basligi veya yorum metni girmeniz gerekir.'];
        }

        if (! $this->reviewEligibilityService->canUserReviewProduct($userId, $productId)) {
            return ['success' => false, 'message' => 'Yorum yapabilmek icin urunu satin almis olmaniz gerekir.'];
        }

        if ($this->hasUserActiveReviewForProduct($userId, $productId)) {
            return ['success' => false, 'message' => 'Bu urun icin daha once yorum gonderdiniz.'];
        }

        $purchaseContext = $this->findEligiblePurchaseContext($userId, $productId);

        $inserted = $this->productReviewModel->insert([
            'product_id' => $productId,
            'user_id' => $userId,
            'order_id' => $purchaseContext['order_id'],
            'order_item_id' => $purchaseContext['order_item_id'],
            'rating' => $rating,
            'title' => $title !== '' ? $title : null,
            'comment' => $comment !== '' ? $comment : null,
            'status' => 'pending',
        ]);

        if ($inserted === false) {
            return ['success' => false, 'message' => 'Yorumunuz kaydedilemedi.'];
        }

        return ['success' => true, 'message' => 'Yorumunuz moderasyon onayindan sonra yayinlanacaktir.'];
    }

    public function hasUserActiveReviewForProduct(string $userId, string $productId): bool
    {
        $userId = trim($userId);
        $productId = trim($productId);
        if ($userId === '' || $productId === '') {
            return false;
        }

        $row = $this->productReviewModel
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('deleted_at', null)
            ->whereIn('status', ['pending', 'approved', 'hidden'])
            ->first();

        return is_array($row);
    }

    public function canUserReviewProduct(string $userId, string $productId): bool
    {
        return $this->reviewEligibilityService->canUserReviewProduct($userId, $productId);
    }

    private function findEligiblePurchaseContext(string $userId, string $productId): array
    {
        $row = db_connect()->table('orders')
            ->select('orders.id AS order_id, order_items.id AS order_item_id')
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
            ->orderBy('COALESCE(orders.order_date, orders.created_at)', 'DESC', false)
            ->limit(1)
            ->get()
            ->getRowArray();

        return [
            'order_id' => is_array($row) ? trim((string) ($row['order_id'] ?? '')) ?: null : null,
            'order_item_id' => is_array($row) ? trim((string) ($row['order_item_id'] ?? '')) ?: null : null,
        ];
    }
}
