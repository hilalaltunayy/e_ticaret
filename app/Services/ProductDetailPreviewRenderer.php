<?php

namespace App\Services;

class ProductDetailPreviewRenderer
{
    public function build(array $config): array
    {
        $config = $this->normalizeConfig($config);
        $sections = $this->orderedSections($config);
        $product = $this->sampleProduct();

        return [
            'config' => $config,
            'sections' => $sections,
            'product' => $product,
            'hasVisibleSections' => count(array_filter($sections, static fn (array $section): bool => ! empty($section['active']))) > 0,
            'visibleSectionCount' => count(array_filter($sections, static fn (array $section): bool => ! empty($section['active']))),
        ];
    }

    public function buildFromFormInput(array $input, array $baseConfig = []): array
    {
        $config = $this->normalizeConfig($baseConfig);
        $sectionDefaults = [
            'sayfa_ust_alani' => 1,
            'urun_ana_tanitim_alani' => 2,
            'fiyat_satin_alma_bilgi_alani' => 3,
            'urun_meta_bilgi_alani' => 4,
            'aciklama_icerik_alani' => 5,
            'yorum_puan_alani' => 6,
            'ilgili_urunler_cta_alani' => 7,
        ];

        foreach ($sectionDefaults as $key => $defaultOrder) {
            $config['sections'][$key] = [
                'active' => $this->boolFromInput($input, 'section_' . $key . '_active', ! empty($config['sections'][$key]['active'])),
                'order' => $this->intFromInput($input, 'section_' . $key . '_order', $defaultOrder, 1, 7),
            ];
        }

        foreach (array_keys($config) as $field) {
            if ($field === 'sections') {
                continue;
            }

            if (is_bool($config[$field])) {
                $config[$field] = $this->boolFromInput($input, $field, ! empty($config[$field]));
                continue;
            }

            if (array_key_exists($field, $input)) {
                $config[$field] = trim((string) $input[$field]);
            }
        }

        return $this->build($config);
    }

    private function orderedSections(array $config): array
    {
        $meta = [
            'sayfa_ust_alani' => ['title' => 'Sayfa Ust Alani', 'icon' => 'ti ti-layout-navbar'],
            'urun_ana_tanitim_alani' => ['title' => 'Urun Ana Tanitim Alani', 'icon' => 'ti ti-book'],
            'fiyat_satin_alma_bilgi_alani' => ['title' => 'Fiyat / Satin Alma Bilgi Alani', 'icon' => 'ti ti-cash'],
            'urun_meta_bilgi_alani' => ['title' => 'Urun Meta Bilgi Alani', 'icon' => 'ti ti-list-details'],
            'aciklama_icerik_alani' => ['title' => 'Aciklama / Icerik Alani', 'icon' => 'ti ti-align-box-left-middle'],
            'yorum_puan_alani' => ['title' => 'Yorum / Puan Alani', 'icon' => 'ti ti-message-stars'],
            'ilgili_urunler_cta_alani' => ['title' => 'Ilgili Urunler / CTA Alani', 'icon' => 'ti ti-layout-grid'],
        ];

        $sections = [];
        foreach ($meta as $key => $item) {
            $sections[] = [
                'key' => $key,
                'title' => $item['title'],
                'icon' => $item['icon'],
                'active' => ! empty($config['sections'][$key]['active']),
                'order' => (int) ($config['sections'][$key]['order'] ?? 99),
            ];
        }

        usort($sections, static fn (array $left, array $right): int => $left['order'] <=> $right['order']);

        return $sections;
    }

    private function sampleProduct(): array
    {
        return [
            'name' => 'BeAble Pro Ileri Seviye Calisma Kitabi',
            'author' => 'Hilal Y.',
            'format' => 'Basili + Dijital',
            'price' => 349.90,
            'old_price' => 399.90,
            'discount_rate' => 12,
            'rating' => 4.8,
            'review_count' => 126,
            'stock_message' => 'Stokta var',
            'isbn' => '978-625-0000-12-3',
            'language' => 'Turkce',
            'page_count' => '240',
            'publish_year' => '2026',
            'category' => 'Egitim',
            'tags' => 'Sinav, Gelisim, BeAble Pro',
        ];
    }

    private function normalizeConfig(array $config): array
    {
        $defaults = [
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

        $config['sections'] = is_array($config['sections'] ?? null) ? $config['sections'] : [];
        foreach ($defaults['sections'] as $key => $sectionDefaults) {
            $current = is_array($config['sections'][$key] ?? null) ? $config['sections'][$key] : [];
            $config['sections'][$key] = [
                'active' => array_key_exists('active', $current) ? (bool) $current['active'] : $sectionDefaults['active'],
                'order' => $this->intFromInput(['value' => $current['order'] ?? $sectionDefaults['order']], 'value', $sectionDefaults['order'], 1, 7),
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
                $config[$key] = trim((string) $config[$key]) !== '' ? trim((string) $config[$key]) : $value;
            } else {
                $config[$key] = ! empty($config[$key]);
            }
        }

        return $config;
    }

    private function boolFromInput(array $input, string $key, bool $fallback): bool
    {
        if (! array_key_exists($key, $input)) {
            return false;
        }

        return ! in_array($input[$key], [null, '', '0', 0, false], true);
    }

    private function intFromInput(array $input, string $key, int $fallback, int $min, int $max): int
    {
        if (! array_key_exists($key, $input)) {
            return $fallback;
        }

        $value = (int) $input[$key];

        return ($value < $min || $value > $max) ? $fallback : $value;
    }
}
