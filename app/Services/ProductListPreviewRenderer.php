<?php

namespace App\Services;

class ProductListPreviewRenderer
{
    public function build(array $config): array
    {
        $config = $this->normalizeConfig($config);
        $sections = $this->buildOrderedSections($config);
        $gridDensity = (string) ($config['grid_yogunlugu'] ?? '3');

        return [
            'config' => $config,
            'sections' => $sections,
            'gridColClass' => $gridDensity === '2'
                ? 'col-md-6'
                : ($gridDensity === '4' ? 'col-xl-3 col-md-6' : 'col-lg-4 col-md-6'),
        ];
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

        $config['sections'] = is_array($config['sections'] ?? null) ? $config['sections'] : [];
        foreach ($defaults['sections'] as $key => $sectionDefaults) {
            $current = is_array($config['sections'][$key] ?? null) ? $config['sections'][$key] : [];
            $config['sections'][$key] = array_merge($sectionDefaults, $current);
        }

        unset($defaults['sections']);

        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }

        return $config;
    }
}
