<?php

namespace App\Services;

use App\Models\BlockInstanceModel;
use App\Models\PageVersionModel;

class CheckoutStorefrontBindingService
{
    private const OPTIONAL_TEXT_FIELDS = [
        'sayfa_alt_basligi',
        'guven_kisa_notu',
        'adim_cubugu_aciklama',
        'teslimat_aciklama',
        'ayni_adres_notu',
        'zorunlu_alan_bilgi_metni',
        'odeme_aciklama',
        'guvenli_odeme_notu',
        'siparis_tipi_notu',
        'bilgi_kutusu_aciklama',
        'guven_mesaji',
        'alt_yardim_metni',
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
        $page = $this->pageService->findPageByCode('checkout');
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

        return [
            'page' => $page,
            'publishedVersion' => $publishedVersion,
            'layoutBlock' => $layoutBlock,
            'hasPublishedConfig' => true,
            'config' => $config,
            'presenter' => [
                'pageTitle' => $this->resolveTitleText($config, 'sayfa_basligi'),
                'pageSubtitle' => $this->resolveOptionalText($rawConfig, $config, 'sayfa_alt_basligi'),
                'showBreadcrumb' => ! empty($config['breadcrumb_goster']),
                'trustNote' => $this->resolveOptionalText($rawConfig, $config, 'guven_kisa_notu'),
                'deliveryTitle' => $this->resolveTitleText($config, 'teslimat_baslik'),
                'deliveryDescription' => $this->resolveOptionalText($rawConfig, $config, 'teslimat_aciklama'),
                'paymentTitle' => $this->resolveTitleText($config, 'odeme_baslik'),
                'paymentDescription' => $this->resolveOptionalText($rawConfig, $config, 'odeme_aciklama'),
                'securePaymentNote' => $this->resolveOptionalText($rawConfig, $config, 'guvenli_odeme_notu'),
                'summaryTitle' => $this->resolveTitleText($config, 'ozet_baslik'),
                'infoBoxTitle' => $this->resolveTitleText($config, 'bilgi_kutusu_baslik'),
                'infoBoxDescription' => $this->resolveOptionalText($rawConfig, $config, 'bilgi_kutusu_aciklama'),
                'trustMessage' => $this->resolveOptionalText($rawConfig, $config, 'guven_mesaji'),
                'completeButtonLabel' => $this->resolveTitleText($config, 'tamamla_buton_metni'),
                'helpText' => $this->resolveOptionalText($rawConfig, $config, 'alt_yardim_metni'),
            ],
            'supportedFields' => [
                'sayfa_basligi',
                'sayfa_alt_basligi',
                'breadcrumb_goster',
                'guven_kisa_notu',
                'teslimat_baslik',
                'teslimat_aciklama',
                'odeme_baslik',
                'odeme_aciklama',
                'guvenli_odeme_notu',
                'ozet_baslik',
                'bilgi_kutusu_baslik',
                'bilgi_kutusu_aciklama',
                'guven_mesaji',
                'tamamla_buton_metni',
                'alt_yardim_metni',
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

            if ((string) ($block['zone'] ?? '') !== 'checkout_layout') {
                continue;
            }

            $config = $this->decodeConfig($block['config_json'] ?? null);
            if (($config['_template'] ?? '') !== 'checkout_layout') {
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
        $defaults = [
            'sayfa_basligi' => '',
            'breadcrumb_goster' => true,
            'teslimat_baslik' => '',
            'odeme_baslik' => '',
            'ozet_baslik' => '',
            'bilgi_kutusu_baslik' => '',
            'tamamla_buton_metni' => '',
        ];

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }

        $config['breadcrumb_goster'] = array_key_exists('breadcrumb_goster', $config)
            ? ! empty($config['breadcrumb_goster'])
            : true;

        foreach (array_keys($defaults) as $key) {
            if ($key === 'breadcrumb_goster') {
                continue;
            }

            $config[$key] = trim((string) ($config[$key] ?? ''));
        }

        foreach (self::OPTIONAL_TEXT_FIELDS as $key) {
            $config[$key] = trim((string) ($config[$key] ?? ''));
        }

        return $config;
    }

    private function resolveTitleText(array $config, string $key): ?string
    {
        $value = trim((string) ($config[$key] ?? ''));

        return $value !== '' ? $value : null;
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
