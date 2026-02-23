<?php

namespace App\Models;

class OrderModel extends BaseUuidModel
{
    protected $table         = 'orders';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id',
        'product_id',
        'quantity',
        'total_price',
        'total_amount',
        'customer_name',
        'status',
        'reserved_at',
        'shipped_at',
        'cancelled_at',
        'returned_at',
        'order_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $deletedField   = 'deleted_at';

    public function countAllOrders(): int
    {
        return (int) $this->builder()->countAllResults();
    }

    public function countOrdersBetween(string $start, string $end): int
    {
        return (int) $this->builder()
            ->where('order_date >=', $start)
            ->where('order_date <=', $end)
            ->countAllResults();
    }

    public function countOrdersByStatus(string $status): int
    {
        return (int) $this->builder()
            ->where('status', $status)
            ->countAllResults();
    }

    public function getLatestWithProductName(int $limit = 5): array
    {
        return $this->db->table('orders o')
            ->select('o.id, o.customer_name, o.total_amount, o.status, o.order_date, p.product_name')
            ->join('products p', 'p.id = o.product_id', 'left')
            ->orderBy('o.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getDailyCounts(string $start, string $end): array
    {
        return $this->builder()
            ->select('DATE(order_date) as d, COUNT(*) as c')
            ->where('order_date >=', $start)
            ->where('order_date <=', $end)
            ->groupBy('DATE(order_date)')
            ->orderBy('d', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getRevenueSumRow(string $start, string $end): array
    {
        return $this->builder()
            ->select('COALESCE(SUM(total_amount),0) as total')
            ->where('order_date >=', $start)
            ->where('order_date <=', $end)
            ->where('status !=', 'cancelled')
            ->get()
            ->getRowArray() ?? [];
    }

    public function getTopCategoriesByQuantity(int $limit = 6): array
    {
        return $this->db->table('orders o')
            ->select('c.category_name as category_name, SUM(o.quantity) as qty')
            ->join('products p', 'p.id = o.product_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->groupBy('c.category_name')
            ->orderBy('qty', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getTopAuthorsByQuantity(int $limit = 10): array
    {
        return $this->db->table('orders o')
            ->select('a.name as author_name, SUM(o.quantity) as qty')
            ->join('products p', 'p.id = o.product_id', 'left')
            ->join('authors a', 'a.id = p.author_id', 'left')
            ->groupBy('a.name')
            ->orderBy('qty', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getTopDigitalBooksByQuantity(int $limit = 10): array
    {
        return $this->db->table('orders o')
            ->select('p.product_name as title, SUM(o.quantity) as qty')
            ->join('products p', 'p.id = o.product_id', 'left')
            ->where('p.type', 'digital')
            ->groupBy('p.product_name')
            ->orderBy('qty', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function createOrderReserved(
        string $productId,
        int $quantity,
        float $totalAmount,
        string $actorUserId,
        ?string $customerName = null
    ): string|false {
        if ($quantity <= 0 || trim($productId) === '' || trim($actorUserId) === '') {
            return false;
        }

        $productsModel = new ProductsModel();
        $orderId = self::uuidV4();
        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $reserved = $productsModel->reserveStockForOrder($productId, $quantity, $orderId, $actorUserId);
        if (!$reserved) {
            $this->db->transRollback();
            return false;
        }

        $inserted = $this->insert([
            'id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'total_price' => $totalAmount,
            'total_amount' => $totalAmount,
            'customer_name' => $customerName,
            'status' => 'reserved',
            'reserved_at' => $now,
            'order_date' => $now,
        ], false);

        if (!$inserted) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $orderId : false;
    }

    public function markShipped(string $orderId, string $actorUserId): bool
    {
        $order = $this->where('id', $orderId)->where('deleted_at', null)->first();
        if (!$order) {
            return false;
        }

        $status = (string) ($order['status'] ?? '');
        if (!in_array($status, ['reserved', 'paid'], true)) {
            return false;
        }

        $productsModel = new ProductsModel();
        $qty = (int) ($order['quantity'] ?? 0);
        $productId = (string) ($order['product_id'] ?? '');
        if ($qty <= 0 || $productId === '' || trim($actorUserId) === '') {
            return false;
        }

        $this->db->transStart();

        $moved = $productsModel->shipReservedToSold($productId, $qty, $orderId, $actorUserId);
        if (!$moved) {
            $this->db->transRollback();
            return false;
        }

        $updated = $this->update($orderId, [
            'status' => 'shipped',
            'shipped_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function cancelOrder(string $orderId, string $actorUserId): bool
    {
        $order = $this->where('id', $orderId)->where('deleted_at', null)->first();
        if (!$order) {
            return false;
        }

        $status = (string) ($order['status'] ?? '');
        if (!in_array($status, ['reserved', 'paid'], true)) {
            return false;
        }

        $productsModel = new ProductsModel();
        $qty = (int) ($order['quantity'] ?? 0);
        $productId = (string) ($order['product_id'] ?? '');
        if ($qty <= 0 || $productId === '' || trim($actorUserId) === '') {
            return false;
        }

        $this->db->transStart();

        $released = $productsModel->releaseReservedForCancel($productId, $qty, $orderId, $actorUserId);
        if (!$released) {
            $this->db->transRollback();
            return false;
        }

        $updated = $this->update($orderId, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function returnOrder(string $orderId, string $actorUserId): bool
    {
        $order = $this->where('id', $orderId)->where('deleted_at', null)->first();
        if (!$order) {
            return false;
        }

        $status = (string) ($order['status'] ?? '');
        if ($status !== 'shipped') {
            return false;
        }

        $productsModel = new ProductsModel();
        $qty = (int) ($order['quantity'] ?? 0);
        $productId = (string) ($order['product_id'] ?? '');
        if ($qty <= 0 || $productId === '' || trim($actorUserId) === '') {
            return false;
        }

        $this->db->transStart();

        $moved = $productsModel->applyStockMovement(
            $productId,
            $qty,
            'iade_girisi',
            'Sipariş iadesi',
            $actorUserId,
            null,
            $orderId
        );

        if (!$moved) {
            $this->db->transRollback();
            return false;
        }

        $updated = $this->update($orderId, [
            'status' => 'returned',
            'returned_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }
}
