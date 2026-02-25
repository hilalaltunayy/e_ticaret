<?php

namespace App\Models;

class ProductsModel extends BaseUuidModel
{
    protected $table         = 'products';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id',
        'author_id',
        'type_id',
        'category_id',
        'product_name',
        'author',
        'description',
        'price',
        'stock_count',
        'reserved_count',
        'type',
        'image',
        'is_active',
        'stock',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $deletedField   = 'deleted_at';

    public function getActiveProducts(): array
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function getFilteredByTypeAndCategory(string $type, $categoryId = null): array
    {
        $builder = $this->where('type', $type)->where('is_active', 1);

        if ($categoryId !== null && $categoryId !== 'all') {
            $builder->where('category_id', (int) $categoryId);
        }

        return $builder->findAll();
    }

    public function getCategoriesByType(string $type): array
    {
        return $this->db->table('categories')
            ->select('categories.id, categories.category_name')
            ->join('products', 'products.category_id = categories.id')
            ->where('products.type', $type)
            ->groupBy('categories.id')
            ->get()
            ->getResult();
    }

    public function countPrintedBooks(): int
    {
        return (int) $this->where('type', 'basili')
            ->where('deleted_at', null)
            ->countAllResults();
    }

    public function getAdminListWithCategory(): array
    {
        return $this->select('products.id, products.product_name, products.type, categories.category_name AS category, products.price, products.stock_count, products.is_active')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->orderBy('products.id', 'DESC')
            ->findAll();
    }

    public function filterProducts(array $filters): array
    {
        $builder = $this->select('products.id, products.product_name, products.type, products.author_id, categories.category_name AS category, products.price, products.stock_count, products.is_active')
            ->join('categories', 'categories.id = products.category_id', 'left');

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $builder->like('products.product_name', $q);
        }

        $type = (string) ($filters['type'] ?? '');
        if ($type !== '') {
            $builder->where('products.type', $type);
        }

        $isActive = (string) ($filters['is_active'] ?? '');
        if ($isActive === '0' || $isActive === '1') {
            $builder->where('products.is_active', (int) $isActive);
        }

        $stockRange = (string) ($filters['stock_range'] ?? 'all');
        if ($stockRange === 'low') {
            $builder->where('products.stock_count >=', 0)->where('products.stock_count <=', 5);
        } elseif ($stockRange === 'medium') {
            $builder->where('products.stock_count >', 5)->where('products.stock_count <=', 100);
        } elseif ($stockRange === 'high') {
            $builder->where('products.stock_count >', 100);
        }

        $authorId = trim((string) ($filters['author_id'] ?? ''));
        if ($authorId !== '') {
            $builder->where('products.author_id', $authorId);
        }

        return $builder->orderBy('products.id', 'DESC')->findAll();
    }

    public function createProduct(array $data): bool|string
    {
        $insertId = $this->insert($data, true);
        if ($insertId === false) {
            return false;
        }

        return (string) $insertId;
    }

    public function getCriticalStockPrinted(int $threshold = 5): array
    {
        return $this->select('products.id, products.product_name, categories.category_name, products.stock_count')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where('products.type', 'basili')
            ->where('products.is_active', 1)
            ->where('products.deleted_at', null)
            ->where('products.stock_count <=', $threshold)
            ->orderBy('products.stock_count', 'ASC')
            ->orderBy('products.product_name', 'ASC')
            ->findAll();
    }

    public function getCriticalStockPrintedByAvailable(int $threshold = 5): array
    {
        $threshold = max(0, $threshold);

        return $this->select('products.id, products.product_name, categories.category_name, products.stock_count, products.reserved_count, GREATEST(products.stock_count - products.reserved_count, 0) AS available_stock')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where('products.type', 'basili')
            ->where('products.is_active', 1)
            ->where('products.deleted_at', null)
            ->where('GREATEST(products.stock_count - products.reserved_count, 0) <=', $threshold)
            ->orderBy('available_stock', 'ASC')
            ->orderBy('products.product_name', 'ASC')
            ->findAll();
    }

    public function getCategoryCountsPrinted(): array
    {
        return $this->select('categories.category_name, COUNT(products.id) as count')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where('products.type', 'basili')
            ->where('products.is_active', 1)
            ->where('products.deleted_at', null)
            ->groupBy('categories.id, categories.category_name')
            ->orderBy('count', 'DESC')
            ->orderBy('categories.category_name', 'ASC')
            ->findAll();
    }

    public function getPrintedActiveProductsForSelect(): array
    {
        return $this->select('products.id, products.product_name')
            ->where('products.type', 'basili')
            ->where('products.is_active', 1)
            ->where('products.deleted_at', null)
            ->orderBy('products.product_name', 'ASC')
            ->findAll();
    }

    public function getAllActivePrintedProductsForSelect(): array
    {
        return $this->select('products.id, products.product_name')
            ->where('products.type', 'basili')
            ->where('products.is_active', 1)
            ->where('products.deleted_at', null)
            ->orderBy('products.product_name', 'ASC')
            ->findAll();
    }

    public function getAllActivePrintedWithStatusForList(): array
    {
        return $this->select('products.id, products.product_name, categories.category_name, products.stock_count, products.reserved_count, GREATEST(products.stock_count - products.reserved_count, 0) AS salable')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where('products.type', 'basili')
            ->where('products.is_active', 1)
            ->where('products.deleted_at', null)
            ->orderBy('products.product_name', 'ASC')
            ->findAll();
    }

    public function getProductForStock(string $id): ?array
    {
        $row = $this->select('products.id, products.stock_count, products.reserved_count, GREATEST(products.stock_count - products.reserved_count, 0) AS available_stock')
            ->where('products.id', $id)
            ->where('products.deleted_at', null)
            ->first();

        return $row ?: null;
    }

    public function getProductStockSnapshot(string $productId): array
    {
        return $this->db->table('products')
            ->select('products.id, products.product_name, products.stock_count, products.reserved_count, GREATEST(products.stock_count - products.reserved_count, 0) AS sellable, categories.category_name')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->where('products.id', $productId)
            ->where('products.deleted_at', null)
            ->limit(1)
            ->get()
            ->getRowArray() ?? [];
    }

    public function getLatestStockMoves(string $productId, int $limit = 20): array
    {
        $limit = max(1, $limit);

        return $this->db->table('product_stock_logs l')
            ->select('l.created_at, l.delta, l.reason, l.note, l.related_order_id, l.ref_no, l.actor_user_id, u.username AS actor_name, u.email AS actor_email')
            ->join('users u', 'u.id = l.actor_user_id', 'left')
            ->where('l.product_id', $productId)
            ->orderBy('l.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function updateStock(string $id, int $newStock): bool
    {
        return $this->update($id, [
            'stock_count' => $newStock,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getProductForOrder(string $id): ?array
    {
        $row = $this->select('products.id, products.price, products.type, products.is_active, products.stock_count, products.reserved_count, GREATEST(products.stock_count - products.reserved_count, 0) AS available_stock')
            ->where('products.id', $id)
            ->where('products.deleted_at', null)
            ->first();

        return $row ?: null;
    }

    public function deactivateProduct(string $id): bool
    {
        return $this->update($id, [
            'is_active' => 0,
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function logStockChange(
        string $productId,
        int $old,
        int $new,
        string $reason,
        string $note = '',
        ?string $actorUserId = null,
        ?string $refNo = null,
        ?string $relatedOrderId = null
    ): bool
    {
        if ($actorUserId === null || trim($actorUserId) === '') {
            return false;
        }

        return (bool) $this->db->table('product_stock_logs')->insert([
            'id' => BaseUuidModel::uuidV4(),
            'product_id' => $productId,
            'old_stock' => $old,
            'new_stock' => $new,
            'change_amount' => $new - $old,
            'delta' => $new - $old,
            'reason' => $reason,
            'note' => $note,
            'actor_user_id' => $actorUserId,
            'ref_no' => $refNo,
            'related_order_id' => $relatedOrderId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function applyStockMovement(
        string $productId,
        int $delta,
        string $reason,
        string $note,
        string $actorUserId,
        ?string $refNo = null,
        ?string $relatedOrderId = null
    ): bool {
        if ($delta === 0 || trim($note) === '' || trim($reason) === '' || trim($actorUserId) === '') {
            return false;
        }

        $product = $this->getProductForStock($productId);
        if (!$product) {
            return false;
        }

        $oldStock = (int) ($product['stock_count'] ?? 0);
        $newStock = $oldStock + $delta;
        if ($newStock < 0) {
            return false;
        }

        $this->db->transStart();

        $updated = $this->update($productId, [
            'stock_count' => $newStock,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->db->transRollback();
            return false;
        }

        $logged = $this->logStockChange(
            $productId,
            $oldStock,
            $newStock,
            $reason,
            $note,
            $actorUserId,
            $refNo,
            $relatedOrderId
        );

        if (!$logged) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function applyStockMove(string $productId, int $delta, array $meta): bool
    {
        $reason = trim((string) ($meta['reason'] ?? ''));
        $note = trim((string) ($meta['note'] ?? ''));
        $actorUserId = trim((string) ($meta['actor_user_id'] ?? ''));
        $relatedOrderId = isset($meta['related_order_id']) ? trim((string) $meta['related_order_id']) : null;
        $refNo = isset($meta['ref_no']) ? trim((string) $meta['ref_no']) : null;

        if ($delta === 0 || $reason === '' || $note === '' || $actorUserId === '') {
            return false;
        }

        $snapshot = $this->getProductStockSnapshot($productId);
        if (empty($snapshot)) {
            return false;
        }

        $oldStock = (int) ($snapshot['stock_count'] ?? 0);
        $sellable = (int) ($snapshot['sellable'] ?? 0);

        if ($delta < 0 && abs($delta) > $sellable) {
            return false;
        }

        $newStock = $oldStock + $delta;
        if ($newStock < 0) {
            return false;
        }

        $this->db->transStart();

        $updated = $this->update($productId, [
            'stock_count' => $newStock,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if (! $updated) {
            $this->db->transRollback();
            return false;
        }

        $inserted = (bool) $this->db->table('product_stock_logs')->insert([
            'id' => BaseUuidModel::uuidV4(),
            'product_id' => $productId,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'change_amount' => $delta,
            'delta' => $delta,
            'reason' => $reason,
            'note' => $note,
            'actor_user_id' => $actorUserId,
            'related_order_id' => $relatedOrderId !== '' ? $relatedOrderId : null,
            'ref_no' => $refNo !== '' ? $refNo : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (! $inserted) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function reserveStockForOrder(string $productId, int $qty, string $orderId, ?string $actorUserId = null): bool
    {
        if ($qty <= 0 || trim($orderId) === '' || trim((string) $actorUserId) === '') {
            return false;
        }

        $product = $this->getProductForStock($productId);
        if (!$product) {
            return false;
        }

        $stock = (int) ($product['stock_count'] ?? 0);
        $reserved = (int) ($product['reserved_count'] ?? 0);
        $available = max(0, $stock - $reserved);
        if ($available < $qty) {
            return false;
        }

        $this->db->transStart();

        $updated = $this->update($productId, [
            'reserved_count' => $reserved + $qty,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->db->transRollback();
            return false;
        }

        $logged = $this->logStockChange(
            $productId,
            $stock,
            $stock,
            'order_reserved',
            'SipariÅŸ rezervasyonu: ' . $qty,
            (string) $actorUserId,
            null,
            $orderId
        );

        if (!$logged) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function releaseReservedForCancel(string $productId, int $qty, string $orderId, ?string $actorUserId = null): bool
    {
        if ($qty <= 0 || trim($orderId) === '' || trim((string) $actorUserId) === '') {
            return false;
        }

        $product = $this->getProductForStock($productId);
        if (!$product) {
            return false;
        }

        $stock = (int) ($product['stock_count'] ?? 0);
        $reserved = (int) ($product['reserved_count'] ?? 0);
        if ($reserved < $qty) {
            return false;
        }

        $this->db->transStart();

        $updated = $this->update($productId, [
            'reserved_count' => $reserved - $qty,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->db->transRollback();
            return false;
        }

        $logged = $this->logStockChange(
            $productId,
            $stock,
            $stock,
            'order_cancelled',
            'SipariÅŸ iptali rezervasyon Ã§Ã¶zÃ¼mÃ¼: ' . $qty,
            (string) $actorUserId,
            null,
            $orderId
        );

        if (!$logged) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function shipReservedToSold(string $productId, int $qty, string $orderId, ?string $actorUserId = null): bool
    {
        if ($qty <= 0 || trim($orderId) === '' || trim((string) $actorUserId) === '') {
            return false;
        }

        $product = $this->getProductForStock($productId);
        if (!$product) {
            return false;
        }

        $oldStock = (int) ($product['stock_count'] ?? 0);
        $reserved = (int) ($product['reserved_count'] ?? 0);
        if ($reserved < $qty || $oldStock < $qty) {
            return false;
        }

        $newStock = $oldStock - $qty;
        $newReserved = $reserved - $qty;

        $this->db->transStart();

        $updated = $this->update($productId, [
            'stock_count' => $newStock,
            'reserved_count' => $newReserved,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$updated) {
            $this->db->transRollback();
            return false;
        }

        $logged = $this->logStockChange(
            $productId,
            $oldStock,
            $newStock,
            'order_shipped',
            'SipariÅŸ kargoya verildi: ' . $qty,
            (string) $actorUserId,
            null,
            $orderId
        );

        if (!$logged) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();

        return (bool) $this->db->transStatus();
    }

    public function getStockHistoryDaily(string $productId, int $days = 30): array
    {
        $days = max(1, $days);
        $start = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
        $end = date('Y-m-d 23:59:59');

        $rows = $this->db->table('product_stock_logs')
            ->select('DATE(created_at) as d, new_stock, created_at')
            ->where('product_id', $productId)
            ->where('created_at >=', $start)
            ->where('created_at <=', $end)
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        if (!$rows) {
            return [];
        }

        $dailyLatest = [];
        foreach ($rows as $row) {
            $day = (string) ($row['d'] ?? '');
            if ($day === '') {
                continue;
            }
            $dailyLatest[$day] = (int) ($row['new_stock'] ?? 0);
        }

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = [
                'd' => $day,
                'stock' => array_key_exists($day, $dailyLatest) ? $dailyLatest[$day] : null,
            ];
        }

        return $result;
    }

    public function datatablesList(array $params): array
    {
        $baseBuilder = $this->db->table('products')
            ->select('products.id, products.product_name AS title, products.type, products.price, products.stock_count AS stock_total, products.reserved_count AS stock_reserved, GREATEST(products.stock_count - products.reserved_count, 0) AS stock_available, products.is_active, categories.category_name, authors.name AS author_name')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->join('authors', 'authors.id = products.author_id', 'left')
            ->where('products.deleted_at', null);

        $recordsTotal = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = clone $baseBuilder;
        $this->applyDatatablesFilters($filteredBuilder, $params);
        $recordsFiltered = (clone $filteredBuilder)->countAllResults();

        $columnDbMap = [
            'id' => 'products.id',
            'title' => 'products.product_name',
            'author_name' => 'authors.name',
            'type' => 'products.type',
            'category_name' => 'categories.category_name',
            'price' => 'products.price',
            'stock_total' => 'products.stock_count',
            'stock_reserved' => 'products.reserved_count',
            'stock_available' => 'GREATEST(products.stock_count - products.reserved_count, 0)',
            'is_active' => 'products.is_active',
        ];

        $orderIndex = (int) ($params['order'][0]['column'] ?? 0);
        $orderDirRaw = strtolower((string) ($params['order'][0]['dir'] ?? 'asc'));
        $orderDir = $orderDirRaw === 'desc' ? 'DESC' : 'ASC';

        $columnName = (string) ($params['columns'][$orderIndex]['data'] ?? 'title');
        $orderColumn = $columnDbMap[$columnName] ?? 'products.product_name';
        $filteredBuilder->orderBy($orderColumn, $orderDir);

        $length = (int) ($params['length'] ?? 10);
        $start = max(0, (int) ($params['start'] ?? 0));
        if ($length !== -1) {
            $length = max(1, min(100, $length));
            $filteredBuilder->limit($length, $start);
        }

        $rows = $filteredBuilder->get()->getResultArray();

        return [
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $rows,
        ];
    }

    private function applyDatatablesFilters(\CodeIgniter\Database\BaseBuilder $builder, array $params): void
    {
        $searchTerm = trim((string) ($params['search']['value'] ?? ''));
        if ($searchTerm !== '') {
            $builder->groupStart()
                ->like('products.product_name', $searchTerm)
                ->orLike('authors.name', $searchTerm)
                ->orLike('categories.category_name', $searchTerm)
                ->orLike('products.type', $searchTerm)
                ->groupEnd();
        }
    }
}
