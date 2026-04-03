<?php

namespace App\Services;

helper(['dashboard_date', 'dashboard_series']);

use App\Models\OrderModel;

class DashboardDataSourceService
{
    public function __construct(private ?OrderModel $orderModel = null)
    {
        $this->orderModel = $this->orderModel ?? new OrderModel();
    }

    public function hydrateBlocks(array $blocks): array
    {
        $hydrated = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $config = $this->parseConfig($block['config_json'] ?? null);
            $block['config'] = $config;
            $block['render'] = $this->resolveRenderPayload($block, $config);
            $hydrated[] = $block;
        }

        return $hydrated;
    }

    public function getChartBreakdownDetail(string $source, string $label, string $period): array
    {
        $source = trim($source);
        $label = trim($label);
        $period = $this->normalizeDetailPeriod($period);

        if (! in_array($source, ['sales_by_category', 'print_vs_digital_sales'], true) || $label === '') {
            return [
                'success' => false,
                'message' => 'Gecerli detay kirilimi bulunamadi.',
            ];
        }

        [$start, $end] = $this->periodRange($period);
        $rows = [];
        $title = $label . ' Satis Detayi';

        if ($source === 'sales_by_category') {
            $rows = $this->orderModel->getBuilderCategoryDetailAggregate($label, $start, $end);
            $title = $label . ' Satis Detayi';
        }

        if ($source === 'print_vs_digital_sales') {
            $normalizedType = $this->normalizeProductTypeLabel($label);
            $rows = $this->orderModel->getBuilderProductTypeDetailAggregate($normalizedType, $start, $end);
            $title = ($normalizedType === 'digital' ? 'Dijital' : 'Baski') . ' Urun Satis Detayi';
        }

        return [
            'success' => true,
            'title' => $title,
            'period' => $period,
            'totalSoldQty' => array_sum(array_map(static fn(array $row) => (int) ($row['sold_qty'] ?? 0), $rows)),
            'rows' => array_map(function (array $row) {
                $type = trim((string) ($row['normalized_type'] ?? $row['product_type'] ?? ''));

                return [
                    'book_name' => (string) ($row['product_name'] ?? '-'),
                    'format' => $this->formatProductTypeLabel($type),
                    'sold_qty' => (int) ($row['sold_qty'] ?? 0),
                    'remaining_stock' => isset($row['remaining_stock']) ? (int) $row['remaining_stock'] : '-',
                    'last_sale_date' => $this->formatDateTime((string) ($row['last_sale_date'] ?? '')),
                ];
            }, $rows),
        ];
    }

    private function resolveRenderPayload(array $block, array $config): array
    {
        $blockType = trim((string) ($block['block_type_code'] ?? ''));

        if ($blockType === 'stat_card') {
            return $this->buildStatCardPayload($block, $config);
        }

        if ($blockType === 'chart') {
            return $this->buildChartPayload($block, $config);
        }

        if ($blockType === 'note') {
            return $this->buildNotePayload($block, $config);
        }

        return [
            'kind' => 'unknown',
            'message' => 'Bu blok tipi icin render tanimi bulunamadi.',
        ];
    }

    private function buildStatCardPayload(array $block, array $config): array
    {
        $source = trim((string) ($config['data_source'] ?? ''));
        $theme = $this->statTheme($source);
        $title = trim((string) ($config['title'] ?? $block['title'] ?? 'Istatistik Karti'));
        $subtitle = trim((string) ($config['subtitle'] ?? ''));
        $valueLabel = trim((string) ($config['value_label'] ?? 'Siparis'));
        $fallbackValue = trim((string) ($config['value'] ?? ''));
        $dateRange = $this->normalizeDateRange((string) ($config['date_range'] ?? '7d'));
        $variant = $this->normalizeVariant('stat_card', (string) ($config['variant'] ?? 'mini_spark'));
        $colorPalette = $this->normalizeColorPalette((string) ($config['color_palette'] ?? 'default'));
        $customColors = $this->normalizeColorList($config['custom_colors'] ?? []);
        $accentColor = $this->palettePrimaryColor($colorPalette, $theme['color'], $customColors);
        $theme['color'] = $accentColor;

        if ($source === '') {
            return [
                'kind' => 'stat_card',
                'variant' => $variant,
                'theme' => $theme,
                'title' => $title,
                'value' => $fallbackValue !== '' ? $fallbackValue : '--',
                'subtitle' => $subtitle !== '' ? $subtitle : 'Veri kaynagi tanimlanmadi.',
                'valueLabel' => $valueLabel,
                'trendText' => 'Kaynak bekleniyor',
                'trendClass' => 'text-muted',
                'chartId' => $this->chartId($block, 'spark'),
                'chartOptions' => null,
                'message' => 'Veri kaynagi tanimli olmadigi icin kayitli icerik gosteriliyor.',
            ];
        }

        $metric = $this->resolveStatMetric($source, $dateRange);
        if ($metric === null) {
            return [
                'kind' => 'stat_card',
                'variant' => $variant,
                'theme' => $theme,
                'title' => $title,
                'value' => $fallbackValue !== '' ? $fallbackValue : '--',
                'subtitle' => $subtitle !== '' ? $subtitle : 'Veri kaynagi okunamadi.',
                'valueLabel' => $valueLabel,
                'trendText' => 'Kaynak hatasi',
                'trendClass' => 'text-danger',
                'chartId' => $this->chartId($block, 'spark'),
                'chartOptions' => null,
                'message' => 'Tanimli veri kaynagi desteklenmiyor.',
            ];
        }

        $chartId = $this->chartId($block, 'spark');

        return [
            'kind' => 'stat_card',
            'variant' => $variant,
            'theme' => $theme,
            'title' => $title,
            'value' => $metric['value'],
            'subtitle' => $subtitle !== '' ? $subtitle : $metric['subtitle'],
            'valueLabel' => $valueLabel !== '' ? $valueLabel : $metric['valueLabel'],
            'trendText' => $metric['trendText'],
            'trendClass' => $metric['trendClass'],
            'chartId' => $chartId,
            'chartOptions' => $this->buildSparklineOptions($chartId, $theme['color'], $metric['series']),
            'message' => null,
        ];
    }

    private function buildChartPayload(array $block, array $config): array
    {
        $source = trim((string) ($config['data_source'] ?? ''));
        $chartType = $this->normalizeChartType((string) ($config['chart_type'] ?? 'line'));
        $dateRange = $this->normalizeDateRange((string) ($config['date_range'] ?? '14d'));
        $title = trim((string) ($config['title'] ?? $block['title'] ?? 'Grafik'));
        $subtitle = trim((string) ($config['subtitle'] ?? ''));
        $chartId = $this->chartId($block, 'chart');
        $variant = $this->normalizeVariant('chart', (string) ($config['variant'] ?? 'line_trend'));
        $colorPalette = $this->normalizeColorPalette((string) ($config['color_palette'] ?? 'default'));
        $customColors = $this->normalizeColorList($config['custom_colors'] ?? []);
        $categoryColors = $this->normalizeCategoryColors($config['category_colors'] ?? []);

        if ($source === '') {
            return [
                'kind' => 'chart',
                'variant' => $variant,
                'title' => $title,
                'subtitle' => $subtitle !== '' ? $subtitle : 'Veri kaynagi tanimlanmadi.',
                'chartId' => null,
                'chartOptions' => null,
                'message' => 'Grafik icin veri kaynagi secilmedi.',
            ];
        }

        $dataset = $this->resolveChartDataset($source, $dateRange);
        if ($dataset === null) {
            return [
                'kind' => 'chart',
                'variant' => $variant,
                'title' => $title,
                'subtitle' => $subtitle !== '' ? $subtitle : 'Grafik verisi olusturulamadi.',
                'chartId' => null,
                'chartOptions' => null,
                'message' => 'Tanimli veri kaynagi desteklenmiyor.',
            ];
        }

        $dataset['colors'] = $this->resolveDatasetColors(
            $dataset['labels'],
            $dataset['colors'],
            $colorPalette,
            $customColors,
            $categoryColors
        );

        return [
            'kind' => 'chart',
            'variant' => $variant,
            'title' => $title,
            'subtitle' => $subtitle !== '' ? $subtitle : $dataset['subtitle'],
            'detailSource' => $source,
            'chartId' => $chartId,
            'chartOptions' => $this->buildChartOptions($chartId, $chartType, $dataset, $variant),
            'message' => null,
            'summary' => $dataset['summary'],
        ];
    }

    private function buildNotePayload(array $block, array $config): array
    {
        $content = trim((string) ($config['content'] ?? ''));

        return [
            'kind' => 'note',
            'variant' => $this->normalizeVariant('note', (string) ($config['variant'] ?? 'simple_note')),
            'title' => trim((string) ($config['title'] ?? $block['title'] ?? 'Not')),
            'subtitle' => trim((string) ($config['subtitle'] ?? 'Kisa dashboard notu')),
            'content' => $content !== '' ? $content : 'Bu blok icin henuz not icerigi girilmedi.',
        ];
    }

    private function resolveStatMetric(string $source, string $dateRange): ?array
    {
        $seriesPoints = $this->getOrderCountSeries($dateRange);
        $seriesValues = array_map(static fn($point) => (float) ($point['value'] ?? 0), $seriesPoints);

        if ($source === 'total_orders') {
            $historicalSeriesPoints = $this->getHistoricalOrderCountSeries($dateRange);
            $historicalSeriesValues = array_map(static fn($point) => (float) ($point['value'] ?? 0), $historicalSeriesPoints);

            return [
                'value' => number_format($this->orderModel->countAllOrders(), 0, ',', '.'),
                'subtitle' => 'Sistemdeki toplam siparis adedi',
                'valueLabel' => 'Toplam Siparis',
                'trendText' => 'Son ' . count($historicalSeriesValues) . ' gunde ' . number_format((float) array_sum($historicalSeriesValues), 0, ',', '.') . ' siparis',
                'trendClass' => 'text-primary',
                'series' => $historicalSeriesValues,
            ];
        }

        if ($source === 'today_orders') {
            [$yesterdayStart, $yesterdayEnd] = dash_yesterday_range();
            $today = $this->orderModel->countOrdersBetween(dash_today_start(), dash_today_end());
            $yesterday = $this->orderModel->countOrdersBetween($yesterdayStart, $yesterdayEnd);

            return [
                'value' => number_format($today, 0, ',', '.'),
                'subtitle' => 'Bugun alinan siparisler',
                'valueLabel' => 'Bugun',
                'trendText' => $this->compareText($today, $yesterday, 'dune gore'),
                'trendClass' => $this->compareClass($today, $yesterday),
                'series' => $seriesValues,
            ];
        }

        if ($source === 'weekly_orders') {
            $current = $this->orderModel->countOrdersBetween(dash_week_start(), dash_today_end());
            $previousStart = date('Y-m-d 00:00:00', strtotime('-13 days'));
            $previousEnd = date('Y-m-d 23:59:59', strtotime('-7 days'));
            $previous = $this->orderModel->countOrdersBetween($previousStart, $previousEnd);

            return [
                'value' => number_format($current, 0, ',', '.'),
                'subtitle' => 'Bu hafta alinan siparisler',
                'valueLabel' => 'Bu Hafta',
                'trendText' => $this->compareText($current, $previous, 'onceki haftaya gore'),
                'trendClass' => $this->compareClass($current, $previous),
                'series' => $seriesValues,
            ];
        }

        if ($source === 'pending_orders') {
            $pending = $this->orderModel->countOrdersByStatus('pending');

            return [
                'value' => number_format($pending, 0, ',', '.'),
                'subtitle' => 'Islem bekleyen siparisler',
                'valueLabel' => 'Bekleyen',
                'trendText' => $pending > 0 ? 'Aksiyon bekliyor' : 'Guncel',
                'trendClass' => $pending > 0 ? 'text-warning' : 'text-success',
                'series' => $seriesValues,
            ];
        }

        return null;
    }

    private function resolveChartDataset(string $source, string $dateRange): ?array
    {
        if ($source === 'orders_by_period') {
            $series = $this->getOrderCountSeries($dateRange);

            return [
                'subtitle' => 'Secilen donemde siparis hacmi',
                'labels' => array_map(static fn($point) => $point['label'], $series),
                'values' => array_map(static fn($point) => (float) $point['value'], $series),
                'seriesName' => 'Siparis',
                'colors' => ['#4680FF'],
                'summary' => [
                    [
                        'label' => 'Toplam',
                        'value' => number_format(array_sum(array_map(static fn($point) => (float) $point['value'], $series)), 0, ',', '.'),
                        'dotClass' => 'bg-primary',
                    ],
                ],
            ];
        }

        if ($source === 'sales_by_category') {
            $rows = $this->orderModel->getTopCategoriesByQuantity(6);
            $labels = [];
            $values = [];

            foreach ($rows as $row) {
                $labels[] = (string) ($row['category_name'] ?? 'Kategori');
                $values[] = (float) ($row['qty'] ?? 0);
            }

            return [
                'subtitle' => 'Kategori bazinda satis dagilimi',
                'labels' => $labels,
                'values' => $values,
                'seriesName' => 'Satis',
                'colors' => ['#4680FF', '#2CA87F', '#E58A00', '#DC2626', '#212529', '#7C3AED'],
                'summary' => $this->buildSummary($labels, $values, ['bg-primary', 'bg-success', 'bg-warning', 'bg-danger', 'bg-dark', 'bg-info']),
            ];
        }

        if ($source === 'print_vs_digital_sales') {
            $rows = $this->orderModel->getSalesByProductType();
            $labels = [];
            $values = [];

            foreach ($rows as $row) {
                $type = trim((string) ($row['product_type'] ?? 'unknown'));
                $labels[] = $type === 'digital' ? 'Dijital' : 'Baski';
                $values[] = (float) ($row['qty'] ?? 0);
            }

            return [
                'subtitle' => 'Baski ve dijital siparis dagilimi',
                'labels' => $labels,
                'values' => $values,
                'seriesName' => 'Satis',
                'colors' => ['#4680FF', '#2CA87F', '#E58A00'],
                'summary' => $this->buildSummary($labels, $values, ['bg-primary', 'bg-success', 'bg-warning']),
            ];
        }

        return null;
    }

    private function buildChartOptions(string $chartId, string $chartType, array $dataset, string $variant): array
    {
        if ($chartType === 'pie') {
            return [
                'chart' => [
                    'height' => 300,
                    'type' => $variant === 'pie_breakdown' ? 'pie' : 'donut',
                    'toolbar' => ['show' => false],
                ],
                'labels' => $dataset['labels'],
                'series' => $dataset['values'],
                'colors' => $dataset['colors'],
                'dataLabels' => ['enabled' => false],
                'legend' => [
                    'show' => true,
                    'position' => 'bottom',
                ],
                'plotOptions' => [
                    'pie' => [
                        'donut' => [
                            'size' => $variant === 'pie_breakdown' ? '0%' : '68%',
                        ],
                    ],
                ],
            ];
        }

        $base = [
            'chart' => [
                'height' => 300,
                'type' => $chartType,
                'toolbar' => ['show' => false],
            ],
            'series' => [[
                'name' => $dataset['seriesName'],
                'data' => $dataset['values'],
            ]],
            'colors' => $chartType === 'bar' ? $dataset['colors'] : [$dataset['colors'][0] ?? '#4680FF'],
            'dataLabels' => ['enabled' => false],
            'stroke' => [
                'curve' => 'smooth',
                'width' => $chartType === 'bar' ? 0 : 3,
            ],
            'grid' => [
                'strokeDashArray' => 4,
            ],
            'xaxis' => [
                'categories' => $dataset['labels'],
            ],
        ];

        if ($chartType === 'bar') {
            $base['plotOptions'] = [
                'bar' => [
                    'borderRadius' => 4,
                    'columnWidth' => '46%',
                    'distributed' => true,
                ],
            ];
        }

        if ($chartType === 'line') {
            $base['markers'] = [
                'size' => 4,
                'strokeWidth' => 0,
            ];
        }

        return $base;
    }

    private function buildSparklineOptions(string $chartId, string $color, array $series): array
    {
        return [
            'chart' => [
                'height' => 90,
                'type' => 'area',
                'sparkline' => ['enabled' => true],
                'toolbar' => ['show' => false],
            ],
            'series' => [[
                'name' => 'Siparis',
                'data' => $series,
            ]],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.35,
                    'opacityTo' => 0.05,
                    'stops' => [0, 90, 100],
                ],
            ],
            'colors' => [$color],
        ];
    }

    private function getOrderCountSeries(string $dateRange): array
    {
        $days = $dateRange === '30d' ? 30 : ($dateRange === '14d' ? 14 : 7);
        $start = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
        $rows = $this->orderModel->getDailyCounts($start, dash_today_end());
        $map = [];

        foreach ($rows as $row) {
            $map[(string) ($row['d'] ?? '')] = (float) ($row['c'] ?? 0);
        }

        return array_map(static function ($point) {
            $label = date('d M', strtotime((string) $point->label));

            return [
                'label' => $label,
                'value' => (float) $point->value,
            ];
        }, dash_fill_daily_series($map, $days));
    }

    private function getHistoricalOrderCountSeries(string $dateRange): array
    {
        $days = $dateRange === '30d' ? 30 : ($dateRange === '14d' ? 14 : 7);
        $latestOrderDate = $this->orderModel->getLatestOrderDate();

        if ($latestOrderDate === null) {
            return $this->getOrderCountSeries($dateRange);
        }

        $endTimestamp = strtotime(date('Y-m-d 23:59:59', strtotime($latestOrderDate)));
        $start = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days', $endTimestamp));
        $end = date('Y-m-d 23:59:59', $endTimestamp);
        $rows = $this->orderModel->getDailyCounts($start, $end);
        $map = [];

        foreach ($rows as $row) {
            $map[(string) ($row['d'] ?? '')] = (float) ($row['c'] ?? 0);
        }

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime('-' . $i . ' days', $endTimestamp));
            $series[] = [
                'label' => date('d M', strtotime($date)),
                'value' => (float) ($map[$date] ?? 0),
            ];
        }

        return $series;
    }

    private function buildSummary(array $labels, array $values, array $dotClasses): array
    {
        $summary = [];

        foreach ($labels as $index => $label) {
            $summary[] = [
                'label' => $label,
                'value' => number_format((float) ($values[$index] ?? 0), 0, ',', '.'),
                'dotClass' => $dotClasses[$index] ?? 'bg-primary',
            ];
        }

        return $summary;
    }

    private function parseConfig(mixed $rawConfig): array
    {
        if (! is_string($rawConfig) || trim($rawConfig) === '') {
            return [];
        }

        $decoded = json_decode($rawConfig, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function compareText(int $current, int $previous, string $suffix): string
    {
        if ($previous <= 0) {
            return $current > 0 ? 'Yeni veri var' : 'Karsilastirma yok';
        }

        $percent = (($current - $previous) / $previous) * 100;
        $prefix = $percent >= 0 ? '+' : '';

        return $prefix . number_format($percent, 1, ',', '.') . '% ' . $suffix;
    }

    private function compareClass(int $current, int $previous): string
    {
        if ($previous <= 0) {
            return 'text-primary';
        }

        if ($current > $previous) {
            return 'text-success';
        }

        if ($current < $previous) {
            return 'text-danger';
        }

        return 'text-muted';
    }

    private function statTheme(string $source): array
    {
        return match ($source) {
            'today_orders' => [
                'avatarClass' => 'bg-light-warning',
                'textClass' => 'text-warning',
                'iconClass' => 'ti ti-chart-line',
                'color' => '#E58A00',
            ],
            'weekly_orders' => [
                'avatarClass' => 'bg-light-success',
                'textClass' => 'text-success',
                'iconClass' => 'ti ti-calendar-stats',
                'color' => '#2CA87F',
            ],
            'pending_orders' => [
                'avatarClass' => 'bg-light-danger',
                'textClass' => 'text-danger',
                'iconClass' => 'ti ti-clock-hour-4',
                'color' => '#DC2626',
            ],
            default => [
                'avatarClass' => 'bg-light-primary',
                'textClass' => 'text-primary',
                'iconClass' => 'ti ti-shopping-cart',
                'color' => '#4680FF',
            ],
        };
    }

    private function resolveDatasetColors(
        array $labels,
        array $defaultColors,
        string $palette,
        array $customColors,
        array $categoryColors
    ): array {
        $baseColors = $this->paletteColors($palette, $defaultColors, $customColors);
        if ($labels === []) {
            return $baseColors;
        }

        $colors = [];
        foreach ($labels as $index => $label) {
            $label = (string) $label;
            $colors[] = $categoryColors[$label] ?? $baseColors[$index % max(1, count($baseColors))];
        }

        return $colors;
    }

    private function paletteColors(string $palette, array $defaultColors, array $customColors): array
    {
        if ($palette === 'custom' && $customColors !== []) {
            return $customColors;
        }

        return match ($palette) {
            'blue' => ['#4680FF', '#3B82F6', '#2563EB', '#60A5FA', '#1D4ED8', '#93C5FD'],
            'orange' => ['#F97316', '#FB923C', '#F59E0B', '#EA580C', '#FDBA74', '#FFEDD5'],
            'green' => ['#2CA87F', '#22C55E', '#16A34A', '#86EFAC', '#15803D', '#DCFCE7'],
            'purple' => ['#7C3AED', '#8B5CF6', '#A855F7', '#C084FC', '#6D28D9', '#E9D5FF'],
            'finance' => ['#0F172A', '#1D4ED8', '#2CA87F', '#14B8A6', '#38BDF8', '#CBD5E1'],
            'analytics' => ['#4680FF', '#06B6D4', '#7C3AED', '#EC4899', '#F97316', '#2CA87F'],
            'pastel' => ['#93C5FD', '#F9A8D4', '#C4B5FD', '#86EFAC', '#FCD34D', '#FDBA74'],
            'dark' => ['#111827', '#1F2937', '#374151', '#4B5563', '#6B7280', '#9CA3AF'],
            default => $defaultColors,
        };
    }

    private function palettePrimaryColor(string $palette, string $defaultColor, array $customColors): string
    {
        $paletteColors = $this->paletteColors($palette, [$defaultColor], $customColors);

        return (string) ($paletteColors[0] ?? $defaultColor);
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
            $result = [];
            foreach ($rawValue as $label => $color) {
                $label = trim((string) $label);
                $color = $this->normalizeHexColor((string) $color);
                if ($label !== '' && $color !== null) {
                    $result[$label] = $color;
                }
            }

            return $result;
        }

        $lines = preg_split('/[\r\n]+/', (string) $rawValue) ?: [];
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

    private function normalizeColorPalette(string $palette): string
    {
        $palette = strtolower(trim($palette));

        return in_array($palette, ['default', 'blue', 'orange', 'green', 'purple', 'finance', 'analytics', 'pastel', 'dark', 'custom'], true) ? $palette : 'default';
    }

    private function normalizeChartType(string $chartType): string
    {
        $chartType = strtolower(trim($chartType));

        return in_array($chartType, ['bar', 'line', 'pie'], true) ? $chartType : 'line';
    }

    private function normalizeVariant(string $blockType, string $variant): string
    {
        $variant = strtolower(trim($variant));

        return match ($blockType) {
            'stat_card' => in_array($variant, ['mini_spark', 'metric_tile', 'income_card'], true) ? $variant : 'mini_spark',
            'chart' => in_array($variant, ['line_trend', 'bar_overview', 'donut_summary', 'pie_breakdown'], true) ? $variant : 'line_trend',
            'note' => in_array($variant, ['simple_note', 'accent_note'], true) ? $variant : 'simple_note',
            default => $variant,
        };
    }

    private function normalizeDateRange(string $dateRange): string
    {
        $dateRange = strtolower(trim($dateRange));

        return in_array($dateRange, ['7d', '14d', '30d'], true) ? $dateRange : '7d';
    }

    private function periodRange(string $period): array
    {
        if ($period === 'summary') {
            return [null, null];
        }

        $referenceTimestamp = $this->detailReferenceTimestamp();

        return match ($period) {
            'daily' => [
                date('Y-m-d 00:00:00', $referenceTimestamp),
                date('Y-m-d 23:59:59', $referenceTimestamp),
            ],
            'monthly' => [
                date('Y-m-d 00:00:00', strtotime('-29 days', $referenceTimestamp)),
                date('Y-m-d 23:59:59', $referenceTimestamp),
            ],
            default => [
                date('Y-m-d 00:00:00', strtotime('-6 days', $referenceTimestamp)),
                date('Y-m-d 23:59:59', $referenceTimestamp),
            ],
        };
    }

    private function normalizeDetailPeriod(string $period): string
    {
        $period = strtolower(trim($period));

        return in_array($period, ['summary', 'daily', 'weekly', 'monthly'], true) ? $period : 'summary';
    }

    private function detailReferenceTimestamp(): int
    {
        $latestOrderDate = $this->orderModel->getLatestOrderDate();
        if ($latestOrderDate === null) {
            return time();
        }

        $timestamp = strtotime($latestOrderDate);

        return $timestamp ?: time();
    }

    private function normalizeProductTypeLabel(string $label): string
    {
        $label = strtolower(trim($label));

        return in_array($label, ['digital', 'dijital', 'ebook', 'e-book'], true) ? 'digital' : 'print';
    }

    private function formatProductTypeLabel(string $type): string
    {
        $type = strtolower(trim($type));

        return in_array($type, ['digital', 'dijital', 'ebook', 'e-book'], true) ? 'Dijital' : 'Baski';
    }

    private function formatDateTime(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '-';
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('d.m.Y H:i', $timestamp) : $value;
    }

    private function chartId(array $block, string $suffix): string
    {
        $id = preg_replace('/[^a-zA-Z0-9_-]/', '-', (string) ($block['id'] ?? uniqid('builder-', true)));

        return 'builder-' . $suffix . '-' . trim((string) $id, '-');
    }
}
