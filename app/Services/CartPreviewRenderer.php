<?php

namespace App\Services;

class CartPreviewRenderer
{
    public function build(array $config): array
    {
        $config = $this->normalizeConfig($config);
        $sections = $this->orderedSections($config);
        $sampleItems = $this->sampleItems($config);

        return [
            'config' => $config,
            'sections' => $sections,
            'sampleItems' => $sampleItems,
            'hasVisibleSections' => count(array_filter($sections, static fn (array $section): bool => ! empty($section['active']))) > 0,
            'visibleSectionCount' => count(array_filter($sections, static fn (array $section): bool => ! empty($section['active']))),
            'pricingDeltaCount' => count(array_filter($sampleItems, static fn (array $item): bool => ! empty($item['has_price_change']))),
            'stockWarningCount' => count(array_filter($sampleItems, static fn (array $item): bool => trim((string) ($item['stock_message'] ?? '')) !== '')),
        ];
    }

    public function buildFromFormInput(array $input, array $baseConfig = []): array
    {
        $config = $this->normalizeConfig($baseConfig);
        $sectionDefaults = [
            'sayfa_ust_alani' => 1,
            'sepet_urunleri_alani' => 2,
            'fiyat_guncelleme_uyari_alani' => 3,
            'stok_uygunluk_uyari_alani' => 4,
            'kupon_kampanya_alani' => 5,
            'sepet_ozeti_cta_alani' => 6,
            'bos_sepet_alani' => 7,
        ];

        foreach ($sectionDefaults as $key => $defaultOrder) {
            $config['sections'][$key] = [
                'active' => $this->boolFromInput($input, 'section_' . $key . '_active', ! empty($config['sections'][$key]['active'])),
                'order' => $this->intFromInput($input, 'section_' . $key . '_order', $defaultOrder, 1, 7),
            ];
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
        ] as $field) {
            if (array_key_exists($field, $input)) {
                $config[$field] = trim((string) $input[$field]);
            }
        }

        foreach ([
            'breadcrumb_goster', 'urun_gorseli_goster', 'format_etiketi_goster',
            'adet_kontrolu_goster', 'fiyat_farki_bilgi_kutusu_goster', 'dusuk_stok_uyarisi_goster',
            'tukenme_mesaji_goster', 'kupon_alani_goster', 'ara_toplam_goster',
            'indirim_goster', 'kargo_goster',
        ] as $field) {
            $config[$field] = $this->boolFromInput($input, $field, ! empty($config[$field]));
        }

        return $this->build($config);
    }

    private function orderedSections(array $config): array
    {
        $meta = [
            'sayfa_ust_alani' => ['title' => 'Sayfa Ust Alani', 'icon' => 'ti ti-layout-navbar'],
            'sepet_urunleri_alani' => ['title' => 'Sepet Urunleri Alani', 'icon' => 'ti ti-shopping-cart'],
            'fiyat_guncelleme_uyari_alani' => ['title' => 'Fiyat Guncelleme Uyarisi', 'icon' => 'ti ti-receipt-tax'],
            'stok_uygunluk_uyari_alani' => ['title' => 'Stok / Uygunluk Uyarisi', 'icon' => 'ti ti-alert-triangle'],
            'kupon_kampanya_alani' => ['title' => 'Kupon / Kampanya Alani', 'icon' => 'ti ti-ticket'],
            'sepet_ozeti_cta_alani' => ['title' => 'Sepet Ozeti ve CTA', 'icon' => 'ti ti-cash-banknote'],
            'bos_sepet_alani' => ['title' => 'Bos Sepet Alani', 'icon' => 'ti ti-shopping-cart-off'],
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

    private function sampleItems(array $config): array
    {
        $items = [
            [
                'name' => 'BeAble Pro Defter Seti',
                'type' => 'fiziksel',
                'quantity' => 2,
                'snapshot_price' => 189.90,
                'current_price' => 199.90,
                'stock_level' => 4,
                'image_label' => 'DEFTER',
            ],
            [
                'name' => 'BeAble Pro Egitim PDF Paketi',
                'type' => 'dijital',
                'quantity' => 1,
                'snapshot_price' => 129.90,
                'current_price' => 129.90,
                'stock_level' => null,
                'image_label' => 'PDF',
            ],
            [
                'name' => 'BeAble Pro Kalem',
                'type' => 'fiziksel',
                'quantity' => 1,
                'snapshot_price' => 59.90,
                'current_price' => 59.90,
                'stock_level' => 1,
                'image_label' => 'KALEM',
            ],
        ];

        foreach ($items as &$item) {
            $item['has_price_change'] = (float) $item['snapshot_price'] !== (float) $item['current_price'];
            $item['price_decreased'] = (float) $item['current_price'] < (float) $item['snapshot_price'];
            $item['price_increased'] = (float) $item['current_price'] > (float) $item['snapshot_price'];
            $item['stock_message'] = $this->resolveStockMessage($item, $config);
        }
        unset($item);

        return $items;
    }

    private function resolveStockMessage(array $item, array $config): string
    {
        if (($item['type'] ?? '') !== 'fiziksel') {
            return '';
        }

        $stockLevel = $item['stock_level'] ?? null;
        if ($stockLevel === null) {
            return '';
        }

        if ((int) $stockLevel <= 0) {
            return ! empty($config['tukenme_mesaji_goster']) ? 'Tukendi' : '';
        }

        if ((int) $stockLevel === 1) {
            return ! empty($config['tukenme_mesaji_goster'])
                ? trim((string) ($config['son_urun_mesaj_sablonu'] ?? 'Son urun'))
                : '';
        }

        if ((int) $stockLevel <= 5 && ! empty($config['dusuk_stok_uyarisi_goster'])) {
            return str_replace('{count}', (string) $stockLevel, (string) ($config['dusuk_stok_mesaj_sablonu'] ?? 'Son {count} urun'));
        }

        return '';
    }

    private function normalizeConfig(array $config): array
    {
        $defaults = [
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
