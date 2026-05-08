<?php

namespace App\Services;

use App\Models\BlockInstanceModel;
use App\Models\PageVersionModel;

class CartStorefrontBindingService
{
    private const OPTIONAL_TEXT_FIELDS = [
        'sayfa_alt_basligi',
        'kisa_aciklama',
        'sepet_urunleri_aciklama',
        'guvenli_odeme_kisa_notu',
        'bos_sepet_aciklama',
    ];

    private const BUILDER_DEFAULTS = [
        'sayfa_basligi' => 'Sepetim',
        'sepet_urunleri_baslik' => 'Sepetinizdeki Urunler',
        'sepet_ozeti_baslik' => 'Sepet Ozeti',
        'genel_toplam_basligi' => 'Genel Toplam',
        'odeme_sayfasina_git_buton_metni' => 'Odeme Sayfasina Git',
        'bos_sepet_baslik' => 'Sepetiniz Su Anda Bos',
        'alisverise_basla_buton_metni' => 'Alisverise Basla',
    ];

    public function __construct(
        private ?PageService $pageService = null,
        private ?PageVersionModel $pageVersionModel = null,
        private ?BlockInstanceModel $blockInstanceModel = null
    ) {
        $this->pageService = $this->pageService ?? new PageService();
        $this->pageVersionModel = $this->pageVersionModel ?? new PageVersionModel();
        $this->blockInstanceModel = $this->blockInstanceModel ?? new BlockInstanceModel();
    }

    public function getPublishedBinding(): array
    {
        $page = $this->pageService->findPageByCode('cart');
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
        $config = $this->normalizeConfig($rawConfig);
        $presenter = [
            'showBreadcrumb' => ! empty($config['breadcrumb_goster']),
            'pageTitle' => $this->resolveCustomText($config, 'sayfa_basligi'),
            'pageSubtitle' => $this->resolveOptionalText($rawConfig, $config, 'sayfa_alt_basligi'),
            'shortDescription' => $this->resolveOptionalText($rawConfig, $config, 'kisa_aciklama'),
            'itemsTitle' => $this->resolveCustomText($config, 'sepet_urunleri_baslik'),
            'itemsDescription' => $this->resolveOptionalText($rawConfig, $config, 'sepet_urunleri_aciklama'),
            'summaryTitle' => $this->resolveCustomText($config, 'sepet_ozeti_baslik'),
            'grandTotalLabel' => $this->resolveCustomText($config, 'genel_toplam_basligi'),
            'checkoutButtonText' => $this->resolveCustomText($config, 'odeme_sayfasina_git_buton_metni'),
            'securePaymentNote' => $this->resolveOptionalText($rawConfig, $config, 'guvenli_odeme_kisa_notu'),
            'emptyTitle' => $this->resolveCustomText($config, 'bos_sepet_baslik'),
            'emptyDescription' => $this->resolveOptionalText($rawConfig, $config, 'bos_sepet_aciklama'),
            'emptyButtonText' => $this->resolveCustomText($config, 'alisverise_basla_buton_metni'),
        ];

        return [
            'page' => $page,
            'publishedVersion' => $publishedVersion,
            'layoutBlock' => $layoutBlock,
            'hasPublishedConfig' => true,
            'config' => $config,
            'presenter' => $presenter,
            'supportedFields' => [
                'sayfa_basligi',
                'sayfa_alt_basligi',
                'breadcrumb_goster',
                'kisa_aciklama',
                'sepet_urunleri_baslik',
                'sepet_urunleri_aciklama',
                'sepet_ozeti_baslik',
                'genel_toplam_basligi',
                'odeme_sayfasina_git_buton_metni',
                'guvenli_odeme_kisa_notu',
                'bos_sepet_baslik',
                'bos_sepet_aciklama',
                'alisverise_basla_buton_metni',
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
            'presenter' => [],
            'supportedFields' => [],
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

            if ((string) ($block['zone'] ?? '') !== 'cart_layout') {
                continue;
            }

            $config = $this->decodeConfig($block['config_json'] ?? null);
            if (($config['_template'] ?? '') !== 'cart_layout') {
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

    private function normalizeConfig(array $config): array
    {
        $defaults = self::BUILDER_DEFAULTS;

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }

        $config['breadcrumb_goster'] = array_key_exists('breadcrumb_goster', $config)
            ? ! empty($config['breadcrumb_goster'])
            : true;

        foreach (array_keys(self::BUILDER_DEFAULTS) as $key) {
            $config[$key] = trim((string) ($config[$key] ?? ''));
        }

        foreach (self::OPTIONAL_TEXT_FIELDS as $key) {
            $config[$key] = trim((string) ($config[$key] ?? ''));
        }

        return $config;
    }

    private function resolveCustomText(array $config, string $key): ?string
    {
        $value = trim((string) ($config[$key] ?? ''));
        if ($value === '') {
            return null;
        }

        $default = self::BUILDER_DEFAULTS[$key] ?? null;
        if (is_string($default) && trim($default) === $value) {
            return null;
        }

        return $value;
    }

    private function resolveOptionalText(array $rawConfig, array $config, string $key): ?string
    {
        if (array_key_exists($key, $rawConfig)) {
            $value = trim((string) ($rawConfig[$key] ?? ''));
            return $value !== '' ? $value : null;
        }

        $value = trim((string) ($config[$key] ?? ''));
        return $value !== '' ? $value : null;
    }
}
