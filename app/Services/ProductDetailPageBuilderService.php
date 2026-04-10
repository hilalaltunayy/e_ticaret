<?php

namespace App\Services;

use App\Models\BlockInstanceModel;
use App\Models\BlockTypeModel;

class ProductDetailPageBuilderService
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
            return ['success' => false, 'error' => 'Urun detay taslagi bulunamadi.'];
        }

        if ((string) ($version['page_code'] ?? '') !== 'product_detail') {
            return ['success' => false, 'error' => 'Bu ayarlar yalnizca product_detail sayfasinda kullanilir.'];
        }

        if (! in_array((string) ($version['status'] ?? ''), ['DRAFT', 'SCHEDULED'], true)) {
            return ['success' => false, 'error' => 'Yalnizca draft veya scheduled version duzenlenebilir.'];
        }

        $layoutBlock = $this->ensureLayoutBlock($versionId);
        if (! is_array($layoutBlock)) {
            return ['success' => false, 'error' => 'Product detail layout blogu hazirlanamadi.'];
        }

        $updated = $this->blockInstanceModel->update((string) $layoutBlock['id'], [
            'config_json' => json_encode($this->buildConfigPayload($input), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if (! $updated) {
            return ['success' => false, 'error' => 'Product detail ayarlari kaydedilemedi.'];
        }

        return ['success' => true];
    }

    private function ensureLayoutBlock(string $versionId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        foreach ($this->blockInstanceModel->findDetailedByPageVersion($versionId) as $block) {
            if ((string) ($block['zone'] ?? '') !== 'product_detail_layout') {
                continue;
            }

            $config = $this->decodeJson((string) ($block['config_json'] ?? ''));
            if (($config['_template'] ?? '') === 'product_detail_layout') {
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
            'zone' => 'product_detail_layout',
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
            '_template' => 'product_detail_layout',
            'sections' => [
                'sayfa_ust_alani' => ['active' => true, 'order' => 1],
                'urun_ana_tanitim_alani' => ['active' => true, 'order' => 2],
                'fiyat_satin_alma_bilgi_alani' => ['active' => true, 'order' => 3],
                'urun_meta_bilgi_alani' => ['active' => true, 'order' => 4],
                'aciklama_icerik_alani' => ['active' => true, 'order' => 5],
                'yorum_puan_alani' => ['active' => true, 'order' => 6],
                'ilgili_urunler_cta_alani' => ['active' => true, 'order' => 7],
            ],
            'sayfa_basligi' => 'Urun Detayi',
            'sayfa_alt_basligi' => 'Urunun tum detaylarini, fiyat bilgisini ve satin alma alanini yonetin.',
            'breadcrumb_goster' => true,
            'kisa_aciklama' => 'Tanitim, fiyat ve guven notlari ayni sayfada dengeli bir bicimde sunulur.',
            'bilgi_kampanya_rozeti_metni' => 'Editorun Secimi',
            'urun_tanitim_baslik' => 'Urun Ana Tanitimi',
            'urun_tanitim_kisa_aciklama' => 'Kapak, format, yazar ve hizli satin alma aksiyonlarini bir arada sunun.',
            'kapak_galeri_goster' => true,
            'format_etiketi_goster' => true,
            'yazar_bilgisi_goster' => true,
            'favori_butonu_goster' => true,
            'sepete_ekle_buton_metni' => 'Sepete Ekle',
            'fiyat_satin_alma_baslik' => 'Fiyat ve Satin Alma Bilgisi',
            'fiyat_satin_alma_aciklama' => 'Kullaniciya fiyat, stok ve teslimat notlarini guven veren bir alanda gosterin.',
            'eski_fiyat_goster' => true,
            'indirim_rozeti_goster' => true,
            'stok_uygunluk_bilgisi_goster' => true,
            'dijital_erisim_kisa_notu' => 'Dijital urunlerde erisim satin alma sonrasi e-posta ile iletilir.',
            'teslimat_kargo_kisa_notu' => 'Basili urunlerde hazirlama ve kargo bilgisi siparis adiminda netlesir.',
            'guvenli_alisveris_kisa_notu' => 'Guvenli alisveris ve onayli odeme altyapisi ile korunur.',
            'urun_meta_bilgi_baslik' => 'Urun Meta Bilgileri',
            'isbn_goster' => true,
            'dil_goster' => true,
            'sayfa_sayisi_goster' => true,
            'yayin_yili_goster' => true,
            'format_goster' => true,
            'kategori_etiket_goster' => true,
            'aciklama_icerik_baslik' => 'Aciklama ve Icerik',
            'uzun_aciklama_basligi' => 'Urun Aciklamasi',
            'arka_kapak_tanitim_basligi' => 'Arka Kapak / Tanitim',
            'one_cikanlar_basligi' => 'One Cikanlar',
            'icerik_aciklama_notu' => 'Bu alan urunun neden tercih edilmesi gerektigini ozetler.',
            'yorum_puan_baslik' => 'Yorumlar ve Puanlar',
            'yorum_ozeti_metni' => 'Kullanicilarin geri bildirimleri satin alma kararini destekler.',
            'puan_ortalamasi_goster' => true,
            'yorum_sayisi_goster' => true,
            'yorum_yap_cagrisi_metni' => 'Deneyimini paylas ve diger kullanicilara yol goster.',
            'ilgili_urunler_cta_baslik' => 'Ilgili Urunler ve CTA',
            'ilgili_urunler_cta_aciklama' => 'Benzer urunler ve ek aksiyon alaniyla kesfetme akisini guclendir.',
            'benzer_urunler_basligi' => 'Benzer Urunler',
            'cta_buton_metni' => 'Hemen Incele',
            'guven_notu_kisa_bilgi' => 'Iade, destek ve satin alma guvencesi tek ekranda gorunur.',
        ];
    }

    private function buildConfigPayload(array $input): array
    {
        $defaults = $this->defaultConfig();
        $sections = $defaults['sections'];

        return [
            '_template' => 'product_detail_layout',
            'sections' => [
                'sayfa_ust_alani' => $this->buildSectionConfig($input, 'sayfa_ust_alani', $sections['sayfa_ust_alani']['order']),
                'urun_ana_tanitim_alani' => $this->buildSectionConfig($input, 'urun_ana_tanitim_alani', $sections['urun_ana_tanitim_alani']['order']),
                'fiyat_satin_alma_bilgi_alani' => $this->buildSectionConfig($input, 'fiyat_satin_alma_bilgi_alani', $sections['fiyat_satin_alma_bilgi_alani']['order']),
                'urun_meta_bilgi_alani' => $this->buildSectionConfig($input, 'urun_meta_bilgi_alani', $sections['urun_meta_bilgi_alani']['order']),
                'aciklama_icerik_alani' => $this->buildSectionConfig($input, 'aciklama_icerik_alani', $sections['aciklama_icerik_alani']['order']),
                'yorum_puan_alani' => $this->buildSectionConfig($input, 'yorum_puan_alani', $sections['yorum_puan_alani']['order']),
                'ilgili_urunler_cta_alani' => $this->buildSectionConfig($input, 'ilgili_urunler_cta_alani', $sections['ilgili_urunler_cta_alani']['order']),
            ],
            'sayfa_basligi' => trim((string) ($input['sayfa_basligi'] ?? $defaults['sayfa_basligi'])),
            'sayfa_alt_basligi' => trim((string) ($input['sayfa_alt_basligi'] ?? $defaults['sayfa_alt_basligi'])),
            'breadcrumb_goster' => $this->sanitizeBool($input['breadcrumb_goster'] ?? null),
            'kisa_aciklama' => trim((string) ($input['kisa_aciklama'] ?? $defaults['kisa_aciklama'])),
            'bilgi_kampanya_rozeti_metni' => trim((string) ($input['bilgi_kampanya_rozeti_metni'] ?? $defaults['bilgi_kampanya_rozeti_metni'])),
            'urun_tanitim_baslik' => trim((string) ($input['urun_tanitim_baslik'] ?? $defaults['urun_tanitim_baslik'])),
            'urun_tanitim_kisa_aciklama' => trim((string) ($input['urun_tanitim_kisa_aciklama'] ?? $defaults['urun_tanitim_kisa_aciklama'])),
            'kapak_galeri_goster' => $this->sanitizeBool($input['kapak_galeri_goster'] ?? null),
            'format_etiketi_goster' => $this->sanitizeBool($input['format_etiketi_goster'] ?? null),
            'yazar_bilgisi_goster' => $this->sanitizeBool($input['yazar_bilgisi_goster'] ?? null),
            'favori_butonu_goster' => $this->sanitizeBool($input['favori_butonu_goster'] ?? null),
            'sepete_ekle_buton_metni' => trim((string) ($input['sepete_ekle_buton_metni'] ?? $defaults['sepete_ekle_buton_metni'])),
            'fiyat_satin_alma_baslik' => trim((string) ($input['fiyat_satin_alma_baslik'] ?? $defaults['fiyat_satin_alma_baslik'])),
            'fiyat_satin_alma_aciklama' => trim((string) ($input['fiyat_satin_alma_aciklama'] ?? $defaults['fiyat_satin_alma_aciklama'])),
            'eski_fiyat_goster' => $this->sanitizeBool($input['eski_fiyat_goster'] ?? null),
            'indirim_rozeti_goster' => $this->sanitizeBool($input['indirim_rozeti_goster'] ?? null),
            'stok_uygunluk_bilgisi_goster' => $this->sanitizeBool($input['stok_uygunluk_bilgisi_goster'] ?? null),
            'dijital_erisim_kisa_notu' => trim((string) ($input['dijital_erisim_kisa_notu'] ?? $defaults['dijital_erisim_kisa_notu'])),
            'teslimat_kargo_kisa_notu' => trim((string) ($input['teslimat_kargo_kisa_notu'] ?? $defaults['teslimat_kargo_kisa_notu'])),
            'guvenli_alisveris_kisa_notu' => trim((string) ($input['guvenli_alisveris_kisa_notu'] ?? $defaults['guvenli_alisveris_kisa_notu'])),
            'urun_meta_bilgi_baslik' => trim((string) ($input['urun_meta_bilgi_baslik'] ?? $defaults['urun_meta_bilgi_baslik'])),
            'isbn_goster' => $this->sanitizeBool($input['isbn_goster'] ?? null),
            'dil_goster' => $this->sanitizeBool($input['dil_goster'] ?? null),
            'sayfa_sayisi_goster' => $this->sanitizeBool($input['sayfa_sayisi_goster'] ?? null),
            'yayin_yili_goster' => $this->sanitizeBool($input['yayin_yili_goster'] ?? null),
            'format_goster' => $this->sanitizeBool($input['format_goster'] ?? null),
            'kategori_etiket_goster' => $this->sanitizeBool($input['kategori_etiket_goster'] ?? null),
            'aciklama_icerik_baslik' => trim((string) ($input['aciklama_icerik_baslik'] ?? $defaults['aciklama_icerik_baslik'])),
            'uzun_aciklama_basligi' => trim((string) ($input['uzun_aciklama_basligi'] ?? $defaults['uzun_aciklama_basligi'])),
            'arka_kapak_tanitim_basligi' => trim((string) ($input['arka_kapak_tanitim_basligi'] ?? $defaults['arka_kapak_tanitim_basligi'])),
            'one_cikanlar_basligi' => trim((string) ($input['one_cikanlar_basligi'] ?? $defaults['one_cikanlar_basligi'])),
            'icerik_aciklama_notu' => trim((string) ($input['icerik_aciklama_notu'] ?? $defaults['icerik_aciklama_notu'])),
            'yorum_puan_baslik' => trim((string) ($input['yorum_puan_baslik'] ?? $defaults['yorum_puan_baslik'])),
            'yorum_ozeti_metni' => trim((string) ($input['yorum_ozeti_metni'] ?? $defaults['yorum_ozeti_metni'])),
            'puan_ortalamasi_goster' => $this->sanitizeBool($input['puan_ortalamasi_goster'] ?? null),
            'yorum_sayisi_goster' => $this->sanitizeBool($input['yorum_sayisi_goster'] ?? null),
            'yorum_yap_cagrisi_metni' => trim((string) ($input['yorum_yap_cagrisi_metni'] ?? $defaults['yorum_yap_cagrisi_metni'])),
            'ilgili_urunler_cta_baslik' => trim((string) ($input['ilgili_urunler_cta_baslik'] ?? $defaults['ilgili_urunler_cta_baslik'])),
            'ilgili_urunler_cta_aciklama' => trim((string) ($input['ilgili_urunler_cta_aciklama'] ?? $defaults['ilgili_urunler_cta_aciklama'])),
            'benzer_urunler_basligi' => trim((string) ($input['benzer_urunler_basligi'] ?? $defaults['benzer_urunler_basligi'])),
            'cta_buton_metni' => trim((string) ($input['cta_buton_metni'] ?? $defaults['cta_buton_metni'])),
            'guven_notu_kisa_bilgi' => trim((string) ($input['guven_notu_kisa_bilgi'] ?? $defaults['guven_notu_kisa_bilgi'])),
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
                'order' => $this->sanitizeInt($current['order'] ?? $sectionDefaults['order'], 1, 7, $sectionDefaults['order']),
            ];
        }

        unset($defaults['sections']);

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }

        foreach ($defaults as $key => $value) {
            if (is_string($value)) {
                $config[$key] = $this->fallbackText((string) ($config[$key] ?? ''), $value);
            } else {
                $config[$key] = ! empty($config[$key]);
            }
        }

        return $config;
    }

    private function buildSectionConfig(array $input, string $key, int $fallbackOrder): array
    {
        return [
            'active' => $this->sanitizeBool($input['section_' . $key . '_active'] ?? null),
            'order' => $this->sanitizeInt($input['section_' . $key . '_order'] ?? $fallbackOrder, 1, 7, $fallbackOrder),
        ];
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
