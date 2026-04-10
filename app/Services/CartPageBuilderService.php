<?php

namespace App\Services;

use App\Models\BlockInstanceModel;
use App\Models\BlockTypeModel;

class CartPageBuilderService
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
            return ['success' => false, 'error' => 'Sepet taslagi bulunamadi.'];
        }

        if ((string) ($version['page_code'] ?? '') !== 'cart') {
            return ['success' => false, 'error' => 'Bu ayarlar yalnizca cart sayfasinda kullanilir.'];
        }

        if (! in_array((string) ($version['status'] ?? ''), ['DRAFT', 'SCHEDULED'], true)) {
            return ['success' => false, 'error' => 'Yalnizca draft veya scheduled version duzenlenebilir.'];
        }

        $layoutBlock = $this->ensureLayoutBlock($versionId);
        if (! is_array($layoutBlock)) {
            return ['success' => false, 'error' => 'Cart layout blogu hazirlanamadi.'];
        }

        $updated = $this->blockInstanceModel->update((string) $layoutBlock['id'], [
            'config_json' => json_encode($this->buildConfigPayload($input), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if (! $updated) {
            return ['success' => false, 'error' => 'Cart ayarlari kaydedilemedi.'];
        }

        return ['success' => true];
    }

    private function ensureLayoutBlock(string $versionId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        foreach ($this->blockInstanceModel->findDetailedByPageVersion($versionId) as $block) {
            if ((string) ($block['zone'] ?? '') !== 'cart_layout') {
                continue;
            }

            $config = $this->decodeJson((string) ($block['config_json'] ?? ''));
            if (($config['_template'] ?? '') === 'cart_layout') {
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
            'zone' => 'cart_layout',
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
            '_template' => 'cart_layout',
            'sections' => [
                'sayfa_ust_alani' => ['active' => true, 'order' => 1],
                'sepet_urunleri_alani' => ['active' => true, 'order' => 2],
                'fiyat_guncelleme_uyari_alani' => ['active' => true, 'order' => 3],
                'stok_uygunluk_uyari_alani' => ['active' => true, 'order' => 4],
                'kupon_kampanya_alani' => ['active' => true, 'order' => 5],
                'sepet_ozeti_cta_alani' => ['active' => true, 'order' => 6],
                'bos_sepet_alani' => ['active' => true, 'order' => 7],
            ],
            'sayfa_basligi' => 'Sepetim',
            'sayfa_alt_basligi' => 'Sepetinizdeki urunleri kontrol edip odeme adimina gecin.',
            'breadcrumb_goster' => true,
            'kisa_aciklama' => 'Fiyat, stok ve kampanya bilgileri siparis oncesinde tekrar kontrol edilir.',
            'sepet_urunleri_baslik' => 'Sepetinizdeki Urunler',
            'sepet_urunleri_aciklama' => 'Urun adetlerini guncelleyebilir veya urunleri sepetinizden kaldirabilirsiniz.',
            'urun_gorseli_goster' => true,
            'format_etiketi_goster' => true,
            'adet_kontrolu_goster' => true,
            'kaldir_buton_metni' => 'Kaldir',
            'fiyat_uyari_baslik' => 'Fiyat Guncellemesi',
            'fiyat_uyari_aciklama' => 'Sepete eklediginiz andan sonra fiyati degisen urunler burada bilgilendirme kutusuyla vurgulanir.',
            'fiyat_farki_bilgi_kutusu_goster' => true,
            'eski_fiyat_etiketi' => 'Sepete eklendigindeki fiyat',
            'guncel_fiyat_etiketi' => 'Guncel fiyat',
            'toplam_guncelleme_notu' => 'Toplam tutar odeme adiminda en guncel fiyatlara gore yenilenir.',
            'stok_uyari_baslik' => 'Stok ve Uygunluk Kontrolu',
            'stok_uyari_aciklama' => 'Fiziksel urunlerde stok durumu mesaj bazli gosterilir, dijital urunlerde stok mesaji yer almaz.',
            'dusuk_stok_uyarisi_goster' => true,
            'tukenme_mesaji_goster' => true,
            'dusuk_stok_mesaj_sablonu' => 'Son {count} urun',
            'son_urun_mesaj_sablonu' => 'Son urun',
            'kupon_kampanya_baslik' => 'Kupon ve Kampanyalar',
            'kupon_kampanya_aciklama' => 'Aktif kampanya ve kupon bilgilerini odeme oncesinde gozden gecirin.',
            'kupon_alani_goster' => true,
            'kampanya_bilgi_notu' => 'Bu sipariste uygulanabilen kampanyalar odeme adiminda otomatik degerlendirilir.',
            'ucretsiz_kargo_bilgi_notu' => '500 TL ve uzeri alisverislerde ucretsiz kargo firsati sunulur.',
            'sepet_ozeti_baslik' => 'Sepet Ozeti',
            'ara_toplam_goster' => true,
            'indirim_goster' => true,
            'kargo_goster' => true,
            'genel_toplam_basligi' => 'Genel Toplam',
            'odeme_sayfasina_git_buton_metni' => 'Odeme Sayfasina Git',
            'guvenli_odeme_kisa_notu' => 'Guvenli odeme adiminda kart ve adres bilgileriniz korunur.',
            'bos_sepet_baslik' => 'Sepetiniz Su Anda Bos',
            'bos_sepet_aciklama' => 'Katalogtan urun ekleyerek alisverise devam edebilirsiniz.',
            'alisverise_basla_buton_metni' => 'Alisverise Basla',
        ];
    }

    private function buildConfigPayload(array $input): array
    {
        $defaults = $this->defaultConfig();
        $sectionDefaults = $defaults['sections'];

        return [
            '_template' => 'cart_layout',
            'sections' => [
                'sayfa_ust_alani' => $this->buildSectionConfig($input, 'sayfa_ust_alani', $sectionDefaults, 7),
                'sepet_urunleri_alani' => $this->buildSectionConfig($input, 'sepet_urunleri_alani', $sectionDefaults, 7),
                'fiyat_guncelleme_uyari_alani' => $this->buildSectionConfig($input, 'fiyat_guncelleme_uyari_alani', $sectionDefaults, 7),
                'stok_uygunluk_uyari_alani' => $this->buildSectionConfig($input, 'stok_uygunluk_uyari_alani', $sectionDefaults, 7),
                'kupon_kampanya_alani' => $this->buildSectionConfig($input, 'kupon_kampanya_alani', $sectionDefaults, 7),
                'sepet_ozeti_cta_alani' => $this->buildSectionConfig($input, 'sepet_ozeti_cta_alani', $sectionDefaults, 7),
                'bos_sepet_alani' => $this->buildSectionConfig($input, 'bos_sepet_alani', $sectionDefaults, 7),
            ],
            'sayfa_basligi' => trim((string) ($input['sayfa_basligi'] ?? $defaults['sayfa_basligi'])),
            'sayfa_alt_basligi' => trim((string) ($input['sayfa_alt_basligi'] ?? $defaults['sayfa_alt_basligi'])),
            'breadcrumb_goster' => $this->sanitizeBool($input['breadcrumb_goster'] ?? null),
            'kisa_aciklama' => trim((string) ($input['kisa_aciklama'] ?? $defaults['kisa_aciklama'])),
            'sepet_urunleri_baslik' => trim((string) ($input['sepet_urunleri_baslik'] ?? $defaults['sepet_urunleri_baslik'])),
            'sepet_urunleri_aciklama' => trim((string) ($input['sepet_urunleri_aciklama'] ?? $defaults['sepet_urunleri_aciklama'])),
            'urun_gorseli_goster' => $this->sanitizeBool($input['urun_gorseli_goster'] ?? null),
            'format_etiketi_goster' => $this->sanitizeBool($input['format_etiketi_goster'] ?? null),
            'adet_kontrolu_goster' => $this->sanitizeBool($input['adet_kontrolu_goster'] ?? null),
            'kaldir_buton_metni' => trim((string) ($input['kaldir_buton_metni'] ?? $defaults['kaldir_buton_metni'])),
            'fiyat_uyari_baslik' => trim((string) ($input['fiyat_uyari_baslik'] ?? $defaults['fiyat_uyari_baslik'])),
            'fiyat_uyari_aciklama' => trim((string) ($input['fiyat_uyari_aciklama'] ?? $defaults['fiyat_uyari_aciklama'])),
            'fiyat_farki_bilgi_kutusu_goster' => $this->sanitizeBool($input['fiyat_farki_bilgi_kutusu_goster'] ?? null),
            'eski_fiyat_etiketi' => trim((string) ($input['eski_fiyat_etiketi'] ?? $defaults['eski_fiyat_etiketi'])),
            'guncel_fiyat_etiketi' => trim((string) ($input['guncel_fiyat_etiketi'] ?? $defaults['guncel_fiyat_etiketi'])),
            'toplam_guncelleme_notu' => trim((string) ($input['toplam_guncelleme_notu'] ?? $defaults['toplam_guncelleme_notu'])),
            'stok_uyari_baslik' => trim((string) ($input['stok_uyari_baslik'] ?? $defaults['stok_uyari_baslik'])),
            'stok_uyari_aciklama' => trim((string) ($input['stok_uyari_aciklama'] ?? $defaults['stok_uyari_aciklama'])),
            'dusuk_stok_uyarisi_goster' => $this->sanitizeBool($input['dusuk_stok_uyarisi_goster'] ?? null),
            'tukenme_mesaji_goster' => $this->sanitizeBool($input['tukenme_mesaji_goster'] ?? null),
            'dusuk_stok_mesaj_sablonu' => trim((string) ($input['dusuk_stok_mesaj_sablonu'] ?? $defaults['dusuk_stok_mesaj_sablonu'])),
            'son_urun_mesaj_sablonu' => trim((string) ($input['son_urun_mesaj_sablonu'] ?? $defaults['son_urun_mesaj_sablonu'])),
            'kupon_kampanya_baslik' => trim((string) ($input['kupon_kampanya_baslik'] ?? $defaults['kupon_kampanya_baslik'])),
            'kupon_kampanya_aciklama' => trim((string) ($input['kupon_kampanya_aciklama'] ?? $defaults['kupon_kampanya_aciklama'])),
            'kupon_alani_goster' => $this->sanitizeBool($input['kupon_alani_goster'] ?? null),
            'kampanya_bilgi_notu' => trim((string) ($input['kampanya_bilgi_notu'] ?? $defaults['kampanya_bilgi_notu'])),
            'ucretsiz_kargo_bilgi_notu' => trim((string) ($input['ucretsiz_kargo_bilgi_notu'] ?? $defaults['ucretsiz_kargo_bilgi_notu'])),
            'sepet_ozeti_baslik' => trim((string) ($input['sepet_ozeti_baslik'] ?? $defaults['sepet_ozeti_baslik'])),
            'ara_toplam_goster' => $this->sanitizeBool($input['ara_toplam_goster'] ?? null),
            'indirim_goster' => $this->sanitizeBool($input['indirim_goster'] ?? null),
            'kargo_goster' => $this->sanitizeBool($input['kargo_goster'] ?? null),
            'genel_toplam_basligi' => trim((string) ($input['genel_toplam_basligi'] ?? $defaults['genel_toplam_basligi'])),
            'odeme_sayfasina_git_buton_metni' => trim((string) ($input['odeme_sayfasina_git_buton_metni'] ?? $defaults['odeme_sayfasina_git_buton_metni'])),
            'guvenli_odeme_kisa_notu' => trim((string) ($input['guvenli_odeme_kisa_notu'] ?? $defaults['guvenli_odeme_kisa_notu'])),
            'bos_sepet_baslik' => trim((string) ($input['bos_sepet_baslik'] ?? $defaults['bos_sepet_baslik'])),
            'bos_sepet_aciklama' => trim((string) ($input['bos_sepet_aciklama'] ?? $defaults['bos_sepet_aciklama'])),
            'alisverise_basla_buton_metni' => trim((string) ($input['alisverise_basla_buton_metni'] ?? $defaults['alisverise_basla_buton_metni'])),
        ];
    }

    private function buildSectionConfig(array $input, string $key, array $defaults, int $maxOrder): array
    {
        $fallback = $defaults[$key] ?? ['active' => true, 'order' => 1];

        return [
            'active' => $this->sanitizeBool($input['section_' . $key . '_active'] ?? null),
            'order' => $this->sanitizeInt($input['section_' . $key . '_order'] ?? $fallback['order'], 1, $maxOrder, (int) $fallback['order']),
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
                'order' => $this->sanitizeInt($current['order'] ?? $sectionDefaults['order'], 1, 7, (int) $sectionDefaults['order']),
            ];
        }

        unset($defaults['sections']);

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }

        foreach ([
            'sayfa_basligi', 'sayfa_alt_basligi', 'kisa_aciklama', 'sepet_urunleri_baslik',
            'sepet_urunleri_aciklama', 'kaldir_buton_metni', 'fiyat_uyari_baslik',
            'fiyat_uyari_aciklama', 'eski_fiyat_etiketi', 'guncel_fiyat_etiketi',
            'toplam_guncelleme_notu', 'stok_uyari_baslik', 'stok_uyari_aciklama',
            'dusuk_stok_mesaj_sablonu', 'son_urun_mesaj_sablonu', 'kupon_kampanya_baslik',
            'kupon_kampanya_aciklama', 'kampanya_bilgi_notu', 'ucretsiz_kargo_bilgi_notu',
            'sepet_ozeti_baslik', 'genel_toplam_basligi', 'odeme_sayfasina_git_buton_metni',
            'guvenli_odeme_kisa_notu', 'bos_sepet_baslik', 'bos_sepet_aciklama',
            'alisverise_basla_buton_metni',
        ] as $key) {
            $config[$key] = $this->fallbackText((string) ($config[$key] ?? ''), (string) $defaults[$key]);
        }

        foreach ([
            'breadcrumb_goster', 'urun_gorseli_goster', 'format_etiketi_goster',
            'adet_kontrolu_goster', 'fiyat_farki_bilgi_kutusu_goster', 'dusuk_stok_uyarisi_goster',
            'tukenme_mesaji_goster', 'kupon_alani_goster', 'ara_toplam_goster',
            'indirim_goster', 'kargo_goster',
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
