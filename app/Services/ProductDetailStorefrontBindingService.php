<?php

namespace App\Services;

use App\Models\BlockInstanceModel;
use App\Models\PageVersionModel;

class ProductDetailStorefrontBindingService
{
    private const LEGACY_DEFAULT_HELPER_TEXTS = [
        'Tanitim, fiyat ve guven notlari ayni sayfada dengeli bir bicimde sunulur.',
        'Kapak, format, yazar ve hizli satin alma aksiyonlarini bir arada sunun.',
        'Kullaniciya fiyat, stok ve teslimat notlarini guven veren bir alanda gosterin.',
        'Dijital urunlerde erisim satin alma sonrasi e-posta ile iletilir.',
        'Basili urunlerde hazirlama ve kargo bilgisi siparis adiminda netlesir.',
        'Guvenli alisveris ve onayli odeme altyapisi ile korunur.',
        'Iade, destek ve satin alma guvencesi tek ekranda gorunur.',
    ];

    private const OPTIONAL_TEXT_FIELDS = [
        'sayfa_alt_basligi',
        'kisa_aciklama',
        'bilgi_kampanya_rozeti_metni',
        'urun_tanitim_kisa_aciklama',
        'fiyat_satin_alma_aciklama',
        'dijital_erisim_kisa_notu',
        'teslimat_kargo_kisa_notu',
        'guvenli_alisveris_kisa_notu',
        'icerik_aciklama_notu',
        'yorum_ozeti_metni',
        'yorum_yap_cagrisi_metni',
        'ilgili_urunler_cta_aciklama',
        'guven_notu_kisa_bilgi',
    ];

    private const BUILDER_DEFAULTS = [
        'sayfa_basligi' => 'Urun Detayi',
        'urun_tanitim_baslik' => 'Urun Ana Tanitimi',
        'fiyat_satin_alma_baslik' => 'Fiyat ve Satin Alma Bilgisi',
        'urun_meta_bilgi_baslik' => 'Urun Meta Bilgileri',
        'aciklama_icerik_baslik' => 'Aciklama ve Icerik',
        'uzun_aciklama_basligi' => 'Urun Aciklamasi',
        'arka_kapak_tanitim_basligi' => 'Arka Kapak / Tanitim',
        'one_cikanlar_basligi' => 'One Cikanlar',
        'yorum_puan_baslik' => 'Yorumlar ve Puanlar',
        'ilgili_urunler_cta_baslik' => 'Ilgili Urunler ve CTA',
        'benzer_urunler_basligi' => 'Benzer Urunler',
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
        $page = $this->pageService->findPageByCode('product_detail');
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
                'showBreadcrumb' => ! empty($config['breadcrumb_goster']),
                'pageSubtitle' => $this->resolveOptionalText($rawConfig, $config, 'sayfa_alt_basligi'),
                'shortDescription' => $this->resolveOptionalText($rawConfig, $config, 'kisa_aciklama'),
                'promoBadgeText' => $this->resolveOptionalText($rawConfig, $config, 'bilgi_kampanya_rozeti_metni'),
                'heroSectionTitle' => $this->resolveCustomText($config, 'urun_tanitim_baslik'),
                'heroShortDescription' => $this->resolveOptionalText($rawConfig, $config, 'urun_tanitim_kisa_aciklama'),
                'contentSectionTitle' => $this->resolveCustomText($config, 'aciklama_icerik_baslik'),
                'metaSectionTitle' => $this->resolveCustomText($config, 'urun_meta_bilgi_baslik'),
                'longDescriptionTitle' => $this->resolveCustomText($config, 'uzun_aciklama_basligi'),
                'backCoverTitle' => $this->resolveCustomText($config, 'arka_kapak_tanitim_basligi'),
                'highlightsTitle' => $this->resolveCustomText($config, 'one_cikanlar_basligi'),
                'contentNote' => $this->resolveOptionalText($rawConfig, $config, 'icerik_aciklama_notu'),
                'reviewSectionTitle' => $this->resolveCustomText($config, 'yorum_puan_baslik'),
                'reviewSummaryText' => $this->resolveOptionalText($rawConfig, $config, 'yorum_ozeti_metni'),
                'reviewCalloutText' => $this->resolveOptionalText($rawConfig, $config, 'yorum_yap_cagrisi_metni'),
                'relatedSectionTitle' => $this->resolveCustomText($config, 'benzer_urunler_basligi'),
                'relatedSectionIntro' => $this->resolveOptionalText($rawConfig, $config, 'ilgili_urunler_cta_aciklama'),
                'purchaseNotes' => array_values(array_filter([
                    $this->resolveOptionalText($rawConfig, $config, 'fiyat_satin_alma_aciklama'),
                    $this->resolveOptionalText($rawConfig, $config, 'dijital_erisim_kisa_notu'),
                    $this->resolveOptionalText($rawConfig, $config, 'teslimat_kargo_kisa_notu'),
                    $this->resolveOptionalText($rawConfig, $config, 'guvenli_alisveris_kisa_notu'),
                    $this->resolveOptionalText($rawConfig, $config, 'guven_notu_kisa_bilgi'),
                ], static fn (?string $value): bool => is_string($value) && trim($value) !== '')),
            ],
            'supportedFields' => [
                'breadcrumb_goster',
                'sayfa_alt_basligi',
                'kisa_aciklama',
                'bilgi_kampanya_rozeti_metni',
                'urun_tanitim_baslik',
                'urun_tanitim_kisa_aciklama',
                'urun_meta_bilgi_baslik',
                'aciklama_icerik_baslik',
                'uzun_aciklama_basligi',
                'arka_kapak_tanitim_basligi',
                'one_cikanlar_basligi',
                'icerik_aciklama_notu',
                'yorum_puan_baslik',
                'yorum_ozeti_metni',
                'yorum_yap_cagrisi_metni',
                'benzer_urunler_basligi',
                'ilgili_urunler_cta_aciklama',
                'fiyat_satin_alma_aciklama',
                'dijital_erisim_kisa_notu',
                'teslimat_kargo_kisa_notu',
                'guvenli_alisveris_kisa_notu',
                'guven_notu_kisa_bilgi',
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

            if ((string) ($block['zone'] ?? '') !== 'product_detail_layout') {
                continue;
            }

            $config = $this->decodeConfig($block['config_json'] ?? null);
            if (($config['_template'] ?? '') !== 'product_detail_layout') {
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
        foreach (self::BUILDER_DEFAULTS as $key => $value) {
            if (! array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }

        $config['breadcrumb_goster'] = array_key_exists('breadcrumb_goster', $config)
            ? ! empty($config['breadcrumb_goster'])
            : true;

        foreach (self::BUILDER_DEFAULTS as $key => $value) {
            $config[$key] = trim((string) ($config[$key] ?? $value));
        }

        foreach (self::OPTIONAL_TEXT_FIELDS as $key) {
            $config[$key] = $this->sanitizeOptionalText($config[$key] ?? '');
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
            $value = $this->sanitizeOptionalText($rawConfig[$key] ?? '');
            return $value !== '' ? $value : null;
        }

        $value = $this->sanitizeOptionalText($config[$key] ?? '');
        return $value !== '' ? $value : null;
    }

    private function sanitizeOptionalText(mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        return in_array($value, self::LEGACY_DEFAULT_HELPER_TEXTS, true) ? '' : $value;
    }
}
