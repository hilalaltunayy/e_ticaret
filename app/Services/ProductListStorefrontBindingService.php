<?php

namespace App\Services;

use App\Models\BlockInstanceModel;
use App\Models\PageVersionModel;

class ProductListStorefrontBindingService
{
    private const PRODUCT_LIST_DEFAULTS = [
        'sayfa_alt_basligi' => 'One cikan urunleri ve filtreleri duzenleyin',
        'banner_basligi' => 'Secili Kategori',
        'banner_alt_metni' => 'Listeleme sayfasinin ust alanini yonetin',
        'bos_sonuc_basligi' => 'Sonuc bulunamadi',
        'bos_sonuc_aciklamasi' => 'Filtreleri degistirerek tekrar deneyin.',
        'alt_aciklama_basligi' => 'Listeleme Aciklamasi',
        'alt_aciklama_metni' => 'Bu alan kategoriye ait aciklayici metinler icin kullanilir.',
    ];

    public function __construct(
        private ?PageService $pageService = null,
        private ?PageVersionModel $pageVersionModel = null,
        private ?BlockInstanceModel $blockInstanceModel = null,
        private ?ProductListPreviewRenderer $previewRenderer = null
    ) {
        $this->pageService = $this->pageService ?? new PageService();
        $this->pageVersionModel = $this->pageVersionModel ?? new PageVersionModel();
        $this->blockInstanceModel = $this->blockInstanceModel ?? new BlockInstanceModel();
        $this->previewRenderer = $this->previewRenderer ?? new ProductListPreviewRenderer();
    }

    public function getPublishedBinding(array $context = []): array
    {
        $page = $this->pageService->findPageByCode('product_list');
        if (! is_array($page) || empty($page['id'])) {
            return $this->emptyBinding();
        }

        $publishedVersion = $this->pageVersionModel->findPublishedByPageId((string) $page['id']);
        if (! is_array($publishedVersion) || empty($publishedVersion['id'])) {
            return $this->emptyBinding([
                'page' => $page,
            ]);
        }

        $layoutBlock = $this->findLayoutBlock((string) $publishedVersion['id']);
        if (! is_array($layoutBlock)) {
            return $this->emptyBinding([
                'page' => $page,
                'publishedVersion' => $publishedVersion,
            ]);
        }

        $rawConfig = $this->decodeConfig($layoutBlock['config_json'] ?? null);
        $preview = $this->previewRenderer->build($rawConfig);
        $config = is_array($preview['config'] ?? null) ? $preview['config'] : [];
        $sections = $this->mapSectionsByKey($preview['sections'] ?? []);

        return [
            'page' => $page,
            'publishedVersion' => $publishedVersion,
            'layoutBlock' => $layoutBlock,
            'hasPublishedConfig' => true,
            'config' => $config,
            'sections' => $sections,
            'presenter' => [
                'hero' => [
                    'enabled' => $this->isSectionActive($sections, 'sayfa_ust_alani'),
                    'showBreadcrumb' => $this->isSectionActive($sections, 'sayfa_ust_alani') && ! empty($config['breadcrumb_goster']),
                    'subtitle' => $this->resolveCustomText($config, 'sayfa_alt_basligi'),
                ],
                'banner' => [
                    'enabled' => $this->isSectionActive($sections, 'sayfa_ust_alani')
                        && ! empty($config['ust_banner_goster'])
                        && ($this->resolveCustomText($config, 'banner_basligi') !== null || $this->resolveCustomText($config, 'banner_alt_metni') !== null),
                    'title' => $this->resolveCustomText($config, 'banner_basligi'),
                    'subtitle' => $this->resolveCustomText($config, 'banner_alt_metni'),
                    'tone' => $this->normalizeTone((string) ($config['banner_tonu'] ?? 'soft'), ['light', 'dark', 'soft', 'accent'], 'soft'),
                ],
                'emptyState' => [
                    'title' => $this->isSectionActive($sections, 'bos_sonuc_alani')
                        ? $this->resolveCustomText($config, 'bos_sonuc_basligi')
                        : null,
                    'description' => $this->isSectionActive($sections, 'bos_sonuc_alani')
                        ? $this->resolveCustomText($config, 'bos_sonuc_aciklamasi')
                        : null,
                ],
                'footerDescription' => [
                    'enabled' => $this->isSectionActive($sections, 'alt_aciklama_alani')
                        && ! empty($config['alt_aciklama_goster'])
                        && ($this->resolveCustomText($config, 'alt_aciklama_basligi') !== null || $this->resolveCustomText($config, 'alt_aciklama_metni') !== null),
                    'title' => $this->resolveCustomText($config, 'alt_aciklama_basligi'),
                    'text' => $this->resolveCustomText($config, 'alt_aciklama_metni'),
                ],
                'supportedFields' => [
                    'section_sayfa_ust_alani_active',
                    'breadcrumb_goster',
                    'sayfa_alt_basligi',
                    'ust_banner_goster',
                    'banner_basligi',
                    'banner_alt_metni',
                    'banner_tonu',
                    'section_bos_sonuc_alani_active',
                    'bos_sonuc_basligi',
                    'bos_sonuc_aciklamasi',
                    'section_alt_aciklama_alani_active',
                    'alt_aciklama_goster',
                    'alt_aciklama_basligi',
                    'alt_aciklama_metni',
                ],
                'productCount' => max(0, (int) ($context['productCount'] ?? 0)),
            ],
        ];
    }

    private function emptyBinding(array $extra = []): array
    {
        return array_merge([
            'page' => null,
            'publishedVersion' => null,
            'layoutBlock' => null,
            'hasPublishedConfig' => false,
            'config' => [],
            'sections' => [],
            'presenter' => [],
        ], $extra);
    }

    private function findLayoutBlock(string $versionId): ?array
    {
        if ($versionId === '') {
            return null;
        }

        foreach ($this->blockInstanceModel->findDetailedByPageVersion($versionId) as $block) {
            if ((int) ($block['is_visible'] ?? 1) !== 1) {
                continue;
            }

            if ((string) ($block['block_type_code'] ?? '') !== 'product_list_layout') {
                continue;
            }

            return $block;
        }

        return null;
    }

    private function decodeConfig(mixed $configJson): array
    {
        if (! is_string($configJson) || trim($configJson) === '') {
            return [];
        }

        $decoded = json_decode($configJson, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function mapSectionsByKey(mixed $sections): array
    {
        if (! is_array($sections)) {
            return [];
        }

        $mapped = [];
        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            $key = trim((string) ($section['key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $mapped[$key] = $section;
        }

        return $mapped;
    }

    private function isSectionActive(array $sections, string $key): bool
    {
        return ! empty($sections[$key]['active']);
    }

    private function resolveCustomText(array $config, string $key): ?string
    {
        $value = trim((string) ($config[$key] ?? ''));
        if ($value === '') {
            return null;
        }

        $default = self::PRODUCT_LIST_DEFAULTS[$key] ?? null;
        if (is_string($default) && trim($default) === $value) {
            return null;
        }

        return $value;
    }
    private function normalizeTone(string $value, array $allowed, string $fallback): string
    {
        $value = trim($value);

        return in_array($value, $allowed, true) ? $value : $fallback;
    }
}
