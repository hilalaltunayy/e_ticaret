<?php

namespace App\Services;

use App\DTO\ProductDTO;
use App\Models\AuthorModel;
use App\Models\CategoryModel;
use App\Models\ProductsModel;
use App\Models\TypeModel;

class ProductsService
{
    protected ProductsModel $model;

    public function __construct()
    {
        $this->model = new ProductsModel();
    }

    public function getActiveProducts(): array
    {
        $results = $this->model->getActiveProducts();

        return array_map(function ($item) {
            return new ProductDTO($item);
        }, $results);
    }

    public function getProductsByType(string $type): array
    {
        return $this->getFilteredProducts($type, 'all');
    }

    public function getCategoriesByType(string $type): array
    {
        return $this->model->getCategoriesByType($type);
    }

    public function getFilteredProducts(string|array $typeOrFilters, $categoryId = null): array
    {
        if (is_array($typeOrFilters)) {
            return $this->model->filterProducts($typeOrFilters);
        }

        $results = $this->model->getFilteredByTypeAndCategory($typeOrFilters, $categoryId);

        return array_map(fn($item) => new ProductDTO($item), $results);
    }

    public function getAdminListWithCategory(): array
    {
        return $this->model->getAdminListWithCategory();
    }

    public function saveProduct(ProductDTO $dto): bool
    {
        $data = [
            'product_name' => $dto->product_name,
            'author'       => $dto->author,
            'category_id'  => $dto->category_id,
            'description'  => $dto->description,
            'price'        => $dto->price,
            'stock_count'  => $dto->stock,
            'type'         => $dto->type,
            'is_active'    => 1,
        ];

        return (bool) $this->model->insert($data);
    }

    public function updateProduct(ProductDTO $dto): bool
    {
        $data = [
            'product_name' => $dto->product_name,
            'author'       => $dto->author,
            'price'        => $dto->price,
            'stock_count'  => $dto->stock,
            'description'  => $dto->description,
            'category_id'  => $dto->category_id,
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($dto->type !== 'dijital' && $dto->stock <= 0) {
            $data['is_active'] = 0;
            $data['deleted_at'] = date('Y-m-d H:i:s');
        } else {
            $data['is_active'] = 1;
            $data['deleted_at'] = null;
        }

        return $this->model->update($dto->id, $data);
    }

    public function removeFromSale(int $id): bool
    {
        return $this->model->update($id, [
            'is_active'  => 0,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getProductById($id): ?ProductDTO
    {
        $item = $this->model->find($id);

        if (!$item) {
            return null;
        }

        return new ProductDTO($item);
    }

    public function getAdminCategories(): array
    {
        return (new CategoryModel())->getAllForAdmin();
    }

    public function getAdminAuthors(): array
    {
        return (new AuthorModel())->getAllForAdmin();
    }

    public function getLatestAdminAuthors(int $limit = 5): array
    {
        return (new AuthorModel())->getLatestForAdmin($limit);
    }

    public function findOrCreateAuthorByName(string $name): string|int
    {
        return (new AuthorModel())->findOrCreateByName($name);
    }

    public function findOrCreateCategoryByName(string $name): string|int
    {
        return (new CategoryModel())->findOrCreateByName($name);
    }

    public function getAdminTypes(): array
    {
        return (new TypeModel())->getAllForAdmin();
    }

    public function createProduct(array $data): bool|string
    {
        return $this->model->createProduct($data);
    }

    public function getCriticalStockPrinted(int $threshold = 5): array
    {
        return $this->model->getCriticalStockPrintedByAvailable($threshold);
    }

    public function getCriticalStockPrintedByAvailable(int $threshold = 5): array
    {
        return $this->model->getCriticalStockPrintedByAvailable($threshold);
    }

    public function getCategoryCountsPrinted(): array
    {
        return $this->model->getCategoryCountsPrinted();
    }

    public function getPrintedActiveProductsForSelect(): array
    {
        return $this->model->getPrintedActiveProductsForSelect();
    }

    public function getAllActivePrintedProductsForSelect(): array
    {
        return $this->model->getAllActivePrintedProductsForSelect();
    }

    public function getAllActivePrintedWithStatusForList(): array
    {
        return $this->model->getAllActivePrintedWithStatusForList();
    }

    public function getProductForStock(string $id): ?array
    {
        return $this->model->getProductForStock($id);
    }

    public function getProductStockSnapshot(string $productId): array
    {
        return $this->model->getProductStockSnapshot($productId);
    }

    public function getLatestStockMoves(string $productId, int $limit = 20): array
    {
        return $this->model->getLatestStockMoves($productId, $limit);
    }

    public function applyStockMove(string $productId, int $delta, array $meta): bool
    {
        return $this->model->applyStockMove($productId, $delta, $meta);
    }

    public function updateStock(string $id, int $newStock): bool
    {
        return $this->model->updateStock($id, $newStock);
    }

    public function deactivateProduct(string $id): bool
    {
        return $this->model->deactivateProduct($id);
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
        return $this->model->logStockChange($productId, $old, $new, $reason, $note, $actorUserId, $refNo, $relatedOrderId);
    }

    public function createStockMovement(
        string $productId,
        string $reason,
        string $note,
        string $direction,
        int $quantity,
        string $actorUserId,
        ?string $refNo = null,
        ?string $relatedOrderId = null
    ): bool {
        if ($quantity <= 0 || trim($actorUserId) === '' || trim($reason) === '' || trim($note) === '') {
            return false;
        }

        $delta = $direction === '-' ? -$quantity : $quantity;

        return $this->model->applyStockMovement(
            $productId,
            $delta,
            $reason,
            $note,
            $actorUserId,
            $refNo,
            $relatedOrderId
        );
    }

    public function getStockHistoryDaily(string $productId, int $days = 30): array
    {
        return $this->model->getStockHistoryDaily($productId, $days);
    }

    public function datatablesList(array $params): array
    {
        return $this->model->datatablesList($params);
    }

    public function reduceStock(int $productId, int $quantity = 1, ?string $actorUserId = null): bool
    {
        $product = $this->model->find($productId);

        if ($product && $product['stock_count'] >= $quantity) {
            $oldStock = (int) $product['stock_count'];
            $newStock = $oldStock - $quantity;
            $updateData = ['stock_count' => $newStock];

            if ($newStock <= 0 && $product['type'] !== 'dijital') {
                $updateData['is_active'] = 0;
                $updateData['deleted_at'] = date('Y-m-d H:i:s');
            }

            $updated = $this->model->update($productId, $updateData);
            if (!$updated) {
                return false;
            }

            if (trim((string) $actorUserId) === '') {
                return false;
            }

            $this->model->logStockChange((string) $productId, $oldStock, $newStock, 'order_created', '', (string) $actorUserId);

            return true;
        }

        return false;
    }
}
