<?php

namespace App\Services;

use App\Models\DashboardBlockModel;
use App\Models\DashboardBlockTypeModel;

class DashboardBlockService
{
    private const STAT_VARIANTS = ['mini_spark', 'metric_tile', 'income_card'];
    private const CHART_VARIANTS = ['line_trend', 'bar_overview', 'donut_summary', 'pie_breakdown'];
    private const NOTE_VARIANTS = ['simple_note', 'accent_note'];
    private const CHART_TYPES = ['bar', 'line', 'pie'];
    private const COLOR_PALETTES = ['default', 'blue', 'orange', 'green', 'purple', 'finance', 'analytics', 'pastel', 'dark', 'custom'];

    public function __construct(
        private ?DashboardBlockModel $dashboardBlockModel = null,
        private ?DashboardBlockTypeModel $dashboardBlockTypeModel = null
    ) {
        $this->dashboardBlockModel = $this->dashboardBlockModel ?? new DashboardBlockModel();
        $this->dashboardBlockTypeModel = $this->dashboardBlockTypeModel ?? new DashboardBlockTypeModel();
    }

    public function getBlocksForDashboard(?string $dashboardId): array
    {
        $dashboardId = trim((string) $dashboardId);
        if ($dashboardId === '' || ! $this->builderTablesReady()) {
            return [];
        }

        return $this->dashboardBlockModel->getBlocksByDashboardId($dashboardId);
    }

    public function getAvailableBlockTypes(): array
    {
        if (! $this->builderTablesReady()) {
            return [];
        }

        return $this->dashboardBlockTypeModel->getActiveTypes();
    }

    public function getBlockForEdit(string $blockId): ?array
    {
        $blockId = trim($blockId);
        if ($blockId === '' || ! $this->builderTablesReady()) {
            return null;
        }

        $block = $this->dashboardBlockModel->findBlockWithType($blockId);
        if (! is_array($block)) {
            return null;
        }

        $block['config'] = $this->decodeConfig($block['config_json'] ?? null);

        return $block;
    }

    public function addBlock(string $dashboardId, array $data): array
    {
        $dashboardId = trim($dashboardId);
        if ($dashboardId === '' || ! $this->builderTablesReady()) {
            return [
                'success' => false,
                'errors' => ['Aktif dashboard bulunamadi.'],
            ];
        }

        $blockTypeId = trim((string) ($data['block_type_id'] ?? ''));
        $title = trim((string) ($data['title'] ?? ''));
        $blockType = $blockTypeId === '' ? null : $this->dashboardBlockTypeModel->findActiveById($blockTypeId);
        $blockCode = (string) ($blockType['code'] ?? '');
        $errors = [];

        if ($blockTypeId === '' || ! is_array($blockType)) {
            $errors[] = 'Gecerli bir blok tipi secin.';
        }

        if ($title === '') {
            $errors[] = 'Blok basligi zorunludur.';
        }

        $configResult = $this->buildConfigForBlock($blockCode, $data, ['title' => $title]);
        $errors = array_merge($errors, $configResult['errors']);

        if ($errors !== []) {
            return [
                'success' => false,
                'errors' => $errors,
            ];
        }

        $orderIndex = $this->dashboardBlockModel->getNextOrderIndex($dashboardId);
        $dimensions = $this->defaultDimensionsForType($blockCode);
        $payload = [
            'dashboard_id' => $dashboardId,
            'block_type_id' => $blockTypeId,
            'title' => $title,
            'config_json' => json_encode($configResult['config'], JSON_UNESCAPED_UNICODE),
            'position_x' => 0,
            'position_y' => $orderIndex,
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'order_index' => $orderIndex,
            'is_visible' => $this->normalizeVisibility($data['is_visible'] ?? 1),
        ];

        $insertId = $this->dashboardBlockModel->insert($payload, true);
        if (! $insertId) {
            return [
                'success' => false,
                'errors' => ['Blok kaydedilemedi.'],
            ];
        }

        return [
            'success' => true,
            'id' => $insertId,
        ];
    }

    public function updateBlock(string $blockId, array $data): array
    {
        $block = $this->getBlockForEdit($blockId);
        if (! is_array($block)) {
            return [
                'success' => false,
                'errors' => ['Dashboard blogu bulunamadi.'],
            ];
        }

        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            return [
                'success' => false,
                'errors' => ['Blok basligi zorunludur.'],
            ];
        }

        $blockCode = trim((string) ($block['block_type_code'] ?? ''));
        $existingConfig = is_array($block['config'] ?? null) ? $block['config'] : [];
        $configResult = $this->buildConfigForBlock($blockCode, $data, array_merge($existingConfig, ['title' => $title]));

        if ($configResult['errors'] !== []) {
            return [
                'success' => false,
                'errors' => $configResult['errors'],
            ];
        }

        $payload = [
            'title' => $title,
            'config_json' => json_encode($configResult['config'], JSON_UNESCAPED_UNICODE),
            'is_visible' => $this->normalizeVisibility($data['is_visible'] ?? ($block['is_visible'] ?? 1)),
        ];

        if (! $this->dashboardBlockModel->update((string) $block['id'], $payload)) {
            return [
                'success' => false,
                'errors' => ['Dashboard blogu guncellenemedi.'],
            ];
        }

        return [
            'success' => true,
            'id' => (string) $block['id'],
        ];
    }

    public function deleteBlock(string $blockId): array
    {
        $blockId = trim($blockId);
        if ($blockId === '' || ! $this->builderTablesReady()) {
            return [
                'success' => false,
                'errors' => ['Dashboard blogu bulunamadi.'],
            ];
        }

        if (! is_array($this->dashboardBlockModel->find($blockId))) {
            return [
                'success' => false,
                'errors' => ['Dashboard blogu bulunamadi.'],
            ];
        }

        if (! $this->dashboardBlockModel->delete($blockId)) {
            return [
                'success' => false,
                'errors' => ['Dashboard blogu silinemedi.'],
            ];
        }

        return [
            'success' => true,
            'id' => $blockId,
        ];
    }

    private function builderTablesReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('dashboard_blocks') && $db->tableExists('dashboard_block_types');
    }

    private function buildConfigForBlock(string $blockCode, array $data, array $defaults = []): array
    {
        $config = [
            'title' => trim((string) ($defaults['title'] ?? $data['title'] ?? '')),
            'subtitle' => trim((string) ($data['subtitle'] ?? $defaults['subtitle'] ?? '')),
            'date_range' => $this->normalizeDateRange((string) ($data['date_range'] ?? $defaults['date_range'] ?? '7d')),
        ];
        $errors = [];

        if ($blockCode === 'stat_card') {
            $dataSource = trim((string) ($data['data_source'] ?? $defaults['data_source'] ?? ''));
            if ($dataSource === '') {
                $errors[] = 'Istatistik karti icin veri kaynagi secin.';
            }

            $config['data_source'] = $dataSource;
            $config['value_label'] = trim((string) ($data['value_label'] ?? $defaults['value_label'] ?? ''));
            $config['value'] = trim((string) ($data['value'] ?? $defaults['value'] ?? ''));
            $config['variant'] = $this->normalizeVariant('stat_card', (string) ($data['variant'] ?? $defaults['variant'] ?? 'mini_spark'));
            $config['color_palette'] = $this->normalizeColorPalette((string) ($data['color_palette'] ?? $defaults['color_palette'] ?? 'default'));
            $config['custom_colors'] = $this->normalizeColorList($data['custom_colors'] ?? ($defaults['custom_colors'] ?? []));
        } elseif ($blockCode === 'chart') {
            $dataSource = trim((string) ($data['data_source'] ?? $defaults['data_source'] ?? ''));
            if ($dataSource === '') {
                $errors[] = 'Grafik icin veri kaynagi secin.';
            }

            $config['data_source'] = $dataSource;
            $config['chart_type'] = $this->normalizeChartType((string) ($data['chart_type'] ?? $defaults['chart_type'] ?? 'line'));
            $config['variant'] = $this->normalizeVariant('chart', (string) ($data['variant'] ?? $defaults['variant'] ?? 'line_trend'));
            $config['color_palette'] = $this->normalizeColorPalette((string) ($data['color_palette'] ?? $defaults['color_palette'] ?? 'default'));
            $config['custom_colors'] = $this->normalizeColorList($data['custom_colors'] ?? ($defaults['custom_colors'] ?? []));
            $config['category_colors'] = $this->normalizeCategoryColors($data['category_colors'] ?? ($defaults['category_colors'] ?? []));
        } elseif ($blockCode === 'note') {
            $content = trim((string) ($data['content'] ?? $defaults['content'] ?? ''));
            if ($content === '') {
                $errors[] = 'Not icerigi zorunludur.';
            }

            $config['content'] = $content;
            $config['variant'] = $this->normalizeVariant('note', (string) ($data['variant'] ?? $defaults['variant'] ?? 'simple_note'));
        }

        return [
            'config' => $config,
            'errors' => $errors,
        ];
    }

    private function defaultDimensionsForType(string $blockTypeCode): array
    {
        if ($blockTypeCode === 'chart') {
            return ['width' => 8, 'height' => 2];
        }

        if ($blockTypeCode === 'note') {
            return ['width' => 4, 'height' => 2];
        }

        return ['width' => 4, 'height' => 1];
    }

    private function decodeConfig(mixed $configJson): array
    {
        if (! is_string($configJson) || trim($configJson) === '') {
            return [];
        }

        $decoded = json_decode($configJson, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeVisibility(mixed $value): int
    {
        return (string) $value === '0' ? 0 : 1;
    }

    private function normalizeVariant(string $blockCode, string $variant): string
    {
        $variant = strtolower(trim($variant));

        return match ($blockCode) {
            'stat_card' => in_array($variant, self::STAT_VARIANTS, true) ? $variant : 'mini_spark',
            'chart' => in_array($variant, self::CHART_VARIANTS, true) ? $variant : 'line_trend',
            'note' => in_array($variant, self::NOTE_VARIANTS, true) ? $variant : 'simple_note',
            default => $variant,
        };
    }

    private function normalizeChartType(string $chartType): string
    {
        $chartType = strtolower(trim($chartType));

        return in_array($chartType, self::CHART_TYPES, true) ? $chartType : 'line';
    }

    private function normalizeColorPalette(string $palette): string
    {
        $palette = strtolower(trim($palette));

        return in_array($palette, self::COLOR_PALETTES, true) ? $palette : 'default';
    }

    private function normalizeColorList(mixed $colors): array
    {
        if (is_array($colors)) {
            $items = $colors;
        } else {
            $items = preg_split('/[\r\n,]+/', (string) $colors) ?: [];
        }

        $normalized = [];

        foreach ($items as $color) {
            $color = $this->normalizeHexColor((string) $color);
            if ($color !== null) {
                $normalized[] = $color;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function normalizeCategoryColors(mixed $rawValue): array
    {
        if (is_array($rawValue)) {
            $lines = $rawValue;
        } else {
            $lines = preg_split('/[\r\n]+/', (string) $rawValue) ?: [];
        }

        $result = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '' || ! str_contains($line, '=')) {
                continue;
            }

            [$label, $color] = array_map('trim', explode('=', $line, 2));
            $color = $this->normalizeHexColor($color);
            if ($label !== '' && $color !== null) {
                $result[$label] = $color;
            }
        }

        return $result;
    }

    private function normalizeHexColor(string $color): ?string
    {
        $color = trim($color);
        if ($color === '') {
            return null;
        }

        if ($color[0] !== '#') {
            $color = '#' . $color;
        }

        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) === 1 ? strtoupper($color) : null;
    }

    private function normalizeDateRange(string $dateRange): string
    {
        $dateRange = strtolower(trim($dateRange));

        return in_array($dateRange, ['7d', '14d', '30d'], true) ? $dateRange : '7d';
    }
}
