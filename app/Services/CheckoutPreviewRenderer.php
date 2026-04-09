<?php

namespace App\Services;

class CheckoutPreviewRenderer
{
    public function build(array $config): array
    {
        $config = $this->normalizeConfig($config);
        $sections = $this->orderedSections($config);

        return [
            'config' => $config,
            'sections' => $sections,
            'hasVisibleSections' => count(array_filter($sections, static fn (array $section): bool => ! empty($section['active']))) > 0,
            'visibleSectionCount' => count(array_filter($sections, static fn (array $section): bool => ! empty($section['active']))),
        ];
    }

    public function buildFromFormInput(array $input, array $baseConfig = []): array
    {
        $config = $this->normalizeConfig($baseConfig);
        $sectionDefaults = [
            'sayfa_ust_alani' => 1,
            'adim_cubugu' => 2,
            'teslimat_fatura_alani' => 3,
            'odeme_yontemi_alani' => 4,
            'siparis_ozeti_alani' => 5,
            'bilgilendirme_guven_cta_alani' => 6,
        ];

        foreach ($sectionDefaults as $key => $defaultOrder) {
            $config['sections'][$key] = [
                'active' => $this->boolFromInput($input, 'section_' . $key . '_active', ! empty($config['sections'][$key]['active'])),
                'order' => $this->intFromInput($input, 'section_' . $key . '_order', $defaultOrder, 1, 6),
            ];
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
        ] as $field) {
            if (array_key_exists($field, $input)) {
                $config[$field] = trim((string) $input[$field]);
            }
        }

        foreach ([
            'breadcrumb_goster',
            'adim_cubugu_gorunur',
            'kart_logo_goster',
            'guven_rozeti_goster',
            'kupon_alani_goster',
            'indirim_satiri_goster',
            'kargo_satiri_goster',
        ] as $field) {
            $config[$field] = $this->boolFromInput($input, $field, ! empty($config[$field]));
        }

        return $this->build($config);
    }

    private function orderedSections(array $config): array
    {
        $meta = [
            'sayfa_ust_alani' => ['title' => 'Sayfa Ust Alani', 'icon' => 'ti ti-layout-navbar'],
            'adim_cubugu' => ['title' => 'Adim Cubugu', 'icon' => 'ti ti-route'],
            'teslimat_fatura_alani' => ['title' => 'Teslimat / Fatura Alani', 'icon' => 'ti ti-address-book'],
            'odeme_yontemi_alani' => ['title' => 'Odeme Yontemi', 'icon' => 'ti ti-credit-card'],
            'siparis_ozeti_alani' => ['title' => 'Siparis Ozeti', 'icon' => 'ti ti-receipt-2'],
            'bilgilendirme_guven_cta_alani' => ['title' => 'Bilgilendirme / Guven / CTA', 'icon' => 'ti ti-shield-check'],
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

    private function normalizeConfig(array $config): array
    {
        $defaults = [
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

        $config['sections'] = is_array($config['sections'] ?? null) ? $config['sections'] : [];
        foreach ($defaults['sections'] as $key => $sectionDefaults) {
            $current = is_array($config['sections'][$key] ?? null) ? $config['sections'][$key] : [];
            $config['sections'][$key] = [
                'active' => array_key_exists('active', $current) ? (bool) $current['active'] : $sectionDefaults['active'],
                'order' => $this->intFromInput(['value' => $current['order'] ?? $sectionDefaults['order']], 'value', $sectionDefaults['order'], 1, 6),
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
