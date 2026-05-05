<?php

namespace App\Services\Admin;

use App\Models\AdminNotificationPreferenceModel;
use App\Models\AdminNotificationRecordModel;
use App\Models\OrderModel;
use App\Models\ProductsModel;
use DomainException;

class AdminNotificationManagementService
{
    public function __construct(
        private ?AdminNotificationPreferenceModel $preferenceModel = null,
        private ?AdminNotificationRecordModel $recordModel = null,
        private ?ProductsModel $productsModel = null,
        private ?OrderModel $orderModel = null,
    ) {
        $this->preferenceModel = $this->preferenceModel ?? new AdminNotificationPreferenceModel();
        $this->recordModel = $this->recordModel ?? new AdminNotificationRecordModel();
        $this->productsModel = $this->productsModel ?? new ProductsModel();
        $this->orderModel = $this->orderModel ?? new OrderModel();
    }

    public function getPageData(string $userId, string $userRole): array
    {
        $definitions = $this->definitions();
        $hasPreferencesTable = $this->hasPreferencesTable();
        $hasRecordsTable = $this->hasRecordsTable();

        $preferences = $this->mergePreferences(
            $definitions,
            $hasPreferencesTable ? $this->preferenceModel->getForUser($userId) : []
        );

        $alerts = $this->buildAlerts($preferences);

        if ($hasRecordsTable) {
            $this->syncAlertRecords($userId, $alerts);
            $history = $this->recordModel->getRecentForUser($userId, 10);
        } else {
            $history = $this->buildPreviewHistory($alerts);
        }

        return [
            'setupReady' => $hasPreferencesTable && $hasRecordsTable,
            'setupWarning' => (! $hasPreferencesTable || ! $hasRecordsTable)
                ? 'Bildirim tercih/gecmis tablolari bulunamadi. Kalici kullanim icin migration uygulanmalidir.'
                : null,
            'definitions' => $definitions,
            'preferences' => $preferences,
            'summary' => [
                'enabled_count' => count(array_filter($preferences, static fn (array $item): bool => $item['is_enabled'])),
                'threshold_count' => count(array_filter($preferences, static fn (array $item): bool => $item['has_threshold'])),
                'active_alert_count' => count($alerts),
                'critical_alert_count' => count(array_filter($alerts, static fn (array $item): bool => $item['severity'] === 'critical')),
            ],
            'history' => $history,
            'userRole' => $userRole,
        ];
    }

    public function savePreferences(string $userId, string $userRole, array $payload): void
    {
        if (! $this->hasPreferencesTable()) {
            throw new DomainException('Bildirim tercih tablosu bulunamadi. Migration uygulanmadan kayit yapilamaz.');
        }

        $preferences = $payload['preferences'] ?? [];
        if (! is_array($preferences)) {
            throw new DomainException('Bildirim tercih verisi gecersiz.');
        }

        foreach ($this->definitions() as $definition) {
            $key = $definition['key'];
            $row = is_array($preferences[$key] ?? null) ? $preferences[$key] : [];
            $enabled = (string) ($row['enabled'] ?? '0') === '1' ? 1 : 0;
            $threshold = null;

            if ($definition['has_threshold']) {
                $rawThreshold = trim((string) ($row['threshold'] ?? ''));
                if ($rawThreshold === '' || ! is_numeric($rawThreshold)) {
                    throw new DomainException($definition['label'] . ' icin esik degeri zorunludur.');
                }

                $thresholdNumber = (float) $rawThreshold;
                if ($thresholdNumber < 0) {
                    throw new DomainException($definition['label'] . ' icin esik degeri negatif olamaz.');
                }

                $threshold = $definition['threshold_type'] === 'int'
                    ? (string) (int) round($thresholdNumber)
                    : number_format($thresholdNumber, 2, '.', '');
            }

            $existing = $this->preferenceModel
                ->where('user_id', $userId)
                ->where('notification_key', $key)
                ->first();

            $record = [
                'user_id' => $userId,
                'user_role' => $userRole,
                'notification_key' => $key,
                'category_key' => $definition['category_key'],
                'is_enabled' => $enabled,
                'threshold_value' => $threshold,
            ];

            if (is_array($existing) && isset($existing['id'])) {
                $this->preferenceModel->update((string) $existing['id'], $record);
                continue;
            }

            $this->preferenceModel->insert($record);
        }
    }

    private function definitions(): array
    {
        return [
            [
                'category_key' => 'stock',
                'category_label' => 'Stok Bildirimleri',
                'key' => 'stock_critical_below',
                'label' => 'Kritik stok altina dusen urunler',
                'description' => 'Belirlenen esigin altina inen basili urunleri bildirir.',
                'has_threshold' => true,
                'threshold_label' => 'Stok esigi',
                'threshold_type' => 'int',
                'default_enabled' => true,
                'default_threshold' => '5',
            ],
            [
                'category_key' => 'stock',
                'category_label' => 'Stok Bildirimleri',
                'key' => 'stock_out_of_stock',
                'label' => 'Stogu tukenen urunler',
                'description' => 'Satilabilir stok 0 olan urunler icin operasyon uyarisi olusturur.',
                'has_threshold' => false,
                'threshold_label' => null,
                'threshold_type' => null,
                'default_enabled' => true,
                'default_threshold' => null,
            ],
            [
                'category_key' => 'orders',
                'category_label' => 'Siparis Bildirimleri',
                'key' => 'order_new_created',
                'label' => 'Yeni siparis olustu',
                'description' => 'Gun icindeki yeni siparisleri ozet uyarida gosterir.',
                'has_threshold' => false,
                'threshold_label' => null,
                'threshold_type' => null,
                'default_enabled' => true,
                'default_threshold' => null,
            ],
            [
                'category_key' => 'orders',
                'category_label' => 'Siparis Bildirimleri',
                'key' => 'order_daily_sales_above',
                'label' => 'Gunluk satis hedefi asildi',
                'description' => 'Gunluk toplam satis belirlenen hedefin ustune ciktiginda bildirir.',
                'has_threshold' => true,
                'threshold_label' => 'Gunluk satis hedefi (TL)',
                'threshold_type' => 'decimal',
                'default_enabled' => true,
                'default_threshold' => '10000.00',
            ],
            [
                'category_key' => 'returns',
                'category_label' => 'Iade Bildirimleri',
                'key' => 'return_new_request',
                'label' => 'Yeni iade talebi',
                'description' => 'Iade surecine giren siparisler icin operasyon kaydi olusturur.',
                'has_threshold' => false,
                'threshold_label' => null,
                'threshold_type' => null,
                'default_enabled' => true,
                'default_threshold' => null,
            ],
            [
                'category_key' => 'returns',
                'category_label' => 'Iade Bildirimleri',
                'key' => 'return_rate_above',
                'label' => 'Iade orani yukseldi',
                'description' => 'Son 30 gunde iade orani belirlenen yuzdenin ustune cikarsa bildirir.',
                'has_threshold' => true,
                'threshold_label' => 'Iade orani esigi (%)',
                'threshold_type' => 'decimal',
                'default_enabled' => true,
                'default_threshold' => '10.00',
            ],
            [
                'category_key' => 'system',
                'category_label' => 'Sistem Bildirimleri',
                'key' => 'system_critical_errors_above',
                'label' => 'Kritik sistem hatalari',
                'description' => 'Son 24 saatteki basarisiz sistem kayitlari bu esigi asarsa bildirir.',
                'has_threshold' => true,
                'threshold_label' => 'Hata adedi esigi',
                'threshold_type' => 'int',
                'default_enabled' => true,
                'default_threshold' => '1',
            ],
            [
                'category_key' => 'system',
                'category_label' => 'Sistem Bildirimleri',
                'key' => 'system_unauthorized_access_above',
                'label' => 'Yetkisiz erisim denemeleri',
                'description' => 'Son 24 saatteki yetkisiz erisim denemeleri bu esigi asarsa bildirir.',
                'has_threshold' => true,
                'threshold_label' => 'Deneme adedi esigi',
                'threshold_type' => 'int',
                'default_enabled' => true,
                'default_threshold' => '3',
            ],
        ];
    }

    private function mergePreferences(array $definitions, array $rows): array
    {
        $rowMap = [];
        foreach ($rows as $row) {
            $key = (string) ($row['notification_key'] ?? '');
            if ($key !== '') {
                $rowMap[$key] = $row;
            }
        }

        $result = [];
        foreach ($definitions as $definition) {
            $row = $rowMap[$definition['key']] ?? null;
            $result[] = $definition + [
                'is_enabled' => $row !== null
                    ? (int) ($row['is_enabled'] ?? 0) === 1
                    : (bool) $definition['default_enabled'],
                'threshold_value' => $row !== null
                    ? (string) ($row['threshold_value'] ?? '')
                    : (string) ($definition['default_threshold'] ?? ''),
            ];
        }

        return $result;
    }

    private function buildAlerts(array $preferences): array
    {
        $alerts = [];
        foreach ($preferences as $preference) {
            if (! $preference['is_enabled']) {
                continue;
            }

            $key = (string) $preference['key'];
            $threshold = $preference['threshold_value'];

            switch ($key) {
                case 'stock_critical_below':
                    $alerts = array_merge($alerts, $this->buildCriticalStockAlerts((int) $threshold));
                    break;
                case 'stock_out_of_stock':
                    $alerts = array_merge($alerts, $this->buildOutOfStockAlerts());
                    break;
                case 'order_new_created':
                    $alerts = array_merge($alerts, $this->buildNewOrderAlerts());
                    break;
                case 'order_daily_sales_above':
                    $alerts = array_merge($alerts, $this->buildDailySalesAlerts((float) $threshold));
                    break;
                case 'return_new_request':
                    $alerts = array_merge($alerts, $this->buildReturnRequestAlerts());
                    break;
                case 'return_rate_above':
                    $alerts = array_merge($alerts, $this->buildReturnRateAlerts((float) $threshold));
                    break;
                case 'system_critical_errors_above':
                    $alerts = array_merge($alerts, $this->buildCriticalErrorAlerts((int) $threshold));
                    break;
                case 'system_unauthorized_access_above':
                    $alerts = array_merge($alerts, $this->buildUnauthorizedAlerts((int) $threshold));
                    break;
            }
        }

        usort($alerts, static function (array $left, array $right): int {
            $severityWeight = ['critical' => 3, 'warning' => 2, 'info' => 1];
            $leftWeight = $severityWeight[$left['severity']] ?? 0;
            $rightWeight = $severityWeight[$right['severity']] ?? 0;

            if ($leftWeight !== $rightWeight) {
                return $rightWeight <=> $leftWeight;
            }

            return strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? ''));
        });

        return $alerts;
    }

    private function buildCriticalStockAlerts(int $threshold): array
    {
        if (! db_connect()->tableExists('products')) {
            return [];
        }

        $items = $this->productsModel->getCriticalStockPrintedByAvailable(max(0, $threshold));
        if ($items === []) {
            return [];
        }

        $first = $items[0];
        return [[
            'notification_key' => 'stock_critical_below',
            'category_key' => 'stock',
            'severity' => 'warning',
            'title' => 'Kritik stok uyarisi',
            'message' => count($items) . ' urun kritik stok esiginin altinda. Ilk urun: '
                . (string) ($first['product_name'] ?? 'Urun')
                . ' (' . (string) ($first['available_stock'] ?? $first['stock_count'] ?? 0) . ' adet).',
            'created_at' => date('Y-m-d H:i:s'),
            'context' => ['count' => count($items), 'threshold' => $threshold],
        ]];
    }

    private function buildOutOfStockAlerts(): array
    {
        if (! db_connect()->tableExists('products')) {
            return [];
        }

        $items = array_filter(
            $this->productsModel->getCriticalStockPrintedByAvailable(0),
            static fn (array $item): bool => (int) ($item['available_stock'] ?? $item['stock_count'] ?? 0) <= 0
        );

        if ($items === []) {
            return [];
        }

        return [[
            'notification_key' => 'stock_out_of_stock',
            'category_key' => 'stock',
            'severity' => 'critical',
            'title' => 'Stogu tükenen urunler',
            'message' => count($items) . ' urunun satilabilir stogu tukendi.',
            'created_at' => date('Y-m-d H:i:s'),
            'context' => ['count' => count($items)],
        ]];
    }

    private function buildNewOrderAlerts(): array
    {
        if (! db_connect()->tableExists('orders')) {
            return [];
        }

        $start = date('Y-m-d 00:00:00');
        $count = (int) db_connect()->table('orders')
            ->where('deleted_at', null)
            ->where('created_at >=', $start)
            ->countAllResults();

        if ($count <= 0) {
            return [];
        }

        return [[
            'notification_key' => 'order_new_created',
            'category_key' => 'orders',
            'severity' => 'info',
            'title' => 'Yeni siparisler var',
            'message' => 'Bugun olusan yeni siparis sayisi: ' . $count . '.',
            'created_at' => date('Y-m-d H:i:s'),
            'context' => ['count' => $count],
        ]];
    }

    private function buildDailySalesAlerts(float $threshold): array
    {
        if (! db_connect()->tableExists('orders')) {
            return [];
        }

        $start = date('Y-m-d 00:00:00');
        $end = date('Y-m-d 23:59:59');
        $row = $this->orderModel->getRevenueSumRow($start, $end);
        $total = (float) ($row['total'] ?? 0);

        if ($total <= $threshold) {
            return [];
        }

        return [[
            'notification_key' => 'order_daily_sales_above',
            'category_key' => 'orders',
            'severity' => 'info',
            'title' => 'Gunluk satis hedefi asildi',
            'message' => 'Bugunku satis toplami ' . number_format($total, 2, ',', '.') . ' TL ile hedefi asti.',
            'created_at' => date('Y-m-d H:i:s'),
            'context' => ['total' => $total, 'threshold' => $threshold],
        ]];
    }

    private function buildReturnRequestAlerts(): array
    {
        if (! db_connect()->tableExists('orders')) {
            return [];
        }

        $count = (int) db_connect()->table('orders')
            ->where('deleted_at', null)
            ->where('order_status', 'return_in_progress')
            ->countAllResults();

        if ($count <= 0) {
            return [];
        }

        return [[
            'notification_key' => 'return_new_request',
            'category_key' => 'returns',
            'severity' => 'warning',
            'title' => 'Yeni iade talepleri var',
            'message' => 'Isleme alinan iade talebi sayisi: ' . $count . '.',
            'created_at' => date('Y-m-d H:i:s'),
            'context' => ['count' => $count],
        ]];
    }

    private function buildReturnRateAlerts(float $threshold): array
    {
        if (! db_connect()->tableExists('orders')) {
            return [];
        }

        $start = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $db = db_connect();
        $total = (int) $db->table('orders')
            ->where('deleted_at', null)
            ->where('created_at >=', $start)
            ->countAllResults();

        if ($total <= 0) {
            return [];
        }

        $returned = (int) $db->table('orders')
            ->where('deleted_at', null)
            ->where('created_at >=', $start)
            ->whereIn('order_status', ['return_in_progress', 'return_done', 'returned'])
            ->countAllResults();

        $rate = ($returned / $total) * 100;
        if ($rate <= $threshold) {
            return [];
        }

        return [[
            'notification_key' => 'return_rate_above',
            'category_key' => 'returns',
            'severity' => 'warning',
            'title' => 'Iade orani yuksek',
            'message' => 'Son 30 gunde iade orani %' . number_format($rate, 1, ',', '.') . ' seviyesine ulasti.',
            'created_at' => date('Y-m-d H:i:s'),
            'context' => ['rate' => $rate, 'threshold' => $threshold],
        ]];
    }

    private function buildCriticalErrorAlerts(int $threshold): array
    {
        $db = db_connect();
        if (! $db->tableExists('notification_delivery_logs')) {
            return [];
        }

        $start = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $count = (int) $db->table('notification_delivery_logs')
            ->where('status', 'failed')
            ->where('created_at >=', $start)
            ->countAllResults();

        if ($count < max(1, $threshold)) {
            return [];
        }

        return [[
            'notification_key' => 'system_critical_errors_above',
            'category_key' => 'system',
            'severity' => 'critical',
            'title' => 'Kritik sistem hatalari',
            'message' => 'Son 24 saatte ' . $count . ' basarisiz sistem kaydi olustu.',
            'created_at' => date('Y-m-d H:i:s'),
            'context' => ['count' => $count, 'threshold' => $threshold],
        ]];
    }

    private function buildUnauthorizedAlerts(int $threshold): array
    {
        $db = db_connect();
        if (! $db->tableExists('audit_logs')) {
            return [];
        }

        $start = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $actions = ['auth.login_failed', 'login.failed', 'security.unauthorized_access', 'auth.unauthorized'];
        $count = (int) $db->table('audit_logs')
            ->where('created_at >=', $start)
            ->whereIn('action', $actions)
            ->countAllResults();

        if ($count < max(1, $threshold)) {
            return [];
        }

        return [[
            'notification_key' => 'system_unauthorized_access_above',
            'category_key' => 'system',
            'severity' => 'warning',
            'title' => 'Yetkisiz erisim denemeleri',
            'message' => 'Son 24 saatte ' . $count . ' yetkisiz erisim denemesi kaydi bulundu.',
            'created_at' => date('Y-m-d H:i:s'),
            'context' => ['count' => $count, 'threshold' => $threshold],
        ]];
    }

    private function syncAlertRecords(string $userId, array $alerts): void
    {
        if (! $this->hasRecordsTable()) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $seenFingerprints = [];

        foreach ($alerts as $alert) {
            $fingerprint = hash('sha256', $userId . '|' . $alert['notification_key'] . '|' . $alert['message']);
            $seenFingerprints[] = $fingerprint;

            $existing = $this->recordModel
                ->where('user_id', $userId)
                ->where('fingerprint', $fingerprint)
                ->first();

            $payload = [
                'user_id' => $userId,
                'notification_key' => $alert['notification_key'],
                'category_key' => $alert['category_key'],
                'severity' => $alert['severity'],
                'title' => $alert['title'],
                'message' => $alert['message'],
                'status' => 'active',
                'source_type' => 'system_preview',
                'fingerprint' => $fingerprint,
                'context_json' => json_encode($alert['context'] ?? [], JSON_UNESCAPED_UNICODE),
                'resolved_at' => null,
                'updated_at' => $now,
            ];

            if (is_array($existing) && isset($existing['id'])) {
                $this->recordModel->update((string) $existing['id'], $payload);
                continue;
            }

            $payload['created_at'] = $now;
            $this->recordModel->insert($payload);
        }

        $builder = $this->recordModel->builder()
            ->where('user_id', $userId)
            ->where('source_type', 'system_preview')
            ->where('status', 'active');

        if ($seenFingerprints !== []) {
            $builder->whereNotIn('fingerprint', $seenFingerprints);
        }

        $builder->update([
            'status' => 'resolved',
            'resolved_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function buildPreviewHistory(array $alerts): array
    {
        return array_map(static function (array $alert): array {
            return [
                'title' => $alert['title'],
                'message' => $alert['message'],
                'severity' => $alert['severity'],
                'status' => 'active',
                'updated_at' => $alert['created_at'],
            ];
        }, array_slice($alerts, 0, 10));
    }

    private function hasPreferencesTable(): bool
    {
        return db_connect()->tableExists('admin_notification_preferences');
    }

    private function hasRecordsTable(): bool
    {
        return db_connect()->tableExists('admin_notification_records');
    }
}
