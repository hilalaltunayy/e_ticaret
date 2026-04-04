<?php

namespace App\Services;

use App\Models\DashboardBlockInstanceModel;
use App\Models\DashboardModel;

class DashboardBuilderService
{
    public function __construct(
        private ?DashboardModel $dashboardModel = null,
        private ?DashboardBlockInstanceModel $dashboardBlockInstanceModel = null
    ) {
        $this->dashboardModel = $this->dashboardModel ?? new DashboardModel();
        $this->dashboardBlockInstanceModel = $this->dashboardBlockInstanceModel ?? new DashboardBlockInstanceModel();
    }

    public function getOrCreateAdminDashboard(string $userId): array
    {
        $userId = trim($userId);
        if ($userId === '' || ! $this->builderTablesReady()) {
            return [];
        }

        $dashboard = $this->dashboardModel
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->orderBy('updated_at', 'DESC')
            ->first();

        if (is_array($dashboard) && ! empty($dashboard['id'])) {
            return $dashboard;
        }

        $globalDashboard = $this->dashboardModel
            ->where('is_active', 1)
            ->groupStart()
            ->where('user_id', null)
            ->orWhere('user_id', '')
            ->groupEnd()
            ->orderBy('updated_at', 'DESC')
            ->first();

        if (is_array($globalDashboard) && ! empty($globalDashboard['id'])) {
            $globalDashboardCount = $this->dashboardModel
                ->where('is_active', 1)
                ->groupStart()
                ->where('user_id', null)
                ->orWhere('user_id', '')
                ->groupEnd()
                ->countAllResults();

            if ($globalDashboardCount === 1) {
                $this->dashboardModel->update((string) $globalDashboard['id'], ['user_id' => $userId]);
                $globalDashboard = $this->dashboardModel->find((string) $globalDashboard['id']) ?? $globalDashboard;
            }

            return is_array($globalDashboard) ? $globalDashboard : [];
        }

        $existing = $this->dashboardModel
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->first();

        if (is_array($existing) && ! empty($existing['id'])) {
            $this->dashboardModel->update((string) $existing['id'], ['is_active' => 1]);

            $dashboard = $this->dashboardModel->find((string) $existing['id']);

            return is_array($dashboard) ? $dashboard : [];
        }

        $dashboardId = $this->dashboardModel->insert([
            'user_id' => $userId,
            'name' => 'Dashboard Builder',
            'description' => 'Admin kullanicisi icin otomatik olusturulan dashboard.',
            'is_active' => 1,
        ], true);

        if (! $dashboardId) {
            return [];
        }

        $dashboard = $this->dashboardModel->find((string) $dashboardId);

        return is_array($dashboard) ? $dashboard : [];
    }

    public function getBuilderBlocks(string $userId): array
    {
        if (! $this->builderTablesReady()) {
            return [];
        }

        $dashboard = $this->getOrCreateAdminDashboard($userId);
        $dashboardId = trim((string) ($dashboard['id'] ?? ''));

        if ($dashboardId === '') {
            return [];
        }

        $blocks = $this->dashboardBlockInstanceModel->getInstancesByDashboardId($dashboardId);

        return array_map(fn(array $block) => $this->formatBuilderBlock($block), $blocks);
    }

    public function saveBlockOrder(string $userId, array $blocks): array
    {
        if (! $this->builderTablesReady()) {
            return [
                'success' => false,
                'message' => 'Builder tablolarina ulasilamadi.',
            ];
        }

        $dashboard = $this->getOrCreateAdminDashboard($userId);
        $dashboardId = trim((string) ($dashboard['id'] ?? ''));
        if ($dashboardId === '') {
            return [
                'success' => false,
                'message' => 'Aktif dashboard bulunamadi.',
            ];
        }

        $existingBlocks = $this->dashboardBlockInstanceModel->getInstancesByDashboardId($dashboardId);
        $existingIds = array_values(array_filter(array_map(static fn(array $block) => trim((string) ($block['id'] ?? '')), $existingBlocks)));

        if ($existingIds === []) {
            return [
                'success' => false,
                'message' => 'Siralanacak blok bulunamadi.',
            ];
        }

        $normalizedBlocks = [];

        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                return [
                    'success' => false,
                    'message' => 'Gonderilen blok verisi gecersiz.',
                ];
            }

            $id = trim((string) ($block['id'] ?? ''));
            if ($id === '') {
                return [
                    'success' => false,
                    'message' => 'Blok kimligi eksik.',
                ];
            }

            $normalizedBlocks[] = [
                'id' => $id,
                'order_index' => isset($block['order_index']) ? (int) $block['order_index'] : $index,
                'position_x' => isset($block['position_x']) ? (int) $block['position_x'] : 0,
                'position_y' => isset($block['position_y']) ? (int) $block['position_y'] : $index,
            ];
        }

        $incomingIds = array_values(array_map(static fn(array $block) => $block['id'], $normalizedBlocks));
        $existingSorted = $existingIds;
        $incomingSorted = $incomingIds;
        sort($existingSorted);
        sort($incomingSorted);

        if (count($incomingIds) !== count($existingIds) || $incomingSorted !== $existingSorted) {
            return [
                'success' => false,
                'message' => 'Siralama verisi bu dashboard ile uyusmuyor.',
            ];
        }

        $db = db_connect();
        $db->transStart();

        foreach ($normalizedBlocks as $index => $block) {
            $payload = [
                'order_index' => $index,
                'position_x' => (int) $block['position_x'],
                'position_y' => $index,
            ];

            $updated = $this->dashboardBlockInstanceModel->update($block['id'], $payload);

            if (! $updated) {
                $db->transRollback();

                return [
                    'success' => false,
                    'message' => 'Blok sirasi kaydedilemedi.',
                ];
            }
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return [
                'success' => false,
                'message' => 'Blok sirasi kaydedilemedi.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Yeni blok sirasi kaydedildi.',
        ];
    }

    public function resizeBlock(string $userId, string $blockId, mixed $width, mixed $height): array
    {
        if (! $this->builderTablesReady()) {
            return [
                'success' => false,
                'message' => 'Builder tablolarina ulasilamadi.',
            ];
        }

        $dashboard = $this->getOrCreateAdminDashboard($userId);
        $dashboardId = trim((string) ($dashboard['id'] ?? ''));
        if ($dashboardId === '') {
            return [
                'success' => false,
                'message' => 'Aktif dashboard bulunamadi.',
            ];
        }

        $blockId = trim($blockId);
        if ($blockId === '') {
            return [
                'success' => false,
                'message' => 'Blok kimligi eksik.',
            ];
        }

        $normalizedWidth = max(2, min(12, (int) $width));
        $normalizedHeight = max(1, min(6, (int) $height));

        $instance = $this->dashboardBlockInstanceModel
            ->where('dashboard_id', $dashboardId)
            ->where('id', $blockId)
            ->first();

        if (! is_array($instance) || empty($instance['id'])) {
            return [
                'success' => false,
                'message' => 'Blok bu dashboard ile uyusmuyor.',
            ];
        }

        $db = db_connect();
        $db->transStart();

        $updated = $this->dashboardBlockInstanceModel->update($blockId, [
            'width' => $normalizedWidth,
            'height' => $normalizedHeight,
        ]);

        if (! $updated) {
            $db->transRollback();

            return [
                'success' => false,
                'message' => 'Blok boyutu kaydedilemedi.',
            ];
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return [
                'success' => false,
                'message' => 'Blok boyutu kaydedilemedi.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Blok boyutu kaydedildi.',
            'width' => $normalizedWidth,
            'height' => $normalizedHeight,
        ];
    }

    private function builderTablesReady(): bool
    {
        $db = db_connect();
        $blockTypeTableExists = $db->tableExists('dashboard_block_types') || $db->tableExists('dashboard_blocks');

        return $db->tableExists('dashboards')
            && $blockTypeTableExists
            && $db->tableExists('dashboard_block_instances');
    }

    private function formatBuilderBlock(array $block): array
    {
        $configJson = (string) ($block['config_json'] ?? '');
        $config = $this->parseConfig($configJson);

        return [
            'id' => (string) ($block['id'] ?? ''),
            'title' => trim((string) ($block['title'] ?? '')) !== '' ? (string) $block['title'] : (string) ($block['block_type_name'] ?? 'Blok'),
            'block_type_code' => (string) ($block['block_type_code'] ?? ''),
            'block_type_name' => (string) ($block['block_type_name'] ?? ''),
            'is_visible' => (int) ($block['is_visible'] ?? 0),
            'order_index' => (int) ($block['order_index'] ?? 0),
            'position_x' => (int) ($block['position_x'] ?? 0),
            'position_y' => (int) ($block['position_y'] ?? 0),
            'width' => (int) ($block['width'] ?? 0),
            'height' => (int) ($block['height'] ?? 0),
            'config_json' => $configJson,
            'config_summary' => $this->buildConfigSummary($config, $configJson),
        ];
    }

    private function parseConfig(string $configJson): array
    {
        if (trim($configJson) === '') {
            return [];
        }

        $decoded = json_decode($configJson, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function buildConfigSummary(array $config, string $configJson): string
    {
        $summaryParts = [];

        foreach (['title', 'data_source', 'date_range', 'chart_type', 'metric', 'value_label'] as $key) {
            $value = trim((string) ($config[$key] ?? ''));
            if ($value !== '') {
                $summaryParts[] = $key . ': ' . $value;
            }
        }

        if ($summaryParts !== []) {
            return $this->truncate(implode(' | ', $summaryParts), 140);
        }

        if (trim($configJson) === '') {
            return 'Konfigürasyon bilgisi yok.';
        }

        return $this->truncate($configJson, 140);
    }

    private function truncate(string $text, int $limit): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit - 1) . '…' : $text;
        }

        return strlen($text) > $limit ? substr($text, 0, $limit - 3) . '...' : $text;
    }
}
