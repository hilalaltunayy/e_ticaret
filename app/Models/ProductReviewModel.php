<?php

namespace App\Models;

class ProductReviewModel extends BaseUuidModel
{
    protected $table         = 'product_reviews';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id',
        'product_id',
        'user_id',
        'order_id',
        'order_item_id',
        'rating',
        'title',
        'comment',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $deletedField   = 'deleted_at';

    public function findApprovedByProduct(string $productId): array
    {
        return $this->where('product_id', trim($productId))
            ->where('status', 'approved')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getAverageRatingForProduct(string $productId): ?float
    {
        $row = $this->builder()
            ->select('AVG(rating) AS average_rating')
            ->where('product_id', trim($productId))
            ->where('status', 'approved')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        $value = $row['average_rating'] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 1);
    }

    public function getReviewCountForProduct(string $productId): int
    {
        return (int) $this->builder()
            ->where('product_id', trim($productId))
            ->where('status', 'approved')
            ->where('deleted_at', null)
            ->countAllResults();
    }
}
