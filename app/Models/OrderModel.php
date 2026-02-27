<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;

class OrderModel extends BaseUuidModel
{
    protected $table         = 'orders';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id',
        'order_no',
        'user_id',
        'product_id',
        'quantity',
        'total_price',
        'total_amount',
        'customer_name',
        'payment_method',
        'payment_status',
        'status',
        'order_status',
        'shipping_status',
        'shipping_company',
        'tracking_number',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_district',
        'shipping_postal_code',
        'shipping_country',
        'reserved_at',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'returned_at',
        'return_started_at',
        'return_completed_at',
        'notes_admin',
        'updated_by',
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
            ->groupStart()
            ->where('status', $status)
            ->orWhere('order_status', $status)
            ->groupEnd()
            ->countAllResults();
    }

    public function getLatestWithProductName(int $limit = 5): array
    {
        return $this->db->table('orders o')
            ->select('o.id, o.order_no, o.customer_name, o.total_amount, o.status, o.order_status, o.order_date, o.created_at, p.product_name')
            ->join('products p', 'p.id = o.product_id', 'left')
            ->orderBy('o.created_at', 'DESC')
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
        if (! $reserved) {
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
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'shipping_status' => 'not_shipped',
            'reserved_at' => $now,
            'order_date' => $now,
            'updated_by' => $actorUserId,
        ], false);

        if (! $inserted) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $orderId : false;
    }

    public function markShipped(string $orderId, string $actorUserId): bool
    {
        $order = $this->where('id', $orderId)->where('deleted_at', null)->first();
        if (! $order) {
            return false;
        }

        $status = (string) ($order['status'] ?? '');
        if (! in_array($status, ['reserved', 'paid'], true)) {
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
        if (! $moved) {
            $this->db->transRollback();
            return false;
        }

        $updated = $this->update($orderId, [
            'status' => 'shipped',
            'order_status' => 'shipped',
            'shipping_status' => 'shipped',
            'shipped_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actorUserId,
        ]);

        if (! $updated) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function cancelOrder(string $orderId, string $actorUserId): bool
    {
        $order = $this->where('id', $orderId)->where('deleted_at', null)->first();
        if (! $order) {
            return false;
        }

        $status = (string) ($order['status'] ?? '');
        if (! in_array($status, ['reserved', 'paid'], true)) {
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
        if (! $released) {
            $this->db->transRollback();
            return false;
        }

        $updated = $this->update($orderId, [
            'status' => 'cancelled',
            'order_status' => 'cancelled',
            'shipping_status' => 'not_shipped',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actorUserId,
        ]);

        if (! $updated) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function returnOrder(string $orderId, string $actorUserId): bool
    {
        $order = $this->where('id', $orderId)->where('deleted_at', null)->first();
        if (! $order) {
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
            'Siparis iadesi',
            $actorUserId,
            null,
            $orderId
        );

        if (! $moved) {
            $this->db->transRollback();
            return false;
        }

        $updated = $this->update($orderId, [
            'status' => 'returned',
            'order_status' => 'return_done',
            'shipping_status' => 'returned',
            'returned_at' => date('Y-m-d H:i:s'),
            'return_completed_at' => date('Y-m-d H:i:s'),
            'updated_by' => $actorUserId,
        ]);

        if (! $updated) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function datatablesList(array $params): array
    {
        $baseBuilder = $this->db->table('orders o')
            ->select("
                o.id,
                o.order_no,
                o.customer_name,
                o.total_amount,
                o.created_at,
                o.order_date,
                o.payment_method,
                o.payment_status,
                o.order_status,
                o.shipping_status,
                o.shipping_company,
                o.tracking_number,
                o.status,
                COALESCE(NULLIF(o.customer_name, ''), u.username, u.email, '-') AS customer_display
            ")
            ->join('users u', 'u.id = o.user_id', 'left')
            ->where('o.deleted_at', null);

        $recordsTotal = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = clone $baseBuilder;
        $this->applyDatatablesFilters($filteredBuilder, $params);
        $recordsFiltered = (clone $filteredBuilder)->countAllResults();

        $columnDbMap = [
            'order_no' => 'o.order_no',
            'customer' => 'customer_display',
            'date' => 'o.created_at',
            'total_amount' => 'o.total_amount',
            'payment_status' => 'o.payment_status',
            'order_status' => 'o.order_status',
            'shipping_status' => 'o.shipping_status',
        ];

        $orderIndex = (int) ($params['order'][0]['column'] ?? 2);
        $orderDirRaw = strtolower((string) ($params['order'][0]['dir'] ?? 'desc'));
        $orderDir = $orderDirRaw === 'asc' ? 'ASC' : 'DESC';

        $columnName = (string) ($params['columns'][$orderIndex]['data'] ?? 'date');
        $orderColumn = $columnDbMap[$columnName] ?? 'o.created_at';
        $filteredBuilder->orderBy($orderColumn, $orderDir);

        $length = (int) ($params['length'] ?? 10);
        $start = max(0, (int) ($params['start'] ?? 0));
        if ($length !== -1) {
            $length = max(1, min(200, $length));
            $filteredBuilder->limit($length, $start);
        }

        $rows = $filteredBuilder->get()->getResultArray();

        return [
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $rows,
        ];
    }

    public function findByIdOrOrderNo(string $identifier): ?array
    {
        $id = trim($identifier);
        if ($id === '') {
            return null;
        }

        return $this->db->table('orders o')
            ->select("
                o.*,
                p.product_name,
                p.price AS product_price,
                u.username AS user_name,
                u.email AS user_email
            ")
            ->join('products p', 'p.id = o.product_id', 'left')
            ->join('users u', 'u.id = o.user_id', 'left')
            ->where('o.deleted_at', null)
            ->groupStart()
            ->where('o.id', $id)
            ->orWhere('o.order_no', $id)
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    public function getOrderItems(string $orderId): array
    {
        if (! $this->db->tableExists('order_items')) {
            return [];
        }

        return $this->db->table('order_items')
            ->where('order_id', $orderId)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getOrderLogs(string $orderId, int $limit = 200): array
    {
        if (! $this->db->tableExists('order_logs')) {
            return [];
        }

        return $this->db->table('order_logs l')
            ->select('l.*, u.username, u.email')
            ->join('users u', 'u.id = l.actor_user_id', 'left')
            ->where('l.order_id', $orderId)
            ->orderBy('l.created_at', 'DESC')
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();
    }

    private function applyDatatablesFilters(BaseBuilder $builder, array $params): void
    {
        $searchTerm = trim((string) ($params['search']['value'] ?? ''));
        if ($searchTerm !== '') {
            $builder->groupStart()
                ->like('o.order_no', $searchTerm)
                ->orLike('o.id', $searchTerm)
                ->orLike('o.customer_name', $searchTerm)
                ->orLike('o.tracking_number', $searchTerm)
                ->orLike('u.username', $searchTerm)
                ->orLike('u.email', $searchTerm)
                ->groupEnd();
        }

        $orderNo = trim((string) ($params['filter_order_no'] ?? ''));
        if ($orderNo !== '') {
            $builder->groupStart()
                ->like('o.order_no', $orderNo)
                ->orLike('o.id', $orderNo)
                ->groupEnd();
        }

        $customer = trim((string) ($params['filter_customer'] ?? ''));
        if ($customer !== '') {
            $builder->groupStart()
                ->like('o.customer_name', $customer)
                ->orLike('u.username', $customer)
                ->orLike('u.email', $customer)
                ->groupEnd();
        }

        $dateStart = trim((string) ($params['filter_date_start'] ?? ''));
        if ($dateStart !== '') {
            $builder->where('o.created_at >=', $dateStart . ' 00:00:00');
        }

        $dateEnd = trim((string) ($params['filter_date_end'] ?? ''));
        if ($dateEnd !== '') {
            $builder->where('o.created_at <=', $dateEnd . ' 23:59:59');
        }

        $orderStatus = trim((string) ($params['filter_order_status'] ?? ''));
        if ($orderStatus !== '') {
            $builder->where('o.order_status', $orderStatus);
        }

        $orderStatusesRaw = trim((string) ($params['filter_order_statuses'] ?? ''));
        if ($orderStatusesRaw !== '') {
            $statuses = array_values(array_filter(array_map('trim', explode(',', $orderStatusesRaw))));
            if ($statuses !== []) {
                $builder->whereIn('o.order_status', $statuses);
            }
        }

        $paymentMethod = trim((string) ($params['filter_payment_method'] ?? ''));
        if ($paymentMethod !== '') {
            $builder->where('o.payment_method', $paymentMethod);
        }

        $paymentStatus = trim((string) ($params['filter_payment_status'] ?? ''));
        if ($paymentStatus !== '') {
            $builder->where('o.payment_status', $paymentStatus);
        }

        $shippingCompany = trim((string) ($params['filter_shipping_company'] ?? ''));
        if ($shippingCompany !== '') {
            $builder->like('o.shipping_company', $shippingCompany);
        }
    }
}
