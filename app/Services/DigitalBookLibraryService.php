<?php

namespace App\Services;

class DigitalBookLibraryService
{
    private const DIGITAL_TYPES = ['dijital', 'digital', 'ebook', 'e-book'];
    private const ALLOW_PAYMENT_STATUSES = ['paid'];
    private const ALLOW_ORDER_STATUSES = ['delivered'];
    private const ALLOW_LEGACY_STATUSES = ['completed', 'delivered', 'paid'];
    private const DENY_PAYMENT_STATUSES = ['unpaid', 'failed', 'refunded', 'partial_refund'];
    private const DENY_ORDER_STATUSES = ['pending', 'cancelled', 'return_in_progress', 'return_done', 'returned', 'refunded'];
    // `orders.status=reserved` bu projede "siparis ayrildi/hazirlaniyor" durumunda da kullaniliyor.
    // Bu nedenle paid siparisleri yanlislikla elememek icin reserved deny listesinden cikarildi.
    private const DENY_LEGACY_STATUSES = ['pending', 'cancelled', 'returned', 'refunded'];

    public function userOwnsDigitalBook(string $userId, string $productId): bool
    {
        $decision = $this->getDigitalBookAccessDecision($userId, $productId);

        return (bool) ($decision['allowed'] ?? false);
    }

    public function ownsDigitalBook(string $userId, string $productId): bool
    {
        return $this->userOwnsDigitalBook($userId, $productId);
    }

    public function getDigitalBookAccessDecision(string $userId, string $productId): array
    {
        $userId = trim($userId);
        $productId = trim($productId);
        if ($userId === '' || $productId === '') {
            return ['allowed' => false, 'reason' => 'invalid_input'];
        }

        $db = db_connect();
        if (
            ! $db->tableExists('orders')
            || ! $db->tableExists('order_items')
            || ! $db->tableExists('products')
        ) {
            return ['allowed' => false, 'reason' => 'schema_missing'];
        }

        $product = $db->table('products')
            ->select('id, type')
            ->where('id', $productId)
            ->where('deleted_at', null)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (! is_array($product)) {
            return ['allowed' => false, 'reason' => 'not_found'];
        }

        $productType = strtolower(trim((string) ($product['type'] ?? '')));
        if (! in_array($productType, self::DIGITAL_TYPES, true)) {
            return ['allowed' => false, 'reason' => 'not_digital'];
        }

        $ownedRow = $db->table('orders')
            ->select('orders.id')
            ->join('order_items', 'order_items.order_id = orders.id', 'inner')
            ->join('products', 'products.id = order_items.product_id', 'inner')
            ->where('orders.user_id', $userId)
            ->where('orders.deleted_at', null)
            ->where('order_items.deleted_at', null)
            ->where('products.deleted_at', null)
            ->where('order_items.product_id', $productId)
            ->groupStart()
                ->whereIn('LOWER(TRIM(COALESCE(products.type, \'\')))', self::DIGITAL_TYPES)
                ->orWhereIn('LOWER(TRIM(COALESCE(order_items.product_type, \'\')))', self::DIGITAL_TYPES)
            ->groupEnd()
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

        if (! is_array($ownedRow)) {
            return ['allowed' => false, 'reason' => 'not_owned'];
        }

        return ['allowed' => true, 'reason' => 'ok'];
    }

    public function getOwnedDigitalBooksForUser(string $userId): array
    {
        $userId = trim($userId);
        if ($userId === '') {
            return [];
        }

        $db = db_connect();
        if (
            ! $db->tableExists('orders')
            || ! $db->tableExists('order_items')
            || ! $db->tableExists('products')
        ) {
            return [];
        }

        helper('product_media');

        $rows = $db->table('orders')
            ->select([
                'orders.id AS order_id',
                'orders.order_no',
                'orders.order_date',
                'orders.created_at AS order_created_at',
                'orders.paid_at',
                'orders.delivered_at',
                'orders.payment_status',
                'orders.order_status',
                'orders.status',
                'order_items.product_id',
                'order_items.product_name_snapshot',
                'order_items.author AS order_item_author',
                'order_items.product_image',
                'order_items.product_type',
                'products.product_name',
                'products.author AS product_author',
                'products.type AS product_type_live',
                'products.image AS product_image_live',
            ])
            ->join('order_items', 'order_items.order_id = orders.id', 'inner')
            ->join('products', 'products.id = order_items.product_id', 'inner')
            ->where('orders.user_id', $userId)
            ->where('orders.deleted_at', null)
            ->where('order_items.deleted_at', null)
            ->where('products.deleted_at', null)
            ->groupStart()
                ->whereIn('LOWER(TRIM(COALESCE(products.type, \'\')))', self::DIGITAL_TYPES)
                ->orWhereIn('LOWER(TRIM(COALESCE(order_items.product_type, \'\')))', self::DIGITAL_TYPES)
            ->groupEnd()
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
            ->orderBy('orders.paid_at', 'DESC')
            ->orderBy('orders.delivered_at', 'DESC')
            ->orderBy('orders.order_date', 'DESC')
            ->orderBy('orders.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $deduped = [];
        foreach ($rows as $row) {
            $productId = trim((string) ($row['product_id'] ?? ''));
            if ($productId === '') {
                continue;
            }

            // Keep only first row per product; query sorting ensures most recent qualifying purchase wins.
            if (isset($deduped[$productId])) {
                continue;
            }

            $name = trim((string) ($row['product_name'] ?? ''));
            if ($name === '') {
                $name = trim((string) ($row['product_name_snapshot'] ?? ''));
            }
            if ($name === '') {
                $name = 'Dijital Kitap';
            }

            $author = trim((string) ($row['product_author'] ?? ''));
            if ($author === '') {
                $author = trim((string) ($row['order_item_author'] ?? ''));
            }
            if ($author === '') {
                $author = 'Yazar belirtilmedi';
            }

            $imagePath = trim((string) ($row['product_image_live'] ?? ''));
            if ($imagePath === '') {
                $imagePath = trim((string) ($row['product_image'] ?? ''));
            }

            $deduped[$productId] = [
                'product_id' => $productId,
                'title' => $name,
                'author' => $author,
                'image_url' => product_image_url($imagePath),
                'last_order_no' => trim((string) ($row['order_no'] ?? '')),
                'last_order_date' => trim((string) ($row['order_date'] ?? '')),
            ];
        }

        return array_values($deduped);
    }
}
