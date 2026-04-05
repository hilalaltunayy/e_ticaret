<?php

namespace App\Database\Seeds;

use App\Models\BaseUuidModel;
use CodeIgniter\Database\Seeder;

class PageManagementSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $this->seedPages($now);
        $this->seedBlockTypes($now);

        echo "PageManagementSeeder: varsayilan page ve block type kayitlari hazirlandi.\n";
    }

    private function seedPages(string $now): void
    {
        if (! $this->db->tableExists('pages')) {
            echo "PageManagementSeeder: pages tablosu bulunamadi.\n";

            return;
        }

        $table = $this->db->table('pages');
        $pages = [
            ['code' => 'home', 'name' => 'Ana Sayfa'],
            ['code' => 'product_list', 'name' => 'Kategori / Liste Sayfasi'],
            ['code' => 'product_detail', 'name' => 'Urun Detay Sayfasi'],
            ['code' => 'cart', 'name' => 'Sepet Sayfasi'],
            ['code' => 'checkout', 'name' => 'Odeme Sayfasi'],
        ];

        foreach ($pages as $page) {
            $exists = $table->select('id')->where('code', $page['code'])->get()->getRowArray();

            if (is_array($exists) && isset($exists['id'])) {
                continue;
            }

            $table->insert([
                'id' => BaseUuidModel::uuidV4(),
                'code' => $page['code'],
                'name' => $page['name'],
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedBlockTypes(string $now): void
    {
        if (! $this->db->tableExists('block_types')) {
            echo "PageManagementSeeder: block_types tablosu bulunamadi.\n";

            return;
        }

        $blockTypes = [
            [
                'code' => 'product_list_layout',
                'name' => 'Product List Layout',
                'description' => 'Urun listeleme sayfasi icin kontrollu layout ve section config blogu.',
                'default_config_json' => json_encode([
                    'page_title' => 'Kategori Sayfasi',
                    'page_subtitle' => 'One cikan urunleri ve filtreleri duzenleyin',
                    'show_breadcrumb' => true,
                    'show_top_banner' => true,
                    'banner_image' => '',
                    'banner_title' => 'Secili Kategori',
                    'banner_subtitle' => 'Listeleme sayfasinin ust alanini yonetin',
                    'show_filters' => true,
                    'filter_position' => 'left',
                    'show_filter_summary' => true,
                    'show_sort_bar' => true,
                    'default_grid_density' => '3',
                    'show_result_count' => true,
                    'card_variant' => 'classic',
                    'grid_density' => '3',
                    'show_badges' => true,
                    'show_quick_actions' => false,
                    'show_favorite_button' => true,
                    'empty_title' => 'Sonuc bulunamadi',
                    'empty_description' => 'Filtreleri degistirerek tekrar deneyin.',
                    'empty_notice_tone' => 'warning',
                    'show_notice' => true,
                    'notice_title' => 'Kargo Bilgisi',
                    'notice_text' => '250 TL ve uzeri siparislerde ucretsiz kargo.',
                    'notice_tone' => 'info',
                    'notice_image' => '',
                ]),
            ],
            [
                'code' => 'hero_banner',
                'name' => 'Hero Banner',
                'description' => 'Ana kahraman banner blogu.',
                'default_config_json' => json_encode([
                    'title' => 'Yeni Koleksiyon',
                    'subtitle' => 'One cikan kampanya alani',
                    'button_text' => 'Hemen Incele',
                    'button_link' => '/products',
                    'variant' => 'light',
                    'image_path' => '',
                ]),
            ],
            [
                'code' => 'best_sellers',
                'name' => 'Best Sellers',
                'description' => 'En cok satan urunleri listeleyen blok.',
                'default_config_json' => json_encode([
                    'title' => 'Cok Satanlar',
                    'item_limit' => 8,
                    'sort_type' => 'sales_desc',
                    'show_badge' => true,
                ]),
            ],
            [
                'code' => 'featured_products',
                'name' => 'Featured Products',
                'description' => 'Editor secimli urunleri one cikarir.',
                'default_config_json' => json_encode([
                    'title' => 'One Cikan Urunler',
                    'item_limit' => 6,
                    'variant' => 'grid',
                ]),
            ],
            [
                'code' => 'category_grid',
                'name' => 'Category Grid',
                'description' => 'Kategori kutularini grid yapida gosterir.',
                'default_config_json' => json_encode([
                    'title' => 'Kategoriler',
                    'item_limit' => 6,
                ]),
            ],
            [
                'code' => 'campaign_banner',
                'name' => 'Campaign Banner',
                'description' => 'Kampanya duyurusu icin banner blogu.',
                'default_config_json' => json_encode([
                    'title' => 'Haftanin Firsati',
                    'subtitle' => 'Sinirli sureli kampanya',
                    'button_text' => 'Kampanyayi Gor',
                    'button_link' => '/campaigns',
                    'variant' => 'dark',
                ]),
            ],
            [
                'code' => 'author_showcase',
                'name' => 'Author Showcase',
                'description' => 'Secili yazarlari vitrinler.',
                'default_config_json' => json_encode([
                    'title' => 'Yazar Seckisi',
                    'item_limit' => 4,
                    'layout_type' => 'grid',
                ]),
            ],
            [
                'code' => 'newsletter',
                'name' => 'Newsletter',
                'description' => 'E-bulten kayit blogu.',
                'default_config_json' => json_encode([
                    'title' => 'Bultene Katil',
                    'subtitle' => 'Yeni kitaplar ve kampanyalar icin kayit ol',
                ]),
            ],
            [
                'code' => 'notice',
                'name' => 'Notice',
                'description' => 'Kisa duyuru veya zengin metin blogu.',
                'default_config_json' => json_encode([
                    'title' => 'Duyuru',
                    'content' => 'Kisa bilgilendirme metni',
                ]),
            ],
            [
                'code' => 'slider',
                'name' => 'Slider',
                'description' => 'Coklu gorsel slayt blogu.',
                'default_config_json' => json_encode([
                    'title' => 'Vitrin Slider',
                    'autoplay' => true,
                ]),
            ],
        ];

        foreach ($blockTypes as $blockType) {
            $blockTypeTable = $this->db->table('block_types');
            $exists = $blockTypeTable->select('id')->where('code', $blockType['code'])->get()->getRowArray();
            $payload = [
                'code' => $blockType['code'],
                'name' => $blockType['name'],
                'description' => $blockType['description'],
                'schema_json' => null,
                'default_config_json' => $blockType['default_config_json'],
                'allowed_zones' => json_encode(['main']),
                'is_active' => 1,
                'updated_at' => $now,
            ];

            if (is_array($exists) && isset($exists['id'])) {
                $this->db->table('block_types')->where('id', $exists['id'])->update($payload);
                continue;
            }

            $this->db->table('block_types')->insert($payload + [
                'id' => BaseUuidModel::uuidV4(),
                'created_at' => $now,
            ]);
        }
    }
}
