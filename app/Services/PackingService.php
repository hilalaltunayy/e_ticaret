<?php

namespace App\Services;

use App\Models\OrderModel;
use App\Models\PackingSessionModel;

class PackingService
{
    public function __construct(
        private ?PackingSessionModel $packingSessionModel = null,
        private ?OrderModel $orderModel = null
    ) {
        $this->packingSessionModel = $this->packingSessionModel ?? new PackingSessionModel();
        $this->orderModel = $this->orderModel ?? new OrderModel();
    }

    public function createOrGetSession(string $orderId, ?string $createdBy = null): ?array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            return null;
        }

        $openSession = $this->packingSessionModel
            ->where('order_id', $orderId)
            ->where('status', 'open')
            ->orderBy('created_at', 'DESC')
            ->first();

        if (is_array($openSession) && $openSession !== []) {
            $backfilledSession = $this->backfillExpectedItemsIfEmpty($openSession);
            return $backfilledSession ?? $openSession;
        }

        $order = $this->orderModel->findByIdOrOrderNo($orderId);
        if (! $order) {
            return null;
        }

        $expectedItems = $this->buildExpectedItemsSnapshot((string) $order['id'], $order);
        $expectedItemsJson = json_encode($expectedItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($expectedItemsJson)) {
            $expectedItemsJson = '[]';
        }

        $sessionData = [
            'id' => PackingSessionModel::uuidV4(),
            'order_id' => (string) $order['id'],
            'package_code' => $this->generatePackageCode(),
            'status' => 'open',
            'expected_items_json' => $expectedItemsJson,
            'scanned_items_json' => '{"items":[],"unknown_scans":[]}',
            'verified_at' => null,
            'created_by' => $createdBy !== null && trim($createdBy) !== '' ? trim($createdBy) : null,
        ];

        $inserted = $this->packingSessionModel->insert($sessionData, false);
        if (! $inserted) {
            $raceOpenSession = $this->packingSessionModel
                ->where('order_id', (string) $order['id'])
                ->where('status', 'open')
                ->orderBy('created_at', 'DESC')
                ->first();

            return is_array($raceOpenSession) ? $raceOpenSession : null;
        }

        return $this->packingSessionModel->find((string) $sessionData['id']) ?: null;
    }

    private function backfillExpectedItemsIfEmpty(array $session): ?array
    {
        $sessionId = trim((string) ($session['id'] ?? ''));
        $orderId = trim((string) ($session['order_id'] ?? ''));
        if ($sessionId === '' || $orderId === '') {
            return null;
        }

        $rawExpected = trim((string) ($session['expected_items_json'] ?? ''));
        if ($rawExpected !== '' && $rawExpected !== '[]' && $this->decodeExpectedItems($session) !== []) {
            return $session;
        }

        $order = $this->orderModel->findByIdOrOrderNo($orderId);
        if (! $order) {
            return $session;
        }

        $expectedItems = $this->buildExpectedItemsSnapshot((string) $order['id'], $order);
        if ($expectedItems === []) {
            return $session;
        }

        $expectedItemsJson = json_encode($expectedItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($expectedItemsJson) || trim($expectedItemsJson) === '') {
            return $session;
        }

        $updated = $this->packingSessionModel->update($sessionId, [
            'expected_items_json' => $expectedItemsJson,
        ]);

        if (! $updated) {
            return $session;
        }

        $refreshed = $this->packingSessionModel->find($sessionId);
        return is_array($refreshed) && $refreshed !== [] ? $refreshed : $session;
    }

    public function decodeExpectedItems(array $session): array
    {
        $raw = trim((string) ($session['expected_items_json'] ?? ''));
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        $normalized = [];
        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }
            $normalized[] = $this->normalizeExpectedItem($item);
        }

        return $normalized;
    }

    public function decodeScannedPayload(array $session): array
    {
        $raw = trim((string) ($session['scanned_items_json'] ?? ''));
        if ($raw === '') {
            return ['items' => [], 'unknown_scans' => []];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return ['items' => [], 'unknown_scans' => []];
        }

        // Backward compatibility: old format was plain scanned items array.
        if (array_is_list($decoded)) {
            $items = [];
            foreach ($decoded as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $code = trim((string) ($row['barcode'] ?? $row['code'] ?? ''));
                $qty = max(1, (int) ($row['qty'] ?? 1));
                if ($code === '') {
                    continue;
                }
                $items[] = ['code' => $code, 'qty' => $qty];
            }

            return ['items' => $items, 'unknown_scans' => []];
        }

        $items = [];
        foreach ((array) ($decoded['items'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $code = trim((string) ($row['code'] ?? $row['barcode'] ?? ''));
            $qty = max(1, (int) ($row['qty'] ?? 1));
            if ($code === '') {
                continue;
            }
            $items[] = [
                'code' => $code,
                'qty' => $qty,
                'expected_key' => trim((string) ($row['expected_key'] ?? '')),
                'name' => trim((string) ($row['name'] ?? '')),
            ];
        }

        $unknownScans = [];
        foreach ((array) ($decoded['unknown_scans'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $code = trim((string) ($row['code'] ?? $row['barcode'] ?? ''));
            $qty = max(1, (int) ($row['qty'] ?? 1));
            if ($code === '') {
                continue;
            }
            $unknownScans[] = ['code' => $code, 'qty' => $qty];
        }

        return [
            'items' => $items,
            'unknown_scans' => $unknownScans,
        ];
    }

    public function decodeScannedItems(array $session): array
    {
        return $this->decodeScannedPayload($session)['items'];
    }

    public function encodeScannedPayload(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return is_string($json) ? $json : '{"items":[],"unknown_scans":[]}';
    }

    public function normalizeScannedItemsJson(string $rawJson): string
    {
        return $this->encodeScannedPayload(
            $this->decodeScannedPayload(['scanned_items_json' => $rawJson])
        );
    }

    public function applyScan(array $session, string $scanCode, int $qty = 1): array
    {
        $qty = max(1, $qty);
        $scanCode = trim($scanCode);
        if ($scanCode === '') {
            return [
                'success' => false,
                'message' => 'Barkod veya ISBN zorunludur.',
                'scanned_payload' => $this->decodeScannedPayload($session),
                'verification' => $this->getVerificationState($session),
            ];
        }

        $expectedItems = $this->decodeExpectedItems($session);
        $payload = $this->decodeScannedPayload($session);

        $match = $this->findExpectedItemByScanCode($expectedItems, $scanCode);
        $message = 'Okutma kaydedildi.';
        $isUnknown = false;

        if ($match === null) {
            $isUnknown = true;
            $message = 'Bilinmeyen ürün okutuldu.';
            $unknownScans = (array) ($payload['unknown_scans'] ?? []);
            $unknownScans = $this->incrementCodeQty($unknownScans, $scanCode);
            $payload['unknown_scans'] = $unknownScans;
        } else {
            $expectedKey = $this->resolveExpectedKey($match);
            $name = trim((string) ($match['name'] ?? 'Urun'));
            $code = (string) ($match['barcode'] ?? ($match['isbn'] ?? ($match['product_id'] ?? $scanCode)));
            if (trim($code) === '') {
                $code = $scanCode;
            }

            $items = (array) ($payload['items'] ?? []);
            $updated = false;
            foreach ($items as &$row) {
                if (! is_array($row)) {
                    continue;
                }

                $rowExpectedKey = trim((string) ($row['expected_key'] ?? ''));
                if ($rowExpectedKey !== '' && $rowExpectedKey === $expectedKey) {
                    $row['qty'] = max(1, (int) ($row['qty'] ?? 0) + $qty);
                    $row['code'] = $code;
                    $row['name'] = $name;
                    $updated = true;
                    break;
                }
            }
            unset($row);

            if (! $updated) {
                $items[] = [
                    'expected_key' => $expectedKey,
                    'code' => $code,
                    'name' => $name,
                    'qty' => $qty,
                ];
            }

            $payload['items'] = $items;
        }

        $session['scanned_items_json'] = $this->encodeScannedPayload($payload);

        return [
            'success' => true,
            'message' => $message,
            'is_unknown' => $isUnknown,
            'scanned_payload' => $payload,
            'scanned_json' => $session['scanned_items_json'],
            'verification' => $this->getVerificationState($session),
        ];
    }

    public function getVerificationState(array $session): array
    {
        $expectedItems = $this->decodeExpectedItems($session);
        $payload = $this->decodeScannedPayload($session);
        $knownScans = (array) ($payload['items'] ?? []);
        $unknownScans = (array) ($payload['unknown_scans'] ?? []);

        $scannedByExpectedKey = [];
        foreach ($knownScans as $scan) {
            if (! is_array($scan)) {
                continue;
            }
            $expectedKey = trim((string) ($scan['expected_key'] ?? ''));
            $qty = max(1, (int) ($scan['qty'] ?? 1));

            if ($expectedKey === '') {
                $code = trim((string) ($scan['code'] ?? $scan['barcode'] ?? ''));
                if ($code === '') {
                    continue;
                }
                $expectedKey = $this->normalizeScanCode($code);
            }

            if (! isset($scannedByExpectedKey[$expectedKey])) {
                $scannedByExpectedKey[$expectedKey] = 0;
            }
            $scannedByExpectedKey[$expectedKey] += $qty;
        }

        $expectedRows = [];
        $errors = [];
        $missingTotal = 0;
        $excessTotal = 0;

        foreach ($expectedItems as $item) {
            $expectedKey = $this->resolveExpectedKey($item);
            $expectedQty = max(1, (int) ($item['qty'] ?? 1));
            $scannedQty = (int) ($scannedByExpectedKey[$expectedKey] ?? 0);

            $status = 'match';
            if ($scannedQty < $expectedQty) {
                $status = 'missing';
                $missingTotal += ($expectedQty - $scannedQty);
            } elseif ($scannedQty > $expectedQty) {
                $status = 'excess';
                $excessTotal += ($scannedQty - $expectedQty);
            }

            $expectedRows[] = [
                'expected_key' => $expectedKey,
                'name' => (string) ($item['name'] ?? 'Urun'),
                'product_id' => (string) ($item['product_id'] ?? ''),
                'barcode' => (string) ($item['barcode'] ?? ''),
                'isbn' => (string) ($item['isbn'] ?? ''),
                'expected_qty' => $expectedQty,
                'scanned_qty' => $scannedQty,
                'status' => $status,
            ];
        }

        $scannedRows = [];
        foreach ($knownScans as $scan) {
            if (! is_array($scan)) {
                continue;
            }

            $code = trim((string) ($scan['code'] ?? $scan['barcode'] ?? ''));
            $qty = max(1, (int) ($scan['qty'] ?? 1));
            $expectedKey = trim((string) ($scan['expected_key'] ?? ''));
            $name = trim((string) ($scan['name'] ?? ''));

            $status = 'ok';
            if ($expectedKey !== '') {
                foreach ($expectedRows as $expectedRow) {
                    if ((string) $expectedRow['expected_key'] === $expectedKey && (string) $expectedRow['status'] === 'excess') {
                        $status = 'excess';
                        break;
                    }
                }
            }

            $scannedRows[] = [
                'type' => 'known',
                'code' => $code,
                'name' => $name !== '' ? $name : 'Urun',
                'qty' => $qty,
                'status' => $status,
            ];
        }

        $unknownTotal = 0;
        foreach ($unknownScans as $unknown) {
            if (! is_array($unknown)) {
                continue;
            }
            $code = trim((string) ($unknown['code'] ?? $unknown['barcode'] ?? ''));
            $qty = max(1, (int) ($unknown['qty'] ?? 1));
            if ($code === '') {
                continue;
            }

            $unknownTotal += $qty;
            $scannedRows[] = [
                'type' => 'unknown',
                'code' => $code,
                'name' => 'Bilinmeyen Urun',
                'qty' => $qty,
                'status' => 'unknown',
            ];
        }

        if ($missingTotal > 0) {
            $errors[] = 'Eksik urun var.';
        }
        if ($excessTotal > 0) {
            $errors[] = 'Fazla okutulan urun var.';
        }
        if ($unknownTotal > 0) {
            $errors[] = 'Bilinmeyen urun okutuldu.';
        }

        $canFinish = $expectedRows !== [] && $errors === [];

        return [
            'expected_items' => $expectedRows,
            'scanned_items' => $scannedRows,
            'unknown_scans' => $unknownScans,
            'errors' => $errors,
            'can_finish' => $canFinish,
            'totals' => [
                'expected_lines' => count($expectedRows),
                'missing_qty' => $missingTotal,
                'excess_qty' => $excessTotal,
                'unknown_qty' => $unknownTotal,
            ],
        ];
    }

    private function buildExpectedItemsSnapshot(string $orderId, array $order): array
    {
        $db = db_connect();
        $items = $this->orderModel->getOrderItems($orderId);

        $hasProductsTable = $db->tableExists('products');
        $hasBarcode = $hasProductsTable && $db->fieldExists('barcode', 'products');
        $hasIsbn = $hasProductsTable && $db->fieldExists('isbn', 'products');

        if ($items === []) {
            $fallbackProductId = (string) ($order['product_id'] ?? '');
            $fallbackName = (string) ($order['product_name'] ?? 'Urun');
            $fallbackQty = max(1, (int) ($order['quantity'] ?? 1));

            $item = [
                'product_id' => $fallbackProductId,
                'name' => $fallbackName,
                'qty' => $fallbackQty,
                'barcode' => $fallbackProductId,
                'isbn' => '',
                'scan_codes' => array_values(array_filter([$fallbackProductId])),
            ];

            if ($hasProductsTable && $fallbackProductId !== '' && ($hasBarcode || $hasIsbn)) {
                $select = 'id, product_name';
                if ($hasBarcode) {
                    $select .= ', barcode';
                }
                if ($hasIsbn) {
                    $select .= ', isbn';
                }

                $product = $db->table('products')
                    ->select($select)
                    ->where('id', $fallbackProductId)
                    ->get()
                    ->getRowArray();

                if ($product) {
                    $barcode = trim((string) ($product['barcode'] ?? ''));
                    $isbn = trim((string) ($product['isbn'] ?? ''));
                    $resolved = $barcode !== '' ? $barcode : ($isbn !== '' ? $isbn : $fallbackProductId);
                    $item['name'] = (string) ($product['product_name'] ?? $fallbackName);
                    $item['barcode'] = $resolved;
                    $item['isbn'] = $isbn;
                    $item['scan_codes'] = array_values(array_unique(array_filter([$resolved, $isbn, $fallbackProductId])));
                }
            }

            return [$item];
        }

        $productIds = [];
        foreach ($items as $item) {
            $pid = trim((string) ($item['product_id'] ?? ''));
            if ($pid !== '') {
                $productIds[] = $pid;
            }
        }
        $productIds = array_values(array_unique($productIds));

        $barcodeMap = [];
        if ($hasProductsTable && $productIds !== [] && ($hasBarcode || $hasIsbn)) {
            $select = 'id, product_name';
            if ($hasBarcode) {
                $select .= ', barcode';
            }
            if ($hasIsbn) {
                $select .= ', isbn';
            }

            $products = $db->table('products')
                ->select($select)
                ->whereIn('id', $productIds)
                ->get()
                ->getResultArray();

            foreach ($products as $product) {
                $id = (string) ($product['id'] ?? '');
                if ($id === '') {
                    continue;
                }

                $barcode = trim((string) ($product['barcode'] ?? ''));
                $isbn = trim((string) ($product['isbn'] ?? ''));
                $barcodeMap[$id] = [
                    'name' => (string) ($product['product_name'] ?? ''),
                    'barcode' => $barcode !== '' ? $barcode : $isbn,
                    'isbn' => $isbn,
                ];
            }
        }

        $snapshot = [];
        foreach ($items as $item) {
            $productId = trim((string) ($item['product_id'] ?? ''));
            $name = trim((string) ($item['product_name_snapshot'] ?? ''));
            $qty = max(1, (int) ($item['quantity'] ?? 1));

            $barcode = $productId;
            $isbn = '';
            if ($productId !== '' && isset($barcodeMap[$productId])) {
                if (trim((string) $barcodeMap[$productId]['name']) !== '') {
                    $name = (string) $barcodeMap[$productId]['name'];
                }

                $mapped = trim((string) ($barcodeMap[$productId]['barcode'] ?? ''));
                if ($mapped !== '') {
                    $barcode = $mapped;
                }
                $isbn = trim((string) ($barcodeMap[$productId]['isbn'] ?? ''));
            }

            if ($name === '') {
                $name = 'Urun';
            }

            $snapshot[] = [
                'product_id' => $productId,
                'name' => $name,
                'qty' => $qty,
                'barcode' => $barcode !== '' ? $barcode : $productId,
                'isbn' => $isbn,
                'scan_codes' => array_values(array_unique(array_filter([$barcode, $isbn, $productId]))),
            ];
        }

        return $snapshot;
    }

    private function generatePackageCode(): string
    {
        $year = (int) date('Y');
        $prefix = 'PKG-' . $year . '-';

        $maxNumber = 0;
        $rows = $this->packingSessionModel->builder()
            ->select('package_code')
            ->like('package_code', $prefix, 'after')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $packageCode = trim((string) ($row['package_code'] ?? ''));
            if ($packageCode === '' || strncmp($packageCode, $prefix, strlen($prefix)) !== 0) {
                continue;
            }

            $numeric = substr($packageCode, strlen($prefix));
            if (ctype_digit($numeric)) {
                $maxNumber = max($maxNumber, (int) $numeric);
            }
        }

        $next = $maxNumber + 1;
        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    private function normalizeExpectedItem(array $item): array
    {
        $productId = trim((string) ($item['product_id'] ?? ''));
        $barcode = trim((string) ($item['barcode'] ?? ''));
        $isbn = trim((string) ($item['isbn'] ?? ''));
        $name = trim((string) ($item['name'] ?? 'Urun'));
        $qty = max(1, (int) ($item['qty'] ?? 1));

        $scanCodes = [];
        foreach ((array) ($item['scan_codes'] ?? []) as $code) {
            $code = trim((string) $code);
            if ($code !== '') {
                $scanCodes[] = $code;
            }
        }

        if ($barcode !== '') {
            $scanCodes[] = $barcode;
        }
        if ($isbn !== '') {
            $scanCodes[] = $isbn;
        }
        if ($productId !== '') {
            $scanCodes[] = $productId;
        }

        return [
            'product_id' => $productId,
            'barcode' => $barcode !== '' ? $barcode : ($isbn !== '' ? $isbn : $productId),
            'isbn' => $isbn,
            'name' => $name,
            'qty' => $qty,
            'scan_codes' => array_values(array_unique($scanCodes)),
        ];
    }

    private function findExpectedItemByScanCode(array $expectedItems, string $scanCode): ?array
    {
        $needle = $this->normalizeScanCode($scanCode);
        if ($needle === '') {
            return null;
        }

        foreach ($expectedItems as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ((array) ($item['scan_codes'] ?? []) as $candidate) {
                if ($this->normalizeScanCode((string) $candidate) === $needle) {
                    return $item;
                }
            }
        }

        return null;
    }

    private function resolveExpectedKey(array $item): string
    {
        $barcode = trim((string) ($item['barcode'] ?? ''));
        $isbn = trim((string) ($item['isbn'] ?? ''));
        $productId = trim((string) ($item['product_id'] ?? ''));

        return $this->normalizeScanCode($barcode !== '' ? $barcode : ($isbn !== '' ? $isbn : $productId));
    }

    private function normalizeScanCode(string $code): string
    {
        $code = trim($code);
        if ($code === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($code, 'UTF-8');
        }

        return strtolower($code);
    }

    private function incrementCodeQty(array $rows, string $code): array
    {
        $needle = $this->normalizeScanCode($code);

        foreach ($rows as &$row) {
            if (! is_array($row)) {
                continue;
            }
            $current = $this->normalizeScanCode((string) ($row['code'] ?? ''));
            if ($current !== '' && $current === $needle) {
                $row['qty'] = max(1, (int) ($row['qty'] ?? 0) + 1);
                return $rows;
            }
        }
        unset($row);

        $rows[] = ['code' => $code, 'qty' => 1];
        return $rows;
    }
}
