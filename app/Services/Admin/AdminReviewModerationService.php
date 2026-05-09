<?php

namespace App\Services\Admin;

use App\Models\ProductReviewModel;

class AdminReviewModerationService
{
    private ProductReviewModel $productReviewModel;

    public function __construct()
    {
        $this->productReviewModel = new ProductReviewModel();
    }

    public function getReviewListing(array $filters = []): array
    {
        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        $search = trim((string) ($filters['q'] ?? ''));

        $builder = db_connect()->table('product_reviews pr')
            ->select('
                pr.id,
                pr.product_id,
                pr.user_id,
                pr.rating,
                pr.title,
                pr.comment,
                pr.status,
                pr.created_at,
                p.product_name,
                u.username,
                u.email
            ')
            ->join('products p', 'p.id = pr.product_id', 'left')
            ->join('users u', 'u.id = pr.user_id', 'left')
            ->where('pr.deleted_at', null);

        if (in_array($status, ['pending', 'approved', 'hidden', 'rejected'], true)) {
            $builder->where('pr.status', $status);
        } else {
            $status = '';
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('p.product_name', $search)
                ->orLike('u.username', $search)
                ->orLike('u.email', $search)
                ->orLike('pr.title', $search)
                ->orLike('pr.comment', $search)
                ->groupEnd();
        }

        $rows = $builder
            ->orderBy('pr.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $summary = [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'hidden' => 0,
            'rejected' => 0,
        ];

        $items = [];
        foreach ($rows as $row) {
            $normalizedStatus = strtolower(trim((string) ($row['status'] ?? 'pending')));
            if (! isset($summary[$normalizedStatus])) {
                $normalizedStatus = 'pending';
            }

            $summary['total']++;
            $summary[$normalizedStatus]++;

            $reviewId = trim((string) ($row['id'] ?? ''));
            $displayTitle = trim((string) ($row['title'] ?? ''));
            $displayComment = trim((string) ($row['comment'] ?? ''));
            $customerName = trim((string) ($row['username'] ?? ''));
            $customerEmail = trim((string) ($row['email'] ?? ''));

            $items[] = [
                'id' => $reviewId,
                'short_id' => $reviewId !== '' ? strtoupper(substr(str_replace('-', '', $reviewId), 0, 8)) : '-',
                'product_name' => trim((string) ($row['product_name'] ?? '')) !== '' ? (string) $row['product_name'] : 'Ürün silinmiş olabilir',
                'customer' => $customerName !== '' ? $customerName : ($customerEmail !== '' ? $customerEmail : '-'),
                'customer_email' => $customerEmail,
                'rating' => (int) ($row['rating'] ?? 0),
                'title' => $displayTitle,
                'comment' => $displayComment,
                'status' => $normalizedStatus,
                'status_label' => $this->mapStatusLabel($normalizedStatus),
                'status_badge_class' => $this->mapStatusBadgeClass($normalizedStatus),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'can_approve' => in_array($normalizedStatus, ['pending', 'hidden', 'rejected'], true),
                'can_hide' => in_array($normalizedStatus, ['pending', 'approved'], true),
                'can_reject' => $normalizedStatus === 'pending',
            ];
        }

        return [
            'summary' => $summary,
            'items' => $items,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
        ];
    }

    public function approve(string $reviewId): array
    {
        return $this->changeStatus($reviewId, 'approved');
    }

    public function hide(string $reviewId): array
    {
        return $this->changeStatus($reviewId, 'hidden');
    }

    public function reject(string $reviewId): array
    {
        return $this->changeStatus($reviewId, 'rejected');
    }

    public function deleteReview(string $reviewId): array
    {
        $review = $this->findReview($reviewId);
        if (! $review) {
            return ['success' => false, 'message' => 'Yorum bulunamadı.'];
        }

        $deleted = $this->productReviewModel->delete((string) $review['id']);
        if (! $deleted) {
            return ['success' => false, 'message' => 'Yorum silinemedi.'];
        }

        return ['success' => true, 'message' => 'Yorum gizli silme ile kaldırıldı.'];
    }

    private function changeStatus(string $reviewId, string $targetStatus): array
    {
        $review = $this->findReview($reviewId);
        if (! $review) {
            return ['success' => false, 'message' => 'Yorum bulunamadı.'];
        }

        $currentStatus = strtolower(trim((string) ($review['status'] ?? 'pending')));
        if (! $this->isTransitionAllowed($currentStatus, $targetStatus)) {
            return ['success' => false, 'message' => 'Bu yorum için istenen durum geçişi geçerli değil.'];
        }

        if ($currentStatus === $targetStatus) {
            return ['success' => true, 'message' => 'Yorum durumu zaten güncel.'];
        }

        $updated = $this->productReviewModel->update((string) $review['id'], [
            'status' => $targetStatus,
        ]);

        if (! $updated) {
            return ['success' => false, 'message' => 'Yorum durumu güncellenemedi.'];
        }

        return [
            'success' => true,
            'message' => match ($targetStatus) {
                'approved' => 'Yorum yayına alındı.',
                'hidden' => 'Yorum gizlendi.',
                'rejected' => 'Yorum reddedildi.',
                default => 'Yorum durumu güncellendi.',
            },
        ];
    }

    private function findReview(string $reviewId): ?array
    {
        $reviewId = trim($reviewId);
        if ($reviewId === '') {
            return null;
        }

        $row = $this->productReviewModel
            ->where('id', $reviewId)
            ->where('deleted_at', null)
            ->first();

        return is_array($row) ? $row : null;
    }

    private function isTransitionAllowed(string $currentStatus, string $targetStatus): bool
    {
        $map = [
            'pending' => ['approved', 'hidden', 'rejected'],
            'approved' => ['hidden'],
            'hidden' => ['approved'],
            'rejected' => ['approved'],
        ];

        return in_array($targetStatus, $map[$currentStatus] ?? [], true);
    }

    private function mapStatusLabel(string $status): string
    {
        return match ($status) {
            'approved' => 'Yayında',
            'hidden' => 'Gizli',
            'rejected' => 'Reddedildi',
            default => 'Onay Bekliyor',
        };
    }

    private function mapStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'approved' => 'bg-light-success text-success',
            'hidden' => 'bg-light-secondary text-secondary',
            'rejected' => 'bg-light-danger text-danger',
            default => 'bg-light-warning text-warning',
        };
    }
}
