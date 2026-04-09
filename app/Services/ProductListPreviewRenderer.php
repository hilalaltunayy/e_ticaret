<?php

namespace App\Services;

class ProductListPreviewRenderer
{
    public function build(array $config): array
    {
        $config = $this->normalizeConfig($config);
        $sections = $this->buildOrderedSections($config);

        return [
            'config' => $config,
            'sections' => $sections,
            'gridColClass' => $this->gridColumnClass((string) ($config['grid_yogunlugu'] ?? '3')),
            'visibleSectionCount' => count(array_filter($sections, static fn (array $section): bool => ! empty($section['active']))),
            'hiddenSectionCount' => count(array_filter($sections, static fn (array $section): bool => empty($section['active']))),
            'hasVisibleSections' => count(array_filter($sections, static fn (array $section): bool => ! empty($section['active']))) > 0,
        ];
    }

    public function buildFromFormInput(array $input, array $baseConfig = []): array
    {
        $config = $this->normalizeConfig($baseConfig);

        $sectionDefaults = [
            'sayfa_ust_alani' => 1,
            'filtre_alani' => 2,
            'siralama_sonuc_cubugu' => 3,
            'urun_listesi_gorunumu' => 4,
            'bilgilendirme_kampanya_alani' => 5,
            'bos_sonuc_alani' => 6,
            'alt_aciklama_alani' => 7,
        ];

        foreach ($sectionDefaults as $sectionKey => $defaultOrder) {
            $config['sections'][$sectionKey] = [
                'active' => $this->boolFromInput($input, 'section_' . $sectionKey . '_active', ! empty($config['sections'][$sectionKey]['active'])),
                'order' => $this->intFromInput($input, 'section_' . $sectionKey . '_order', $defaultOrder, 1, 7),
            ];
        }

        $textFields = [
            'sayfa_basligi',
            'sayfa_alt_basligi',
            'banner_gorseli',
            'banner_basligi',
            'banner_alt_metni',
            'filtre_basligi',
            'bilgilendirme_basligi',
            'bilgilendirme_metni',
            'bilgilendirme_gorseli',
            'bos_sonuc_basligi',
            'bos_sonuc_aciklamasi',
            'bos_sonuc_gorseli',
            'alt_aciklama_basligi',
            'alt_aciklama_metni',
        ];

        foreach ($textFields as $field) {
            if (array_key_exists($field, $input)) {
                $config[$field] = trim((string) $input[$field]);
            }
        }

        $boolFields = [
            'breadcrumb_goster',
            'ust_banner_goster',
            'filtreler_goster',
            'filtre_ozeti_goster',
            'siralama_cubugu_goster',
            'sonuc_sayisi_goster',
            'aktif_filtre_etiketleri_goster',
            'rozetleri_goster',
            'favori_butonu_goster',
            'hizli_aksiyonlari_goster',
            'bilgilendirme_alani_goster',
            'alt_aciklama_goster',
        ];

        foreach ($boolFields as $field) {
            $config[$field] = $this->boolFromInput($input, $field, ! empty($config[$field]));
        }

        $config['banner_tonu'] = $this->enumFromInput($input, 'banner_tonu', ['light', 'dark', 'soft', 'accent'], (string) ($config['banner_tonu'] ?? 'soft'));
        $config['filtre_konumu'] = $this->enumFromInput($input, 'filtre_konumu', ['left', 'top'], (string) ($config['filtre_konumu'] ?? 'left'));
        $config['varsayilan_grid_yogunlugu'] = $this->enumFromInput($input, 'varsayilan_grid_yogunlugu', ['2', '3', '4'], (string) ($config['varsayilan_grid_yogunlugu'] ?? '3'));
        $config['kart_varyanti'] = $this->enumFromInput($input, 'kart_varyanti', ['classic', 'minimal', 'elevated'], (string) ($config['kart_varyanti'] ?? 'classic'));
        $config['grid_yogunlugu'] = $this->enumFromInput($input, 'grid_yogunlugu', ['2', '3', '4'], (string) ($config['grid_yogunlugu'] ?? '3'));
        $config['bilgilendirme_tonu'] = $this->enumFromInput($input, 'bilgilendirme_tonu', ['info', 'success', 'warning', 'danger'], (string) ($config['bilgilendirme_tonu'] ?? 'info'));
        $config['bos_sonuc_tonu'] = $this->enumFromInput($input, 'bos_sonuc_tonu', ['info', 'success', 'warning', 'danger'], (string) ($config['bos_sonuc_tonu'] ?? 'warning'));

        return $this->build($config);
    }

    private function buildOrderedSections(array $config): array
    {
        $sectionMeta = [
            'sayfa_ust_alani' => ['title' => 'Sayfa Ust Alani', 'icon' => 'ti ti-layout-navbar'],
            'filtre_alani' => ['title' => 'Filtre Alani', 'icon' => 'ti ti-adjustments-horizontal'],
            'siralama_sonuc_cubugu' => ['title' => 'Siralama ve Sonuc Cubugu', 'icon' => 'ti ti-arrows-sort'],
            'urun_listesi_gorunumu' => ['title' => 'Urun Listesi Gorunumu', 'icon' => 'ti ti-layout-grid'],
            'bilgilendirme_kampanya_alani' => ['title' => 'Bilgilendirme / Kampanya Alani', 'icon' => 'ti ti-speakerphone'],
            'bos_sonuc_alani' => ['title' => 'Bos Sonuc Alani', 'icon' => 'ti ti-mood-empty'],
            'alt_aciklama_alani' => ['title' => 'Alt Aciklama Alani', 'icon' => 'ti ti-align-box-bottom-left'],
        ];

        $orderedSections = [];
        foreach ($sectionMeta as $key => $meta) {
            $orderedSections[] = [
                'key' => $key,
                'title' => $meta['title'],
                'icon' => $meta['icon'],
                'active' => ! empty($config['sections'][$key]['active']),
                'order' => (int) ($config['sections'][$key]['order'] ?? 99),
            ];
        }

        usort($orderedSections, static function (array $left, array $right): int {
            return $left['order'] <=> $right['order'];
        });

        return $orderedSections;
    }

    private function normalizeConfig(array $config): array
    {
        $legacyMap = [
            'page_title' => 'sayfa_basligi',
            'page_subtitle' => 'sayfa_alt_basligi',
            'show_breadcrumb' => 'breadcrumb_goster',
            'show_top_banner' => 'ust_banner_goster',
            'banner_image' => 'banner_gorseli',
            'banner_title' => 'banner_basligi',
            'banner_subtitle' => 'banner_alt_metni',
            'show_filters' => 'filtreler_goster',
            'filter_position' => 'filtre_konumu',
            'show_filter_summary' => 'filtre_ozeti_goster',
            'show_sort_bar' => 'siralama_cubugu_goster',
            'show_result_count' => 'sonuc_sayisi_goster',
            'default_grid_density' => 'varsayilan_grid_yogunlugu',
            'card_variant' => 'kart_varyanti',
            'grid_density' => 'grid_yogunlugu',
            'show_badges' => 'rozetleri_goster',
            'show_favorite_button' => 'favori_butonu_goster',
            'show_quick_actions' => 'hizli_aksiyonlari_goster',
            'show_notice' => 'bilgilendirme_alani_goster',
            'notice_title' => 'bilgilendirme_basligi',
            'notice_text' => 'bilgilendirme_metni',
            'notice_tone' => 'bilgilendirme_tonu',
            'notice_image' => 'bilgilendirme_gorseli',
            'empty_title' => 'bos_sonuc_basligi',
            'empty_description' => 'bos_sonuc_aciklamasi',
            'empty_notice_tone' => 'bos_sonuc_tonu',
            'empty_image' => 'bos_sonuc_gorseli',
        ];

        $defaults = [
            'sections' => [
                'sayfa_ust_alani' => ['active' => true, 'order' => 1],
                'filtre_alani' => ['active' => true, 'order' => 2],
                'siralama_sonuc_cubugu' => ['active' => true, 'order' => 3],
                'urun_listesi_gorunumu' => ['active' => true, 'order' => 4],
                'bilgilendirme_kampanya_alani' => ['active' => true, 'order' => 5],
                'bos_sonuc_alani' => ['active' => true, 'order' => 6],
                'alt_aciklama_alani' => ['active' => false, 'order' => 7],
            ],
            'sayfa_basligi' => 'Kategori Sayfasi',
            'sayfa_alt_basligi' => 'One cikan urunleri ve filtreleri duzenleyin',
            'breadcrumb_goster' => true,
            'ust_banner_goster' => true,
            'banner_gorseli' => '',
            'banner_basligi' => 'Secili Kategori',
            'banner_alt_metni' => 'Listeleme sayfasinin ust alanini yonetin',
            'banner_tonu' => 'soft',
            'filtreler_goster' => true,
            'filtre_konumu' => 'left',
            'filtre_ozeti_goster' => true,
            'filtre_basligi' => 'Filtreler',
            'siralama_cubugu_goster' => true,
            'sonuc_sayisi_goster' => true,
            'aktif_filtre_etiketleri_goster' => true,
            'varsayilan_grid_yogunlugu' => '3',
            'kart_varyanti' => 'classic',
            'grid_yogunlugu' => '3',
            'rozetleri_goster' => true,
            'favori_butonu_goster' => true,
            'hizli_aksiyonlari_goster' => false,
            'bilgilendirme_alani_goster' => true,
            'bilgilendirme_basligi' => 'Kargo Bilgisi',
            'bilgilendirme_metni' => '250 TL ve uzeri siparislerde ucretsiz kargo.',
            'bilgilendirme_tonu' => 'info',
            'bilgilendirme_gorseli' => '',
            'bos_sonuc_basligi' => 'Sonuc bulunamadi',
            'bos_sonuc_aciklamasi' => 'Filtreleri degistirerek tekrar deneyin.',
            'bos_sonuc_tonu' => 'warning',
            'bos_sonuc_gorseli' => '',
            'alt_aciklama_goster' => false,
            'alt_aciklama_basligi' => 'Listeleme Aciklamasi',
            'alt_aciklama_metni' => 'Bu alan kategoriye ait aciklayici metinler icin kullanilir.',
        ];

        foreach ($legacyMap as $oldKey => $newKey) {
            if (! array_key_exists($newKey, $config) && array_key_exists($oldKey, $config)) {
                $config[$newKey] = $config[$oldKey];
            }
        }

        $config['sections'] = is_array($config['sections'] ?? null) ? $config['sections'] : [];
        foreach ($defaults['sections'] as $key => $sectionDefaults) {
            $current = is_array($config['sections'][$key] ?? null) ? $config['sections'][$key] : [];
            $config['sections'][$key] = [
                'active' => array_key_exists('active', $current) ? (bool) $current['active'] : $sectionDefaults['active'],
                'order' => $this->normalizeSectionOrder($current['order'] ?? $sectionDefaults['order'], $sectionDefaults['order']),
            ];
        }

        unset($defaults['sections']);

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }

        $config['sayfa_basligi'] = $this->fallbackText((string) ($config['sayfa_basligi'] ?? ''), $defaults['sayfa_basligi']);
        $config['sayfa_alt_basligi'] = $this->fallbackText((string) ($config['sayfa_alt_basligi'] ?? ''), $defaults['sayfa_alt_basligi']);
        $config['banner_basligi'] = $this->fallbackText((string) ($config['banner_basligi'] ?? ''), $defaults['banner_basligi']);
        $config['banner_alt_metni'] = $this->fallbackText((string) ($config['banner_alt_metni'] ?? ''), $defaults['banner_alt_metni']);
        $config['filtre_basligi'] = $this->fallbackText((string) ($config['filtre_basligi'] ?? ''), $defaults['filtre_basligi']);
        $config['bilgilendirme_basligi'] = $this->fallbackText((string) ($config['bilgilendirme_basligi'] ?? ''), $defaults['bilgilendirme_basligi']);
        $config['bilgilendirme_metni'] = $this->fallbackText((string) ($config['bilgilendirme_metni'] ?? ''), $defaults['bilgilendirme_metni']);
        $config['bos_sonuc_basligi'] = $this->fallbackText((string) ($config['bos_sonuc_basligi'] ?? ''), $defaults['bos_sonuc_basligi']);
        $config['bos_sonuc_aciklamasi'] = $this->fallbackText((string) ($config['bos_sonuc_aciklamasi'] ?? ''), $defaults['bos_sonuc_aciklamasi']);
        $config['alt_aciklama_basligi'] = $this->fallbackText((string) ($config['alt_aciklama_basligi'] ?? ''), $defaults['alt_aciklama_basligi']);
        $config['alt_aciklama_metni'] = $this->fallbackText((string) ($config['alt_aciklama_metni'] ?? ''), $defaults['alt_aciklama_metni']);

        $config['banner_tonu'] = $this->normalizeEnum((string) ($config['banner_tonu'] ?? 'soft'), ['light', 'dark', 'soft', 'accent'], 'soft');
        $config['filtre_konumu'] = $this->normalizeEnum((string) ($config['filtre_konumu'] ?? 'left'), ['left', 'top'], 'left');
        $config['varsayilan_grid_yogunlugu'] = $this->normalizeEnum((string) ($config['varsayilan_grid_yogunlugu'] ?? '3'), ['2', '3', '4'], '3');
        $config['kart_varyanti'] = $this->normalizeEnum((string) ($config['kart_varyanti'] ?? 'classic'), ['classic', 'minimal', 'elevated'], 'classic');
        $config['grid_yogunlugu'] = $this->normalizeEnum((string) ($config['grid_yogunlugu'] ?? '3'), ['2', '3', '4'], '3');
        $config['bilgilendirme_tonu'] = $this->normalizeEnum((string) ($config['bilgilendirme_tonu'] ?? 'info'), ['info', 'success', 'warning', 'danger'], 'info');
        $config['bos_sonuc_tonu'] = $this->normalizeEnum((string) ($config['bos_sonuc_tonu'] ?? 'warning'), ['info', 'success', 'warning', 'danger'], 'warning');

        return $config;
    }

    private function gridColumnClass(string $gridDensity): string
    {
        return $gridDensity === '2'
            ? 'col-md-6'
            : ($gridDensity === '4' ? 'col-xl-3 col-md-6' : 'col-lg-4 col-md-6');
    }

    private function fallbackText(string $value, string $fallback): string
    {
        $value = trim($value);

        return $value === '' ? $fallback : $value;
    }

    private function normalizeSectionOrder(mixed $value, int $fallback): int
    {
        $order = (int) $value;

        return ($order < 1 || $order > 7) ? $fallback : $order;
    }

    private function normalizeEnum(string $value, array $allowed, string $fallback): string
    {
        $value = trim($value);

        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private function boolFromInput(array $input, string $key, bool $fallback): bool
    {
        if (! array_key_exists($key, $input)) {
            return false;
        }

        $value = $input[$key];

        return ! in_array($value, [null, '', '0', 0, false], true);
    }

    private function intFromInput(array $input, string $key, int $fallback, int $min, int $max): int
    {
        if (! array_key_exists($key, $input)) {
            return $fallback;
        }

        $value = (int) $input[$key];

        return ($value < $min || $value > $max) ? $fallback : $value;
    }

    private function enumFromInput(array $input, string $key, array $allowed, string $fallback): string
    {
        if (! array_key_exists($key, $input)) {
            return $fallback;
        }

        return $this->normalizeEnum((string) $input[$key], $allowed, $fallback);
    }
}
