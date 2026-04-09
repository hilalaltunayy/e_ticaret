<?php

namespace App\Services;

use App\Models\BlockInstanceModel;
use App\Models\BlockTypeModel;

class CheckoutPageBuilderService
{
    public function __construct(
        private ?PageVersionService $pageVersionService = null,
        private ?BlockInstanceModel $blockInstanceModel = null,
        private ?BlockTypeModel $blockTypeModel = null
    ) {
        $this->pageVersionService = $this->pageVersionService ?? new PageVersionService();
        $this->blockInstanceModel = $this->blockInstanceModel ?? new BlockInstanceModel();
        $this->blockTypeModel = $this->blockTypeModel ?? new BlockTypeModel();
    }

    public function getBuilderState(string $versionId): array
    {
        $layoutBlock = $this->ensureLayoutBlock($versionId);

        return [
            'layoutBlock' => $layoutBlock,
            'config' => $this->normalizeConfig(
                is_array($layoutBlock)
                    ? $this->decodeJson((string) ($layoutBlock['config_json'] ?? ''))
                    : []
            ),
        ];
    }

    public function updateConfig(string $versionId, array $input): array
    {
        $version = $this->pageVersionService->findVersionDetail($versionId);
        if (! is_array($version)) {
            return ['success' => false, 'error' => 'Checkout taslagi bulunamadi.'];
        }

        if ((string) ($version['page_code'] ?? '') !== 'checkout') {
            return ['success' => false, 'error' => 'Bu ayarlar yalnizca checkout sayfasinda kullanilir.'];
        }

        if (! in_array((string) ($version['status'] ?? ''), ['DRAFT', 'SCHEDULED'], true)) {
            return ['success' => false, 'error' => 'Yalnizca draft veya scheduled version duzenlenebilir.'];
        }

        $layoutBlock = $this->ensureLayoutBlock($versionId);
        if (! is_array($layoutBlock)) {
            return ['success' => false, 'error' => 'Checkout layout blogu hazirlanamadi.'];
        }

        $updated = $this->blockInstanceModel->update((string) $layoutBlock['id'], [
            'config_json' => json_encode($this->buildConfigPayload($input), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if (! $updated) {
            return ['success' => false, 'error' => 'Checkout ayarlari kaydedilemedi.'];
        }

        return ['success' => true];
    }

    private function ensureLayoutBlock(string $versionId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        foreach ($this->blockInstanceModel->findDetailedByPageVersion($versionId) as $block) {
            if ((string) ($block['zone'] ?? '') !== 'checkout_layout') {
                continue;
            }

            $config = $this->decodeJson((string) ($block['config_json'] ?? ''));
            if (($config['_template'] ?? '') === 'checkout_layout') {
                return $block;
            }
        }

        $blockType = $this->blockTypeModel->findByCode('notice');
        if (! is_array($blockType)) {
            return null;
        }

        $newId = $this->blockInstanceModel->insert([
            'owner_type' => 'PAGE',
            'owner_version_id' => $versionId,
            'block_type_id' => $blockType['id'],
            'zone' => 'checkout_layout',
            'position_x' => 0,
            'position_y' => 0,
            'width' => 12,
            'height' => 1,
            'order_index' => 0,
            'config_json' => json_encode($this->defaultConfig(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_visible' => 1,
        ], true);

        if (! $newId) {
            return null;
        }

        return $this->blockInstanceModel->findByIdDetailed((string) $newId);
    }

    private function defaultConfig(): array
    {
        return [
            '_template' => 'checkout_layout',
            'sections' => [
                'sayfa_ust_alani' => ['active' => true, 'order' => 1],
                'adim_cubugu' => ['active' => true, 'order' => 2],
                'teslimat_fatura_alani' => ['active' => true, 'order' => 3],
                'odeme_yontemi_alani' => ['active' => true, 'order' => 4],
                'siparis_ozeti_alani' => ['active' => true, 'order' => 5],
                'bilgilendirme_guven_cta_alani' => ['active' => true, 'order' => 6],
            ],
            'sayfa_basligi' => 'Guvenli Checkout',
            'sayfa_alt_basligi' => 'Teslimat ve odeme adimlarini tamamlayarak siparisinizi olusturun.',
            'breadcrumb_goster' => true,
            'guven_kisa_notu' => 'SSL korumasi aktif, bilgileriniz guvende islenir.',
            'adim_cubugu_aciklama' => 'Teslimat, odeme ve onay adimlarini sirasiyla tamamlayin.',
            'adim_cubugu_gorunur' => true,
            'teslimat_baslik' => 'Teslimat ve Fatura Bilgileri',
            'teslimat_aciklama' => 'Adres ve iletisim bilgilerinizi eksiksiz girin.',
            'ayni_adres_notu' => 'Fatura adresi teslimat adresi ile ayni olabilir.',
            'zorunlu_alan_bilgi_metni' => '* ile isaretli alanlar zorunludur.',
            'odeme_baslik' => 'Odeme Yontemi',
            'odeme_aciklama' => 'Tercih ettiginiz odeme yontemini secin.',
            'guvenli_odeme_notu' => 'Kart bilgileriniz PCI uyumlu guvenli altyapida islenir.',
            'kart_logo_goster' => true,
            'guven_rozeti_goster' => true,
            'ozet_baslik' => 'Siparis Ozeti',
            'kupon_alani_goster' => true,
            'indirim_satiri_goster' => true,
            'kargo_satiri_goster' => true,
            'siparis_tipi_notu' => 'Dijital urunlerde teslimat e-posta ile saglanir.',
            'bilgi_kutusu_baslik' => 'Siparisinizi Tamamlamadan Once',
            'bilgi_kutusu_aciklama' => 'Adres, odeme ve siparis ozeti bilgilerinizi son kez kontrol edin.',
            'guven_mesaji' => '7/24 destek ve guvenli odeme korumasi ile yaninizdayiz.',
            'tamamla_buton_metni' => 'Siparisi Tamamla',
            'alt_yardim_metni' => 'Bir sorun olursa destek ekibimiz yardim icin hazir.',
        ];
    }

    private function buildConfigPayload(array $input): array
    {
        $defaults = $this->defaultConfig();

        return [
            '_template' => 'checkout_layout',
            'sections' => [
                'sayfa_ust_alani' => [
                    'active' => $this->sanitizeBool($input['section_sayfa_ust_alani_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_sayfa_ust_alani_order'] ?? 1, 1, 6, 1),
                ],
                'adim_cubugu' => [
                    'active' => $this->sanitizeBool($input['section_adim_cubugu_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_adim_cubugu_order'] ?? 2, 1, 6, 2),
                ],
                'teslimat_fatura_alani' => [
                    'active' => $this->sanitizeBool($input['section_teslimat_fatura_alani_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_teslimat_fatura_alani_order'] ?? 3, 1, 6, 3),
                ],
                'odeme_yontemi_alani' => [
                    'active' => $this->sanitizeBool($input['section_odeme_yontemi_alani_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_odeme_yontemi_alani_order'] ?? 4, 1, 6, 4),
                ],
                'siparis_ozeti_alani' => [
                    'active' => $this->sanitizeBool($input['section_siparis_ozeti_alani_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_siparis_ozeti_alani_order'] ?? 5, 1, 6, 5),
                ],
                'bilgilendirme_guven_cta_alani' => [
                    'active' => $this->sanitizeBool($input['section_bilgilendirme_guven_cta_alani_active'] ?? null),
                    'order' => $this->sanitizeInt($input['section_bilgilendirme_guven_cta_alani_order'] ?? 6, 1, 6, 6),
                ],
            ],
            'sayfa_basligi' => trim((string) ($input['sayfa_basligi'] ?? $defaults['sayfa_basligi'])),
            'sayfa_alt_basligi' => trim((string) ($input['sayfa_alt_basligi'] ?? $defaults['sayfa_alt_basligi'])),
            'breadcrumb_goster' => $this->sanitizeBool($input['breadcrumb_goster'] ?? null),
            'guven_kisa_notu' => trim((string) ($input['guven_kisa_notu'] ?? $defaults['guven_kisa_notu'])),
            'adim_cubugu_aciklama' => trim((string) ($input['adim_cubugu_aciklama'] ?? $defaults['adim_cubugu_aciklama'])),
            'adim_cubugu_gorunur' => $this->sanitizeBool($input['adim_cubugu_gorunur'] ?? null),
            'teslimat_baslik' => trim((string) ($input['teslimat_baslik'] ?? $defaults['teslimat_baslik'])),
            'teslimat_aciklama' => trim((string) ($input['teslimat_aciklama'] ?? $defaults['teslimat_aciklama'])),
            'ayni_adres_notu' => trim((string) ($input['ayni_adres_notu'] ?? $defaults['ayni_adres_notu'])),
            'zorunlu_alan_bilgi_metni' => trim((string) ($input['zorunlu_alan_bilgi_metni'] ?? $defaults['zorunlu_alan_bilgi_metni'])),
            'odeme_baslik' => trim((string) ($input['odeme_baslik'] ?? $defaults['odeme_baslik'])),
            'odeme_aciklama' => trim((string) ($input['odeme_aciklama'] ?? $defaults['odeme_aciklama'])),
            'guvenli_odeme_notu' => trim((string) ($input['guvenli_odeme_notu'] ?? $defaults['guvenli_odeme_notu'])),
            'kart_logo_goster' => $this->sanitizeBool($input['kart_logo_goster'] ?? null),
            'guven_rozeti_goster' => $this->sanitizeBool($input['guven_rozeti_goster'] ?? null),
            'ozet_baslik' => trim((string) ($input['ozet_baslik'] ?? $defaults['ozet_baslik'])),
            'kupon_alani_goster' => $this->sanitizeBool($input['kupon_alani_goster'] ?? null),
            'indirim_satiri_goster' => $this->sanitizeBool($input['indirim_satiri_goster'] ?? null),
            'kargo_satiri_goster' => $this->sanitizeBool($input['kargo_satiri_goster'] ?? null),
            'siparis_tipi_notu' => trim((string) ($input['siparis_tipi_notu'] ?? $defaults['siparis_tipi_notu'])),
            'bilgi_kutusu_baslik' => trim((string) ($input['bilgi_kutusu_baslik'] ?? $defaults['bilgi_kutusu_baslik'])),
            'bilgi_kutusu_aciklama' => trim((string) ($input['bilgi_kutusu_aciklama'] ?? $defaults['bilgi_kutusu_aciklama'])),
            'guven_mesaji' => trim((string) ($input['guven_mesaji'] ?? $defaults['guven_mesaji'])),
            'tamamla_buton_metni' => trim((string) ($input['tamamla_buton_metni'] ?? $defaults['tamamla_buton_metni'])),
            'alt_yardim_metni' => trim((string) ($input['alt_yardim_metni'] ?? $defaults['alt_yardim_metni'])),
        ];
    }

    private function normalizeConfig(array $config): array
    {
        $defaults = $this->defaultConfig();
        $config['sections'] = is_array($config['sections'] ?? null) ? $config['sections'] : [];

        foreach ($defaults['sections'] as $key => $sectionDefaults) {
            $current = is_array($config['sections'][$key] ?? null) ? $config['sections'][$key] : [];
            $config['sections'][$key] = [
                'active' => array_key_exists('active', $current) ? (bool) $current['active'] : $sectionDefaults['active'],
                'order' => $this->sanitizeInt($current['order'] ?? $sectionDefaults['order'], 1, 6, $sectionDefaults['order']),
            ];
        }

        unset($defaults['sections']);

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }

        foreach ([
            'sayfa_basligi',
            'sayfa_alt_basligi',
            'guven_kisa_notu',
            'adim_cubugu_aciklama',
            'teslimat_baslik',
            'teslimat_aciklama',
            'ayni_adres_notu',
            'zorunlu_alan_bilgi_metni',
            'odeme_baslik',
            'odeme_aciklama',
            'guvenli_odeme_notu',
            'ozet_baslik',
            'siparis_tipi_notu',
            'bilgi_kutusu_baslik',
            'bilgi_kutusu_aciklama',
            'guven_mesaji',
            'tamamla_buton_metni',
            'alt_yardim_metni',
        ] as $key) {
            $config[$key] = $this->fallbackText((string) ($config[$key] ?? ''), (string) $defaults[$key]);
        }

        foreach ([
            'breadcrumb_goster',
            'adim_cubugu_gorunur',
            'kart_logo_goster',
            'guven_rozeti_goster',
            'kupon_alani_goster',
            'indirim_satiri_goster',
            'kargo_satiri_goster',
        ] as $key) {
            $config[$key] = ! empty($config[$key]);
        }

        return $config;
    }

    private function tablesReady(): bool
    {
        $db = db_connect();

        return $db->tableExists('page_versions')
            && $db->tableExists('block_instances')
            && $db->tableExists('block_types');
    }

    private function decodeJson(string $json): array
    {
        if (trim($json) === '') {
            return [];
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function sanitizeBool(mixed $value): bool
    {
        return $value !== null && $value !== '' && $value !== '0';
    }

    private function sanitizeInt(mixed $value, int $min, int $max, int $fallback): int
    {
        $value = (int) $value;

        if ($value < $min || $value > $max) {
            return $fallback;
        }

        return $value;
    }

    private function fallbackText(string $value, string $fallback): string
    {
        $value = trim($value);

        return $value === '' ? $fallback : $value;
    }
}
